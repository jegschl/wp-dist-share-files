
	

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

            return '<input type="checkbox" ' + selection + ' class="dosf-checker"/>' ;
        }
         
        return data;
    }

	function inExpireWarnPeriod(vdbe) {
		const days = Number(vdbe);
		const windowDays = Number(dosf_config.lastDayRangeForExprWrng);
		return !isNaN(days) && !isNaN(windowDays) && days > 0 && days <= windowDays;
	}

	function actions_data_render(data, type, row){
		if (type === 'display') {
			var output = '';
			output += '<div class="actions">';

			output += '<div class="action edit-dosf" data-tip="Editar certificado" aria-label="Editar certificado">';
			output += '<i class="fas fa-edit"></i>';
			output += '</div>';

			output += '<div class="action send-dosf-download-code" data-tip="Enviar código de descarga" aria-label="Enviar código de descarga">';
			output += '<i class="fas fa-paper-plane"></i>';
			output += '</div>';

			if( inExpireWarnPeriod(row['vdbe']) ){
				output += '<div class="action send-dosf-expirity-alert-request" data-tip="Enviar aviso de vencimiento" aria-label="Enviar aviso de vencimiento">';
				output += '<i class="fas fa-paper-plane"></i>';
				output += '</div>';
			}

			output += '<div class="action remove-dosf" data-tip="Eliminar certificado" aria-label="Eliminar certificado">';
			output += '<i class="fas fa-minus-circle"></i>';
			output += '</div>';

			

			output += '</div>';
            return output ;
        }
         
        return data;
	}

	(function( $ ) {
	'use strict';
		let recipientChoices = {};
		let recipientSetsDirty = false;
		function warnSentHtml(cert) {
			const sent = parseInt(cert.expire_warn_sent, 10) || 0;
			const failed = parseInt(cert.expire_warn_failed, 10) || 0;
			const times = Array.isArray(cert.expire_warn_sent_at) ? cert.expire_warn_sent_at : [];
			if (sent < 1 && failed < 1 && times.length < 1) {
				return '0';
			}
			const sentWord = sent === 1 ? 'enviado' : 'enviados';
			const parts = [escHtml(sent + ' ' + sentWord)];
			times.forEach(function (when) {
				parts.push('<span class="dosf-warn-sent-at">' + escHtml(when) + '</span>');
			});
			if (failed > 0) {
				parts.push('<span class="dosf-warn-failed">' + escHtml(failed + ' con error') + '</span>');
			}
			return parts.join('<br>');
		}

		function escHtml(value) {
			return String(value == null ? '' : value)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;');
		}

		function certificates_render(data, type, row) {
			if (type !== 'display') {
				return '';
			}
			const dated = !!dosf_config.useIssueDate;
			let html = '<table class="dosf-cert-tree' + (dated ? ' dosf-cert-tree-dated' : '') + '"><colgroup>';
			html += '<col class="dosf-col-check"><col class="dosf-col-title">';
			if (dated) {
				html += '<col class="dosf-col-emision"><col class="dosf-col-days"><col class="dosf-col-status"><col class="dosf-col-warns">';
			}
			html += '<col class="dosf-col-file"><col class="dosf-col-email"><col class="dosf-col-actions"></colgroup>';
			html += '<thead><tr><th></th><th class="dosf-cert-title-col">Título</th>';
			if (dated) {
				html += '<th>Emisión</th><th>Días</th><th>Estado</th><th>Emails Previos al Vencimiento</th>';
			}
			html += '<th>Archivo</th><th>Email</th><th>Acciones</th></tr></thead><tbody>';
			(row.certificates || []).forEach(function(cert) {
				const recipients = cert.recipients || {};
				html += '<tr id="' + escHtml(cert.id) + '" class="dosf-cert-row"'
					+ ' data-serie="' + escHtml(row.serie) + '"'
					+ ' data-ruts="' + escHtml(row.linked_ruts) + '"'
					+ ' data-cert-title="' + escHtml(cert.cert_title) + '"'
					+ ' data-emision="' + escHtml(cert.emision) + '"'
					+ ' data-file="' + escHtml(cert.file_name) + '"'
					+ ' data-attachment-id="' + escHtml(cert.attachment_id) + '"'
					+ ' data-recipients="' + escHtml(JSON.stringify(recipients)) + '">';
				html += '<td><input type="checkbox" class="dosf-checker" /></td>';
				const certTitle = cert.cert_title == null ? '' : String(cert.cert_title);
				html += '<td class="dosf-cert-title-cell"><span class="dosf-cert-title-tip" data-tip="' + escHtml(certTitle) + '" tabindex="0">';
				html += '<span class="dosf-cert-title">' + escHtml(certTitle) + '</span></span></td>';
				if (dosf_config.useIssueDate) {
					html += '<td>' + escHtml(cert.emision) + '</td>';
					html += '<td>' + escHtml(cert.vdbe) + '</td>';
					const statusKey = String(cert.status || '').toLowerCase() === 'vigente' ? 'vigente' : 'vencido';
					html += '<td><span class="dosf-status dosf-status-' + statusKey + '">' + escHtml(cert.status) + '</span></td>';
					html += '<td class="dosf-warn-sent">' + warnSentHtml(cert) + '</td>';
				}
				html += '<td>' + escHtml(cert.file_name) + (parseInt(cert.file_unavailable, 10) === 1 ? ' <span class="dosf-file-unavailable">No disponible</span>' : '') + '</td>';
				let emailParts = [];
				(dosf_config.recipientSets || []).forEach(function(set) {
					const emails = String(recipients[set.id] || '').trim();
					if (emails === '') {
						return;
					}
					emailParts.push('<strong>' + escHtml(set.name) + '</strong>: ' + escHtml(emails));
				});
				html += '<td class="dosf-cert-emails">' + emailParts.join(' - ') + '</td>';
				html += '<td>' + actions_data_render(null, 'display', cert) + '</td>';
				html += '</tr>';
			});
			html += '</tbody></table>';
			html += '<button type="button" class="button add-cert-to-serie" data-serie="' + escHtml(row.serie) + '" data-ruts="' + escHtml(row.linked_ruts) + '">Agregar certificado a esta serie</button>';
			return html;
		}

		let dtColumns = [
			{ data: 'serie' },
			{ data: 'linked_ruts' },
			{ data: 'certificates', render: certificates_render }
		];

		const JGB_DOSF_AOE_FORM_MODE_ADD  = 0;
		const JGB_DOSF_AOE_FORM_MODE_EDIT = 1;
		const bluckUICOnfig = { css: { backgroundColor: '#f00', color: '#fff', 'border-radius': '10px', padding: '10px 0px' }, message: 'Procesando la solicitud...' };
		let aoeFormMode;
		let currentEditionDosfId;
		let currentEditionDosfTR;
		let dosfAddNewSentTryErrorCondMsg = '';
		let delConfirmtnDlg;
		let istr; // Ids to remove.

		function onDttblCreatedRow( row, data, dataIndex, cells ){
			if (data && data.DT_RowData && data.DT_RowData['attachment-id']) {
				$(row).data('attachment-id', data.DT_RowData['attachment-id']);
			}
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

			delConfirmtnDlg = $('#confirm-del-dlg').dialog({
				modal: true,
				autoOpen: false,
				draggable: false,
				resizable: false,
				closeText: "Cancelar",
				buttons:[
					{
						text: 'Si, eliminar',
						click: function(){
							$.blockUI(bluckUICOnfig); 
							sendDosfRemovRequest();
							$( this ).dialog( "close" );
							
						}
					}
				]
			});

			if ( $('#dosf-options-tabs').length ) {
				$('#dosf-options-tabs').tabs();
			}

			if ( $('.dosf-plus-options').length && $('#dosf-open-options').length ) {
				$('.dosf-plus-options').dialog({
					modal: true,
					autoOpen: false,
					draggable: true,
					resizable: true,
					width: Math.min(960, $(window).width() - 48),
					height: Math.min(760, $(window).height() - 80),
					title: 'Opciones',
					closeText: 'Cerrar',
					dialogClass: 'dosf-options-dialog'
				});
				$('#dosf-open-options').on('click', function() {
					$('.dosf-plus-options').dialog('open');
					if ( $('#dosf-options-tabs').length ) {
						$('#dosf-options-tabs').tabs('refresh');
					}
				});
			}

						function dosfRefreshMenuPreview() {
				if ( !$('#dosf-menu-label').length ) {
					return;
				}
				var label = $.trim($('#dosf-menu-label').val());
				if (!label) {
					label = 'Distribución de archivos';
				}
				$('#dosf-menu-preview-label').text(label);
				var icon = $('#dosf-menu-icon').val() || 'dashicons-forms';
				$('#dosf-menu-preview-icon').attr('class', 'dashicons ' + icon);
			}
			$('#dosf-menu-label').on('input', dosfRefreshMenuPreview);
			$('.dosf-menu-icon-choice').on('click', function() {
				var icon = $(this).attr('data-icon');
				$('#dosf-menu-icon').val(icon);
				$('.dosf-menu-icon-choice').removeClass('is-selected').attr('aria-pressed', 'false');
				$(this).addClass('is-selected').attr('aria-pressed', 'true');
				$('#dosf-menu-icon-name').text($(this).attr('data-label') || '');
				dosfRefreshMenuPreview();
			});
if ( $('#dosf-email-preview-dialog').length ) {
				$('#dosf-email-preview-dialog').dialog({
					modal: true,
					autoOpen: false,
					draggable: true,
					resizable: true,
					width: Math.min(860, $(window).width() - 48),
					height: Math.min(720, $(window).height() - 80),
					closeText: 'Cerrar',
					dialogClass: 'dosf-email-preview-dialog',
					close: function() {
						$('#dosf-email-preview-frame').attr('srcdoc', '');
					}
				});
				$('.dosf-email-preview').on('click', function() {
					var kind = $(this).attr('data-kind');
					var titles = {
						download: 'Vista previa: código de descarga',
						expire: 'Vista previa: aviso de vencimiento'
					};
					var sample = {
						'{cert_title}': 'Certificado de ejemplo',
						'{serie}': 'GA000000000',
						'{download_code}': 'A1B2C3',
						'{validez_codigo}': dosfCodeValidityLabel( $('#download-code-valid-nmb').val(), $('#download-code-valid-unit').val() ),
						'{dias_vigencia}': '15',
						'{emision}': '01-10-2026',
						'{id}': '1'
					};
					var subject = dosfFillEmailPreview( $('#email-tpl-' + kind + '-subject').val(), sample, false );
					var html = dosfFillEmailPreview( $('#email-tpl-' + kind + '-html').val(), sample, true );
					$('#dosf-email-preview-dialog').dialog( 'option', 'title', titles[kind] || 'Vista previa del correo' );
					$('#dosf-email-preview-subject').text( subject === '' ? '(Sin asunto)' : subject );
					var frame = document.getElementById('dosf-email-preview-frame');
					if ( html === '' ) {
						frame.srcdoc = '<p style="font-family:sans-serif;color:#646970;">El HTML del correo está vacío.</p>';
					} else {
						frame.srcdoc = html;
					}
					$('#dosf-email-preview-dialog').dialog('open');
				});
			}
			
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id;
			var set_to_post_id;


		function dosfCodeValidityLabel(amount, unit) {
			var n = parseInt(amount, 10);
			if (isNaN(n) || n < 1) {
				n = 1;
			}
			var words = {
				minute: ['minuto', 'minutos'],
				hour: ['hora', 'horas'],
				day: ['día', 'días'],
				week: ['semana', 'semanas']
			};
			var pair = words[unit] || words.hour;
			return n + ' ' + (n === 1 ? pair[0] : pair[1]);
		}

		function dosfFillEmailPreview(template, pairs, escapeValues) {
			var out = String(template || '');
			Object.keys(pairs).forEach(function(token) {
				var value = String(pairs[token]);
				if (escapeValues) {
					value = value
						.replace(/&/g, '&amp;')
						.replace(/</g, '&lt;')
						.replace(/>/g, '&gt;')
						.replace(/"/g, '&quot;');
				}
				out = out.split(token).join(value);
			});
			return out;
		}
			function onDttblDraw(){
				$('.dosf-cert-title-tip').each(function() {
					const inner = this.querySelector('.dosf-cert-title');
					if (!inner || inner.scrollWidth <= inner.clientWidth + 1) {
						$(this).removeAttr('data-tip').removeAttr('tabindex');
					}
				});
				const itemActionReqSendDwldCodeSelector = '.action.send-dosf-download-code';
				$(itemActionReqSendDwldCodeSelector).off('click');
				$(itemActionReqSendDwldCodeSelector).on('click',dttblItemActionReqSendDownloadCodeEmail);

				const itemActionReqSendExpWrngSelector = '.action.send-dosf-expirity-alert-request';
				$(itemActionReqSendExpWrngSelector).off('click');
				$(itemActionReqSendExpWrngSelector).on('click',dttblItemActionReqSendExprWrngEmail);

				const itemActionEditionCodeSelector = '.action.edit-dosf';
				$(itemActionEditionCodeSelector).off('click');
				$(itemActionEditionCodeSelector).on('click',setWidgetsForDosfEdition);

				const itemActionReqRemoveCodeSelector = '.action.remove-dosf';
				$(itemActionReqRemoveCodeSelector).off('click');
				$(itemActionReqRemoveCodeSelector).on('click',dttblItemActionReqRemoveDosf);

				const itemDosfCheckerSelector = '.dosf-checker';
				$(itemDosfCheckerSelector).off('click');
				$(itemDosfCheckerSelector).on('click',dttblItemDosfChecker);

				$('.add-cert-to-serie').off('click');
				$('.add-cert-to-serie').on('click', function() {
					setWidgetsForDosfAddNew();
					$('#dosf_so_serie').val($(this).attr('data-serie')).prop('readonly', true);
					$('#dosf_so_ruts_linked').val($(this).attr('data-ruts'));
				});

				dttblItemDosfChecker();
			}

			function dttblItemDosfChecker(){
				let i;
				istr = [];
				let dcdAr = $('.dosf-checker');
				if( dcdAr.length > 0 ){
					for( i = 0; i < dcdAr.length; i++ ){
						if( $( dcdAr[i] ).is(":checked") )
							istr.push(  $( dcdAr[i] ).closest('tr').attr('id') );
					}
					if( istr.length > 0 ){
						if( $('#rem-dosf').hasClass('disabled') ){
							$('#rem-dosf').removeClass('disabled');
						}
						
					} else {
						if( !$('#rem-dosf').hasClass('disabled') ){
							$('#rem-dosf').addClass('disabled');
						}
					}
				}else{
					if( !$('#rem-dosf').hasClass('disabled') ){
						$('#rem-dosf').addClass('disabled');
					}
				}
			}

			function sendDosfRemovRequest(){
				const ac = {
					method: 'DELETE',
					url: dosf_config.urlRemSO,
					data: JSON.stringify({istr: istr}),
					accepts: 'application/json; charset=UTF-8',
					contentType: 'application/json; charset=UTF-8',
					complete: function(jqXHR, textStatus){
						
						$.unblockUI();
						dttbl.ajax.reload();
					}
				}

				$.ajax(ac);
			}

			function dttblItemActionReqRemoveDosf(){
				istr = [ $(this).closest('tr').attr('id') ];
				delConfirmtnDlg.dialog('open');
			}

			function dttblItemActionReqSendDownloadCodeEmail(){
				$.blockUI(bluckUICOnfig);
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

			function dttblItemActionReqSendExprWrngEmail(){
				$.blockUI(bluckUICOnfig);
				const dosf_id = $(this).parent().parent().parent().attr('id');
				const ajxSettings = {
					method: 'GET',
					url: dosf_config.urlSndExpWrng + dosf_id,
					accepts: 'application/json; charset=UTF-8',
					contentType: 'application/json; charset=UTF-8',
					complete: onDosfReqSendExprWrngComplete,
					success: onDosfReqSendExprWrngSuccess,
					error: onDosfReqSendExprWrngError
				}
				
				$.ajax(ajxSettings);
			}

			function onDosfReqSendDwldCdComplete( jqXHR, textStatus ){
				// Desactivar icono de progreso.
				$.unblockUI();
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

			function onDosfReqSendExprWrngComplete( jqXHR, textStatus ){
				// Desactivar icono de progreso.
				$.unblockUI();
			}

			function onDosfReqSendExprWrngSuccess( data,  textStatus,  jqXHR ){
				if ( data && dttbl && ( data.dosfExprtWrngSend_email_sent || data.expire_warn_attempt ) ) {
					dttbl.ajax.reload( null, false );
				}
			}

			function onDosfReqSendExprWrngError( jqXHR, textStatus, errorThrown ){
				console.log('Error al enviar la Solicitud de envío de email de envío de alerta de vencimiento');
				console.log('Respuesta del servidor:');
				console.log(jqXHR);
			}

			function resetDosfAddFields(){
				$( '#dosf_so_ruts_linked' ).val('');
				$( '#dosf_attachment_id').val(''),
				$( '#dosf_so_emision' ).val('');
				$( '#dosf-file-selectd' ).text(''),
				$( '#dosf_so_serie' ).val('').prop('readonly', false);
				$( '#dosf_so_cert_title' ).val('');
				Object.keys(recipientChoices).forEach(function(id) {
					recipientChoices[id].clearStore();
				});
				
				if( !$('.dosf-admin-add-so .notice.notice-error').hasClass('hidden') ){
					$('.dosf-admin-add-so .notice.notice-error').addClass('hidden')
				}
			}

			function fillEmails(selector, raw) {
				const values = String(raw || '').split(',').map(function(item){ return item.trim(); }).filter(Boolean);
				selector.clearStore();
				if (values.length) {
					selector.setValue(values);
				}
			}

			function dumpDataToDosfAddFields(){
				const row = $(currentEditionDosfTR);
				$( '#dosf_so_serie' ).val(row.attr('data-serie')).prop('readonly', true);
				$( '#dosf_so_cert_title' ).val(row.attr('data-cert-title'));
				$( '#dosf_so_emision' ).val(row.attr('data-emision'));
				$( '#dosf_so_ruts_linked' ).val(row.attr('data-ruts'));
				let storedRecipients = {};
				try {
					storedRecipients = JSON.parse(row.attr('data-recipients') || '{}') || {};
				} catch (e) {
					storedRecipients = {};
				}
				Object.keys(recipientChoices).forEach(function(id) {
					fillEmails(recipientChoices[id], storedRecipients[id] || '');
				});
				$( '#dosf_attachment_id').val( row.attr('data-attachment-id') );
				$( '#dosf-file-selectd' ).text(row.attr('data-file'));
				
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

				if( data['dosfAddNew_post_status'] == 'ok' ^ data['dosfUpdate_post_status'] == 'ok'){
					dosfAddNewSentTryErrorCondMsg = '';
					dttbl.ajax.reload();
				}

				if( data['dosfAddNew_post_status'] == 'error' && data['err_code'] == '403' ){
					dosfAddNewSentTryErrorCondMsg = 'Ya existe un certificado con ese título en esta serie.'
				}
				
			}

			function onDosfNewComplete( jqXHR, textStatus ){
				setWidgetsForDosfAddedOrCanceled();
				$.unblockUI();
			}

			function setWidgetsForDosfSendingToServer(){
				if( !$('.dosf-admin-add-so .notice.notice-error').hasClass('hidden') ){
					$('.dosf-admin-add-so .notice.notice-error').addClass('hidden')
				}
			}

			$('#rem-dosf').click(function(evn){
				evn.preventDefault();

				if( !$(this).hasClass('disabled') ){
					delConfirmtnDlg.dialog('open');
				}

			});

			$( '.dosf-admin-add-so .actions-wrapper .save' ).on('click',function(event){
				event.preventDefault();

				$.blockUI(bluckUICOnfig); 

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
					'serie': 			$('#dosf_so_serie').val(),
					'cert_title': 		$('#dosf_so_cert_title').val(),
					'recipients':		recipientsPayload(),
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
					'serial-search-label'			: $('#serial-search-label').val(),
					'use-issue-date'				: $('#use-issue-date').is(':checked'),
					'expire-period-nmb'				: $('#expire-period-nmb').val(),
					'expire-period-unit'			: $('#expire-period-unit').val(),
					'validity-date-format'		: $('#validity-date-format').val(),
					'ebep-nmb'						: $('#ebep-nmb').val(),
					'ebep-unit'						: $('#ebep-unit').val(),
					'expire-warn-max'				: $('#expire-warn-max').val(),
					'monitor-expire-interval'		: $('#monitor-expire-interval').val(),
					'pm-download-code-popup-id'		: parseInt($('#pm-download-code-popup-id').val()) || 0,
					'download-code-valid-nmb'		: $('#download-code-valid-nmb').val(),
					'download-code-valid-unit'		: $('#download-code-valid-unit').val(),
					'download-code-reuse-nmb'		: $('#download-code-reuse-nmb').val(),
					'download-code-reuse-unit'		: $('#download-code-reuse-unit').val(),
					'status-tpl-vigente'			: $('#status-tpl-vigente').val(),
					'status-tpl-vencido'			: $('#status-tpl-vencido').val(),
					'delete-file-after-expire'	: $('#delete-file-after-expire').is(':checked'),
					'delete-file-after-nmb'		: $('#delete-file-after-nmb').val(),
					'delete-file-after-unit'		: $('#delete-file-after-unit').val(),
					'email-tpl-download-subject'	: $('#email-tpl-download-subject').val(),
					'email-tpl-download-html'		: $('#email-tpl-download-html').val(),
					'email-tpl-expire-subject'	: $('#email-tpl-expire-subject').val(),
					'email-tpl-expire-html'		: $('#email-tpl-expire-html').val(),
					'page-heading'				: $('#dosf-page-heading').val(),
					'menu-label'					: $('#dosf-menu-label').val(),
					'menu-icon'					: $('#dosf-menu-icon').val()
				};
				config['recipient-sets'] = collectRecipientSets();
				$('.dosf-mail-to').each(function() {
					config[$(this).attr('data-kind') + '-mail-to-' + $(this).attr('data-set-id')] = $(this).is(':checked');
				});

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
				var setNameMissing = false;
				$('#dosf-recipient-set-list .dosf-recipient-set-name').each(function(){
					if (!$.trim($(this).val())) {
						setNameMissing = true;
					}
				});
				if (setNameMissing) {
					window.alert('Cada conjunto de destinatarios necesita un nombre.');
					return;
				}
				setPlusOptionsFieldsInProcessingMode();

				const cfg = preparePlusOptions();

				// preparando la configuración de la llamada a endpoint para la configuración adicional.
				var ajxSettings = {
					url: dosf_config.urlUpdPlusOpts,
					method:'POST',
					accepts: 'application/json; charset=UTF-8',
					contentType: 'application/json; charset=UTF-8',
					data: cfg,
					beforeSend: function( xhr ){
						xhr.setRequestHeader( 'X-WP-Nonce', dosf_config.restNonce );
					},
					complete: function(jqXHR, textStatus){
						setPlusOptionsFieldsInNormalMode();
					},
					success: function(data,  textStatus,  jqXHR){
						var menuDirty = $('#dosf-menu-label').length && (
							$('#dosf-page-heading').val() !== $('#dosf-page-heading').attr('data-saved') ||
							$('#dosf-menu-label').val() !== $('#dosf-menu-label').attr('data-saved') ||
							$('#dosf-menu-icon').val() !== $('#dosf-menu-icon').attr('data-saved')
						);
						if ((recipientSetsDirty || menuDirty) && data && !data.error) {
							window.location.reload();
						}
					},
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

			function recipientsPayload() {
				const out = {};
				Object.keys(recipientChoices).forEach(function(id) {
					out[id] = recipientChoices[id].getValue(true);
				});
				return out;
			}

			function collectRecipientSets() {
				const sets = [];
				$('#dosf-recipient-set-list .dosf-recipient-set').each(function() {
					sets.push({
						id: $(this).attr('data-set-id'),
						name: $(this).find('.dosf-recipient-set-name').val()
					});
				});
				return sets;
			}

			function newRecipientSetId() {
				let hex = '';
				while (hex.length < 8) {
					hex += Math.random().toString(16).slice(2);
				}
				return 'set_' + hex.slice(0, 8);
			}

			$('.dosf-recipient-input').each(function() {
				const id = $(this).attr('data-set-id');
				recipientChoices[id] = new Choices(this, choicesEmlsCfg);
			});

			$('#dosf-recipient-set-add').on('click', function() {
				const name = $.trim($('#dosf-recipient-set-new').val());
				if (!name) {
					return;
				}
				const id = newRecipientSetId();
				const row = $('<div class="dosf-recipient-set"></div>').attr('data-set-id', id);
				row.append($('<input type="text" class="dosf-recipient-set-name regular-text" maxlength="80">').val(name));
				row.append($('<button type="button" class="button dosf-recipient-set-remove">Eliminar</button>'));
				$('#dosf-recipient-set-list').append(row);
				['download', 'expire'].forEach(function(kind) {
					const mailId = kind + '-mail-to-' + id;
					const wrap = $('<div class="input dosf-mail-to-row"></div>').attr('data-set-id', id);
					wrap.append($('<input type="checkbox" class="dosf-mail-to">').attr({ id: mailId, 'data-kind': kind, 'data-set-id': id }));
					wrap.append($('<label class="dosf-mail-to-label"></label>').attr('for', mailId).text(name));
					$('.dosf-mail-kind[data-kind="' + kind + '"] .dosf-mail-kind-sets').append(wrap);
				});
				$('#dosf-recipient-set-new').val('');
				recipientSetsDirty = true;
			});

			$(document).on('click', '.dosf-recipient-set-remove', function() {
				const id = $(this).closest('.dosf-recipient-set').attr('data-set-id');
				$('.dosf-mail-to-row[data-set-id="' + id + '"]').remove();
				$(this).closest('.dosf-recipient-set').remove();
				recipientSetsDirty = true;
			});

			$(document).on('input', '.dosf-recipient-set-name', function() {
				const id = $(this).closest('.dosf-recipient-set').attr('data-set-id');
				const name = $(this).val();
				$('.dosf-mail-to-row[data-set-id="' + id + '"] .dosf-mail-to-label').text(name);
				recipientSetsDirty = true;
			});



			let restoredCatalog = $.isArray( dosf_config.restoredCatalog ) ? dosf_config.restoredCatalog : [];

			function renderRestoredCatalog() {
				const $list = $('#dosf-catalog-list');
				$list.empty();
				if ( ! restoredCatalog.length ) {
					$list.append( '<p>No hay archivos restaurados.</p>' );
					return;
				}
				$.each( restoredCatalog, function( index, item ) {
					const button = $('<button type="button" class="button dosf-catalog-pick"></button>');
					button.attr( 'data-id', item.id );
					button.attr( 'data-filename', item.filename );
					button.text( item.label );
					$list.append( button );
				});
			}

			if ( $('#dosf-catalog-dialog').length ) {
				$('#dosf-catalog-dialog').dialog({
					modal: true,
					autoOpen: false,
					width: Math.min( 520, $(window).width() - 48 ),
					title: 'Catálogo del plugin',
					closeText: 'Cerrar'
				});
			}

			$('#browse-catalog').on('click', function( event ) {
				event.preventDefault();
				renderRestoredCatalog();
				$('#dosf-catalog-dialog').dialog('open');
			});

			$(document).on('click', '.dosf-catalog-pick', function() {
				$('#dosf_attachment_id').val( $(this).attr('data-id') );
				$('#dosf-file-selectd').text( $(this).attr('data-filename') );
				$('#dosf-catalog-dialog').dialog('close');
			});

			function expiredFileRequest( op, attachmentId, $button ) {
				$button.prop( 'disabled', true );
				$.post( ajaxurl, {
					action: 'dosf_expired_file',
					op: op,
					attachment_id: attachmentId,
					_ajax_nonce: dosf_config.expiredNonce
				}).done(function( response ) {
					if ( ! response || ! response.success ) {
						window.alert( response && response.data && response.data.message ? response.data.message : 'No se pudo completar la acción.' );
						$button.prop( 'disabled', false );
						return;
					}
					restoredCatalog = $.isArray( response.data.catalog ) ? response.data.catalog : [];
					if ( op === 'delete' ) {
						$('.dosf-expired-files tr[data-attachment="' + attachmentId + '"]').remove();
						if ( ! $('.dosf-expired-files tbody tr').length ) {
							$('.dosf-expired-files table').replaceWith( '<p>No hay certificados vencidos con un archivo guardado.</p>' );
						}
						return;
					}
					$('.dosf-expired-restore[data-attachment="' + attachmentId + '"]').prop( 'disabled', true ).text( 'En el catálogo' );
				}).fail(function( xhr ) {
					let message = 'No se pudo completar la acción.';
					if ( xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ) {
						message = xhr.responseJSON.data.message;
					}
					window.alert( message );
					$button.prop( 'disabled', false );
				});
			}

			$(document).on('click', '.dosf-expired-restore', function() {
				expiredFileRequest( 'restore', $(this).attr('data-attachment'), $(this) );
			});

			let expiredDeleteAttachmentId = '';
			let $expiredDeleteButton = null;
			const expiredDeleteDlg = $('#dosf-expired-delete-dlg').dialog({
				modal: true,
				autoOpen: false,
				draggable: false,
				resizable: false,
				width: 420,
				appendTo: 'body',
				closeText: 'Cerrar',
				buttons: [
					{
						text: 'Cancelar',
						click: function() {
							$(this).dialog('close');
						}
					},
					{
						text: 'Eliminar',
						click: function() {
							$(this).dialog('close');
							if ( expiredDeleteAttachmentId && $expiredDeleteButton ) {
								expiredFileRequest( 'delete', expiredDeleteAttachmentId, $expiredDeleteButton );
							}
						}
					}
				],
				open: function() {
					let z = 100000;
					$('.ui-dialog:visible').each(function() {
						const zi = parseInt( $(this).css('z-index'), 10 );
						if ( zi > z ) {
							z = zi;
						}
					});
					$(this).dialog('widget').css('z-index', z + 2);
					$('.ui-widget-overlay').last().css('z-index', z + 1);
				}
			});

			$(document).on('click', '.dosf-expired-delete', function() {
				if ( $(this).prop('disabled') ) {
					return;
				}
				expiredDeleteAttachmentId = $(this).attr('data-attachment');
				$expiredDeleteButton = $(this);
				expiredDeleteDlg.dialog('open');
			});

		});

	})( jQuery );


