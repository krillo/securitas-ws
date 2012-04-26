<?php
/**
 * Template Name: Sändarnummer
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
      <?php
      $user_id = $current_user->ID;
      $cellphone = get_user_meta($user_id, 'sec_cellphone', true);
      $showCellphone = false;
      if ($cellphone != '') {
        //echo $cellphone;
        $showCellphone = true;
      }
      ?>

      <style>
        .hidden{display:none;}      
      </style>

      <script type="text/javascript">    
        jQuery(function() {    
    
          jQuery('#sender-type').change(function() {
            val =  jQuery('#sender-type option:selected').val();            
            switch (val)
            {
              case 'Chiron':
                jQuery('#chiron').show("slow");
                jQuery('#system3').hide("slow");
                jQuery('#dualtech').hide("slow");                            
                break;
              case 'System 3':
                jQuery('#system3').show("slow");
                jQuery('#chiron').hide("slow");
                jQuery('#dualtech').hide("slow");                                
                break;   
              case 'Dualtech':
                jQuery('#dualtech').show("slow");
                jQuery('#chiron').hide("slow");
                jQuery('#system3').hide("slow");                             
                break;
              default: 
                jQuery('#dualtech').hide("slow");
                jQuery('#chiron').hide("slow");
                jQuery('#system3').hide("slow");              
            }
          });
    
        });
      </script>       

      <form action="thankyou" method="post" >
        <div class="pp-transmitter">
          <div class="pp-select">
            <div class="labels">
              <label><?php _e('Choose sender type', 'woothemes'); ?></label>
            </div>
            <select class="wpcf7-form-control  wpcf7-select wpcf7-validates-as-required" name="type" id="sender-type">								
              <option value="Antenna">Antenna</option>
              <option value="Chiron">Chiron</option>
              <option value="Contact ID">Contact ID</option>
              <option value="Dualtech">Dualtech</option>
              <option value="P100">P100</option>
              <option value="Robofon">Robofon</option>              
              <option value="Sia, 4-ställig">Sia, 4-ställig</option>
              <option value="Sia, 6-ställig">Sia, 6-ställig</option>
              <option value="System 3">System 3</option>
              <option value="Vista">Vista</option>
            </select>									 
          </div>
          <div id="chiron" class="hidden">
            <select class="wpcf7-form-control  wpcf7-select wpcf7-validates-as-required" name="chiron-intervall">								
              <option value="">Välj intervall</option>
              <option value="90 sek">90 sek</option>
              <option value="3 min">3 min</option>
              <option value="1 h">1 h</option>
              <option value="5 h">5 h</option>
              <option value="18 h">18 h</option>
              <option value="25 h">25 h</option>
            </select>
            <br/>
            <span class="pp-checkbox" style="margin:5px 0 10px 0;width:200px;">
              <span><input type="radio" value="Ethernet" name="chiron-type">Ethernet</span>
              <span><input type="radio" value="GPRS" name="chiron-type">GPRS</span>
              <span style="width:200px;"><input type="radio" value="Både Ethernet & GPRS" name="chiron-type">Både Ethernet & GPRS</span>
            </span>
          </div>
          <div id="system3" class="hidden">
            <select class="wpcf7-form-control  wpcf7-select wpcf7-validates-as-required" name="system3-intervall">								
              <option value="">Välj intervall</option>
              <option value="90 sek">90 sek</option>
              <option value="5 min">5 min</option>
            </select>
            <br/>
            <br/>  
          </div>
          <div id="dualtech" class="hidden">
            <select class="wpcf7-form-control  wpcf7-select wpcf7-validates-as-required" name="dualtech-intervall">								
              <option value="">Välj intervall</option>
              <option value="Ingen">Ingen</option>
              <option value="90 sek">90 sek</option>
              <option value="3 min">3 min</option>
              <option value="1 h">1 h</option>
              <option value="5 h">5 h</option>
              <option value="25 h">25 h</option>
            </select>
            <br/>
            <br/>  
          </div>

          <div class="pp-select">
            <div class="labels">
              <label><?php _e('Choose LC', 'woothemes'); ?></label>
            </div>
            <select class="wpcf7-form-control  wpcf7-select wpcf7-validates-as-required" name="lc-email">		              
              <option value="kundtjanst.lc.sto@securitas.se">Stockholm</option>
              <option value="larmcentralen.mal@securitas.se">Malmö</option>
            </select>
          </div>
          <div>
            <div class="labels">
              <label><?php _e('Your technician code', 'woothemes'); ?></label>
            </div>
            <input type="text" size="40" value="" name="techcode">
          </div>
          <div>
            <div class="labels">
              <label><?php _e('Choose between E-mail or SMS', 'woothemes'); ?></label>
            </div>
            <span class="pp-checkbox">
              <span><input type="radio" value="email" name="com"><?php _e('E-mail', 'woothemes'); ?></span>
              <?php if ($showCellphone): ?>
                <span><input type="radio" value="sms" name="com"><?php _e('SMS', 'woothemes'); ?></span>
              <?php endif; ?>
            </span>
            <br/>
            <div class="labels">
              <label><?php _e('Your e-mail', 'woothemes'); ?></label>
            </div>
            <input type="text" size="40" value="" name="email">
            <div class="labels">
              <label><?php _e('Your mobile', 'woothemes'); ?></label>
            </div>
            <input type="text"size="40" value="" name="mobile">
          </div>
          <div class="trans-buttons">
            <input type="submit" class="wpcf7-submit" value="<?php _e('Send', 'woothemes'); ?>">
          </div>
        </div>
      </form>
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