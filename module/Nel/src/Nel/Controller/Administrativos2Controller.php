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

//                                $listaAdministrativo = $objAdministrativos->FiltrarAdministrativoPorPersona($listaPersona[0]['idPersona']);
                               
//                                $ingresar=false;
//                                if(count($listaAdministrativo) > 0){
//                                    if($listaAdministrativo[0]['estadoAdministrativo']==TRUE)
//                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.' YA TIENE UN CARGO ADMINISTRATIVO ASIGNADO</div>';                            
//                                    else
//                                        $ingresar=true;
//                                }
//                                
//                                if(count($listaAdministrativo) ==0 || $ingresar==TRUE)
//                                {
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
                                        <div class="table-responsive">
                                        <table class="table">
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
                               
                                    $objAdministrativoExistente= $objAdministrativo->FiltrarAdministrativoPorPersona($idPersona);
                                    $ingresar=false;
                                    if(count($objAdministrativoExistente)>0)
                                    {
                                        if($objAdministrativoExistente[0]['idCargoAdministrativo']==$idCargoAdministrativo)
                                            $resultado= $objAdministrativo->ModificarEstadoAdministrativo($objAdministrativoExistente[0]['idAdministrativo'], 1);
                                        else
                                            $ingresar=true;
                                    }
                                       
                                    if(count($objAdministrativoExistente)==0 || $ingresar==TRUE)                                    
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
    
    
    public function obteneradministrativosAction()
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
                    $objAdministrativos = new Administrativos($this->dbAdapter);
                    $listaAdministrativos = $objAdministrativos->ObtenerAdministrativos();
                    $tabla = $this->CargarTablaAdministrativosAction($listaAdministrativos);
                    
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }                    
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    
    
    function CargarTablaAdministrativosAction($listaAdministrativos)
    {
        $objPersona =new Persona($this->dbAdapter);
        $objMetodos = new Metodos($this->dbAdapter);
        $objCargoAdmin = new CargosAdministrativos($this->dbAdapter);
        $cuerpoTabla="";
        $contador=1;
        foreach ($listaAdministrativos as $valueAdmin)
        {
            $idAdministrativoEncriptado = $objMetodos->encriptar($valueAdmin['idAdministrativo']);
            $persona = $objPersona->FiltrarPersona($valueAdmin['idPersona']);
            $cargo = $objCargoAdmin->FiltrarCargoAdministrativo($valueAdmin['idCargoAdministrativo']);
            
            $botonEliminarCargo= '<button id="btnEliminarAdministrativo'.$contador.'" title="ELIMINAR CARGO ASIGNADO A '.$persona[0]['primerNombre'].' '.$persona[0]['segundoNombre'].' '.$persona[0]['primerApellido'].' '.$persona[0]['segundoApellido'].'" onclick="EliminarCargoAsignado(\''.$idAdministrativoEncriptado.'\','.$contador.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
            
            $cuerpoTabla = $cuerpoTabla.'<tr>
                    <td>'.$contador.'</td>
                    <td>'.$persona[0]['identificacion'].'</td>
                    <td>'.$persona[0]['primerNombre'].' '.$persona[0]['segundoNombre'].' '.$persona[0]['primerApellido'].' '.$persona[0]['segundoApellido'].'</td>
                    <td>'.$cargo[0]['descripcion'].'</td>
                    <td>'.$botonEliminarCargo.'</td></tr>';
            $contador++;
        }
        
        $tabla = '<div class="col-lg-2"></div><div class="col-lg-8 table-responsive" > <h4>PERSONAS CON CARGOS ADMINISTRATIVOS ASIGNADOS</h4><table class="table">
                 <thead>
                    <tr style="background-color:#eee">
                        <td>
                            #
                        </td>
                        <td>
                            IDENTIFICACIÓN
                        </td>
                        <td>
                            PERSONA
                        </td>
                        <td>
                            CARGO
                        </td>
                        <td>
                            ELIMINAR
                        </td>                        
                    </tr>
                 </thead>
                  <tbody>
                    '.$cuerpoTabla.'
                  </tbody>
                  </table></div><div class="col-lg-2"></div>';
        
        return $tabla;
    }
    
    
     public function eliminaradministrativoAction()
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
            $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 17, 1);
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
                        $idAdministrativoEncriptado = trim($post['id']); 
                                                 

                        if(empty($idAdministrativoEncriptado) || $idAdministrativoEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL ADMINISTRATIVO</div>';
                       }else 
                        { 
                            $idAdministrativo = $objMetodos->desencriptar($idAdministrativoEncriptado); 
                            
                            $administrativo=$objAdministrativo->FiltrarAdministrativo($idAdministrativo);
                            if(count($administrativo)==0) 
                                $mensaje = '<div class="alert alert-danger text-center" role="alert"> ADMINISTRATIVO NO REGISTRADO EN LA BASE DE DATOS</div>';
                            else{                         
                                if($administrativo[0]['estadoAdministrativo'] == FALSE){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTE REGISTRO YA HA SIDO DESHABILITADO POR LO TANTO NO PUEDE SER ELIMINADO</div>';
                                }else
                                {  
                                    $resultado= $objAdministrativo->ModificarEstadoAdministrativo($idAdministrativo, 0);
                                    if(count($resultado) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ LA ASIGNACIÓN DE CARGO ADMINISTRATIVO, POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{
                                        $mensaje = '<div class="alert alert-success text-center" role="alert">ASIGNACIÓN DE CARGO ADMINISTRATIVO ELIMINADO CORRECTAMENTE</div>';
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