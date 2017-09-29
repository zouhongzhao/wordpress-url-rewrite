<?php 
wp_enqueue_style('stylsheet', plugins_url( 'css/partner.css', __FILE__ ) );
?>
<section id="topimage" class="mobile-scroll">
	<div id="iggoslider">
        <!-- Slider Repeater -->
        <?php
        // Get content type first
        $post_type = 'page';
        // User proper function depending on the post type
        if($post_type == 'page' || $post_type == 'post') { $rows = get_page_or_ancestor_slider("slider", false); }
        elseif($post_type == 'category') { $rows = get_category_slider("slider", $cat ,false); }

        if($rows && is_array($rows)):
            foreach($rows as $row):
                $bgimage = $row["bgimage"];
                $bgcolor = $row["bgcolor"];
                $title = $row["text-title"];
                $content = $row["text-content"];
                $buttongroup = $row["rep-buttons"];
        ?>

        <?php
            if(is_front_page()) { $simgsize = 'sliderbig'; }
            else { $simgsize = 'slidersmall'; }
        ?>

            <div class="single-slide" style="background-image: url('<?php echo $bgimage["sizes"][$simgsize]; ?>');" >

                <?php
                    if(!empty($content)):
                ?>

                    <div class="bgcolor <?php echo $bgcolor; ?>"></div>

                    <div class="container">
                        <div class="slideinfo" data-sr="wait 0.1s">

                            <h2><?php echo $title; ?></h2>
                            <p><?php echo $content; ?></p>

                            <?php
                                if(!empty($buttongroup)):
                            ?>

                                <div class="buttongroup">
                                    <?php foreach($buttongroup as $singlebutton): ?>
                                        <div class="sbwrap">
                                            <a href="<?php echo $singlebutton['button-link']; ?>" class="readmore">
                                                <?php echo $singlebutton["button-text"]; ?>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach;endif; ?>

    </div>
</section>
<style>
	section#breadcrumbs { padding: 0.5rem 0; background: #F0F0F0; font-size: 0.875rem; font-weight: 300; }
	.breadcrumbs { margin: 0 -0.5rem; }
	.breadcrumbs > span { margin: 0 0.5rem; }

	/* Small devices (landscape phones, less than 48em) */
	@media (max-width: 47.9em) {
		section#breadcrumbs { display: none; }
	}
</style>
<section id="breadcrumbs">
	<div class="container">
		<div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
			<!-- Breadcrumb NavXT 5.7.1 -->
			<span property="itemListElement" typeof="ListItem">
				<a property="item" typeof="WebPage" title="Siirry kohtaan FYSI ry." href="/" class="home">
					<span property="name">FYSIry</span>
				</a>
				<meta property="position" content="1">
			</span> &gt; 
			<span property="itemListElement" typeof="ListItem">
				<span property="name">Jäsenyritykset</span>
				<meta property="position" content="2">
			</span>
		</div>
	</div>
</section>
<div class="page-title container">
<h1>Jäsenyritykset</h1>
</div>