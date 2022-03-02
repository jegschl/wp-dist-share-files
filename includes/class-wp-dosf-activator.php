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
		$charset_collate = $wpdb->get_charset_collate();
		$isql_initialize_tables = "CREATE TABLE IF NOT EXISTS $tbl_nm_shared_objs (
			id INT UNSIGNED NOT NULL,
			title varchar(256) NOT NULL,
			file_name varchar(256) NOT NULL,
			wp_file_obj_id INT UNSIGNED NULL,
			created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
		) $charset_collate";
		$wpdb->query( $isql_initialize_tables );

		$isql_initialize_tables = "CREATE UNIQUE INDEX ".$tbl_nm_shared_objs."_id_IDX USING BTREE ON $tbl_nm_shared_objs (id)";
		$wpdb->query( $isql_initialize_tables );

		$isql_initialize_tables = "CREATE INDEX ".$tbl_nm_shared_objs."_title_IDX USING BTREE ON $tbl_nm_shared_objs (title,id)";
		$wpdb->query( $isql_initialize_tables );

		$isql_initialize_tables = "CREATE INDEX ".$tbl_nm_shared_objs."_file_name_IDX USING BTREE ON $tbl_nm_shared_objs (file_name,id)";
		$wpdb->query( $isql_initialize_tables );

		$isql_initialize_tables = "ALTER TABLE $tbl_nm_shared_objs MODIFY COLUMN id int unsigned auto_increment NOT NULL";
		$wpdb->query( $isql_initialize_tables );



		$isql_initialize_tables = "CREATE TABLE IF NOT EXISTS $tbl_nm_so_ruts_links (
			id INT UNSIGNED NOT NULL,
			so_id INT UNSIGNED NOT NULL,
			rut varchar(13) NOT NULL,
			created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
		) $charset_collate";
		$wpdb->query( $isql_initialize_tables );

		$isql_initialize_tables = "CREATE UNIQUE INDEX ".$tbl_nm_so_ruts_links."_id_IDX USING BTREE ON $tbl_nm_so_ruts_links (id)";
		$wpdb->query( $isql_initialize_tables );

		$isql_initialize_tables = "CREATE INDEX ".$tbl_nm_so_ruts_links."_soId_IDX USING BTREE ON $tbl_nm_so_ruts_links (so_id,id,rut)";
		$wpdb->query( $isql_initialize_tables );

		$isql_initialize_tables = "ALTER TABLE $tbl_nm_so_ruts_links MODIFY COLUMN id int unsigned auto_increment NOT NULL";
		$wpdb->query( $isql_initialize_tables );
	}

}
