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
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\AsignarModulo; 
use Nel\Modelo\Entity\HoraHorario;
use Nel\Modelo\Entity\ConfigurarCurso;
use Nel\Modelo\Entity\Periodos;
use Nel\Modelo\Entity\Docentes;
use Nel\Modelo\Entity\RangoAsistencia;
use Nel\Modelo\Entity\Cursos;
use Nel\Modelo\Entity\HorarioCurso;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class ConfigurarCursoController extends AbstractActionController
{
    public $dbAdapter;
    public function filtrardatoscursoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 13);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 13, 3);
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
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                         $idCursoEncriptado = $post['id'];

                        if(empty($idCursoEncriptado)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CURSO</div>';
                        }else{
                            $idCurso = $objMetodos->desencriptar($idCursoEncriptado);
                            $listaCurso = $objCurso->FiltrarCurso($idCurso);
                            if(count($listaCurso) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO EXISTE</div>';
                            }else if($listaCurso[0]['estadoCurso'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO HA SIDO DESHABILITADO POR LO TANTO NO PUEDE SER UTILIZADO HASTA QUE SEA HABILITADO</div>';
                            }else{
                                $listaHorario = $objHorario->FiltrarHorarioPorCurso($listaCurso[0]['idCurso']);
                                $validarHoraHorario = FALSE;
                                foreach ($listaHorario as $valueHorario) {
                                    if(count($objHoraHorario->FiltrarHoraHorarioPorHorarioActivo($valueHorario['idHorario'])) > 0){
                                        $validarHoraHorario = TRUE;
                                        break;
                                    }
                                }
                                if($validarHoraHorario == FALSE){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO TIENE UN HORARIO ESTABLECIDO POR LO TANTO NO PUEDE SER UTILIZADO PARA UNA CONFIGURACIÓN</div>';
                                }else{
                                    $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarConfigurarCurso" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
                                    $cuerpoTablaDias = '';
                                    foreach ($listaHorario as $valueHorario) {
                                        $rowspan = 1; 
                                        $listaHoraHorario = $objHoraHorario->FiltrarHoraHorarioPorHorarioActivo($valueHorario['idHorario']);
                                        $filaHoras = '';
                                        if(count($listaHoraHorario) > 0){
                                            foreach ($listaHoraHorario as $valueHoraHorario) {
                                                $rowspan++;
                                                $horaInicio = strtotime ( '-1 second' , strtotime($valueHoraHorario['horaInicio']));
                                                $horaInicio = date( 'H:i:s' , $horaInicio );
                                                $horaFin = strtotime ( '+1 second' , strtotime($valueHoraHorario['horaFin']));
                                                $horaFin = date( 'H:i:s' , $horaFin );
                                                $horas = $horaInicio.' - '.$horaFin;
                                                $filaHoras = $filaHoras.'<tr><td>'.$horas.'</td></tr>';
                                            }
                                            $cuerpoTablaDias = $cuerpoTablaDias.'<tr>
                                                <td rowspan="'.$rowspan.'">'.$valueHorario['nombreDia'].'</td>
                                                '.$filaHoras.'
                                            </tr>';
                                        }
                                       
                                   }
                                    $tabla = '<div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>DÍA</td>
                                                            <th>HORAS</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        '.$cuerpoTablaDias.'
                                                    </tbody>
                                                </table>
                                            </div>'.$botonGuardar;
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
    
    
    
    
    
    
    
    
    
    
    
//    public function eliminarsacerdoteAction()
//    {
//        $this->layout("layout/administrador");
//        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
//        $validar = false;
//        $sesionUsuario = new Container('sesionparroquia');
//        if(!$sesionUsuario->offsetExists('idUsuario')){
//            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
//        }else{
//            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
//            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
//            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
//            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 2);
//            if (count($AsignarModulo)==0)
//                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
//            else {
//                $objMetodosC = new MetodosControladores();
//                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 2, 1);
//                if ($validarprivilegio==false)
//                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
//                else{
//                    $request=$this->getRequest();
//                    if(!$request->isPost()){
//                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
//                    }else{               
//                        $objMetodos = new Metodos();
//                        $objSacerdote = new Sacerdotes($this->dbAdapter);
//                        $post = array_merge_recursive(
//                            $request->getPost()->toArray(),
//                            $request->getFiles()->toArray()
//                        );
//
//                        $idSacerdoteEncriptado = $post['id'];
//                        $numeroFila = $post['numeroFila'];
//                     
//                        if($idSacerdoteEncriptado == NULL || $idSacerdoteEncriptado == ""){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL SACERDOTE</div>';
//                        }else if(!is_numeric($numeroFila)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
//                        }else{
//                            $idSacerdote = $objMetodos->desencriptar($idSacerdoteEncriptado);
//                            $listaSacerdote = $objSacerdote->FiltrarSacerdote($idSacerdote);
//                            if(count($listaSacerdote) == 0){
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SACERDOTE SELECCIONADO NO EXISTE</div>';
//                            }else{
//                                $resultado = $objSacerdote->EliminarSacerdote($idSacerdote);
//                                if(count($resultado) > 0){
//                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ  EL SACERDOTE</div>';
//                                }else{
//                                    $mensaje = '';
//                                    $validar = TRUE;
//                                    return new JsonModel(array('numeroFila'=>$numeroFila,'mensaje'=>$mensaje,'validar'=>$validar));
//                                }
//                            }
//                 
//                        }   
//                    }
//                }
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }
//    
//    
//    
    public function ingresarconfigurarcursoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 13);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 13, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPeriodo = new Periodos($this->dbAdapter);
                        $objCursos = new Cursos($this->dbAdapter);
                        $objDocentes = new Docentes($this->dbAdapter);
                        $objHorario = new Horario($this->dbAdapter);
                        $objHoraHorario = new HoraHorario($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $objRangoAsistencia = new RangoAsistencia($this->dbAdapter);
                        $objHorarioCurso = new HorarioCurso($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                         $idPeriodoEncriptado = $post['selectPeriodo'];
                         $idCursoEncriptado = $post['selectCurso'];
                         $idDocenteEncriptado = $post['selectDocente'];
                         $fechaInicioMatricula = $post['fechaInicioMatricula'];
                         $fechaFinMatricula = $post['fechaFinMatricula'];
                         $fechaInicio = $post['fechaInicio'];
                         $fechaFin = $post['fechaFin'];
                         $cupos = $post['cupos'];
                         $valor = $post['valor'];
                         $listaRangoAsistencia = $objRangoAsistencia->ObtenerRangoAsistenciaAcivo();
                         if(count($listaRangoAsistencia) != 1){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE HA ESTABLECIDO UN PORCENTAJE MÍNIMO DE ASISTENCIA POR FAVOR ESTABLÉSCALO</div>';
                         }else if(empty($idPeriodoEncriptado) || $idPeriodoEncriptado == NULL || $idPeriodoEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN PERIODO PERIODO</div>';
                        }else if(empty($idCursoEncriptado) || $idCursoEncriptado == NULL || $idCursoEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN CURSO</div>';
                        }else if(empty($idDocenteEncriptado) || $idDocenteEncriptado == NULL || $idDocenteEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN DOCENTE</div>';
                        }else if(empty($fechaInicioMatricula)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA INICIO DE LAS MATRÍCULAS</div>';
                        }else if(empty($fechaFinMatricula)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA FÍN DE LAS MATRÍCULAS</div>';
                        }else if(empty($fechaInicio)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA INICIO</div>';
                        }else if(empty($fechaFin)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA FÍN</div>';
                        }else if(empty($cupos)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LOS CUPOS</div>';
                        }else if(!is_numeric($valor)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL VALOR DEL CURSO</div>';
                        }else{
                            
                            $idPeriodo = $objMetodos->desencriptar($idPeriodoEncriptado);
                            $listaPeriodo = $objPeriodo->FiltrarPeriodo($idPeriodo);
                            if(count($listaPeriodo) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PERIODO SELECCIONADO NO EXISTE</div>';
                            }else if($listaPeriodo[0]['estadoPeriodo'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PERIODO SELECCIONADO HA SIDO DESHABILITADO POR LO TANTO NO PUEDE SER UTILIZADO HASTA QUE SEA HABILITADO</div>';
                            } else{
                                ini_set('date.timezone','America/Bogota'); 
                                $fechaActualCom =  strtotime(date("d-m-Y"));
                                $fechaInicioMatriculaCom = strtotime($fechaInicioMatricula);
                                $fechaFinMatriculaCom = strtotime($fechaFinMatricula);
                                $fechaInicioPeriodoCom = strtotime($listaPeriodo[0]['fechaInicio']);
                                $fechaFinPeriodoCom = strtotime($listaPeriodo[0]['fechaFin']);
                                $fechaInicioCom = strtotime($fechaInicio);
                                $fechaFinCom = strtotime($fechaFin);
                                if($fechaInicioMatriculaCom < $fechaInicioPeriodoCom || $fechaInicioMatriculaCom > $fechaFinPeriodoCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INICIO DE LAS MATRÍCULAS NO ESTÁ EN EL RANGO DE FECHAS DEL
                                            PERIODO '.$objMetodos->obtenerFechaEnLetraSinHora($listaPeriodo[0]['fechaInicio']).' hasta '.$objMetodos->obtenerFechaEnLetraSinHora($listaPeriodo[0]['fechaFin']).'</div>';                        
                                }else if($fechaFinMatriculaCom < $fechaInicioPeriodoCom || $fechaFinMatriculaCom > $fechaFinPeriodoCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA FIN DE LAS MATRÍCULAS NO ESTÁ EN EL RANGO DE FECHAS DEL
                                    PERIODO '.$objMetodos->obtenerFechaEnLetraSinHora($listaPeriodo[0]['fechaInicio']).' hasta '.$objMetodos->obtenerFechaEnLetraSinHora($listaPeriodo[0]['fechaFin']).'</div>';                                   
                                }else if($fechaInicioCom < $fechaInicioPeriodoCom || $fechaInicioCom > $fechaFinPeriodoCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INICIO DEL CURSO NO ESTÁ EN EL RANGO DE FECHAS DEL
                                            PERIODO '.$objMetodos->obtenerFechaEnLetraSinHora($listaPeriodo[0]['fechaInicio']).' hasta '.$objMetodos->obtenerFechaEnLetraSinHora($listaPeriodo[0]['fechaFin']).'</div>';                        
                                }else if($fechaFinCom < $fechaInicioPeriodoCom || $fechaFinCom > $fechaFinPeriodoCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA FIN DEL CURSO NO ESTÁ EN EL RANGO DE FECHAS DEL
                                    PERIODO '.$objMetodos->obtenerFechaEnLetraSinHora($listaPeriodo[0]['fechaInicio']).' hasta '.$objMetodos->obtenerFechaEnLetraSinHora($listaPeriodo[0]['fechaFin']).'</div>';                                   
                                }else if($fechaFinMatriculaCom < $fechaInicioMatriculaCom){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA FÍN DE LAS MATRÍCULAS NO DEBE SER MENOR A LA FECHA DE INICIO DE LAS MATRÍCULAS</div>';                        
                                }else if($fechaInicioMatriculaCom < $fechaActualCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INICIO DE LAS MATRÍCULAS NO DEBE SER MENOR A LA FECHA ACTUAL</div>';                        
                                }else if($fechaInicioCom < $fechaFinMatriculaCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INICIO DEL CURSO NO DEBE SER MENOR A LA FECHA DE FINALIZACIÓN DE MATRÍCULAS</div>';                        
                                }else if($fechaFinCom < $fechaInicioCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA FÍN DEL CURSO NO DEBE SER MENOR A LA FECHA DE INICIO DEL CURSO</div>';                        
                                }else {
                                    $idCurso = $objMetodos->desencriptar($idCursoEncriptado);
                                    $listaCurso = $objCursos->FiltrarCurso($idCurso);
                                    if(count($listaCurso) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO EXISTE</div>';
                                    }else if($listaCurso[0]['estadoCurso'] == FALSE){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO HA SIDO DESHABILITADO POR LO TANTO NO PUEDE SER UTILIZADO HASTA QUE SEA HABILITADO</div>';
                                    } else{
                                        $listaHorario = $objHorario->FiltrarHorarioPorCurso($idCurso);
                                        $validarHoraHorario = FALSE;
                                        foreach ($listaHorario as $valueHorario) {
                                            if(count($objHoraHorario->FiltrarHoraHorarioPorHorarioActivo($valueHorario['idHorario'])) > 0){
                                                $validarHoraHorario = TRUE;
                                                break;
                                            }
                                        }
                                        if($validarHoraHorario == FALSE){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO TIENE UN HORARIO ESTABLECIDO POR LO TANTO NO PUEDE SER SELECCIONADO PARA UNA CONFIGURACIÓN</div>';                        
                                        }else{
                                            $idDocente = $objMetodos->desencriptar($idDocenteEncriptado);
                                            $listaDocente = $objDocentes->FiltrarDocente($idDocente);
                                            if(count($listaDocente) == 0){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL DOCENTE SELECCIONADO NO EXISTE</div>';
                                            }else if($listaDocente[0]['estadoDocente'] == FALSE){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL DOCENTE SELECCIONADO HA SIDO DESHABILITADO POR LO TANTO NO PUEDE SER UTILIZADO HASTA QUE SEA HABILITADO</div>';
                                            } else{
                                                $hoy = getdate();
                                                $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];

                                                $resultado = $objConfigurarCurso->IngresarConfigurarCurso($idCurso, $idDocente, $idPeriodo, $listaRangoAsistencia[0]['idRangoAsistencia'], $fechaInicioMatricula, $fechaFinMatricula, $fechaInicio, $fechaFin, $cupos, $valor, $fechaSubida, 1);
                                                if(count($resultado) == 0){
                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA CONFIGURACIÓN DEL CURSO POR FAVOR INTENTE MÁS TARDE</div>';
                                                }else{
                                                    $validarIngresoHorarioCurso = TRUE;
                                                    $idConfigurarCurso = $resultado[0]['idConfigurarCurso'];
                                                    foreach ($listaHorario as $valueHorario) {
                                                        $listaHoraHorario = $objHoraHorario->FiltrarHoraHorarioPorHorarioActivo($valueHorario['idHorario']);
                                                        if(count($listaHoraHorario) > 0){
                                                            foreach ($listaHoraHorario as $valueHoraHorario) {
                                                                if(count($objHorarioCurso->IngresarHorarioCurso($idConfigurarCurso, $valueHoraHorario['idHoraHorario'], 1)) == 0){
                                                                    $validarIngresoHorarioCurso = FALSE;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if($validarIngresoHorarioCurso == FALSE){
                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA CONFIGURACIÓN DEL CURSO POR FAVOR INTENTE MÁS TARDE</div>';
                                                        $objConfigurarCurso->EliminarConfigurarCurso($idConfigurarCurso);
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
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
//    public function filtrarsacerdoteporidentificacionAction()
//    {
//        $this->layout("layout/administrador");
//        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
//        $validar = false;
//        $sesionUsuario = new Container('sesionparroquia');
//        if(!$sesionUsuario->offsetExists('idUsuario')){
//            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
//        }else{
//            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
//            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
//            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
//            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 2);
//            if (count($AsignarModulo)==0)
//                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
//            else {
//                $objMetodosC = new MetodosControladores();
//                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 2, 3);
//                if ($validarprivilegio==false)
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
//                else{
//                    $request=$this->getRequest();
//                    if(!$request->isPost()){
//                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
//                    }else{
//                        $objMetodos = new Metodos();
//                        $objPersona = new Persona($this->dbAdapter);
//                        $objSacerdotes = new Sacerdotes($this->dbAdapter);
//                        $post = array_merge_recursive(
//                            $request->getPost()->toArray(),
//                            $request->getFiles()->toArray()
//                        );
//                         $identificacion = trim($post['identificacion']);
//
//                        if(strlen($identificacion) > 10){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IDENTIFICACIÓN NO DEBE TENER MÁS DE 10 DÍGITOS</div>';
//                        }else{
//                            $listaPersona = $objPersona->FiltrarPersonaPorIdentificacion($identificacion);
//                            if(count($listaPersona) == 0){
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
//                            }else if($listaPersona[0]['estadoPersona'] == FALSE){
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
//                            } else{
//
//                                $listaSacerdote = $objSacerdotes->FiltrarSacerdotePorPersona($listaPersona[0]['idPersona']);
//                                if(count($listaSacerdote) > 0){
//                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN SACERDOTE CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
//                                }else{
//                                    $idPersonaEncriptado = $objMetodos->encriptar($listaPersona[0]['idPersona']);
//                                    $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
//                                    $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
//                                    $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarSacerdote" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
//                                    $tabla = '<input type="hidden" id="idPersonaEncriptado" name="idPersonaEncriptado" value="'.$idPersonaEncriptado.'">
//                                        <div class="table-responsive"><table class="table">
//                                        <thead> 
//                                            <tr>
//                                                <th>NOMBRES</th>
//                                                <td>'.$nombres.'</td>
//                                            </tr>
//                                            <tr>
//                                                <th>APELLIDOS</th>
//                                                <td>'.$apellidos.'</td>
//                                            </tr>
//                                            <tr>
//                                                <td colspan="2">'.$botonGuardar.'</td>
//                                            </tr>
//                                        </thead>
//                                    </table></div>';
//                                    $mensaje = '';
//                                    $validar = TRUE;
//                                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }
//    public function obtenersacerdotesAction()
//    {
//        $this->layout("layout/administrador");
//        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
//        $validar = false;
//        $sesionUsuario = new Container('sesionparroquia');
//        if(!$sesionUsuario->offsetExists('idUsuario')){
//            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
//        }else{
//            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
//            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
//            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
//            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 2);
//            if (count($AsignarModulo)==0)
//                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
//            else 
//                {
//                $request=$this->getRequest();
//                if(!$request->isPost()){
//                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
//                }else{
//                    $objPersona = new Persona($this->dbAdapter);
//                    $objSacerdotes = new Sacerdotes($this->dbAdapter);
//                    $objTelefono = new Telefonos($this->dbAdapter);
//                    $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
//                    $objDireccionPersona = new DireccionPersona($this->dbAdapter);
//                    $objConfigurarMisa = new ConfigurarMisa($this->dbAdapter);
//                    
//                    $objMetodos = new Metodos();
//                    ini_set('date.timezone','America/Bogota'); 
//                    $listaSacerdotes = $objSacerdotes->ObtenerSacerdotes();
//                    $array1 = array();
//                    $i = 0;
//                    $j = count($listaSacerdotes);
//                    
//                    $objMetodosC = new MetodosControladores();
//                    $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 2, 1);
//                 
//                    
//                    foreach ($listaSacerdotes as $value) {
//                        $idSacerdoteEncriptado = $objMetodos->encriptar($value['idSacerdote']);
//                        $listaPersona = $objPersona->FiltrarPersona($value['idPersona']);
//                        $listaTelefonoPersona = $objTelefonoPersona->FiltrarTelefonoPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
//                        $numeroTelefono = '';
//                        if(count($listaTelefonoPersona) > 0){
//                            $listaTelefono = $objTelefono->FiltrarTelefono($listaTelefonoPersona[0]['idTelefono']);
//                            $numeroTelefono = $listaTelefono[0]['numeroTelefono'];
//                        }
//
//                        $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
//                        $provincia = '';
//                        $canton = '';
//                        $parroquia = '';
//                        $direccion = '';
//                        $referencia = '';
//                        if(count($listaDireccionPersona) > 0){
//                            $provincia = $listaDireccionPersona[0]['nombreProvincia'];
//                            $canton = $listaDireccionPersona[0]['nombreCanton'];
//                            $parroquia = $listaDireccionPersona[0]['nombreParroquia'];
//                            $direccion = $listaDireccionPersona[0]['direccionPersona'];
//                            $referencia = $listaDireccionPersona[0]['referenciaDireccionPersona'];
//                        }
//                        $identificacion = $listaPersona[0]['identificacion'];
//                        $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
//                        $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
//                        $fechaRegistro = $objMetodos->obtenerFechaEnLetra($value['fechaIngresoSacerdote']);
//
//                        $fechaNacimiento2 = new \DateTime($listaPersona[0]['fechaNacimiento']);
//                        $fechaActual = new \DateTime(date("d-m-Y"));
//                        $diff = $fechaActual->diff($fechaNacimiento2);
//                        $fechaNacimiento = $objMetodos->obtenerFechaEnLetraSinHora($listaPersona[0]['fechaNacimiento']);
//                        
//                        $botonEliminarSacerdote = '';
//                        if($validarprivilegioEliminar == TRUE){
//                            if(count($objConfigurarMisa->FiltrarConfigurarMisaPorSacerdoteLimite1($value['idSacerdote'])) == 0){
//                                $botonEliminarSacerdote = '<button id="btnEliminarSacerdote'.$i.'" title="ELIMINAR A '.$nombres.'" onclick="EliminarSacerdote(\''.$idSacerdoteEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
//                            }
//                        }
//                        $botones = $botonEliminarSacerdote;     
//                        $array1[$i] = array(
//                            '_j'=>$j,
//                            'identificacion'=>$identificacion,
//                            'nombres'=>$nombres,
//                            'apellidos'=>$apellidos,
//                            'fechaNacimiento'=>$fechaNacimiento,
//                            'edad'=>$diff->y,
//                            'numeroTelefono'=>$numeroTelefono,
//                            'provincia'=>$provincia,
//                            'canton'=>$canton,
//                            'parroquia'=>$parroquia,
//                            'direccion'=>$direccion,
//                            'referencia'=>$referencia,
//                            'fechaRegistro'=>$fechaRegistro,
//                            'opciones'=>$botones,
//                        );
//                        $j--;
//                        $i++;
//                    }
//                    $mensaje = '';
//                    $validar = TRUE;
//                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$array1));
//                }
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }

}