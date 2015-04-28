<?php
function iggo_items_list () {
?>
<script type="text/javascript" src="<?php echo plugins_url('iggogrid/js/jquery.datatables.min.js')?>">
</script>
<script type="text/javascript">
jQuery(document).ready(function () {
	jQuery('#item-list').DataTable();
});
</script>
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/items-admin.css')?>" rel="stylesheet" />
<link type="text/css" href="<?php echo plugins_url('iggogrid/css/jquery.dataTables.min.css')?>" rel="stylesheet" />

<div class="wrap">
<h2>All Items</h2>
<!-- 
<a href="<?php echo admin_url('admin.php?page=iggo_items_create'); ?>">Add New</a>
-->
<?php
global $wpdb;
$currentPage = 1;
$pageSize = 30;
$typesSql = "SELECT code,type FROM iggogrid_types ";
//  echo $typesSql;die;
$typesArray = array();
foreach( $wpdb->get_results($typesSql) as $key => $row) {
	$row = (array)$row;
	$typesArray[$row['code']] = $row['type'];
}

$languages = IggoGrid::getLanguages();
$rows = $wpdb->get_results("SELECT  	info.item_id,info.lang_id, info.destination_name, info.url_key,info.old_url,type_code
															from iggogrid_item_info as info
														");
$pageNum = ceil(count($rows)/$pageSize);
$urlArray = array(
						//fi
						1=>array(
									'accommodation'=>'/majoitus/kohde/',
									'activity'=>'/aktiviteetit/kohde/',
									'restaurant'=>'/ruokailu/kohde/'
								),
						//se
						2=>array(
									'accommodation'=>'/se/accommodation/code/',
									'activity'=>'/se/activity/code/',
									'restaurant'=>'/se/restaurant/code/'
								),
						//en
						3=>array(
									'accommodation'=>'/en/accommodation/code/',
									'activity'=>'/en/activity/code/',
									'restaurant'=>'/en/restaurant/code/'
								),
						//de
						4=>array(
									'accommodation'=>'/de/accommodation/code/',
									'activity'=>'/de/activity/code/',
									'restaurant'=>'/de/restaurant/code/'
								),
						//ru
						5=>array(
									'accommodation'=>'/ru/accommodation/code/',
									'activity'=>'/ru/activity/code/',
									'restaurant'=>'/ru/restaurant/code/'
								),
					);
?>


<table id='item-list'  class="display" cellspacing="0" width="100%">
<thead>
<tr><th>Type</th><th>Name</th><th>Language</th><th>Current Url</th><th>Old Url</th><th>&nbsp;</th></tr>
</thead>
<tfoot>
<tr><th>Type</th><th>Name</th><th>Language</th><th>Current Url</th><th>Old Url</th><th>&nbsp;</th></tr>
</tfoot>
<?php
echo '<tbody>';
foreach ($rows as $row ){
	$row->type = $typesArray[$row->type_code];
	$oldUrls = unserialize($row->old_url);
	echo "<tr>";
	echo "<td>".ucfirst($row->type)."</td>";
	echo "<td>$row->destination_name</td>";
	echo "<td>{$languages[$row->lang_id]['lang']}</td>";
	echo "<td><a href='".site_url().$urlArray[$row->lang_id][$row->type].$row->url_key."' target='_blank'>$row->url_key</a></td>";
	echo "<td>";
	foreach ($oldUrls as $url){
		echo "<p>{$url}</p>";
	}
	echo "</td>";
	echo "<td><a href='".admin_url('admin.php?page=iggo_items_update&id='.$row->item_id.'&lang_id='.$row->lang_id.'&type='.$row->type)."'>Update</a>
				
			</td>";
	echo "</tr>";}
echo '</tbody>';
echo "</table>";
?>
</div>
<?php
}
?>