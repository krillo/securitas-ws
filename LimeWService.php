<?php

/**
 *
 */
class LimeWService {

  const debug = true;

  public $client = null;
  public $logFile = null;

  public function __construct($url) {
    $this->logFile = __DIR__ . '/ws.log';
    ini_set('soap.wsdl_cache', WSDL_CACHE_NONE);
    $options = array(
        'trace' => 1,
        'exceptions' => true,
        'encoding' => 'UTF-8',
        'features' => SOAP_WAIT_ONE_WAY_CALLS,
        'style' => SOAP_DOCUMENT,
        'use' => SOAP_LITERAL,
        'soap_version' => SOAP_1_1,
        'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
    );
    $this->client = new SoapClient($url, $options); //https://limehosting.se:5797/meta    https://one.securitas.se:26090/Meta/
    //print_r($this->client);
  }

  /**
   * Return the tableschema of the whole Lime db
   *
   * @return <type>
   */
  public function tableschema() {
    $client = $this->client;
    $params = array(
        'table' => 'company'
    );
    try {
      $response = $client->GetTableSchema($params);
    } catch (exception $e) {
      echo "exception tableschema<br>"; // $e->getmessage();
      die($e->getmessage());
    }
    return $response;
  }

  /**
   * Get the databaseschema
   * @return <type>
   */
  public function databaseschema() {
    $params = array();
    try {
      $response = $this->client->GetDatabaseSchema($params);
      $this->saveToFile("lime.log", $this->client->__getLastResponse(), 'INFO');
      $response = $this->client->__getLastResponse();
      $xml = $this->responseToSimpleXML($response, 'databaseschema');
      return $xml;
    } catch (exception $e) {
      echo "Exception databasschema<br>"; // $e->getmessage();
      die($e->getmessage());
    }
    return $response;
  }

  /**
   * Select from coworker
   * @param <type> $count
   * @return <type>
   */
  public function selectFromCoworker($count = 10) {
    $params = array('query' =>
        '<query distinct="0" top="' . $count . '">
         <tables>
            <table>coworker</table>
         </tables>
         <fields>
            <field sortorder="asc" sortindex="1">name</field>
            <field>email</field>
            <field>phone</field>
            <field>cellphone</field>
         </fields>
         <conditions>
        </conditions>
		  </query>');

    try {
      if (self::debug) {
        $this->saveToFile("lime.log", print_r($params, true), 'DEBUG');
      }
      $response = $this->client->GetXmlQueryData($params);
      $this->saveToFile("lime.log", $this->client->__getLastResponse(), 'INFO');
      $response = $this->client->__getLastResponse();
      $xml = $this->responseToSimpleXML($response, 'select');
      return $xml;
    } catch (exception $e) {
      echo "Exception in company()<br>";
      die($e->getmessage());
    }
  }

  /**
   * Select from company
   * $count is defaulted to 0 which means get all posts
   * 
   * @param <type> $count
   * @return <type>
   */
  public function selectFromCompany($id, $count = 0) {
    if (isset($id)) {
      $params = array('query' =>
          '<query distinct="0" top="' . $count . '">
         <tables>
            <table>company</table>
         </tables>
         <fields>
            <field sortorder="asc" sortindex="1">name</field>
            <field>idcompany</field>
            <field>email</field>
            <field>phone</field>
            <field>fax</field>
            <field>www</field>
            <field>address</field>
            <field>visitingaddress</field>
            <field>visitingzip</field>
            <field>visitingcity</field>
            <field>country</field>
            <field>noofemployees</field>
            <field>coworker</field>
            <field>coworker.name</field>
            <field>coworker.phone</field>
            <field>product</field>
         </fields>
         <conditions>
            <condition operator="=">
              <exp type="field">idcompany</exp>
              <exp type="numeric">6016001</exp>
            </condition>
        </conditions>
		  </query>');

      try {
        if (self::debug) {
          $this->saveToFile("lime.log", print_r($params, true), 'DEBUG');
        }
        $response = $this->client->GetXmlQueryData($params);
        $this->saveToFile("lime.log", $this->client->__getLastResponse(), 'INFO');
        $response = $this->client->__getLastResponse();
        $xml = $this->responseToSimpleXML($response, 'select');
        return $xml;
      } catch (exception $e) {
        $this->saveToFile("lime.log", 'Exception in selectFromCompany()', 'ERROR');
        $this->saveToFile("lime.log", $e->getmessage(), 'ERROR');
        echo "Exception in selectFromCompany()<br>";
        die($e->getmessage());
      }
    } else {
      $this->saveToFile("lime.log", 'Exception in selectFromCompany(). No companyId ', 'ERROR');
    }
  }

  /**
   * Update or insert a record into Company
   *
   * @return <type>
   */
  public function updateCompany() {
    $params = array('data' =>
        '<data>
              <company
                idcompany="-1"
                name="Reptilo AB CD"
                email="kristian@reptilo.se"
                phone="780010303"
                fax="0909090"
                www="http://reptilo.se"
                address="gatan 6"
                visitingaddress="visigatan 6"
                visitingzip="74477"
                visitingcity="Helsingborg"
                country="2035001"
                noofemployees="1421001"
                product=""
                suffix="Helsingborg"
              />
            </data>'
    );
    try {
      if (self::debug) {
        $this->saveToFile("lime.log", print_r($params, true), 'DEBUG');
      }
      $response = $this->client->UpdateData($params);
      $this->saveToFile("lime.log", $this->client->__getLastResponse(), 'INFO');
      $response = $this->client->__getLastResponse();
      $xml = $this->responseToSimpleXML($response, 'update');
      return $xml;
    } catch (exception $e) {
      $this->saveToFile("lime.log", 'Exception in updateCompany()', 'ERROR');
      $this->saveToFile("lime.log", $e->getmessage(), 'ERROR');
      echo "Exception in updateCompany()<br>";
      die($e->getmessage());
    }
  }

  /**
   * Select person by id
   * @param <type> $personId
   * @return <type>
   */
  public function getPerson($personId) {
    if (isset($personId)) {
      $params = array('query' =>
          '<query distinct="0" top="0">
         <tables>
            <table>person</table>
         </tables>
         <fields>
            <field sortorder="asc" sortindex="1">name</field>
            <field>idperson</field>
            <field>firstname</field>
            <field>familyname</field>
            <field>company</field>
            <field>ended</field>
            <field>position</field>
            <field>email</field>
            <field>cellphone</field>
            <field>authorizedarc</field>
            <field>authorizedportal</field>
            <field>admninrights</field>
            <field>wpuserid</field>
         </fields>
         <conditions>
            <condition operator="=">
              <exp type="field">idperson</exp>
              <exp type="numeric">' . $personId . '</exp>
            </condition>
        </conditions>
		  </query>');
      $xml = $this->doWSQuery($params, 'select', 'getPerson()');
      return $xml;
    } else {
      $this->saveToFile($this->logFile, 'Exception in getPerson(). No personId ', 'ERROR');
    }
  }

  /**
   * Select person by id
   * @param <type> $personId
   * @return <type>
   */
  public function deletePerson($idperson) {
    if (isset($idperson)) {
      $params = array('data' =>
          '<data>
           <person
             idperson="' . $idperson . '"
             ended="1"
           />
         </data>'
      );
      $xml = $this->doWSQuery($params, 'update', 'deletePerson()');
      return $xml;
    } else {
      $this->saveToFile($this->logFile, 'Exception in deletePerson(). No personId ', 'ERROR');
    }
  }

  /**
   * Translate position code to text and vice versa
   * 
   * @param type $type 'code' or 'text'
   * @param type $arg   the parameter to be translated
   * @return string 
   */
  public function positionTranslate($type, $arg) {
    if ($type == 'code') {
      $codeToName = array(
          '2164001' => 'Administration',
          '2161001' => 'Säljare',
          '2163001' => 'Tekniker',
          '3065001' => 'Annan');
      return $codeToName[$arg];
    } else {
      $nameToCode = array(
          'Administration' => '2164001',
          'Säljare' => '2161001',
          'Tekniker' => '2163001',
          'Annan' => '3065001');
      return $nameToCode[$arg];
    }
  }

  /**
   * This function will do the actual WS lookup and handle logging and errors
   * Supply the query (params) and the type of query to be executed
   * 1. select
   * 2. update
   * 3. databaseschema
   * Supply also which is the calling function, for debug and error information
   *
   * @return <type> 
   */
  private function doWSQuery($params, $queryType, $callerFunction = 'xxx') {
    try {
      if (self::debug) {
        $this->saveToFile($this->logFile, print_r($params, true), 'DEBUG');
      }
      switch ($queryType) {
        case 'select':
          $response = $this->client->GetXmlQueryData($params);
          break;
        case 'update':
          $response = $this->client->UpdateData($params);
          break;
        case 'databaseschema':
          $response = $this->client->GetDatabaseSchema($params);
          break;
      }

      $this->saveToFile($this->logFile, $this->client->__getLastResponse(), 'INFO');
      $response = $this->client->__getLastResponse();
      $xml = $this->responseToSimpleXML($response, $queryType);
      return $xml;
    } catch (exception $e) {
      $this->saveToFile($this->logFile, "Exception in $callerFunction", 'ERROR');
      $this->saveToFile($this->logFile, $e->getmessage(), 'ERROR');
      echo "Exception in $callerFunction<br>";
      die($e->getmessage());
    }
  }

  /**
   * Select distinct from Person
   * if count is omitted then 0 is used and it means -all
   * @param <type> $count
   * @return <type>
   */
  public function selectFromPerson($companyId, $count = 0) {
    if (isset($companyId)) {
      $params = array('query' =>
          '<query distinct="0" top="' . $count . '">
         <tables>
            <table>person</table>
         </tables>
         <fields>
            <field sortorder="asc" sortindex="1">name</field>
            <field>idperson</field>
            <field>firstname</field>
            <field>familyname</field>
            <field>company</field>
            <field>ended</field>
            <field>position</field>
            <field>email</field>
            <field>cellphone</field>
            <field>authorizedarc</field>
            <field>authorizedportal</field>
            <field>admninrights</field>
            <field>wpuserid</field>
         </fields>
         <conditions>
            <condition operator="=">
              <exp type="field">company</exp>
              <exp type="numeric">' . $companyId . '</exp>
            </condition>
        </conditions>
		  </query>');

      $xml = $this->doWSQuery($params, 'select', 'selectFromPerson()');
      return $xml;
    } else {
      $this->saveToFile($this->logFile, 'Exception in selectFromPerson(). No personId ', 'ERROR');
      return false;
    }      
  }

  /**
   * Update or insert a record into Person
   * Position is defaulted to '3065001' which translates to 'other'
   * Ended is defaulted to 0 which translates to 'not ended' 
   *
   * @return <type>
   */
  public function updatePerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $portal, $idcompany, $position = '3065001', $ended = '0', $wpuserid = 0) {
    if (isset($idperson)) {
      $params = array('data' =>
          '<data>
              <person
                idperson="' . $idperson . '"
                firstname="' . $firstname . '"
                familyname="' . $familyname . '"
                company="' . $idcompany . '"
                ended="' . $ended . '"
                position="' . $position . '"
                email="' . $email . '"
                cellphone="' . $cellphone . '"
                authorizedarc="' . $lc . '"
                authorizedportal="' . $portal . '"
                admninrights="' . $admin . '"
                wpuserid="' . $wpuserid . '"
              />
            </data>'
      );
      $xml = $this->doWSQuery($params, 'update', 'updatePerson()');
      //return $xml;
      return true;
    } else {
      $this->saveToFile($this->logFile, 'Exception in updatePerson(). No personId ', 'ERROR');
      return false;
    }
  }


  /**
   * Update or insert a record into Person
   * Position is defaulted to '3065001' which translates to 'other'
   * Ended is defaulted to 0 which translates to 'not ended' 
   *
   * @return <type>
   */
  public function insertPerson($firstname, $familyname, $cellphone, $email, $idperson, $admin, $lc, $portal, $idcompany, $position = '3065001', $ended = '0', $wpuserid = 0) {
    if (isset($idperson)) {
      $params = array('data' =>
          '<data>
              <person
                idperson="' . $idperson . '"
                firstname="' . $firstname . '"
                familyname="' . $familyname . '"
                company="' . $idcompany . '"
                ended="' . $ended . '"
                position="' . $position . '"
                email="' . $email . '"
                cellphone="' . $cellphone . '"
                authorizedarc="' . $lc . '"
                authorizedportal="' . $portal . '"
                admninrights="' . $admin . '"
                wpuserid="' . $wpuserid . '"
              />
            </data>'
      );
      $xml = $this->doWSQuery($params, 'update', 'insertPerson()');
      $idnew = $xml->record[0]->attributes()->idnew;
      $idnew = (string)$idnew[0];
      return $idnew;
    } else {
      $this->saveToFile($this->logFile, 'Exception in updatePerson(). No personId ', 'ERROR');
      return 0;
    }
  }
  

  
  
  
  
  /**
   * Convert the response to simpleXML object
   * @param <type> $response
   */
  private function responseToSimpleXML($response, $type = 'query') {
    $returnXml = '';
    try {
      if (self::debug) {
        $s = 'The response is of type ' . gettype($response);
        $this->saveToFile("lime.log", $s, 'DEBUG');
      }
      libxml_use_internal_errors(true);
      $xml = simplexml_load_string($response);
      switch ($type) {
        case 'select':
          $body = trim((string) $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->children()->GetXmlQueryDataResponse->GetXmlQueryDataResult);
          break;
        case 'update':
          $body = trim((string) $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->children()->UpdateDataResponse->UpdateDataResult);
          //<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><UpdateDataResponse xmlns="http://lundalogik.se/Tangelo/"><UpdateDataResult>&lt;?xml version="1.0" encoding="UTF-16" ?&gt;&lt;xml transactionid="89334EF6-5055-48BB-8129-B81656A403EB"&gt;&lt;record table="office" idold="-1" idnew="15001"/&gt;&lt;/xml&gt;</UpdateDataResult></UpdateDataResponse></s:Body></s:Envelope>
          break;
        case 'databaseschema':
          $body = trim((string) $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->children()->GetDatabaseSchemaResponse->GetDatabaseSchemaResult);
          break;
        default:
          break;
      }
      $body = str_replace("UTF-16", "UTF-8", $body);  //fucked! but this must be done!
      $cleanXml = simplexml_load_string($body);
      if (!$cleanXml) {
        $this->saveToFile("lime.log", libxml_get_errors(), 'ERROR');
      }
      $returnXml = $cleanXml;
    } catch (Exception $e) {
      $this->saveToFile("lime.log", 'Exception in responseToSimpleXML() ' . $e->getmessage() . "\n" . $e->getTraceAsString(), 'ERROR');
      die($e->getmessage());
    }
    return $cleanXml;
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

  public function debug() {
    $client = $this->client;
    echo "REQUEST HEADERS:" . $client->__getLastRequestHeaders() . "<br />";
    echo "REQUEST:" . $client->__getLastRequest() . "<br />";
    echo "RESPONSE HEADERS:" . $client->__getLastResponseHeaders() . "<br />";
    echo "RESPONSE :" . $client->__getLastResponse() . "<br />";
  }

}
