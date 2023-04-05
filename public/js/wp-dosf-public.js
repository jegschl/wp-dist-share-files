(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

	let closeForm = true;

	 function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;
	
		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');
	
			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
		return false;
	};

	function onDosfGetUrlError( jqXHR, textStatus, errorThrown ){
		console.log('Error al intentar recuperar la URL de un dosf desde el server.');
		console.log(jqXHR);

		if( $('#download-code-error').hasClass('hidden')  ){
			$('#download-code-error').removeClass('hidden') 
			closeForm = false;
		}
	}

	function onDosfGetUrlSuccess(  data,  textStatus,  jqXHR ){
		if( (data.error != undefined) && !data.error){
			console.log('URL del dosf recuperada correctamente.');
			console.log(data);
			const downloadLink = data['download-link'];
			window.location = downloadLink;
		} else {
			if( $('#download-code-error').hasClass('hidden')  ){
				$('#download-code-error').removeClass('hidden') 
				closeForm = false;
			}
		}
	}

	function onDosfGetUrlComplete( jqXHR, textStatus ){
		//PUM.close(popupDownloadCode);
	}

	let popupDownloadCode = null;

	$(document).ready(function(){
		const dosSearchValue = getUrlParameter(dosfDt.searchFldNm);
		if(dosSearchValue !== false){
			$('.dosf-search-res-row .link a').click(function(e){
				e.preventDefault();
				const falseURL = $(this).attr('href');
				const objid = falseURL.substring(7);
				$('#obj-id').val(objid);
				$('#input-download-code').text('');
				$('#input-download-code').val('');
				
				popupDownloadCode= PUM.getPopup(parseInt(dosfDt.pmDldCodeId));
				PUM.open(popupDownloadCode);
			});

			$('#send-download-code').click(function(e){
				e.preventDefault();
				const dt = {
					'objid': $('#obj-id').val(),
					'dldcd': $('#input-download-code').val()
				};

				const ajxsettings = {
					method: 'POST',
					url	: dosfDt.urlGetDosfURL,
					accepts: 'application/json; charset=UTF-8',
					contentType: 'application/json; charset=UTF-8',
					data: JSON.stringify(dt),
					complete: onDosfGetUrlComplete,
					success: onDosfGetUrlSuccess,
					error: onDosfGetUrlError
				};

				$.ajax(ajxsettings);
			});
		}
	});

})( jQuery );
