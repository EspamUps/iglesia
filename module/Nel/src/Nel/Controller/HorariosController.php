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
use Nel\Modelo\Entity\Telefonos;
use Nel\Modelo\Entity\AsignarModulo; 
use Nel\Modelo\Entity\Dias;
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\HoraHorario;
use Nel\Modelo\Entity\Cursos;
use Nel\Modelo\Entity\HorarioCurso;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class HorariosController extends AbstractActionController
{
    public $dbAdapter;
    public function modificarestadohorahorarioAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 11);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else{
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 11, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE MODIFICAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objHoraHorario = new HoraHorario($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idHoraHorarioEncriptado = $post['id'];
                        if($idHoraHorarioEncriptado == NULL || $idHoraHorarioEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL SACERDOTE</div>';
                        }else{
                            $idHoraHorario = $objMetodos->desencriptar($idHoraHorarioEncriptado);
                            $listaHoraHorario = $objHoraHorario->FiltrarHoraHorario($idHoraHorario);
                            if(count($listaHoraHorario) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL RANGO DE HORAS SELECCIONADO NO EXISTE</div>';
                            }else{
                                
                                $idHorario = $listaHoraHorario[0]['idHorario'];
                                $horaInicio =   $listaHoraHorario[0]['horaInicio'];
                                $horaFin = $listaHoraHorario[0]['horaFin'];
                                
                                if(count($objHoraHorario->FiltrarChoqueHorasHoraHorario($idHoraHorario,$idHorario, $horaInicio, $horaFin, 1)) > 0 ){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL RANGO QUE QUIERE HABILITAR CHOCA CON UN RANGO QUE YA ESTÁ HABILITADO</div>';
                                }else{
                                    $listaHoraHorarioPorHorario = $objHoraHorario->FiltrarHoraHorarioPorHorario($idHorario);
                                    $validarChoqueHoras = TRUE;
                                    foreach ($listaHoraHorarioPorHorario as $valueHoraHorario) {
                                        if($idHoraHorario != $valueHoraHorario['idHoraHorario']){
                                            if(count($objHoraHorario->FiltrarChoqueHorasHoraHorario($valueHoraHorario['idHoraHorario'], $idHorario, $valueHoraHorario['horaInicio'], $valueHoraHorario['horaFin'], 1)) > 0){
                                                $validarChoqueHoras = FALSE;
                                                break;
                                            }
                                            
                                        }
                                    }
                                    if($validarChoqueHoras == FALSE && $listaHoraHorario[0]['estadoHoraHorario'] == FALSE){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL RANGO QUE QUIERE HABILITAR CHOCA CON UN RANGO QUE YA ESTÁ HABILITADO</div>';
                                    }else{
                                        $estado = TRUE;
                                        if($listaHoraHorario[0]['estadoHoraHorario'] == TRUE){
                                            $estado = FALSE;
                                        }
                                        $resultado = $objHoraHorario->ModificarEstadoHoraHorario($idHoraHorario,$estado);
                                        if(count($resultado) == 0){
                                            if($estado == TRUE)
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE HABILITÓ EL RANGO DE HORAS</div>';
                                            else
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE DESHABILITÓ EL RANGO DE HORAS</div>';
                                        }else{
                                            $mensaje = '';
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
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function eliminarhorahorarioAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 11);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 11, 1);
                if ($validarprivilegio==false)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objHoraHorario = new HoraHorario($this->dbAdapter);
                        $objHorarioCurso = new HorarioCurso($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idHoraHorarioEncriptado = $post['id'];
                     
                        if($idHoraHorarioEncriptado == NULL || $idHoraHorarioEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL SACERDOTE</div>';
                        }else{
                            $idHoraHorario = $objMetodos->desencriptar($idHoraHorarioEncriptado);
                            $listaHoraHorario = $objHoraHorario->FiltrarHoraHorario($idHoraHorario);
                            if(count($listaHoraHorario) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL RANGO DE HORAS SELECCIONADO NO EXISTE</div>';
                            }else if(count($objHorarioCurso->FiltrarHorarioCursoPorHoraHorarioLimit1($idHoraHorario))> 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTE RANGO DE HORAS YA HA SIDO UTILIZADO PARA UN CURSO, POR LO TANTO NO PUEDE ELIMINARSE</div>';
                            }else{
                                
                                
                                $resultado = $objHoraHorario->EliminarHoraHorario($idHoraHorario);
                                if(count($resultado) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ EL RANGO DE HORAS</div>';
                                }else{
                                    $mensaje = '';
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
    
    
    
    
    
    public function ingresarhorahorarioAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 11);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 11, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objHoraHorario = new HoraHorario($this->dbAdapter);
                        $objHorario = new Horario($this->dbAdapter);
                        
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idHorarioEncriptado = $post['idHorarioEncriptado'];
                        $horaInicio = $post['horaInicio'];
                        $horaFin = $post['horaFin'];
                        if(empty($idHorarioEncriptado) || $idHorarioEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL HORARIO</div>';
                        }else if(empty($horaInicio) || $horaInicio == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA HORA INICIO</div>';
                        }else if(empty($horaFin) || $horaFin == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA HORA FÍN</div>';
                        }else{
                            $horaInicioComparar = strtotime($horaInicio);
                            $horaFinComparar = strtotime($horaFin);
                            if($horaInicioComparar >= $horaFinComparar){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA HORA DE INICIO NO DEBE SER MAYOR NI IGUAL QUE LA HORA FIN</div>';                        
                            }else{
                                $idHorario = $objMetodos->desencriptar($idHorarioEncriptado);
                                $listaHorario = $objHorario->FiltrarHorario($idHorario);
                                if(count($listaHorario) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE EL HORARIO</div>';
                                }else if(count($objHoraHorario->FiltrarHoraHorarioPorHorarioPorHoras($idHorario, $horaInicio, $horaFin)) > 0){ 
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE EL RANGO DE HORAS '.$horaInicio.' - '.$horaFin.'</div>';
                                }else{
                                    
                                    $horaInicio = strtotime ( '+1 second' , strtotime($horaInicio));
                                    $horaInicio = date( 'H:i:s' , $horaInicio );
                                    $horaFin = strtotime ( '-1 second' , strtotime($horaFin));
                                    $horaFin = date( 'H:i:s' , $horaFin );
                                    ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                    $resultado = $objHoraHorario->IngresarHoraHorario($idHorario, $horaInicio, $horaFin, $fechaSubida, 0);
                                    if(count($resultado) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESARON LAS HORAS INTENTE MÁS TARDE</div>';
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
    
    
    
    public function filtrarhorarioporcursoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 11);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objMetodos = new Metodos();
                    $objCurso = new Cursos($this->dbAdapter);
                    $objHorario = new Horario($this->dbAdapter);
                    $objHoraHorario = new HoraHorario($this->dbAdapter);
                    $objHorarioCurso = new HorarioCurso($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );
                     $idCursoEncriptado = $post['idCurso'];

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
                            $listaHorario = $objHorario->FiltrarHorarioPorCurso($idCurso);
                            if(count($listaHorario) != 7){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTE CURSO NO TIENE UN HORARIO ASIGNADO POR FAVOR ELIMÍNELO</div>';
                            }else{
                                $tabla = '';
                                $cuerpoTabla = '';

                                $objMetodosC = new MetodosControladores();
                                $validarprivilegioIngresar = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 11, 3);
                                $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 11, 1);
                                $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 11, 2);
                                $i = 1;
                                $iHoras = 0;
                                foreach ($listaHorario as $valueHorario) {
                                    $idHorarioEncriptado = $objMetodos->encriptar($valueHorario['idHorario']);
                                    $colorFondo = 'background-color: #DCFBFF;';
                                    if($i%2 == 0){
                                        $colorFondo = 'background-color: #CFCFCF;';
                                    }
                                    $listaHoraHorario = $objHoraHorario->FiltrarHoraHorarioPorHorario($valueHorario['idHorario']);
                                    $rowspan = 1; 

                                    $botonAgregarHoras = '';
                                    if($validarprivilegioIngresar == TRUE){
                                        $botonAgregarHoras = '<a onclick="obtenerIdHorario(\''.$idHorarioEncriptado.'\','.$i.')" data-toggle="modal" data-target="#modalIngresarHoraHorario" style="cursor: pointer;" ><i class="fa fa-plus"></i>Agregar</a>';
                                    }
                                    $filaHoras = '';
                                    foreach ($listaHoraHorario as $valueHoraHorario) {
                                        $horaInicio = strtotime ( '-1 second' , strtotime($valueHoraHorario['horaInicio']));
                                        $horaInicio = date( 'H:i:s' , $horaInicio );
                                        $horaFin = strtotime ( '+1 second' , strtotime($valueHoraHorario['horaFin']));
                                        $horaFin = date( 'H:i:s' , $horaFin );
                                        
                                        
                                        $colorFondoHora = '';
                                        if($valueHoraHorario['estadoHoraHorario']==FALSE){
                                            $colorFondoHora = 'background-color: #FFB0B0;';
                                        }else{
                                            $colorFondoHora = $colorFondo;
                                        }
                                       $idHoraHorarioEncriptado = $objMetodos->encriptar($valueHoraHorario['idHoraHorario']);
                                       $botonEliminarHoraHorario = '';
                                       if($validarprivilegioEliminar == TRUE){
                                           if(count($objHorarioCurso->FiltrarHorarioCursoPorHoraHorarioLimit1($valueHoraHorario['idHoraHorario'])) == 0)
                                                $botonEliminarHoraHorario = '<button id="btnEliminarHoraHorario'.$iHoras.'" title="ELIMINAR A '.$horaInicio.' - '.$horaFin.'" onclick="EliminarHoraHorario(\''.$idHoraHorarioEncriptado.'\','.$iHoras.','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                                       }
                                       $botonModificarEstadoHoraHorario = '';
                                       if($validarprivilegioModificar == TRUE){
                                           if($valueHoraHorario['estadoHoraHorario'] == FALSE){
                                                $botonModificarEstadoHoraHorario = '<button id="btnModificarEstadoHoraHorario'.$iHoras.'" title="HABILITAR '.$horaInicio.' - '.$horaFin.'" onclick="ModificarEstadoHoraHorario(\''.$idHoraHorarioEncriptado.'\','.$iHoras.','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-minus-square"></i></button>';
                                           }else{
                                                $botonModificarEstadoHoraHorario = '<button id="btnModificarEstadoHoraHorario'.$iHoras.'" title="DESHABILITAR '.$horaInicio.' - '.$horaFin.'" onclick="ModificarEstadoHoraHorario(\''.$idHoraHorarioEncriptado.'\','.$iHoras.','.$i.')" class="btn btn-success btn-sm btn-flat"><i class="fa fa-plus-square"></i></button>';
                                           }
                                       }
                                       $filaHoras = $filaHoras.'
                                           <tr style="'.$colorFondoHora.'">
                                                <td id="nombreHorarios'.$iHoras.'">'.$horaInicio.' - '.$horaFin.'</td>
                                                <td>'.$botonModificarEstadoHoraHorario.' '.$botonEliminarHoraHorario.'</td>
                                           </tr>';

                                        $rowspan++;
                                        $iHoras++;
                                    }
                                    if(empty($filaHoras)){
                                        $filaHoras = '
                                                <td>'.$botonAgregarHoras.'</td>
                                                <td></td>';
                                    }else{
                                        if(!empty($botonAgregarHoras)){
                                            $rowspan = $rowspan+1;
                                            $filaHoras = $filaHoras.'
                                               <tr style="'.$colorFondo.'">
                                                    <td>'.$botonAgregarHoras.'</td>
                                                    <td></td>
                                               </tr>';
                                        }
                                    }
                                    $cuerpoTabla = $cuerpoTabla.'<tr style="'.$colorFondo.'">
                                        <td rowspan="'.$rowspan.'" id="nombreDia'.$i.'">'.$valueHorario['nombreDia'].'</td>
                                        '.$filaHoras.'
                                        </tr>';

                                    $i++;
                                }
                                if(!empty($cuerpoTabla)){
                                    $tabla = '<div class="table-responsive">
                                                <table class="table ">
                                                    <thead>
                                                        <tr>
                                                            <th>DIAS</th>
                                                            <th>HORAS</th>
                                                            <th>OPCIONES</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        '.$cuerpoTabla.'
                                                    </tbody>
                                                </table>
                                            </div>';
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
    
    

//    
//    
//    


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