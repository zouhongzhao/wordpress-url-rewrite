<?php
/**
 *IggoPartner Class
 *
 * @package IggoPartner
 * @author Iggo
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * IggoPartner class
 * @package IggoPartner
 * @author Iggo
 * @since 1.0.0
 */
abstract class IggoPartner {
    public static $_convertTable = array(
        '&amp;' => 'and',   '@' => 'at',    '©' => 'c', '®' => 'r', 'À' => 'a',
        'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae','Ç' => 'c',
        'È' => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i',
        'Ï' => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'Ø' => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
        'ß' => 'ss','à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ' => 'ae','ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
        'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'p', 'ÿ' => 'y', 'Ā' => 'a',
        'ā' => 'a', 'Ă' => 'a', 'ă' => 'a', 'Ą' => 'a', 'ą' => 'a', 'Ć' => 'c',
        'ć' => 'c', 'Ĉ' => 'c', 'ĉ' => 'c', 'Ċ' => 'c', 'ċ' => 'c', 'Č' => 'c',
        'č' => 'c', 'Ď' => 'd', 'ď' => 'd', 'Đ' => 'd', 'đ' => 'd', 'Ē' => 'e',
        'ē' => 'e', 'Ĕ' => 'e', 'ĕ' => 'e', 'Ė' => 'e', 'ė' => 'e', 'Ę' => 'e',
        'ę' => 'e', 'Ě' => 'e', 'ě' => 'e', 'Ĝ' => 'g', 'ĝ' => 'g', 'Ğ' => 'g',
        'ğ' => 'g', 'Ġ' => 'g', 'ġ' => 'g', 'Ģ' => 'g', 'ģ' => 'g', 'Ĥ' => 'h',
        'ĥ' => 'h', 'Ħ' => 'h', 'ħ' => 'h', 'Ĩ' => 'i', 'ĩ' => 'i', 'Ī' => 'i',
        'ī' => 'i', 'Ĭ' => 'i', 'ĭ' => 'i', 'Į' => 'i', 'į' => 'i', 'İ' => 'i',
        'ı' => 'i', 'Ĳ' => 'ij','ĳ' => 'ij','Ĵ' => 'j', 'ĵ' => 'j', 'Ķ' => 'k',
        'ķ' => 'k', 'ĸ' => 'k', 'Ĺ' => 'l', 'ĺ' => 'l', 'Ļ' => 'l', 'ļ' => 'l',
        'Ľ' => 'l', 'ľ' => 'l', 'Ŀ' => 'l', 'ŀ' => 'l', 'Ł' => 'l', 'ł' => 'l',
        'Ń' => 'n', 'ń' => 'n', 'Ņ' => 'n', 'ņ' => 'n', 'Ň' => 'n', 'ň' => 'n',
        'ŉ' => 'n', 'Ŋ' => 'n', 'ŋ' => 'n', 'Ō' => 'o', 'ō' => 'o', 'Ŏ' => 'o',
        'ŏ' => 'o', 'Ő' => 'o', 'ő' => 'o', 'Œ' => 'oe','œ' => 'oe','Ŕ' => 'r',
        'ŕ' => 'r', 'Ŗ' => 'r', 'ŗ' => 'r', 'Ř' => 'r', 'ř' => 'r', 'Ś' => 's',
        'ś' => 's', 'Ŝ' => 's', 'ŝ' => 's', 'Ş' => 's', 'ş' => 's', 'Š' => 's',
        'š' => 's', 'Ţ' => 't', 'ţ' => 't', 'Ť' => 't', 'ť' => 't', 'Ŧ' => 't',
        'ŧ' => 't', 'Ũ' => 'u', 'ũ' => 'u', 'Ū' => 'u', 'ū' => 'u', 'Ŭ' => 'u',
        'ŭ' => 'u', 'Ů' => 'u', 'ů' => 'u', 'Ű' => 'u', 'ű' => 'u', 'Ų' => 'u',
        'ų' => 'u', 'Ŵ' => 'w', 'ŵ' => 'w', 'Ŷ' => 'y', 'ŷ' => 'y', 'Ÿ' => 'y',
        'Ź' => 'z', 'ź' => 'z', 'Ż' => 'z', 'ż' => 'z', 'Ž' => 'z', 'ž' => 'z',
        'ſ' => 'z', 'Ə' => 'e', 'ƒ' => 'f', 'Ơ' => 'o', 'ơ' => 'o', 'Ư' => 'u',
        'ư' => 'u', 'Ǎ' => 'a', 'ǎ' => 'a', 'Ǐ' => 'i', 'ǐ' => 'i', 'Ǒ' => 'o',
        'ǒ' => 'o', 'Ǔ' => 'u', 'ǔ' => 'u', 'Ǖ' => 'u', 'ǖ' => 'u', 'Ǘ' => 'u',
        'ǘ' => 'u', 'Ǚ' => 'u', 'ǚ' => 'u', 'Ǜ' => 'u', 'ǜ' => 'u', 'Ǻ' => 'a',
        'ǻ' => 'a', 'Ǽ' => 'ae','ǽ' => 'ae','Ǿ' => 'o', 'ǿ' => 'o', 'ə' => 'e',
        'Ё' => 'jo','Є' => 'e', 'І' => 'i', 'Ї' => 'i', 'А' => 'a', 'Б' => 'b',
        'В' => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ж' => 'zh','З' => 'z',
        'И' => 'i', 'Й' => 'j', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
        'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u',
        'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch','Ш' => 'sh','Щ' => 'sch',
        'Ъ' => '-', 'Ы' => 'y', 'Ь' => '-', 'Э' => 'je','Ю' => 'ju','Я' => 'ja',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ж' => 'zh','з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l',
        'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
        'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh','щ' => 'sch','ъ' => '-','ы' => 'y', 'ь' => '-', 'э' => 'je',
        'ю' => 'ju','я' => 'ja','ё' => 'jo','є' => 'e', 'і' => 'i', 'ї' => 'i',
        'Ґ' => 'g', 'ґ' => 'g', 'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd',
        'ה' => 'h', 'ו' => 'v', 'ז' => 'z', 'ח' => 'h', 'ט' => 't', 'י' => 'i',
        'ך' => 'k', 'כ' => 'k', 'ל' => 'l', 'ם' => 'm', 'מ' => 'm', 'ן' => 'n',
        'נ' => 'n', 'ס' => 's', 'ע' => 'e', 'ף' => 'p', 'פ' => 'p', 'ץ' => 'C',
        'צ' => 'c', 'ק' => 'q', 'ר' => 'r', 'ש' => 'w', 'ת' => 't', '™' => 'tm',
    );
    
    function activate() {
        global $wp_rewrite;
        self::flush_rewrite_rules();
    }
    
    // Took out the $wp_rewrite->rules replacement so the rewrite rules filter could handle this.
    function create_rewrite_rules($rules) {
        //return $rules;
        global $wp_rewrite;
        $firstRules = array(
            'jasenyritykset$'=>'index.php?single_page=jasenyritykset',
            'jasenyritykset/(\d{5})$'=>'index.php?single_page=jasenyritykset&postcode='.$wp_rewrite->preg_index(1),
            'jasenyritykset/(\d{1,3}-[a-zA-Z]{1,2})$'=>'index.php?single_page=jasenyritykset&city_index='.$wp_rewrite->preg_index(1),
            'jasenyritykset/(\d{1,3}-[a-zA-Z]{1,2})/([^/]+)$'=>'index.php?single_page=jasenyritykset&city_index='.$wp_rewrite->preg_index(1).'&city='.$wp_rewrite->preg_index(2),
            'jasenyritykset/(\d{1,3}-[a-zA-Z]{1,2})/([^/]+)/(\d{1,3}-[^/]+)$'=>'index.php?single_page=jasenyritykset&city_index='.$wp_rewrite->preg_index(1).'&city='.$wp_rewrite->preg_index(2).'&marketing='.$wp_rewrite->preg_index(3),
        );
        $newRules = $firstRules + $rules;
//         echo "<pre>";
//         print_r($rules);
//         echo "</pre>";
        return $newRules;
    }
    
    function add_query_vars($qvars) {
        $qvars[] = 'postcode';
        $qvars[] = 'single_page';
        $qvars[] = 'city_index';
        $qvars[] = 'city';
        $qvars[] = 'marketing';
        return $qvars;
    }
    
    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
    
    function template_redirect_intercept() {
        global $wp_query;
        $cityIndex = $wp_query->get('city_index');
        $city = $wp_query->get('city');
        $marketing = $wp_query->get('marketing');
        $single_page = $wp_query->get('single_page');
        $postcode = $wp_query->get('postcode');
//         var_dump($cityIndex);
//         var_dump($city);
//         var_dump($marketing);
//         var_dump($postcode);
        if($single_page == 'jasenyritykset'){
            //var_dump($single_page);
            $currentPage = '';
            if($postcode && !$cityIndex && !$city && !$marketing){
                $currentPage = 'postcode';
            }
            if(!$postcode && !$cityIndex && !$city && !$marketing){
                $currentPage = 'index';
            }
            if(!$postcode && $cityIndex && !$city && !$marketing){
                $currentPage = 'city_index';
            }
            if(!$postcode && $cityIndex && $city && !$marketing){
                $currentPage = 'city';
            }
            if(!$postcode && $cityIndex && $city && $marketing){
                $currentPage = 'marketing';
            }
            global $wpdb;
            $result = array();
            $tableName = $wpdb->prefix . 'crm_accounts';
            //         $where = "where url_key = '{$urlKey}' limit 1";
            //         $dbResult = array();
            //         $sql = "SELECT info.*,item.admin_added FROM iggogrid_item_info as info left join iggogrid_item as item on item.id = info.item_id {$where}";
            //         $row = $wpdb->get_row($sql,ARRAY_A);
            //foreach( $wpdb->get_results($sql) as $key => $row) {};
            //city为municipality
            switch ($currentPage) {
                case 'postcode':
                    if(!preg_match("/(\d{5})/",$postcode)){
                        return;
                    }
                    $result['postcode'] = $postcode;
                    $result['items'] = array();
                    $templateName = 'page-jasenyritykset-postcode.php';
                    $sql = "select * from {$tableName} where postcode = '{$postcode}'";
                    $result['items'] = $wpdb->get_results($sql,ARRAY_A);
                break;
                case 'index':
                    $templateName = 'page-jasenyritykset.php';
                    $sql = "select * from {$tableName} order by municipality asc";
                    $municipalityData = array();
                    foreach( $wpdb->get_results($sql) as $key => $row) {
                        $row = (array)$row;
                        if(in_array($row['municipality'], $municipalityData)){
                            continue;
                        }
                        array_push($municipalityData, $row['municipality']);
                        $firstLetter = mb_substr($row['municipality'], 0, 1);
                        $firstLetter = mb_strtoupper($firstLetter);
                        $result[$firstLetter][$row['id']] = $row;
                    };
                    ksort($result);
                    foreach ($result as $key=>$items){
                        $result[$key] = self::array_sort($items,'municipality','asc');
                    }
                break;
                case 'city_index':
                    $cityIndex = explode('-', $cityIndex);
                    $cityIndex[1] = self::flipFiLetterMap($cityIndex[1]);
                    $cityIndex[1] = mb_strtoupper($cityIndex[1]);
                    //var_dump($cityIndex);die;
                    $result['letter'] = $cityIndex[1];
                    $result['items'] = array();
                    $templateName = 'page-jasenyritykset-cityindex.php';
                    $sql = "select * from {$tableName} order by municipality asc";
                    $municipalityData = array();
                    foreach( $wpdb->get_results($sql) as $key => $row) {
                        $row = (array)$row;
                        $firstLetter = mb_substr($row['municipality'], 0, 1);
                        $firstLetter = mb_strtoupper($firstLetter);
                        if($firstLetter != $cityIndex[1]){
                            continue;
                        }
                        if(in_array($row['municipality'], $municipalityData)){
                            continue;
                        }
                        array_push($municipalityData, $row['municipality']);
                        $result['items'][$row['id']] = $row;
                    };
                    $result['items'] = self::array_sort($result['items'],'municipality','asc');
                break;
                case 'city':
                    $cityIndex = explode('-', $cityIndex);
                    $cityRow = $wpdb->get_row("select municipality from {$tableName} where id = {$cityIndex[0]}",ARRAY_A);
                    //var_dump($cityRow);
                    $result['index'] = $cityIndex[0];
                    $result['letter'] = $cityIndex[1];
                    $result['city'] = $cityRow['municipality'];
                    $result['items'] = array();
                    $sql = "select * from {$tableName} where municipality = '{$cityRow['municipality']}'";
                    $result['items'] = $wpdb->get_results($sql,ARRAY_A);
                    $templateName = 'page-jasenyritykset-city.php';
                break;
                case 'marketing':
                    $marketing = explode('-', $marketing);
                    $sql = "select * from {$tableName} where id = '{$marketing[0]}'";
                    $result = $wpdb->get_row($sql,ARRAY_A);
                    $templateName = 'page-jasenyritykset-market.php';
                break;
                default:
                    ;
                break;
            }
            $GLOBALS['iggoPartnerInfoData'] = $result;
//             echo "<pre>";
//             print_r($result);
//             echo "</pre>";
//             die;
            echo self::locate_plugin_template($templateName,true);
            exit();
        }
    }
    function flipFiLetterMap($letter=''){
        $map = array_flip(self::fiLetterMap());
        if($letter){
            return isset($map[$letter])?$map[$letter]:$letter;
        }
        return $map;
    }
    function fiLetterMap($letter=''){
        $map = array(
            'ä'=>'ae',
            'ö'=>'oe',
            'ü'=>'ue',
            'Ä'=>'AE',
            'Ö'=>'OE',
        );
        if($letter){
            return isset($map[$letter])?$map[$letter]:$letter;
        }
        return $map;
    }
    function addhttp($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }
    public function formatUrlKey($str)
    {
        $str = strtr($str, self::$_convertTable);
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-',$str);
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
        return $urlKey;
    }
    function array_sort($arr, $keys, $type = 'desc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
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
        
        $this_plugin_dir = WP_PLUGIN_DIR.'/'.str_replace( basename( __FILE__), "", plugin_basename(__FILE__) );
        $this_theme_dir = IGGOPARTNER_ABSPATH.'/themes/default/';
        // 		foreach ( $template_names as $template_name ) {
        // 			if ( !$template_name )
        // 				continue;
            			
                    // 		}
        if ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
            $located = STYLESHEETPATH . '/' . $template_name;
        } else if ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
            $located = TEMPLATEPATH . '/' . $template_name;
        } else if ( file_exists( $this_plugin_dir .  $template_name) ) {
            $located =  $this_plugin_dir . $template_name;
        }else if ( file_exists( $this_theme_dir . $template_name) ) {
            $located = $this_theme_dir . $template_name;
        }else{
            return '';
        }
        if ( $load && '' != $located ){
            load_template( $located, $require_once );
        }
        //return $located;
    }
        
    //function
    
    function getLanguageId(){
        $languages = array(
            'fi'=>1,
            'se'=>2,
            'en'=>3,
            'de'=>4,
            'ru'=>5,
            'zh-hans'=>6
        );
        // 		$current = substr(get_bloginfo ( 'language' ), 0, 2);
        $current = ICL_LANGUAGE_CODE;
        if(!isset($languages[$current])){
            $current = 'fi';
        }
        return $languages[$current];
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
    
    function get_ID_by_slug($page_slug) {
        $page = get_page_by_path($page_slug);
        if ($page) {
            return $page->ID;
        } else {
            return null;
        }
    }
    
    //ajax function
    /*
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
     */
    
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
    
    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}