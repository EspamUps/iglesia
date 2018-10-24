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
use Nel\Modelo\Entity\Administrativos;
use Nel\Modelo\Entity\CargosAdministrativos;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class Administrativos2Controller extends AbstractActionController
{
    public $dbAdapter;
    
    public function filtraradministrativoporidentificacionAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓz UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 17);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 17, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objAdministrativos = new Administrativos($this->dbAdapter);
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
                            }else if($listaPersona[0]['estadoPersona'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                            } else{

                                $listaAdministrativo = $objAdministrativos->FiltrarAdministrativoPorPersona($listaPersona[0]['idPersona']);
                                if(count($listaAdministrativo) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.' YA TIENE UN CARGO ADMINISTRATIVO ASIGNADO</div>';
                                }else{
                                    $idPersonaEncriptado = $objMetodos->encriptar($listaPersona[0]['idPersona']);
                                    $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                                    $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
                                    $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarResponsable" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>ASIGNAR CARGO ADMINISTRATIVO</button>';
                                    
                                    $objCargosAdministrativos = new CargosAdministrativos($this->dbAdapter);
                                    
                                    $optionCargosAdministrativos = '<option value="0">SELECCIONE UN CARGO</option>';
                                    
                                    foreach ($objCargosAdministrativos->ObtenerCargosAdministrativos() as $valueCargos) {
                                        $listaAdministrativosPorCargo = $objAdministrativos->ObtenerAdministrativosPorCargoAdministrativo($valueCargos['idCargoAdministrativo']);
                                       
                                            if(count($listaAdministrativosPorCargo)==0)
                                            {                                                
                                                $idCargoAdministrativoEncriptado = $objMetodos->encriptar($valueCargos['idCargoAdministrativo']);
                                                $optionCargosAdministrativos = $optionCargosAdministrativos.'<option value="'.$idCargoAdministrativoEncriptado.'">'.$valueCargos['descripcion'].'</option>';
                                            }
                                        
                                    }
                                     
                                    $selectCargosAdmin = '<select class="form-control" id="selectCargosAdmin"  name="selectCargosAdmin">
                                            '.$optionCargosAdministrativos.'</select>';
                                    
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
                                                <th>CARGO ADMINISTRATIVO</th>
                                                <td>'.$selectCargosAdmin.'</td>
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
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function ingresaradministrativoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 17);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 17, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objAdministrativo = new Administrativos($this->dbAdapter);
                        $objCargosAdministrativos = new CargosAdministrativos($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idPersonaEncriptado = trim($post['idPersonaEncriptado']); 
                        $idCargoAdministrativoEncriptado = $post['selectCargosAdmin'];
                                                 

                        if(empty($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                       }else if(empty ($idCargoAdministrativoEncriptado) || ($idCargoAdministrativoEncriptado=="0") ){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">POR FAVOR, SELECCIONE UN CARGO ADMINISTRATIVO</div>';
                        }else 
                        { 
                            $idCargoAdministrativo = $objMetodos->desencriptar($idCargoAdministrativoEncriptado);    
                            if(count($objCargosAdministrativos->FiltrarCargoAdministrativo($idCargoAdministrativo))==0) 
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">CARGO ADMINISTRATIVO NO REGISTRADO EN LA BASE DE DATOS</div>';
                            else{                         
                                $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                                $listaPersona = $objPersona->FiltrarPersona($idPersona);
                                if(count($listaPersona) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA</div>';
                                }else if($listaPersona[0]['estadoPersona'] == FALSE){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                                }else
                                {  
                                    ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaRegistroA = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];

                                    $resultado = $objAdministrativo->IngresarAdministrativo($idPersona, $idCargoAdministrativo, $fechaRegistroA, 1);
                                    if(count($resultado) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA ASIGNACIÓN, POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{
                                        $mensaje = '<div class="alert alert-success text-center" role="alert">ASIGNACIÓN DE CARGO ADMINISTRATIVO INGRESADA CORRECTAMENTE</div>';
                                        $validar = TRUE;
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