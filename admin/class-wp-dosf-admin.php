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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-dosf-admin.css', array(), $this->version, 'all' );
		
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
				'useIssueDate'	 			=> isset( $this->plus_options['use-issue-date'] ) && $this->plus_options['use-issue-date'],
				'expirityBefDayC'			=> self::get_dosf_validity_total_days(),
				'lastDayRangeForExprWrng'	=> self::get_last_range_days_before_expiration()
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

	public function dosf_menu() {
		add_menu_page( 
			apply_filters('dosf-admin/admin-page-title','Distribución de archivos'), 
			apply_filters('dosf-admin/admin-menu-title','Distribución de archivos'), 
			'jgb_collab_access', 
			'dosf-admin',  //'dosf/dosf-admin.php', 
			array($this,'dosf_admin_page'), 
			'dashicons-forms', 
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

			<h2>Compartir o distribuir archivos</h2>
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

		<div class="dosf-admin-header">
			
			<div id="add-dosf" class="action-wrapper"><span class="dashicons dashicons-plus-alt"></span>Agregar certificado</div>
			<div id="rem-dosf" class="action-wrapper disabled"><span class="dashicons dashicons-dismiss"></span>Remover seleccionados</div>
					
		</div>

		<div class="dosf-admin-add-so" style="display: none;">
			<div class="title"></div>
			<div class="fields-wrapper">
				<div class="fld-so">
					<label>Seleccionar archivo</label>
					<button class="dosf-button" id="browse-file">Buscar...</button>
					<div class="file-selected" id="dosf-file-selectd"></div>
					<input type='hidden' name='dosf_fl_attachment_id' id='dosf_attachment_id' value='' />
				</div>
				<div class="fld-serie">
					<label>Número de serie</label>
					<input name="dosf_so_serie" id="dosf_so_serie" type="text" />
				</div>
				<div class="fld-title">
					<label>Título del certificado</label>
					<input name="dosf_so_cert_title" id="dosf_so_cert_title" type="text" />
				</div>
				
				<?php if( isset( $plus_options['use-issue-date'] ) && $plus_options['use-issue-date'] ) : ?>
				<div class="fld-emision">
					<label>Emisión</label>
					<input name="dosf_so_emision" id="dosf_so_emision" type="datetime" />
				</div>
				<?php endif; ?>

				<div class="fld-ruts"> 
					<label>RUTs asociados</label>
					<input 
						type="text" 
						id="dosf_so_ruts_linked"
						name="dosf_so_ruts_linked"
						placeholder="Separar RUTs con comas y sin puntos ni guines"
					>
				</div>
				<div class="fld-email"> 
					<label>Emails colarboradores</label>
					<input 
						type="text" 
						id="dosf_so_email"
						name="dosf_so_email"
						placeholder="Ingrese emails de colaboradores para enviar código de descarga"
					>
				</div>

				<div class="fld-email2"> 
					<label>Emails operadores</label>
					<input 
						type="text" 
						id="dosf_so_email2"
						name="dosf_so_email2"
						placeholder="Ingrese emails de operadores para enviar código de descarga"
					>
				</div>

				<div class="fld-email3"> 
					<label>Emails Mecánico</label>
					<input 
						type="text" 
						id="dosf_so_email3"
						name="dosf_so_email3"
						placeholder="Ingrese emails de mecánicos para enviar aviso de vencimiento de mantención"
					>
				</div>

			</div>
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
		<div class="dosf-plus-options">
			<h2>Otras opciones</h2>
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
	<p class="description">El plazo empieza cuando se solicita la descarga y se envía el código. Pasado ese tiempo hay que solicitar la descarga otra vez.</p>
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
			<p class="description">Texto que ve quien busca un certificado. Marcadores: <code>{cert_title}</code> título, <code>{serie}</code> número de serie, <code>{estado}</code> la palabra vigente o vencido y <code>{fecha_vencimiento}</code> la fecha de vencimiento, calculada sumando el plazo de vigencia a la fecha de emisión.</p>
			<div class="input">
				<label for="status-tpl-vigente">Certificado vigente</label>
				<textarea id="status-tpl-vigente" name="status-tpl-vigente" rows="3"><?= esc_textarea( $status_tpl_vigente ) ?></textarea>
			</div>
			<div class="input">
				<label for="status-tpl-vencido">Certificado vencido</label>
				<textarea id="status-tpl-vencido" name="status-tpl-vencido" rows="3"><?= esc_textarea( $status_tpl_vencido ) ?></textarea>
			</div>
		</div>
			<input type="hidden" name="plus-options-update-nonce" id="plus-options-update-nonce" value="<?= wp_create_nonce(DOSF_NONCE_ACTION_PLUS_OPTS_UPDATE) ?>">
			<div class="dosf-plus-options-actions">
				<button id="dosf-plus-options-save">Guardar otras opciones</button>
				<div class="processing hidden"><img src="<?= $processing_img_src ?>"></div>
			</div>
			<div id="after-try-update-res-placeholder" class="hidden">Respuesta</div>
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

	function get_dosf_validity_total_days(){
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

	public static function certificate_expiration_label( $plus_options, $emision ) {
		$date = self::certificate_expiration_datetime( $plus_options, $emision );
		if ( ! $date ) {
			return '';
		}
		$format = get_option( 'date_format' );
		if ( ! is_string( $format ) || $format === '' ) {
			$format = 'd/m/Y';
		}
		return date_i18n( $format, $date->getTimestamp() );
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

		return strtr( nl2br( esc_html( $tpl ) ), $repl );
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
				$data[ $tpl_key ] = sanitize_textarea_field( $data[ $tpl_key ] );
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

		$data['delete-file-after-expire'] = ! empty( $data['delete-file-after-expire'] );
		$data['delete-file-after-nmb'] = isset( $data['delete-file-after-nmb'] ) ? max( 0, intval( $data['delete-file-after-nmb'] ) ) : 0;
		$delete_units = array( 'day', 'week', 'month', 'year' );
		if ( empty( $data['delete-file-after-unit'] ) || ! in_array( $data['delete-file-after-unit'], $delete_units, true ) ) {
			$data['delete-file-after-unit'] = 'day';
		}

		//DOSF_WP_OPT_NM_PLUS_OPTIONS
		if( !update_option(DOSF_WP_OPT_NM_PLUS_OPTIONS,$data) ){
			$r['error'] 	= true;
			$r['err_code']	= DOSF_PLUS_OPTS_UPDATE_ERR_DB;
			return $r;
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
			'dosfExprtWrngSend_email_sent' => $mail_sent_res
		];
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

		if( empty( $args['email'] ) && empty( $args['email2'] )	)
			return false;

		$header_template_path = apply_filters(
									'osf_eml_tpl_new_obj_header',
									WP_DOSF_PLUGIN_PATH . '/templates/emails/email_header.tpl'
								);
		
		$content_template_path = apply_filters(
									'dosf_eml_tpl_new_obj_content_path',
									WP_DOSF_PLUGIN_PATH . '/templates/emails/email_new_dosf_content.tpl'
								);

		$footer_template_path = apply_filters(
									'dosf_eml_tpl_new_obj_footer',
									WP_DOSF_PLUGIN_PATH . '/templates/emails/email_footer.tpl'
								);

		$mail_sent_res = false;
		if(file_exists($content_template_path)){
			$email = '';

			if( !empty( $args['email'] ) )
				$email .= $args['email'];

			if( !empty( $args['email'] ) && !empty( $args['email2'] ) )
				$email .= ',';
			
			if( !empty( $args['email2'] ) )
				$email .= $args['email2'];

			$content = '';
			if(file_exists($header_template_path)){
				$content  .= file_get_contents($header_template_path);
			}
			$content .= file_get_contents($content_template_path);
			if(file_exists($footer_template_path)){
				$content .= file_get_contents($footer_template_path);
			}
			$content = str_replace(
							'{download_code}',
							$args['download_code'],
							$content
						);
			$content = str_replace( '{serie}', isset( $args['serial'] ) ? $args['serial'] : '', $content );
			$content = str_replace( '{cert_title}', isset( $args['cert_title'] ) ? $args['cert_title'] : '', $content );
			$subject = apply_filters(
							'dosf_eml_new_obj_eml_subject',
							'Grua PM :: Código de descarga ' . ( isset( $args['cert_title'] ) ? $args['cert_title'] : '' ) . ' serie ' . ( isset( $args['serial'] ) ? $args['serial'] : '' )
						);

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
			
		if(!isset($args['email3']) || !isset($args['emision']) )
			return false;

		if( empty($args['email3']) || empty($args['emision']) )
			return false;

		if( Wp_Dosf_Admin::get_dosf_validity_days_before_expiration( $args['emision'] ) > Wp_Dosf_Admin::get_last_range_days_before_expiration() ){
			return false;
		}

		$header_template_path = apply_filters(
									'osf_eml_tpl_expiration_warning_header',
									WP_DOSF_PLUGIN_PATH . '/templates/emails/email_header.tpl'
								);
		
		$content_template_path = apply_filters(
									'dosf_eml_tpl_expiration_warning_content_path',
									WP_DOSF_PLUGIN_PATH . '/templates/emails/email_expiration_warning.tpl'
								);

		$footer_template_path = apply_filters(
									'dosf_eml_tpl_expiration_warning_footer',
									WP_DOSF_PLUGIN_PATH . '/templates/emails/email_footer.tpl'
								);

		$mail_sent_res = false;
		if(file_exists($content_template_path)){
			$email  = $args['email3'];
			$email = apply_filters( 'dosf_eml_expiration_warning_str_dest_recipts', $email );
			$content = '';
			if(file_exists($header_template_path)){
				$content  .= file_get_contents($header_template_path);
			}
			$content .= file_get_contents($content_template_path);
			if(file_exists($footer_template_path)){
				$content .= file_get_contents($footer_template_path);
			}

			/* $rc[] = array(
					
					'id'   		=> $c->id,
					'serial'   	=> $c->title,
					'email'		=> $c->email,
					'email2'	=> $c->email2,
					'email3'	=> $c->email3,
					'emision'	=> $c->emision
					
				); */
			$content = str_replace(
							'{id}',
							$args['id'],
							$content
						);
			if ( ! empty( $args['id'] ) && class_exists( 'Dosf_Series' ) ) {
			$ctx = Dosf_Series::context( intval( $args['id'] ) );
			if ( $ctx ) {
				$args['serial'] = $ctx->serie;
				$args['cert_title'] = $ctx->cert_title;
			}
		}
			$content = str_replace(
							'{serie}',
							$args['serial'],
							$content
						);
			$content = str_replace(
							'{cert_title}',
							isset( $args['cert_title'] ) ? $args['cert_title'] : '',
							$content
						);
			$content = str_replace(
							'{emision}',
							$args['emision'],
							$content
						);
			$content = str_replace(
						'{dias_vigencia}',
						self::get_dosf_validity_days_before_expiration($args['emision']),
						$content
					);

			$subject = apply_filters(
							'dosf_eml_expiration_warning_subject',
							'Grua PM :: ' . ( isset( $args['cert_title'] ) ? $args['cert_title'] : 'Certificado' ) . ' de la serie '.$args['serial'].' próximo a vencer'
						);

			$header = array('Content-Type: text/html; charset=UTF-8');
			$header = apply_filters(
						'dosf_eml_expiration_warning_eml_header',
						$header
					);
			
			$mail_sent_res = wp_mail($email,$subject,$content,$header);
		}

		return $mail_sent_res;
	}

	public static function verify_certs_expiration(){
		global $wpdb;
		$tbl_nm_shared_objs = $wpdb->prefix . 'dosf_shared_objs';
		$tbl_nm_ewmq =  $wpdb->prefix . 'dosf_expiration_warnig_email_queue';

		$isql = "SELECT id, emision FROM $tbl_nm_shared_objs";
		$results = $wpdb->get_results( $isql );

		foreach( $results as $r ){
			if( Wp_Dosf_Admin::get_dosf_validity_days_before_expiration( $r->emision ) <= wp_Dosf_Admin::get_last_range_days_before_expiration() ){
				$wpdb->delete($tbl_nm_ewmq, [ 'so_id' => $r->id ] );
				$wpdb->insert($tbl_nm_ewmq, [ 'so_id' => $r->id ] );
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

		$isql = "SELECT 
					so.id,
					so.title,
					so.email,
					so.email3,
					so.emision,
					ewmq.id as ewmq_id
				 FROM $tbl_nm_ewmq ewmq 
				 INNER JOIN $tbl_nm_shared_objs so
				 	ON ewmq.so_id = so.id
				 LIMIT $row_process_count";

		$res = $wpdb->get_results($isql);
		if( count($res) > 0 ){
			foreach( $res as $r ){
				$args = [
					'id'   		=> $r->id,
					'serial'   	=> $r->title,
					'email'		=> $r->email,
					'email3'	=> $r->email3, //mecánico
					'emision'	=> $r->emision
				];
				Wp_Dosf_Admin::send_expiration_warning_email( $args );
				$wpdb->delete($tbl_nm_ewmq,['id' => $r->ewmq_id]);
			}
		} else {
			// se retira el hook del scheduler
			$timestamp = wp_next_scheduled( DOSF_HOOK_SCHEDULED_EVENT_EWMQ_PROCESS );
			wp_unschedule_event( $timestamp, DOSF_HOOK_SCHEDULED_EVENT_EWMQ_PROCESS );
		}

	}

}
