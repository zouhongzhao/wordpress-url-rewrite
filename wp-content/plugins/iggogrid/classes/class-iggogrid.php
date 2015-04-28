<?php
/**
 *IggoGrid Class
 *
 * @package IggoGrid
 * @author Iggo
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * IggoGrid class
 * @package IggoGrid
 * @author Iggo
 * @since 1.0.0
 */
abstract class IggoGrid {

	/**
	 * IggoGrid version
	 *
	 * Increases whenever a new plugin version is released
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const version = '0.1';

	/**
	 * IggoGrid internal plugin version ("options scheme" version)
	 *
	 * Increases whenever the scheme for the plugin options changes, or on a plugin update
	 *
	 * @since 1.0.0
	 *
	 * @const int
	 */
	const db_version = 1;

	/**
	 * IggoGrid "table scheme" (data format structure) version
	 *
	 * Increases whenever the scheme for a $table changes,
	 * used to be able to update plugin options and table scheme independently
	 *
	 * @since 1.0.0
	 *
	 * @const int
	 */
	const table_scheme_version = 3;

	/**
	 * Instance of the Options Model
	 *
	 * @since 1.3.0
	 *
	 * @var object
	 */
	public static $model_options;

	/**
	 * Instance of the Table Model
	 *
	 * @since 1.3.0
	 *
	 * @var object
	 */
	public static $model_table;

	/**
	 * Instance of the controller
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $controller;

	/**
	 * Name of the Shortcode to show a IggoGrid table
	 * Should only be modified through the filter hook 'iggogrid_table_shortcode'
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $shortcode = 'iggotable';

	/**
	 * Name of the Shortcode to show extra information of a IggoGrid table
	 * Should only be modified through the filter hook 'iggogrid_table_info_shortcode'
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $shortcode_info = 'table-info';

	/**
	 * Start-up IggoGrid (run on WordPress "init") and load the controller for the current state
	 *
	 * @since 1.0.0
	 * @uses load_controller()
	 */
	public static function run() {
		/**
		 * Fires when IggoGrid is loaded.
		 *
		 * @since 1.0.0
		 */
		do_action( 'iggogrid_run' );
		// exit early if IggoGrid doesn't have to be loaded
		if ( ( 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) ) // Login screen
				|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
				|| ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}

		// check if minimum requirements are fulfilled, currently WordPress 3.8
		if ( version_compare( str_replace( '-src', '', $GLOBALS['wp_version'] ), '3.8', '<' ) ) {
			// show error notice to admins, if WP is not installed in the minimum required version, in which case IggoGrid will not work
			if ( current_user_can( 'update_plugins' ) ) {
				add_action( 'admin_notices', array( 'IggoGrid', 'show_minimum_requirements_error_notice' ) );
			}
			// and exit IggoGrid
			return;
		}

		/**
		 * Filter the string that is used as the [table] Shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @param string $shortcode The [table] Shortcode string.
		 */
		
		self::$shortcode = apply_filters( 'iggogrid_table_shortcode', self::$shortcode );
		
		/**
		 * Filter the string that is used as the [table-info] Shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @param string $shortcode_info The [table-info] Shortcode string.
		 */
		self::$shortcode_info = apply_filters( 'iggogrid_table_info_shortcode', self::$shortcode_info );

		// Load modals for table and options, to be accessible from everywhere via `IggoGrid::$model_options` and `IggoGrid::$model_table`
		self::$model_options = self::load_model( 'options' );
		self::$model_table = self::load_model( 'table' );
		if ( is_admin() ) {
			$controller = 'admin';
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$controller .= '_ajax';
			}
		} else {
			$controller = 'frontend';
		}
		self::$controller = self::load_controller( $controller );
	}
	
	

	/**
	 * Load a file with require_once(), after running it through a filter
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Name of the PHP file with the class
	 * @param string $folder Name of the folder with $class's $file
	 */
	public static function load_file( $file, $folder ) {
		$full_path = IGGOGRID_ABSPATH . $folder . '/' . $file;
		/**
		 * Filter the full path of a file that shall be loaded.
		 *
		 * @since 1.0.0
		 *
		 * @param string $full_path Full path of the file that shall be loaded.
		 * @param string $file      File name of the file that shall be loaded.
		 * @param string $folder    Folder name of the file that shall be loaded.
		 */
		$full_path = apply_filters( 'iggogrid_load_file_full_path', $full_path, $file, $folder );
		
		if ( $full_path ) {
			require_once $full_path;
		}
	}

	/**
	 * Create a new instance of the $class, which is stored in $file in the $folder subfolder
	 * of the plugin's directory
	 *
	 * @since 1.0.0
	 *
	 * @param string $class Name of the class
	 * @param string $file Name of the PHP file with the class
	 * @param string $folder Name of the folder with $class's $file
	 * @param mixed $params (optional) Parameters that are passed to the constructor of $class
	 * @return object Initialized instance of the class
	 */
	public static function load_class( $class, $file, $folder, $params = null ) {
		/**
		 * Filter name of the class that shall be loaded.
		 *
		 * @since 1.0.0
		 *
		 * @param string $class Name of the class that shall be loaded.
		 */
		$class = apply_filters( 'iggogrid_load_class_name', $class );
		if ( ! class_exists( $class ) ) {
			self::load_file( $file, $folder );
		}
		$the_class = new $class( $params );
		return $the_class;
	}

	/**
	 * Create a new instance of the $model, which is stored in the "models" subfolder
	 *
	 * @since 1.0.0
	 * @uses load_class()
	 *
	 * @param string $model Name of the model
	 * @return object Instance of the initialized model
	 */
	public static function load_model( $model ) {
		self::load_file( 'class-model.php', 'classes' ); // Model Base Class
		$ucmodel = ucfirst( $model ); // make first letter uppercase for a better looking naming pattern
		$the_model = self::load_class( "IggoGrid_{$ucmodel}_Model", "model-{$model}.php", 'models' );
		return $the_model;
	}

	/**
	 * Create a new instance of the $view, which is stored in the "views" subfolder, and set it up with $data
	 *
	 * @since 1.0.0
	 * @uses load_class()
	 *
	 * @param string $view Name of the view to load
	 * @param array $data (optional) Parameters/PHP variables that shall be available to the view
	 * @return object Instance of the initialized view, already set up, just needs to be render()ed
	 */
	public static function load_view( $view, array $data = array() ) {
		self::load_file( 'class-view.php', 'classes' ); // View Base Class
		$ucview = ucfirst( $view ); // make first letter uppercase for a better looking naming pattern
		$the_view = self::load_class( "IggoGrid_{$ucview}_View", "view-{$view}.php", 'views' );
		$the_view->setup( $view, $data );
		return $the_view;
	}

	/**
	 * Create a new instance of the $controller, which is stored in the "controllers" subfolder
	 *
	 * @since 1.0.0
	 * @uses load_class()
	 *
	 * @param string $controller Name of the controller
	 * @return object Instance of the initialized controller
	 */
	public static function load_controller( $controller ) {
		self::load_file( 'class-controller.php', 'classes' ); // Controller Base Class
		$uccontroller = ucfirst( $controller ); // make first letter uppercase for a better looking naming pattern
		$the_controller = self::load_class( "IggoGrid_{$uccontroller}_Controller", "controller-{$controller}.php", 'controllers' );
		return $the_controller;
	}

	/**
	 * Generate the complete nonce string, from the nonce base, the action and an item, e.g. iggogrid_delete_table_3
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action for which the nonce is needed
	 * @param string|bool $item (optional) Item for which the action will be performed, like "table"
	 * @return string The resulting nonce string
	 */
	public static function nonce( $action, $item = false ) {
		$nonce = "iggogrid_{$action}";
		if ( $item ) {
			$nonce .= "_{$item}";
		}
		return $nonce;
	}

	/**
	 * Check whether a nonce string is valid
	 *
	 * @since 1.0.0
	 * @uses nonce()
	 *
	 * @param string $action Action for which the nonce should be checked
	 * @param string|bool $item (optional) Item for which the action should be performed, like "table"
	 * @param string $query_arg (optional) Name of the nonce query string argument in $_POST
	 * @param bool $ajax Whether the nonce comes from an AJAX request
	 */
	public static function check_nonce( $action, $item = false, $query_arg = '_wpnonce', $ajax = false ) {
		$nonce_action = self::nonce( $action, $item );
		if ( $ajax ) {
			check_ajax_referer( $nonce_action, $query_arg );
		} else {
			check_admin_referer( $nonce_action, $query_arg );
		}
	}

	/**
	 * Calculate the column index (number) of a column header string (example: A is 1, AA is 27, ...)
	 *
	 * For the opposite, @see number_to_letter()
	 *
	 * @since 1.0.0
	 *
	 * @param string $column Column string
	 * @return int $number Column number, 1-based
	 */
	public static function letter_to_number( $column ) {
		$column = strtoupper( $column );
		$count = strlen( $column );
		$number = 0;
		for ( $i = 0; $i < $count; $i++ ) {
			$number += ( ord( $column[ $count-1-$i ] ) - 64 ) * pow( 26, $i );
		}
		return $number;
	}

	/**
	 * "Calculate" the column header string of a column index (example: 2 is B, AB is 28, ...)
	 *
	 * For the opposite, @see letter_to_number()
	 *
	 * @since 1.0.0
	 *
	 * @param int $number Column number, 1-based
	 * @return string $column Column string
	 */
	public static function number_to_letter( $number ) {
		$column = '';
		while ( $number > 0 ) {
			$column = chr( 65 + ( ( $number-1 ) % 26 ) ) . $column;
			$number = floor( ( $number-1 ) / 26 );
		}
		return $column;
	}

	/**
	 * Get a nice looking date and time string from the mySQL format of datetime strings for output
	 *
	 * @param string $datetime DateTime string in mySQL format or a Unix timestamp
	 * @param string $type (optional) Type of $datetime, 'mysql' or 'timestamp'
	 * @param string $separator (optional) Separator between date and time
	 * @return string Nice looking string with the date and time
	 */
	public static function format_datetime( $datetime, $type = 'mysql', $separator = ' ' ) {
		if ( 'mysql' == $type ) {
			return mysql2date( get_option( 'date_format' ), $datetime ) . $separator . mysql2date( get_option( 'time_format' ), $datetime );
		} else {
			return date_i18n( get_option( 'date_format' ), $datetime ) . $separator . date_i18n( get_option( 'time_format' ), $datetime );
		}
	}

	/**
	 * Get the name from a WP user ID (used to store information on last editor of a table)
	 *
	 * @param int $user_id WP user ID
	 * @return string Nickname of the WP user with the $user_id
	 */
	public static function get_user_display_name( $user_id ) {
		$user = get_userdata( $user_id );
		return ( $user && isset( $user->display_name ) ) ? $user->display_name : __( '<em>unknown</em>', 'iggogrid' );
	}

	/**
	 * Generate the action URL, to be used as a link within the plugin (e.g. in the submenu navigation or List of Tables)
	 *
	 * @since 1.0.0
	 *
	 * @param array $params (optional) Parameters to form the query string of the URL
	 * @param bool $add_nonce (optional) Whether the URL shall be nonced by WordPress
	 * @param string $target (optional) Target File, e.g. "admin-post.php" for POST requests
	 * @return string The URL for the given parameters (already run through esc_url() with $add_nonce == true!)
	 */
	public static function url( array $params = array(), $add_nonce = false, $target = '' ) {

		// default action is "list", if no action given
		if ( ! isset( $params['action'] ) ) {
			$params['action'] = 'list';
		}
		$nonce_action = $params['action'];
		
		if ( $target ) {
			$params['action'] = "iggogrid_{$params['action']}";
		} else {
			$params['page'] = 'iggogrid';
			// top-level parent page needs special treatment for better action strings
			
			if ( self::$controller->is_top_level_page ) {
				$target = 'admin.php';
				if ( ! in_array( $params['action'], array( 'list', 'edit' ), true ) ) {
					$params['page'] = "iggogrid_{$params['action']}";
				}
				if ( ! in_array( $params['action'], array( 'edit' ), true ) ) {
					$params['action'] = false;
				}
			} else {
				$target = self::$controller->parent_page;
			}
		}

		// $default_params also determines the order of the values in the query string
		$default_params = array(
			'page' => false,
			'action' => false,
			'item' => false,
		);
		$params = array_merge( $default_params, $params );

		$url = add_query_arg( $params, admin_url( $target ) );
		if ( $add_nonce ) {
			$url = wp_nonce_url( $url, self::nonce( $nonce_action, $params['item'] ) ); // wp_nonce_url() does esc_html()
		}
		return $url;
	}

	/**
	 * Create a redirect URL from the $target_parameters and redirect the user
	 *
	 * @since 1.0.0
	 * @uses url()
	 *
	 * @param array $params (optional) Parameters from which the target URL is constructed
	 * @param bool $add_nonce (optional) Whether the URL shall be nonced by WordPress
	 */
	public static function redirect( array $params = array(), $add_nonce = false ) {
		$redirect = self::url( $params );
		if ( $add_nonce ) {
			if ( ! isset( $params['item'] ) ) {
				$params['item'] = false;
			}
			// don't use wp_nonce_url(), as that uses esc_html()
			$redirect = add_query_arg( '_wpnonce', wp_create_nonce( self::nonce( $params['action'], $params['item'] ) ), $redirect );
		}
		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Show an error notice to admins, if IggoGrid's minimum requirements are not reached
	 *
	 * @since 1.0.0
	 */
	public static function show_minimum_requirements_error_notice() {
		// Message is not translated as it is shown on every admin screen, for which we don't want to load translations
		echo '<div class="error"><p>' .
			'<strong>Attention:</strong> ' .
			'The installed version of WordPress is too old for the IggoGrid plugin! IggoGrid requires an up-to-date version! <strong>Please <a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">update your WordPress installation</a></strong>!' .
			"</p></div>\n";
	}


	
	function activate() {
		global $wp_rewrite;
		self::flush_rewrite_rules();
	}
	
	// Took out the $wp_rewrite->rules replacement so the rewrite rules filter could handle this.
	function create_rewrite_rules($rules) {
		global $wp_rewrite;
		$firstRules = array(
				'matkustaminen/lentaen/lentokenttakuljetukset'=>'index.php?single_page=matkustaminen',
		);
		$newRule = array(
				
// 				'majoitus/kohde/([_0-9a-z-]+)' => 'index.php?url_key='.$wp_rewrite->preg_index(1),
// 				'accommodation/kohde/([_0-9a-z-]+)' => 'index.php?url_key='.$wp_rewrite->preg_index(1),
			
// 				'majoitus/(hotellit)/(.+)'=> 'index.php?model=accommodation&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2),
		);
		
		$types = self::getTypesGroup();
// 		$rewriteType  = $wp_rewrite->preg_index(1);
// 		$rewriteArea  = $wp_rewrite->preg_index(2);
		$langId =  self::getLanguageId();
		
		if($langId == 1){//fi
			$newRule['majoitus/kohde/([_0-9a-z-]+)'] = 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$newRule['ruokailu/kohde/([_0-9a-z-]+)'] = 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$newRule['activity/kohde/([_0-9a-z-]+)'] = 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$newRule['accommodation/kohde/([_0-9a-z-]+)'] = 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$newRule['restaurant/kohde/([_0-9a-z-]+)'] = 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$newRule['aktiviteetit/kohde/([_0-9a-z-]+)'] = 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$firstRules['majoitus/(tarjoukset)'] = 'index.php?model=accommodation&type=tarjoukset';
			$firstRules['aktiviteetit/(kayntikohteet)'] = 'index.php?model=activity&type='.$wp_rewrite->preg_index(1);
			$firstRules['aktiviteetit/(ohjelmakalenteri)'] = 'index.php?model=activity&type='.$wp_rewrite->preg_index(1);
			$newRule['aktiviteetit/(ohjelmakalenteri)/([_0-9a-z-]+)'] = 'index.php?model=activity&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
// 			$newRule['activity']= 'index.php?model=activity'; 
// 			$newRule['restaurant']= 'index.php?model=restaurant';
// 			$newRule['accommodation/kohde/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
		}
		
 		if($langId == 3){//en
			//$newRule['accommodation']= 'index.php?model=accommodation';
			$newRule['accommodation/code/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$newRule['activity/code/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$newRule['activities/code/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$newRule['restaurant/code/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
			$firstRules['activities/(activity-and-event-calendar)'] = 'index.php?model=activity&type='.$wp_rewrite->preg_index(1);
			$newRule['activities/(activity-and-event-calendar)/([_0-9a-z-]+)'] = 'index.php?model=activity&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
			$newRule['food-and-dining/(restaurants)'] = 'index.php?model=restaurant&type='.$wp_rewrite->preg_index(1);
			$newRule['food-and-dining/(restaurants)/([_0-9a-z-]+)'] = 'index.php?model=restaurant&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
			$firstRules['accommodation$']= 'index.php?model=accommodation';
			$firstRules['activity$']= 'index.php?model=activity';
			$firstRules['activities$']= 'index.php?model=activity';
			$firstRules['restaurant$']= 'index.php?model=restaurant';
			$firstRules['food-and-dining$']= 'index.php?model=restaurant';
			$firstRules['accommodation/(offers)'] = 'index.php?model=accommodation&type=tarjoukset';
 		}
		
 		if($langId == 5){//ru
 			//$newRule['accommodation']= 'index.php?model=accommodation';
 			$newRule['accommodation/code/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
 			$newRule['activity/code/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
 			$newRule['activities/code/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
 			$newRule['restaurant/code/([_0-9a-z-]+)']= 'index.php?url_key='.$wp_rewrite->preg_index(1);
 			$firstRules['activities/(activity-and-event-calendar)'] = 'index.php?model=activity&type='.$wp_rewrite->preg_index(1);
 			$newRule['activities/(activity-and-event-calendar)/([_0-9a-z-]+)'] = 'index.php?model=activity&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
 			$newRule['food-and-dining-ru/(restaurants-ru)'] = 'index.php?model=restaurant&type='.$wp_rewrite->preg_index(1);
 			$newRule['food-and-dining-ru/(restaurants-ru)/([_0-9a-z-]+)'] = 'index.php?model=restaurant&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
 			$newRule['food-and-dining/(restaurants)'] = 'index.php?model=restaurant&type='.$wp_rewrite->preg_index(1);
 			$newRule['food-and-dining/(restaurants)/([_0-9a-z-]+)'] = 'index.php?model=restaurant&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
 			$firstRules['accommodation$']= 'index.php?model=accommodation';
 			$firstRules['activity$']= 'index.php?model=activity';
 			$firstRules['restaurant$']= 'index.php?model=restaurant';
 			$firstRules['food-and-dining$']= 'index.php?model=restaurant';
 			$firstRules['accommodation/(hotel)'] = 'index.php?model=accommodation&type='.$wp_rewrite->preg_index(1);
 			$newRule['accommodation/(hotel)/([_0-9a-z-]+)'] = 'index.php?model=accommodation&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
 			$firstRules['accommodation/(townhouse)'] = 'index.php?model=accommodation&type='.$wp_rewrite->preg_index(1);
 			$newRule['accommodation/(townhouse)/([_0-9a-z-]+)'] = 'index.php?model=accommodation&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
 			$firstRules['accommodation/(offers)'] = 'index.php?model=accommodation&type=tarjoukset';
 		}
		
		//echo $langId;die;
		foreach ((array)$types as $key=>$items){
			$models = array();
			if($key == 'accommodation'){
				if($langId == 1){
					$models = array('majoitus');
				}elseif($langId == 3){
					$models = array('accommodation');
				}else{
					$models = array('accommodation');
				}
			}elseif ($key == 'restaurant'){
				if($langId == 1){
					$models = array('ruokailu');
				}elseif($langId == 3){
					$models = array('restaurant');
				}else{
					$models = array('restaurant');
				}
			}elseif ($key == 'activity'){
				if($langId == 1){
					$model = 'aktiviteetit';
				}elseif($langId == 3){
					$models = array('activity','activities');
				}else{
					$models = array('activity','activities');
				}
				
			}
			
			foreach ($items as $type){
				$type= self::replaceUrlStr($type);
				if(empty($type)){
					continue;
				}
				foreach($models as $model){
					$firstRules["{$model}/({$type})"] = "index.php?model={$key}&type=".$wp_rewrite->preg_index(1);
					$firstRules["{$model}/({$type})/([_0-9a-z-]+)"] = "index.php?model={$key}&type=".$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2);
				}
				
			} 
		}
//		print_r($newRule);
		$itemsKeys = self::getItemKeys($langId);
		
		foreach ((array)$itemsKeys as $old=>$new){
			$firstRules[$old.'$'] = 'index.php?url_key='.$new;
		}
// 		print_r($firstRules);
		$customRewrites = self::getCustomRewrites();
		foreach ((array)$customRewrites as $old=>$new){
			$firstRules[$old.'$'] = 'index.php?new_url='.$new;;
		}
// 		$newRule = $newRule+$newTypeRule;
// 		print_r($newRule);die;
// 		$newRule = array(
// 				'majoitus/kohde/(.+)' => 'index.php?url_key='.$wp_rewrite->preg_index(1),
// 				'majoitus/(.+)/(.+)'=> 'index.php?model=accommodation&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2),
// 				'ruokailu/(.+)/(.+)'=> 'index.php?model=restaurant&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2),
// 				'aktiviteetit/(.+)/(.+)'=> 'index.php?model=activity&type='.$wp_rewrite->preg_index(1).'&area='.$wp_rewrite->preg_index(2),
// 				'majoitus/(.+)'=> 'index.php?model=accommodation&type='.$wp_rewrite->preg_index(1),
// 				'ruokailu/(.+)'=> 'index.php?model=restaurant&type='.$wp_rewrite->preg_index(1),
// 				'aktiviteetit/(.+)'=> 'index.php?model=activity&type='.$wp_rewrite->preg_index(1),
// 				'matkailijalle/majoitus/kohteet/uuvana' => 'index.php?url_key=uuvana',
// 		);

// 		print_r($newRule);die;
		$newRules = $firstRules + $rules + $newRule;
//		print_r($firstRules);
		return $newRules;
	}
	
	function add_query_vars($qvars) {
		$qvars[] = 'url_key';
		$qvars[] = 'single_page';
		$qvars[] = 'new_url';
		$qvars[] = 'area';
		$qvars[] = 'type';
		$qvars[] = 'model';
		return $qvars;
	}
	
	function flush_rewrite_rules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
	
	function template_redirect_intercept() {
		$GLOBALS['iggoLangItemUrls'] = array();
		$langId = IggoGrid::getLanguageId();
		$currentSymbol = strtolower(substr(get_bloginfo ( 'language' ), 0, 2));
		//echo 888;die;
		global $wp_query;
// 		var_dump($wp_query->get('model'));
// 		var_dump($wp_query->get('type'));
// 		var_dump($wp_query->get('area'));die; 
		//info
// 		var_dump($wp_query->get('url_key'));die;
// 		var_dump(get_option('current_page_template'));die;
		if ($wp_query->get('url_key')) {
			$urlKey = self::replaceUrlStr($wp_query->get('url_key'));
//			$urlKey = $wp_query->get('url_key');
			
			global $wpdb;
			$where = "where url_key = '{$urlKey}' limit 1";
			$dbResult = array(); 
			$sql = "SELECT info.*,item.admin_added FROM iggogrid_item_info as info left join iggogrid_item as item on item.id = info.item_id {$where}";
			$row = $wpdb->get_row($sql,ARRAY_A);
			$GLOBALS['iggoGridInfoData'] = $row;
// 			print_r($GLOBALS['iggoGridInfoData']);die;

			echo self::locate_plugin_template('page-singleitem.php',true);
			exit;
		}

		//category

		if($wp_query->get('model') && ($wp_query->get('area') || $wp_query->get('type')) ){
			$area = $wp_query->get('area');
			$type = $wp_query->get('type');
			$model = $wp_query->get('model');
			$dbResult = array();
			//var_dump($GLOBALS['iggoGridListData']);die;
			//echo self::replaceStr('Etelärinne, 1-3 km saariselän keskustasta');die;
			$typesArray = IggoGrid::getTypesAndAreaByModel($model,$langId);
			$typeString = implode('","', $typesArray['typeData']);
			$dbResult = $typesArray;
			$dbResult['model'] = $model;
			global $wpdb;
			if($model == 'activity' and $type == 'kayntikohteet'){
				//special page
				$orderBy = 'ORDER BY `info`.`product_name` ASC';
				$customWhere = "and item.is_active=1 ";
				$where = ' where info.lang_id = '.$langId.' '.$customWhere." {$orderBy}";
				// 			$where = ' where info.lang_id = '.$langId.'  '.$customWhere.' order by info.destination_name asc';
				$sql = "SELECT item.admin_added,info.item_id,info.acc_properties,info.images,info.url_key,info.destination_name,info.headline,info.area,info.type,info.person,info.rooms,info.price,
				info.product_name,info.price_{$currentSymbol},info.type_code
				FROM iggogrid_item_info as info
				left join iggogrid_item as item on item.id = info.item_id  $where";
				$allTypes = array_keys($typesArray['typeData']);
				foreach( $wpdb->get_results($sql) as $key => $row) {
					$row = (array)$row;
					if(isset($allTypes)){
						if(!in_array($row['type_code'], $allTypes)){
							continue;
						}
					}
					if(empty($row['acc_properties'])){
						continue;
					}
					$acc_properties = unserialize($row['acc_properties']);
					if(isset($acc_properties['PropertyCriteria']) && isset($acc_properties['PropertyCriteria']['NameValueItem'])){
						$nameValueItem = $acc_properties['PropertyCriteria']['NameValueItem'];
					}
					$traveller = '';
					if(isset($nameValueItem)){
						foreach ($nameValueItem as $item){
							if ($item['Name'] == 'Traveller' && $item['Value'] == 0){
								$traveller = 'traveller';
								break;
							}
						}
					}
					if(empty($traveller)){
						continue;	
					}
					$dbResult['items'][] = $row;
				}
			}else{
				$customWhere = 'and item.is_active=1 ';
				if($type == 'tarjoukset'){
					$customWhere .= "and info.offer > 0";
				}
				if($model == 'accommodation'){
					$customWhere .= "and info.price > 0";
				}
					
				if($model == 'activity'){
					$orderBy .= 'ORDER BY `info`.`product_name` ASC';
				}else{
					$orderBy .= 'ORDER BY `info`.`destination_name` ASC';
				}
				$where = ' where info.lang_id = '.$langId.' '.$customWhere." {$orderBy}";
				// 			$where = ' where info.lang_id = '.$langId.'  '.$customWhere.' order by info.destination_name asc';
				$sql = "SELECT item.admin_added,info.item_id,info.acc_properties,info.images,info.url_key,info.destination_name,info.headline,info.area,info.type,info.person,info.rooms,info.price,
								info.product_name,info.price_{$currentSymbol},info.type_code
								FROM iggogrid_item_info as info
								left join iggogrid_item as item on item.id = info.item_id  $where";
				$allTypes = array_keys($typesArray['typeData']);
				foreach( $wpdb->get_results($sql) as $key => $row) {
						$row = (array)$row;
						if(isset($allTypes)){
							if(!in_array($row['type_code'], $allTypes)){
								continue;
						}
					}
					if($model == 'activity' && $row['admin_added'] == 0 && $row['price'] <= 0){
							continue;
					}
					$dbResult['items'][] = $row;
				}
			}
			
			$GLOBALS['iggoGridListData'] = $dbResult;
			echo self::locate_plugin_template('page-outsidecontent.php',true);
			exit;
		}
		
		//root catagory
		if($wp_query->get('model')){
			$model = $wp_query->get('model');
			$dbResult = array();
			$typesArray = IggoGrid::getTypesAndAreaByModel($model,$langId);
			$typeString = implode('","', $typesArray['typeData']);
			$dbResult = $typesArray;
			$dbResult['model'] = $model;
			$where = '';
			global $wpdb;
			$allowGrid = array(
					'accommodation'=>'accommodation',
					'restaurant'=>'restaurant',
					'activity'=>'activity'
			);
			$customWhere = 'and item.is_active=1 ';
			if($model == 'accommodation'){
				$customWhere .= "and info.price > 0";
			}
			$orderBy = '';
			if($model == 'activity'){
				$orderBy .= 'ORDER BY `info`.`product_name` ASC';
			}else{
				$orderBy .= 'ORDER BY `info`.`destination_name` ASC';
			}
			$where = ' where info.lang_id = '.$langId.' '.$customWhere." {$orderBy}";
			$sql = "SELECT item.admin_added,info.item_id,info.acc_properties,info.images,info.url_key,info.destination_name,info.headline,info.area,info.type,info.person,info.rooms,info.price,
			info.product_name,info.price_{$currentSymbol},info.type_code
			FROM iggogrid_item_info as info
			left join iggogrid_item as item on item.id = info.item_id  $where";
			
			$allTypes = array_keys($typesArray['typeData']);
// 			print_r($sql);
			foreach( $wpdb->get_results($sql) as $key => $row) {
				$row = (array)$row;
				if(isset($allTypes)){
					if(!in_array($row['type_code'], $allTypes)){
						continue;
					}
				}
				if($model == 'activity' && $row['admin_added'] == 0 && $row['price'] <= 0){
					continue;
				}
				$dbResult['items'][] = $row;
			}
			$GLOBALS['iggoGridListData'] = $dbResult;
			echo self::locate_plugin_template('page-outsidecontent.php',true);
			exit;
		}
		if ($wp_query->get('new_url')) {
			$newUrl = get_site_url().'/'.$wp_query->get('new_url').'/';
			header("Location:{$newUrl}");
			exit();
		}
		if ($wp_query->get('single_page')) {
			$page = $wp_query->get('single_page');
			$wsdl = 'http://varaamo.saariselka.com/intres/shops/Saariselka/WcfData/WinresDataService.svc?wsdl';
			$key= "33d45fd1-aede-9271-e166-ab32778e0134";
			
			$client = new SoapClient($wsdl);
			$row = $client->GetItem(array('destinationCode' => 'SBTRANS', 'productCode' => 'KULJ', 'culture' => 'Finnish', 'authorizationKey' => $key));
			
			$GLOBALS['iggoGridInfoData'] = $row;
				
			echo self::locate_plugin_template('page-lentokenttakuljetukset.php',true);
			echo $page;
			exit();
		}
		
	}
	function getTypesAndAreaByModel($currentType,$langId){
		$areaData = array();
		$typeData = array();
		$result = array();
		global $wpdb;
		$typesSql = "SELECT code FROM iggogrid_types where type = '{$currentType}' ";
		//  echo $typesSql;die;
		$typesArray = array();
		foreach( $wpdb->get_results($typesSql) as $key => $row) {
			$row = (array)$row;
			$typesArray[] = $row['code'];
		}
		//  print_r($typesArray);
		$where = "where lang_id = {$langId}";
		$sql = "SELECT type,value,text FROM iggogrid_translation $where";
		//  echo $sql;die;
		foreach( $wpdb->get_results($sql) as $key => $row) {
			$row = (array)$row;
			if(empty($row['text']) && $langId == 5){
				$sql2 = "SELECT text FROM iggogrid_translation WHERE lang_id = 3 and type='type' and value='{$row['value']}'";
				$en_row = $wpdb->get_row($sql2, ARRAY_A);
				$row['text'] = $en_row['text'];
			}
			//  	print_r($row);
// 			if(empty($row['text'])){
// 				continue;
// 			}
			if($row['type'] == 'area'){
				$areaData[$row['text']] = $row['text'];
			}
			if($row['type'] == 'type'){
				if(in_array($row['value'], $typesArray)){
						$typeData[$row['value']] = $row['text'];
				}
			}
		}
		$result['typesArray'] = $typesArray;
		$result['areaData'] = $areaData;
		$result['typeData'] = $typeData;
		return $result;
	}
	 function replaceUrlStr($str){
		$str = preg_replace("/([A-Z])([a-z])/", "-\\0", $str);
		$str  = strtolower($str);
		$msg = preg_replace("/ä/","a",$str);
		$msg = preg_replace("/å/","a",$msg);
		$msg = preg_replace("/ö/","o",$msg);
		$msg = preg_replace("/Å/","a",$msg);
		$msg = preg_replace("/[\s\x-\x#\[\]\{\}\'\"=+\-\(\)!*&%~^,.<|>\?$@]+/i","-",$msg);
		$msg = filter_var($msg, FILTER_SANITIZE_URL);
		$msg = stripslashes($msg);
		$msg=preg_replace('/-(-){1,}/',"-",$msg);
		$msg = trim($msg,'-');
		return $msg;
	 }
	function pushoutput($table,$id) {
		if(is_numeric($id) && is_string($table)){
			global $wpdb;
			$GLOBALS['iggoGridInfoData'] = '';
			
			$id = $id - 1;
			$table_name = $wpdb->prefix .$table;
			$flag = true;
			$table_name = $wpdb->prefix .$table;
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				$flag = false;
				$table_name = $table;
				if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
					$flag = true;
				}
			}
			if($flag){
				$sql = "SELECT * FROM {$table_name}  limit {$id},1";
				$GLOBALS['iggoGridInfoData'] = (array)$wpdb->get_row($sql);
			}
			if(!empty($GLOBALS['iggoGridInfoData'])){
				echo self::locate_plugin_template('page.php',true);
			}
		}
		exit;
	}
	
	function output( $output ) {
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
	
		// Commented to display in browser.
		// header( 'Content-type: application/json' );
	
		echo json_encode( $output );
	}
	
	function locate_plugin_template($template_name, $load = false, $require_once = true )
	{
// 		if ( !is_array($template_names) )
// 			return '';
	
		$located = '';
	
// 		$this_plugin_dir = WP_PLUGIN_DIR.'/'.str_replace( basename( __FILE__), "", plugin_basename(__FILE__) );
		$this_theme_dir = IGGOGRID_ABSPATH.'/themes/default/';
// 		foreach ( $template_names as $template_name ) {
// 			if ( !$template_name )
// 				continue;
// 			if ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
// 				$located = STYLESHEETPATH . '/' . $template_name;
// 				break;
// 			} else if ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
// 				$located = TEMPLATEPATH . '/' . $template_name;
// 				break;
// 			} else if ( file_exists( $this_plugin_dir .  $template_name) ) {
// 				$located =  $this_plugin_dir . $template_name;
// 				break;
// 			}
// 		}
		if ( file_exists( $this_theme_dir . $template_name) ) {
			$located = $this_theme_dir . $template_name;
		}else{
			return '';
		}
		if ( $load && '' != $located ){
			load_template( $located, $require_once );
		}
		//return $located;
	}
	
	function getLanguageId(){
		$languages = array(
										'fi'=>1,
										'se'=>2,
										'en'=>3,
										'de'=>4,
										'ru'=>5
									);
		$current = substr(get_bloginfo ( 'language' ), 0, 2);
		if(!isset($languages[$current])){
			$current = 'fi';
		}
		return $languages[$current];
	}

	//function
	//iggo item info
	function getItems($number = 1){
		global $wpdb;
		$dbResult = array();
		$lang = self::getLanguageId();
		$where = "where lang_id = {$lang}";
		$sql = "SELECT * FROM iggogrid_item_info {$where} limit {$number}";
		
		foreach( $wpdb->get_results($sql) as $key => $row) {
			$row = (array)$row;
			$dbResult[] = $row;//array_values($row);
		}
		return $dbResult;
	}
	
	function getTypesGroup($langId=null){

		if(is_null($langId)){
			$transName = 'iggo_rewrite_type_cache'; // Name of value in database.
		}else{
			$transName = 'iggo_rewrite_type_cache_'.$langId; // Name of value in database.
		}
		//var_dump($transName);
		$cacheTime = 10; // Time in minutes between updates.

 		if(false === ($dbResult = get_transient($transName) ) ){
 			global $wpdb;
			$where = "where trans.type = 'type'";
			if(!is_null($langId)){
				$where .= " and trans.lang_id={$langId}";
			}
			$sql = "SELECT trans.text,types.type,trans.value FROM iggogrid_translation as trans left join iggogrid_types as types on types.code = trans.value {$where}";
			$dbResult = array();
			foreach( $wpdb->get_results($sql) as $key => $row) {
				$row = (array)$row;
				if(empty($row['text'])){
					$sql2 = "SELECT text FROM iggogrid_translation WHERE lang_id = 3 and type='type' and value='{$row['value']}'";
					$en_row = $wpdb->get_row($sql2, ARRAY_A);
					$row['text'] = $en_row['text'];
				}
				//$row['text'] = self::replaceUrlStr($row['text']);
				if(empty($row['text'])){
					continue;
				}
				$dbResult[$row['type']][] = $row['text'];
			}
			set_transient($transName, $dbResult, 7200 * $cacheTime);
 		}
		return $dbResult;
	}
	
	function checkShowList($uri){
		if($uri[0] == 'activity'){
			if( isset($uri[1]) && ($uri[1] == self::__lang('ohjelmakalenteri') || $uri[1] == self::__lang('kayntikohteet') )){
				return true;
			}
			if(!isset($uri[1])){
				return true;
			}
			return false;
		}
		return true;
	}
	
	function checkShowWeekselector($uri){
		if($uri[0] == 'activity'){
			return true;
		}
		return false;
	}
	
	function checkShowFilter($uri){
		if($uri[0] == 'activity' && isset($uri[1]) && $uri[1] == self::__lang('kayntikohteet')){
			return false;
		}
		return true;
	}
	
	function checkShowFilterCheckbox($uri){
		if($uri[0] == 'restaurant'){
			return false;
		}
		return true;
	}
	
	function getFilterData($datas,$str){
		if(!is_array($datas) || empty($str)){
			return '';
		}
		foreach ($datas as $data){
			if($str == self::replaceUrlStr($data)){
				return $data;
			}
		}
		return '';
	}
	/*
	function getLanguages(){
		global $wpdb;
		$dbResult = array();
		$sql = "SELECT * FROM iggogrid_languages";
		foreach( $wpdb->get_results($sql) as $key => $row) {
			$row = (array)$row;
			$dbResult[$row['id']] = $row;//array_values($row);
		}
		return $dbResult;
	}
	*/
	function getLanguages(){
		$transName = 'iggo_languages_cache'; // Name of value in database.
		$cacheTime = 10; // Time in minutes between updates.
		if(false === ($dbResult = get_transient($transName) ) ){
			global $wpdb;
			$dbResult = array();
			$sql = "SELECT * FROM iggogrid_languages";
			foreach( $wpdb->get_results($sql) as $key => $row) {
				$row = (array)$row;
				$dbResult[$row['id']] = $row;//array_values($row);
			}
			set_transient($transName, $dbResult, 7200 * $cacheTime);
		}
		return $dbResult;
	}
	
	function get_ID_by_slug($page_slug) {
		$page = get_page_by_path($page_slug);
		if ($page) {
			return $page->ID;
		} else {
			return null;
		}
	}
	
	function getItemKeys($langId){
		global $wpdb;
		$dbResult = array();
		$where = "where lang_id = {$langId} and url_key !='' and old_url != ''";
		$sql = "SELECT url_key,old_url FROM iggogrid_item_info {$where}";
		foreach( $wpdb->get_results($sql) as $key => $row) {
			$row = (array)$row;
			$oldUrls = unserialize($row['old_url']);
			foreach ($oldUrls as $url){
				$dbResult[$url] = $row['url_key'];//array_values($row);
			}
		}
		return $dbResult;
	}
	function getCustomRewrites(){
		global $wpdb;
		$dbResult = array();
		$where = 'where old_url !="" and new_url != ""';
		$sql = "SELECT * FROM iggogrid_custom_rewrites {$where}";
		foreach( $wpdb->get_results($sql) as $key => $row) {
			$row = (array)$row;
			$dbResult[$row['old_url']] = $row['new_url'];//array_values($row);
		}
		return $dbResult;
	}
	
	function __lang($str){
		$langId = IggoGrid::getLanguageId();
		
		switch ($langId){
			case 1:
				include(IGGOGRID_ABSPATH . 'languages/fi.php');
				return isset($lang_fi[$str])?$lang_fi[$str]:$str;
			case 3:
				include(IGGOGRID_ABSPATH . 'languages/en.php');
				return isset($lang_en[$str])?$lang_en[$str]:$str;
				break;
			case 5:
				include(IGGOGRID_ABSPATH . 'languages/ru.php');
				return isset($lang_ru[$str])?$lang_ru[$str]:$str;
				break;
			default:
				return $str;
		}
	}
	
	function getAllWeeks($startdate=null,$enddate=null)
	{
		if(is_null($startdate)){
			$startdate = date('Y-m-d');  
		}
		if(is_null($enddate)){
			$enddate = date('Y-m-d',strtotime("+1 year"));
		}
	    if(!empty($startdate) && !empty($enddate)){
	        $startdate=strtotime($startdate);
	        $enddate=strtotime($enddate);
	        if($startdate<=$enddate){
	            $end_date=strtotime("next monday",$enddate);
	            if(date("w",$startdate)==1){
	                $start_date=$startdate;
	            }else{
	                $start_date=strtotime("last monday",$startdate);
	            }
	            $countweek=($end_date-$start_date)/(7*24*3600);
	            for($i=0;$i<$countweek;$i++){
	                $sd=date("Y-m-d",$start_date);
	                $ed=strtotime("+ 6 days",$start_date);
	                $eed=date("Y-m-d",$ed);
	                $arr[]=array($sd,$eed);
	                $start_date=strtotime("+ 1 day",$ed);
	            }
	            return $arr;    
	        }
	    }
	}
	//ajax function
	function apf_weekselector() { 
		$weekItemIds = array();
		$startdate = $_POST['startdate'];
		$enddate =  $_POST['enddate'];
 		global $wpdb;
 		$weekSql = "SELECT DISTINCT item_id FROM iggogrid_activity_availability WHERE day BETWEEN '{$startdate}' AND '{$enddate}' and free > 0";
 		//  	echo $weekSql;
 		foreach( $wpdb->get_results($weekSql) as $key => $row) {
 			array_push($weekItemIds, (int)$row->item_id);
 		}
 		$weekItemIds = array_unique($weekItemIds);
		// Return the String
		die(json_encode($weekItemIds));
	}
	
	//menu functions
	function getMenuChildPages($langId=null){
		$result = array();
		if(is_null($langId)){
			return $result;
		}
// 		$parentPageIds = array();
		$parentPageIds = array('accommodation'=>9,'activity'=>13,'restaurant'=>11);
// 		if($langId == 3){//en 
// 			$parentPageIds = array('accommodation'=>1008,'activity'=>816,'restaurant'=>1033);
// 		}elseif ($langId == 1){//fi
// 			$parentPageIds = array('accommodation'=>9,'activity'=>13,'restaurant'=>11);
// 		}elseif ($langId == 5){//ru
// 			$parentPageIds = array('accommodation'=>9,'activity'=>13,'restaurant'=>11);
// 		}
		foreach ($parentPageIds as $key=>$pid){
			$firstChildPages = get_children(array(
					'post_parent' => $pid,
					'post_type'   => 'any',
					'posts_per_page' => -1,
					'post_status' => 'publish'
			));
			foreach ($firstChildPages as $firstChildPage){
				$secondChildPages = get_children(array(
						'post_parent' => $firstChildPage->ID,
						'post_type'   => 'any',
						'posts_per_page' => -1,
						'post_status' => 'publish'
				));
// 				$first_child_permalink = get_permalink($firstChildPages->ID);
				$result[$key][$firstChildPage->post_title] = array('id'=>$firstChildPage->ID,'title'=>$firstChildPage->post_title,'name'=>$firstChildPage->post_name);
				if(!empty($secondChildPages)){
					foreach ($secondChildPages as $secondChildPage){
// 						$first_child_permalink = get_permalink($secondChildPage->ID);
						$result[$key][$firstChildPage->post_title]['childrens'][$secondChildPage->post_title] = array('id'=>$secondChildPage->ID,'title'=>$secondChildPage->post_title,'name'=>$secondChildPage->post_name);
					}
				}
			}
		}
		return $result;
	}
	
	function sortArray($originData=array(),$orderArray=array()){
		if(empty($originData) || empty($orderArray)){
			return $originData;
		}
		$newArray = array();
		foreach($orderArray as $key) {
			if(array_key_exists($key,$originData)) {
				$newArray[$key] = $originData[$key];
				unset($originData[$key]);
			}
		}
		return $newArray + $originData;
	}

	function ob2ar($obj) {
		if(is_object($obj)) {
			$obj = (array)$obj;
			$obj = self::ob2ar($obj);
		} elseif(is_array($obj)) {
			foreach($obj as $key => $value) {
				$obj[$key] = self::ob2ar($value);
			}
		}
		return $obj;
	}
	function languagesCode($lang_id){
		$langFlag = array(
				1=>array('short'=>'fi','long'=>'Finnish'),
				2=>array('short'=>'se','long'=>'Swedish'),
				3=>array('short'=>'en','long'=>'English'),
				4=>array('short'=>'de','long'=>'German'),
				5=>array('short'=>'ru','long'=>'Russian'),
		);
		return $langFlag[$lang_id];
	}
	
	function getRewriteUrlKey($model,$url,$lang_id){
		$urlLangs = array(
				//fi
				1=>array(
						'accommodation'=>'/majoitus/kohde/',
						'activity'=>'/aktiviteetit/kohde/',
						'restaurant'=>'/ruokailu/kohde/'
				),
				//se
				2=>array(
						'accommodation'=>'/se/accommodation/code/',
						'activity'=>'/se/activity/code/',
						'restaurant'=>'/se/restaurant/code/'
				),
				//en
				3=>array(
						'accommodation'=>'/en/accommodation/code/',
						'activity'=>'/en/activity/code/',
						'restaurant'=>'/en/restaurant/code/'
				),
				//de
				4=>array(
						'accommodation'=>'/de/accommodation/code/',
						'activity'=>'/de/activity/code/',
						'restaurant'=>'/de/restaurant/code/'
				),
				//ru
				5=>array(
						'accommodation'=>'/ru/accommodation/code/',
						'activity'=>'/ru/activity/code/',
						'restaurant'=>'/ru/restaurant/code/'
				),
		);
		return site_url().$urlLangs[$lang_id][$model].$url;
	}
} // class IggoGrid
