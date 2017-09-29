<?php
/* Template Name: Koko leveys (ei sivupalkkia) */
$infoData = $GLOBALS['iggoPartnerInfoData'];
$siteUrl = site_url();
$currentUrl = $siteUrl.'/jasenyritykset/';
get_header(); 
?>
<?php include 'page-header.php';?>

<section id="content" class="mobile-scroll expand jasenyritykset-marketing">
    <div class="container">
    	<div class="item-page" itemscope="" itemtype="https://schema.org/Article">
    		<?php 
    		$marketingName = trim($infoData['marketing_name']);
    		if(!$marketingName){
    		    $marketingName = $infoData['account_name'];
    		}
    		?>
			<meta itemprop="inLanguage" content="fi-FI">
			<div class="page-header">
				<h2 itemprop="headline"><?php echo $marketingName?></h2>
			</div>
			<div itemprop="articleBody">
    			<p><?php echo $marketingName?><br> <?php echo $infoData['street']?>, <?php echo $infoData['postcode']?> <?php echo $infoData['municipality']?>
					<br> Puh <?php echo $infoData['phone']?>
					<br> Sähköposti: <a href="mailto:<?php echo $infoData['email']?>"><?php echo $infoData['email']?></a> 
					<?php if($infoData['url']):?>
					<br>Kotisivut: <a href="//<?php echo $infoData['url']?>" target="_blank"><?php echo $infoData['url']?></a>
					<?php endif;?>
					<?php if($infoData['facebook']):?>
					<br>Facebook: <a href="//<?php echo $infoData['facebook']?>" target="_blank"><?php echo $infoData['facebook']?></a>
					<?php endif;?>
    			</p> 	
			</div>
		</div>
		<a class="jasenyritykset-link" href="javascript:history.back(-1)">Back</a>
    </div>
</section>

<?php get_footer(); ?>