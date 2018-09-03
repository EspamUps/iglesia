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
use Nel\Metodos\Correo;
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\Telefonos;
use Nel\Modelo\Entity\TelefonoPersona;
use Nel\Modelo\Entity\DireccionPersona;
use Nel\Modelo\Entity\Sacerdotes;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class SacerdoteController extends AbstractActionController
{
    public $dbAdapter;
//    public function ingresarpersonaAction()
//    {
//        $this->layout("layout/administrador");
//        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
//        $validar = false;
//        $sesionUsuario = new Container('sesionparroquia');
//        if(!$sesionUsuario->offsetExists('idUsuario')){
//            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
//        }else{
//            $request=$this->getRequest();
//            if(!$request->isPost()){
//                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
//            }else{
//                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
//                $objMetodos = new Metodos();
//                $objPersona = new Persona($this->dbAdapter);
//                $objDireccionPersona = new DireccionPersona($this->dbAdapter);
//                $objTelefono = new Telefonos($this->dbAdapter);
//                $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
//                $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
//                $post = array_merge_recursive(
//                    $request->getPost()->toArray(),
//                    $request->getFiles()->toArray()
//                );
//                
//                $idIglesia = $sesionUsuario->offsetGet('idIglesia');
//                $identificacion = trim($post['identificacion']);
//                $primerNombre = trim(strtoupper($post['primerNombre']));
//                $segundoNombre = trim(strtoupper($post['segundoNombre']));
//                $primerApellido = trim(strtoupper($post['primerApellido']));
//                $segundoApellido = trim(strtoupper($post['segundoApellido']));
//                $numeroTelefono = trim($post['telefono']);
//                $idProvinciaEncriptado = $post['selectProvincias'];
//                $idCantonEncriptado = $post['selectCantones'];
//                $idConfigurarParroquiaCantonEncriptado = $post['selectParroquias'];
//                $direccion = trim(strtoupper($post['direccion']));
//                $referencia = trim(strtoupper($post['referencia']));
//                $fechaNacimiento = $post['fechaNacimiento'];
//                $validarIngresoCedula = FALSE;
//                if(strlen($identificacion) > 0){
//                    $validarIngresoCedula = true;
//                }
//                if($validarIngresoCedula == true && $objMetodos->validarIdentificacion($identificacion) == FALSE){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IDENTIFICACIÓN INGRESADA NO ES CORRECTA</div>';
//                }else if($validarIngresoCedula == true && count($objPersona->FiltrarPersonaPorIdentificacion($identificacion)) > 0){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
//                }else if($idIglesia == NULL || $idIglesia == ""){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA IGLESIA</div>';
//                }else if(empty ($primerNombre) || strlen($primerNombre) > 50){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL PRIMER NOMBRE MÁXIMO 50 CARACTERES</div>';
//                }else if(empty ($segundoNombre) || strlen($segundoNombre) > 50){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO NOMBRE MÁXIMO 50 CARACTERES</div>';
//                }else if(empty ($primerApellido) || strlen($primerApellido) > 50){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE PRIMER APELLIDO MÁXIMO 50 CARACTERES</div>';
//                }else if(empty ($segundoApellido) || strlen($segundoApellido) > 50){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO APELLIDO MÁXIMO 50 CARACTERES</div>';
//                }else if(empty ($numeroTelefono) || strlen($numeroTelefono) > 20){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NÚMERO DE TELÉFONO MÁXIMO 20 CARACTERES</div>';
//                }else if(empty ($fechaNacimiento)){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO</div>';
//                }else if($idProvinciaEncriptado == NULL || $idProvinciaEncriptado == "" || $idProvinciaEncriptado == "0"){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVINCIA</div>';
//                }else if($idCantonEncriptado == NULL || $idCantonEncriptado == "" || $idCantonEncriptado == "0"){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN CANTÓN</div>';
//                }else if($idConfigurarParroquiaCantonEncriptado == NULL || $idConfigurarParroquiaCantonEncriptado == "" || $idConfigurarParroquiaCantonEncriptado == "0"){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PARRÓQUIA</div>';
//                }else if(empty ($direccion) || strlen($direccion) > 200){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA DIRECCIÓN MÁXIMO 200 CARACTERES</div>';
//                }else if(empty ($referencia) || strlen($referencia) > 200){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA REFERENCIA MÁXIMO 200 CARACTERES</div>';
//                }else{
//                    $idConfigurarParroquiaCanton = $objMetodos->desencriptar($idConfigurarParroquiaCantonEncriptado); 
//                    $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCanton($idConfigurarParroquiaCanton);
//                    if(count($listaConfigurarParroquiaCanton) == 0){
//                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA DIRECCIÓN SELECCIONADA NO EXISTE EN NUESTRA BASE DE DATOS</div>';
//                    }else{
//                        ini_set('date.timezone','America/Bogota'); 
//                        $hoy = getdate();
//                        $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
//                       $arrayPersona = array(
//                           'idIglesia' => $idIglesia,
//                            'identificacion' =>$identificacion,
//                            'primerNombre' => $primerNombre,
//                            'segundoNombre' => $segundoNombre,
//                            'primerApellido' => $primerApellido,
//                            'segundoApellido' => $segundoApellido,
//                            'fechaNacimiento'=>$fechaNacimiento,
//                            'fechaRegistro' => $fechaSubida,
//                            'estadoPersona' => 1);
//                        $resultado =  $objPersona->IngresarPersona($arrayPersona);
//                        if(count($resultado) == 0){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
//                        }else{
//                            $idPersona = $resultado[0]['idPersona'];
//                            $arrayDireccionPersona = array(
//                                'idPersona' => $idPersona,
//                                'idConfigurarParroquiaCanton' => $idConfigurarParroquiaCanton,
//                                'direccionPersona' => $direccion,
//                                'referenciaDireccionPersona' => $referencia,
//                                'fechaIngresoDireccionPersona' => $fechaSubida,
//                                'estadoDireccionPersona' => 1
//                            );
//                            if(count($objDireccionPersona->IngresarDireccionPersona($arrayDireccionPersona)) == 0){
//                                $objPersona->EliminarPersona($idPersona);
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
//                            }else{
//                                $listaTelefono = $objTelefono->FiltrarTelefonoPorNumero($numeroTelefono);
//                                $idTelefono = 0;
//                                if(count($listaTelefono) > 0){
//                                    $idTelefono = $listaTelefono[0]['numeroTelefono'];
//                                }else{
//                                    $arrayTelefono = array(
//                                      'numeroTelefono'=>$numeroTelefono,
//                                        'estadoTelefono'=>1
//                                    );
//                                    $resultadoTelefono = $objTelefono->IngresarTelefono($arrayTelefono);
//                                    if(count($resultadoTelefono) > 0){
//                                        $idTelefono = $resultadoTelefono[0]['idTelefono'];
//                                    }
//                                }
//                                if($idTelefono == 0){
//                                    $objPersona->EliminarPersona($idPersona);
//                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
//                                }else{
//                                    $arrayTelefonoPersona = array(
//                                        'idPersona'=>$idPersona,
//                                        'idTelefono'=>$idTelefono,
//                                        'fechaRegistro'=>$fechaSubida,
//                                        'estadoTelefonoPersona'=>1
//                                    );
//                                    $resultadoTelefonoPersona = $objTelefonoPersona->IngresarTelefonoPersona($arrayTelefonoPersona);
//                                    if(count($resultadoTelefonoPersona) == 0){
//                                        $objPersona->EliminarPersona($idPersona);
//                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
//                                    }else{
//                                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
//                                        $validar = TRUE;
//                                    }
//                                }
//
//                            }
//                            
//                        }
//                    }
//                }
//                
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }
//    
//  
    
    public function ingresarsacerdoteAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $request=$this->getRequest();
            if(!$request->isPost()){
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
            }else{
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $objMetodos = new Metodos();
                $objPersona = new Persona($this->dbAdapter);
                $objSacerdotes = new Sacerdotes($this->dbAdapter);
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                 $idPersonaEncriptado = trim($post['idPersonaEncriptado']);
                
                if(empty($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                }else{
                    $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                    $listaPersona = $objPersona->FiltrarPersona($idPersona);
                    if(count($listaPersona) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA</div>';
                    }else{ 
                        $listaSacerdote = $objSacerdotes->FiltrarSacerdotePorPersona($listaPersona[0]['idPersona']);
                        if(count($listaSacerdote) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN SACERDOTE CON LA IDENTIFICACIÓN '.$listaPersona[0]['identificacion'].'</div>';
                        }else{
                            ini_set('date.timezone','America/Bogota'); 
                            $hoy = getdate();
                            $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                            $resultado = $objSacerdotes->IngresarSacerdote($idPersona, $fechaSubida, 1);
                            if(count($resultado) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL SACERDOTE POR FAVOR INTENTE MÁS TARDE</div>';
                            }else{
                                $mensaje = '';
                                $validar = TRUE;
                            }
                            
                            
                        }
                    }
                }
                
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    public function filtrarsacerdoteporidentificacionAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $request=$this->getRequest();
            if(!$request->isPost()){
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
            }else{
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $objMetodos = new Metodos();
                $objPersona = new Persona($this->dbAdapter);
                $objSacerdotes = new Sacerdotes($this->dbAdapter);
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                 $identificacion = trim($post['identificacion']);
                
                if(strlen($identificacion) > 10){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IDENTIFICACIÓN NO DEBE TENER MÁS DE 10 DÍGITOS</div>';
                }else{
                    $listaPersona = $objPersona->FiltrarPersonaPorIdentificacion($identificacion);
                    if(count($listaPersona) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
                    }else{ 
                        
                        $listaSacerdote = $objSacerdotes->FiltrarSacerdotePorPersona($listaPersona[0]['idPersona']);
                        if(count($listaSacerdote) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN SACERDOTE CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
                        }else{
                            $idPersonaEncriptado = $objMetodos->encriptar($listaPersona[0]['idPersona']);
                            $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                            $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
                            $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarSacerdote" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
                            $tabla = '<input type="hidden" id="idPersonaEncriptado" name="idPersonaEncriptado" value="'.$idPersonaEncriptado.'">
                                <div class="table-responsive"><table class="table">
                                <thead> 
                                    <tr>
                                        <th>NOMBRES</th>
                                        <td>'.$nombres.'</td>
                                    </tr>
                                    <tr>
                                        <th>APELLIDOS</th>
                                        <td>'.$apellidos.'</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">'.$botonGuardar.'</td>
                                    </tr>
                                </thead>
                            </table></div>';
                            $mensaje = '';
                            $validar = TRUE;
                            return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                        }
                    }
                }
                
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    public function obtenersacerdotesAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $request=$this->getRequest();
            if(!$request->isPost()){
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
            }else{
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $objPersona = new Persona($this->dbAdapter);
                $objSacerdotes = new Sacerdotes($this->dbAdapter);
                $objTelefono = new Telefonos($this->dbAdapter);
                $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
                $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                $objMetodos = new Metodos();
                ini_set('date.timezone','America/Bogota'); 
                $listaSacerdotes = $objSacerdotes->ObtenerSacerdotes();
                $array1 = array();
                $i = 0;
                $j = count($listaSacerdotes);
                foreach ($listaSacerdotes as $value) {
                    $listaPersona = $objPersona->FiltrarPersona($value['idPersona']);
                    $listaTelefonoPersona = $objTelefonoPersona->FiltrarTelefonoPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
                    $numeroTelefono = '';
                    if(count($listaTelefonoPersona) > 0){
                        $listaTelefono = $objTelefono->FiltrarTelefono($listaTelefonoPersona[0]['idTelefono']);
                        $numeroTelefono = $listaTelefono[0]['numeroTelefono'];
                    }
                        
                    $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
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
                    $identificacion = $listaPersona[0]['identificacion'];
                    $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                    $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
                    $fechaRegistro = $objMetodos->obtenerFechaEnLetra($value['fechaIngresoSacerdote']);
                        
                    $fechaNacimiento2 = new \DateTime($listaPersona[0]['fechaNacimiento']);
                    $fechaActual = new \DateTime(date("d-m-Y"));
                    $diff = $fechaActual->diff($fechaNacimiento2);
                    $fechaNacimiento = $objMetodos->obtenerFechaEnLetraSinHora($listaPersona[0]['fechaNacimiento']);
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
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }

}