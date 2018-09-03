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
use Nel\Metodos\Metodos;
use Nel\Metodos\Correo;
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\NombreIglesia;
use Nel\Modelo\Entity\Iglesias;
use Nel\Modelo\Entity\Usuario;
use Nel\Modelo\Entity\TipoUsuario;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class InicioController extends AbstractActionController
{
    public $dbAdapter;
    public function inicioAction()
    {
        $this->layout("layout/login");
        $sesionUsuario = new Container('sesionparroquia');
        if($sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
        }else{
            $request=$this->getRequest();
            if(!$request->isPost()){
                $array = array(
                    'mensaje'=>'',
                    'nombreUsuario'=>'',
                    'contrasena'=>''
                );
                return new ViewModel($array);            
            }else{
                
                $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $objUsuario = new Usuario($this->dbAdapter);
                $objTipoUsuario = new TipoUsuario($this->dbAdapter);
                $objPersona = new Persona($this->dbAdapter);
                $objIglesia = new Iglesias($this->dbAdapter);
                $objNombreIglesia = new NombreIglesia($this->dbAdapter); 
                        
                $objMetodos = new Metodos();
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $nombreUsuario = trim($post['nombreUsuario']);
                $contrasena = trim($post['contrasena']);
                if(empty($nombreUsuario)){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DE USUARIO</div>';
                }else if($objMetodos->comprobarCadenaSoloLetras($nombreUsuario) == FALSE){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">SÓLO SE PERMITEN LETRAS Y NUMEROS Y LOS CARACTERES (_ Y .)</div>';
                }else if(empty ($contrasena)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA CONTRASEÑA</div>';
                }else{
                    $listaUsuario = $objUsuario->LoginUsuario($nombreUsuario);
                    if(count($listaUsuario) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UN USUARIO LLAMADO '.$nombreUsuario.'</div>';
                    }else if($listaUsuario[0]['contrasena'] != $contrasena){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA CONTRASEÑA INGRESADA NO ES CORRECTA</div>';
                    }else if($listaUsuario[0]['estadoUsuario'] == FALSE){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA CUENTA HA SIDO DESHABILITADA</div>';
                    }else{
                        $listaTipoUsuario = $objTipoUsuario->FiltrarTipoUsuario($listaUsuario[0]['idTipoUsuario']);
                        if(count($listaTipoUsuario) != 1){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">ERROR INTERNO DEL SISTEMA, REVISAR LOS TIPOS DE USUARIO</div>';
                        }else{
                            $sesionUsuario->offsetSet('idUsuario',$listaUsuario[0]['idUsuario']);
                            $sesionUsuario->offsetSet('nombreUsuario', $listaUsuario[0]['nombreUsuario']);
                            $sesionUsuario->offsetSet('contrasena', $listaUsuario[0]['contrasena']);
                            $listaPersona = $objPersona->FiltrarPersona($listaUsuario[0]['idPersona']);
                            $sesionUsuario->offsetSet('nombres', $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['primerApellido']);
                            $sesionUsuario->offsetSet('idIglesia',$listaPersona[0]['idIglesia']);
//                            $listaIglesia = $objIglesia->FiltrarIglesia($listaPersona[0]['idIglesia']);
                            $nombreIglesia = 'SGP';
                            $listaNombreIglesia = $objNombreIglesia->FiltrarNombreIglesiaEstado($listaPersona[0]['idIglesia'], 1);
                            if(count($listaNombreIglesia) == 1){
                                $nombreIglesia = $listaNombreIglesia[0]['nombreIglesia'];
                            }
                            $sesionUsuario->offsetSet('nombreIglesia',$nombreIglesia);
                            
                            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
                        }
                    }
                    
                }
            }
            $array = array(
                'mensaje'=>$mensaje,
                'nombreUsuario'=>$nombreUsuario,
                'contrasena'=>$contrasena
            );
        }
        return new ViewModel($array);
    }
    
    public function salirAction()
    {
        $sesionUsuario = new Container('sesionparroquia');
        if($sesionUsuario->offsetExists('idUsuario')){
            $sesionUsuario->offsetUnset('idUsuario');
            $sesionUsuario->offsetUnset('nombreUsuario');
            $sesionUsuario->offsetUnset('nombres');
            $sesionUsuario->offsetUnset('idIglesia');
            $sesionUsuario->offsetUnset('nombreIglesia');
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }else{
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }
    }
    
    
    
}