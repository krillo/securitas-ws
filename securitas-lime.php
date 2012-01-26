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
   * Return the markup to edit the person by personId
   * A jQuery-script to handle the insert will also be added to the markup
   * 
   * @param type $personId 
   */
  public function editPerson($personId) {
    $response = $this->lime->getPerson($personId);
    //var_dump($response);
    
    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api_lime_update_person.php";
    echo '<script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery("#save-person").click(function(event) {
      event.preventDefault();
      var self = jQuery(this);
      var firstname = jQuery("#firstname").val();
      var familyname = jQuery("#familyname").val();
      var cellphone = jQuery("#cellphone").val();
      var email = jQuery("#email").val();
      var idperson = jQuery("#idperson").val();      
      //alert("firstname: " + firstname + " familyname: " + familyname + " cellphone: " + cellphone + " email: " + email + " idperson: " + idperson);

      //get the checkboxes default them to 0
      var admin = jQuery("#admin:checked").val();
      if(admin === undefined){admin = "0";}
      var lc = jQuery("#lc:checked").val();
      if(lc === undefined){lc = "0";}
      var portal = jQuery("#portal:checked").val();
      if(portal === undefined){portal = "0";}
      //alert("admin: " + admin + " lc: " + lc + " portal: " + portal);
      
      dataString = "firstname=" + firstname + "&familyname=" + familyname + "&cellphone=" + cellphone + "&email=" + email + "&idperson=" + idperson + "&admin=" + admin + "&lc=" + lc + "&portal=" + portal;
      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){
              console.log(data);
              
              jQuery("#krillo").html("KRILLO");
            }
        });


    });
  });
</script>';
    
        
    $portal = '';
    $elegible = '';
    $admin = '';
    foreach ($response as $value) {
      if($value->attributes()->authorizedportal == '1'){
          echo "Portal";
          $portal = ' checked ';
      }
      if($value->attributes()->authorizedarc == '1'){
          echo "LC";
          $elegible = ' checked ';
      }        
      if($value->attributes()->admninrights == '1'){
          echo "admin";
          $admin = ' checked ';
      }        
        
      $output = '<div id="list-staff">';
      $output .= '<ul>';
      $output .= '<li>';
      $output .= '<div class="staff-container">';
      $output .= '<form class="form" method="get" action="#">';
      $output .= '<fieldset>';
      $output .= '<div class="staff-info">';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="name">Name</label></div>';
      $output .= '<input type="text" class="name" value="' . $value->attributes()->firstname . '" id="firstname" name="firstname">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="lastname">Last name</label></div>';
      $output .= '<input type="text" class="lastname" value="' . $value->attributes()->familyname . '" id="familyname" name="familyname">';
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
      $output .= '<input type="text" class="mobile" value="' . $value->attributes()->cellphone . '" id="cellphone" name="cellphone">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="email">E-mail</label></div>';
      $output .= '<input type="text" class="email" value="' . $value->attributes()->email . '" id="email" name="email">';
      $output .= '</div>';
      $output .= '<div class="pp-wrap">';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="admin" id="admin" '.$admin.'/>';
      $output .= '<div class="pp-checkbox">Technical Administrator</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="lc"  id="lc" '.$elegible.'/>';
      $output .= '<div class="pp-checkbox">Elegible LC</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="portal" id="portal" '.$portal.'/>';
      $output .= '<div class="pp-checkbox">Elegible Portal</div>';
      $output .= '</div>';
      $output .= '</div>';
      $output .= '<p><!--staff-info--></p>';
      $output .= '<div class="staff-buttons">';
      $output .= '<input type="hidden" name="idperson" id="idperson" value="' . $value->attributes()->idperson . '">';
      $output .= '<input type="submit" class="wpcf7-submit" id="save-person" value="Save">';
      $output .= '</div>';
      $output .= '</fieldset>';
      $output .= '</form>';
      $output .= '</div>';
      $output .= '<p><!--staff-container-->';
      $output .= '</li>';
      $output .= '</ul>';
      $output .= '</div>';
      $output .= '<div id="krillo"></div>';
    }
    echo $output;
  }

  
  

  
  
  
  
  /**
   * Return the markup to add a person by companyId
   * A jQuery-script to handle the insert will also be added to the markup
   * 
   * @param type $companyId 
   */
  public function addPerson($companyId) {
    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api_lime_add_person.php";
    
    echo '<script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery("#save-person").click(function(event) {
      event.preventDefault();
      var self = jQuery(this);
      var firstname = jQuery("#firstname").val();
      var familyname = jQuery("#familyname").val();
      var cellphone = jQuery("#cellphone").val();
      var email = jQuery("#email").val();
      var idcompany = jQuery("#idcompany").val();      
      //alert("firstname: " + firstname + " familyname: " + familyname + " cellphone: " + cellphone + " email: " + email + " idcompany: " + idcompany);

      //get the checkboxes default them to 0
      var admin = jQuery("#admin:checked").val();
      if(admin === undefined){admin = "0";}
      var lc = jQuery("#lc:checked").val();
      if(lc === undefined){lc = "0";}
      var portal = jQuery("#portal:checked").val();
      if(portal === undefined){portal = "0";}
      //alert("admin: " + admin + " lc: " + lc + " portal: " + portal);
      
      dataString = "firstname=" + firstname + "&familyname=" + familyname + "&cellphone=" + cellphone + "&email=" + email + "&idcompany=" + idcompany + "&admin=" + admin + "&lc=" + lc + "&portal=" + portal;
      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){
              console.log(data);
              
              jQuery("#krillo").html("KRILLO");
            }
        });


    });
  });
</script>';
    
     
        
      $output = '<div id="list-staff">';
      $output .= '<ul>';
      $output .= '<li>';
      $output .= '<div class="staff-container">';
      $output .= '<form class="form" method="get" action="#">';
      $output .= '<fieldset>';
      $output .= '<div class="staff-info">';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="name">Name</label></div>';
      $output .= '<input type="text" class="name" value="" id="firstname" name="firstname">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="lastname">Last name</label></div>';
      $output .= '<input type="text" class="lastname" value="" id="familyname" name="familyname">';
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
      $output .= '<input type="text" class="mobile" value="" id="cellphone" name="cellphone">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="email">E-mail</label></div>';
      $output .= '<input type="text" class="email" value="" id="email" name="email">';
      $output .= '</div>';
      $output .= '<div class="pp-wrap">';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="admin" id="admin" />';
      $output .= '<div class="pp-checkbox">Technical Administrator</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="lc"  id="lc" />';
      $output .= '<div class="pp-checkbox">Elegible LC</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="portal" id="portal" />';
      $output .= '<div class="pp-checkbox">Elegible Portal</div>';
      $output .= '</div>';
      $output .= '</div>';
      $output .= '<p><!--staff-info--></p>';
      $output .= '<div class="staff-buttons">';
      $output .= '<input type="hidden" name="idcompany" id="idcompany" value="'.$companyId.'">';
      $output .= '<input type="submit" class="wpcf7-submit" id="save-person" value="Save">';
      $output .= '</div>';
      $output .= '</fieldset>';
      $output .= '</form>';
      $output .= '</div>';
      $output .= '<p><!--staff-container-->';
      $output .= '</li>';
      $output .= '</ul>';
      $output .= '</div>';
      $output .= '<div id="krillo"></div>';
      
      
    echo $output;
  }

  
  
  /**
   * Do the update in  
   * @param type $firstname
   * @param type $familyname
   * @param type $cellphone
   * @param type $email
   * @param type $idperson
   * @param type $admin
   * @param type $lc
   * @param type $portal
   * @param type $idcompany
   * @param type $position
   * @param type $ended
   * @return type 
   */
  public function updatePerson($firstname,$familyname,$cellphone,$email,$idperson,$admin,$lc,$portal, $idcompany, $position, $ended){
     return $this->lime->updatePerson($firstname,$familyname,$cellphone,$email,$idperson,$admin,$lc,$portal, $idcompany, $position, $ended);
  }
  

}  //end class SecuritasWS







/*************************************************
 * Call these functions from the wordpress theme
 *************************************************/


/**
 * Get the staff list by companyId
 * 
 * @param type $companyId 
 */
function securitasWSgetStaffList($companyId) {
  $lime = new SecuritasWS();
  $lime->getStaffList($companyId);
}

/**
 * Edit a person by personId
 * 
 * @param type $personId 
 */
function securitasWSeditStaff($personId) {
  $lime = new SecuritasWS();
  $lime->editPerson($personId);
}


/**
 * Add a person in the companyId
 * 
 * @param type $companyId 
 */
function securitasWSaddStaff($companyId) {
  $lime = new SecuritasWS();
  $lime->addPerson($companyId);
}





/************************************
 * Wordpress admin pages
 ************************************/

add_action('admin_menu', 'lime_plugin_menu');  //network_admin_menu

function lime_plugin_menu() {
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

