<?php

// Usar rutas absolutas para que funcionen desde cualquier contexto
$raiz = dirname(__DIR__);
require_once $raiz . "/modelos/seguridad.modelo.php";
require_once $raiz . "/modelos/upload.modelo.php";
require_once $raiz . "/modelos/login.modelo.php";

class ControladorUsuarios{

	/*=============================================
	INGRESO DE USUARIO
	=============================================*/
	static public function ctrIngresoUsuario(){

		if(isset($_POST["ingUsuario"])){

			if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"])){

				// ✅ Verificar protección contra fuerza bruta
				if(ModeloLogin::estaBloqueado($_POST["ingUsuario"])){
					$tiempoRestante = ModeloLogin::tiempoRestanteBloqueo($_POST["ingUsuario"]);
					echo '<br><div class="alert alert-danger">
						<i class="fa fa-lock"></i> 
						Cuenta bloqueada por múltiples intentos fallidos. 
						Intenta nuevamente en ' . $tiempoRestante . ' minutos.
					</div>';
					return;
				}

				$tabla = "usuarios";

				$item = "usuario";
				$valor = $_POST["ingUsuario"];

				$respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);

				// Verificar que el usuario existe
				if($respuesta && $respuesta["usuario"] == $_POST["ingUsuario"]) {

					// Verificar contraseña (compatible con formato antiguo y nuevo)
					$passwordCorrecta = false;
					
					// Intentar verificación con password_verify (formato nuevo)
					if(ModeloSeguridad::verifyPassword($_POST["ingPassword"], $respuesta["password"])) {
						$passwordCorrecta = true;
					} 
					// Si falla, intentar con formato antiguo (compatibilidad)
					else {
						$encriptarAntiguo = crypt($_POST["ingPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
						if($respuesta["password"] == $encriptarAntiguo) {
							$passwordCorrecta = true;
							// Migrar automáticamente al nuevo formato
							$nuevoHash = ModeloSeguridad::hashPassword($_POST["ingPassword"]);
							ModeloUsuarios::mdlActualizarUsuario($tabla, "password", $nuevoHash, "id", $respuesta["id"]);
						}
					}

					if($passwordCorrecta){

						// ✅ Resetear intentos fallidos después de login exitoso
						ModeloLogin::resetearIntentos($_POST["ingUsuario"]);

						if($respuesta["estado"] == 1){

							// Verificar si el hash necesita actualización
							if(ModeloSeguridad::needsRehash($respuesta["password"])){
								$nuevoHash = ModeloSeguridad::hashPassword($_POST["ingPassword"]);
								ModeloUsuarios::mdlActualizarUsuario($tabla, "password", $nuevoHash, "id", $respuesta["id"]);
							}

							$_SESSION["iniciarSesion"] = "ok";
							$_SESSION["id"] = $respuesta["id"];
							$_SESSION["nombre"] = $respuesta["nombre"];
							$_SESSION["usuario"] = $respuesta["usuario"];
							$_SESSION["foto"] = $respuesta["foto"];
							$_SESSION["perfil"] = $respuesta["perfil"];
							$_SESSION["sucursal"] = $respuesta["sucursal"];
							$_SESSION["puntos_venta"] = $respuesta["puntos_venta"];
							$_SESSION["listas_precio"] = $respuesta["listas_precio"];
							$_SESSION['token'] = session_create_id();
							$_SESSION["empresa"] = $respuesta["empresa"];
							
							// Token CSRF
							$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

							// Permisos por rol: pantallas a las que puede acceder este perfil
							require_once $raiz . "/modelos/permisos_rol.modelo.php";
							if (ModeloPermisosRol::tablasExisten()) {
								$codigos = ModeloPermisosRol::mdlCodigosPermitidosPorRol($respuesta["perfil"]);
								// Si Administrador tiene lista vacía (tablas nuevas sin INSERTs), usar legacy para que pueda entrar a todo y a permisos-rol
								if ($respuesta["perfil"] === "Administrador" && empty($codigos)) {
									$_SESSION["permisos_pantallas"] = null;
								} else {
									$_SESSION["permisos_pantallas"] = $codigos;
								}
							} else {
								$_SESSION["permisos_pantallas"] = null; // legacy: sin filtro
							}

							/*=============================================
							REGISTRAR FECHA PARA SABER EL ÚLTIMO LOGIN
							=============================================*/
							date_default_timezone_set('America/Argentina/Mendoza');

							$fecha = date('Y-m-d');
							$hora = date('H:i:s');

							$fechaActual = $fecha.' '.$hora;

							$item1 = "ultimo_login";
							$valor1 = $fechaActual;

							$item2 = "id";
							$valor2 = $respuesta["id"];

							$ultimoLogin = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

							if($ultimoLogin == "ok"){

								echo '<script>
									window.location = "inicio";
								</script>';

							}				
							
						}else{

							echo '<br>
								<div class="alert alert-danger">El usuario aún no está activado</div>';

						}		

					}else{

						// ✅ Registrar intento fallido
						ModeloLogin::registrarIntentoFallido($_POST["ingUsuario"]);
						$intentosRestantes = ModeloLogin::intentosRestantes($_POST["ingUsuario"]);
						
						if($intentosRestantes > 0){
							echo '<br><div class="alert alert-danger">
								<i class="fa fa-exclamation-triangle"></i> 
								Error al ingresar, vuelve a intentarlo. 
								Intentos restantes: ' . $intentosRestantes . '
							</div>';
						} else {
							$tiempoRestante = ModeloLogin::tiempoRestanteBloqueo($_POST["ingUsuario"]);
							echo '<br><div class="alert alert-danger">
								<i class="fa fa-lock"></i> 
								Cuenta bloqueada por múltiples intentos fallidos. 
								Intenta nuevamente en ' . $tiempoRestante . ' minutos.
							</div>';
						}

					}

				}else{

					// ✅ Registrar intento fallido (usuario no existe)
					ModeloLogin::registrarIntentoFallido($_POST["ingUsuario"]);
					echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';

				}

			}	

		}

	}

	/*=============================================
	REGISTRO DE USUARIO
	=============================================*/
	static public function ctrCrearUsuario(){
		if(isset($_POST["nuevoUsuario"])){
			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
			   preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
			   preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])){
			   	/*=============================================
				✅ VALIDAR Y PROCESAR IMAGEN DE FORMA SEGURA
				=============================================*/
				$ruta = "";
				if(isset($_FILES["nuevaFoto"]["tmp_name"]) && $_FILES["nuevaFoto"]["tmp_name"] != ""){
					$resultado = ModeloUpload::procesarImagenUsuario($_FILES["nuevaFoto"], $_POST["nuevoUsuario"]);
					
					if($resultado['error']){
						echo '<script>
						swal({
							type: "error",
							title: "Error",
							text: "'.$resultado['mensaje'].'",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
						</script>';
						return;
					}
					
					$ruta = $resultado['ruta'];
				}
				$tabla = "usuarios";
				// ✅ Hash seguro con password_hash
				$encriptar = ModeloSeguridad::hashPassword($_POST["nuevoPassword"]);
				$listasPrecio = '';
				if(!empty($_POST['nuevoPreciosVentaUsuario'])) {    
                    foreach($_POST['nuevoPreciosVentaUsuario'] as $value){
                        $listasPrecio .= $value.',';
                    }
                    $listasPrecio = substr($listasPrecio, 0, -1);
                }

				$datos = array("nombre" => $_POST["nuevoNombre"],
					           "usuario" => $_POST["nuevoUsuario"],
					           "password" => $encriptar,
					           "perfil" => $_POST["nuevoPerfil"],
					           "sucursal" => $_POST["nuevaSucursal"],
					           "puntos_venta" => $_POST["nuevoPuntoVenta"],
					           "listas_precio" => $listasPrecio,
							   "empresa" => $_POST["nuevoRazonSocial"],
					           "foto"=>$ruta);
				$respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);
    			if($respuesta == "ok"){
					echo '<script>
					swal({
						type: "success",
						title: "Usuarios",
					  	text: "¡El usuario ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function() {
                        window.location.href = "usuarios";
                    });
					</script>';

				} else {
				    $msjResp = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";
				    echo '<script>
					swal({
						type: "error",
						title: "Usuarios",
					  	text: "'.$msjResp.'",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function() {
                        window.location.href = "usuarios";
                    });
					</script>';
				}
				
			}else{
				echo '<script>
					swal({
						type: "error",
						title: "Usuarios",
					  	text: "¡El usuario no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					})
				</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR USUARIO
	=============================================*/
	static public function ctrMostrarUsuarios($item, $valor){
		$tabla = "usuarios";
		$respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	EDITAR USUARIO
	=============================================*/
	static public function ctrEditarUsuario(){
		if(isset($_POST["editarUsuario"])){
			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"])){
				/*=============================================
				✅ VALIDAR Y PROCESAR IMAGEN DE FORMA SEGURA
				=============================================*/
				$ruta = $_POST["fotoActual"];
				if(isset($_FILES["editarFoto"]["tmp_name"]) && !empty($_FILES["editarFoto"]["tmp_name"])){
					// Eliminar imagen anterior si existe
					if(!empty($_POST["fotoActual"])){
						ModeloUpload::eliminarImagenUsuario($_POST["fotoActual"]);
					}
					
					$resultado = ModeloUpload::procesarImagenUsuario($_FILES["editarFoto"], $_POST["editarUsuario"]);
					
					if($resultado['error']){
						echo'<script>
							swal({
								type: "error",
								title: "Error",
								text: "'.$resultado['mensaje'].'",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							});
						</script>';
						return;
					}
					
					$ruta = $resultado['ruta'];
				}
				$tabla = "usuarios";
				if($_POST["editarPassword"] != ""){
					if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarPassword"])){
						// ✅ Hash seguro con password_hash
						$encriptar = ModeloSeguridad::hashPassword($_POST["editarPassword"]);
					}else{
						echo'<script>
								swal({
									  type: "error",
									  title: "Usuarios",
					  				  text: "¡La contraseña no puede ir vacía o llevar caracteres especiales!",
									  showConfirmButton: true,
									  confirmButtonText: "Cerrar"
									  })
						  	</script>';
						  	return;
					}
				}else{
					$encriptar = $_POST["passwordActual"];
				}
				
				$listasPrecio = '';
				if(!empty($_POST['editarPreciosVentaUsuario'])) {    
                    foreach($_POST['editarPreciosVentaUsuario'] as $value){
                        $listasPrecio .= $value.',';
                    }
                    $listasPrecio = substr($listasPrecio, 0, -1);
                }

				$datos = array("nombre" => $_POST["editarNombre"],
							   "usuario" => $_POST["editarUsuario"],
							   "password" => $encriptar,
							   "perfil" => $_POST["editarPerfil"],
							   "sucursal" => $_POST["editarSucursal"],
					           "puntos_venta" => $_POST["editarPuntoVenta"],
					           "listas_precio" => $listasPrecio,
							   "empresa" => $_POST["editarRazonSocial"],
							   "foto" => $ruta);

				$respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

				// Aceptar tanto "ok" (nuevo modelo) como true (por compatibilidad antigua)
				if($respuesta === "ok" || $respuesta === true){

					echo'<script>

					swal({
						  type: "success",
						  title: "Usuarios",
					  	  text: "El usuario ha sido editado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function() {
                        window.location.href = "usuarios";
                    });
					</script>';
				} else {
				    // Normalizar mensaje de error
				    if (is_array($respuesta) && isset($respuesta[2])) {
				        $msjResp = $respuesta[2];
				    } elseif (is_string($respuesta)) {
				        $msjResp = $respuesta;
				    } elseif ($respuesta === false) {
				        $msjResp = "No se pudo actualizar el usuario (respuesta false del modelo)";
				    } else {
				        $msjResp = "Error desconocido al editar el usuario";
				    }

				    echo '<script>
						swal({
							type: "error",
							title: "Usuarios",
						  	text: "'.$msjResp.'",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(function() {
	                        window.location.href = "usuarios";
	                    });
					</script>';
				}
			}else{
				echo'<script>
					swal({
						  type: "error",
						  title: "Usuarios",
					  	  text: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function() {
                        window.location.href = "usuarios";
                    });
			  	</script>';
			}
		}
	}

	/*=============================================
	BORRAR USUARIO
	=============================================*/
	static public function ctrBorrarUsuario(){
		if(isset($_GET["idUsuario"])){
			$tabla ="usuarios";
			$datos = $_GET["idUsuario"];
			if($_GET["fotoUsuario"] != ""){
				unlink($_GET["fotoUsuario"]);
				rmdir('vistas/img/usuarios/'.$_GET["usuario"]);
			}
			$respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $datos);
			if($respuesta == "ok"){
				echo'<script>
				swal({
					  type: "success",
					  title: "Usuarios",
					  text: "El usuario ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar",
					  closeOnConfirm: false
					  }).then(function(result) {
							if (result.value) {
								window.location = "usuarios";
							}
					})
			
				</script>';
			}		
		}
	}
}