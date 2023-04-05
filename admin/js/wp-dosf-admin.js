
	

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

	function actions_data_render(data, type){
		if (type === 'display') {
			var output = '';
			output += '<div class="actions">';

			output += '<div class="action edit-dosf">';
			output += '<i class="fas fa-edit"></i>';
			output += '</div>';

			output += '<div class="action send-dosf-download-code">';
			output += '<i class="fas fa-paper-plane"></i>';
			output += '</div>';

			output += '<div class="action remove-dosf">';
			output += '<i class="fas fa-minus-circle"></i>';
			output += '</div>';

			output += '</div>';
            return output ;
        }
         
        return data;
	}

	(function( $ ) {
	'use strict';
		let choiceEmlsColabs;
		let choiceEmlsOprtrs;
		let dtColumns = [];
		dtColumns.push(
			{
				data: 'selection',
				render: selection_data_render
			}
		);

		dtColumns.push(
			{
				data: 'title'
			}
		);

		if(dosf_config.useIssueDate){
			dtColumns.push(
				{
					data: 'emision'
				}
			);
		}

		dtColumns.push(
			{
				data: 'file_name'
			}
		);

		dtColumns.push(
			{
				data: 'linked_ruts'
			}
		);

		dtColumns.push(
			{
				data: 'email'
			}
		);

		dtColumns.push(
			{
				data: 'email2'
			}
		);

		if(dosf_config.useIssueDate){
			dtColumns.push(
				{
					data: 'status'
				}
			);
		}

		dtColumns.push(
			{
				data: 'actions',
				render: actions_data_render
			}
		);

		const JGB_DOSF_AOE_FORM_MODE_ADD  = 0;
		const JGB_DOSF_AOE_FORM_MODE_EDIT = 1;
		let aoeFormMode;
		let currentEditionDosfId;
		let currentEditionDosfTR;
		let dosfAddNewSentTryErrorCondMsg = '';

		function onDttblCreatedRow( row, data, dataIndex, cells ){
			const atid = data['DT_RowData']['attachment-id'];
			$(row).data('attachment-id',atid);
		}

		$(document).ready(function ($) {
			//debugger;
			dttbl = $('#tabla').DataTable( {
				processing: true,
				serverSide: true,
				ajax: dosf_config.urlGetSOs,
				language: {
					url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es-cl.json'
				},
				columns: dtColumns,
				drawCallback: onDttblDraw,
				createdRow: onDttblCreatedRow
			} );	
			
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id;
			var set_to_post_id;

			function onDttblDraw(){
				const itemActionReqSendDwldCodeSelector = '.action.send-dosf-download-code';
				$(itemActionReqSendDwldCodeSelector).off('click');
				$(itemActionReqSendDwldCodeSelector).on('click',dttblItemActionReqSendDownloadCodeEmail);

				const itemActionEditionCodeSelector = '.action.edit-dosf';
				$(itemActionEditionCodeSelector).off('click');
				$(itemActionEditionCodeSelector).on('click',setWidgetsForDosfEdition);

				const itemActionReqRemoveCodeSelector = '.action.remove-dosf';
				$(itemActionReqRemoveCodeSelector).off('click');
				$(itemActionReqRemoveCodeSelector).on('click',dttblItemActionReqRemoveDosf);
			}

			function dttblItemActionReqRemoveDosf(){
				const dosf_id = $(this).closest('tr').attr('id');
			}

			function dttblItemActionReqSendDownloadCodeEmail(){
				const dosf_id = $(this).parent().parent().parent().attr('id');
				const ajxSettings = {
					method: 'GET',
					url: dosf_config.urlSndDC + dosf_id,
					accepts: 'application/json; charset=UTF-8',
					contentType: 'application/json; charset=UTF-8',
					complete: onDosfReqSendDwldCdComplete,
					success: onDosfReqSendDwldCdSuccess,
					error: onDosfReqSendDwldCdError
				}
				
				$.ajax(ajxSettings);
			}

			function onDosfReqSendDwldCdComplete( jqXHR, textStatus ){
				// Desactivar icono de progreso.
			}

			function onDosfReqSendDwldCdSuccess( data,  textStatus,  jqXHR ){
				console.log('Solicitud enviada al servidor exitosamente');
				console.log('Respuesta del servidor:');
				console.log(data);
			}

			function onDosfReqSendDwldCdError( jqXHR, textStatus, errorThrown ){
				console.log('Error al enviar la Solicitud de envío de email de código de descarga');
				console.log('Respuesta del servidor:');
				console.log(jqXHR);
			}

			function resetDosfAddFields(){
				$( '#dosf_so_ruts_linked' ).val('');
				$( '#dosf_attachment_id').val(''),
				$( '#dosf_so_emision' ).val('');
				$( '#dosf-file-selectd' ).text(''),
				$( '#dosf_so_title' ).val('')
				choiceEmlsColabs.clearStore();
				choiceEmlsOprtrs.clearStore();
				
				if( !$('.dosf-admin-add-so .notice.notice-error').hasClass('hidden') ){
					$('.dosf-admin-add-so .notice.notice-error').addClass('hidden')
				}
			}

			function dumpDataToDosfAddFields(){
				let cell = $(currentEditionDosfTR).children()[1];
				let vl 	 = $(cell).text();
				$( '#dosf_so_title' ).val(vl);

				cell = $(currentEditionDosfTR).children()[2];
				vl 	 = $(cell).text();
				$( '#dosf_so_emision' ).val(vl);

				cell = $(currentEditionDosfTR).children()[4];
				vl 	 = $(cell).text();
				$( '#dosf_so_ruts_linked' ).val(vl);

				cell = $(currentEditionDosfTR).children()[5];
				vl 	 = $(cell).text().split(',');
				choiceEmlsColabs.clearStore();
				choiceEmlsColabs.setValue(vl);

				cell = $(currentEditionDosfTR).children()[6];
				vl 	 = $(cell).text().split(',');
				choiceEmlsOprtrs.clearStore();
				choiceEmlsOprtrs.setValue(vl);


				$( '#dosf_attachment_id').val( $(currentEditionDosfTR).data('attachment-id') );

				cell = $(currentEditionDosfTR).children()[3];
				vl 	 = $(cell).text();
				$( '#dosf-file-selectd' ).text(vl);
				
				if( !$('.dosf-admin-add-so .notice.notice-error').hasClass('hidden') ){
					$('.dosf-admin-add-so .notice.notice-error').addClass('hidden')
				}
				
			}

			function setWidgetsForDosfAddNew(){
				aoeFormMode = JGB_DOSF_AOE_FORM_MODE_ADD;
				currentEditionDosfId = null;
				$('.dosf-admin-add-so > .title').text('Agregando certificado nuevo.');
				$('.dosf-admin-header').hide();
				$('#dosf-data-tbl').hide();
				resetDosfAddFields();
				$('.dosf-admin-add-so').show();
			}

			function setWidgetsForDosfEdition(){
				aoeFormMode = JGB_DOSF_AOE_FORM_MODE_EDIT;
				currentEditionDosfTR = $(this).closest('tr');
				currentEditionDosfId = $(currentEditionDosfTR).attr('id');
				$('.dosf-admin-add-so > .title').text('Modificando certificado con ID interno ' + currentEditionDosfId + '.');
				$('.dosf-admin-header').hide();
				$('#dosf-data-tbl').hide();
				dumpDataToDosfAddFields();
				$('.dosf-admin-add-so').show();
			}

			function setWidgetsForDosfAddedOrCanceled(){
				if( dosfAddNewSentTryErrorCondMsg == '' ){
					$('.dosf-admin-add-so').hide();
					$('.dosf-admin-header').show();
					$('#dosf-data-tbl').show();
				} else {
					$('.dosf-admin-add-so .notice.notice-error').text(dosfAddNewSentTryErrorCondMsg);
					if( $('.dosf-admin-add-so .notice.notice-error').hasClass('hidden') ){
						$('.dosf-admin-add-so .notice.notice-error').removeClass('hidden')
					}
				}
			}

			$( '.dosf-admin-header #add-dosf' ).on('click',function(event){
				setWidgetsForDosfAddNew();
			});

			$( '.actions-wrapper .cancel' ).on('click',function(event){
				event.preventDefault();
				dosfAddNewSentTryErrorCondMsg = '';
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
				dosfAddNewSentTryErrorCondMsg = 'Error al intentar enviar un nuevo dosf data set al server.';
				console.log('Error al intentar enviar un nuevo dosf data set al server.');
				console.log(jqXHR);
			}

			function onDosfNewSuccess(  data,  textStatus,  jqXHR ){
				console.log('Datos enviados al server correctamente.');
				console.log(data);
				if( data['dosfAddNew_post_status'] == 'ok' ^ data['dosfUpdate_post_status'] == 'ok'){
					dosfAddNewSentTryErrorCondMsg = '';
					dttbl.ajax.reload();
				}

				if( data['dosfAddNew_post_status'] == 'error' && data['err_code'] == '403' ){
					dosfAddNewSentTryErrorCondMsg = 'Ya existe un certificado con el mismo número de serie.'
				}
				
			}

			function onDosfNewComplete( jqXHR, textStatus ){
				setWidgetsForDosfAddedOrCanceled();
			}

			function setWidgetsForDosfSendingToServer(){
				if( !$('.dosf-admin-add-so .notice.notice-error').hasClass('hidden') ){
					$('.dosf-admin-add-so .notice.notice-error').addClass('hidden')
				}
			}

			$( '.dosf-admin-add-so .actions-wrapper .save' ).on('click',function(event){
				event.preventDefault();

				setWidgetsForDosfSendingToServer();

				// enumerando ruts...
				var ruts = $('#dosf_so_ruts_linked').val().split(',');
				var rutsCount = ruts.length;

				// retirar puntos y guiones.
				for(var i=0; i<rutsCount; i++){
					ruts[i] = ruts[i].replace(/\.|\-/g,'');
				}

				// crear datos para enviar.
				var dosfNewData = {
					'wp_obj_file_id': 	$('#dosf_attachment_id').val(),
					'file_name': 		$( '#dosf-file-selectd' ).text(),
					'linked_ruts': 		ruts,
					'title': 			$('#dosf_so_title').val(),
					'email': 			choiceEmlsColabs.getValue(true),
					'email2': 			choiceEmlsOprtrs.getValue(true),
					'updateId': 		currentEditionDosfId 
				};

				if(dosf_config.useIssueDate){
					dosfNewData.emision = $('#dosf_so_emision').val();
				}

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



			function preparePlusOptions(){
				var config = {
					'nonce'							: $('#plus-options-update-nonce'),
					'frontend-specific-match-search': $('#especific-match').is(':checked'),
					'use-serial-number' 			: $('#use-serial-numbers').is(':checked'),
					'use-issue-date'				: $('#use-issue-date').is(':checked'),
					'expire-period-nmb'				: $('#expire-period-nmb').val(),
					'expire-period-unit'			: $('#expire-period-unit').val()
				};

				return JSON.stringify(config);
			}

			function setPlusOptionsFieldsInProcessingMode(){
				$( '#dosf-plus-options-save' ).prop("disabled","disabled");
				$( '.dosf-plus-options-actions .processing' ).removeClass('hidden');
			}

			function setPlusOptionsFieldsInNormalMode(){
				$( '#dosf-plus-options-save' ).prop("disabled",false);
				$( '.dosf-plus-options-actions .processing' ).addClass('hidden');
			}

			$( '#dosf-plus-options-save' ).on('click',function(evnt){
				setPlusOptionsFieldsInProcessingMode();

				const cfg = preparePlusOptions();

				// preparando la configuración de la llamada a endpoint para la configuración adicional.
				var ajxSettings = {
					url: dosf_config.urlUpdPlusOpts,
					method:'POST',
					accepts: 'application/json; charset=UTF-8',
					contentType: 'application/json; charset=UTF-8',
					data: cfg,
					complete: function(jqXHR, textStatus){
						setPlusOptionsFieldsInNormalMode();
					},
					success: function(data,  textStatus,  jqXHR){},
					error: function(jqXHR, textStatus, errorThrown){
						console.log('Error intentando enviar datos de opciones adicionales para almacenar en el servidor.');
						console.log("Estos son los datos del error:");
						console.log(errorThrown);
						console.log(textStatus);
					}
				}

				// Activando animación de proceso.

				// ejecutando AJAX.
				$.ajax(ajxSettings);
				
			});
			
			$('.dosf-admin-add-so .fields-wrapper #dosf_so_emision').datetimepicker(
				{	
					format: 'Y-m-d H:i',
					lang: 'es',
					i18n: {
						'es': { // Spanish
							months: [
								"Enero",
								"Febrero",
								"Marzo", 
								"Abril", 
								"Mayo", 
								"Junio", 
								"Julio", 
								"Agosto", 
								"Septiembre", 
								"Octubre", 
								"Noviembre", 
								"Diciembre"
							],
			
							dayOfWeekShort: [
								"Dom", 
								"Lun", 
								"Mar", 
								"Mié", 
								"Jue", 
								"Vie", 
								"Sáb"
							],
			
							dayOfWeek: [
								"Domingo", 
								"Lunes", 
								"Martes", 
								"Miércoles", 
								"Jueves", 
								"Viernes", 
								"Sábado"
							]
						}
					}
				}
			);

			const choicesEmlsCfg = {
				allowHTML: true,
				delimiter: ',',
				editItems: true,
				maxItemCount: 5,
				removeItemButton: true,
				duplicateItemsAllowed: false,
				addItemFilter: function(value) {
					if (!value) {
					  return false;
					}
		
					const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
					const expression = new RegExp(regex.source, 'i');
					return expression.test(value);
				},
				addItemText: (value) => {
					return `Presiona ENTER para agregar <b>"${value}"</b>`;
				},
				customAddItemText: 'Solo se permite agregar emails'
			};

			choiceEmlsColabs = new Choices($('#dosf_so_email')[0],choicesEmlsCfg);

			choiceEmlsOprtrs = new Choices($('#dosf_so_email2')[0],choicesEmlsCfg);


		});

	})( jQuery );


