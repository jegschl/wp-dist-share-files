<?php
/**
 * El archivo de un certificado no puede quedar en una URL pública.
 * El servidor web entrega cualquier archivo que exista dentro del sitio
 * antes de que WordPress arranque, así que el PDF se guarda en la base
 * de datos y se borra de la carpeta pública. Solo se entrega después de
 * un código de descarga vigente.
 *
 * @package Wp_Dosf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dosf_Private_Files {

	const OPTION_READY = 'dosf_private_files_ok';
	const GRANT_TTL    = 120;
	const CHUNK_BYTES  = 1048576;

	private static $certificate_attachment_ids = null;
	private static $table_ready                = false;

	public function protect_linked_certificate_files() {
		if ( get_option( self::OPTION_READY ) === '2' ) {
			return;
		}
		if ( ! self::ensure_table() ) {
			return;
		}

		global $wpdb;

		$ids = $wpdb->get_col( "SELECT DISTINCT wp_file_obj_id FROM {$wpdb->prefix}dosf_shared_objs WHERE wp_file_obj_id > 0" );
		if ( ! is_array( $ids ) ) {
			return;
		}

		$ok = true;
		foreach ( $ids as $attachment_id ) {
			if ( ! self::protect_certificate_attachment( $attachment_id ) ) {
				$ok = false;
			}
		}

		if ( $ok ) {
			self::remove_legacy_public_dir();
			update_option( self::OPTION_READY, '2', false );
		}
	}

	public static function protect_certificate_attachment( $attachment_id ) {
		$attachment_id = intval( $attachment_id );
		$post          = get_post( $attachment_id );
		if ( ! $post || $post->post_type !== 'attachment' ) {
			return true;
		}
		if ( ! self::ensure_table() ) {
			return false;
		}

		self::$certificate_attachment_ids = null;

		$path = get_attached_file( $attachment_id );
		$path = is_string( $path ) ? $path : '';
		if ( self::has_private_bytes( $attachment_id ) && ( $path === '' || ! is_file( $path ) ) ) {
			return true;
		}
		if ( $path === '' || ! is_file( $path ) ) {
			return true;
		}

		$raw = file_get_contents( $path );
		if ( ! is_string( $raw ) || $raw === '' ) {
			return false;
		}

		$stored = self::store_bytes(
			$attachment_id,
			wp_basename( $path ),
			(string) get_post_mime_type( $attachment_id ),
			$raw
		);
		if ( ! $stored || self::read_private_bytes( $attachment_id ) !== $raw ) {
			return false;
		}

		self::delete_public_copies( $attachment_id, $path );
		return ! is_file( $path );
	}

	public static function issue_download_grant( $cert_id ) {
		$cert_id = intval( $cert_id );
		if ( $cert_id < 1 ) {
			return '';
		}

		$token = wp_generate_password( 32, false, false );
		set_transient( self::grant_key( $token ), $cert_id, self::GRANT_TTL );

		return rest_url( DOSF_APIREST_BASE_ROUTE . 'file/' . $token );
	}

	public function stream_granted_file( $request ) {
		$token = '';
		if ( is_object( $request ) && method_exists( $request, 'get_param' ) ) {
			$token = (string) $request->get_param( 'token' );
		}

		if ( ! preg_match( '/^[A-Za-z0-9]{20,64}$/', $token ) ) {
			return new WP_Error( 'dosf_forbidden', 'No autorizado', array( 'status' => 403 ) );
		}

		$key     = self::grant_key( $token );
		$cert_id = get_transient( $key );
		delete_transient( $key );
		$cert_id = intval( $cert_id );
		if ( $cert_id < 1 ) {
			return new WP_Error( 'dosf_forbidden', 'No autorizado', array( 'status' => 403 ) );
		}

		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT wp_file_obj_id, file_unavailable FROM {$wpdb->prefix}dosf_shared_objs WHERE id = %d",
				$cert_id
			)
		);
		if ( ! $row || ! empty( $row->file_unavailable ) ) {
			return new WP_Error( 'dosf_missing', 'Certificado no disponible', array( 'status' => 404 ) );
		}

		$attachment_id = intval( $row->wp_file_obj_id );
		if ( ! Wp_Dosf_Admin::attachment_is_downloadable( $attachment_id ) ) {
			return new WP_Error( 'dosf_missing', 'Certificado no disponible', array( 'status' => 404 ) );
		}

		self::send_attachment_file( $attachment_id );
		exit;
	}

	public function ajax_private_file() {
		$attachment_id = isset( $_GET['attachment_id'] ) ? intval( $_GET['attachment_id'] ) : 0;
		$nonce         = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( $attachment_id < 1 || ! self::user_can_read_private_files() ) {
			wp_die( esc_html( 'No autorizado' ), 403 );
		}
		if ( ! wp_verify_nonce( $nonce, 'dosf_private_file_' . $attachment_id ) ) {
			wp_die( esc_html( 'No autorizado' ), 403 );
		}
		if ( ! self::attachment_is_certificate( $attachment_id ) ) {
			wp_die( esc_html( 'No autorizado' ), 403 );
		}

		self::send_attachment_file( $attachment_id );
	}

	public function filter_private_attachment_url( $url, $post_id ) {
		if ( ! is_admin() ) {
			return $url;
		}

		$post_id = intval( $post_id );
		if ( ! self::user_can_read_private_files() || ! self::attachment_is_certificate( $post_id ) || ! self::attachment_is_private( $post_id ) ) {
			return $url;
		}

		return wp_nonce_url(
			admin_url( 'admin-ajax.php?action=dosf_private_file&attachment_id=' . $post_id ),
			'dosf_private_file_' . $post_id
		);
	}

	public function block_certificate_attachment_page() {
		if ( ! is_attachment() ) {
			return;
		}

		$attachment_id = intval( get_queried_object_id() );
		if ( ! self::attachment_is_certificate( $attachment_id ) ) {
			return;
		}

		status_header( 404 );
		nocache_headers();
		exit;
	}

	public static function send_attachment_file( $attachment_id ) {
		$attachment_id = intval( $attachment_id );
		$stored        = self::open_private_stream( $attachment_id );
		if ( is_array( $stored ) ) {
			self::emit_stream( $stored['filename'], $stored['mime'], $stored['stream'], $stored['length'] );
		}

		$path = get_attached_file( $attachment_id );
		if ( ! $path || ! is_readable( $path ) ) {
			status_header( 404 );
			exit;
		}

		$mime = get_post_mime_type( $attachment_id );
		if ( ! is_string( $mime ) || $mime === '' ) {
			$mime = 'application/octet-stream';
		}
		$stream = fopen( $path, 'rb' );
		if ( ! $stream ) {
			status_header( 404 );
			exit;
		}
		self::emit_stream( wp_basename( $path ), $mime, $stream, filesize( $path ) );
	}

	private static function user_can_read_private_files() {
		return current_user_can( 'upload_files' ) || current_user_can( 'jgb_collab_access' );
	}

	private static function grant_key( $token ) {
		return 'dosf_file_grant_' . $token;
	}

	private static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'dosf_private_files';
	}

	private static function ensure_table() {
		if ( self::$table_ready ) {
			return self::ensure_restored_column();
		}

		global $wpdb;

		$table = self::table_name();
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $found !== $table ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$charset = $wpdb->get_charset_collate();
			$sql     = "CREATE TABLE {$table} (
				attachment_id bigint(20) unsigned NOT NULL,
				seq smallint unsigned NOT NULL,
				filename varchar(255) NOT NULL,
				mime varchar(100) NOT NULL DEFAULT '',
				byte_length int unsigned NOT NULL DEFAULT 0,
				chunk_b64 longtext NOT NULL,
				restored tinyint(1) NOT NULL DEFAULT 0,
				PRIMARY KEY  (attachment_id, seq)
			) {$charset};";
			dbDelta( $sql );
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		}

		self::$table_ready = ( $found === $table );
		return self::$table_ready && self::ensure_restored_column();
	}

	private static function attachment_is_private( $attachment_id ) {
		return self::has_private_bytes( $attachment_id );
	}

	private static function has_private_bytes( $attachment_id ) {
		if ( ! self::ensure_table() ) {
			return false;
		}

		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::table_name() . ' WHERE attachment_id = %d',
				intval( $attachment_id )
			)
		);
		return intval( $count ) > 0;
	}

	private static function store_bytes( $attachment_id, $filename, $mime, $raw ) {
		global $wpdb;

		$table         = self::table_name();
		$attachment_id = intval( $attachment_id );
		$filename      = str_replace( array( '"', '\\', "\r", "\n" ), '', (string) $filename );
		$mime          = $mime !== '' ? $mime : 'application/octet-stream';
		$parts         = str_split( $raw, self::CHUNK_BYTES );
		if ( ! is_array( $parts ) || ! count( $parts ) ) {
			return false;
		}

		$kept_restored = 0;
		if ( self::ensure_restored_column() ) {
			$kept_restored = intval( $wpdb->get_var( $wpdb->prepare( 'SELECT MAX(restored) FROM ' . self::table_name() . ' WHERE attachment_id = %d', $attachment_id ) ) );
		}

		$wpdb->query( 'START TRANSACTION' );
		$wpdb->delete( $table, array( 'attachment_id' => $attachment_id ), array( '%d' ) );

		foreach ( $parts as $seq => $part ) {
			$inserted = $wpdb->insert(
				$table,
				array(
					'attachment_id' => $attachment_id,
					'seq'           => intval( $seq ),
					'filename'      => $filename,
					'mime'          => $mime,
					'byte_length'   => strlen( $part ),
					'chunk_b64'     => base64_encode( $part ),
					'restored'      => $kept_restored,
				),
				array( '%d', '%d', '%s', '%s', '%d', '%s', '%d' )
			);
			if ( ! $inserted ) {
				$wpdb->query( 'ROLLBACK' );
				return false;
			}
		}

		$wpdb->query( 'COMMIT' );
		return true;
	}

	private static function read_private_bytes( $attachment_id ) {
		$stream = self::open_private_stream( $attachment_id );
		if ( ! is_array( $stream ) ) {
			return '';
		}

		$raw = stream_get_contents( $stream['stream'] );
		fclose( $stream['stream'] );
		return is_string( $raw ) ? $raw : '';
	}

	private static function open_private_stream( $attachment_id ) {
		if ( ! self::ensure_table() ) {
			return false;
		}

		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT filename, mime, byte_length, chunk_b64 FROM ' . self::table_name() . ' WHERE attachment_id = %d ORDER BY seq ASC',
				intval( $attachment_id )
			)
		);
		if ( ! is_array( $rows ) || ! count( $rows ) ) {
			return false;
		}

		$stream = fopen( 'php://temp', 'w+b' );
		if ( ! $stream ) {
			return false;
		}

		$length = 0;
		foreach ( $rows as $row ) {
			$bin = base64_decode( $row->chunk_b64, true );
			if ( ! is_string( $bin ) || strlen( $bin ) !== intval( $row->byte_length ) ) {
				fclose( $stream );
				return false;
			}
			fwrite( $stream, $bin );
			$length += strlen( $bin );
		}
		rewind( $stream );

		$mime = (string) $rows[0]->mime;
		if ( $mime === '' ) {
			$mime = 'application/octet-stream';
		}

		return array(
			'filename' => (string) $rows[0]->filename,
			'mime'     => $mime,
			'stream'   => $stream,
			'length'   => $length,
		);
	}

	private static function emit_stream( $filename, $mime, $stream, $length ) {
		$name = str_replace( array( '"', '\\', "\r", "\n" ), '', (string) $filename );
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: ' . $mime );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );
		header( 'Content-Length: ' . (string) intval( $length ) );
		header( 'X-Robots-Tag: noindex, nofollow' );
		fpassthru( $stream );
		fclose( $stream );
		exit;
	}

	private static function delete_public_copies( $attachment_id, $main_path ) {
		$main_path = wp_normalize_path( $main_path );
		$uploads   = wp_upload_dir();
		$basedir   = wp_normalize_path( trailingslashit( $uploads['basedir'] ) );
		if ( strpos( $main_path, $basedir ) !== 0 ) {
			return;
		}

		$dir   = wp_normalize_path( dirname( $main_path ) );
		$names = array( wp_basename( $main_path ) );
		$meta  = wp_get_attachment_metadata( $attachment_id );
		if ( is_array( $meta ) ) {
			if ( ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $data ) {
					if ( ! empty( $data['file'] ) ) {
						$names[] = wp_basename( $data['file'] );
					}
				}
			}
			if ( ! empty( $meta['original_image'] ) ) {
				$names[] = wp_basename( $meta['original_image'] );
			}
		}

		foreach ( array_unique( $names ) as $name ) {
			$file = $dir . '/' . $name;
			if ( is_file( $file ) ) {
				@unlink( $file );
			}
		}
	}

	private static function remove_legacy_public_dir() {
		$uploads = wp_upload_dir();
		$dir     = wp_normalize_path( trailingslashit( $uploads['basedir'] ) . 'dosf-private' );
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$pending = glob( $dir . '/*/*' );
		if ( is_array( $pending ) ) {
			foreach ( $pending as $file ) {
				if ( is_file( $file ) ) {
					return;
				}
			}
		}

		self::rmdir_tree( $dir );
	}

	private static function rmdir_tree( $dir ) {
		$items = scandir( $dir );
		if ( ! is_array( $items ) ) {
			return;
		}
		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}
			$path = $dir . '/' . $item;
			if ( is_dir( $path ) ) {
				self::rmdir_tree( $path );
			} else {
				@unlink( $path );
			}
		}
		@rmdir( $dir );
	}

	private static function attachment_is_certificate( $attachment_id ) {
		$map = self::certificate_attachment_ids();
		return isset( $map[ intval( $attachment_id ) ] );
	}

	private static function certificate_attachment_ids() {
		if ( is_array( self::$certificate_attachment_ids ) ) {
			return self::$certificate_attachment_ids;
		}

		global $wpdb;

		$ids = $wpdb->get_col( "SELECT DISTINCT wp_file_obj_id FROM {$wpdb->prefix}dosf_shared_objs WHERE wp_file_obj_id > 0" );
		$map = array();
		if ( is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				$map[ intval( $id ) ] = true;
			}
		}

		self::$certificate_attachment_ids = $map;
		return $map;
	}
	public static $suppress_public_restore = false;
	private static $restored_column_ready = false;

	public static function attachment_has_bytes( $attachment_id ) {
		return self::has_private_bytes( $attachment_id );
	}

	public static function is_restored( $attachment_id ) {
		if ( ! self::ensure_table() ) {
			return false;
		}

		global $wpdb;

		$value = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT MAX(restored) FROM ' . self::table_name() . ' WHERE attachment_id = %d',
				intval( $attachment_id )
			)
		);
		return intval( $value ) === 1;
	}

	public static function admin_download_url( $attachment_id ) {
		$attachment_id = intval( $attachment_id );
		return wp_nonce_url(
			admin_url( 'admin-ajax.php?action=dosf_private_file&attachment_id=' . $attachment_id ),
			'dosf_private_file_' . $attachment_id
		);
	}

	public static function expired_certificate_rows() {
		if ( ! self::ensure_table() ) {
			return array();
		}

		global $wpdb;

		$certs = $wpdb->prefix . 'dosf_shared_objs';
		$files = self::table_name();
		$rows  = $wpdb->get_results(
			"SELECT c.id, c.cert_title, c.title AS serie, c.emision, c.wp_file_obj_id,
				MAX(f.filename) AS filename, MAX(f.restored) AS restored
			FROM {$certs} c
			INNER JOIN {$files} f ON f.attachment_id = c.wp_file_obj_id
			GROUP BY c.id, c.cert_title, c.title, c.emision, c.wp_file_obj_id"
		);
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			if ( strtolower( (string) Wp_Dosf_Admin::get_dosf_status( $row->emision ) ) !== 'vencido' ) {
				continue;
			}
			$attachment_id = intval( $row->wp_file_obj_id );
			$out[]         = array(
				'cert_id'        => intval( $row->id ),
				'title'          => (string) $row->cert_title,
				'serie'          => (string) $row->serie,
				'attachment_id'  => $attachment_id,
				'filename'       => (string) $row->filename,
				'restored'       => intval( $row->restored ) === 1,
				'delete_blocked' => self::attachment_used_by_current_cert( $attachment_id ),
			);
		}
		return $out;
	}

	public static function restored_catalog() {
		if ( ! self::ensure_table() ) {
			return array();
		}

		global $wpdb;

		$certs = $wpdb->prefix . 'dosf_shared_objs';
		$files = self::table_name();
		$rows  = $wpdb->get_results( "SELECT attachment_id, MAX(filename) AS filename FROM {$files} WHERE restored = 1 GROUP BY attachment_id" );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$attachment_id = intval( $row->attachment_id );
			$names         = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT cert_title, title FROM {$certs} WHERE wp_file_obj_id = %d",
					$attachment_id
				)
			);
			$bits = array();
			if ( is_array( $names ) ) {
				foreach ( $names as $name ) {
					$bits[] = trim( (string) $name->title . ' — ' . (string) $name->cert_title );
				}
			}
			$label = (string) $row->filename;
			if ( $bits ) {
				$label .= ' (' . implode( ', ', $bits ) . ')';
			}
			$out[] = array(
				'id'       => $attachment_id,
				'filename' => (string) $row->filename,
				'label'    => $label,
			);
		}
		return $out;
	}

	public static function restore_for_catalog( $attachment_id ) {
		$attachment_id = intval( $attachment_id );
		if ( ! self::has_private_bytes( $attachment_id ) ) {
			return false;
		}

		global $wpdb;

		$updated = $wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . self::table_name() . ' SET restored = 1 WHERE attachment_id = %d',
				$attachment_id
			)
		);
		return $updated !== false;
	}

	public static function delete_stored_file( $attachment_id ) {
		$attachment_id = intval( $attachment_id );
		if ( $attachment_id < 1 || ! self::has_private_bytes( $attachment_id ) ) {
			return false;
		}
		if ( self::attachment_used_by_current_cert( $attachment_id ) ) {
			return false;
		}

		global $wpdb;

		$wpdb->delete( self::table_name(), array( 'attachment_id' => $attachment_id ), array( '%d' ) );
		if ( self::has_private_bytes( $attachment_id ) ) {
			return false;
		}
		Wp_Dosf_Admin::mark_certificates_file_unavailable( $attachment_id );
		return true;
	}

	public function ajax_expired_file() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'No autorizado' ), 403 );
		}
		check_ajax_referer( 'dosf_expired_file' );

		$op            = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';
		$attachment_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;
		if ( $attachment_id < 1 || ( $op !== 'restore' && $op !== 'delete' ) ) {
			wp_send_json_error( array( 'message' => 'Solicitud no válida' ), 400 );
		}

		if ( $op === 'restore' ) {
			$ok      = self::restore_for_catalog( $attachment_id );
			$message = 'No se pudo restaurar el archivo.';
		} else {
			$ok      = self::delete_stored_file( $attachment_id );
			$message = 'No se puede eliminar: hay un certificado vigente que usa este archivo.';
		}
		if ( ! $ok ) {
			wp_send_json_error( array( 'message' => $message ), 400 );
		}

		wp_send_json_success( array( 'catalog' => self::restored_catalog() ) );
	}

	private static function attachment_used_by_current_cert( $attachment_id ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT emision FROM {$wpdb->prefix}dosf_shared_objs WHERE wp_file_obj_id = %d",
				intval( $attachment_id )
			)
		);
		if ( ! is_array( $rows ) ) {
			return false;
		}
		foreach ( $rows as $row ) {
			if ( strtolower( (string) Wp_Dosf_Admin::get_dosf_status( $row->emision ) ) !== 'vencido' ) {
				return true;
			}
		}
		return false;
	}

	private static function ensure_restored_column() {
		if ( self::$restored_column_ready ) {
			return true;
		}

		global $wpdb;

		$table = self::table_name();
		$found = $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE 'restored'" );
		if ( empty( $found ) ) {
			$altered = $wpdb->query( "ALTER TABLE {$table} ADD COLUMN restored tinyint(1) NOT NULL DEFAULT 0" );
			if ( $altered === false ) {
				return false;
			}
		}
		self::$restored_column_ready = true;
		return true;
	}

}
