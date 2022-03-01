<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://empdigital.cl
 * @since      1.0.0
 *
 * @package    Wp_Dosf
 * @subpackage Wp_Dosf/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Dosf
 * @subpackage Wp_Dosf/admin
 * @author     Jorge Garrido <jegschl@gmail.com>
 */
class Wp_Dosf_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-dosf-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-dosf-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**** Creando una pgina en el admin para configurar las imgenes de los ads y los links de cada una ****/
	//add_action( 'admin_menu', 'dosf_menu' );

	public function dosf_menu() {
		add_menu_page( 
			'Distribución de archivos', 
			'Distribución de archivos', 
			'manage_options', 
			'dosf-admin',  //'dosf/dosf-admin.php', 
			array($this,'dosf_admin_page'), 
			'dashicons-forms', 
			11
		);
	}

	/* renderiza el contenido de la pgina de configuracin de ads-imgs */
	public function dosf_admin_page(){
		?>
		<form action='options.php' method='post'>

			<h2>Compartir o distribuir archivos</h2>

			<?php
			settings_fields( 'dosf' );
			do_settings_sections( 'dosf' );
			submit_button();
			?>

		</form>
		<?php
	}


	public function dosf_update_option( $data ) {
		
		$i=0;
		$data = array();
		for($i = 0; $i < count($_POST['img_url']); $i++){
			$data[] = array(
				'img_url' => $_POST['img_url'][$i],
				'ads_url' => $_POST['ads_url'][$i]
			);
		}
		return $data;
	}

	//add_action( 'admin_init', 'dosf_settings_init' );
	public function dosf_settings_init(){
		
		register_setting( 
			'dosf', 
			'dosf_settings', 
			array(
				'sanitize_callback' => array(
					$this,
					'dosf_update_option'
				)
			) 
		);
		
		add_settings_section(
			'dosf_section',
			'Indicar archivos de la librería para compartir o distribuir',
			array($this,'dosf_settings_section_header'),
			'dosf'
		);
		
		add_settings_field(
			'file_data_set',
			'',
			array($this,'file_data_set_stack_field_render'),
			'dosf',
			'dosf_section'
		);
	}

	public function dosf_settings_section_header(){
		echo __( 'Datos para compartir o distribuir archivos', 'wp-dosf' );
	}

	public function file_data_set_stack_field_render(){
		?>

		<div class="ads-img-lnks-wrapper">
			<table>
				<thead>
					<tr>
						<td>Seleccionar</td>
						<td class="desc-wrapper">
							<div class="title-wrapper">Archivo</div>
							<div id="addPil" class="action-wrapper"><i class="fas fa-plus-circle"></i></div>
							<div id="remPil" class="action-wrapper"><i class="fas fa-minus-circle"></i></div>
						</td>
						<td>
							Acciones
						</td>
					</tr>

				</thead>
				<tbody id="rows_pils">
					<?php
						$pils = get_option('dosf_settings',true);
						if(!empty($pils)) {
							$i = 0;
							foreach($pils as $pil){
								?>
					<tr>
						<td>
							<input type="checkbox" name="pil_selection">
						</td>
						<td>
							<div class="img_url_fld_wrapper">
								<label for="img_url[<?php echo $i; ?>]">Url de la Imagen</label>
								<input type="text" name="img_url[<?php echo $i; ?>]" value="<?php echo $pil['img_url']; ?>">
							</div>
							<div class="ads_url_fld_wrapper">
								<label for="ads_url[<?php echo $i; ?>]">Url del Ads </label>
								<input type="text" name="ads_url[<?php echo $i; ?>]" value="<?php echo $pil['ads_url']; ?>">
							</div>
						</td>
						<td class="col-action-wrapper"><div class="action-wrapper remCurrentPil"><i class="fas fa-minus-circle"></i></div></td>
					</tr>
								<?php
								$i++;
							}
						}
					?>
					
				</tbody>
			</table>
		</div>
		<?php
	}

}
