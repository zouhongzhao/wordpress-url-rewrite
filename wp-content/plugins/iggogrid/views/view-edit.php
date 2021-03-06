<?php
/**
 * Edit Table View
 *
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Edit Table View class
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class IggoGrid_Edit_View extends IggoGrid_View {

	/**
	 * List of WP feature pointers for this view
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $wp_pointers = array( 'tp09_edit_drag_drop_sort' );

	/**
	 * Set up the view with data and do things that are specific for this view
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action for this view
	 * @param array $data Data for this view
	 */
	public function setup( $action, array $data ) {
		parent::setup( $action, $data );

		if ( isset( $data['table']['is_corrupted'] ) && $data['table']['is_corrupted'] ) {
			$this->add_text_box( 'table-corrupted', array( $this, 'textbox_corrupted_table' ), 'normal' );
			return;
		};

		$action_messages = array(
			'success_save' => __( 'The table was saved successfully.', 'iggogrid' ),
			'success_add' => __( 'The table was added successfully.', 'iggogrid' ),
			'success_copy' => _n( 'The table was copied successfully.', 'The tables were copied successfully.', 1, 'iggogrid' ) . ' ' . sprintf( __( 'You are now seeing the copied table, which has the table ID &#8220;%s&#8221;.', 'iggogrid' ), esc_html( $data['table']['id'] ) ),
			'success_import' => __( 'The table was imported successfully.', 'iggogrid' ),
			'success_import_wp_table_reloaded' => __( 'The table was imported successfully from WP-Table Reloaded.', 'iggogrid' ),
			'error_save' => __( 'Error: The table could not be saved.', 'iggogrid' ),
			'error_delete' => __( 'Error: The table could not be deleted.', 'iggogrid' ),
			'success_save_success_id_change' => __( 'The table was saved successfully, and the table ID was changed.', 'iggogrid' ),
			'success_save_error_id_change' => __( 'The table was saved successfully, but the table ID could not be changed!', 'iggogrid' ),
		);
		// Custom handling instead of $this->process_action_messages(). Also, $action_messages is used below.
		if ( $data['message'] && isset( $action_messages[ $data['message'] ] ) ) {
			$class = ( 'error' == substr( $data['message'], 0, 5 ) || in_array( $data['message'], array( 'success_save_error_id_change' ), true ) ) ? 'error' : 'updated' ;
			$this->add_header_message( "<strong>{$action_messages[ $data['message'] ]}</strong>", $class );
		}

		wp_enqueue_style( 'wp-jquery-ui-dialog' ); // do this here to get CSS into <head>
		wp_enqueue_script( 'wpdialogs' ); // For the Advanced Editor
		add_action( 'admin_footer', array( $this, 'dequeue_media_upload_js' ), 2 ); // remove default media-upload.js, in favor of own code
		add_thickbox();
		add_filter( 'media_view_strings', array( $this, 'change_media_view_strings' ) );
		wp_enqueue_media();

		// Use modified version of wpLink, instead of default version (changes "Title" to "Link Text")
		wp_deregister_script( 'wplink' );
		$version = ( 0 === strpos( $GLOBALS['wp_version'], '3.8' ) ) ? '38' : ''; // temporary backward-compatibility with WordPress 3.8, where we keep loading the old version of the customized wplink script
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		// See wp-includes/script-loader.php for default parameters
		$wplink_url = plugins_url( "admin/js/tp_wplink{$version}{$suffix}.js", IGGOGRID__FILE__ );
		wp_enqueue_script( 'wplink', $wplink_url, array( 'jquery' ), IggoGrid::version, true );
		wp_localize_script( 'wplink', 'wpLinkL10n', array(
			'title' => _x( 'Insert/edit link', 'Insert Link dialog', 'iggogrid' ),
			'update' => _x( 'Update', 'Insert Link dialog', 'iggogrid' ),
			'save' => _x( 'Add Link', 'Insert Link dialog', 'iggogrid' ),
			'noTitle' => _x( '(no title)', 'Insert Link dialog', 'iggogrid' ),
			'noMatchesFound' => _x( 'No matches found.', 'Insert Link dialog', 'iggogrid' ),
			'link_text' => _x( 'Link Text', 'Insert Link dialog', 'iggogrid' ), // Previous strings are default strings, this is the string that the modified tp_wplink.js inserts
		) );

		$this->admin_page->enqueue_style( 'edit' );
		$this->admin_page->enqueue_script( 'edit', array( 'jquery', 'jquery-ui-sortable', 'json2' ), array(
			'options' => array(
				/**
				 * Filter whether debug output shall be printed to the page.
				 *
				 * The value before filtering is determined from the GET parameter "debug" or the WP_DEBUG constant.
				 *
				 * @since 1.4.0
				 *
				 * @param bool $print Whether debug output shall be printed.
				 */
				'print_debug_output' => apply_filters( 'iggogrid_print_debug_output', isset( $_GET['debug'] ) ? ( 'true' == $_GET['debug'] ) : WP_DEBUG ),
				/**
				 * Filter whether the "Advanced Editor" button shall be enabled.
				 *
				 * @since 1.0.0
				 *
				 * @param bool $enable Whether the "Advanced Editor" shall be enabled. Default true.
				 */
				'cells_advanced_editor' => apply_filters( 'iggogrid_edit_cells_advanced_editor', true ),
				/**
				 * Filter whether the size of the table input textareas shall increase when they are focused.
				 *
				 * @since 1.0.0
				 *
				 * @param bool $auto_grow Whether the size of the cell textareas shall increase. Default true.
				 */
				'cells_auto_grow' => apply_filters( 'iggogrid_edit_cells_auto_grow', true ),
				'shortcode' => esc_js( IggoGrid::$shortcode ),
			),
			'strings' => array_merge( array(
				'no_remove_all_rows' => __( 'You can not delete all table rows!', 'iggogrid' ),
				'no_remove_all_columns' => __( 'You can not delete all table columns!', 'iggogrid' ),
				'no_rows_selected' => __( 'You did not select any rows!', 'iggogrid' ),
				'no_columns_selected' => __( 'You did not select any columns!', 'iggogrid' ),
				'append_num_rows_invalid' => __( 'The value for the number of rows is invalid!', 'iggogrid' ),
				'append_num_columns_invalid' => __( 'The value for the number of columns is invalid!', 'iggogrid' ),
				'ays_remove_rows_singular' => _n( 'Do you really want to delete the selected row?', 'Do you really want to delete the selected rows?', 1, 'iggogrid' ),
				'ays_remove_rows_plural' => _n( 'Do you really want to delete the selected row?', 'Do you really want to delete the selected rows?', 2, 'iggogrid' ),
				'ays_remove_columns_singular' => _n( 'Do you really want to delete the selected column?', 'Do you really want to delete the selected columns?', 1, 'iggogrid' ),
				'ays_remove_columns_plural' => _n( 'Do you really want to delete the selected column?', 'Do you really want to delete the selected columns?', 2, 'iggogrid' ),
				'advanced_editor_open' => __( 'Please click into the cell that you want to edit using the &#8220;Advanced Editor&#8221;.', 'iggogrid' ),
				'rowspan_add' => __( 'To combine cells within a column, click into the cell below the cell that has the content the combined cells shall have.', 'iggogrid' ),
				'colspan_add' => __( 'To combine cells within a row, click into the cell to the right of the cell that has the content the combined cells shall have.', 'iggogrid' ),
				'span_add_datatables_warning' => __( 'Attention: You have enabled the usage of the DataTables JavaScript library for features like sorting, search, or pagination.', 'iggogrid' ) . "\n" .
								__( 'Unfortunately, these can not be used in tables with combined cells.', 'iggogrid' ) . "\n" .
								__( 'Do you want to proceed and automatically turn off the usage of DataTables for this table?', 'iggogrid' ),
				'link_add' => __( 'Please click into the cell that you want to add a link to.', 'iggogrid' ) . "\n" .
								__( 'You can then enter the Link URL and Text or choose an existing page or post.', 'iggogrid' ),
				'image_add' => __( 'Please click into the cell that you want to add an image to.', 'iggogrid' ) . "\n" .
								__( 'The Media Library will open, where you can select or upload the desired image or enter the image URL.', 'iggogrid' ) . "\n" .
								sprintf( __( 'Click the &#8220;%s&#8221; button to insert the image.', 'iggogrid' ), __( 'Insert into Post', 'default' ) ),
				'unsaved_changes_unload' => __( 'The changes to this table were not saved yet and will be lost if you navigate away from this page.', 'iggogrid' ),
				'preparing_preview' => __( 'The Table Preview is being loaded...', 'iggogrid' ),
				'preview_error' => __( 'The Table Preview could not be loaded.', 'iggogrid' ),
				'save_changes_success' => __( 'Saving successful', 'iggogrid' ),
				'save_changes_error' => __( 'Saving failed', 'iggogrid' ),
				'saving_changes' => __( 'Changes are being saved...', 'iggogrid' ),
				'table_id_not_empty' => __( 'The Table ID field can not be empty. Please enter a Table ID!', 'iggogrid' ),
				'table_id_not_zero' => __( 'The Table ID &#8220;0&#8221; is not supported. Please enter a different Table ID!', 'iggogrid' ),
				'ays_change_table_id' => __( 'Do you really want to change the Table ID? All Shortcodes for this table in your pages and posts will have to be adjusted!', 'iggogrid' ),
				'extra_css_classes_invalid' => __( 'The entered value in the field &#8220;Extra CSS classes&#8221; is invalid.', 'iggogrid' ),
				'num_pagination_entries_invalid' => __( 'The entered value in the field &#8220;Pagination Entries&#8221; is not a number.', 'iggogrid' ),
				'sort_asc' => __( 'Sort ascending', 'iggogrid' ),
				'sort_desc' => __( 'Sort descending', 'iggogrid' ),
				'no_rowspan_first_row' => __( 'You can not add rowspan to the first row!', 'iggogrid' ),
				'no_colspan_first_col' => __( 'You can not add colspan to the first column!', 'iggogrid' ),
				'no_rowspan_table_head' => __( 'You can not connect cells into the table head row!', 'iggogrid' ),
				'no_rowspan_table_foot' => __( 'You can not connect cells out of the table foot row!', 'iggogrid' ),
			), $action_messages ), // merge this to have messages available for AJAX after save dialog
		) );

		$this->add_text_box( 'head', array( $this, 'textbox_head' ), 'normal' );
		$this->add_text_box( 'buttons-1', array( $this, 'textbox_buttons' ), 'normal' );
		$this->add_meta_box( 'table-information', __( 'Table Information', 'iggogrid' ), array( $this, 'postbox_table_information' ), 'normal' );
// 		$this->add_meta_box( 'table-data', __( 'Table Content', 'iggogrid' ), array( $this, 'postbox_table_data' ), 'normal' );
// 		$this->add_meta_box( 'table-manipulation', __( 'Table Manipulation', 'iggogrid' ), array( $this, 'postbox_table_manipulation' ), 'normal' );
		$this->add_meta_box( 'table-options', __( 'Table Options', 'iggogrid' ), array( $this, 'postbox_table_options' ), 'normal' );
		$this->add_meta_box( 'datatables-features', __( 'Features of the DataTables JavaScript library', 'iggogrid' ), array( $this, 'postbox_datatables_features' ), 'normal' );
		$this->add_text_box( 'hidden-containers', array( $this, 'textbox_hidden_containers' ), 'additional' );
		$this->add_text_box( 'buttons-2', array( $this, 'textbox_buttons' ), 'additional' );
		$this->add_text_box( 'other-actions', array( $this, 'textbox_other_actions' ), 'submit' );
	}

	/**
	 * Dequeue 'media-upload' JavaScript, which gets added by the Media Library,
	 * but is undesired here, as we have a custom function for this (send_to_editor()) and
	 * don't want the tb_position() function for resizing
	 *
	 * @since 1.0.0
	 */
	public function dequeue_media_upload_js() {
		wp_dequeue_script( 'media-upload' );
	}

	/**
	 * Change Media View string "Insert into post" to "Insert into Table"
	 *
	 * @since 1.0.0
	 *
	 * @param array $strings Current set of Media View strings
	 * @return array Changed Media View strings
	 */
	public function change_media_view_strings( array $strings ) {
		$strings['insertIntoPost'] = __( 'Insert into Table', 'iggogrid' );
		return $strings;
	}

	/**
	 * Print hidden field with a nonce for the screen's action, to be transmitted in HTTP requests
	 *
	 * @since 1.0.0
	 * @uses wp_nonce_field()
	 *
	 * @param array $data Data for this screen
	 * @param array $box Information about the text box
	 */
	protected function action_nonce_field( array $data, array $box ) {
		// use custom nonce field here, that includes the table ID
		wp_nonce_field( IggoGrid::nonce( $this->action, $data['table']['id'] ), 'nonce-edit-table' ); echo "\n";
		wp_nonce_field( IggoGrid::nonce( 'preview_table', $data['table']['id'] ), 'nonce-preview-table', false, true );
	}

	/**
	 * Print the content of the "Table Information" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_table_information( $data, $box ) {
		//var_dump(wp_unslash($data["table"]['visibility']['columns']));
?>
<table class="iggogrid-postbox-table fixed">
<tbody>
	<tr class="bottom-border">
		<th class="column-1" scope="row"><label for="table-id"><?php _e( 'Table ID', 'iggogrid' ); ?>:</label></th>
		<td class="column-2">
			<input type="hidden" name="table[id]" id="table-id" value="<?php echo esc_attr( $data['table']['id'] ); ?>" />
			<input type="text" name="table[new_id]" id="table-new-id" value="<?php echo esc_attr( $data['table']['id'] ); ?>" title="<?php esc_attr_e( 'The Table ID can only consist of letters, numbers, hyphens (-), and underscores (_).', 'iggogrid' ); ?>" pattern="[A-Za-z0-9-_]+" required <?php echo ( ! current_user_can( 'iggogrid_edit_table_id', $data['table']['id'] ) ) ? 'readonly ' : ''; ?>/>
			<div style="float: right; margin-right: 1%;"><label for="table-information-shortcode"><?php _e( 'Shortcode', 'iggogrid' ); ?>:</label>
			<input type="text" id="table-information-shortcode" class="table-shortcode" value="<?php echo esc_attr( '[' . IggoGrid::$shortcode . " id={$data['table']['id']} /]" ); ?>" readonly="readonly" /></div>
			<input type="hidden" class ="current_table_rows_show" name="current_show_table_rows" value="<?php echo json_encode($data["table"]['visibility']['rows']);?>" >
			<input type="hidden" class ="current_table_show" name="current_show_column" value='<?php echo json_encode($data["table"]['visibility']['columns']);?>' >
		</td>
	</tr>
	<tr class="top-border">
		<th class="column-1" scope="row"><label for="table-name"><?php _e( 'Table Name', 'iggogrid' ); ?>:</label></th>
		<td class="column-2"><input type="text" name="table[name]" id="table-name" class="large-text" value="<?php echo esc_attr( $data['table']['name'] ); ?>" /></td>
	</tr>
	<tr class="top-border">
		<th class="column-1" scope="row"><label for="table-column"><?php _e( 'Table Column', 'iggogrid' ); ?>:</label></th>
		<td class="column-2"><div class="table_column_div">
				<button type="button"  class="iggogrid_get_column"><?php _e( 'Get Column', 'iggogrid' ); ?></button>		
				 </div>
				<p><?php _e( 'The Table Column or title of your table.', 'iggogrid' ); ?></p>
		</td>
	</tr>
	<tr class="bottom-border">
		<th class="column-1 top-align" scope="row"><label for="table-description"><?php _e( 'Description', 'iggogrid' ); ?>:</label></th>
		<td class="column-2"><textarea name="table[description]" id="table-description" class="large-text" rows="4"><?php echo esc_textarea( $data['table']['description'] ); ?></textarea></td>
	</tr>
	<tr class="top-border">
		<th class="column-1" scope="row"><?php _e( 'Last Modified', 'iggogrid' ); ?>:</th>
		<td class="column-2"><?php printf( __( '%1$s by %2$s', 'iggogrid' ), '<span id="last-modified">' . IggoGrid::format_datetime( $data['table']['last_modified'] ) . '</span>', '<span id="last-editor">' . IggoGrid::get_user_display_name( $data['table']['options']['last_editor'] ) . '</span>' ); ?></td>
	</tr>
</tbody>
</table>
<?php
	}

	/**
	 * Print the content of the "Table Content" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_table_data( $data, $box ) {
		$table = $data['table']['data'];
		$options = $data['table']['options'];
		$visibility = $data['table']['visibility'];
		$rows = count( $table );
		$columns = count( $table[0] );

		$head_row_idx = $foot_row_idx = -1;
		// determine row index of the table head row, by excluding all hidden rows from the beginning
		if ( $options['table_head'] ) {
			for ( $row_idx = 0; $row_idx < $rows; $row_idx++ ) {
				if ( 1 === $visibility['rows'][ $row_idx ] ) {
					$head_row_idx = $row_idx;
					break;
				}
			}
		}
		// determine row index of the table foot row, by excluding all hidden rows from the end
		if ( $options['table_foot'] ) {
			for ( $row_idx = $rows - 1; $row_idx > -1; $row_idx-- ) {
				if ( 1 === $visibility['rows'][ $row_idx ] ) {
					$foot_row_idx = $row_idx;
					break;
				}
			}
		}
?>
<table id="edit-form" class="iggogrid-edit-screen-id-<?php echo esc_attr( $data['table']['id'] ); ?>">
	<thead>
		<tr id="edit-form-head">
			<th></th>
			<th></th>
<?php
	for ( $col_idx = 0; $col_idx < $columns; $col_idx++ ) {
		$column_class = '';
		if ( 0 === $visibility['columns'][ $col_idx ] ) {
			$column_class = ' column-hidden';
		}
		$column = IggoGrid::number_to_letter( $col_idx + 1 );
		echo "\t\t\t<th class=\"head{$column_class}\"><span class=\"sort-control sort-desc hide-if-no-js\" title=\"" . esc_attr__( 'Sort descending', 'iggogrid' ) . "\"><span class=\"sorting-indicator\"></span></span><span class=\"sort-control sort-asc hide-if-no-js\" title=\"" . esc_attr__( 'Sort ascending', 'iggogrid' ) . "\"><span class=\"sorting-indicator\"></span></span><span class=\"move-handle\">{$column}</span></th>\n";
	}
?>
			<th></th>
		</tr>
	</thead>
	<tfoot>
		<tr id="edit-form-foot">
			<th></th>
			<th></th>
<?php
	for ( $col_idx = 0; $col_idx < $columns; $col_idx++ ) {
		$column_class = '';
		if ( 0 === $visibility['columns'][ $col_idx ] ) {
			$column_class = ' class="column-hidden"';
		}
		echo "\t\t\t<th{$column_class}><input type=\"checkbox\" class=\"hide-if-no-js\" />";
		echo "<input type=\"hidden\" class=\"visibility\" name=\"table[visibility][columns][]\" value=\"{$visibility['columns'][ $col_idx ]}\" /></th>\n";
	}
?>
			<th></th>
		</tr>
	</tfoot>
	<tbody id="edit-form-body">
<?php
	foreach ( $table as $row_idx => $row_data ) {
		$row = $row_idx + 1;
		$classes = array();
		if ( $row_idx % 2 == 0 ) {
			$classes[] = 'odd';
		}
		if ( $head_row_idx == $row_idx ) {
			$classes[] = 'head-row';
		} elseif ( $foot_row_idx == $row_idx ) {
			$classes[] = 'foot-row';
		}
		if ( 0 === $visibility['rows'][ $row_idx ] ) {
			$classes[] = 'row-hidden';
		}
		$row_class = ( ! empty( $classes ) ) ? ' class="' . implode( ' ', $classes ) . '"' : '';
		echo "\t\t<tr{$row_class}>\n";
		echo "\t\t\t<td><span class=\"move-handle\">{$row}</span></td>";
		echo "<td><input type=\"checkbox\" class=\"hide-if-no-js\" /><input type=\"hidden\" class=\"visibility\" name=\"table[visibility][rows][]\" value=\"{$visibility['rows'][ $row_idx ]}\" /></td>";
		foreach ( $row_data as $col_idx => $cell ) {
			$column = IggoGrid::number_to_letter( $col_idx + 1 );
			$column_class = '';
			if ( 0 === $visibility['columns'][ $col_idx ] ) {
				$column_class = ' class="column-hidden"';
			}
			$cell = esc_textarea( $cell ); // sanitize, so that HTML is possible in table cells
			echo "<td{$column_class}><textarea name=\"table[data][{$row_idx}][{$col_idx}]\" id=\"cell-{$column}{$row}\" rows=\"1\">{$cell}</textarea></td>";
		}
		echo "<td><span class=\"move-handle\">{$row}</span></td>\n";
		echo "\t\t</tr>\n";
	}
?>
	</tbody>
</table>
<input type="hidden" id="number-rows" name="table[number][rows]" value="<?php echo $rows; ?>" />
<input type="hidden" id="number-columns" name="table[number][columns]" value="<?php echo $columns; ?>" />
<?php
	}

	/**
	 * Print the content of the "Table Manipulation" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_table_manipulation( $data, $box ) {
		$media_library_url = esc_url( add_query_arg( array( 'post_id' => '0', 'type' => 'image', 'tab' => 'library' ), admin_url( 'media-upload.php' ) ) );
?>
<table class="iggogrid-postbox-table fixed hide-if-no-js">
<tbody>
	<tr class="bottom-border">
		<td class="column-1">
			<input type="button" class="button" id="link-add" value="<?php esc_attr_e( 'Insert Link', 'iggogrid' ); ?>" />
			<a href="<?php echo $media_library_url; ?>" class="button" id="image-add"><?php _e( 'Insert Image', 'iggogrid' ); ?></a>
			<input type="button" class="button" id="advanced-editor-open" value="<?php esc_attr_e( 'Advanced Editor', 'iggogrid' ); ?>" />
		</td>
		<td class="column-2">
			<?php _e( 'Combine cells', 'iggogrid' ); ?>:&nbsp;
			<input type="button" class="button" id="span-add-rowspan" value="<?php esc_attr_e( 'in a column (rowspan)', 'iggogrid' ); ?>" />
			<input type="button" class="button" id="span-add-colspan" value="<?php esc_attr_e( 'in a row (colspan)', 'iggogrid' ); ?>" />
			<input type="button" class="button show-help-box" value="<?php esc_attr_e( '?', 'iggogrid' ); ?>" title="<?php esc_attr_e( 'Help on combining cells', 'iggogrid' ); ?>" />
			<div class="hidden-container hidden-help-box-container"><?php
				echo '<p>' . __( 'Table cells can span across more than one column or row.', 'iggogrid' ) . '</p>';
				echo '<p>' . __( 'Combining consecutive cells within the same row is called &#8220;colspanning&#8221;.', 'iggogrid' )
					. ' ' . __( 'Combining consecutive cells within the same column is called &#8220;rowspanning&#8221;.', 'iggogrid' ) . '</p>';
				echo '<p>' . __( 'To combine adjacent cells in a row, add the keyword <code>#colspan#</code> to the cell to the right of the one with the content for the combined cell by using the corresponding button.', 'iggogrid' )
					. ' ' . __( 'To combine adjacent cells in a column, add the keyword <code>#rowspan#</code> to the cell below the one with the content for the combined cell by using the corresponding button.', 'iggogrid' ) . '</p>';
				echo '<p>' . __( 'Repeat this to add the keyword to all cells that shall be connected.', 'iggogrid' ) . '</p>';
				echo '<p><strong>' . __( 'Be aware that the functions of the DataTables JavaScript library will not work on tables which have combined cells.', 'iggogrid' ) . '</strong></p>';
			?></div>
		</td>
	</tr>
	<tr class="top-border">
		<td class="column-1">
			<?php _e( 'Selected rows', 'iggogrid' ); ?>:&nbsp;
			<input type="button" class="button" id="rows-hide" value="<?php esc_attr_e( 'Hide', 'iggogrid' ); ?>" />
			<input type="button" class="button" id="rows-unhide" value="<?php esc_attr_e( 'Show', 'iggogrid' ); ?>" />
		</td>
		<td class="column-2">
			<?php _e( 'Selected columns', 'iggogrid' ); ?>:&nbsp;
			<input type="button" class="button" id="columns-hide" value="<?php esc_attr_e( 'Hide', 'iggogrid' ); ?>" />
			<input type="button" class="button" id="columns-unhide" value="<?php esc_attr_e( 'Show', 'iggogrid' ); ?>" />
		</td>
	</tr>
	<tr class="bottom-border">
		<td class="column-1">
			<?php _e( 'Selected rows', 'iggogrid' ); ?>:&nbsp;
			<input type="button" class="button" id="rows-duplicate" value="<?php esc_attr_e( 'Duplicate', 'iggogrid' ); ?>" />
			<input type="button" class="button" id="rows-insert" value="<?php esc_attr_e( 'Insert', 'iggogrid' ); ?>" />
			<input type="button" class="button" id="rows-remove" value="<?php esc_attr_e( 'Delete', 'iggogrid' ); ?>" />
		</td>
		<td class="column-2">
			<?php _e( 'Selected columns', 'iggogrid' ); ?>:&nbsp;
			<input type="button" class="button" id="columns-duplicate" value="<?php esc_attr_e( 'Duplicate', 'iggogrid' ); ?>" />
			<input type="button" class="button" id="columns-insert" value="<?php esc_attr_e( 'Insert', 'iggogrid' ); ?>" />
			<input type="button" class="button" id="columns-remove" value="<?php esc_attr_e( 'Delete', 'iggogrid' ); ?>" />
		</td>
	</tr>
	<tr class="top-border">
		<td class="column-1">
			<?php printf( __( 'Add %s row(s)', 'iggogrid' ), '<input type="number" id="rows-append-number" class="small-text numbers-only" title="' . esc_attr__( 'This field must contain a positive number.', 'iggogrid' ) . '" value="1" min="1" max="99999" maxlength="5" required />' ); ?>&nbsp;<input type="button" class="button" id="rows-append" value="<?php esc_attr_e( 'Add', 'iggogrid' ); ?>" />
		</td>
		<td class="column-2">
			<?php printf( __( 'Add %s column(s)', 'iggogrid' ), '<input type="number" id="columns-append-number" class="small-text numbers-only" title="' . esc_attr__( 'This field must contain a positive number.', 'iggogrid' ) . '" value="1" min="1" max="99999" maxlength="5" required />' ); ?>&nbsp;<input type="button" class="button" id="columns-append" value="<?php esc_attr_e( 'Add', 'iggogrid' ); ?>" />
		</td>
	</tr>
</table>
<p class="hide-if-js"><?php _e( 'To use the Table Manipulation features, JavaScript needs to be enabled in your browser.', 'iggogrid' ); ?></p>
<?php
	}

	/**
	 * Print the "Preview" and "Save Changes" button
	 *
	 * @since 1.0.0
	 */
	public function textbox_buttons( $data, $box ) {
		$preview_url = IggoGrid::url( array( 'action' => 'preview_table', 'item' => $data['table']['id'], 'return' => 'edit', 'return_item' => $data['table']['id'] ), true, 'admin-post.php' );

		echo '<p class="submit">';
		if ( current_user_can( 'iggogrid_preview_table', $data['table']['id'] ) ) {
			echo '<a href="' . $preview_url . '" class="button button-large show-preview-button" target="_blank">' . __( 'Preview', 'iggogrid' ) . '</a>';
		}
		?>
			<input type="button" class="button button-primary button-large save-changes-button hide-if-no-js" value="<?php esc_attr_e( 'Save Changes', 'iggogrid' ); ?>" />
			<input type="submit" class="button button-primary button-large hide-if-js" value="<?php esc_attr_e( 'Save Changes', 'iggogrid' ); ?>" />
		<?php
		echo '</p>';
	}

	/**
	 * Print the "Delete Table" and "Export Table" buttons
	 *
	 * @since 1.0.0
	 */
	public function textbox_other_actions( $data, $box ) {
		$user_can_copy_table = current_user_can( 'iggogrid_copy_table', $data['table']['id'] );
// 		$user_can_export_table = current_user_can( 'iggogrid_export_table', $data['table']['id'] );
		$user_can_delete_table = current_user_can( 'iggogrid_delete_table', $data['table']['id'] );

		if ( ! $user_can_copy_table && ! $user_can_export_table && ! $user_can_delete_table ) {
			return;
		}

		echo '<p class="submit">';
		echo __( 'Other Actions', 'iggogrid' ) . ':&nbsp; ';
		if ( $user_can_copy_table ) {
			echo '<a href="' . IggoGrid::url( array( 'action' => 'copy_table', 'item' => $data['table']['id'], 'return' => 'edit' ), true, 'admin-post.php' ) . '" class="button">' . __( 'Copy Table', 'iggogrid' ) . '</a> ';
		}
// 		if ( $user_can_export_table ) {
// 			echo '<a href="' . IggoGrid::url( array( 'action' => 'export', 'table_id' => $data['table']['id'] ) ) . '" class="button">' . __( 'Export Table', 'iggogrid' ) . '</a> ';
// 		}
		if ( $user_can_delete_table ) {
			echo '<a href="' . IggoGrid::url( array( 'action' => 'delete_table', 'item' => $data['table']['id'], 'return' => 'edit', 'return_item' => $data['table']['id'] ), true, 'admin-post.php' ) . '" class="button delete-link">' . __( 'Delete Table', 'iggogrid' ) . '</a>';
		}
		echo '</p>';
	}

	/**
	 * Print the hidden containers for the Advanced Editor and the Preview
	 *
	 * @since 1.0.0
	 */
	public function textbox_hidden_containers( $data, $box ) {
?>
<div class="hidden-container">
	<div id="advanced-editor">
	<?php
		$wp_editor_options = array(
			'textarea_rows' => 10,
			'tinymce' => false,
			'quicktags' => array(
				'buttons' => 'strong,em,link,del,ins,img,code,spell,close',
			),
		);
		wp_editor( '', 'advanced-editor-content', $wp_editor_options );
	?>
	<div class="submitbox">
		<a href="#" class="submitdelete" id="advanced-editor-cancel"><?php _e( 'Cancel', 'iggogrid' ); ?></a>
		<input type="button" class="button button-primary button-large" id="advanced-editor-confirm" value="<?php esc_attr_e( 'OK', 'iggogrid' ); ?>" />
	</div>
	</div>
</div>
<div id="preview-container" class="hidden-container">
	<div id="table-preview"></div>
</div>
<?php
	}

	/**
	 * Print the content of the "Table Options" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_table_options( $data, $box ) {
		$options = $data['table']['options'];
?>
<table class="iggogrid-postbox-table fixed">
<tbody>
	<tr>
		<th class="column-1" scope="row"><?php _e( 'Table Head Row', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-table-head"><input type="checkbox" id="option-table-head" name="table[options][table_head]" value="true"<?php checked( $options['table_head'] ); ?> /> <?php _e( 'The first row of the table is the table header.', 'iggogrid' ); ?></label></td>
	</tr>
	<tr class="bottom-border">
		<th class="column-1" scope="row"><?php _e( 'Table Foot Row', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-table-foot"><input type="checkbox" id="option-table-foot" name="table[options][table_foot]" value="true"<?php checked( $options['table_foot'] ); ?> /> <?php _e( 'The last row of the table is the table footer.', 'iggogrid' ); ?></label></td>
	</tr>
	<tr class="top-border">
		<th class="column-1" scope="row"><?php _e( 'Alternating Row Colors', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-alternating-row-colors"><input type="checkbox" id="option-alternating-row-colors" name="table[options][alternating_row_colors]" value="true"<?php checked( $options['alternating_row_colors'] ); ?> /> <?php _e( 'The background colors of consecutive rows shall alternate.', 'iggogrid' ); ?></label></td>
	</tr>
	<tr class="bottom-border">
		<th class="column-1" scope="row"><?php _e( 'Row Hover Highlighting', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-row-hover"><input type="checkbox" id="option-row-hover" name="table[options][row_hover]" value="true"<?php checked( $options['row_hover'] ); ?> /> <?php _e( 'Highlight a row while the mouse cursor hovers above it by changing its background color.', 'iggogrid' ); ?></label></td>
	</tr>
	<tr class="top-border">
		<th class="column-1" scope="row"><?php _e( 'Print Table Name', 'iggogrid' ); ?>:</th>
		<?php
			$position_select = '<select id="option-print-name-position" name="table[options][print_name_position]">';
			$position_select .= '<option' . selected( 'above', $options['print_name_position'], false ) . ' value="above">' . __( 'above', 'iggogrid' ) . '</option>';
			$position_select .= '<option' . selected( 'below', $options['print_name_position'], false ) . ' value="below">' . __( 'below', 'iggogrid' ) . '</option>';
			$position_select .= '</select>';
		?>
		<td class="column-2"><input type="checkbox" id="option-print-name" name="table[options][print_name]" value="true"<?php checked( $options['print_name'] ); ?> /> <?php printf( __( 'Show the table name %s the table.', 'iggogrid' ), $position_select ); ?></td>
	</tr>
	<tr class="bottom-border">
		<th class="column-1" scope="row"><?php _e( 'Print Table Description', 'iggogrid' ); ?>:</th>
		<?php
			$position_select = '<select id="option-print-description-position" name="table[options][print_description_position]">';
			$position_select .= '<option' . selected( 'above', $options['print_description_position'], false ) . ' value="above">' . __( 'above', 'iggogrid' ) . '</option>';
			$position_select .= '<option' . selected( 'below', $options['print_description_position'], false ) . ' value="below">' . __( 'below', 'iggogrid' ) . '</option>';
			$position_select .= '</select>';
		?>
		<td class="column-2"><input type="checkbox" id="option-print-description" name="table[options][print_description]" value="true"<?php checked( $options['print_description'] ); ?> /> <?php printf( __( 'Show the table description %s the table.', 'iggogrid' ), $position_select ); ?></td>
	</tr>
	<tr class="top-border">
		<th class="column-1" scope="row"><?php _e( 'Extra CSS Classes', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-extra-css-classes"><input type="text" id="option-extra-css-classes" class="large-text" name="table[options][extra_css_classes]" value="<?php echo esc_attr( $options['extra_css_classes'] ); ?>" title="<?php esc_attr_e( 'This field can only contain letters, numbers, spaces, hyphens (-), and underscores (_).', 'iggogrid' ); ?>" pattern="[A-Za-z0-9- _]*" /><p class="description"><?php echo __( 'Additional CSS classes for styling purposes can be entered here.', 'iggogrid' ) . ' ' . sprintf( __( 'This is NOT the place to enter <a href="%s">Custom CSS</a> code!', 'iggogrid' ), IggoGrid::url( array( 'action' => 'options' ) ) ); ?></p></label></td>
	</tr>
</tbody>
</table>
<?php
	}

	/**
	 * Print the content of the "Features of the DataTables JavaScript library" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_datatables_features( $data, $box ) {
		$options = $data['table']['options'];
?>
<p id="notice-datatables-head-row" class="hide-if-js"><?php printf( __( 'These features and options are only available, when the &#8220;%1$s&#8221; checkbox in the &#8220;%2$s&#8221; section is checked.', 'iggogrid' ), __( 'Table Head Row', 'iggogrid' ), __( 'Table Options', 'iggogrid' ) ); ?></p>
<table class="iggogrid-postbox-table fixed">
<tbody>
	<tr class="bottom-border">
		<th class="column-1" scope="row"><?php _e( 'Use DataTables', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-use-datatables"><input type="checkbox" id="option-use-datatables" name="table[options][use_datatables]" value="true"<?php checked( $options['use_datatables'] ); ?> /> <?php _e( 'Use the following features of the DataTables JavaScript library with this table:', 'iggogrid' ); ?></label></td>
	</tr>
	<tr class="top-border">
		<th class="column-1" scope="row"><?php _e( 'Sorting', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-datatables-sort"><input type="checkbox" id="option-datatables-sort" name="table[options][datatables_sort]" value="true"<?php checked( $options['datatables_sort'] ); ?> /> <?php _e( 'Enable sorting of the table by the visitor.', 'iggogrid' ); ?></label></td>
	</tr>
	<tr>
		<th class="column-1" scope="row"><?php _e( 'Search/Filtering', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-datatables-filter"><input type="checkbox" id="option-datatables-filter" name="table[options][datatables_filter]" value="true"<?php checked( $options['datatables_filter'] ); ?> /> <?php _e( 'Enable the visitor to filter or search the table. Only rows with the search word in them are shown.', 'iggogrid' ); ?></label></td>
	</tr>
	<tr>
		<th class="column-1" scope="row" style="vertical-align: top;"><?php _e( 'Pagination', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-datatables-paginate"><input type="checkbox" id="option-datatables-paginate" name="table[options][datatables_paginate]" value="true"<?php checked( $options['datatables_paginate'] ); ?> /> <?php _e( 'Enable pagination of the table (viewing only a certain number of rows at a time) by the visitor.', 'iggogrid' ); ?></label><br />
		<label for="option-datatables-paginate_entries"><input type="checkbox" style="visibility: hidden;" <?php // Dummy checkbox for space alignment ?>/> <?php printf( __( 'Show %s rows per page.', 'iggogrid' ), '<input type="number" id="option-datatables-paginate_entries" name="table[options][datatables_paginate_entries]" value="' . intval( $options['datatables_paginate_entries'] ) . '" min="1" max="99999" maxlength="5" required />' ); ?></label></td>
	</tr>
	<tr>
		<th class="column-1" scope="row"><?php _e( 'Pagination Length Change', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-datatables-lengthchange"><input type="checkbox" id="option-datatables-lengthchange" name="table[options][datatables_lengthchange]" value="true"<?php checked( $options['datatables_lengthchange'] ); ?> /> <?php _e( 'Allow the visitor to change the number of rows shown when using pagination.', 'iggogrid' ); ?></label></td>
	</tr>
	<tr>
		<th class="column-1" scope="row"><?php _e( 'Info', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-datatables-info"><input type="checkbox" id="option-datatables-info" name="table[options][datatables_info]" value="true"<?php checked( $options['datatables_info'] ); ?> /> <?php _e( 'Enable the table information display, with information about the currently visible data, like the number of rows.', 'iggogrid' ); ?></label></td>
	</tr>
	<tr<?php echo current_user_can( 'unfiltered_html' ) ? ' class="bottom-border"' : ''; ?>>
		<th class="column-1" scope="row"><?php _e( 'Horizontal Scrolling', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-datatables-scrollx"><input type="checkbox" id="option-datatables-scrollx" name="table[options][datatables_scrollx]" value="true"<?php checked( $options['datatables_scrollx'] ); ?> /> <?php _e( 'Enable horizontal scrolling, to make viewing tables with many columns easier.', 'iggogrid' ); ?></label></td>
	</tr>
	<?php
		// "Custom Commands" must only be available to trusted users
	?>
	<tr class="<?php echo current_user_can( 'unfiltered_html' ) ? 'top-border' : 'hidden'; ?>">
		<th class="column-1" scope="row"><?php _e( 'Custom Commands', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-datatables-custom-commands"><input type="text" id="option-datatables-custom-commands" class="large-text" name="table[options][datatables_custom_commands]" value="<?php echo esc_attr( $options['datatables_custom_commands'] ); ?>" /><p class="description"><?php echo __( 'Additional parameters from the <a href="http://www.datatables.net/">DataTables documentation</a> to be added to the JS call.', 'iggogrid' ) . ' ' . __( 'For advanced use only.', 'iggogrid' ); ?></p></label></td>
	</tr>
</tbody>
</table>
<?php
	}

	/**
	 * Print a notification about a corrupted table
	 *
	 * @since 1.4.0
	 */
	public function textbox_corrupted_table( $data, $box ) {
		?>
		<div class="error">
			<p><strong><?php _e( 'Attention: Unfortunately, an error occured.', 'iggogrid' ); ?></strong></p>
			<p>
				<?php
					printf( __( 'The internal data of table &#8220;%1$s&#8221 (ID %2$s) is corrupted.', 'iggogrid' ), esc_html( $data['table']['name'] ), esc_html( $data['table']['id'] ) );
					echo ' ';
					printf( __( 'The following error was registered: <code>%s</code>.', 'iggogrid' ), esc_html( $data['table']['json_error'] ) );
				?>
			</p>
			<p>
				<?php
					_e( 'Because of this error, the table can not be edited at this time, to prevent possible further data loss.', 'iggogrid' );
					echo ' ';
					printf( __( 'Please see the <a href="%s">IggoGrid FAQ page</a> for further instructions.', 'iggogrid' ), 'http://iggogrid.org/faq/corrupted-tables/' );
				?>
			</p>
			<p>
				<?php
					echo '<a href="' . IggoGrid::url( array( 'action' => 'list' ) ) . '" class="button">' . __( 'Back to the List of Tables', 'iggogrid' ) . '</a>';
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Print the screen head text
	 *
	 * @since 1.0.0
	 */
	public function textbox_head( $data, $box ) {
		?>
	<p>
		<?php printf( __( 'On this screen, you can edit the content and structure of the table with the ID %s.', 'iggogrid' ), esc_html( $data['table']['id'] ) ); ?>
		<?php _e( 'For example, you can insert things like text, images, or links into the table, or change the used table features. You can also insert, delete, move, hide, and swap columns and rows.', 'iggogrid' ); ?>
	</p>
	<p>
		<?php printf( __( 'To insert the table into a page, post, or text widget, copy the Shortcode %s and paste it at the desired place in the editor.', 'iggogrid' ), '<input type="text" class="table-shortcode table-shortcode-inline" value="' . esc_attr( '[' . IggoGrid::$shortcode . " id={$data['table']['id']} /]" ) . '" readonly="readonly" />' ); ?>
	</p>
		<?php
	}

	/**
	 * Set the content for the WP feature pointer about the drag and drop and sort on the "Edit" screen
	 *
	 * @since 1.0.0
	 */
	public function wp_pointer_tp09_edit_drag_drop_sort() {
		$content  = '<h3>' . __( 'IggoGrid Feature: Moving rows and columns', 'iggogrid' ) . '</h3>';
		$content .= '<p>' . __( 'Did you know? You can drag and drop rows and columns via the row number and the column title. And the arrows next to the column title can be used for sorting.', 'iggogrid' ) . '</p>';

		$this->admin_page->print_wp_pointer_js( 'tp09_edit_drag_drop_sort', '#edit-form-head', array(
			'content'  => $content,
			'position' => array( 'edge' => 'top', 'align' => 'left', 'offset' => '56 2' ),
		) );
	}

} // class IggoGrid_Edit_View
