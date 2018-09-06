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
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Provincias;
use Nel\Modelo\Entity\Telefonos;
use Nel\Modelo\Entity\TelefonoPersona;
use Nel\Modelo\Entity\HistorialPersona;
use Nel\Modelo\Entity\DireccionPersona;
use Nel\Modelo\Entity\Parroquias;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Nel\Modelo\Entity\ConfigurarCantonProvincia;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class PersonaController extends AbstractActionController
{
    public $dbAdapter;
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
                                    'fechaRegistro' => $fechaSubida,
                                    'estadoPersona' => 1);
                                $resultado =  $objPersona->IngresarPersona($arrayPersona);
                                if(count($resultado) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                }else{
                                    $idPersona = $resultado[0]['idPersona'];
                                    $arrayDireccionPersona = array(
                                        'idPersona' => $idPersona,
                                        'idConfigurarParroquiaCanton' => $idConfigurarParroquiaCanton,
                                        'direccionPersona' => $direccion,
                                        'referenciaDireccionPersona' => $referencia,
                                        'fechaIngresoDireccionPersona' => $fechaSubida,
                                        'estadoDireccionPersona' => 1
                                    );
                                    if(count($objDireccionPersona->IngresarDireccionPersona($arrayDireccionPersona)) == 0){
                                        $objPersona->EliminarPersona($idPersona);
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{
                                        $listaTelefono = $objTelefono->FiltrarTelefonoPorNumero($numeroTelefono);
                                        $idTelefono = 0;
                                        if(count($listaTelefono) > 0){
                                            $idTelefono = $listaTelefono[0]['numeroTelefono'];
                                        }else{
                                            $arrayTelefono = array(
                                              'numeroTelefono'=>$numeroTelefono,
                                                'estadoTelefono'=>1
                                            );
                                            $resultadoTelefono = $objTelefono->IngresarTelefono($arrayTelefono);
                                            if(count($resultadoTelefono) > 0){
                                                $idTelefono = $resultadoTelefono[0]['idTelefono'];
                                            }
                                        }
                                        if($idTelefono == 0){
                                            $objPersona->EliminarPersona($idPersona);
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PERSONA POR FAVOR INTENTE MÁS TARDE</div>';
                                        }else{
                                            $arrayTelefonoPersona = array(
                                                'idPersona'=>$idPersona,
                                                'idTelefono'=>$idTelefono,
                                                'fechaRegistro'=>$fechaSubida,
                                                'estadoTelefonoPersona'=>1
                                            );
                                            $resultadoTelefonoPersona = $objTelefonoPersona->IngresarTelefonoPersona($arrayTelefonoPersona);
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

                    $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );


                    $idPersonaEncriptado = $post['id'];
                    $i = $post['i'];
                    if($idPersonaEncriptado == NULL || $idPersonaEncriptado == "" ){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';

                    }else if(!is_numeric($i)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else{
                        $idPersona = $objMetodos->desencriptar($idPersonaEncriptado); 
                        $listaPersona = $objPersona->FiltrarPersona($idPersona);
                        if(count($listaPersona) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                        }else{
                            $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
                            $tabla = '';
                            if(count($listaDireccionPersona) > 0){
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
        $objTelefono = new Telefonos($adaptador);
        $objTelefonoPersona = new TelefonoPersona($adaptador);
        $objDireccionPersona = new DireccionPersona($adaptador);
        $objMetodosControler = new MetodosControladores();
        $objHistorialPersona = new HistorialPersona($adaptador);
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $array1 = array();
//      $i = 0;
//      $j = count($listaPersonas);
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
                $botonDireccion = '<button data-target="#modalVerDireccionPersona" data-toggle="modal" id="btnFiltrarDireccion'.$i.'" title="VER DIRECCIÓN" onclick="FiltrarDireccionPorPersona(\''.$idPersonaEncriptado.'\','.$i.')" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-home"></i></button>';
            }
            $botonEliminarPersona = '';
            if($objMetodosControler->ValidarPrivilegioAction($adaptador, $idUsuario, 1, 1) == true){
               if(count($objHistorialPersona->FiltrarHistorialPersonaPorPersona($value['idPersona'])) == 0)
                $botonEliminarPersona = '<button id="btnEliminarPersona'.$i.'" title="ELIMINAR A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="EliminarPersona(\''.$idPersonaEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
            }
            if($objMetodosControler->ValidarPrivilegioAction($adaptador, $idUsuario, 1, 2) == true)
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
                            
                            $listaDireccionPersona = $objDireccionPersona->FiltrarDireccionPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
                            $tabla = '';
//                            if(count($listaDireccionPersona) > 0){
//                                
//                            }
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
                            
                            
                            $listaTelefonoPersona = $objTelefonoPersona->FiltrarTelefonoPersonaPorPersonaEstado($listaPersona[0]['idPersona'], 1);
                            $numeroTelefono = '';
                            if(count($listaTelefonoPersona) > 0){
                                $listaTelefono = $objTelefono->FiltrarTelefono($listaTelefonoPersona[0]['idTelefono']);
                                $numeroTelefono = $listaTelefono[0]['numeroTelefono'];
                            }
                            
                            $tabla = '<div class="form-group col-lg-6">
                                <label for="identificacionM">IDENTIFICACIÓN</label>
                                <input value="'.$listaPersona[0]['identificacion'].'" onkeydown="validarNumeros(\'identificacionM\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacionM" name="identificacionM" class="form-control">
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


                            <div class="form-group col-lg-6">
                                <label for="telefonoM">TELÉFONO</label>
                                <input value="'.$numeroTelefono.'" onkeydown="validarNumeros(\'telefono\')" maxlength="20" autocomplete="off" type="text" id="telefonoM" name="telefonoM" class="form-control">
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

}