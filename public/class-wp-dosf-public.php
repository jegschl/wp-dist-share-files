<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://empdigital.cl
 * @since      1.0.0
 *
 * @package    Wp_Dosf
 * @subpackage Wp_Dosf/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Dosf
 * @subpackage Wp_Dosf/public
 * @author     Jorge Garrido <jegschl@gmail.com>
 */
class Wp_Dosf_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Dosf_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Dosf_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-dosf-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Dosf_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Dosf_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-dosf-public.js', array( 'jquery' ), $this->version, false );

	}

	public function sc_browser(){
		$rut = filter_input(INPUT_GET, 'dosf-search-rut');
		if( $rut !== false && !is_null($rut)){
			global $wpdb;
			$tbl_nm_shared_objs = $wpdb->prefix . 'dosf_shared_objs';
			$tbl_nm_so_ruts_links = $wpdb->prefix . 'dosf_so_ruts_links'; 

			$select = "SELECT title,wp_file_obj_id 
					   FROM $tbl_nm_shared_objs wdso
					   JOIN $tbl_nm_so_ruts_links wdsrl
					   	ON wdsrl.so_id = wdso.id 
					   WHERE wdsrl.rut LIKE \"%{rut}%\"";

			$sql = str_replace("{rut}",$rut,$select);
			$res = $wpdb->get_results($sql);
			?>
			<div id="dosf-browser-wrapper">
				<form method="get">
					<div class="input-text">
						<label>Introduce tu RUT (sin puntos ni guión)</label>
						<input type="text" name="dosf-search-rut" value="<?=$rut?>">
					</div>
					<input type="submit" value="Volver a buscar">
				</form>
			</div>
			<?php
			if(is_array($res) || count($res)>0){
				?>
				<div class="dosf-search-res-wrapper">
				<?php
				foreach($res as $i => $so){
					$wfo_id = $so->wp_file_obj_id;
					$hr = wp_get_attachment_url($wfo_id);
					?>
					<div class="dosf-search-res-row">
						<div class="link">
							<a href="<?= $hr ?>"><i class="fa-solid fa-download"></i></a>
							<span class="title"><?= $so->title ?></span>
						</div>	
					</div>
					<?php
				}
				?>
				</div>
				<?php
			} else {
				?>
				<div class="dosf-search-no-res-wrapper">
					Sin resultados
				</div>
				<?php
			}
			?>
			<div id="dosf-browser-wrapper">
				<form method="get">
					<div class="input-text">
						<label>Introduce tu RUT (sin puntos ni guión)</label>
						<input type="text" name="dosf-search-rut" value="<?=$rut?>">
					</div>
					<input type="submit" value="Volver a buscar">
				</form>
			</div>
			<?php
		} else {
			?>
			<div id="dosf-browser-wrapper">
				<form method="get">
					<div class="input-text">
						<label>Introduce tu RUT (sin puntos ni guión)</label>
						<input type="text" name="dosf-search-rut">
					</div>
					<input type="submit">
				</form>
			</div>
			<?php
		}
	}

}
