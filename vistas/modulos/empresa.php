<?php

if($_SESSION["perfil"] == "Vendedor"){
  echo '<script>
  window.location = "inicio";
  </script>';
  return;
}

?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar empresa
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar empresa</li>
    </ol>
  </section>
  <section class="content">
    <div class="box">
      <div class="box-body">
       <form role="form" method="post" enctype="multipart/form-data">
        <div class="row">

          <!-- ENTRADA PARA EL NOMBRE -->
          <div class="col-md-4">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <?php 
                    echo '<input type="text" class="form-control " name="empRazonSocial" id="empRazonSocial" placeholder="Ingresar razon social" value= "'. $arrayEmpresa['razon_social'] . '" required>';
                ?>
                <input type="hidden"  name="idEmpresa" id="idEmpresa" value="1">
              </div>
            </div>  
          </div>
        
          <!-- ENTRADA TITULAR -->
          <div class="col-md-4">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <?php 
                    echo '<input type="text" class="form-control " name="empTitular" id="empTitular" placeholder="Ingresar Titular" value= "'. $arrayEmpresa['titular'] . '" required>';
                ?>
              </div>
            </div>              
          </div>          

          <!-- ENTRADA PARA EL CUIT -->
          <div class="col-md-4">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <?php 
                    echo '<input type="text" class="form-control " name="empCuit" id="empCuit" placeholder="Ingresar C.U.I.T." value= "'. $arrayEmpresa['cuit'] . '" required>';
                ?>
              </div>
            </div>              
          </div>
        
        </div>

        <div class="row">
          <!-- ENTRADA PARA EL DOMICILIO -->
          <div class="col-md-4">
           <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
                echo '<input type="text" class="form-control " name="empDomicilio" id="empDomicilio" placeholder="Ingresar domicilio" value= "'. $arrayEmpresa['domicilio'] . '">';
              ?>
            </div>
          </div>
        </div>

        <!-- ENTRADA PARA EL LOCALIDAD -->
        <div class="col-md-4">
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
                echo '<input type="text" class="form-control " name="empLocalidad" id="empLocalidad" placeholder="Ingresar localidad" value= "'. $arrayEmpresa['localidad'] . '">';
              ?>
            </div>
          </div>
        </div>

        <!-- ENTRADA PARA CODIGO POSTAL -->
        <div class="col-md-4">
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
                  echo '<input type="text" class="form-control " name="empCodPostal" id="empCodPostal" placeholder="Ingresar codigo postal" value= "'. $arrayEmpresa['codigo_postal'] . '">';
              ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row">

        <!-- ENTRADA PARA MAIL -->
        <div class="col-md-6">
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
                  echo '<input type="text" class="form-control " name="empMail" id="empMail" placeholder="Ingresar e-mail" value= "'. $arrayEmpresa['mail'] . '">';
              ?>
            </div>
          </div>
        </div>

        <!-- ENTRADA PARA TELEFONO -->
        <div class="col-md-6">
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
                  echo '<input type="text" class="form-control " name="empTelefono" id="empTelefono" placeholder="Ingresar telefono" value= "'. $arrayEmpresa['telefono'] . '">';
              ?>
            </div>
          </div>
        </div>

      </div>

      <div class="row">
        <div class="col-md-3">
          <!-- ENTRADA PARA EL PUNTO DE VENTA -->      
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
              echo '<input type="text" class="form-control " name="empPtosVta" id="empPtosVta" placeholder="Ingresar  todos los puntos de venta separados por coma. Ej. 2, 3, 4" value= "'. htmlspecialchars($arrayEmpresa['ptos_venta']) . '">';
              ?>
            </div>
          </div>    
        </div>
        <div class="col-md-3">
          <!-- ENTRADA PARA STOCK -->      
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
              echo '<input type="text" class="form-control " name="empStock" id="empStock" placeholder="Ingresar almacenes (según tabla productos)" value= "'. htmlspecialchars($arrayEmpresa['almacenes']) . '">';
              ?>
            </div>
          </div>    
        </div>
        <div class="col-md-3">
          <!-- ENTRADA PARA EL PUNTO DE VENTA DEFECTO-->
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
              echo '<input type="text" class="form-control " name="empPtoVtaDefecto" id="empPtoVtaDefecto" placeholder="Ingresar punto de venta por defecto" value= "'. $arrayEmpresa['pto_venta_defecto'] . '">';
              ?>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <!-- ENTRADA PARA CONDICION FRENTE A IVA-->
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 
              <?php 
              $arrCondIva = [ 
                 "" => "Seleccione condición I.V.A ",
                 "1" => "IVA Responsable Inscripto ",
                 "6" => "Responsable Monotributo ",
                 "4" => "IVA Sujeto Exento",
                 "7" => "Sujeto no Categorizado",
                 "10" => "IVA Liberado – Ley Nº 19.640 ",
                 "13" => "Monotributista Social ",
                 "15" => "IVA No Alcanzado",
                 "16" => "Monotributo Trabajador Independiente Promovido"
                ];

              echo '<select class="form-control " name="empCondicionIva" id="empCondicionIva">';
              foreach ($arrCondIva as $key => $value) {
                if ($key == $arrayEmpresa['condicion_iva']) {
                  echo '<option value="' . $key . '" selected>' . $value . '</option>';
                } else {
                  echo '<option value="' . $key . '">' . $value . '</option>';
                }
              }  
              echo '</select>';
              ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">

          <!-- ENTRADA PARA CONDICION FRENTE A IVA-->
          <div class="form-group">

            <div class="input-group">

              <span class="input-group-addon"><i class="fa fa-th"></i></span> 


              <?php 
              $arrCondiibb = [ 
                "" => "Seleccione condición Ingresos Brutos",
                "1" => "Regimen simplificado",
                "2" => "Exento",
                "3" => "Local (Provincial o CABA)",
                "4" => "Otra",
                "5" => "Convenio Multilateral"
              ];

              echo '<select class="form-control " name="empCondicionIIBB" id="empCondicionIIBB">';
              foreach ($arrCondiibb as $key => $value) {

                if ($key == $arrayEmpresa['condicion_iibb']) {
                  echo '<option value="' . $key . '" selected>' . $value . '</option>';
                } else {
                  echo '<option value="' . $key . '">' . $value . '</option>';
                }

              }  

              echo '</select>';

              ?>

            </div>
          </div>
        </div>

        <div class="col-md-3">

          <!-- ENTRADA PARA NUMERO IIBB-->

          <div class="form-group">

            <div class="input-group">

              <span class="input-group-addon"><i class="fa fa-th"></i></span> 


              <?php 

              echo '<input type="text" class="form-control " name="empNumeroIIBB" id="empNumeroIIBB" placeholder="Ingresar número IIBB" value= "'. $arrayEmpresa['numero_iibb'] . '">';

              ?>
              

            </div>

          </div>

        </div>
        <div class="col-md-3">

          <!-- ENTRADA PARA INICIO ACTIVIDADES-->

          <div class="form-group">

            <div class="input-group">

              <span class="input-group-addon"><i class="fa fa-th"></i></span> 


              <?php 

              echo '<input type="text" class="form-control " name="empInicioActividades" id="empInicioActividades" placeholder="Ingresar fecha inicio actividades" value= "'. $arrayEmpresa['inicio_actividades'] . '">';

              ?>
              

            </div>

          </div>

        </div>        
        <div class="col-md-3">
          <!-- ENTRADA PARA CONCEPTO-->
          <div class="form-group">

            <div class="input-group">

              <span class="input-group-addon"><i class="fa fa-th"></i></span> 

              <?php 
              $arrConceptos = [ 
                "0" => "Seleccionar concepto por defecto",
                "1" => "Productos",
                "2" => "Servicios",
                "3" => "Productos y Servicios"
              ];

              echo '<select class="form-control " name="empConceptoDefecto" id="empConceptoDefecto">';
              foreach ($arrConceptos as $key => $value) {

                if ($key == $arrayEmpresa['concepto_defecto']) {
                  echo '<option value="' . $key . '" selected>' . $value . '</option>';
                } else {
                  echo '<option value="' . $key . '">' . $value . '</option>';
                }

              }  

              echo '</select>';

              ?>

            </div>
          </div>
        </div>
      </div>


      <div class="row">
        <div class="col-md-3">

          <!-- ENTRADA PARA NUMERO ESTABLECIMIENTO-->
          <div class="form-group">

            <div class="input-group">

              <span class="input-group-addon"><i class="fa fa-th"></i></span> 

                <?php 

                  echo '<input type="text" class="form-control " name="empNumeroEstablecimiento" id="empNumeroEstablecimiento" placeholder="Ingresar número establecimiento" value= "'. $arrayEmpresa['numero_establecimiento'] . '">';

                ?>

            </div>
          </div>
        </div>

        <div class="col-md-3">

          <!-- ENTRADA PARA NUMERO CBU-->

          <div class="form-group">

            <div class="input-group">

              <span class="input-group-addon"><i class="fa fa-th"></i></span> 


              <?php 

              echo '<input type="text" class="form-control " name="empNumeroCBU" id="empNumeroCBU" placeholder="Ingresar CBU" value= "'. $arrayEmpresa['cbu'] . '">';

              ?>
              

            </div>

          </div>

        </div>
        <div class="col-md-3">

          <!-- ENTRADA PARA ALIAS CBU-->

          <div class="form-group">

            <div class="input-group">

              <span class="input-group-addon"><i class="fa fa-th"></i></span> 


              <?php 

              echo '<input type="text" class="form-control " name="empNumeroCBUAlias" id="empNumeroCBUAlias" placeholder="Ingresar alias CBU" value= "'. $arrayEmpresa['cbu_alias'] . '">';

              ?>
              

            </div>

          </div>

        </div>        
        
      </div>


      <!-- ENTRADA TIPOS DE COMPROBANTES-->
      <div class="row">
        <div class="col-md-12">
          <label class="control-label">Tipos de comprobante que realiza</label>
        </div>
      </div>

      <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-2"><label class="control-label">Tipos de comprobante A</label></div>
        <div class="col-md-2"><label class="control-label">Tipos de comprobante B</label></div>
        <div class="col-md-2"><label class="control-label">Tipos de comprobante C</label></div>
        <div class="col-md-2"><label class="control-label">Tipos de comprobante E</label></div>
        <div class="col-md-2"><label class="control-label">Tipos de comprobante M</label></div>
      </div>

      <?php 
      $tiposCbtes = array(
        'Factura A' => 1, 
        'Factura B' => 6, 
        'Factura C' => 11, 
        'Factura E' => 'NO', 
        'Factura M' => 51, 
        'Nota Débito A' => 2, 
        'Nota Débito B' => 7, 
        'Nota Débito C' => 12, 
        'Nota Débito E' => 'NO', 
        'Nota Débito M' => 52, 
        'Nota Crédito A' => 3, 
        'Nota Crédito B' => 8, 
        'Nota Crédito C' => 13, 
        'Nota Crédito E' => 'NO',
        'Nota Crédito M' => 53, 
        'Recibo A' => 4, 
        'Recibo B' => 9, 
        'Recibo C' => 15, 
        'Recibo E' => 'NO', 
        'Recibo M' => 54,
        'Factura MiPyMEs (FCE) A' => 201,
        'Factura MiPyMEs (FCE) B' => 206,
        'Factura MiPyMEs (FCE) C' => 211,
        '1' => null,
        '2' => null,
        'Nota de Débito MiPyMEs (FCE) A' => 202,
        'Nota de Débito MiPyMEs (FCE) B' => 207,
        'Nota de Débito MiPyMEs (FCE) C' => 212,
        '3' => null,
        '4' => null,
        'Nota de Crédito MiPyMEs (FCE) A' => 203,        
        'Nota de Crédito MiPyMEs (FCE) B' => 208,        
        'Nota de Crédito MiPyMEs (FCE) C' => 213,
        '5' => null,
        '6' => null

      );

      $arrTiposCbteBd = json_decode($arrayEmpresa['tipos_cbtes']);

      $x = 0;
      foreach ($tiposCbtes as $key => $value) {
        if ($x==0) {
          echo '<div class="row">';
          echo '<div class="col-md-1"></div>';
        }

        echo '<div class="col-md-2">';

        $habilitado = "";
        $chekeado = "";

        if(strpos($key, ' A') || strpos($key, ' B') || strpos($key, ' E') || strpos($key, ' M'))
          $habilitado = '';

        foreach ($arrTiposCbteBd as $clave => $valor) {

          if ($value == $valor->codigo) {
            $chekeado = 'checked';
            break;
          }

        }

        if(isset($value)) {
          echo  '<input class="chkTiposCbtes" ' . $habilitado . ' ' . $chekeado .' type="checkbox" value="'.$value.'" cbteDesc="'.$key.'" > ' . $key;
        } else {
          echo '';
        }

        echo '</div>';
        $x++;

        if($x==5) {
          echo '</div>';
          $x=0;
        }

      }

      echo "<input type='hidden' id='empTipoCbtes' name='empTipoCbtes' value='".$arrayEmpresa['tipos_cbtes']."'>";

      ?>

      <hr>

      <p><b>Entorno de facturación</b></p>

      <?php 
        if($arrayEmpresa["entorno_facturacion"] == "produccion"){

          $chkProduccion = "checked";
          $chkTesting = "";
          $chkNull = "";

        } elseif($arrayEmpresa["entorno_facturacion"] == "testing"){

          $chkProduccion = "";
          $chkTesting = "checked";
          $chkNull = "";

        } else {

          $chkProduccion = "";
          $chkTesting = "";
          $chkNull = "checked";

        }
      ?>
      <div class="row"> 
        <div class="col-md-3">
          <div class="input-group">
          <span class="input-group-addon">
            <input type="radio" name="entornoFacturacion" value="testing" <?php echo $chkTesting; ?>>
          </span>
            <input type="text" class="form-control input-sm" value="Testing" readonly>
          </div>
        </div>
        <div class="col-md-3">
          <div class="input-group">
          <span class="input-group-addon">
            <input type="radio" name="entornoFacturacion" value="produccion" <?php echo $chkProduccion; ?>>
          </span>
            <input type="text" class="form-control input-sm" value="Produccion" readonly>
          </div>
        </div>
        <div class="col-md-3">
          <div class="input-group">
          <span class="input-group-addon">
            <input type="radio" name="entornoFacturacion" value="NULL" <?php echo $chkNull; ?>>
          </span>
            <input type="text" class="form-control input-sm" value="FACTURACION NO" readonly>
          </div>
        </div>
      </div>
      <hr>
      <p><b>Padron</b></p>
      <div class="row">
        <div class="col-md-4">
          <!-- ENTRADA PARA CONDICION FRENTE A IVA-->
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span> 

              <?php 
              $arrPadron = [ 
                "NULL" => "SIN padron",
                "ws_sr_padron_a4" => "Padron Alcance 4 (testing)",
                
                //"ws_sr_padron_a5" => "Padron Alcance 5",
                //"ws_sr_padron_a10" => "Padron Alcance 10",
                "ws_sr_padron_a13" => "Padron Alcance 13 (produccion)",
                //"ws_sr_padron_a100" => "Padron Alcance 100",
                //"ws_sr_constancia_inscripcion" => "Padron Consulta Padron",
                

              ];
              echo '<select class="form-control " name="empTipoPadron" id="empTipoPadron">';
              foreach ($arrPadron as $key => $value) {
                if ($key == $arrayEmpresa['ws_padron']) {
                  echo '<option value="' . $key . '" selected>' . $value . '</option>';
                } else {
                  echo '<option value="' . $key . '">' . $value . '</option>';
                }
              }  
              echo '</select>';
              ?>
            </div>
          </div>
        </div>
      </div>
      <div class="row" style="padding-top: 20px;">
        <div class="col-md-4">
          <!-- ENTRADA PARA EL CERTIFICADO CSR -...... NOOO!!! error!, lo eu se tiene que cargar es la clave generada con openssl-->
          <div class="form-group">
            <div class="input-group">
              <div class="panel">Subir Clave Pública (Formato .KEY generado con OpenSSL)</div>
              <?php 
              echo '<input type="hidden" id="hayCSR" name="hayCSR" value="'.$arrayEmpresa['csr'].'">';
              echo '<input type="hidden" id="hayPhrase" name="hayPhrase" value="'.$arrayEmpresa['passphrase'].'">';
              echo '<input type="file" id="empCSR" name="empCSR">';
              echo '<p class="help-block">Solitud de certificado </p>';
              //echo '<input type="text" disabled class="form-control " value= "'. $arrayEmpresa['csr'] . '">';
              if($arrayEmpresa['csr'] <> ""){
                echo '<img src="vistas/img/cert.png" class="img-thumbnail" width="100px">';  
              } else {
                echo '<img src="vistas/img/nocert.png" class="img-thumbnail" width="100px">';
              }
              ?>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <!-- ENTRADA PARA EL CERTIFICADO PEM -->
          <div class="form-group">
            <div class="input-group">
              <div class="panel">Subir certificado x509 (formato .PEM | .CRT devuelto por AFIP)</div>
              <?php 
              echo '<input type="hidden" id="hayPEM" name="hayPEM" value="'.$arrayEmpresa['pem'].'">';
              echo '<input type="file" id="empPEM" name="empPEM" >';
              echo '<p class="help-block">Certificado validado por AFIP</p>';
              //echo '<input type="text" disabled class="form-control " value= "'. $arrayEmpresa['pem'] . '">';
              if($arrayEmpresa['pem'] <> ""){
                echo '<img src="vistas/img/certAfip.png" class="img-thumbnail" width="100px">';  
              } else {
                echo '<img src="vistas/img/nocertAfip.png" class="img-thumbnail" width="100px">';
              }
              ?>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <!-- ENTRADA PARA IMAGEN EMPRESA -->
          <div class="form-group">
            <div class="input-group">
              <div class="panel">Subir logo empresa</div>
              <input disabled type="file" id="empLogo" name="empLogo">
              <p class="help-block">Peso máximo de la imagen 2MB</p>
              <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail" width="100px">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="box-footer with-border">
      <button type="submit" class="btn btn-primary pull-right">
        Guardar Empresa
      </button>
    </div>
    <?php
    $editarEmpresa = new ControladorEmpresa();
    $editarEmpresa -> ctrEditarEmpresa();
    ?> 
  </form>
</div>
</section>
</div>