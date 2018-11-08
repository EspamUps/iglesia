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
use Nel\Modelo\Entity\Sacerdotes;
use Nel\Modelo\Entity\Cursos;
use Nel\Modelo\Entity\HoraHorario;
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\Periodos;
use Nel\Modelo\Entity\Adjunto;
use Nel\Modelo\Entity\AdjuntoMatricula;
use Nel\Modelo\Entity\HorarioCurso;
use Nel\Modelo\Entity\ConfigurarCurso;
use Nel\Modelo\Entity\NombreIglesia;
use Nel\Modelo\Entity\DireccionIglesia;
use Nel\Modelo\Entity\CargosAdministrativos;
use Nel\Modelo\Entity\Administrativos;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class MatriculasController extends AbstractActionController
{
    public $dbAdapter;
  
    
    
    public function filtrarpersonaporidentificacionAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = FALSE;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }
        else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objMatricula = new Matricula($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $objDocente = new Docentes($this->dbAdapter);
                        $objSacerdote = new Sacerdotes($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                         $identificacion = trim($post['identificacion']);
                         $idConfigurarCursoEncriptado = $post['idConfCurso'];
                        
                        if(strlen($identificacion) > 10){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IDENTIFICACIÓN NO DEBE TENER MÁS DE 10 DÍGITOS</div>';
                        } if(empty($idConfigurarCursoEncriptado) || $idConfigurarCursoEncriptado==null){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL CONFIGURAR CURSO</div>';
                        }else{
                            $idConfigurarCurso = $objMetodos->desencriptar($idConfigurarCursoEncriptado);
                            
                            $listaConfigurarCurso = $objConfigurarCurso->FiltrarConfigurarCurso($idConfigurarCurso);
                            if(count($listaConfigurarCurso) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO ESTÁ CONFIGURADO</div>';
                            }else {
                                $listaPersona = $objPersona->FiltrarPersonaPorIdentificacion($identificacion);
                                if(count($listaPersona) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'. PRIMERO DEBE REGISTRARLA EN EL MÓDULO PERSONAS.</div>';
                                }else
                                {
                                    $inputIdPersona = '';
                                    $matricular=false;
                                    $idPersona= $listaPersona[0]['idPersona'];
                                    $idPersonaEncriptado = $objMetodos->encriptar($idPersona);
                                    
                                    $EsSacerdote=count($objSacerdote->FiltrarSacerdotePorPersona($idPersona));
                                    $EsDocente=count($objDocente->FiltrarDocentePorPersona($idPersona));
                                    if($EsDocente>0)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' ESTÁ REGISTRADA COMO DOCENTE, POR LO TANTO NO PUEDE SER MATRICULADA COMO ESTUDIANTE</div>';
                                    else if ($EsSacerdote>0)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' ESTÁ REGISTRADA COMO SACERDOTE, POR LO TANTO NO PUEDE SER MATRICULADA COMO ESTUDIANTE</div>';
                                    else{
                                        
                                        $listaMatricula = $objMatricula->FiltrarMatriculaPorPersona($idPersona);
                                        $listaMatriculadosTotales = $objMatricula->FiltrarMatriculaPorConfigurarCursoYEstado($idConfigurarCurso, 1); 
                                        $cuposDisponibles = $listaConfigurarCurso[0]['cupos']-count($listaMatriculadosTotales);
                                        
                                        
                                       if($cuposDisponibles<=0)
                                           $mensaje = '<div class="alert alert-warning text-center" role="alert">NO HAY CUPOS DISPONIBLES EN ESTE CURSO </div>';
                                       else {            
                                        
                                            if(count($listaMatricula)>0)
                                            {
                                                ini_set('date.timezone','America/Bogota'); 
                                                $hoy = getdate();
                                                $mes =date("m");
                                                $fechaActual = $hoy['year']."-".$mes."-".$hoy['mday'];
                                                $nombreCursoActual= $listaMatricula[0]['nombreCurso'];
                                                $nivelCursoActual=$listaMatricula[0]['nivelCurso'];
                                                $estadoCursoActual='No aprobado';
                                                $estadoMatricula='Cancelada';

                                                if($listaMatricula[0]['aprobado']==1)
                                                    $estadoCursoActual='Aprobado';

                                                if($idConfigurarCurso==$listaMatricula[0]['idConfigurarCurso'])
                                                {
                                                    if($listaMatricula[0]['estadoMatricula']==0){
                                                        $mensaje = '<div class="alert alert-warning text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' YA FUE MATRICULADA EN ESTE CURSO Y HORARIO PERO CANCELÓ SU MATRÍCULA. </div>';

                                                    }
                                                    else{
                                                        $estadoMatricula='Activa';
                                                        $mensaje = '<div class="alert alert-success text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' YA HA SIDO MATRICULADA EN ESTE CURSO. </div>';
                                                    }                                             
                                                }
                                                else{
                                                    if($listaMatricula[0]['nivelCurso']==$listaConfigurarCurso[0]['nivelCurso'])
                                                       if($listaMatricula[0]['aprobado']==1)
                                                       {
                                                           $mensaje = '<div class="alert alert-warning text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' YA HA SIDO MATRICULADA Y HA APROBADO ESTE CURSO CON ANTERIORIDAD. </div>';
                                                           $estadoMatricula='Activa';                                                       
                                                       }
                                                       else if($listaMatricula[0]['aprobado']==0 && $listaMatricula[0]['fechaFin']>$fechaActual && $listaMatricula[0]['estadoMatricula']==1)
                                                       {
                                                            $estadoMatricula='Activa';
                                                            $mensaje = '<div class="alert alert-warning text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' YA HA SIDO MATRICULADA EN UN CURSO SIMILAR A ESTE PERO EN OTRO HORARIO. </div>';
                                                       }else {
                                                            $mensaje = '<div class="alert alert-success text-center" role="alert">PARA FINALIZAR EL PROCESO, DE CLIC EN EL BOTÓN MATRICULAR</div>';
                                                            $inputIdPersona = '<input type="hidden" id="idPersonaEncriptado" name="idPersonaEncriptado" value="'.$idPersonaEncriptado.'">';                                                           
                                                            $matricular=true;
                                                        }
                                                    else if ($listaMatricula[0]['nivelCurso']>$listaConfigurarCurso[0]['nivelCurso'])
                                                        if($listaMatricula[0]['aprobado']==1 )
                                                        {
                                                            $estadoMatricula='Activa';
                                                            $mensaje = '<div class="alert alert-warning text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' HA SIDO MATRICULADA Y HA APROBADO UN CURSO SUPERIOR A ESTE. </div>';
                                                        }
                                                        else{
                                                            if($listaMatricula[0]['estadoMatricula']==1)
                                                            {
                                                                $estadoMatricula='Activa';
                                                                $mensaje = '<div class="alert alert-warning text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' YA APROBÓ ESTE NIVEL Y TIENE UNA MATRÍCULA ACTIVA EN UN CURSO DE NIVEL SUPERIOR. </div>';                            
                                                            }else{
                                                                $mensaje = ' '.$listaMatricula[0]['estadoMatricula'].' <div class="alert alert-warning text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' YA APROBÓ ESTE NIVEL Y DEBE VOLVER A MATRICULARSE EN UN CURSO DE NIVEL SUPERIOR PORQUE TIENE UNA MATRÍCULA CANCELADA.</div>';                            
                                                            }
                                                        }
                                                    else 
                                                        if($listaMatricula[0]['aprobado']==0 &&  $listaMatricula[0]['fechaFin']>$fechaActual && $listaMatricula[0]['estadoMatricula']==1){
                                                             $mensaje = '<div class="alert alert-warning text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' HA SIDO MATRICULADA EN UN CURSO INFERIOR Y DEBE APROBARLO PRIMERO. </div>';
                                                             $estadoMatricula='Activa';
                                                            }
                                                        else{  
                                                            if($listaMatricula[0]['estadoMatricula']==1)
                                                            {
                                                                $estadoMatricula='Activa';
                                                                $mensaje = '<div class="alert alert-success text-center" role="alert">PARA FINALIZAR EL PROCESO, DE CLIC EN EL BOTÓN MATRICULAR</div>';                            
                                                                $matricular=true;
                                                                $inputIdPersona = '<input type="hidden" id="idPersonaEncriptado" name="idPersonaEncriptado" value="'.$idPersonaEncriptado.'">';
                                                            }else
                                                            {
                                                                $mensaje = '<div class="alert alert-warning text-center" role="alert">LA PERSONA CON IDENTIFICACIÓN '.$identificacion.' HA REGISTRADO UNA MATRÍCULA CANCELADA, POR LO TANTO DEBE VOLVER A SER MATRICULADA Y APROBAR UN NIVEL INFERIOR A ESTE. </div>';
                                                            }

                                                        }                                                 
                                                    }
                                            }else{

                                               $nivelCursoActual ='No tiene cursos registrados en esta iglesia';
                                               $estadoCursoActual='Sin estado';
                                               $nombreCursoActual = 'Desconocido';
                                               $mensaje = '<div class="alert alert-success text-center" role="alert">PARA FINALIZAR EL PROCESO, DE CLIC EN EL BOTÓN MATRICULAR</div>';
                                               $matricular=true;
                                               $inputIdPersona = '<input type="hidden" id="idPersonaEncriptado" name="idPersonaEncriptado" value="'.$idPersonaEncriptado.'">';
                                               $estadoMatricula='No registrado';
                                               
                                            }

                                            $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                                            $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];



                                            $tabla = '<div><h3>INFORMACIÓN ACTUAL DEL ESTUDIANTE</h3><br></div>
                                                    '.$inputIdPersona.'
                                                    <div class="table-responsive"><table class="table">
                                                    <thead> 
                                                        <tr>
                                                            <th><label for="nombres">NOMBRES Y APELLIDOS</label></th>
                                                            <td>'.$nombres.'</td>
                                                            <td>'.$apellidos.'</td>
                                                        </tr>
                                                        <tr>
                                                            <th><label for="curso">INFORMACIÓN ÚLTIMO CURSO EN EL QUE FUE MATRICULADO/A</label></th>
                                                            <td>CURSO:<span style="background-color:#ffa50070">'.$nombreCursoActual.'</span></td>
                                                            <td><span style="background-color:#3c8dbc42">ESTADO: '.$estadoCursoActual.'</span> <span style="background-color:#00a65a42"> NIVEL: '.$nivelCursoActual.'</span>  <span style="background-color:#a6520042"> ESTADO MATRÍCULA: '.$estadoMatricula.'</span></td>
                                                        </tr>
                                                    </thead>
                                                </table></div>';

                                            $validar = TRUE;
                                            if($matricular==false){                                               
                                                    return new JsonModel(array('tabla'=>$tabla,'mensaje'=>$mensaje,'validar'=>$validar));
                                            }else{
                                                    $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarMatricula" type="submit" class="btn btn-primary pull-right"><i class="fa fa-check"></i>MATRICULAR</button>';
                                                    return new JsonModel(array('tabla'=>$tabla,'mensaje'=>$mensaje,'validar'=>$validar,'idPersonaEncriptado'=>$idPersonaEncriptado, 'btnMatricular'=>$botonGuardar));

                                            }
                                       }
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 3);
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
                                        <select onchange="filtrarHorarioCursoSeleccionado();obtenerMatriculas();cargarFormularioIngresarMatricula();"  id="selectConfigurarCurso" name="selectConfigurarCurso" class="form-control">'.$optionHorarios.'</select>';
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 3);
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
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
         
       
        $objAdjuntoMat = new AdjuntoMatricula($this->dbAdapter);
        $objMetodos = new Metodos();
        $array1 = array();
        ini_set('date.timezone','America/Bogota'); 
        $fechaActual = strtotime(date("d-m-Y"));
        $fechaInicioMat = strtotime($listaConfigurarCurso[0]['fechaInicioMatricula']);
        $fechaFinMat = strtotime($listaConfigurarCurso[0]['fechaFinMatricula']); 
        foreach ($listaMatriculas as $value) {
            $idMatricula=$value['idMatricula'];
            $idMatriculaEncriptado = $objMetodos->encriptar($idMatricula);
            
            $botonCambiarEstadoMatricula = '';
                        
            
            $identificacionEstudiante = $value['identificacion'];
            $nombresEstudiante = $value['primerNombre'].' '.$value['segundoNombre'];
            $apellidosEstudiante = $value['primerApellido'].' '.$value['segundoApellido'];
            $fechaMatricula=$value['fechaMatricula'];
            $estadoMatricula = $value['estadoMatricula'];
            
            $botonimprimir='';
            if($estadoMatricula==1)
            {     
                $botonimprimir =' <a title="IMPRIMIR COMPROBANTE DE MATRÍCULA DE '.$value['primerNombre'].' '.$value['primerApellido'].' " class="btn bg-purple btn-sm btn-flat"  target="_blank" href="'.$this->getRequest()->getBaseUrl().'/matriculas/generarcomprobante?id='.urlencode($idMatriculaEncriptado).'"><i class="fa  fa-file-pdf-o"></i></a> ';  
                $labelEstadoMatricula= '<label style="background-color:#b1ffa1" class="form-control" >Habilitada</label>';   
                if(($fechaActual>=$fechaInicioMat)&&($fechaActual<$fechaFinMat))
                {
                    $botonCambiarEstadoMatricula = '<button data-target="#modalModificarEstadoMatricula" data-toggle="modal"  id="btnModificarEstadoMatricula'.$i.'" title="CANCELAR MATRÍCULA DE '.$value['primerNombre'].' '.$value['primerApellido'].'" onclick="obtenerFormularioModificarEstadoMatricula(\''.$idMatriculaEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                }
                else
                    $botonCambiarEstadoMatricula = '<label title="ESTUDIANTE '.$value['primerNombre'].' '.$value['primerApellido'].' MATRICULADO CORRECTAMENTE"  class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i></label>';
            }
            else
            {
               $docAdjunto = $objAdjuntoMat->FiltrarAdjuntoPorIdMatriculaYTipoAdjunto($idMatricula, 2);
               
               $botonCambiarEstadoMatricula = '<a class="btn btn-primary btn-sm btn-flat"  title="DESCARGAR COMPROBANTE DE CANCELACIÓN DE MATRÍCULA" href=" '.$this->getRequest()->getBaseUrl().$docAdjunto[0]['rutaAdjunto'].'"  download="'.$docAdjunto[0]['nombreAdjunto'].'"><i class="fa fa-download"></i></a>';
               $labelEstadoMatricula= '<label style="background-color:#ddd" class="form-control"  >Deshabilitada</label>';

            }
            
            $fechaInicioClases = strtotime($value['fechaInicio']);
            $fechaFinClases =strtotime($value['fechaFin']);
            
            if($fechaActual>=$fechaInicioMat && $fechaActual<=$fechaFinMat)
            $estadoCurso= 'Periodo de matrículas';
            else if($fechaActual>= $fechaInicioClases && $fechaActual<=$fechaFinClases)
            {   
                $estadoCurso='En clases';
                if($value['aprobado']==0)
                    $estadoCurso='Reprobado';
            } else
                $estadoCurso='Esperando inicio clases';
            
            $botones = $botonCambiarEstadoMatricula.''.$botonimprimir;

             
            $array1[$i] = array(
                '_j'=>$j,
                '_idMatriculaEncriptado'=>$idMatriculaEncriptado,                
                'identificacion'=>$identificacionEstudiante,
                'nombres'=>$nombresEstudiante,
                'apellidos'=>$apellidosEstudiante,
                'labelestadoMatricula' =>$labelEstadoMatricula,
                'estadoCurso'=>$estadoCurso,
                'fechaMatricula'=>$fechaMatricula,
                'opciones1'=>$botones
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    
    
    public function cargarformularioingresoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
            $objMetodos = new Metodos();
            $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
            $objMatricula = new Matricula($this->dbAdapter);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS PARA MODIFICAR EN ESTE MÓDULO</div>';
                else{
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
                                ini_set('date.timezone','America/Bogota'); 
                                $fechaActual = strtotime(date("d-m-Y"));
                                $fechaInicioMat = strtotime($listaConfCurso[0]['fechaInicioMatricula']);
                                 $fechaFinMat = strtotime($listaConfCurso[0]['fechaFinMatricula']);                                    
                                if(($fechaActual<$fechaInicioMat)||($fechaActual>=$fechaFinMat))
                                    $mensaje = '<div class="alert alert-warning text-center" role="alert">ESTE CURSO NO ESTÁ HABILITADO PARA MATRICULAR EN ESTA FECHA</div>';
                                else{
                                    $div = '<h4>FORMULARIO DE MATRÍCULA</h4><hr>
                                        <label for="identificacion">IDENTIFICACIÓN DE LA PERSONA</label>
                                            <input onkeyup="filtrarUsuarioPorIdentificacionEnMatricula(event);" onkeydown="validarNumeros(\'identificacion\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacion" name="identificacion" class="form-control">';
                                    
                                    $mensaje = '';
                                    $validar = TRUE;
                                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'div'=>$div));
                                    }
                                }
                            }
                        }                   
                    }                    
                }
            }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function ingresarmatriculaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objCurso = new Cursos($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $objMatricula = new Matricula($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idPersonaEncriptado = $post['idPersonaEncriptado']; 
                        $idConfigurarCursoEncriptado = $post['selectConfigurarCurso'];
                                                 

                        if(empty($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                        }else if(empty ($idConfigurarCursoEncriptado)|| $idConfigurarCursoEncriptado == NULL ){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">POR FAVOR, SELECCIONE UN HORARIO DE CLASES</div>';
                        }else 
                        { 
                            $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                            $listaPersona = $objPersona->FiltrarPersona($idPersona);
                            if(count($listaPersona) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA</div>';
                            }else if($listaPersona[0]['estadoPersona'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER MATRICULADA</div>';
                            }else{
                                    $idConfigurarCurso = $objMetodos->desencriptar($idConfigurarCursoEncriptado);
                                    $listaConfigurarCurso = $objConfigurarCurso->FiltrarConfigurarCurso($idConfigurarCurso);
                                    if(count($listaConfigurarCurso) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE EL HORARIO</div>';
                                    }else{                                        
                                        ini_set('date.timezone','America/Bogota'); 
                                        $hoy = getdate();
                                        $fechaMatricula = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                        
                                        $res = $objMatricula->IngresarMatricula($idPersona, $idConfigurarCurso, $fechaMatricula);
                                        
                                        if(count($res)==0)
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR, NO SE PUDO LA MATRÍCULA</div>';
                                        else{
                                            $mensaje = '<div class="alert alert-success text-center" role="alert">MATRICULA FINALIZADA CON ÉXITO</div>';
                                            $validar = TRUE;
                                        }
                                    }
//                                }
                            }
                        }
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
     public function obtenerformulariomodificarestadomatriculaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{               
                    $objMetodos = new Metodos();
                    $objMatricula = new Matricula($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );

                    $idMatriculaEncriptado = $post['id'];
                    $i = $post['i'];
                    $j = $post['j'];
                    if($idMatriculaEncriptado == NULL || $idMatriculaEncriptado == "" ){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA MATRICULA</div>';
                    }else if(!is_numeric($i)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else if(!is_numeric($j)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else{
                        $idMatricula = $objMetodos->desencriptar($idMatriculaEncriptado); 
                        $listaMatricula = $objMatricula->FiltrarMatricula($idMatricula);
                        if(count($listaMatricula) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MATRÍCULA SELECCIONADA NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                        }else{
                            
                            
                            $tabla = '';
                            if($listaMatricula[0]['estadoMatricula']==1){
                            $tabla = '<div class="form-group col-lg-12">
                                    <input type="hidden" value="'.$i.'" id="ime" name="ime">
                                    <input type="hidden" value="'.$j.'" id="jme" name="jme">
                                    <input type="hidden" value="'.$idMatriculaEncriptado.'" name="idMatriculaEncriptado" id="idMatriculaEncriptado">
                                    
                                    <h4><b>ESTUDIANTE:</b> '.$listaMatricula[0]['primerNombre'].' '.$listaMatricula[0]['segundoNombre'].' '.$listaMatricula[0]['primerApellido'].' '.$listaMatricula[0]['segundoApellido'].'</h4>
                                    <p><span style="background-color: #dd4b3954">Una vez que se cancele la matrícula no se podrá revertir el proceso.</span> </p>                                          
                                    <p><span style="background-color: #f39c124a">Es necesario subir un documento de respaldo para poder continuar.</span> </p>       
                                    </div>
                                    <div class="form-group col-lg-12">
                                    DOCUMENTO DE RESPALDO:
                                    <div class="fileUpload btn btn-success" id="contenedorArchivoPdfDocDeshabilitarMat">
                                    <span>SUBIR ARCHIVO</span>
                                    <input type="file" id="documentoDeshabilitarMatModal" name="documentoDeshabilitarMatModal" class="upload" onchange="vistaPreviaMatricula();" accept="application/pdf$">
                                    </div>
                                    <br />
                                    <output id="contenedorVistaPreviaDeshabilitarMat" style="background-color: #f4f4f4ba;border-radius: 10px;border-color: #f4f4f4ba; margin-bottom: inherit;" class="col-lg-12"></output>
                                  
                                    <div id="contenedorBtnCancelarDeshabilitarMat"></div>
                                    <div id="contenedorBtnAplicarDeshabilitarMat">
                                    
                                        </div>
                                    </div>
                                ';
                            }
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
    
    public function nombredocumentoAction($length,$uc,$n,$sc)
    {
        $source = 'abcdefghijklmnopqrstuvwxyz';
        if($uc==1) $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        if($n==1) $source .= '1234567890'; 
        if($sc==1) $source .= '|@#~$%()=^*+[]{}-_'; 
        if($length>0)
        { 
            $rstr = ""; 
            $source = str_split($source,1);
            for($i=1; $i<=$length; $i++)
            { 
                mt_srand((double)microtime() * 1000000); 
                $num = mt_rand(1,count($source)); 
                $rstr .= $source[$num-1];  
            }   
        } 
        return $rstr;
    }
    
    public function modificarestadomatriculaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 14);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 14, 1);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS PARA MODIFICAR EN ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objMatricula = new Matricula($this->dbAdapter);     
                        $objAdjunto = new Adjunto($this->dbAdapter);
                        $objAdjuntoMatricula = new AdjuntoMatricula($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idMatriculaEncriptado = $post['idMatriculaEncriptado'];
                        $im = $post['ime'];
                        $jm = $post['jme'];
                        
                        $documento = $post['documentoDeshabilitarMatModal'];
                                    
                        
                        if(empty($documento)){
                            $mensaje='<div class="alert alert-danger text-center" role="alert">POR FAVOR SELECCIONE UNA FOTO</div>';
                        }else
                        {
                            $idMatricula= $objMetodos->desencriptar($idMatriculaEncriptado);
                            $listaMatricula = $objMatricula->FiltrarMatricula($idMatricula);
                            if(count($listaMatricula)>0)
                            {
                                ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                    
                                    if($documento['type'] != 'application/pdf'){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL DOCUMENTO DEBE SER EN FORMATO PDF</div>';
                                    }else
                                        {
                                        $nombreFechaDocumento = $hoy['year'].$hoy['mon'].$hoy['mday'].$hoy['hours'].$hoy['minutes'].$hoy['seconds'];
                                        $name = $documento['name'];
                                        $nombreTemporal = $documento['tmp_name'];
                                        $trozos = explode('.',$name);
                                        $ext = end($trozos);
                                        $nombreFinalImagen = $this->nombredocumentoAction(10, TRUE, TRUE, FALSE).$nombreFechaDocumento.'.'.$ext;
                                        
                                        $destino = '/public/evidenciasmatriculas/'.$nombreFinalImagen;
                                        $src = $_SERVER['DOCUMENT_ROOT'].$this->getRequest()->getBaseUrl().$destino;
                                                     
            //                                             GUARDAR IMAGEN
                                        if(move_uploaded_file($nombreTemporal,$src))
                                        {
                                           $resultado= $objAdjunto->IngresarAdjunto($name, $destino, $fechaSubida, 1);
                                           $idAdjunto=$resultado[0]['idAdjunto'];
                                           if(count($resultado)==0)
                                           {
                                                unlink($src);
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE PUDO SUBIR EL DOCUMENTO, POR FAVOR INTENTE MÁS TARDE</div>';
                                           }                                               
                                           else{       
                                                $resultado2= $objMatricula->ModificarEstadoMatricula($idMatricula, 0);
                                                if(count($resultado2) == 0){ 
                                                    unlink($src);
                                                    $resultado3 =$objAdjunto->ModificarEstadoAdjunto($resultado[0]['idAdjunto'], 0);
                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE MODIFICÓ EL ESTADO, POR FAVOR INTENTE MÁS TARDE</div>';
                                                }else{
                                                    $resAdjuntoMatricula = $objAdjuntoMatricula->IngresarAdjuntoMatricula($idAdjunto, $idMatricula, 2, 1);
                                                    if(count($resAdjuntoMatricula)==0)
                                                    {
                                                        unlink($src);
                                                        $resultado4 =$objAdjunto->ModificarEstadoAdjunto($resultado[0]['idAdjunto'], 0);
                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE MODIFICÓ EL ESTADO, POR FAVOR INTENTE MÁS TARDE</div>';
                                                    }else{
                                                        $listaConfigurarCurso = $objConfigurarCurso->FiltrarConfigurarCurso($resultado2[0]['idConfigurarCurso']);
                                                        $tablaMatricula = $this->CargarTablaMatriculasAction($listaConfigurarCurso, $resultado2, $im, $jm);
    
                                                        $mensaje = '<div class="alert alert-success text-center" role="alert">SE CANCELÓ LA MATRÍCULA DE '.$resultado2[0]['primerNombre'].' '.$resultado2[0]['primerApellido'].' CON IDENTIFICACIÓN '.$resultado2[0]['identificacion'].'</div>';
                                                        $validar = TRUE;

                                                        return new JsonModel(array( 'tabla'=>$tablaMatricula,'idMatricula'=>$idMatriculaEncriptado,'jm'=>$jm,'im'=>$im,'mensaje'=>$mensaje,'validar'=>$validar,'idadjnt'=>$idAdjunto));
                                                
                                                    }
                                                }
                                            } 
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
    
//    
//    public function generarcomprobanteAction()
//    {
//        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
//        require($_SERVER['DOCUMENT_ROOT'].$this->getRequest()->getBaseUrl().'/public/metodos/fpdf/fpdf.php');
//        
  
//        
//        $idMatriculaEncriptado = $this->params()->fromQuery('id');
//
//        $objMetodos = new Metodos();
//        $idMatricula = $objMetodos->desencriptar($idMatriculaEncriptado);
//        $objMat = new Matricula($this->dbAdapter);
//        $pdf = new \FPDF();
//	$resultado = $objMat->FiltrarMatricula($idMatricula);
//	$pdf->AliasNbPages();
//	$pdf->AddPage();
//	
//	$pdf->SetFillColor(232,232,232);
//	$pdf->SetFont('Arial','B',12);
////	$pdf->Cell(70,6,'ESTADO',1,0,'C',1);
//        $pdf->Cell(40,10,utf8_decode('¡Mi primera página pdf con FPDF!'),1,0,'C',1);
//	$pdf->Cell(10,40,'ID',1,0,'C',1);
//	
//	$pdf->SetFont('Arial','B',10);
//	
//        
//	foreach ($resultado as $row)
//	{
////		$pdf->Cell(70,6,utf8_decode($row['estadoMatricula']),1,0,'C');
//		$pdf->Cell(20,6,$row['idMatricula'],1,0,'C');
//	}
////        print_r($resultado);
//	return $pdf->output();
//
//    }
    
    
     public function generarcomprobanteAction()
    {
        $this->layout("layout/administrador");
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $sesionUsuario = new Container('sesionparroquia');
        $idIglesia = $sesionUsuario->offsetGet('idIglesia');
        
        $objMetodos = new Metodos();
        $objConfCurso = new ConfigurarCurso($this->dbAdapter);
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
         $listaPeriodo = $objPeriodo->FiltrarPeriodo($listaConfCurso[0]['idPeriodo']);
        
        $objAdministrativos = new Administrativos($this->dbAdapter);
        $cuerpoTablaAdm ='';
        //el id 1 pertenece al parroco
        $listaAdministrativo = $objAdministrativos->FiltrarAdministrativosPorIdentificadorCargo(1);
            if(count($listaAdministrativo)>0)
            {
                $listaPersona = $objPersona->FiltrarPersona($listaAdministrativo[0]['idPersona']);
                $cuerpoTablaAdm = $cuerpoTablaAdm.'<tr> 
                            <th>_________________________________________<br>
                            '.$listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'].' '.$listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'].'
                            <br>'.$listaAdministrativo[0]['descripcion'].'</th>
                             </tr>';
            }
         
	
        $listaHorarioCurso = $objHorarioCurso->FiltrarHorarioCursoPorConfiguCurso($resultado[0]['idConfigurarCurso']);
         $cuerpoTablaHorario = '';
        foreach ($listaHorarioCurso as $valueHorarioCurso) {
            $horaInicio = strtotime ( '-1 second' , strtotime($valueHorarioCurso['horaInicio']));
            $horaInicio = date( 'H:i:s' , $horaInicio );
            $horaFin = strtotime ( '+1 second' , strtotime($valueHorarioCurso['horaFin']));
            $horaFin = date( 'H:i:s' , $horaFin );
            $horas = $horaInicio.' - '.$horaFin;
        $cuerpoTablaHorario = $cuerpoTablaHorario.'<tr class="text-center"><td class="text-center">'.$valueHorarioCurso['nombreDia'].'</td><td style="text-align:left;">'.$horas.'</td></tr>';}
                                
        $tablahorarios= '<table style="text-align:center; width:100%"  class="table " >
                        <thead>
                                <tr>
                                    <th style=" width:50%" colspan="2" >HORARIO DE CATEQUESIS</td>
                                </tr>
                               
                        </thead>
                            <tbody>
                                '.$cuerpoTablaHorario.'
                            </tbody>
                         </table>';
    
        $estado='HABILITADA';
        if($resultado[0]['estadoMatricula']==0)
            $estado='DESHABILITADA';
        
        
        
        $tabla = '<br><br><br><div class="box box-success">
            <div  style="text-align:center; width:100%; color:#777" >
              <img style="width:10%" src="'.$this->getRequest()->getBaseUrl().'/public/librerias/images/pagina/logoiglesia.png" >
             <br> <label style="font-size:24px" class="box-title ">'.$listaIglesia[0]['nombreIglesia'].'<br>'.$direccionIglesia.'</label>
             <br> <label>Sistema Web de Gestión Parroquial</label>
            </div>
            <hr>
            <div class="box-body text-center"   >
              <!-- Minimal style -->
              <h2  style="text-align:center; width:100%; color:#3c8dbc" ><b>COMPROBANTE DE MATRÍCULA</b></h2>
               <div class="col-lg-1"></div>
                <div class="col-lg-10">
              <table style="text-align:center;width:100%;" class="table" >
              <tbody>
                <tr>
                    <td><b>Código de matrícula:</b></td>
                    <td style="text-align:left;">'.$resultado[0]['idMatricula'].' </td>
                 </tr>
                 <tr>
                    <td><b>Estado de matrícula:</b></td>
                    <td style="text-align:left;">'.$estado.' </td>
                 </tr>
                <tr>
                    <td><b>Estudiante:</b></td>
                    <td style="text-align:left;">'.$resultado[0]['primerNombre'].' '.$resultado[0]['segundoNombre'].' '.$resultado[0]['primerApellido'].' '.$resultado[0]['segundoApellido'].'</td>
                </tr>
                <tr>
                    <td><b>Cédula de ciudadanía:</b></td>
                    <td style="text-align:left;">'.$resultado[0]['identificacion'].' </td>
                 </tr>
                 <tr>
                    <td><b>Fecha y hora de la matrícula:</b></td>
                    <td style="text-align:left;">'.$resultado[0]['fechaMatricula'].' </td>
                 </tr>
                  <tr>
                    <td><b>Periodo de catequesis:</b></td>
                    <td style="text-align:left;">'.$listaPeriodo[0]['nombrePeriodo'].' </td>
                 </tr>
               
                 <tr>
                    <td><b>Nivel del curso:</b></td>
                    <td style="text-align:left;">'.$listaConfCurso[0]['nombreCurso'].' </td>
                 </tr>
                <tr>
                    <td><b>Catequista:</b></td>
                    <td style="text-align:left;">'.$listaConfCurso[0]['primerNombre'].' '.$listaConfCurso[0]['segundoNombre'].' '.$listaConfCurso[0]['primerApellido'].' '.$listaConfCurso[0]['segundoApellido'].'</td>
                 </tr>
                 
                  <tr>
                    <td><b>Fecha de inicio de clases:</b></td>
                    <td style="text-align:left;">'.$listaConfCurso[0]['fechaInicio'].' </td>
                 </tr>
                  <tr>
                    <td><b>Fecha de fin de clases:</b></td>
                    <td style="text-align:left;">'.$listaConfCurso[0]['fechaFin'].' </td>
                 </tr>

               </tbody>
               </table>
               </div>
                <div class="col-lg-1"></div>
               <br>
               <div class="col-lg-4"></div>
             
               '.$tablahorarios.'
             <div class="col-lg-4"></div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer text-center"  style="text-align:center; width:100%">
           <table class="table text-center" style="width:100%; padding-top:20%" > 
               '.$cuerpoTablaAdm.'                               
            </table>
             <p> <i> Comprobante de matrícula generado automáticamente por el Sistema Web de Gestión Parroquial.</i> </p>
            </div>
          </div>';
        
        
	$array =  array(
            'tabla'=>$tabla            
        );
        
        
        
	return new ViewModel($array);

    }
 
}