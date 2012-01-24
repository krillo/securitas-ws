<?php
if (is_user_logged_in ()) {
  // get the plugin base url
  $pluginRoot = plugins_url('', __DIR__);
  $eb = new TwentyfourEmailBin();
  $offset = 0;
  $limit = 20;
  $shortEmailArr = $eb->twentyfourEBgetEmailList($offset, $limit);
  $allEmailArr = $eb->twentyfourEBgetEmailList(0, -1);
  $count = $eb->twentyfourEBEmailCount();
?>

  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery("#email-raw").click(function(event) {
        event.preventDefault();
        jQuery("#email-list-container").toggle();
        jQuery("#email-list_raw").toggle();
      });
    });
  </script>



  <div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2>E-postlista <a href="#" id="email-raw" class="add-new-h2">Se hela listan utan formatering</a></h2>
    <div style="font-weight: bold;">Alla
      <span style="font-weight: normal;">(<?php echo $count; ?>)</span>
    </div>
    <div id="email-list_raw" style="display:none;">
    <?php
    foreach ($allEmailArr as $email) {
      echo $email->email . '<br/>';
    }
    ?>
  </div>
  <div id="email-list-container">
    <table class="wp-list-table widefat users" cellspacing="0">
      <thead>
        <tr>
          <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
          <th scope="col" id="role" class="manage-column column-role" style="">Id</th>
          <th scope="col" id="role" class="manage-column column-role" style="">Email</th>
          <th scope="col" id="role" class="manage-column column-role" style="">Datum</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
          <th scope="col" id="role" class="manage-column column-role" style="">Id</th>
          <th scope="col" id="role" class="manage-column column-role" style="">Email</th>
          <th scope="col" id="role" class="manage-column column-role" style="">Datum</th>
      </tfoot>

      <tbody id="the-list" class="list:user">
        <?php foreach ($shortEmailArr as $email) : ?>
          <tr id="row_<?php echo $email->id; ?>" class="alternate">
            <th scope="row" class="check-column" style="line-height:1em;padding-bottom:5px;"><input name="email_id[]" id="email_<?php echo $email->id; ?>" class="" value="1" type="checkbox"></th>
            <td class="email column-email"><?php echo $email->id; ?></td>
            <td class="email column-email"><a href="mailto:<?php echo $email->email; ?>"><?php echo $email->email; ?></a></td>
            <td class="email column-email"><?php echo $email->createDate; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>


  <div class="clear"></div>
<?php
        }
?>