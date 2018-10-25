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
use Nel\Modelo\Entity\Usuario;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Provincias;
use Nel\Modelo\Entity\Telefonos;
use Nel\Modelo\Entity\TelefonoPersona;
use Nel\Modelo\Entity\HistorialPersona;
use Nel\Modelo\Entity\DireccionPersona;
use Nel\Modelo\Entity\Parroquias;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Nel\Modelo\Entity\ConfigurarCantonProvincia;
use Nel\Modelo\Entity\Sacerdotes;
use Nel\Modelo\Entity\Docentes;
use Nel\Modelo\Entity\Administrativos;
use Nel\Modelo\Entity\Bautismo;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class PersonaController extends AbstractActionController
{
    public $dbAdapter;
    public function modificartelefonoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 1, 2);
                if ($validarprivilegio==false)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objTelefono = new Telefonos($this->dbAdapter);
                        $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idPersonaEncriptado = $post['idPersonaEncriptado'];
                        $telefono = trim($post['nuevoTelefono']);
                        $numeroFila = $post['numeroFilaT'];
                        $numeroFila2 = $post['numeroFila2T'];
                     
                        if($idPersonaEncriptado == NULL || $idPersonaEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else  if(!is_numeric($numeroFila2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else if(!is_numeric($telefono) || strlen($telefono) > 20){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL TELÉFONO SÓLO NUMEROS</div>';
                        }else{
                            $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                            $listaPersona = $objPersona->FiltrarPersona($idPersona);
                            if(count($listaPersona) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA NO EXISTE</div>';
                            }else{
                                
                                
                                $listaTelefonoActual = $objTelefonoPersona->FiltrarTelefonoPersonaPorPersonaEstado($idPersona, true);
                                
                                
                                $listaTelefonoAnterior = $objTelefonoPersona->FiltrarTelefonoPersonaPorNumeroPersonaEstado($telefono, $idPersona);
                                if(count($listaTelefonoAnterior) > 0){
                                    if($listaTelefonoAnterior[0]['estadoTelefonoPersona'] == true)
                                    {
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">POR FAVOR MODIFIQUE EL TELÉFONO</div>';
                                    }else{
                                        $resultado = $objTelefonoPersona->ModificarTelefonoPersona($listaTelefonoActual[0]['idTelefonoPersona'], 0);
                                        $resultado = $objTelefonoPersona->ModificarTelefonoPersona($listaTelefonoAnterior[0]['idTelefonoPersona'], true);
                                    }
                                }else{
                                    ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                    $resultadoTelefono = $objTelefono->IngresarTelefono($telefono, 1);
                                    $idTelefono = $resultadoTelefono[0]['idTelefono'];
                                    $objTelefonoPersona->ModificarTelefonoPersona($listaTelefonoActual[0]['idTelefonoPersona'], 0);
                                    $objTelefonoPersona->IngresarTelefonoPersona($idPersona, $idTelefono, $fechaSubida, 1);
                                    
                                     
                                    
                                    
                                    
                                   
                               }
                               $tablaPersona = $this->CargarTablaPersonaAction($idUsuario, $this->dbAdapter,$listaPersona, $numeroFila, $numeroFila2);
                               $mensaje = '<div class="alert alert-success text-center" role="alert">MODIFICADO CORRECTAMENTE</div>';
                               $validar = TRUE;
                               return new JsonModel(array('tabla'=>$tablaPersona,'numeroFila'=>$numeroFila,'numeroFila2'=>$numeroFila2,'idPersona'=>$idPersonaEncriptado,'mensaje'=>$mensaje,'validar'=>$validar));
                               
                            }
                 
                        }   
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }        
            
            
            
            
            
    public function eliminarpersonaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 1, 1);
                if ($validarprivilegio==false)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE ELIMINAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objUsuario = new Usuario($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idPersonaEncriptado = $post['id'];
                        $numeroFila = $post['numeroFila'];
                     
                        if($idPersonaEncriptado == NULL || $idPersonaEncriptado == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                        }else if(!is_numeric($numeroFila)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL NÚMERO DE LA FILA</div>';
                        }else{
                            $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                            $listaPersona = $objPersona->FiltrarPersona($idPersona);
                            if(count($listaPersona) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA NO EXISTE</div>';
                            }else if (count($objUsuario->FiltrarUnUsuarioPorPersona($idPersona))>0)
                            {
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA TIENE UN USUARIO ASIGNADO, NO PUEDE SER ELIMINADA</div>';

                            }else{
                                $resultado = $objPersona->EliminarPersona($idPersona);
                                if(count($resultado) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ  A LA PERSONA</div>';
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
    
    
    public function ingresarpersonaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 1, 3);
                if ($validarprivilegio==false)
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                        $objTelefono = new Telefonos($this->dbAdapter);
                        $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );

                        $idIglesia = $sesionUsuario->offsetGet('idIglesia');
                        $identificacion = trim($post['identificacion']);
                        $primerNombre = trim(strtoupper($post['primerNombre']));
                        $segundoNombre = trim(strtoupper($post['segundoNombre']));
                        $primerApellido = trim(strtoupper($post['primerApellido']));
                        $segundoApellido = trim(strtoupper($post['segundoApellido']));
                        $numeroTelefono = trim($post['telefono']);
                        $idProvinciaEncriptado = $post['selectProvincias'];
                        $idCantonEncriptado = $post['selectCantones'];
                        $idConfigurarParroquiaCantonEncriptado = $post['selectParroquias'];
                        $direccion = trim(strtoupper($post['direccion']));
                        $referencia = trim(strtoupper($post['referencia']));
                        $fechaNacimiento = $post['fechaNacimiento'];
                        $idSexoEncriptado = $post['sexo'];
                        $validarIngresoCedula = FALSE;
                        if(strlen($identificacion) > 0){
                            $validarIngresoCedula = true;
                        }
                        if($validarIngresoCedula == true && $objMetodos->validarIdentificacion($identificacion) == FALSE){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IDENTIFICACIÓN INGRESADA NO ES CORRECTA</div>';
                        }else if($validarIngresoCedula == true && count($objPersona->FiltrarPersonaPorIdentificacion($identificacion)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
                        }else if($idIglesia == NULL || $idIglesia == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA IGLESIA</div>';
                        }else if(empty ($primerNombre) || strlen($primerNombre) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL PRIMER NOMBRE MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($segundoNombre) || strlen($segundoNombre) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO NOMBRE MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($primerApellido) || strlen($primerApellido) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE PRIMER APELLIDO MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($segundoApellido) || strlen($segundoApellido) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO APELLIDO MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($numeroTelefono) || strlen($numeroTelefono) > 20){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NÚMERO DE TELÉFONO MÁXIMO 20 CARACTERES</div>';
                        }else if(empty ($fechaNacimiento)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO</div>';
                        }else if($idSexoEncriptado == NULL || $idSexoEncriptado == "" || $idSexoEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN SEXO</div>';
                        }else if($idProvinciaEncriptado == NULL || $idProvinciaEncriptado == "" || $idProvinciaEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVINCIA</div>';
                        }else if($idCantonEncriptado == NULL || $idCantonEncriptado == "" || $idCantonEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN CANTÓN</div>';
                        }else if($idConfigurarParroquiaCantonEncriptado == NULL || $idConfigurarParroquiaCantonEncriptado == "" || $idConfigurarParroquiaCantonEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PARRÓQUIA</div>';
                        }else if(empty ($direccion) || strlen($direccion) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA DIRECCIÓN MÁXIMO 200 CARACTERES</div>';
                        }else if(empty ($referencia) || strlen($referencia) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA REFERENCIA MÁXIMO 200 CARACTERES</div>';
                        }else{
                            $idSexo = $objMetodos->desencriptar($idSexoEncriptado);
                            
                            $idConfigurarParroquiaCanton = $objMetodos->desencriptar($idConfigurarParroquiaCantonEncriptado); 
                            $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCanton($idConfigurarParroquiaCanton);
                            if(count($listaConfigurarParroquiaCanton) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA DIRECCIÓN SELECCIONADA NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                            }else{
                                ini_set('date.timezone','America/Bogota'); 
                                $hoy = getdate();
                                $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                               $arrayPersona = array(
                                   'idIglesia' => $idIglesia,
                                    'identificacion' =>$identificacion,
                                    'primerNombre' => $primerNombre,
                                    'segundoNombre' => $segundoNombre,
                                    'primerApellido' => $primerApellido,
                                    'segundoApellido' => $segundoApellido,
                                    'fechaNacimiento'=>$fechaNacimiento,
                                    'idSexo'=>$idSexo,
                                    'fechaRegistro' => $fechaSubida,
                                    'estadoPersona' => 1);
                                $resultado =  $objPersona->IngresarPersona($arrayPersona);
                                if(count($resultado) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                }else{
                                    $idPersona = $resultado[0]['idPersona'];
                                    
                                if(count($objDireccionPersona->IngresarDireccionPersona(
                                        $idPersona, $idConfigurarParroquiaCanton,$direccion,$referencia, $fechaSubida, 1
                                        )) == 0){
                                        $objPersona->EliminarPersona($idPersona);
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{
                                        $listaTelefono = $objTelefono->FiltrarTelefonoPorNumero($numeroTelefono);
                                        $idTelefono = 0;
                                        if(count($listaTelefono) > 0){
                                            $idTelefono = $listaTelefono[0]['idTelefono'];
                                        }else{
                                            $resultadoTelefono = $objTelefono->IngresarTelefono($numeroTelefono,1);
                                            if(count($resultadoTelefono) > 0){
                                                $idTelefono = $resultadoTelefono[0]['idTelefono'];
                                            }
                                        }
                                        if($idTelefono == 0){
                                            $objPersona->EliminarPersona($idPersona);
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                        }else{
                                           
                                            $resultadoTelefonoPersona = $objTelefonoPersona->IngresarTelefonoPersona($idPersona,$idTelefono,$fechaSubida,1);
                                            if(count($resultadoTelefonoPersona) == 0){
                                                $objPersona->EliminarPersona($idPersona);
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
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
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    public function  obtenerdireccionpersonaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{               
                    $objMetodos = new Metodos();
                    $objPersona = new Persona($this->dbAdapter);
                    $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                    $objProvincias = new Provincias($this->dbAdapter);
                    $objParroquias = new Parroquias($this->dbAdapter);
                    $objConfigurarCantonProvincia  = new ConfigurarCantonProvincia($this->dbAdapter);
                    $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );


                    $idPersonaEncriptado = $post['id'];
                    $i = $post['i'];
                    $j = $post['j'];
                    if($idPersonaEncriptado == NULL || $idPersonaEncriptado == "" ){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';

                    }else if(!is_numeric($i)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else if(!is_numeric($j)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else{
                        $idPersona = $objMetodos->desencriptar($idPersonaEncriptado); 
                        $listaPersona = $objPersona->FiltrarPersona($idPersona);
                        if(count($listaPersona) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                        }else{
                            $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
                            $tabla = '';
                            if(count($listaDireccionPersona) > 0)
                            {
                                $objMetodosC = new MetodosControladores();
                                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 1, 2);
                                
                                if ($validarprivilegio==false)
                                {
                                     $tabla = '<div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th>PROVINCIA</th>
                                                <td>'.$listaDireccionPersona[0]['nombreProvincia'].'</td>
                                            </tr>
                                            <tr>
                                                <th>CANTÓN</th>
                                                <td>'.$listaDireccionPersona[0]['nombreCanton'].'</td>
                                            </tr>
                                            <tr>
                                                <th>PARRÓQUIA</th>
                                                <td>'.$listaDireccionPersona[0]['nombreParroquia'].'</td>
                                            </tr>
                                            <tr>
                                                <th>DIRECCIÓN</th>
                                                 <td>'.$listaDireccionPersona[0]['direccionPersona'].'</td>
                                            </tr>
                                            <tr>
                                                <th>REFERENCIA</th>
                                                 <td>'.$listaDireccionPersona[0]['referenciaDireccionPersona'].'</td>
                                            </tr>
                                        </table>
                                    </div>';
                                } 
                                else {                                 

                                    $optionParroquias = '<option value="0">SELECCIONE UNA PARRÓQUIA</option>';
                                    $optionProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                                    $optionCantones = '<option value="0">SELECCIONE UN CANTÓN</option>';
                                    foreach ($objProvincias->ObtenerProvincias() as $valueProvincias) {
                                        $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                                        if($valueProvincias['idProvincia'] == $listaDireccionPersona[0]['idProvincia']){
                                            $optionProvincias = $optionProvincias.'<option selected value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                                            $listaConfigurarCantonProvincia = $objConfigurarCantonProvincia->FiltrarConfigurarCantonProvinciaPorProvincia($valueProvincias['idProvincia'], true);
                                            foreach ($listaConfigurarCantonProvincia as $valueCanton) {
                                                $idCantonEncriptado = $objMetodos->encriptar($valueCanton['idCanton']);
                                                if($valueCanton['idCanton'] == $listaDireccionPersona[0]['idCanton']){
                                                    $optionCantones = $optionCantones.'<option selected value="'.$idCantonEncriptado.'">'.$valueCanton['nombreCanton'].'</option>';


                                                    $listaConfigurarCantonProvincia = $objConfigurarCantonProvincia->FiltrarConfigurarCantonProvinciaPorProvinciaCanton($valueProvincias['idProvincia'],$valueCanton['idCanton'], true);
                                                    foreach ($listaConfigurarCantonProvincia as $valueConfigurarCantonProvincia) {
                                                        $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvincia($valueConfigurarCantonProvincia['idConfigurarCantonProvincia']);
                                                        foreach ($listaConfigurarParroquiaCanton as $valueConfigurarParroquiaCanton) {
                                                            $listaParroquia = $objParroquias->FiltrarParroquia($valueConfigurarParroquiaCanton['idParroquia']);
                                                            $idConfigurarParroquiaCantonEncriptado = $objMetodos->encriptar($valueConfigurarParroquiaCanton['idConfigurarParroquiaCanton']);
                                                            if($valueConfigurarParroquiaCanton['idParroquia'] == $listaDireccionPersona[0]['idParroquia'])
                                                                $optionParroquias = $optionParroquias.'<option selected value="'.$idConfigurarParroquiaCantonEncriptado.'">'.$listaParroquia[0]['nombreParroquia'].'</option>';
                                                            else
                                                                $optionParroquias = $optionParroquias.'<option value="'.$idConfigurarParroquiaCantonEncriptado.'">'.$listaParroquia[0]['nombreParroquia'].'</option>';
                                                        }
                                                    }  

                                                }else
                                                    $optionCantones = $optionCantones.'<option value="'.$idCantonEncriptado.'">'.$valueCanton['nombreCanton'].'</option>';
                                            }
                                        }else
                                            $optionProvincias = $optionProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';

                                        }   
                                        $idDireccionEncriptado = $objMetodos->encriptar($listaDireccionPersona[0]['idDireccionPersona']);
                                        $tabla = '<div class="form-group col-lg-12">
                                            <input value="'.$j.'" type="hidden" id="numeroFila2" name="numeroFila2">
                                            <input value="'.$i.'" type="hidden" id="numeroFila" name="numeroFila">
                                            <input value="'.$idDireccionEncriptado.'" type="hidden" id="direccionPersonaEncriptado" name="direccionPersonaEncriptado">
                                            <label for="selectProvinciasM">PROVINCIA</label>
                                            <select onchange="filtrarConfigurarCantonProvinciaPorProvinciaM();" id="selectProvinciasM" name="selectProvinciasM" class="form-control">'.$optionProvincias.'</select>
                                            <label for="selectCantonesM">CANTÓN</label>
                                            <select onchange="filtrarConfigurarParroquiaCantonPorConfigurarCantonProvinciaM();" id="selectCantonesM" name="selectCantonesM" class="form-control">
                                                '.$optionCantones.'
                                            </select>
                                            <label for="selectParroquiasM">PARRÓQUIA</label>
                                            <select id="selectParroquiasM" name="selectParroquiasM" class="form-control">
                                                '.$optionParroquias.'
                                            </select>
                                            <label for="direccionM">DIRECCIÓN</label>
                                            <input value="'.$listaDireccionPersona[0]['direccionPersona'].'" maxlength="200" autocomplete="off" type="text" id="direccionM" name="direccionM" class="form-control">
                                            <label for="referenciaM">REFERENCIA</label>
                                            <input value="'.$listaDireccionPersona[0]['referenciaDireccionPersona'].'" maxlength="200" autocomplete="off" type="text" id="referenciaM" name="referenciaM" class="form-control">
                                        </div>
                                        <div class="form-group col-lg-12">
                                            <button data-loading-text="GUARDANDO..." id="btnModificarDireccion" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>
                                        </div>';
                                    }
                                    $mensaje = '';
                                    $validar = TRUE;
                                    return new JsonModel(array('val'=>$validarprivilegio,'mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));

        //                               
                                }
                            
                        }
                    }

                }  
            }
        }
        
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }

    public function obtenerpersonasAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objPersona = new Persona($this->dbAdapter);
                   
                    $listaPersonas = $objPersona->ObtenerPersonas();
                    $tabla = $this->CargarTablaPersonaAction($idUsuario,$this->dbAdapter,$listaPersonas, 0, count($listaPersonas));
                    

                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }                    
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    function CargarTablaPersonaAction($idUsuario,$adaptador,$listaPersonas, $i, $j)
    {
        $objUsuario = new Usuario($adaptador);
        $objTelefono = new Telefonos($adaptador);
        $objTelefonoPersona = new TelefonoPersona($adaptador);
        $objDireccionPersona = new DireccionPersona($adaptador);
        $objMetodosControler = new MetodosControladores();
        $objHistorialPersona = new HistorialPersona($adaptador);
        $objSacerdote = new Sacerdotes($adaptador);
        $objDocente = new Docentes($adaptador);
        $objBautismo = new Bautismo($adaptador);
        $objAdministrativo = new Administrativos($adaptador);
        
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $array1 = array();
        $validarPrivilegioEliminar = $objMetodosControler->ValidarPrivilegioAction($adaptador, $idUsuario, 1, 1);
        $validarPrivilegioModificar = $objMetodosControler->ValidarPrivilegioAction($adaptador, $idUsuario, 1, 2);
        foreach ($listaPersonas as $value) {
            $idPersonaEncriptado = $objMetodos->encriptar($value['idPersona']);
            $listaTelefonoPersona = $objTelefonoPersona->FiltrarTelefonoPersonaPorPersonaEstado($value['idPersona'], 1);
            $numeroTelefono = '';
            if(count($listaTelefonoPersona) > 0){
                $listaTelefono = $objTelefono->FiltrarTelefono($listaTelefonoPersona[0]['idTelefono']);
                $numeroTelefono = $listaTelefono[0]['numeroTelefono'];
            }
            $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersonaPorPersonaEstado($value['idPersona'], 1);

            $botonDireccion ="";
            if (count($listaDireccionPersona)>0)
            {
                $botonDireccion = '<button data-target="#modalVerDireccionPersona" data-toggle="modal" id="btnFiltrarDireccion'.$i.'" title="VER DIRECCIÓN" onclick="FiltrarDireccionPorPersona(\''.$idPersonaEncriptado.'\','.$i.','.$j.')" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-home"></i></button>';
            }
            $botonEliminarPersona = '';
            
            $listaSacerdote = $objSacerdote->FiltrarSacerdotePorPersona($value['idPersona']);
            $listaDocente = $objDocente->FiltrarDocentePorPersona($value['idPersona']);
            $listaBautismo = $objBautismo->FiltrarBautismoPorPersona($value['idPersona']);
            $listaAdministrativos = $objAdministrativo->FiltrarAdministrativoPorPersona($value['idPersona']);
            
            if($validarPrivilegioEliminar == true){
               if(count($objHistorialPersona->FiltrarHistorialPersonaPorPersona($value['idPersona'])) == 0 && count($objUsuario->FiltrarUnUsuarioPorPersona($value['idPersona']))==0)
               {
                   if(count($listaDocente) == 0 && count($listaBautismo) == 0 && count($listaSacerdote) == 0 && count($listaAdministrativos) == 0){
                        $botonEliminarPersona = '<button id="btnEliminarPersona'.$i.'" title="ELIMINAR A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="EliminarPersona(\''.$idPersonaEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                   }
                   
               }
            }
            $botonModificar ='';
            if($validarPrivilegioModificar == true)
                $botonModificar = '<button data-target="#modalModificarPersona" data-toggle="modal" id="btnModificarPersona'.$i.'" title="MODIFICAR A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioModificarPersona(\''.$idPersonaEncriptado.'\','.$i.','.$j.')" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-pencil"></i></button>';
            
            $identificacion = $value['identificacion'];
            $nombres = $value['primerNombre'].' '.$value['segundoNombre'];
            $apellidos = $value['primerApellido'].' '.$value['segundoApellido'];
            $fechaRegistro = $objMetodos->obtenerFechaEnLetra($value['fechaRegistro']);
            $fechaNacimiento2 = new \DateTime($value['fechaNacimiento']);
            $fechaActual = new \DateTime(date("d-m-Y"));
            $diff = $fechaActual->diff($fechaNacimiento2);
            $fechaNacimiento = $objMetodos->obtenerFechaEnLetraSinHora($value['fechaNacimiento']);
             $botones = $botonEliminarPersona .' '.$botonModificar;  
             
             
            $array1[$i] = array(
                '_j'=>$j,
                '_idPersonaEncriptado'=>$idPersonaEncriptado,
                'identificacion'=>$identificacion,
                'nombres'=>$nombres,
                'apellidos'=>$apellidos,
                'fechaNacimiento'=>$fechaNacimiento,
                'edad'=>$diff->y,
                'numeroTelefono'=>$numeroTelefono,
                'botonVerDireccion'=>$botonDireccion,
                'fechaRegistro'=>$fechaRegistro,
                'opciones'=>$botones,
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    
    
    public function obtenerformulariomodificarpersonaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else{
                $objMetodosControlador =  new MetodosControladores();
                
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{               
                    $objMetodos = new Metodos();
                    $objPersona = new Persona($this->dbAdapter);
                    $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                    $objProvincias = new Provincias($this->dbAdapter);
                    $objParroquias = new Parroquias($this->dbAdapter);
                    $objTelefonoPersona = new TelefonoPersona($this->dbAdapter);
                    $objTelefono = new Telefonos($this->dbAdapter);
                    $objConfigurarCantonProvincia  = new ConfigurarCantonProvincia($this->dbAdapter);
                    $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );


                    $idPersonaEncriptado = $post['id'];
                    $i = $post['i'];
                    $j = $post['j'];
                    if($idPersonaEncriptado == NULL || $idPersonaEncriptado == "" ){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';

                    }else if(!is_numeric($i)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else if(!is_numeric($j)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else{
                        $idPersona = $objMetodos->desencriptar($idPersonaEncriptado); 
                        $listaPersona = $objPersona->FiltrarPersona($idPersona);
                        if(count($listaPersona) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                        }else{
                            
                            $tabla = '';
//                            if($objMetodosControlador->ValidarPrivilegioAction($adaptador, $idUsuario, 1, 1));
                            
                            $inputIdentificacion = '<input  value="0" type="hidden" id="identificacionM" name="identificacionM" class="form-control">';
                            if($listaPersona[0]['identificacion'] == ""){
                                $inputIdentificacion = '<label for="identificacionM">IDENTIFICACIÓN</label>
                                    <input onkeydown="validarNumeros(\'identificacionM\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacionM" name="identificacionM" class="form-control">';
                                                           
                            }
                            
                             
                            $tabla = '<div class="form-group col-lg-12">
                                    <input type="hidden" value="'.$i.'" id="im" name="im">
                                    <input type="hidden" value="'.$j.'" id="jm" name="jm">
                                    <input type="hidden" value="'.$idPersonaEncriptado.'" name="idPersonaEncriptadoM" id="idPersonaEncriptadoM">
                                    '.$inputIdentificacion.'
                                    <label for="primerNombreM">PRIMER NOMBRE</label>
                                    <input value="'.$listaPersona[0]['primerNombre'].'" maxlength="50" autocomplete="off" type="text" id="primerNombreM" name="primerNombreM" class="form-control">
                                    <label for="segundoNombreM">SEGUNDO NOMBRE</label>
                                    <input value="'.$listaPersona[0]['segundoNombre'].'" maxlength="50" autocomplete="off" type="text" id="segundoNombreM" name="segundoNombreM" class="form-control">
                                    <label for="primerApellidoM">PRIMER APELLIDO</label>
                                    <input value="'.$listaPersona[0]['primerApellido'].'" maxlength="50" autocomplete="off" type="text" id="primerApellidoM" name="primerApellidoM" class="form-control">
                                    <label for="segundoApellidoM">SEGUNDO APELLIDO</label>
                                    <input value="'.$listaPersona[0]['segundoApellido'].'" maxlength="50" autocomplete="off" type="text" id="segundoApellidoM" name="segundoApellidoM" class="form-control">
                                    <label for="fechaNacimientoM">FECHA DE NACIMIENTO</label>
                                    <input value="'.$listaPersona[0]['fechaNacimiento'].'" type="date" id="fechaNacimientoM" name="fechaNacimientoM" class="form-control">

                                </div>
                                <div class="form-group col-lg-12">
                                    <button data-loading-text="GUARDANDO..." id="btnGuardarPersonaM" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>
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
    
    public function modificarpersonaAction()
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
            $objHistorialPersona = new HistorialPersona($this->dbAdapter);
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 1);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 1, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS PARA MODIFICAR EN ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idPersonaEncriptadoM = $post['idPersonaEncriptadoM'];
                        $im = $post['im'];
                        $jm = $post['jm'];
                        $idIglesia = $sesionUsuario->offsetGet('idIglesia');
                        $identificacion = trim($post['identificacionM']);
                        $primerNombre = trim(strtoupper($post['primerNombreM']));
                        $segundoNombre = trim(strtoupper($post['segundoNombreM']));
                        $primerApellido = trim(strtoupper($post['primerApellidoM']));
                        $segundoApellido = trim(strtoupper($post['segundoApellidoM']));                     
                        $fechaNacimiento = $post['fechaNacimientoM'];
                        $validarIngresoCedula = FALSE;
                        if($identificacion != '0' && !empty($identificacion)){
                            $validarIngresoCedula = TRUE;
                        }
                        if($validarIngresoCedula == true && $objMetodos->validarIdentificacion($identificacion) == FALSE){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IDENTIFICACIÓN INGRESADA NO ES CORRECTA</div>';
                        }else if($validarIngresoCedula == true && count($objPersona->FiltrarPersonaPorIdentificacion($identificacion)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
                        }else if($idIglesia == NULL || $idIglesia == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA IGLESIA</div>';
                        }else if(empty ($primerNombre) || strlen($primerNombre) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL PRIMER NOMBRE MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($segundoNombre) || strlen($segundoNombre) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO NOMBRE MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($primerApellido) || strlen($primerApellido) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE PRIMER APELLIDO MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($segundoApellido) || strlen($segundoApellido) > 50){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO APELLIDO MÁXIMO 50 CARACTERES</div>';
                        }else if(empty ($fechaNacimiento)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO</div>';
                        }else{   
                            
                            $idPersona= $objMetodos->desencriptar($idPersonaEncriptadoM);
                            $listaPersona = $objPersona->FiltrarPersona($idPersona);
                            if(count($listaPersona)>0)
                            {
                                if($validarIngresoCedula == FALSE)
                                    $identificacion = $listaPersona[0]['identificacion'];
                                ini_set('date.timezone','America/Bogota'); 
                                $hoy = getdate();
                                $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
     
                                $estado = $listaPersona[0]['estadoPersona'];
                                $fechaRegistro = $listaPersona[0]['fechaRegistro'];
                                $resultado =  $objPersona->ModificarPersona($idPersona, $idIglesia, $identificacion, $primerNombre, $segundoNombre, $primerApellido, $segundoApellido, $fechaNacimiento, $fechaSubida, $estado);
                                if(count($resultado) == 0){                                    
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                }else{
                                     $resultado2 =  $objHistorialPersona->IngresarHistorialPersona(
                                             $idUsuario, $idPersona, $idIglesia, 
                                     $listaPersona[0]['identificacion'],$listaPersona[0]['primerNombre'], $listaPersona[0]['segundoNombre'], $listaPersona[0]['primerApellido'], $listaPersona[0]['segundoApellido'], $listaPersona[0]['fechaNacimiento'], $fechaRegistro, $estado);
                                     
                                     $tablaPersona ="";
                                     $tablaPersona = $this->CargarTablaPersonaAction($idUsuario, $this->dbAdapter,$resultado, $im, $jm);
                                     
                                     $mensaje = '<div class="alert alert-success text-center" role="alert">MODIFICADO CORRECTAMENTE</div>';
                                     $validar = TRUE;
                                     
                                    return new JsonModel(array('tabla'=>$tablaPersona,'idPersona'=>$idPersonaEncriptadoM,'jm'=>$jm,'im'=>$im,'mensaje'=>$mensaje,'validar'=>$validar));
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