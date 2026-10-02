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

		return true;
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

	public static function generate_code( $length = 6 ) {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$string     = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$string .= $characters[ mt_rand( 0, strlen( $characters ) - 1 ) ];
		}
		return $string;
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

		$row = array(
			'title'          => $serie,
			'cert_title'     => $cert_title,
			'serie_id'       => $serie_id,
			'file_name'      => isset( $data['file_name'] ) ? $data['file_name'] : '',
			'wp_file_obj_id' => isset( $data['wp_obj_file_id'] ) ? intval( $data['wp_obj_file_id'] ) : 0,
			'email'          => self::email_list( $data, 'email' ),
			'email2'         => self::email_list( $data, 'email2' ),
			'email3'         => self::email_list( $data, 'email3' ),
			'emision'        => isset( $data['emision'] ) ? $data['emision'] : null,
		);

		if ( $update_id ) {
			$wpdb->update( $tables['certs'], $row, array( 'id' => $update_id ) );
			self::sync_ruts( $serie_id, isset( $data['linked_ruts'] ) ? $data['linked_ruts'] : array() );
			return array(
				'error'     => false,
				'operation' => 'UPDATE',
				'id'        => $update_id,
			);
		}

		$row['download_code'] = self::generate_code();
		$wpdb->insert( $tables['certs'], $row );
		$id = intval( $wpdb->insert_id );
		self::sync_ruts( $serie_id, isset( $data['linked_ruts'] ) ? $data['linked_ruts'] : array() );

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
				'(s.serie LIKE %s OR c.cert_title LIKE %s OR c.file_name LIKE %s OR c.email LIKE %s OR c.email2 LIKE %s OR c.email3 LIKE %s OR r.rut LIKE %s)',
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
						'attachment_id' => $cert->wp_file_obj_id,
						'email'         => $cert->email,
						'email2'        => $cert->email2,
						'email3'        => $cert->email3,
						'status'        => Wp_Dosf_Admin::get_dosf_status( $cert->emision ),
						'vdbe'          => Wp_Dosf_Admin::get_dosf_validity_days_before_expiration( $cert->emision ),
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
					"SELECT c.id, c.cert_title AS title, s.serie, c.emision, c.file_name
					 FROM {$tables['certs']} c
					 INNER JOIN {$tables['series']} s ON s.id = c.serie_id
					 WHERE s.serie = %s
					 ORDER BY c.cert_title ASC",
					$value
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT c.id, c.cert_title AS title, s.serie, c.emision, c.file_name
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
					"SELECT DISTINCT c.id, c.cert_title AS title, s.serie, c.emision, c.file_name
					 FROM {$tables['ruts']} r
					 INNER JOIN {$tables['series']} s ON s.id = r.serie_id
					 INNER JOIN {$tables['certs']} c ON c.serie_id = s.id
					 WHERE r.rut = %s
					 ORDER BY c.cert_title ASC",
					$rut
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT DISTINCT c.id, c.cert_title AS title, s.serie, c.emision, c.file_name
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
