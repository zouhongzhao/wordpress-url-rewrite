<?php
/**
 * Add Table View
 *
 * @package IggoGrid
 * @subpackage Views
 * @author Iggo
 * @since 1.0.0
 */
// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Add Table View class
 * @package IggoGrid
 * @subpackage Views
 * @author Iggo
 * @since 1.0.0
 */
class IggoGrid_Add_View extends IggoGrid_View {

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

		$this->admin_page->enqueue_script( 'add', array( 'jquery' ) );

		$this->process_action_messages( array(
			'error_add' => __( 'Error: The table could not be added.', 'iggogrid' ),
		) );

		$this->add_text_box( 'head', array( $this, 'textbox_head' ), 'normal' );
		$this->add_meta_box( 'add-table', __( 'Add New Table', 'iggogrid' ), array( $this, 'postbox_add_table' ), 'normal' );
		$this->data['submit_button_caption'] = __( 'Add Table', 'iggogrid' );
		$this->add_text_box( 'submit', array( $this, 'textbox_submit_button' ), 'submit' );
	}

	/**
	 * Print the screen head text
	 *
	 * @since 1.0.0
	 */
	public function textbox_head( $data, $box ) {
		?>
		<p>
			<?php _e( 'To add a new table, enter its name, a description (optional), and the number of rows and columns into the form below.', 'iggogrid' ); ?>
		</p>
		<p>
			<?php _e( 'You can always change the name, description, and size of your table later.', 'iggogrid' ); ?>
		</p>
		<?php
	}

	/**
	 * Print the content of the "Add New Table" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_add_table( $data, $box ) {
		?>
		<div class="form-wrap">
			<div class="form-field">
				<label for="table-name"><?php _e( 'Table Name', 'iggogrid' ); ?>:</label>
				<input type="text" name="table[name]" id="table-name" class="placeholder placeholder-active" value="<?php esc_attr_e( 'Enter Table Name here', 'iggogrid' ); ?>" />
				<p><?php _e( 'The name or title of your table.', 'iggogrid' ); ?></p>
			</div>
			<div class="form-field">
				<label for="table-column"><?php _e( 'Table Column', 'iggogrid' ); ?>:</label>
				<div class="table_column_div">
				<button type="button"  class="iggogrid_get_column"><?php _e( 'Get Column', 'iggogrid' ); ?></button>		
				<!-- 
				<input type="text" name="table[column]" id="table-column" class="placeholder placeholder-active" value="<?php esc_attr_e( 'Enter Table Column here', 'iggogrid' ); ?>" />
				
				 -->
				 </div>
				<p><?php _e( 'The Table Column or title of your table.', 'iggogrid' ); ?></p>
			</div>
			<div class="form-field">
				<label for="table-description"><?php _e( 'Description', 'iggogrid' ); ?> <?php _e( '(optional)', 'iggogrid' ); ?>:</label>
				<textarea name="table[description]" id="table-description" class="placeholder placeholder-active" rows="4"><?php echo esc_textarea( __( 'Enter Description here', 'iggogrid' ) ); ?></textarea>
				<p><?php _e( 'A description of the contents of your table.', 'iggogrid' ); ?></p>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

} // class IggoGrid_Add_View
