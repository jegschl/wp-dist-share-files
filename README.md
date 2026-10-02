# jgb-dosf — Dist or Share Files with WordPress

Plugin para **compartir y distribuir documentos** con control de vencimiento, protegidos por código de autorización enviado por email. Desarrollado por [Jorge Garrido](https://phonix.dev).

---

## Características

- Gestión de certificados/documentos compartidos desde el panel de administración
- Búsqueda pública por **RUT** o **número de serie** del equipo
- Control de **fecha de emisión y vencimiento** con indicador visual (vigente / vencido)
- Descarga protegida mediante **código de autorización enviado por correo electrónico**
- Avisos automáticos de vencimiento próximo por email (colaboradores, operadores, mecánicos)
- Integración con **Popup Maker** para el formulario de ingreso del código de autorización
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
5. Configurar el ID del popup en **Distribución de archivos → Otras opciones**
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

### Paso 3 — Obtener el ID del popup

1. Guarda el popup
2. En **Popup Maker → Todos los popups**, anota el **ID numérico** (columna ID, o en la URL al editar: `?post=XXXX`)

### Paso 4 — Registrar el ID en el plugin

1. Ve a **Distribución de archivos → Otras opciones**
2. En el campo **"ID del popup (Popup Maker) para ingreso de código de descarga"**, ingresa el ID
3. Haz clic en **Guardar otras opciones**

---

## Shortcode público

```
[dosf_browser]
```

Agrega este shortcode en la página donde los usuarios buscarán sus certificados. El plugin mostrará un formulario de búsqueda por RUT o número de serie según la configuración en "Otras opciones".

## Mensajes de vigencia en el frontend

Con la búsqueda por número de serie y la opción **Coincidencias específicas** activa, cada resultado indica si el certificado está vigente o vencido.

Esos textos se editan en **Distribución de archivos → Gestión**, dentro de **Otras opciones → Mensajes del frontend**. Hay un cuadro para el certificado vigente y otro para el vencido. Hay que pulsar **Guardar otras opciones**.

| Marcador | Se reemplaza por |
|---|---|
| `{cert_title}` | Título del certificado |
| `{serie}` | Número de serie |
| `{estado}` | La palabra vigente o vencido, con su color |

Si un cuadro queda vacío, el texto usado es:

```
{cert_title} de la serie {serie} se encuentra actualmente {estado}.
```

`{estado}` es el único marcador que conserva el color. El resto del mensaje se publica como texto plano.

---

## Filtros disponibles (API para desarrolladores)

Todos los filtros se agregan en el `functions.php` del tema activo.

### ID del popup de Popup Maker (alternativa por código)
```php
add_filter( 'dosf/front/search-rut/download-code/popupmakerId', function( $id ) {
    return 11927; // Reemplaza con el ID de tu popup
} );
```

### Imagen indicadora "vigente" (ej. manito verde)
```php
add_filter( 'dosf/front/status-indicator/vigente/img-url', function( $url ) {
    return get_template_directory_uri() . '/images/mi-imagen-vigente.png';
} );
```

### Imagen indicadora "vencido" (ej. manito roja)
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
    `created`        timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `email`          varchar(255)  NOT NULL DEFAULT '',
    `email2`         varchar(255)  NOT NULL DEFAULT '',
    `email3`         varchar(255)  NOT NULL DEFAULT '',
    `download_code`  varchar(16)   NOT NULL DEFAULT '',
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

---

## Changelog

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
