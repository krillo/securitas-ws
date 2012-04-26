<?php
/**
 * Template Name: Sändarnummer - Tack
 */
get_header();
?>       
<!-- #content Starts -->
<?php
if (function_exists('woo_content_before')) {
  woo_content_before();
}
?>
<div id="content" class="col-full">
  <div id="main-sidebar-container">    
    <!-- #main Starts -->
    <?php
    if (function_exists('woo_main_before')) {
      woo_main_before();
    }
    ?>
    <div id="main">                     
      <?php
      if (function_exists('woo_loop_before')) {
        woo_loop_before();
      }
      ?>								
      <?php while (have_posts()) : the_post(); ?>
        <?php
        if (function_exists('woo_get_template_part')) {
          woo_get_template_part('content', 'page');
        }
        ?>
      <?php endwhile; ?>  




      <?
      $to = isset($_REQUEST['lc-email']) ? $_REQUEST["lc-email"] : "";
      $type = isset($_REQUEST['type']) ? $_REQUEST["type"] : "";
      $lc = isset($_REQUEST['lc']) ? $_REQUEST["lc"] : "";
      $techcode = isset($_REQUEST['techcode']) ? $_REQUEST["techcode"] : "";
      $com = isset($_REQUEST['com']) ? $_REQUEST["com"] : "";
      $mobile = isset($_REQUEST['mobile']) ? $_REQUEST["mobile"] : "";
      $email = isset($_REQUEST['email']) ? $_REQUEST["email"] : "";
      
      $chiron = isset($_REQUEST['chiron-intervall']) ? $_REQUEST["chiron-intervall"] : "";
      $chironType = isset($_REQUEST['chiron-type']) ? $_REQUEST["chiron-type"] : "";
      $system3 = isset($_REQUEST['system3-intervall']) ? $_REQUEST["system3-intervall"] : "";
      $dualtech = isset($_REQUEST['dualtech-intervall']) ? $_REQUEST["dualtech-intervall"] : "";
      
      $interval = __('interval', 'woothemes');
      $extra = '';
      if($type == 'Chiron'){
        $extra = "Chiron $interval: $chiron, ". __('type', 'woothemes') .": $chironType <br/>";
      }
      if($type == 'Dualtech'){
        $extra = "Dualtech $interval: $dualtech <br/>";
      }
      if($type == 'System 3'){
        $extra = "System 3 $interval: $system3 <br/>";
      }
      
      
      $user_id = $current_user->ID;
      $user_email = $current_user->data->user_email;
      $user_firstname = get_user_meta($user_id, 'first_name', true);
      $user_lastname = get_user_meta($user_id, 'last_name', true);
      $user_company = get_user_meta($user_id, 'sec_companyname', true);
      $content_to_show = __('Your mail could not be sent!', 'woothemes');

      $message = __('Request of sender number', 'woothemes') . '<br/>';
      $message .= __('Transmitter code', 'woothemes') . ': ' . $type . '<br/>';
      $message .= $extra;
      $message .= __('Name', 'woothemes') . ': ' . $user_firstname . ' ' . $user_lastname . '<br/>';
      $message .= __('Company', 'woothemes') . ': ' . $user_company . '<br/>';
      $message .= __('Technician code', 'woothemes') . ': ' . $techcode . '<br/>';
      $message .= __('Cellphone', 'woothemes') . ': ' . $mobile . '<br/>';
      $message .= __('Communicate through', 'woothemes') . ' ';
      if ($com == 'email') {
        $message .= __('email', 'woothemes') . ': ' . $email;
      } else {
        $message .= __('sms', 'woothemes') . ': ' . $mobile;
      }
      //$message = mb_convert_encoding($message, "UTF-8");
      //$message = utf8_decode($message);

      $message = str_replace('å', '&aring;', $message);
      $message = str_replace('ä', '&auml;', $message);
      $message = str_replace('ö', '&ouml;', $message);
      $message = str_replace('Å', '&Aring;', $message);
      $message = str_replace('Ä', '&Auml;', $message);
      $message = str_replace('Ö', '&Ouml;', $message);


      $subject = __('Request of sender number', 'woothemes');
      //$subject = mb_convert_encoding($subject, "UTF-8");
      //$subject = utf8_decode($subject);
      $from = get_option('sec_support_email_sender');



      if ($to != "" && $message != "") {
        $headers = 'To: ' . $to . ' <' . $to . '>' . "\r\n";
        $headers .= 'From: ' . $from . ' <' . $from . '>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        /*
          echo 'headers: ' . $headers . '<br>';
          echo 'to: ' . $to . '<br>';
          echo 'from: ' . $from . '<br>';
          echo 'subject: ' . $subject . '<br>';
          echo 'message: ' . $message . '<br>';
         */

        $success = mail($to, $subject, $message, $headers);
        if ($success) {
          $content_to_show = get_the_content($post->ID);
        }
      }

      echo $content_to_show;
      ?>    






      <?php
      if (function_exists('woo_loop_after')) {
        woo_loop_after();
      }
      ?>     
    </div><!-- /#main -->
    <?php
    if (function_exists('woo_main_after')) {
      woo_main_after();
    }
    ?>
    <?php get_sidebar(); ?>
  </div><!-- /#main-sidebar-container -->         
  <?php get_sidebar('alt'); ?>
</div><!-- /#content -->
<?php
if (function_exists('woo_content_after')) {
  woo_content_after();
}
?>
<?php get_footer(); ?>