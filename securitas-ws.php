<?php

/**
  Plugin Name: Securitas-WS
  Plugin URI: http://securitas.com/
  Description: Integration to Securitas Web Services
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

  public $debug = false;
  public $logFile = null;
  //plugin db version
  public static $myDbVersion = "0.1";
  public $lime = null;

  function __construct() {
    if (get_option("sec_log") == '1') {
      $this->debug = true;
    } else {
      $this->debug = false;
    }

    $this->logFile = __DIR__ . '/ws.log';
    global $wpdb;
    $url = null;
    $url = get_option("sec_lime_url");
    if (!isset($url)) {
      echo 'ERROR no url to web service set';
    } else {
      include_once 'LimeWService.php';
      $this->lime = new LimeWService($url);
    }
  }

  /**
   * Just for testing
   */
  public function debugOutput() {
    $response = $this->lime->selectFromCompany(1);  //funkar
    var_dump($response);
  }

  /**
   * Get get the securitas contact information
   * The first time store it as user_meta
   * Get also installationno 
   * 
   * @param type $idcompany 
   */
  public function getSecuritasContact() {
    $wpUserId = get_current_user_id();
    $secContactDate = get_user_meta($wpUserId, 'sec_contact_date', true);
    if ($secContactDate == '' || $this->isOldData($secContactDate)) {
      $idcompany = get_user_meta($wpUserId, 'sec_idcompany', true);     
      $xml = $this->lime->selectFromCompany($idcompany);
      $coworkername = 'coworker.name';
      $coworkerphone = 'coworker.phone';
      $coworkeremail = 'coworker.email';
      $installationno = 'installationno';
      $data['name'] = (string) $xml->company->attributes()->$coworkername;
      $data['phone'] = (string) $xml->company->attributes()->$coworkerphone;
      $data['email'] = (string) $xml->company->attributes()->$coworkeremail;
      $data['installationno'] = (string) $xml->company->attributes()->$installationno;
      //echo 'krillo ' . $data['installationno'];
      update_user_meta($wpUserId, 'sec_contact_name', $data['name']);
      update_user_meta($wpUserId, 'sec_contact_phone', $data['phone']);
      update_user_meta($wpUserId, 'sec_contact_email', $data['email']);
      update_user_meta($wpUserId, 'sec_contact_date', date("Y-m-d"));
      update_user_meta($wpUserId, 'sec_installationno', $data['installationno']);      
    } else {
      $data['name'] = get_user_meta($wpUserId, 'sec_contact_name', true);
      $data['phone'] = get_user_meta($wpUserId, 'sec_contact_phone', true);
      $data['email'] = get_user_meta($wpUserId, 'sec_contact_email', true);
    }
    return $data;
  }

  /**
   * returns true if date is older than one day
   * @param type $sStartDate
   * @return boolean 
   */
  private function isOldData($sStartDate) {
    $sStartTime = strtotime($sStartDate);
    $now = time();
    $diff = $now - $sStartTime;
    //echo '$sStartDate: '. $sStartDate . ' $sStartTime: ' . $sStartTime . ' $now: '. $now . ' $diff: ' . $diff;    
    $oneDay = 24*60*60;  //24 hours
    //$oneDay = 10;  //10 seconds for test only
    if ($diff > $oneDay) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Get the markup of all the staff
   */
  public function getStaffList($companyId) {
    $response = $this->lime->selectFromPerson($companyId);
    //var_dump($response);
    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api_lime_delete_person.php";

    //access to edit?
    $fullAccess = false;
    if (get_user_meta(get_current_user_id(), 'sec_technician', true) == '1') {
      $fullAccess = true;
    }

    if ($fullAccess) {
      $confirm = __("Do you want to remove the user?", 'securitas-ws');
      echo '
<script type="text/javascript">
  function deletePerson(idperson){
    var answer = confirm ("'.$confirm.'");
    if (answer){
        jQuery.ajax({
        type: "POST",
        url: "' . $actionFile . '",
        data: "idperson=" + idperson,
        success: function(data){
          jQuery("#loading").hide();
          //json array returned
          console.log(data);
          var resultDiv = "#result_" + idperson;
          if(data.status == "deleted"){
            window.location.reload();
          } else {
            jQuery(resultDiv).html(data.status);
          }
        }
      });
    } else{
      return false;
    }
      return false;
  }

  function editPerson(idperson){  
    window.location.href = "staff/edit-staff?idperson=" + idperson;    
  }
  
</script>
';
    }

    $output = '<ul>';
    foreach ($response as $value) {
      $portal = '';
      $lc = '';
      $admin = '';
      if ($value->attributes()->authorizedportal == '1') {
        $portal = __('Portal', 'securitas-ws');
      }
      if ($value->attributes()->authorizedarc == '1') {
        $lc = __('LC', 'securitas-ws');
      }
      if ($value->attributes()->admninrights == '1') {
        $admin = __('Administrator', 'securitas-ws');
      }
      if ($value->attributes()->ended == '0') {
        $position = "position.text";
        $output .= '<li>';
        $output .= '<div class="staff-container">';
        $output .= '<div class="staff-info">';
        $output .= '<div><strong>' . __('Name', 'securitas-ws') . '</strong>';
        $output .= '<p>' . $value->attributes()->firstname . '</p>';
        $output .= '</div>';
        $output .= '<div><strong>' . __('Last name', 'securitas-ws') . '</strong>';
        $output .= '<p>' . $value->attributes()->familyname . '</p>';
        $output .= '</div>';
        $output .= '<div><strong>' . __('Function', 'securitas-ws') . '</strong>';
        $output .= '<p>' . $value->attributes()->$position . '</p>';
        $output .= '</div>';
        $output .= '<div><strong>' . __('E-mail', 'securitas-ws') . '</strong>';
        $output .= '<p><a href="mailto:' . $value->attributes()->email . '">' . $value->attributes()->email . '</a></p>';
        $output .= '</div>';
        $output .= '<div><strong>' . __('Mobile', 'securitas-ws') . '</strong>';
        $output .= '<p>' . $value->attributes()->cellphone . '</p>';
        $output .= '</div>';
        $output .= '<div><strong>' . __('Eligibility', 'securitas-ws') . '</strong>';
        $output .= '<ul>';
        $output .= '<li>' . $lc . '</li>';
        $output .= '<li>' . $portal . '</li>';
        $output .= '<li>' . $admin . '</li>';
        $output .= '</ul>';
        $output .= '</div>';
        $output .= '</div>';
        if ($fullAccess) {
          $output .= '<div id="staff-buttons">';
          $output .= '<input type="hidden" name="idperson" id="idperson" value="' . $value->attributes()->idperson . '" />';
          $output .= '<input type="hidden" name="wpuserid" id="wpuserid" value="' . $value->attributes()->wpuserid . '">';
          $output .= '<input class="wpcf7-submit" type="button" value="X" onclick="deletePerson(' . $value->attributes()->idperson . ');return false;" />';
          $output .= '<input class="wpcf7-submit" type="button" value="' . __('Edit', 'securitas-ws') . '"  onclick="editPerson(' . $value->attributes()->idperson . ');return false;"/>';
          $output .= '</div>';
          $output .= '<div id="result_'. $value->attributes()->idperson . '"></div>';
        }
        $output .= '</div>';
        $output .= '</li>';
        
      }
    }
    $output .= '<ul>';
    echo $output;
  }

  /**
   * Edit the profile
   */
  public function editProfile($wpuserid) {

    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api_lime_update_profile.php";
    echo '<script type="text/javascript">
  jQuery(document).ready(function(){
  
    //progress wheel
    jQuery("#loading")
      .hide()  // hide it initially
      .ajaxStart(function() {
        jQuery(this).show();
      })
      .ajaxStop(function() {
        jQuery(this).hide();
    });

    jQuery("#save-profile").click(function(event) {
      event.preventDefault();
      var self = jQuery(this);
      var firstname = jQuery("#firstname").val();
      var familyname = jQuery("#familyname").val();
      var cellphone = jQuery("#cellphone").val();
      var email = jQuery("#email").val();
      var idperson = jQuery("#idperson").val();    
      var idcompany = jQuery("#idcompany").val();  
      var position = jQuery("#position").val(); 
      var wpuserid = jQuery("#wpuserid").val();             
      dataString = "firstname=" + firstname + "&familyname=" + familyname + "&cellphone=" + cellphone + "&email=" + email+ "&position=" + position;                 

      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){
              jQuery("#loading").hide();
               //json array returned
              console.log(data);
              jQuery("#success").html(data.status);
              window.location.reload();
            }
        });


    });
  });
</script>
';


    $wpUserData = get_userdata($wpuserid);
    $wpUserMeta = get_user_meta($wpuserid);
    //print_r($wpUserMeta);
    //print_r($wpUserMeta);
    //the dropdown
    $sales = '';
    $technician = '';
    $marketing = '';
    $other = '';

    $cellphone = '';
    if (isset($wpUserMeta['sec_cellphone'][0])) {
      $cellphone = $wpUserMeta['sec_cellphone'][0];
    }

    $position = '0';
    if (isset($wpUserMeta['sec_position'][0])) {
      $position = $wpUserMeta['sec_position'][0];
    }
    switch ($position) {
      case ';2161001;':
        $sales = 'selected';
        break;
      case ';2163001;':
        $technician = 'selected';
        break;
      case ';2164001;':
        $marketing = 'selected';
        break;
      case ';3065001;':
        $other = 'selected';
        break;
      default:  //default to other
        $other = 'selected';
        break;
    }
    $output = '<form class="form" method="POST" action="#">';
    $output .= '<div class="pp-transmitter">';
    $output .= '<fieldset>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="company">' . __('Company / Employer:', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" value="' . $wpUserMeta['sec_companyname'][0] . '" name="company" id="company" disabled>';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="firstname">' . __('Name', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" value="' . $wpUserMeta['first_name'][0] . '" name="firstname" id="firstname">';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="familyname">' . __('Last name', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" value="' . $wpUserMeta['last_name'][0] . '" name="familyname" id="familyname">';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="position">' . __('Choose role', 'securitas-ws') . '</label></div>';
    $output .= '<select id="position">';
    $output .= '<option value="2161001" ' . $sales . '>' . __('Sales', 'securitas-ws') . '</option>';
    $output .= '<option value="2163001" ' . $technician . '>' . __('Technician', 'securitas-ws') . '</option>';
    $output .= '<option value="2164001" ' . $marketing . '>' . __('Marketing', 'securitas-ws') . '</option>';
    $output .= '<option value="3065001" ' . $other . '>' . __('Other', 'securitas-ws') . '</option>';
    $output .= '</select>';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="email">' . __('E-mail', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" value="' . $wpUserData->data->user_email . '" name="email" id="email">';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="cellphone">' . __('Mobile', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" value="' . $cellphone . '" name="cellphone" id="cellphone">';
    $output .= '</div>';
    $output .= '<div class="trans-buttons">';
    $output .= '<input type="submit" class="wpcf7-submit" id="save-profile" value="' . __('Update', 'securitas-ws') . '">';
    $output .= '</div>';
    $output .= '</fieldset>';
    $output .= '</div>';
    $output .= '</form>';
    echo $output;


    $actionFile2 = $pluginRoot . "/api_change_password.php";
    echo '
 <script type="text/javascript">
    jQuery(document).ready(function(){
  
      jQuery("#passwordform").validate({
          rules: {
            pwd: "required",
            confirm: {
              equalTo: "#pwd"
            }
          },          
          messages: {
            confirm: {
              equalTo: "' . __('The passwords does not match', 'securitas-ws') . '"
            }         
          },
          submitHandler: function(form) {
            var pwd = jQuery("#pwd").val();
            var confirm = jQuery("#confirm").val();
            dataString = "pwd=" + pwd;                 
            jQuery.ajax({
              type: "POST",
              url: "' . $actionFile2 . '",
              data: dataString,
              cache: false,
              success: function(data){
                jQuery("#loading").hide();
                //json array returned
                console.log(data);                
                jQuery("#success").html(data.status);
              }
            });
          }
        });        

   });
</script>
';
    $output2 = '
      <form class="form" method="POST" action="" id="passwordform">
        <div class="pp-transmitter">
          <fieldset>
            <div>
              <div class="labels"><label for="pwd">' . __('Change password', 'securitas-ws') . '</label></div>
              <input type="password" value="" name="pwd" id="pwd">
            </div>
            <div>
              <div class="labels"><label for="confirm">' . __('Confirm password', 'securitas-ws') . '</label></div>
              <input type="password" value="" name="confirm" id="confirm">
            </div>
            <div>
              <input type="submit" id="change-pass" class="wpcf7-submit" value="' . __('Change', 'securitas-ws') . '">
            </div>
          </fieldset>
        </div>
      </form>
      <div id="success"></div>
      <div id="loading"><img src="' . $pluginRoot . '/img/ajax-loader.gif" alt=""></div> 
      </br></br>';
    echo $output2;
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
    $userId = get_current_user_id();

    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api_lime_update_person.php";
    echo '<script type="text/javascript">
  jQuery(document).ready(function(){
  
    //progress wheel
    jQuery("#loading")
      .hide()  // hide it initially
      .ajaxStart(function() {
        jQuery(this).show();
      })
      .ajaxStop(function() {
        jQuery(this).hide();
    });

    jQuery("#save-person").click(function(event) {
      event.preventDefault();
      var self = jQuery(this);
      var firstname = jQuery("#firstname").val();
      var familyname = jQuery("#familyname").val();
      var cellphone = jQuery("#cellphone").val();
      var email = jQuery("#email").val();
      var idperson = jQuery("#idperson").val();    
      var idcompany = jQuery("#idcompany").val(); 
      var companyname = jQuery("#companyname").val();  
      var original_lc = jQuery("#original_lc").val();    
      var position = jQuery("#position").val(); 
      var wpuserid = jQuery("#wpuserid").val();            

      //get the checkboxes default them to 0
      var admin = jQuery("#admin:checked").val();
      if(admin === undefined){admin = "0";}
      var lc = jQuery("#lc:checked").val();
      if(lc === undefined){lc = "0";}
      var portal = jQuery("#portal:checked").val();
      if(portal === undefined){portal = "0";}
      
      dataString = "firstname=" + firstname + "&familyname=" + familyname + "&cellphone=" + cellphone + "&email=" + email
                    + "&idperson=" + idperson + "&admin=" + admin + "&lc=" + lc + "&portal=" + portal + "&position=" + position
                    + "&idcompany=" + idcompany + "&companyname=" + companyname + "&original_lc=" + original_lc + "&ended=0" + "&wpuserid=" + wpuserid;
      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){
               //json array returned
              console.log(data);
              jQuery("#success").html(data.status);
            }
        });


    });
  });
</script>';


    $portal = '';
    $elegible = '';
    $admin = '';
    foreach ($response as $value) {
      //the checkboxes
      if ($value->attributes()->authorizedportal == '1') {
        //echo "Portal";
        $portal = ' checked ';
      }
      if ($value->attributes()->authorizedarc == '1') {
        //echo "LC";
        $elegible = ' checked ';
      }
      if ($value->attributes()->admninrights == '1') {
        //echo "admin";
        $admin = ' checked ';
      }

      //the dropdown
      $sales = '';
      $technician = '';
      $marketing = '';
      $other = '';
      //echo $value->attributes()->position;
      switch ($value->attributes()->position) {
        case ';2161001;':
          $sales = 'selected';
          break;
        case ';2163001;':
          $technician = 'selected';
          break;
        case ';2164001;':
          $marketing = 'selected';
          break;
        case ';3065001;':
          $other = 'selected';
          break;
        default:  //default to other
          $other = 'selected';
          break;
      }
      //$position = $this->lime->positionTranslate('code', $arg)

      $output = '<div id="list-staff">';
      $output .= '<ul>';
      $output .= '<li>';
      $output .= '<div class="staff-container">';
      $output .= '<form class="form" method="get" action="#">';
      $output .= '<fieldset>';
      $output .= '<div class="staff-info">';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="name">' . __('Name', 'securitas-ws') . '</label></div>';
      $output .= '<input type="text" class="name" value="' . $value->attributes()->firstname . '" id="firstname" name="firstname">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="lastname">' . __('Last name', 'securitas-ws') . '</label></div>';
      $output .= '<input type="text" class="lastname" value="' . $value->attributes()->familyname . '" id="familyname" name="familyname">';
      $output .= '</div>';
      $output .= '<div class="pp-select">';
      $output .= '<div class="labels"><label for="position">' . __('Choose role', 'securitas-ws') . '</label></div>';
      $output .= '<select id="position">';
      $output .= '<option value="2161001" ' . $sales . '>' . __('Sales', 'securitas-ws') . '</option>';
      $output .= '<option value="2163001" ' . $technician . '>' . __('Technician', 'securitas-ws') . '</option>';
      $output .= '<option value="2164001" ' . $marketing . '>' . __('Marketing', 'securitas-ws') . '</option>';
      $output .= '<option value="3065001" ' . $other . '>' . __('Other', 'securitas-ws') . '</option>';
      $output .= '</select>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="mobile">' . __('Mobile', 'securitas-ws') . '</label></div>';
      $output .= '<input type="text" class="mobile" value="' . $value->attributes()->cellphone . '" id="cellphone" name="cellphone">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="email">' . __('E-mail', 'securitas-ws') . '</label></div>';
      $output .= '<input type="text" class="email" value="' . $value->attributes()->email . '" id="email" name="email">';
      $output .= '</div>';
      $output .= '<div class="pp-wrap">';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="admin" id="admin" ' . $admin . '/>';
      $output .= '<div class="pp-checkbox">' . __('Technical Administrator', 'securitas-ws') . '</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="lc"  id="lc" ' . $elegible . '/>';
      $output .= '<div class="pp-checkbox">' . __('Elegible LC', 'securitas-ws') . '</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="portal" id="portal" ' . $portal . '/>';
      $output .= '<div class="pp-checkbox">' . __('Elegible Portal', 'securitas-ws') . '</div>';
      $output .= '</div>';
      $output .= '</div>';
      $output .= '<p><!--staff-info--></p>';
      $output .= '<div class="staff-buttons">';
      $output .= '<input type="hidden" name="idperson" id="idperson" value="' . $value->attributes()->idperson . '">';
      $output .= '<input type="hidden" name="idcompany" id="idcompany" value="' . get_user_meta($userId, 'sec_idcompany', true) . '">';
      $output .= '<input type="hidden" name="companyname" id="companyname" value="' . get_user_meta($userId, 'sec_companyname', true) . '">';
      $output .= '<input type="hidden" name="original_lc" id="original_lc" value="' . $value->attributes()->authorizedarc . '">';
      $output .= '<input type="hidden" name="wpuserid" id="wpuserid" value="' . $value->attributes()->wpuserid . '">';
      $output .= '<input type="submit" class="wpcf7-submit" id="save-person" value="' . __('Save', 'securitas-ws') . '">';
      $output .= '</div>';
      $output .= '</fieldset>';
      $output .= '</form>';
      $output .= '</div>';
      $output .= '<p><!--staff-container-->';
      $output .= '</li>';
      $output .= '</ul>';
      $output .= '</div>';
      $output .= '<div id="success"></div><div id="loading"><img src="' . $pluginRoot . '/img/ajax-loader.gif" alt=""></div>';
    }
    echo $output;
  }

  /**
   * Return the markup to add a person by companyId
   * A jQuery-script to handle the insert will also be added to the markup
   * 
   * @param type $companyId 
   */
  public static function addPerson($companyId) {
    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api_lime_add_person.php";
    $s0 = 'Error 4';
    $s1 = __('Added successfully', 'securitas-ws');
    $s2 = __('User already existed, and is now activated on portal', 'securitas-ws');

    echo '<script type="text/javascript">
  jQuery(document).ready(function(){


    //progress wheel
    jQuery("#loading")
      .hide()  // hide it initially
      .ajaxStart(function() {
        jQuery(this).show();
      })
      .ajaxStop(function() {
        jQuery(this).hide();
    });


/*
jQuery.ajaxSetup({
  beforeSend: function() {
     $("#loading").show()
  },
  complete: function(){
     $("#loading").hide()
  },
  success: function() {}
});
*/

    jQuery("#save-person").click(function(event) {
      event.preventDefault();
      var self = jQuery(this);
      var firstname = jQuery("#firstname").val();
      var familyname = jQuery("#familyname").val();
      var cellphone = jQuery("#cellphone").val();
      var email = jQuery("#email").val();
      var idcompany = jQuery("#idcompany").val();
      var companyname = jQuery("#companyname").val();      
      var position = jQuery("#position").val(); 

      //get the checkboxes default them to 0
      var admin = jQuery("#admin:checked").val();
      if(admin === undefined){admin = "0";}
      var lc = jQuery("#lc:checked").val();
      if(lc === undefined){lc = "0";}
      var portal = jQuery("#portal:checked").val();
      if(portal === undefined){portal = "0";}
      
      dataString = "firstname=" + firstname + "&familyname=" + familyname + "&cellphone=" + cellphone + "&email=" + email
                   + "&idcompany=" + idcompany+ "&companyname=" + companyname + "&admin=" + admin + "&lc=" + lc + "&portal=" + portal + "&position=" + position;
      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){  //json array returned
              jQuery("#loading").hide();
              var s = "ok;"
              console.log(data);
              var status = data.status;
              switch(status){              
                case 0:
                  s = "' . $s0 . '";
                  break;
                case 1:
                  s = "' . $s1 . '";
                  break;
                case 2:
                  s = "' . $s2 . '";
                  break;
                default:
                  s = "error 3";
              }
              
              jQuery("#success").html(s);
              //jQuery("#success").html(data.status);
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
    $output .= '<div class="labels"><label for="name">' . __('Name', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" class="name" value="" id="firstname" name="firstname">';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="lastname">' . __('Last name', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" class="lastname" value="" id="familyname" name="familyname">';
    $output .= '</div>';
    $output .= '<div class="pp-select">';
    $output .= '<div class="labels"><label for="position">' . __('Choose role', 'securitas-ws') . '</label></div>';
    $output .= '<select id="position">';
    $output .= '<option value="2161001" >' . __('Sales', 'securitas-ws') . '</option>';
    $output .= '<option value="2163001" >' . __('Technician', 'securitas-ws') . '</option>';
    $output .= '<option value="2164001" >' . __('Marketing', 'securitas-ws') . '</option>';
    $output .= '<option value="3065001" >' . __('Other', 'securitas-ws') . '</option>';
    $output .= '</select>';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="mobile">' . __('Mobile', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" class="mobile" value="" id="cellphone" name="cellphone">';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<div class="labels"><label for="email">' . __('E-mail', 'securitas-ws') . '</label></div>';
    $output .= '<input type="text" class="email" value="" id="email" name="email">';
    $output .= '</div>';
    $output .= '<div class="pp-wrap">';
    $output .= '<input type="checkbox" value="1" class="pp-check" name="admin" id="admin" />';
    $output .= '<div class="pp-checkbox">' . __('Technical Administrator', 'securitas-ws') . '</div>';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<input type="checkbox" value="1" class="pp-check" name="lc"  id="lc" />';
    $output .= '<div class="pp-checkbox">' . __('Elegible LC', 'securitas-ws') . '</div>';
    $output .= '</div>';
    $output .= '<div>';
    $output .= '<input type="checkbox" value="1" class="pp-check" name="portal" id="portal" />';
    $output .= '<div class="pp-checkbox">' . __('Elegible Portal', 'securitas-ws') . '</div>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<p><!--staff-info--></p>';
    $output .= '<div class="staff-buttons">';
    $output .= '<input type="hidden" name="idcompany" id="idcompany" value="' . $companyId . '">';
    $output .= '<input type="hidden" name="companyname" id="companyname" value="' . get_user_meta(get_current_user_id(), 'sec_companyname', true) . '">';
    $output .= '<input type="submit" class="wpcf7-submit" id="save-person" value="' . __('Save', 'securitas-ws') . '">';
    $output .= '</div>';
    $output .= '</fieldset>';
    $output .= '</form>';
    $output .= '</div>';
    $output .= '<p><!--staff-container-->';
    $output .= '</li>';
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div id="success"></div><div id="loading"><img src="' . $pluginRoot . '/img/ajax-loader.gif" alt=""></div>';

    echo $output;
  }

  /**
   * insert a new person
   * 
   * @param type $firstname
   * @param type $familyname
   * @param type $cellphone
   * @param type $email
   * @param type $idperson
   * @param type $admin
   * @param type $lc
   * @param type $original_lc
   * @param type $portal
   * @param type $idcompany
   * @param type $companyname
   * @param type $position
   * @param type $ended
   * @param type $wpuserid 
   */
  public function insertPerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $original_lc, $portal, $idcompany, $companyname, $position, $ended, $wpuserid) {
    error_reporting(E_ERROR | E_PARSE);
    $exists = $this->lime->personExists($email);
    //print_r($exists);
    switch ($exists['idperson']) {
      case 'error':
        $response = array('status' => 0);
        break;
      case '0':   //add the person to the WS
        $securitasUserId = $this->lime->insertPerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $portal, $idcompany, $position, $ended, $wpuserid);
        if ($securitasUserId > 0 && ($portal == 1 || $admin == 1)) {
          $userData = $this->createUser($email, $firstname, $familyname, $idcompany, $companyname, $admin, $securitasUserId, $cellphone);
          if (gettype($userData) == 'array') {  //the user was just created - send a welcome email   
            $this->sendEmail('portal', $firstname, $familyname, $cellphone, $email, $lc, $portal, $userData);
            //add the wpuserid to the WebService
            $success = $this->lime->updatePerson($firstname, $familyname, $cellphone, $email, $securitasUserId, $admin, $lc, $portal, $idcompany, $position, $ended, $userData['user_id']);
          }
        }
        //$response = array('status' => __('Added successfully', 'securitas-ws'));
        $response = array('status' => 1);
        break;
      default:
        //user exists on ws - activate her
        $securitasUserId = $exists['idperson'];
        $this->lime->updateProfile($firstname, $familyname, $cellphone, $email, $securitasUserId, $position);
        if ($securitasUserId > 0 && ($portal == 1 || $admin == 1)) {
          $userData = $this->createUser($email, $firstname, $familyname, $idcompany, $companyname, $admin, $securitasUserId, $cellphone);
          if (gettype($userData) == 'array') {  //the user was just created - send a welcome email   
            $this->sendEmail('portal', $firstname, $familyname, $cellphone, $email, $lc, $portal, $userData);
            //add the wpuserid to the WebService
            $success = $this->lime->updatePerson($firstname, $familyname, $cellphone, $email, $securitasUserId, $admin, $lc, $portal, $idcompany, $position, $ended, $userData['user_id']);
          }
        }
        //$response = array('status' => __('User already existed, and is now activated on portal', 'securitas-ws'));
        //$response = array('status' => 'User already existed, and is now activated on portal');
        $response = array('status' => 2);
        break;
    }

    //return the result to ajax, write it as json
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
  }

  /**
   * Update the profile, first in wordpress and then on the WebService
   * The user does this herselfe i.e. she is logged in 
   */
  public function updateProfile($firstname, $familyname, $cellphone, $email, $position) {
    $result['status'] = 'Error';
    try {
      $wpUserId = get_current_user_id();
      update_user_meta($wpUserId, 'first_name', $firstname);
      update_user_meta($wpUserId, 'last_name', $familyname);
      update_user_meta($wpUserId, 'sec_position', $position);
      update_user_meta($wpUserId, 'sec_cellphone', $cellphone);
      wp_update_user(array('ID' => $wpUserId, 'user_email' => $email));
      $result['status'] = __('Success', 'securitas-ws');
      //update the WebService
      $wpUserMeta = get_user_meta($wpUserId);
      $success = $this->lime->updateProfile($firstname, $familyname, $cellphone, $email, $wpUserMeta['sec_securitasid'][0], $position);
      if ($success) {
        $result['WPUserIdToWS'] = __('Success', 'securitas-ws');
      }
      //return the result to ajax, write it as json
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Content-type: application/json');
      echo json_encode($result);
    } catch (Exception $exc) {
      $this->saveToFile($this->logFile, (string) $exc->getTraceAsString(), 'ERROR');
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Content-type: application/json');
      echo json_encode($result);
    }
  }

  /**
   * Changes the users password, call this by ajax.
   * returns the result as a data array in json format 
   * @param type $password 
   */
  function changepassword($password) {
    $response = array('status' => __('Error password not changed', 'securitas-ws'));
    try {
      $user_id = get_current_user_id();
      wp_set_password($password, $user_id);  //sets the new password but also logs out the user
      wp_set_auth_cookie($user_id, true);    //set the new authentication cookies - still logged in
      $response['status'] = __('Password changed', 'securitas-ws');
    } catch (Exception $exc) {
      echo $exc->getTraceAsString();
    }

    //return the result to ajax, write it as json
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
  }

  /**
   * Changes the users password, call this by ajax.
   * returns the result as a data array in json format 
   * @param type $password 
   */
  function newpassword($username) {
    $user_id = username_exists($username);
    if (!isset($user_id)) {
      $response = array('status' => __('Wrong user name', 'securitas-ws'));
    } else {
      $password = wp_generate_password();
      wp_set_password($password, $user_id);
      $user_info = get_userdata($user_id);
      $this->sendEmail('newpassword', null, null, null, $user_info->user_email, null, null, null, $username, $password);
      $response = array('status' => __('The new password is sent to your email', 'securitas-ws'));
    }
    //return the result to ajax, write it as json
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
  }

  /**
   * Update person in WS, requires $idperson
   * An insert requires companyId and -1 as $idperson  
   * 
   * If lc-eligibility is changed then send an email to securitas
   * If portal is 1 then create the user and send email to her
   * 
   */
  public function updatePerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $original_lc, $portal, $idcompany, $companyname, $position, $ended, $wpuserid) {
    $result = array('status' => 'Error');
    //do the update on the WS
    $success = $this->lime->updatePerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $portal, $idcompany, $position, $ended, $wpuserid);
    if ($success) {
      $result['WSUser'] = 'updated';

      $wpUserUpdated = false;
      $wpUserId = $wpuserid;
      if ($wpUserId != 0) {   //person already wp-user, update the wp-data     
        update_user_meta($wpUserId, 'first_name', $firstname);
        update_user_meta($wpUserId, 'last_name', $familyname);
        update_user_meta($wpUserId, 'sec_companyname', $companyname);
        update_user_meta($wpUserId, 'sec_idcompany', $idcompany);
        update_user_meta($wpUserId, 'sec_technician', $admin);
        update_user_meta($wpUserId, 'sec_securitasid', $idperson);
        update_user_meta($wpUserId, 'sec_position', $position);
        update_user_meta($wpUserId, 'sec_cellphone', $cellphone);
        wp_update_user(array('ID' => $wpUserId, 'user_email' => $email));

        $wpUserUpdated = true;
        $result['WPUser'] = 'updated, ' . $wpUserId;
        $result['status'] = __('Success', 'securitas-ws');
      }

      if ($portal == 1 && $wpUserId == 0) {   //if access to the portal and no wp-useridthen create a wp-user
        $userData = $this->createUser($email, $firstname, $familyname, $idcompany, $companyname, $admin, $idperson, $cellphone);
        $result['WPUser'] = $userData['user_id'];
        if (gettype($userData) == 'array') {  //the user was just created - send a welcome email   
          $wpUserUpdated = true;
          $this->sendEmail('portal', $firstname, $familyname, $cellphone, $email, $lc, $portal, $userData);
          $idperson = $userData[sec_securitasid];
          $result['WelcomeEmail'] = $email;
          //add the wpuserid to the WebService
          $success = $this->lime->updatePerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $portal, $idcompany, $position, $ended, $userData['user_id']);
          $result['WPUserIdToWS'] = __('Success', 'securitas-ws');
          $result['status'] = __('Success', 'securitas-ws');
        }
      }

      if ($admin == 1 && $wpUserId == 0) {   //if adminster the portal then create a wp-user
        $userData = $this->createUser($email, $firstname, $familyname, $idcompany, $companyname, $admin, $idperson, $cellphone);
        $result['WPUser'] = 'created';
        $result['status'] = __('Success', 'securitas-ws');
        if (gettype($userData) == 'array') {  //the user was just created update WebService with wpuserid  
          $wpUserUpdated = true;
          $idperson = $userData[sec_securitasid];
          $success = $this->lime->updatePerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $portal, $idcompany, $position, $ended, $userData['user_id']);
          $result['WSUser'] = 'updated';
        }
      }


      if ($original_lc != $lc) {  //the LC eligibility has been changed, send mail to securitas
        $this->sendEmail('lc', $firstname, $familyname, $cellphone, $email, $lc, $portal);
        $result['EmailSecuritasSupport'] = 'sent';
        $result['status'] = __('Success', 'securitas-ws');
      }
    }


    //return the result to ajax, write it as json
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($result);
  }

  /**
   * Delete the person
   * 
   * @param type $idperson
   * @return type 
   */
  public function deletePerson($idperson) {
    $result = array('status' => 'Error');    
    $success = $this->lime->deletePerson($idperson);
    if($success){
      global $wpdb; 
      $wpuserid = $wpdb->get_var("select user_id from wp_usermeta where meta_key = 'sec_securitasid' and meta_value = '$idperson'");
      clean_user_cache($wpuserid);
      $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id = %d", $wpuserid) );
		  $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->users WHERE ID = %d", $wpuserid) );
      //$deleted = wp_delete_user($wpuserid);   //delete user in wp
      //if($deleted){
        $result['status'] = 'deleted';
      //}
    }
    
    //return the result to ajax, write it as json
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($result);
  }

  /**
   * Send email
   * There are two $type of mail: 'lc' or 'portal'
   * LC - them send an email to securitas for noting that LC eligibility has changed
   * PORTAL - send an invite email to the person just added to WordPress 
   */
  public function sendEmail($type, $firstname, $familyname, $cellphone, $email, $lc, $portal, $userData = null, $username = null, $password = null) {
    require_once(__DIR__ . '/../../../wp-config.php');
    global $current_user;
    $user_id = $current_user->ID;
    $user_email = $current_user->data->user_email;
    $user_firstname = get_user_meta($user_id, 'first_name', true);
    $user_lastname = get_user_meta($user_id, 'last_name', true);
    $user_company = get_user_meta($user_id, 'sec_companyname', true);
    $sec_installationno = get_user_meta($user_id, 'sec_installationno', true);

    switch ($type) {
      case 'lc':
        $lcAcess = __('NO ACCESS', 'securitas-ws');
        if ($lc == '1') {
          $lcAcess = __('ACCESS', 'securitas-ws');
        }

        $subject = __("LC eligibility changed", 'securitas-ws');
        $message = __("LC eligibility changed to", 'securitas-ws') . " <strong>$lcAcess</strong> " . __("for", 'securitas-ws') . ": <br><br>";
        $message .= __("Company", 'securitas-ws') . ": $user_company <br>";
        $message .= __("Name", 'securitas-ws') . ": $firstname $familyname <br>";
        $message .= __("Email", 'securitas-ws') . ": $email <br>";
        $message .= __("Mobile", 'securitas-ws') . ": $cellphone <br><br><br>";
        $message .= __("The person who did this change is", 'securitas-ws') . ": <br><br>";
        $message .= __("Company", 'securitas-ws') . ": $user_company <br>";
        $message .= __("Installation number", 'securitas-ws') . ": $sec_installationno <br>";        
        $message .= __("Name", 'securitas-ws') . ": $user_firstname $user_lastname <br>";
        $message .= __("Email", 'securitas-ws') . ": $user_email <br>";


        $to = get_option('sec_support_email'); //'kundtjanst.alert@securitas.se';
        $from = get_option('sec_support_email_sender');
        break;
      case 'portal':
        $username = $userData['user_name'];
        $password = $userData['password'];
        $url = get_bloginfo('url');

        $subject = __("Welcome to Securitas Partner Portal", 'securitas-ws');
        $message = __("Hello", 'securitas-ws') . " $firstname $familyname <br>";
        $message .= __("You have been added as a user at Securitas alarmcentral parterportal.", 'securitas-ws') . " <br><br>";
        $message .= '<a href="' . $url . '">' . $url . '</a><br><br>';
        $message .= __("Your username is", 'securitas-ws') . ' <strong>' . $username . ' </strong>' . __("and password is ", 'securitas-ws') . ' <strong>' . $password . ' </strong>' . "<br>";
        $to = $email;
        $from = get_option('sec_support_email_sender');

        break;
      case 'newpassword':
        $url = get_bloginfo('url');

        $subject = __("Your new password", 'securitas-ws');
        $message = __("Your have requested a new temporary password", 'securitas-ws') . " <br>";
        $message .= __("Your username is ", 'securitas-ws') . ' <strong>' . $username . ' </strong>' . __(" and new temporary password is ", 'securitas-ws') . ' <strong>' . $password . ' </strong>' . "<br>";
        $message .= '<a href="' . $url . '">' . $url . '</a><br><br>';
        $to = $email;
        $from = get_option('sec_support_email_sender');
        break;
      default:
        $this->saveToFile($this->logFile, 'Error sending email, 1', 'ERROR');
        return;
        break;
    }

    if ($to != "" && $message != "") {

      //$message = mb_convert_encoding($message, "UTF-8");
      //$message = utf8_decode($message);
      $message = $this->svToHtmlEntitys($message);

      $headers = 'From: ' . $from . ' <' . $from . '>' . "\r\n";
      $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
      $success = wp_mail($to, $subject, $message, $headers);

      if ($success):
        $msg = "Email ($subject)sent to $to";
        $this->saveToFile($this->logFile, $msg, 'INFO');
      else:
        $this->saveToFile($this->logFile, 'Error sending email, 2', 'ERROR');
      endif;
    }
  }

  private function svToHtmlEntitys($m) {
    $m = str_replace('√•', '&aring;', $m);
    $m = str_replace('√§', '&auml;', $m);
    $m = str_replace('√∂', '&ouml;', $m);
    $m = str_replace('√Ö', '&Aring;', $m);
    $m = str_replace('√Ñ', '&Auml;', $m);
    $m = str_replace('√ñ', '&Ouml;', $m);
    return $m;
  }

  /**
   * Create a valid wordpress username from the email address, i.e without any funky chars
   * @param type $email
   * @return type 
   */
  static function createUserName($email = "") {
    $email = trim($email);
    $email = str_replace(" ", "", $email);
    $email = preg_replace("[\.]", "", $email);
    $email = preg_replace("#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $email);
    $email = preg_replace("[@]", "2", $email);
    return $email;
  }

  /**
   * Create a wordpress user with all user and company attributes from securitas-WS
   * 
   * @param type $user_email
   * @param type $first_name
   * @param type $family_name
   * @param type $idcompany
   * @param type $companyname
   * @param type $admin
   * @return array 
   */
  public function createUser($user_email, $first_name, $family_name, $idcompany, $companyname, $admin, $idperson, $cellphone = '') {
    $idperson = (string) $idperson;
    /*
      echo '$user_email ' .  $user_email . ' ' .gettype($user_email) . '<br>';
      echo '$first_name ' .  $first_name . ' ' .gettype($first_name) . '<br>';
      echo '$family_name ' .  $family_name . ' ' .gettype($family_name) . '<br>';
      echo '$idcompany ' .  $idcompany . ' ' .gettype($idcompany) . '<br>';
      echo '$companyname ' .  $companyname . ' ' .gettype($companyname) . '<br>';
      echo '$admin ' .  $admin . ' ' .gettype($admin) . '<br>';
      echo '$idperson ' .  $idperson . ' ' .gettype($idperson) . '<br>';
     */
    try {
      $user_name = $this::createUserName($user_email);
      $data = username_exists($user_name);
      print_r($data);


      if ($user_id == NULL) {  //new wp-user
        $random_password = wp_generate_password(8, false);
        $user_id = wp_create_user($user_name, $random_password, $user_email);
        $user_id = (string) $user_id;
        //echo '$user_id ' .  $user_id . ' ' .gettype($user_id) . '<br>';        
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $family_name);
        add_user_meta($user_id, 'sec_companyname', $companyname, true);
        add_user_meta($user_id, 'sec_idcompany', $idcompany, true);
        add_user_meta($user_id, 'sec_technician', $admin, true);
        add_user_meta($user_id, 'sec_securitasid', $idperson, true);
        add_user_meta($user_id, 'sec_cellphone', $cellphone, true);

        $msg = "A new user was created for $companyname, $idcompany, user name: $user_name, password: $random_password, email: $user_email, user id: $user_id, sec_securitasid: $idperson";
        $this->saveToFile($this->logFile, $msg, 'INFO');
        $userData = array('user_id' => $user_id,
            'user_name' => $user_name,
            'password' => $random_password,
            'first_name' => $first_name,
            'last_name' => $family_name,
            'sec_companyname' => $companyname,
            'sec_idcompany' => $idcompany,
            'sec_technician' => $admin,
            'sec_securitasid' => $idperson);
        return $userData;
      } else {
        return $user_id;
      }
    } catch (exception $e) {
      $msg = "Error in SecuritasWS->createUser(),  email: $user_email. " . $e->getmessage();
      $this->saveToFile($this->logFile, $msg, 'ERROR');
      die($e->getmessage());
    }
  }

  /**
   * Appends data to (log-)file
   * It only writes if debug is enabled
   * 
   * @param <type> $data
   */
  public function saveToFile($filename, $data, $type = 'INFO') {
    if ($this->debug) {
      $fh = fopen($filename, 'a') or die("can't open file");
      fwrite($fh, "\n" . date('Y-m-d H:m:s') . ' [' . $type . '] ');
      fwrite($fh, $data);
      fclose($fh);
    }
  }

}

//end class SecuritasWS




load_theme_textdomain('securitas-ws', get_template_directory() . '/languages');

//echo get_template_directory() . '/languages';


/* * ***********************************************
 * Call these functions from the wordpress theme
 * *********************************************** */

/**
 * Get the staff list by companyId
 * 
 * @param type $companyId 
 */
function securitasWSgetStaffList($companyId) {
  $sws = new SecuritasWS();
  $sws->getStaffList($companyId);
}

/**
 * Edit a person by personId
 * 
 * @param type $personId 
 */
function securitasWSeditStaff($personId) {
  $sws = new SecuritasWS();
  $sws->editPerson($personId);
}

/**
 * Add a person in the companyId
 * 
 * @param type $companyId 
 */
function securitasWSaddStaff($companyId) {
  SecuritasWS::addPerson($companyId);
}

/**
 * Just for testing. 
 * Outputs a company dump from WS 
 */
function securitasWSdebugOutput() {
  $sws = new SecuritasWS();
  $sws->debugOutput();
}

/* * ************************************
 * The securitas login shortcode
 * *********************************** */

/**
 * A shortcaode for a specialized login form for Securitas
 * Author: Kristian Erendi - Reptilo AB
 * Date: 2012-03-02
 * [securitas_login]
 */
function securitas_login_func($atts) {
  $pluginRoot = plugins_url("", __FILE__);
  $actionFile = $pluginRoot . "/api_send_new_password.php";

  echo '<script type="text/javascript">
  jQuery(document).ready(function(){

    //progress wheel
    jQuery("#loading")
      .hide()  // hide it initially
      .ajaxStart(function() {
        jQuery(this).show();
      })
      .ajaxStop(function() {
        jQuery(this).hide();
    });

    jQuery("#sec-new-pass").click(function(event) {
      event.preventDefault();
      var self = jQuery(this);
      var username = jQuery("#log").val();
      
      dataString = "username=" + username;
      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){  //json array returned
              console.log(data);
              jQuery("#success").html(data.status);
            }
        });

    });
  });
</script>';


  /*
    $args = array(
    'echo' => true,
    //'redirect' => site_url('/partnerportal-sverige/?page_id=56'), //site_url( $_SERVER['REQUEST_URI'] ),
    'redirect' => site_url('/?page_id=56'),
    'form_id' => 'loginform',
    'label_username' => __('Username'),
    'label_password' => __('Password'),
    'label_remember' => __('Remember Me'),
    'label_log_in' => __('Log In'),
    'id_username' => 'user_login',
    'id_password' => 'user_pass',
    'id_remember' => 'rememberme',
    'id_submit' => 'wp-submit',
    'remember' => false,
    'value_username' => NULL,
    'value_remember' => false);

    wp_login_form($args);
   */

  $output = '  <a href="#" name="sec-new-pass" id="sec-new-pass">' . __("Please send me a new password", 'securitas-ws') . '</a>
  <div id="success"></div><div id="loading"><img src="' . $pluginRoot . '/img/ajax-loader.gif" alt=""></div>';

  return $output;
}

add_shortcode('securitas_login', 'securitas_login_func');

/**
 * A shortcaode for a specialized login form for Securitas
 * Author: Kristian Erendi - Reptilo AB
 * Date: 2012-03-02
 * [securitas_login_redir]
 */
function securitas_login_redir_func($atts) {
  if (is_user_logged_in()) {
    echo 'logged in';
    //wp_redirect( home_url() ); exit;
  } else {
    echo 'not logged in';
    //wp_redirect( home_url() ); exit;
  }
}

add_shortcode('securitas_login_redir', 'securitas_login_redir_func');

/**
 * preview a username from email address
 * @param type $atts 
 */
function securitas_gen_username_func($atts) {

  $pluginRoot = plugins_url("", __FILE__);
  $actionFile = $pluginRoot . "/api_gen_username.php";

  echo '<script type="text/javascript">
  jQuery(document).ready(function(){

    //progress wheel
    jQuery("#loading")
      .hide()  // hide it initially
      .ajaxStart(function() {
        jQuery(this).show();
      })
      .ajaxStop(function() {
        jQuery(this).hide();
    });

    jQuery("#generate").click(function(event) {
      event.preventDefault();
      var self = jQuery(this);
      var username = jQuery("#username").val();
      
      dataString = "username=" + username;
      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){  //json array returned
              console.log(data);
              jQuery("#success").html(data.status);
            }
        });

    });
  });
</script>';

  $output = '
   <form action="#"> 
   <input type="text" id="username" />
   <input type="submit" class="buttons" value="Generate" id="generate"/>
   </form>
   <div id="success">
     </div><div id="loading">
     <img src="' . $pluginRoot . '/img/ajax-loader.gif" alt="">
   </div>
 ';

  echo $output;
}

add_shortcode('securitas_gen_username', 'securitas_gen_username_func');










/* * ************************************
 * The securitas widget
 * *********************************** */
require_once 'securitas-widget.php';
add_action('widgets_init', create_function('', 'return register_widget("SecuritasWidget");'));



/* * ************************************
 * Wordpress admin pages
 * *********************************** */

function securitasws_init() {
  load_plugin_textdomain('securitas-ws', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('init', 'securitasws_init');



add_action('admin_menu', 'lime_plugin_menu');  //network_admin_menu

function lime_plugin_menu() {
  add_options_page('Lims WS Plugin Options', 'Securitas WS', 'manage_options', 'limeWS', 'wSOptionsPage');
}

function wSOptionsPage() {
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }
  $updated = false;
  $lime_url = get_option('sec_lime_url');
  $support_email = get_option('sec_support_email');
  $support_email_sender = get_option('sec_support_email_sender');
  $log = get_option('sec_log');
  if ($log == 0) {
    $on = '';
    $off = 'checked';
  } else {
    $on = 'checked';
    $off = '';
  }

  if (isset($_POST['lime_url'])) {
    $lime_url = $_POST['lime_url'];
    update_option('sec_lime_url', $lime_url);
    $updated = true;
  }
  if (isset($_POST['support_email'])) {
    $support_email = $_POST['support_email'];
    update_option('sec_support_email', $support_email);
    $updated = true;
  }
  if (isset($_POST['support_email_sender'])) {
    $support_email_sender = $_POST['support_email_sender'];
    update_option('sec_support_email_sender', $support_email_sender);
    $updated = true;
  }
  if (isset($_POST['log'])) {
    $log = $_POST['log'];
    update_option('sec_log', $log);
    $updated = true;
    if ($log == 0) {
      $on = '';
      $off = 'checked';
    } else {
      $on = 'checked';
      $off = '';
    }
  }
  if ($updated) {
    echo '<div class="updated"><p><strong>' . __("Updated the URL", 'securitas-ws') . '</strong></p></div>';
  }
  echo '<div class="wrap">';
  echo '<form name="form1" method="post" action="">';
  echo '<input type="hidden" name="hidden_field" value="Y">';
  echo '<p><input type="text" name="lime_url" value="' . $lime_url . '" size="50"> &nbsp;' . __("The Lime Web Service URL", 'securitas-ws') . '</p>';
  echo '<p><input type="text" name="support_email" value="' . $support_email . '" size="50">  &nbsp; ' . __("The Securitas support email address", 'securitas-ws') . '</p>';
  echo '<p><input type="text" name="support_email_sender" value="' . $support_email_sender . '" size="50">  &nbsp; 
       ' . _e("The Securitas support email senders name and email. E.g. noreply@securitas.com", 'securitas-ws') . '</p>';
  echo '<p>Log to file<br/>';
  echo '<input type="radio" name="log" size="50" value="1" ' . $on . '> on <br/>';
  echo '<input type="radio" name="log" size="50" value="0" ' . $off . '> off';
  echo '</p>';
  echo '<p class="submit"> <input type="submit" name="Submit" class="button-primary" value="' . __('Save Changes', 'securitas-ws') . '" /></p>';
  echo '</form>';
  echo '</div>';
}

/* * *****************************************
 * Wordpress admin - extra companyId field 
 * ***************************************** */

add_action('show_user_profile', 'extra_user_company_id');
add_action('edit_user_profile', 'extra_user_company_id');

function extra_user_company_id($user) {
  $technician = '';
  $technician_value = "0";
  if (get_user_meta($user->ID, 'sec_technician', true) == '1') {
    $technician = 'checked="checked"';
  }
  ?>
  <?php if (current_user_can('administrator')): ?>
    <h3><?php _e("Securitas WebService Settings", "blank"); ?></h3>
    <table class="form-table">
      <tr>
        <th><label for="idcompany"><?php _e("CompanyId", 'securitas-ws'); ?></label></th>
        <td>                                                                            
          <input type="text" name="idcompany" id="idcompany" value="<?php echo esc_attr(get_user_meta($user->ID, 'sec_idcompany', true)); ?>" class="regular-text" />
          <span class="description"><?php _e("Enter the company id used in your web service.", 'securitas-ws'); ?></span>
        </td>
      </tr>
      <tr>
        <th><label for="companyname"><?php _e("Company name", 'securitas-ws'); ?></label></th>
        <td>
          <input type="text" name="companyname" id="companyname" value="<?php echo esc_attr(get_user_meta($user->ID, 'sec_companyname', true)); ?>" class="regular-text" />
          <span class="description"><?php _e("Enter the company name.", 'securitas-ws'); ?></span>
        </td>
      </tr>      
      <tr>
        <th><label for="securitaswsid"><?php _e("Securitas WebService user id", 'securitas-ws'); ?></label></th>
        <td>
          <input type="text" name="securitaswsid" id="securitaswsid" value="<?php echo esc_attr(get_user_meta($user->ID, 'sec_securitasid', true)); ?>" class="regular-text" />
          <span class="description"><?php _e("Enter the Securitas WebService user id.", 'securitas-ws'); ?></span>
        </td>
      </tr>      
      <tr>
        <th><label for="technician"><?php _e("Technician administrator", 'securitas-ws'); ?></label></th>
        <td>
          <input type="checkbox" name="technician" id="technician" value="<?php echo $technician_value; ?>" <?php echo $technician; ?>/>   
          <span class="description"><?php _e("User can add and remove personel on this site.", 'securitas-ws'); ?></span>
        </td>
      </tr>
    </table>
  <?php endif; ?>  
  <?php
}

add_action('personal_options_update', 'save_extra_user_company_id');
add_action('edit_user_profile_update', 'save_extra_user_company_id');

function save_extra_user_company_id($user_id) {
  if (!current_user_can('edit_user', $user_id)) {
    return false;
  }
  if (isset($_POST['idcompany'])) {
    update_user_meta($user_id, 'sec_idcompany', $_POST['idcompany']);
  }
  if (isset($_POST['companyname'])) {
    update_user_meta($user_id, 'sec_companyname', $_POST['companyname']);
  }
  if (isset($_POST['securitaswsid'])) {
    update_user_meta($user_id, 'sec_securitasid', $_POST['securitaswsid']);
  }
  $tech_value = isset($_POST['technician']) && '0' == $_POST['technician'] ? '1' : '0';
  update_user_meta($user_id, 'sec_technician', $tech_value);
}

/**
 * Enqueue some java scripts
 */
function securitasws_load_scripts() {
  $pluginRoot = plugins_url("", __FILE__);
  wp_register_script('validate', $pluginRoot . '/js/jquery.validate.min.js');
  wp_enqueue_script('validate');
}

add_action('wp_enqueue_scripts', 'securitasws_load_scripts');
?>