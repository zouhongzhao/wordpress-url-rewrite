<?php
/**
 * Admin AJAX Controller for IggoGrid with functionality for the AJAX backend
 *
 * @package IggoGrid
 * @subpackage Controllers
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Admin AJAX Controller class, extends Base Controller Class
 * @package IggoGrid
 * @subpackage Controllers
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class IggoGrid_Admin_AJAX_Controller extends IggoGrid_Controller {

	/**
	 * Initiate Admin AJAX functionality
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Buffer all outputs, to prevent errors/warnings being printed that make the JSON invalid
		ob_start();
		parent::__construct();
// 		add_action('wp_ajax_my_action',array( $this, 'my_action_callback'));
		$ajax_actions = array( 'hide_message', 'save_table', 'preview_table' ,'get_columns');
		foreach ( $ajax_actions as $action ) {
			add_action( "wp_ajax_iggogrid_{$action}", array( $this, "ajax_action_{$action}" ) );
		}
	}
	
	/**
	 * Hide a header message on an admin screen
	 *
	 * @since 1.0.0
	 */
	public function ajax_action_hide_message() {
		if ( empty( $_GET['item'] ) ) {
			wp_die( '0' );
		} else {
			$message_item = $_GET['item'];
		}

		IggoGrid::check_nonce( 'hide_message', $message_item, '_wpnonce', true );

		if ( ! current_user_can( 'iggogrid_list_tables' ) ) {
			wp_die( '-1' );
		}

		$updated_options = array( "message_{$message_item}" => false );
		if ( 'plugin_update' === $message_item ) {
			$updated_options['message_plugin_update_content'] = '';
		}
		IggoGrid::$model_options->update( $updated_options );

		wp_die( '1' );
	}

	/**
	 * Save the table after the "Save Changes" button on the "Edit" screen has been clicked
	 *
	 * @since 1.0.0
	 */
	public function ajax_action_save_table() {
		if ( empty( $_POST['iggogrid'] ) || empty( $_POST['iggogrid']['id'] ) ) {
		wp_die( '-1' );
		} else {
			$edit_table = wp_unslash( $_POST['iggogrid'] );
		}
		//var_dump($edit_table);die();
		// check to see if the submitted nonce matches with the generated nonce we created earlier, dies -1 on fail
		IggoGrid::check_nonce( 'edit', $edit_table['id'], '_ajax_nonce', true );

		// ignore the request if the current user doesn't have sufficient permissions
		if ( ! current_user_can( 'iggogrid_edit_table', $edit_table['id'] ) ) {
			wp_die( '-1' );
		}

		// default response data:
		$success = false;
		$message = 'error_save';
		$error_details = '';
		do { // to be able to "break;" (allows for better readable code)
			// Load existing table from DB
			$existing_table = IggoGrid::$model_table->load( $edit_table['id'], false, true ); // Load table, without table data, but with options and visibility settings
			if ( is_wp_error( $existing_table ) ) { // maybe somehow load a new table here? (IggoGrid::$model_table->get_table_template())?
				// Add an error code to the existing WP_Error
				$existing_table->add( 'ajax_save_table_load', '', $edit_table['id'] );
				$error_details = $this->get_wp_error_string( $existing_table );
				break;
			};
			//var_dump($edit_table);
			$edit_table['visibility']["rows"] =json_decode($edit_table["show_table_rows"]);
			$edit_table['visibility']["columns"] = $edit_table["columns"];
			$edit_table['number']["rows"] = count($edit_table["columns"]);
			$edit_table['number']["columns"] =count($edit_table["columns"]);
			// Check and convert data that was transmitted as JSON
			if ( empty( $edit_table['data'] )
			|| empty( $edit_table['options'] )
			|| empty( $edit_table['visibility'] )
            || !isset($edit_table["columns"])) {
				// Create a new WP_Error
				$empty_data_error = new WP_Error( 'ajax_save_table_data_empty', '', $edit_table['id'] );
				$error_details = $this->get_wp_error_string( $empty_data_error );
				break;
			}
			//var_dump($edit_table);die();
			$edit_table['data'] = json_decode( $edit_table['data'], true );
			$edit_table['options'] = json_decode( $edit_table['options'], true );
		//	$edit_table['visibility'] = json_decode( $edit_table['visibility'], true );
			//var_dump($edit_table);die();
			// Check consistency of new table, and then merge with existing table
			$table = IggoGrid::$model_table->prepare_table( $existing_table, $edit_table, true, true );
		//	var_dump($table);die();
			if ( is_wp_error( $table ) ) {
				// Add an error code to the existing WP_Error
				$table->add( 'ajax_save_table_prepare', '', $edit_table['id'] );
				$error_details = $this->get_wp_error_string( $table );
				break;
			}

			// DataTables Custom Commands can only be edit by trusted users
			if ( ! current_user_can( 'unfiltered_html' ) ) {
				$table['options']['datatables_custom_commands'] = $existing_table['options']['datatables_custom_commands'];
			}

			// Save updated table
			$saved = IggoGrid::$model_table->save( $table );
			if ( is_wp_error( $saved ) ) {
				// Add an error code to the existing WP_Error
				$saved->add( 'ajax_save_table_save', '', $table['id'] );
				$error_details = $this->get_wp_error_string( $saved );
				break;
			}

			// at this point, the table was saved successfully, possible ID change remains
			$success = true;
			$message = 'success_save';

			// Check if ID change is desired
			if ( $table['id'] === $table['new_id'] ) { // if not, we are done
				break;
			}

			// Change table ID
			if ( current_user_can( 'iggogrid_edit_table_id', $table['id'] ) ) {
				$id_changed = IggoGrid::$model_table->change_table_id( $table['id'], $table['new_id'] );
				if ( ! is_wp_error( $id_changed ) ) {
					$message = 'success_save_success_id_change';
					$table['id'] = $table['new_id'];
				} else {
					$message = 'success_save_error_id_change';
					// Add an error code to the existing WP_Error
					$id_changed->add( 'ajax_save_table_id_change', '', $table['new_id'] );
					$error_details = $this->get_wp_error_string( $id_changed );
				}
			} else {
				$message = 'success_save_error_id_change';
				$error_details = 'table_id_could_not_be_changed: capability_check_failed';
			}
		} while ( false ); // do-while-loop through this exactly once, to be able to "break;" early

		// Generate the response
		$response = array( // common for all responses
			'success' => $success,
			'message' => $message
		);
		if ( $success ) {
			$response['table_id'] = $table['id'];
			$response['new_edit_nonce'] = wp_create_nonce( IggoGrid::nonce( 'edit', $table['id'] ) );
			$response['new_preview_nonce'] = wp_create_nonce( IggoGrid::nonce( 'preview_table', $table['id'] ) );
			$response['last_modified'] = IggoGrid::format_datetime( $table['last_modified'] );
			$response['last_editor'] = IggoGrid::get_user_display_name( $table['options']['last_editor'] );
		}
		if ( ! empty( $error_details ) ) {
			$response['error_details'] = esc_html( $error_details );
		}
		// Buffer all outputs, to prevent errors/warnings being printed that make the JSON invalid
		$output_buffer = ob_get_clean();
		if ( ! empty( $output_buffer ) ) {
			$response['output_buffer'] = $output_buffer;
		}

		// Send the response
		wp_send_json( $response );
	}

	/**
	 * Return the live preview data of table that has non-saved changes
	 *
	 * @since 1.0.0
	 */
	public function ajax_action_preview_table() {
		if ( empty( $_POST['iggogrid'] ) || empty( $_POST['iggogrid']['id'] ) ) {
			wp_die( '-1' );
		} else {
			$preview_table = wp_unslash( $_POST['iggogrid'] );
		}

		// check to see if the submitted nonce matches with the generated nonce we created earlier, dies -1 on fail
		IggoGrid::check_nonce( 'preview_table', $preview_table['id'], '_ajax_nonce', true );

		// ignore the request if the current user doesn't have sufficient permissions
		if ( ! current_user_can( 'iggogrid_preview_table', $preview_table['id'] ) ) {
			wp_die( '-1' );
		}

		// default response data:
		$success = false;
		do { // to be able to "break;" (allows for better readable code)
			// Load existing table from DB
			$existing_table = IggoGrid::$model_table->load( $preview_table['id'], false, true ); // Load table, without table data, but with options and visibility settings
			if ( is_wp_error( $existing_table ) ) { // maybe somehow load a new table here? (IggoGrid::$model_table->get_table_template())?
				break;
			}

			// Check and convert data that was transmitted as JSON
			if ( empty( $preview_table['data'] )
			|| empty( $preview_table['options'] )
			|| empty( $preview_table['visibility'] ) ) {
				break;
			}
			$preview_table['data'] = json_decode( $preview_table['data'], true );
			$preview_table['options'] = json_decode( $preview_table['options'], true );
			$preview_table['visibility'] = json_decode( $preview_table['visibility'], true );

			// Check consistency of new table, and then merge with existing table
			$table = IggoGrid::$model_table->prepare_table( $existing_table, $preview_table, true, true );
			if ( is_wp_error( $table ) ) {
				break;
			}

			// DataTables Custom Commands can only be edit by trusted users
			if ( ! current_user_can( 'unfiltered_html' ) ) {
				$table['options']['datatables_custom_commands'] = $existing_table['options']['datatables_custom_commands'];
			}

			// If the ID has changed, and the new ID is valid, render with the new ID (important e.g. for CSS classes/HTML ID)
			if ( $table['id'] !== $table['new_id'] && 0 === preg_match( '/[^a-zA-Z0-9_-]/', $table['new_id'] ) ) {
				$table['id'] = $table['new_id'];
			}

			// at this point, the table data is valid and can be rendered
			$success = true;
		} while ( false ); // do-while-loop through this exactly once, to be able to "break;" early

		if ( $success ) {
			// Create a render class instance
			$_render = IggoGrid::load_class( 'IggoGrid_Render', 'class-render.php', 'classes' );
			// Merge desired options with default render options (see IggoGrid_Controller_Frontend::shortcode_table())
			$default_render_options = $_render->get_default_render_options();
			/** This filter is documented in controllers/controller-frontend.php */
			$default_render_options = apply_filters( 'iggogrid_shortcode_table_default_shortcode_atts', $default_render_options );
			$render_options = shortcode_atts( $default_render_options, $table['options'] );
			/** This filter is documented in controllers/controller-frontend.php */
			$render_options = apply_filters( 'iggogrid_shortcode_table_shortcode_atts', $render_options );
			$_render->set_input( $table, $render_options );
			$head_html = $_render->get_preview_css();
			$custom_css = IggoGrid::$model_options->get( 'custom_css' );
			if ( ! empty( $custom_css ) ) {
				$head_html .= "<style type=\"text/css\">\n{$custom_css}\n</style>\n";
			}

			$body_html = '<div id="iggogrid-page"><p>'
				. __( 'This is a preview of your table.', 'iggogrid' ) . ' '
				. __( 'Because of CSS styling in your theme, the table might look different on your page!', 'iggogrid' ) . ' '
				. __( 'The features of the DataTables JavaScript library are also not available or visible in this preview!', 'iggogrid' ) . '<br />'
				. sprintf( __( 'To insert the table into a page, post, or text widget, copy the Shortcode %s and paste it into the editor.', 'iggogrid' ), '<input type="text" class="table-shortcode table-shortcode-inline" value="' . esc_attr( '[' . IggoGrid::$shortcode . " id={$table['id']} /]" ) . '" readonly="readonly" />' )
				. '</p>' . $_render->get_output() . '</div>';
		} else {
			$head_html = '';
			$body_html = __( 'The preview could not be loaded.', 'iggogrid' );
		}

		// Generate the response
		$response = array(
			'success' => $success,
			'head_html' => $head_html,
			'body_html' => $body_html,
		);
		// Buffer all outputs, to prevent errors/warnings being printed that make the JSON invalid
		$output_buffer = ob_get_clean();
		if ( ! empty( $output_buffer ) ) {
			$response['output_buffer'] = $output_buffer;
		}

		// Send the response
		wp_send_json( $response );
	}

	/**
	 * Retrieve all information of a WP_Error object as a string.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_Error $wp_error A WP_Error object.
	 * @return string All error codes, messages, and data of the WP_Error.
	 */
	protected function get_wp_error_string( $wp_error ) {
		$error_strings = array();
		$error_codes = $wp_error->get_error_codes();
		$error_codes = array_reverse( $error_codes ); // Reverse order to get latest errors first
		foreach ( $error_codes as $error_code ) {
			$error_strings[ $error_code ] = $error_code;
			$error_messages = $wp_error->get_error_messages( $error_code );
			$error_messages = implode( ', ', $error_messages );
			if ( ! empty( $error_messages ) ) {
				$error_strings[ $error_code ] .= " ({$error_messages})";
			}
			$error_data = $wp_error->get_error_data( $error_code );
			if ( ! is_null( $error_data ) ) {
				$error_strings[ $error_code ] .= " [{$error_data}]";
			}
		}
		return implode( ";\n", $error_strings );
	}

	
	public function ajax_action_get_columns() {
		if ( empty( $_POST['table'] ) ) {
			wp_die( '-1' );
		}
		global $wpdb;
		$success = true;
		$flag = true;
		$message = array();
		$table_name = $wpdb->prefix .$_POST['table'];
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$flag = false;
			$table_name = $_POST['table'];
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
				$flag = true;
			}
		}
		if($flag){
			foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
				array_push($message, $column_name);
				// 			error_log( $column_name );
			}
		}

		if(empty($message)){
			$success = false;
		}
		$response = array( // common for all responses
				'success' => $success,
				'message' => $message
		);
		wp_send_json($response);
		
	}
} // class IggoGrid_Admin_AJAX_Controller
