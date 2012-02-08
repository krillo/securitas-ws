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

  const debug = true;

  public $logFile = null;
  //plugin db version
  public static $myDbVersion = "0.1";
  public $lime = null;

  function __construct() {
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
   * Get the markup of all the staff
   */
  public function getStaffList($companyId) {
    $response = $this->lime->selectFromPerson($companyId);
    //var_dump($response);
    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api_lime_delete_person.php";

    echo '
<script type="text/javascript">
  function deletePerson(idperson){
    var answer = confirm ("Do you want to remove the user?")
    if (answer){
        jQuery.ajax({
        type: "POST",
        url: "' . $actionFile . '",
        data: "idperson=" + idperson,
        success: function(data){         
          //jQuery("#success").html("Delete successful");
          window.location.reload();
        }
      });
    } else{
      return false;
    }
      return false;
  }


  function editPerson(idperson){
    //alert(idperson);    
    window.location.href = "/staff/edit-staff?idperson=" + idperson;    
  }
//staff/edit-staff/



</script>
';


    $output = '<ul>';
    foreach ($response as $value) {
      //the checkboxes
      $portal = '';
      $lc = '';
      $admin = '';
      if ($value->attributes()->authorizedportal == '1') {
        //echo "Portal";
        $portal = ' checked ';
      }
      if ($value->attributes()->authorizedarc == '1') {
        //echo "LC";
        $lc = ' checked ';
      }
      if ($value->attributes()->admninrights == '1') {
        //echo "admin";
        $admin = ' checked ';
      }
      if ($value->attributes()->ended == '0') {
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
        $output .= '<input type="checkbox" disabled ' . $lc . '> LC';
        $output .= '<input type="checkbox" disabled ' . $portal . '> Portal';
        $output .= '<input type="checkbox" disabled ' . $admin . '> Administrator';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '<div id="staff-buttons">';
        $output .= '<input type="hidden" value="' . $value->attributes()->idperson . '" /><span><br />';
        $output .= '<input class="wpcf7-submit" type="button" value="X" onclick="deletePerson(' . $value->attributes()->idperson . ');return false;" /><span><br />';
        $output .= '<input class="wpcf7-submit" type="button" value="Edit"  onclick="editPerson(' . $value->attributes()->idperson . ');return false;"/></span>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</li>';
        $output .= '<div id="success"></div>';
      }
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
      var idcompany = jQuery("#idcompany").val();    
      var original_lc = jQuery("#original_lc").val();    
      var position = jQuery("#position").val(); 
      //alert("firstname: " + firstname + " familyname: " + familyname + " cellphone: " + cellphone +
      //" email: " + email + " idperson: " + idperson + " &position=" + position);

      //get the checkboxes default them to 0
      var admin = jQuery("#admin:checked").val();
      if(admin === undefined){admin = "0";}
      var lc = jQuery("#lc:checked").val();
      if(lc === undefined){lc = "0";}
      var portal = jQuery("#portal:checked").val();
      if(portal === undefined){portal = "0";}
      //alert("admin: " + admin + " lc: " + lc + " portal: " + portal);
      
      dataString = "firstname=" + firstname + "&familyname=" + familyname + "&cellphone=" + cellphone + "&email=" + email
                    + "&idperson=" + idperson + "&admin=" + admin + "&lc=" + lc + "&portal=" + portal + "&position=" + position
                    + "&position=" + position + "&idcompany=" + idcompany + "&original_lc=" + original_lc;
      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){
              console.log(data);
              
              jQuery("#success").html("Save successful");
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
      $output .= '<div class="labels"><label for="name">Name</label></div>';
      $output .= '<input type="text" class="name" value="' . $value->attributes()->firstname . '" id="firstname" name="firstname">';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<div class="labels"><label for="lastname">Last name</label></div>';
      $output .= '<input type="text" class="lastname" value="' . $value->attributes()->familyname . '" id="familyname" name="familyname">';
      $output .= '</div>';
      $output .= '<div class="pp-select">';
      $output .= '<div class="labels"><label for="position">Choose role</label></div>';
      $output .= '<select id="position">';
      $output .= '<option value="2161001" ' . $sales . '>Sales</option>';
      $output .= '<option value="2163001" ' . $technician . '>Technician</option>';
      $output .= '<option value="2164001" ' . $marketing . '>Marketing</option>';
      $output .= '<option value="3065001" ' . $other . '>Other</option>';
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
      $output .= '<input type="checkbox" value="1" class="pp-check" name="admin" id="admin" ' . $admin . '/>';
      $output .= '<div class="pp-checkbox">Technical Administrator</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="lc"  id="lc" ' . $elegible . '/>';
      $output .= '<div class="pp-checkbox">Elegible LC</div>';
      $output .= '</div>';
      $output .= '<div>';
      $output .= '<input type="checkbox" value="1" class="pp-check" name="portal" id="portal" ' . $portal . '/>';
      $output .= '<div class="pp-checkbox">Elegible Portal</div>';
      $output .= '</div>';
      $output .= '</div>';
      $output .= '<p><!--staff-info--></p>';
      $output .= '<div class="staff-buttons">';
      $output .= '<input type="hidden" name="idperson" id="idperson" value="' . $value->attributes()->idperson . '">';
      $output .= '<input type="hidden" name="idcompany" id="idcompany" value="' . get_the_author_meta('idcompany') . '">';
      $output .= '<input type="hidden" name="original_lc" id="original_lc" value="' . $value->attributes()->authorizedarc . '">';
      $output .= '<input type="submit" class="wpcf7-submit" id="save-person" value="Save">';
      $output .= '</div>';
      $output .= '</fieldset>';
      $output .= '</form>';
      $output .= '</div>';
      $output .= '<p><!--staff-container-->';
      $output .= '</li>';
      $output .= '</ul>';
      $output .= '</div>';
      $output .= '<div id="success"></div>';
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
      var position = jQuery("#position").val(); 
      //alert("firstname: " + firstname + " familyname: " + familyname + " cellphone: " + cellphone + " email: " + email
      //+ " idcompany: " + idcompany + " &position=" + position);


      //get the checkboxes default them to 0
      var admin = jQuery("#admin:checked").val();
      if(admin === undefined){admin = "0";}
      var lc = jQuery("#lc:checked").val();
      if(lc === undefined){lc = "0";}
      var portal = jQuery("#portal:checked").val();
      if(portal === undefined){portal = "0";}
      //alert("admin: " + admin + " lc: " + lc + " portal: " + portal);
      
      dataString = "firstname=" + firstname + "&familyname=" + familyname + "&cellphone=" + cellphone + "&email=" + email + "&idcompany=" 
                   + idcompany + "&admin=" + admin + "&lc=" + lc + "&portal=" + portal + "&position=" + position;
      jQuery.ajax({
            type: "POST",
            url: "' . $actionFile . '",
            data: dataString,
            cache: false,
            success: function(data){
              console.log(data);
              
              jQuery("#success").html("Save successful");
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
    $output .= '<div class="labels"><label for="position">Choose role</label></div>';
    $output .= '<select id="position">';
    $output .= '<option value="2161001" >Sales</option>';
    $output .= '<option value="2163001" >Technician</option>';
    $output .= '<option value="2164001" >Marketing</option>';
    $output .= '<option value="3065001" >Other</option>';
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
    $output .= '<input type="hidden" name="idcompany" id="idcompany" value="' . $companyId . '">';
    $output .= '<input type="submit" class="wpcf7-submit" id="save-person" value="Save">';
    $output .= '</div>';
    $output .= '</fieldset>';
    $output .= '</form>';
    $output .= '</div>';
    $output .= '<p><!--staff-container-->';
    $output .= '</li>';
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div id="success"></div>';

    echo $output;
  }

  /**
   * Update person in WS, requires $idperson
   * An insert requires companyId and -1 as $idperson  
   * 
   * If lc-eligibility is changed then send an email to securitas
   * If portal is 1 then create the user and send email to her
   * 
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
  public function updatePerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $original_lc, $portal, $idcompany, $position, $ended) {
    $success = $this->lime->updatePerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $portal, $idcompany, $position, $ended);
    if ($success) {
      //echo '$original_lc: '. $original_lc . ' lc: ' .$lc;      
      if ($original_lc != $lc) {  //the LC eligibility has been changed, send mail to securitas 
        $this->sendEmail('lc', $firstname, $familyname, $cellphone, $email, $lc, $portal);
      }
      if ($portal == 1) {
        $userData = $this->createUser($email);
        if(isset($userData)){
          print_r($userData);
        }
        
      }
    }
  }

  /**
   * Delete the person
   * 
   * @param type $idperson
   * @return type 
   */
  public function deletePerson($idperson) {
    return $this->lime->deletePerson($idperson);
  }

  /**
   * Send email
   * There are two $type of mail: 'lc' or 'portal'
   * LC - them send an email to securitas for noting that LC eligibility has changed
   * PORTAL - send an invite email to the person just added to WordPress 
   */
  public function sendEmail($type, $firstname, $familyname, $cellphone, $email, $lc, $portal) {
    require_once(__DIR__ . '/../../../wp-config.php');
    global $current_user;
    $user_id = $current_user->ID;
    $user_email = $current_user->data->user_email;
    $user_firstname = get_user_meta($user_id, 'first_name', true);
    $user_lastname = get_user_meta($user_id, 'last_name', true);
    $user_company = get_user_meta($user_id, 'sec_companyname', true);

    switch ($type) {
      case 'lc':
        echo ", inne i sendEmail";
        $lcAcess = 'NO ACCESS';
        if ($lc == '1') {
          $lcAcess = 'ACCESS';
        }

        $subject = "LC eligibility changed";
        $message = "LC eligibility changed to <strong>$lcAcess</strong> for: <br><br>";
        $message .= "Company: $user_company <br>";
        $message .= "Name: $firstname $familyname <br>";
        $message .= "Email: $email <br>";
        $message .= "Mobile: $cellphone <br><br><br>";
        $message .= "The person who did this change is: <br><br>";
        $message .= "Company: $user_company <br>";
        $message .= "Name: $user_firstname $user_lastname <br>";
        $message .= "Email: $user_email <br>";

        $to = get_option('sec_support_email'); //'kundtjanst.alert@securitas.se';
        $from = get_option('sec_support_email_sender');
        break;
      case 'portal':

        $subject = "LC eligibility changed";
        $message = "LC eligibility changed to for: <br>";
        $message .= "$firstname $familyname <br>";
        $message .= "$email <br>";
        $message .= "$cellphone <br><br><br>";
        $message .= "The person who did this change is: <br>";
        $message .= "$firstname $familyname <br>";
        $message .= "$email <br>";
        $message .= "$cellphone <br><br>";

        $to = 'krillo@gmail.com';
        $from = 'noreply-partner-portal@securitas.com';

        break;
      default:
        $this->saveToFile($this->logFile, 'Error sending email, 1', 'ERROR');
        return;
        break;
    }

    if ($to != "" && $message != "") {
      $headers = 'To: ' . $to . ' <' . $to . '>' . "\r\n";
      $headers .= 'From: ' . $from . ' <' . $from . '>' . "\r\n";
      $headers .= 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=UTF-8' . '\r\n';

      $success = mail($to, $subject, $message, $headers);
      if ($success):
        $msg = "Email ($subject)sent to $to";
        $this->saveToFile($this->logFile, $msg, 'INFO');
        echo 'success';
      else:
        $this->saveToFile($this->logFile, 'Error sending email, 2', 'ERROR');
        echo 'error';
      endif;
    }
  }
  
  /**
   * Create a valid wordpress username from the email address, i.e without any funky chars
   * @param type $email
   * @return type 
   */
  function createUserName($email = "") {
    $email = trim($email);
    $email = str_replace(" ", "", $email);
    $email = preg_replace("[\.]", "", $email);    
    $email = preg_replace("#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $email);
    $email = preg_replace("[@]", "2", $email);
    return $email;    
  }

  /**
   * Create a wordpress user
   */
  public function createUser($user_email) {
    try{
    $user_name = $this->createUserName($user_email);
    $user_id = username_exists($user_name);
    if (!$user_id) {
      $random_password = wp_generate_password(8, false);
      $user_id = wp_create_user($user_name, $random_password, $user_email);
      $userData = array('user_id'=> $user_id, 'user_name'=> $user_name, 'password'=> $random_password);
      $msg = "A new user was created, user name: $user_name, password: $random_password, email: $user_email, user id: $user_id";
      $this->saveToFile($this->logFile, $msg, 'INFO');
      return $userData;      
    } else {
      $msg = "Could not create a new user email: $user_email";
      $this->saveToFile($this->logFile, $msg, 'ERROR');
      return false;
    }
    } catch (exception $e) {
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
    if (self::debug) {
      $fh = fopen($filename, 'a') or die("can't open file");
      fwrite($fh, "\n" . date('Y-m-d H:m:s') . ' [' . $type . '] ');
      fwrite($fh, $data);
      fclose($fh);
    }
  }

}

//end class SecuritasWS







/* * ***********************************************
 * Call these functions from the wordpress theme
 * *********************************************** */

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
  SecuritasWS::addPerson($companyId);
}

/**
 * Just for testing. 
 * Outputs a company dump from WS 
 */
function securitasWSdebugOutput() {
  $lime = new SecuritasWS();
  $lime->debugOutput();
}

/* * ************************************
 * Wordpress admin pages
 * *********************************** */

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
  if ($updated) {
    echo '<div class="updated"><p><strong>Updated the URL</strong></p></div>';
  }
  echo '<div class="wrap">';
  echo '<form name="form1" method="post" action="">';
  echo '<input type="hidden" name="hidden_field" value="Y">';
  echo '<p><input type="text" name="lime_url" value="' . $lime_url . '" size="50"> &nbsp; The Lime Web Service URL</p>';
  echo '<p><input type="text" name="support_email" value="' . $support_email . '" size="50">  &nbsp; The Securitas support email address</p>';
  echo '<p><input type="text" name="support_email_sender" value="' . $support_email_sender . '" size="50">  &nbsp; The Securitas support email senders name and email. E.g. noreply@securitas.com </p>';
  echo '<p class="submit"> <input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p>';
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
  if (get_the_author_meta('sec_technician', $user->ID) == '1') {
    $technician = 'checked';
  }
  ?>
  <?php if (current_user_can('administrator')): ?>
    <h3><?php _e("Securitas WebService Settings", "blank"); ?></h3>
    <table class="form-table">
      <tr>
        <th><label for="idcompany"><?php _e("CompanyId"); ?></label></th>
        <td>
          <input type="text" name="idcompany" id="idcompany" value="<?php echo esc_attr(get_the_author_meta('sec_idcompany', $user->ID)); ?>" class="regular-text" />
          <span class="description"><?php _e("Please enter the company id used in your web service."); ?></span>
        </td>
      </tr>
      <tr>
        <th><label for="companyname"><?php _e("Company name"); ?></label></th>
        <td>
          <input type="text" name="companyname" id="companyname" value="<?php echo esc_attr(get_the_author_meta('sec_companyname', $user->ID)); ?>" class="regular-text" />
          <span class="description"><?php _e("Please enter the company name."); ?></span>
        </td>
      </tr>      
      <tr>
        <th><label for="technician"><?php _e("Technician administrator"); ?></label></th>
        <td>
          <input type="checkbox" name="technician" id="technician" value="1" <?php echo $technician; ?>/>
          <span class="description"><?php _e("User can add and remove personel."); ?></span>
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
  if (isset($_POST['technician'])) {
    update_user_meta($user_id, 'sec_technician', $_POST['technician']);
  }
}
?>