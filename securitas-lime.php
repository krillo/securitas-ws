<?php

/**
  Plugin Name: Securitas-WS
  Plugin URI: http://securitas.com/
  Description: Integration to Lundalogik, Lime
  Version: 1.0
  Author: Kristian Erendi
  Author URI: http://reptilo.se
  License: GPL2
 */
/*
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class SecuritasWS {

  //plugin db version
  public static $myDbVersion = "0.1";
  public $lime = null;

  function __construct() {
    global $wpdb;
    $url = null;
    $url = get_option("lime_url");  //https://limehosting.se:5797/meta
    if (!isset($url)) {
      echo 'ERROR no url to Lime service set';
    } else {
      include_once 'LimeWService.php';
      $this->lime = new LimeWService($url);
      //$response = $lime->databaseschema();
      //$response = $lime->tableschema();
      //$response = $lime->selectFromOffice(10);  //funkar
      //$response = $lime->updateOffice();
      //$response = $lime->updateCompany();
      //$response = $lime->selectFromCompany(100);
      //$response = $lime->selectFromPerson(10);
      //$response = $lime->updatePerson();
      //var_dump($response);
    }
  }

  /**
   * install function, ie create or update the database
   */
  public static function install() {
    global $wpdb;
    $installed_ver = get_option("twentyfourEmailBinDbVersion");
    if ($installed_ver != twentyfourEmailBin::$myDbVersion) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      $table_name = $wpdb->prefix . 'emailbin';
      $sql = "CREATE TABLE " . $table_name . " (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              email varchar(64) NOT NULL,
              createDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY id (id)
              );";
      dbDelta($sql);
      //echo $sql;
      update_option("twentyfourEmailBinDbVersion", twentyfourEmailBin::$myDbVersion);
    }
  }

  /**
   * checks if a database table update is needed
   */
  public static function update() {
    $installed_ver = get_option("twentyfourEmailBinDbVersion");
    if ($installed_ver != twentyfourEmailBin::$myDbVersion) {
      twentyfourEmailBin::install();
    }
  }

  /**
   * Just for testing
   */
  public function debugOutput() {
    $response = $this->lime->selectFromOffice(1);  //funkar
    var_dump($response);
  }

  /**
   * Get the markup of all the staff
   */
  public function getStaffList($companyId) {
    $response = $this->lime->selectFromPerson($companyId);
    //var_dump($response);

    $output = '<ul>';
    foreach ($response as $value) {
      $position = "position.text";
      $output .= '<li>';
      $output .= '<div class="staff-container">';
      $output .= '<div class="staff-info">';
      $output .= '<div><strong>Name</strong>';
      $output .= '<p>' . $value->attributes()->firstname . '</p>';
      $output .= '</div>';
      $output .= '<div><strong>Last name</strong>';
      $output .= '<p>' . $value->attributes()->familyname . '</p>';
      $output .= '</div>';
      $output .= '<div><strong>Function</strong>';
      $output .= '<p>' . $value->attributes()->$position . '</p>';
      $output .= '</div>';
      $output .= '<div><strong>E-mail</strong>';
      $output .= '<p><a href="mailto:' . $value->attributes()->email . '">' . $value->attributes()->email . '</a></p>';
      $output .= '</div>';
      $output .= '<div><strong>Mobile</strong>';
      $output .= '<p>' . $value->attributes()->cellphone . '</p>';
      $output .= '</div>';
      $output .= '<div><strong>Eligibility</strong>';
      $output .= '<p>' . $value->attributes()->authorizedarc . '</p>';
      $output .= '</div>';
      $output .= '</div>';
      $output .= '<div id="staff-buttons">';
      $output .= '<input type="hidden" value="' . $value->attributes()->idperson . '" /><span><br />';
      $output .= '<input class="wpcf7-submit" type="submit" value="X" /><span><br />';
      $output .= '<input class="wpcf7-submit" type="submit" value="Edit" /></span>';
      $output .= '</div>';
      $output .= '</div>';
      $output .= '</li>';
    }
    $output .= '<ul>';
    echo $output;
  }

  /**
   * Return the markup of the editable person
   */
  public function editPerson($personId) {
    $response = $this->lime->getPerson($personId);
    var_dump($response);

    $portal = '';
    $elegible = '';
    $tech = '';
    foreach ($response as $value) {
      if($value->attributes()->authorizedportal == 1){
          $portal = ' checked ';
      }
      if($value->attributes()->authorizedarc == 1){
          $elegible = ' checked ';
      }        
      if($value->attributes()->admninrights == 1){
          $tech = ' checked ';
      }        
        
      $output = '<div id="list-staff">';
      $output .= '<ul>';
      $output .= '<li>';
      $output .= '<div class="staff-container">';
      $output .= '<form class="form" method="POST" action="#">';
      $output .= '<fieldset>';
      $output .= '<div class="staff-info">';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="name">Name</label></div>';
      $output .= '<input type="text" class="name" value="' . $value->attributes()->firstname . '" id="name" name="name">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="lastname">Last name</label></div>';
      $output .= '<input type="text" class="lastname" value="' . $value->attributes()->familyname . '" id="lastname" name="lastname">';
      $output .= '</div>';
      $output .= '<div class="pp-select">';
      $output .= '<div class="labels"><label for="role">Choose role</label></div>';
      $output .= '<select id="role">';
      $output .= '<option value="sales">Sales</option>';
      $output .= '<option value="technician">Technician</option>';
      $output .= '<option value="marketing">Marketing</option>';
      $output .= '<option value="other">Other</option>';
      $output .= '</select>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="mobile">Mobile</label></div>';
      $output .= '<input type="text" class="mobile" value="' . $value->attributes()->cellphone . '" id="mobile" name="mobile">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="email">E-mail</label></div>';
      $output .= '<input type="text" class="email" value="' . $value->attributes()->email . '" id="email" name="email">';
      $output .= '</div>';
      $output .= '<div class="pp-wrap">';
      $output .= '<input type="checkbox" value="forever" class="pp-check" name="tech" '.$tech.'>';
      $output .= '<div class="pp-checkbox">Technical Administrator</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="forever" class="pp-check" name="elegible" '.$elegible.'>';
      $output .= '<div class="pp-checkbox">Elegible LC</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="forever" class="pp-check" name="portal" '.$portal.'>';
      $output .= '<div class="pp-checkbox">Elegible Portal</div>';
      $output .= '</div>';
      $output .= '</div>';
      $output .= '<p><!--staff-info--></p>';
      $output .= '<div class="staff-buttons">';
      $output .= '<input type="submit" class="wpcf7-submit" value="Save">';
      $output .= '</div>';
      $output .= '</fieldset>';
      $output .= '</form>';
      $output .= '</div>';
      $output .= '<p><!--staff-container-->';
      $output .= '</li>';
      $output .= '</ul>';
      $output .= '</div>';
    }
    echo $output;
  }

  public function addStaff() {
    $output = '<div class="entry">';
    $output .= '<div id="list-staff">';
    $output .= '<ul>';
    $output .= '<li>';
    $output .= '<div id="staff-container">';
    $output .= '<form class="form" method="POST" action="#">';
    $output .= '<fieldset>';
    $output .= '<div id="staff-info">';
    $output .= '<div>';
    $output .= '<div id="labels"><label for="name">Name</label></div>';
    $output .= '<input type="text" class="name" value="" id="name" name="name">';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div id="labels"><label for="lastname">Last name</label></div>';
    $output .= '<input type="text" class="lastname" value="" id="lastname" name="lastname">';
    $output .= '</div>';
    $output .= '<div class="pp-select">';
    $output .= '<div id="labels"><label for="mobile">Choose role</label></div>';
    $output .= '<select>';
    $output .= '<option value="choose">Choose</option>';
    $output .= '<option value="sales">Sales</option>';
    $output .= '<option value="technician">Technician</option>';
    $output .= '<option value="marketing">Marketing</option>';
    $output .= '<option value="other">Other</option>';
    $output .= '</select>';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div id="labels"><label for="mobile">Mobile</label></div>';
    $output .= '<input type="text" class="mobile" value="" id="mobile" name="mobile">';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div id="labels"><label for="email">E-mail</label></div>';
    $output .= '<input type="text" class="email" value="" id="email" name="email">';
    $output .= '</div>';
    $output .= '<div class="pp-wrap">';
    $output .= '<input type="checkbox" value="forever" class="pp-check" name="tech">';
    $output .= '<div class="pp-checkbox">Technical Administrator</div>';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<input type="checkbox" value="forever" class="pp-check" name="elegible">';
    $output .= '<div class="pp-checkbox">Elegible LC</div>';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<input type="checkbox" value="forever" class="pp-check" name="portal">';
    $output .= '<div class="pp-checkbox">Elegible Portal</div>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<p><!--staff-info--></p>';
    $output .= '<div id="staff-buttons">';
    $output .= '<input type="submit" class="wpcf7-submit" value="Save">';
    $output .= '</div>';
    $output .= '</fieldset>';
    $output .= '</form>';
    $output .= '</div>';
    $output .= '<p><!--staff-container-->';
    $output .= '</li>';
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '	</div><!-- /.entry -->';
  }

  /**
   * Add an email to the db if it has no duplicate already
   *
   * @global  $wpdb
   * @param <type> $email
   */
  public function twentyfourEBinsert($email) {
    $exists = $this->twentyfourEBgetEmail($email);
    if (empty($exists)) {
      global $wpdb;
      $table_name = $wpdb->prefix . 'emailbin';
      $sql = "insert into " . $table_name . " (email) values('" . $email . "');";
      $wpdb->get_results($sql);
    }
  }

  /**
   * If email exists this function will return the whole row
   *
   * @global $wpdb $wpdb
   * @param <type> $email
   * @return <type>
   */
  public function twentyfourEBgetEmail($email) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'emailbin';
    $sql = "select * from " . $table_name . " where email = '" . $email . "';";
    $res = $wpdb->get_results($sql);
    return $res;
  }

  /**
   * Print the javascript to the page
   */
  public function twentyfourEBCode() {
    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api/emailbin.php";
    echo '<script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery("#pren").click(function(event) {
      event.preventDefault();
      if( $("#pren-email").val().indexOf("@") == -1){
        alert("Felaktig emailadress");
      } else {
        var dataString = "email="+ jQuery("#pren-email").val();
        //alert(dataString);
        var self = jQuery(this);    var self = jQuery(this);
        if(!self.hasClass("used")){  //continue only if class "used" is not present
          if(dataString==""){
          } else{
            jQuery.ajax({
              type: "POST",
              url: "' . $actionFile . '",
              data: dataString,
              cache: false,
              success: function(html){
              self.addClass("used");
              }
            });
          }
          return false;
        }
      }
    });
  });
</script>';

    echo '<div class="newsletter">
        <input type="text" class="field" value="" placeholder="Din e-postadress:" id="pren-email"/>
        <input type="submit" class="button" value="Prenumerera" id="pren"/>

        <a href="" class="anonymous">» Lorem ipsum anonymitet</a>
        <a href="" class="stop">» Avsluta prenumeration</a>
      </div>';
  }

}

/**
 * get the staff list by companyId
 */
function securitasWSgetStaffList($companyId) {
  $lime = new SecuritasWS();
  $lime->getStaffList($companyId);
}

/**
 * get the staff list by companyId
 */
function securitasWSeditStaff($personId) {
  $lime = new SecuritasWS();
  $lime->editPerson($personId);
}

/**
 * Shortcode [LimeStaffList]
 * @param <type> $atts
 * @return <type>
 */
/*
  function lime_staff_list($atts) {
  $lime = new SecuritasWS();
  $lime->getStaffList();
  }

  add_shortcode('LimeStaffList', 'lime_staff_list');
 */


add_action('admin_menu', 'lime_plugin_menu');  //network_admin_menu

function lime_plugin_menu() {
  //add_menu_page('Email för nyhetsbrev', 'Email', 'manage_options', __FILE__, 'twentyfourEmailBinListPage');
  add_options_page('Lims WS Plugin Options', 'Securitas WS', 'manage_options', 'limeWS', 'wSOptionsPage');
}

function wSOptionsPage() {
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }
  $lime_url = get_option('lime_url');
  if (isset($_POST['lime_url'])) {
    $lime_url = $_POST['lime_url'];
    update_option('lime_url', $lime_url);
    echo '<div class="updated"><p><strong>Updated the URL</strong></p></div>';
  }
  echo '<div class="wrap">';
  echo '<p>The Lime Web Service URL</p>';
  echo '<form name="form1" method="post" action="">';
  echo '<input type="hidden" name="hidden_field" value="Y">';
  echo '<p>';
  echo '  <input type="text" name="lime_url" value="' . $lime_url . '" size="100">';
  echo '</p><hr />';
  echo '<p class="submit">';
  echo '  <input type="submit" name="Submit" class="button-primary" value="Save Changes" />';
  echo '</p>';
  echo '</form>';
  echo '</div>';
}

