<?php
/**
 * Fired during plugin activation
 *
 * @link       https://empdigital.cl
 * @since      1.0.0
 *
 * @package    Wp_Dosf
 * @subpackage Wp_Dosf/includes
 */

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Dosf
 * @subpackage Wp_Dosf/includes
 * @author     Jorge Garrido <jegschl@gmail.com>
 */
class Wp_Dosf_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$tables_initializeds = get_option('jgb-dosf_tables_initialized',false);
		if(!$tables_initializeds){
			Wp_Dosf_Activator::initialize_tables();
			$tables_initializeds = true;
			update_option('jgb-dosf_tables_initialized',$tables_initializeds,true);
		}
	}

	public static function initialize_tables(){
		global $wpdb;
		$tbl_nm_shared_objs = $wpdb->prefix . 'dosf_shared_objs';
		$tbl_nm_so_ruts_links = $wpdb->prefix . 'dosf_so_ruts_links';
		$tbl_nm_so_ewmq = $wpdb->prefix . 'dosf_expiration_warnig_email_queue';

		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $tbl_nm_shared_objs (
			id int unsigned NOT NULL AUTO_INCREMENT,
			title varchar(256) NOT NULL,
			emision datetime DEFAULT NULL,
			file_name varchar(256) NOT NULL,
			wp_file_obj_id int unsigned NULL,
			created timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
			email varchar(255) NOT NULL,
			email2 varchar(255) NOT NULL,
			email3 varchar(255) NOT NULL DEFAULT '',
			download_code varchar(16) NOT NULL,
			PRIMARY KEY  (id),
			KEY title_idx (title(191),id),
			KEY file_name_idx (file_name(191),id)
		) $charset_collate;
		CREATE TABLE $tbl_nm_so_ruts_links (
			id int unsigned NOT NULL AUTO_INCREMENT,
			so_id int unsigned NOT NULL,
			rut varchar(13) NOT NULL,
			created timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY soId_idx (so_id,id,rut)
		) $charset_collate;
		CREATE TABLE $tbl_nm_so_ewmq (
			id int unsigned NOT NULL AUTO_INCREMENT,
			so_id int NOT NULL,
			PRIMARY KEY  (id),
			KEY soId_idx (so_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

}
