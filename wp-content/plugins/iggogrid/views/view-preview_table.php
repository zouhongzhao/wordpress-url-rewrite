<?php
/**
 * Table Preview View
 *
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Table Preview View class
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class IggoGrid_Preview_Table_View extends IggoGrid_View {

	/**
	 * Initialize the View class
	 *
	 * Intentionally left empty, to void code from parent::__construct()
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// intentionally left empty, to void code from parent::__construct()
	}

	/**
	 * Set up the view with data and do things that are specific for this view
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action for this view
	 * @param array $data Data for this view
	 */
	public function setup( $action, array $data ) {
		$this->action = $action;
		$this->data = $data;
	}

	/**
	 * Render the current view
	 *
	 * @since 1.0.0
	 */
	public function render() {
		_wp_admin_html_begin();
?>
<title><?php printf( __( '%1$s &lsaquo; %2$s', 'iggogrid' ), __( 'Preview', 'iggogrid' ), 'IggoGrid' ); ?></title>
<style type="text/css">
body {
	margin-top: -6px !important;
}
</style>
<?php echo $this->data['head_html']; ?>
</head>
<body>
<div id="iggogrid-page">
<p>
<?php _e( 'This is a preview of your table.', 'iggogrid' ); ?> <?php _e( 'Because of CSS styling in your theme, the table might look different on your page!', 'iggogrid' ); ?> <?php _e( 'The features of the DataTables JavaScript library are also not available or visible in this preview!', 'iggogrid' ); ?><br />
<?php printf( __( 'To insert the table into a page, post, or text widget, copy the Shortcode %s and paste it into the editor.', 'iggogrid' ), '<input type="text" class="table-shortcode table-shortcode-inline" value="' . esc_attr( '[' . IggoGrid::$shortcode . " id={$this->data['table_id']} /]" ) . '" readonly="readonly" />' ); ?>
</p>
<?php echo $this->data['body_html']; ?>
</div>
</body>
</html>
<?php
	}

} // class IggoGrid_Preview_Table_View
