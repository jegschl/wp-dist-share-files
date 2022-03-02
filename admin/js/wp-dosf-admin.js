
	

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
					data: 'id'
				},
				{
					data: 'wp_obj_id'
				},
				{
					data: 'selection'
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
	});
	
})( jQuery );


