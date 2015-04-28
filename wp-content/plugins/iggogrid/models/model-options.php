<?php
/**
 * Options Model
 *
 * @package IggoGrid
 * @subpackage Models
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Options Model class
 * @package IggoGrid
 * @subpackage Models
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class IggoGrid_Options_Model extends IggoGrid_Model {

	/**
	 * Default Plugin Options
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $default_plugin_options = array(
		'plugin_options_db_version' => 0,
		'table_scheme_db_version' => 0,
		'prev_iggogrid_version' => '0',
		'iggogrid_version' => IggoGrid::version,
		'first_activation' => 0,
		'message_plugin_update' => false,
		'message_plugin_update_content' => '',
		'message_donation_nag' => true,
		'use_custom_css' => true,
		'use_custom_css_file' => true,
		'custom_css' => '',
		'custom_css_minified' => '',
		'custom_css_version' => 0,
	);

	/**
	 * Default User Options
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $default_user_options = array(
		'user_options_db_version' => IggoGrid::db_version, // to prevent saving on first load
		'admin_menu_parent_page' => 'middle',
		'plugin_language' => 'auto',
		'message_first_visit' => true,
	);

	/**
	 * Instance of WP_Option class for Plugin Options
	 *
	 * @since 1.0.0
	 *
	 * @var IggoGrid_WP_Option
	 */
	protected $plugin_options;

	/**
	 * Instance of WP_User_Option class for User Options
	 *
	 * @since 1.0.0
	 *
	 * @var IggoGrid_WP_User_Option
	 */
	protected $user_options;

	/**
	 * Init Options Model by creating the object instances for the Plugin and User Options
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$params = array(
			'option_name' => 'iggogrid_plugin_options',
			'default_value' => $this->default_plugin_options,
		);
		$this->plugin_options = IggoGrid::load_class( 'IggoGrid_WP_Option', 'class-wp_option.php', 'classes', $params );

		$params = array(
			'option_name' => 'iggogrid_user_options',
			'default_value' => $this->default_user_options,
		);
		$this->user_options = IggoGrid::load_class( 'IggoGrid_WP_User_Option', 'class-wp_user_option.php', 'classes', $params );

		// Filter to map Meta capabilities to Primitive Capabilities
		add_filter( 'map_meta_cap', array( $this, 'map_iggogrid_meta_caps' ), 10, 4 );
	}


	/**
	 * Get the value of a single option, or an array with all options
	 *
	 * @since 1.0.0
	 *
	 * @param string|false $name (optional) Name of a single option to get, or false for all options
	 * @param mixed $default_value (optional) Default value, if the option $name does not exist
	 * @return mixed Value of the retrieved option $name, or $default_value if it does not exist, or array of all options
	 */
	public function get( $name = false, $default_value = null ) {
		if ( false === $name ) {
			return array_merge( $this->plugin_options->get(), $this->user_options->get() );
		}

		// Single Option wanted
		if ( $this->plugin_options->is_set( $name ) ) {
			return $this->plugin_options->get( $name );
		} elseif ( $this->user_options->is_set( $name ) ) {
			return $this->user_options->get( $name );
		} else {
			// no valid Plugin or User Option
			return $default_value;
		}
	}

	/**
	 * Get all Plugin Options (only used in Debug)
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of all Plugin Options
	 */
	public function _debug_get_plugin_options() {
		return $this->plugin_options->get();
	}

	/**
	 * Get all User Options (only used in Debug)
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of all User Options
	 */
	public function _debug_get_user_options() {
		return $this->user_options->get();
	}

	/**
	 * Merge existing User Options with default User Options,
	 * remove (no longer) existing options, e.g. after a plugin update
	 *
	 * @since 1.0.0
	 */
	public function merge_user_options_defaults() {
		$user_options = $this->user_options->get();
		// remove old (i.e. no longer existing) User Options:
		$user_options = array_intersect_key( $user_options, $this->default_user_options );
		// merge into new User Options:
		$user_options = array_merge( $this->default_user_options, $user_options );

		$this->user_options->update( $user_options );
	}

	/**
	 * Add default capabilities to "Administrator", "Editor", and "Author" roles
	 *
	 * @since 1.0.0
	 */
	public function add_access_capabilities() {
		// Capabilities for all roles
		$roles = array( 'administrator', 'editor', 'author' );
		foreach ( $roles as $role ) {
			$role = get_role( $role );
			if ( empty( $role ) ) {
				continue;
			}

			// from get_post_type_capabilities()
			$role->add_cap( 'iggogrid_edit_tables' );
			// $role->add_cap( 'iggogrid_edit_others_tables' );
			$role->add_cap( 'iggogrid_delete_tables' );
			// $role->add_cap( 'iggogrid_delete_others_tables' );

			// custom capabilities()
			$role->add_cap( 'iggogrid_list_tables' );
			$role->add_cap( 'iggogrid_add_tables' );
			$role->add_cap( 'iggogrid_copy_tables' );
			$role->add_cap( 'iggogrid_access_options_screen' );
			$role->add_cap( 'iggogrid_access_about_screen' );
		}

		// Capabilities for single roles
		$role = get_role( 'administrator' );
		if ( ! empty( $role ) ) {
			$role->add_cap( 'iggogrid_edit_options' );
		}

		// Refresh current set of capabilities of the user, to be able to directly use the new caps
		$user = wp_get_current_user();
		$user->get_role_caps();
	}

	/**
	 * Remove all IggoGrid capabilities from all roles
	 *
	 * @see add_access_capabilities()
	 *
	 * @since 1.1.0
	 */
	public function remove_access_capabilities() {
		// Capabilities for all roles
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		foreach ( $wp_roles->roles as $role => $details ) {
			$role = $wp_roles->get_role( $role );
			if ( empty( $role ) ) {
				continue;
			}

			$role->remove_cap( 'iggogrid_edit_tables' );
			$role->remove_cap( 'iggogrid_delete_tables' );
			$role->remove_cap( 'iggogrid_list_tables' );
			$role->remove_cap( 'iggogrid_add_tables' );
			$role->remove_cap( 'iggogrid_copy_tables' );
			$role->remove_cap( 'iggogrid_access_options_screen' );
			$role->remove_cap( 'iggogrid_access_about_screen' );
			$role->remove_cap( 'iggogrid_edit_options' );
		}

		// Refresh current set of capabilities of the user, to be able to directly use the new caps
		$user = wp_get_current_user();
		$user->get_role_caps();
	}

	/**
	 * Map IggoGrid meta capabilities to primitive capabilities
	 *
	 * @since 1.0.0
	 *
	 * @param array $caps Current set of primitive caps
	 * @param string $cap Meta cap that is to be checked/mapped
	 * @param int $user_id User ID for which meta cap is to be checked
	 * @param array $args Arguments for the check, here e.g. the table ID
	 * @return array $caps Modified set of primitive caps
	 */
	public function map_iggogrid_meta_caps( array $caps, $cap, $user_id, $args ) {
		if ( ! in_array( $cap, array( 'iggogrid_edit_table', 'iggogrid_edit_table_id', 'iggogrid_copy_table', 'iggogrid_delete_table', 'iggogrid_export_table', 'iggogrid_preview_table' ), true ) ) {
			return $caps;
		}

		// $table_id = ( ! empty( $args ) ) ? $args[0] : false;

		// reset current set of primitive caps
		$caps = array();

		switch ( $cap ) {
			case 'iggogrid_edit_table':
				$caps[] = 'iggogrid_edit_tables';
				break;
			case 'iggogrid_edit_table_id':
				$caps[] = 'iggogrid_edit_tables';
				break;
			case 'iggogrid_copy_table':
				$caps[] = 'iggogrid_copy_tables';
				break;
			case 'iggogrid_delete_table':
				$caps[] = 'iggogrid_delete_tables';
				break;
			case 'iggogrid_export_table':
				$caps[] = 'iggogrid_export_tables';
				break;
			case 'iggogrid_preview_table':
				$caps[] = 'iggogrid_edit_tables';
				break;
			default:
				// something went wrong; deny access to be on the safe side
				$caps[] = 'do_not_allow';
				break;
		}

		/**
		 * Filter a user's IggoGrid capabilities.
		 *
		 * See the WordPress function map_meta_cap() for details.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $caps    The user's current IggoGrid capabilities.
		 * @param string $cap     Capability name.
		 * @param int    $user_id The user ID.
		 * @param array  $args    Adds the context to the cap. Typically the table ID.
		 */
		return apply_filters( 'iggogrid_map_meta_caps', $caps, $cap, $user_id, $args );
	}


	/**
	 * Delete the WP_Option and the user option of the model
	 *
	 * @since 1.0.0
	 */
	public function destroy() {
		$this->plugin_options->delete();
		$this->user_options->delete_for_all_users();
	}

} // class IggoGrid_Options_Model
