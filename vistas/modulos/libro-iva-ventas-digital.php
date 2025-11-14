<?php 

  //LIBRO_IVA_DIGITAL_VENTAS_CBTE

  require_once "../../controladores/ventas.controlador.php";
  require_once "../../modelos/ventas.modelo.php";
  require_once "../../controladores/clientes.controlador.php";
  require_once "../../modelos/clientes.modelo.php";
  
  date_default_timezone_set('America/Argentina/Mendoza');
  $fechaInicial = $_GET["fechaInicial"];
  $fechaFinal = $_GET["fechaFinal"];

  header('Expires: 0');
  header('Cache-control: private');
  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=VENTAS_CBTE_".$fechaInicial."_".$fechaFinal.".txt");
  header("Content-Type: text/plain");

  $comprobantes = "";
  $libroIva = ControladorVentas::ctrLibroIvaVentas($fechaInicial, $fechaFinal);

  foreach ($libroIva as $key => $value) {
    
    //fecha (AAAAMMDD) 8 caracteres
    $comprobantes = $comprobantes . $value["fechavf"];

    //Tipo de comprobante 3 caracteres
    $comprobantes = $comprobantes . str_pad($value["cbte_tipo"],  3, "0", STR_PAD_LEFT);

    //Punto de venta 5 caracteres
    $comprobantes = $comprobantes . str_pad($value["pto_vta"],  5, "0", STR_PAD_LEFT);

    //Número de comprobante - 20 caracteres
    $comprobantes = $comprobantes . str_pad($value["nro_cbte"],  20, "0", STR_PAD_LEFT);

    //Número de comprobante HASTA - 20 caracteres
    $comprobantes = $comprobantes . str_pad($value["nro_cbte"],  20, "0", STR_PAD_LEFT);

    //Código de documento del comprador - 2 caracteres
    $comprobantes = $comprobantes . str_pad($value["tipo_documento"],  2, "0", STR_PAD_LEFT);

    //Número de identificación del comprador - 20 caracteres
    $comprobantes = $comprobantes . str_pad($value["documento"],  20, "0", STR_PAD_LEFT);

    //Apellido y nombre o denominación del comprador - 30 caracteres
    $comprobantes = $comprobantes . strtoupper(str_pad(substr($value["nombre"], 0, 29),  30, " ", STR_PAD_RIGHT));

    //Importe total de la operación - 15 caracteres, 13 enteros 2 decimales sin punto decimal
    $entero = (int)$value["total"];
    $decimal = $value["total"] - $entero;
    $decimal = number_format($decimal, 2);
    $decimal = str_replace('0.', "", $decimal);
    $comprobantes = $comprobantes . str_pad($entero,  13, "0", STR_PAD_LEFT);
    $comprobantes = $comprobantes . str_pad($decimal,  2, "0", STR_PAD_RIGHT);

    //Importe total de conceptos que no integran el precio neto gravado - 15 caracteres 13 enteros 2 decimales sin punto decimal
    $entero = 0;
    $decimal = 0;
    $decimal = number_format($decimal, 2);
    $decimal = str_replace('0.', "", $decimal);
    $comprobantes = $comprobantes . str_pad($entero,  13, "0", STR_PAD_LEFT);
    $comprobantes = $comprobantes . str_pad($decimal,  2, "0", STR_PAD_RIGHT);

    //Percepción a no categorizados - 15 caracteres 13 enteros 2 decimales sin punto decimal
    $entero = 0;
    $decimal = 0;
    $decimal = number_format($decimal, 2);
    $decimal = str_replace('0.', "", $decimal);
    $comprobantes = $comprobantes . str_pad($entero,  13, "0", STR_PAD_LEFT);
    $comprobantes = $comprobantes . str_pad($decimal,  2, "0", STR_PAD_RIGHT);
    
    //Importe de operaciones exentas - 15 caracteres 13 enteros 2 decimales sin punto decimal
    $entero = 0;
    $decimal = 0;
    $decimal = number_format($decimal, 2);
    $decimal = str_replace('0.', "", $decimal);
    $comprobantes = $comprobantes . str_pad($entero,  13, "0", STR_PAD_LEFT);
    $comprobantes = $comprobantes . str_pad($decimal,  2, "0", STR_PAD_RIGHT);

    //Importe de percepciones o pagos a cuenta de impuestos Nacionales - 15 caracteres 13 enteros 2 decimales sin punto decimal
    $entero = 0;
    $decimal = 0;
    $decimal = number_format($decimal, 2);
    $decimal = str_replace('0.', "", $decimal);
    $comprobantes = $comprobantes . str_pad($entero,  13, "0", STR_PAD_LEFT);
    $comprobantes = $comprobantes . str_pad($decimal,  2, "0", STR_PAD_RIGHT);
    
    //Importe de percepciones de Ingresos Brutos - 15 caracteres 13 enteros 2 decimales sin punto decimal
    $entero = 0;
    $decimal = 0;
    $decimal = number_format($decimal, 2);
    $decimal = str_replace('0.', "", $decimal);
    $comprobantes = $comprobantes . str_pad($entero,  13, "0", STR_PAD_LEFT);
    $comprobantes = $comprobantes . str_pad($decimal,  2, "0", STR_PAD_RIGHT);

    //Importe de percepciones impuestos Municipales - 15 caracteres 13 enteros 2 decimales sin punto decimal
    $entero = 0;
    $decimal = 0;
    $decimal = number_format($decimal, 2);
    $decimal = str_replace('0.', "", $decimal);
    $comprobantes = $comprobantes . str_pad($entero,  13, "0", STR_PAD_LEFT);
    $comprobantes = $comprobantes . str_pad($decimal,  2, "0", STR_PAD_RIGHT);

    //Importe impuestos internos - 15 caracteres 13 enteros 2 decimales sin punto decimal
    $entero = 0;
    $decimal = 0;
    $decimal = number_format($decimal, 2);
    $decimal = str_replace('0.', "", $decimal);
    $comprobantes = $comprobantes . str_pad($entero,  13, "0", STR_PAD_LEFT);
    $comprobantes = $comprobantes . str_pad($decimal,  2, "0", STR_PAD_RIGHT);

    //Código de moneda - 3 caracteres
    $comprobantes = $comprobantes . 'PES';

    //Tipo de cambio - 10 caracteres 4 enteros 6 decimales sin punto decimal
    $comprobantes = $comprobantes . '0001000000';

    //Cantidad de alícuotas de IVA - 1 caracter
    $cantAli = 0;
    if ($value["base_imponible_0"] != 0) {
      $cantAli ++;
    }
    if ($value["base_imponible_2"] != 0) {
      $cantAli ++;
    }
    if ($value["base_imponible_5"] != 0) {
      $cantAli ++;
    }
    if ($value["base_imponible_10"] != 0) {
      $cantAli ++;
    }
    if ($value["base_imponible_21"] != 0) {
      $cantAli ++;
    }
    if ($value["base_imponible_27"] != 0) {
      $cantAli ++;
    }

    $comprobantes = $comprobantes . $cantAli;

    //Código de operación - 1 caracter - CONSULTAR
      // A No Alcanzado No Alcanzado
      // (blanco) No corresponde No corresponde
      // C Operac. Canje Operac. Canje
      // D Devol. IVA Turistas Extr. Devol. IVA Turistas Extr.
      // E Operaciones Exentas Operaciones Exentas
      // N No gravado No gravado
      // T Reintegro Decreto 1043/2016 Reintegro Decreto 1043/2016
      // X Importación del Exterior Exportación al Exterior
      // Z Importación de Zona Franca Exportación a Zona Franca
    $comprobantes = $comprobantes . ' ';

    //Otros Tributos - 15 caracteres 13 enteros 2 decimales sin punto decimal
    $comprobantes = $comprobantes . '000000000000000';

    //Nueva linea
    $comprobantes = $comprobantes . PHP_EOL;
  }

  echo $comprobantes;

  exit;