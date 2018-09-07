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
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Misas;
use Nel\Modelo\Entity\DireccionPersona;
use Nel\Modelo\Entity\DireccionLugarMisa;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;

use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class DireccionPersonaController extends AbstractActionController
{
    public $dbAdapter;
   
    public function modificardireccionAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 5);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objControladores = new MetodosControladores();
                if($objControladores->ValidarPrivilegioAction($this->dbAdapter, $idUsuario, 1, 2)==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA MODIFICAR EN ESTE MÓDULO</div>';
                else {
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objDireccionPersona = new DireccionPersona($this->dbAdapter);
//                       $objMisas = new Misas($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idConfigurarParroquiaCantonEncriptado = $post['selectParroquiasM'];
                        $idDireccionPersonaEncriptado = $post['direccionPersonaEncriptado'];
                        $i = $post['numeroFila'];
                        $direccion = trim(strtoupper($post['direccionM']));
                        $referencia = trim(strtoupper($post['referenciaM']));
                        if($idConfigurarParroquiaCantonEncriptado == NULL || $idConfigurarParroquiaCantonEncriptado == "" || $idConfigurarParroquiaCantonEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA DIRECCIÓN</div>';
                        } else if($idDireccionPersonaEncriptado == NULL || $idDireccionPersonaEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PARROQUIA</div>';
                        }else if(empty ($direccion) || strlen($direccion) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA DIRECCIÓN MÁXIMO 200 CARACTERES</div>';
                        }else if(empty ($referencia) || strlen($referencia) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA REFERENCIA MÁXIMO 200 CARACTERES</div>';
                        }else if(!is_numeric($i)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                        }else{
                            $idDireccionPersona = $objMetodos->desencriptar($idDireccionPersonaEncriptado);
                            $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersona($idDireccionPersona);
                            if(count($listaDireccionPersona)==0)
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ERROR INTERNO DEL SISTEMA, ESTE USUARIO NO TIENE REGISTRADO UNA DIRECCIÓN</div>';
                            else 
                            {
                                $idPersonaEncriptado = $objMetodos->encriptar($listaDireccionPersona[0]['idPersona']);
                                 $idConfigurarParroquiaCanton = $objMetodos->desencriptar($idConfigurarParroquiaCantonEncriptado);
                                $validarDireccionDiferentes = true;
                                 if($listaDireccionPersona[0]['idConfigurarParroquiaCanton'] == $idConfigurarParroquiaCanton){
                                     if($listaDireccionPersona[0]['direccionPersona']==$direccion && $listaDireccionPersona[0]['referenciaDireccionPersona']==$referencia)
                                         $validarDireccionDiferentes=false;
                                 }
                                 
                                 if($validarDireccionDiferentes==false)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA DIRECCIÓN NO HA CAMBIADO</div>';
                                 else
                                 {
                                    $idPersona = $listaDireccionPersona[0]['idPersona'];
                                    $objDireccionPersona->ModificarDireccionPersonaEstado($idDireccionPersona, 0);
                                    ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                    if(count( $objDireccionPersona->IngresarDireccionPersona( $idPersona, $idConfigurarParroquiaCanton,$direccion,$referencia, $fechaSubida, 1)) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ</div>';
                                        $objDireccionPersona->ModificarDireccionPersonaEstado($idDireccionPersona, 1);
                                    }else{
                                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
                                        $validar = TRUE;
                                      return new JsonModel(array('idpersona'=>$idPersonaEncriptado,'i'=>$i,'mensaje'=>$mensaje,'validar'=>$validar));
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
}

