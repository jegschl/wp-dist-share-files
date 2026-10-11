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
            'jgb_collab_access',
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
            <p>Los cambios de hoy —el máximo de avisos antes de vencer, la columna <strong>Emails Previos al Vencimiento</strong> con fecha, hora y errores, la reutilización del código de descarga y los colores de la tabla de Gestión— son de la versión <strong>1.4.4</strong>.</p>
            <p>El título de la pantalla de Gestión, el texto que aparece sobre los botones, se configura en <strong>Opciones → Sistema</strong> desde la versión <strong>1.4.5</strong>. Es independiente del texto del menú lateral.</p>

            <!-- ÍNDICE -->
            <ul>
                <li><a href="#dosf-shortcode">Shortcode público</a></li>
                <li><a href="#dosf-access">Quién puede configurar el plugin</a></li>
                <li><a href="#dosf-admin-list">Listado de certificados</a></li>
                <li><a href="#dosf-menu">Título y menú del plugin</a></li>
                <li><a href="#dosf-private-file">Archivo del certificado</a></li>
                <li><a href="#dosf-expired-files">Certificados vencidos</a></li>
                <li><a href="#dosf-status-templates">Mensajes de vigencia</a></li>
                <li><a href="#dosf-file-trash">Papelera de archivos vencidos</a></li>
                <li><a href="#dosf-download-code">Código de descarga</a></li>
                <li><a href="#dosf-emails">Plantillas de correo</a></li>
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
            <p>El comportamiento (búsqueda por RUT o por número de serie) se configura en <strong>Distribución de archivos → Gestión → Opciones</strong>. Ese botón y el popup solo los ve un administrador de WordPress.</p>

            <h2 id="dosf-access">Quién puede configurar el plugin</h2>
            <p>El menú <strong>Distribución de archivos</strong> (Gestión y esta ayuda) está disponible para quien tenga la capacidad <code>jgb_collab_access</code>: el rol <strong>Gestor de Certificados</strong> y el administrador de WordPress.</p>
            <p>En Gestión, el botón <strong>Opciones</strong> abre un popup con la configuración en cinco pestañas: <strong>Búsqueda</strong>, <strong>Vigencia</strong>, <strong>Descarga</strong>, <strong>Correos</strong> y <strong>Sistema</strong>. Búsqueda define la consulta pública. Vigencia define el plazo, los mensajes de vigente o vencido, los avisos y la papelera. Descarga define el código y su popup. Correos define los conjuntos de destinatarios, a quién llega cada mensaje y las plantillas. Sistema define el título de Gestión, el texto y el icono del menú lateral, y administra los PDF de los certificados vencidos. El botón solo se muestra si la sesión actual es de un administrador de WordPress (capacidad <code>manage_options</code>).</p>
            <div class="dosf-info">
                Un gestor de certificados sigue pudiendo crear, editar y eliminar certificados. No ve el botón <strong>Opciones</strong>. La ruta de la API que guarda esa configuración también exige <code>manage_options</code>: si alguien más la llama, WordPress la rechaza.
            </div>

            <h2 id="dosf-admin-list">Listado de certificados</h2>
            <p>En Gestión, cada serie muestra sus certificados en una tabla. Las cabeceras usan el azul del sitio (<code>#13398E</code>) y una línea naranja (<code>#FE6B00</code>). El texto usa el carbón del sitio (<code>#293133</code>). <strong>Vigente</strong> se muestra en una pastilla azul y <strong>Vencido</strong> en una pastilla naranja. La columna <strong>Título</strong> ocupa el 14&nbsp;% del ancho cuando hay fecha de emisión, y el 26&nbsp;% cuando no. Si el título no cabe, se corta con puntos suspensivos y, al pasar el mouse, un tooltip muestra el título completo. Los títulos cortos se ven enteros y no muestran tooltip.</p>
            <p>Los correos de los conjuntos van en una sola columna, <strong>Email</strong>: 14&nbsp;% con fecha de emisión y 40&nbsp;% sin ella. Solo aparecen los conjuntos que tienen direcciones. El nombre del conjunto va en negrita y los grupos se separan con un guion. Por ejemplo: <strong>Emails de colaboradores:</strong> julio@gmail.com, jegschl@gmail.com - <strong>Emails de operadores:</strong> luciano@aol.com.</p>
            <p>Los iconos de la columna <strong>Acciones</strong> muestran un tooltip: Editar certificado, Enviar código de descarga, Enviar aviso de vencimiento y Eliminar certificado. El aviso de vencimiento aparece cuando el certificado está dentro del plazo de aviso configurado y todavía no vence. La columna <strong>Emails Previos al Vencimiento</strong> muestra cuántos correos de aviso se enviaron y la fecha y hora de cada envío exitoso. La fecha usa el formato de <strong>Formato de la fecha de vigencia</strong>, más la hora y el minuto. Si algún intento falló, también indica cuántos terminaron con error. Esos datos siguen visibles cuando el certificado ya venció. Vuelven a cero solo si cambia la fecha de emisión. En <strong>Opciones → Vigencia</strong>, <strong>Avisos máximos antes de vencer</strong> limita la cola automática. Solo suma un envío sin error. El botón de la fila sigue disponible durante el plazo aunque ya se haya llegado a ese máximo.</p>
            <p>Producción y el entorno local corren <strong>PHP 8.3</strong>. El listado calcula los días de vigencia con un método estático. En PHP 8, si ese método no es estático, la petición se corta y DataTables muestra un error de Ajax.</p>

            <h2 id="dosf-menu">Título y menú del plugin</h2>
            <p>En <strong>Opciones → Sistema</strong> se cambia el título de la pantalla de Gestión y lo que WordPress muestra en el menú lateral.</p>
            <ul>
                <li><strong>Título de la pantalla de Gestión</strong> es el texto de arriba, sobre los botones. Admite hasta 80 caracteres. Si se deja vacío, se muestra <strong>Compartir o distribuir archivos</strong>. No cambia el texto del menú lateral.</li>
                <li><strong>Label para la opción del menú</strong> es un texto de hasta 40 caracteres. Si se deja vacío, el menú muestra <strong>Distribución de archivos</strong>.</li>
                <li><strong>Icono del menú</strong> es uno de los iconos estándar de WordPress. El predeterminado es el de formulario.</li>
                <li><strong>Vista previa del menú lateral</strong> queda debajo de la cuadrícula y antes de <strong>Certificados vencidos</strong>. Muestra el icono y el texto juntos, y cambia al escribir el texto o al elegir otro icono, sin guardar.</li>
            </ul>
            <p>Al guardar un cambio de título, de texto o de icono, la página se recarga para que la pantalla y el menú lateral lo reflejen. Gestión y Ayuda siguen como submenús de esa entrada.</p>

            <h2 id="dosf-private-file">Archivo del certificado</h2>
            <p>Al guardar un certificado, el plugin copia el PDF a la tabla <code>dosf_private_files</code> y lo borra de <code>wp-content/uploads</code>. La URL del adjunto deja de existir y responde 404. No hace falta configurar el servidor web: el archivo ya no está en una carpeta pública.</p>
            <p>Mientras el PDF solo está en la biblioteca de medios, antes de guardar el certificado, esa URL sigue pública. Un archivo de la biblioteca que nunca se asocia a un certificado también sigue público.</p>
            <p>Quien ingresa el código vigente recibe un enlace de un solo uso, válido 2 minutos. Ese enlace pasa por WordPress y entrega el PDF. Abrirlo de nuevo ya no funciona. La ficha pública del adjunto también responde 404.</p>
            <p>En el formulario de alta o edición hay dos botones:</p>
            <ul>
                <li><strong>Biblioteca de medios</strong> elige o sube un archivo nuevo. Al guardar, pasa a la tabla y sale de la carpeta pública.</li>
                <li><strong>Catálogo del plugin</strong> lista los PDF restaurados desde certificados vencidos. Esos archivos no tienen URL pública.</li>
            </ul>

            <h2 id="dosf-expired-files">Certificados vencidos</h2>
            <p>En <strong>Opciones → Sistema → Certificados vencidos</strong> se listan los certificados vencidos que todavía tienen el PDF en la tabla. Solo lo ve un administrador.</p>
            <table>
                <tr><th>Acción</th><th>Qué hace</th></tr>
                <tr><td>Descargar</td><td>Entrega el PDF en esa pantalla, con la sesión de administrador. No publica una URL.</td></tr>
                <tr><td>Restaurar</td><td>Lo deja en el catálogo para asignarlo al crear o editar un certificado. El certificado vencido sigue sin ofrecerse al público y la URL del adjunto no vuelve.</td></tr>
                <tr><td>Eliminar</td><td>Abre el cuadro <strong>Eliminar PDF</strong> y pide confirmación. Si se confirma, borra el PDF de <code>dosf_private_files</code>. <strong>Cancelar</strong> no borra nada. Si un certificado vigente usa el mismo archivo, el botón queda desactivado.</td></tr>
            </table>
            <div class="dosf-info">
                La tarea que mueve el adjunto a la papelera no borra esas filas. El borrado completo es el botón <strong>Eliminar</strong>, después de confirmar. Restaurar desde esta sección no es lo mismo que restaurar el adjunto en <strong>Medios → Papelera</strong>: esto solo lo devuelve al catálogo.
            </div>

            <hr>

            <h2 id="dosf-status-templates">Mensajes de vigencia en el frontend</h2>
            <p>Cuando la búsqueda pública es por número de serie y está activa la opción <strong>Coincidencias específicas</strong>, cada certificado muestra un aviso de si está vigente o vencido.</p>
            <p>Esos textos se editan en <strong>Distribución de archivos → Gestión → Opciones</strong>, pestaña <strong>Vigencia</strong>, en el bloque <strong>Mensajes del frontend</strong>. Hay un cuadro para el certificado vigente y otro para el vencido. El cambio se aplica al pulsar <strong>Guardar opciones</strong>.</p>
            <table>
                <tr><th>Marcador</th><th>Se reemplaza por</th></tr>
                <tr><td><code>{cert_title}</code></td><td>Título del certificado</td></tr>
                <tr><td><code>{serie}</code></td><td>Número de serie</td></tr>
                <tr><td><code>{estado}</code></td><td>La palabra <em>vigente</em> o <em>vencido</em>, con el color de ese estado</td></tr>
                <tr><td><code>{fecha_vencimiento}</code></td><td>Fecha de vencimiento: la fecha de emisión más el plazo configurado en «Vencimiento a los». El formato se elige en <strong>Formato de la fecha de vigencia</strong>. Por defecto es el de Chile, día-mes-año (09-10-2026).</td></tr>
            </table>
            <p>Si un cuadro se deja vacío, se usa este texto:</p>
            <pre><code>{cert_title} de la serie {serie} se encuentra actualmente {estado}.</code></pre>
            <div class="dosf-info">
                El marcador <code>{estado}</code> sigue en verde o rojo. El mensaje ya no lleva negrita forzada: el peso, la cursiva y el tamaño salen del HTML del cuadro. El resto del mensaje admite HTML de formato, en una parte del texto o en todo: <code>&lt;b&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;br&gt;</code> y <code>&lt;span style="..."&gt;</code> (color, tipo de letra, tamaño, negrita o subrayado). El mismo formato vale para el texto del campo de búsqueda por número de serie. Cualquier otra etiqueta se descarta. Los valores de los marcadores se publican como texto. Las plantillas de correo siguen siendo HTML completo.
            </div>

            <hr>

            <h2 id="dosf-file-trash">Papelera de archivos vencidos</h2>
            <p>En <strong>Distribución de archivos → Gestión → Opciones</strong> se puede pedir que el archivo de cada certificado pase a la papelera cuando ya venció.</p>
            <ol>
                <li>Marca <strong>Mover a la papelera el archivo del certificado después de vencer</strong>.</li>
                <li>Indica cuánto tiempo debe pasar <strong>desde la fecha de vencimiento</strong> (días, semanas, meses o años).</li>
                <li>Guarda con <strong>Guardar opciones</strong>.</li>
            </ol>
            <p>La fecha de vencimiento es la de emisión más el plazo de <strong>Vencimiento a los</strong>. La tarea corre sola, una vez al día, con el programador de WordPress. Llama a <code>wp_delete_attachment</code> sin borrado permanente, que es el procedimiento de WordPress para mandar un adjunto a la papelera. El PDF ya no está en la carpeta pública: está en <code>dosf_private_files</code>. Esta tarea no borra esas filas. El adjunto se restaura desde <strong>Medios → Papelera</strong>.</p>
            <div class="dosf-info">
                Esa pantalla aparece si en <code>wp-config.php</code> está <code>define( 'MEDIA_TRASH', true );</code>. Sin esa constante el adjunto igual queda en estado papelera y el archivo no se borra, pero WordPress no muestra la papelera de medios.
            </div>
            <p>Un plazo de 0 mueve el archivo en cuanto vence. Si el mismo adjunto está vinculado a varios certificados, solo pasa a la papelera cuando todos ellos ya cumplieron ese plazo.</p>
            <p>Al pasar el adjunto a la papelera o al eliminarlo, el plugin marca como no disponible cada certificado que usa ese archivo. La marca queda guardada en el registro del certificado. En el listado público, en lugar del enlace de descarga, se muestra <strong>Certificado no disponible</strong>. En la columna de archivo del admin se muestra <strong>No disponible</strong>. Intentar la descarga devuelve el mismo aviso. Si el adjunto se restaura desde la papelera, la marca se quita en todos esos certificados y la descarga vuelve a funcionar.</p>

            <hr>

            <h2 id="dosf-download-code">Código de descarga</h2>
            <p>Al pulsar el icono de descarga, el plugin envía un código al correo del certificado. Si al código vigente todavía le queda el tiempo de <strong>Reutilizar el código si todavía le quedan al menos</strong>, reenvía ese mismo código. Un valor 0 lo reutiliza durante toda la validez que le quede. Si queda menos, o el código ya venció, genera otro, guarda la nueva hora de emisión y el anterior deja de valer.</p>
            <p>La duración se configura en <strong>Opciones</strong>, en <strong>El código de descarga es válido durante</strong>. El número mínimo es 1. Las unidades son minuto, hora, día o semana. Si no hay un plazo guardado, se usa <strong>1 hora</strong>. El plazo empieza cuando se solicita la descarga y se envía el código, no cuando se creó el certificado. En la plantilla de ese correo, <code>{validez_codigo}</code> se reemplaza por ese plazo en palabras, por ejemplo <strong>30 minutos</strong> o <strong>1 hora</strong>.</p>
            <p>Al dar de alta un certificado, el correo inicial usa el código generado en ese momento y también queda registrada su hora de emisión.</p>
            <table>
                <tr><th>Situación</th><th>Qué ve quien descarga</th></tr>
                <tr><td>Código distinto al vigente</td><td>Código de descarga no válido</td></tr>
                <tr><td>Código vencido, o certificado sin hora de emisión</td><td>El código de descarga venció. Solicita la descarga nuevamente.</td></tr>
                <tr><td>Archivo en la papelera, eliminado de la tabla o marcado como no disponible</td><td>Certificado no disponible</td></tr>
                <tr><td>Código vigente</td><td>Un enlace de un solo uso, válido 2 minutos, que entrega el PDF. La URL del adjunto no sirve</td></tr>
            </table>
            <div class="dosf-info">
                Ingresar un código correcto no genera otro. Si se solicita la descarga otra vez y al código todavía le queda el tiempo indicado en Opciones, pestaña Descarga, se reenvía el mismo y sigue sirviendo. Si queda menos, se genera otro y el anterior deja de servir. Un certificado antiguo sin hora de emisión se trata como vencido hasta esa nueva solicitud.
            </div>

            <hr>

            <h2 id="dosf-emails">Plantillas de correo</h2>
            <p>En <strong>Opciones → Correos → Conjuntos de destinatarios</strong> se crean, eliminan o renombran los grupos de correos. Al empezar hay tres: colaboradores, operadores y mecánicos. En cada certificado se agregan, quitan o editan los correos de cada conjunto.</p>
            <p>En <strong>Destinatarios de los correos</strong> se marca qué conjuntos reciben cada mensaje. Por defecto, el código de descarga va a colaboradores y operadores, y el aviso de vencimiento va a mecánicos.</p>
            <p>En <strong>Opciones → Correos → Plantillas de correo</strong> un administrador puede editar el asunto y el código HTML de los dos correos del plugin. <strong>Vista previa</strong> abre un popup con el asunto y el HTML tal como están en los cuadros, también si todavía no se han guardado. No envía el correo. Los datos de ejemplo son el título Certificado de ejemplo, la serie GA000000000 y el código A1B2C3. <code>{validez_codigo}</code> usa el plazo escrito en la pestaña Descarga. El aviso de vencimiento usa 15 días, la emisión 01-10-2026 y el id 1. El cambio se guarda con <strong>Guardar opciones</strong>. Si un cuadro se deja vacío y se guarda, vuelve la plantilla que trae el plugin.</p>
            <table>
                <tr><th>Correo</th><th>Cuándo se envía</th><th>Marcadores</th></tr>
                <tr><td>Código de descarga</td><td>Al crear el certificado y cada vez que se solicita la descarga</td><td><code>{cert_title}</code>, <code>{serie}</code>, <code>{download_code}</code>, <code>{validez_codigo}</code></td></tr>
                <tr><td>Aviso de vencimiento</td><td>Dentro del plazo de aviso y antes de vencer. La cola automática se detiene al llegar al máximo de Vigencia. El botón manual sigue disponible en ese plazo. Solo un envío sin error suma al máximo y guarda su fecha y hora</td><td><code>{cert_title}</code>, <code>{serie}</code>, <code>{dias_vigencia}</code>, <code>{emision}</code>, <code>{id}</code></td></tr>
            </table>
            <div class="dosf-info">
                El HTML es el mensaje completo, con el encabezado y el pie. Los marcadores se reemplazan al enviar el correo.
            </div>

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
                <li>Si al código vigente todavía le queda el tiempo mínimo de Opciones, pestaña Descarga, el plugin reenvía ese mismo código. Si queda menos, genera uno nuevo y el anterior deja de servir. El correo sale en ambos casos y después se abre el popup de Popup Maker.</li>
                <li>El código vale solo durante el plazo definido en <strong>Opciones</strong> (<strong>El código de descarga es válido durante</strong>). Si vence, hay que solicitar la descarga otra vez.</li>
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

            <h3>Paso 3 — Elegir el popup en este plugin</h3>
            <ol class="dosf-help-steps">
                <li>Guarda el popup en Popup Maker.</li>
                <li>Ve a <strong>Distribución de archivos → Gestión</strong> y pulsa <strong>Opciones</strong>.</li>
                <li>En <strong>Popup (Popup Maker) para ingreso de código de descarga</strong>, elige el popup de la lista. Cada opción se muestra como título y número, por ejemplo <em>Descarga con código de autorización (#11927)</em>. Entran los popups publicados, en borrador, privados y pendientes. Si el popup guardado ya no existe, la lista conserva la opción <em>Popup #ID (no encontrado)</em>.</li>
                <li>Haz clic en <strong>Guardar opciones</strong>.</li>
            </ol>

            <hr>

            <!-- FILTROS -->
            <h2 id="dosf-filters">Filtros de WordPress disponibles</h2>
            <p>Agrega estos filtros en el archivo <code>functions.php</code> de tu tema activo para personalizar el comportamiento del plugin.</p>

            <h3>ID del popup (alternativa por código)</h3>
            <pre><code>add_filter( 'dosf/front/search-rut/download-code/popupmakerId', function( $id ) {
    return 11927; // Reemplaza con el ID de tu popup
} );</code></pre>

            <h3>Imagen indicadora "vigente" (círculo verde con check blanco u otra)</h3>
            <pre><code>add_filter( 'dosf/front/status-indicator/vigente/img-url', function( $url ) {
    return get_template_directory_uri() . '/images/mi-imagen-vigente.png';
} );</code></pre>

            <h3>Imagen indicadora "vencido" (círculo rojo con X u otra)</h3>
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
                <tr><td><code><?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_private_files</code></td><td>PDF de cada certificado, en trozos, fuera de la carpeta pública. <code>restored</code> indica si está en el catálogo</td></tr>
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
    `file_unavailable` tinyint(1) NOT NULL DEFAULT 0,
    `created`        timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `email`          varchar(255)  NOT NULL DEFAULT '',
    `email2`         varchar(255)  NOT NULL DEFAULT '',
    `email3`         varchar(255)  NOT NULL DEFAULT '',
    `recipient_extra` longtext     NULL,
    `download_code`  varchar(16)   NOT NULL DEFAULT '',
    `download_code_issued` datetime NULL,
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
            <div class="dosf-tip">
                <strong>Si faltan las columnas de los cambios recientes</strong> (<code>file_unavailable</code> o <code>download_code_issued</code>):
                <pre style="margin:8px 0 0;"><code>ALTER TABLE `<?php echo esc_html( $GLOBALS['wpdb']->prefix ); ?>dosf_shared_objs`
  ADD COLUMN `file_unavailable` tinyint(1) NOT NULL DEFAULT 0 AFTER `wp_file_obj_id`,
  ADD COLUMN `download_code_issued` datetime NULL AFTER `download_code`;</code></pre>
                Al cargar el plugin, si la versión de base de datos guardada no es 1.5, la migración agrega esas columnas sola. Los correos de conjuntos creados después de los tres iniciales se guardan en <code>recipient_extra</code>. La tabla <code>dosf_private_files</code> se crea sola la primera vez que el plugin carga, sin cambiar esa marca de versión. Las columnas <code>expire_warn_sent</code>, <code>expire_warn_failed</code> y <code>expire_warn_sent_at</code> también se agregan solas al usar el listado o al enviar un aviso, sin subir la versión de la base de datos. Si ya existe y le falta <code>restored</code>, el plugin agrega la columna. <code>chunk_b64</code> guarda cada trozo del PDF y <code>restored</code> vale 1 cuando el archivo está en el catálogo.
            </div>

            <p>Después de ejecutar el SQL, ve a <strong>Plugins → Plugins instalados</strong>, <strong>desactiva</strong> y vuelve a <strong>activar</strong> el plugin <code>jgb-dosf</code>.</p>

            <hr>
            <p style="color:#787c82; font-size:.9em;">jgb-dosf v<?php echo esc_html( WP_DOSF_VERSION ); ?> — <a href="https://phonix.dev" target="_blank">phonix.dev</a></p>
        </div>
        <?php
    }
}
