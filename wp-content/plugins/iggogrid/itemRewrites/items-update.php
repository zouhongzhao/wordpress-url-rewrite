<?php
function iggo_items_update () {
global $wpdb;
$id = $_GET["id"];
$lang_id = $_GET["lang_id"];
$type = $_GET["type"];
$old_urls=$_POST["old_url"];
$url_key=$_POST["url_key"];
$row = $_POST;
$languages = IggoGrid::getLanguages();
//update
if(isset($_POST['update'])){
	foreach ($old_urls as $key=>$old_url){
		$parseUrl = parse_url($old_url);
		$old_url = trim($parseUrl['path'],'/');
		$old_urls[$key] = str_replace(array('www.saariselka.com','saariselka.com'), '', $old_url);
	}
	$old_url = serialize($old_urls);
	$wpdb->update(
		'iggogrid_item_info', //table
		array('old_url' => $old_url), //data
		array( 'item_id' => $id,'lang_id'=>$lang_id ), //where
		array('%s'), //data format
		array('%s') //where format
	);	
}else if(isset($_POST['delete'])){//delete
	$wpdb->query($wpdb->prepare("DELETE FROM iggogrid_item_info WHERE  item_id=%s and lang_id=%s",$id,$lang_id));
}else{//selecting value to update	
	$sql = $wpdb->prepare("SELECT item_id,lang_id, destination_name, url_key,old_url from iggogrid_item_info where item_id=%s and lang_id=%s",$id,$lang_id);
	$row = $wpdb->get_row($sql,ARRAY_A);
	$old_urls = unserialize($row['old_url']);
	$old_urls = json_encode($old_urls);
// 	foreach ($iggogrid_item_infos as $s ){
// 		$name=$s->name;
// 	}
}
?>
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/items-admin.css')?>" rel="stylesheet" />
<div class="wrap">
<h2><?php echo sprintf(__("Edit '<font color='red'>%s</font>'"),$row['destination_name']);?></h2>

<?php if($_POST['delete']){?>
<div class="delete"><p><?php echo sprintf(__("%s deleted"),$row['destination_name'])?></p></div>
<a href="<?php echo admin_url('admin.php?page=iggo_items_list')?>">&laquo; Back to item list</a>

<?php } else if($_POST['update']) {?>
<div class="updated"><p><?php echo sprintf(__("%s updated"),$row['destination_name'])?></p></div>
<a href="<?php echo admin_url('admin.php?page=iggo_items_list')?>">&laquo; Back to item list</a>

<?php } else {?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<table class='wp-list-table widefat fixed'>
<tr><th>Type</th><td><?php echo ucfirst($type);?></td></tr>
<tr><th>Language</th><td><?php echo $languages[$lang_id]['lang'];?></td></tr>
<tr><th>Destination Name</th><td><input type="hidden" name="destination_name" value="<?php echo $row['destination_name'];?>"/><?php echo $row['destination_name'];?></td></tr>
<tr><th>Url Key</th><td><input type="hidden" name="url_key" value="<?php echo $row['url_key'];?>"/><?php echo $row['url_key'];?></td></tr>
<tr>
<th>Old Url</th>
<td>
<?php echo site_url()?>/
<div class="old_url_list"><div class="row old_url_row">
	 <div class="col-xs-8"><input type="text" name="old_url[0]" value=""/></div>
	<div class="col-xs-2"><button type="button" class="btn btn-default btn-sm add_url_list">Add</button> </div>
</div></div>
</td></tr>
</table>
<input type='submit' name="update" value='Save' class='button'> &nbsp;&nbsp;
<input type='submit' name="delete" value='Delete' class='button' onclick="return confirm('are you sure?')">
</form>
<script type="text/javascript">
var urlTemplate = '<div class="row old_url_row">\
	  <div class="col-xs-8">\
	    <input type="text" value="{url_value}" class="form-control"  name="{url_name}">\
	  </div>\
	  <div class="col-xs-2">\
		 <button type="button" class="btn btn-default btn-sm add_url_list">Add</button>\
		 <button type="button" class="btn btn-default btn-sm delete_url_list">Delete</button>\
	  </div>\
	</div>';
var urlListCount = 0;
function removeDeleteFirst(){
	jQuery('.old_url_list .old_url_row:first').find('.delete_url_list').remove();
}
jQuery(document).ready(function () {
	var old_urls = '<?php echo $old_urls?>';
	old_urls = eval('(' + jQuery.trim(old_urls) + ')');
// 	console.log(old_urls);
	if(old_urls !== false){
// 		console.log(old_urls);
		jQuery(".old_url_list").html('');
		var count = 0;
		jQuery.each(old_urls,function(i,value){
			var url_name = 'old_url['+i+']',
					url_value = value,
					template = urlTemplate.replace("{url_name}",url_name).replace("{url_value}",url_value);
			jQuery(".old_url_list").append(template);
			count ++;
		})
		urlListCount = count;
// 		console.log(urlListCount);
// 		console.log(old_urls);
	}else{
		urlListCount = 1;
	}
	jQuery(document).on('click','.add_url_list',function(){
			var count = urlListCount + 1,
					url_name = 'old_url['+count+']',
					url_value = '',
					template = urlTemplate.replace("{url_name}",url_name).replace("{url_value}",url_value);
			urlListCount ++;
			jQuery(".old_url_list").append(template);
	})
	//delete price row
	jQuery(document).on('click','.delete_url_list',function(){
		jQuery(this).parents('.old_url_row').remove();
	})
});
</script>
<?php }?>

</div>
<?php
}