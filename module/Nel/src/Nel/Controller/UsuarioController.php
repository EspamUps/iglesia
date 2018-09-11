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
use Nel\Modelo\Entity\Usuario;
use Nel\Modelo\Entity\Telefonos;
use Nel\Modelo\Entity\TelefonoPersona;
use Nel\Modelo\Entity\HistorialPersona;
use Nel\Modelo\Entity\DireccionPersona;
use Nel\Modelo\Entity\Parroquias;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Nel\Modelo\Entity\ConfigurarCantonProvincia;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class UsuarioController extends AbstractActionController
{
    public $dbAdapter;
  

    public function obtenerusuariosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 7);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objUsuario = new Usuario($this->dbAdapter);
                    $listaUsuarios = $objUsuario->ObtenerUsuarios();
                    $tabla = $this->CargarTablaUsuarioAction($idUsuario,$this->dbAdapter,$listaUsuarios, 0, count($listaUsuarios));
                    
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }                    
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    function CargarTablaUsuarioAction($idUsuario,$adaptador,$listaUsuarios, $i, $j)
    {
        $objMetodos = new Metodos();
        $objMetodosControlador = new MetodosControladores();
        ini_set('date.timezone','America/Bogota'); 
        $array1 = array();
        foreach ($listaUsuarios as $value) {
            $idUsuarioEncriptado = $objMetodos->encriptar($value['idUsuario']);
            
            $botonEliminarUsuario = '';
            $botonModificarUsuario ='';
            
            if($objMetodosControlador->ValidarPrivilegioAction($adaptador, $idUsuario, 7, 1) == true){
                $botonEliminarUsuario = '<button id="btnDeshabilitarUsuario'.$i.'" title="DESHABILITAR A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="DeshabilitarUsuario(\''.$idUsuarioEncriptado.'\','.$i.')" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i></button>';
            }
            
            if($objMetodosControlador->ValidarPrivilegioAction($adaptador, $idUsuario, 7, 2) == true)
                $botonModificarUsuario = '<button data-target="#modalModificar" data-toggle="modal" id="btnModificarUsuario'.$i.'" title="MODIFICAR A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioModificarUsuario(\''.$idUsuarioEncriptado.'\','.$i.','.$j.')" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-pencil"></i></button>';
            
            $botonGestionModulos = '<button data-target="#modalGestionModulos" data-toggle="modal" id="btnGestionModulos'.$i.'" title="ASIGNAR MODULOS A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioGestionModulos(\''.$idUsuarioEncriptado.'\','.$i.','.$j.')" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-cog"></i></button>';
            $botonGestionPrivilegios = '<button data-target="#modalGestionPrivilegios" data-toggle="modal" id="btnGestionPrivilegios'.$i.'" title="ASIGNAR PRIVILEGIOS A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioGestionPrivilegios(\''.$idUsuarioEncriptado.'\','.$i.','.$j.')" class="btn btn-default btn-sm btn-flat"><i class="fa fa-cogs"></i></button>';

            
            $identificacion = $value['identificacion'];
            $nombres = $value['primerNombre'].' '.$value['segundoNombre'];
            $apellidos = $value['primerApellido'].' '.$value['segundoApellido'];
            $usuario = $value['nombreUsuario'];
            $tipoUsuario = $value['descripcionTipoUsuario'];
            
            $botones = $botonEliminarUsuario .' '.$botonModificarUsuario;  
            $botones2 = $botonGestionModulos.' '.$botonGestionPrivilegios;  

             
            $array1[$i] = array(
                '_j'=>$j,
                '_idUsuarioEncriptado'=>$idUsuarioEncriptado,                
                'identificacion'=>$identificacion,
                'nombres'=>$nombres,
                'apellidos'=>$apellidos,
                'tipousuario'=>$tipoUsuario,
                'usuario'=>$usuario,
                'opciones1'=>$botones,
                'opciones2'=>$botones2
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    

}