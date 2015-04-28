/**
 * JavaScript code for the "Add New" screen
 *
 * @package IggoGrid
 * @subpackage Views JavaScript
 * @author Tobias Bäthge
 * @since 1.0.0
 */

/* global validateForm */

jQuery( document ).ready( function( $ ) {
	
	$(document).on('click','.iggogrid_get_column',function(e){
			var tableObj = $("#table-name");
			if($.trim(tableObj.val()) == ''){
				alert('填表名');
				
				return false;
			}
			var data = {
				action:'iggogrid_get_columns',
				table:tableObj.val()
			};
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxurl, data, function(response) {
				if( response.success == true){
					var html = '<select multiple="multiple" size="2" name="columns[]" style="width:200px;height:150px;">';
					$.each(response.message, function(i,val){
						html += '<option style="padding:3px 4px;" value ="'+val+'">'+val+'</option>';
					});
					html += '</select>';
					$(".table_column_div").html(html);
				}
//				$(".table_column_div").append('333344');
//				console.log(response.success);
			},'json');
	});

	
	'use strict';

	/**
	 * Check, whether entered numbers for rows and columns are valid
	 *
	 * @since 1.0.0
	 */
	$( '#iggogrid-page' ).find( 'form' ).on( 'submit', function( /* event */ ) {
		var valid_form = true;

		// remove default values from required placeholders, if no value was entered
		$( '#iggogrid-page' ).find( '.form-required' ).find( '.placeholder' ).each( function() {
			if ( this.value === this.defaultValue ) {
				this.value = '';
				$(this).removeClass( 'placeholder-active' );
			}
		} );

		// WordPress validation function, checks if required fields (.form-required) are non-empty
		if ( ! validateForm( $(this) ) ) {
			valid_form = false;
		}

		// validate numerical values (.form-field-numbers-only): only 1 < x < 9...9 (up to maxlength) are allowed
		$( '#iggogrid-page' ).find( '.form-field-numbers-only' ).find( 'input' ).each( function() {
			var $field = $(this),
				maxlength = parseInt( $field.attr( 'maxlength' ), 10 ),
				regexp_number;

			if ( ! isNaN( maxlength ) ) {
				maxlength += -1; // first number is handled already in RegExp
			} else {
				maxlength = '';
			}

			regexp_number = new RegExp( '^[1-9][0-9]{0,' + maxlength + '}$' );
			if ( regexp_number.test( $field.val() ) ) {
				return; // field is valid
			}

			$field
				.one( 'change', function() { $(this).closest( '.form-invalid' ).removeClass( 'form-invalid' ); } )
				.focus().select()
				.closest( '.form-field' ).addClass( 'form-invalid' );
			valid_form = false;
		} );

		if ( ! valid_form ) {
			return false;
		}
		// at this point, the form is valid and will be submitted

		// remove the default values of optional fields, as we don't want to save those
		$( '#iggogrid-page' ).find( '.placeholder' ).each( function() {
			if ( this.value === this.defaultValue ) {
				this.value = '';
			}
		} );
	} );

} );
