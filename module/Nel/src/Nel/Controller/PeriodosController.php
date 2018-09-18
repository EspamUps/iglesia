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
use Nel\Modelo\Entity\Periodos;
use Nel\Modelo\Entity\ConfigurarCurso;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class PeriodosController extends AbstractActionController
{
    public $dbAdapter;
    
    public function modificarestadoperiodoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 8);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 8, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE MODIFICAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objPeriodo = new Periodos($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idPeriodoEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                        $numeroFila2 = $post['numeroFila2'];
                        if($idPeriodoEncriptado == NULL || $idPeriodoEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL PERIODO</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else  if(!is_numeric($numeroFila2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idPeriodo = $objMetodos->desencriptar($idPeriodoEncriptado);
                            $listaPeriodo = $objPeriodo->FiltrarPeriodo($idPeriodo);
                            if(count($listaPeriodo) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PERIODO SELECCIONADA NO EXISTE</div>';
                            }else{
                                $estadoPeriodo = FALSE;
                                if($listaPeriodo[0]['estadoPeriodo'] == FALSE){
                                    $estadoPeriodo = TRUE;
                                }
                                $resultado = $objPeriodo->ModificarEstadoPeriodo($idPeriodo, $estadoPeriodo);
                                if(count($resultado) == 0){
                                    if($estadoPeriodo == TRUE)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE HABILITÓ EL PERIODO</div>';
                                    else
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE DESHABILITÓ EL PERIODO</div>';
                                }else{
                                    $tabla = $this->CargarTablaPeriodosAction($idUsuario, $this->dbAdapter, $resultado, $numeroFila, $numeroFila2);
                                    $mensaje = '';
                                    $validar = TRUE;
                                    return new JsonModel(array('tabla'=>$tabla,'numeroFila'=>$numeroFila,'numeroFila2'=>$numeroFila2,'mensaje'=>$mensaje,'validar'=>$validar));
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
    public function eliminarperiodoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 8);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 8, 1);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objPeriodo = new Periodos($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idPeriodoEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                        if($idPeriodoEncriptado == NULL || $idPeriodoEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL PERIODO</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idPeriodo = $objMetodos->desencriptar($idPeriodoEncriptado);
                            $listaPeriodo = $objPeriodo->FiltrarPeriodo($idPeriodo);
                            if(count($listaPeriodo) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PERIODO SELECCIONADA NO EXISTE</div>';
                            }else{
                                $resultado = $objPeriodo->EliminarPeriodo($idPeriodo);
                                if(count($resultado) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ EL PERIODO</div>';
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
    public function obtenerperiodosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 8);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objPeriodo = new Periodos($this->dbAdapter);
                    ini_set('date.timezone','America/Bogota'); 
                    $listaPeriodos = $objPeriodo->ObtenerPeriodos();
                    $tabla = $this->CargarTablaPeriodosAction($idUsuario, $this->dbAdapter, $listaPeriodos, 0, count($listaPeriodos));
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }
            
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
//    
    function CargarTablaPeriodosAction($idUsuario,$adaptador,$listaPeriodo, $i, $j)
    {
        $objConfigurarCurso = new ConfigurarCurso($adaptador);
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $objMetodosC = new MetodosControladores();
        $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 8, 1);
        $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 8, 2);
        $array1 = array();
        foreach ($listaPeriodo as $value) {
            $idPeriodoEncriptado = $objMetodos->encriptar($value['idPeriodo']);
            $nombrePeriodo = '<input type="hidden" id="estadoPeriodoA'.$i.'" name="estadoPeriodoA'.$i.'" value="'.$value['estadoPeriodo'].'">'.$value['nombrePeriodo'];
            $fechaIngreso = $objMetodos->obtenerFechaEnLetra($value['fechaIngreso']);

            $botonEliminarPeriodo = '';
            if($validarprivilegioEliminar == TRUE){
                if(count($objConfigurarCurso->FiltrarConfigurarCursoPorPeriodoLimit1($value['idPeriodo'])) == 0)
                    $botonEliminarPeriodo = '<button id="btnEliminarPeriodo'.$i.'" title="ELIMINAR '.$value['nombrePeriodo'].'" onclick="eliminarPeriodo(\''.$idPeriodoEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
            }

            $botonDeshabilitarPeriodo = '';
            if($validarprivilegioModificar == TRUE){
                if($value['estadoPeriodo'] == TRUE)
                    $botonDeshabilitarPeriodo = '<button id="btnDeshabilitarPeriodo'.$i.'" title="DESHABILITAR '.$value['nombrePeriodo'].'" onclick="deshabilitarPeriodo(\''.$idPeriodoEncriptado.'\','.$i.','.$j.')" class="btn btn-success btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
                else
                    $botonDeshabilitarPeriodo = '<button id="btnDeshabilitarPeriodo'.$i.'" title="HABILITAR '.$value['nombrePeriodo'].'" onclick="deshabilitarPeriodo(\''.$idPeriodoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-minus-square"></i></button>';
            }
            $botones =  $botonDeshabilitarPeriodo.' '.$botonEliminarPeriodo;     
            
            $fechaInicio = $objMetodos->obtenerFechaEnLetraSinHora($value['fechaInicio']);
            $fechaFin = $objMetodos->obtenerFechaEnLetraSinHora($value['fechaFin']);
            
            $array1[$i] = array(
                '_j'=>$j,
                'nombrePeriodo'=>$nombrePeriodo,
                'fechaInicio'=>$fechaInicio,
                'fechaFin'=>$fechaFin,
                'fechaIngreso'=>$fechaIngreso,
                'opciones'=>$botones,
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    
    
    public function ingresarperiodoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 8);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 8, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                       $objPeriodo = new Periodos($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $nombrePeriodo = trim(strtoupper($post['descripcionPeriodo']));
                        $fechaInicio = $post['fechaInicio'];
                        $fechaFin = $post['fechaFin'];
                        if(empty ($nombrePeriodo) || strlen($nombrePeriodo) > 100){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DEL PERIODO MÁXIMO 100 CARACTERES</div>';
                        }else if(count( $objPeriodo->FiltrarPeriodoPorNombre($nombrePeriodo)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN PERIODO LLAMADO '.$nombrePeriodo.'</div>';
                        }else if(empty ($fechaInicio)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA FECHA DE INICIO</div>';
                        }else if(empty ($fechaFin)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA FECHA FÍN</div>';
                        }else{
                            ini_set('date.timezone','America/Bogota'); 
                            $hoy = getdate();
                            $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                            $fecha_actual = strtotime(date("d-m-Y"));
                            $fechaInicio2 = strtotime($fechaInicio);
                            $fechaFin2 = strtotime($fechaFin);
                            
                            if($fechaInicio2 < $fecha_actual || $fechaFin2 < $fecha_actual){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LAS FECHAS INGRESADAS NO DEBEN SER MENORES A LA ACTUAL</div>';
                            }else{
                            
                                if($fechaFin2 <= $fechaInicio2){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA FIN NO DEBE SER MENOR O IGUAL A LA DE INICIO</div>';
                                }else{
                                    $resultado = $objPeriodo->IngresarPeriodo($nombrePeriodo, $fechaInicio, $fechaFin, $fechaSubida, 1);
                                    if(count($resultado) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL PERIODO POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{ 
                                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
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

