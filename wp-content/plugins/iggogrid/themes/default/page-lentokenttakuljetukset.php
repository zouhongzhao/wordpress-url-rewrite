 <?php
 function replaceUrlStr($str){
	$str = preg_replace("/([A-Z])([a-z])/", "-\\0", $str);
	$str  = strtolower($str);
	$msg = preg_replace("/ä/","a",$str);
	$msg = preg_replace("/å/","a",$msg);
	$msg = preg_replace("/ö/","o",$msg);
	$msg = preg_replace("/Å/","a",$msg);
	$msg = preg_replace("/[\s\x-\x#\[\]\{\}\'\"=+\-\(\)!*&%~^,.<|>\?$@]+/i","-",$msg);
	$msg=preg_replace('/-(-){1,}/',"-",$msg);
	$msg = trim($msg,'-');
	$msg = filter_var($msg, FILTER_SANITIZE_URL);
	$msg = stripslashes($msg);
	return $msg;
 }
 
 function ob2ar($obj) {
 	if(is_object($obj)) {
 		$obj = (array)$obj;
 		$obj = ob2ar($obj);
 	} elseif(is_array($obj)) {
 		foreach($obj as $key => $value) {
 			$obj[$key] = ob2ar($value);
 		}
 	}
 	return $obj;
 }
 
 
 
 $langId = IggoGrid::getLanguageId();
 $langShortCode = substr(get_bloginfo ( 'language' ), 0, 2);
 
 $row = ob2ar($GLOBALS['iggoGridInfoData']->GetItemResult);
 
 $nameValueItem = array();
 $itemName='';

 foreach ((array)$row['AccProperties']['PropertyCriteria']['NameValueItem'] as $nameValueData ){
 	$itemName = $nameValueData['Name'];
 	if($itemName != ''){
	 	$nameValueItem[$itemName]['MaxValue'] = $nameValueData['MaxValue'];
	 	$nameValueItem[$itemName]['MinValue'] = $nameValueData['MinValue'];
	 	$nameValueItem[$itemName]['Text'] = $nameValueData['Text'];
	 	$nameValueItem[$itemName]['Value'] = $nameValueData['Value'];
 	}
 }
 
 $siteUrl = get_site_url();
//  print_r($siteUrl);

?>
<?php get_header(); ?>
    <section id="pageintro">
        <div class="container">
            <div class="row">
                <!-- Get booking block -->
                <?php include_once(TEMPLATEPATH . '/bookingblock.php'); ?>

                <div class="col-xs-12 col-sm-8 col-md-9 col-lg-9">
                    <div class="singlebanner">
                    <!-- Matkustaminen EN -->
                    <?php if ($langId == 3) { ?>
                        <?php $image = wp_get_attachment_image_src(get_field("matkustaminen_osion_kuva_en", "option"), "defaulttop"); ?>
                        <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                    <!-- Matkustaminen RU -->
                    <?php } elseif ($langId == 5) { ?>
                        <?php $image = wp_get_attachment_image_src(get_field("matkustaminen_osion_kuva_ru", "option"), "defaulttop"); ?>
                        <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                    <!-- Matkustaminen -->
                    <?php } else{ ?>
                        <?php $image = wp_get_attachment_image_src(get_field("matkustaminen_osion_kuva", "option"), "defaulttop"); ?>
                        <img src="<?php echo $image[0]; ?>" alt="Saariselka" />
                    <?php }?>
                            
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section id="outside">
        <div class="container">
            <div class="row">
                <div id="sidebar" class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
					<ul class="sidemenu">
						<li class="page_item page-item-15 current_page_ancestor">
							<a href="<?php echo $siteUrl.'/matkustaminen/'; ?>"><?php echo _e('Matkustaminen','iggo')?></a>
						</li>
						<li class="page_item page-item-1602">
							<a href="<?php echo $siteUrl.'/matkustaminen/autolla/'; ?>"><?php echo _e('Autolla','iggo')?></a>
						</li>
						<li class="page_item page-item-1604">
							<a href="<?php echo $siteUrl.'/matkustaminen/junalla/'; ?>"><?php echo _e('Junalla','iggo')?></a>
						</li>
						<li class="page_item page-item-1608 page_item_has_children current_page_ancestor current_page_parent">
							<a href="<?php echo $siteUrl.'/matkustaminen/lentaen/'; ?>"><?php echo _e('Lentäen','iggo')?></a>
						<ul class="children">
							<li class="page_item page-item-690">
								<a href="<?php echo $siteUrl.'/matkustaminen/lentaen/lentokenttabussi/'; ?>"><?php echo _e('Lentokenttäbussi','iggo')?></a>
							</li>
							<li class="page_item page-item-668 current_page_item">
							    <a href="<?php echo $siteUrl.'/matkustaminen/lentaen/lentokenttakuljetukset/'; ?>"><?php echo _e('Tilauskuljetukset lentokentältä','iggo')?></a>
							</li>
						</ul>
						</li>
					</ul>
				</div>
            
                <div class="col-xs-12 col-sm-8 col-md-9 col-lg-9 single-content">
	                <div class="row introtext">
	                	<div class="col-xs-12 ">
	                	    <h1><?php echo $row['DestinationName']?></h1>
	                	</div>
	                </div>
	                
	                <div class="row">
	                	<!-- Left content text -->
		                <div class="col-xs-12">
						    <?php echo $row['Description'];?>
		                </div>
	                	
	                </div>
	               
               </div>
           </div>
        </div>
    </section>
<?php get_footer(); ?>
<script type="text/javascript">
$(".colorbox").colorbox({
				maxHeight: "85%",
				maxWidth: "85%",
				pagination:true,
				rel:'colorbox'
		});
$(document).ready(function(){

})
</script>