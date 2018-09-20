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
use Zend\View\Model\JsonModel;
use Nel\Metodos\Metodos;
use Nel\Metodos\MetodosControladores;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\RangoAsistencia;
use Nel\Modelo\Entity\ConfigurarCurso;
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\Dias;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class RangoAsistenciaController extends AbstractActionController
{
    public $dbAdapter;
//    
    public function modificarestadorangoasistenciaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 12);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 12, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE MODIFICAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objRangoAsistencia = new RangoAsistencia($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idRangoAsistenciaEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                        $numeroFila2 = $post['numeroFila2'];
                        if($idRangoAsistenciaEncriptado == NULL || $idRangoAsistenciaEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL PORCENTAJE</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else  if(!is_numeric($numeroFila2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idRangoAsistencia = $objMetodos->desencriptar($idRangoAsistenciaEncriptado);
                            $listaRangoAsistencia = $objRangoAsistencia->FiltrarRangoAsistencia($idRangoAsistencia);
                            if(count($listaRangoAsistencia) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PORCENTAJE SELECCIONADO NO EXISTE</div>';
                            }else{
                                $estadoRangoAsistencia = FALSE;
                                if($listaRangoAsistencia[0]['estadoRangoAsistencia'] == FALSE){
                                    $estadoRangoAsistencia = TRUE;
                                }
                                $resultado = $objRangoAsistencia->ModificarEstadoRangoAsistencia($idRangoAsistencia, $estadoRangoAsistencia);
                                if(count($resultado) == 0){
                                    if($estadoRangoAsistencia == TRUE)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE HABILITÓ EL PORCENTAJE</div>';
                                    else
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE DESHABILITÓ EL PORCENTAJE</div>';
                                }else{
                                    $mensaje = '';
                                    $validar = TRUE;
                                    return new JsonModel(array('numeroFila'=>$numeroFila,'numeroFila2'=>$numeroFila2,'mensaje'=>$mensaje,'validar'=>$validar));
                                }
                            }
                        }   
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
//    
    public function eliminarrangoasistenciaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 12);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 12, 1);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objRangoAsistencia = new RangoAsistencia($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idRangoAsistenciaEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                        if($idRangoAsistenciaEncriptado == NULL || $idRangoAsistenciaEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL PORCENTAJE</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idRangoAsistencia = $objMetodos->desencriptar($idRangoAsistenciaEncriptado);
                            $listaRangoAsistencia = $objRangoAsistencia->FiltrarRangoAsistencia($idRangoAsistencia);
                            if(count($listaRangoAsistencia) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PORCENTAJE SELECCIONADA NO EXISTE</div>';
                            }else if(count($objConfigurarCurso->FiltrarConfigurarCursoPorRangoALimit1($idRangoAsistencia)) > 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PORCENTAJE SELECCIONADO YA HA SIDO UTILIZADO PARA LA CONFIGURACIÓN DE UN CURSO POR LO TANTO NO PUEDE SER ELIMINADO</div>';
                            }else{
                                $resultado = $objRangoAsistencia->EliminarRangoAsistencia($idRangoAsistencia);
                                if(count($resultado) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ EL PORCENTAJE</div>';
                                }else{
                                    $mensaje = '';
                                    $validar = TRUE;
                                    return new JsonModel(array('numeroFila'=>$numeroFila,'mensaje'=>$mensaje,'validar'=>$validar));
                                }
                            }
                        }   
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    } 
    public function obtenerrangosasistenciaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario,12);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objRangoAsistencia = new RangoAsistencia($this->dbAdapter);
                    ini_set('date.timezone','America/Bogota'); 
                    $listaRangosAsistencia = $objRangoAsistencia->ObtenerRangosAsistencia();
                    $tabla = $this->CargarTablaRangoAsistenciaAction($idUsuario, $this->dbAdapter, $listaRangosAsistencia, 0, count($listaRangosAsistencia));
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }
            
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
//    
    function CargarTablaRangoAsistenciaAction($idUsuario,$adaptador,$listaRangoAsistencia, $i, $j)
    {
        $objConfigurarCurso = new ConfigurarCurso($adaptador);
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $objMetodosC = new MetodosControladores();
        $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 12, 1);
        $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 12, 2);
        $array1 = array();
        foreach ($listaRangoAsistencia as $value) {
            $porcentaje = $value['porcentaje'].'%';
            $idRangoAsistenciaEncriptado = $objMetodos->encriptar($value['idRangoAsistencia']);
            $porcentaje2 = '<input type="hidden" id="porcentajeA'.$i.'" name="porcentajeA'.$i.'" value="'.$value['estadoRangoAsistencia'].'">'.$value['porcentaje'].'%';
            $fechaIngreso = $objMetodos->obtenerFechaEnLetra($value['fechaIngreso']);

            $botonEliminarRangoAsistencia = '';
            if($validarprivilegioEliminar == TRUE){
                if(count($objConfigurarCurso->FiltrarConfigurarCursoPorRangoALimit1($value['idRangoAsistencia'])) == 0)
                    $botonEliminarRangoAsistencia = '<button id="btnEliminarRangoAsistencia'.$i.'" title="ELIMINAR '.$porcentaje.'" onclick="eliminarRangoAsistencia(\''.$idRangoAsistenciaEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
            }

            $botonDeshabilitarRangoAsistencia = '';
            if($validarprivilegioModificar == TRUE){
                if($value['estadoRangoAsistencia'] == FALSE)
                    $botonDeshabilitarRangoAsistencia = '<button id="btnDeshabilitarRangoAsistencia'.$i.'" title="HABILITAR '.$porcentaje.'" onclick="deshabilitarRangoAsistencia(\''.$idRangoAsistenciaEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-minus-square"></i></button>';
            }
            $botones =  $botonDeshabilitarRangoAsistencia.' '.$botonEliminarRangoAsistencia;     
            $array1[$i] = array(
                '_j'=>$j,
                'porcentaje'=>$porcentaje2,
                'fechaIngreso'=>$fechaIngreso,
                'estadoRangoAsistencia'=>$value['estadoRangoAsistencia'],
                'opciones'=>$botones,
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
//    
//    
    public function ingresarrangoasistenciaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 12);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 12, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                       $objRangoAsistencia = new RangoAsistencia($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $porcentaje = trim(strtoupper($post['porcentaje']));
                        if(!is_numeric($porcentaje) || empty ($porcentaje) || $porcentaje > 100  || $porcentaje < 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE PORCENTAJE DE ASISTENCIA MÁXIMO 100</div>';
                        }else if(count($objRangoAsistencia->FiltrarRangoAsistenciaPorPorcentaje($porcentaje)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN PORCENTAJE LLAMADO '.$porcentaje.'%</div>';
                        }else{
                            $listaRangoActivo = $objRangoAsistencia->ObtenerRangoAsistenciaAcivo();
                            $estadoRangoAsistencia = FALSE;
                            if(count($listaRangoActivo) == 0){
                                $estadoRangoAsistencia = TRUE;
                            }
                            ini_set('date.timezone','America/Bogota'); 
                            $hoy = getdate();
                            $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                            $resultado =  $objRangoAsistencia->IngresarRangoAsistencia($porcentaje, $fechaSubida, $estadoRangoAsistencia);
                            if(count($resultado) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL PORCENTAJE POR FAVOR INTENTE MÁS TARDE</div>';
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

