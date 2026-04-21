<?php
/**
 * Subpágina de documentación/ayuda del plugin jgb-dosf.
 * Se muestra en el admin de WordPress bajo "Distribución de archivos → Ayuda".
 *
 * @package Wp_Dosf
 */

// Seguridad: abortar si se llama directamente.
if ( ! defined( 'WPINC' ) ) { die; }

class Wp_Dosf_Help_Page {

    public static function register( $loader, $plugin_admin ) {
        $loader->add_action( 'admin_menu', new self(), 'add_help_submenu' );
    }

    public function add_help_submenu() {
        add_submenu_page(
            'dosf-admin',
            'Ayuda — jgb-dosf',
            'Ayuda / Documentación',
            'manage_options',
            'dosf-help',
            array( $this, 'render' )
        );
    }

    public function render() {
        $plugin_url = plugin_dir_url( WP_DOSF_PLUGIN_PATH . '/.' );
        ?>
        <style>
            .dosf-help-wrap { max-width: 900px; font-family: -apple-system, sans-serif; }
            .dosf-help-wrap h1 { border-bottom: 2px solid #0073aa; padding-bottom: 10px; color: #1d2327; }
            .dosf-help-wrap h2 { color: #0073aa; margin-top: 32px; }
            .dosf-help-wrap h3 { color: #1d2327; margin-top: 24px; }
            .dosf-help-wrap code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: .9em; }
            .dosf-help-wrap pre { background: #1d2327; color: #f0f6fc; padding: 16px 20px; border-radius: 6px; overflow-x: auto; font-size: .88em; line-height: 1.6; }
            .dosf-help-wrap pre code { background: none; color: inherit; padding: 0; }
            .dosf-help-wrap table { border-collapse: collapse; width: 100%; margin: 12px 0; }
            .dosf-help-wrap th { background: #0073aa; color: #fff; text-align: left; padding: 8px 12px; }
            .dosf-help-wrap td { border: 1px solid #ddd; padding: 8px 12px; }
            .dosf-help-wrap tr:nth-child(even) td { background: #f6f7f7; }
            .dosf-help-wrap .dosf-alert { border-left: 4px solid #d63638; background: #fdf2f2; padding: 12px 16px; margin: 16px 0; border-radius: 0 4px 4px 0; }
            .dosf-help-wrap .dosf-tip  { border-left: 4px solid #00a32a; background: #f0f8f1; padding: 12px 16px; margin: 16px 0; border-radius: 0 4px 4px 0; }
            .dosf-help-wrap .dosf-info { border-left: 4px solid #0073aa; background: #f0f6fc; padding: 12px 16px; margin: 16px 0; border-radius: 0 4px 4px 0; }
            .dosf-help-steps { counter-reset: step; }
            .dosf-help-steps li { counter-increment: step; margin: 10px 0; }
            .dosf-help-steps li::marker { font-weight: 700; color: #0073aa; }
        </style>

        <div class="wrap dosf-help-wrap">
            <h1>📘 Documentación — Distribución de archivos (jgb-dosf)</h1>
            <p>Versión <?php echo esc_html( WP_DOSF_VERSION ); ?> | Autor: <a href="https://phonix.dev" target="_blank">Jorge Garrido</a></p>

            <!-- ÍNDICE -->
            <ul>
                <li><a href="#dosf-shortcode">Shortcode público</a></li>
                <li><a href="#dosf-popup">Integración con Popup Maker</a></li>
                <li><a href="#dosf-filters">Filtros de WordPress</a></li>
                <li><a href="#dosf-tables">Tablas de base de datos</a></li>
                <li><a href="#dosf-tables-manual">Creación manual de tablas (solución a fallos)</a></li>
            </ul>

            <hr>

            <!-- SHORTCODE -->
            <h2 id="dosf-shortcode">Shortcode público</h2>
            <p>Coloca el siguiente shortcode en la página donde los usuarios buscarán sus certificados:</p>
            <pre><code>[dosf_browser]</code></pre>
            <p>El comportamiento (búsqueda por RUT o por número de serie) se configura en <strong>Distribución de archivos → Otras opciones</strong>.</p>

            <hr>

            <!-- POPUP MAKER -->
            <h2 id="dosf-popup">Integración con Popup Maker</h2>
            <div class="dosf-alert">
                <strong>⚠ Dependencia requerida:</strong> El plugin <strong>Popup Maker</strong> debe estar instalado y activo para que la descarga con código de autorización funcione.
            </div>

            <p>El flujo es el siguiente:</p>
            <ol>
                <li>El usuario busca su certificado por RUT o número de serie.</li>
                <li>Hace clic en el icono de descarga junto a su resultado.</li>
                <li>El plugin envía un <strong>código de autorización</strong> al correo registrado y abre el popup de Popup Maker.</li>
                <li>El usuario ingresa el código en el popup y hace clic en "Descargar".</li>
                <li>Si el código es correcto, se inicia la descarga del archivo.</li>
            </ol>

            <h3>Paso 1 — Crear el popup en Popup Maker</h3>
            <ol class="dosf-help-steps">
                <li>Ve a <strong>Popup Maker → Añadir nuevo</strong>.</li>
                <li>Asígnale un título (ej: <em>Descarga con código de autorización</em>).</li>
                <li>Cambia el editor de contenido a modo <strong>Texto / HTML</strong> (no Visual).</li>
                <li>Pega el siguiente HTML en el contenido del popup:</li>
            </ol>

            <pre><code>&lt;!-- Campo oculto requerido por el plugin --&gt;
&lt;input type="hidden" id="obj-id" value=""&gt;

&lt;p style="text-align:center; font-size:1.05em; margin-bottom:18px;"&gt;
  Se ha enviado un &lt;strong&gt;código de autorización&lt;/strong&gt; al correo registrado.&lt;br&gt;
  Por favor, ingrésalo a continuación para continuar con la descarga.
&lt;/p&gt;

&lt;div style="text-align:center; margin:15px 0;"&gt;
  &lt;input
    type="text"
    id="input-download-code"
    placeholder="Código de autorización"
    autocomplete="off"
    style="font-size:1.4em; padding:8px 14px; text-align:center;
           border:1px solid #ccc; border-radius:6px; width:220px;
           letter-spacing:4px; text-transform:uppercase;"
  &gt;
&lt;/div&gt;

&lt;p id="download-code-error" class="hidden"
   style="color:red; text-align:center; margin-top:10px;"&gt;
  Código incorrecto. Por favor, verifica e intenta nuevamente.
&lt;/p&gt;

&lt;div style="text-align:center; margin-top:20px;"&gt;
  &lt;button
    id="send-download-code"
    style="background-color:#0073aa; color:#fff; padding:11px 28px;
           border:none; border-radius:6px; font-size:1em; cursor:pointer;"&gt;
    Descargar certificado
  &lt;/button&gt;
&lt;/div&gt;</code></pre>

            <div class="dosf-alert">
                <strong>Importante:</strong> Los atributos <code>id</code> de los elementos (<code>obj-id</code>, <code>input-download-code</code>, <code>send-download-code</code>, <code>download-code-error</code>) son utilizados por el JavaScript del plugin. <strong>No los modifiques.</strong>
            </div>

            <h3>Paso 2 — Ajustes recomendados del popup</h3>
            <table>
                <tr><th>Ajuste</th><th>Valor recomendado</th></tr>
                <tr><td>Tamaño</td><td>Medium (o Custom ~480px de ancho)</td></tr>
                <tr><td>Posición</td><td>Centro de la pantalla</td></tr>
                <tr><td>Auto-open</td><td>Desactivado (el plugin lo abre por JavaScript)</td></tr>
                <tr><td>Overlay</td><td>Activado</td></tr>
                <tr><td>Cerrar con ESC</td><td>Activado</td></tr>
            </table>

            <h3>Paso 3 — Registrar el ID del popup en este plugin</h3>
            <ol class="dosf-help-steps">
                <li>Guarda el popup en Popup Maker.</li>
                <li>Anota el <strong>ID numérico</strong> que aparece en la columna ID del listado de popups, o en la URL al editar: <code>?post=XXXX</code>.</li>
                <li>Ve a <strong>Distribución de archivos → Otras opciones</strong>.</li>
                <li>Ingresa el ID en el campo <strong>"ID del popup (Popup Maker) para ingreso de código de descarga"</strong>.</li>
                <li>Haz clic en <strong>Guardar otras opciones</strong>.</li>
            </ol>

            <hr>

            <!-- FILTROS -->
            <h2 id="dosf-filters">Filtros de WordPress disponibles</h2>
            <p>Agrega estos filtros en el archivo <code>functions.php</code> de tu tema activo para personalizar el comportamiento del plugin.</p>

            <h3>ID del popup (alternativa por código)</h3>
            <pre><code>add_filter( 'dosf/front/search-rut/download-code/popupmakerId', function( $id ) {
    return 11927; // Reemplaza con el ID de tu popup
} );</code></pre>

            <h3>Imagen indicadora "vigente" (manito verde u otra)</h3>
            <pre><code>add_filter( 'dosf/front/status-indicator/vigente/img-url', function( $url ) {
    // $url contiene la URL por defecto (hand-good.png del plugin)
    return get_template_directory_uri() . '/images/mi-imagen-vigente.png';
} );</code></pre>

            <h3>Imagen indicadora "vencido" (manito roja u otra)</h3>
            <pre><code>add_filter( 'dosf/front/status-indicator/vencido/img-url', function( $url ) {
    return get_template_directory_uri() . '/images/mi-imagen-vencida.png';
} );</code></pre>

            <h3>Personalizar etiquetas del menú de administración</h3>
            <pre><code>add_filter( 'dosf-admin/admin-page-title', fn( $t ) => 'Certificados de Mantención' );
add_filter( 'dosf-admin/admin-menu-title', fn( $t ) => 'Certificados' );</code></pre>

            <hr>

            <!-- TABLAS -->
            <h2 id="dosf-tables">Tablas de base de datos</h2>
            <p>El plugin crea automáticamente estas tablas al activarse (usando el prefijo configurado en <code>wp-config.php</code>):</p>
            <table>
                <tr><th>Tabla</th><th>Descripción</th></tr>
                <tr><td><code><?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_shared_objs</code></td><td>Certificados/documentos registrados</td></tr>
                <tr><td><code><?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_so_ruts_links</code></td><td>Relación certificado — RUTs autorizados</td></tr>
                <tr><td><code><?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_expiration_warnig_email_queue</code></td><td>Cola de avisos de vencimiento por email</td></tr>
            </table>

            <!-- TABLAS MANUAL -->
            <h2 id="dosf-tables-manual">Creación manual de tablas</h2>
            <div class="dosf-alert">
                <strong>¿Cuándo usar esto?</strong> Si el listado de certificados aparece vacío, no se pueden agregar registros, o ves errores de MySQL en el log, es probable que las tablas no se hayan creado correctamente al activar el plugin (puede ocurrir en hosting con permisos restrictivos o tras una migración de base de datos).
            </div>
            <p>Ejecuta el siguiente SQL en <strong>phpMyAdmin</strong> o cualquier cliente MySQL. El prefijo usado aquí es <code><?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?></code> (tomado automáticamente de tu instalación):</p>

            <pre><code>-- Tabla principal de certificados
CREATE TABLE IF NOT EXISTS `<?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_shared_objs` (
    `id`             int unsigned  NOT NULL AUTO_INCREMENT,
    `title`          varchar(256)  NOT NULL,
    `emision`        datetime      DEFAULT NULL,
    `file_name`      varchar(256)  NOT NULL,
    `wp_file_obj_id` int unsigned  NULL,
    `created`        timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `email`          varchar(255)  NOT NULL DEFAULT '',
    `email2`         varchar(255)  NOT NULL DEFAULT '',
    `email3`         varchar(255)  NOT NULL DEFAULT '',
    `download_code`  varchar(16)   NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `title_idx`     (`title`(191), `id`),
    KEY `file_name_idx` (`file_name`(191), `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de RUTs vinculados a cada certificado
CREATE TABLE IF NOT EXISTS `<?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_so_ruts_links` (
    `id`      int unsigned NOT NULL AUTO_INCREMENT,
    `so_id`   int unsigned NOT NULL,
    `rut`     varchar(13)  NOT NULL,
    `created` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `soId_idx` (`so_id`, `id`, `rut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cola de emails de aviso de vencimiento
CREATE TABLE IF NOT EXISTS `<?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_expiration_warnig_email_queue` (
    `id`    int unsigned NOT NULL AUTO_INCREMENT,
    `so_id` int          NOT NULL,
    PRIMARY KEY (`id`),
    KEY `soId_idx` (`so_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</code></pre>

            <div class="dosf-tip">
                <strong>Si solo falta la columna <code>email3</code></strong> (instalaciones creadas con v1.0.1 o anterior):
                <pre style="margin:8px 0 0;"><code>ALTER TABLE `<?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_shared_objs`
  ADD COLUMN `email3` varchar(255) NOT NULL DEFAULT '' AFTER `email2`;</code></pre>
            </div>

            <p>Después de ejecutar el SQL, ve a <strong>Plugins → Plugins instalados</strong>, <strong>desactiva</strong> y vuelve a <strong>activar</strong> el plugin <code>jgb-dosf</code>.</p>

            <hr>
            <p style="color:#787c82; font-size:.9em;">jgb-dosf v<?php echo esc_html( WP_DOSF_VERSION ); ?> — <a href="https://phonix.dev" target="_blank">phonix.dev</a></p>
        </div>
        <?php
    }
}
