<?php
function iggo_custom_rewrite_list () {
?>
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/items-admin.css')?>" rel="stylesheet" />
<div class="wrap">
<h2>All Items</h2>
<a href="<?php echo admin_url('admin.php?page=iggo_custom_rewrite_create'); ?>">Add New</a>
<?php
global $wpdb;
$languages = IggoGrid::getLanguages();
$rows = $wpdb->get_results("SELECT  	*	from  iggogrid_custom_rewrites");

echo "<table class='wp-list-table widefat fixed'>";
echo "<tr><th>ID</th><th>Old Url</th><th>New Url</th><th>&nbsp;</th></tr>";
foreach ($rows as $row ){
	echo "<tr>";
	echo "<td>".$row->id."</td>";
	echo "<td><a href='".site_url().'/'.$row->old_url."' target='_blank'>$row->old_url</a></td>";
	echo "<td><a href='".site_url().'/'.$row->new_url."' target='_blank'>$row->new_url</a></td>";
	echo "<td><a href='".admin_url('admin.php?page=iggo_custom_rewrite_update&id='.$row->id)."'>Update</a>
				
			</td>";
	echo "</tr>";}
echo "</table>";
?>
</div>
<?php
}
//<a href='".admin_url('admin.php?page=iggo_items_delete&id='.$row->item_id.'&lang_id='.$row->lang_id)."'>Delete</a>
// function iggo_items_delete(){
// 	global $wpdb;
// 	$id = trim($_GET["id"]);
// 	$lang_id = trim($_GET["lang_id"]);
// 	if(empty($id) || empty($lang_id)){	
// 		$message = '';
// 		wp_redirect( admin_url('admin.php?page=iggo_items_list') );
// 		exit();
// 	}
// 	$table = 'iggogrid_item_info';
// 	global $wpdb;
// 	$conditionValue = array('item_id'=>$id,'lang_id'=>$lang_id);
// 	//$deleted = $wpdb->delete( $table, $conditionValue );
// // 	var_dump($deleted);
// 	wp_redirect( admin_url('admin.php?page=iggo_items_list') );
// 	exit;
// }