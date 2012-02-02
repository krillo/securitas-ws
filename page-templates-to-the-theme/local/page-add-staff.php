<?php
/**
 * Template Name: SecuritasWS Add Staff
 */
get_header();
?>
<div id="primary">
    <div id="content" role="main">
      <?php  if(get_the_author_meta('technician', get_current_user_id()) == '1'):  ?>      
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('content', 'page'); ?>
            <?php securitasWSaddStaff(get_the_author_meta('idcompany', get_current_user_id()));?>
        <?php endwhile; ?>
      <? else: ?>
       Not allowed
      <? endif; ?>       
    </div><!-- #content -->
</div><!-- #primary -->
<?php get_footer(); ?>