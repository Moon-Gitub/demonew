<?php

class ClaseParametros{

  public $mediosPagos = array('Efectivo', 'TarjetaCredito', 'TarjetaDebito', 'Cheque', 'Transferencia');

  //VALOR POR DEFECTO EN MARGEN DE GANANCIA, UTILIZADO EN PRODUCTOS
  public $PROD_MARGEN_GANANCIA_DEFECTO = 40;

  //TIPO COMPROBANTE POR DEFECTO EN VENTAS
  public $TIPO_COMPROBANTE_DEFECTO = 0; //CBTE X

  public $LISTAS_DE_PRECIO_HABILITADAS = array('precio_venta'=>'Precio Publico');

  public $SUCURSALES_HABILITADAS = array('stock'=>'Local');

  public $PRECIO_DOLAR = false;

  //==================================================//
                      //METODOS
  //==================================================//
  public function getMediosPago(){

    return $this->mediosPagos;

  }

  public function getProdMargenGananciaDefecto(){

    return $this->PROD_MARGEN_GANANCIA_DEFECTO;

  }

  public function getCbteDefecto(){
    
    return $this->TIPO_COMPROBANTE_DEFECTO;
    
  }
    
  public function getListasPrecio(){

    return $this->LISTAS_DE_PRECIO_HABILITADAS;

  }
    
  public function getSucursales(){

    return $this->SUCURSALES_HABILITADAS;

  }

  public function getPrecioDolar(){

    return $this->PRECIO_DOLAR;

  }

}

/*============================
	TRIGGER PRODUCTOS
/*============================
-- Volcando estructura para tabla productos_historial
CREATE TABLE IF NOT EXISTS `productos_historial` (
  `accion` varchar(9) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT 'insertar',
  `revision` int NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int NOT NULL,

  `stock1` float DEFAULT NULL,
  `stock2` float DEFAULT NULL,
  `stock3` float DEFAULT NULL,
  `stock4` float DEFAULT NULL,
  `stock5` float DEFAULT NULL,
  `stock6` float DEFAULT NULL,
  `stock7` float DEFAULT NULL,
  `stock8` float DEFAULT NULL,
  `stock9` float DEFAULT NULL,
  `stock10` float DEFAULT NULL,

  `precio_compra` float DEFAULT NULL,
  `precio_venta` float DEFAULT NULL,
  `nombre_usuario` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `cambio_desde` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`,`revision`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=DYNAMIC;

SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `prod_eliminar` BEFORE DELETE ON `productos` FOR EACH ROW INSERT INTO productos_historial SELECT 'borrar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock1, pro.stock2, pro.stock3, pro.stock4, pro.stock5, pro.stock6, pro.stock7, pro.stock8, pro.stock9, pro.stock10, 
pro.precio_compra, pro.precio_venta, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = OLD.id//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `prod_insertar` AFTER INSERT ON `productos` FOR EACH ROW INSERT INTO productos_historial SELECT 'insertar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock1, pro.stock2, pro.stock3, pro.stock4, pro.stock5, pro.stock6, pro.stock7, pro.stock8, pro.stock9, pro.stock10, 
pro.precio_compra, pro.precio_venta, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `prod_modificar` AFTER UPDATE ON `productos` FOR EACH ROW 
IF NEW.stock1 <> OLD.stock1 || 
NEW.stock2 <> OLD.stock2 || 
NEW.stock3 <> OLD.stock3 ||
NEW.stock4 <> OLD.stock4 ||
NEW.stock5 <> OLD.stock5 ||
NEW.stock6 <> OLD.stock6 ||
NEW.stock7 <> OLD.stock7 ||
NEW.stock8 <> OLD.stock8 ||
NEW.stock9 <> OLD.stock9 ||
NEW.stock10 <> OLD.stock10 ||
NEW.precio_compra <> OLD.precio_compra || 
NEW.precio_venta <> OLD.precio_venta THEN
INSERT INTO productos_historial SELECT 'modificar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), pro.id, pro.stock1, pro.stock2, pro.stock3, pro.stock4, pro.stock5, pro.stock6, pro.stock7, pro.stock8, pro.stock9, pro.stock10, pro.precio_compra, pro.precio_venta, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id;
END IF//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para vista mavi.productos_cambios
-- EliminANDo tabla tempORal y crear estructura final de VIEW
DROP TABLE IF EXISTS `productos_cambios`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `productos_cambios` AS 
SELECT `t2`.`fecha_hora` AS `fecha_hora`,`t2`.`accion` AS `accion`,`t1`.`id` AS `id_prod`,`pro`.`codigoProveedOR` AS `codigoProveedOR`,`pro`.`descripcion` AS `descripcion`,
IF((`t1`.`stock1` = `t2`.`stock1`),`t1`.`stock1`,CONCAT(`t1`.`stock1`,' a ',`t2`.`stock1`)) AS `stock1`,
IF((`t1`.`stock2` = `t2`.`stock2`),`t1`.`stock2`,CONCAT(`t1`.`stock2`,' a ',`t2`.`stock2`)) AS `stock2`,
IF((`t1`.`stock3` = `t2`.`stock3`),`t1`.`stock3`,CONCAT(`t1`.`stock3`,' a ',`t2`.`stock3`)) AS `stock3`,
IF((`t1`.`stock4` = `t2`.`stock4`),`t1`.`stock4`,CONCAT(`t1`.`stock4`,' a ',`t2`.`stock4`)) AS `stock4`,
IF((`t1`.`stock5` = `t2`.`stock5`),`t1`.`stock5`,CONCAT(`t1`.`stock5`,' a ',`t2`.`stock5`)) AS `stock5`,
IF((`t1`.`stock6` = `t2`.`stock6`),`t1`.`stock6`,CONCAT(`t1`.`stock6`,' a ',`t2`.`stock6`)) AS `stock6`,
IF((`t1`.`stock7` = `t2`.`stock7`),`t1`.`stock7`,CONCAT(`t1`.`stock7`,' a ',`t2`.`stock7`)) AS `stock7`,
IF((`t1`.`stock8` = `t2`.`stock8`),`t1`.`stock8`,CONCAT(`t1`.`stock8`,' a ',`t2`.`stock8`)) AS `stock8`,
IF((`t1`.`stock9` = `t2`.`stock9`),`t1`.`stock9`,CONCAT(`t1`.`stock9`,' a ',`t2`.`stock9`)) AS `stock9`,
IF((`t1`.`stock10` = `t2`.`stock10`),`t1`.`stock10`,CONCAT(`t1`.`stock10`,' a ',`t2`.`stock10`)) AS `stock10`,
IF((`t1`.`precio_compra` = `t2`.`precio_compra`),`t1`.`precio_compra`,CONCAT(`t1`.`precio_compra`,' a ',`t2`.`precio_compra`)) AS `precio_compra`,
IF((`t1`.`precio_venta` = `t2`.`precio_venta`),`t1`.`precio_venta`,CONCAT(`t1`.`precio_venta`,' a ',`t2`.`precio_venta`)) AS `precio_venta`,
`t2`.`nombre_usuario` AS `nombre_usuario`,`t2`.`cambio_desde` AS `cambio_desde` FROM ((`productos_historial` `t1` JOIN `productos_historial` `t2` ON((`t1`.`id` = `t2`.`id`))) 
LEFT JOIN `productos` `pro` ON((`pro`.`id` = `t1`.`id`))) WHERE (((`t1`.`revision` = 1) AND (`t2`.`revision` = 1)) OR (`t2`.`revision` = (`t1`.`revision` + 1))) 
ORDER BY `t1`.`id`,`t2`.`revision`;
*/