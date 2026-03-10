-- =====================================================
-- CREAR VISTA productos_cambios (sin DEFINER)
-- Para hosting compartido donde no hay privilegios SUPER
-- =====================================================

DROP VIEW IF EXISTS `productos_cambios`;

CREATE VIEW `productos_cambios` AS 
SELECT 
  `t2`.`fecha_hora` AS `fecha_hora`, 
  `t2`.`accion` AS `accion`, 
  `t1`.`id` AS `id_prod`, 
  `pro`.`descripcion` AS `descripcion`, 
  IF(`t1`.`stock` = `t2`.`stock`, `t1`.`stock`, CONCAT(`t1`.`stock`, ' a ', `t2`.`stock`)) AS `stock`, 
  IF(`t1`.`precio_compra` = `t2`.`precio_compra`, `t1`.`precio_compra`, CONCAT(`t1`.`precio_compra`, ' a ', `t2`.`precio_compra`)) AS `precio_compra`, 
  IF(`t1`.`precio_venta` = `t2`.`precio_venta`, `t1`.`precio_venta`, CONCAT(`t1`.`precio_venta`, ' a ', `t2`.`precio_venta`)) AS `precio_venta`, 
  IF(`t1`.`precio_venta_mayorista` = `t2`.`precio_venta_mayorista`, `t1`.`precio_venta_mayorista`, CONCAT(`t1`.`precio_venta_mayorista`, ' a ', `t2`.`precio_venta_mayorista`)) AS `precio_venta_mayorista`, 
  `t2`.`nombre_usuario` AS `nombre_usuario`, 
  `t2`.`cambio_desde` AS `cambio_desde` 
FROM ((`productos_historial` `t1` 
JOIN `productos_historial` `t2` ON `t1`.`id` = `t2`.`id`) 
LEFT JOIN `productos` `pro` ON `pro`.`id` = `t1`.`id`) 
WHERE (`t1`.`revision` = 1 AND `t2`.`revision` = 1) OR (`t2`.`revision` = `t1`.`revision` + 1) 
ORDER BY `t1`.`id` ASC, `t2`.`revision` ASC;
