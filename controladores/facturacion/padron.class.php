<?php

class PADRON {

    //const WSDL = "ws/personaServiceA4.wsdl";             # The WSDL corresponding to WSFE
    const PROXY_ENABLE = false;
    const LOG_XMLS = false;                  # For debugging purposes
    
    private $TAP_GEN = "xml/TAPX.xml";
    private $ID_EMP = "";
    private $WSDL = "";

    /*
     * ENTORNO DE PADRON
     */
    
    //constancia inscripcion private $PADRON_URL = "https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5"; //testing
    
    private $PADRON_URL = "";

    /*
     * CUIT EMISOR 
     */
    private $CUIT = "";

    /*
     * el path relativo, terminado en /
     */
    private $path = './';

    /*
     * manejo de errores
     */
    public $error = '';

    /*
     * Cliente SOAP
     */
    private $client;

    /*
     * objeto que va a contener el xml de TAP
     */
    private $TAP;

    /*
     * Constructor
     */
    public function __construct($arrEmpresa) {

      $this->path = dirname(__FILE__) . '/';
      $this->CUIT = $arrEmpresa["cuit"] + 0; //No se porque antes andaba con string y ahora debe ser int
      $this->TAP_GEN = "xml/TAP".$arrEmpresa["id"].".xml";
      $this->ID_EMP = $arrEmpresa["id"];

      switch ($arrEmpresa["ws_padron"]) {
        case 'ws_sr_padron_a4':
          $this->WSDL = "ws/personaServiceA4.wsdl";
          $this->PADRON_URL = ($arrEmpresa["entorno_facturacion"] == "produccion") ? 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4' : 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4';
          break;

        case 'ws_sr_padron_a5':
          $this->WSDL = "ws/personaServiceA5.wsdl";
          $this->PADRON_URL = ($arrEmpresa["entorno_facturacion"] == "produccion") ? 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5' : 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5';
          break;

        case 'ws_sr_padron_a10':
          $this->WSDL = "ws/personaServiceA10.wsdl";
          $this->PADRON_URL = ($arrEmpresa["entorno_facturacion"] == "produccion") ? 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA10' : 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA10';
          break;
        
        case 'ws_sr_padron_a13':
          $this->WSDL = "ws/personaServiceA13.wsdl";
          $this->PADRON_URL = ($arrEmpresa["entorno_facturacion"] == "produccion") ? 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13' : 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA13';
          break;

        case 'ws_sr_padron_a100':
          $this->WSDL = "ws/personaServiceA100.wsdl";
          $this->PADRON_URL = ($arrEmpresa["entorno_facturacion"] == "produccion") ? 'https://aws.afip.gov.ar/sr-parametros/webservices/parameterServiceA100' : 'https://awshomo.afip.gov.ar/sr-parametros/webservices/parameterServiceA100';
          break;
        case 'ws_sr_constancia_inscripcion':
          $this->WSDL = "ws/personaServiceA5_2.wsdl";
          $this->PADRON_URL = ($arrEmpresa["entorno_facturacion"] == "produccion") ? 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5' : 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5';
          break;
        default:
          $this->WSDL = "ws/personaServiceA4.wsdl";
          $this->PADRON_URL = ($arrEmpresa["entorno_facturacion"] == "produccion") ? 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4' : 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4';
          break;
      }

        // seteos en php
      ini_set("soap.wsdl_cache_enabled", "0");

      date_default_timezone_set('America/Argentina/Buenos_Aires');

        // validar archivos necesarios
      if (!file_exists($this->path . $this->WSDL))
        $this->error .= " Error al abrir WSDL: " . $this->WSDL;

      if (!empty($this->error)) {
        throw new Exception('Error en clase PADRON. Faltan archivos necesarios para el funcionamiento');
      }

      $opts = array(
        'ssl' => array('ciphers' => 'AES256-SHA')
      );

      $this->client = new SoapClient($this->path . $this->WSDL, array(
        'soap_version' => '_1_2',
        'location' => $this->PADRON_URL,
        'encoding' => 'UTF-8',
        'cache_wsdl' => WSDL_CACHE_BOTH,
        'exceptions' => false,
        'stream_context' => stream_context_create($opts),
        'trace' => true,
        'connection_timeout' => 5) //VER SI ANDA EN 5 SEGUNDOS
      );


    }

    /*
     * Chequea los errores en la operacion, si encuentra algun error fatal lanza una exepcion
     * si encuentra un error no fatal, loguea lo que paso en $this->error
     */
    private function _checkErrors($results, $method) {
      if (self::LOG_XMLS) {
        file_put_contents("xml/request-" . $method . "-id".$this->ID_EMP.".xml", $this->client->__getLastRequest());
        file_put_contents("xml/response-" . $method . "-id".$this->ID_EMP.".xml", $this->client->__getLastResponse());
      }

      if (is_soap_fault($results)) {
        //return false;
        throw new Exception('Error PADRON. Detalle: ' . $results->faultcode . ' ' . $results->faultstring);
      }
    }

    /*
     * Abre el archivo de TAP xml,
     * si hay algun problema devuelve false
     */
    public function openTAP() {
      $this->TAP = simplexml_load_file($this->path . $this->TAP_GEN);

      return $this->TAP == false ? false : true;
    }
    
    public function datosTAP(){
      return array(
        'Token' => $this->TAP->credentials->token,
        'Firma' => $this->TAP->credentials->sign,
        'Exp' => $this->TAP->header->expirationTime
      );
    }

    /*
     * Metodo dummy para verificacion de funcionamiento
     */
    public function PruebaConexion() {
      $results = $this->client->dummy();
      $e = $this->_checkErrors($results, 'dummy');
      return $e == false ? $results : false;
    }

    /* 
     * Solicitud de DATOS PERSONA (VALIDO PARA PADRON 4, 5, 10 Y 13).
    */
    public function getPersona($documento) {
      $persona = array(
               'token' => $this->TAP->credentials->token,
               'sign' => $this->TAP->credentials->sign,
               'cuitRepresentada' => $this->CUIT,
               'idPersona' => $documento
          );
      try {

        $results = $this->client->getPersona($persona);
      
      } catch (Exception $e) {

        $e = $this->_checkErrors($results, 'getPersona');
      
      }

      return (!isset($e) || $e == false) ? $results : false;
    } 

    /* 
     * Solicitud de DATOS PERSONA (VALIDO PARA PADRON 5 nuevo).
    */
    public function getPersona2($documento) {
      $persona = array(
               'token' => $this->TAP->credentials->token,
               'sign' => $this->TAP->credentials->sign,
               'cuitRepresentada' => $this->CUIT,
               'idPersona' => $documento
          );
      try {

        $results = $this->client->getPersona_v2($persona);
      
      } catch (Exception $e) {

        $e = $this->_checkErrors($results, 'getPersona');
      
      }

      return (!isset($e) || $e == false) ? $results : false;
    } 

    /* 
     * Solicitud de DATOS PERSONA (VALIDO PARA PADRON 5 nuevo).
    */
    public function getPersonaList($arrDocumentos) {
      $persona = array(
               'token' => $this->TAP->credentials->token,
               'sign' => $this->TAP->credentials->sign,
               'cuitRepresentada' => $this->CUIT,
               'idPersona' => $arrDocumentos[0]+0,
               'idPersona' => $arrDocumentos[1]+0,
               'idPersona' => $arrDocumentos[2]+0,
               'idPersona' => $arrDocumentos[3]+0
          );
      try {

        $results = $this->client->getPersonaList_v2($persona);
      
      } catch (Exception $e) {

        $e = $this->_checkErrors($results, 'getPersona');
      
      }

      return (!isset($e) || $e == false) ? $results : false;
    }
    
    /* 
     * Solicitud de DATOS PERSONA (VALIDO PARA PADRON 100).
    */
    public function getPersona100($documento) {

      $persona = array(
               'token' => $this->TAP->credentials->token,
               'sign' => $this->TAP->credentials->sign,
               'cuitRepresentada' => $this->CUIT,
               'collectionName' => 'SUPA.TIPO_TELEFONO'
          );

      try {

        $results = $this->client->getParameterCollectionByName($persona);
      
      } catch (Exception $e) {

        $e = $this->_checkErrors($results, 'getParameterCollectionByName');
      
      }

      return (!isset($e) || $e == false) ? $results : false;
    } 
}

?>