<?php
/**
 * Template Name: SecuritasWS Edit profile
 */
get_header();
?>
<div id="primary">
    <div id="content" role="main">      
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('content', 'page'); ?>
            <?php 
              $wpUserId = get_current_user_id();
              $sws = new SecuritasWS();
              $sws->editProfile($wpUserId);
            ?>
        <?php endwhile;?>        
    </div>
</div>
<?php get_footer(); ?>