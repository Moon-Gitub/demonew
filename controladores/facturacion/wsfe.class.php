<?php

class WSFE {

    const WSDL = "ws/wsfe.wsdl";             # The WSDL corresponding to WSFE
    const TA = "xml/TA.xml";                 # Archivo con el Token y Sign   
    const PROXY_ENABLE = false;
    //const WSFEURL = "https://wswhomo.afip.gov.ar/wsfev1/service.asmx"; //testing
    //const WSFEURL = "https://servicios1.afip.gov.ar/wsfev1/service.asmx"; // produccion
    const LOG_XMLS = false;                  # For debugging purposes
    
    // const CERT = "keys/cert.pem";            # The X.509 certificate in PEM format
    // const PRIVATEKEY = "keys/qwer1234";      # The private key correspoding to CERT (PEM)
    // const PASSPHRASE = "qwer1234";           # The passphrase (if any) to sign
    
    /*
     * CUIT EMISOR DE FACTURAS
     */
    private $CUIT = "";

    private $WSFEURL = "https://wswhomo.afip.gov.ar/wsfev1/service.asmx"; //testing

    /*
     * Punto de Venta - Generado desde la pagina de AFIP
     */
    //private $pto_vta;

    /*
     * Tipo de comprobantes C:
                    Código 11 FACTURA C
                    Código 12 NOTA DE DEBITO C
                    Código 13 NOTA DE CREDITO C
                    Código 15 RECIBO C
     */
     //private $tipo_cbte = 0;

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
     * objeto que va a contener el xml de TA
     */
    private $TA;

    /*
     * Constructor
     */
    public function __construct($arrEmpresa) {

      $this->path = dirname(__FILE__) . '/';
      $this->CUIT = $arrEmpresa["cuit"] + 0; //No se porque antes andaba con string y ahora debe ser int
    // $this->CUIT = intval($arrEmpresa["cuit"]); //Antes se podia sumar 0 y lo tomaba como int, ahora al parecer hay que castear

      if($arrEmpresa["entorno_facturacion"] == "produccion") {
          $this->WSFEURL = "https://servicios1.afip.gov.ar/wsfev1/service.asmx"; // produccion
      }
        // seteos en php
      ini_set("soap.wsdl_cache_enabled", "0");

      date_default_timezone_set('America/Argentina/Buenos_Aires');

        // validar archivos necesarios
      if (!file_exists($this->path . self::WSDL))
        $this->error .= " Error al abrir WSDL: " . self::WSDL;

      if (!empty($this->error)) {
        throw new Exception('Error en clase WSFE. Faltan archivos necesarios para el funcionamiento');
      }

      // $opts = array(
      //   'ssl' => array('ciphers' => 'AES256-SHA')
      // );

      $this->client = new SoapClient($this->path . self::WSDL, array(
        'soap_version' => SOAP_1_2,
        'location' => $this->WSFEURL,
        'trace' => 1,
        'exceptions' => 0,
        'connection_timeout' => 5
        // 'encoding' => 'UTF-8',
        // 'cache_wsdl' => WSDL_CACHE_BOTH,
        // 'stream_context' => stream_context_create($opts)
       )
          //'connection_timeout' => 5) //VER SI ANDA EN 5 SEGUNDOS
      );

    }

    /*
     * Chequea los errores en la operacion, si encuentra algun error fatal lanza una exepcion
     * si encuentra un error no fatal, loguea lo que paso en $this->error
     */
    private function _checkErrors($results, $method) {
      if (self::LOG_XMLS) {
        file_put_contents("xml/request-" . $method . ".xml", $this->client->__getLastRequest());
        file_put_contents("xml/response-" . $method . ".xml", $this->client->__getLastResponse());
      }

      if (is_soap_fault($results)) {
        //return false;
        throw new Exception('Error WSFE. Detalle: ' . $results->faultcode . ' ' . $results->faultstring);
      }
    }

    /*
     * Abre el archivo de TA xml,
     * si hay algun problema devuelve false
     */
    public function openTA() {
      $this->TA = simplexml_load_file($this->path . self::TA);

      return $this->TA == false ? false : true;
    }
    
    public function datosTA(){
      return array(
        'Token' => $this->TA->credentials->token,
        'Firma' => $this->TA->credentials->sign,
        'Exp' => $this->TA->header->expirationTime
      );
    }

    /* 
     * Solicitud de Código de Autorización Electrónico (CAE).
     */

    public function CAESolicitar($pto_vta, $tipo_cbte, $regfac) {

            $regfac += array('Auth' => array
              ('Token' => $this->TA->credentials->token,
               'Sign' => $this->TA->credentials->sign,
               'Cuit' => $this->CUIT));

      $results = $this->client->FECAESolicitar($regfac);

      $e = $this->_checkErrors($results, 'FECAESolicitar');

      return $e == false ? $results : false;
    }

    /*
     * Retorna el ultimo comprobante autorizado para el tipo de comprobante / 
     * cuit / punto de venta ingresado / Tipo de Emisión
     */
    public function UltimoAutorizado($pto_vta, $tipo_cbte) {

      $results = $this->client->FECompUltimoAutorizado(
        array('Auth' => array
          (
            'Token' => $this->TA->credentials->token,
            'Sign' => $this->TA->credentials->sign,
            'Cuit' => $this->CUIT
          ),
          'PtoVta' => $pto_vta, 
          'CbteTipo' => $tipo_cbte
        )
      );

      $e = $this->_checkErrors($results, 'FECompUltimoAutorizado');

      return $e == false ? $results->FECompUltimoAutorizadoResult->CbteNro : false;
    }
    
    /*
     * Consulta Comprobante emitido y su código.
     */
    public function ConsultarAutorizado($pto_vta, $tipo_cbte, $nrocbte) {

      $results = $this->client->FECompConsultar(
        array(
          'Auth' => array (
            'Token' => $this->TA->credentials->token,
            'Sign' => $this->TA->credentials->sign,
            'Cuit' => $this->CUIT
          ),
          'FECompConsultarResult' => array (
            'PtoVta' => $pto_vta, 
            'CbteTipo' => $tipo_cbte,
            'CbteNro' => $nrocbte
          )
        )
      );

      $e = $this->_checkErrors($results, 'FECompConsultarResult');

      return $e == false ? $results : false;
    }

    /*
     * Retorna la cantidad maxima de registros que puede tener una invocacion 
     * al metodo FECAESolicitar / FECAEARegInformativo
     */
    public function CantidadRegistros() {

      $results = $this->client->FECompTotXRequest(
        array(
          'Auth' => array(
            'Token' => $this->TA->credentials->token,
            'Sign' => $this->TA->credentials->sign,
            'Cuit' => $this->CUIT)
        )
      );

      $e = $this->_checkErrors($results, 'FECompTotXRequest');

      return $e == false ? $results : false;
    }

    /*
     * Recupera el listado de los diferente paises que pueden ser utilizados en el servicio de autorizacion
     */
    public function TiposPaises() {

      $results = $this->client->FEParamGetTiposPaises (
        array(
          'Auth' => array(
            'Token' => $this->TA->credentials->token,
            'Sign' => $this->TA->credentials->sign,
            'Cuit' => $this->CUIT)
        )
      );

      $e = $this->_checkErrors($results, 'FEParamGetTiposPaises');

      return $e == false ? $results : false;
    }    
    
    /*
     * Metodo dummy para verificacion de funcionamiento
     */
    public function PruebaConexion() {

      $results = $this->client->FEDummy();

      $e = $this->_checkErrors($results, 'FEDummy');

      return $e == false ? $results : false;
    }

     /*
     * Recupera el listado de puntos de venta registrados y su estado
     */
    public function PtosVenta() {

      $results = $this->client->FEParamGetPtosVenta(
        array(
          'Auth' => 
            array('Token' => $this->TA->credentials->token,
                  'Sign' => $this->TA->credentials->sign,
                  'Cuit' => $this->CUIT
          )
        )
      );

      $e = $this->_checkErrors($results, 'FEParamGetPtosVenta');

      return $e == false ? $results : false;
    }
    
    /*
     * Recupera el listado de Tipos de Comprobantes utilizables en 
     * servicio de autorización.
     */
    public function TiposCbte() {
      $results = $this->client->FEParamGetTiposCbte(
        array('Auth' => array('Token' => $this->TA->credentials->token,
          'Sign' => $this->TA->credentials->sign,
          'Cuit' => $this->CUIT
        )
      )
      );

      $e = $this->_checkErrors($results, 'FEParamGetTiposCbte');

      return $e == false ? $results : false;
    }
    
    /*
     * Recupera el listado de identificadores para el campo Concepto.
     */
    public function TiposConcepto() {

      $results = $this->client->FEParamGetTiposConcepto(
        array('Auth' => array('Token' => $this->TA->credentials->token,
          'Sign' => $this->TA->credentials->sign,
          'Cuit' => $this->CUIT
        )
      )
      );

      $e = $this->_checkErrors($results, 'FEParamGetTiposConcepto');

      return $e == false ? $results : false;
    }
    
    /*
     * Recupera el listado de Tipos de Documentos utilizables en servicio 
     * de autorización.
     */
    public function TiposDoc() {

      $results = $this->client->FEParamGetTiposDoc(
        array('Auth' => array('Token' => $this->TA->credentials->token,
          'Sign' => $this->TA->credentials->sign,
          'Cuit' => $this->CUIT
        )
      )
      );

      $e = $this->_checkErrors($results, 'FEParamGetTiposDoc');

      return $e == false ? $results : false;
    }
    
    /*
     * Recupera el listado de Tipos de Iva utilizables en servicio de 
     * autorización.
     */
    public function TiposIva() {

      $results = $this->client->FEParamGetTiposIva(
        array('Auth' => array('Token' => $this->TA->credentials->token,
          'Sign' => $this->TA->credentials->sign,
          'Cuit' => $this->CUIT
        )
      )
      );

      $e = $this->_checkErrors($results, 'FEParamGetTiposIva');

      return $e == false ? $results : false;
    }
    
    /*
     * Recupera el listado de monedas utilizables en servicio de autorización
     */
    public function TiposMonedas() {

      $results = $this->client->FEParamGetTiposMonedas(
        array('Auth' => array('Token' => $this->TA->credentials->token,
          'Sign' => $this->TA->credentials->sign,
          'Cuit' => $this->CUIT
        )
      )
      );

      $e = $this->_checkErrors($results, 'FEParamGetTiposMonedas');

      return $e == false ? $results : false;
    }

    /*
     * Recupera la cotizacion de la moneda consultada y su fecha
     */
    public function Cotizacion($moneda) {

      $results = $this->client->FEParamGetCotizacion(
        array('Auth' => array('Token' => $this->TA->credentials->token,
          'Sign' => $this->TA->credentials->sign,
          'Cuit' => $this->CUIT),
        'MonId' => $moneda
      )
      );

      $e = $this->_checkErrors($results, 'FEParamGetCotizacion');

      return $e == false ? $results : false;
    }

    /*
    * Recuperador de valores referenciales de códigos de Tipos de datos Opcionales
    */
    public function Opcionales() {

      $results = $this->client->FEParamGetTiposOpcional(
        array('Auth' => array('Token' => $this->TA->credentials->token,
          'Sign' => $this->TA->credentials->sign,
          'Cuit' => $this->CUIT
        )
      )
      );

      $e = $this->_checkErrors($results, 'FEParamGetTiposOpcional');

      return $e == false ? $results : false;
    }

    /*
     * Recuperador de valores referenciales de códigos de Tipos de Tributos
     */
    public function TiposTributos() {

      $results = $this->client->FEParamGetTiposTributos(
        array('Auth' => array('Token' => $this->TA->credentials->token,
          'Sign' => $this->TA->credentials->sign,
          'Cuit' => $this->CUIT
        )
      )
      );

      $e = $this->_checkErrors($results, 'FEParamGetTiposOpcional');

      return $e == false ? $results : false;
    }

    /*
     * 
     */
    public function CondicionIvaReceptor() {

      $results = $this->client->FEParamGetCondicionIvaReceptor (
        array(
          'Auth' => array(
            'Token' => $this->TA->credentials->token,
            'Sign' => $this->TA->credentials->sign,
            'Cuit' => $this->CUIT)
        )
      );

      $e = $this->_checkErrors($results, 'FEParamGetCondicionIvaReceptor');

      return $e == false ? $results : false;
    }

}

?>