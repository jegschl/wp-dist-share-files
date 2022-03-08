
	

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	var dttbl = null;

	function selection_data_render(data, type) {
        if (type === 'display') {
            let selection = '';
            if(data == true){
                selection = 'checked';
            }

            return '<input type="checkbox" ' + selection + ' />' ;
        }
         
        return data;
    }

	(function( $ ) {
	'use strict';
	$(document).ready(function ($) {
		//debugger;
		dttbl = $('#tabla').DataTable( {
			processing: true,
			serverSide: true,
			ajax: dosf_config.urlGetSOs,
			language: {
				url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es-cl.json'
			},
			columns: [
				/* 'id'			  => $c->id,
                'title'           => $c->title,
                'file_name'   => $c->file_name,
                'wp_obj_id'      => $c->wp_file_obj_id,
                'linked_ruts'       => $c->linked_ruts, */
				{
					data: 'selection',
					render: selection_data_render
				},
				{
					data: 'title'
				},
				{
					data: 'file_name'
				},
				{
					data: 'linked_ruts'
				}
			]
		} );	
		
		var file_frame;
		var wp_media_post_id = wp.media.model.settings.post.id;
		var set_to_post_id;

		function resetDosfAddFields(){
			$( '#dosf_so_ruts_linked' ).val('');
			$( '#dosf_attachment_id').val(''),
			$( '#dosf-file-selectd' ).text(''),
			$( '#dosf_so_title' ).val('')
		}

		function setWidgetsForDosfAddNew(){
			$('.dosf-admin-header').hide();
			$('#dosf-data-tbl').hide();
			resetDosfAddFields();
			$('.dosf-admin-add-so').show();
		}

		function setWidgetsForDosfAddedOrCanceled(){
			$('.dosf-admin-add-so').hide();
			$('.dosf-admin-header').show();
			$('#dosf-data-tbl').show();
		}

		$( '.dosf-admin-header #add-dosf' ).on('click',function(event){
			setWidgetsForDosfAddNew();
		});

		$( '.actions-wrapper .cancel' ).on('click',function(event){
			event.preventDefault();
			setWidgetsForDosfAddedOrCanceled();
		});

		$('.fld-so #browse-file').on('click', function( event ){

			event.preventDefault();

			// >Si el mediaframe existe, se abre este mismo.
			if ( file_frame ) {
				// Se establece el  post ID que esté previamente establecido
				file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
				// Open frame
				file_frame.open();
				return;
			} else {
				// Set the wp.media post id so the uploader grabs the ID we want when initialised
				wp.media.model.settings.post.id = set_to_post_id;
			}

			// Se crea el media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: 'Seleccionar archivo',
				button: {
					text: 'Asignar archivo',
				},
				multiple: false	// Se podría establecer a true para seleccionar varios archivos.
			});

			// Cuando un archivo es seleccionado, se ejecuta un callback.
			file_frame.on( 'select', function() {
				// Como se estableció múltiple a false solo se obtienen los datos de un solo archivo en el uploader
				var attachment = file_frame.state().get('selection').first().toJSON();
				
				// Se almacena el attachment.id y el nombre del archivo.
				$( '#dosf_attachment_id' ).val( attachment.id );
				$( '#dosf-file-selectd' ).text(attachment.filename)
				// Restaurando el post ID principal
				wp.media.model.settings.post.id = wp_media_post_id;
			});

			// Finalmente, abriendo el selector de archivos en modal
			file_frame.open();
		});

		// Restaurando el ID principal cuando el botón add media es presionado
		$( 'a.add_media' ).on( 'click', function() {
			wp.media.model.settings.post.id = wp_media_post_id;
		});

		function onDosfNewError( jqXHR, textStatus, errorThrown ){
			console.log('Error al intentar enviar un nuevo dosf data set al server.');
			console.log(jqXHR);
		}

		function onDosfNewSuccess(  data,  textStatus,  jqXHR ){
			console.log('Datos enviados al server correctamente.');
			console.log(data);
			dttbl.ajax.reload();
			
		}

		function onDosfNewComplete( jqXHR, textStatus ){
			setWidgetsForDosfAddedOrCanceled();
		}

		$( '.dosf-admin-add-so .actions-wrapper .save' ).on('click',function(event){
			event.preventDefault();

			// enumerando ruts...
			var ruts = $('#dosf_so_ruts_linked').val().split(',');
			var rutsCount = ruts.length;

			// retirar puntos y guiones.
			for(var i=0; i<rutsCount; i++){
				ruts[i] = ruts[i].replace(/\.|\-/g,'');
			}

			// crear datos para enviar.
			var dosfNewData = {
				'wp_obj_file_id': $('#dosf_attachment_id').val(),
				'file_name': $( '#dosf-file-selectd' ).text(),
				'linked_ruts': ruts,
				'title': $('#dosf_so_title').val()
			};

			// pendiente agregar validaciones.

			// preparando la configuración de la llamada a endpoint para crear nuevo dosf.
			var ajxSettings = {
				url: dosf_config.urlAddSO,
				method:'POST',
				accepts: 'application/json; charset=UTF-8',
				contentType: 'application/json; charset=UTF-8',
				data: JSON.stringify(dosfNewData),
				complete: onDosfNewComplete,
				success: onDosfNewSuccess,
				error: onDosfNewError
			}

			// Activando animación de proceso.

			// ejecutando AJAX.
			$.ajax(ajxSettings);

		});
	});

	
	
})( jQuery );


