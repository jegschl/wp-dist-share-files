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

 define('HTML_DOSF_ID','dosf-data-tbl');
 define('DOSF_APIREST_BASE_ROUTE','dosf/');
 define('DOSF_URI_ID_GET','list');
 define('DOSF_URI_ID_ADD_SO','add');
 define('DOSF_URI_ID_UPD_SO','update');
 define('DOSF_URI_ID_REM_SO','rem');
 define('DOSF_URI_ID_UPD_PLUS_OPTIONS','set_plus_options_settings');
 define('DOSF_URI_ID_SEND_DWNL_CD','send-download-code');
 define('DOSF_URI_ID_SEND_EXPRT_WRNG','send-expiration-warning');

 define('DOSF_NONCE_ACTION_PLUS_OPTS_UPDATE','update-plus-options');
 
 define('DOSF_PLUS_OPTS_UPDATE_ERR_INVALID_NONCE'	,1);
 define('DOSF_PLUS_OPTS_UPDATE_ERR_DB'				,2);
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

	private $plus_options;

	private $dosf_identifier_label;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name 				 = $plugin_name;
		$this->version 					 = $version;
		$this->plus_options 			 = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		$this->dosf_identifier_label 	 = self::get_dosf_identifier_label( $this->plus_options );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		if( $hook != "toplevel_page_dosf-admin" )
			return;

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

		$admin_css     = plugin_dir_path( __FILE__ ) . 'css/wp-dosf-admin.css';
		$admin_css_ver = $this->version . '.' . ( file_exists( $admin_css ) ? filemtime( $admin_css ) : '0' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-dosf-admin.css', array(), $admin_css_ver, 'all' );
		
		wp_enqueue_style( 
			'dosf_jquery_dtp_css', 
			plugin_dir_url( __FILE__ ) . 'js/libs/datetimepicker-master/build/jquery.datetimepicker.min.css', 
			array(),
			null,
			'all'
		);

		/* wp_enqueue_style( 
			'dosf_choices_base_css', 
			plugin_dir_url( __FILE__ ) . 'js/libs/choices-master/public/assets/styles/base.min.css', 
			array(),
			null,
			'all'
		); */

		wp_enqueue_style( 
			'dosf_choices_css', 
			plugin_dir_url( __FILE__ ) . 'js/libs/choices-master/public/assets/styles/choices.min.css', 
			array(),
			null,
			'all'
		);

		wp_enqueue_style(
			'dosf_font_awesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css',
			[],
			null,
			'all'
		);

		wp_enqueue_style(
			'dosf_jquery_ui_css',
			'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css',
			[],
			null,
			'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

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

		if( $hook != "toplevel_page_dosf-admin" )
			return;

		$admin_js = plugin_dir_path( __FILE__ ) . 'js/wp-dosf-admin.js';
		$admin_js_ver = $this->version . '.' . ( file_exists( $admin_js ) ? filemtime( $admin_js ) : '0' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-dosf-admin.js', array( 'jquery' ), $admin_js_ver, false );

		wp_localize_script( 
			$this->plugin_name, 
			'dosf_config',
			array(
				'urlGetSOs'		 			=> rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_GET . '/' ),
				'urlAddSO'		 			=> rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_ADD_SO . '/' ),
				'urlRemSO'		 			=> rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_REM_SO . '/' ),
				'urlUpdSO'		 			=> rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_UPD_SO . '/' ),
				'urlSndDC'		 			=> rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_SEND_DWNL_CD . '/' ),
				'urlSndExpWrng'	 			=> rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_SEND_EXPRT_WRNG . '/' ),
				'urlUpdPlusOpts' 			=> rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_UPD_PLUS_OPTIONS . '/'),
				'restNonce' 				=> wp_create_nonce( 'wp_rest' ),
				'recipientSets'			=> self::recipient_sets(),
				'useIssueDate'	 			=> isset( $this->plus_options['use-issue-date'] ) && $this->plus_options['use-issue-date'],
				'restoredCatalog'			=> Dosf_Private_Files::restored_catalog(),
				'expiredNonce'			=> wp_create_nonce( 'dosf_expired_file' ),
				'expirityBefDayC'			=> self::get_dosf_validity_total_days(),
				'lastDayRangeForExprWrng'	=> self::get_last_range_days_before_expiration(),
				'expireWarnMax'			=> self::expire_warn_max()
			) 
		);
		
		$script_fl = 'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js';
		wp_enqueue_script(
			'dosf_jquery_datatable', 
			$script_fl,
			array('jquery'),
			null,
			false
		);

		$script_fl = 'https://code.jquery.com/ui/1.12.1/jquery-ui.js';
		wp_enqueue_script(
			'dosf_jquery_ui_js', 
			$script_fl,
			array('jquery'),
			null,
			false
		);

		$script_fl = plugin_dir_url( __FILE__ ) . 'js/libs/datetimepicker-master/build/jquery.datetimepicker.full.js';
		wp_enqueue_script(
			'dosf_jquery_datatimepicker', 
			$script_fl,
			array('jquery'),
			null,
			false
		);

		$script_fl = plugin_dir_url( __FILE__ ) . 'js/libs/choices-master/public/assets/scripts/choices.min.js';
		wp_enqueue_script(
			'dosf_js_choice', 
			$script_fl,
			array(),
			null,
			false
		);

		$script_fl = plugin_dir_url( __FILE__ ) . 'js/libs/blockui-2.70.0/jquery-blockUI.js';
		wp_enqueue_script(
			'dosf_jq_blockUI', 
			$script_fl,
			array('jquery'),
			null,
			false
		);
	}

	/**** Creando una pgina en el admin para configurar las imgenes de los ads y los links de cada una ****/
	//add_action( 'admin_menu', 'dosf_menu' );

	public static function menu_icon_choices() {
		return array(
			'dashicons-forms'          => 'Formulario',
			'dashicons-awards'         => 'Certificado',
			'dashicons-media-document' => 'Documento',
			'dashicons-media-text'     => 'Texto',
			'dashicons-media-default'  => 'Archivo',
			'dashicons-portfolio'      => 'Portafolio',
			'dashicons-clipboard'      => 'Portapapeles',
			'dashicons-paperclip'      => 'Adjunto',
			'dashicons-download'       => 'Descarga',
			'dashicons-email'          => 'Correo',
			'dashicons-admin-page'     => 'Página',
			'dashicons-admin-generic'  => 'Ajustes',
			'dashicons-admin-tools'    => 'Herramientas',
			'dashicons-shield'         => 'Escudo',
			'dashicons-lock'           => 'Candado',
			'dashicons-id'             => 'Identificación',
			'dashicons-groups'         => 'Grupos',
			'dashicons-building'       => 'Edificio',
			'dashicons-calendar-alt'   => 'Calendario',
			'dashicons-clock'          => 'Reloj',
			'dashicons-search'         => 'Búsqueda',
			'dashicons-list-view'      => 'Lista',
			'dashicons-category'       => 'Categoría',
			'dashicons-star-filled'    => 'Estrella',
			'dashicons-archive'        => 'Archivo comprimido',
			'dashicons-book-alt'       => 'Libro',
			'dashicons-tickets'        => 'Tickets',
			'dashicons-hammer'         => 'Martillo',
			'dashicons-images-alt2'    => 'Imagen',
			'dashicons-admin-site'     => 'Sitio',
		);
	}

	public static function page_heading( $plus_options = null ) {
		if ( $plus_options === null ) {
			$plus_options = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		}
		$heading = '';
		if ( is_array( $plus_options ) && isset( $plus_options['page-heading'] ) ) {
			$heading = trim( (string) $plus_options['page-heading'] );
		}
		if ( $heading === '' ) {
			$heading = 'Compartir o distribuir archivos';
		}
		return $heading;
	}

	public static function menu_label( $plus_options = null ) {
		if ( $plus_options === null ) {
			$plus_options = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		}
		$label = '';
		if ( is_array( $plus_options ) && isset( $plus_options['menu-label'] ) ) {
			$label = trim( (string) $plus_options['menu-label'] );
		}
		if ( $label === '' ) {
			$label = 'Distribución de archivos';
		}
		return $label;
	}

	public static function menu_icon( $plus_options = null ) {
		if ( $plus_options === null ) {
			$plus_options = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		}
		$icon = 'dashicons-forms';
		if ( is_array( $plus_options ) && ! empty( $plus_options['menu-icon'] ) ) {
			$candidate = (string) $plus_options['menu-icon'];
			if ( isset( self::menu_icon_choices()[ $candidate ] ) ) {
				$icon = $candidate;
			}
		}
		return $icon;
	}

	public function dosf_menu() {
		$plus  = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		$label = self::menu_label( $plus );
		add_menu_page(
			apply_filters( 'dosf-admin/admin-page-title', $label ),
			apply_filters( 'dosf-admin/admin-menu-title', $label ),
			'jgb_collab_access',
			'dosf-admin',
			array( $this, 'dosf_admin_page' ),
			self::menu_icon( $plus ),
			11
		);
	}
	public function dosf_register_submenus() {
		remove_submenu_page( 'dosf-admin', 'dosf-admin' );
		add_submenu_page(
			'dosf-admin',
			apply_filters( 'dosf-admin/gestion-page-title', 'Gestión de archivos' ),
			apply_filters( 'dosf-admin/gestion-menu-title', 'Gestión' ),
			'jgb_collab_access',
			'dosf-admin',
			array( $this, 'dosf_admin_page' )
		);
	}


	/* renderiza el contenido de la pgina de configuracin de ads-imgs */
	public function dosf_admin_page(){
		?>
		<!-- <form action='options.php' method='post'> -->

			<h2><?php echo esc_html( self::page_heading() ); ?></h2>
			<div class="dosf-admin-main-container">
			<?php
			//settings_fields( 'dosf' );
			//do_settings_sections( 'dosf' );
			$this->file_data_set_stack_field_render()
			//submit_button();
			?>
			</div>
		<!-- </form> -->
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

	public static function get_dosf_identifier_label($plus_options = null){
		if( is_null( $plus_options ) ){
			$plus_options 		= get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		}
		$dil = apply_filters('dosf_get_doc_obj_identifier_label_on´serials_disable','Título');
		if( isset( $plus_options['use-serial-number'] ) && $plus_options['use-serial-number'] ){
			$dil = apply_filters('dosf_get_doc_obj_identifier_label_on´serials_enable','Serie');
		}

		return $dil;
	}

	public function file_data_set_stack_field_render(){
		wp_enqueue_media();
		$plus_opts_settings = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		$processing_img_src = apply_filters('dosf_processing_img_src', plugin_dir_url(WP_DOSF_PLUGIN_PATH . "/.") . 'assets/imgs/spinningwheel.gif');
		$plus_options 		= $this->plus_options;
		$dosf_label_idntfr  = $this->dosf_identifier_label;
		
		?>

		<div id="confirm-del-dlg" title="Confirmación de eliminiación de certificado(s)">Se eliminarán los certificados seleccionados de la sección pública. Por favor confirme.</div>
		<div id="dosf-expired-delete-dlg" title="Eliminar PDF" style="display:none"><p>Se borrará el PDF de la tabla del plugin. Esta acción no se puede deshacer.</p></div>

		<div class="dosf-admin-header">
			
			<div id="add-dosf" class="action-wrapper"><span class="dashicons dashicons-plus-alt"></span>Agregar certificado</div>
			<div id="rem-dosf" class="action-wrapper disabled"><span class="dashicons dashicons-dismiss"></span>Remover seleccionados</div>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<div id="dosf-open-options" class="action-wrapper"><span class="dashicons dashicons-admin-generic"></span>Opciones</div>
			<?php endif; ?>
		</div>

		<div class="dosf-admin-add-so" style="display: none;">
			<div class="title"></div>
			<div class="fields-wrapper">
				<div class="fld-serie">
					<label for="dosf_so_serie">Número de serie</label>
					<input name="dosf_so_serie" id="dosf_so_serie" type="text" />
				</div>
				<div class="fld-title">
					<label for="dosf_so_cert_title">Título del certificado</label>
					<input name="dosf_so_cert_title" id="dosf_so_cert_title" type="text" />
				</div>
				<?php if( isset( $plus_options['use-issue-date'] ) && $plus_options['use-issue-date'] ) : ?>
				<div class="fld-emision">
					<label for="dosf_so_emision">Emisión</label>
					<input name="dosf_so_emision" id="dosf_so_emision" type="datetime" />
				</div>
				<?php endif; ?>
				<div class="fld-so">
					<label>Archivo del certificado</label>
					<button class="dosf-button" id="browse-file" type="button">Biblioteca de medios</button>
					<button class="dosf-button" id="browse-catalog" type="button">Catálogo del plugin</button>
					<div class="file-selected" id="dosf-file-selectd"></div>
					<input type='hidden' name='dosf_fl_attachment_id' id='dosf_attachment_id' value='' />
					<p class="description">La biblioteca de medios sube un archivo nuevo. El catálogo lista los PDF restaurados desde certificados vencidos.</p>
					<div id="dosf-catalog-dialog" title="Catálogo del plugin" style="display:none">
						<p class="description">Estos archivos no tienen URL pública.</p>
						<div id="dosf-catalog-list"></div>
					</div>
				</div>
				<div class="fld-ruts">
					<label for="dosf_so_ruts_linked">RUTs asociados</label>
					<input
						type="text"
						id="dosf_so_ruts_linked"
						name="dosf_so_ruts_linked"
						placeholder="Separar RUTs con comas y sin puntos ni guiones"
					>
				</div>
				<div id="dosf-recipient-fields">
				<?php foreach ( self::recipient_sets() as $recipient_set ) : ?>
				<div class="fld-email dosf-recipient-field">
					<label for="dosf_so_recipients_<?= esc_attr( $recipient_set['id'] ) ?>"><?= esc_html( $recipient_set['name'] ) ?></label>
					<input
						type="text"
						class="dosf-recipient-input"
						id="dosf_so_recipients_<?= esc_attr( $recipient_set['id'] ) ?>"
						data-set-id="<?= esc_attr( $recipient_set['id'] ) ?>"
						placeholder="Escriba un correo y pulse Enter"
					>
				</div>
				<?php endforeach; ?>
				</div>			</div>
			<div class="actions-wrapper">
				<div class="save"><button>Guardar</button></div>
				<div class="cancel"><button>Cancelar</button></div>
			</div>
			<div class="notice notice-error hidden"></div>
		</div>

		<div id="<?=HTML_DOSF_ID?>">
			
						<table id="tabla" class="display" style="width:100%">
				<thead class="thead">
					<tr class="tr">
						<th>Serie</th>
						<th>RUTs asociados</th>
						<th>Certificados</th>
					</tr>
				</thead>
				<tfoot>
					<tr class="tr">
						<th>Serie</th>
						<th>RUTs asociados</th>
						<th>Certificados</th>
					</tr>
				</tfoot>
			</table>

		</div>

		<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<div class="dosf-plus-options" title="Opciones">
			<div id="dosf-options-tabs">
				<ul>
					<li><a href="#dosf-tab-search">Búsqueda</a></li>
					<li><a href="#dosf-tab-validity">Vigencia</a></li>
					<li><a href="#dosf-tab-download">Descarga</a></li>
					<li><a href="#dosf-tab-mail">Correos</a></li>
					<li><a href="#dosf-tab-system">Sistema</a></li>
				</ul>
				<div id="dosf-tab-search">
					<p class="description">Cómo se busca un certificado en el sitio público.</p>
					<?php $checked = $plus_options['frontend-specific-match-search'] ? 'checked' : ''; ?>
					<div class="input">
						<input id="especific-match" type="checkbox" name="especific-match" <?= $checked ?>>
						<label for="especific-match">Coincidencias específicas en las búsquedas del frontend.</label>
					</div>
					<?php $checked = $plus_options['use-serial-number'] ? 'checked' : ''; ?>
					<div class="input">
						<input id="use-serial-numbers" type="checkbox" name="use-serial-numbers" <?= $checked ?>>
						<label for="use-serial-numbers">Identificar cada grúa por número de serie.</label>
					</div>
					<div class="input">
						<label for="serial-search-label">Texto del campo de búsqueda por número de serie</label>
						<textarea id="serial-search-label" name="serial-search-label" rows="3"><?= esc_textarea( self::serial_search_label( $plus_options ) ) ?></textarea>
						<p class="description">Se muestra en el frontend, encima del campo, cuando la búsqueda es por número de serie. <?= self::config_text_format_hint() ?></p>
					</div>
				</div>
				<div id="dosf-tab-validity">
					<p class="description">Cuándo vence un certificado, qué texto ve quien lo busca y qué pasa con el archivo vencido.</p>
					<?php $checked = $plus_options['use-issue-date'] ? 'checked' : ''; ?>
					<div class="input">
						<input id="use-issue-date" type="checkbox" name="use-issue-date" <?= $checked ?>>
						<label for="use-issue-date">Utilizar fecha de emisión.</label>
					</div>
					<div class="input">
						<label for="expire-period-nmb">Vencimiento a los</label>
						<?php $value = empty($plus_options['expire-period-nmb']) ? '' : 'value="'.intval($plus_options['expire-period-nmb']).'"'; ?>
						<input id="expire-period-nmb" type="number" name="expire-period-nmb" min="1" <?= $value ?>>
						<?php $value = empty($plus_options['expire-period-unit']) ? '' : $plus_options['expire-period-unit']; ?>
						<select id="expire-period-unit" name="expire-period-unit">
							<option value="">Seleccione intervalo</option>
							<option value="day" <?= $value=='day' ? 'selected' : ''?>>día(s)</option>
							<option value="week" <?= $value=='week' ? 'selected' : ''?>>semana(s)</option>
							<option value="month" <?= $value=='month' ? 'selected' : ''?>>mes(es)</option>
							<option value="year" <?= $value=='year' ? 'selected' : ''?>>año(s)</option>
						</select>
					</div>
					<div class="input">
						<label for="validity-date-format">Formato de la fecha de vigencia</label>
						<select id="validity-date-format" name="validity-date-format">
							<?php
							$validity_format = self::validity_date_format( $plus_options );
							foreach ( self::validity_date_formats() as $format_key => $format_label ) :
							?>
							<option value="<?= esc_attr( $format_key ) ?>" <?= $validity_format === $format_key ? 'selected' : '' ?>><?= esc_html( $format_label ) ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">Se usa en el marcador {fecha_vencimiento}. El estándar de Chile es día-mes-año, por ejemplo 09-10-2026.</p>
					</div>
					<?php
						$status_tpl_defaults = self::default_status_templates();
						$status_tpl_vigente  = ( isset( $plus_options['status-tpl-vigente'] ) && trim( (string) $plus_options['status-tpl-vigente'] ) !== '' )
							? $plus_options['status-tpl-vigente']
							: $status_tpl_defaults['vigente'];
						$status_tpl_vencido  = ( isset( $plus_options['status-tpl-vencido'] ) && trim( (string) $plus_options['status-tpl-vencido'] ) !== '' )
							? $plus_options['status-tpl-vencido']
							: $status_tpl_defaults['vencido'];
					?>
					<div class="dosf-status-templates">
						<h3>Mensajes del frontend</h3>
						<p class="description">Texto que ve quien busca un certificado. Marcadores: <code>{cert_title}</code> título, <code>{serie}</code> número de serie, <code>{estado}</code> la palabra vigente o vencido y <code>{fecha_vencimiento}</code> la fecha de vencimiento, calculada sumando el plazo de vigencia a la fecha de emisión. <?= self::config_text_format_hint() ?></p>
						<div class="input">
							<label for="status-tpl-vigente">Certificado vigente</label>
							<textarea id="status-tpl-vigente" name="status-tpl-vigente" rows="3"><?= esc_textarea( $status_tpl_vigente ) ?></textarea>
						</div>
						<div class="input">
							<label for="status-tpl-vencido">Certificado vencido</label>
							<textarea id="status-tpl-vencido" name="status-tpl-vencido" rows="3"><?= esc_textarea( $status_tpl_vencido ) ?></textarea>
						</div>
					</div>
					<div class="dosf-status-templates">
						<h3>Avisos y papelera</h3>
						<div class="input">
							<label for="ebep-period">Aviso por email dentro de los</label>
							<?php $value = empty($plus_options['ebep-nmb']) ? '' : 'value="'.intval($plus_options['ebep-nmb']).'"'; ?>
							<input id="ebep-nmb" type="number" name="ebep-nmb" min="1" <?= $value ?>>
							<?php $value = empty($plus_options['ebep-unit']) ? '' : $plus_options['ebep-unit']; ?>
							<select id="ebep-unit" name="ebep-unit">
								<option value="">Seleccione intervalo</option>
								<option value="day" <?= $value=='day' ? 'selected' : ''?>>día(s)</option>
								<option value="week" <?= $value=='week' ? 'selected' : ''?>>semana(s)</option>
								<option value="month" <?= $value=='month' ? 'selected' : ''?>>mes(es)</option>
								<option value="year" <?= $value=='year' ? 'selected' : ''?>>año(s)</option>
							</select>
							<label for="ebep-period"> antes de vencer.</label>
						</div>
						<div class="input">
							<label for="expire-warn-max">Avisos máximos antes de vencer</label>
							<?php
								$expire_warn_max = isset( $plus_options['expire-warn-max'] ) ? intval( $plus_options['expire-warn-max'] ) : 1;
								if ( $expire_warn_max < 1 ) {
									$expire_warn_max = 1;
								}
							?>
							<input id="expire-warn-max" type="number" name="expire-warn-max" min="1" value="<?= $expire_warn_max ?>">
							<p class="description">Cuántos correos de aviso se envían como máximo mientras el certificado sigue dentro del plazo y todavía no vence. Al llegar a ese número no se envían más. El contador y la fecha de cada envío exitoso se conservan cuando el certificado vence. Vuelven a cero solo si cambia la fecha de emisión.</p>
						</div>
						<div class="input">
							<label for="monitor-expire-interval">Monitorear vencimientos cada </label>
							<?php $value = empty($plus_options['monitor-expire-interval']) ? '' : 'value="'.intval($plus_options['monitor-expire-interval']).'"'; ?>
							<input id="monitor-expire-interval" type="number" name="monitor-expire-interval" min="1" <?= $value ?>>
							<label for="monitor-expire-interval"> día(s).</label>
						</div>
						<div class="input">
							<?php $checked = ! empty( $plus_options['delete-file-after-expire'] ) ? 'checked' : ''; ?>
							<input id="delete-file-after-expire" type="checkbox" name="delete-file-after-expire" <?= $checked ?>>
							<label for="delete-file-after-expire">Mover a la papelera el archivo del certificado después de vencer.</label>
						</div>
						<div class="input">
							<label for="delete-file-after-nmb">Eliminar transcurridos</label>
							<?php $value = isset( $plus_options['delete-file-after-nmb'] ) && $plus_options['delete-file-after-nmb'] !== '' ? 'value="' . intval( $plus_options['delete-file-after-nmb'] ) . '"' : 'value="0"'; ?>
							<input id="delete-file-after-nmb" type="number" name="delete-file-after-nmb" min="0" <?= $value ?>>
							<?php $value = empty( $plus_options['delete-file-after-unit'] ) ? 'day' : $plus_options['delete-file-after-unit']; ?>
							<select id="delete-file-after-unit" name="delete-file-after-unit">
								<option value="day" <?= $value=='day' ? 'selected' : '' ?>>día(s)</option>
								<option value="week" <?= $value=='week' ? 'selected' : '' ?>>semana(s)</option>
								<option value="month" <?= $value=='month' ? 'selected' : '' ?>>mes(es)</option>
								<option value="year" <?= $value=='year' ? 'selected' : '' ?>>año(s)</option>
							</select>
							<label for="delete-file-after-unit">desde el vencimiento.</label>
							<p class="description">El plazo se cuenta desde la fecha de vencimiento. Una tarea diaria mueve el adjunto a la papelera de WordPress con el procedimiento estándar de adjuntos. El archivo permanece en el disco y se puede restaurar desde Medios → Papelera.</p>
						</div>
					</div>
				</div>
				<div id="dosf-tab-download">
					<p class="description">El código que se envía para autorizar una descarga y el popup donde se ingresa.</p>
					<div class="input">
						<label for="download-code-valid-nmb">El código de descarga es válido durante</label>
						<?php
							$code_valid_nmb  = isset( $plus_options['download-code-valid-nmb'] ) ? max( 1, intval( $plus_options['download-code-valid-nmb'] ) ) : 1;
							$code_valid_unit = isset( $plus_options['download-code-valid-unit'] ) ? $plus_options['download-code-valid-unit'] : 'hour';
							if ( ! in_array( $code_valid_unit, array( 'minute', 'hour', 'day', 'week' ), true ) ) {
								$code_valid_unit = 'hour';
							}
						?>
						<input id="download-code-valid-nmb" type="number" name="download-code-valid-nmb" min="1" value="<?= $code_valid_nmb ?>">
						<select id="download-code-valid-unit" name="download-code-valid-unit">
							<option value="minute" <?= $code_valid_unit === 'minute' ? 'selected' : '' ?>>minuto(s)</option>
							<option value="hour" <?= $code_valid_unit === 'hour' ? 'selected' : '' ?>>hora(s)</option>
							<option value="day" <?= $code_valid_unit === 'day' ? 'selected' : '' ?>>día(s)</option>
							<option value="week" <?= $code_valid_unit === 'week' ? 'selected' : '' ?>>semana(s)</option>
						</select>
						<p class="description">El plazo empieza cuando se genera el código. Pasado ese tiempo hay que solicitar la descarga otra vez.</p>
					</div>
					<div class="input">
						<label for="download-code-reuse-nmb">Reutilizar el código si todavía le quedan al menos</label>
						<?php
							$code_reuse_nmb  = isset( $plus_options['download-code-reuse-nmb'] ) ? max( 0, intval( $plus_options['download-code-reuse-nmb'] ) ) : 0;
							$code_reuse_unit = isset( $plus_options['download-code-reuse-unit'] ) ? $plus_options['download-code-reuse-unit'] : 'minute';
							if ( ! in_array( $code_reuse_unit, array( 'minute', 'hour', 'day', 'week' ), true ) ) {
								$code_reuse_unit = 'minute';
							}
						?>
						<input id="download-code-reuse-nmb" type="number" name="download-code-reuse-nmb" min="0" value="<?= $code_reuse_nmb ?>">
						<select id="download-code-reuse-unit" name="download-code-reuse-unit">
							<option value="minute" <?= $code_reuse_unit === 'minute' ? 'selected' : '' ?>>minuto(s)</option>
							<option value="hour" <?= $code_reuse_unit === 'hour' ? 'selected' : '' ?>>hora(s)</option>
							<option value="day" <?= $code_reuse_unit === 'day' ? 'selected' : '' ?>>día(s)</option>
							<option value="week" <?= $code_reuse_unit === 'week' ? 'selected' : '' ?>>semana(s)</option>
						</select>
						<p class="description">Si una solicitud nueva llega y al código vigente todavía le queda al menos ese tiempo, se reenvía el mismo y el anterior sigue sirviendo. Con 0 se reutiliza durante todo el plazo de validez. Si queda menos, se genera otro código y el anterior deja de servir. El vencimiento original no se alarga.</p>
					</div>
					<div class="input">
						<label for="pm-download-code-popup-id">Popup (Popup Maker) para ingreso de código de descarga</label>
						<?php
							$pm_popup_id = empty( $plus_options['pm-download-code-popup-id'] ) ? 0 : intval( $plus_options['pm-download-code-popup-id'] );
							$pm_popups   = self::popup_maker_choices();
							$pm_known    = array();
						?>
						<select id="pm-download-code-popup-id" name="pm-download-code-popup-id">
							<option value="0">Seleccione un popup</option>
							<?php foreach ( $pm_popups as $pm_popup ) : ?>
								<?php $pm_known[] = $pm_popup['id']; ?>
								<option value="<?= intval( $pm_popup['id'] ) ?>" <?= $pm_popup_id === intval( $pm_popup['id'] ) ? 'selected' : '' ?>><?= esc_html( $pm_popup['label'] ) ?></option>
							<?php endforeach; ?>
							<?php if ( $pm_popup_id > 0 && ! in_array( $pm_popup_id, $pm_known, true ) ) : ?>
								<option value="<?= $pm_popup_id ?>" selected><?= esc_html( 'Popup #' . $pm_popup_id . ' (no encontrado)' ) ?></option>
							<?php endif; ?>
						</select>
					</div>
				</div>
				<div id="dosf-tab-mail">
					<p class="description">A quién se escribe y con qué texto.</p>
					<div class="dosf-status-templates" id="dosf-recipient-sets">
						<h3>Conjuntos de destinatarios</h3>
						<p class="description">Cada conjunto agrupa correos de un certificado. Se puede crear un conjunto, eliminarlo o cambiarle el nombre. Los correos se agregan, quitan o editan en cada certificado.</p>
						<div id="dosf-recipient-set-list">
							<?php foreach ( self::recipient_sets() as $recipient_set ) : ?>
							<div class="dosf-recipient-set" data-set-id="<?= esc_attr( $recipient_set['id'] ) ?>">
								<input type="text" class="dosf-recipient-set-name regular-text" maxlength="80" value="<?= esc_attr( $recipient_set['name'] ) ?>">
								<button type="button" class="button dosf-recipient-set-remove">Eliminar</button>
							</div>
							<?php endforeach; ?>
						</div>
						<div class="input">
							<input type="text" id="dosf-recipient-set-new" class="regular-text" maxlength="80" placeholder="Nombre del conjunto">
							<button type="button" class="button" id="dosf-recipient-set-add">Agregar conjunto</button>
						</div>
						<h3>Destinatarios de los correos</h3>
						<p class="description">Marque qué conjuntos reciben cada mensaje. Por defecto, el código de descarga va a colaboradores y operadores, y el aviso de vencimiento va a mecánicos.</p>
						<?php foreach ( array( 'download' => 'Código de descarga', 'expire' => 'Aviso de vencimiento' ) as $mail_kind => $mail_title ) : ?>
						<div class="dosf-mail-kind" data-kind="<?= esc_attr( $mail_kind ) ?>">
							<p><strong><?= esc_html( $mail_title ) ?></strong></p>
							<div class="dosf-mail-kind-sets">
							<?php foreach ( self::recipient_sets() as $recipient_set ) : ?>
								<?php $mail_id = $mail_kind . '-mail-to-' . $recipient_set['id']; ?>
								<div class="input dosf-mail-to-row" data-set-id="<?= esc_attr( $recipient_set['id'] ) ?>">
									<input id="<?= esc_attr( $mail_id ) ?>" class="dosf-mail-to" type="checkbox" data-kind="<?= esc_attr( $mail_kind ) ?>" data-set-id="<?= esc_attr( $recipient_set['id'] ) ?>" <?= self::mail_goes_to( $plus_options, $mail_kind, $recipient_set['id'] ) ? 'checked' : '' ?>>
									<label class="dosf-mail-to-label" for="<?= esc_attr( $mail_id ) ?>"><?= esc_html( $recipient_set['name'] ) ?></label>
								</div>
							<?php endforeach; ?>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
					<div class="dosf-status-templates dosf-email-templates">
						<h3>Plantillas de correo</h3>
						<p class="description">Código HTML de cada correo que envía el plugin. <strong>Vista previa</strong> abre el correo con datos de ejemplo, sin enviarlo y sin guardar. Si un cuadro se deja vacío y se guarda, se usa otra vez la plantilla incluida con el plugin.</p>
						<div class="input">
							<label for="email-tpl-download-subject">Asunto del correo con el código de descarga</label>
							<input id="email-tpl-download-subject" type="text" class="large-text" value="<?= esc_attr( self::email_template_value( $plus_options, 'download-subject' ) ) ?>">
							<p class="description">Marcadores: <code>{cert_title}</code>, <code>{serie}</code>, <code>{download_code}</code> y <code>{validez_codigo}</code> (el plazo de <strong>El código de descarga es válido durante</strong>, por ejemplo 30 minutos o 1 hora). Se envía al crear el certificado y cada vez que se solicita la descarga.</p>
						</div>
						<div class="input">
							<label for="email-tpl-download-html">HTML del correo con el código de descarga</label>
							<textarea id="email-tpl-download-html" rows="14"><?= esc_textarea( self::email_template_value( $plus_options, 'download-html' ) ) ?></textarea>
							<p class="dosf-email-preview-bar"><button type="button" class="button dosf-email-preview" data-kind="download">Vista previa</button></p>
						</div>
						<div class="input">
							<label for="email-tpl-expire-subject">Asunto del aviso de vencimiento</label>
							<input id="email-tpl-expire-subject" type="text" class="large-text" value="<?= esc_attr( self::email_template_value( $plus_options, 'expire-subject' ) ) ?>">
							<p class="description">Marcadores: <code>{cert_title}</code>, <code>{serie}</code>, <code>{dias_vigencia}</code>, <code>{emision}</code> y <code>{id}</code>.</p>
						</div>
						<div class="input">
							<label for="email-tpl-expire-html">HTML del aviso de vencimiento</label>
							<textarea id="email-tpl-expire-html" rows="14"><?= esc_textarea( self::email_template_value( $plus_options, 'expire-html' ) ) ?></textarea>
							<p class="dosf-email-preview-bar"><button type="button" class="button dosf-email-preview" data-kind="expire">Vista previa</button></p>
						</div>
					</div>
				</div>
				<div id="dosf-tab-system">
					<p class="description">Título de la pantalla de Gestión, y texto e icono del menú lateral de WordPress para este plugin.</p>
					<?php
						$menu_label_saved = ( is_array( $plus_options ) && isset( $plus_options['menu-label'] ) ) ? (string) $plus_options['menu-label'] : '';
						$menu_icon_saved  = self::menu_icon( $plus_options );
						$menu_icon_names  = self::menu_icon_choices();
					?>
					<div class="input">
						<label for="dosf-page-heading">Título de la pantalla de Gestión</label>
						<?php $page_heading_saved = ( is_array( $plus_options ) && isset( $plus_options['page-heading'] ) ) ? (string) $plus_options['page-heading'] : ''; ?>
						<input id="dosf-page-heading" type="text" class="regular-text" maxlength="80" data-saved="<?= esc_attr( $page_heading_saved ) ?>" value="<?= esc_attr( $page_heading_saved ) ?>" placeholder="Compartir o distribuir archivos">
						<p class="description">Es el título que aparece arriba de los botones. Si se deja vacío, se muestra «Compartir o distribuir archivos».</p>
					</div>
					<div class="input">
						<label for="dosf-menu-label">Label para la opción del menú</label>
						<input id="dosf-menu-label" type="text" class="regular-text" maxlength="40" data-saved="<?= esc_attr( $menu_label_saved ) ?>" value="<?= esc_attr( $menu_label_saved ) ?>" placeholder="Distribución de archivos">
						<p class="description">Si se deja vacío, el menú muestra «Distribución de archivos».</p>
					</div>
					<div class="input">
						<label>Icono del menú</label>
						<input type="hidden" id="dosf-menu-icon" data-saved="<?= esc_attr( $menu_icon_saved ) ?>" value="<?= esc_attr( $menu_icon_saved ) ?>">
						<div class="dosf-menu-icon-picker">
							<?php foreach ( $menu_icon_names as $menu_icon_slug => $menu_icon_name ) : ?>
								<button type="button" class="dosf-menu-icon-choice<?= $menu_icon_saved === $menu_icon_slug ? ' is-selected' : '' ?>" data-icon="<?= esc_attr( $menu_icon_slug ) ?>" data-label="<?= esc_attr( $menu_icon_name ) ?>" aria-label="<?= esc_attr( $menu_icon_name ) ?>" aria-pressed="<?= $menu_icon_saved === $menu_icon_slug ? 'true' : 'false' ?>">
									<span class="dashicons <?= esc_attr( $menu_icon_slug ) ?>"></span>
								</button>
							<?php endforeach; ?>
						</div>
						<p class="description">Iconos estándar de WordPress. El elegido se muestra junto al título en el menú lateral. Ahora: <strong id="dosf-menu-icon-name"><?= esc_html( $menu_icon_names[ $menu_icon_saved ] ) ?></strong>.</p>
						<div class="dosf-menu-preview-wrap">
							<p class="description">Vista previa del menú lateral.</p>
							<p class="dosf-menu-preview" aria-hidden="true"><span id="dosf-menu-preview-icon" class="dashicons <?= esc_attr( $menu_icon_saved ) ?>"></span><span id="dosf-menu-preview-label"><?= esc_html( self::menu_label( $plus_options ) ) ?></span></p>
						</div>
					</div>
										<div class="dosf-expired-files">
						<h3>Certificados vencidos</h3>
						<p class="description">PDF guardados en la tabla del plugin. Descargar solo funciona en esta pantalla. Restaurar los deja en el catálogo para asignarlos al crear o editar un certificado, sin volver a publicar la URL. Eliminar borra el PDF de la tabla.</p>
						<?php $expired_files = Dosf_Private_Files::expired_certificate_rows(); ?>
						<?php if ( ! count( $expired_files ) ) : ?>
							<p>No hay certificados vencidos con un archivo guardado.</p>
						<?php else : ?>
						<table class="widefat striped">
							<thead>
								<tr>
									<th>Serie</th>
									<th>Título</th>
									<th>Archivo</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ( $expired_files as $expired_file ) : ?>
								<tr data-attachment="<?= intval( $expired_file['attachment_id'] ) ?>">
									<td><?= esc_html( $expired_file['serie'] ) ?></td>
									<td><?= esc_html( $expired_file['title'] ) ?></td>
									<td><?= esc_html( $expired_file['filename'] ) ?></td>
									<td class="dosf-expired-actions">
										<a class="button button-small" href="<?= esc_url( Dosf_Private_Files::admin_download_url( $expired_file['attachment_id'] ) ) ?>">Descargar</a>
										<button type="button" class="button button-small dosf-expired-restore" data-attachment="<?= intval( $expired_file['attachment_id'] ) ?>" <?= $expired_file['restored'] ? 'disabled' : '' ?>><?= $expired_file['restored'] ? 'En el catálogo' : 'Restaurar' ?></button>
										<button type="button" class="button button-small dosf-expired-delete" data-attachment="<?= intval( $expired_file['attachment_id'] ) ?>" <?= $expired_file['delete_blocked'] ? 'disabled' : '' ?> title="<?= $expired_file['delete_blocked'] ? 'Hay un certificado vigente que usa este archivo' : '' ?>">Eliminar</button>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<input type="hidden" name="plus-options-update-nonce" id="plus-options-update-nonce" value="<?= wp_create_nonce(DOSF_NONCE_ACTION_PLUS_OPTS_UPDATE) ?>">
			<div class="dosf-plus-options-actions">
				<button id="dosf-plus-options-save">Guardar opciones</button>
				<div class="processing hidden"><img src="<?= $processing_img_src ?>"></div>
			</div>
			<div id="after-try-update-res-placeholder" class="hidden">Respuesta</div>
		</div>
		<div id="dosf-email-preview-dialog" title="Vista previa del correo" style="display:none">
			<p id="dosf-email-preview-note" class="description">Datos de ejemplo. El correo real usa el certificado que se envía.</p>
			<p id="dosf-email-preview-subject"></p>
			<iframe id="dosf-email-preview-frame" sandbox title="Vista previa del correo"></iframe>
		</div>

		<?php endif; ?>
		
		<?php
	}

	//add_action( 'rest_api_init', 'emp_set_endpoints');
    public function set_endpoints(){
        register_rest_route(
            DOSF_APIREST_BASE_ROUTE,
            '/'. DOSF_URI_ID_GET . '/',
            array(
                'methods'  => 'GET',
                'callback' => array(
                    $this,
                    'send_so_data'
                ),
                'permission_callback' => '__return_true'
            )
        );

        register_rest_route(
            DOSF_APIREST_BASE_ROUTE,
            '/'.DOSF_URI_ID_ADD_SO.'/',
            array(
                'methods'  => 'POST',
                'callback' => array(
                    $this,
                    'receive_new_dosf_data_set'
                ),
                'permission_callback' => '__return_true',
            )
        );

		register_rest_route(
            DOSF_APIREST_BASE_ROUTE,
            '/'.DOSF_URI_ID_UPD_SO.'/(?P<dosf_id>\d+)',
            array(
                'methods'  => 'PUT',
                'callback' => array(
                    $this,
                    'receive_update_dosf_data_set'
                ),
                'permission_callback' => '__return_true',
            )
        );

		register_rest_route(
            DOSF_APIREST_BASE_ROUTE,
            '/'.DOSF_URI_ID_REM_SO,
            array(
                'methods'  => 'DELETE',
                'callback' => array(
                    $this,
                    'receive_dosf_remove_request'
                ),
                'permission_callback' => '__return_true',
            )
        );

		register_rest_route(
            DOSF_APIREST_BASE_ROUTE,
            '/'.DOSF_URI_ID_SEND_DWNL_CD.'/(?P<dosf_id>\d+)',
            array(
                'methods'  => 'GET',
                'callback' => array(
                    $this,
                    'receive_send_dwld_code_req'
                ),
                'permission_callback' => '__return_true',
            )
        );

		register_rest_route(
            DOSF_APIREST_BASE_ROUTE,
            '/'.DOSF_URI_ID_SEND_EXPRT_WRNG.'/(?P<dosf_id>\d+)',
            array(
                'methods'  => 'GET',
                'callback' => array(
                    $this,
                    'receive_send_exp_warning_req'
                ),
                'permission_callback' => '__return_true',
            )
        );

		register_rest_route(
			DOSF_APIREST_BASE_ROUTE,
            '/'.DOSF_URI_ID_UPD_PLUS_OPTIONS,
            array(
                'methods'  => 'POST',
                'callback' => array(
                    $this,
                    'receive_plus_options'
                ),
                'permission_callback' => array( $this, 'user_can_edit_plugin_options' ),
            )
		);
    }

	public function user_can_edit_plugin_options() {
		return current_user_can( 'manage_options' );
	}

	public function receive_dosf_remove_request( WP_REST_Request $r ){
		$ids = $r->get_json_params();
		$ids = isset( $ids['istr'] ) ? $ids['istr'] : array();
		$res = array( 'details' => Dosf_Series::delete_certificates( $ids ) );
		return new WP_REST_Response( $res );
	}

	public static function get_dosf_validity_total_days(){
		$plus_options = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		if( isset( $plus_options['use-issue-date'] ) && $plus_options['use-issue-date'] ){
			$punits =  intval($plus_options['expire-period-nmb']);
			switch( $plus_options['expire-period-unit'] ){
				case 'year':
					$diff = $punits * 365;
					break;

				case 'week':
					$diff = $punits * 7;
					break;

				case 'month':

					$diff = $punits * 30;
					break;

				case 'day':
					$diff = $punits;
					break;

				default:
					$diff = $punits;
			}

			return $diff;
		}
		return null;
	}


	public static function config_text_allowed_html() {
		$span = array(
			'style' => true,
		);
		return array(
			'b'      => array(),
			'strong' => array(),
			'i'      => array(),
			'em'     => array(),
			'u'      => array(),
			'br'     => array(),
			'span'   => $span,
			'font'   => array(
				'color' => true,
				'face'  => true,
				'size'  => true,
			),
		);
	}

	public static function format_config_html( $html ) {
		return trim( wp_kses( (string) $html, self::config_text_allowed_html() ) );
	}

	public static function render_config_html( $html ) {
		return nl2br( self::format_config_html( $html ) );
	}

	public static function config_text_format_hint() {
		return 'El formato se escribe en HTML y puede cubrir una parte del texto o todo: <code>&lt;b&gt;negrita&lt;/b&gt;</code>, <code>&lt;u&gt;subrayado&lt;/u&gt;</code>, <code>&lt;i&gt;cursiva&lt;/i&gt;</code>, <code>&lt;span style="color:#c0392b"&gt;color&lt;/span&gt;</code>, <code>&lt;span style="font-family:Georgia, serif"&gt;tipo de letra&lt;/span&gt;</code> y <code>&lt;span style="font-size:18px"&gt;tamaño&lt;/span&gt;</code>.';
	}

	public static function serial_search_label( $plus_options ) {
		$default = 'Ingrese número de serie sin punto y sin guión (por ejemplo G1000000000 ó GA000000000)';
		if ( is_array( $plus_options ) && isset( $plus_options['serial-search-label'] ) ) {
			$label = trim( (string) $plus_options['serial-search-label'] );
			if ( $label !== '' ) {
				return $label;
			}
		}
		return $default;
	}

	public static function default_status_templates() {
		$text = '{cert_title} de la serie {serie} se encuentra actualmente {estado}.';
		return array(
			'vigente' => $text,
			'vencido' => $text,
		);
	}

	public static function add_calendar_interval( $date, $amount, $unit ) {
		$units = array(
			'minute' => 'minute',
			'hour'   => 'hour',
			'day'    => 'day',
			'week'   => 'week',
			'month'  => 'month',
			'year'   => 'year',
		);
		$amount = intval( $amount );
		if ( ! ( $date instanceof DateTime ) || $amount < 0 || ! isset( $units[ $unit ] ) ) {
			return null;
		}
		$copy = clone $date;
		if ( $amount === 0 ) {
			return $copy;
		}
		$spec = $amount === 1 ? $units[ $unit ] : $units[ $unit ] . 's';
		$copy->modify( '+' . $amount . ' ' . $spec );
		return $copy;
	}

	public static function download_code_validity_label( $plus_options ) {
		$amount = 1;
		$unit   = 'hour';
		$units  = array(
			'minute' => array( 'minuto', 'minutos' ),
			'hour'   => array( 'hora', 'horas' ),
			'day'    => array( 'día', 'días' ),
			'week'   => array( 'semana', 'semanas' ),
		);
		if ( is_array( $plus_options ) ) {
			if ( isset( $plus_options['download-code-valid-nmb'] ) ) {
				$amount = max( 1, intval( $plus_options['download-code-valid-nmb'] ) );
			}
			if ( ! empty( $plus_options['download-code-valid-unit'] ) && isset( $units[ $plus_options['download-code-valid-unit'] ] ) ) {
				$unit = $plus_options['download-code-valid-unit'];
			}
		}
		$word = $amount === 1 ? $units[ $unit ][0] : $units[ $unit ][1];
		return $amount . ' ' . $word;
	}

	public static function download_code_is_current( $issued_at ) {
		if ( ! is_string( $issued_at ) || trim( $issued_at ) === '' || $issued_at === '0000-00-00 00:00:00' ) {
			return false;
		}

		$plus   = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		$amount = 1;
		$unit   = 'hour';
		$units  = array( 'minute', 'hour', 'day', 'week' );
		if ( is_array( $plus ) ) {
			if ( isset( $plus['download-code-valid-nmb'] ) ) {
				$amount = max( 1, intval( $plus['download-code-valid-nmb'] ) );
			}
			if ( ! empty( $plus['download-code-valid-unit'] ) && in_array( $plus['download-code-valid-unit'], $units, true ) ) {
				$unit = $plus['download-code-valid-unit'];
			}
		}

		try {
			$issued = new DateTime( $issued_at );
		} catch ( Exception $e ) {
			return false;
		}

		$expires = self::add_calendar_interval( $issued, $amount, $unit );
		if ( ! $expires ) {
			return false;
		}

		return new DateTime( current_time( 'mysql' ) ) <= $expires;
	}

	public static function download_code_should_reuse( $issued_at ) {
		if ( ! self::download_code_is_current( $issued_at ) ) {
			return false;
		}

		$plus   = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		$amount = 0;
		$unit   = 'minute';
		$units  = array( 'minute', 'hour', 'day', 'week' );
		if ( is_array( $plus ) ) {
			if ( isset( $plus['download-code-reuse-nmb'] ) ) {
				$amount = max( 0, intval( $plus['download-code-reuse-nmb'] ) );
			}
			if ( ! empty( $plus['download-code-reuse-unit'] ) && in_array( $plus['download-code-reuse-unit'], $units, true ) ) {
				$unit = $plus['download-code-reuse-unit'];
			}
		}
		if ( $amount < 1 ) {
			return true;
		}

		$valid_amount = 1;
		$valid_unit   = 'hour';
		if ( is_array( $plus ) ) {
			if ( isset( $plus['download-code-valid-nmb'] ) ) {
				$valid_amount = max( 1, intval( $plus['download-code-valid-nmb'] ) );
			}
			if ( ! empty( $plus['download-code-valid-unit'] ) && in_array( $plus['download-code-valid-unit'], $units, true ) ) {
				$valid_unit = $plus['download-code-valid-unit'];
			}
		}

		try {
			$issued = new DateTime( $issued_at );
			$now    = new DateTime( current_time( 'mysql' ) );
		} catch ( Exception $e ) {
			return false;
		}

		$expires = self::add_calendar_interval( $issued, $valid_amount, $valid_unit );
		$limit   = self::add_calendar_interval( $now, $amount, $unit );
		if ( ! $expires || ! $limit ) {
			return false;
		}

		return $expires >= $limit;
	}

	public static function certificate_expiration_datetime( $plus_options, $emision ) {
		if ( ! is_array( $plus_options ) || empty( $emision ) ) {
			return null;
		}
		$amount = isset( $plus_options['expire-period-nmb'] ) ? intval( $plus_options['expire-period-nmb'] ) : 0;
		$unit   = isset( $plus_options['expire-period-unit'] ) ? $plus_options['expire-period-unit'] : '';
		if ( $amount < 1 ) {
			return null;
		}
		try {
			$date = new DateTime( $emision );
		} catch ( Exception $e ) {
			return null;
		}
		return self::add_calendar_interval( $date, $amount, $unit );
	}

	public static function validity_date_formats() {
		return array(
			'd-m-Y'           => 'Chile: día-mes-año (09-10-2026)',
			'd/m/Y'           => 'Día/mes/año (09/10/2026)',
			'j \d\e F \d\e Y' => 'Día de mes de año (9 de octubre de 2026)',
			'Y-m-d'           => 'Año-mes-día (2026-10-09)',
		);
	}

	public static function validity_date_format( $plus_options ) {
		$formats = self::validity_date_formats();
		if ( is_array( $plus_options ) && isset( $plus_options['validity-date-format'] ) && isset( $formats[ $plus_options['validity-date-format'] ] ) ) {
			return $plus_options['validity-date-format'];
		}
		return 'd-m-Y';
	}

	public static function certificate_expiration_label( $plus_options, $emision ) {
		$date = self::certificate_expiration_datetime( $plus_options, $emision );
		if ( ! $date ) {
			return '';
		}
		return date_i18n( self::validity_date_format( $plus_options ), $date->getTimestamp() );
	}

	public static function certificate_file_due_for_removal( $plus_options, $emision, $now = null ) {
		if ( ! is_array( $plus_options ) || empty( $plus_options['delete-file-after-expire'] ) ) {
			return false;
		}
		$expires = self::certificate_expiration_datetime( $plus_options, $emision );
		if ( ! $expires ) {
			return false;
		}
		$grace_n = isset( $plus_options['delete-file-after-nmb'] ) ? intval( $plus_options['delete-file-after-nmb'] ) : 0;
		$grace_u = isset( $plus_options['delete-file-after-unit'] ) ? $plus_options['delete-file-after-unit'] : 'day';
		$due     = self::add_calendar_interval( $expires, $grace_n, $grace_u );
		if ( ! $due ) {
			return false;
		}
		if ( ! ( $now instanceof DateTime ) ) {
			$now = new DateTime( 'now' );
		}
		return $now >= $due;
	}

	public static function attachment_is_downloadable( $attachment_id ) {
		$post = get_post( intval( $attachment_id ) );
		if ( ! $post || $post->post_type !== 'attachment' || $post->post_status === 'trash' ) {
			return false;
		}
		return ! empty( wp_get_attachment_url( $post->ID ) );
	}

	public static function trash_certificate_attachment( $attachment_id ) {
		$attachment_id = intval( $attachment_id );
		$post          = get_post( $attachment_id );
		if ( ! $post || $post->post_type !== 'attachment' ) {
			return false;
		}
		if ( $post->post_status === 'trash' ) {
			self::mark_certificates_file_unavailable( $attachment_id );
			return true;
		}
		if ( defined( 'MEDIA_TRASH' ) && MEDIA_TRASH && function_exists( 'wp_delete_attachment' ) ) {
			$result = wp_delete_attachment( $attachment_id, false );
		} else {
			$result = wp_trash_post( $attachment_id );
		}
		if ( ! empty( $result ) ) {
			self::mark_certificates_file_unavailable( $attachment_id );
		}
		return ! empty( $result );
	}

	public static function mark_certificates_file_unavailable( $attachment_id ) {
		return self::set_certificates_file_unavailable( $attachment_id, 1 );
	}

	public static function mark_certificates_file_available( $attachment_id ) {
		return self::set_certificates_file_unavailable( $attachment_id, 0 );
	}

	private static function set_certificates_file_unavailable( $attachment_id, $unavailable ) {
		global $wpdb;

		$attachment_id = intval( $attachment_id );
		if ( $attachment_id < 1 ) {
			return 0;
		}

		$table   = $wpdb->prefix . 'dosf_shared_objs';
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET file_unavailable = %d WHERE wp_file_obj_id = %d",
				$unavailable ? 1 : 0,
				$attachment_id
			)
		);

		return $updated === false ? 0 : intval( $updated );
	}

	public function on_attachment_trashed( $post_id ) {
		$post = get_post( intval( $post_id ) );
		if ( ! $post || $post->post_type !== 'attachment' ) {
			return;
		}
		self::mark_certificates_file_unavailable( $post_id );
	}

	public function on_attachment_deleted( $post_id ) {
		self::mark_certificates_file_unavailable( $post_id );
	}

	public function on_attachment_restored( $post_id ) {
		if ( class_exists( 'Dosf_Private_Files' ) && Dosf_Private_Files::$suppress_public_restore ) {
			return;
		}
		$post = get_post( intval( $post_id ) );
		if ( ! $post || $post->post_type !== 'attachment' ) {
			return;
		}
		if ( self::attachment_is_downloadable( $post_id ) ) {
			self::mark_certificates_file_available( $post_id );
			return;
		}
		self::mark_certificates_file_unavailable( $post_id );
	}

	public static function purge_expired_certificate_files() {
		$plus_options = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		if ( ! is_array( $plus_options ) || empty( $plus_options['delete-file-after-expire'] ) ) {
			return 0;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'dosf_shared_objs';
		$rows  = $wpdb->get_results( "SELECT id, emision, wp_file_obj_id FROM {$table} WHERE wp_file_obj_id > 0" );
		if ( ! is_array( $rows ) ) {
			return 0;
		}

		$grouped = array();
		foreach ( $rows as $row ) {
			$attachment_id = intval( $row->wp_file_obj_id );
			if ( $attachment_id < 1 ) {
				continue;
			}
			if ( ! isset( $grouped[ $attachment_id ] ) ) {
				$grouped[ $attachment_id ] = array();
			}
			$grouped[ $attachment_id ][] = $row;
		}

		$trashed = 0;
		$now     = new DateTime( 'now' );
		foreach ( $grouped as $attachment_id => $certs ) {
			$all_due = true;
			foreach ( $certs as $cert ) {
				if ( ! self::certificate_file_due_for_removal( $plus_options, $cert->emision, $now ) ) {
					$all_due = false;
					break;
				}
			}
			if ( ! $all_due ) {
				continue;
			}
			if ( self::trash_certificate_attachment( $attachment_id ) ) {
				$trashed++;
			}
		}

		return $trashed;
	}
	public static function frontend_status_message( $plus_options, $cert_title, $serie, $status, $emision = '' ) {
		$status = strtolower( (string) $status );
		if ( $status !== 'vigente' && $status !== 'vencido' ) {
			$status = 'vencido';
		}

		$defaults = self::default_status_templates();
		$key      = 'status-tpl-' . $status;
		$tpl      = ( is_array( $plus_options ) && isset( $plus_options[ $key ] ) ) ? trim( (string) $plus_options[ $key ] ) : '';
		if ( $tpl === '' ) {
			$tpl = $defaults[ $status ];
		}

		$estado = '<span class="status ' . esc_attr( $status ) . '">' . esc_html( $status ) . '</span>';
		$repl   = array(
			'{cert_title}'        => esc_html( (string) $cert_title ),
			'{serie}'             => esc_html( (string) $serie ),
			'{estado}'            => $estado,
			'{fecha_vencimiento}' => esc_html( self::certificate_expiration_label( $plus_options, $emision ) ),
		);

		return strtr( self::render_config_html( $tpl ), $repl );
	}
	public static function get_dosf_status($sql_date){
		$plus_options = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		if( isset( $plus_options['use-issue-date'] ) && $plus_options['use-issue-date'] ){
			$emsdt = strtotime($sql_date);
			$curdt = strtotime(date('Y-m-d H:i:s'));
			switch( $plus_options['expire-period-unit'] ){
				case 'year':
					$y1 = intval(date('Y',$emsdt));
					$y2 = intval(date('Y',$curdt));
					$diff = $y2 - $y1;
					break;

				case 'week':
					
					break;

				case 'month':
					$y1 = intval(date('Y',$emsdt));
					$y2 = intval(date('Y',$curdt));

					$m1 = intval(date('m',$emsdt));
					$m2 = intval(date('m',$curdt));

					$d1 = intval(date('d',$emsdt));
					$d2 = intval(date('d',$curdt));

					$md = ($d2 - $d1) < 0 ? -1 : 0;

					$diff = (($y2 - $y1) * 12) + ($m2 - $m1) + $md;
					break;

				case 'day':
					$diff = 0;
					break;

				default:
					$diff = 0;
			}
		}

		if( intval($plus_options['expire-period-nmb']) > $diff ) 
			return 'Vigente';
		else 
			return 'Vencido';
	}

	public static function get_dosf_validity_days_before_expiration( $sql_date ){
		$plus_options = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		if( isset( $plus_options['use-issue-date'] ) && $plus_options['use-issue-date'] ){
			$emsdt = strtotime($sql_date);
			$curdt = strtotime(date('Y-m-d H:i:s'));
			$dtdiffDF = $emsdt - $curdt;
			$dtdiffNF = round( $dtdiffDF / (60 * 60 * 24) ) + Wp_Dosf_Admin::get_dosf_validity_total_days();
			return $dtdiffNF;
		}

		return null;
	}

	public static function get_last_range_days_before_expiration(){
		$plus_options = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		if( isset( $plus_options['use-issue-date'] ) && $plus_options['use-issue-date'] ){
			
			$nmbBefExp = intval($plus_options['ebep-nmb']);
			switch( $plus_options['ebep-unit'] ){
				case 'year':
					$dtdiffDF = $nmbBefExp * 365;
					break;

				case 'week':
					$dtdiffDF = $nmbBefExp * 7;
					break;

				case 'month':
					$dtdiffDF = $nmbBefExp * 30;
					break;

				default:
					$dtdiffDF = $nmbBefExp;
			}
			
			return $dtdiffDF;
		}

		return null;
	}

	public function send_so_data($r){
		$res = Dosf_Series::datatables_response( $_GET );
		$response = new WP_REST_Response( $res );
		$response->set_status( 200 );
		return $response;
	}

	function generate_download_code($length = 6, $chars = null){
	
		if(is_null($chars))
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$string = '';

		for ($i = 0; $i < $length; $i++) {
			$string .= $characters[mt_rand(0, strlen($characters) - 1)];
		}

		return $string;

	}

	public static function popup_maker_choices() {
		if ( ! post_type_exists( 'popup' ) ) {
			return array();
		}

		$posts = get_posts( array(
			'post_type'      => 'popup',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$choices = array();
		foreach ( $posts as $post ) {
			$title = trim( $post->post_title );
			if ( $title === '' ) {
				$title = '(sin título)';
			}
			$label = $title . ' (#' . intval( $post->ID ) . ')';
			if ( $post->post_status !== 'publish' ) {
				$label .= ' — ' . $post->post_status;
			}
			$choices[] = array(
				'id'    => intval( $post->ID ),
				'label' => $label,
			);
		}

		return $choices;
	}

	public function receive_plus_options($r){
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'error'    => true,
				'err_code' => DOSF_PLUS_OPTS_UPDATE_ERR_INVALID_NONCE,
			);
		}

		$data = $r->get_json_params();

		$r = array(
			'error' 		=> false,
			'err_code'		=> null
		);

		
		if( !isset( $data['nonce'] ) ){
			$r['error'] 	= true;
			$r['err_code']	= DOSF_PLUS_OPTS_UPDATE_ERR_INVALID_NONCE;
			return $r;
		}

		
		/* if ( ! wp_verify_nonce(  $r['nonce'], DOSF_NONCE_ACTION_PLUS_OPTS_UPDATE ) ) {
			$r['error'] 	= true;
			$r['err_code']	= DOSF_PLUS_OPTS_UPDATE_ERR_INVALID_NONCE;
			return $r;
		} */



		foreach ( array( 'status-tpl-vigente', 'status-tpl-vencido' ) as $tpl_key ) {
			if ( isset( $data[ $tpl_key ] ) ) {
				$data[ $tpl_key ] = self::format_config_html( $data[ $tpl_key ] );
			}
		}

		$popup_id = isset( $data['pm-download-code-popup-id'] ) ? intval( $data['pm-download-code-popup-id'] ) : 0;
		if ( $popup_id > 0 ) {
			$popup_post = get_post( $popup_id );
			if ( ! $popup_post || $popup_post->post_type !== 'popup' ) {
				$popup_id = 0;
			}
		}
		$data['pm-download-code-popup-id'] = $popup_id;

		$data['download-code-valid-nmb'] = isset( $data['download-code-valid-nmb'] ) ? max( 1, intval( $data['download-code-valid-nmb'] ) ) : 1;
		$code_valid_units = array( 'minute', 'hour', 'day', 'week' );
		if ( empty( $data['download-code-valid-unit'] ) || ! in_array( $data['download-code-valid-unit'], $code_valid_units, true ) ) {
			$data['download-code-valid-unit'] = 'hour';
		}
		if ( isset( $data['download-code-reuse-nmb'] ) ) {
			$data['download-code-reuse-nmb'] = max( 0, intval( $data['download-code-reuse-nmb'] ) );
		} else {
			$stored_code = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
			$data['download-code-reuse-nmb'] = ( is_array( $stored_code ) && isset( $stored_code['download-code-reuse-nmb'] ) )
				? max( 0, intval( $stored_code['download-code-reuse-nmb'] ) )
				: 0;
		}
		if ( empty( $data['download-code-reuse-unit'] ) || ! in_array( $data['download-code-reuse-unit'], $code_valid_units, true ) ) {
			$stored_code = isset( $stored_code ) ? $stored_code : get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
			$reuse_unit  = ( is_array( $stored_code ) && ! empty( $stored_code['download-code-reuse-unit'] ) ) ? $stored_code['download-code-reuse-unit'] : 'minute';
			$data['download-code-reuse-unit'] = in_array( $reuse_unit, $code_valid_units, true ) ? $reuse_unit : 'minute';
		}

		$formats = array_keys( self::validity_date_formats() );
		if ( empty( $data['validity-date-format'] ) || ! in_array( $data['validity-date-format'], $formats, true ) ) {
			$data['validity-date-format'] = 'd-m-Y';
		}

		if ( isset( $data['recipient-sets'] ) && is_array( $data['recipient-sets'] ) ) {
			$recipient_sets = self::sanitize_recipient_sets( $data['recipient-sets'] );
			update_option( DOSF_WP_OPT_NM_RECIPIENT_SETS, $recipient_sets, false );
			$kept_sets = array();
			foreach ( $recipient_sets as $recipient_set ) {
				$kept_sets[ $recipient_set['id'] ] = true;
			}
			if ( class_exists( 'Dosf_Series' ) ) {
				Dosf_Series::purge_removed_recipient_sets( $kept_sets );
			}
		} else {
			$recipient_sets = self::recipient_sets();
		}
		unset( $data['recipient-sets'] );

		$valid_set_ids = array();
		foreach ( $recipient_sets as $recipient_set ) {
			$valid_set_ids[ $recipient_set['id'] ] = true;
		}
		$mail_keys_sent = array();
		foreach ( array_keys( $data ) as $mail_key ) {
			if ( ! preg_match( '/^(download|expire)-mail-to-(.+)$/', $mail_key, $mail_match ) ) {
				continue;
			}
			if ( ! isset( $valid_set_ids[ $mail_match[2] ] ) ) {
				unset( $data[ $mail_key ] );
				continue;
			}
			$data[ $mail_key ]           = ! empty( $data[ $mail_key ] );
			$mail_keys_sent[ $mail_key ] = true;
		}
		foreach ( $recipient_sets as $recipient_set ) {
			foreach ( array( 'download', 'expire' ) as $mail_kind ) {
				$mail_key = $mail_kind . '-mail-to-' . $recipient_set['id'];
				if ( ! isset( $mail_keys_sent[ $mail_key ] ) ) {
					$data[ $mail_key ] = self::mail_goes_to( array(), $mail_kind, $recipient_set['id'] );
				}
			}
		}

		foreach ( array( 'email-tpl-download-subject', 'email-tpl-expire-subject' ) as $mail_key ) {
			$data[ $mail_key ] = isset( $data[ $mail_key ] ) ? sanitize_text_field( $data[ $mail_key ] ) : '';
		}
		foreach ( array( 'email-tpl-download-html', 'email-tpl-expire-html' ) as $mail_key ) {
			$html = isset( $data[ $mail_key ] ) ? (string) $data[ $mail_key ] : '';
			if ( ! current_user_can( 'unfiltered_html' ) ) {
				$html = wp_kses_post( $html );
			}
			$data[ $mail_key ] = $html;
		}

		$menu_label = isset( $data['menu-label'] ) ? sanitize_text_field( $data['menu-label'] ) : '';
		$menu_label = trim( $menu_label );
		if ( function_exists( 'mb_substr' ) ) {
			$menu_label = mb_substr( $menu_label, 0, 40 );
		} else {
			$menu_label = substr( $menu_label, 0, 40 );
		}
		$data['menu-label'] = $menu_label;
		$page_heading = isset( $data['page-heading'] ) ? sanitize_text_field( $data['page-heading'] ) : '';
		$page_heading = trim( $page_heading );
		if ( function_exists( 'mb_substr' ) ) {
			$page_heading = mb_substr( $page_heading, 0, 80 );
		} else {
			$page_heading = substr( $page_heading, 0, 80 );
		}
		$data['page-heading'] = $page_heading;
		$menu_icon = isset( $data['menu-icon'] ) ? sanitize_text_field( $data['menu-icon'] ) : '';
		if ( ! isset( self::menu_icon_choices()[ $menu_icon ] ) ) {
			$menu_icon = 'dashicons-forms';
		}
		$data['menu-icon'] = $menu_icon;

		$data['serial-search-label'] = isset( $data['serial-search-label'] ) ? self::format_config_html( $data['serial-search-label'] ) : '';
		if ( $data['serial-search-label'] === '' ) {
			$data['serial-search-label'] = self::serial_search_label( array() );
		}

		if ( isset( $data['expire-warn-max'] ) ) {
			$data['expire-warn-max'] = max( 1, intval( $data['expire-warn-max'] ) );
		} else {
			$stored_warn = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
			$data['expire-warn-max'] = ( is_array( $stored_warn ) && isset( $stored_warn['expire-warn-max'] ) )
				? max( 1, intval( $stored_warn['expire-warn-max'] ) )
				: 1;
		}

		$data['delete-file-after-expire'] = ! empty( $data['delete-file-after-expire'] );
		$data['delete-file-after-nmb'] = isset( $data['delete-file-after-nmb'] ) ? max( 0, intval( $data['delete-file-after-nmb'] ) ) : 0;
		$delete_units = array( 'day', 'week', 'month', 'year' );
		if ( empty( $data['delete-file-after-unit'] ) || ! in_array( $data['delete-file-after-unit'], $delete_units, true ) ) {
			$data['delete-file-after-unit'] = 'day';
		}

		//DOSF_WP_OPT_NM_PLUS_OPTIONS
		if( !update_option(DOSF_WP_OPT_NM_PLUS_OPTIONS,$data) ){
			$stored_plus = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
			if ( $stored_plus != $data ) {
				$r['error'] 	= true;
				$r['err_code']	= DOSF_PLUS_OPTS_UPDATE_ERR_DB;
				return $r;
			}
		}

		$r['updated_data'] = $data;

		return $r;
		
	}

	public function receive_new_dosf_data_set($r){
		$data = $r->get_json_params();
		$saved = Dosf_Series::save_certificate( $data );

		if ( ! empty( $saved['error'] ) ) {
			return $saved['payload'];
		}

		if ( $saved['operation'] === 'INSERT' ) {
			$mail_sent_res = $this->send_download_code_email( $saved['mail_args'] );
			return array(
				'dosf_operation'         => 'INSERT',
				'dosfAddNew_post_status' => 'ok',
				'dosfAddNew_email_sent'  => $mail_sent_res,
			);
		}

		return array(
			'dosf_operation'         => 'UPDATE',
			'dosfUpdate_post_status' => 'ok',
			'dosfAddNew_email_sent'  => null,
		);
	}

	function checkStoredMatchBySerialNumber( $serial ){
		global $wpdb;
		$tbl_nm_shared_objs = $wpdb->prefix . 'dosf_shared_objs';
		$match_count = $wpdb->get_var("
			SELECT COUNT(*)
			FROM `$tbl_nm_shared_objs` 
			WHERE `id` = \"$serial\"" );
		return $match_count > 0 ? true : false;
	}

	public function receive_send_dwld_code_req($r){
		$dosf_id = $r->get_url_params();
		if(isset($dosf_id['dosf_id']) && !empty($dosf_id['dosf_id'])){
			$dosf_id = $dosf_id['dosf_id'];
			global $wpdb;
			
			$where  = ' WHERE wdso.id = '. $dosf_id . ' ';

			$isql = "SELECT 
						file_name,
						wp_file_obj_id,
									email,
						email2,
						email3,
						download_code

					FROM {$wpdb->prefix}dosf_shared_objs wdso 
					
					$where ";
			$qry = 'SELECT FOUND_ROWS() AS total_rcds';
			
			$sos = $wpdb->get_results($isql, OBJECT);
			
			$rc = array();
			
			foreach($sos as $c){
				
				$rc[] = array(
					
					'file_name'   => $c->file_name,
					'wp_obj_id'   => $c->wp_file_obj_id,
					'email'		  => $c->email,
					'email2'	  => $c->email2,
					'email3'	  => $c->email3,
					'download_code'	  => $c->download_code
					
				);
			}

			if(count($rc) == 1){
				$attachment_id = intval($rc[0]['wp_obj_id']);
				$file_path = get_attached_file($attachment_id);
				$download_code = Dosf_Series::refresh_download_code( intval( $dosf_id ) );
				if ( $download_code === '' ) {
					return array(
						'error' => true,
						'error_code' => 3,
						'error_msg' => 'No se pudo generar un código de descarga',
						'dosfDldCdSend_email_sent' => false,
					);
				}

				$args = array(
					'id' => $dosf_id,
					'email' => $rc[0]['email'],
					'email2' => $rc[0]['email2'],
					'email3' => $rc[0]['email3'],
					'download_code' => $download_code,
					'file' => $file_path
				);
				
				$mail_sent_res = $this->send_download_code_email($args);
			} else {
				// error
				return [
					'error' => true,
					'error_code' => 1,
					'error_msg' => 'Demasiados registros o registro no encontrado', 
					'dosfDldCdSend_email_sent' => false
				];
			}
		} else {
			return [
				'error' => true,
				'error_code' => 2,
				'error_msg' => 'Identificador inválido', 
				'dosfDldCdSend_email_sent' => false
			];
		}

		
		

		return [
			'error' => false,
			'dosfDldCdSend_email_sent' => $mail_sent_res
		];
	}

	public function receive_send_exp_warning_req(WP_REST_Request $r){
		$dosf_id = $r->get_url_params();
		if(isset($dosf_id['dosf_id']) && !empty($dosf_id['dosf_id'])){
			$dosf_id = $dosf_id['dosf_id'];
			global $wpdb;
			
			$where  = ' WHERE wdso.id = '. $dosf_id . ' ';

			$isql = "SELECT 
						id,
						title,
						email,
						email2,
						email3,
						emision

					FROM {$wpdb->prefix}dosf_shared_objs wdso 
					
					$where ";
			
			$sos = $wpdb->get_results($isql, OBJECT);
			
			$rc = array();
			
			foreach($sos as $c){
				
				$rc[] = array(
					
					'id'   		=> $c->id,
					'serial'   	=> $c->title,
					'email'		=> $c->email,
					'email2'	=> $c->email2,
					'email3'	=> $c->email3,
					'emision'	=> $c->emision
					
				);
			}

			if(count($rc) == 1){
				$mail_sent_res = self::send_expiration_warning_email($rc[0]);
				if ( $mail_sent_res === true ) {
					self::increment_expire_warn_sent( intval( $rc[0]['id'] ) );
				} elseif ( is_wp_error( $mail_sent_res ) ) {
					self::increment_expire_warn_failed( intval( $rc[0]['id'] ) );
					return array(
						'error' => true,
						'error_code' => 3,
						'error_msg' => 'No se pudo enviar el aviso de vencimiento',
						'dosfExprtWrngSend_email_sent' => false,
						'expire_warn_attempt' => true,
					);
				} else {
					return array(
						'error' => true,
						'error_code' => 3,
						'error_msg' => 'No se pudo enviar el aviso de vencimiento',
						'dosfExprtWrngSend_email_sent' => false,
					);
				}

			} else {
				// error
				return [
					'error' => true,
					'error_code' => 1,
					'error_msg' => 'Demasiados registros o registro no encontrado',
					'dosfExprtWrngSend_email_sent' => false
				];
			}
		} else {
			return [
				'error' => true,
				'error_code' => 2,
				'error_msg' => 'Identificador inválido',
				'dosfExprtWrngSend_email_sent' => false
			];
		}

		return [
			'error' => false,
			'dosfExprtWrngSend_email_sent' => true,
			'expire_warn_sent' => self::expire_warn_sent_count( intval( $rc[0]['id'] ) ),
		];
	}

	public static function read_bundled_email_file( $filename ) {
		$path = WP_DOSF_PLUGIN_PATH . '/templates/emails/' . $filename;
		if ( ! is_readable( $path ) ) {
			return '';
		}
		$contents = file_get_contents( $path );
		return is_string( $contents ) ? $contents : '';
	}

	public static function default_email_templates() {
		$header = self::read_bundled_email_file( 'email_header.tpl' );
		$footer = self::read_bundled_email_file( 'email_footer.tpl' );
		return array(
			'download-subject' => 'Grua PM :: Código de descarga {cert_title} serie {serie}',
			'download-html'    => $header . self::read_bundled_email_file( 'email_new_dosf_content.tpl' ) . $footer,
			'expire-subject'   => 'Grua PM :: {cert_title} de la serie {serie} próximo a vencer',
			'expire-html'      => $header . self::read_bundled_email_file( 'email_expiration_warning.tpl' ) . $footer,
		);
	}

	public static function email_template_value( $plus_options, $key ) {
		$defaults   = self::default_email_templates();
		$option_key = 'email-tpl-' . $key;
		if ( is_array( $plus_options ) && isset( $plus_options[ $option_key ] ) && trim( (string) $plus_options[ $option_key ] ) !== '' ) {
			return (string) $plus_options[ $option_key ];
		}
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	public static function fill_email_template( $template, $pairs, $escape_html ) {
		$search  = array();
		$replace = array();
		foreach ( $pairs as $token => $value ) {
			$search[]  = $token;
			$text      = (string) $value;
			$replace[] = $escape_html ? esc_html( $text ) : $text;
		}
		return str_replace( $search, $replace, (string) $template );
	}

	public static function default_recipient_sets() {
		return array(
			array(
				'id'   => 'email',
				'name' => 'Emails de colaboradores',
			),
			array(
				'id'   => 'email2',
				'name' => 'Emails de operadores',
			),
			array(
				'id'   => 'email3',
				'name' => 'Emails de mecánicos',
			),
		);
	}

	private static function recipient_set_id_ok( $id ) {
		return $id === 'email' || $id === 'email2' || $id === 'email3' || (bool) preg_match( '/^set_[a-z0-9]{6,12}$/', (string) $id );
	}

	public static function recipient_sets() {
		$stored = get_option( DOSF_WP_OPT_NM_RECIPIENT_SETS, null );
		if ( ! is_array( $stored ) ) {
			$stored = self::default_recipient_sets();
			update_option( DOSF_WP_OPT_NM_RECIPIENT_SETS, $stored, false );
		}
		$clean = array();
		foreach ( $stored as $set ) {
			if ( ! is_array( $set ) || empty( $set['id'] ) || ! isset( $set['name'] ) ) {
				continue;
			}
			$id   = (string) $set['id'];
			$name = trim( (string) $set['name'] );
			if ( $name === '' || ! self::recipient_set_id_ok( $id ) ) {
				continue;
			}
			$clean[] = array(
				'id'   => $id,
				'name' => $name,
			);
		}
		return $clean;
	}

	public static function sanitize_recipient_sets( $raw ) {
		$clean = array();
		$seen  = array();
		if ( ! is_array( $raw ) ) {
			return self::recipient_sets();
		}
		foreach ( $raw as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$name = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
			$name = trim( $name );
			if ( $name === '' ) {
				continue;
			}
			if ( function_exists( 'mb_substr' ) ) {
				$name = mb_substr( $name, 0, 80 );
			} else {
				$name = substr( $name, 0, 80 );
			}
			$id = isset( $item['id'] ) ? (string) $item['id'] : '';
			if ( ! self::recipient_set_id_ok( $id ) || isset( $seen[ $id ] ) ) {
				$id = 'set_' . substr( md5( uniqid( '', true ) ), 0, 8 );
			}
			$seen[ $id ] = true;
			$clean[]     = array(
				'id'   => $id,
				'name' => $name,
			);
		}
		return $clean;
	}

	public static function mail_recipient_groups() {
		$groups = array();
		foreach ( self::recipient_sets() as $set ) {
			$groups[ $set['id'] ] = $set['name'];
		}
		return $groups;
	}

	public static function mail_goes_to( $plus_options, $kind, $group ) {
		$key = $kind . '-mail-to-' . $group;
		if ( is_array( $plus_options ) && array_key_exists( $key, $plus_options ) ) {
			return ! empty( $plus_options[ $key ] );
		}
		if ( $kind === 'download' ) {
			return $group === 'email' || $group === 'email2';
		}
		return $group === 'email3';
	}

	public static function mail_recipients( $plus_options, $kind, $args ) {
		$map = array();
		if ( ! empty( $args['id'] ) && class_exists( 'Dosf_Series' ) ) {
			$row = Dosf_Series::context( intval( $args['id'] ) );
			if ( $row ) {
				$map = Dosf_Series::recipient_map( $row );
			}
		}
		if ( isset( $args['recipients'] ) && is_array( $args['recipients'] ) ) {
			foreach ( $args['recipients'] as $id => $value ) {
				$map[ $id ] = is_array( $value ) ? implode( ',', $value ) : (string) $value;
			}
		}
		foreach ( self::recipient_sets() as $set ) {
			$id = $set['id'];
			if ( ! isset( $map[ $id ] ) && isset( $args[ $id ] ) ) {
				$map[ $id ] = is_array( $args[ $id ] ) ? implode( ',', $args[ $id ] ) : (string) $args[ $id ];
			}
		}

		$seen = array();
		$list = array();
		foreach ( self::mail_recipient_groups() as $group => $label ) {
			if ( ! self::mail_goes_to( $plus_options, $kind, $group ) ) {
				continue;
			}
			$raw   = isset( $map[ $group ] ) ? (string) $map[ $group ] : '';
			$parts = preg_split( '/\s*,\s*/', $raw );
			if ( ! is_array( $parts ) ) {
				continue;
			}
			foreach ( $parts as $part ) {
				$part = trim( $part );
				if ( $part === '' ) {
					continue;
				}
				$token = strtolower( $part );
				if ( isset( $seen[ $token ] ) ) {
					continue;
				}
				$seen[ $token ] = true;
				$list[]         = $part;
			}
		}
		return implode( ',', $list );
	}
	public function send_download_code_email($args){
		$cert_id = 0;
		if ( ! empty( $args['id'] ) ) {
			$cert_id = intval( $args['id'] );
		}
		if ( $cert_id && class_exists( 'Dosf_Series' ) ) {
			$ctx = Dosf_Series::context( $cert_id );
			if ( $ctx ) {
				$args['serial'] = $ctx->serie;
				$args['cert_title'] = $ctx->cert_title;
			}
		}

		if(  !isset($args['download_code']) || empty($args['download_code']) )
			return false;

		$plus_mail = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		$email = self::mail_recipients( $plus_mail, 'download', $args );
		if ( $email === '' ) {
			return false;
		}

		$pairs = array(
			'{download_code}'  => $args['download_code'],
			'{serie}'          => isset( $args['serial'] ) ? $args['serial'] : '',
			'{cert_title}'     => isset( $args['cert_title'] ) ? $args['cert_title'] : '',
			'{validez_codigo}' => self::download_code_validity_label( $plus_mail ),
		);
		$content = self::fill_email_template( self::email_template_value( $plus_mail, 'download-html' ), $pairs, true );
		$subject = apply_filters(
			'dosf_eml_new_obj_eml_subject',
			self::fill_email_template( self::email_template_value( $plus_mail, 'download-subject' ), $pairs, false )
		);

		$mail_sent_res = false;
		if ( $content !== '' ) {
			$header = array('Content-Type: text/html; charset=UTF-8');
			$header = apply_filters(
						'dosf_eml_new_obj_eml_header',
						$header
					);
			
			$attachments = array();
			if(isset($args['file']) && !empty($args['file'])){
				$attachments[] = $args['file'];
			}
			//$mail_sent_res = wp_mail($email,$subject,$content,$header,$attachments);
			$mail_sent_res = wp_mail($email,$subject,$content,$header);
		}

		return $mail_sent_res;
	}

	public static function send_expiration_warning_email( $args ){
		$plus_options = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		if( !isset( $plus_options['use-issue-date'] ) ){
			return false;
		}
		
		if( empty( $plus_options['use-issue-date'] ) ){
			return false;
		}
			
		if ( empty( $args['emision'] ) ) {
			return false;
		}

		$days_left = self::get_dosf_validity_days_before_expiration( $args['emision'] );
		$warn_window = self::get_last_range_days_before_expiration();
		if ( $days_left === null || $warn_window === null || $days_left <= 0 || $days_left > $warn_window ) {
			return false;
		}

		$email = apply_filters(
			'dosf_eml_expiration_warning_str_dest_recipts',
			self::mail_recipients( $plus_options, 'expire', $args )
		);
		if ( $email === '' ) {
			return new WP_Error( 'dosf_warn_mail', 'sin destinatarios' );
		}

				if ( ! empty( $args['id'] ) && class_exists( 'Dosf_Series' ) ) {
			$ctx = Dosf_Series::context( intval( $args['id'] ) );
			if ( $ctx ) {
				$args['serial']     = $ctx->serie;
				$args['cert_title'] = $ctx->cert_title;
			}
		}
		$pairs = array(
			'{id}'             => isset( $args['id'] ) ? $args['id'] : '',
			'{serie}'          => isset( $args['serial'] ) ? $args['serial'] : '',
			'{cert_title}'     => isset( $args['cert_title'] ) ? $args['cert_title'] : '',
			'{emision}'        => $args['emision'],
			'{dias_vigencia}'  => self::get_dosf_validity_days_before_expiration( $args['emision'] ),
		);
		$content = self::fill_email_template( self::email_template_value( $plus_options, 'expire-html' ), $pairs, true );
		$subject = apply_filters(
			'dosf_eml_expiration_warning_subject',
			self::fill_email_template( self::email_template_value( $plus_options, 'expire-subject' ), $pairs, false )
		);

		if ( $content === '' ) {
			return new WP_Error( 'dosf_warn_mail', 'sin contenido' );
		}
		$header = array('Content-Type: text/html; charset=UTF-8');
		$header = apply_filters(
					'dosf_eml_expiration_warning_eml_header',
					$header
				);
		if ( ! self::html_mail_without_error( $email, $subject, $content, $header ) ) {
			return new WP_Error( 'dosf_warn_mail', 'envio fallido' );
		}
		return true;
	}

	public static function html_mail_without_error( $email, $subject, $content, $header ) {
		$failed = false;
		$listener = function () use ( &$failed ) {
			$failed = true;
		};
		add_action( 'wp_mail_failed', $listener );
		$sent = wp_mail( $email, $subject, $content, $header );
		remove_action( 'wp_mail_failed', $listener );
		return ( $sent === true && ! $failed );
	}

	public static function expire_warn_sent_count( $cert_id ) {
		global $wpdb;

		if ( class_exists( 'Dosf_Series' ) ) {
			Dosf_Series::ensure_expire_warn_column();
		}
		$sent = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT expire_warn_sent FROM {$wpdb->prefix}dosf_shared_objs WHERE id = %d",
				intval( $cert_id )
			)
		);
		return $sent === null ? 0 : intval( $sent );
	}

	public static function expire_warn_max() {
		$plus_options = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		$max          = ( is_array( $plus_options ) && isset( $plus_options['expire-warn-max'] ) ) ? intval( $plus_options['expire-warn-max'] ) : 1;
		return max( 1, $max );
	}

	public static function expire_warn_allowed( $cert_id ) {
		global $wpdb;

		if ( class_exists( 'Dosf_Series' ) ) {
			Dosf_Series::ensure_expire_warn_column();
		}
		$sent = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT expire_warn_sent FROM {$wpdb->prefix}dosf_shared_objs WHERE id = %d",
				intval( $cert_id )
			)
		);
		if ( $sent === null ) {
			return false;
		}
		return intval( $sent ) < self::expire_warn_max();
	}

	public static function increment_expire_warn_sent( $cert_id ) {
		self::increment_expire_warn_column( $cert_id, 'expire_warn_sent' );
		self::append_expire_warn_sent_at( $cert_id );
	}

	public static function append_expire_warn_sent_at( $cert_id, $mysql_time = null ) {
		global $wpdb;

		if ( class_exists( 'Dosf_Series' ) ) {
			Dosf_Series::ensure_expire_warn_column();
		}
		$raw = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT expire_warn_sent_at FROM {$wpdb->prefix}dosf_shared_objs WHERE id = %d",
				intval( $cert_id )
			)
		);
		$list = json_decode( (string) $raw, true );
		if ( ! is_array( $list ) ) {
			$list = array();
		}
		$stamp = is_string( $mysql_time ) && $mysql_time !== '' ? $mysql_time : current_time( 'mysql' );
		$list[] = $stamp;
		$wpdb->update(
			$wpdb->prefix . 'dosf_shared_objs',
			array( 'expire_warn_sent_at' => wp_json_encode( array_values( $list ) ) ),
			array( 'id' => intval( $cert_id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}

	public static function expire_warn_sent_labels( $raw ) {
		$list = json_decode( (string) $raw, true );
		if ( ! is_array( $list ) ) {
			return array();
		}
		$plus_options = get_option( DOSF_WP_OPT_NM_PLUS_OPTIONS );
		$format = self::validity_date_format( $plus_options ) . ' H:i';
		$labels = array();
		foreach ( $list as $item ) {
			$item = trim( (string) $item );
			if ( $item === '' ) {
				continue;
			}
			$dt = date_create( $item, wp_timezone() );
			if ( ! $dt ) {
				continue;
			}
			$labels[] = wp_date( $format, $dt->getTimestamp() );
		}
		return $labels;
	}

	public static function increment_expire_warn_failed( $cert_id ) {
		self::increment_expire_warn_column( $cert_id, 'expire_warn_failed' );
	}

	private static function increment_expire_warn_column( $cert_id, $column ) {
		global $wpdb;

		if ( $column !== 'expire_warn_sent' && $column !== 'expire_warn_failed' ) {
			return;
		}
		if ( class_exists( 'Dosf_Series' ) ) {
			Dosf_Series::ensure_expire_warn_column();
		}
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}dosf_shared_objs SET {$column} = {$column} + 1 WHERE id = %d",
				intval( $cert_id )
			)
		);
	}

	public static function verify_certs_expiration(){
		global $wpdb;
		$tbl_nm_shared_objs = $wpdb->prefix . 'dosf_shared_objs';
		$tbl_nm_ewmq =  $wpdb->prefix . 'dosf_expiration_warnig_email_queue';

		if ( class_exists( 'Dosf_Series' ) ) {
			Dosf_Series::ensure_expire_warn_column();
		}

		$max    = self::expire_warn_max();
		$window = self::get_last_range_days_before_expiration();
		$isql = "SELECT id, emision, expire_warn_sent, expire_warn_failed FROM $tbl_nm_shared_objs";
		$results = $wpdb->get_results( $isql );

		foreach( $results as $r ){
			$days = Wp_Dosf_Admin::get_dosf_validity_days_before_expiration( $r->emision );
			$sent = intval( $r->expire_warn_sent );
			$inside = ( $days !== null && $window !== null && $days > 0 && $days <= $window );
			if ( $inside && $sent < $max ) {
				$wpdb->delete($tbl_nm_ewmq, [ 'so_id' => $r->id ] );
				$wpdb->insert($tbl_nm_ewmq, [ 'so_id' => $r->id ] );
			} elseif ( ! $inside ) {
				$wpdb->delete( $tbl_nm_ewmq, array( 'so_id' => $r->id ) );
			}
		}

		self::purge_expired_certificate_files();

		if( !wp_next_scheduled( DOSF_HOOK_SCHEDULED_EVENT_EWMQ_PROCESS ) )
        {
            wp_schedule_event( time(), DOSF_SCHEDULE_INTERVAL_EWMQ_PROC_KEY_NM, DOSF_HOOK_SCHEDULED_EVENT_EWMQ_PROCESS );
        }

	} 

	public static function process_certs_expiration_queue(){
		global $wpdb;
		$tbl_nm_ewmq =  $wpdb->prefix . 'dosf_expiration_warnig_email_queue';
		$tbl_nm_shared_objs = $wpdb->prefix . 'dosf_shared_objs';
		$row_process_count = apply_filters('dosf_expiration_warning_queue_pc',5);

		if ( class_exists( 'Dosf_Series' ) ) {
			Dosf_Series::ensure_expire_warn_column();
		}
		$max    = self::expire_warn_max();
		$window = self::get_last_range_days_before_expiration();

		$isql = "SELECT 
					so.id,
					so.title,
					so.email,
					so.email2,
					so.email3,
					so.emision,
					so.expire_warn_sent,
					ewmq.id as ewmq_id
				 FROM $tbl_nm_ewmq ewmq 
				 INNER JOIN $tbl_nm_shared_objs so
				 	ON ewmq.so_id = so.id
				 LIMIT $row_process_count";

		$res = $wpdb->get_results($isql);
		if( count($res) > 0 ){
			foreach( $res as $r ){
				$days   = self::get_dosf_validity_days_before_expiration( $r->emision );
				$inside = ( $days !== null && $window !== null && $days > 0 && $days <= $window );
				if ( $inside && intval( $r->expire_warn_sent ) < $max ) {
					$args = [
						'id'   		=> $r->id,
						'serial'   	=> $r->title,
						'email'		=> $r->email,
						'email2'	=> $r->email2,
						'email3'	=> $r->email3,
						'emision'	=> $r->emision
					];
					$warn_result = self::send_expiration_warning_email( $args );
					if ( $warn_result === true ) {
						self::increment_expire_warn_sent( intval( $r->id ) );
					} elseif ( is_wp_error( $warn_result ) ) {
						self::increment_expire_warn_failed( intval( $r->id ) );
					}
				}
				$wpdb->delete($tbl_nm_ewmq,['id' => $r->ewmq_id]);
			}
		} else {
			// se retira el hook del scheduler
			$timestamp = wp_next_scheduled( DOSF_HOOK_SCHEDULED_EVENT_EWMQ_PROCESS );
			wp_unschedule_event( $timestamp, DOSF_HOOK_SCHEDULED_EVENT_EWMQ_PROCESS );
		}

	}

}
