<?php
function iggo_custom_rewrite_update () {
global $wpdb;
$id = $_GET["id"];
$old_url=$_POST["old_url"];
$new_url=$_POST["new_url"];
$row = $_POST;
//update
if(isset($_POST['update'])){
	$parseUrl = parse_url($old_url);
	$old_url = trim($parseUrl['path'],'/');
	$parseUrl = parse_url($new_url);
	$new_url = trim($parseUrl['path'],'/');
	$new_url = str_replace(array('www.saariselka.com','saariselka.com'), '', $new_url);
	$wpdb->update(
		'iggogrid_custom_rewrites', //table
		array('old_url' => $old_url,'new_url' => $new_url), //data
		array( 'id' => $id ), //where
		array('%s'), //data format
		array('%s') //where format
	);	
}else if(isset($_POST['delete'])){//delete
	$wpdb->query($wpdb->prepare("DELETE FROM iggogrid_custom_rewrites WHERE  id=%s",$id));
}else{//selecting value to update	
	$sql = $wpdb->prepare("SELECT * from iggogrid_custom_rewrites where id=%s",$id);
	$row = $wpdb->get_row($sql,ARRAY_A);
// 	foreach ($iggogrid_item_infos as $s ){
// 		$name=$s->name;
// 	}
}
?>
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/items-admin.css')?>" rel="stylesheet" />
<div class="wrap">
<h2><?php echo sprintf(__("Edit '<font color='red'>%s</font>'"),$id);?></h2>

<?php if($_POST['delete']){?>
<div class="delete"><p><?php echo sprintf(__("%s deleted"),$id)?></p></div>
<a href="<?php echo admin_url('admin.php?page=iggo_custom_rewrite_list')?>">&laquo; Back to Url list</a>

<?php } else if($_POST['update']) {?>
<div class="updated"><p><?php echo sprintf(__("%s updated"),$id)?></p></div>
<a href="<?php echo admin_url('admin.php?page=iggo_custom_rewrite_list')?>">&laquo; Back to Url list</a>

<?php } else {?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<table class='wp-list-table widefat fixed'>
<tr><th>ID</th><td><?php echo $id;?></td></tr>
<tr><th>Old Url</th><td><?php echo site_url()?>/<input type="text" name="old_url" value="<?php echo $row['old_url'];?>"/></td></tr>
<tr><th>Url Key</th><td><?php echo site_url()?>/<input type="text" name="new_url" value="<?php echo $row['new_url'];?>"/></td></tr>
</table>
<input type='submit' name="update" value='Save' class='button'> &nbsp;&nbsp;
<input type='submit' name="delete" value='Delete' class='button' onclick="return confirm('are you sure ?')">
</form>
<?php }?>

</div>
<?php
}