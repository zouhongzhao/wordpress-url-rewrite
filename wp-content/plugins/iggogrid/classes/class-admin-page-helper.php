<?php
/**
 * Admin Page Helper Class for IggoGrid with functions needed in the admin area
 *
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Admin Page class
 * @package IggoGrid
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class IggoGrid_Admin_Page {

	/**
	 * Enqueue a CSS file
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Name of the CSS file, without extension(s)
	 * @param array $dependencies List of names of CSS stylesheets that this stylesheet depends on, and which need to be included before this one
	 */
	public function enqueue_style( $name, array $dependencies = array() ) {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$css_file = "admin/css/{$name}{$suffix}.css";
		$css_url = plugins_url( $css_file, IGGOGRID__FILE__ );
		wp_enqueue_style( "iggogrid-{$name}", $css_url, $dependencies, IggoGrid::version );
	}

	/**
	 * Enqueue a JavaScript file, possibility with dependencies and extra information
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Name of the JS file, without extension(s)
	 * @param array $dependencies List of names of JS scripts that this script depends on, and which need to be included before this one
	 * @param bool|array $localize_script (optional) An array with strings that gets transformed into a JS object and is added to the page before the script is included
	 * @param bool $force_minified Always load the minified version, regardless of SCRIPT_DEBUG constant value
	 */
	public function enqueue_script( $name, array $dependencies = array(), $localize_script = false, $force_minified = false ) {
// 		$suffix = ( ! $force_minified && defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
// 		var_dump(SCRIPT_DEBUG );die;
		$suffix = '';
		$js_file = "admin/js/{$name}{$suffix}.js";
		$js_url = plugins_url( $js_file, IGGOGRID__FILE__ );
		wp_enqueue_script( "iggogrid-{$name}", $js_url, $dependencies, IggoGrid::version, true );
		if ( ! empty( $localize_script ) ) {
			foreach ( $localize_script as $var_name => $var_data ) {
				wp_localize_script( "iggogrid-{$name}", "iggogrid_{$var_name}", $var_data );
			}
		}
	}

	/**
	 * Register a filter hook on the admin footer
	 *
	 * @since 1.0.0
	 */
	public function add_admin_footer_text() {
		// show admin footer message (only on pages of IggoGrid)
		add_filter( 'admin_footer_text', array( $this, '_admin_footer_text' ) );
	}

	/**
	 * Add a IggoGrid "Thank You" message to the admin footer content
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Current admin footer content
	 * @return string New admin footer content
	 */
	public function _admin_footer_text( $content ) {
		$content .= ' &bull; ' . __( 'Thank you for using <a href="http://iggogrid.org/">IggoGrid</a>.', 'iggogrid' );
		$content .= ' ' . sprintf( __( 'Support the plugin with your <a href="%s">donation</a>!', 'iggogrid' ), 'http://iggogrid.org/donate/' );
		return $content;
	}

	/**
	 * Print the JavaScript code for a WP feature pointer
	 *
	 * @since 1.0.0
	 *
	 * @param string $pointer_id The pointer ID.
	 * @param string $selector The HTML elements, on which the pointer should be attached.
	 * @param array $args Arguments to be passed to the pointer JS (see wp-pointer.js).
	 */
	public function print_wp_pointer_js( $pointer_id, $selector, array $args ) {
		if ( empty( $pointer_id ) || empty( $selector ) || empty( $args ) || empty( $args['content'] ) ) {
			return;
		}
		?>
		<script type="text/javascript">
		( function( $ ) {
			var options = <?php echo json_encode( $args ); ?>, setup;

			if ( ! options ) {
				return;
			}

			options = $.extend( options, {
				close: function() {
					$.post( ajaxurl, {
						pointer: '<?php echo $pointer_id; ?>',
						action: 'dismiss-wp-pointer'
					} );
				}
			} );

			setup = function() {
				$( '<?php echo $selector; ?>' ).pointer( options ).pointer( 'open' );
			};

			if ( options.position && options.position.defer_loading ) {
				$( window ).bind( 'load.wp-pointers', setup );
			} else {
				$( document ).ready( setup );
			}
		} )( jQuery );
		</script>
		<?php
	}
	
	//update item from api ajax
	function apf_itemApiUpdate(){
		$itemId = $_POST['id'];
		$type =  $_POST['type'];
		global $wpdb;
		$sql = $wpdb->prepare("SELECT item_id,product_code, destination_code,type_code from iggogrid_item_info where item_id=$itemId limit 1",$itemId);
		$itemRow = $wpdb->get_row($sql,ARRAY_A);
		if(empty($itemRow)){
			echo "Not found this item!";
			exit();
		}
		$destinationCode = $itemRow["destination_code"];
		$productCode = $itemRow["product_code"];
		
		// $conn = mysql_connect("localhost", "demoiggo_saarise", "GqW33J#ma}s0");
		// mysql_set_charset('utf8',$conn);
		// mysql_select_db("demoiggo_saariselka", $conn);
		$enTmpData = array();
		$langArray = array(
				3=>array(
						'short'=>'en',
						'long'=>'English'),
				1=>array(
						'short'=>'fi',
						'long'=>'Finnish'),
				2=>array(
						'short'=>'se',
						'long'=>'Swedish'),
		
				4=>array(
						'short'=>'de',
						'long'=>'German'),
				5=>array(
						'short'=>'ru',
						'long'=>'Russian'),
		);
		$client = new SoapClient(WSDL);
		
		
		foreach ($langArray as $langIndex=>$lang){
			$info = array();
			$info['person'] = '';
			$info['rooms'] = '';
			$info['url_key'] = '';
			$info['season'] = '';
			$info['offer'] = '';
			$info['price'] = '';
// 			$info['old_url'] = '';
			if($type == 'activity'){
				$beginDate = date('Y-m-d',strtotime("+1 day"));
				$priceKey = 'price_'.strtolower($lang['short']);
				// 		echo $beginDate;
				// 		$price = $GLOBALS['client']->GetExtProductPrice(array('destinationCode' => 'talrere', 'productCode' => 'rere', 'beginDate' => '2015-01-13', 'authorizationKey' => $GLOBALS['key'], 'culture' =>$langArr["long"]));
				// 		print_r($price);die;
		
				$countDateNum = 1;
				do{
					$price = $client->GetExtProductPrice(array('destinationCode' => $destinationCode, 'productCode' =>$productCode, 'beginDate' => $beginDate, 'authorizationKey' => APIKEY, 'culture' =>$lang["long"]));
					$countDateNum++;
					if($countDateNum >= 365){
						break;
					}
					$beginDate = date('Y-m-d',strtotime("+1 day",strtotime($beginDate)));
				}while(!isset($price->GetExtProductPriceResult) || !isset($price->GetExtProductPriceResult->PriceData));
		
				$priceData = array();
				if(isset($price->GetExtProductPriceResult) && isset($price->GetExtProductPriceResult->PriceData)){
						
					foreach ($price->GetExtProductPriceResult->PriceData as $item){
						if(empty($item)){
							continue;
						}
						$priceData[$item->Index] = array('header'=>$item->Header,'price'=>$item->Price);
					}
				}
		
				if(!empty($priceData)){
					$info[$priceKey] = $priceData;//serialize($priceData);
					$firstKeyData = current($priceData);
					$info['price'] = $firstKeyData['price'];
					// 			print_r($info[$priceKey]);
				}
				$activityAvailability = $client->GetCapacityDateRangeList(array('code' => $destinationCode, 'product' => $productCode, 'beginDate' => date('Y-m-d'), 'endDate' =>  date("Y-m-d",strtotime("+1 year")), 'authorizationKey' => APIKEY));
		
				foreach ($activityAvailability->GetCapacityDateRangeListResult as $rows){
					if(empty($rows)){
						continue;
					}
					foreach ($rows as $row){
						if(empty($row)){
							continue;
						}
						$day = date('Y-m-d',strtotime($row->Day));
						
						$sqlActivityDetect = "SELECT id FROM iggogrid_activity_availability WHERE item_id='{$itemId}' AND DATE(day) = DATE('{$day}');";
						$rowItem  = $wpdb->get_row($sqlActivityDetect,ARRAY_A);
// 						print_r($rowItem);die;
						$day .= ' 00:00:00';
						// 				$queryActivityDetect = mysql_query($sqlActivityDetect, $conn);
						if(empty($rowItem)){
								
							// 					if($row->Free != 0){
							// 					    echo 'day = '.$day.' itemid = '.$itemId.' free = '.$row->Free."\r\n";
							// 					}
								
							$sqlItemInsert = "INSERT INTO iggogrid_activity_availability (id,item_id,day,capacity,booked,free) VALUES ('','{$itemId}','{$day}','{$row->Capacity}','{$row->Booked}','{$row->Free}');";
							//echo $sqlItemInsert."\r\n";
							$wpdb->query($sqlItemInsert);
							//mysql_query($sqlItemInsert, $conn);
						}else{
							// 					$rowItem = mysql_fetch_assoc($queryActivityDetect);
							$activityId = $rowItem["id"];
							// 					var_dump($activityId);
							$sqlActivityUpdate = "UPDATE iggogrid_activity_availability SET capacity='{$row->Capacity}', booked='{$row->Booked}', booked='{$row->Free}' WHERE id='{$activityId}';";
							//echo $sqlActivityUpdate."\r\n";
							$wpdb->query($sqlActivityUpdate);
							//mysql_query($sqlActivityUpdate, $conn);
						}
					}
		
				}
			}else{
				$beginDate = date('Y-m-d',strtotime("+10 day"));
				$endDate =  date('Y-m-d',strtotime("+12 day"));
				//echo $beginDate."||".$endDate;
				$price = $client->GetProductPrice(array('code' =>$destinationCode, 'product' =>$productCode, 'beginDate' =>$beginDate, 'endDate' =>$endDate, 'authorizationKey' => APIKEY));
				$info['price'] =$price->GetProductPriceResult;
			}
		
		
			$eventInfo = $client->GetItem(array(
					'authorizationKey' => APIKEY,
					'destinationCode' => $destinationCode,
					'productCode' => $productCode,
					'culture' => $lang["long"]
			));
		
			$itemInfo = IggoGrid::ob2ar((array)$eventInfo->GetItemResult);
		
			if(isset($itemInfo['Beds']) && isset($itemInfo['ExtraBeds'])){
				$info['person'] = (int)$itemInfo['Beds'] + (int)$itemInfo['ExtraBeds'];
			}
			if($lang['short'] == 'fi'){
				$info['url_key'] =  $itemInfo['DestinationName'];
			}elseif($lang['short'] == 'en'){
				$info['url_key'] =  $itemInfo['DestinationName'].'-en';
			}else{
				$info['url_key'] =  $enTmpData[$itemId]['destination_name'].'-'.strtolower($lang['short']);
			}
		
			if( isset($itemInfo['AccProperties']) && isset($itemInfo['AccProperties']['PropertyCriteria']['NameValueItem']) ){
				$nameItemValue = $itemInfo['AccProperties']['PropertyCriteria']['NameValueItem'];
				foreach ($nameItemValue as $item){
					if($item['Name'] == 'Rooms'){
						$info['rooms'] = (int)$item['Value'];
						break;
					}
				}
				foreach ($nameItemValue as $item){
					if($item['Name'] == 'Season'){
						$info['season'] = (int)$item['Value'];
						break;
					}
				}
				foreach ($nameItemValue as $item){
					if($item['Name'] == 'Offer'){
						// 				print_r($nameValueItem);
						$info['offer'] = (int)$item['Value'];
						break;
					}
				}
		
			}
			// 	print_r($itemInfo);die;
			$info['acc_properties']='';
			if(!empty($itemInfo['AccProperties'])){
				$info['acc_properties'] = serialize($itemInfo['AccProperties']);
			}else{
				$info['acc_properties'] = '';
			}
		
			if(!empty($itemInfo['AccProperties'])){
				$info['appreciation_list'] = serialize($itemInfo['AppreciationList']);
			}else{
				$info['appreciation_list'] = '';
			}
			$images = $itemInfo['Images'];
			if(isset($images['string'])){
				$images = $images['string'];
			}
			$info['info'] = serialize($itemInfo);
			$info['area'] = $itemInfo['Area'];
			$info['area_code'] = $itemInfo['AreaCode'];
			$info['beds'] = $itemInfo['Beds'];
			$info['classification'] = $itemInfo['Classification'];
			$info['country'] = $itemInfo['Country'];
			$info['description'] = $itemInfo['Description'];
			$info['destination_name'] = $itemInfo['DestinationName'];
			$info['distance_criteria'] = $itemInfo['DistanceCriteria'];
			$info['extra_beds'] = $itemInfo['ExtraBeds'];
			$info['group'] = $itemInfo['Group'];
			$info['group_code'] = $itemInfo['GroupCode'];
			$info['headline'] = $itemInfo['Headline'];
			$info['home_page'] = $itemInfo['HomePage'];
			$info['images'] = serialize($images);
			$info['internet_visibility'] = $itemInfo['InternetVisibility'];
			$info['is_bookable'] = $itemInfo['IsBookable'];
			$info['latitude'] = $itemInfo['Latitude'];
			$info['longitude'] = $itemInfo['Longitude'];
			$info['postal_address'] = $itemInfo['PostalAddress'];
			$info['product_name'] = $itemInfo['ProductName'];
			$info['street_address'] = $itemInfo['StreetAddress'];
			$info['product_name'] = $itemInfo['ProductName'];
			$info['street_address'] = $itemInfo['StreetAddress'];
			$info['type'] = $itemInfo['Type'];
			$info['type_code'] = $itemInfo['TypeCode'];
		
		
		
			if(!empty($info['home_page'])){
				$parseUrl = parse_url($info['home_page']);
// 				$old_url = trim($parseUrl['path'],'/');
// 				$info['old_url'] = $old_url;
			}
			if($lang['short'] == 'en'){
				$enTmpData[$itemId] =  $info;
			}
			foreach ($info as $akey=>$value){
				if(is_array($value) || is_object($value)){
					$value = serialize(IggoGrid::ob2ar($value));
				}
				if(empty($value) && isset($enTmpData[$itemId])){
					$value = $enTmpData[$itemId][$akey];
				}
				if(isset($enTmpData[$itemId])){
					if($akey == 'headline' && $value == 'Headline'){
						$value = $enTmpData[$itemId][$akey];
					}
					if($akey == 'destination_name' && $value == 'Name'){
						$value = $enTmpData[$itemId][$akey];
					}
					if($akey == 'description' && $value == 'Description'){
						$value = $enTmpData[$itemId][$akey];
					}
				}
				if($akey == 'url_key'){
					$value = IggoGrid::replaceUrlStr($value);
				}
				$value = stripslashes_deep($value);
				$info[$akey] = $value;
			}
// 				echo $itemId."<br/>";
// 				echo $langIndex;
// 				print_r($info);
				try {
					$wpdb->update(
						'iggogrid_item_info', //table
						$info, //data
						array( 'item_id' => $itemId,'lang_id'=>$langIndex ) //where
					);
				} catch (Exception $e) {

				}
			// 	$wpdb->update(
			// 			'iggogrid_item_info', //table
			// 			array(
			// 				'acc_properties' => serialize($itemInfo['AccProperties']),
			// 				'appreciation_list' => serialize($itemInfo['AppreciationList']),
			// 				'application_summary' => $itemInfo['AppreciationSummary'],
			// 				'areas' => $itemInfo['Area'],
			// 				'beds' => $itemInfo['Beds'],
			// 				'classification' => $itemInfo['Classification'],
			// 				'country' => $itemInfo['Country'],
			// 				'description' => $itemInfo['Description'],
			// 				'destination_name' => $itemInfo['DestinationName'],
			// 				'distance_criteria' => $itemInfo['DistanceCriteria'],
			// 				'extra_beds' => $itemInfo['ExtraBeds'],
			// 				'group' => $itemInfo['Group'],
			// 				'group_code' => $itemInfo['GroupCode'],
			// 				'headline' => $itemInfo['Headline'],
			// 				'home_page' => $itemInfo['HomePage'],
			// 				'images' => serialize($itemInfo['Images']),
			// 				'internet_visibility' => $itemInfo['InternetVisibility'],
			// 				'is_bookable' => $itemInfo['IsBookable'],
			// 				'latitude' => $itemInfo['Latitude'],
			// 				'longitude' => $itemInfo['Longitude'],
			// 				'postal_address' => $itemInfo['PostalAddress'],
			// 				'product_name' => $itemInfo['ProductName'],
			// 				'street_address' => $itemInfo['StreetAddress'],
			// 				'type' => $itemInfo['Type'],
			// 				'type_code' => $itemInfo['TypeCode'],
			// 				'person' => $info['person'],
			// 	            'rooms' => $info['rooms'],
			// 	            'offer' => $info['offer'],
			// 	            'season' => $info['season'],
			// 	            'url_key' => $info['url_key'],
			// 	            'price' =>  $info['price']
			// 	            ), //data
			// 			array( 'item_id' => $itemId,'lang_id'=>$langIndex,'destination_code'=>$destinationCode,'$product_code'=>$productCode ), //where
			// 			array('%s'), //data format
			// 			array('%s') //where format
			// 	    );
		
		}
		echo "success";
		exit();
	}
	
	//update all items from api ajax
	function apf_fullUpdateItems(){
		$soapDir = ABSPATH . 'iggo-soap.php';;
		$logPath = ABSPATH . "soap.log";
		exec("php {$soapDir} > {$logPath} 2>/dev/null &");
		echo "Full update scheduled, it will take up to 1 hour to finish.";
		exit();
	}
} // class IggoGrid_Admin_Page
