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
//                $objMetodosC = new MetodosControladores();
//                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 11, 3);
//                if ($validarprivilegio==false)
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
//                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
//                        $objPersona = new Persona($this->dbAdapter);
//                        $objSacerdotes = new Sacerdotes($this->dbAdapter);
                        
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
                                            $botonAgregarHoras = '<a onclick="obtenerIdHorario(\''.$idHorarioEncriptado.'\')" data-toggle="modal" data-target="#modalIngresarHoraHorario" style="cursor: pointer;" ><i class="fa fa-plus"></i>Agregar</a>';
                                        }
                                        
                                        $filaHoras = '';
                                        foreach ($listaHoraHorario as $valueHoraHorario) {
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
                                                    $botonEliminarHoraHorario = '<button id="btnEliminarHoraHorario'.$i.'" title="ELIMINAR A '.$valueHoraHorario['horaInicio'].' - '.$valueHoraHorario['horaFin'].'" onclick="EliminarHoraHorario(\''.$idHoraHorarioEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                                           }
//                                           
                                           $filaHoras = $filaHoras.'
                                               <tr style="'.$colorFondoHora.'">
                                                    <td>'.$valueHoraHorario['horaInicio'].' - '.$valueHoraHorario['horaFin'].'</td>
                                                    <td>'.$botonEliminarHoraHorario.'</td>
                                               </tr>';
                                                
                                            $rowspan++;
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
                                            <td rowspan="'.$rowspan.'">'.$valueHorario['nombreDia'].'</td>
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
//                    }
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
//    public function ingresarsacerdoteAction()
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
//                         $idPersonaEncriptado = trim($post['idPersonaEncriptado']);
//
//                        if(empty($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
//                        }else{
//                            $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
//                            $listaPersona = $objPersona->FiltrarPersona($idPersona);
//                            if(count($listaPersona) == 0){
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA</div>';
//                            }else if($listaPersona[0]['estadoPersona'] == FALSE){
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
//                            } else{ 
//                                $listaSacerdote = $objSacerdotes->FiltrarSacerdotePorPersona($listaPersona[0]['idPersona']);
//                                if(count($listaSacerdote) > 0){
//                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN SACERDOTE CON LA IDENTIFICACIÓN '.$listaPersona[0]['identificacion'].'</div>';
//                                }else{
//                                    ini_set('date.timezone','America/Bogota'); 
//                                    $hoy = getdate();
//                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
//                                    $resultado = $objSacerdotes->IngresarSacerdote($idPersona, $fechaSubida, 1);
//                                    if(count($resultado) == 0){
//                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL SACERDOTE POR FAVOR INTENTE MÁS TARDE</div>';
//                                    }else{
//                                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
//                                        $validar = TRUE;
//                                    }
//
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