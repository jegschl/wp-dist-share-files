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
				'urlUpdSO'		 => rest_url( '/'. DOSF_APIREST_BASE_ROUTE .DOSF_URI_ID_UPD_SO . '/' )
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
		<form action='options.php' method='post'>

			<h2>Compartir o distribuir archivos</h2>

			<?php
			settings_fields( 'dosf' );
			do_settings_sections( 'dosf' );
			//submit_button();
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
		global $wpdb;
		$tbl_nm_dosf = $wpdb->prefix . 'dosf_shared_objs';
		$limis_prms = "10";
		$isql_select = "SELECT * FROM $tbl_nm_dosf";
		$isql_limit = "LIMIT $limis_prms";
		?>

		<div class="dosf-admin-header">
			
			<div id="add-dosf" class="action-wrapper"><i class="fas fa-plus-circle"></i>Agregar nuevo archivo para compartir</div>
			<div id="rem-dosf" class="action-wrapper"><i class="fas fa-minus-circle"></i>Remover seleccionados</div>
					
		</div>

		<div class="dosf-admin-add-so">
			<div class="fields-wrapper">
				<div class="fld-so">
					<label>Seleccionar archivo...</label>
					<button>Buscar</button>
					<div class="file-selected"></div>
				</div>
				<div class="fld-title">
					<label>Título</label>
					<input name="so_title" type="text" />
				</div>
				<div class="fld-ruts"> 
					<label>RUTs asociados</label>
					<input type="text" name="so_ruts_linked"/>
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
						<th>Id</th>
						<th>WP Obj Id</th>
						<th>Título</th>
						<th>Archivo</th>
						<th>RUTs asociados</th>
					</tr>
				</thead>
				<!--body-->
				<tfoot>
					<tr class="tr">
						<th>Seleccionar</th>
						<th>Id</th>
						<th>WP Obj Id</th>
						<th>Título</th>
						<th>Archivo</th>
						<th>RUTs asociados</th>
					</tr>
				</tfoot>
			</table>

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
                'methods'  => 'UPDATE',
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

		$isql = "SELECT 
					wdso.id,
					title,
					file_name,
					wp_file_obj_id,
					GROUP_CONCAT(wdsrl.rut) AS linked_ruts
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
				'selection'	  => ''
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

}
