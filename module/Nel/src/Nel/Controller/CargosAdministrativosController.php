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

class CargosAdministrativosController extends AbstractActionController
{
    public $dbAdapter;
    
    
    public function obtenercargosadministrativosAction()
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
            $objAdministrativos = new Administrativos($this->dbAdapter);
            $objMetodos = new Metodos();
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 7);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objCargosAdministrativos = new CargosAdministrativos($this->dbAdapter);
                    $listaCargosAdministrativos = $objCargosAdministrativos->ObtenerTodosCargosAdministrativos();
                
                    $cuerpoTabla ="";
                   
                    $utilizado="Sin asignar";
                    $contador=0;
                    foreach ($listaCargosAdministrativos as $valueCargos) {
                        $idCargoAdministrativoEncriptado = $objMetodos->encriptar( $valueCargos['idCargoAdministrativo']);
                        if($valueCargos['estadoCargoAdministrativo']==1)
                        { 
                            $botonCambiarEstado="";
                            $listaUtilizado= $objAdministrativos->ObtenerAdministrativosPorCargoAdministrativo($valueCargos['idCargoAdministrativo']);
                            
                            if(count($listaUtilizado)>0)
                                $utilizado="Asignado";
                            else{
                                $botonCambiarEstado = '<button id="btnCambiarEstadoCargo'.$contador.'" title="DESHABILITAR CARGO '.$valueCargos['descripcion'].'" onclick="CambiarEstadoCargo(\''.$idCargoAdministrativoEncriptado.'\','.$contador.')" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i></button>';
                            }
                                
                        }
                        else
                            $botonCambiarEstado = '<button id="btnCambiarEstadoCargo'.$contador.'" title="HABILITAR CARGO '.$valueCargos['descripcion'].'" onclick="CambiarEstadoCargo(\''.$idCargoAdministrativoEncriptado.'\','.$contador.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                        
                        
                        $numero=$contador+1;
                        $cuerpoTabla=$cuerpoTabla.'<tr id="numerofila'.$contador.'">
                                <td  >'.$numero.'</td>
                                <td>'.$valueCargos['descripcion'].'</td>
                                <td>'.$utilizado.'</td>
                            </tr>';
                        $contador++;
                    }
                    
                    
                    $tabla = '<div class="col-lg-2"></div><div class="col-lg-8 table-responsive" >
                            <h4>CARGOS ADMINISTRATIVOS REGISTRADOS</h4><table class="table table-bordered table-hover">
                            <thead>
                            <tr  style="background-color:#eee">
                                <td>#</td>
                                <td>CARGO ADMINISTRATIVO</td>
                                <td>ESTADO</td>
                                
                            </tr>
                            </thead>
                            <tbody>
                            '.$cuerpoTabla.'
                            </tbody>
                            </table></div><div class="col-lg-2">';
                    
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }                    
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function ingresarcargoadministrativoAction()
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
                        $descripcionCargo = trim(strtoupper($post['nombreCargo']));
                                                 

                        if(empty($descripcionCargo)|| strlen($descripcionCargo) > 100){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA DESCRIPCIÓN DEBE SER DE HASTA 100 CARACTERES</div>';
                       }else 
                        { 
                           $listaCargoAdministrativo = $objCargosAdministrativos->FiltrarCargoAdministrativoPorDescripcion($descripcionCargo);
                           
                            
                            if(count($listaCargoAdministrativo)>0) 
                                if($listaCargoAdministrativo[0]['estadoCargoAdministrativo']==FALSE)
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">CARGO ADMINISTRATIVO YA REGISTRADO EN EL SISTEMA, SOLO DEBE HABILITARLO</div>';
                                else
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">CARGO ADMINISTRATIVO YA REGISTRADO Y HABILITADO EN EL SISTEMA</div>';   
                            else{              
                                
                                    ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaIngreso = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                               
                                    $ultimoCargoRegistrado = $objCargosAdministrativos->FiltrarCargoAdministrativoLast();
                                    if(count($ultimoCargoRegistrado)==0)
                                        $identificador=1;
                                    else
                                        $identificador=$ultimoCargoRegistrado[0]['identificador']+1;
                                    $resultado = $objCargosAdministrativos->IngresarCargoAdministrativo($descripcionCargo, $identificador, $fechaIngreso, 1) ;
                                    if(count($resultado) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL CARGO, POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{
                                        $mensaje = '<div class="alert alert-success text-center" role="alert">CARGO ADMINISTRATIVO INGRESADO CORRECTAMENTE</div>';
                                        $validar = TRUE;
                                    }
                                }
                        }
                        }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function modificarestadoAction()
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
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 17, 2);
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
                        $idCargoAdministrativoEncriptado = $post['idCargoAdministrativoEncriptado'];
                        $Nfila = $post['Nfila'];                        

                        if(empty($idCargoAdministrativoEncriptado)|| $idCargoAdministrativoEncriptado=="0" || $idCargoAdministrativoEncriptado ==NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CARGO</div>';
                        }if($Nfila ==NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA LA FILA DE LA TABLA</div>';
                        }else 
                        { 
                           $idCargoAdministrativo = $objMetodos->desencriptar($idCargoAdministrativoEncriptado);
                           $listaCargoAdministrativo=$objCargosAdministrativos->FiltrarCargoAdministrativoSinImportarEstado($idCargoAdministrativo);
                            
                            if(count($listaCargoAdministrativo)==0) 
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCONTRÓ EL CARGO ADMINISTRATIVO REGISTRADO EN EL SISTEMA</div>';
                             else{  
                                if($listaCargoAdministrativo[0]['estadoCargoAdministrativo']==1){
                                       $resultado = $objCargosAdministrativos->ModificarEstadoCargoAdministrativo($idCargoAdministrativo, 0);
                                }else {
                                       $resultado = $objCargosAdministrativos->ModificarEstadoCargoAdministrativo($idCargoAdministrativo, 1);
                                   } 
                                if(count($resultado) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE MODIFICÓ EL ESTADO DEL CARGO, POR FAVOR INTENTE MÁS TARDE</div>';
                                }else{

                                    $listaUtilizado= $objAdministrativo->ObtenerAdministrativosPorCargoAdministrativo($resultado[0]['idCargoAdministrativo']);

                                    if(count($listaUtilizado)==0)
                                        $utilizado='Sin Asignar';
                                    else
                                        $utilizado='Asignado';
                                    $botonCambiarEstado="";
                                    if($resultado[0]['estadoCargoAdministrativo']==1)
                                        $botonCambiarEstado = '<button id="btnCambiarEstadoCargo'.$Nfila.'" title="DESHABILITAR CARGO '.$resultado[0]['descripcion'].'" onclick="CambiarEstadoCargo(\''.$idCargoAdministrativoEncriptado.'\','.$Nfila.')" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i></button>';
                                    else
                                         $botonCambiarEstado = '<button id="btnCambiarEstadoCargo'.$Nfila.'" title="HABILITAR CARGO '.$resultado[0]['descripcion'].'" onclick="CambiarEstadoCargo(\''.$idCargoAdministrativoEncriptado.'\','.$Nfila.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                                    $numero=$Nfila+1;
                                    $nuevaFila ='<td>'.$numero.'</td>
                                                 <td>'.$resultado[0]['descripcion'].'</td>
                                                 <td>'.$utilizado.'</td>
                                                 <td>'.$botonCambiarEstado.'</td>';

                                    $mensaje = '<div class="alert alert-success text-center" role="alert">ESTADO DEL CARGO ADMINISTRATIVO MODIFICADO CORRECTAMENTE</div>';
                                    $validar = TRUE;
                                     return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar, 'nuevafila'=>$nuevaFila, 'numeroFila'=>$Nfila));
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