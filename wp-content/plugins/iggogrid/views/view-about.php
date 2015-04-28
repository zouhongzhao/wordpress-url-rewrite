<?php
/**
 * About IggoGrid View
 *
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * About IggoGrid View class
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class IggoGrid_About_View extends IggoGrid_View {

	/**
	 * Number of screen columns for post boxes on this screen
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $screen_columns = 2;

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

		$this->add_meta_box( 'plugin-purpose', __( 'Plugin Purpose', 'iggogrid' ), array( $this, 'postbox_plugin_purpose' ), 'normal' );
	}

	/**
	 * Print the content of the "Plugin Purpose" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_plugin_purpose( $data, $box ) {
		?>
	<p>
		<?php _e( 'IggoGrid allows you to create and manage tables in the admin area of WordPress.', 'iggogrid' ); ?>
		<?php _e( 'Tables may contain text, numbers and even HTML (e.g. to include images or links).', 'iggogrid' ); ?>
		<?php _e( 'You can then show the tables in your posts, on your pages, or in text widgets by using a Shortcode.', 'iggogrid' ); ?>
		<?php _e( 'If you want to show your tables anywhere else in your theme, you can use a Template Tag function.', 'iggogrid' ); ?>
	</p>
		<?php
	}


} // class IggoGrid_About_View
