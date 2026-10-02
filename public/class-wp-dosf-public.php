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

 define('DOSF_URI_GET_OBJ_URL','get-dosf-url');

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

	private $plus_options;

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
		$this->plus_options = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
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

		$img_vigente = apply_filters(
			'dosf/front/status-indicator/vigente/img-url',
			plugin_dir_url( __FILE__ ) . '../assets/imgs/hand-good.png'
		);
		$img_vencido = apply_filters(
			'dosf/front/status-indicator/vencido/img-url',
			plugin_dir_url( __FILE__ ) . '../assets/imgs/hand-not-good.png'
		);
		$inline_css = ".dosf-search-res-row .indicator.vigente { background-image: url('{$img_vigente}'); }
.dosf-search-res-row .indicator.vencido  { background-image: url('{$img_vencido}'); }";
		wp_add_inline_style( $this->plugin_name, $inline_css );

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

		$public_js = plugin_dir_path( __FILE__ ) . 'js/wp-dosf-public.js';
		$public_js_ver = $this->version . '.' . ( file_exists( $public_js ) ? filemtime( $public_js ) : '0' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-dosf-public.js', array( 'jquery' ), $public_js_ver, false );
		
		$dosfData = array();
		$useSerialNmbCriterial = ( isset($this->plus_options['use-serial-number']) && $this->plus_options['use-serial-number'] );
		$dosfData['pmDldCodeId'] = apply_filters(
									'dosf/front/search-rut/download-code/popupmakerId',
									( !empty($this->plus_options['pm-download-code-popup-id']) ? intval($this->plus_options['pm-download-code-popup-id']) : 11927 )
								);
		$dosfData['searchFldNm'] = $useSerialNmbCriterial ? 'dosf-search-serial' : 'dosf-search-rut';
		$dosfData['urlGetDosfURL'] = rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_GET_OBJ_URL . '/' );
		$dosfData['urlSndDCReq']   = rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_SEND_DWNL_CD . '/' );
		wp_localize_script(
			$this->plugin_name,
			'dosfDt',
			$dosfData
		);
	}

	public function set_public_endpoints(){
		register_rest_route(
            DOSF_APIREST_BASE_ROUTE,
            DOSF_URI_GET_OBJ_URL.'/',
            array(
                'methods'  => 'POST',
                'callback' => array(
                    $this,
                    'receive_object_download_code'
                ),
                'permission_callback' => '__return_true',
            )
        );
	}

	public function receive_object_download_code($r){
		global $wpdb;
		$data  = $r->get_json_params();
		$objid = ( is_array( $data ) && isset( $data['objid'] ) ) ? intval( $data['objid'] ) : 0;
		$code  = ( is_array( $data ) && isset( $data['dldcd'] ) ) ? strtoupper( trim( (string) $data['dldcd'] ) ) : '';

		if ( ! $objid || $code === '' ) {
			return array(
				'error'   => true,
				'message' => 'Código de descarga no válido',
			);
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, wp_file_obj_id, download_code FROM {$wpdb->prefix}dosf_shared_objs WHERE id = %d",
				$objid
			)
		);

		$stored = $row ? strtoupper( trim( (string) $row->download_code ) ) : '';
		if ( ! $row || $stored === '' || $stored !== $code ) {
			return array(
				'error'   => true,
				'message' => 'Código de descarga no válido',
			);
		}

		$url = wp_get_attachment_url( intval( $row->wp_file_obj_id ) );
		if ( empty( $url ) ) {
			return array(
				'error'   => true,
				'message' => 'Certificado no disponible',
			);
		}

		return array(
			'error'         => false,
			'download-link' => $url,
		);
	}

	public function sc_browser(){
		ob_start();
		$browse_input_label = 'Introduce tu RUT (sin puntos ni guión';
		$no_results_label = 'Sin resultados';
		$urlGetParamNm = 'dosf-search-rut';
		$useSerialNmbCriterial = false;
		if( $useSerialNmbCriterial = ( isset($this->plus_options['use-serial-number']) && $this->plus_options['use-serial-number'] ) ){
			$urlGetParamNm = 'dosf-search-serial';
			$browse_input_label = 'Ingrese número de serie';
		}
		$valueToSearch = filter_input(INPUT_GET, $urlGetParamNm );
		if( $valueToSearch !== false && !is_null($valueToSearch)){
			$exact = ! empty( $this->plus_options['frontend-specific-match-search'] );
			$res = Dosf_Series::search_public( $valueToSearch, $useSerialNmbCriterial, $exact );
			?>
			
			<div id="dosf-browser-wrapper">
				<form method="get">
					<div class="input-text">
						<label><?= $browse_input_label?></label>
						<input type="text" name="<?= $urlGetParamNm  ?>" value="<?=$valueToSearch?>">
					</div>
					<input type="submit" value="Volver a buscar">
				</form>
			</div>
			<?php
			if(is_array($res) && count($res)>0){
				$wfo_id = $res[0]->id;
				$hr = "/objid/" . $wfo_id;
				?>
				<div class="dosf-search-res-wrapper">
				<?php
				if( $this->plus_options['frontend-specific-match-search'] && $useSerialNmbCriterial ){
					foreach ( $res as $cert ) {
						$status = strtolower( Wp_Dosf_Admin::get_dosf_status( $cert->emision ) );
						$hr = '/objid/' . $cert->id;
						$status_message = Wp_Dosf_Admin::frontend_status_message( $this->plus_options, $cert->title, $cert->serie, $status );
						?>
						<div class="dosf-search-res-row">
							<div class="msg-indicator"><?= $status_message ?></div>
							<div class="indicator <?= esc_attr( $status ) ?>"></div>
							<div class="details">
								<div class="link">
									<a href="<?= esc_attr( $hr ) ?>"><span class="dosf-icon-download"></span></a>
									<span class="title">Descargar <?= esc_html( $cert->title ) ?></span>
								</div>
							</div>
						</div>
						<?php
					}

				} else {

					foreach($res as $i => $so){
						$wfo_id = $so->id;
						$hr = "/objid/" . $wfo_id;
						?>
						<div class="dosf-search-res-row">
							<div class="link">
								<a href="<?= $hr ?>"><span class="dosf-icon-download"></span></a>
								<span class="title"><?= $so->title ?></span>
							</div>	
						</div>
						<?php
					}
				}
				?>
				</div>
				<?php
			} else {
				?>
				<div class="dosf-search-no-res-wrapper">
					<?= $no_results_label ?>
				</div>
				<?php
			}
			?>
			<?php if ( is_array( $res ) && count( $res ) > 0 ) : ?>
			<div id="dosf-browser-wrapper">
				<form method="get">
					<div class="input-text">
						<label><?= $browse_input_label?></label>
						<input type="text" name="<?= $urlGetParamNm  ?>" value="<?=$valueToSearch?>">
					</div>
					<input type="submit" value="Volver a buscar">
				</form>
			</div>
			<?php endif; ?>
			<?php
		} else {
			?>
			<div id="dosf-browser-wrapper">
				<form method="get">
					<div class="input-text">
						<label><?= $browse_input_label?></label>
						<input type="text" name="<?= $urlGetParamNm  ?>">
					</div>
					<input type="submit">
				</form>
			</div>
			<?php
		}

		return ob_get_clean();
	}

}
