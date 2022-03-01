
	

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

	(function( $ ) {
	'use strict';
	$(document).ready(function ($) {
		$('#addPil').click(function(){
			
			let html_row_pil = '';
			let row_id = $('#rows_pils tr').length;
			
			$('#rows_pils tr').each(function(i){
				$(this).find('.img_url_fld_wrapper label').prop('for','img_url[' + i + ']');
				$(this).find('.ads_url_fld_wrapper label').prop('for','ads_url[' + i + ']');
			});
			
			html_row_pil += '<tr><td><input type="checkbox" name="pil_selection"></td>';
			html_row_pil += '<td>';
			html_row_pil += '<div class="img_url_fld_wrapper">';
			html_row_pil += '<label ';
			html_row_pil += 'for="img_url[' + row_id + ']"';
			html_row_pil += '>Url de la Imagen</label>';
			html_row_pil += '<input type="text" ';
			html_row_pil += 'name="img_url[' + row_id + ']">';
			html_row_pil += '</div>';
			html_row_pil += '<div class="ads_url_fld_wrapper">';
			html_row_pil += '<label ';
			html_row_pil += 'for="ads_url[' + row_id + ']"';	
			html_row_pil +=  '>Url del Ads </label>';
			html_row_pil +=  '<input type="text" ';
			html_row_pil +=  'name="ads_url[' + row_id + ']">';
			html_row_pil +=  '</div>';
			html_row_pil +=  '</td>';
			html_row_pil +=  '<td class="col-action-wrapper">';
			html_row_pil +=  '<div class="action-wrapper remCurrentPil"><i class="fas fa-minus-circle"></i></div>';
			html_row_pil +=  '</td></tr>';
			
			$('#rows_pils').append(html_row_pil);
			
			$('.remCurrentPil').off('click');
			$('.remCurrentPil').click(function(){
				$(this).parent().parent().remove();
			});
		});
		
		$('#remPil').click(function(){
			
			$('input[name="pil_selection"]:checked').parent().parent().remove();
		});
		
		console.log('===== DEfiniendo evento click de objetos con clase remCurrentPil. ===== ');
		$('.remCurrentPil').click(function(){
			$(this).parent().parent().remove();
		});				
	});
	
})( jQuery );


