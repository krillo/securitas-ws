<?php
/**
  Template Name: SecuritasWS Staff list
 */
get_header();
?>
       
    <!-- #content Starts -->
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">
    
    	<div id="main-sidebar-container">    

            <!-- #main Starts -->
            <?php woo_main_before(); ?>
            <div id="main">                     
<?php	woo_loop_before(); ?>
	
  <?php while (have_posts()) : the_post(); ?>
    <?php woo_get_template_part('content', 'page'); ?>
    <?php securitasWSgetStaffList(get_the_author_meta('sec_idcompany', get_current_user_id()));?>
  <?php endwhile; ?>            
              
<?php	woo_loop_after(); ?>     
            </div><!-- /#main -->
            <?php woo_main_after(); ?>
    
            <?php get_sidebar(); ?>

		</div><!-- /#main-sidebar-container -->         

		<?php get_sidebar( 'alt' ); ?>

    </div><!-- /#content -->
	<?php woo_content_after(); ?>

<?php get_footer(); ?>