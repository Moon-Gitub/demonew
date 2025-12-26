	/*=============================================
	OBTENER ESTADÍSTICAS DEL DASHBOARD EN UNA SOLA CONSULTA (ULTRA OPTIMIZADO)
	=============================================*/
	static public function mdlEstadisticasDashboard($fechaHoy, $fechaSemanaInicio, $fechaSemanaFin, $fechaMesInicio, $fechaMesFin, $fechaMesAnteriorInicio, $fechaMesAnteriorFin){
		
		// OPTIMIZADO: Una sola consulta con UNION para todas las estadísticas
		$stmt = Conexion::conectar()->prepare("
			SELECT 'hoy' as periodo, COALESCE(SUM(total), 0) as total 
			FROM ventas 
			WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) 
			AND fecha = :fechaHoy
			
			UNION ALL
			
			SELECT 'semana' as periodo, COALESCE(SUM(total), 0) as total 
			FROM ventas 
			WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) 
			AND fecha BETWEEN :fechaSemanaInicio AND :fechaSemanaFin
			
			UNION ALL
			
			SELECT 'mes_actual' as periodo, COALESCE(SUM(total), 0) as total 
			FROM ventas 
			WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) 
			AND fecha BETWEEN :fechaMesInicio AND :fechaMesFin
			
			UNION ALL
			
			SELECT 'mes_anterior' as periodo, COALESCE(SUM(total), 0) as total 
			FROM ventas 
			WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) 
			AND fecha BETWEEN :fechaMesAnteriorInicio AND :fechaMesAnteriorFin
		");
		
		$stmt->bindParam(":fechaHoy", $fechaHoy, PDO::PARAM_STR);
		$stmt->bindParam(":fechaSemanaInicio", $fechaSemanaInicio, PDO::PARAM_STR);
		$stmt->bindParam(":fechaSemanaFin", $fechaSemanaFin, PDO::PARAM_STR);
		$stmt->bindParam(":fechaMesInicio", $fechaMesInicio, PDO::PARAM_STR);
		$stmt->bindParam(":fechaMesFin", $fechaMesFin, PDO::PARAM_STR);
		$stmt->bindParam(":fechaMesAnteriorInicio", $fechaMesAnteriorInicio, PDO::PARAM_STR);
		$stmt->bindParam(":fechaMesAnteriorFin", $fechaMesAnteriorFin, PDO::PARAM_STR);
		
		$stmt->execute();
		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$stmt->closeCursor();
		$stmt = null;
		
		// Convertir a array asociativo más fácil de usar
		$estadisticas = array();
		foreach ($resultados as $row) {
			$estadisticas[$row['periodo']] = floatval($row['total']);
		}
		
		return $estadisticas;
	}
