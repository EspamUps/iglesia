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
use Nel\Modelo\Entity\Cursos;
use Nel\Modelo\Entity\ConfigurarCurso;
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\Dias;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class CursosController extends AbstractActionController
{
    public $dbAdapter;
    
    public function modificarestadocursoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 9);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 9, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE MODIFICAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objCurso = new Cursos($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idCursoEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                        $numeroFila2 = $post['numeroFila2'];
                        if($idCursoEncriptado == NULL || $idCursoEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CUROS</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else  if(!is_numeric($numeroFila2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idCurso = $objMetodos->desencriptar($idCursoEncriptado);
                            $listaCurso = $objCurso->FiltrarCurso($idCurso);
                            if(count($listaCurso) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADA NO EXISTE</div>';
                            }else if($listaCurso[0]['estadoCurso'] == FALSE && count($objCurso->FiltrarCursoPorNivelEstado($listaCurso[0]['nivelCurso'], 1)) > 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO NO SE PUEDE HABILITAR PORQUE YA HAY UN CURSO CON EL NIVEL '.$listaCurso[0]['nivelCurso'].' ACTIVO</div>';
                            }else{
                                $estadoCurso = FALSE;
                                if($listaCurso[0]['estadoCurso'] == FALSE){
                                    $estadoCurso = TRUE;
                                }
                                $resultado = $objCurso->ModificarEstadoCurso($idCurso,$estadoCurso);
                                if(count($resultado) == 0){
                                    if($estadoCurso == TRUE)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE HABILITÓ EL CURSO</div>';
                                    else
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE DESHABILITÓ EL CURSO</div>';
                                }else{
                                    $tabla = $this->CargarTablaCrusosAction($idUsuario, $this->dbAdapter, $resultado, $numeroFila, $numeroFila2);
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
    
    public function eliminarcursoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 9);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 9, 1);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objCurso = new Cursos($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idCursoEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                        if($idCursoEncriptado == NULL || $idCursoEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CURSO</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else {
                            $idCurso = $objMetodos->desencriptar($idCursoEncriptado);
                            $listaCurso = $objCurso->FiltrarCurso($idCurso);
                            if(count($listaCurso) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADA NO EXISTE</div>';
                            }else if(count($objConfigurarCurso->FiltrarConfigurarCursoPorCursoLimit1($idCurso)) > 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO YA HA SISDO SELECCIONADO PARA UNA CONFIGURACIÓN POR LO TANTO NO PUEDE SER ELIMINADO</div>';
                            }else{
                                $resultado = $objCurso->EliminarCurso($idCurso);
                                if(count($resultado) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ EL CURSO</div>';
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
    public function obtenercursosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 9);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objCursos = new Cursos($this->dbAdapter);
                    ini_set('date.timezone','America/Bogota'); 
                    $listaCursos = $objCursos->ObtenerCursos();
                    $tabla = $this->CargarTablaCrusosAction($idUsuario, $this->dbAdapter, $listaCursos, 0, count($listaCursos));
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }
            
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    function CargarTablaCrusosAction($idUsuario,$adaptador,$listaCursos, $i, $j)
    {
        $objConfigurarCurso = new ConfigurarCurso($adaptador);
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $objMetodosC = new MetodosControladores();
        $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 9, 1);
        $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 9, 2);
        $array1 = array();
        foreach ($listaCursos as $value) {
            $idCursoEncriptado = $objMetodos->encriptar($value['idCurso']);
            $nombreCurso = '<input type="hidden" id="estadoCursoA'.$i.'" name="estadoCursoA'.$i.'" value="'.$value['estadoCurso'].'">'.$value['nombreCurso'];
            $fechaIngreso = $objMetodos->obtenerFechaEnLetra($value['fechaIngreso']);

            $botonEliminarCurso = '';
            if($validarprivilegioEliminar == TRUE){
                if(count($objConfigurarCurso->FiltrarConfigurarCursoPorCursoLimit1($value['idCurso'])) == 0)
                    $botonEliminarCurso = '<button id="btnEliminarCurso'.$i.'" title="ELIMINAR '.$value['nombreCurso'].'" onclick="eliminarCurso(\''.$idCursoEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
            }

            $botonDeshabilitarCurso = '';
            if($validarprivilegioModificar == TRUE){
                if($value['estadoCurso'] == TRUE)
                    $botonDeshabilitarCurso = '<button id="btnDeshabilitarCurso'.$i.'" title="DESHABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarCurso(\''.$idCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-success btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
                else
                    $botonDeshabilitarCurso = '<button id="btnDeshabilitarCurso'.$i.'" title="HABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarCurso(\''.$idCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-minus-square"></i></button>';
            }
            $botones =  $botonDeshabilitarCurso.' '.$botonEliminarCurso;     
            $array1[$i] = array(
                '_j'=>$j,
                'nombreCurso'=>$nombreCurso,
                'fechaIngreso'=>$fechaIngreso,
                'nivelCurso'=>$value['nivelCurso'],
                'estadoCurso'=>$value['estadoCurso'],
                'opciones'=>$botones,
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    
    
    public function ingresarcursoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 9);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 9, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                       $objCurso = new Cursos($this->dbAdapter);
                       $objDias = new Dias($this->dbAdapter);
                       $objHorario = new Horario($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $descripcionCruso = trim(strtoupper($post['descripcionCurso']));
                        $nivelCurso = $post['nivelCurso'];
                        if(empty ($descripcionCruso) || strlen($descripcionCruso) > 100){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DEL CURSO MÁXIMO 100 CARACTERES</div>';
                        }else if(count( $objCurso->FiltrarCursoPorNombre($descripcionCruso)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA CRUSO LLAMADO '.$descripcionCruso.'</div>';
                        }else if(!is_numeric($nivelCurso)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NIVEL DEL CURSO</div>';
                        }else if(count( $objCurso->FiltrarCursoPorNivelEstado($nivelCurso,1)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA CURSO ACTIVO CON EL NIVEL '.$nivelCurso.'</div>';
                        }else{
                            ini_set('date.timezone','America/Bogota'); 
                            $hoy = getdate();
                            $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                            $resultado =  $objCurso->IngresarCurso($descripcionCruso,$nivelCurso, $fechaSubida, 1);
                            if(count($resultado) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL CURSO POR FAVOR INTENTE MÁS TARDE</div>';
                            }else{ 
                                $listaDias = $objDias->ObtenerDias();
                                if(count($listaDias) != 7){
                                    $objCurso->EliminarCurso($resultado[0]['idCurso']);
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">ERROR INTERNO DEL SISTEMA POR FAVOR VERIFIQUE LOS DÍAS</div>';
                                }else{
                                    $validarIngresoHorario = TRUE;
                                    $idCurso = $resultado[0]['idCurso'];
                                    foreach ($listaDias as $valueDias){
                                        
                                        $resultadoHorario = $objHorario->IngresarHorario($idCurso, $valueDias['idDia'], 1);
                                        if(count($resultadoHorario) == 0){
                                            $validarIngresoHorario = FALSE;
                                            break;
                                        }
                                    } 
                                    if($validarIngresoHorario == FALSE){
                                        $objCurso->EliminarCurso($idCurso);
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL CURSO POR FAVOR INTENTE MÁS TARDE</div>';
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

