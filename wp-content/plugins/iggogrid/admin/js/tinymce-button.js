/**
 * JavaScript code for the "Table" button in the TinyMCE editor toolbar
 *
 * @package IggoGrid
 * @subpackage Views JavaScript
 * @author Tobias Bäthge
 * @since 1.0.0
 */

/* global tinymce , iggogrid_editor_button*/

( function() {

	'use strict';

	// only do this if TinyMCE is available
	if ( 'undefined' === typeof( tinymce ) ) {
		return;
	}

	/**
	 * Register a button for the TinyMCE (aka Visual Editor) toolbar
	 *
	 * @since 1.0.0
	 */
	tinymce.create( 'tinymce.plugins.IggoGridPlugin', {
		init: function( ed, url ) {
			ed.addCommand( 'IggoGrid_insert_table', window.iggogrid_open_shortcode_thickbox );

			ed.addButton( 'iggogrid_insert_table', {
				title: iggogrid_editor_button.title,
				cmd: 'IggoGrid_insert_table',
				image: url.slice( 0, url.length - 2 ) + 'img/iggogrid-editor-button.png'
			} );
		}
/* // no real need for getInfo(), as it is not displayed/used anywhere
		,
		getInfo: function() {
			return {
				longname: 'IggoGrid',
				author: 'Tobias Bäthge',
				authorurl: 'http://tobias.baethge.com/',
				infourl: 'http://iggogrid.org/',
				version: '1.0.0'
			};
		}
*/
	} );
	tinymce.PluginManager.add( 'iggogrid_tinymce', tinymce.plugins.IggoGridPlugin );

} )();
