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

		wp_localize_script( 
			$this->plugin_name, 
			'dosf_config',
			array(
				'urlGetSOs'		 => rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_GET . '/' ),
				'urlAddSO'		 => rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_ADD_SO . '/' ),
				'urlRemSO'		 => rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_REM_SO . '/' ),
				'urlUpdSO'		 => rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_UPD_SO . '/' ),
				'urlSndDC'		 => rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_SEND_DWNL_CD . '/' )
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

	public function file_data_set_stack_field_render(){
		wp_enqueue_media();
		$plus_opts_settings = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);
		?>

		<div class="dosf-admin-header">
			
			<div id="add-dosf" class="action-wrapper"><span class="dashicons dashicons-plus-alt"></span>Agregar nuevo archivo para compartir</div>
			<div id="rem-dosf" class="action-wrapper"><span class="dashicons dashicons-dismiss"></span>Remover seleccionados</div>
					
		</div>

		<div class="dosf-admin-add-so" style="display: none;">
			<div class="fields-wrapper">
				<div class="fld-so">
					<label>Seleccionar archivo</label>
					<button class="dosf-button" id="browse-file">Buscar...</button>
					<div class="file-selected" id="dosf-file-selectd"></div>
					<input type='hidden' name='dosf_fl_attachment_id' id='dosf_attachment_id' value='' />
				</div>
				<div class="fld-title">
					<label>Título</label>
					<input name="dosf_so_title" id="dosf_so_title" type="text" />
				</div>
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
					<label>Correo electrónico</label>
					<input 
						type="text" 
						id="dosf_so_email"
						name="dosf_so_email"
						placeholder="Ingrese email para enviar código de descarga"
					>
				</div>
			</div>
			<div class="actions-wrapper">
				<div class="save"><button>Guardar</button></div>
				<div class="cancel"><button>Cancelar</button></div>
			</div>
		</div>

		<div id="<?=HTML_DOSF_ID?>">
			
			<table id="tabla" class="display" style="width:100%">
				
				<thead class="thead">
					<tr class="tr">
						<th>Seleccionar</th>						
						<th>Título</th>
						<th>Archivo</th>
						<th>RUTs asociados</th>
						<th>Correo electrónico</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<!--body-->
				<tfoot>
					<tr class="tr">
						<th>Seleccionar</th>
						<th>Título</th>
						<th>Archivo</th>
						<th>RUTs asociados</th>
						<th>Correo electrónico</th>
						<th>Acciones</th>
					</tr>
				</tfoot>
			</table>

		</div>

		<div class="dosf-plus-options">
			<h2>Otras opciones</h2>
			<div class="input">
				<input id="especific-match" type="checkbox" name="especific-match">
				<label for="especific-match">Coincidencias específicas en las búsquedas del frontend.</label>
			</div>

			<div class="input">
				<input id="use-serial-numbers" type="checkbox" name="use-serial-numbers">
				<label for="use-serial-numbers">Utilizar números de serie únicos.</label>
			</div>

			<div class="input">
				<input id="use-issue-date" type="checkbox" name="use-issue-date">
				<label for="use-issue-date">Utilizar fecha de emisión.</label>
			</div>

			<div class="input">
				<label for="expire-period-nmb">Vencimiento a los</label>
				<input id="expire-period-nmb" type="number" name="expire-period-nmb">
				<select id="expire-period-unit" name="expire-period-unit">>
					<option value="">Seleccione intervalo</option>
					<option value="day">día(s)</option>
					<option value="week">semana(s)</option>
					<option value="month">mes(es)</option>
					<option value="year">año(s)</option>
				</select>
			</div>
			<input type="hidden" name="plus-options-update-nonce" id="plus-options-update-nonce" value="<?= wp_create_nonce(DOSF_NONCE_ACTION_PLUS_OPTS_UPDATE) ?>">
			<div class="dosf-plus-options-actions">
				<button id="dosf-plus-options-save">Guardar otras opciones</button>
			</div>
			<div id="after-try-update-res-placeholder" class="hidden">Respuesta</div>
		</div>
		
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
            '/'.DOSF_URI_ID_REM_SO.'/(?P<dosf_id>\d+)',
            array(
                'methods'  => 'DELETE',
                'callback' => array(
                    $this,
                    'receive_remove_dosf'
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
            '/'.DOSF_URI_ID_UPD_PLUS_OPTIONS,
            array(
                'methods'  => 'POST',
                'callback' => array(
                    $this,
                    'receive_plus_options'
                ),
                'permission_callback' => '__return_true',
            )
		);
    }

	public function send_so_data($r){
		global $wpdb;
		
		$limit = '';
		if(isset($_GET['length']) && $_GET['length']>0)
            $limit = ' LIMIT ' . $_GET['start'] . ',' . $_GET['length'];
        
		$where = '';
        if(isset($_GET['search']) && !empty($_GET['search'])){
            $sv = $_GET['search']['value'];
            $where  = ' WHERE file_name LIKE "%'. $sv . '%"';
            $where .= ' OR wdsrl.rut LIKE "%' . $sv . '%"';
            $where .= ' OR title LIKE "%' . $sv . '%"';
        }

		$isql = "SELECT SQL_CALC_FOUND_ROWS
					wdso.id,
					title,
					file_name,
					wp_file_obj_id,
					GROUP_CONCAT(wdsrl.rut) AS linked_ruts,
					email
				FROM wp_dosf_shared_objs wdso 
				JOIN wp_dosf_so_ruts_links wdsrl 
					ON wdso.id = wdsrl.so_id 
				$where 
				GROUP BY wdso.id
				$limit";
		$qry = 'SELECT FOUND_ROWS() AS total_rcds';
		
		$sos = $wpdb->get_results($isql, OBJECT);
		$frs = $wpdb->get_row($qry, OBJECT);
        
		$rc = array();
        
        foreach($sos as $c){
            
            $rc[] = array(
				'DT_RowId'	  => $c->id,
				'id'		  => $c->id,
                'title'       => $c->title,
                'file_name'   => $c->file_name,
                'wp_obj_id'   => $c->wp_file_obj_id,
                'linked_ruts' => $c->linked_ruts,
				'email'		  => $c->email,
				'selection'	  => '',
				'actions'	  => ''
            );
        }

        if($sos && empty($wpdb->last_error) ){
            $res = array(
                'draw' => $_GET['draw'],
                "recordsTotal" =>  intval($frs->total_rcds),
                "recordsFiltered" => intval($frs->total_rcds),
                'data' => $rc
            );
            $response = new WP_REST_Response( $res );
            $response->set_status( 200 );
            
        } else {
			$res = array(
                'draw' => $_GET['draw'],
                "recordsTotal" =>  intval($frs->total_rcds),
                "recordsFiltered" => intval($frs->total_rcds),
                'data' => array(),
				//'error' => new WP_Error( 'cant-read-dosf-sos', __( 'Can\'t get shared objects', 'wp-dosf' ), array( 'status' => 500 ) )
            );
            $response = new WP_REST_Response( $res );
            $response->set_status( 200 );
        }
        return $response;
	}

	private function generate_download_code($length = 6, $chars = null){
	
		if(is_null($chars))
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$string = '';

		for ($i = 0; $i < $length; $i++) {
			$string .= $characters[mt_rand(0, strlen($characters) - 1)];
		}

		return $string;

	}

	public function receive_plus_options($r){
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

		
		if ( ! wp_verify_nonce(  $r['nonce'], DOSF_NONCE_ACTION_PLUS_OPTS_UPDATE ) ) {
			$r['error'] 	= true;
			$r['err_code']	= DOSF_PLUS_OPTS_UPDATE_ERR_INVALID_NONCE;
			return $r;
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
		// validaciones del lado del server.
		global $wpdb;
		$tbl_nm_shared_objs = $wpdb->prefix . 'dosf_shared_objs';
		$tbl_nm_so_ruts_links = $wpdb->prefix . 'dosf_so_ruts_links'; 
		$dowld_code = $this->generate_download_code();
		$wpdb->insert(
			$tbl_nm_shared_objs,
			array(
				'title' 		 => $data['title'],
				'file_name' 	 => $data['file_name'],
				'wp_file_obj_id' => $data['wp_obj_file_id'],
				'email'			 => $data['email'],
				'download_code'  => $dowld_code
			)
		);
		$so_id = $wpdb->insert_id;
		if( $so_id !== false ){
			foreach($data["linked_ruts"] as $rut){
				$wpdb->insert(
					$tbl_nm_so_ruts_links,
					array(
						'so_id' => intval($so_id),
						'rut' 	=> $rut
					)
				);
			}
		} 
		$wp_upload_dir_info = wp_upload_dir(); 
		$attachment_id = intval($data['wp_obj_file_id']);
		$file_path = get_attached_file($attachment_id);
		$dce_args = array(
						'email' => $data['email'],
						'download_code' => $dowld_code,
						'file' => $file_path
					);


		$mail_sent_res = $this->send_download_code_email($dce_args);

		return [
			'dosfAddNew_post_status' => 'ok',
			'dosfAddNew_email_sent' => $mail_sent_res
		];
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
						download_code

					FROM wp_dosf_shared_objs wdso 
					
					$where ";
			$qry = 'SELECT FOUND_ROWS() AS total_rcds';
			
			$sos = $wpdb->get_results($isql, OBJECT);
			
			$rc = array();
			
			foreach($sos as $c){
				
				$rc[] = array(
					
					'file_name'   => $c->file_name,
					'wp_obj_id'   => $c->wp_file_obj_id,
					'email'		  => $c->email,
					'download_code'	  => $c->download_code
					
				);
			}

			if(count($rc) == 1){
				$attachment_id = intval($rc[0]['wp_obj_id']);
				$file_path = get_attached_file($attachment_id);

				$args = array(
					'email' => $rc[0]['email'],
					'download_code' => $rc[0]['download_code'],
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

	public function send_download_code_email($args){
		if(!isset($args['email']) || !isset($args['download_code']) )
			return false;

		if( empty($args['email']) || empty($args['download_code']) )
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
			$email = $args['email'];
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
			$subject = apply_filters(
							'dosf_eml_new_obj_eml_subject',
							'medicoatuomicilio.cl :: Descarga tu nuevo resultado de examen'
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
			$mail_sent_res = wp_mail($email,$subject,$content,$header,$attachments);
		}

		return $mail_sent_res;
	}

}
