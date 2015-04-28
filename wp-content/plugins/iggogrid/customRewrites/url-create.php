<?php
function iggo_custom_rewrite_create () {
$row = $_POST;
//insert
if(isset($_POST['insert'])){
	global $wpdb;
// 	print_r($_POST);
	$old_url = $_POST['old_url'];
	$parseUrl = parse_url($old_url);
	$old_url = trim($parseUrl['path'],'/');
	$new_url = $_POST['new_url'];
	$parseUrl = parse_url($new_url);
	$new_url = trim($parseUrl['path'],'/');
	$new_url = str_replace(array('www.saariselka.com','saariselka.com'), '', $new_url);
	$sqlUrlInfoDetect = "SELECT id FROM iggogrid_custom_rewrites WHERE old_url='{$old_url}';";
	$sqlUrlInfoNum = $wpdb->get_var($sqlUrlInfoDetect);
	if(is_null($sqlUrlInfoNum) || $sqlUrlInfoNum == 0){
		unset($_POST['insert']);
		$_POST['old_url'] = $old_url;
		$_POST['new_url'] = $new_url;
		$wpdb->insert(
					'iggogrid_custom_rewrites', //table
					$_POST //data
		);
// 		echo $wpdb->insert_id;
		$message = "Url inserted ";

	}else{
		$message = "Url already exists!";
	}

}
?>
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/items-admin.css')?>" rel="stylesheet" />
<div class="wrap">
<h2>Add New Url</h2>
<?php if (isset($message)): ?><div class="updated"><p><?php echo $message;?></p></div><?php endif;?>
<a href="<?php echo admin_url('admin.php?page=iggo_custom_rewrite_list')?>">&laquo; Back to url list</a>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<p>Create New Url</p>
<table class='wp-list-table widefat fixed'>
<tr><th>Old Url</th><td><?php echo site_url()?>/<input type="text" name="old_url" value="<?php echo isset($row['old_url'])?$row['old_url']:'';?>"/></td></tr>
<tr><th>New Url</th><td><?php echo site_url()?>/<input type="text" name="new_url" value="<?php echo isset($row['new_url'])?$row['new_url']:'';?>"/></td></tr>
</table>
<input type='submit' name="insert" value='Save' class='button'>
</form>
</div>
<?php
}