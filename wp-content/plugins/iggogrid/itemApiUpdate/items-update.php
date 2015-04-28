<?php
function iggo_items_api_update () {
global $wpdb;
$id = $_GET["id"];
$lang_id = $_GET["lang_id"];
$langArray = array(
	1=>array('long'=>'FI'),
	2=>array('long'=>'SE'),
	3=>array('long'=>'EN'),
	4=>array('long'=>'DE'),
	5=>array('long'=>'RU'),
);
$wsdl = 'http://varaamo.saariselka.com/intres/shops/Saariselka/WcfData/WinresDataService.svc?wsdl';

$key= "33d45fd1-aede-9271-e166-ab32778e0134";
$client = new SoapClient($wsdl);
$sql = $wpdb->prepare("SELECT product_code, destination_code from iggogrid_item_info where item_id=%s and lang_id=%s",$id,$lang_id);
$row = $wpdb->get_row($sql,ARRAY_A);
$eventInfo = $client->GetItem(array(
		'authorizationKey' => $key,
		'destinationCode' => $row['destination_code'],
		'productCode' => $row['product_code'],
		'culture' => $langArray[$lang_id]["long"]
));
$info = (array)$eventInfo->GetItemResult;
$info['person'] = '';
$info['rooms'] = '';
$info['url_key'] = '';
$info['season'] = '';
$info['offer'] = '';
$info['price'] = '';

//iggogrid_activity_availability insert or update

if($text == 'activity'){

	$beginDate = date('Y-m-d',strtotime("+1 day"));
	$priceKey = 'price_'.strtolower($langArr['short']);
	// 		echo $beginDate;
	// 		$price = $GLOBALS['client']->GetExtProductPrice(array('destinationCode' => 'talrere', 'productCode' => 'rere', 'beginDate' => '2015-01-13', 'authorizationKey' => $GLOBALS['key'], 'culture' =>$langArr["long"]));
	// 		print_r($price);die;

	$countDateNum = 1;
	do{
		$price = $GLOBALS['client']->GetExtProductPrice(array('destinationCode' => $info['DestinationCode'], 'productCode' =>$info['ProductCode'], 'beginDate' => $beginDate, 'authorizationKey' => $GLOBALS['key'], 'culture' =>$langArr["long"]));
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
	$activityAvailability = $GLOBALS['client']->GetCapacityDateRangeList(array('code' => $info['DestinationCode'], 'product' => $info['ProductCode'], 'beginDate' => date('Y-m-d'), 'endDate' =>  date("Y-m-d",strtotime("+1 year")), 'authorizationKey' => $GLOBALS['key']));

	foreach ($activityAvailability->GetCapacityDateRangeListResult as $rows){
		if(empty($rows)){
			continue;
		}
		foreach ($rows as $row){
			if(empty($row)){
				continue;
			}
			$day = date('Y-m-d',strtotime($row->Day));
			$day .= ' 00:00:00';

			$sqlActivityDetect = "SELECT * FROM iggogrid_activity_availability WHERE item_id='{$itemId}' AND day = '{$day}';";
			$queryActivityDetect = mysql_query($sqlActivityDetect, $conn);
			if(mysql_num_rows($queryActivityDetect) == 0){
					
				// 					if($row->Free != 0){
				// 					    echo 'day = '.$day.' itemid = '.$itemId.' free = '.$row->Free."\r\n";
				// 					}
					
				$sqlItemInsert = "INSERT INTO iggogrid_activity_availability (id,item_id,day,capacity,booked,free) VALUES ('','{$itemId}','{$day}','{$row->Capacity}','{$row->Booked}','{$row->Free}');";
				//echo $sqlItemInsert."\r\n";
				mysql_query($sqlItemInsert, $conn);
			}else{
				$rowItem = mysql_fetch_assoc($queryActivityDetect);
				$activityId = $rowItem["id"];
				//var_dump($activityId);
				$sqlActivityUpdate = "UPDATE iggogrid_activity_availability SET capacity='{$row->Capacity}', booked='{$row->Booked}', booked='{$row->Free}' WHERE id='{$activityId}';";
				//echo $sqlActivityUpdate."\r\n";
				mysql_query($sqlActivityUpdate, $conn);
			}
		}

	}
}else{
	$beginDate = date('Y-m-d',strtotime("+10 day"));
	$endDate =  date('Y-m-d',strtotime("+12 day"));
	//echo $beginDate."||".$endDate;
	$price = $GLOBALS['client']->GetProductPrice(array('code' =>$info['DestinationCode'], 'product' =>$info['ProductCode'], 'beginDate' =>$beginDate, 'endDate' =>$endDate, 'authorizationKey' => $GLOBALS['key']));
	$info['price'] =$price->GetProductPriceResult;
}

// 	return;
// 	die;
//end iggogrid_activity_availability
if(isset($info['Beds']) && isset($info['ExtraBeds'])){
	$info['person'] = (int)$info['Beds'] + (int)$info['ExtraBeds'];
}
if($langArr['short'] == 'FI'){
	$info['url_key'] =  $info['DestinationName'];
}elseif($langArr['short'] == 'EN'){
	$info['url_key'] =  $info['DestinationName'].'-en';
}else{
	$info['url_key'] =  $enTmpData[$itemId]['destination_name'].'-'.strtolower($langArr['short']);
}
if(isset($info['AccProperties']) && isset($info['AccProperties']->PropertyCriteria->NameValueItem)){
	$nameValueItem = $info['AccProperties']->PropertyCriteria->NameValueItem;
	// 		print_r($nameValueItem);
	foreach ($nameValueItem as $item){
		if($item->Name == 'Rooms'){
			$info['rooms'] = (int)$item->Value;
			break;
		}
	}
	foreach ($nameValueItem as $item){
		if($item->Name == 'Season'){
			$info['season'] = (int)$item->Value;
			break;
		}
	}
	foreach ($nameValueItem as $item){
		if($item->Name == 'Offer'){
			// 				print_r($nameValueItem);
			$info['offer'] = (int)$item->Value;
			break;
		}
	}
}
// 	print_r($info);
$alterColumns = array_keys($info);
//                 print_r($info);die;
//                 $info = serialize($info);
$sqlItemInfoDetect = "SELECT * FROM iggogrid_item_info WHERE item_id='{$itemId}' AND lang_id='{$langId}';";
$queryItemInfoDetect = mysql_query($sqlItemInfoDetect, $conn);
$queryColumnData = array();
$result=mysql_query('SHOW COLUMNS FROM iggogrid_item_info');
while ($row=mysql_fetch_row($result))
{
	$queryColumnData[$row[0]] = $row[1];
}
//print_r($queryColumnData);die;
// 	$queryColumnData = mysql_fetch_array($queryItemInfoDetect);
$alterTmpArray = array();
$alterDataArray = array();
foreach ($info as $akey=>$value){
	$akey = replaceStr($akey);
		
	if($akey == 'images'){
		if(isset($value->string)){
			$value = $value->string;
		}
	}
	//$value = trim($value);
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
		$value = replaceStr($value,'-');
	}
		
	if(is_array($value) || is_object($value)){
		$alterDataArray[$akey] = serialize(objectToArray($value));
	}else{
		$alterDataArray[$akey] = addslashes($value);
	}
		
	if(!isset($queryColumnData[$akey])){
		if(is_array($value) || is_object($value)){
			$type = 'text';
		}else{
			if(is_int($value)){
				$type = 'int';
			}else{
				$strlen = strlen($akey);
				if($strlen < 255){
					$type = 'vachar';
				}else{
					$type = 'text';
				}
			}
		}
		if($akey == 'description' || $akey == 'street_address'){
			$type = 'text';
		}
		switch ($type) {
			case 'vachar':
				$alterTmpArray[$akey] = '`'.$akey.'` varchar(255)';
				break;
			case 'text':
				$alterTmpArray[$akey] = '`'.$akey.'` text';
				break;
			case 'int':
				$alterTmpArray[$akey] = '`'.$akey.'` int(10)';
				break;
		}
	}
}

if(!empty($alterTmpArray)){
	$alterSql = 'alter table iggogrid_item_info add ('.implode(',', $alterTmpArray).');';
	mysql_query($alterSql, $conn);
}
if($langArr['short'] == 'EN'){
	$enTmpData[$itemId] =  $alterDataArray;
}
if(!empty($alterDataArray['home_page'])){
	$parseUrl = parse_url($alterDataArray['home_page']);
	$old_url = trim($parseUrl['path'],'/');
	$alterDataArray['old_url'] = $old_url;
}
$infoKeys = '';
$infoValues = '';
//print_r($alterDataArray);die;
	if(!empty($alterDataArray)){
		foreach ($alterDataArray as $akey=>$value){
			$infoValues .= '`'.$akey."`='".$value."',";
		}
		$infoValues = rtrim($infoValues,',');
	}
	//print_r($alterDataArray['url_key']);
	//var_dump($infoValues);die;
	$sqlItemInfoUpdate = "UPDATE iggogrid_item_info SET {$infoValues} WHERE item_id='{$itemId}' AND lang_id='{$langId}';";

	mysql_query($sqlItemInfoUpdate, $conn);
print_r($eventInfo);die;
}
?>