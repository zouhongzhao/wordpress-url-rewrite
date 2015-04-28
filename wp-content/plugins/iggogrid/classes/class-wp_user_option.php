<?php
/**
 * IggoGrid WP User Option Wrapper class for WordPress Options
 *
 * Wraps the WordPress Options API, so that (especially) arrays are stored as JSON, instead of being serialized by PHP
 *
 * @package IggoGrid
 * @subpackage Classes
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

// load parent class
IggoGrid::load_file( 'class-wp_option.php', 'classes' );

/**
 * IggoGrid WP User Option Wrapper class
 * @package IggoGrid
 * @subpackage Classes
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class IggoGrid_WP_User_Option extends IggoGrid_WP_Option {

	/**
	 * Get the value of a WP User Option with the WP API
	 *
	 * @since 1.0.0
	 * @uses is_user_logged_in(), get_user_option()
	 *
	 * @param string $option_name Name of the WP User Option
	 * @param mixed $default_value Default value of the WP User Option
	 * @return mixed Current value of the WP User Option, or $default_value if it does not exist
	 */
	protected function _get_option( $option_name, $default_value ) {
		// non-logged-in user can never have a saved option value
		if ( ! is_user_logged_in() ) {
			return $default_value;
		}

		$option_value = get_user_option( $option_name );
		// get_user_option() only knows false as the default value, so we have to wrap that
		if ( false === $option_value ) {
			$option_value = $default_value;
		}
		return $option_value;
	}

	/**
	 * Update the value of a WP User Option with the WP API
	 *
	 * @since 1.0.0
	 * @uses is_user_logged_in(), update_user_option()
	 *
	 * @param string $option_name Name of the WP User Option
	 * @param string $new_value New value of the WP User Option (not slashed)
	 * @return bool True on success, false on failure
	 */
	protected function _update_option( $option_name, $new_value ) {
		// non-logged-in user can never have a saved option value to be updated
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$new_value = wp_slash( $new_value ); // WP expects a slashed value...
		return update_user_option( get_current_user_id(), $option_name, $new_value, false );
	}

	/**
	 * Delete a WP User Option with the WP API
	 *
	 * @since 1.0.0
	 * @uses is_user_logged_in(), delete_user_option()
	 *
	 * @param string $option_name Name of the WP User Option
	 * @return bool True on success, false on failure
	 */
	protected function _delete_option( $option_name ) {
		// non-logged-in user can never have a saved option value to be deleted
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return delete_user_option( get_current_user_id(), $option_name, false );
	}

	/**
	 * Delete a WP User Option with the WP API, for all users of the site
	 *
	 * @since 1.0.0
	 * @uses delete_user_option()
	 */
	public function delete_for_all_users() {
		$users = get_users();
		foreach ( $users as $user ) {
			delete_user_option( $user->ID, $this->option_name, false );
			// @TODO: Add other user options for different actions as well
		}
	}

} // class IggoGrid_WP_User_Option