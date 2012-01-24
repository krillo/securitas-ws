<?php

/**
 *
 */
class LimeWService {
  const debug = true;
  public $client = null;

  public function __construct($url) {
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
    $this->client = new SoapClient($url, $options); //'https://limehosting.se:5797/meta'
    print_r($this->client);
  }

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
   * Update or insert a record
   *
   * @return <type>
   */
  public function updateOffice() {
    $params = array('data' =>
           '<data>
              <office idoffice="-1" name="krillo" city="Helsingborg" phone="042-18181818" www="boyhappy.se" address1="Gatan 1" address2="Gatan 2" misc="Tjoho" />
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
      echo "Exception in office()<br>";
      die($e->getmessage());
    }
  }


  public function updateCompany() {
    $params = array('data' =>
           '<data>
              <office idcompany="-1" name="Reptilo AB" phone="042-242188" www="reptilo.se" email="krillo@gmail.com" address="finjagtan 6" invoiceaddress="finjagtan 6" visitingaddress="finjagtan 6" visitingzip="25251" visitingcity="Helsingborg" country="Sweden" customerno="333" agreementno="22"  registrationno="23" creditrating="2" noofemployees="5" bg="2828-33" pg="2828-33"/>
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
      echo "Exception in office()<br>";
      die($e->getmessage());
    }
  }


  /**
   * Select from office
   * @param <type> $count
   * @return <type>
   */
  public function selectFromOffice($count = 10) {
    $params = array('query' =>
        '<query distinct="0" top="' . $count . '">
         <tables>
            <table>office</table>
         </tables>
         <fields>
            <field sortorder="asc" sortindex="1">name</field>
            <field>city</field>
            <field>phone</field>
            <field>fax</field>
            <field>www</field>
            <field>address1</field>
            <field>address2</field>
            <field>registrationno</field>
            <field>vatno</field>
            <field>bg</field>
            <field>pg</field>
            <field>misc</field>
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
      echo "Exception in selectFromOffice()<br>";
      die($e->getmessage());
    }
  }


  /**
   * Select from company
   * @param <type> $count
   * @return <type>
   */
  public function selectFromCompany($count = 10) {
    $params = array('query' =>
        '<query distinct="0" top="' . $count . '">
         <tables>
            <table>company</table>
         </tables>
         <fields>
            <field sortorder="asc" sortindex="1">name</field>
            <field>email</field>
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
   * Select from person
   * @param <type> $count
   * @return <type>
   */
  public function selectFromPerson($count = 10) {
    $params = array('query' =>
        '<query distinct="0" top="' . $count . '">
         <tables>
            <table>person</table>
         </tables>
         <fields>
            <field sortorder="asc" sortindex="1">name</field>
            <field>email</field>
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
      echo "Exception in person()<br>";
      die($e->getmessage());
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
   * Appends data to logfile
   * @param <type> $data
   */
  public function saveToFile($filename, $data, $type = 'INFO') {
    $fh = fopen($filename, 'a') or die("can't open file");
    fwrite($fh, "\n" . date('Y-m-d H:m:s') . ' [' . $type . '] ');
    fwrite($fh, $data);
    fclose($fh);
  }

  public function insertcompanypost($client) {
    $params = array(
        'query' =>
        '<data>
      <person idperson="-1" company="4379001" name="Karl Pedal2"  />
		</data>'
    );
    try {
      $response = $client->UpdateData($params);
    } catch (exception $e) {
      echo "exception<br>"; // $e->getmessage();
      die($e->getmessage());
    }
    return $response;
  }

  public function debug() {
    $client = $this->client;
    echo "REQUEST HEADERS:" . $client->__getLastRequestHeaders() . "<br />";
    echo "REQUEST:" . $client->__getLastRequest() . "<br />";
    echo "RESPONSE HEADERS:" . $client->__getLastResponseHeaders() . "<br />";
    echo "RESPONSE :" . $client->__getLastResponse() . "<br />";
  }


}
