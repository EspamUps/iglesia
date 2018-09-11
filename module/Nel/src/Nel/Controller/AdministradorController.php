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
use Nel\Metodos\MetodosControladores;
use Nel\Metodos\Correo;
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\AsignarPrivilegio;
use Nel\Modelo\Entity\Misas;
use Nel\Modelo\Entity\Provincias;
use Nel\Modelo\Entity\LugaresMisa;
use Nel\Modelo\Entity\DireccionLugarMisa;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class AdministradorController extends AbstractActionController
{
    public $dbAdapter;
    
    public function configurarmisasAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array();
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 6);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 6, 3);
                
                $objMisas = new Misas($this->dbAdapter);
                $objPersonas = new Persona($this->dbAdapter);
                $objLugaresMisa = new LugaresMisa($this->dbAdapter);
                $objDireccionLugarMisa = new DireccionLugarMisa($this->dbAdapter);
                $objMetodos = new Metodos();
                $listaMisas = $objMisas->ObtenerMisas();
                $optionSelectMisas = '<option value="0">SELECCIONE UNA MISA</option>';
                foreach ($listaMisas as $valueMisas) {
                    $idMisaEncriptado = $objMetodos->encriptar($valueMisas['idMisa']);
                    $optionSelectMisas = $optionSelectMisas.'<option value="'.$idMisaEncriptado.'">'.$valueMisas['descripcionMisa'].'</option>';
                }
                        
                $listaLugaresMisa = $objLugaresMisa->ObtenerObtenerLugaresMisa();
                $optionSelectLugaresMisa = '<option value="0">SELECCIONE UN LUGAR</option>';
                foreach ($listaLugaresMisa as $valueLugarMisa) {
                    $idLugarMisaEncriptado = $objMetodos->encriptar($valueLugarMisa['idLugarMisa']);
                    $optionSelectLugaresMisa = $optionSelectLugaresMisa.'<option value="'.$idLugarMisaEncriptado.'">'.$valueLugarMisa['nombreLugar'].'</option>';
                }
                 $array = array(
                    'optionSelectMisas'=>$optionSelectMisas,
                     'optionSelectLugaresMisa'=>$optionSelectLugaresMisa,
                     'validacionPrivilegio' =>  $validarprivilegio

                );
            }
        }
        return new ViewModel($array);
    }
    public function misasAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array();
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }
        else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 5);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                 $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 5, 3);
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
            
        }
        return new ViewModel($array);
    }
    
    public function sacerdotesAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array();
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }
        else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 2);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                 $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 2, 3);
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
            
        }
        return new ViewModel($array);
    }
    
    public function lugaresmisaAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array(
            'optionSelectProvincias'=>''
        );
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 4);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else {                
                $objProvincias = new Provincias($this->dbAdapter);
                $objMetodos = new Metodos();
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 4, 3);

                $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
                $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                foreach ($listaProvincias as $valueProvincias) {
                    $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                    $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                }
                $array = array(
                    'optionSelectProvincias'=>$optionSelectProvincias,
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
        }
        return new ViewModel($array);
    }
    
    public function direccionesAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array();
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }
        else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 3);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            
        }     
        return new ViewModel($array);
    }
    
    public function personasAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array(
            'optionSelectProvincias'=>''
        );
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }else{            
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else {       
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 1, 3);
                $objProvincias = new Provincias($this->dbAdapter);
                $objMetodos = new Metodos();

                $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
                $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                foreach ($listaProvincias as $valueProvincias) {
                    $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                    $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                }
                $array = array(
                    'optionSelectProvincias'=>$optionSelectProvincias,
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
        }
        return new ViewModel($array);
    }
      
    public function inicioAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array();
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }else{
            $array = array();
        }
        return new ViewModel($array);
    }
    
    public function usuariosAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array();
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 7);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else {       
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 7, 3);
//                $objProvincias = new Provincias($this->dbAdapter);
//                $objMetodos = new Metodos();
//
//                $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
//                $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
//                foreach ($listaProvincias as $valueProvincias) {
//                    $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
//                    $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
//                }
                $array = array(
//                    'optionSelectProvincias'=>$optionSelectProvincias,
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
        }
        return new ViewModel($array);
    }
}