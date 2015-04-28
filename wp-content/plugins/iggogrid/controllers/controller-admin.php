<?php
/**
 * Admin Controller for IggoGrid with the functionality for the non-AJAX backend
 *
 * @package IggoGrid
 * @subpackage Controllers
 * @author Iggo
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Admin Controller class, extends Base Controller Class
 * @package IggoGrid
 * @subpackage Controllers
 * @author Iggo
 * @since 1.0.0
 */
class IggoGrid_Admin_Controller extends IggoGrid_Controller {

	/**
	 * Page hooks (i.e. names) WordPress uses for the IggoGrid admin screens,
	 * populated in add_admin_menu_entry()
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $page_hooks = array();

	/**
	 * Actions that have a view and admin menu or nav tab menu entry
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $view_actions = array();

	/**
	 * Boolean to record whether language support has been loaded (to prevent to do it twice)
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $i18n_support_loaded = false;

	/**
	 * Initialize the Admin Controller, determine location the admin menu, set up actions
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		// handler for changing the number of shown tables in the list of tables (via WP List Table class)
		add_filter( 'set-screen-option', array( $this, 'save_list_tables_screen_option' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'add_admin_menu_entry' ) );
		add_action( 'admin_init', array( $this, 'add_admin_actions' ) );
	}

	/**
	 * Handler for changing the number of shown tables in the list of tables (via WP List Table class)
	 *
	 * @since 1.0.0
	 *
	 * @param bool $false Current value of the filter (probably bool false)
	 * @param string $option Option in which the setting is stored
	 * @param int $value Current value of the setting
	 * @return bool|int False to not save the changed setting, or the int value to be saved
	 */
	public function save_list_tables_screen_option( $false, $option, $value ) {
		if ( 'iggogrid_list_per_page' == $option ) {
			return $value;
		} else {
			return $false;
		}
	}

	/**
	 * Add admin screens to the correct place in the admin menu
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu_entry() {
		
		// for all menu entries:
		$callback = array( $this, 'show_admin_page' );
		/**
		 * Filter the IggoGrid admin menu entry name.
		 *
		 * @since 1.0.0
		 *
		 * @param string $entry_name The admin menu entry name. Default "IggoGrid".
		 */
		$admin_menu_entry_name = apply_filters( 'iggogrid_admin_menu_entry_name', 'IggoGrid' );

		if ( $this->is_top_level_page ) {
			$this->init_i18n_support(); // done here as translated strings for admin menu are needed already
			$this->init_view_actions(); // after init_i18n_support(), as it requires translation
			$min_access_cap = $this->view_actions['list']['required_cap'];
			
			$icon_url = 'dashicons-list-view';
			switch ( $this->parent_page ) {
				case 'top':
					$position = 3; // position of Dashboard + 1
					break;
				case 'middle':
					$position = ( ++$GLOBALS['_wp_last_object_menu'] );
					break;
				case 'bottom':
					$position = ( ++$GLOBALS['_wp_last_utility_menu'] );
					break;
			}
			add_menu_page( 'IggoGrid', $admin_menu_entry_name, $min_access_cap, 'iggogrid', $callback, $icon_url, $position );
			
			foreach ( $this->view_actions as $action => $entry ) {
				if ( ! $entry['show_entry'] ) {
					continue;
				}
				$slug = 'iggogrid';
				if ( 'list' != $action ) {
					$slug .= '_' . $action;
				}
				$this->page_hooks[] = add_submenu_page( 'iggogrid', sprintf( __( '%1$s &lsaquo; %2$s', 'iggogrid' ), $entry['page_title'], 'IggoGrid' ), $entry['admin_menu_title'], $entry['required_cap'], $slug, $callback );
			}
		} else {
			$this->init_view_actions(); // no translation necessary here
			$min_access_cap = $this->view_actions['list']['required_cap'];
			$this->page_hooks[] = add_submenu_page( $this->parent_page, 'IggoGrid', $admin_menu_entry_name, $min_access_cap, 'iggogrid', $callback );
		}
	}

	/**
	 * Set up handlers for user actions in the backend that exceed plain viewing
	 *
	 * @since 1.0.0
	 */
	public function add_admin_actions() {
		// register the callbacks for processing action requests
		$post_actions = array( 'list', 'add', 'edit', 'options');
		$get_actions = array( 'hide_message', 'delete_table', 'copy_table', 'preview_table', 'editor_button_thickbox', 'uninstall_iggogrid' );
		foreach ( $post_actions as $action ) {
			add_action( "admin_post_iggogrid_{$action}", array( $this, "handle_post_action_{$action}" ) );
		}
		foreach ( $get_actions as $action ) {
			add_action( "admin_post_iggogrid_{$action}", array( $this, "handle_get_action_{$action}" ) );
		}

		// register callbacks to trigger load behavior for admin pages
		foreach ( $this->page_hooks as $page_hook ) {
			add_action( "load-{$page_hook}", array( $this, 'load_admin_page' ) );
		}

		$pages_with_editor_button = array( 'post.php', 'post-new.php' );
		foreach ( $pages_with_editor_button as $editor_page ) {
			add_action( "load-{$editor_page}", array( $this, 'add_editor_buttons' ) );
		}

		if ( ! is_network_admin() && ! is_user_admin() ) {
			add_action( 'admin_bar_menu', array( $this, 'add_wp_admin_bar_new_content_menu_entry' ), 71 );
		}

		add_action( 'load-plugins.php', array( $this, 'plugins_page' ) );
		add_action( 'admin_print_styles-media-upload-popup', array( $this, 'add_media_upload_thickbox_css' ) );
	}

	/**
	 * Register actions to add "Table" button to "HTML editor" and "Visual editor" toolbars
	 *
	 * @since 1.0.0
	 */
	public function add_editor_buttons() {
		if ( ! current_user_can( 'iggogrid_list_tables' ) ) {
			return;
		}

		$this->init_i18n_support();
		add_thickbox(); // usually already loaded by media upload functions
		$admin_page = IggoGrid::load_class( 'IggoGrid_Admin_Page', 'class-admin-page-helper.php', 'classes' );
		$admin_page->enqueue_script( 'quicktags-button', array( 'quicktags', 'media-upload' ), array(
			'editor_button' => array(
				'caption' => __( 'Table', 'iggogrid' ),
				'title' => __( 'Insert a Table from IggoGrid', 'iggogrid' ),
				'thickbox_title' => __( 'Insert a Table from IggoGrid', 'iggogrid' ),
				'thickbox_url' => IggoGrid::url( array( 'action' => 'editor_button_thickbox' ), true, 'admin-post.php' ),
			),
		) );

		// TinyMCE integration
		if ( user_can_richedit() ) {
			add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
			add_filter( 'mce_buttons', array( $this, 'add_tinymce_button' ) );
			add_action( 'admin_print_styles', array( $this, 'add_iggogrid_hidpi_css' ), 21 );
		}
	}

	/**
	 * Add "Table" button and separator to the TinyMCE toolbar
	 *
	 * @since 1.0.0
	 *
	 * @param array $buttons Current set of buttons in the TinyMCE toolbar
	 * @return array Current set of buttons in the TinyMCE toolbar, including "Table" button
	 */
	public function add_tinymce_button( array $buttons ) {
		$buttons[] = 'iggogrid_insert_table';
		return $buttons;
	}

	/**
	 * Register "Table" button plugin to TinyMCE
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugins Current set of registered TinyMCE plugins
	 * @return array Current set of registered TinyMCE plugins, including "Table" button plugin
	 */
	public function add_tinymce_plugin( array $plugins ) {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$js_file = "admin/js/tinymce-button{$suffix}.js";
		$plugins['iggogrid_tinymce'] = plugins_url( $js_file, IGGOGRID__FILE__ );
		return $plugins;
	}

	/**
	 * Print IggoGrid HiDPI CSS to the <head> for TinyMCE button
	 *
	 * @since 1.0.0
	 */
	public function add_iggogrid_hidpi_css() {
		echo '<style type="text/css">@media print,(-o-min-device-pixel-ratio:5/4),(-webkit-min-device-pixel-ratio:1.25),(min-resolution:120dpi){';
		echo '#content_iggogrid_insert_table span{background:url(' . plugins_url( 'admin/img/iggogrid-editor-button-2x.png', IGGOGRID__FILE__ ) . ') no-repeat 0 0;background-size:20px 20px}';
		echo '#content_iggogrid_insert_table img{display:none}';
		echo '}</style>' . "\n";
	}

	/**
	 * Print some CSS in the Media Upload Thickbox to fix some positioning issues.
	 *
	 * These will most likely not be fixed in core, as the old media uploader is deprecated.
	 * They will be removed in IggoGrid, once the new media uploader is used.
	 *
	 * @since 1.4.0
	 */
	public function add_media_upload_thickbox_css() {
		echo '<style type="text/css">#media-items,#media-upload #filter{width:auto!important}.media-item .describe input[type="text"],.media-item .describe textarea{width:100%!important}.media-item .image-editor input[type="text"]{width:3em!important}</style>' . "\n";
	}

	/**
	 * Add "IggoGrid Table" entry to "New" dropdown menu in the WP Admin Bar
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The current WP Admin Bar object
	 */
	public function add_wp_admin_bar_new_content_menu_entry( WP_Admin_Bar $wp_admin_bar ) {
		if ( ! current_user_can( 'iggogrid_add_tables' ) ) {
			return;
		}
		// @TODO: Translation might not work, as textdomain might not yet be loaded here (for submenu entries)
		// Might need $this->init_i18n_support(); here
		$wp_admin_bar->add_menu( array(
			'parent' => 'new-content',
			'id' => 'new-iggogrid-table',
			'title' => __( 'IggoGrid Table', 'iggogrid' ),
			'href' => IggoGrid::url( array( 'action' => 'add' ) ),
		) );
	}

	/**
	 * Handle actions for loading of Plugins page
	 *
	 * @since 1.0.0
	 */
	public function plugins_page() {
		$this->init_i18n_support();
		// add additional links on Plugins page
		add_filter( 'plugin_action_links_' . IGGOGRID_BASENAME, array( $this, 'add_plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Add links to the IggoGrid entry in the "Plugin" column on the Plugins page
	 *
	 * @since 1.0.0
	 *
	 * @param array $links List of links to print in the "Plugin" column on the Plugins page
	 * @return array Extended list of links to print in the "Plugin" column on the Plugins page
	 */
	public function add_plugin_action_links( array $links ) {
		if ( current_user_can( 'iggogrid_list_tables' ) ) {
			$links[] = '<a href="' . IggoGrid::url() . '">' . __( 'Plugin page', 'iggogrid' ) . '</a>';
		}
		return $links;
	}
	/**
	 * Add links to the IggoGrid entry in the "Description" column on the Plugins page
	 *
	 * @since 1.0.0
	 *
	 * @param array $links List of links to print in the "Description" column on the Plugins page
	 * @param string $file Name of the plugin
	 * @return array Extended list of links to print in the "Description" column on the Plugins page
	 */
	public function add_plugin_row_meta( array $links, $file ) {
		if ( IGGOGRID_BASENAME == $file ) {
			$links[] = '<a href="http://iggogrid.org/faq/" title="' . esc_attr__( 'Frequently Asked Questions', 'iggogrid' ) . '">' . __( 'FAQ', 'iggogrid' ) . '</a>';
			$links[] = '<a href="http://iggogrid.org/documentation/">' . __( 'Documentation', 'iggogrid' ) . '</a>';
			$links[] = '<a href="http://iggogrid.org/support/">' . __( 'Support', 'iggogrid' ) . '</a>';
			$links[] = '<a href="http://iggogrid.org/donate/" title="' . esc_attr__( 'Support IggoGrid with your donation!', 'iggogrid' ) . '"><strong>' . __( 'Donate', 'iggogrid' ) . '</strong></a>';
		}
		return $links;
	}

	/**
	 * Prepare the rendering of an admin screen, by determining the current action, loading necessary data and initializing the view
	 *
	 * @since 1.0.0
	 */
	 public function load_admin_page() {
	 	
		// determine the action from either the GET parameter (for sub-menu entries, and the main admin menu entry)
		$action = ( ! empty( $_GET['action'] ) ) ? $_GET['action'] : 'list'; // default action is list
		
		if ( $this->is_top_level_page ) {
			// or for sub-menu entry of an admin menu "IggoGrid" entry, get it from the "page" GET parameter
			if ( 'iggogrid' !== $_GET['page'] ) {
				
				// actions that are top-level entries, but don't have an action GET parameter (action is after last _ in string)
				$action = substr( $_GET['page'], 9 ); // $_GET['page'] has the format 'iggogrid_{$action}'
			}
		} else {
			// do this here in the else-part, instead of adding another if ( ! $this->is_top_level_page ) check
			$this->init_i18n_support(); // done here, as for sub menu admin pages this is the first time translated strings are needed
			$this->init_view_actions(); // for top-level menu entries, this has been done above, just like init_i18n_support()
		}
		
		// check if action is a supported action, and whether the user is allowed to access this screen
		if ( ! isset( $this->view_actions[ $action ] ) || ! current_user_can( $this->view_actions[ $action ]['required_cap'] ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}
		
		// changes current screen ID and pagenow variable in JS, to enable automatic meta box JS handling
		set_current_screen( "iggogrid_{$action}" );

		// pre-define some table data
		$data = array(
			'view_actions' => $this->view_actions,
			'message' => ( ! empty( $_GET['message'] ) ) ? $_GET['message'] : false,
		);
		
		// depending on action, load more necessary data for the corresponding view
		switch ( $action ) {
			case 'list':
				$data['table_id'] = ( ! empty( $_GET['table_id'] ) ) ? $_GET['table_id'] : false;
				$data['table_ids'] = IggoGrid::$model_table->load_all( true ); // Prime the post meta cache for cached loading of last_editor
				$data['messages']['first_visit'] = IggoGrid::$model_options->get( 'message_first_visit' );
				if ( current_user_can( 'iggogrid_import_tables_wptr' ) ) {
					$data['messages']['wp_table_reloaded_warning'] = is_plugin_active( 'wp-table-reloaded/wp-table-reloaded.php' ); // check if WP-Table Reloaded is activated
				} else {
					$data['messages']['wp_table_reloaded_warning'] = false;
				}
				$data['messages']['show_plugin_update'] = IggoGrid::$model_options->get( 'message_plugin_update' );
				$data['messages']['plugin_update_message'] = IggoGrid::$model_options->get( 'message_plugin_update_content' );
				$data['messages']['donation_message'] = $this->maybe_show_donation_message();
				$data['table_count'] = count( $data['table_ids'] );
				break;
			case 'about':
				$data['plugin_languages'] = $this->get_plugin_languages();
				$data['first_activation'] = IggoGrid::$model_options->get( 'first_activation' );
// 				$exporter = IggoGrid::load_class( 'IggoGrid_Export', 'class-export.php', 'classes' );
// 				$data['zip_support_available'] = $exporter->zip_support_available;
				break;
			case 'options':
				// Maybe try saving "Custom CSS" to a file:
				// (called here, as the credentials form posts to this handler again, due to how request_filesystem_credentials() works)
				if ( isset( $_GET['item'] ) && 'save_custom_css' == $_GET['item'] ) {
					IggoGrid::check_nonce( 'options', $_GET['item'] ); // nonce check here, as we don't have an explicit handler, and even viewing the screen needs to be checked
					$action = 'options_custom_css'; // to load a different view
					// try saving "Custom CSS" to a file, otherwise this gets the HTML for the credentials form
					$iggogrid_css = IggoGrid::load_class( 'IggoGrid_CSS', 'class-css.php', 'classes' );
					$result = $iggogrid_css->save_custom_css_to_file_plugin_options( IggoGrid::$model_options->get( 'custom_css' ), IggoGrid::$model_options->get( 'custom_css_minified' ) );
					if ( is_string( $result ) ) {
						$data['credentials_form'] = $result; // this will only be called if the save function doesn't do a redirect
					} elseif ( true === $result ) {
						// at this point, saving was successful, so enable usage of CSS in files again,
						// and also increase the "Custom CSS" version number (for cache busting)
						IggoGrid::$model_options->update( array(
							'use_custom_css_file' => true,
							'custom_css_version' => IggoGrid::$model_options->get( 'custom_css_version' ) + 1,
						) );
						IggoGrid::redirect( array( 'action' => 'options', 'message' => 'success_save' ) );
					} else { // leaves only $result = false
						IggoGrid::redirect( array( 'action' => 'options', 'message' => 'success_save_error_custom_css' ) );
					}
					break;
				}
				$data['frontend_options']['use_custom_css'] = IggoGrid::$model_options->get( 'use_custom_css' );
				$data['frontend_options']['custom_css'] = IggoGrid::$model_options->get( 'custom_css' );
				$data['user_options']['parent_page'] = $this->parent_page;
				$data['user_options']['plugin_language'] = IggoGrid::$model_options->get( 'plugin_language' );
				$data['user_options']['plugin_languages'] = $this->get_plugin_languages();
				break;
			case 'edit':
				if ( ! empty( $_GET['table_id'] ) ) {
					$data['table'] = IggoGrid::$model_table->load( $_GET['table_id'], true, true ); // Load table, with table data, options, and visibility settings
// 					print_r($data['table']);die;
					if ( is_wp_error( $data['table'] ) ) {
						IggoGrid::redirect( array( 'action' => 'list', 'message' => 'error_load_table' ) );
					}
					if ( ! current_user_can( 'iggogrid_edit_table', $_GET['table_id'] ) ) {
						wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
					}
				} else {
					IggoGrid::redirect( array( 'action' => 'list', 'message' => 'error_no_table' ) );
				}
				break;
		}
		
		/**
		 * Filter the data that is passed to the current IggoGrid View.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $data   Data for the view.
		 * @param string $action The current action for the view.
		 */
		$data = apply_filters( 'iggogrid_view_data', $data, $action );
		
		// prepare and initialize the view
		$this->view = IggoGrid::load_view( $action, $data );
	}

	/**
	 * Render the view that has been initialized in load_admin_page() (called by WordPress when the actual page content is needed)
	 *
	 * @since 1.0.0
	 */
	public function show_admin_page() {
		$this->view->render();
	}

	/**
	 * Initialize i18n support, load plugin's textdomain, to retrieve correct translations
	 *
	 * @since 1.0.0
	 */
	protected function init_i18n_support() {
		if ( $this->i18n_support_loaded ) {
			return;
		}
		add_filter( 'plugin_locale', array( $this, 'change_plugin_locale' ), 10, 2 ); // allow changing the plugin language
		$language_directory = dirname( IGGOGRID_BASENAME ) . '/i18n';
		load_plugin_textdomain( 'iggogrid', false, $language_directory );
		remove_filter( 'plugin_locale', array( $this, 'change_plugin_locale' ), 10, 2 );
		$this->i18n_support_loaded = true;
	}

	/**
	 * Get a list of available plugin languages and information on the translator
	 *
	 * @since 1.0.0
	 *
	 * @return array List of languages
	 */
	protected function get_plugin_languages() {
		$languages = array(
			'cs_CZ' => array(
				'name' => __( 'Czech', 'iggogrid' ),
				'translator_name' => 'Jiří Janda',
				'translator_url' => 'http://gadjukin.net/',
			),
			'de_DE' => array(
				'name' => __( 'German', 'iggogrid' ),
				'translator_name' => 'Iggo',
				'translator_url' => 'http://tobias.baethge.com/',
			),
			'en_US' => array(
				'name' => __( 'English', 'iggogrid' ),
				'translator_name' => 'Iggo',
				'translator_url' => 'http://tobias.baethge.com/',
			),
			'fi' => array(
				'name' => __( 'Finnish', 'iggogrid' ),
				'translator_name' => 'Joel Kosola',
				'translator_url' => '',
			),
			'fr_FR' => array(
				'name' => __( 'French', 'iggogrid' ),
				'translator_name' => 'Loïc Herry',
				'translator_url' => 'http://www.lherry.fr/',
			),
			'zh_CN' => array(
				'name' => __( 'Chinese (Simplified)', 'iggogrid' ),
				'translator_name' => 'Haoxian Zeng',
				'translator_url' => 'http://cnzhx.net/',
			),
		);
		uasort( $languages, array( $this, '_get_plugin_languages_sort_cb' ) ); // to sort after the translation is done
		return $languages;
	}

	/**
	 * Callback for sorting the language array in @see get_plugin_languages()
	 *
	 * @see get_plugin_languages()
	 * @since 1.0.0
	 *
	 * @param array $a First language to sort
	 * @param array $b Second language to sort
	 * @return array -1, 0, 1, depending on sort
	 */
	protected function _get_plugin_languages_sort_cb( array $a, array $b ) {
		return strnatcasecmp( $a['name'], $b['name'] );
	}

	/**
	 * Decide whether a donate message shall be shown on the "All Tables" screen, depending on passed days since installation and whether it was shown before
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the donate message shall be shown on the "All Tables" screen
	 */
	protected function maybe_show_donation_message() {
		// Only show the message to plugin admins
		if ( ! current_user_can( 'iggogrid_edit_options' ) ) {
			return false;
		}

		if ( ! IggoGrid::$model_options->get( 'message_donation_nag' ) ) {
			return false;
		}

		// How long has the plugin been installed?
		$seconds_installed = time() - IggoGrid::$model_options->get( 'first_activation' );
		return ( $seconds_installed > 30*DAY_IN_SECONDS );
	}

	/**
	 * Init list of actions that have a view with their titles/names/caps
	 *
	 * @since 1.0.0
	 */
	protected function init_view_actions() {
		$this->view_actions = array(
			'list' => array(
				'show_entry' => true,
				'page_title' => __( 'All Tables', 'iggogrid' ),
				'admin_menu_title' => __( 'All Tables', 'iggogrid' ),
				'nav_tab_title' => __( 'All Tables', 'iggogrid' ),
				'required_cap' => 'iggogrid_list_tables',
			),
			'add' => array(
				'show_entry' => true,
				'page_title' => __( 'Add New Table', 'iggogrid' ),
				'admin_menu_title' => __( 'Add New Table', 'iggogrid' ),
				'nav_tab_title' => __( 'Add New', 'iggogrid' ),
				'required_cap' => 'iggogrid_add_tables',
			),
			'edit' => array(
				'show_entry' => false,
				'page_title' => __( 'Edit Table', 'iggogrid' ),
				'admin_menu_title' => '',
				'nav_tab_title' => '',
				'required_cap' => 'iggogrid_edit_tables',
			),
// 			'options' => array(
// 				'show_entry' => true,
// 				'page_title' => __( 'Plugin Options', 'iggogrid' ),
// 				'admin_menu_title' => __( 'Plugin Options', 'iggogrid' ),
// 				'nav_tab_title' => __( 'Plugin Options', 'iggogrid' ),
// 				'required_cap' => 'iggogrid_access_options_screen',
// 			),
// 			'about' => array(
// 				'show_entry' => true,
// 				'page_title' => __( 'About', 'iggogrid' ),
// 				'admin_menu_title' => __( 'About IggoGrid', 'iggogrid' ),
// 				'nav_tab_title' => __( 'About', 'iggogrid' ),
// 				'required_cap' => 'iggogrid_access_about_screen',
// 			),
		);

		/**
		 * Filter the available TablePres Views/Actions and their parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param array $view_actions The available Views/Actions and their parameters.
		 */
		$this->view_actions = apply_filters( 'iggogrid_admin_view_actions', $this->view_actions );
	}

	/**
	 * Change the WordPress locale to the desired plugin locale, applied as a filter in get_locale(), while loading the plugin textdomain
	 *
	 * @since 1.0.0
	 *
	 * @param string $locale Current WordPress locale
	 * @param string $textdomain Text domain of the currently filtered plugin
	 * @return string IggoGrid locale
	 */
	public function change_plugin_locale( $locale, $textdomain ) {
		if ( 'iggogrid' != $textdomain ) {
			return $locale;
		}
		$new_locale = IggoGrid::$model_options->get( 'plugin_language' );
		$locale = ( ! empty( $new_locale ) && 'auto' != $new_locale ) ? $new_locale : $locale;
		return $locale;
	}

	/**
	 * HTTP POST actions
	 */

	/**
	 * Handle Bulk Actions (Copy, Export, Delete) on "All Tables" list screen
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_list() {
		IggoGrid::check_nonce( 'list' );

		if ( isset( $_POST['bulk-action-top'] ) && '-1' != $_POST['bulk-action-top'] ) {
			$bulk_action = $_POST['bulk-action-top'];
		} elseif ( isset( $_POST['bulk-action-bottom'] ) && '-1' != $_POST['bulk-action-bottom'] ) {
			$bulk_action = $_POST['bulk-action-bottom'];
		} else {
			$bulk_action = false;
		}

		if ( ! in_array( $bulk_action, array( 'copy', 'export', 'delete' ), true ) ) {
			IggoGrid::redirect( array( 'action' => 'list', 'message' => 'error_bulk_action_invalid' ) );
		}

		if ( empty( $_POST['table'] ) || ! is_array( $_POST['table'] ) ) {
			IggoGrid::redirect( array( 'action' => 'list', 'message' => 'error_no_selection' ) );
		} else {
			$tables = wp_unslash( $_POST['table'] );
		}

		$no_success = array(); // to store table IDs that failed

		switch ( $bulk_action ) {
			case 'copy':
				$this->init_i18n_support(); // for the translation of "Copy of"
				foreach ( $tables as $table_id ) {
					if ( current_user_can( 'iggogrid_copy_table', $table_id ) ) {
						$copy_table_id = IggoGrid::$model_table->copy( $table_id );
						if ( is_wp_error( $copy_table_id ) ) {
							$no_success[] = $table_id;
						}
					} else {
						$no_success[] = $table_id;
					}
				}
				break;
			case 'delete':
				foreach ( $tables as $table_id ) {
					if ( current_user_can( 'iggogrid_delete_table', $table_id ) ) {
						$deleted = IggoGrid::$model_table->delete( $table_id );
						if ( is_wp_error( $deleted ) ) {
							$no_success[] = $table_id;
						}
					} else {
						$no_success[] = $table_id;
					}
				}
				break;
		}

		if ( count( $no_success ) != 0 ) { // maybe pass this information to the view?
			$message = "error_{$bulk_action}_not_all_tables";
		} else {
			$plural = ( count( $tables ) > 1 ) ? '_plural' : '';
			$message = "success_{$bulk_action}{$plural}";
		}

		// slightly more complex redirect method, to account for sort, search, and pagination in the WP_List_Table on the List View
		// but only if this action succeeds, to have everything fresh in the event of an error
		$sendback = wp_get_referer();
		if ( ! $sendback ) {
			$sendback = IggoGrid::url( array( 'action' => 'list', 'message' => $message ) );
		} else {
			$sendback = remove_query_arg( array( 'action', 'message', 'table_id' ), $sendback );
			$sendback = add_query_arg( array( 'action' => 'list', 'message' => $message ), $sendback );
		}
		wp_redirect( $sendback );
		exit;
	}

	/**
	 * Save a table after the "Edit" screen was submitted
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_edit() {
		if ( empty( $_POST['table'] ) || empty( $_POST['table']['id'] ) ) {
			IggoGrid::redirect( array( 'action' => 'list', 'message' => 'error_save' ) );
		} else {
			$edit_table = wp_unslash( $_POST['table'] );
		}

		IggoGrid::check_nonce( 'edit', $edit_table['id'], 'nonce-edit-table' );

		if ( ! current_user_can( 'iggogrid_edit_table', $edit_table['id'] ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		// Options array must exist, so that checkboxes can be evaluated
		if ( empty( $edit_table['options'] ) ) {
			IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $edit_table['id'], 'message' => 'error_save' ) );
		}

		// Evaluate options that have a checkbox (only necessary in Admin Controller, where they might not be set (if unchecked))
		$checkbox_options = array(
			'table_head', 'table_foot', 'alternating_row_colors', 'row_hover', 'print_name', 'print_description', // Table Options
			'use_datatables', 'datatables_sort', 'datatables_filter', 'datatables_paginate', 'datatables_lengthchange', 'datatables_info', 'datatables_scrollx', // DataTables JS Features
		);
		foreach ( $checkbox_options as $option ) {
			$edit_table['options'][ $option ] = ( isset( $edit_table['options'][ $option ] ) && 'true' === $edit_table['options'][ $option ] );
		}

		// Load existing table from DB
		$existing_table = IggoGrid::$model_table->load( $edit_table['id'], false, true ); // Load table, without table data, but with options and visibility settings
		if ( is_wp_error( $existing_table ) ) { // @TODO: Maybe somehow load a new table here? (IggoGrid::$model_table->get_table_template())?
			IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $edit_table['id'], 'message' => 'error_save' ) );
		}
		
		// Check consistency of new table, and then merge with existing table
		$table = IggoGrid::$model_table->prepare_table( $existing_table, $edit_table );
		if ( is_wp_error( $table ) ) {
			IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $edit_table['id'], 'message' => 'error_save' ) );
		}

		// DataTables Custom Commands can only be edit by trusted users
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$table['options']['datatables_custom_commands'] = $existing_table['options']['datatables_custom_commands'];
		}

		// Save updated table
		$saved = IggoGrid::$model_table->save( $table );
		if ( is_wp_error( $saved ) ) {
			IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $table['id'], 'message' => 'error_save' ) );
		}

		// Check if ID change is desired
		if ( $table['id'] === $table['new_id'] ) { // if not, we are done
			IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $table['id'], 'message' => 'success_save' ) );
		}

		// Change table ID
		if ( current_user_can( 'iggogrid_edit_table_id', $table['id'] ) ) {
			$id_changed = IggoGrid::$model_table->change_table_id( $table['id'], $table['new_id'] );
			if ( ! is_wp_error( $id_changed ) ) {
				IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $table['new_id'], 'message' => 'success_save_success_id_change' ) );
			} else {
				IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $table['id'], 'message' => 'success_save_error_id_change' ) );
			}
		} else {
			IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $table['id'], 'message' => 'success_save_error_id_change' ) );
		}
	}

	/**
	 * Add a table, according to the parameters on the "Add new Table" screen
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_add() {
		
		IggoGrid::check_nonce( 'add' );
		if ( ! current_user_can( 'iggogrid_add_tables' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		if ( empty( $_POST['table'] ) || ! is_array( $_POST['table'] ) ) {
			IggoGrid::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );
		} else {
			$add_table = wp_unslash( $_POST['table'] );
		}
// 		var_dump($add_table);die;
		// Perform sanity checks of posted data
		$name = ( isset( $add_table['name'] ) ) ? $add_table['name'] : '';
		$description = ( isset( $add_table['description'] ) ) ? $add_table['description'] : '';
		
		if ( ! isset( $_POST['columns'] )  || empty($_POST['columns'])) {
			IggoGrid::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );
		}

// 		$num_rows = absint( $add_table['rows'] );
// 		$num_columns = absint( $add_table['columns'] );
// 		if ( 0 == $num_rows || 0 == $num_columns ) {
// 			IggoGrid::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );
// 		}
// 		if(!isset($_POST['columns']) || empty($_POST['columns'])){
// 			IggoGrid::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );
// 		}
		
		$columns = wp_unslash( $_POST['columns'] );
// 		print_r($columns);
// 		$columns = array_values($columns);
		$num_rows = count($columns);
// 		var_dump($columns);die;
		// Create a new table array with information from the posted data
		$new_table = array(
			'name' => $name,
// 			'columns'=>$columns,
			'description' => $description,
			'data' => array_fill( 0, $num_rows, array_fill( 0, $num_rows, '' ) ),
			'visibility' => array(
				'rows' => array_fill( 0, $num_rows, 1 ),
				//'columns' => array_fill( 0, $num_columns, 1 ),
				'columns' => $columns
			),
		);
		
		// Merge this data into an empty table template
		$table = IggoGrid::$model_table->prepare_table( IggoGrid::$model_table->get_table_template(), $new_table, false );
// 		print_r($table);die;
		if ( is_wp_error( $table ) ) {
			IggoGrid::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );
		}

		// Add the new table (and get its first ID)
		$table_id = IggoGrid::$model_table->add( $table );
		if ( is_wp_error( $table_id ) ) {
			IggoGrid::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );
		}

		IggoGrid::redirect( array( 'action' => 'edit', 'table_id' => $table_id, 'message' => 'success_add' ) );
	}

	/**
	 * Save changed "Plugin Options"
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_options() {
		IggoGrid::check_nonce( 'options' );

		if ( ! current_user_can( 'iggogrid_access_options_screen' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		if ( empty( $_POST['options'] ) || ! is_array( $_POST['options'] ) ) {
			IggoGrid::redirect( array( 'action' => 'options', 'message' => 'error_save' ) );
		} else {
			$posted_options = wp_unslash( $_POST['options'] );
		}

		// Valid new options that will be merged into existing ones
		$new_options = array();

		// Check each posted option value, and (maybe) add it to the new options
		if ( ! empty( $posted_options['admin_menu_parent_page'] ) && '-' != $posted_options['admin_menu_parent_page'] ) {
			$new_options['admin_menu_parent_page'] = $posted_options['admin_menu_parent_page'];
			// re-init parent information, as IggoGrid::redirect() URL might be wrong otherwise
			/** This filter is documented in classes/class-controller.php */
			$this->parent_page = apply_filters( 'iggogrid_admin_menu_parent_page', $posted_options['admin_menu_parent_page'] );
			$this->is_top_level_page = in_array( $this->parent_page, array( 'top', 'middle', 'bottom' ), true );
		}
		if ( ! empty( $posted_options['plugin_language'] ) && '-' != $posted_options['plugin_language'] ) {
			// only allow "auto" language and all values that have a translation
			if ( 'auto' == $posted_options['plugin_language'] || array_key_exists( $posted_options['plugin_language'], $this->get_plugin_languages() ) ) {
				$new_options['plugin_language'] = $posted_options['plugin_language'];
			}
		}

		// Custom CSS can only be saved if the user is allowed to do so
		$update_custom_css_files = false;
		if ( current_user_can( 'iggogrid_edit_options' ) ) {
			// Checkbox
			$new_options['use_custom_css'] = ( isset( $posted_options['use_custom_css'] ) && 'true' === $posted_options['use_custom_css'] );

			if ( isset( $posted_options['custom_css'] ) ) {
				$new_options['custom_css'] = $posted_options['custom_css'];

				$iggogrid_css = IggoGrid::load_class( 'IggoGrid_CSS', 'class-css.php', 'classes' );
				$new_options['custom_css'] = $iggogrid_css->sanitize_css( $new_options['custom_css'] ); // Sanitize and tidy up Custom CSS
				$new_options['custom_css_minified'] = $iggogrid_css->minify_css( $new_options['custom_css'] ); // Minify Custom CSS

				// Maybe update CSS files as well
				$custom_css_file_contents = $iggogrid_css->load_custom_css_from_file( 'normal' );
				if ( false === $custom_css_file_contents ) {
					$custom_css_file_contents = '';
				}
				if ( $new_options['custom_css'] !== $custom_css_file_contents ) { // don't write to file if it already has the desired content
					$update_custom_css_files = true;
					// Set to false again. As it was set here, it will be set true again, if file saving succeeds
					$new_options['use_custom_css_file'] = false;
				}
			}
		}

		// save gathered new options (will be merged into existing ones), and flush caches of caching plugins, to make sure that the new Custom CSS is used
		if ( ! empty( $new_options ) ) {
			IggoGrid::$model_options->update( $new_options );
			IggoGrid::$model_table->_flush_caching_plugins_caches();
		}

		if ( $update_custom_css_files ) { // capability check is performed above
			IggoGrid::redirect( array( 'action' => 'options', 'item' => 'save_custom_css' ), true );
		}

		IggoGrid::redirect( array( 'action' => 'options', 'message' => 'success_save' ) );
	}

	
	/**
	 * Save GET actions
	 */

	/**
	 * Hide a header message on an admin screen
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_hide_message() {
		$message_item = ! empty( $_GET['item'] ) ? $_GET['item'] : '';
		IggoGrid::check_nonce( 'hide_message', $message_item );

		if ( ! current_user_can( 'iggogrid_list_tables' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		$updated_options = array( "message_{$message_item}" => false );
		if ( 'plugin_update' == $message_item ) {
			$updated_options['message_plugin_update_content'] = '';
		}
		IggoGrid::$model_options->update( $updated_options );

		$return = ! empty( $_GET['return'] ) ? $_GET['return'] : 'list';
		IggoGrid::redirect( array( 'action' => $return ) );
	}

	/**
	 * Delete a table
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_delete_table() {
		$table_id = ( ! empty( $_GET['item'] ) ) ? $_GET['item'] : false;
		IggoGrid::check_nonce( 'delete_table', $table_id );

		$return = ! empty( $_GET['return'] ) ? $_GET['return'] : 'list';
		$return_item = ! empty( $_GET['return_item'] ) ? $_GET['return_item'] : false;

		if ( false === $table_id ) { // nonce check should actually catch this already
			IggoGrid::redirect( array( 'action' => $return, 'message' => 'error_delete', 'table_id' => $return_item ) );
		}

		if ( ! current_user_can( 'iggogrid_delete_table', $table_id ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		$deleted = IggoGrid::$model_table->delete( $table_id );
		if ( is_wp_error( $deleted ) ) {
			IggoGrid::redirect( array( 'action' => $return, 'message' => 'error_delete', 'table_id' => $return_item ) );
		}

		// slightly more complex redirect method, to account for sort, search, and pagination in the WP_List_Table on the List View
		// but only if this action succeeds, to have everything fresh in the event of an error
		$sendback = wp_get_referer();
		if ( ! $sendback ) {
			$sendback = IggoGrid::url( array( 'action' => 'list', 'message' => 'success_delete', 'table_id' => $return_item ) );
		} else {
			$sendback = remove_query_arg( array( 'action', 'message', 'table_id' ), $sendback );
			$sendback = add_query_arg( array( 'action' => 'list', 'message' => 'success_delete', 'table_id' => $return_item ), $sendback );
		}
		wp_redirect( $sendback );
		exit;
	}

	/**
	 * Copy a table
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_copy_table() {
		$table_id = ( ! empty( $_GET['item'] ) ) ? $_GET['item'] : false;
		IggoGrid::check_nonce( 'copy_table', $table_id );

		$return = ! empty( $_GET['return'] ) ? $_GET['return'] : 'list';
		$return_item = ! empty( $_GET['return_item'] ) ? $_GET['return_item'] : false;

		if ( false === $table_id ) { // nonce check should actually catch this already
			IggoGrid::redirect( array( 'action' => $return, 'message' => 'error_copy', 'table_id' => $return_item ) );
		}

		if ( ! current_user_can( 'iggogrid_copy_table', $table_id ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		$this->init_i18n_support(); // for the translation of "Copy of"

		$copy_table_id = IggoGrid::$model_table->copy( $table_id );
		if ( is_wp_error( $copy_table_id ) ) {
			IggoGrid::redirect( array( 'action' => $return, 'message' => 'error_copy', 'table_id' => $return_item ) );
		} else {
			$return_item = $copy_table_id;
		}

		// slightly more complex redirect method, to account for sort, search, and pagination in the WP_List_Table on the List View
		// but only if this action succeeds, to have everything fresh in the event of an error
		$sendback = wp_get_referer();
		if ( ! $sendback ) {
			$sendback = IggoGrid::url( array( 'action' => $return, 'message' => 'success_copy', 'table_id' => $return_item ) );
		} else {
			$sendback = remove_query_arg( array( 'action', 'message', 'table_id' ), $sendback );
			$sendback = add_query_arg( array( 'action' => $return, 'message' => 'success_copy', 'table_id' => $return_item ), $sendback );
		}
		wp_redirect( $sendback );
		exit;
	}

	/**
	 * Preview a table
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_preview_table() {
		$table_id = ( ! empty( $_GET['item'] ) ) ? $_GET['item'] : false;
		IggoGrid::check_nonce( 'preview_table', $table_id );

		$this->init_i18n_support();

		if ( false === $table_id ) { // nonce check should actually catch this already
			wp_die( __( 'The preview could not be loaded.', 'iggogrid' ), __( 'Preview', 'iggogrid' ) );
		}

		if ( ! current_user_can( 'iggogrid_preview_table', $table_id ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		// Load existing table from DB
		$table = IggoGrid::$model_table->load( $table_id, true, true ); // Load table, with table data, options, and visibility settings
		if ( is_wp_error( $table ) ) {
			wp_die( __( 'The table could not be loaded.', 'iggogrid' ), __( 'Preview', 'iggogrid' ) );
		}

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
		$view_data = array(
			'table_id' => $table_id,
			'head_html' => $_render->get_preview_css(),
			'body_html' => $_render->get_output(),
		);

		$custom_css = IggoGrid::$model_options->get( 'custom_css' );
		if ( ! empty( $custom_css ) ) {
			$view_data['head_html'] .= "<style type=\"text/css\">\n{$custom_css}\n</style>\n";
		}

		// Prepare, initialize, and render the view
		$this->view = IggoGrid::load_view( 'preview_table', $view_data );
		$this->view->render();
	}

	/**
	 * Show a list of tables in the Editor toolbar Thickbox (opened by TinyMCE or Quicktags button)
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_editor_button_thickbox() {
		IggoGrid::check_nonce( 'editor_button_thickbox' );

		if ( ! current_user_can( 'iggogrid_list_tables' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		$this->init_i18n_support();

		$view_data = array(
			'table_ids' => IggoGrid::$model_table->load_all( false ), // Load all table IDs without priming the post meta cache, as table options/visibility are not needed
		);

		set_current_screen( 'iggogrid_editor_button_thickbox' );

		// Prepare, initialize, and render the view
		$this->view = IggoGrid::load_view( 'editor_button_thickbox', $view_data );
		$this->view->render();
	}


	/**
	 * Uninstall IggoGrid, and delete all tables and options
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_uninstall_iggogrid() {
		IggoGrid::check_nonce( 'uninstall_iggogrid' );

		$plugin = IGGOGRID_BASENAME;

		if ( ! current_user_can( 'activate_plugins' ) || ! current_user_can( 'iggogrid_edit_options' ) || ! current_user_can( 'iggogrid_delete_tables' ) || is_plugin_active_for_network( $plugin ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		// Deactivate IggoGrid for the site (but not for the network)
		deactivate_plugins( $plugin, false, false );
		update_option( 'recently_activated', array( $plugin => time() ) + (array) get_option( 'recently_activated', array() ) );

		// Delete all tables, "Custom CSS" files, and options
		IggoGrid::$model_table->delete_all();
		$iggogrid_css = IggoGrid::load_class( 'IggoGrid_CSS', 'class-css.php', 'classes' );
		$css_files_deleted = $iggogrid_css->delete_custom_css_files();
		IggoGrid::$model_options->remove_access_capabilities();

		IggoGrid::$model_table->destroy();
		IggoGrid::$model_options->destroy();

		$this->init_i18n_support();

		$output = '<strong>' . __( 'IggoGrid was uninstalled successfully.', 'iggogrid' ) . '</strong><br /><br />';
		$output .= __( 'All tables, data, and options were deleted.', 'iggogrid' );
		if ( is_multisite() ) {
			$output .= ' ' . __( 'You may now ask the network admin to delete the plugin&#8217;s folder <code>iggogrid</code> from the server, if no other site in the network uses it.', 'iggogrid' );
		} else {
			$output .= ' ' . __( 'You may now manually delete the plugin&#8217;s folder <code>iggogrid</code> from the <code>plugins</code> directory on your server or use the &#8220;Delete&#8221; link for IggoGrid on the WordPress &#8220;Plugins&#8221; page.', 'iggogrid' );
		}
		if ( $css_files_deleted ) {
			$output .= ' ' . __( 'Your IggoGrid &#8220;Custom CSS&#8221; files have been deleted automatically.', 'iggogrid' );
		} else {
			if ( is_multisite() ) {
				$output .= ' ' . __( 'Please also ask him to delete your IggoGrid &#8220;Custom CSS&#8221; files from the server.', 'iggogrid' );
			} else {
				$output .= ' ' . __( 'You may now also delete your IggoGrid &#8220;Custom CSS&#8221; files in the <code>wp-content</code> folder.', 'iggogrid' );
			}
		}
		$output .= "</p>\n<p>";
		if ( ! is_multisite() || is_super_admin() ) {
			$output .= '<a class="button" href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . __( 'Go to &#8220;Plugins&#8221; page', 'iggogrid' ) . '</a> ';
		}
		$output .= '<a class="button" href="' . esc_url( admin_url( 'index.php' ) ) . '">' . __( 'Go to Dashboard', 'iggogrid' ) . '</a>';

		wp_die( $output, __( 'Uninstall IggoGrid', 'iggogrid' ), array( 'response' => 200, 'back_link' => false ) );
	}

} // class IggoGrid_Admin_Controller
