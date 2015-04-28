<?php
/**
 * Plugin Options View
 *
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Plugin Options View class
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class IggoGrid_Options_View extends IggoGrid_View {

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

		$this->admin_page->enqueue_style( 'codemirror' );
		$this->admin_page->enqueue_script( 'codemirror', array(), false, true );
		$this->admin_page->enqueue_script( 'options', array( 'jquery', 'iggogrid-codemirror' ), array(
			'strings' => array(
				'uninstall_warning_1' => __( 'Do you really want to uninstall IggoGrid and delete ALL data?', 'iggogrid' ),
				'uninstall_warning_2' => __( 'Are you really sure?', 'iggogrid' ),
			),
		) );

		$this->process_action_messages( array(
			'success_save' => __( 'Options saved successfully.', 'iggogrid' ),
			'success_save_error_custom_css' => __( 'Options saved successfully, but &#8220;Custom CSS&#8221; was not saved to file.', 'iggogrid' ),
			'error_save' => __( 'Error: Options could not be saved.', 'iggogrid' ),
			'success_import_wp_table_reloaded' => __( 'The WP-Table Reloaded &#8220;Custom CSS&#8221; was imported successfully.', 'iggogrid' ),
		) );

		$this->add_text_box( 'head', array( $this, 'textbox_head' ), 'normal' );
		if ( current_user_can( 'iggogrid_edit_options' ) ) {
			$this->add_meta_box( 'frontend-options', __( 'Frontend Options', 'iggogrid' ), array( $this, 'postbox_frontend_options' ), 'normal' );
		}
		$this->add_meta_box( 'user-options', __( 'User Options', 'iggogrid' ), array( $this, 'postbox_user_options' ), 'normal' );
		$this->data['submit_button_caption'] = __( 'Save Changes', 'iggogrid' );
		$this->add_text_box( 'submit', array( $this, 'textbox_submit_button' ), 'submit' );
		if ( current_user_can( 'activate_plugins' ) && current_user_can( 'iggogrid_edit_options' ) && current_user_can( 'iggogrid_delete_tables' ) && ! is_plugin_active_for_network( IGGOGRID_BASENAME ) ) {
			$this->add_text_box( 'uninstall-iggogrid', array( $this, 'textbox_uninstall_iggogrid' ), 'submit' );
		}
	}

	/**
	 * Print the screen head text
	 *
	 * @since 1.0.0
	 */
	public function textbox_head( $data, $box ) {
		?>
		<p>
			<?php _e( 'IggoGrid has several options which affect the plugin&#8217;s behavior in different areas.', 'iggogrid' ); ?>
		</p>
		<p>
			<?php
				if ( current_user_can( 'iggogrid_edit_options' ) ) {
					_e( 'Frontend Options influence the styling of tables in pages, posts, or text widgets, by defining which CSS code shall be loaded.', 'iggogrid' );
					echo '<br />';
				}
				_e( 'In the User Options, every IggoGrid user can choose the position of the plugin in his WordPress admin menu, and his desired plugin language.', 'iggogrid' );
			?>
		</p>
		<?php
	}

	/**
	 * Print the content of the "Frontend Options" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_frontend_options( $data, $box ) {
?>
<table class="iggogrid-postbox-table fixed">
<tbody>
	<tr>
		<th class="column-1" scope="row"><?php _e( 'Custom CSS', 'iggogrid' ); ?>:</th>
		<td class="column-2"><label for="option-use-custom-css"><input type="checkbox" id="option-use-custom-css" name="options[use_custom_css]" value="true"<?php checked( $data['frontend_options']['use_custom_css'] ); ?> /> <?php _e( 'Load these &#8220;Custom CSS&#8221; commands to influence the table styling:', 'iggogrid' ); ?></label>
		</td>
	</tr>
	<tr>
		<th class="column-1" scope="row"></th>
		<td class="column-2">
			<textarea name="options[custom_css]" id="option-custom-css" class="large-text" rows="8"><?php echo esc_textarea( $data['frontend_options']['custom_css'] ); ?></textarea>
			<p class="description"><?php
				printf( __( '&#8220;Custom CSS&#8221; (<a href="%s">Cascading Style Sheets</a>) can be used to change the styling or layout of a table.', 'iggogrid' ), 'http://www.htmldog.com/guides/cssbeginner/' );
				echo ' ';
				printf( __( 'You can get styling examples from the <a href="%s">FAQ</a>.', 'iggogrid' ), 'http://iggogrid.org/faq/' );
				echo ' ';
				printf( __( 'Information on available CSS selectors can be found in the <a href="%s">documentation</a>.', 'iggogrid' ), 'http://iggogrid.org/documentation/' );
				echo ' ';
				_e( 'Please note that invalid CSS code will be stripped, if it can not be corrected automatically.', 'iggogrid' );
			?></p>
		</td>
	</tr>
</tbody>
</table>
<?php
	}

	/**
	 * Print the content of the "User Options" post meta box
	 *
	 * @since 1.0.0
	 */
	public function postbox_user_options( $data, $box ) {
		?>
<table class="iggogrid-postbox-table fixed">
<tbody>
		<?php
		// get list of current admin menu entries
		$entries = array();
		foreach ( $GLOBALS['menu'] as $entry ) {
			if ( false !== strpos( $entry[2], '.php' ) ) {
				$entries[ $entry[2] ] = $entry[0];
			}
		}

		// remove <span> elements with notification bubbles (e.g. update or comment count)
		if ( isset( $entries['plugins.php'] ) ) {
			$entries['plugins.php'] = preg_replace( '/ <span.*span>/', '', $entries['plugins.php'] );
		}
		if ( isset( $entries['edit-comments.php'] ) ) {
			$entries['edit-comments.php'] = preg_replace( '/ <span.*span>/', '', $entries['edit-comments.php'] );
		}

		// add separator and generic positions
		$entries['-'] = '---';
		$entries['top'] = __( 'Top-Level (top)', 'iggogrid' );
		$entries['middle'] = __( 'Top-Level (middle)', 'iggogrid' );
		$entries['bottom'] = __( 'Top-Level (bottom)', 'iggogrid' );

		$select_box = '<select id="option-admin-menu-parent-page" name="options[admin_menu_parent_page]">' . "\n";
		foreach ( $entries as $page => $entry ) {
			$select_box .= '<option' . selected( $page, $data['user_options']['parent_page'], false ) . disabled( $page, '-', false ) .' value="' . $page . '">' . $entry . "</option>\n";
		}
		$select_box .= "</select>\n";
		?>
	<tr class="bottom-border">
		<th class="column-1" scope="row"><label for="option-admin-menu-parent-page"><?php _e( 'Admin menu entry', 'iggogrid' ); ?>:</label></th>
		<td class="column-2"><?php printf( __( 'IggoGrid shall be shown in this section of my admin menu: %s', 'iggogrid' ), $select_box ); ?></td>
	</tr>
		<?php
		$select_box = '<select id="option-plugin-language" name="options[plugin_language]">' . "\n";
		$select_box .= '<option' . selected( $data['user_options']['plugin_language'], 'auto', false ) . ' value="auto">' . sprintf( __( 'WordPress Default (currently %s)', 'iggogrid' ), get_locale() ) . "</option>\n";
		$select_box .= '<option value="-" disabled="disabled">---</option>' . "\n";
		foreach ( $data['user_options']['plugin_languages'] as $lang_abbr => $language ) {
			$select_box .= '<option' . selected( $data['user_options']['plugin_language'], $lang_abbr, false ) . ' value="' . $lang_abbr . '">' . "{$language['name']} ({$lang_abbr})</option>\n";
		}
		$select_box .= "</select>\n";
		?>
	<tr class="top-border">
		<th class="column-1" scope="row"><label for="option-plugin-language"><?php _e( 'Plugin Language', 'iggogrid' ); ?>:</label></th>
		<td class="column-2"><?php printf( __( 'I want to use IggoGrid in this language: %s', 'iggogrid' ), $select_box ); ?></td>
	</tr>
</tbody>
</table>
<?php
	}

	/**
	 * Print the content of the "Admin Options" post meta box
	 *
	 * @since 1.0.0
	 */
	public function textbox_uninstall_iggogrid( $data, $box ) {
		?>
		<h2 style="margin-top:40px;"><?php _e( 'Uninstall IggoGrid', 'iggogrid' ); ?></h2>
		<p><?php
			echo __( 'Uninstalling <strong>will permanently delete</strong> all IggoGrid tables and options from the database.', 'iggogrid' ) . '<br />'
				. __( 'It is recommended that you create a backup of the tables (by exporting the tables in the JSON format), in case you later change your mind.', 'iggogrid' ) . '<br />'
				. __( 'You will manually need to remove the plugin&#8217;s files from the plugin folder afterwards.', 'iggogrid' ) . '<br />'
				. __( 'Be very careful with this and only click the button if you know what you are doing!', 'iggogrid' );
		?></p>
		<p><a href="<?php echo IggoGrid::url( array( 'action' => 'uninstall_iggogrid' ), true, 'admin-post.php' ); ?>" id="uninstall-iggogrid" class="button"><?php _e( 'Uninstall IggoGrid', 'iggogrid' ); ?></a></p>
		<?php
	}

} // class IggoGrid_Options_View
