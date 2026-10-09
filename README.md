# jgb-dosf — Dist or Share Files with WordPress

Plugin para **compartir y distribuir documentos** con control de vencimiento, protegidos por código de autorización enviado por email. Desarrollado por [Jorge Garrido](https://phonix.dev).

---

## Características

- Gestión de certificados/documentos compartidos desde el panel de administración
- Búsqueda pública por **RUT** o **número de serie** del equipo
- Control de **fecha de emisión y vencimiento** con indicador visual (vigente / vencido)
- Descarga protegida mediante **código de autorización enviado por correo electrónico**. Cada solicitud de descarga genera un código nuevo y anula el anterior. El plazo de validez (minuto, hora, día o semana) se configura en **Otras opciones**.
- Papelera automática del archivo vencido, con un plazo de gracia desde el vencimiento, y marca de archivo no disponible si el adjunto se elimina o se mueve a la papelera
- Avisos automáticos de vencimiento próximo por email (colaboradores, operadores, mecánicos)
- Integración con **Popup Maker**: el popup se elige en una lista, no escribiendo su ID
- La configuración de **Otras opciones** solo la ve un administrador de WordPress. El gestor de certificados sigue administrando los certificados
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
5. Elegir el popup en **Distribución de archivos → Otras opciones**
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
2. Ve a **Distribución de archivos → Gestión → Otras opciones**
3. En **Popup (Popup Maker) para ingreso de código de descarga**, elige el popup de la lista. Cada opción se muestra como título y número, por ejemplo `Descarga con código de autorización (#11927)`. Entran los popups publicados, en borrador, privados y pendientes. Si el popup guardado ya no existe, la lista conserva `Popup #ID (no encontrado)`
4. Haz clic en **Guardar otras opciones**

---

## Shortcode público

```
[dosf_browser]
```

Agrega este shortcode en la página donde los usuarios buscarán sus certificados. El plugin mostrará un formulario de búsqueda por RUT o número de serie según la configuración en "Otras opciones".

## Quién puede configurar el plugin

El menú **Distribución de archivos** (Gestión y Ayuda) está disponible para quien tenga la capacidad `jgb_collab_access`: el rol **Gestor de Certificados** y el administrador de WordPress.

El bloque **Otras opciones**, al final de Gestión, solo se muestra si la sesión actual es de un administrador de WordPress (capacidad `manage_options`). Ahí se definen la búsqueda pública, el uso de número de serie y de fecha de emisión, el vencimiento, los avisos por correo, la papelera de archivos vencidos, la validez del código de descarga, el popup y los mensajes del frontend.

Un gestor de certificados sigue pudiendo crear, editar y eliminar certificados. No ve ese formulario. Si llama al guardado de esas opciones, WordPress lo rechaza.

## Mensajes de vigencia en el frontend

Con la búsqueda por número de serie y la opción **Coincidencias específicas** activa, cada resultado indica si el certificado está vigente o vencido.

Esos textos se editan en **Distribución de archivos → Gestión**, dentro de **Otras opciones → Mensajes del frontend**. Hay un cuadro para el certificado vigente y otro para el vencido. Hay que pulsar **Guardar otras opciones**.

| Marcador | Se reemplaza por |
|---|---|
| `{cert_title}` | Título del certificado |
| `{serie}` | Número de serie |
| `{estado}` | La palabra vigente o vencido, con su color |
| `{fecha_vencimiento}` | Fecha de vencimiento: emisión más el plazo de «Vencimiento a los» |

Si un cuadro queda vacío, el texto usado es:

```
{cert_title} de la serie {serie} se encuentra actualmente {estado}.
```

`{estado}` es el único marcador que conserva el color. El resto del mensaje se publica como texto plano.

## Papelera de archivos vencidos

En **Otras opciones** se puede activar **Mover a la papelera el archivo del certificado después de vencer** e indicar cuántos días, semanas, meses o años deben pasar desde el vencimiento. Un plazo de 0 mueve el archivo en cuanto vence.

El vencimiento es la fecha de emisión más el plazo de **Vencimiento a los**. Una tarea diaria de WordPress mueve el adjunto con `wp_delete_attachment` sin borrado permanente. El archivo sigue en el disco y se restaura desde **Medios → Papelera** cuando `wp-config.php` define `MEDIA_TRASH` como `true`. Sin esa constante el adjunto queda en estado papelera y el archivo no se borra, pero la pantalla de medios no muestra la papelera.

Si el mismo adjunto está vinculado a varios certificados, solo pasa a la papelera cuando todos ellos ya cumplieron ese plazo.

Cuando el adjunto pasa a la papelera o se elimina, cada certificado que lo usa queda marcado en su propio registro. En el listado público, en lugar del enlace de descarga, se muestra **Certificado no disponible**. En la columna de archivo del admin se muestra **No disponible**. Intentar la descarga devuelve el mismo aviso. Restaurar el adjunto quita esa marca y vuelve a habilitar la descarga.

## Código de descarga

Cada clic en el icono de descarga genera un código nuevo, guarda la fecha y hora de emisión y lo envía al correo del certificado. El código anterior deja de valer en ese momento.

La duración se configura en **Otras opciones**, en **El código de descarga es válido durante**. El número mínimo es 1. Las unidades son minuto, hora, día o semana. Si no hay un plazo guardado, se usa **1 hora**. El plazo empieza cuando se solicita la descarga, no cuando se creó el certificado. El alta de un certificado también registra la hora de emisión del código que sale en el correo inicial.

| Situación | Qué ve quien descarga |
|---|---|
| Código distinto al vigente | Código de descarga no válido |
| Código vencido, o certificado sin hora de emisión | El código de descarga venció. Solicita la descarga nuevamente. |
| Archivo en la papelera, eliminado o marcado como no disponible | Certificado no disponible |

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
> Al cargar el plugin, si la versión de base de datos guardada no es 1.4, la migración agrega esas columnas sola.

---

## Changelog

### Cambios recientes
- `feat` Otras opciones solo es visible para un administrador de WordPress. El gestor de certificados sigue gestionando los certificados, y el guardado de la configuración rechaza al resto
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
