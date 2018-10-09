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
                            if(count($listaMatriculados)==0)
                            {
                                $small= '<small class="label label-default"><i class="fa fa-close"></i> curso sin estudiantes matriculados</small>';
                                $smallhoy ='  <small class="label label-default"><i class="fa fa-close"></i> curso sin estudiantes matriculados</small>
                                                ';
                            }else
                            {
                                if(count($listaFechasAsistencia)>0)
                                { 
                                   $small= '<small class="label label-success"><i class="fa fa-check"></i> realizado</small>';
                                    $smallhoy ='  <small class="label label-default"><i class="fa fa-close"></i> hoy no clases</small>
                                                ';
                                    foreach ($listaFechasAsistencia as $valueFechasAsistencia)
                                    {
                                        $fechaAsistencia = strtotime($valueFechasAsistencia['fechaAsistencia']);
                                        if($fechaAsistencia==$fechaActual)
                                        {
                                            $listaFechasAsistenciaTomada= $objFechaAsistencia->FiltrarFechaAsistenciaPorFechaAsistencia($listaFechasAsistencia[0]['idFechaAsistencia'], 1);

                                            if(count($listaFechasAsistenciaTomada)==0){
                                                $idFechaAsistenciaEncriptado = $objMetodos->encriptar($valueFechasAsistencia['idFechaAsistencia']);
                                                $smallhoy ='  <small class="label label-danger"><i class="fa fa-clock-o"></i> pendiente</small>
                                                 <button type="button" onClick="GenerarAsistenciaHoy(\''.$idFechaAsistenciaEncriptado.'\')" class="btn  btn-success  btn-xs"><i class="fa fa-check"></i> TOMAR ASISTENCIA
                                               </button>';
                                            }else
                                             $smallhoy ='  <small class="label label-success"><i class="fa fa-check"></i> realizado</small>
                                                ';
                                        }
                                    }  
                                }else
                                 {                              

                                    if($fechaActual>= $fechaInicioClases)
                                    {
                                        $small ='  <small class="label label-danger"><i class="fa fa-clock-o"></i> pendiente</small>
                                          <button type="button" onClick="GenerarAsistencia()" class="btn  btn-success  btn-xs"><i class="fa fa-check"></i> GENERAR ASISTENCIA
                                           </button>';
                                        $smallhoy ='  <small class="label label-danger"><i class="fa fa-clock-o"></i> pendiente </small>';
                                    }else
                                    {
                                        $small= ' <small class="label label-default"><i class="fa fa-close"></i> deshabilitado hasta que finalicen las matrículas</small>';
                                        $smallhoy='  <small class="label label-default"><i class="fa fa-close"></i> deshabilitado hasta que inicien las clases</small>';
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
                                    <div class="form-group" style="padding-right:10%; padding-left:10%;"> 
                                         <tr class="text-center">
                                            <td class="text-center">ASISTENCIA DE HOY:</td>
                                            <td>'.$smallhoy.'</td>
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
                            return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla,'datosGenerales'=>$divInfoGeneral, 'tablaAsistencia'=>$tablaAsistencia));

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
                               
                            
                                $listaHorarioRegistrado =$objHorarioCurso->FiltrarHorarioCursoPorConfiguCurso($idConfigurarCurso);
                              
                                $fechaInicioClases =$listaConfigurarCurso[0]['fechaInicio'];
                                $fechaFinClases = $listaConfigurarCurso[0]['fechaFin'];
                                for($i=$fechaInicioClases;$i<=$fechaFinClases;$i = date("Y-m-d", strtotime($i ."+ 1 days"))){

                                   $fechaAsistencia= strtotime($i);
                                   $numeroDia =date("w",$fechaAsistencia);
                                   foreach ($listaHorarioRegistrado as $valueDiaHorarioR)
                                   {
                                       if($valueDiaHorarioR['identificadorDia']==$numeroDia)
                                           $resultado = $objFechaAsistencia->IngresarFechasAsistencia ($idConfigurarCurso, $i, 1);
                                   }
                                }
                            
                                
                                $mensaje = '<div class="alert alert-success text-center" role="alert">SE REGISTRARON LAS FECHAS PARA LA ASISTENCIA DE FORMA CORRECTA</div>';
                                $validar = TRUE;
                                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
                                }
                            }   
                        }
                    }                    
                }
            }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function generarasistenciahoyAction()
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
            $objAsistencia = new Asistencia($this->dbAdapter);
            $objMatricula= new Matricula($this->dbAdapter);
            $objFechaAsistencia = new FechaAsistencia($this->dbAdapter);
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
                        $idFechaAsistenciaEncriptado = $post['idFechaAsistencia'];

                        if(empty($idFechaAsistenciaEncriptado) || $idFechaAsistenciaEncriptado == NULL){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ÍNDICE DEL REGISTRO FECHA ASISTENCIA ESTÁ VACÍO</div>';
                        }else{
                            $idFechaAsistencia = $objMetodos->desencriptar($idFechaAsistenciaEncriptado);
                            $listaFechaAsistencia = $objFechaAsistencia->FiltrarFechaAsistencia($idFechaAsistencia, 1);
                            if(count($listaFechaAsistencia) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA SIDO CONFIGURADO LA FECHA DE ESTE DÍA</div>';
                            }else{
                                $idConfigurarCurso = $listaFechaAsistencia[0]['idConfigurarCurso'];
                                
                                $listaMatriculadosEnElCurso= $objMatricula->FiltrarMatriculaPorConfigurarCursoYEstado($idConfigurarCurso,1);
                                if(count($listaMatriculadosEnElCurso)==0)
                                  $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HAY MATRICULADOS EN ESTE CURSO</div>';
                                else{
                                    
                                    ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaAsistencia = $hoy['year']."-".$hoy['mon']."-".$hoy['mday'];
                                        
                                    
                                    foreach ($listaMatriculadosEnElCurso as $valueMatriculado) {
                                        $resultado = $objAsistencia->IngresarAsistenciaHoy($idFechaAsistencia, $valueMatriculado['idMatricula'], 1, $fechaAsistencia, 1);
                                    }
                                    
                                    $mensaje = '<div class="alert alert-success text-center" role="alert">ASISTENCIA DE HOY, INGRESADA CORRECTAMENTE</div>';
                                    $validar = TRUE;
                                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
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