<?php
/**
 * Template Name: SecuritasWS Add Staff
 */
get_header();
?>
<div id="primary">
    <div id="content" role="main">
      <?php  if(get_user_meta(get_current_user_id(), 'sec_technician', true) == '1'):  ?>        
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('content', 'page'); ?>
            <?php securitasWSaddStaff(get_user_meta(get_current_user_id(), 'sec_idcompany', true)); ?>
        <?php endwhile; ?>
      <? else: ?>
       Not allowed
      <? endif; ?>       
    </div><!-- #content -->
</div><!-- #primary -->
<?php get_footer(); ?>