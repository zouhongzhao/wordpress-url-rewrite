<?php
/**
 * WordPress plugin "Iggo Partner" main file, responsible for initiating the plugin
 *
 * @package IggoPartner
 * @author Iggo
 * @version 1.0
 */
/*
Plugin Name: IggoPartner
Description: FYSI partner pages
Version: 1.0
Author: Iggo
Author email: dev@fenzsoft.com
Text Domain: iggopartner
Domain Path: /i18n/
License: GPL 2
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

// Define certain plugin variables as constants
define( 'IGGOPARTNER_ABSPATH', plugin_dir_path( __FILE__ ) );

define( 'IGGOPARTNER__FILE__', __FILE__ );
define( 'IGGOPARTNER_BASENAME', plugin_basename( IGGOPARTNER__FILE__ ) );

// // Load IggoPartner class, which holds common functions and variables
require_once IGGOPARTNER_ABSPATH . 'classes/partner.php';

// // Start up IggoPartner on WordPress's "init" hook
add_action( 'init', array( 'IggoPartner', 'run') );
register_activation_hook( __file__, array('IggoPartner', 'activate') );

// Using a filter instead of an action to create the rewrite rules.
// Write rules -> Add query vars -> Recalculate rewrite rules

add_filter('rewrite_rules_array', array('IggoPartner', 'create_rewrite_rules'));

add_filter('query_vars',array('IggoPartner', 'add_query_vars'));
add_action( 'wp_loaded', array('IggoPartner', 'flush_rewrite_rules'));
// Recalculates rewrite rules during admin init to save resourcees.
// Could probably run it once as long as it isn't going to change or check the
// $wp_rewrite rules to see if it's active.
//add_filter('admin_init', array('IggoPartner', 'flush_rewrite_rules'));
add_action( 'template_redirect', array('IggoPartner', 'template_redirect_intercept') );

