<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Nel\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Nel\Metodos\Metodos;
use Nel\Metodos\MetodosControladores;
use Nel\Metodos\Correo;
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Telefonos;
use Nel\Modelo\Entity\TelefonoPersona;
use Nel\Modelo\Entity\DireccionPersona;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class PersonaController extends AbstractActionController
{
    public $dbAdapter;
    public function ingresarpersonaAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 1, 3);
                if ($validarprivilegio==false)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                        $objTelefono = new Telefonos($this->dbAdapter);
                        $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idIglesia = $sesionUsuario->offsetGet('idIglesia');
                        $identificacion = trim($post['identificacion']);
                        $primerNombre = trim(strtoupper($post['primerNombre']));
                        $segundoNombre = trim(strtoupper($post['segundoNombre']));
                        $primerApellido = trim(strtoupper($post['primerApellido']));
                        $segundoApellido = trim(strtoupper($post['segundoApellido']));
                        $numeroTelefono = trim($post['telefono']);
                        $idProvinciaEncriptado = $post['selectProvincias'];
                        $idCantonEncriptado = $post['selectCantones'];
                        $idConfigurarParroquiaCantonEncriptado = $post['selectParroquias'];
                        $direccion = trim(strtoupper($post['direccion']));
                        $referencia = trim(strtoupper($post['referencia']));
                        $fechaNacimiento = $post['fechaNacimiento'];
                        $validarIngresoCedula = FALSE;
                        if(strlen($identificacion) > 0){
                            $validarIngresoCedula = true;
                        }
                        if($validarIngresoCedula == true && $objMetodos->validarIdentificacion($identificacion) == FALSE){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IDENTIFICACIÓN INGRESADA NO ES CORRECTA</div>';
                        }else if($validarIngresoCedula == true && count($objPersona->FiltrarPersonaPorIdentificacion($identificacion)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
                        }else if($idIglesia == NULL || $idIglesia == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA IGLESIA</div>';
                        }else if(empty ($primerNombre) || strlen($primerNombre) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL PRIMER NOMBRE MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($segundoNombre) || strlen($segundoNombre) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO NOMBRE MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($primerApellido) || strlen($primerApellido) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE PRIMER APELLIDO MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($segundoApellido) || strlen($segundoApellido) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO APELLIDO MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($numeroTelefono) || strlen($numeroTelefono) > 20){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NÚMERO DE TELÉFONO MÁXIMO 20 CARACTERES</div>';
                        }else if(empty ($fechaNacimiento)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO</div>';
                        }else if($idProvinciaEncriptado == NULL || $idProvinciaEncriptado == "" || $idProvinciaEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVINCIA</div>';
                        }else if($idCantonEncriptado == NULL || $idCantonEncriptado == "" || $idCantonEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN CANTÓN</div>';
                        }else if($idConfigurarParroquiaCantonEncriptado == NULL || $idConfigurarParroquiaCantonEncriptado == "" || $idConfigurarParroquiaCantonEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PARRÓQUIA</div>';
                        }else if(empty ($direccion) || strlen($direccion) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA DIRECCIÓN MÁXIMO 200 CARACTERES</div>';
                        }else if(empty ($referencia) || strlen($referencia) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA REFERENCIA MÁXIMO 200 CARACTERES</div>';
                        }else{
                            $idConfigurarParroquiaCanton = $objMetodos->desencriptar($idConfigurarParroquiaCantonEncriptado); 
                            $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCanton($idConfigurarParroquiaCanton);
                            if(count($listaConfigurarParroquiaCanton) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA DIRECCIÓN SELECCIONADA NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                            }else{
                                ini_set('date.timezone','America/Bogota'); 
                                $hoy = getdate();
                                $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                               $arrayPersona = array(
                                   'idIglesia' => $idIglesia,
                                    'identificacion' =>$identificacion,
                                    'primerNombre' => $primerNombre,
                                    'segundoNombre' => $segundoNombre,
                                    'primerApellido' => $primerApellido,
                                    'segundoApellido' => $segundoApellido,
                                    'fechaNacimiento'=>$fechaNacimiento,
                                    'fechaRegistro' => $fechaSubida,
                                    'estadoPersona' => 1);
                                $resultado =  $objPersona->IngresarPersona($arrayPersona);
                                if(count($resultado) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                }else{
                                    $idPersona = $resultado[0]['idPersona'];
                                    $arrayDireccionPersona = array(
                                        'idPersona' => $idPersona,
                                        'idConfigurarParroquiaCanton' => $idConfigurarParroquiaCanton,
                                        'direccionPersona' => $direccion,
                                        'referenciaDireccionPersona' => $referencia,
                                        'fechaIngresoDireccionPersona' => $fechaSubida,
                                        'estadoDireccionPersona' => 1
                                    );
                                    if(count($objDireccionPersona->IngresarDireccionPersona($arrayDireccionPersona)) == 0){
                                        $objPersona->EliminarPersona($idPersona);
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{
                                        $listaTelefono = $objTelefono->FiltrarTelefonoPorNumero($numeroTelefono);
                                        $idTelefono = 0;
                                        if(count($listaTelefono) > 0){
                                            $idTelefono = $listaTelefono[0]['numeroTelefono'];
                                        }else{
                                            $arrayTelefono = array(
                                              'numeroTelefono'=>$numeroTelefono,
                                                'estadoTelefono'=>1
                                            );
                                            $resultadoTelefono = $objTelefono->IngresarTelefono($arrayTelefono);
                                            if(count($resultadoTelefono) > 0){
                                                $idTelefono = $resultadoTelefono[0]['idTelefono'];
                                            }
                                        }
                                        if($idTelefono == 0){
                                            $objPersona->EliminarPersona($idPersona);
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                        }else{
                                            $arrayTelefonoPersona = array(
                                                'idPersona'=>$idPersona,
                                                'idTelefono'=>$idTelefono,
                                                'fechaRegistro'=>$fechaSubida,
                                                'estadoTelefonoPersona'=>1
                                            );
                                            $resultadoTelefonoPersona = $objTelefonoPersona->IngresarTelefonoPersona($arrayTelefonoPersona);
                                            if(count($resultadoTelefonoPersona) == 0){
                                                $objPersona->EliminarPersona($idPersona);
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                            }else{
                                                $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
                                                $validar = TRUE;
                                            }
                                        }

                                    }

                                }
                            }
                        }   
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function obtenerpersonasAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objPersona = new Persona($this->dbAdapter);
                    $objTelefono = new Telefonos($this->dbAdapter);
                    $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
                    $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                    $objMetodos = new Metodos();
                    ini_set('date.timezone','America/Bogota'); 
                    $listaPersonas = $objPersona->ObtenerPersonas();
                    $array1 = array();
                    $i = 0;
                    $j = count($listaPersonas);
                    foreach ($listaPersonas as $value) {
                        $listaTelefonoPersona = $objTelefonoPersona->FiltrarTelefonoPersonaPorPersonaEstado($value['idPersona'], 1);
                        $numeroTelefono = '';
                        if(count($listaTelefonoPersona) > 0){
                            $listaTelefono = $objTelefono->FiltrarTelefono($listaTelefonoPersona[0]['idTelefono']);
                            $numeroTelefono = $listaTelefono[0]['numeroTelefono'];
                        }
                        $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersonaPorPersonaEstado($value['idPersona'], 1);
                        $provincia = '';
                        $canton = '';
                        $parroquia = '';
                        $direccion = '';
                        $referencia = '';
                        if(count($listaDireccionPersona) > 0){
                            $provincia = $listaDireccionPersona[0]['nombreProvincia'];
                            $canton = $listaDireccionPersona[0]['nombreCanton'];
                            $parroquia = $listaDireccionPersona[0]['nombreParroquia'];
                            $direccion = $listaDireccionPersona[0]['direccionPersona'];
                            $referencia = $listaDireccionPersona[0]['referenciaDireccionPersona'];
                        }
                        $identificacion = $value['identificacion'];
                        $nombres = $value['primerNombre'].' '.$value['segundoNombre'];
                        $apellidos = $value['primerApellido'].' '.$value['segundoApellido'];
                        $fechaRegistro = $objMetodos->obtenerFechaEnLetra($value['fechaRegistro']);
                        $fechaNacimiento2 = new \DateTime($value['fechaNacimiento']);
                        $fechaActual = new \DateTime(date("d-m-Y"));
                        $diff = $fechaActual->diff($fechaNacimiento2);
                        $fechaNacimiento = $objMetodos->obtenerFechaEnLetraSinHora($value['fechaNacimiento']);
                         $botones = '';     
                        $array1[$i] = array(
                            '_j'=>$j,
                            'identificacion'=>$identificacion,
                            'nombres'=>$nombres,
                            'apellidos'=>$apellidos,
                            'fechaNacimiento'=>$fechaNacimiento,
                            'edad'=>$diff->y,
                            'numeroTelefono'=>$numeroTelefono,
                            'provincia'=>$provincia,
                            'canton'=>$canton,
                            'parroquia'=>$parroquia,
                            'direccion'=>$direccion,
                            'referencia'=>$referencia,
                            'fechaRegistro'=>$fechaRegistro,
                            'opciones'=>$botones,
                        );
                        $j--;
                        $i++;
                    }

                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$array1));
                }                    
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }

}