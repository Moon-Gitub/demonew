<?php

class WSAA_P {

    const WSDL = "ws/wsaa.wsdl";          # The WSDL corresponding to WSAA
    const PROXY_ENABLE = false;

    private $TAP_GEN = "xml/TAPX.xml";
    private $ID_EMP = "";

    private $URL = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms"; //Testing

    private $CERT = "";

    private $PRIVATEKEY = "";

    private $PASSPHRASE = "";

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

     private $TAP;

    /*
     * servicio del cual queremos obtener la autorizacion
     */
    private $service;

    /*
     * Constructor
     */
    public function __construct($arrEmpresa) {

        $this->path = dirname(__FILE__) . '/';
        $this->service = $arrEmpresa["ws_padron"];

        $this->PRIVATEKEY = $arrEmpresa["csr"];
        $this->PASSPHRASE = $arrEmpresa["passphrase"];
        $this->CERT = $arrEmpresa["pem"];
        $this->TAP_GEN = "xml/TAP".$arrEmpresa["id"].".xml";
        $this->ID_EMP = $arrEmpresa["id"];

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
            throw new Exception('Error WSAA PADRON, no se pudo conectar a AFIP, faltan archivos necesarios para el funcionamiento ' . $this->error);
        }

        $this->client = new SoapClient($this->path . self::WSDL, array(
            'soap_version' => SOAP_1_2,
            'location' => $this->URL,
            'trace' => 1,
            'exceptions' => 0
            )
        );
    }

    /*
     * Crea el archivo xml de TRAP
     */
    private function create_TRAP() {
        $TRAP = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<loginTicketRequest version="1.0">' .
            '</loginTicketRequest>');
        $TRAP->addChild('header');
        $TRAP->header->addChild('uniqueId', date('U'));
        $TRAP->header->addChild('generationTime', date('c', date('U') - 60));
        $TRAP->header->addChild('expirationTime', date('c', date('U') + 60));
        $TRAP->addChild('service', $this->service);
        $TRAP->asXML($this->path . 'xml/TRAP'.$this->ID_EMP.'.xml');
    }

    /*
     * This functions makes the PKCS#7 signature using TRAP as input file, CERT and
     * PRIVATEKEY to sign. Generates an intermediate file and finally trims the 
     * MIME heading leaving the final CMS required by WSAA.
     * 
     * devuelve el CMS
     */

    private function sign_TRAP() {
        
        $STATUS = openssl_pkcs7_sign(
            realpath($this->path . "xml/TRAP".$this->ID_EMP.".xml"),
            $this->path . "xml/TRAP".$this->ID_EMP.".tmp",
            "file://" . realpath($this->path . $this->CERT),
            array("file://" . realpath($this->path . $this->PRIVATEKEY), $this->PASSPHRASE),
            array(),
            !PKCS7_DETACHED);

        if (!$STATUS)
            throw new Exception("Error al generar firma PKCS#7");

        $inf = fopen($this->path . "xml/TRAP".$this->ID_EMP.".tmp", "r");
        $i = 0;
        $CMS = "";
        while (!feof($inf)) {
            $buffer = fgets($inf);
            if ($i++ >= 4)
                $CMS .= $buffer;
        }

        fclose($inf);
        //unlink("TRAP.xml");
        unlink($this->path . "xml/TRAP".$this->ID_EMP.".tmp");

        return $CMS;
    }

    /*
     * Conecta con el web service y obtiene el token y sign
     */
    private function call_WSAA($cms) {
        $results = $this->client->loginCms(array('in0' => $cms));

        // para logueo
        file_put_contents($this->path . "xml/request-loginCms".$this->ID_EMP."PADRON.xml", $this->client->__getLastRequest());
        file_put_contents($this->path . "xml/response-loginCms".$this->ID_EMP."PADRON.xml", $this->client->__getLastResponse());

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
     * Funcion principal que llama a las demas para generar el archivo TAP.xml
     * que contiene el token y sign
     */
    public function generar_TAP() {
        $this->create_TRAP();

        $TAP = $this->call_WSAA($this->sign_TRAP());

        if (!file_put_contents($this->path . $this->TAP_GEN, $TAP))
            throw new Exception("Error al generar al archivo TAP".$this->ID_EMP.".xml");

        $this->TAP = $this->xml2Array($TAP);

        return true;
    }

    /*
     * Obtener la fecha de expiracion del TAP
     * si no existe el archivo, devuelve false
     */
    public function get_expiration() {
        if (empty($this->TAP)) {
            $TAP_file = file($this->path . $this->TAP_GEN, FILE_IGNORE_NEW_LINES);
            if ($TAP_file) {
                $TAP_xml = '';
                for ($i = 0; $i < sizeof($TAP_file); $i++)
                    $TAP_xml.= $TAP_file[$i];
                $this->TAP = $this->xml2Array($TAP_xml);
                $r = $this->TAP['header']['expirationTime'];
            } else {
                $r = false;
            }
        } else {
            $r = $this->TAP['header']['expirationTime'];
        }

        return $r;
    }

    ////////////Agregada por CJC para Moon POS
    public function datosTAP(){
        $ticketAcceso = simplexml_load_file($this->path . $this->TAP_GEN);

        return array(
            'expiracion' => $ticketAcceso->header->expirationTime,
            'TokenAfip' => $ticketAcceso->credentials->token,
            'FirmaAfip' => $ticketAcceso->credentials->sign
        );
    }

}

?>