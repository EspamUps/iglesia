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
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Matricula;
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\Asistencia;
use Nel\Modelo\Entity\HoraHorario;
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\FechaAsistencia;
use Nel\Modelo\Entity\Periodos;
use Nel\Modelo\Entity\HorarioCurso;
use Nel\Modelo\Entity\ConfigurarCurso;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class AsistenciasController extends AbstractActionController
{
    public $dbAdapter;
  
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 16);
            $objMetodos = new Metodos();
            $objPeriodo = new Periodos($this->dbAdapter);
            $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    
                    $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                    $idPeriodoEncriptado = $post['idPeriodo'];
                    
                    if(empty($idPeriodoEncriptado) || $idPeriodoEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">ÍNDICE DEL PERIODO VACÍO</div>';
                    }else{
                        $idPeriodo = $objMetodos->desencriptar($idPeriodoEncriptado);
                        $listaPeriodo = $objPeriodo->FiltrarPeriodo($idPeriodo);
                        if(count($listaPeriodo)==0)
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL PERIODO EN EL SISTEMA</div>';
                        else{                            
                            $listaCursos = $objConfigurarCurso->FiltrarConfigurarCursoPorPeriodo($idPeriodo);
                            if(count($listaCursos)==0)
                            {
                               $mensaje = '<div class="alert alert-warning text-center" role="alert">ESTE PERIODO NO TIENE CURSOS HABILITADOS</div>';
                            }else{
                                $optionCurso = '<option value="0">SELECCIONE UN CURSO</option>';
                                foreach ($listaCursos as $valueC) {
                                    $idCursoEncriptado = $objMetodos->encriptar($valueC['idCurso']);
                                    $optionCurso = $optionCurso.'<option value="'.$idCursoEncriptado.'">'.$valueC['nombreCurso'].'</option>';
                                }
                                
                                $select = '<label for="selectCursoAsistencia">CURSO</label><select onchange="filtrarlistaHorariosPorCurso();"  id="selectCursoAsistencia" name="selectCursoAsistencia" class="form-control">'.$optionCurso.'</select>';
                                $mensaje = '';
                                $validar = TRUE;
                                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'select'=>$select));
                            } 
                        }
                    }                   
                }                    
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function obtenerhorariosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 16);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objMetodos = new Metodos();
                    $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );
                    $idCursoEncriptado = $post['id'];
                    $idPeriodoEncriptado = $post['idPeriodo'];
                    if(empty($idPeriodoEncriptado)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL PERIODO</div>';
                    }else if(empty($idCursoEncriptado)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CURSO</div>';
                    }else{
                        $idCurso=$objMetodos->desencriptar($idCursoEncriptado);
                        $idPeriodo = $objMetodos->desencriptar($idPeriodoEncriptado);
                        $listaConfigurarCurso = $objConfigurarCurso->FiltrarListaHorariosPorCursoPorPeriodo($idPeriodo, $idCurso, 1);

                        if(count($listaConfigurarCurso)==0)
                            $mensaje = '<div class="alert alert-warning text-center" role="alert">ACTUALMENTE NO EXISTEN HORARIOS HABILITADOS PARA ESTE CURSO</div>';
                        else{      
                            $contador =1;
                            $optionHorarios = '<option value="0">SELECCIONE UN HORARIO</option>';
                            foreach ($listaConfigurarCurso as $valueCC) {
                                $idConfigurarCursoEncriptado = $objMetodos->encriptar($valueCC['idConfigurarCurso']);
                                $optionHorarios = $optionHorarios.'<option value="'.$idConfigurarCursoEncriptado.'">Horario#'.$contador.'</option>';
                                $contador++;
                            }

                            $div = '<label for="selectConfigurarCursoAsistencia">HORARIO</label>
                                    <select onchange="filtrarHorarioPorCurso();"  id="selectConfigurarCursoAsistencia" name="selectConfigurarCursoAsistencia" class="form-control">'.$optionHorarios.'</select>';
                            $mensaje ='';
                            $validar = TRUE;    
                            return new JsonModel(array('div'=>$div,'mensaje'=>$mensaje,'validar'=>$validar));
                            }
                        }
                    }
                }
            }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function filtrardatoshorarioAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 16);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objMetodos = new Metodos();
                    $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                    $objMatricula = new Matricula($this->dbAdapter);
                    $objHorarioCurso = new HorarioCurso($this->dbAdapter);
                    $objFechaAsistencia = new FechaAsistencia($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );
                     $idConfigurarCursoEncriptado = $post['id'];

                    if(empty($idConfigurarCursoEncriptado)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CURSO</div>';
                    }else{
                        $idConfigurarCurso = $objMetodos->desencriptar($idConfigurarCursoEncriptado);

                        $listaConfigurarCurso = $objConfigurarCurso->FiltrarConfigurarCurso($idConfigurarCurso);
                        if(count($listaConfigurarCurso) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO ESTÁ CONFIGURADO</div>';
                        }else if($listaConfigurarCurso[0]['estadoConfigurarCurso'] == FALSE){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO HA SIDO HABILITADO</div>';
                        }else{

                            $listaHorarioCurso = $objHorarioCurso->FiltrarHorarioCursoPorConfiguCurso($listaConfigurarCurso[0]['idConfigurarCurso']);
                            $cuerpoTablaHorario = '';
                            foreach ($listaHorarioCurso as $valueHorarioCurso) {
                                $horaInicio = strtotime ( '-1 second' , strtotime($valueHorarioCurso['horaInicio']));
                                $horaInicio = date( 'H:i:s' , $horaInicio );
                                $horaFin = strtotime ( '+1 second' , strtotime($valueHorarioCurso['horaFin']));
                                $horaFin = date( 'H:i:s' , $horaFin );
                                $horas = $horaInicio.' - '.$horaFin;
                                $cuerpoTablaHorario = $cuerpoTablaHorario.'<tr class="text-center"><td class="text-center">'.$valueHorarioCurso['nombreDia'].'</td><td>'.$horas.'</td></tr>';
                            }
                            $listaMatriculados = $objMatricula->FiltrarMatriculaPorConfigurarCursoYEstado($idConfigurarCurso, 1); 
                            $cuposDisponibles = $listaConfigurarCurso[0]['cupos']-count($listaMatriculados);

                           $divInfoGeneral = 
                            '<div class="box box-solid form-group" style="border-style: solid;border: 1px solid #d2d6de;">
                                <div class="box-header ui-sortable-handle" style="cursor: move;">
                                    <i class="fa  fa-info-circle"></i>

                                    <h3 class="box-title">INFORMACIÓN GENERAL DEL HORARIO SELECCIONADO</h3>
                                    <!-- tools box -->
                                    <div class="pull-right box-tools">
                                      <button type="button" class="btn  btn-sm btn-default" data-widget="collapse"><i class="fa fa-minus"></i>
                                      </button>
                                    </div>
                                    <!-- /. tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body no-padding" style="display: block;">
                                    <!--The calendar -->
                                    <div id="calendar" style="width: 100%">
                                    <div class="datepicker datepicker-inline">
                                    <div class="datepicker-days" style="display: block;">
                                    <table class="table table-condensed">
                                    <thead> 

                                        <tr>
                                            <th><label for="apellidos">FECHA DE INICIO DE CLASES</label></th>
                                            <td>'.$listaConfigurarCurso[0]['fechaInicio'].'</td>
                                        </tr>
                                        <tr>
                                            <th><label for="apellidos">FECHA DE FIN DE CLASES</label></th>
                                            <td>'.$listaConfigurarCurso[0]['fechaFin'].'</td>
                                        </tr>
                                        <tr>
                                            <th><label for="apellidos">CUPOS TOTALES</label></th>
                                            <td>'.$listaConfigurarCurso[0]['cupos'].'</td>
                                        </tr>
                                        <tr>
                                            <th><label for="apellidos">ESTUDIANTES MATRICULADOS</label></th>
                                            <td>'.count($listaMatriculados).'</td>
                                        </tr>
                                        <tr>
                                            <th><label for="apellidos">CUPOS NO UTILIZADOS</label></th>
                                            <td>'.$cuposDisponibles.'</td>
                                        </tr>
                                        <tr>
                                            <th><label for="apellidos">DOCENTE</label></th>
                                            <td>'.$listaConfigurarCurso[0]['primerNombre'].' '.$listaConfigurarCurso[0]['primerApellido'].'</td>
                                        </tr>
                                    </thead>
                                    </table>
                                    </div>
                                    </div>
                                    </div>
                                </div>
                                  <!-- /.box-body -->
                                    <!-- /.row -->
                            </div>';


                           $tabla = 
                            '<div class="box box-solid form-group" style="border-style: solid;border: 1px solid #d2d6de;" >
                                <div class="box-header ui-sortable-handle" style="cursor: move;">
                                    <i class="fa fa-calendar"></i>

                                    <h3 class="box-title">HORARIO DE CLASES</h3>
                                    <!-- tools box -->
                                    <div class="pull-right box-tools">
                                      <button type="button" class="btn  btn-default btn-sm" data-widget="collapse"><i class="fa fa-minus"></i>
                                      </button>
                                    </div>
                                    <!-- /. tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body no-padding" style="display: block;">
                                    <!--The calendar -->
                                    <div id="calendar" style="width: 100%">
                                    <div class="datepicker datepicker-inline">
                                    <div class="datepicker-days" style="display: block;">
                                    <table class="table table-condensed">
                                    <thead>
                                                <tr>
                                                    <th>DÍA</td>
                                                    <th>HORAS</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                '.$cuerpoTablaHorario.'
                                            </tbody>
                                    </table>
                                    </div>
                                    </div>
                                    </div>
                                </div>
                                  <!-- /.box-body -->
                                    <!-- /.row -->
                            </div>';
                           
                            ini_set('date.timezone','America/Bogota'); 
                            $fechaActual = strtotime(date("d-m-Y"));
                            $fechaInicioClases = strtotime($listaConfigurarCurso[0]['fechaInicio']);
                            
                            $listaFechasAsistencia= $objFechaAsistencia->FiltrarFechaAsistenciaPorIdConfCurso($idConfigurarCurso, 1);
                           
                            $selectFechas='';
                            if(count($listaMatriculados)==0)
                            {
                                $small= '<small class="label label-default"><i class="fa fa-close"></i> curso sin estudiantes matriculados</small>';
                                
                            }else
                            {
                                if(count($listaFechasAsistencia)>0)
                                { 
                                   $small= '<small class="label label-success"><i class="fa fa-check"></i> realizado</small>';
                                   
                                    
                                    $optionFechas = '<option value="0">SELECCIONE UNA FECHA</option>';
                                    foreach ($listaFechasAsistencia as $valueF) {
                                        $fechaAsistencia = strtotime($valueF['fechaAsistencia']);
                                        $idFechaAsistenciaEncriptado = $objMetodos->encriptar($valueF['idFechaAsistencia']);
                                        if($fechaAsistencia==$fechaActual)
                                            $optionFechas = $optionFechas.'<option selected value="'.$idFechaAsistenciaEncriptado.'">'.$valueF['fechaAsistencia'].'</option>';
                                        else
                                            $optionFechas = $optionFechas.'<option value="'.$idFechaAsistenciaEncriptado.'">'.$valueF['fechaAsistencia'].'</option>';
                                    }

                                    
                                    $selectFechas = '<label for="selectListaFechasAsistencia">LISTA DE FECHAS DE ASISTENCIA</label><select onchange="cargarTablaListaAsistencia();"  id="selectListaFechasAsistencia" name="selectListaFechasAsistencia" class="form-control">'.$optionFechas.'</select>';
                                   
                                     
                                }else
                                 {                              

                                    if($fechaActual>= $fechaInicioClases)
                                    {
                                        $small ='  <small class="label label-danger"><i class="fa fa-clock-o"></i> pendiente</small>
                                          <button type="button" onClick="GenerarAsistencia()" class="btn  btn-success  btn-xs"><i class="fa fa-check"></i> GENERAR ASISTENCIA
                                           </button>';}else
                                    {
                                        $small= ' <small class="label label-default"><i class="fa fa-close"></i> deshabilitado hasta que finalicen las matrículas</small>';
                                    }
                                }
                            }
                            
                           
                           

                            $tablaAsistencia = 
                            '<div class="box box-solid form-group" style="border-style: solid;border: 1px solid #d2d6de;">
                                <div class="box-header ui-sortable-handle" style="cursor: move;">
                                    <i class="fa fa-user"></i>

                                    <h3 class="box-title">ACTIVIDADES PARA EL DOCENTE</h3>
                                    <!-- tools box -->
                                    <div class="pull-right box-tools">
                                      <button type="button" class="btn  btn-default  btn-sm" data-widget="collapse"><i class="fa fa-minus"></i>
                                      </button>
                                    </div>
                                    <!-- /. tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body no-padding" style="display: block;">
                                    <!--The calendar -->
                                    <div id="calendar" style="width: 100%">
                                    <div class="datepicker datepicker-inline">
                                    <div class="datepicker-days" style="display: block;">
                                    <div class="form-group" style="padding-right:10%; padding-left:10%;"> 
                                        <tr class="text-center">
                                            <td class="text-center">ASISTENCIA DEL CURSO: </td>
                                            <td>'.$small.'</td>
                                        </tr> 
                                    </div>
                                    </div>
                                    </div>
                                    </div>
                                    </div>
                                </div>
                            </div>';
                            $mensaje = '';
                            $validar = TRUE;
                            return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla,'datosGenerales'=>$divInfoGeneral, 'tablaAsistencia'=>$tablaAsistencia, 'select'=>$selectFechas));

                        }
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    public function generarasistenciaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 16);
            $objMetodos = new Metodos();
            $objMetodosC = new MetodosControladores();
            $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
            $objHorarioCurso= new HorarioCurso($this->dbAdapter);
            $objFechaAsistencia = new FechaAsistencia($this->dbAdapter);
            $objMatricula = new Matricula($this->dbAdapter);
            $objAsistencia = new Asistencia($this->dbAdapter);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 16, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{

                        $post = array_merge_recursive(
                                $request->getPost()->toArray(),
                                $request->getFiles()->toArray()
                            );
                        $idConfigurarCursoEncriptado = $post['idConfCurso'];

                        if(empty($idConfigurarCursoEncriptado) || $idConfigurarCursoEncriptado == NULL){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ÍNDICE DEL CURSO CONFIGURADO ESTÁ VACÍO</div>';
                        }else{
                            $idConfigurarCurso = $objMetodos->desencriptar($idConfigurarCursoEncriptado);
                            $listaConfigurarCurso = $objConfigurarCurso->FiltrarConfigurarCurso($idConfigurarCurso);
                            if(count($listaConfigurarCurso) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO ESTÁ CONFIGURADO</div>';
                            }else if($listaConfigurarCurso[0]['estadoConfigurarCurso'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO HA SIDO HABILITADO</div>';
                            }else{
                               
                            
                                $listaHorarioRegistrado =$objHorarioCurso->FiltrarHorarioCursoPorConfiguCursoDistinctIdentificadorDia($idConfigurarCurso);
                                $listaMatriculadosEnElCurso= $objMatricula->FiltrarMatriculaPorConfigurarCursoYEstado($idConfigurarCurso,1);
                                if(count($listaMatriculadosEnElCurso)==0)
                                  $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HAY MATRICULADOS EN ESTE CURSO</div>';
                                else{
                                    
                                    $fechaInicio =$listaConfigurarCurso[0]['fechaInicio'];
                                    $fechaFin = $listaConfigurarCurso[0]['fechaFin'];
                                    $this->generarRegistrosAsistenciaAction($idConfigurarCurso,$fechaInicio,$fechaFin,$listaHorarioRegistrado,$listaMatriculadosEnElCurso,$objAsistencia, $objFechaAsistencia);
                                   
                                    
                                    $listaFechasIngresadas = $objFechaAsistencia->FiltrarFechaAsistenciaPorIdConfCurso($idConfigurarCurso, 1);
                                    
                                    $optionFechas = '<option value="0">SELECCIONE UNA FECHA</option>';
                                    foreach ($listaFechasIngresadas as $valueF) {
                                        $idFechaAsistenciaEncriptado = $objMetodos->encriptar($valueF['idFechaAsistencia']);
                                        $optionFechas = $optionFechas.'<option value="'.$idFechaAsistenciaEncriptado.'">'.$valueF['fechaAsistencia'].'</option>';
                                    }


                                    $select = '<label for="selectListaFechasAsistencia">ASISTENCIA</label><select onchange="cargarTablaListaAsistencia();"  id="selectListaFechasAsistencia" name="selectListaFechasAsistencia" class="form-control">'.$optionFechas.'</select>';
        
                                    $mensaje = '<div class="alert alert-success text-center" role="alert">SE REGISTRÓ LA ASISTENCIA DE TODOS LOS ESTUDIANTES DE FORMA CORRECTA</div>';
                                    $validar = TRUE;
                                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar, 'select'=>$select));
                                    }
                                }
                            }   
                        }
                    }                    
                }
            }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    function  generarRegistrosAsistenciaAction($idConfigurarCurso,$fechaInicio,$fechaFin,$listaHorarioRegistrado,$listaMatriculadosEnElCurso,$objAsistencia, $objFechaAsistencia)
    {
        
        for($i=$fechaInicio;$i<=$fechaFin;$i = date("Y-m-d", strtotime($i ."+ 1 days")))
        {

            $fechaAsistencia= strtotime($i);
            $numeroDia =date("w",$fechaAsistencia);
            foreach ($listaHorarioRegistrado as $valueDiaHorarioR)
            {
                if($valueDiaHorarioR['identificadorDia']==$numeroDia)
                    {
                    $resultado = $objFechaAsistencia->IngresarFechasAsistencia ($idConfigurarCurso, $i, 1);
                    $idFechaAsistencia = $resultado[0]['idFechaAsistencia'];
                    $this->generarasistenciascompletasAction($listaMatriculadosEnElCurso,$idFechaAsistencia,$objAsistencia);
                    }
                }
        }
        
        
    }
    
    function  generarasistenciascompletasAction($listaMatriculadosEnElCurso, $idFechaAsistencia, $objAsistencia)
    {
        ini_set('date.timezone','America/Bogota'); 
        $hoy = getdate();
        $fechaActual = $hoy['year']."-".$hoy['mon']."-".$hoy['mday'];

        foreach ($listaMatriculadosEnElCurso as $valueMatriculado) {
            $resultado = $objAsistencia->IngresarAsistenciaHoy($idFechaAsistencia, $valueMatriculado['idMatricula'], 1, $fechaActual, 1);
        }
    }
    
    
    
    public function obtenerasistenciasAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 16);
            $objMetodos = new Metodos();
            $objFechaAsistencia = new FechaAsistencia($this->dbAdapter);
            $objAsistencia = new Asistencia($this->dbAdapter);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    
                    $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                    $idFechaAsistenciaEncriptado = $post['id'];
                    
                    if(empty($idFechaAsistenciaEncriptado) || $idFechaAsistenciaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">ÍNDICE DE LA FECHA ASISTENCIA VACÍO</div>';
                    }else{
                        $idFechaAsistencia = $objMetodos->desencriptar($idFechaAsistenciaEncriptado);
                        $listaFechaAsistencia = $objFechaAsistencia->FiltrarFechaAsistencia($idFechaAsistencia, 1);
                        if(count($listaFechaAsistencia)==0)
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA FECHA ASISTENCIA EN EL SISTEMA</div>';
                        else{           
                            
                            $listaAsistencias = $objAsistencia->FiltrarAsistenciaPorFechaAsistencia($idFechaAsistencia, 1);
                            
                             if(count($listaAsistencias)==0)
                            {
                               $mensaje = '<div class="alert alert-warning text-center" role="alert">ESTE CURSO NO HA REGISTRADO SU ASISTENCIA PORQUE NO TIENE ESTUDIANTES</div>';
                            }else{
                                 ini_set('date.timezone','America/Bogota'); 
                                $fechaActual = strtotime(date("d-m-Y"));
                                $fechaAsistencia= strtotime($listaFechaAsistencia[0]['fechaAsistencia']);
                                
                                
                                
                                $cuerpoTablaListaEstudiantes = '';
                                $num=1;
                                $i=0;
                                foreach ($listaAsistencias as $valueAsistencia) {

//                                   if($fechaActual==$fechaAsistencia)
//                                   {
                                        if($valueAsistencia['estadoAsistenciaTomada']==0)
                                        {
                                            $optionAsistencia = '<option selected value="0">No asistió</option>';
                                            $optionAsistencia = $optionAsistencia.'<option value="1">Sí asistió</option>';
                                        }else{
                                            $optionAsistencia = '<option value="0">No asistió</option>';
                                            $optionAsistencia = $optionAsistencia.'<option selected value="1">Sí asistió</option>';
                                        }

                                        $idAsistenciaEncriptado = $objMetodos->encriptar( $valueAsistencia['idAsistencia']);
                                        $selectAsistencia = '<select onchange="cambiarAsistenciaHoy(\''.$idAsistenciaEncriptado.'\','.$i.')" id="selectAsistenciaDiaria" name="selectAsistenciaDiaria">
                                                            '.$optionAsistencia.'</select>';
//                                   }
//                                   else{
//                                        $selectAsistencia = '<small style="color:green">Sí</small>';
//                                        if($valueAsistencia['estadoAsistenciaTomada']==0)
//                                           $selectAsistencia='<small style="color:red" small>No</small>';                                       
//                                   }
                                
                                       
                                   

                                    $cuerpoTablaListaEstudiantes = $cuerpoTablaListaEstudiantes.
                                            '<tr id="numerofila'.$i.'" role="row" class="odd"><td class="text-center">'.$num.'</td>
                                            <td class="text-center">'.$valueAsistencia['primerApellido'].' '.$valueAsistencia['segundoApellido'].' '.$valueAsistencia['primerNombre'].' '.$valueAsistencia['segundoNombre'].' </td>
                                            <td class="text-center">'.$selectAsistencia.'</td></tr>';

                                    $num++;
                                    $i++;
                                    }


                                    $tabla = '<div class="box">
                                                <div class="box-header">
                                                  <h3 class="box-title">Tabla de Asistencias</h3>
                                                </div>
                                                <!-- /.box-header -->
                                                <div class="box-body">
                                                  <table id="example2" class="table table-bordered table-hover">
                                                    <thead role="row">
                                                    <tr>
                                                     <th class="text-center">N</th> 
                                                     <th class="text-center"">Apellidos y Nombres</th>
                                                     <th class="text-center">Asistencia</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    '.$cuerpoTablaListaEstudiantes.'
                                                    </tbody>
                                                  </table>
                                                </div>
                                              </div>';

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
    
    
    public function  asistenciahoyAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 16);
            $objMetodos = new Metodos();
            $objAsistencia = new Asistencia($this->dbAdapter);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    
                    $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                    $idAsistenciaEncriptado = $post['idAsistenciaEncriptado'];
                    $numeroFila = $post['Nfila'];
                    if($idAsistenciaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">ÍNDICE DE LA ASISTENCIA VACÍO</div>';
                    }else if($numeroFila==NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NUMERO DE FILA VACÍO</div>';
                    }else{
                        $idAsistencia = $objMetodos->desencriptar($idAsistenciaEncriptado);
                        $listaAsistencia = $objAsistencia->FiltrarAsistencia($idAsistencia);
                        if(count($listaAsistencia)==0)
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA ASISTENCIA EN EL SISTEMA</div>';
                        else{       
//                                ini_set('date.timezone','America/Bogota'); 
//                                $hoy = getdate();
//                                $fechaActual = $hoy['year']."-".$hoy['mon']."-".$hoy['mday'];
                                $nuevoEstadoAsistencia=0;
                                if($listaAsistencia[0]['estadoAsistenciaTomada']==0)
                                    $nuevoEstadoAsistencia=1;
                                
                                $resultado=$objAsistencia->ActualizarEstadoAsistencia($idAsistencia, $nuevoEstadoAsistencia);
                                
                                if(count($resultado)==0)
                                   $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR Y NO SE PUDO ACTUALIZAR LA ASISTENCIA. INTENTE MÁS TARDE.</div>';
                                else{
                                    
                                    if($resultado[0]['estadoAsistenciaTomada']==0)
                                    {
                                        $optionAsistencia = '<option selected value="0">No asistió</option>';
                                        $optionAsistencia = $optionAsistencia.'<option value="1">Sí asistió</option>';
                                    }else{
                                        $optionAsistencia = '<option value="0">No asistió</option>';
                                        $optionAsistencia = $optionAsistencia.'<option selected value="1">Sí asistió</option>';
                                    }
                                
                                    $idAsistenciaEncriptado = $objMetodos->encriptar( $resultado[0]['idAsistencia']);
                                    $selectAsistencia = '<select onchange="cambiarAsistenciaHoy(\''.$idAsistenciaEncriptado.'\','.$numeroFila.')" id="selectAsistenciaDiaria" name="selectAsistenciaDiaria">
                                        '.$optionAsistencia.'</select>';
                                    $num=$numeroFila+1;
                                    $nuevaFila=  '<td class="text-center">'.$num.'</td>
                                            <td class="text-center">'.$resultado[0]['primerApellido'].' '.$resultado[0]['segundoApellido'].' '.$resultado[0]['primerNombre'].' '.$resultado[0]['segundoNombre'].' </td>
                                            <td class="text-center">'.$selectAsistencia.'</td>';

                                    $mensaje = '';
                                    $validar = TRUE;
                                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar, 'nuevafila'=>$nuevaFila, 'numeroFila'=>$numeroFila));
                                } 
                            }
                        }
                    }                   
                }                    
            }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }

    
    
}