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
use Nel\Modelo\Entity\Periodos;
use Nel\Modelo\Entity\Docentes;
use Nel\Modelo\Entity\Cursos;
use Nel\Modelo\Entity\Misas;
use Nel\Modelo\Entity\Provincias;
use Nel\Modelo\Entity\LugaresMisa;
use Nel\Modelo\Entity\DireccionLugarMisa;
use Nel\Modelo\Entity\RangoAsistencia;
use Nel\Modelo\Entity\Sexo;
use Nel\Modelo\Entity\Sacerdotes;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class AdministradorController extends AbstractActionController
{
    public $dbAdapter;
    
    public function bautismoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 15);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                
                $objMetodosC = new MetodosControladores();
         
               
                
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 15, 3);
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio,
                );
            }
            
        }
        return new ViewModel($array);
    }
    
    public function  configurarcursoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 13);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 13, 3);
                
                
//                $objMisas = new Misas($this->dbAdapter);
                $objCursos = new Cursos($this->dbAdapter);
                $objPeriodos = new Periodos($this->dbAdapter);
                $objDocentes = new Docentes($this->dbAdapter);
                $objRangoAsistencia = new RangoAsistencia($this->dbAdapter);
//                $objLugaresMisa = new LugaresMisa($this->dbAdapter);
//                $objDireccionLugarMisa = new DireccionLugarMisa($this->dbAdapter);
                $objMetodos = new Metodos();
                $listaPeriodo = $objPeriodos->ObtenerPeriodosEstado(1);
                $optionSelectPeriodos = '<option value="0">SELECCIONE UN PERIODO</option>';
                foreach ($listaPeriodo as $valuePeriodo) {
                    $idPeriodoEncriptado = $objMetodos->encriptar($valuePeriodo['idPeriodo']);
                    $optionSelectPeriodos = $optionSelectPeriodos.'<option value="'.$idPeriodoEncriptado.'">'.$valuePeriodo['nombrePeriodo'].'</option>';
                }
                        
                $listaCursos = $objCursos->ObtenerCursosEstado(1);
                $optionSelectCurso = '<option value="0">SELECCIONE UN CURSO</option>';
                foreach ($listaCursos as $valueCursos) {
                    $idCursoMisaEncriptado = $objMetodos->encriptar($valueCursos['idCurso']);
                    $optionSelectCurso = $optionSelectCurso.'<option value="'.$idCursoMisaEncriptado.'">'.$valueCursos['nombreCurso'].'</option>';
                }
                
                $listaDocentes = $objDocentes->ObtenerDocentesEstado(1);
                $optionSelectDocentes = '<option value="0">SELECCIONE UN DOCENTE</option>';
                foreach ($listaDocentes as $valueDocentes) {
                    $idDocenteEncriptado = $objMetodos->encriptar($valueDocentes['idDocente']);
                    $nombres = $valueDocentes['primerApellido'].' '.$valueDocentes['segundoApellido'].' '.$valueDocentes['primerNombre'].' '.$valueDocentes['segundoNombre'];
                    $optionSelectDocentes = $optionSelectDocentes.'<option value="'.$idDocenteEncriptado.'">'.$nombres.'</option>';
                }
                
                $validarRangoAsistencia = TRUE;
                $mensaje = '';
                if(count($objRangoAsistencia->ObtenerRangoAsistenciaAcivo()) != 1){
                    $validarRangoAsistencia = FALSE;
                }
                
                 $array = array(
                     'validarRangoAsistencia'=>$validarRangoAsistencia,
                     'optionSelectPeriodos'=>$optionSelectPeriodos,
                     'optionSelectCurso'=>$optionSelectCurso,
                     'optionSelectDocentes'=>$optionSelectDocentes,
                     'validacionPrivilegio' =>  $validarprivilegio

                );
            }
        }
        return new ViewModel($array);
    }
    
    
    public function rangoasistenciaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 12);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                 $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 12, 3);
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
            
        }
        return new ViewModel($array);
    }
    
    public function  horariosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 11);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 11, 3);
                
                $objCursos = new Cursos($this->dbAdapter);
               $objMetodos = new Metodos();

                        
                $listaCursos = $objCursos->ObtenerCursosEstado(1);
                $optionSelectCurso = '<option value="0">SELECCIONE UN CURSO</option>';
                foreach ($listaCursos as $valueCursos) {
                    $idCursoMisaEncriptado = $objMetodos->encriptar($valueCursos['idCurso']);
                    $optionSelectCurso = $optionSelectCurso.'<option value="'.$idCursoMisaEncriptado.'">'.$valueCursos['nombreCurso'].'</option>';
                }
                

                
                 $array = array(
                     'optionSelectCurso'=>$optionSelectCurso,
                     'validacionPrivilegio' =>  $validarprivilegio
                );
            }
        }
        return new ViewModel($array);
    }
    
    
     public function docentesAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 10);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                 $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 10, 3);
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
            
        }
        return new ViewModel($array);
    }
    
    public function cursosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 9);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                 $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 9, 3);
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
            
        }
        return new ViewModel($array);
    }
    
    
    public function periodosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 8);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else{
                 $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 8, 3);
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
            
        }
        return new ViewModel($array);
    }
    
    
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
                $objSexo = new Sexo($this->dbAdapter);
                $objMetodos = new Metodos();

                $listaSexo = $objSexo->ObtenerSexoActivo();
                $optionSelectSexo = '<option value="0">SELECCIONE UN SEXO</option>'; 
                foreach ($listaSexo as $valueSexo) {
                    $idSexoEncriptado = $objMetodos->encriptar($valueSexo['idSexo']);
                    $optionSelectSexo = $optionSelectSexo.'<option value="'.$idSexoEncriptado.'">'.$valueSexo['descripcionSexo'].'</option>';
                }
                
                
                $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
                $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                foreach ($listaProvincias as $valueProvincias) {
                    $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                    $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                }
                $array = array(
                    'optionSelectProvincias'=>$optionSelectProvincias,
                    'validacionPrivilegio' =>  $validarprivilegio,
                    'optionSelectSexo'=>$optionSelectSexo
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

                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio
                );
            }
        }
        return new ViewModel($array);
    }
    
    public function matriculasAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array();
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objPerido = new Periodos($this->dbAdapter);
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $objMetodos = new Metodos();
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else {       
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 3);
                $listaPeriodo = $objPerido->ObtenerPeriodosEstado(1);
                $optionPeriodo = '<option value="0">SELECCIONE UN PERIODO</option>';
                foreach ($listaPeriodo as $valueP) {
                    $idPeriodoEncriptado = $objMetodos->encriptar($valueP['idPeriodo']);
                    $optionPeriodo = $optionPeriodo.'<option value="'.$idPeriodoEncriptado.'">'.$valueP['nombrePeriodo'].'</option>';
                }
                
                
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio,
                    'optionPeriodo' => $optionPeriodo
                );
            }
        }
        return new ViewModel($array);
    }
    
    public function asistenciasAction()
    {
        $this->layout("layout/administrador");
        $sesionUsuario = new Container('sesionparroquia');
        $array = array();
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
        }else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objPerido = new Periodos($this->dbAdapter);
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $objMetodos = new Metodos();
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 16);
            if (count($AsignarModulo)==0)
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrador/inicio');
            else {       
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 3);
                $listaPeriodo = $objPerido->ObtenerPeriodosEstado(1);
                $optionPeriodos = '<option value="0">SELECCIONE UN PERIODO</option>';
                foreach ($listaPeriodo as $valueP) {
                    $idPeriodoEncriptado = $objMetodos->encriptar($valueP['idPeriodo']);
                    $optionPeriodos = $optionPeriodos.'<option value="'.$idPeriodoEncriptado.'">'.$valueP['nombrePeriodo'].'</option>';
                }
                
                
                $array = array(
                    'validacionPrivilegio' =>  $validarprivilegio,
                    'optionPeriodos' => $optionPeriodos
                );
            }
        }
        return new ViewModel($array);
    }
}