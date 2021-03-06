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
use Nel\Modelo\Entity\TelefonoPersona;
use Nel\Modelo\Entity\DireccionPersona;
use Nel\Modelo\Entity\Docentes;
use Nel\Modelo\Entity\ConfigurarCurso;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class DocentesController extends AbstractActionController
{
    public $dbAdapter;
    public function eliminardocenteAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 10);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 10, 1);
                if ($validarprivilegio==false)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objDocente = new Docentes($this->dbAdapter);
                        $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idDocenteEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                     
                        if($idDocenteEncriptado == NULL || $idDocenteEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL DOCENTE</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idDocente = $objMetodos->desencriptar($idDocenteEncriptado);
                            $listaDocente = $objDocente->FiltrarDocente($idDocente);
                            if(count($listaDocente) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL DOCENTE SELECCIONADO NO EXISTE</div>';
                            }else if(count ($objConfigurarCurso->FiltrarConfigurarCursoPorDocenteLimit1($idDocente))>0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTE DOCENTE YA TIENE ASIGNADO UN CURSO POR LO TANTO NO PUEDE SER ELIMINADO</div>';
                            }else{
                                
                                
                                $resultado = $objDocente->EliminarDocente($idDocente);
                                if(count($resultado) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ  EL DOCENTE</div>';
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
    
    public function ingresardocenteAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 10);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 10, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objDocentes = new Docentes($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                         $idPersonaEncriptado = trim($post['idPersonaEncriptado']);

                        if(empty($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                        }else{
                            $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                            $listaPersona = $objPersona->FiltrarPersona($idPersona);
                            if(count($listaPersona) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA</div>';
                            }else if($listaPersona[0]['estadoPersona'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                            }else{ 
                                $listaDocente = $objDocentes->FiltrarDocentePorPersona($listaPersona[0]['idPersona']);
                                if(count($listaDocente) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN CATEQUISTA CON LA IDENTIFICACIÓN '.$listaPersona[0]['identificacion'].'</div>';
                                }else{
                                    ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                    $resultado = $objDocentes->IngresarDocente($idPersona, $fechaSubida, 1);
                                    if(count($resultado) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL CATEQUISTA POR FAVOR INTENTE MÁS TARDE</div>';
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
    public function filtrardocenteporidentificacionAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 10);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 10, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objDocentes = new Docentes($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                         $identificacion = trim($post['identificacion']);

                        if(strlen($identificacion) > 10){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IDENTIFICACIÓN NO DEBE TENER MÁS DE 10 DÍGITOS</div>';
                        }else{
                            $listaPersona = $objPersona->FiltrarPersonaPorIdentificacion($identificacion);
                            if(count($listaPersona) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
                            }else if($listaPersona[0]['estadoPersona'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                            } else{

                                $listaDocente = $objDocentes->FiltrarDocentePorPersona($listaPersona[0]['idPersona']);
                                if(count($listaDocente) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN CATEQUISTA CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
                                }else{
                                    $idPersonaEncriptado = $objMetodos->encriptar($listaPersona[0]['idPersona']);
                                    $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                                    $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
                                    $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarDocente" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
                                    $tabla = '<input type="hidden" id="idPersonaEncriptado" name="idPersonaEncriptado" value="'.$idPersonaEncriptado.'">
                                        <div class="table-responsive"><table class="table">
                                        <thead> 
                                            <tr>
                                                <th>NOMBRES</th>
                                                <td>'.$nombres.'</td>
                                            </tr>
                                            <tr>
                                                <th>APELLIDOS</th>
                                                <td>'.$apellidos.'</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">'.$botonGuardar.'</td>
                                            </tr>
                                        </thead>
                                    </table></div>';
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
    
    
    
    public function obtenerdocentesAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 10);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else 
                {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objPersona = new Persona($this->dbAdapter);
                    $objDocentes = new Docentes($this->dbAdapter);
                    $objTelefono = new Telefonos($this->dbAdapter);
                    $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
                    $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                    $objConfigurarCurso = new ConfigurarCurso($this->dbAdapter);
                    
                    $objMetodos = new Metodos();
                    ini_set('date.timezone','America/Bogota'); 
                    $listaDocentes = $objDocentes->ObtenerDocentes();
                    $array1 = array();
                    $i = 0;
                    $j = count($listaDocentes);
                    
                    $objMetodosC = new MetodosControladores();
                    $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 10, 1);
                 
                    
                    foreach ($listaDocentes as $value) {
                        $idDocenteEncriptado = $objMetodos->encriptar($value['idDocente']);
                        $listaPersona = $objPersona->FiltrarPersona($value['idPersona']);
                        $listaTelefonoPersona = $objTelefonoPersona->FiltrarTelefonoPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
                        $numeroTelefono = '';
                        if(count($listaTelefonoPersona) > 0){
                            $listaTelefono = $objTelefono->FiltrarTelefono($listaTelefonoPersona[0]['idTelefono']);
                            $numeroTelefono = $listaTelefono[0]['numeroTelefono'];
                        }

                        $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
                        $provincia = '';
                        $canton = '';
                        $parroquia = '';
                        $direccion = '';
                        $referencia = '';
                        if(count($listaDireccionPersona) > 0){
                            $provincia = $listaDireccionPersona[0]['nombreProvincia'];
                            $canton = $listaDireccionPersona[0]['nombreCanton'];
                            $parroquia = $listaDireccionPersona[0]['nombreParroquia'];
                            $direccion = $listaDireccionPersona[0]['direccionPersona'];
                            $referencia = $listaDireccionPersona[0]['referenciaDireccionPersona'];
                        }
                        $identificacion = $listaPersona[0]['identificacion'];
                        $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                        $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
                        $fechaRegistro = $objMetodos->obtenerFechaEnLetra($value['fechaIngreso']);

                        $fechaNacimiento2 = new \DateTime($listaPersona[0]['fechaNacimiento']);
                        $fechaActual = new \DateTime(date("d-m-Y"));
                        $diff = $fechaActual->diff($fechaNacimiento2);
                        $fechaNacimiento = $objMetodos->obtenerFechaEnLetraSinHora($listaPersona[0]['fechaNacimiento']);
                        
                        $botonEliminarDocente = '';
                        if($validarprivilegioEliminar == TRUE){
                            if(count($objConfigurarCurso->FiltrarConfigurarCursoPorDocenteLimit1($value['idDocente'])) == 0){
                                $botonEliminarDocente = '<button id="btnEliminarDocente'.$i.'" title="ELIMINAR A '.$nombres.'" onclick="EliminarDocente(\''.$idDocenteEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                            }
                        }
                        $botones = $botonEliminarDocente;     
                        $array1[$i] = array(
                            '_j'=>$j,
                            'identificacion'=>$identificacion,
                            'nombres'=>$nombres,
                            'apellidos'=>$apellidos,
                            'fechaNacimiento'=>$fechaNacimiento,
                            'edad'=>$diff->y,
                            'numeroTelefono'=>$numeroTelefono,
                            'provincia'=>$provincia,
                            'canton'=>$canton,
                            'parroquia'=>$parroquia,
                            'direccion'=>$direccion,
                            'referencia'=>$referencia,
                            'fechaRegistro'=>$fechaRegistro,
                            'opciones'=>$botones,
                        );
                        $j--;
                        $i++;
                    }
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$array1));
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }

}