<?php
/* Template Name: Koko leveys (ei sivupalkkia) */
$infoData = $GLOBALS['iggoPartnerInfoData'];
$siteUrl = site_url();
$currentUrl = $siteUrl.'/jasenyritykset/';
$lowerLetter = mb_strtolower($infoData['letter']);
$lowerLetter = IggoPartner::fiLetterMap($lowerLetter);
get_header(); 

?>
<?php include 'page-header.php';?>

<section id="content" class="mobile-scroll expand jasenyritykset-city-index">
    <div class="container">
    	<div class="blog">
			<h2>
				<span class="subheading-category">Fysi ry:n jäsenyritykset / <?php echo $infoData['letter']?></span>
			</h2>
			<div class="cat-children">
				<h3>Alakategoriat</h3>
				<?php $num = 0;$total = count($infoData['items']);?>
				<?php foreach ($infoData['items'] as $id=>$item):?>
					<?php 
					   $num ++;
					   $liClass = '';
					   if($num == 1){
					       $liClass = 'first';
					   }
					   if($num == $total){
					       $liClass = 'last';
					   }
					   $lowerMunicipality = mb_strtolower($item['municipality']);
					   $url = $currentUrl.$id."-{$lowerLetter}/{$lowerMunicipality}";
					?>
					<div class="<?php echo $liClass?>">
    					<h3 class="page-header item-title">
    						<a href="<?php echo $url?>"><?php echo $item['municipality']?></a>
    					</h3>
        			</div>
				<?php endforeach;?>
				
			</div>
		</div>
		<a class="jasenyritykset-link" href="<?php echo $currentUrl?>">Palaa hakusivulle</a>
    </div>
</section>

<?php get_footer(); ?>