<?php

class WSAA {

    const WSDL = "ws/wsaa.wsdl";          # The WSDL corresponding to WSAA
    const TA = "xml/TA.xml";              # Archivo con el Token y Sign    
    const PROXY_ENABLE = false;
    //const URL = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms"; //Testing
    //const URL = "https://wsaa.afip.gov.ar/ws/services/LoginCms"; // produccion  

    private $CERT = "";

    private $PRIVATEKEY = "";

    private $PASSPHRASE = "";

    private $URL = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms"; //Testing

    private $TA;

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
     * servicio del cual queremos obtener la autorizacion
     */
    private $service;

    /*
     * Constructor
     */
    public function __construct($arrEmpresa) {

        $this->TA = null;
        $this->path = dirname(__FILE__) . '/';
        $this->service = 'wsfe';

        $this->PRIVATEKEY = $arrEmpresa["csr"];
        $this->PASSPHRASE = $arrEmpresa["passphrase"];
        $this->CERT = $arrEmpresa["pem"];

        if($arrEmpresa["entorno_facturacion"] == "produccion") {
            $this->URL = "https://wsaa.afip.gov.ar/ws/services/LoginCms"; // produccion 
        }        

        // seteos en php
        ini_set("soap.wsdl_cache_enabled", "0");

        // validar archivos necesarios
        if (!file_exists($this->path . $this->CERT) || is_null($this->CERT))
            $this->error .= " Error al abrir " . $this->CERT;
        if (!file_exists($this->path .  $this->PRIVATEKEY) || is_null($this->PRIVATEKEY))
            $this->error .= " Error al abrir " . $this->PRIVATEKEY;
        if (!file_exists($this->path . self::WSDL))
            $this->error .= " Error al abrir " . self::WSDL;

        if (!empty($this->error)) {
            throw new Exception('Error WSAA, no se pudo conectar a AFIP, faltan archivos necesarios para el funcionamiento ' . $this->error);
        }

        $this->client = new SoapClient($this->path . self::WSDL, array(
            'soap_version' => SOAP_1_2,
            'location' =>  $this->URL,
            'trace' => 1,
            'exceptions' => 0,
            'connection_timeout' => 5
            )
        );
    }

    /*
     * Crea el archivo xml de TRA
     */
    private function create_TRA() {
        $TRA = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<loginTicketRequest version="1.0">' .
            '</loginTicketRequest>');
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId', date('U'));
        $TRA->header->addChild('generationTime', date('c', date('U') - 60));
        $TRA->header->addChild('expirationTime', date('c', date('U') + 60));
        $TRA->addChild('service', $this->service);
        $TRA->asXML($this->path . 'xml/TRA.xml');
    }

    /*
     * This functions makes the PKCS#7 signature using TRA as input file, CERT and
     * PRIVATEKEY to sign. Generates an intermediate file and finally trims the 
     * MIME heading leaving the final CMS required by WSAA.
     * 
     * devuelve el CMS
     */

    private function sign_TRA() {
        
        $STATUS = openssl_pkcs7_sign(
            realpath($this->path . "xml/TRA.xml"),
            $this->path . "xml/TRA.tmp",
            "file://" . realpath($this->path . $this->CERT),
            array("file://" . realpath($this->path . $this->PRIVATEKEY), $this->PASSPHRASE),
            array(),
            !PKCS7_DETACHED);

        if (!$STATUS)
            throw new Exception("Error al generar firma PKCS#7");

        $inf = fopen($this->path . "xml/TRA.tmp", "r");
        $i = 0;
        $CMS = "";
        while (!feof($inf)) {
            $buffer = fgets($inf);
            if ($i++ >= 4)
                $CMS .= $buffer;
        }

        fclose($inf);
        //unlink("TRA.xml");
        unlink($this->path . "xml/TRA.tmp");

        return $CMS;
    }

    /*
     * Conecta con el web service y obtiene el token y sign
     */
    private function call_WSAA($cms) {
        $results = $this->client->loginCms(array('in0' => $cms));

        // para logueo
        file_put_contents($this->path . "xml/request-loginCms.xml", $this->client->__getLastRequest());
        file_put_contents($this->path . "xml/response-loginCms.xml", $this->client->__getLastResponse());

        if (is_soap_fault($results))
            throw new Exception("Error SOAP: " . $results->faultcode . ': ' . $results->faultstring);

        return $results->loginCmsReturn;
    }

    /*
     * Convertir un XML a Array
     */
    private function xml2array($xml) {
        $json = json_encode(simplexml_load_string($xml));
        return json_decode($json, TRUE);
    }

    /*
     * Funcion principal que llama a las demas para generar el archivo TA.xml
     * que contiene el token y sign
     */
    public function generar_TA() {
        $this->create_TRA();

        $TA = $this->call_WSAA($this->sign_TRA());

        if (!file_put_contents($this->path . self::TA, $TA))
            throw new Exception("Error al generar al archivo TA.xml");

        $this->TA = $this->xml2Array($TA);

        return true;
    }

    /*
     * Obtener la fecha de expiracion del TA
     * si no existe el archivo, devuelve false
     */
    public function get_expiration() {
        if (empty($this->TA)) {
            $TA_file = file($this->path . self::TA, FILE_IGNORE_NEW_LINES);
            if ($TA_file) {
                //$TA_xml = '';
                //for ($i = 0; $i < sizeof($TA_file); $i++)
                //    $TA_xml.= $TA_file[$i];
                $TA_xml = implode('', $TA_file); // Concatenar líneas del archivo
                $this->TA = $this->xml2Array($TA_xml);
                $r = $this->TA['header']['expirationTime'];
            } else {
                $r = false;
            }
        } else {
            $r = $this->TA['header']['expirationTime'] ?? false;
        }

        return $r;
    }


    ////////////Agregada por CJC para Moon POS
    public function datosTA(){
        $ticketAcceso = simplexml_load_file($this->path . "xml/TA.xml");

        return array(
            'expiracion' => $ticketAcceso->header->expirationTime,
            'TokenAfip' => $ticketAcceso->credentials->token,
            'FirmaAfip' => $ticketAcceso->credentials->sign
        );
    }

}


?>