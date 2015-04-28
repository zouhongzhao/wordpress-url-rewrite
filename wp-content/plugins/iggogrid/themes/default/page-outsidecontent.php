 <?php
 global $wp_query;
 global $wp;
 $weeks = IggoGrid::getAllWeeks();
 $langId = IggoGrid::getLanguageId();
//  $pageLang=get_bloginfo("language");
 $pageLang = strtolower(substr(get_bloginfo ( 'language' ), 0, 2));
// var_dump($langId);
 $currentType = $GLOBALS['iggoGridListData']['model'];
 $areaData = $GLOBALS['iggoGridListData']['areaData'];
 $typeData = $GLOBALS['iggoGridListData']['typeData'];
 $typesArray = $GLOBALS['iggoGridListData']['typesArray'];

 $allItems = $GLOBALS['iggoGridListData']['items'];
 global $wpdb;
 if($currentType == 'activity'){
 	$typeData = array();
 	foreach ((array)$allItems as $item){
 		if(empty($item['type'])){
 			continue;
 		}
 		$typeData[$item['type']] = $item['type'];
 	}
 }
 //
//  if($currentType == 'activity'){
//  	$typeString = implode("','", $typesArray);
//  	$typeString = "('".$typeString."')";
//  	$activeTypeSql = "SELECT DISTINCT type FROM iggogrid_item_info WHERE type_code in {$typeString} and lang_id = {$langId} and price > 0";
//  	echo $activeTypeSql;
// //  	$allowTypes = array();
//  	foreach( $wpdb->get_results($activeTypeSql) as $key => $row) {
//  		$row = (array)$row;
//  		if(empty($row['type'])){continue;}
//  		$typeData[$row['type']] = $row['type'];
//  	}
// //  	$allowTypes = array_unique($allowTypes);
//  }
//  print_r($typeData);
//  get_ID_by_slug

 $request = explode("/",$wp->request);

 if($langId != 1){
 	 unset($request[0]);
 	 $request = array_values($request);
// 	 reset($request);
 }

 $request[0] = $currentType;
//  if($langId != 1){
//  	unset($request[0]);
//  	$request = array_values($request);
//  	if($request[0] == 'activities'){
//  		$request[0] = 'activity';
//  	}
//  	if($request[0] == 'food-and-dining'){
//  		$request[0] = 'restaurant';
//  	}
//  	if($langId == 5){
//  		$request[0] = IggoGrid::__lang($request[0]);
// //  		if($request[0] == 'accommodation'){
// //  			$request[0] = 'activity';
// //  		}
//  	}
//  }

 $weekItemIds = array();
 if(IggoGrid::checkShowWeekselector($request)){
 	//get current week items id
 	$weekSql = "SELECT DISTINCT item_id FROM iggogrid_activity_availability WHERE day BETWEEN '{$weeks[0][0]}' AND '{$weeks[0][1]}' and free > 0";
//  	echo $weekSql;
 	foreach( $wpdb->get_results($weekSql) as $key => $row) {
 		array_push($weekItemIds, (int)$row->item_id);
//  		$weekItemIds[$row] = (array)$row;
 	}
 	$weekItemIds = array_unique($weekItemIds);
 }

 $area = isset($request[2])?$request[2]:'';
 $type = isset($request[1])?$request[1]:'';
 if($wp_query->get('area') || $wp_query->get('type')){
 	get_header();
 	$area = $wp_query->get('area')?$wp_query->get('area'):$area;
 	$type = $wp_query->get('type')?$wp_query->get('type'):$type;
 }
 if(!$wp_query->get('area') && !$wp_query->get('type')){
 	get_header();
 }
// echo $area;
// print_r($request);
 $pageIdArray = array(
 		'en'=>array(
 				'accommodation'=>1008,
 				'activity'=>816,
 				'restaurant'=>1033,
 				''
 		),
 		'ru'=>array(
 				'accommodation'=>1010,
 				'activity'=>818,
 				'restaurant'=>1035,
 		)
 );
 $pageId = null;
 if(isset($request[1])){
 	$pageId = IggoGrid::get_ID_by_slug($request[0]);
 }

$naviType = $request[0];

$urlFlag = '';
$smallTitle = "";
switch ($naviType){
	case 'activity':
		$pageId = '13';
        $smallTitle = "Activities";
		break;
	case 'accommodation':
		$pageId = '9';
		$smallTitle = "Accommodation";
		break;
	case 'restaurant':
		$pageId = '11';
		$smallTitle = "Restaurant";
		break;
	default:
}

if($langId == 3){
		$urlFlag = '/en';
		$pageId = $pageIdArray['en'][$currentType];
}elseif ($langId == 5){
		$pageId = $pageIdArray['ru'][$currentType];
		$urlFlag = '/ru';
}
// }
// var_dump($request);
// var_dump($pageId);
//die;
//  print_r($GLOBALS['iggoGridListData']);die;
 ?>
 <style type="text/css">
 .spinner-loading {
background: url('<?php echo get_site_url()?>/wp-admin/images/loading.gif') no-repeat;
background-size: 16px 16px;
display: none;
/* float: right; */
opacity: .7;
filter: alpha(opacity=70);
width: 16px;
height: 16px;
margin: 5px 5px 0;
}

 </style>
 <link type="text/css" href="<?php echo plugins_url('iggogrid/css/datepicker3.css')?>" rel="stylesheet" />
<?php if(IggoGrid::checkShowList($request)): ?>
    <section id="pageintro">
        <div class="container">
            <div class="row">

                <!-- Get booking block -->
                <?php include_once(TEMPLATEPATH . '/bookingblock.php'); ?>

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

				
                <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
                <?php if(IggoGrid::checkShowFilter($request)):?>
                    <div class="row">
                        <div class="col-xs-12">
                            <h1>
                            <?php
                            if($naviType == "activity"){
                                _e("Activities", "iggo");
                            }else if($naviType == "accommodation"){
                                _e("Accommodation", "iggo");
                            }else if($naviType == "restaurant"){
                                _e("Restaurant", "iggo");
                            }
                            ?>
                            <?php //_e($smallTitle, "iggo"); ?></h1>
                        </div>
                    </div>
                    <div class="row filters">

                        <div class="col-xs-12">
                            <label><?php _e('Area','iggo'); ?>:</label>
                            <select id="iggo_area" name="iggo_area">
                            	<option value=""><?php _e('All','iggo'); ?></option>
                            <?php foreach ((array)$areaData as $data): ?>
                            	<?php if($area && $area == IggoGrid::replaceUrlStr(IggoGrid::__lang($data))):?>
                            		<option value="<?php echo $data?>" selected = "selected"><?php echo $data?></option>
                            	<?php else:?>
                            		<option value="<?php echo $data?>"><?php echo $data?></option>
                            	<?php endif;?>

                            <?php endforeach;?>
                            </select>
                        </div>
						<?php if($naviType == 'accommodation'):?>
						 <div class="col-xs-12">
	                            <label><?php _e('Type of accommodation','iggo'); ?>:</label>
	                            <select id="iggo_type" name="iggo_type">
	                            	<option value=""><?php _e('All','iggo'); ?></option>
	                              <?php foreach ((array)$typeData as $data):?>
		                              <?php if($type && $type == IggoGrid::replaceUrlStr(IggoGrid::__lang($data))):?>
		                            		<option value="<?php echo $data?>" selected = "selected"><?php echo $data?></option>
		                            	<?php else:?>
		                            		<option value="<?php echo $data?>"><?php echo $data?></option>
		                            	<?php endif;?>
	                            <?php endforeach;?>
	                            </select>
	                        </div>
							<div class="col-xs-6">
	                            <label><?php _e('Persons','iggo');?>:</label>
	                            <select id="iggo_person" name="iggo_person">
	                            	<option value="0"><?php _e( 'All', 'iggo' ); ?></option>
	                                <option value="1">1</option>
	                                <option value="2">2</option>
	                                <option value="3">3</option>
	                                <option value="4">4</option>
	                                <option value="5">5</option>
	                                <option value="6">6</option>
	                                <option value="7">7</option>
	                                <option value="8">8</option>
	                                <option value="9">9</option>
	                                <option value="10"><?php _e( '10 or more', 'iggo' ); ?></option>
	                            </select>
	                        </div>

	                        <div class="col-xs-6">
	                            <label><?php _e('Bedrooms','iggo');?>:</label>
	                            <select id="iggo_rooms" name="iggo_rooms">
	                            	<option value="0"><?php _e( 'All', 'iggo' ); ?></option>
	                                 <option value="1">1</option>
	                                <option value="2">2</option>
	                                <option value="3">3</option>
	                                <option value="4">4</option>
	                                <option value="5">5</option>
	                                <option value="6"><?php _e( '6 or more', 'iggo' ); ?></option>
	                            </select>
	                        </div>
	                        
	                       <div class="col-xs-6">
	                            <label><?php _e('Distance','iggo');?>:</label>
	                            <select id="iggo_distance" name="iggo_distance">
	                            	<option value=""><?php _e('All','iggo');?></option>
	                            	<option value="1"><?php _e('Below','iggo');?> 1 km</option>
	                                 <option value="2"><?php _e('Below','iggo');?> 2 km</option>
	                                 <option value="3"><?php _e('Below','iggo');?> 3 km</option>
	                                 <option value="5"><?php _e('Below','iggo');?> 5 km</option>
	                                 <option value="8"><?php _e('Below','iggo');?> 8 km</option>
	                                 <option value="10"><?php _e('Below','iggo');?> 10 km</option>
	                            </select>
	                        </div>
						<?php endif;?>
						<?php if($naviType == 'activity'):?>
							 <div class="col-xs-12">
	                            <label><?php _e('Activities','iggo');?>:</label>
	                            <select id="iggo_type" name="iggo_type">
	                            	<option value=""><?php _e( 'All', 'iggo' ); ?></option>
	                              <?php foreach ((array)$typeData as $data):?>
		                              <?php if($type && $type == IggoGrid::replaceUrlStr(IggoGrid::__lang($data))):?>
		                            		<option value="<?php echo $data?>" selected = "selected"><?php echo $data?></option>
		                            	<?php else:?>
		                            		<option value="<?php echo $data?>"><?php echo $data?></option>
		                            	<?php endif;?>
	                            <?php endforeach;?>
	                            </select>
	                        </div>

						<!-- 
	                        <div class="col-xs-6">
	                            <label><?php _e('Distance','iggo');?>:</label>
 	                            <select id="iggo_distance" name="iggo_distance">
	                            	<option value=""><?php _e('All','iggo');?></option>
	                            	<option value="1"><?php _e('Below','iggo');?> 1 km</option>
	                                 <option value="2"><?php _e('Below','iggo');?> 2 km</option>
	                                 <option value="3"><?php _e('Below','iggo');?> 3 km</option>
	                                 <option value="5"><?php _e('Below','iggo');?> 5 km</option>
	                                 <option value="8"><?php _e('Below','iggo');?> 8 km</option>
	                                 <option value="10"><?php _e('Below','iggo');?> 10 km</option>
	                                 <option value="20"><?php _e('Below','iggo');?> 20 km</option>
  		                        </select>
        	               </div>
        	            -->


						<?php endif;?>
						<?php if(IggoGrid::checkShowFilterCheckbox($request)):?>
						<div class="col-xs-12 advoptions">
                            <input id="advanced-filter" type="checkbox" class="iggo_show_options">
                            <label for="advanced-filter" class="advanced"><?php _e('Narrow your search','iggo');?></label>
                        </div>
                        <?php if($naviType == 'accommodation'):?>
	                     	<div class="col-xs-6 iggo_show_options_electem"  style="display:none">
	                            <label><?php _e('Internet Access','iggo');?>:</label>
	                            <select id="iggo_internet" name="iggo_internet">
	                            	<option value=""><?php _e('All','iggo');?></option>
	                            	<option value="0"><?php _e('No','iggo');?></option>
	                                <option value="1"><?php _e('Yes','iggo');?></option>
	                            </select>
	                        </div>
	                        <div class="col-xs-6 iggo_show_options_electem" style="display:none">
	                            <label><?php _e('Pets allowed','iggo');?>:</label>

	                            <select id="iggo_animals" name="iggo_animals">
	                            	<option value=""><?php _e('All','iggo');?></option>
	                            	<option value="0"><?php _e('No','iggo');?></option>
	                                 <option value="1"><?php _e('Yes','iggo');?></option>
	                            </select>
	                        </div>

	                        <div class="col-xs-6 iggo_show_options_electem" style="display:none">
	                            <label><?php _e('Washing Machine','iggo');?>:</label>
	                            <select id="iggo_washing" name="iggo_washing">
	                            	<option value=""><?php _e('All','iggo');?></option>
	                            	<option value="0"><?php _e('No','iggo');?></option>
	                                 <option value="1"><?php _e('Yes','iggo');?></option>
	                            </select>
	                        </div>
                        <?php endif;?>

                         <div class="col-xs-6 iggo_show_options_electem" style="display:none">
                            <label><?php _e('Customer Profile','iggo');?>:</label>
                            <select id="iggo_acctype" name="iggo_acctype">
                            	<option value=""><?php _e('All','iggo');?></option>
                            	 <option value="incentive"><?php echo IggoGrid::__lang('Incentive');//_e('Incentive','iggo');?></option>
                                 <option value="family"><?php echo IggoGrid::__lang('Family');//_e('Family','iggo');?></option>
                                 <option value="nature_lover"><?php echo IggoGrid::__lang('Nature_Lover');//_e('Nature_Lover','iggo');?></option>
                                 <option value="company"><?php echo IggoGrid::__lang('Company');//_e('Company','iggo');?></option>

                            </select>
                         </div>
						<?php endif;?>

                        <div class="col-xs-12">
                            <button class="iggo-button iggo_filter_submit" >
                                <span class="title"><?php _e('Search','iggo');?></span>
                            </button>
                        </div>
                    </div>
				<?php endif;?>
                </div>
				
				<div id="post-<?php the_ID(); ?>" class="col-xs-12 col-sm-8 col-md-9 col-lg-9 content">

                    <!-- Global intro texts for the six top level pages -->
                    <div class="row introtext">
                        <div class="col-xs-12">
                            <?php
                            ob_start();
                            the_title();
                            $pageTitle = ob_get_clean();
                            //is_page("9")
                            ?>
                            <!-- Majoitus -->
                            <?php if ($pageId == 9) { ?>
                                <?php the_field('majoitus_osion_teksti', 'option'); ?>

                            <!-- Majoitus EN -->
                            <?php } elseif ($pageId == 1008 ) { ?>
                                <?php the_field('majoitus_osion_teksti_en', 'option'); ?>

                            <!-- Majoitus RU -->
                            <?php } elseif ($pageId == 1010) { ?>
                                <?php the_field('majoitus_osion_teksti_ru', 'option'); ?>



                            <!-- Ruokailu -->
                            <?php } elseif ($pageId == 11) { ?>
                                <?php the_field('ruokailu_osion_teksti', 'option'); ?>

                            <!-- Ruokailu EN -->
                            <?php } elseif ($pageId == 1033) { ?>
                                <?php the_field('ruokailu_osion_teksti_en', 'option'); ?>

                            <!-- Ruokailu RU -->
                            <?php } elseif ($pageId == 1035) { ?>
                                <?php the_field('ruokailu_osion_teksti_ru', 'option'); ?>



                            <!-- Aktiviteetit -->
                            <?php } elseif ($pageId == 13) { ?>
                                <?php the_field('aktiviteetit_osion_teksti', 'option'); ?>

                            <!-- Aktiviteetit EN -->
                            <?php } elseif ($pageId == 816) { ?>
                                <?php the_field('aktiviteetit_osion_teksti_en', 'option'); ?>

                            <!-- Aktiviteetit RU -->
                            <?php } elseif ($pageId == 818) { ?>
                                <?php the_field('aktiviteetit_osion_teksti_ru', 'option'); ?>



                            <!-- Matkustaminen -->
                            <?php } elseif ($pageId == 15) { ?>
                                <?php the_field('matkustaminen_osion_teksti', 'option'); ?>

                            <!-- Matkustaminen EN -->
                            <?php } elseif ($pageId == 890) { ?>
                                <?php the_field('matkustaminen_osion_teksti_en', 'option'); ?>

                            <!-- Matkustaminen RU -->
                            <?php } elseif ($pageId == 901) { ?>
                                <?php the_field('matkustaminen_osion_teksti_ru', 'option'); ?>



                            <!-- Tietoa meistä -->
                            <?php } elseif ($pageId ==17) { ?>
                                <?php the_field('tietoa_meista_osion_teksti', 'option'); ?>

                            <!-- Tietoa meistä EN -->
                            <?php } elseif ($pageId == 760) { ?>
                                <?php the_field('tietoa_meista_osion_teksti_en', 'option'); ?>

                            <!-- Tietoa meistä RU -->
                            <?php } elseif ($pageId == 576) { ?>
                                <?php the_field('tietoa_meista_osion_teksti_ru', 'option'); ?>



                            <!-- Tietoa alueesta -->
                            <?php } elseif ($pageId == 19) { ?>
                                <?php the_field('tietoa_alueesta_osion_teksti', 'option'); ?>

                            <!-- Tietoa alueesta EN -->
                            <?php } elseif ($pageId == 858) { ?>
                                <?php the_field('tietoa_alueesta_osion_teksti_en', 'option'); ?>

                            <!-- Tietoa alueesta RU -->
                            <?php } elseif ($pageId == 860) { ?>
                                <?php the_field('tietoa_alueesta_osion_teksti_ru', 'option'); ?>



                            <!-- For everything else display normal page title as it won't be the same as the root level title in sidebar -->
                            <?php } else if(!empty($pageTitle)){ ?>
                                <h1><?php the_title(); ?></h1>

                            <?php } ?>

                        </div>
                    </div>

                    <!--
                        Search criteria text.
                        Displays selected criteria and only shows when viewing the results of some search criteria.
                    -->

                    <!-- Normal content -->
                    <div class="row">
                        <div class="col-xs-12">
					       <?php the_content(); ?>
                        </div>
                    </div>

                    <div class="row search-criteria">
                        <div class="col-xs-12">
                            <h2><?php _e('Displaying results criteria','iggo');?>:</h2>
                            <ul>
                            </ul>
                        </div>
                    </div>

                    <!-- Aukioloajat (repeater) (32 is FI, 1154 is EN, 1156 is RU) -->
                    <?php if (is_page("32") || is_page("1154") || is_page("1156")) { ?>
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 timetable">
                                <h2><?php _e( 'Reception open', 'iggo' ); ?></h2>
                                <?php if( have_rows('normaalit_aukioloajat') ): ?>
                                    <ul>

                                    <?php while( have_rows('normaalit_aukioloajat') ): the_row();
                                        // vars
                                        $date = get_sub_field('paiva');
                                        $time = get_sub_field('aika');
                                        ?>

                                        <li class="row">
                                            <span class="col-xs-12"><hr></span>
                                            <span class="col-xs-6 date"><?php echo $date; ?></span>
                                            <span class="col-xs-6 time"><?php echo $time; ?></span>
                                        </li>
                                    <?php endwhile; ?>

                                    </ul>
                                <?php endif; ?>
                            </div>

                            <div class="col-xs-12 col-sm-6 timetable">
                                <h2><?php _e( 'Exceptional opening hours', 'iggo' ); ?></h2>
                                <?php if( have_rows('poikkeavat_aukioloajat') ): ?>
                                    <ul>

                                    <?php while( have_rows('poikkeavat_aukioloajat') ): the_row();
                                        // vars
                                        $date = get_sub_field('paiva');
                                        $time = get_sub_field('aika');
                                        ?>

                                        <li class="row">
                                            <span class="col-xs-12"><hr></span>
                                            <span class="col-xs-6 date"><?php echo $date; ?></span>
                                            <span class="col-xs-6 time"><?php echo $time; ?></span>
                                        </li>
                                    <?php endwhile; ?>

                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>


                    <?php } elseif (is_page("9999")) { ?>
                    <?php } else { ?>
                    <?php } ?>

                  <?php if(IggoGrid::checkShowWeekselector($request)):?>
	                    <div class="row">
	                        <div class="col-xs-12">
                                <a href="#" class="weekpicker">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                    <span class="title"><?php _e( 'Select week', 'iggo' ); ?></span>
                                </a>
                                <input type="hidden" id="datepicker_hidden" value="<?php echo date("Y-m-d") ?>" />

	                            <div class="weekselector">
	                                <a href="#" class="prev disabled"><span class="glyphicon glyphicon-chevron-left"></span></a>
	                                <div class="current" startdate="<?php echo $weeks[0][0]?>" enddate="<?php echo $weeks[0][1]?>">
	                                    <?php
	                                    function makeFinDate($date){
	                                        $time = strtotime($date." 00:00:00");
                                            return date("d.m.Y", $time);
	                                    }
	                                    if($pageLang == "fi"){
	                                        $startDateLabel = makeFinDate($weeks[0][0]);
                                            $endDateLabel = makeFinDate($weeks[0][1]);
	                                    }else{
	                                        $startDateLabel = $weeks[0][0];
                                            $endDateLabel = $weeks[0][1];
	                                    }
	                                    ?>
	                                    <span class="begindate"><?php echo $startDateLabel ?></span><span class="divider">-</span><span class="enddate"><?php echo $endDateLabel ?></span>
	                                </div>
	                                <a href="#" class="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
	                            </div>
	                        </div>
	                    </div>
					<?php endif;?>

                    <div class="spinner-loading"></div>
                    <div class="row data_list">
                      <?php foreach ($allItems as $key=>$data):?>
                      <?php
                   
                      $internet = '';
                      $animals = '';
                      $washing = '';
                      $incentive = '';
                      $family = '';
                      $traveller = '';
                      $nature_lover = '';
                      $company = '';
                      $distance = '';
                      if(!empty($data['acc_properties'])){
                      	$acc_properties = unserialize($data['acc_properties']);
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
                      			}elseif ($item['Name'] == 'Incentive'){
									if($item['Value'] == 1){
										$incentive = 'incentive';
									}
                      			}elseif ($item['Name'] == 'Family'){
                      				if($item['Value'] == 1){
										$family = 'family';
									}
                      			}elseif ($item['Name'] == 'Nature_Lover'){
                      				if($item['Value'] == 1){
										$nature_lover = 'nature_lover';
									}
                      			}elseif ($item['Name'] == 'Company'){
                      				if($item['Value'] == 1){
										$company = 'company';
									}
                      			}elseif ($item['Name'] == 'Traveller'){
                      				if($item['Value'] == 1){
										$traveller = 'traveller';
									}
                      			}elseif ($item['Name'] == 'Distance'){
									$val = str_replace(',', '.', trim($item['Value']));
                      				$distance = (int)$val;
                      			}else{
                      				continue;
                      			}
                      		}
                      	}
                      }
                      if($traveller == '' && $naviType != 'restaurant' && $type != 'kayntikohteet'){
							continue;
					   }
// 					   var_dump($traveller);
//                       print_r($data);die;
                      	$images = unserialize($data['images']);
                      	if(!empty($images) && isset($images[0])){
							$image = $images[0];
						}else{
							$image = get_stylesheet_directory_uri().'/images/189x135_thumbnail1.jpg';
						}
						if($data['admin_added'] == 1){
							$image = get_site_url().$image;
						}
						if($naviType == 'activity'){
							$where = "where item_id = {$data['item_id']} and free > 0";
							$sql = "SELECT day FROM iggogrid_activity_availability $where";

							$itemDays = array();
							foreach( $wpdb->get_results($sql) as $key => $row) {
								$row = (array)$row;
								$itemDays[] = date('Y-m-d', strtotime($row['day']));
							}
							$itemDays = implode(',', $itemDays);
						}
						$headerPrice = false;
						if($naviType == 'activity') {
								$pricesArray = $data['price_'.$pageLang];
								$pricesArray = unserialize($pricesArray);
								$firstHeader = reset($pricesArray);
								$headerPrice = $firstHeader['header'];
						}
						?>
						<?php //if($traveller == 'traveller'): ?>
                       	<div class="col-xs-12 single-item" style="display: none">
                       	 <hr>
                            <div class="row">
                                <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3 image">

                                    <a href="<?php echo site_url().$urlFlag.'/'.IggoGrid::__lang($naviType).'/'.IggoGrid::__lang('kohde').'/'.$data['url_key']?>">
                                        <div class="overlay"></div>
                                        <img alt="" src="<?php echo $image ?>" />
                                    </a>
                                </div>
                                <div class="col-xs-12 col-sm-8 col-md-9 col-lg-9 description">
                                	<?php if($naviType != 'restaurant'):?>
                                		<div class="item-price">
                                			<?php if($data['price'] > 0):?>
                                				<span class="price"><?php echo $data['price'];?></span>
                                				<span class="euro-mark">€</span>
                                				 <span class="type">
		                                			<?php 
		                                			if(!$headerPrice){
		                                			    _e( '/week', 'iggo' );
		                                			}else{
		                                			    echo $headerPrice;
		                                			}
		                                			?></span>
                                			<?php endif;?>
                                		</div>
                                    <?php endif;?>
                                    <a href="<?php echo site_url().$urlFlag.'/'.IggoGrid::__lang($naviType).'/'.IggoGrid::__lang('kohde').'/'.$data['url_key']?>">
                                    	<?php if($naviType == 'activity'): ?>
                                    		<h3><?php echo $data['product_name']?></h3>
				                		<?php elseif($naviType == 'accommodation'):?>
				                			<h3><?php echo ucwords($data['destination_name'])?></h3>
				                		<?php else:?>
				                			<h3><?php echo $data['destination_name']?></h3>
				                		<?php endif;?>

                                    </a>
                                    <?php if($naviType == 'activity'):?>
                                    	<a href="#" class="activity-type" data="<?php echo $data['type']?>"><span><?php echo $data['type']?></span></a>
                                    <?php endif;?>

                                    <p><?php echo $data['headline']?></p>
                                    <!-- iggo-button
                                        <a href="" class="readmore">
                                        <span class="title">Lue lisää</span>
                                    </a>

                                    -->

                                </div>
                            </div>
                            <input value="<?php echo $data['area']?>" type="hidden" name="item_area" class="item_area"/>
                            <input value="<?php echo $data['item_id']?>" type="hidden" name="item_id" class="item_id"/>
                            <input value="<?php echo $data['type']?>" type="hidden" name="item_type" class="item_type"/>
                            <input value="<?php echo $data['person']?>" type="hidden" name="item_person" class="item_person"/>
                            <input value="<?php echo $data['rooms']?>" type="hidden" name="item_rooms" class="item_rooms"/>
                             <input value="<?php echo $internet?>" type="hidden" name="item_internet" class="item_internet"/>
                              <input value="<?php echo $animals?>" type="hidden" name="item_animals" class="item_animals"/>
                               <input value="<?php echo $washing?>" type="hidden" name="item_washing" class="item_washing"/>

                                 <input value="<?php echo $incentive?>" type="hidden" name="item_incentive" class="item_incentive"/>
                              <input value="<?php echo $family?>" type="hidden" name="item_family" class="item_family"/>
                               <input value="<?php echo $nature_lover?>" type="hidden" name="item_nature_lover" class="item_nature_lover"/>
                                 <input value="<?php echo $company?>" type="hidden" name="item_company" class="item_company"/>
                               <input value="<?php echo $distance?>" type="hidden" name="item_distance" class="item_distance"/>

                               <?php if($naviType == 'activity'):?>
                                <input value="<?php echo $itemDays?>" type="hidden" name="item_days" class="item_days"/>
                               <?php endif;?>


                        </div>
                       <?php //endif;?>
                      <?php endforeach;?>

                    </div>

                </div>

            </div>

        </div>
    </section>
<script type="text/javascript">
$(document).ready(function(){
	var weeks = <?php echo json_encode($weeks)?>;
	var weekItemIds = <?php echo json_encode($weekItemIds)?>;
	var data_list = $(".data_list").html();
	if($(".weekselector").length){
		setDataByWeek(weeks[0],weekItemIds);
	}
	$(".list_html_hidden").html(data_list);
	<?php if(IggoGrid::checkShowFilter($request)):?>
		var filter = {
				'area' : $("#iggo_area").val(),
				'type' : $("#iggo_type").val(),
				'person' : $("#iggo_person").val(),
				'rooms' : $("#iggo_rooms").val(),
				'internet' : $("#iggo_internet").val(),
				'animals' : $("#iggo_animals").val(),
				'washing' : $("#iggo_washing").val(),
				'acctype' : $("#iggo_acctype").val(),
				'distance' : $("#iggo_distance").val(),
			}
	<?php else:?>
		var filter = {
				'area' : '<?php echo  IggoGrid::getFilterData($areaData,$area)?>',
				'type' :  '<?php echo  IggoGrid::getFilterData($areaData,$type)?>',
				'person' : 0,
				'rooms' : 0,
				'internet' : '',
				'animals' : '',
				'washing' : '',
				'acctype' : '',
				'distance' : '',
			}
	<?php endif;?>
	if($(".iggo_show_options").is(':checked')) {
		$(".iggo_show_options_electem").show();
	}else{
		$(".iggo_show_options_electem").hide();
	}
	filter = checkFilter(filter);
	filterSingleItem(filter);
	$(document).on("click",".iggo_filter_submit",function(){
		var filter = {
					'area' : $("#iggo_area").val(),
					'type' : $("#iggo_type").val(),
					'person' : $("#iggo_person").val(),
					'rooms' : $("#iggo_rooms").val(),
					'internet' : $("#iggo_internet").val(),
					'animals' : $("#iggo_animals").val(),
					'washing' : $("#iggo_washing").val(),
					'acctype' : $("#iggo_acctype").val(),
					'distance' : $("#iggo_distance").val(),
				}
		filter = checkFilter(filter);
		filterSingleItem(filter);
		return false;
	})

	$(document).on("click",".activity-type",function(){
		var type = $(this).attr('data');
		$("#iggo_type").val(type);
		var filter = {
					'type' : type
				}
		filter = checkFilter(filter);
		filterSingleItem(filter);
		return false;
	})

	$(".iggo_show_options").change(function(){
		if($(this).is(':checked')) {
			$(".iggo_show_options_electem").show();
		}else{
			$(".iggo_show_options_electem").hide();
		}
	});

	$(document).on("click",".weekselector .prev",function(){
		var currentObj = $(this).parent().find('.current');

		var startdate = currentObj.attr('startdate'),
				enddate = currentObj.attr('enddate'),
				currentkey = 0;
		$.each(weeks,function(i,item){
			if(item[0] == startdate && item[1] == enddate){
				currentkey = i;
				return;
			}
		});
		if(currentkey == 0){
			return false;
		}
		$(".data_list").empty();
		$(".spinner-loading").show();
		var prevDate = weeks[currentkey - 1];
		currentObj.attr('startdate',prevDate[0]).attr('enddate',prevDate[1]);

		var pageLang = '<?php echo $pageLang; ?>';
        if(pageLang == "fi"){
            var startDateLabel = makeFinDate(prevDate[0]);
            var endDateLabel = makeFinDate(prevDate[1]);
        }else{
            var startDateLabel = prevDate[0];
            var endDateLabel = prevDate[1];
        }
        currentObj.find('span.begindate').text(startDateLabel);
        currentObj.find('span.enddate').text(endDateLabel);
		getWeekselector(prevDate);
		checkPrevNextStatus(weeks);
		$("#datepicker_hidden").val(prevDate[0]);
		return false;
	})
	$(document).on("click",".weekselector .next",function(){
		var currentObj = $(this).parent().find('.current');
		var startdate = currentObj.attr('startdate'),
				enddate = currentObj.attr('enddate'),
				currentkey = 0,
				weekLength = weeks.length;
		$.each(weeks,function(i,item){
			if(item[0] == startdate && item[1] == enddate){
				currentkey = i;
				return;
			}
		});
		if(currentkey == weekLength-1){
			return false;
		}
		$(".data_list").empty();
		$(".spinner-loading").show();
		var nextDate = weeks[currentkey + 1];
		currentObj.attr('startdate',nextDate[0]).attr('enddate',nextDate[1]);

		var pageLang = '<?php echo $pageLang; ?>';
        if(pageLang == "fi"){
            var startDateLabel = makeFinDate(nextDate[0]);
            var endDateLabel = makeFinDate(nextDate[1]);
        }else{
            var startDateLabel = nextDate[0];
            var endDateLabel = nextDate[1];
        }
		currentObj.find('span.begindate').text(startDateLabel);
        currentObj.find('span.enddate').text(endDateLabel);
		//currentObj.find('span').text(nextDate[0] + ' - '+nextDate[1]);
		getWeekselector(nextDate);
		checkPrevNextStatus(weeks);
		$("#datepicker_hidden").val(nextDate[0]);
		return false;
	})
// 	$(document).on("click",".weekselector .current",function(){

// 	})

    var $dp = $('#datepicker_hidden').datepicker({
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: "yy-mm-dd",
        minDate: weeks[0][0],
        maxDate: weeks[weeks.length -1 ][1],
        showWeek: true,
        firstDay: 1,
        onSelect: function(dateText, inst){
            var format = 'yyyy-mm-dd';
            var date=new Date(inst["selectedYear"], inst["selectedMonth"], inst["selectedDay"]);
            var newDate =getWeekArea(date);
            /*var startDate = formatDate(newDate.mondy, format),
            endDate=formatDate(newDate.sundy, format),
            formatted = startDate+' - '+endDate;*/

            var startMonth = newDate.mondy.getMonth()*1 + 1;
            var endMonth = newDate.sundy.getMonth()*1 + 1;

            var startDate = makeSlimDate(newDate.mondy.getFullYear() + "-" + startMonth + "-" + newDate.mondy.getDate());
            var endDate = makeSlimDate(newDate.sundy.getFullYear() + "-" + endMonth + "-" + newDate.sundy.getDate());

            var pageLang = '<?php echo $pageLang; ?>';
            if(pageLang == "fi"){
                var startDateLabel = makeFinDate(startDate);
                var endDateLabel = makeFinDate(endDate);
            }else{
                var startDateLabel = startDate;
                var endDateLabel = endDate;
            }

            // $(this).attr("startdate",startDate).attr('enddate',endDate).val(formatted);
            $(".weekselector .current").attr("startdate",startDate).attr('enddate',endDate);
            $(".weekselector .current span.begindate").text(startDateLabel);
            $(".weekselector .current span.enddate").text(endDateLabel);
            $(".spinner-loading").show();
            getWeekselector([startDate,endDate]);
            checkPrevNextStatus(weeks);
        },
        beforeShow: function(input, inst){
            $(".ui-datepicker a.ui-state-active").parents("tr").find("a").addClass("ui-state-active");
            $(".ui-datepicker a.ui-state-highlight").parents("tr").find("a").addClass("ui-state-highlight");
        }
    });

    $(document).on("mouseover", ".ui-datepicker a.ui-state-default", function(){
        $(this).parents("tr").find("a").addClass("ui-state-hover");
    }).on("mouseoout", ".ui-datepicker a.ui-state-default", function(){
        $(this).parents("tr").find("a").removeClass("ui-state-hover");
    });

    $(document).on("click",".weekpicker",function(){
        if ($dp.datepicker('widget').is(':hidden')) {
            $dp.show().datepicker('show').hide();
            var offset = $(this).offset();
            var iconHeight = $(this).height();
            var left = offset["left"];
            var top = offset["top"] + iconHeight;

            $(".ui-datepicker").css("z-index",999999);
            $(".ui-datepicker").css("left",left);
            $(".ui-datepicker").css("top",top);
            $(".ui-datepicker a.ui-state-active").parents("tr").find("a").addClass("ui-state-active");
            $(".ui-datepicker a.ui-state-highlight").parents("tr").find("a").addClass("ui-state-highlight");
        } else {
            $dp.hide();
        }

        return false;
    });

	$(document).on("mouseover",".datepicker-days .table-condensed td.day",function(){
		if($(this).hasClass('disabled') ){
			return false;
		}
		$(this).addClass("active").siblings().addClass("active");
        return false;
	});
	$(document).on("mouseout",".datepicker-days .table-condensed td.day",function(){
		if($(this).hasClass('disabled') ){
			return false;
		}
		$(".datepicker-days .table-condensed td").each(function(){
			$(this).removeClass("active");
		})
        return false;
	});


})

function checkPrevNextStatus(weeks){
    var startDate = makeSlimDate($(".weekselector .current").attr("startdate"));
    var endDate = makeSlimDate($(".weekselector .current").attr('enddate'));

    var minDate = makeSlimDate(weeks[0][0]);
    var maxDate = makeSlimDate(weeks[weeks.length -1 ][1]);

    if(startDate == minDate || endDate == maxDate){
        if(startDate == minDate){
            $(".weekselector .prev").addClass("disabled");
            $(".weekselector .next").removeClass("disabled");
        }else{
            $(".weekselector .next").addClass("disabled");
            $(".weekselector .prev").removeClass("disabled");
        }
    }else{
        $(".weekselector .prev").removeClass("disabled");
        $(".weekselector .next").removeClass("disabled");
    }
}

function makeSlimDate(date){
    var parts = date.split("-");
    var newMonth = parts[1];
    if(newMonth.length == 1){
        newMonth = "0"+newMonth;
    }
    var newDay = parts[2];
    if(newDay.length == 1){
        newDay = "0"+newDay;
    }

    var newDate = parts[0]+"-"+newMonth+"-"+newDay;
    return newDate;
}

function makeFinDate(date){
    var parts = date.split("-");
    var newDate = parts[2]+"."+parts[1]+"."+parts[0];
    return newDate;
}

function formatDate(date,format){
        var paddNum = function(num){
          num += "";
          return num.replace(/^(\d)$/,"0$1");
        }
        var val = {
           yyyy : date.getFullYear(),
          yy : date.getFullYear().toString().substring(2),
          M  : date.getMonth() + 1,
          m: date.getUTCMonth() + 1,
          MM : paddNum(date.getMonth() + 1),
          d  : date.getDate() ,
          dd : paddNum(date.getDate()),
        }
        val.dd = (val.d < 10 ? '0' : '') + val.d;
		val.mm = (val.m < 10 ? '0' : '') + val.m;
        format || (format = "yyyy-MM-dd hh:mm:ss");
        return format.replace(/([a-z])(\1)*/ig,function(m){return val[m];});
      }
	function getWeekArea(date) {
        if (!(date instanceof Date)) {
            date = new Date();
        }
        var day = date.getDay(), _date = date.getDate(),
            mondy = _date - (day == 0 ? 7 : day) + 1,
            t1 = new Date(date.getTime()),
            t2 = new Date(date.getTime());
        t1.setDate(mondy);
        t2.setDate(mondy + 6);
        return { mondy: t1, sundy: t2 };
    }
	function checkFilter(filter){
		var flag = 1;
		var searchHtml = '';
		$.each(filter,function(i,item){
			if($("#iggo_"+i).length == 0){
				delete filter[i];
			}
		})
		//console.log(filter);
		$.each(filter,function(i,item){
			if(!(item == '' || item == 0)){
				flag  = flag * 0;
				var label = $("#iggo_"+i).prev().text();
				var item =  $("#iggo_"+i).find("option:selected").text();
 				//console.log(item);
				if(i == 'person'){
					item += ' hlö';
				}
				if(i == 'rooms'){
					item += ' kpl';
				}

//				if(i == 'distance'){
//                    item = 'Alle '+item+' km';
//				}
				searchHtml += '<li><span class="glyphicon glyphicon-ok" ></span><span class="title">'+label+'</span><span class="value">'+item+'</span></li>';
			}
		})
// 		console.log(searchHtml);
		$("#outside .search-criteria ul").html(searchHtml);

		if(flag == 0){
			//has
			$("#outside .introtext").hide();
			$("#outside .search-criteria").show();
		}else{
			$("#outside .introtext").show();
			$("#outside .search-criteria").hide();
		}
		return filter;
		//console.log(flag);
	}

	function filterSingleItem(filter){
		$(".data_list .single-item").hide();
		$('.data_list .single-item').filter(function(index) {
			var flag = 1;
			var obj = this;
// 			console.log(filter);
			$.each(filter,function(i,item){
				//console.log(i);
				var value = $(obj).find(".item_"+i).val();
				if(value != undefined){
					value = value.toLowerCase();
				}
				if(item != undefined){
					item = item.toLowerCase();
				}

				if(i == 'person' || i == 'rooms'){
					value = parseInt(value);
					item = parseInt(item);
					flag = flag * (value >= item);
				}else if(i == 'acctype'){
					if(item != ''){
						value = $(obj).find(".item_"+item).val();
// 						console.log(value);
						flag = flag * (value == item);
					}

				}else if(i == 'distance'){
					if(item != ''){
						value = parseInt(value);
						item = parseInt(item);
						flag = flag * (value <= item);
					}
				}else if(i == 'area'){
					if(item != '' && value != 'all'){
						flag = flag * (value == item);
					}
				}else{
					if(item == ''){

					}else{
						flag = flag * (value == item);
					}
				}
// 				console.log(value);
// 				console.log(item);
// 				console.log(value == item);
// 				console.log(flag);
			});
// 			console.log(flag);
			return flag;
		}).show();
		$(".single-day").each(function(){
			if($(this).find(".empty").length > 0){
				var hiddenNums = $(this).find(".single-item:hidden").length,
				allNums = $(this).find(".single-item").length,
				emptyObj = $(this).find(".empty");
				if(allNums == hiddenNums){
					emptyObj.show();
				}else{
					emptyObj.hide();
				}
			}
		})
	}

	function getWeekRanges(d){
		var weekRange = [];
		for (var i=0;i<7;i++)
		{
			var d1 = Date.parse(d.replace(/-/g,   "/"));
			var date = new Date(d1);
			date = date.valueOf(),
			date = date + i * 24 * 60 * 60 * 1000
			date = new Date(date),
			mymonth = date.getMonth()+1,
			myday =  date.getDate(),
			myyear= date.getFullYear();
			year=(myyear > 200) ? myyear : 1900 + myyear;
			if(mymonth >= 10){mymonth=mymonth;}else{mymonth="0" + mymonth;}
			if(myday >= 10){myday=myday;}else{myday="0" + myday;}
			var outputdate = year + "-" + mymonth + "-" + myday;
			weekRange.push(outputdate);
		}
		return weekRange;
	}

	function weekDayLang(day){
		var lang = {
					1:'<?php echo _e("Monday",'iggo')?>',
					2:'<?php echo _e("Tuesday",'iggo')?>',
					3:'<?php echo _e("Wednesday",'iggo')?>',
					4:'<?php echo _e("Thursday",'iggo')?>',
					5:'<?php echo _e("Friday",'iggo')?>',
					6:'<?php echo _e("Saturday",'iggo')?>',
					7:'<?php echo _e("Sunday",'iggo')?>'
				};
		return lang[day];
	}
	function getYMDTime(){
		date = new Date();
		mymonth = date.getMonth()+1,
		myday =  date.getDate(),
		myyear= date.getFullYear();
		year=(myyear > 200) ? myyear : 1900 + myyear;
		if(mymonth >= 10){mymonth=mymonth;}else{mymonth="0" + mymonth;}
		if(myday >= 10){myday=myday;}else{myday="0" + myday;}
		return year + "-" + mymonth + "-" + myday;
	}
	//weekselector
	function setDataByWeek(week,weekItemIds,listclass){
// 		console.log($('.list_html_hidden').html());
		if(listclass == undefined){
			listclass = 'data_list';
		}
		var d = week[0];
		var weekRange = getWeekRanges(d);
		var html = '',
			nowDate = getYMDTime(),
			nowDate = Date.parse(nowDate.replace(/-/g,   "/")),
			timestamp=new Date(nowDate).getTime();
// 		console.log(timestamp);
		for(week in weekRange){
			var date = weekRange[week],weekDay = parseInt(week)+1;
			var d1 = Date.parse(date.replace(/-/g,   "/"));
			var weekTimestamp=new Date(d1).getTime();
			var rowHtml = '',style='';
// 			console.log(date);
// 			console.log(weekTimestamp);
			if(weekTimestamp < timestamp){
				style = 'style="display:none"';
			}
			date = date.split('-');
			date = date[2]+'.'+date[1];
			html += '<div class="single-day" '+style+'><div class="week-header col-xs-12"><span class="dow-title">'+weekDayLang(weekDay)+' '+date+'</span></div>';
			html += '<div class="current-items">';

			$('.'+listclass+' .single-item').filter(function(index) {
				var value = $(this).find(".item_id").val(),
						days = $(this).find(".item_days").val(),
						date=weekRange[week];
				value = parseInt(value);
				days = days.split(',');
				if( $.inArray(value, weekItemIds) !== -1 && $.inArray(date, days) !== -1){
					rowHtml += '<div class="col-xs-12 single-item">'+$(this).html()+'</div>';
					return false;
				}
			})
			if(rowHtml == ''){
				rowHtml = '<div class="col-xs-12 empty"><?php echo  _e('No activities','iggo');?></div>';
			}else{
				rowHtml += '<div class="col-xs-12 empty" style="display:none"><?php echo  _e('No activities','iggo');?></div>';
			}
			html +=rowHtml;
			html += '</div></div>';
		}



		$(".data_list").html(html);
// 		$('.data_list .single-item').filter(function(index) {
// 			var value = $(this).find(".item_id").val();
// 			value = parseInt(value);
// 			if( $.inArray(value, weekItemIds) !== -1){
// 				return false;
// 			}
// 			return true;
// 		}).remove();
	}
</script>
<?php endif;?>
<?php
if($wp_query->get('area') || $wp_query->get('type')){
	get_footer();
}
if(!$wp_query->get('area') && !$wp_query->get('type')){
	get_footer();
}
?>
<div class="list_html_hidden"  style="display: none"></div>
