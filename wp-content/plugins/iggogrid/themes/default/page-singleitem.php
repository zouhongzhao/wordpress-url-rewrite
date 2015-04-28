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
 $areaData = array();
 $typeData = array();
 $langId = IggoGrid::getLanguageId();
 $langShortCode = substr(get_bloginfo ( 'language' ), 0, 2);
 global $wpdb;
 $where = "where lang_id = {$langId}";
 $sql = "SELECT type,value,text FROM iggogrid_translation $where";
 foreach( $wpdb->get_results($sql) as $key => $row) {
 	$row = (array)$row;
 	if($row['type'] == 'area'){
 		$areaData[$row['text']] = $row['text'];
 	}
 	if($row['type'] == 'type'){
 		$typeData[$row['text']] = $row['text'];
 	}
 }
 
 $typesSql = "SELECT type,code FROM iggogrid_types";
 //  echo $typesSql;die;
 $typesArray = array();
 foreach( $wpdb->get_results($typesSql) as $key => $row) {
 	$row = (array)$row;
 	$typesArray[$row['code']] = $row['type'];
 }
 
 $row = $GLOBALS['iggoGridInfoData'];
 $area = isset($row['area'])?$row['area']:'';
 $type = isset($row['type'])?$row['type']:'';

 $internet = 0;
 $animals = 0;
 $washing = 0;
 
 $request = explode("/",$wp->request);
 if($langId != 1){
 	unset($request[0]);
 }
 $request = array_values($request);
//  print_r($row);
//  var_dump($area);
// echo 222;
//  var_dump($area);
//  echo 333;
//  var_dump($row);die;
 $typeValue = $typesArray[$row['type_code']];
 if(($typeValue == 'activity' && $row['admin_added'] == 0 && $row['price'] <= 0) ||($row['price'] <= 0 && $typeValue == 'accommodation')){
 	status_header(404);
 	nocache_headers();
 	include( get_404_template() );
 	exit;
 }
 $distance = '';
 if(!empty($row['acc_properties'])){
	$acc_properties = unserialize($row['acc_properties']);
	if(isset($acc_properties['PropertyCriteria']) && isset($acc_properties['PropertyCriteria']['NameValueItem'])){
		$nameValueItem = $acc_properties['PropertyCriteria']['NameValueItem'];
	}
	if(isset($nameValueItem)){
		foreach ($nameValueItem as $item){
			if($item['Name'] == 'Internet'){
				$internet = (int)$item['Value'];

			}elseif ($item['Name'] == 'Animals'){
				$animals = (int)$item['Value'];
			}elseif ($item['Name'] == 'Washing'){
				$washing = (int)$item['Value'];
			}elseif ($item['Name'] == 'Distance'){
					$val = str_replace(',', '.', trim($item['Value']));
					$distance = (int)$val;
			}else{
				continue;
			}

		}
	}
}

$images = unserialize($row['images']);

$pageId = null;
if(isset($request[1])){
	$pageId = IggoGrid::get_ID_by_slug($request[0]);
}

$naviType = $request[0];
$urlFlag = '';
		switch ($typeValue){
			case 'activity':
				$pageId = '13';
				break;
			case 'accommodation':
				$pageId = '9';
				break;
			case 'restaurant':
				$pageId = '11';
				break;
			default:
		}

	if($langId == 3){
		$urlFlag = '/en';
	}elseif ($langId == 5){
		$urlFlag = '/ru';
	}
 ?>
<?php get_header(); ?>
    <section id="pageintro">
        <div class="container">
            <div class="row">

                <!-- Get booking block -->
                <?php //include_once(TEMPLATEPATH . '/bookingblock.php'); ?>
                <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3"></div>
                
                <div class="col-xs-12 col-sm-8 col-md-9 col-lg-9">
                    <div class="singlebanner">
                    
                        <!-- Majoitus FI -->
                        <?php if (page_id_or_ancestor_id("9",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("majoitus_osion_kuva", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Majoitus EN -->
                        <?php } elseif (page_id_or_ancestor_id("1008",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("majoitus_osion_kuva_en", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Majoitus RU -->
                        <?php } elseif (page_id_or_ancestor_id("1010",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("majoitus_osion_kuva_ru", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />



                        <!-- Ruokailu -->
                        <?php } elseif (page_id_or_ancestor_id("11",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("ruokailu_osion_kuva", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Ruokailu EN -->
                        <?php } elseif (page_id_or_ancestor_id("1033",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("ruokailu_osion_kuva_en", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Ruokailu RU -->
                        <?php } elseif (page_id_or_ancestor_id("1035",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("ruokailu_osion_kuva_ru", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />



                        <!-- Aktiviteetit -->
                        <?php } elseif (page_id_or_ancestor_id("13",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("aktiviteetit_osion_kuva", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Aktiviteetit EN -->
                        <?php } elseif (page_id_or_ancestor_id("816",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("aktiviteetit_osion_kuva_en", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Aktiviteetit RU -->
                        <?php } elseif (page_id_or_ancestor_id("818",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("aktiviteetit_osion_kuva_ru", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />



                        <!-- Matkustaminen -->
                        <?php } elseif (page_id_or_ancestor_id("15",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("matkustaminen_osion_kuva", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Matkustaminen EN -->
                        <?php } elseif (page_id_or_ancestor_id("890",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("matkustaminen_osion_kuva_en", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Matkustaminen RU -->
                        <?php } elseif (page_id_or_ancestor_id("901",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("matkustaminen_osion_kuva_ru", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />



                        <!-- Tietoa meistä -->
                        <?php } elseif (page_id_or_ancestor_id("17",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("tietoa_meista_osion_kuva", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Tietoa meistä EN -->
                        <?php } elseif (page_id_or_ancestor_id("760",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("tietoa_meista_osion_kuva_en", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Tietoa meistä RU -->
                        <?php } elseif (page_id_or_ancestor_id("576",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("tietoa_meista_osion_kuva_ru", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />



                        <!-- Tietoa alueesta -->
                        <?php } elseif (page_id_or_ancestor_id("19",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("tietoa_alueesta_osion_kuva", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Tietoa alueesta EN -->
                        <?php } elseif (page_id_or_ancestor_id("858",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("tietoa_alueesta_osion_kuva_en", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />

                        <!-- Tietoa alueesta RU -->
                        <?php } elseif (page_id_or_ancestor_id("860",$pageId)) { ?>
                            <?php $image = wp_get_attachment_image_src(get_field("tietoa_alueesta_osion_kuva_ru", "option"), "defaulttop"); ?>
                            <img src="<?php echo $image[0]; ?>" alt="Saariselka" />



                        <?php } else { ?>
                        <?php } ?>
                        
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section id="outside">
        <div class="container">

            <div class="row">


            	<!-- Sidebar -->
                <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
                    <!--<div class="row">
                        <div class="col-xs-12">
                            <h1><?php echo ucfirst(IggoGrid::__lang($request[0])); ?></h1>
                        </div>
                    </div>

                    <div class="row filters">

                        <div class="col-xs-12">
                            <label><?php _e('Area','iggo'); ?>:</label>
                            <select id="iggo_area" name="iggo_area">
                            	<option value=""><?php _e('All','iggo'); ?></option>
                            <?php foreach ($areaData as $data):?>
                            	<?php if($area && $area == $data):?>
                            		<option value="<?php echo $data?>" selected = "selected"><?php echo $data?></option>
                            	<?php else:?>
                            		<option value="<?php echo $data?>"><?php echo $data?></option>
                            	<?php endif;?>

                            <?php endforeach;?>
                            </select>
                        </div>

                        <div class="col-xs-12">
                            <label><?php _e('Type of accommodation','iggo'); ?>:</label>
                            <select id="iggo_type" name="iggo_type">
                            	<option value=""><?php _e('All','iggo'); ?></option>
                              <?php foreach ($typeData as $data):?>
	                              <?php if($type && $type == $data):?>
	                            		<option value="<?php echo $data?>" selected = "selected"><?php echo $data?></option>
	                            	<?php else:?>
	                            		<option value="<?php echo $data?>"><?php echo $data?></option>
	                            	<?php endif;?>
                            <?php endforeach;?>
                            </select>
                        </div>

                        <div class="col-xs-6">
                            <label><?php _e('Persons','iggo'); ?>:</label>
                            <select id="iggo_person" name="iggo_person">
                            	<option value="0"><?php _e('All','iggo'); ?></option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                        </div>

                        <div class="col-xs-6">
                            <label><?php _e('Bedrooms','iggo'); ?>:</label>
                            <select id="iggo_rooms" name="iggo_rooms">
                            	<option value="0"><?php _e('All','iggo'); ?></option>
                                 <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                            </select>
                        </div>

                        <div class="col-xs-12">
                            <button class="iggo-button iggo_filter_submit" >
                                <span class="title"><?php _e('Search','iggo'); ?></span>
                            </button>
                        </div>
					</div>-->
                </div>


                <!-- Single Item Content -->
                <div class="col-xs-12 col-sm-8 col-md-9 col-lg-9 single-content">

	                <div class="row">
	                	<div class="col-xs-12 single-title">
	                		<?php if($typeValue == 'activity'): ?>
	                			<h1><?php echo $row['product_name']?></h1>
	                		<?php else:?>
	                			<h1><?php echo $row['destination_name']?></h1>
	                		<?php endif;?>
						
	                	</div>
	                </div>

	                <div class="row">
	                	<div class="col-xs-12 col-sm-5 col-md-4 col-lg-4 pull-right">

	                		<!-- Specs box -->
	                		<?php if($typeValue == 'accommodation'): ?>
	                		<div class="single-specs rightbox">
								<div class="info-box">
								    <table>
								        <tbody>
								        	<tr>
									           <td><?php echo $row['area']?></td>
									        </tr>
								        	<?php if($row['area'] == 'Keskusta, Saariselkä'): ?>
											<tr>
									           <td><?php echo _e('Distance','iggo').': '.$distance.'km'?></td>
									        </tr>
								        	<?php endif;?>
								            <tr>
								                <td><?php echo $row['street_address']?></td>
								            </tr>

								            <tr>
								                <td><?php echo $row['postal_address']?></td>
								            </tr>

								            <tr>
								                <td>
								                    <b><?php _e('Number of beds','iggo'); ?>:</b>
								                    <span><?php echo $row['beds']?></span>

								                </td>
								            </tr>

							                <tr>
							                    <td>
							                        <b><?php _e('Max . number of persons','iggo'); ?>:</b>
							                        <span><?php echo $row['beds'] + $row['extra_beds']?></span>
							                    </td>
							                </tr>

								            <tr>

								                <td>
								                    <b><?php _e('Internet Access','iggo'); ?>:</b>
								                    <span><?php echo $internet == 0 ? _e('No','iggo') : _e('Yes','iggo');?></span>
								                </td>
								                </tr>
								                <tr>
								                <td>
								                    <b><?php _e('Pets allowed','iggo'); ?>:</b>
								                    <span><?php echo $animals == 0 ? _e('No','iggo') : _e('Yes','iggo');?></span>
								                </td>
								                </tr>
								                <tr>
								                <td>
								                    <b><?php _e('Washing Machine','iggo'); ?></b>
								                    <span><?php echo $washing == 0 ? _e('No','iggo') : _e('Yes','iggo');?></span>
								                </td>
								            </tr>

								            <tr>
								                <td>
								                    <!-- b i18n:translate="classification">Classification:</b-->

								                    <a href="http://saariselka.com/matkailijalle/majoitus/laatukriteeristo/@@simple_popup" class="popup">
								                       <img src="http://saariselka.com/portal_url/++resource++plonetheme.saariselka.images/hilla.gif" alt="*">
								                       <img src="http://saariselka.com/portal_url/++resource++plonetheme.saariselka.images/hilla.gif" alt="*">
								                       <img src="http://saariselka.com/portal_url/++resource++plonetheme.saariselka.images/hilla.gif" alt="*">
								                       <img src="http://saariselka.com/portal_url/++resource++plonetheme.saariselka.images/hilla.gif" alt="*">
								                    </a>
								                </td>
								            </tr>
								        </tbody>
								    </table>
								</div>
							</div>
                            <?php endif;?>

							<!-- Booking calendar iframe or something goes here -->
							<?php if($typeValue == 'accommodation'): ?>
							<div class="single-booking rightbox">
								<iframe height="310"  width="233" src="http://varaamo.saariselka.com/intres/shops/saariselka/acc/sskv/SearchEngine.aspx?dcode=<?php echo $row['destination_code']?>&pcode=<?php echo $row['product_code']?>&length=3&culture=<?php echo $langShortCode?>&style=<?php bloginfo('template_directory'); ?>/css/calendar-iframe.css" scrolling="no"></iframe>
								<!-- 
								<img alt="Tempdemo" src="<?php bloginfo('template_directory'); ?>/images/demo_varausframe.png" />
								-->
								
							</div>
                            <?php endif;?>
                            
                            <?php if($typeValue == 'activity'): ?>
                            <?php
                                $itemId = $row['item_id'];
                                $day = date('Y-m-d',time());
                                $day .= ' 00:00:00';
                                
                                
                                $where = "where item_id = {$itemId} and free>0 and unix_timestamp(day) > unix_timestamp('$day') order by day asc limit 1";
                                $sql = "SELECT day FROM iggogrid_activity_availability $where";
                                $activityResult = $wpdb->get_results($sql);
                                
                                if(isset($activityResult[0]->day)){
                                	$bookDay = $activityResult[0]->day;
                                	$bookTime = strtotime($bookDay);
                                	$beginWeek = date('w',$bookTime);
                                	
                                	if($beginWeek != 1){
	                                	$bookDay = date('d.m.Y',$bookTime);
	                                	$beginDay = date('d.m.Y', strtotime("last Monday", $bookTime));
                                	}else{
                                	    $beginDay = $bookDay;
                                	}
                                }else{
                                	 $beginDay = $bookDay = '';
                                }

                                
	                            $itemPrices = '';
	                            if(!empty($row)){
	                                switch ($langId){
									    case 1:
											if(isset($row['price_fi'])){
												$itemPrices = $row['price_fi'];
											}
											break;
									    case 2:
										    if(isset($row['price_se'])){
												$itemPrices = $row['price_se'];
											}
											break;
										case 3:
											if(isset($row['price_en'])){
												$itemPrices = $row['price_en'];
											}
											break;
										case 4:
											if(isset($row['price_de'])){
												$itemPrices = $row['price_de'];
											}
											break;
										case 5:
											if(isset($row['price_ru'])){
												$itemPrices = $row['price_ru'];
											}
											break;
										default:
											$itemPrices = '';
									}
	                            }
	                                
	                            if($itemPrices != ''){
	                                $itemPrices = unserialize($itemPrices);
	                            }
// 	                            print_r($itemPrices);
                            ?>
                            <?php if(isset($activityResult[0]->day) && !empty($itemPrices)) : ?>
                            <div class="single-booking rightbox">
                            <!-- 
                                <div><span><?php echo _e('Available from','iggo')?>:</span></div>
                                <div><span><?php echo $bookDay; ?></span></div>
                             -->
                            		 <div><span><?php echo _e('Price','iggo')?> (€):</span></div>
	                                <?php foreach ((array)$itemPrices as $price): ?>
	                                <div>
		                                <?php if( !empty($price['header'])):?>
		                                	<span><?php echo $price['header'] ?></span>: 
		                                 <?php endif;?>
		                               <span><?php echo $price['price'] ?></span><span class="euro-mark">€</span>
	                               </div>
	                                <?php endforeach;?>
                               
                                <?php if( $row['admin_added'] == 0):?>
                                	<a target="_blank" href="http://varaamo.saariselka.com/intres/shops/saariselka/ext/site/extras.aspx?beginDate=<?php echo $beginDay;?>&typeCodes=<?php echo $row['type_code'] ?>&destinationCode=<?php echo $row['destination_code']; ?>&productCode=<?php echo $row['product_code']?>">
	                                    <span><?php echo _e('Book','iggo')?></span>
	                                </a>
                                <?php endif;?>
                                
                            </div>
                            <?php endif;?>
                            <?php endif;?>
                            <?php if(!empty($images)):?>
	                            <!-- Gallery that shows fake thumbnails (scaled down versions of the full images) and links to the same image which opens with colorbox -->
		                		<div class="single-gallery rightbox">
		                			<div class="row">
		                				<?php foreach ((array)$images as $image):?>
		                				<?php if($row['admin_added'] == 1){$image = get_site_url().$image;}?>
		                					<div class="col-xs-4 single-image">
		                					<a class="colorbox" href="<?php echo $image?>">
		                						<img src="<?php echo $image?>" alt=""/>
		                					</a>
		                				</div>
		                				<?php endforeach;?>
		                			</div>
		                		</div>
                            <?php endif;?>
	                		
	                		<!-- Shows map -->
	                		<?php if(!empty($row['latitude']) && !empty($row['longitude'])):?>
	                			<div class="single-map rightbox">
		                			<iframe src="https://www.google.com/maps/?q=<?php echo $row['latitude']?>,<?php echo $row['longitude']?>&output=embed" width="400" height="300" frameborder="0" style="border:0"></iframe>
		                		</div>
	                		<?php endif;?>
	                	</div>
	                	<!-- Left content text -->
	                	<div class="col-xs-12 col-sm-7 col-md-8 col-lg-8">
							<?php echo $row['description'];?>
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