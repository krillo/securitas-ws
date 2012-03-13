<?php
/**
 * Template Name: SecuritasWS Edit profile
 */
get_header();
?>       
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">    
    	<div id="main-sidebar-container">    
        <?php woo_main_before(); ?>
        <div id="main">                     
          <?php woo_loop_before();  ?>
            <?php while (have_posts()) : the_post(); ?>
              <?php get_template_part('content', 'page'); ?>
              <?php 
                $wpUserId = get_current_user_id();
                $sws = new SecuritasWS();
                $sws->editProfile($wpUserId);
              ?>
            <?php endwhile;?>      
          <?php woo_loop_after(); ?>     
        </div>
        <?php woo_main_after(); ?>
        <?php get_sidebar(); ?>
		</div>
		<?php get_sidebar( 'alt' ); ?>
    </div>
	<?php woo_content_after(); ?>
<?php get_footer(); ?>