<?php
function iggo_items_create () {
$row = $_POST;
//insert
if(isset($_POST['insert'])){
	global $wpdb;
// 	print_r($_POST);
	$sqlItemInfoDetect = "SELECT item_id FROM iggogrid_item_info WHERE item_id='{$_POST['item_id']}' AND lang_id='{$_POST['lang_id']}';";
	$sqlItemInfoNum = $wpdb->get_var($sqlItemInfoDetect);
	if(is_null($sqlItemInfoNum) || $sqlItemInfoNum == 0){
		$sqlItemDetect = "SELECT * FROM iggogrid_item WHERE destination_code='{$_POST['destination_code']}' AND product_code = '{$_POST['product_code']}';";
		$sqlItemNum = $wpdb->get_var($sqlItemDetect);
		if(is_null($sqlItemNum) || $sqlItemNum == 0){
// 			var_dump($sqlItemNum);die;
// 			$wpdb->insert(
// 					'iggogrid_item_info', //table
// 					array('id' => $id,'name' => $name), //data
// 					array('%s','%s') //data format
// 			);
			
		}
		unset($_POST['insert']);
// 		print_r($_POST);
// 		else{
			$wpdb->insert(
					'iggogrid_item_info', //table
					$_POST //data
			);
			echo $wpdb->insert_id;
			$message = "Item inserted ";
// 		}

	}else{
		$message = "Item already exists!";
	}

}
?>
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/items-admin.css')?>" rel="stylesheet" />
<div class="wrap">
<h2>Add New Item</h2>
<?php if (isset($message)): ?><div class="updated"><p><?php echo $message;?></p></div><?php endif;?>
<a href="<?php echo admin_url('admin.php?page=iggo_items_list')?>">&laquo; Back to item list</a>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<p>Create New Item</p>
<table class='wp-list-table widefat fixed'>
<tr><th>Item Id</th><td><input type="text" name="item_id" value="<?php echo isset($row['item_id'])?$row['item_id']:''?>"/></td></tr>
<tr><th>Lang Id</th><td><input type="text" name="lang_id" value="<?php echo isset($row['lang_id'])?$row['lang_id']:'';?>"/></td></tr>
<tr><th>Destination Code</th><td><input type="text" name="destination_code" value="<?php echo isset($row['destination_code'])?$row['destination_code']:'';?>"/></td></tr>
<tr><th>Product Code</th><td><input type="text" name="product_code" value="<?php echo isset($row['product_code'])?$row['product_code']:'';?>"/></td></tr>
<tr><th>Destination Name</th><td><input type="text" name="destination_name" value="<?php echo isset($row['destination_name'])?$row['destination_name']:'';?>"/></td></tr>
<tr><th>Url Key</th><td><input type="text" name="url_key" value="<?php echo isset($row['url_key'])?$row['url_key']:'';?>"/></td></tr>
<tr><th>Old Url</th><td><input type="text" name="old_url" value="<?php echo isset($row['old_url'])?$row['old_url']:'';?>"/></td></tr>
</table>
<input type='submit' name="insert" value='Save' class='button'>
</form>
</div>
<?php
}