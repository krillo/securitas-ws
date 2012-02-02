<?php
/**
  Template Name: SecuritasWS List Staff
 */
get_header();
?>
<div id="primary">
    <div id="content" role="main">
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('content', 'page'); ?>
            <?php securitasWSgetStaffList(get_the_author_meta('idcompany', get_current_user_id()));?>
        <?php endwhile; // end of the loop. ?>
    </div><!-- #content -->
</div><!-- #primary -->
<?php get_footer(); ?>