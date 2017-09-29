<?php
/* Template Name: Koko leveys (ei sivupalkkia) */
$infoData = $GLOBALS['iggoPartnerInfoData'];
$siteUrl = site_url();
$currentUrl = $siteUrl.'/jasenyritykset/';
get_header(); 
?>
<?php include 'page-header.php';?>

<section id="content" class="mobile-scroll expand jasenyritykset-postcode">
    <div class="container">
    	<div class="blog">
			<h2>
				<span class="subheading-category">Fysi ry:n jäsenyritykset / <?php echo $infoData['postcode']?></span>
			</h2>
			<div class="categorySearch">
				<label for="s-zipcode">Search by postcode: </label>
                <input type="text" id="s-zipcode" name="s-zipcode" class="search-keyword" value="<?php echo $infoData['postcode']?>"> 
                <button id="submit-s-zipcode" class="select_class" onmouseout="this.className='select_class'" onmouseover="this.className='select_over'"><i class="fa fa-search"></i></button>
				<script>
    				$(document).ready(function(){
    					function submitZipcode(){
    						var zipcode = $('#s-zipcode').val();
    						if(zipcode){
    							window.location.href = '<?php echo $currentUrl?>'+zipcode; 
    						}
    					}
    					$('#submit-s-zipcode').click(function(){
    						submitZipcode();
    						return false;
    					})
    					$('#s-zipcode').keypress(function (e) {
    						  if (e.which == 13) {
    							  submitZipcode();
    						      return false;
    						  }
    					});
    				})
				</script>
			</div>
			<div class="items-leading jasenyritykset-items">
				<?php foreach ($infoData['items'] as $key=>$item):?>
				<?php 
				    $marketingName = trim($item['marketing_name']);
				    if(!$marketingName){
				        $marketingName = $item['account_name'];
				    }
				    $lowerMarketingName = IggoPartner::formatUrlKey($marketingName);
				    $lowerCity = mb_strtolower($item['municipality']);
				    $lowerLetter = IggoPartner::fiLetterMap(mb_substr($lowerCity, 0, 1));
				    $url = $currentUrl."{$key}-{$lowerLetter}/{$lowerCity}/{$item['id']}-{$lowerMarketingName}";
				    //$url = esc_url($url); 
				?>
				<div class="leading-<?php echo $key?> jasenyritykset-city-box">
					<div class="page-header">
						<h2 itemprop="name">
							<a href="<?php echo $url?>" itemprop="url"><?php echo $marketingName?></a>
						</h2>
					</div>
					<p><?php echo $marketingName?><br> <?php echo $item['street']?>, <?php echo $item['postcode']?> <?php echo $item['municipality']?>
					<br> Puh <?php echo $item['phone']?>
					<?php if($item['email']):?>
					<br> Sähköposti: <a href="mailto:<?php echo $item['email']?>"><?php echo $item['email']?></a> 
					<?php endif;?>
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