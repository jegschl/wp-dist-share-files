<?php
/**
 * Series de grúas y varios certificados por número de serie.
 *
 * @package Wp_Dosf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dosf_Series {

	public static function table_names() {
		global $wpdb;

		return array(
			'series' => $wpdb->prefix . 'dosf_series',
			'certs'  => $wpdb->prefix . 'dosf_shared_objs',
			'ruts'   => $wpdb->prefix . 'dosf_so_ruts_links',
			'queue'  => $wpdb->prefix . 'dosf_expiration_warnig_email_queue',
		);
	}

	public static function migrate() {
		global $wpdb;

		$tables = self::table_names();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tables['series']} (
			id int unsigned NOT NULL AUTO_INCREMENT,
			serie varchar(191) NOT NULL,
			created timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY serie_uid (serie)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		self::ensure_column( $tables['certs'], 'serie_id', 'int unsigned NULL' );
		self::ensure_column( $tables['certs'], 'cert_title', "varchar(256) NOT NULL DEFAULT ''" );
		self::ensure_column( $tables['certs'], 'file_unavailable', 'tinyint(1) NOT NULL DEFAULT 0' );
		self::ensure_column( $tables['certs'], 'download_code_issued', 'datetime NULL' );
		self::ensure_column( $tables['certs'], 'recipient_extra', 'longtext NULL' );
		self::ensure_expire_warn_column();
		self::ensure_column( $tables['ruts'], 'serie_id', 'int unsigned NULL' );

		$titles = $wpdb->get_col( "SELECT DISTINCT title FROM {$tables['certs']} WHERE title <> ''" );
		if ( ! is_array( $titles ) ) {
			return false;
		}

		foreach ( $titles as $title ) {
			$serie_id = self::find_or_create( $title );
			if ( ! $serie_id ) {
				return false;
			}

			$certs = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, file_name, cert_title FROM {$tables['certs']} WHERE title = %s AND (serie_id IS NULL OR serie_id = 0)",
					$title
				)
			);

			$used = array();
			foreach ( $certs as $cert ) {
				$cert_title = self::initial_cert_title( $cert );
				$base       = $cert_title;
				$suffix     = 2;
				while ( isset( $used[ $cert_title ] ) || self::title_taken( $serie_id, $cert_title, intval( $cert->id ) ) ) {
					$cert_title = $base . ' (' . $suffix . ')';
					$suffix++;
				}
				$used[ $cert_title ] = true;

				$updated = $wpdb->update(
					$tables['certs'],
					array(
						'serie_id'   => $serie_id,
						'cert_title' => $cert_title,
					),
					array( 'id' => intval( $cert->id ) ),
					array( '%d', '%s' ),
					array( '%d' )
				);

				if ( $updated === false ) {
					return false;
				}
			}

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$tables['ruts']} r
					 INNER JOIN {$tables['certs']} c ON c.id = r.so_id
					 SET r.serie_id = c.serie_id
					 WHERE c.serie_id = %d AND (r.serie_id IS NULL OR r.serie_id = 0)",
					$serie_id
				)
			);
		}

		self::mark_missing_attachments();

		return true;
	}

	private static function mark_missing_attachments() {
		global $wpdb;

		$tables = self::table_names();
		$ids   = $wpdb->get_col( "SELECT DISTINCT wp_file_obj_id FROM {$tables['certs']} WHERE wp_file_obj_id > 0" );
		if ( ! is_array( $ids ) ) {
			return;
		}

		foreach ( $ids as $attachment_id ) {
			$attachment_id = intval( $attachment_id );
			$post          = get_post( $attachment_id );
			$missing       = ! $post || $post->post_type !== 'attachment' || $post->post_status === 'trash' || ! wp_get_attachment_url( $attachment_id );
			if ( ! $missing ) {
				continue;
			}
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$tables['certs']} SET file_unavailable = 1 WHERE wp_file_obj_id = %d",
					$attachment_id
				)
			);
		}
	}

	private static function initial_cert_title( $cert ) {
		if ( ! empty( $cert->cert_title ) ) {
			return $cert->cert_title;
		}

		$name = isset( $cert->file_name ) ? trim( $cert->file_name ) : '';
		if ( $name !== '' && preg_match( '/[A-Za-z]/', $name ) ) {
			return $name;
		}

		return 'Certificado ' . intval( $cert->id );
	}

	public static function ensure_expire_warn_column() {
		$tables = self::table_names();
		self::ensure_column( $tables['certs'], 'expire_warn_sent', 'int unsigned NOT NULL DEFAULT 0' );
		self::ensure_column( $tables['certs'], 'expire_warn_failed', 'int unsigned NOT NULL DEFAULT 0' );
		self::ensure_column( $tables['certs'], 'expire_warn_sent_at', 'longtext NULL' );
	}

	private static function ensure_column( $table, $column, $definition ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM `' . $table . '` LIKE %s', $column ) );
		if ( $exists === $column ) {
			return;
		}

		$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}" );
	}

	public static function find_or_create( $serie ) {
		global $wpdb;

		$serie = trim( (string) $serie );
		if ( $serie === '' ) {
			return 0;
		}

		$tables = self::table_names();
		$id     = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$tables['series']} WHERE serie = %s", $serie ) );
		if ( $id ) {
			return intval( $id );
		}

		$inserted = $wpdb->insert(
			$tables['series'],
			array( 'serie' => $serie ),
			array( '%s' )
		);

		if ( ! $inserted ) {
			$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$tables['series']} WHERE serie = %s", $serie ) );
			return $id ? intval( $id ) : 0;
		}

		return intval( $wpdb->insert_id );
	}

	public static function title_taken( $serie_id, $cert_title, $exclude_id = 0 ) {
		global $wpdb;

		$tables = self::table_names();
		$id     = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$tables['certs']} WHERE serie_id = %d AND cert_title = %s AND id <> %d LIMIT 1",
				intval( $serie_id ),
				$cert_title,
				intval( $exclude_id )
			)
		);

		return ! empty( $id );
	}

	public static function sync_ruts( $serie_id, $ruts ) {
		global $wpdb;

		$serie_id = intval( $serie_id );
		if ( ! $serie_id ) {
			return;
		}

		$tables = self::table_names();
		$wpdb->delete( $tables['ruts'], array( 'serie_id' => $serie_id ), array( '%d' ) );

		if ( ! is_array( $ruts ) ) {
			return;
		}

		$seen = array();
		foreach ( $ruts as $rut ) {
			$rut = preg_replace( '/[.\-\s]/', '', trim( (string) $rut ) );
			if ( $rut === '' || isset( $seen[ $rut ] ) ) {
				continue;
			}
			$seen[ $rut ] = true;
			$wpdb->insert(
				$tables['ruts'],
				array(
					'so_id'    => 0,
					'serie_id' => $serie_id,
					'rut'      => $rut,
				),
				array( '%d', '%d', '%s' )
			);
		}
	}

	private static function email_list( $data, $key ) {
		if ( ! isset( $data[ $key ] ) ) {
			return '';
		}
		if ( is_array( $data[ $key ] ) ) {
			return implode( ',', $data[ $key ] );
		}
		return (string) $data[ $key ];
	}

	public static function sanitize_email_list( $raw ) {
		$parts = preg_split( '/\s*,\s*/', (string) $raw );
		$seen  = array();
		$list  = array();
		if ( ! is_array( $parts ) ) {
			return '';
		}
		foreach ( $parts as $part ) {
			$part = sanitize_email( trim( $part ) );
			if ( $part === '' || ! is_email( $part ) ) {
				continue;
			}
			$token = strtolower( $part );
			if ( isset( $seen[ $token ] ) ) {
				continue;
			}
			$seen[ $token ] = true;
			$list[]         = $part;
		}
		return implode( ',', $list );
	}

	public static function recipient_map( $cert ) {
		$extra = array();
		if ( is_object( $cert ) && ! empty( $cert->recipient_extra ) ) {
			$decoded = json_decode( $cert->recipient_extra, true );
			if ( is_array( $decoded ) ) {
				$extra = $decoded;
			}
		}
		$map = array();
		if ( ! class_exists( 'Wp_Dosf_Admin' ) ) {
			return $map;
		}
		foreach ( Wp_Dosf_Admin::recipient_sets() as $set ) {
			$id = $set['id'];
			if ( $id === 'email' || $id === 'email2' || $id === 'email3' ) {
				$map[ $id ] = ( is_object( $cert ) && isset( $cert->$id ) ) ? (string) $cert->$id : '';
			} else {
				$map[ $id ] = isset( $extra[ $id ] ) ? (string) $extra[ $id ] : '';
			}
		}
		return $map;
	}

	public static function recipient_storage( $data, $current ) {
		$sets  = class_exists( 'Wp_Dosf_Admin' ) ? Wp_Dosf_Admin::recipient_sets() : array();
		$known = array();
		foreach ( $sets as $set ) {
			$known[ $set['id'] ] = true;
		}

		$legacy = array(
			'email'  => ( $current && isset( $current->email ) ) ? (string) $current->email : '',
			'email2' => ( $current && isset( $current->email2 ) ) ? (string) $current->email2 : '',
			'email3' => ( $current && isset( $current->email3 ) ) ? (string) $current->email3 : '',
		);
		$extra = array();
		if ( $current && ! empty( $current->recipient_extra ) ) {
			$decoded = json_decode( $current->recipient_extra, true );
			if ( is_array( $decoded ) ) {
				$extra = $decoded;
			}
		}
		foreach ( array_keys( $extra ) as $id ) {
			if ( ! isset( $known[ $id ] ) ) {
				unset( $extra[ $id ] );
			}
		}

		$incoming = ( isset( $data['recipients'] ) && is_array( $data['recipients'] ) ) ? $data['recipients'] : null;
		if ( $incoming === null ) {
			foreach ( array( 'email', 'email2', 'email3' ) as $key ) {
				if ( isset( $known[ $key ] ) && array_key_exists( $key, $data ) ) {
					$legacy[ $key ] = self::sanitize_email_list( self::email_list( $data, $key ) );
				}
			}
		} else {
			foreach ( $sets as $set ) {
				$id = $set['id'];
				if ( ! array_key_exists( $id, $incoming ) ) {
					continue;
				}
				$value = self::sanitize_email_list( self::email_list( $incoming, $id ) );
				if ( $id === 'email' || $id === 'email2' || $id === 'email3' ) {
					$legacy[ $id ] = $value;
				} else {
					$extra[ $id ] = $value;
				}
			}
		}

		return array(
			'email'           => $legacy['email'],
			'email2'          => $legacy['email2'],
			'email3'          => $legacy['email3'],
			'recipient_extra' => wp_json_encode( $extra ),
		);
	}

	public static function purge_removed_recipient_sets( $kept_ids ) {
		global $wpdb;

		if ( ! is_array( $kept_ids ) ) {
			return;
		}
		$tables = self::table_names();
		$column = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM `' . $tables['certs'] . '` LIKE %s', 'recipient_extra' ) );
		if ( $column !== 'recipient_extra' ) {
			return;
		}
		$rows = $wpdb->get_results( "SELECT id, recipient_extra FROM {$tables['certs']} WHERE recipient_extra IS NOT NULL AND recipient_extra <> ''" );
		if ( ! is_array( $rows ) ) {
			return;
		}
		foreach ( $rows as $row ) {
			$extra = json_decode( $row->recipient_extra, true );
			if ( ! is_array( $extra ) ) {
				continue;
			}
			$changed = false;
			foreach ( array_keys( $extra ) as $id ) {
				if ( ! isset( $kept_ids[ $id ] ) ) {
					unset( $extra[ $id ] );
					$changed = true;
				}
			}
			if ( ! $changed ) {
				continue;
			}
			$wpdb->update(
				$tables['certs'],
				array( 'recipient_extra' => wp_json_encode( $extra ) ),
				array( 'id' => intval( $row->id ) ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	public static function generate_code( $length = 6 ) {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$string     = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$string .= $characters[ mt_rand( 0, strlen( $characters ) - 1 ) ];
		}
		return $string;
	}

	public static function refresh_download_code( $cert_id ) {
		global $wpdb;

		$cert_id = intval( $cert_id );
		if ( $cert_id < 1 ) {
			return '';
		}

		$tables  = self::table_names();
		$current = $wpdb->get_row( $wpdb->prepare( "SELECT download_code, download_code_issued FROM {$tables['certs']} WHERE id = %d", $cert_id ) );
		if ( ! $current ) {
			return '';
		}

		$existing = trim( (string) $current->download_code );
		if ( $existing !== '' && Wp_Dosf_Admin::download_code_should_reuse( $current->download_code_issued ) ) {
			return $existing;
		}

		$code  = self::generate_code();
		$guard = 0;
		while ( strtoupper( $code ) === strtoupper( $existing ) && $guard < 5 ) {
			$code = self::generate_code();
			$guard++;
		}

		$updated = $wpdb->update(
			$tables['certs'],
			array(
				'download_code'        => $code,
				'download_code_issued' => current_time( 'mysql' ),
			),
			array( 'id' => $cert_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		if ( $updated === false ) {
			return '';
		}

		return $code;
	}

	public static function save_certificate( $data ) {
		global $wpdb;

		$tables     = self::table_names();
		$serie      = isset( $data['serie'] ) ? trim( (string) $data['serie'] ) : '';
		$cert_title = isset( $data['cert_title'] ) ? trim( (string) $data['cert_title'] ) : '';
		$update_id  = ( isset( $data['updateId'] ) && $data['updateId'] !== null && $data['updateId'] !== '' ) ? intval( $data['updateId'] ) : 0;

		if ( $update_id ) {
			$current = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tables['certs']} WHERE id = %d", $update_id ) );
			if ( ! $current ) {
				return array(
					'error'   => true,
					'payload' => array(
						'dosf_operation'         => 'UPDATE',
						'dosfUpdate_post_status' => 'error',
						'err_msg'                => 'Certificado no encontrado',
					),
				);
			}
			$serie_id = intval( $current->serie_id );
			if ( $serie === '' ) {
				$serie = (string) $wpdb->get_var( $wpdb->prepare( "SELECT serie FROM {$tables['series']} WHERE id = %d", $serie_id ) );
			}
		} else {
			if ( $serie === '' ) {
				return array(
					'error'   => true,
					'payload' => array(
						'dosf_operation'         => 'INSERT',
						'dosfAddNew_post_status' => 'error',
						'err_code'               => '400',
						'err_msg'                => 'Número de serie requerido',
					),
				);
			}
			$serie_id = self::find_or_create( $serie );
		}

		if ( $cert_title === '' ) {
			return array(
				'error'   => true,
				'payload' => array(
					'dosf_operation'         => $update_id ? 'UPDATE' : 'INSERT',
					'dosfAddNew_post_status' => 'error',
					'dosfUpdate_post_status' => 'error',
					'err_code'               => '400',
					'err_msg'                => 'Título de certificado requerido',
				),
			);
		}

		if ( ! $serie_id || self::title_taken( $serie_id, $cert_title, $update_id ) ) {
			return array(
				'error'   => true,
				'payload' => array(
					'dosf_operation'         => $update_id ? 'UPDATE' : 'INSERT',
					'dosfAddNew_post_status' => 'error',
					'dosfUpdate_post_status' => 'error',
					'err_code'               => '403',
					'err_msg'                => 'Duplicated certificate title',
				),
			);
		}

		$attachment_id   = isset( $data['wp_obj_file_id'] ) ? intval( $data['wp_obj_file_id'] ) : 0;
		$file_unavailable = 0;
		$restored_file    = false;
		if ( $attachment_id > 0 ) {
			$restored_file = Dosf_Private_Files::is_restored( $attachment_id );
			if ( $restored_file ) {
				$attachment_post = get_post( $attachment_id );
				if ( $attachment_post && $attachment_post->post_status === 'trash' ) {
					Dosf_Private_Files::$suppress_public_restore = true;
					wp_untrash_post( $attachment_id );
					Dosf_Private_Files::$suppress_public_restore = false;
				}
			}
			$sibling_marked = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$tables['certs']} WHERE wp_file_obj_id = %d AND file_unavailable = 1 AND id <> %d LIMIT 1",
					$attachment_id,
					$update_id ? intval( $update_id ) : 0
				)
			);
			$has_private_bytes = Dosf_Private_Files::attachment_has_bytes( $attachment_id );
			if ( ! $has_private_bytes && ! Wp_Dosf_Admin::attachment_is_downloadable( $attachment_id ) ) {
				$file_unavailable = 1;
			} elseif ( ! $restored_file && $sibling_marked ) {
				$file_unavailable = 1;
			}
		}

		$emails = self::recipient_storage( $data, $update_id ? $current : null );
		$row = array(
			'title'            => $serie,
			'cert_title'       => $cert_title,
			'serie_id'         => $serie_id,
			'file_name'        => isset( $data['file_name'] ) ? $data['file_name'] : '',
			'wp_file_obj_id'   => $attachment_id,
			'file_unavailable' => $file_unavailable,
			'email'            => $emails['email'],
			'email2'           => $emails['email2'],
			'email3'           => $emails['email3'],
			'recipient_extra'  => $emails['recipient_extra'],
			'emision'          => isset( $data['emision'] ) ? $data['emision'] : null,
		);

		if ( $update_id ) {
			self::ensure_expire_warn_column();
			$new_emision = isset( $row['emision'] ) ? $row['emision'] : null;
			$old_ts      = ( $current && ! empty( $current->emision ) ) ? strtotime( $current->emision ) : 0;
			$new_ts      = ! empty( $new_emision ) ? strtotime( $new_emision ) : 0;
			if ( $old_ts !== $new_ts ) {
				$row['expire_warn_sent'] = 0;
				$row['expire_warn_failed'] = 0;
				$row['expire_warn_sent_at'] = '';
			}
			$wpdb->update( $tables['certs'], $row, array( 'id' => $update_id ) );
			self::sync_ruts( $serie_id, isset( $data['linked_ruts'] ) ? $data['linked_ruts'] : array() );
			if ( $attachment_id > 0 && ! Dosf_Private_Files::protect_certificate_attachment( $attachment_id ) ) {
				delete_option( Dosf_Private_Files::OPTION_READY );
			}
			return array(
				'error'     => false,
				'operation' => 'UPDATE',
				'id'        => $update_id,
			);
		}

		$row['download_code'] = self::generate_code();
		$row['download_code_issued'] = current_time( 'mysql' );
		$wpdb->insert( $tables['certs'], $row );
		$id = intval( $wpdb->insert_id );
		self::sync_ruts( $serie_id, isset( $data['linked_ruts'] ) ? $data['linked_ruts'] : array() );
		if ( $attachment_id > 0 && ! Dosf_Private_Files::protect_certificate_attachment( $attachment_id ) ) {
			delete_option( Dosf_Private_Files::OPTION_READY );
		}

		$file_path = '';
		if ( ! empty( $row['wp_file_obj_id'] ) ) {
			$file_path = get_attached_file( intval( $row['wp_file_obj_id'] ) );
		}

		return array(
			'error'     => false,
			'operation' => 'INSERT',
			'id'        => $id,
			'mail_args' => array(
				'id'            => $id,
				'email'         => $row['email'],
				'email2'        => $row['email2'],
				'email3'        => $row['email3'],
				'download_code' => $row['download_code'],
				'file'          => $file_path,
				'serial'        => $serie,
				'cert_title'    => $cert_title,
			),
		);
	}

	public static function delete_certificates( $ids ) {
		global $wpdb;

		$tables  = self::table_names();
		$details = array();

		if ( ! is_array( $ids ) ) {
			return $details;
		}

		foreach ( $ids as $id ) {
			$id = intval( $id );
			if ( ! $id ) {
				continue;
			}

			$serie_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT serie_id FROM {$tables['certs']} WHERE id = %d", $id ) ) );
			$deleted  = $wpdb->delete( $tables['certs'], array( 'id' => $id ), array( '%d' ) );
			$wpdb->delete( $tables['queue'], array( 'so_id' => $id ), array( '%d' ) );

			$details[ $id ] = array( 'del-dosf-res' => $deleted !== false );

			if ( $serie_id ) {
				$left = intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tables['certs']} WHERE serie_id = %d", $serie_id ) ) );
				if ( $left === 0 ) {
					$wpdb->delete( $tables['ruts'], array( 'serie_id' => $serie_id ), array( '%d' ) );
					$wpdb->delete( $tables['series'], array( 'id' => $serie_id ), array( '%d' ) );
				}
			}
		}

		return $details;
	}

	public static function context( $cert_id ) {
		global $wpdb;

		$tables = self::table_names();
		$row    = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT c.*, s.serie AS serie_name
				 FROM {$tables['certs']} c
				 LEFT JOIN {$tables['series']} s ON s.id = c.serie_id
				 WHERE c.id = %d",
				intval( $cert_id )
			)
		);

		if ( ! $row ) {
			return null;
		}

		$row->serie = ! empty( $row->serie_name ) ? $row->serie_name : $row->title;
		if ( empty( $row->cert_title ) ) {
			$row->cert_title = $row->title;
		}

		return $row;
	}

	public static function datatables_response( $params ) {
		global $wpdb;

		self::ensure_expire_warn_column();

		$tables = self::table_names();
		$draw   = isset( $params['draw'] ) ? intval( $params['draw'] ) : 0;
		$start  = isset( $params['start'] ) ? max( 0, intval( $params['start'] ) ) : 0;
		$length = isset( $params['length'] ) ? intval( $params['length'] ) : 10;
		if ( $length < 1 ) {
			$length = 10;
		}
		if ( $length > 100 ) {
			$length = 100;
		}

		$search = '';
		if ( isset( $params['search']['value'] ) ) {
			$search = trim( (string) $params['search']['value'] );
		}

		$where = '1=1';
		if ( $search !== '' ) {
			$like  = '%' . $wpdb->esc_like( $search ) . '%';
			$where = $wpdb->prepare(
				'(s.serie LIKE %s OR c.cert_title LIKE %s OR c.file_name LIKE %s OR c.email LIKE %s OR c.email2 LIKE %s OR c.email3 LIKE %s OR c.recipient_extra LIKE %s OR r.rut LIKE %s)',
				$like,
				$like,
				$like,
				$like,
				$like,
				$like,
				$like,
				$like
			);
		}

		$from = "FROM {$tables['series']} s
			LEFT JOIN {$tables['certs']} c ON c.serie_id = s.id
			LEFT JOIN {$tables['ruts']} r ON r.serie_id = s.id";

		$total = intval( $wpdb->get_var( "SELECT COUNT(DISTINCT s.id) {$from} WHERE {$where}" ) );

		$ids = $wpdb->get_col( "SELECT DISTINCT s.id {$from} WHERE {$where} ORDER BY s.serie ASC LIMIT {$start}, {$length}" );
		$rows = array();

		if ( $ids ) {
			$id_list = implode( ',', array_map( 'intval', $ids ) );
			$series  = $wpdb->get_results( "SELECT id, serie FROM {$tables['series']} WHERE id IN ({$id_list}) ORDER BY serie ASC" );

			foreach ( $series as $serie ) {
				$ruts = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT rut FROM {$tables['ruts']} WHERE serie_id = %d AND rut <> ''",
						intval( $serie->id )
					)
				);
				$certs = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$tables['certs']} WHERE serie_id = %d ORDER BY cert_title ASC",
						intval( $serie->id )
					)
				);

				$cert_rows = array();
				foreach ( $certs as $cert ) {
					$cert_rows[] = array(
						'id'            => $cert->id,
						'cert_title'    => $cert->cert_title,
						'emision'       => $cert->emision,
						'file_name'     => $cert->file_name,
						'attachment_id'    => $cert->wp_file_obj_id,
						'file_unavailable' => isset( $cert->file_unavailable ) ? intval( $cert->file_unavailable ) : 0,
						'email'         => $cert->email,
						'email2'        => $cert->email2,
						'email3'        => $cert->email3,
						'recipients'    => self::recipient_map( $cert ),
						'status'        => Wp_Dosf_Admin::get_dosf_status( $cert->emision ),
						'vdbe'          => Wp_Dosf_Admin::get_dosf_validity_days_before_expiration( $cert->emision ),
						'expire_warn_sent' => isset( $cert->expire_warn_sent ) ? intval( $cert->expire_warn_sent ) : 0,
						'expire_warn_failed' => isset( $cert->expire_warn_failed ) ? intval( $cert->expire_warn_failed ) : 0,
						'expire_warn_sent_at' => Wp_Dosf_Admin::expire_warn_sent_labels( isset( $cert->expire_warn_sent_at ) ? $cert->expire_warn_sent_at : '' ),
					);
				}

				$rows[] = array(
					'DT_RowId'     => 'serie-' . $serie->id,
					'id'           => $serie->id,
					'serie'        => $serie->serie,
					'linked_ruts'  => implode( ',', $ruts ),
					'certificates' => $cert_rows,
				);
			}
		}

		return array(
			'draw'            => $draw,
			'recordsTotal'    => $total,
			'recordsFiltered' => $total,
			'data'            => $rows,
		);
	}

	public static function search_public( $value, $by_serial, $exact ) {
		global $wpdb;

		$tables = self::table_names();
		$value  = trim( (string) $value );
		if ( $value === '' ) {
			return array();
		}

		if ( $by_serial ) {
			if ( $exact ) {
				$sql = $wpdb->prepare(
					"SELECT c.id, c.cert_title AS title, s.serie, c.emision, c.file_name, c.file_unavailable
					 FROM {$tables['certs']} c
					 INNER JOIN {$tables['series']} s ON s.id = c.serie_id
					 WHERE s.serie = %s
					 ORDER BY c.cert_title ASC",
					$value
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT c.id, c.cert_title AS title, s.serie, c.emision, c.file_name, c.file_unavailable
					 FROM {$tables['certs']} c
					 INNER JOIN {$tables['series']} s ON s.id = c.serie_id
					 WHERE s.serie LIKE %s
					 ORDER BY s.serie ASC, c.cert_title ASC",
					'%' . $wpdb->esc_like( $value ) . '%'
				);
			}
		} else {
			$rut = preg_replace( '/[.\-\s]/', '', $value );
			if ( $exact ) {
				$sql = $wpdb->prepare(
					"SELECT DISTINCT c.id, c.cert_title AS title, s.serie, c.emision, c.file_name, c.file_unavailable
					 FROM {$tables['ruts']} r
					 INNER JOIN {$tables['series']} s ON s.id = r.serie_id
					 INNER JOIN {$tables['certs']} c ON c.serie_id = s.id
					 WHERE r.rut = %s
					 ORDER BY c.cert_title ASC",
					$rut
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT DISTINCT c.id, c.cert_title AS title, s.serie, c.emision, c.file_name, c.file_unavailable
					 FROM {$tables['ruts']} r
					 INNER JOIN {$tables['series']} s ON s.id = r.serie_id
					 INNER JOIN {$tables['certs']} c ON c.serie_id = s.id
					 WHERE r.rut LIKE %s
					 ORDER BY c.cert_title ASC",
					'%' . $wpdb->esc_like( $rut ) . '%'
				);
			}
		}

		$results = $wpdb->get_results( $sql );
		return is_array( $results ) ? $results : array();
	}
}
