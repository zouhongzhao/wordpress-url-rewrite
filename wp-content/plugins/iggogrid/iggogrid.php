<?php
/**
 * WordPress plugin "IggoGrid" main file, responsible for initiating the plugin
 *
 * @package IggoGrid
 * @author Iggo
 * @version 0.1
 */

/*
Plugin Name: IggoGrid
Description: IggoGrid enables you to create and manage tables in your posts and pages, iggothout having to write HTML code, and it adds valuable functions for your visitors.
Version: 0.1
Author: Iggo
Author email: dev@fenzsoft.com
Text Domain: iggogrid
Domain Path: /i18n/
License: GPL 2
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

// Define certain plugin variables as constants
define( 'IGGOGRID_ABSPATH', plugin_dir_path( __FILE__ ) );

define( 'IGGOGRID__FILE__', __FILE__ );
define( 'IGGOGRID_BASENAME', plugin_basename( IGGOGRID__FILE__ ) );

//api
define( 'WSDL', 'http://varaamo.saariselka.com/intres/shops/Saariselka/WcfData/WinresDataService.svc?wsdl' );
define( 'APIKEY', '33d45fd1-aede-9271-e166-ab32778e0134' );
// // Load IggoGrid class, which holds common functions and variables
require_once IGGOGRID_ABSPATH . 'classes/class-iggogrid.php';

/*
//register activation function
register_activation_hook(__FILE__, array('IggoGrid', 'iggo_plugin_activate'));
//register deactivation function
register_deactivation_hook(__FILE__, array('IggoGrid', 'iggo_plugin_deactivate'));
//add rewrite rules in case another plugin flushes rules
add_action('init', array('IggoGrid', 'iggo_plugin_rules'));
//add plugin query vars (product_id) to wordpress
add_filter('query_vars',  array('IggoGrid', 'iggo_plugin_query_vars'));
//register plugin custom pages display
add_filter('template_redirect',  array('IggoGrid', 'iggo_plugin_display'));
*/

// // Start up IggoGrid on WordPress's "init" hook
add_action( 'init', array( 'IggoGrid', 'run') );
register_activation_hook( __file__, array('IggoGrid', 'activate') );

// Using a filter instead of an action to create the rewrite rules.
// Write rules -> Add query vars -> Recalculate rewrite rules

add_filter('rewrite_rules_array', array('IggoGrid', 'create_rewrite_rules'));

add_filter('query_vars',array('IggoGrid', 'add_query_vars'));
add_action( 'wp_loaded', array('IggoGrid', 'flush_rewrite_rules'));
// Recalculates rewrite rules during admin init to save resourcees.
// Could probably run it once as long as it isn't going to change or check the
// $wp_rewrite rules to see if it's active.
//add_filter('admin_init', array('IggoGrid', 'flush_rewrite_rules'));
add_action( 'template_redirect', array('IggoGrid', 'template_redirect_intercept') );


//On plugin activation schedule our daily api soap
register_activation_hook( __FILE__, 'iggo_create_daily_soap_schedule' );
function iggo_create_daily_soap_schedule(){
	//Use wp_next_scheduled to check if the event is already scheduled
	$timestamp = wp_next_scheduled( 'iggo_create_daily_soap' );

	//If $timestamp == false schedule daily soaps since it hasn't been done previously
	if( $timestamp == false ){
		//Schedule the event for right now, then to repeat daily using the hook 'iggo_create_daily_soap'
		wp_schedule_event( time(), 'daily', 'iggo_create_daily_soap' );
	}
}
//Hook our function , iggo_create_soap(), into the action iggo_create_daily_soap
add_action( 'iggo_create_daily_soap', 'iggo_create_soap' );
function iggo_create_soap(){
	require_once ABSPATH . 'iggo-soap.php';
	//error_log("zouhongzhao", 3, "/home/wordpress/temp/zou.log");
	//Run code to create soap.
}

//menu items
add_action('admin_menu','iggo_items_modifymenu');
function iggo_items_modifymenu() {
	//this is the main item for the menu
	add_menu_page('Items Url Rewrites', //page title
	'URL Rewrite', //menu title
	'manage_options', //capabilities
	'iggo_items_list', //menu slug
	iggo_items_list 
	);
// 	//item rewrites
// 	add_submenu_page('iggo_url_rewrite', //parent slug
// 	'Item Url Rewrites', //page title
// 	'Item Url Rewrites', //menu title
// 	'manage_options', //capability
// 	'iggo_items_list', //menu slug
// 	iggo_items_list //function
// 	); //function.
	
	//this submenu is HIDDEN, however, we need to add it anyways
	add_submenu_page(null, //parent slug
	'Update Item', //page title
	'Update', //menu title
	'manage_options', //capability
	'iggo_items_update', //menu slug 
	'iggo_items_update'); //function
	
	add_submenu_page(null, //parent slug
	'Delete Item', //page title
	'Delete', //menu title
	'manage_options', //capability
	'iggo_items_delete', //menu slug
	'iggo_items_delete'); //function
	
	
	//custom rewrites
	
	//this is a submenu
	add_submenu_page('iggo_items_list', //parent slug
		'Custom Url Rewrites', //page title
		'Custom Url Rewrites', //menu title
		'manage_options', //capability
		'iggo_custom_rewrite_list', //menu slug
		'iggo_custom_rewrite_list'); //function
	
	add_submenu_page('iggo_custom_rewrite_list', //parent slug
		'Add New Url', //page title
		'Add New', //menu title
		'manage_options', //capability
		'iggo_custom_rewrite_create', //menu slug
		'iggo_custom_rewrite_create'); //function
	
	//this submenu is HIDDEN, however, we need to add it anyways
	add_submenu_page('iggo_custom_rewrite_list', //parent slug
	'Update Url', //page title
	'Update', //menu title
	'manage_options', //capability
	'iggo_custom_rewrite_update', //menu slug
	'iggo_custom_rewrite_update'); //function
	
	add_submenu_page('iggo_custom_rewrite_list', //parent slug
	'Delete Url', //page title
	'Delete', //menu title
	'manage_options', //capability
	'iggo_custom_rewrite_delete', //menu slug
	'iggo_custom_rewrite_delete'); //function
	
	
	add_menu_page('Item List', //page title
	'Item List', //menu title
	'manage_options', //capabilities
	'iggo_items_list_update', //menu slug
	iggo_items_list_update
	);
}

require_once(IGGOGRID_ABSPATH . 'itemRewrites/items-list.php');
require_once(IGGOGRID_ABSPATH . 'itemRewrites/items-create.php');
require_once(IGGOGRID_ABSPATH . 'itemRewrites/items-update.php');
require_once(IGGOGRID_ABSPATH . 'customRewrites/url-list.php');
require_once(IGGOGRID_ABSPATH . 'customRewrites/url-create.php');
require_once(IGGOGRID_ABSPATH . 'customRewrites/url-update.php');
require_once(IGGOGRID_ABSPATH . 'itemApiUpdate/items-list.php');
// $langId = IggoGrid::getLanguageId();
// echo $langId;



// echo $lang['Majoitus'];die;

function add_last_nav_item($menu) {
	$langId = IggoGrid::getLanguageId();
	$langShortCode = substr(get_bloginfo ( 'language' ), 0, 2);
	$childPagesResult = IggoGrid::getMenuChildPages($langId);
// 	print_r($childPagesResult);die;
	global $wpdb;
// 	$langId =  IggoGrid::getLanguageId();
// 	echo $langId;die;
	$where = "where type = 'area' and text != '' and lang_id={$langId}";
	$sql = "SELECT text FROM iggogrid_translation {$where}";
	$areaResult = array();
	
	foreach( $wpdb->get_results($sql) as $key => $row) {
		$row = (array)$row;
		//$row['text'] = IggoGrid::replaceUrlStr($row['text']);
		if(empty($row['text'])){
			continue;
		}
		$areaResult[] = $row['text'];
	}
	$itemTypes = IggoGrid::getTypesGroup($langId);
	//get area count
	$areaCountArray = array();
	foreach ($itemTypes as $values){
		$areaCountArray = array_merge($areaCountArray,array_values($values));
	}
	$areaCountSql = "SELECT DISTINCT area,type,price FROM iggogrid_item_info where type in ('".implode("','", $areaCountArray)."')";
	$areaCountResult = array();
	foreach( $wpdb->get_results($areaCountSql) as $key => $row) {
		$row = (array)$row;
		$tmpArea = mb_strtolower($row['area']);
		$tmpType = mb_strtolower($row['type']);
		//$row['text'] = IggoGrid::replaceUrlStr($row['text']);
		$areaCountResult[$tmpType][$tmpArea] += $row['price'];
	}
// 	print_r($areaCountResult);die;
	$types = $itemTypes;
	if(!isset($types['restaurant']) || is_null($types['restaurant'])){
		$types['restaurant'] = array();
	}
	//if(!isset($types['activity']) || is_null($types['activity'])){
		$types['activity'] = array();
	//}
	if(!isset($types['accommodation']) || is_null($types['accommodation'])){
		$types['accommodation'] = array();
	}
	$types['restaurant'] = array_unique(array_merge($types['restaurant'] , array_keys($childPagesResult['restaurant'])));
	$types['activity'] = array_unique(array_merge($types['activity'] , array_keys($childPagesResult['activity'])));
	$types['accommodation'] = array_unique(array_merge($types['accommodation'] , array_keys($childPagesResult['accommodation'])));
	
	if($langId == 3){//en
		$allowTypes = array(
				'accommodation'=>'accommodation',
				'activity'=>'activities',
				'restaurant'=>'restaurant'
		);
	  $denyAccommodationData = array();
	  $denyActivityData = array();
	  $denyRestaurantData = array();
	  $pagesThirdData = array(
		  										'accommodation'=>array(),
										  		'activity'=>array('Activity and event calendar'),
										  		'restaurant'=>array(),
	  										);
	  $orderData = array(
		  										'accommodation'=>array('Cottage','Rowhouse','Hotels','Wilderness cabins'),
										  		'activity'=>array('Activity and event calendar'),
										  		'restaurant'=>array(),
	  							);
	
		$urlFlag = '/en';
	}elseif($langId == 1){//fi
		$allowTypes = array(
				'accommodation'=>'majoitus',
				'activity'=>'aktiviteetit',
				'restaurant'=>'ruokailu'
		);
		$denyAccommodationData = array();
		$denyActivityData = array();
		$denyRestaurantData = array();
		$pagesThirdData = array(
		  										'accommodation'=>array(),
										  		'activity'=>array('Ohjelmakalenteri'),
										  		'restaurant'=>array(),
	  										);
		$orderData = array(
				'accommodation'=>array('Mökki','Rivitalo','Hotellit','Eräkohteet'),
				'activity'=>array('Ohjelmakalenteri'),
				'restaurant'=>array(),
		);
		$urlFlag = '';
	}elseif ($langId == 5){//ru
		$allowTypes = array(
				'accommodation'=>'Pазмещение',
				'activity'=>'Чем заняться',
				'restaurant'=>'Питание'
		);
		$langTypes = array(
				'accommodation'=>'accommodation',
				'activity'=>'activity',
				'restaurant'=>'restaurant'
		);
	  $denyAccommodationData = array();
	  $denyActivityData = array();
	  $denyRestaurantData = array('Restaurants');
	  $pagesThirdData = array(
		  										'accommodation'=>array(),
										  		'activity'=>array('Календарь сафари и экскурсий'),
										  		'restaurant'=>array('Pестораны'),
	  										);
	  $orderData = array(
		  										'accommodation'=>array('Коттедж','Таунхаус','Отель','Wilderness cabins'),
										  		'activity'=>array('Календарь сафари и экскурсий'),
										  		'restaurant'=>array('Pестораны'),
	  							);
		$urlFlag = '/ru';
	}
	$newMenuData = IggoGrid::sortArray($types,array_keys($allowTypes));
	//delete custom data
	if(!empty($denyAccommodationData)){
		$newMenuData['accommodation'] = array_diff($newMenuData['accommodation'],$denyAccommodationData);
	}
	if(!empty($denyActivityData)){
		$newMenuData['activity'] = array_diff($newMenuData['activity'],$denyActivityData);
	}
	if(!empty($denyRestaurantData)){
		$newMenuData['restaurant'] = array_diff($newMenuData['restaurant'],$denyRestaurantData);
	}
	
	//merge data
	foreach ($newMenuData as $key=>$items){
		foreach ($items as $ikey=>$item){
			// 		var_dump($types[$key][$ikey][$item]) ;die;
			$flag = false;
			if (isset($itemTypes[$key]) && in_array($item,$itemTypes[$key])){
				$flag = true;
			}
			if (isset($pagesThirdData[$key]) && in_array($item,$pagesThirdData[$key])){
				$flag = true;
			}
			if($flag){
				$newMenuData[$key][$item] = $areaResult;
// 				unset($newMenuData[$key][$ikey]);
			}else{
				if(array_key_exists($item, $childPagesResult[$key]) && is_array($childPagesResult[$key][$item])){
// 					echo $item;
// 					print_r(array_keys($childPagesResult[$key][$item]));die;
					if(isset($childPagesResult[$key][$item]['childrens'])){
						$newMenuData[$key][$item] = array_keys($childPagesResult[$key][$item]['childrens']);
					}else{
						$newMenuData[$key][$item] = array();
					}
				}else{
					$newMenuData[$key][$item] = array();
				}
			}
			unset($newMenuData[$key][$ikey]);
		} 
	}
	
	//order data
	foreach ($orderData as $key=>$data){
		$newMenuData[$key] = IggoGrid::sortArray($newMenuData[$key],$data);
	}
	
// 	die;
// 	print_r($childPagesResult);
// 	print_r($newMenuData);die;
//create html
	$sortNumber = 1000;
	$footHiddenNumber = array();
	$indexFlag = '';
// 	var_dump(get_permalink(1110));die;
//     print_r($newMenuData);die;
	foreach ($newMenuData as $key=>$items){
		$newKey = $allowTypes[$key];
		if($langId == 5){
			$newKey = $langTypes[$key];
		}
		$sortNumber  ++;
		array_push($footHiddenNumber, $sortNumber);
		$rootUrl =  site_url().$urlFlag.'/'.$newKey;
		$html .= '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-'.$sortNumber.'" id="menu-item-'.$sortNumber.'">
				<a href="'. $rootUrl.'">'.ucfirst($allowTypes[$key]).'</a><ul class="sub-menu">';
		foreach ($items as  $key2=>$rows){
			
			$showData = array();
			$sortNumber ++;
			if(isset($childPagesResult[$key]) && isset($childPagesResult[$key][$key2])){
// 				print_r($childPagesResult[$key][$key2]);
				$rootUrl2 = get_permalink($childPagesResult[$key][$key2]['id']);
// 				$rootUrl2 = $childPagesResult[$key][$key2]['url'];
			}else{
				$rootUrl2 = trim($rootUrl,'/') . '/'.IggoGrid::replaceUrlStr(IggoGrid::__lang($key2));
			}
			
			$html .= '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-'.$sortNumber.'" id="menu-item-'.$sortNumber.'">
					<a href="'.$rootUrl2.'" title="'.$key2.'">'.$key2.'</a><ul class="sub-menu">';
			foreach ((array)$rows as $row){
				$tmpArea = mb_strtolower($row);
				$tmpType = mb_strtolower($key2);
				if(isset($areaCountResult[$tmpType]) && !isset($areaCountResult[$tmpType][$tmpArea])){
					continue;
				}
				if(($key == 'accommodation' || $key == 'activity') && isset($areaCountResult[$tmpType][$tmpArea]) && $areaCountResult[$tmpType][$tmpArea] == 0){
					continue;
				}
				$sortNumber ++;
				if(isset($childPagesResult[$key]) && isset($childPagesResult[$key][$key2]) && isset($childPagesResult[$key][$key2]['childrens']) && isset($childPagesResult[$key][$key2]['childrens'][$row])){
// 					$rootUrl3 = $childPagesResult[$key][$key2]['childrens'][$row]['url'];
					$rootUrl3 = get_permalink($childPagesResult[$key][$key2]['childrens'][$row]['id']);
				}else{
					$rootUrl3 = trim($rootUrl2,'/') . '/'.IggoGrid::replaceUrlStr(IggoGrid::__lang($row));
				}
				
// 				$wordStr = str_word_count($row,1);
// 				$wordStr = preg_replace("/[\s\x-\x#\[\]\{\}\'\"=+\-\(\)!*&%~^,.<|>\?$@]+/i","",$wordStr[0]);
// 				if(empty($wordStr)){
// 					continue;
// 				}
				$html .= '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-'.$sortNumber.'" id="menu-item-'.$sortNumber.'">
						<a href="'.$rootUrl3.'" title="'.$row.'">'.$row.'</a></li>';
			}
			/*
			$indexFlag = $key.'-'.$rows;
			foreach ($outputData as $key3=>$data){
				if(strpos($key3,$indexFlag) !== false){
					$showData[] = $data;
					unset($outputData[$key3]);
				}
			}
			foreach ($showData as $row){
				$sortNumber ++;
				$rootUrl3 = $rootUrl2 . '/'.IggoGrid::replaceUrlStr($row);
				$wordStr = str_word_count($row,1);
				$wordStr = preg_replace("/[\s\x-\x#\[\]\{\}\'\"=+\-\(\)!*&%~^,.<|>\?$@]+/i","",$wordStr[0]);
				if(empty($wordStr)){
					continue;
				}
				$html .= '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-'.$sortNumber.'" id="menu-item-'.$sortNumber.'">
						<a href="'.$rootUrl3.'" title="'.$wordStr.'">'.$wordStr.'</a></li>';
			}
			unset($showData);
			*/
			$html .= '</ul></li>';
		}
		$html .= '</ul></li>';
	}
//     print_r($footHiddenNumber);
    $footHiddenNumber = implode(',', $footHiddenNumber);
    $jsFootLang = '';
    if($langId != 1){
    	$jsFootLang = '-'.substr(get_bloginfo ( 'language' ), 0, 2);
    }
    $js = '<script type="text/javascript">
					$(document).ready(function(){
					    		var hiddenMenus = "'.$footHiddenNumber.'";
					    	   hiddenMenus =  hiddenMenus.split(",");
					    	  for(var key in hiddenMenus)
					          {
					                   $("#menu-footer-bluemenu'.$jsFootLang.' #menu-item-"+ hiddenMenus[key]).remove();
					          }
							$("#menu-item-26").hide();
							$("#menu-item-24").hide();
							$("#menu-item-25").hide();
					})
			</script>';
    $html .= $js;
    
//     $menuDoc = new DomDocument();
//     $meta = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
    
//     if($menuDoc->loadHTML($meta.$menu)){
//     	$searchNodes = $menuDoc->getElementsByTagName( "li" );
    	
//     	$valueID = '';
//     	$domElemsToRemove = array();
//     	foreach ($searchNodes as $searchNode){
//     		$valueID = $searchNode->getAttribute('id');
//     		if($valueID == 'menu-item-24' || $valueID == 'menu-item-25' || $valueID == 'menu-item-26'){
//     			$domElemsToRemove[] = $searchNode;
//     		}
//     	}
    	
//         foreach( $domElemsToRemove as $domElement ){
// 		    $domElement->parentNode->removeChild($domElement);
// 		}
//     }
    
//     $menu = $menuDoc->saveHTML();
    
//     echo $menu;die;
    return $html.$menu;
}
add_filter('wp_nav_menu_items','add_last_nav_item'); 

require_once IGGOGRID_ABSPATH . 'classes/class-admin-page-helper.php';
//ajax
define('APFSURL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
define('APFPATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );
function apf_enqueuescripts()
{
	wp_enqueue_script('apf', APFSURL.'/js/ajaxpostfromfront/apf.js', array('jquery'));
	wp_localize_script( 'apf', 'apfajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action('wp_enqueue_scripts', apf_enqueuescripts);
// creating Ajax call for WordPress
add_action( 'wp_ajax_nopriv_apf_weekselector',array('IggoGrid', 'apf_weekselector') );
add_action( 'wp_ajax_apf_weekselector',array('IggoGrid', 'apf_weekselector') );
add_action( 'wp_ajax_apf_weekselector',array('IggoGrid', 'apf_weekselector') );
// add_action( 'wp_ajax_nopriv_apf_itemApiUpdate',array('IggoGrid', 'apf_itemApiUpdate') );
add_action( 'wp_ajax_apf_itemApiUpdate',array('IggoGrid_Admin_Page', 'apf_itemApiUpdate') );
add_action( 'wp_ajax_apf_fullUpdateItems',array('IggoGrid_Admin_Page', 'apf_fullUpdateItems') );