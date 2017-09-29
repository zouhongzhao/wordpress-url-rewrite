<?php
/* Template Name: Koko leveys (ei sivupalkkia) */
$infoData = $GLOBALS['iggoPartnerInfoData'];
$siteUrl = site_url();
$currentUrl = $siteUrl.'/jasenyritykset/';
$lowerCity = mb_strtolower($infoData['city']);
$lowerLetter = mb_strtolower($infoData['letter']);
//$lowerLetter = IggoPartner::fiLetterMap($lowerLetter);
get_header(); 
?>
<?php include 'page-header.php';?>

<section id="content" class="mobile-scroll expand jasenyritykset-city">
    <div class="container">
    	<div class="blog">
			<h2>
				<span class="subheading-category">Fysi ry:n jäsenyritykset / <?php echo $infoData['city']?></span>
			</h2>
			
			<div class="items-leading jasenyritykset-items">
				<?php foreach ($infoData['items'] as $key=>$item):?>
				<?php 
				    $marketingName = trim($item['marketing_name']);
				    if(!$marketingName){
				        $marketingName = $item['account_name'];
				    }
				    $lowerMarketingName = IggoPartner::formatUrlKey($marketingName);
				    $url = $currentUrl."{$key}-{$lowerLetter}/{$lowerCity}/{$item['id']}-{$lowerMarketingName}";
				?>
				<div class="leading-<?php echo $key?>  jasenyritykset-city-box">
					<div class="page-header">
						<h2 itemprop="name">
							<a href="<?php echo $url?>" itemprop="url"><?php echo $marketingName?></a>
						</h2>
					</div>
					<p><?php echo $marketingName?><br> <?php echo $item['street']?>, <?php echo $item['postcode']?> <?php echo $item['municipality']?>
					<br> Puh <?php echo $item['phone']?>
					<br> Sähköposti: <a href="mailto:<?php echo $item['email']?>"><?php echo $item['email']?></a> 
					<?php if($item['url']):?>
					<br>Kotisivut: <a href="//<?php echo $item['url']?>" target="_blank"><?php echo $item['url']?></a>
					<?php endif;?>
					<?php if($item['facebook']):?>
					<br>Facebook: <a href="//<?php echo $item['facebook']?>" target="_blank"><?php echo $item['facebook']?></a>
					<?php endif;?>
					</p>
				</div>
				<?php endforeach;?>
			</div>
	
			<a class="jasenyritykset-link" href="<?php echo $currentUrl?>">Palaa hakusivulle</a>
		</div>
    </div>
</section>

<?php get_footer(); ?>