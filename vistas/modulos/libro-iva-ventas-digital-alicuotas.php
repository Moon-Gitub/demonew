<?php 

  //LIBRO_IVA_DIGITAL_VENTAS_ALICUOTAS

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
  header("Content-Disposition: attachment; filename=VENTAS_ALICUOTAS_".$fechaInicial."_".$fechaFinal.".txt");
  header("Content-Type: text/plain");

  $comprobantes = "";
  $libroIva = ControladorVentas::ctrLibroIvaVentas($fechaInicial, $fechaFinal);

  foreach ($libroIva as $key => $value) {
    
    //Tipo de comprobante 3 caracteres
    $cbteTipo = str_pad($value["cbte_tipo"],  3, "0", STR_PAD_LEFT);

    //Punto de venta 5 caracteres
    $ptoVta = str_pad($value["pto_vta"],  5, "0", STR_PAD_LEFT);

    //Número de comprobante - 20 caracteres
    $nroCbte = str_pad($value["nro_cbte"],  20, "0", STR_PAD_LEFT);

    //Importe neto gravado - 15 caracteres - 15 caracteres, 13 enteros 2 decimales sin punto decimal

    //Alícuotas de IVA - 4 caracteres:
      // CÓDIGO    DESCRIPCIÓN
      // 0003      0,00 %
      // 0004      10,50 %
      // 0005      21,00 %
      // 0006      27,00 %
      // 0008      5,00 %
      // 0009      2,50 %

    //Impuesto Liquidado - 15 caracteres - 15 caracteres, 13 enteros 2 decimales sin punto decimal

    if ($value["base_imponible_0"] != 0) {

      $entero = (int)$value["base_imponible_0"];
      $decimal = $value["base_imponible_0"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $bimp0 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $bimp0 = $bimp0 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);
      
      $conIva0 = $cbteTipo . $ptoVta . $nroCbte . $bimp0 . '0003' . '000000000000000';

      $comprobantes = $comprobantes . $conIva0 . PHP_EOL;

    }

    if ($value["base_imponible_2"] != 0) {

      $entero = (int)$value["base_imponible_2"];
      $decimal = $value["base_imponible_2"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $bimp2 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $bimp2 = $bimp2 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);

      $entero = (int)$value["iva_2"];
      $decimal = $value["iva_2"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $iva2 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $iva2 = $iva2 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);
      
      $conIva2 = $cbteTipo . $ptoVta . $nroCbte . $bimp2 . '0009' . $iva2;

      $comprobantes = $comprobantes . $conIva2 . PHP_EOL;
      
    }

    if ($value["base_imponible_5"] != 0) {

      $entero = (int)$value["base_imponible_5"];
      $decimal = $value["base_imponible_5"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $bimp5 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $bimp5 = $bimp5 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);

      $entero = (int)$value["iva_5"];
      $decimal = $value["iva_5"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $iva5 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $iva5 = $iva5 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);
      
      $conIva5 = $cbteTipo . $ptoVta . $nroCbte . $bimp5 . '0008' . $iva5;

      $comprobantes = $comprobantes . $conIva5 . PHP_EOL;
      
    }

    if ($value["base_imponible_10"] != 0) {

      $entero = (int)$value["base_imponible_10"];
      $decimal = $value["base_imponible_10"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $bimp10 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $bimp10 = $bimp10 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);

      $entero = (int)$value["iva_10"];
      $decimal = $value["iva_10"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $iva10 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $iva10 = $iva10 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);
      
      $conIva10 = $cbteTipo . $ptoVta . $nroCbte . $bimp10 . '0004' . $iva10;

      $comprobantes = $comprobantes . $conIva10 . PHP_EOL;
      
    }

    if ($value["base_imponible_21"] != 0) {

      $entero = (int)$value["base_imponible_21"];
      $decimal = $value["base_imponible_21"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $bimp21 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $bimp21 = $bimp21 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);

      $entero = (int)$value["iva_21"];
      $decimal = $value["iva_21"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $iva21 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $iva21 = $iva21 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);

      $conIva21 = $cbteTipo . $ptoVta . $nroCbte . $bimp21 . '0005' . $iva21;

      $comprobantes = $comprobantes . $conIva21 . PHP_EOL;
      
    }

    if ($value["base_imponible_27"] != 0) {

      $entero = (int)$value["base_imponible_27"];
      $decimal = $value["base_imponible_27"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $bimp27 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $bimp27 = $bimp27 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);

      $entero = (int)$value["iva_27"];
      $decimal = $value["iva_27"] - $entero;
      $decimal = number_format($decimal, 2);
      $decimal = str_replace('0.', "", $decimal);
      $iva27 = str_pad($entero, 13, "0", STR_PAD_LEFT);
      $iva27 = $iva27 . str_pad($decimal, 2, "0", STR_PAD_RIGHT);

      $conIva27 = $cbteTipo . $ptoVta . $nroCbte . $bimp27 . '0006' . $iva27;

      $comprobantes = $comprobantes . $conIva27 . PHP_EOL;
      
    }    

  }

  echo $comprobantes;

  exit;