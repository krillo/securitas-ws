<?php
/**
 * Template Name: SecuritasWS List Staff
 */
get_header();
?>
<div id="primary">
    <div id="content" role="main">
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('content', 'page'); ?>
            <?php
              $userId = get_current_user_id();
              if($userId != 0){
                securitasWSgetStaffList(get_user_meta($userId, 'sec_idcompany', true));
              } else {
                echo "Not allowed";
              }
            ?>
        <?php endwhile; // end of the loop. ?>
    </div><!-- #content -->
</div><!-- #primary -->
<?php get_footer(); ?>