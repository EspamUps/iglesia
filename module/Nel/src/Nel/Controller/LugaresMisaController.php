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
use Nel\Modelo\Entity\LugaresMisa;
use Nel\Modelo\Entity\Telefonos;
use Nel\Modelo\Entity\TelefonoPersona;
use Nel\Modelo\Entity\DireccionLugarMisa;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;

use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class LugaresMisaController extends AbstractActionController
{
    public $dbAdapter;
    
    public function obtenerlugaresmisaAction()
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
                $objLugaresMisa = new LugaresMisa($this->dbAdapter);
                $objDireccionLugaresMisa = new DireccionLugarMisa($this->dbAdapter);
                $objMetodos = new Metodos();
                ini_set('date.timezone','America/Bogota'); 
                $listaLugaresMisa = $objLugaresMisa->ObtenerObtenerLugaresMisa();
                $array1 = array();
                $i = 0;
                $j = count($listaLugaresMisa);
                foreach ($listaLugaresMisa as $value) {
                    
                    $listaDireccionLugaresMisa = $objDireccionLugaresMisa->FiltrarDireccionLugarMisaPorLugarEstado($value['idLugarMisa'], 1);
                    $provincia = '';
                    $canton = '';
                    $parroquia = '';
                    $direccion = '';
                    $referencia = '';
                    if(count($listaDireccionLugaresMisa) > 0){
                        $provincia = $listaDireccionLugaresMisa[0]['nombreProvincia'];
                        $canton = $listaDireccionLugaresMisa[0]['nombreCanton'];
                        $parroquia = $listaDireccionLugaresMisa[0]['nombreParroquia'];
                        $direccion = $listaDireccionLugaresMisa[0]['direccionLugarMisa'];
                        $referencia = $listaDireccionLugaresMisa[0]['referenciaLugarMisa'];
                    }
                    $nombreLugar = $value['nombreLugar'];
                    $fechaRegistro = $objMetodos->obtenerFechaEnLetra($value['fechaIngresoLugarMisa']);
                    $botones = '';     
                    $array1[$i] = array(
                        '_j'=>$j,
                        'nombreLugar'=>$nombreLugar,
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
    
    
    public function ingresarlugaresmisaAction()
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
                $objLugaresMisa = new LugaresMisa($this->dbAdapter);
                $objDireccionLugarMisa = new DireccionLugarMisa($this->dbAdapter);
                $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $idProvinciaEncriptado = $post['selectProvincias'];
                $idCantonEncriptado = $post['selectCantones'];
                $idConfigurarParroquiaCantonEncriptado = $post['selectParroquias'];
                $direccion = trim(strtoupper($post['direccion']));
                $referencia = trim(strtoupper($post['referencia']));
                $nombreLugar = trim(strtoupper($post['nombreLugar']));
                 if($idProvinciaEncriptado == NULL || $idProvinciaEncriptado == "" || $idProvinciaEncriptado == "0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVINCIA</div>';
                }else if($idCantonEncriptado == NULL || $idCantonEncriptado == "" || $idCantonEncriptado == "0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN CANTÓN</div>';
                }else if($idConfigurarParroquiaCantonEncriptado == NULL || $idConfigurarParroquiaCantonEncriptado == "" || $idConfigurarParroquiaCantonEncriptado == "0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PARRÓQUIA</div>';
                }else if(empty ($direccion) || strlen($direccion) > 200){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA DIRECCIÓN MÁXIMO 200 CARACTERES</div>';
                }else if(empty ($referencia) || strlen($referencia) > 200){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA REFERENCIA MÁXIMO 200 CARACTERES</div>';
                }else if(empty ($nombreLugar) || strlen($nombreLugar) > 300){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DEL LUGAR 200 CARACTERES</div>';
                }else{
                    $idConfigurarParroquiaCanton = $objMetodos->desencriptar($idConfigurarParroquiaCantonEncriptado); 
                    $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCanton($idConfigurarParroquiaCanton);
                    if(count($listaConfigurarParroquiaCanton) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA DIRECCIÓN SELECCIONADA NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                    }else{
                        ini_set('date.timezone','America/Bogota'); 
                        $hoy = getdate();
                        $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                        $resultado =  $objLugaresMisa->IngresarLugaresMisa($nombreLugar, $fechaSubida, 1);
                        if(count($resultado) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL LUGAR POR FAVOR INTENTE MÁS TARDE</div>';
                        }else{
                            $idLugarMisa = $resultado[0]['idLugarMisa'];
                            
                            if(count($objDireccionLugarMisa->IngresarDireccionLugarMisa($idLugarMisa, $idConfigurarParroquiaCanton, $direccion, $referencia, $fechaSubida, 1)) == 0){
                                $objLugaresMisa->EliminarLugarMisa($idLugarMisa);
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL LUGAR POR FAVOR INTENTE MÁS TARDE</div>';
                            }else{
                               
                                $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
                                $validar = TRUE;
                            }
                            
                        }
                    }
                }
                
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
}

