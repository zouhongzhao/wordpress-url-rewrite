<?php
function iggo_items_list_update () {
?>
<script type="text/javascript" src="<?php echo plugins_url('iggogrid/js/jquery.datatables.min.js')?>">
</script>
<script type="text/javascript">

function itemApiUpdate(id,type){
	jQuery("#dvLoading").show();
	jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'apf_itemApiUpdate',
            id: id,
            type: type
        },
        success: function(data, textStatus, XMLHttpRequest) {
        	var data = jQuery.trim(data);
//         	data = jQuery.parseJSON(data);
        	jQuery("#dvLoading").hide();
        	alert(data);
//        	 location.reload();
        },
        error: function(MLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
            jQuery("#dvLoading").hide();
        }
 
    });
}
function fullUpdateItems(){
	jQuery("#dvLoading").show();
	jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'apf_fullUpdateItems',
        },
        success: function(data, textStatus, XMLHttpRequest) {
        	var data = jQuery.trim(data);
//         	data = jQuery.parseJSON(data);
        	jQuery("#dvLoading").hide();
        	alert(data);
//        	 location.reload();
        },
        error: function(MLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
            jQuery("#dvLoading").hide();
        }
 
    });
}

jQuery(document).ready(function () {
	jQuery('#item-list').DataTable();
});
</script>
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/items-admin.css')?>" rel="stylesheet" />
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/jquery.dataTables.min.css')?>" rel="stylesheet" />


<div class="wrap">
<h2>All Items</h2>

<style type="text/css">
#dvLoading
{
   background:#000 url(<?php echo plugins_url('iggogrid/images/loader.gif')?>) no-repeat center center;
   height: 100px;
   width: 100px;
   position: fixed;
   z-index: 1000;
   left: 50%;
   top: 50%;
   margin: -25px 0 0 -25px;
}
</style>
<div class="wrap">
<h2>All Items List <a class="add-new-h2" href="#"  onclick="fullUpdateItems()">Full Update</a></h2>

<!-- 
<a href="<?php echo admin_url('admin.php?page=iggo_items_create'); ?>">Add New</a>
-->
<?php
global $wpdb;
$currentPage = 1;
$pageSize = 30;


$readQeury = "SELECT  	info.item_id,info.lang_id, info.destination_name, info.url_key,info.old_url,types.type
															from iggogrid_item_info as info 
															left join iggogrid_item as items on items.id=info.item_id
															left join iggogrid_types as types on items.type_code =  types.code
														";


$languages = IggoGrid::getLanguages();
$rows = $wpdb->get_results("SELECT  	info.item_id,info.lang_id, info.destination_name, info.url_key,info.old_url,types.type
															from iggogrid_item_info as info 
															left join iggogrid_item as items on items.id=info.item_id
															left join iggogrid_types as types on items.type_code =  types.code
														");

$langArray = array(
		1=>array('long'=>'Finnish'),
		2=>array('long'=>'Swedish'),
		3=>array('long'=>'English'),
		4=>array('long'=>'German'),
		5=>array('long'=>'Russian'),
);
$languages = IggoGrid::getLanguages();

$sql = "SELECT item_id,destination_code,product_code,type_code  from iggogrid_item_info group by item_id";
$rows = $wpdb->get_results($sql);

$typesSql = "SELECT code,type FROM iggogrid_types ";
//  echo $typesSql;die;
$typesArray = array();
foreach( $wpdb->get_results($typesSql) as $key => $row) {
	$row = (array)$row;
	$typesArray[$row['code']] = $row['type'];
}

$pageNum = ceil(count($rows)/$pageSize);

?>


<table id='item-list'  class="display" cellspacing="0" width="100%">
<thead>

<tr><th>Type</th><th>Name</th><th>Language</th><th>Current Url</th><th>Old Url</th><th>&nbsp;</th></tr>
</thead>
<tfoot>
<tr><th>Type</th><th>Name</th><th>Language</th><th>Current Url</th><th>Old Url</th><th>&nbsp;</th></tr>

<tr><th>Item ID</th><th>Type</th><th>Destination Code</th><th>Product Code</th><th>Action</th></tr>
</thead>
<tfoot>
<tr><th>Item ID</th><th>Type</th><th>Destination Code</th><th>Product Code</th><th>Action</th></tr>

</tfoot>
<?php
echo '<tbody>';
foreach ($rows as $row ){

	echo "<tr>";
	echo "<td>".ucfirst($row->type)."</td>";
	echo "<td>$row->destination_name</td>";
	echo "<td>{$languages[$row->lang_id]['lang']}</td>";
	echo "<td><a href='".site_url().'/majoitus/kohde/'.$row->url_key."' target='_blank'>$row->url_key</a></td>";
	echo "<td>$row->old_url</td>";
	echo "<td><a href='".admin_url('admin.php?page=iggo_items_api_update&id='.$row->item_id.'&lang_id='.$row->lang_id.'&type='.$row->type)."'>Update</a>
				
			</td>";
	echo "</tr>";}

// print_r($row);die;
	$typeCode = $row->type_code;
	$type = $typesArray[$typeCode];
	echo "<tr>";
	echo "<td>".$row->item_id."</td>";
	echo "<td>".$type."</td>";
	echo "<td>".$row->destination_code."</td>";
	echo "<td>".$row->product_code."</td>";
// 	echo "<td>".$row->destination_name."</td>";
// 	echo "<td>".$row->product_name."</td>";
	
// 	echo "<td><a href='".site_url().'/majoitus/kohde/'.$row->url_key."' target='_blank'>$row->url_key</a></td>";
// 	echo "<td>$row->old_url</td>";
	echo "<td><a href='#' onclick=\"itemApiUpdate({$row->item_id},'{$type}')\">Update</a></td>";
	echo "</tr>";
}

echo '</tbody>';
echo "</table>";
?>
</div>
<div id="dvLoading" style="display:none"></div>
