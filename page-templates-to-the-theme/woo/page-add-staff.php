<?php
/**
 * Template Name: SecuritasWS Add Staff
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
	
      <?php  if(get_user_meta(get_current_user_id(), 'sec_technician', true) == '1'):  ?>  
        <?php while (have_posts()) : the_post(); ?>
            <?php woo_get_template_part('content', 'page'); ?>
            <?php securitasWSaddStaff(get_user_meta(get_current_user_id(), 'sec_idcompany', true)); ?>
        <?php endwhile; ?>
      <? else: ?>
       Not allowed
      <? endif; ?>
	
<?php	woo_loop_after(); ?>     
            </div><!-- /#main -->
            <?php woo_main_after(); ?>
    
            <?php get_sidebar(); ?>

		</div><!-- /#main-sidebar-container -->         

		<?php get_sidebar( 'alt' ); ?>

    </div><!-- /#content -->
	<?php woo_content_after(); ?>

<?php get_footer(); ?>