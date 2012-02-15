<?php
/**
 * Template Name: SecuritasWS Edit staff
 */
get_header();
?>
<div id="primary">
    <div id="content" role="main">
      <?php  if(get_user_meta(get_current_user_id(), 'sec_technician', true) == '1'):  ?>         
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('content', 'page'); ?>
            <?php 
              $idperson = $_REQUEST['idperson'];
              securitasWSeditStaff($idperson);
            ?>
        <?php endwhile; // end of the loop. ?>
      <? else: ?>
       Not allowed
      <? endif; ?>         
    </div><!-- #content -->
</div><!-- #primary -->
<?php get_footer(); ?>