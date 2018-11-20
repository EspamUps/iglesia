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
use Nel\Metodos\PlantillaPdf;
use Nel\Metodos\MetodosControladores;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Matricula;
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\Docentes;
use Nel\Modelo\Entity\Cursos;
use Nel\Modelo\Entity\HoraHorario;
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\Periodos;
use Nel\Modelo\Entity\Asistencia;
use Nel\Modelo\Entity\FechaAsistencia;
use Nel\Modelo\Entity\RangoAsistencia;
use Nel\Modelo\Entity\HorarioCurso;
use Nel\Modelo\Entity\ConfigurarCurso;
use Nel\Modelo\Entity\NombreIglesia;
use Nel\Modelo\Entity\AdjuntoMatricula;
use Nel\Modelo\Entity\CargosAdministrativos;
use Nel\Modelo\Entity\Administrativos;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class CertificadosCursosController extends AbstractActionController
{
    public $dbAdapter;
  
    
    
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 19);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 19, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
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

                                $div = '<label for="selectConfigurarCurso">HORARIO</label>
                                        <select onchange="filtrarHorarioCursoSeleccionado();obtenerMatriculas();"  id="selectConfigurarCurso" name="selectConfigurarCurso" class="form-control">'.$optionHorarios.'</select>';
                                $mensaje ='';
                                $validar = TRUE;    
                                return new JsonModel(array('div'=>$div,'mensaje'=>$mensaje,'validar'=>$validar));
                            }
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 19);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 19, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objCurso = new Cursos($this->dbAdapter);
                        $objHorario = new Horario($this->dbAdapter);
                        $objHoraHorario = new HoraHorario($this->dbAdapter);
                        $objHorarioCurso = new HorarioCurso($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $objMatricula = new Matricula($this->dbAdapter);
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
                                
                                $div = '<div class="box box-solid form-group" style="border-style: solid;border: 1px solid #d2d6de;">
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
                                                <th><label for="nombres">FECHA DE INICIO DE MATRÍCULAS</label></th>
                                                <td>'.$listaConfigurarCurso[0]['fechaInicioMatricula'].'</td>
                                            </tr>
                                            <tr>
                                                <th><label for="nombres">FECHA DE FIN DE MATRÍCULAS</label></th>
                                                <td>'.$listaConfigurarCurso[0]['fechaFinMatricula'].'</td>
                                            </tr>
                                        <tr>
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
                                </div>
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
                                $mensaje = '';
                                $validar = TRUE;
                                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla,'datosGenerales'=>$div));
                                
                            }
                        }
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    public function obtenermatriculasAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 19);
            $objMetodos = new Metodos();
            $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
            $objMatricula = new Matricula($this->dbAdapter);
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
                    $idConfigurarCursoEncriptado = $post['id'];
                    
                    if(empty($idConfigurarCursoEncriptado) || $idConfigurarCursoEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">ÍNDICE DEL CONFIGURAR CURSO VACÍO</div>';
                    }else{
                        $idConfigurarCurso = $objMetodos->desencriptar($idConfigurarCursoEncriptado);
                        $listaConfCurso = $objConfigurarCurso->FiltrarConfigurarCurso($idConfigurarCurso);
                        if(count($listaConfCurso)==0)
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CONFIGURAR CURSO EN EL SISTEMA</div>';
                        else{                            
                            $listaMatricula = $objMatricula->FiltrarMatriculaPorConfigurarCurso($idConfigurarCurso);
                            if(count($listaMatricula)==0)
                            {
                               $mensaje = '<div class="alert alert-warning text-center" role="alert">ESTE CURSO NO TIENE ESTUDIANTES MATRICULADOS</div>';
                            }else{
                                $tabla = $this->CargarTablaMatriculasAction($listaConfCurso,$listaMatricula, 0, count($listaMatricula));
                                
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 19);
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
                    $idPeriodoEncriptado = $post['id'];
                    
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
                                
                                $select = '<label for="selectCurso">CURSO</label><select onchange="filtrarlistaHorariosCursoSeleccionado();"  id="selectCurso" name="selectCurso" class="form-control">'.$optionCurso.'</select>';
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
    
    
     function CargarTablaMatriculasAction($listaConfigurarCurso,$listaMatriculas, $i, $j)
    {
         
        $objAsistencia = new Asistencia($this->dbAdapter);
        $objMetodos = new Metodos();
        $array1 = array();
        
        $porcentajeAsistenciaPermitido = $listaConfigurarCurso[0]['porcentaje']/100;
        
        ini_set('date.timezone','America/Bogota'); 
        $fechaActual = strtotime(date("d-m-Y"));
        $fechaInicioMat = strtotime($listaConfigurarCurso[0]['fechaInicioMatricula']);
        $fechaFinMat = strtotime($listaConfigurarCurso[0]['fechaFinMatricula']); 
        foreach ($listaMatriculas as $value) {
            $fechaInicioClases = strtotime($value['fechaInicio']);
            $fechaFinClases =strtotime($value['fechaFin']);
            $idMatricula=$value['idMatricula'];
            
           
            
            $idMatriculaEncriptado = $objMetodos->encriptar($idMatricula);
                       
            
            $identificacionEstudiante = $value['identificacion'];
            $nombresEstudiante = $value['primerNombre'].' '.$value['segundoNombre'];
            $apellidosEstudiante = $value['primerApellido'].' '.$value['segundoApellido'];
            $estadoMatricula = $value['estadoMatricula'];
            $botonimprimir='';
            $porcentajeAsistenciaTrue=0;
            
            if($estadoMatricula==1)
            {     
                
                if($fechaActual>=$fechaInicioMat && $fechaActual<=$fechaFinMat)
                $estadoCurso= 'Periodo de matrículas';
                else if($fechaActual>= $fechaInicioClases && $fechaActual<=$fechaFinClases)
                {   
                    $estadoCurso='En clases';
                    if($value['aprobado']==0)
                        $estadoCurso='Reprobado';
                } else  if($fechaActual>$fechaFinClases)
                {
                    $listaAsistencias = $objAsistencia->FiltrarAsistenciaPorMatricula($idMatricula, 1);
                    $totalAsistencia = count($listaAsistencias);
                    if($totalAsistencia>0){
                        $asistenciasTrue = 0;
                        foreach ($listaAsistencias as $valueAsistencia) {
                            if($valueAsistencia['estadoAsistenciaTomada']==1)
                            $asistenciasTrue=$asistenciasTrue+1;
                        }

                        $porcentajeAsistenciaTrue=$asistenciasTrue/$totalAsistencia;
                    }

                    if($porcentajeAsistenciaTrue>=$porcentajeAsistenciaPermitido){
                         $botonimprimir =' <a title="IMPRIMIR CERTIFICADO DE APROBACIÓN DE '.$value['primerNombre'].' '.$value['primerApellido'].' " class="btn bg-purple btn-sm btn-flat"  target="_blank" href="'.$this->getRequest()->getBaseUrl().'/certificadoscursos/generarcomprobante?id='.urlencode($idMatriculaEncriptado).'"><i class="fa  fa-file-pdf-o"></i></a> ';  
                         $estadoCurso = 'Aprobado';
                    }else
                          $estadoCurso ='Reprobado';                    
                }
                else
                {
                    $botonDescargarCertificadoCurso = '';
                    $estadoCurso='Esperando inicio de clases';
                }
            }
            else
            {
               $estadoCurso ='Reprobado';
              
            }
            

            $botones = $botonimprimir;

            $porcentaje = round($porcentajeAsistenciaTrue*100,2);
            $porcentajePresentar = ''.$porcentaje.'%';
            $array1[$i] = array(
                '_j'=>$j,
                '_idMatriculaEncriptado'=>$idMatriculaEncriptado,                
                'identificacion'=>$identificacionEstudiante,
                'nombres'=>$nombresEstudiante,
                'apellidos'=>$apellidosEstudiante,
                'estadoCurso'=>$estadoCurso,
                'porcentajeAsistencia'=>$porcentajePresentar,
                'opciones1'=>$botones
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    
    
   
    
    
   
   
    
     public function generarcomprobanteAction()
    {
        $this->layout("layout/administrador");
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $sesionUsuario = new Container('sesionparroquia');
        $idIglesia = $sesionUsuario->offsetGet('idIglesia');
        
        $objMetodos = new Metodos();
        $objConfCurso = new ConfigurarCurso($this->dbAdapter);
        $objCurso = new Cursos($this->dbAdapter);
        $objNombreIglesia = new NombreIglesia($this->dbAdapter);
        $objHorarioCurso = new HorarioCurso($this->dbAdapter);
        $objPeriodo = new Periodos($this->dbAdapter);
        $objPersona = new Persona($this->dbAdapter);
        $listaIglesia = $objNombreIglesia->FiltrarNombreIglesiaEstado($idIglesia, 1);
        $direccionIglesia = $sesionUsuario->offsetGet('direccionIgleisia');
        $idMatriculaEncriptado = $this->params()->fromQuery('id');

       
        $idMatricula = $objMetodos->desencriptar($idMatriculaEncriptado);
        $objMat = new Matricula($this->dbAdapter);
        $resultado = $objMat->FiltrarMatricula($idMatricula);
        $listaConfCurso = $objConfCurso->FiltrarConfigurarCurso($resultado[0]['idConfigurarCurso']);
        $siguienteCurso = $objCurso->FiltrarCursoSiguiente($listaConfCurso[0]['nivelCurso'], 1);
         $listaPeriodo = $objPeriodo->FiltrarPeriodo($listaConfCurso[0]['idPeriodo']);
        
        $objAdministrativos = new Administrativos($this->dbAdapter);
        $cuerpoTablaAdm ='';
        //firma del catequista
        $cuerpoTablaAdm = $cuerpoTablaAdm.' 
                            <td>_________________________________________<br>
                            '.$listaConfCurso[0]['primerNombre'].' '.$listaConfCurso[0]['segundoNombre'].' '.$listaConfCurso[0]['primerApellido'].' '.$listaConfCurso[0]['segundoApellido'].'
                            <br>CATEQUISTA</td>
                             ';
        //el id 1 pertenece al parroco
        $listaAdministrativo = $objAdministrativos->FiltrarAdministrativosPorIdentificadorCargo(1);
            if(count($listaAdministrativo)>0)
            {
                $listaPersona = $objPersona->FiltrarPersona($listaAdministrativo[0]['idPersona']);
                $cuerpoTablaAdm = $cuerpoTablaAdm.'
                            <td>_________________________________________<br>
                            '.$listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'].' '.$listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'].'
                            <br>'.$listaAdministrativo[0]['descripcion'].'</td>
                             ';
            }
             
        ini_set('date.timezone','America/Bogota'); 
        $hoy = getdate();
        $mes =date("m");
	$fechaActual = $hoy['year']."-".$mes."-".$hoy['mday'];
         $fechaHoy = $objMetodos->obtenerFechaEnLetraSinHora($fechaActual);
        
        if(count($siguienteCurso)>0)
        $tabla = '<br><div class="box box-success">
            <div  style="text-align:center; width:100%; color:#777" >
              <img style="width:10%" src="'.$this->getRequest()->getBaseUrl().'/public/librerias/images/pagina/logoiglesia.png" >
             <br> <label style="font-size:24px" class="box-title ">'.$listaIglesia[0]['nombreIglesia'].'<br>'.$direccionIglesia.'</label>
             <br> <label>Sistema Web de Gestión Parroquial</label>
            </div>
            <hr><br>
            <div  style="text-align:center; width:100%;" class="box-body text-center"   >
              <!-- Minimal style -->
             
              <h2><u>PASE DE NIVEL</u></h2>
              
              <label  style="text-align:center; width:100%; font-size:20px;" >NOMBRE: '.$resultado[0]['primerNombre'].' '.$resultado[0]['segundoNombre'].' '.$resultado[0]['primerApellido'].' '.$resultado[0]['segundoApellido'].'</label>
              <br>
                <label style="text-align:center; width:100%;font-size:20px;" >ES PROMOVIDA/O A: '.$siguienteCurso[0]['nombreCurso'].'</label>
               <br><br><br>
              <p> Dado y firmado el '.$fechaHoy.'</p>
              
            </div>
            <!-- /.box-body -->
            <div class="box-footer text-center"  style="text-align:center; width:100%">
           <table class="table text-center" style="width:100%; padding-top:5%" > 
               <tbody><tr>'.$cuerpoTablaAdm.'</tr></tbody>                               
            </table>
            <br>
             <p> <i> Comprobante de matrícula generado automáticamente por el Sistema Web de Gestión Parroquial.</i> </p>
            </div>
          </div>';
        
        
	$array =  array(
            'tabla'=>$tabla            
        );
        
        
        
	return new ViewModel($array);

    }
 
}