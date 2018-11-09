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
use Nel\Modelo\Entity\Matricula;
use Nel\Modelo\Entity\Dias;
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\AsignarModulo; 
use Nel\Modelo\Entity\HoraHorario;
use Nel\Modelo\Entity\ConfigurarCurso;
use Nel\Modelo\Entity\Periodos;
use Nel\Modelo\Entity\Docentes;
use Nel\Modelo\Entity\RangoAsistencia;
use Nel\Modelo\Entity\FechaAsistencia;
use Nel\Modelo\Entity\Asistencia;
use Nel\Modelo\Entity\Cursos;
use Nel\Modelo\Entity\HorarioCurso;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class ConfigurarCursoController extends AbstractActionController
{
    public $dbAdapter;
    public function filtrarhorariocursoAction()
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
            else{
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objMetodos = new Metodos();
                    $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                    $objHorario = new Horario($this->dbAdapter);
                    $objHoraHorario = new HoraHorario($this->dbAdapter);
                    $objDias = new Dias($this->dbAdapter); 
                    $objHorarioCurso = new HorarioCurso($this->dbAdapter);
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
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO EXISTE</div>';
                        }else{
                            $listaHorarioCurso = $objHorarioCurso->FiltrarHorarioCursoPorConfiguCurso($idConfigurarCurso);
                            $cuerpoTablaHorario = '';
                            foreach ($listaHorarioCurso as $valueHorarioCurso) {
                                $horaInicio = strtotime ( '-1 second' , strtotime($valueHorarioCurso['horaInicio']));
                                $horaInicio = date( 'H:i:s' , $horaInicio );
                                $horaFin = strtotime ( '+1 second' , strtotime($valueHorarioCurso['horaFin']));
                                $horaFin = date( 'H:i:s' , $horaFin );
                                $horas = $horaInicio.' - '.$horaFin;
                                $cuerpoTablaHorario = $cuerpoTablaHorario.'<tr><td>'.$valueHorarioCurso['nombreDia'].'</td><td>'.$horas.'</td></tr>';
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
                                                '.$cuerpoTablaHorario.'
                                            </tbody>
                                        </table>
                                    </div>';
                            $mensaje = '';
                            $validar = TRUE;
                            return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                        }
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    
    
    
    
    
    
    public function modificarfechafinAction()
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
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 13, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE MODIFICAR DATOS PARA ESTE MÓDULO</div>';
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
                        
                        
                        $idConfigurarCursoEncriptado = $post['idConfigurarCursoEncriptado'];
                        $nuevaFecha = $post['nuevaFecha'];
                        $numeroFila = $post['numeroFilaT'];
                        $numeroFila2 = $post['numeroFila2T'];
                        if($idConfigurarCursoEncriptado == NULL || $idConfigurarCursoEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CURSO</div>';
                        }else if(empty($nuevaFecha)  || $nuevaFecha=="" ){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">DEBE COMPLETAR LOS CAMPOS FECHA ACTUAL Y NUEVA FECHA</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else  if(!is_numeric($numeroFila2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idConfigurarCurso = $objMetodos->desencriptar($idConfigurarCursoEncriptado);
                            $listaConfigurarCurso = $objConfigurarCurso->FiltrarConfigurarCurso($idConfigurarCurso);
                            if(count($listaConfigurarCurso) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO EXISTE</div>';
                            }else {
                                
                                $objFechaAsistencia = new FechaAsistencia($this->dbAdapter);
                                $objMatricula = new Matricula($this->dbAdapter);
                                $objAsistencia = new Asistencia($this->dbAdapter);
                                 $objHorarioCurso= new HorarioCurso($this->dbAdapter);
                                 $mensaje = '<div class="alert alert-success text-center" role="alert">EL CURSO SELECCIONADO NO EXISTE</div>';
                                 ini_set('date.timezone','America/Bogota'); 
                                
                                $fechaHoy = strtotime(date("d-m-Y"));
                                $fechaFinPeriodo = strtotime($listaConfigurarCurso[0]['fechaFinPeriodo']);
                                $nuevaFechaComp = strtotime($nuevaFecha);
                                $fechaFinActual = $listaConfigurarCurso[0]['fechaFin'];
                                $fechaFinActualComp = strtotime($fechaFinActual);
                                
                                if($fechaHoy>$nuevaFechaComp)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA NUEVA FECHA DE FIN DE CURSO DEBE SER MAYOR O IGUAL  A LA FECHA DE HOY</div>';
                                else if ($nuevaFechaComp>$fechaFinPeriodo)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA NUEVA FECHA DE FIN DE CURSO DEBE SER MENOR O IGUAL A LA FECHA DE FIN DE PERIODO</div>';
                                else if ($nuevaFechaComp<$fechaFinActualComp)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA NUEVA FECHA DEBE SER MAYOR A LA FECHA DE FIN DE CURSO REGISTRADO</div>';
                                else if ($nuevaFechaComp==$fechaFinActualComp)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA NO HA CAMBIADO</div>';
                                else {
                                    
                                    $resultado =$objConfigurarCurso->ModificarConfigurarCursoFechaFin($idConfigurarCurso, $nuevaFecha);
//                                    
                                    if(count($resultado)==0)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR. NO SE PUDO MODIFICAR LA FECHA DE FIN DE CURSO. INTÉNTELO MÁS TARDE.</div>';
                                    else
                                        {
                                            $nuevaFechaFinIngresada = $resultado[0]['fechaFin'];
                                            $listaHorarioRegistrado =$objHorarioCurso->FiltrarHorarioCursoPorConfiguCursoDistinctIdentificadorDia($idConfigurarCurso);
                                            $listaMatriculadosEnElCurso= $objMatricula->FiltrarMatriculaPorConfigurarCursoYEstado($idConfigurarCurso,1);
                                            $this->generarRegistrosAsistenciaAction($idConfigurarCurso,$fechaFinActual,$nuevaFechaFinIngresada,$listaHorarioRegistrado,$listaMatriculadosEnElCurso,$objAsistencia, $objFechaAsistencia);
                                            $mensaje = '<div class="alert alert-success text-center" role="alert">REGISTRO ACTUALIZADO CORRECTAMENTE</div>';
                                            $tabla = $this->CargarTablaConfigurarCrusosAction($idUsuario, $this->dbAdapter, $resultado, $numeroFila, $numeroFila2);
                                            $validar = TRUE;
                                            return new JsonModel(array('tabla'=>$tabla,'numeroFila'=>$numeroFila,'numeroFila2'=>$numeroFila2,'mensaje'=>$mensaje,'validar'=>$validar));
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
    
    
    function modificarestadoconfigurarcursoAction()
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
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 13, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE MODIFICAR DATOS PARA ESTE MÓDULO</div>';
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
                        $idConfigurarCursoEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                        $numeroFila2 = $post['numeroFila2'];
                        if($idConfigurarCursoEncriptado == NULL || $idConfigurarCursoEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CURSO</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else  if(!is_numeric($numeroFila2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idConfigurarCurso = $objMetodos->desencriptar($idConfigurarCursoEncriptado);
                            $listaConfigurarCurso = $objConfigurarCurso->FiltrarConfigurarCurso($idConfigurarCurso);
                            if(count($listaConfigurarCurso) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO NO EXISTE</div>';
                            }else {
                                $estadoConfigurarCurso = FALSE;
                                if($listaConfigurarCurso[0]['estadoConfigurarCurso'] == FALSE){
                                    $estadoConfigurarCurso = TRUE;
                                }
                                $resultado = $objConfigurarCurso->ModificarEstadoConfigurarCurso($idConfigurarCurso, $estadoConfigurarCurso);
                                if(count($resultado) == 0){
                                    if($estadoConfigurarCurso == TRUE)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE HABILITÓ EL CURSO</div>';
                                    else
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE DESHABILITÓ EL CURSO</div>';
                                }else{
                                    $tabla = $this->CargarTablaConfigurarCrusosAction($idUsuario, $this->dbAdapter, $resultado, $numeroFila, $numeroFila2);
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
    
    public function eliminarconfigurarcursoAction()
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
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 13, 1);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objMatricula = new Matricula($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idConfigurarCursoEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                        if($idConfigurarCursoEncriptado == NULL || $idConfigurarCursoEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CURSO</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else {
                            $idConfigurarCurso = $objMetodos->desencriptar($idConfigurarCursoEncriptado);
                            
                            $listaConfigurarCurso = $objConfigurarCurso->FiltrarConfigurarCurso($idConfigurarCurso);
                            if(count($listaConfigurarCurso) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADA NO EXISTE</div>';
                            }else if(count($objMatricula->FiltrarMatriculaPorConfigurarCursoLimit1($idConfigurarCurso)) > 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CURSO SELECCIONADO YA TIENE PERSONAS MATRICULADAS POR LO TANTO NO PUEDE SER ELIMINADO</div>';
                            }else{
                                $resultado = $objConfigurarCurso->EliminarConfigurarCurso($idConfigurarCurso);
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
    public function obtenerconfigurarcursoAction()
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
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                    ini_set('date.timezone','America/Bogota'); 
                    $listaConfigurarCurso = $objConfigurarCurso->ObtenerConfigurarCurso();
                    $tabla = $this->CargarTablaConfigurarCrusosAction($idUsuario, $this->dbAdapter, $listaConfigurarCurso, 0, count($listaConfigurarCurso));
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }
            
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    function CargarTablaConfigurarCrusosAction($idUsuario,$adaptador,$listaConfigurarCursos, $i, $j)
    {
        $objMatricula = new Matricula($this->dbAdapter);
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $objMetodosC = new MetodosControladores();
        $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 13, 1);
        $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 13, 2);
        $array1 = array();
        foreach ($listaConfigurarCursos as $value) {
            $idConfigurarCursoEncriptado = $objMetodos->encriptar($value['idConfigurarCurso']);
            $nombreCurso = '<input type="hidden" id="estadoConfigurarCursoA'.$i.'" name="estadoConfigurarCursoA'.$i.'" value="'.$value['estadoConfigurarCurso'].'">'.$value['nombreCurso'];
            $fechaIngreso = $objMetodos->obtenerFechaEnLetra($value['fechaIngreso']);

   
            
            
            $botonEliminarConfigurarCurso = '';
            if($validarprivilegioEliminar == TRUE){
                if(count($objMatricula->FiltrarMatriculaPorConfigurarCursoLimit1($value['idConfigurarCurso'])) == 0)
                    $botonEliminarConfigurarCurso = '<button id="btnEliminarConfigurarCurso'.$i.'" title="ELIMINAR '.$value['nombreCurso'].'" onclick="eliminarConfigurarCurso(\''.$idConfigurarCursoEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
            }

            $botonDeshabilitarConfigurarCurso = '';
            if($validarprivilegioModificar == TRUE){
                if($value['estadoConfigurarCurso'] == TRUE)
                    $botonDeshabilitarConfigurarCurso = '<button id="btnDeshabilitarConfigurarCurso'.$i.'" title="DESHABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarConfigurarCurso(\''.$idConfigurarCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-success btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
                else
                    $botonDeshabilitarConfigurarCurso = '<button id="btnDeshabilitarConfigurarCurso'.$i.'" title="HABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarConfigurarCurso(\''.$idConfigurarCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-minus-square"></i></button>';
            }
            $botones =  $botonDeshabilitarConfigurarCurso.' '.$botonEliminarConfigurarCurso;    
            $nombreDocente = $value['primerApellido'].' '.$value['segundoApellido'].' '.$value['primerNombre'].' '.$value['segundoNombre'];
            $fechaInicio = $objMetodos->obtenerFechaEnLetraSinHora($value['fechaInicio']);
            $fechaFin = $objMetodos->obtenerFechaEnLetraSinHora($value['fechaFin']);
            
            
            $array1[$i] = array(
                '_j'=>$j,
                'idConfigurarCursoEncriptado'=>$idConfigurarCursoEncriptado,
                'nombreCurso'=>$nombreCurso,
                'docente'=>$nombreDocente,
                'periodo'=>$value['nombrePeriodo'],
                'fechaInicio'=>$fechaInicio,
                'fechaFin'=>$fechaFin,
                'nivelCurso'=>$value['nivelCurso'],
                'valorCurso'=>$value['precio'],
                'fechaIngreso'=>$fechaIngreso,
                'opciones'=>$botones,
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    
    
    
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
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN CATEQUISTA</div>';
                        }else if(empty($fechaInicioMatricula)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA INICIO DE LAS MATRÍCULAS</div>';
                        }else if(empty($fechaFinMatricula)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA FIN DE LAS MATRÍCULAS</div>';
                        }else if(empty($fechaInicio)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA INICIO</div>';
                        }else if(empty($fechaFin)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA FIN</div>';
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
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA FIN DE LAS MATRÍCULAS NO DEBE SER MENOR A LA FECHA DE INICIO DE LAS MATRÍCULAS</div>';                        
                                }else if($fechaInicioMatriculaCom < $fechaActualCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INICIO DE LAS MATRÍCULAS NO DEBE SER MENOR A LA FECHA ACTUAL</div>';                        
                                }else if($fechaInicioCom < $fechaFinMatriculaCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INICIO DEL CURSO NO DEBE SER MENOR A LA FECHA DE FINALIZACIÓN DE MATRÍCULAS</div>';                        
                                }else if($fechaFinCom < $fechaInicioCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA FIN DEL CURSO NO DEBE SER MENOR A LA FECHA DE INICIO DEL CURSO</div>';                        
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
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CATEQUISTA SELECCIONADO NO EXISTE</div>';
                                            }else if($listaDocente[0]['estadoDocente'] == FALSE){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CATEQUISTA SELECCIONADO HA SIDO DESHABILITADO POR LO TANTO NO PUEDE SER UTILIZADO HASTA QUE SEA HABILITADO</div>';
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



}