<?php
/**
 * IggoGrid Base Controller with members and methods for all controllers
 *
 * @package IggoGrid
 * @subpackage Controllers
 * @author Iggo
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Base Controller class
 * @package IggoGrid
 * @subpackage Controllers
 * @author Iggo
 * @since 1.0.0
 */
abstract class IggoGrid_Controller {

	/**
	 * Instance of the Options Model
	 *
	 * @since 1.0.0
	 *
	 * @var IggoGrid_Options_Model
	 */
	public $model_options;

	/**
	 * Instance of the Table Model
	 *
	 * @since 1.0.0
	 *
	 * @var IggoGrid_Table_Model
	 */
	public $model_table;

	/**
	 * File name of the admin screens's parent page in the admin menu
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $parent_page = 'middle';

	/**
	 * Whether IggoGrid admin screens are a top-level menu item in the admin menu
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $is_top_level_page = false;

	/**
	 * Initialize all controllers, by loading Plugin and User Options, and performing an update check
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// References to the IggoGrid models (only for backwards compatibility in IggoGrid Extensions!)
		// Using `IggoGrid::$model_options` and `IggoGrid::$model_table` is recommended!
		$this->model_options = IggoGrid::$model_options;
		$this->model_table = IggoGrid::$model_table;

		// update check, in all controllers (frontend and admin), to make sure we always have up-to-date options
		$this->plugin_update_check(); // should be done very early

		/**
		 * Filter the admin menu parent page, which is needed for the construction of plugin URLs.
		 *
		 * @since 1.0.0
		 *
		 * @param string $parent_page Current admin menu parent page.
		 */
		$this->parent_page = apply_filters( 'iggogrid_admin_menu_parent_page', IggoGrid::$model_options->get( 'admin_menu_parent_page' ) );
		$this->is_top_level_page = in_array( $this->parent_page, array( 'top', 'middle', 'bottom' ), true );
	}

	/**
	 * Check if the plugin was updated and perform necessary actions, like updating the options
	 *
	 * @since 1.0.0
	 */
	protected function plugin_update_check() {
		// First activation or plugin update
		$current_plugin_options_db_version = IggoGrid::$model_options->get( 'plugin_options_db_version' );
		if ( $current_plugin_options_db_version < IggoGrid::db_version ) {
			// Allow more PHP execution time for update process
			@set_time_limit( 300 );

			// Add IggoGrid capabilities to the WP_Roles objects, for new installations and all versions below 12
			if ( $current_plugin_options_db_version < 12 ) {
				IggoGrid::$model_options->add_access_capabilities();
			}

			if ( 0 == IggoGrid::$model_options->get( 'first_activation' ) ) {
				// Save initial set of plugin options, and time of first activation of the plugin, on first activation
				IggoGrid::$model_options->update( array(
					'first_activation' => current_time( 'timestamp' ),
					'plugin_options_db_version' => IggoGrid::db_version,
				) );
			} else {
				// Update Plugin Options Options, if necessary
				IggoGrid::$model_options->merge_plugin_options_defaults();
				$updated_options = array(
					'plugin_options_db_version' => IggoGrid::db_version,
					'prev_iggogrid_version' => IggoGrid::$model_options->get( 'iggogrid_version' ),
					'iggogrid_version' => IggoGrid::version,
					'message_plugin_update' => true,
				);

				if ( IggoGrid::$model_options->get( 'use_custom_css' ) && '' !== IggoGrid::$model_options->get( 'custom_css' ) ) { // only write files, if "Custom CSS" is to be used, and if there is "Custom CSS"
					// Re-save "Custom CSS" to re-create all files (as IggoGrid Default CSS might have changed)
					require_once ABSPATH . 'wp-admin/includes/file.php'; // to provide filesystem functions early
					require_once ABSPATH . 'wp-admin/includes/template.php'; // to provide `submit_button()` which is necessary for `request_filesystem_credentials()`
					$iggogrid_css = IggoGrid::load_class( 'IggoGrid_CSS', 'class-css.php', 'classes' );
					$result = $iggogrid_css->save_custom_css_to_file( IggoGrid::$model_options->get( 'custom_css' ), IggoGrid::$model_options->get( 'custom_css_minified' ) );
					$updated_options['use_custom_css_file'] = $result; // if saving was successful, use "Custom CSS" file
					// if saving was successful, increase the "Custom CSS" version number for cache busting
					if ( $result ) {
						$updated_options['custom_css_version'] = IggoGrid::$model_options->get( 'custom_css_version' ) + 1;
					}
				}

				IggoGrid::$model_options->update( $updated_options );

				// Clear table caches
				if ( $current_plugin_options_db_version < 16 ) {
					IggoGrid::$model_table->invalidate_table_output_caches_tp09(); // for pre-0.9-RC, where the arrays are serialized and not JSON encoded
				} else {
					IggoGrid::$model_table->invalidate_table_output_caches(); // for 0.9-RC and onwards
				}
			}

			IggoGrid::$model_options->update( array(
				'message_plugin_update_content' => IggoGrid::$model_options->plugin_update_message( IggoGrid::$model_options->get( 'prev_iggogrid_version' ), IggoGrid::version, get_locale() ),
			) );
		}

		// Maybe update the table scheme in each existing table, independently from updating the plugin options
		if ( IggoGrid::$model_options->get( 'table_scheme_db_version' ) < IggoGrid::table_scheme_version ) {
			// Convert parameter "datatables_scrollX" to "datatables_scrollx", has to be done before merge_table_options_defaults() is called!
			if ( IggoGrid::$model_options->get( 'table_scheme_db_version' ) < 3 ) {
				IggoGrid::$model_table->merge_table_options_tp08();
			}

			IggoGrid::$model_table->merge_table_options_defaults();

			// Merge print_name/print_description changes made for 0.6-beta
			if ( IggoGrid::$model_options->get( 'table_scheme_db_version' ) < 2 ) {
				IggoGrid::$model_table->merge_table_options_tp06();
			}

			IggoGrid::$model_options->update( array(
				'table_scheme_db_version' => IggoGrid::table_scheme_version,
			) );
		}

		// Update User Options, if necessary
		// User Options are not saved in DB until first change occurs
		if ( is_user_logged_in() && ( IggoGrid::$model_options->get( 'user_options_db_version' ) < IggoGrid::db_version ) ) {
			IggoGrid::$model_options->merge_user_options_defaults();
			IggoGrid::$model_options->update( array(
				'user_options_db_version' => IggoGrid::db_version,
			) );
		}
	}

} // class IggoGrid_Controller
