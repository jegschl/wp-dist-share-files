# jgb-dosf — Dist or Share Files with WordPress

Plugin para **compartir y distribuir documentos** con control de vencimiento, protegidos por código de autorización enviado por email. Desarrollado por [Jorge Garrido](https://phonix.dev).

---

## Características

- Gestión de certificados/documentos compartidos desde el panel de administración
- Búsqueda pública por **RUT** o **número de serie** del equipo
- Control de **fecha de emisión y vencimiento** con indicador visual (vigente / vencido)
- Descarga protegida mediante **código de autorización enviado por correo electrónico**. Cada solicitud de descarga genera un código nuevo y anula el anterior. El plazo de validez (minuto, hora, día o semana) se configura en **Opciones**. El PDF no queda en una URL pública: se guarda en la base de datos y solo se entrega después de un código vigente
- En **Opciones → Sistema → Certificados vencidos** un administrador descarga ese PDF, lo restaura al catálogo o lo borra de la tabla. El catálogo se elige al crear o editar un certificado, junto con la biblioteca de medios
- Papelera automática del archivo vencido, con un plazo de gracia desde el vencimiento, y marca de archivo no disponible si el adjunto se elimina o se mueve a la papelera
- Avisos automáticos de vencimiento próximo por email. Los conjuntos de destinatarios se crean, renombran y asignan a cada correo
- Integración con **Popup Maker**: el popup se elige en una lista, no escribiendo su ID
- La configuración se abre con el botón **Opciones** y solo la ve un administrador de WordPress. El gestor de certificados sigue administrando los certificados
- Textos públicos con formato HTML acotado, plantillas de correo en HTML completo y vista previa del correo antes de guardarlo
- Menú lateral con texto e icono configurables, y listado de certificados con el título recortado y una sola columna de emails
- Filtros de WordPress para personalizar imágenes y comportamientos

---

## Requisitos

| Componente | Versión mínima |
|---|---|
| WordPress | 5.8+ |
| PHP | 7.4+ (recomendado 8.x) |
| Plugin: Popup Maker | 1.x — **requerido** para la descarga con código |

---

## Instalación

1. Subir la carpeta `jgb-dosf` a `/wp-content/plugins/`
2. Activar el plugin desde **Plugins → Plugins instalados**
3. Instalar y activar el plugin **[Popup Maker](https://wordpress.org/plugins/popup-maker/)**
4. Crear el popup de código de autorización (ver sección siguiente)
5. Elegir el popup en **Distribución de archivos → Gestión → Opciones**
6. Agregar el shortcode `[dosf_browser]` en la página pública de certificados

---

## Configuración del Popup de Popup Maker

El plugin utiliza **Popup Maker** para mostrar el formulario donde el usuario ingresa el código de autorización recibido por email al hacer clic en "Descargar certificado".

### Paso 1 — Crear el popup

1. Ve a **Popup Maker → Añadir nuevo**
2. Asígnale el título: `Descarga con código de autorización` (o el que prefieras)
3. Asegúrate de editar el contenido en modo **Texto/HTML** (no Visual/Gutenberg)
4. Pega el siguiente HTML en el contenido:

```html
<!-- Campo oculto que almacena el ID interno del certificado (requerido por el plugin) -->
<input type="hidden" id="obj-id" value="">

<p style="text-align:center; font-size:1.05em; margin-bottom:18px;">
  Se ha enviado un <strong>código de autorización</strong> al correo registrado.<br>
  Por favor, ingrésalo a continuación para continuar con la descarga.
</p>

<div style="text-align:center; margin: 15px 0;">
  <input
    type="text"
    id="input-download-code"
    placeholder="Código de autorización"
    autocomplete="off"
    style="font-size:1.4em; padding:8px 14px; text-align:center;
           border:1px solid #ccc; border-radius:6px; width:220px;
           letter-spacing:4px; text-transform:uppercase;"
  >
</div>

<!-- Mensaje de error (oculto por defecto, el plugin lo muestra si el código es incorrecto) -->
<p id="download-code-error" class="hidden"
   style="color:red; text-align:center; margin-top:10px;">
  Código incorrecto. Por favor, verifica e intenta nuevamente.
</p>

<div style="text-align:center; margin-top:20px;">
  <button
    id="send-download-code"
    style="background-color:#0073aa; color:#fff; padding:11px 28px;
           border:none; border-radius:6px; font-size:1em; cursor:pointer;">
    Descargar certificado
  </button>
</div>
```

> **Nota:** Los `id` de los elementos (`obj-id`, `input-download-code`, `send-download-code`, `download-code-error`) son requeridos por el JavaScript del plugin. **No los cambies.**

### Paso 2 — Configurar ajustes del popup

En la pestaña **Popup Settings** del popup creado:

| Ajuste | Valor recomendado |
|---|---|
| Tamaño | Medium (o Custom ~480px) |
| Posición | Centro de la pantalla |
| Auto-open | Desactivado |
| Overlay | Activado |
| Cerrar con ESC | Activado |

### Paso 3 — Elegir el popup en el plugin

1. Guarda el popup
2. Ve a **Distribución de archivos → Gestión** y pulsa **Opciones**
3. En **Popup (Popup Maker) para ingreso de código de descarga**, elige el popup de la lista. Cada opción se muestra como título y número, por ejemplo `Descarga con código de autorización (#11927)`. Entran los popups publicados, en borrador, privados y pendientes. Si el popup guardado ya no existe, la lista conserva `Popup #ID (no encontrado)`
4. Haz clic en **Guardar opciones**

---

## Shortcode público

```
[dosf_browser]
```

Agrega este shortcode en la página donde los usuarios buscarán sus certificados. El plugin mostrará un formulario de búsqueda por RUT o número de serie según la configuración en "Opciones".

## Quién puede configurar el plugin

El menú **Distribución de archivos** (Gestión y Ayuda) está disponible para quien tenga la capacidad `jgb_collab_access`: el rol **Gestor de Certificados** y el administrador de WordPress.

En Gestión, el botón **Opciones** abre un popup con la configuración en cinco pestañas: **Búsqueda**, **Vigencia**, **Descarga**, **Correos** y **Sistema**. Búsqueda define la consulta pública. Vigencia define el plazo, los mensajes de vigente o vencido, los avisos y la papelera. Descarga define el código y su popup. Correos define los conjuntos de destinatarios, a quién llega cada mensaje y las plantillas. Sistema define el texto y el icono del menú lateral, y administra los PDF de los certificados vencidos. El botón solo se muestra si la sesión actual es de un administrador de WordPress (capacidad `manage_options`).

Un gestor de certificados sigue pudiendo crear, editar y eliminar certificados. No ve el botón **Opciones**. La ruta de la API que guarda esa configuración también exige `manage_options`: si alguien más la llama, WordPress la rechaza.

## Listado de certificados

En Gestión, cada serie muestra sus certificados en una tabla. La columna **Título** ocupa el 20 % del ancho cuando hay fecha de emisión, y el 26 % cuando no. Si el título no cabe, se corta con puntos suspensivos y, al pasar el mouse, un tooltip muestra el título completo. Los títulos cortos se ven enteros y no muestran tooltip.

Los correos de los conjuntos van en una sola columna, **Email**, que es la más ancha: 30 % con fecha de emisión y 40 % sin ella. Solo aparecen los conjuntos que tienen direcciones. El nombre del conjunto va en negrita y los grupos se separan con un guion. Por ejemplo: **Emails de colaboradores:** julio@gmail.com, jegschl@gmail.com - **Emails de operadores:** luciano@aol.com.

Los iconos de **Acciones** muestran un tooltip: Editar certificado, Enviar código de descarga, Enviar aviso de vencimiento y Eliminar certificado. El aviso de vencimiento solo aparece cuando el certificado está dentro del plazo de aviso.

## Menú del plugin

En **Opciones → Sistema** se cambia lo que WordPress muestra en el menú lateral.

**Label para la opción del menú** es un texto de hasta 40 caracteres. Si se deja vacío, el menú muestra **Distribución de archivos**.

**Icono del menú** es uno de los iconos estándar de WordPress. El predeterminado es el de formulario. Una vista previa muestra el icono y el texto juntos. Al guardar un cambio de texto o de icono, la página se recarga para que el menú lateral lo refleje. Gestión y Ayuda siguen como submenús de esa entrada.

## Archivo del certificado

Al guardar un certificado, el plugin copia el PDF a la tabla `{prefix}dosf_private_files` y lo borra de `wp-content/uploads`. La URL del adjunto deja de existir: responde 404. No hace falta una regla en Apache o nginx, porque el archivo ya no está en una carpeta pública.

Mientras el PDF solo está en la biblioteca de medios, antes de guardar el certificado, esa URL sigue pública. Un archivo de la biblioteca que nunca se asocia a un certificado también sigue público.

Quien ingresa el código vigente recibe un enlace de un solo uso, válido 2 minutos. Ese enlace pasa por WordPress y entrega el PDF. Abrirlo de nuevo ya no funciona. La ficha pública del adjunto también responde 404.

En el formulario de alta o edición hay dos botones:

- **Biblioteca de medios** elige o sube un archivo nuevo. Al guardar, pasa a la tabla y sale de la carpeta pública.
- **Catálogo del plugin** lista los PDF restaurados desde certificados vencidos. Esos archivos no tienen URL pública.

## Certificados vencidos

En **Opciones → Sistema → Certificados vencidos** se listan los certificados vencidos que todavía tienen el PDF en la tabla. Solo lo ve un administrador.

| Acción | Qué hace |
|---|---|
| Descargar | Entrega el PDF en esa pantalla, con la sesión de administrador. No publica una URL. |
| Restaurar | Lo deja en el catálogo para asignarlo al crear o editar un certificado. El certificado vencido sigue sin ofrecerse al público y la URL del adjunto no vuelve. |
| Eliminar | Borra el PDF de `dosf_private_files`. Si un certificado vigente usa el mismo archivo, el botón queda desactivado. |

La tarea que mueve el adjunto a la papelera no borra esas filas. El borrado completo es el botón **Eliminar**. Restaurar desde esta sección no es lo mismo que restaurar el adjunto en **Medios → Papelera**: esto solo lo devuelve al catálogo.

## Mensajes de vigencia en el frontend

Con la búsqueda por número de serie y la opción **Coincidencias específicas** activa, cada resultado indica si el certificado está vigente o vencido.

Esos textos se editan en **Distribución de archivos → Gestión → Opciones**, pestaña **Vigencia**, dentro de **Mensajes del frontend**. Hay un cuadro para el certificado vigente y otro para el vencido. Hay que pulsar **Guardar opciones**.

| Marcador | Se reemplaza por |
|---|---|
| `{cert_title}` | Título del certificado |
| `{serie}` | Número de serie |
| `{estado}` | La palabra vigente o vencido, con su color |
| `{fecha_vencimiento}` | Fecha de vencimiento: emisión más el plazo de «Vencimiento a los». El formato se elige en **Formato de la fecha de vigencia**. Por defecto es el de Chile, día-mes-año (09-10-2026). |

Si un cuadro queda vacío, el texto usado es:

```
{cert_title} de la serie {serie} se encuentra actualmente {estado}.
```

`{estado}` conserva el color verde o rojo. El mensaje ya no lleva negrita forzada: el peso, la cursiva y el tamaño salen del HTML del cuadro. El resto del mensaje, y también el texto del campo de búsqueda por número de serie, admite HTML de formato en una parte del texto o en todo: `<b>`, `<strong>`, `<u>`, `<i>`, `<em>`, `<br>` y `<span style="...">` para color, tipo de letra, tamaño, negrita o subrayado. El resto de etiquetas se descarta. Los valores que sustituyen a los marcadores se publican como texto. Las plantillas de correo siguen siendo HTML completo y no usan esta lista.

## Plantillas de correo

En **Opciones → Correos → Conjuntos de destinatarios** se crean, eliminan o renombran los grupos de correos. Al empezar hay tres: colaboradores, operadores y mecánicos. En cada certificado se agregan, quitan o editan los correos de cada conjunto.

En **Destinatarios de los correos** se marca qué conjuntos reciben cada mensaje. Por defecto, el código de descarga va a colaboradores y operadores, y el aviso de vencimiento va a mecánicos.

En **Opciones → Correos → Plantillas de correo** se editan el asunto y el código HTML de cada correo. **Vista previa** abre un popup con el asunto y el HTML tal como están en los cuadros, también si todavía no se han guardado. No envía el correo. Los datos de ejemplo son el título Certificado de ejemplo, la serie GA000000000 y el código A1B2C3. `{validez_codigo}` usa el plazo escrito en la pestaña Descarga. El aviso de vencimiento usa 15 días, la emisión 01-10-2026 y el id 1. Si un cuadro se deja vacío y se guarda, vuelve la plantilla incluida en el plugin.

| Correo | Cuándo se envía | Marcadores |
|---|---|---|
| Código de descarga | Al crear el certificado y cada vez que se solicita la descarga | `{cert_title}`, `{serie}`, `{download_code}`, `{validez_codigo}` |
| Aviso de vencimiento | Cuando el certificado entra en el plazo de aviso configurado | `{cert_title}`, `{serie}`, `{dias_vigencia}`, `{emision}`, `{id}` |

El HTML es el mensaje completo, con el encabezado y el pie. Los marcadores se reemplazan al enviar el correo.

## Papelera de archivos vencidos

En **Opciones** se puede activar **Mover a la papelera el archivo del certificado después de vencer** e indicar cuántos días, semanas, meses o años deben pasar desde el vencimiento. Un plazo de 0 mueve el archivo en cuanto vence.

El vencimiento es la fecha de emisión más el plazo de **Vencimiento a los**. Una tarea diaria de WordPress mueve el adjunto con `wp_delete_attachment` sin borrado permanente. El PDF ya no está en la carpeta pública: está en `{prefix}dosf_private_files`. Esta tarea no borra esas filas. El adjunto se restaura desde **Medios → Papelera** cuando `wp-config.php` define `MEDIA_TRASH` como `true`. Sin esa constante el adjunto queda en estado papelera, pero la pantalla de medios no muestra la papelera.

Si el mismo adjunto está vinculado a varios certificados, solo pasa a la papelera cuando todos ellos ya cumplieron ese plazo.

Cuando el adjunto pasa a la papelera o se elimina, cada certificado que lo usa queda marcado en su propio registro. En el listado público, en lugar del enlace de descarga, se muestra **Certificado no disponible**. En la columna de archivo del admin se muestra **No disponible**. Intentar la descarga devuelve el mismo aviso. Restaurar el adjunto quita esa marca y vuelve a habilitar la descarga.

## Código de descarga

Cada clic en el icono de descarga genera un código nuevo, guarda la fecha y hora de emisión y lo envía al correo del certificado. El código anterior deja de valer en ese momento.

La duración se configura en **Opciones**, en **El código de descarga es válido durante**. El número mínimo es 1. Las unidades son minuto, hora, día o semana. Si no hay un plazo guardado, se usa **1 hora**. El plazo empieza cuando se solicita la descarga, no cuando se creó el certificado. El alta de un certificado también registra la hora de emisión del código que sale en el correo inicial. En la plantilla de ese correo, `{validez_codigo}` se reemplaza por ese plazo en palabras, por ejemplo **30 minutos** o **1 hora**.

| Situación | Qué ve quien descarga |
|---|---|
| Código distinto al vigente | Código de descarga no válido |
| Código vencido, o certificado sin hora de emisión | El código de descarga venció. Solicita la descarga nuevamente. |
| Archivo en la papelera, eliminado de la tabla o marcado como no disponible | Certificado no disponible |
| Código vigente | Un enlace de un solo uso, válido 2 minutos, que entrega el PDF. La URL del adjunto no sirve |

Ingresar un código correcto no genera otro. Un código nuevo aparece solo cuando se vuelve a solicitar la descarga. Un certificado antiguo sin hora de emisión se trata como vencido hasta esa nueva solicitud.

---

## Filtros disponibles (API para desarrolladores)

Todos los filtros se agregan en el `functions.php` del tema activo.

### ID del popup de Popup Maker (alternativa por código)
```php
add_filter( 'dosf/front/search-rut/download-code/popupmakerId', function( $id ) {
    return 11927; // Reemplaza con el ID de tu popup
} );
```

### Imagen indicadora "vigente" (el icono por defecto es un círculo verde con un check blanco)
```php
add_filter( 'dosf/front/status-indicator/vigente/img-url', function( $url ) {
    return get_template_directory_uri() . '/images/mi-imagen-vigente.png';
} );
```

### Imagen indicadora "vencido" (el icono por defecto es un círculo rojo con una X blanca)
```php
add_filter( 'dosf/front/status-indicator/vencido/img-url', function( $url ) {
    return get_template_directory_uri() . '/images/mi-imagen-vencida.png';
} );
```

### Personalizar etiquetas del menú admin
```php
add_filter( 'dosf-admin/admin-page-title', fn( $t ) => 'Certificados de Mantención' );
add_filter( 'dosf-admin/admin-menu-title', fn( $t ) => 'Certificados' );
```

---

## Tablas de base de datos

El plugin crea automáticamente las siguientes tablas al activarse:

| Tabla | Descripción |
|---|---|
| `{prefix}dosf_shared_objs` | Certificados/documentos registrados |
| `{prefix}dosf_so_ruts_links` | Relación certificado — RUTs autorizados |
| `{prefix}dosf_expiration_warnig_email_queue` | Cola de avisos de vencimiento por email |
| `{prefix}dosf_private_files` | PDF de cada certificado, en trozos, fuera de la carpeta pública |

### Creación manual de tablas (si el plugin falla al inicializarlas)

En algunos entornos (hosting restrictivos, migraciones, permisos especiales) el activador puede no crear las tablas correctamente. **Síntomas:** el listado de certificados queda vacío o no se pueden agregar registros.

**Solución:** Ejecutar el siguiente SQL en phpMyAdmin o cualquier cliente MySQL, reemplazando `umm_` por el prefijo real de tu instalación (definido en `wp-config.php` como `$table_prefix`):

```sql
-- Tabla principal de certificados
CREATE TABLE IF NOT EXISTS `umm_dosf_shared_objs` (
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

-- Tabla de RUTs vinculados a certificados
CREATE TABLE IF NOT EXISTS `umm_dosf_so_ruts_links` (
    `id`      int unsigned NOT NULL AUTO_INCREMENT,
    `so_id`   int unsigned NOT NULL,
    `rut`     varchar(13)  NOT NULL,
    `created` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `soId_idx` (`so_id`, `id`, `rut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cola de emails de aviso de vencimiento
CREATE TABLE IF NOT EXISTS `umm_dosf_expiration_warnig_email_queue` (
    `id`    int unsigned NOT NULL AUTO_INCREMENT,
    `so_id` int          NOT NULL,
    PRIMARY KEY (`id`),
    KEY `soId_idx` (`so_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Después de ejecutar el SQL, desactiva y reactiva el plugin en **Plugins → Plugins instalados**.

> **Si solo falta la columna `email3`** (tabla creada con versión anterior del plugin):
> ```sql
> ALTER TABLE `umm_dosf_shared_objs`
>   ADD COLUMN `email3` varchar(255) NOT NULL DEFAULT '' AFTER `email2`;
> ```

> **Si faltan las columnas de los cambios recientes** (`file_unavailable` o `download_code_issued`):
> ```sql
> ALTER TABLE `umm_dosf_shared_objs`
>   ADD COLUMN `file_unavailable` tinyint(1) NOT NULL DEFAULT 0 AFTER `wp_file_obj_id`,
>   ADD COLUMN `download_code_issued` datetime NULL AFTER `download_code`;
> ```
> Al cargar el plugin, si la versión de base de datos guardada no es 1.5, la migración agrega esas columnas sola. Los correos de conjuntos creados después de los tres iniciales se guardan en `recipient_extra`.

La tabla `{prefix}dosf_private_files` la crea el plugin la primera vez que carga. No cambia la marca de versión 1.5. Si hiciera falta crearla a mano:

```sql
CREATE TABLE IF NOT EXISTS `umm_dosf_private_files` (
    `attachment_id` bigint(20) unsigned NOT NULL,
    `seq` smallint unsigned NOT NULL,
    `filename` varchar(255) NOT NULL,
    `mime` varchar(100) NOT NULL DEFAULT '',
    `byte_length` int unsigned NOT NULL DEFAULT 0,
    `chunk_b64` longtext NOT NULL,
    `restored` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`attachment_id`, `seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`chunk_b64` es un trozo del PDF. `restored` vale 1 cuando el archivo está en el catálogo del plugin. Si la tabla ya existe y falta esa columna:

```sql
ALTER TABLE `umm_dosf_private_files`
  ADD COLUMN `restored` tinyint(1) NOT NULL DEFAULT 0;
```

---

## Changelog

### v1.4.0
- `feat` El PDF del certificado se guarda en la base de datos y se borra de la carpeta pública. La URL del adjunto deja de entregarlo. Un código vigente obtiene un enlace de un solo uso, válido 2 minutos
- `feat` En Sistema, Certificados vencidos permite descargar el PDF solo desde el admin, restaurarlo al catálogo o borrarlo de la tabla. Eliminar se desactiva si un certificado vigente usa el mismo archivo
- `feat` Al crear o editar un certificado se puede elegir un archivo de la biblioteca de medios o uno restaurado del catálogo del plugin

### v1.3.0
- `feat` La configuración queda en un popup con las pestañas Búsqueda, Vigencia, Descarga, Correos y Sistema
- `feat` Los conjuntos de destinatarios se crean, renombran, eliminan y se asignan a cada correo. En el certificado, los correos de cada conjunto se editan por separado
- `feat` El texto de búsqueda por serie y los mensajes de vigente y vencido aceptan HTML de formato. Las plantillas de correo siguen siendo HTML completo
- `feat` Vista previa del correo en un popup, sin enviarlo, con el HTML del cuadro y el plazo de validez escrito en las opciones
- `feat` El marcador `{validez_codigo}` indica en el correo del código cuánto tiempo vale, según el plazo configurado
- `feat` En el listado, el título se recorta con puntos suspensivos y tooltip, y los correos quedan en una sola columna Email
- `feat` El texto y el icono del menú lateral se eligen en Sistema. Si el texto queda vacío, el menú sigue como Distribución de archivos
- `fix` El mensaje de vigencia en el sitio público ya no fuerza la negrita. El peso de la fuente sale del HTML del campo. `{estado}` conserva el verde o el rojo

### Cambios recientes
- `feat` La configuración se abre con el botón Opciones, solo visible para un administrador de WordPress. La ruta que la guarda exige el mismo permiso. El gestor de certificados sigue gestionando los certificados
- `feat` El código de descarga cambia en cada solicitud. El anterior deja de valer. La validez se configura en minutos, horas, días o semanas; por defecto, 1 hora, contada desde el envío del código
- `feat` El archivo vencido puede pasar a la papelera de medios tras un plazo de gracia, con la tarea diaria de WordPress y `wp_delete_attachment`. Un adjunto compartido solo se mueve cuando todos los certificados que lo usan ya cumplieron el plazo
- `feat` Si el adjunto se elimina o va a la papelera, los certificados que lo usan quedan marcados. El público ve «Certificado no disponible» y el admin «No disponible». Restaurar el adjunto quita la marca
- `feat` El popup de Popup Maker se elige en una lista (título y número), no escribiendo el ID
- `feat` Los mensajes de vigencia aceptan el marcador `{fecha_vencimiento}`

### v1.0.2
- `fix` Icono de descarga con SVG embebido, sin dependencia de Font Awesome
- `feat` ID del popup de Popup Maker configurable desde el panel de admin
- `fix` Columna `email3` agregada al schema del activador (faltaba en versiones anteriores)
- `fix` URLs de imagenes vigente/vencido generadas dinamicamente con `plugin_dir_url()`
- `fix` Tablas de BD se actualizan automaticamente via `check_wp_dosf_db_version()`

### v1.0.1
- Version inicial en produccion

---

## Licencia

GPL-2.0+ — ver [LICENSE.txt](LICENSE.txt)
