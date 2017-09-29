<?php
/* Template Name: Koko leveys (ei sivupalkkia) */
$infoData = $GLOBALS['iggoPartnerInfoData'];
get_header(); 
$siteUrl = site_url();
$currentUrl = $siteUrl.'/jasenyritykset/';
$firstLetters = array_keys($infoData);
?>
<?php include 'page-header.php';?>

<section id="content" class="mobile-scroll expand jasenyritykset">
    <div class="container">
		<div class="categories-list">
<!-- 			<div class="category-desc"> -->
<!-- 				<p>Fysi ry:n jäsenyritykset</p> -->
<!-- 			</div> -->
			<div class="categoryNavigation">
				<?php foreach ($firstLetters as $letter):?>
					<a href="#<?php echo $letter?>"><?php echo $letter?></a>
				<?php endforeach;?>
                <!--a href="#A">A</a-->
<!--                 <a href="/jaesenyritykset/109-a">A</a> -->
			</div>
			<div class="categorySearch">
				<label for="s-zipcode">Search by postcode: </label>
                <input type="text" id="s-zipcode" name="s-zipcode" class="search-keyword" value=""> 
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
			<ul>
				<?php $num = 0;$total = count($infoData);?>
				<?php foreach ($infoData as $letter=>$items):?>
					<?php 
					   $num ++;
					   $liClass = '';
					   if($num == 1){
					       $liClass = 'first';
					   }
					   if($num == $total){
					       $liClass = 'last';
					   }
					   $lowerLetter = mb_strtolower($letter);
					?>
					<li class="<?php echo $liClass?>">
                		<span class="item-title">
                			<a id="<?php echo $letter?>" href="<?php echo $currentUrl.$num.'-'.IggoPartner::fiLetterMap($lowerLetter)?>" class="okmemo-tmp-unselect"><?php echo $letter?></a>
                		</span>
            			<ul>
            				<?php $itemNum = 0;$itemTotal = count($items);?>
            				<?php foreach ((array)$items as $itemId=>$item):?>
            					<?php 
            					   $itemNum ++;
            					   $itemLiClass = '';
            					   if($itemNum == 1){
            					       $itemLiClass = 'first';
            					   }
            					   if($itemNum == $itemTotal){
            					       $itemLiClass = 'last';
            					   }
            					?>
                			<li class="<?php echo $itemLiClass?>">
                				<span class="item-title">
                					<?php $url = $currentUrl.$itemId.'-'.IggoPartner::fiLetterMap($lowerLetter).'/'.mb_strtolower($item['municipality']);?>
                					<a id="<?php echo $item['municipality']?>" href="<?php echo $url?>"><?php echo $item['municipality']?></a>
                				</span>
                			</li>
                			<?php endforeach;?>
            		</ul>
            	</li>
				<?php endforeach;?>
			</ul>
		</div>
    </div>
</section>

<?php get_footer(); ?>