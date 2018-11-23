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
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\Sacerdotes;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Bautismo;
use Nel\Modelo\Entity\LugaresMisa;
use Nel\Modelo\Entity\PadresBautismo;
use Nel\Modelo\Entity\PadrinosBautismo;
use Nel\Modelo\Entity\Provincias;
use Nel\Modelo\Entity\Cantones;
use Nel\Modelo\Entity\Parroquias;
use Nel\Modelo\Entity\Matrimonio;
use Nel\Modelo\Entity\PadrinosConfirmacion;
use Nel\Modelo\Entity\Defuncion;
use Nel\Modelo\Entity\Confirmacion;
use Nel\Modelo\Entity\ConfigurarCantonProvincia;
use Nel\Modelo\Entity\Administrativos;
use Nel\Modelo\Entity\TipoPadre;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class DefuncionController extends AbstractActionController
{

    public $dbAdapter;
    
    public function ingresardefuncionAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 21);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else{
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 21, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objSacerdote = new Sacerdotes($this->dbAdapter);
                        $objDefuncion = new Defuncion($this->dbAdapter);
                        $objLugar = new LugaresMisa($this->dbAdapter);
                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idPersonaEncriptado = $post['idPersonaEncriptado'];
                        $nacionalidad = strtoupper(trim($post['nacionalidad']));
                        $fechaFallecimiento = $post['fechaFallecimiento'];
                        $idConfigurarParroquiaCantonEncriptado = $post['selectParroquias'];
                        $causaMuerte = strtoupper(trim($post['causaMuerte']));
                        $nombresPadre = strtoupper(trim($post['nombresPadre']));
                        $nombreMadre = strtoupper(trim($post['nombresMadre']));
                        $nombreConyugue = strtoupper(trim($post['nombreConyugue']));
                        
                        $anoRegistroCivil = trim($post['anoRegistroCivil']);
                        $tomoRegistroCivil = strtoupper(trim($post['tomoRegistroCivil']));
                        $folioRegistroCivil = strtoupper(trim($post['folioRegistroCivil']));
                        $actaRegistroCivil = strtoupper(trim($post['actaRegistroCivil']));
                        $fechaInscripcionRegistroCivil = $post['fechaInscripcionRegistroCivil'];
                        
                        $anoEclesiastico = trim($post['anoEclesiastico']);
                        $tomoEclesiastico = strtoupper(trim($post['tomoEclesiastico']));
                        $folioEclesiastico = strtoupper(trim($post['folioEclesiastico']));
                        $actaEclesiastico = strtoupper(trim($post['actaEclesiastico']));
                        $fechaInscripcionEclesiastico = $post['fechaInscripcionEclesiastico'];    
                        
                        $idLugarMisaEncriptado = $post['lugarMisa'];
                        $idSacerdoteEncriptado = $post['selectSacerdote'];
                        if(empty ($idPersonaEncriptado) || $idPersonaEncriptado == NULL ){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                        }else if(empty($post['estadoCivil'])){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN ESTADO CIVIL</div>';
                        }else if(empty ($nacionalidad) || $nacionalidad == NULL || strlen($nacionalidad) > 100){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA NACIONALIDAD MÁXIMO 100 CARACTERES</div>';
                        }else if(empty ($fechaFallecimiento) || $fechaFallecimiento == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE FALLECIMIENTO</div>';
                        }else if(empty ($idConfigurarParroquiaCantonEncriptado) || $idConfigurarParroquiaCantonEncriptado == NULL || $idConfigurarParroquiaCantonEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE EL LUGAR DEL FALLECIMIENTO</div>';
                        }else if(empty ($causaMuerte) || $causaMuerte == NULL || strlen($causaMuerte) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA CAUSA DE LA MUERTE MÁXIMO 200 CARACTERES</div>';
                        }else if(empty ($nombresPadre) || $nombresPadre == NULL || strlen($nombresPadre) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LOS APELLIDOS Y NOMBRES DEL PADRE MÁXIMO 200 CARACTERES</div>';
                        }else if(empty ($nombreMadre) || $nombreMadre == NULL || strlen($nombreMadre) > 200){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LOS APELLIDOS Y NOMBRES DE LA MADRE MÁXIMO 200 CARACTERES</div>';
                        }else if(($post['estadoCivil'] == "2" || (isset($post['casadoEclesiastico']) && $post['casadoEclesiastico'] == "4")) && (empty ($nombreConyugue) || $nombreConyugue == NULL || strlen($nombreConyugue) > 200)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LOS APELLIDOS Y NOMBRES DEL CONYUGUE MÁXIMO 200 CARACTERES</div>';
                        }else  if(($post['estadoCivil'] != "2" && (!isset($post['casadoEclesiastico']))) && (!empty ($nombreConyugue))){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SI LA PERSONA NO ESTUVO CASADA DEJE EL CAMPO DE CONYUGUE VACÍO</div>';
                        }else if(!is_numeric ($anoRegistroCivil) || strlen($anoRegistroCivil) > 4){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL AÑO DEL REGISTRO CIVIL 4 DÍGITOS</div>';
                        }else if(empty ($tomoRegistroCivil)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL TOMO DEL REGISTRO CIVIL</div>';
                        }else if(empty ($folioRegistroCivil)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL FOLIO DEL REGISTRO CIVIL</div>';
                        }else if(empty ($actaRegistroCivil)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL ACTA DEL REGISTRO CIVIL</div>';
                        }else if(empty ($fechaInscripcionRegistroCivil)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE INSCRIPCIÓN DEL REGISTRO CIVIL</div>';
                        }else if(!is_numeric ($anoEclesiastico) || strlen($anoEclesiastico) > 4){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL AÑO DEL REGISTRO ELCESIÁSTICO 4 DÍGITOS</div>';
                        }else if(empty ($tomoEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL TOMO DEL REGISTRO ELCESIÁSTICO</div>';
                        }else if(empty ($folioEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL FOLIO DEL REGISTRO ELCESIÁSTICO</div>';
                        }else if(empty ($actaEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL ACTA DEL REGISTRO ELCESIÁSTICO</div>';
                        }else if(empty ($fechaInscripcionEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE INSCRIPCIÓN DEL REGISTRO ELCESIÁSTICO</div>';
                        }else if(empty ($idLugarMisaEncriptado) || $idLugarMisaEncriptado == NULL || $idLugarMisaEncriptado == "0" ){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE EL LUGAR DE LA MISA</div>';
                        }else  if(empty ($idSacerdoteEncriptado) || $idSacerdoteEncriptado == NULL || $idSacerdoteEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN SACERDOTE</div>';
                        }else {
                            
                            $idPersona  = $objMetodos->desencriptar($idPersonaEncriptado);
                            $listaPersona = $objPersona->FiltrarPersona($idPersona);
                            if(count($listaPersona) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA NO EXISTE EN LA BASE DE DATOS</div>';
                            }else{
                                $listaDefuncion = $objDefuncion->FiltrarDefuncionPorPersona($idPersona);
                                if(count($listaDefuncion) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA YA TIENE LA DEFUNCIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
                                }else{
                                    $estadoCivil ='SOLTERO';
                                    if($post['estadoCivil'] == "2"){
                                        $estadoCivil ='CASADO';
                                    }else if($post['estadoCivil'] == "3"){
                                        $estadoCivil ='DIVORCIADO';
                                    }else if($post['estadoCivil'] == "4"){
                                        $estadoCivil ='UNIÓN LIBRE';
                                    }
                                    $casadoEcleciastico = 0;
                                    if(isset($post['casadoEclesiastico'])){
                                        $casadoEcleciastico = 1;
                                    }

                                    ini_set('date.timezone','America/Bogota');    
                                    $fechaActualCom =  strtotime(date("d-m-Y"));
                                    $fechaFallecimientoCom = strtotime($fechaFallecimiento);

                                    if($fechaFallecimientoCom > $fechaActualCom){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE FALLECIMIENTO NO DEBE SER MAYOR A LA ACTUAL</div>';
                                    }else{
                                        $idConfigurarParroquiaCanton = $objMetodos->desencriptar($idConfigurarParroquiaCantonEncriptado);
                                        $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCanton($idConfigurarParroquiaCanton);
                                        if(count($listaConfigurarParroquiaCanton) == 0){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL LUGAR DE FALLECIMIENTO SELECCIONADO NO EXISTE EN LA BASE DE DATOS</div>';
                                        }else{
                                            $sacramentoDefuncion = 0;
                                            if(!empty($post['sacramentoDefuncion'])){
                                                $sacramentoDefuncion = TRUE;
                                            }
                                            $fechaInscripcionRegistroCivilCom = strtotime($fechaInscripcionRegistroCivil);
                                            $fechaInscripcionEclesiasticoCom = strtotime($fechaInscripcionEclesiastico);
                                            if($fechaInscripcionRegistroCivilCom > $fechaActualCom){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DEL REGISTRO CIVIL NO DEBE SER MAYOR A LA ACTUAL</div>';
                                            }else if($fechaInscripcionEclesiasticoCom > $fechaActualCom){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DEL REGISTRO ECLESIÁSTICO NO DEBE SER MAYOR A LA ACTUAL</div>';
                                            }else if($fechaFallecimientoCom > $fechaInscripcionRegistroCivilCom){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DEL FALLECIMIENTO NO DEBE SER MAYOR A LA DEL REGISTRO CIVIL</div>';
                                            }else if($fechaFallecimientoCom > $fechaInscripcionEclesiasticoCom){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DEL FALLECIMIENTO NO DEBE SER MAYOR A LA DEL REGISTRO ECLESIÁSTICO</div>';
                                            }else{

                                                $idLugarMisa = $objMetodos->desencriptar($idLugarMisaEncriptado);
                                                $listaLugares = $objLugar->FiltrarLugaresMisa($idLugarMisa);
                                                $idSacerdote = $objMetodos->desencriptar($idSacerdoteEncriptado);
                                                $listaSacerdote = $objSacerdote->FiltrarSacerdote($idSacerdote);
                                                if(count($listaLugares) == 0){
                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL LUGAR DE LA MISA SELECCIONADO NO EXISTE EN LA BASE DE DATOS</div>';
                                                }else if(count($listaSacerdote) == 0){
                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SACERDOTE SELECCIONADO NO EXISTE EN LA BASE DE DATOS</div>';
                                                }else{
                                                    $sacramentoDefuncion = 0;
                                                    if(isset($post['sacramentoDefuncion'])){
                                                        $sacramentoDefuncion = TRUE;
                                                    }
                                                    $hoy = getdate();
                                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];


                                                    $resultado = $objDefuncion->IngresarDefuncion($idPersona, $estadoCivil, $nacionalidad, 
                                                            $fechaFallecimiento,$idConfigurarParroquiaCanton, $causaMuerte, $sacramentoDefuncion, 
                                                            $nombresPadre, $nombreMadre,$nombreConyugue, $casadoEcleciastico, 
                                                            $anoRegistroCivil, $tomoRegistroCivil, $folioRegistroCivil,$actaRegistroCivil, $fechaInscripcionRegistroCivil, 
                                                            $anoEclesiastico, $tomoEclesiastico, $folioEclesiastico,$actaEclesiastico, $fechaInscripcionEclesiastico, 
                                                            $idSacerdote, $idLugarMisa, $fechaSubida);


                                                    if(count($resultado) == 0){
                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA DEFUNSIÓN POR FAVOR INTENTE MÁS TARDE</div>';
                                                    }else{ 
                                                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
                                                        $validar = TRUE;
                                                    }
                                                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$resultado));
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
    
    
    
    
    
    public function filtrarpersonapornombresAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 21);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
               
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objMetodos = new Metodos();
                    $objPersona = new Persona($this->dbAdapter);
                    $objSacerdotes = new Sacerdotes($this->dbAdapter);
                    $objBautismo = new Bautismo($this->dbAdapter);
                    $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                    $objPadresBautismo = new PadresBautismo($this->dbAdapter);
                    $objPadrinosConfirmacion = new PadrinosConfirmacion($this->dbAdapter);
                    $objLugaresMisa = new LugaresMisa($this->dbAdapter);
                    $objConfirmacion = new Confirmacion($this->dbAdapter);
                    $objDefuncion = new Defuncion($this->dbAdapter);
                    $objProvincias = new Provincias($this->dbAdapter); 
                    $objMatrimonio = new Matrimonio($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );
                     $nombres = strtoupper(trim($post['nombres']));
                     $fechaNacimiento = $post['fechaNacimiento'];
                    if(empty ($nombres)){
                        $mensaje = '';
                    }else if(empty ($fechaNacimiento)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO</div>';
                    }else{
                        $listaPersona = $objPersona->FiltrarPersonaPorNombres($nombres,$fechaNacimiento);
                        if(count($listaPersona) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA '.$nombres.' NACIDO(A) EN LA FECHA '.$fechaNacimiento.' POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                        }else if(count($listaPersona) != 1){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">POR FAVOR INGRESE LOS DATOS CORRECTAMENTE O EXISTE MÁS DE UNA PERSONA CON LOS MISMOS NOMBRES APELLIDOS Y FECHA DE NACIMIENTO</div>';
                        }else if($listaPersona[0]['estadoPersona'] == FALSE){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                        } else{
                            
                            $idPersona = $listaPersona[0]['idPersona'];
                            $listaBautismo = $objBautismo->FiltrarBautismoPorPersona($idPersona);
                            if(count($listaBautismo) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA NO HA SIDO BAUTIZADA POR LO TANTO NO PUEDE RECIBIR EL SACRAMENTO DE LA DEFUNCIÓN</div>';
                            }else{
                                $idBautismo = $listaBautismo[0]['idBautismo'];
                                $listaConfirmacion = $objConfirmacion->FiltrarConfirmacionPorBautismo($idBautismo);
                                if(count($listaConfirmacion) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA NO HA SIDO CONFIRMADA POR LO TANTO NO PUEDE RECIBIR EL SACRAMENTO DE LA DEFUNCIÓN</div>';
                                }else{
                                    $tabla = '';
                                    $listaDefuncion = $objDefuncion->FiltrarDefuncionPorPersona($idPersona);
                                    if(count($listaDefuncion) == 0){
                                        $objMetodosC = new MetodosControladores();
                                        $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 21, 3);
                                        if ($validarprivilegio==false)
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                                        else{
                                            $identificacion = 'SIN IDENTIFICACIÓN';
                                            if($listaPersona[0]['identificacion'] != NULL){
                                                $identificacion = $listaPersona[0]['identificacion'] ;
                                            }
                                            $idPersonaEncriptado = $objMetodos->encriptar($listaPersona[0]['idPersona']);
                                            $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarDefuncion" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
                                            $botonCancelar = '<button id="btnCancelar" onclick="limpiarFormularioDefuncion();" type="button" class="btn btn-danger pull-right"><i class="fa fa-times"></i>CANCELAR</button>';
                                            
                                            $checkCasadoEclesiastico = '<h6><b>CASADO ECLESIÁSTICO</b></h6><input style="width:35px;height:18px; cursor: pointer;" type="checkbox" name="casadoEclesiastico" value="5">';
                                            $checkSacramentoDefuncion = '<h6><b>RECIBIÓ EL SACRAMENTO DE DEFUNCIÓN</b></h6><input style="width:35px;height:18px; cursor: pointer;" type="checkbox" name="sacramentoDefuncion" value="4">';
                                            $listaMatrimonio = $objMatrimonio->FiltrarMatrimonioPorPersona($idPersona);
                                            $nombreConyugue = '<label for="nombreConyugue">NOMBRES DEL CONYUGUE</label>
                                                        <input maxlength="300" type="text" id="nombreConyugue" name="nombreConyugue" class="form-control">';
                                            if(count($listaMatrimonio) > 0){
                                                $checkCasadoEclesiastico = '<h6><b>CASADO ECLESIÁSTICO</b></h6><input checked="" style="width:35px;height:18px; cursor: pointer;" type="checkbox" name="casadoEclesiastico" value="4">';
                                                $nombresC ='';
                                                if($listaMatrimonio[0]['idEsposa'] != $idPersona){
                                                    $listaPersonaC = $objPersona->FiltrarPersona($listaMatrimonio[0]['idEsposa']);
                                                    $nombresC = $listaPersonaC[0]['primerApellido'].' '.$listaPersonaC[0]['segundoApellido'].' '.$listaPersonaC[0]['primerNombre'].' '.$listaPersonaC[0]['segundoNombre'];
                                                }else{
                                                    $listaPersonaC = $objPersona->FiltrarPersona($listaMatrimonio[0]['idEsposo']);
                                                    $nombresC = $listaPersonaC[0]['primerApellido'].' '.$listaPersonaC[0]['segundoApellido'].' '.$listaPersonaC[0]['primerNombre'].' '.$listaPersonaC[0]['segundoNombre'];
                                                }
                                                $nombreConyugue = '<label for="nombreConyugue">NOMBRES DEL CONYUGUE</label><input type="text" name="nombreConyugue" id="nombreConyugue" class="form-control" value="'.$nombresC.'">';
                                            }
                                            
                                            $estadoCivil = '<div class="col-lg-2"><h6><b>SOLTERO</b></h6><input style="width:35px;height:18px; cursor: pointer;" type="radio" name="estadoCivil" value="1"></div> 
                                                    <div class="col-lg-2"><h6><b>CASADO</b></h6><input style="width:35px;height:18px; cursor: pointer;" type="radio" name="estadoCivil" value="2"></div>
                                                    <div class="col-lg-2"><h6><b>DIVORCIADO</b></h6><input style="width:35px;height:18px; cursor: pointer;" type="radio" name="estadoCivil" value="3"></div>
                                                    <div class="col-lg-2"><h6><b>UNIÓN LIBRE</b></h6><input style="width:35px;height:18px; cursor: pointer;" type="radio" name="estadoCivil" value="4"></div>
                                                    <div class="col-lg-4">'.$checkCasadoEclesiastico.'</div>
                                                    ';
                                            $nacionalidad = '<label for="nacionalidad">NACIONALIDAD</label>
                                                        <input maxlength="100" type="text" id="nacionalidad" name="nacionalidad" class="form-control"> 
                                                    ';
                                            $fechaFallecimiento = '<label for="fechaFallecimiento">FECHA DE FALLECIMIENTO</label>
                                                        <input type="date" name="fechaFallecimiento" id="fechaFallecimiento" class="form-control"> 
                                                    ';
                                            $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
                                            $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                                            foreach ($listaProvincias as $valueProvincias) {
                                                $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                                                $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                                            }
                                            
                                            $selectProvincias = '<h4 class="text-center">LUGAR DE FALLECIMIENTO</h4>
                                                <label for="selectProvincias">PROVINCIAS</label>
                                                <select class="form-control" onchange="filtrarCantonesPorProvincia()" id="selectProvincias" name="selectProvincias">
                                                    '.$optionSelectProvincias.'
                                                </select>
                                                <label for="selectCantones">CANTÓN</label>
                                                <select class="form-control" onchange="filtrarParroquiasPorProvinciaCanton()" id="selectCantones" name="selectCantones">
                                                    <option value="0">SELECCIONE UN CANTÓN</option>
                                                </select>
                                                <label for="selectParróquia">PARRÓQUIA</label>
                                                <select class="form-control" id="selectParroquias" name="selectParroquias">
                                                    <option value="0">SELECCIONE UNA PARRÓQUIA</option>
                                                </select>';

                                            $causaMuerte = '<label for="causaMuerte">CAUSA DE LA MUERTE</label>
                                                        <input maxlength="300" type="text" id="causaMuerte" name="causaMuerte" class="form-control"> ';
                                            
                                            
                                            $listaPadresBautismo = $objPadresBautismo->FiltrarPadreBautismoPorBautismo($idBautismo);
                                            $nombrePadre = '<label for="nombresPadre">NOMBRES DEL PADRE</label>
                                                        <input maxlength="300" type="text" id="nombresPadre" name="nombresPadre" class="form-control">';
                                            $nombreMadre = '<label for="nombresMadre">NOMBRES DE LA MADRE</label>
                                                        <input maxlength="300" type="text" id="nombresMadre" name="nombresMadre" class="form-control">';
                                            if(count($listaPadresBautismo) > 0){
                                                foreach ($listaPadresBautismo as $valuePadresBautismo) {
                                                    $nombres = $valuePadresBautismo['primerApellido'].' '.$valuePadresBautismo['segundoApellido'].' '.$valuePadresBautismo['primerNombre'].' '.$valuePadresBautismo['segundoNombre'];
                                                    if($valuePadresBautismo['identificadorTipoPadre'] == 1){
                                                        $nombrePadre = '<label for="nombresPadre">NOMBRES DEL PADRE</label><input  type="text" name="nombresPadre" id="nombresPadre" class="form-control" value="'.$nombres.'">';
                                                    }else{
                                                         $nombreMadre = '<label for="nombresMadre">NOMBRES DE LA MADRE</label><input  type="text" name="nombresMadre" id="nombresPadre" class="form-control" value="'.$nombres.'">';
                                                    }
                                                }
                                                
                                            }
                                            
                                            
                                            $inscripcionRegistroCivil = '<h4 class="text-center">REGISTRO CIVIL</h4>
                                            <label for="anoRegistroCivil">AÑO</label>
                                            <input onkeydown="validarNumeros(\'anoRegistroCivil\');" maxlength="10" autocomplete="off"  type="text" id="anoRegistroCivil" name="anoRegistroCivil" class="form-control">
                                            <label for="tomoRegistroCivil">TOMO</label>
                                            <input maxlength="10" autocomplete="off"  type="text" id="tomoRegistroCivil" name="tomoRegistroCivil" class="form-control">
                                            <label for="folioRegistroCivil">FOLIO</label>
                                            <input maxlength="10" autocomplete="off" type="text" id="folioRegistroCivil" name="folioRegistroCivil" class="form-control">
                                            <label for="actaRegistroCivil">ACTA</label>
                                            <input maxlength="10" autocomplete="off"  type="text" id="actaRegistroCivil" name="actaRegistroCivil" class="form-control">
                                            <label for="fechaInscripcionRegistroCivil">FECHA DE INSCRIPCIÓN</label>
                                            <input type="date" class="form-control" id="fechaInscripcionRegistroCivil" name="fechaInscripcionRegistroCivil">';
                                            
                                            $inscripcionEclesiastica = '<h4 class="text-center">REGISTRO ECLESIÁSTICO</h4>
                                            <label for="anoEclesiastico">AÑO</label>
                                            <input onkeydown="validarNumeros(\'anoEclesiastico\');" maxlength="10" autocomplete="off"  type="text" id="anoEclesiastico" name="anoEclesiastico" class="form-control">
                                            <label for="tomoEclesiastico">TOMO</label>
                                            <input maxlength="10" autocomplete="off"  type="text" id="tomoEclesiastico" name="tomoEclesiastico" class="form-control">
                                            <label for="folioEclesiastico">FOLIO</label>
                                            <input maxlength="10" autocomplete="off" type="text" id="folioEclesiastico" name="folioEclesiastico" class="form-control">
                                            <label for="actaEclesiastico">ACTA</label>
                                            <input maxlength="10" autocomplete="off"  type="text" id="actaEclesiastico" name="actaEclesiastico" class="form-control">
                                            <label for="fechaInscripcionEclesiastico">FECHA DE INSCRIPCIÓN</label>
                                            <input type="date" class="form-control" id="fechaInscripcionEclesiastico" name="fechaInscripcionEclesiastico">';
                                            
                                            
                                            
                                            
                                            $listaSacerdote = $objSacerdotes->ObtenerSacerdotesEstado(1); 
                                            $optionSelectSacerdote = '<option value="0">SELECCIONE UN SACERDOTE</option>';
                                            foreach ($listaSacerdote as $valueSacerdote) {
                                                $idSacerdoteEncriptado = $objMetodos->encriptar($valueSacerdote['idSacerdote']);
                                                $listaPersona = $objPersona->FiltrarPersona($valueSacerdote['idPersona']);
                                                $nombres = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'].' '.$listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];

                                                $optionSelectSacerdote =$optionSelectSacerdote.'<option value="'.$idSacerdoteEncriptado.'">'.$nombres.'</option>';
                                            }
                                            
                                            
                                            $selectSacerdote = '<label for="sacerdote">SACERDOTE</label>
                                            <select class="form-control" id="selectSacerdote" name="selectSacerdote">
                                                '.$optionSelectSacerdote.'
                                            </select>';    
                                            
                                            $listaLugares = $objLugaresMisa->ObtenerObtenerLugaresMisa();
                                            $optionLugares  = '<option value="0">SELECCIONE UNA IGLESIA</option>';
                                            foreach ( $listaLugares as $valuesLugares  ){
                                                $idLugarEncriptado = $objMetodos->encriptar($valuesLugares['idLugarMisa']);
                                                $optionLugares  = $optionLugares.'<option value="'.$idLugarEncriptado.'">'.$valuesLugares['nombreLugar'].'</option>';
                                            }

                                            $selectLugares = '<label for="lugarMisa">SELECCIONE LA IGLESIA</label><select class="form-control" id="lugarMisa" name="lugarMisa">'.$optionLugares.'</select>';
                                            
                                            
                                            $tabla = '<div class="form-group col-lg-6">
                                                        <input type="hidden" value="'.$idPersonaEncriptado.'" name="idPersonaEncriptado" id="idPersonaEncriptado">
                                                        <h4 class="text-center">DATOS DE LA FAMILIA</h4>'.
                                                    
                                                            $nombrePadre.
                                                            $nombreMadre.
                                                            $nombreConyugue
                                                    .'</div>
                                                    <div class="form-group col-lg-6">
                                                        '.$selectProvincias.'
                                                    </div>
                                                    <div class="form-group col-lg-12">
                                                        <h4 class="text-center col-lg-12">DATOS DEL FELLECIMIENTO</h4>
                                                        <div class="form-group col-lg-6">'.
                                                            $estadoCivil.
                                                            $nacionalidad.
                                                        '</div>
                                                        <div class="form-group col-lg-6">'.
                                                            $fechaFallecimiento.
                                                            $causaMuerte.
                                                        '</div>
                                                        <h4 class="text-center col-lg-12">DATOS DEL REGISTRO</h4>
                                                        <div class="form-group col-lg-6">'.
                                                            $inscripcionRegistroCivil.
                                                        '</div>
                                                        <div class="form-group col-lg-6">'.
                                                            $inscripcionEclesiastica.
                                                        '</div>
                                                        <h4 class="text-center col-lg-12">DATOS DE LA MISA</h4>
                                                        <div class="form-group col-lg-6">'.
                                                           $selectLugares.
                                                        '</div>
                                                        <div class="form-group col-lg-6">'.
                                                           $selectSacerdote.
                                                        '</div>
                                                        <div class="form-group col-lg-6">'.
                                                           $checkSacramentoDefuncion.
                                                        '</div>
                                                    </div>
                                                    
                                            <div class="form-group col-lg-12">
                                                '.$botonCancelar.' '.$botonGuardar.'
                                            </div>';       
                                            $mensaje = '';
                                            $validar = TRUE;
                                        }
                                    }else{
//                                        $objAdministrativo = new Administrativos($this->dbAdapter);
//                                        $listaAdministrativo = $objAdministrativo->FiltrarAdministrativosPorIdentificadorCargo(1);
//                                        if(count($listaAdministrativo) != 1){
//                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UN PÁRROCO QUE FIRME EL DOCUMENTO POR FAVOR DIRÍGETE AL MENÚ <b>TALENTO HUMANO->ADMINISTRATIVOS</b>Y AGREGA UN PÁRROCO</div>';
//                                        }else{
//                                            $idConfirmacion = $listaConfirmacion[0]['idConfirmacion'];
//                                            $nombres = $listaBautismo[0]['primerApellido'].' '.$listaBautismo[0]['segundoApellido'].' '.$listaBautismo[0]['primerNombre'].' '.$listaBautismo[0]['segundoNombre'];
//                                            $nombreIglesia = $sesionUsuario->offsetGet('nombreIglesia');
//                                            $listaLugar = $objLugaresMisa->FiltrarLugaresMisa($listaConfirmacion[0]['idLugarConfirmacion']);
//                                            $nombreIglesia2 = $listaLugar[0]['nombreLugar'];
//                                            $direccionIglesia = $sesionUsuario->offsetGet('direccionIgleisia');
//                                            $fechaConfirmacion = $objMetodos->obtenerFechaEnLetraSinHora($listaConfirmacion[0]['fechaConfirmacion']);
//                                            $listaSacerdote = $objSacerdotes->FiltrarSacerdote($listaConfirmacion[0]['idSacerdoteConfirmacion']);
//                                            $listaPersonaSacerdote = $objPersona->FiltrarPersona($listaSacerdote[0]['idPersona']);
//                                            $nombresSacerdote = $listaPersonaSacerdote[0]['primerApellido'].' '.$listaPersonaSacerdote[0]['segundoApellido'].' '.$listaPersonaSacerdote[0]['primerNombre'].' '.$listaPersonaSacerdote[0]['segundoNombre'];
//                                            $listaDireccion = $objConfigurarParroquiaCanton->FitrarDireccionesPorConfigurarParroquiaCanton($listaBautismo[0]['idConfigurarParroquiaCanton']);
//                                            $direccionNacimiento = $listaDireccion[0]['nombreParroquia'].' - '.$listaDireccion[0]['nombreCanton'].' - '.$listaDireccion[0]['nombreProvincia'];
//                                            $fechaNacimiento = $objMetodos->obtenerFechaEnLetraSinHora($fechaNacimiento);
//                                            $listaPadres = $objPadresBautismo->FiltrarPadreBautismoPorBautismo($listaBautismo[0]['idBautismo']);
//                                            $padre = '';
//                                            $madre = '';
//                                            foreach ($listaPadres as $valuePadres) {
//                                                if($valuePadres['identificadorTipoPadre'] == 1){
//                                                    $padre = $valuePadres['primerApellido'].' '.$valuePadres['segundoApellido'].' '.$valuePadres['primerNombre'].' '.$valuePadres['segundoNombre'];
//                                                }else{
//                                                    $madre = $valuePadres['primerApellido'].' '.$valuePadres['segundoApellido'].' '.$valuePadres['primerNombre'].' '.$valuePadres['segundoNombre'];
//                                                }
//                                            }
//
//                                            $listaPadrinos = $objPadrinosConfirmacion->FiltrarPadrinosConfirmacionPorConfirmacion($idConfirmacion);
//                                            $padrino1 = '';
//                                            $padrino2 = '';
//                                            $padrinos = '';
//                                            if(count($listaPadrinos) == 1){
//                                                $padrino1 = $listaPadrinos[0]['primerApellido'].' '.$listaPadrinos[0]['segundoApellido'].' '.$listaPadrinos[0]['primerNombre'].' '.$listaPadrinos[0]['segundoNombre'];
//                                                $padrinos = $padrino1;
//
//                                            }else if(count($listaPadrinos) == 2){
//                                                $padrino1 = $listaPadrinos[0]['primerApellido'].' '.$listaPadrinos[0]['segundoApellido'].' '.$listaPadrinos[0]['primerNombre'].' '.$listaPadrinos[0]['segundoNombre'];
//                                                $padrino2 = $listaPadrinos[1]['primerApellido'].' '.$listaPadrinos[1]['segundoApellido'].' '.$listaPadrinos[1]['primerNombre'].' '.$listaPadrinos[1]['segundoNombre'];
//                                                $padrinos = '<b>'.$padrino1.'</b> y <b>'.$padrino2.'</b>';
//
//                                            }
//                                                $tablaDerecha = '<p style="text-align: justify; line-height: 30px;font-size:15px">El día '.$fechaConfirmacion.' en la Iglesia Parroquial de <b>'.$nombreIglesia2.'</b> recibió el Sacramento de la Confirmación '
//                                                        . '<b>'.$nombres.'</b> nacido(a) en <b>'.$direccionNacimiento.'</b> el día <b>'.$fechaNacimiento.'</b>, son su Padres <b>'.$padre.'</b> y <b>'.$madre.'.</b>
//                                                    </p>
//                                                    <p style="text-align: justify; line-height: 30px;font-size:15px"> Fueron sus Padrinos: '.$padrinos.'
//                                                    </p> 
//                                                    <p style="text-align: justify; line-height: 30px;font-size:15px"> Confirmó: <b>'.$nombresSacerdote.'</b>
//                                                    </p>';
//                                            $tablaCaabecera = '<table class="table" style="width:100%">
//                                                        <thead>
//                                                            <tr>
//                                                                <th> 
//                                                                    <img style="width:10%" src="'.$this->getRequest()->getBaseUrl().'/public/librerias/images/pagina/logoiglesia.png" >
//                                                                    <br><label style="font-size:24px" class="box-title ">'.$nombreIglesia.'<br>'.$direccionIglesia.'</label>
//                                                                    <br> <label>Sistema Web de Gestión Parroquial</label>
//                                                                </th>
//                                                            </tr>
//                                                            <tr>
//                                                                <th> 
//                                                                    <h3>CERTIFICADO DE CONFIRMACIÓN</h3>
//                                                                </th>
//                                                            </tr>
//                                                        </thead>
//                                                    </table>';
//
//                                            $tablaIzquierda = '<table border="1" class="table text-center" style="width:100%" > 
//                                                        <thead>
//                                                            <tr> 
//                                                                <th colspan="2">N°</th>
//                                                                <th colspan="2"> '.$listaConfirmacion[0]['numeroConfirmacion'].'</th>
//                                                            </tr> 
//                                                            <tr> 
//                                                                <th colspan="4" >NOMBRE</th>
//                                                            </tr>
//                                                            <tr> 
//                                                                <th colspan="4" >'.$nombres.'</th>
//                                                            </tr>
//                                                            <tr> 
//                                                                <th colspan="4">REGISTRO ECLESIÁSTICO</th>
//                                                            </tr>
//                                                            <tr> 
//                                                                <th><b>AÑO</b> '.$listaConfirmacion[0]['anoConfirmacion'].'</th>
//                                                                <th><b>TOMO</b> '.$listaConfirmacion[0]['tomoConfirmacion'].'</th>
//                                                                <th><b>FOLIO</b> '.$listaConfirmacion[0]['folioConfirmacion'].'</th>
//                                                                <th><b>ACTA</b> '.$listaConfirmacion[0]['actaConfirmacion'].'</th>
//
//                                                            </tr>
//                                                            <tr> 
//                                                                <th colspan="2">FECHA INSCRIPCIÓN</th>
//                                                                <th colspan="2"> '.$listaConfirmacion[0]['fechaInscipcionConfirmacion'].'</th>
//                                                            </tr> 
//
//                                                        </thead>
//                                                    </table>';
//                                                    $listaPersonaFirma = $objPersona->FiltrarPersona($listaAdministrativo[0]['idPersona']);
//                                                    $tablaFirma = '<table class="table text-center" style="width:100%" > 
//                                                        <thead>
//                                                            <tr> 
//                                                                <th>_________________________________________<br>
//                                                                '.$listaPersonaFirma[0]['primerNombre'].' '.$listaPersonaFirma[0]['segundoNombre'].' '.$listaPersonaFirma[0]['primerApellido'].' '.$listaPersonaFirma[0]['segundoApellido'].'
//                                                                <br>'.$listaAdministrativo[0]['descripcion'].'</th>
//                                                            </tr> 
//                                                        </thead>
//                                                    </table>';
//                                            $tabla = '<div class="col-lg-3"></div><div class="col-lg-6"><div id="contenedorImprimirReporte">'.$tablaCaabecera.'<br><br><br>'.$tablaDerecha.'<br><br>'.$tablaIzquierda.'<br><br><br><br>'.$tablaFirma.'</div></div><div class="col-lg-3"></div><button type="button" onclick="imprimir(\'contenedorImprimirReporte\')" class="btn btn-warning btn-flat pull-right"><i class="fa fa-print"></i>Imprimir</button>';
                                            $mensaje = '';
                                            $validar = TRUE;
//                                        }
                                    }
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    public function obtenerdefuncionAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 21);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objDefuncion = new Defuncion($this->dbAdapter);
                    ini_set('date.timezone','America/Bogota'); 
                    
                    $listaDefuncion = $objDefuncion->ObtenerDefuncion();
                    $tabla = $this->CargarTablaDefuncion($idUsuario, $this->dbAdapter, $listaDefuncion, 0, count($listaDefuncion));
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }
            
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
//   
     function CargarTablaDefuncion($idUsuario,$adaptador,$listaDefuncion, $i, $j)
    {
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $objMetodosC = new MetodosControladores();
        $array1 = array();
        foreach ($listaDefuncion as $value) {
            $identificacion = '';
            if($value['identificacion'] != NULL){
                $identificacion = $value['identificacion'];
            }
            $idDefuncionEncriptado = $objMetodos->encriptar($value['idDefuncion']);
            $nombres = $value['primerApellido'].' '.$value['segundoApellido'].' '.$value['primerNombre'].' '.$value['segundoNombre'];
            $nombresPersona = $nombres;
            $fechaNacimiento = $value['fechaNacimiento'];
            $fechaFallecimiento = $value['fechaFallecimiento'];
            $botonEliminarDefuncion = '';
            $botonDeshabilitarDefuncion = '';

            $botones =  $botonEliminarDefuncion.' '.$botonDeshabilitarDefuncion;     
            $array1[$i] = array(
                '_j'=>$j,
                'identificacion'=>$identificacion,
                'nombresPersona'=>$nombresPersona,
                'fechaNacimiento'=>$fechaNacimiento,
                'fechaFallecimiento'=>$fechaFallecimiento,
                'opciones'=>$botones,
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }   
//    public function ingresarconfirmacionAction()
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
//            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 15);
//            if (count($AsignarModulo)==0)
//                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
//            else {
//                $objMetodosC = new MetodosControladores();
//                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 15, 3);
//                if ($validarprivilegio==false)
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
//                else{
//                    $request=$this->getRequest();
//                    if(!$request->isPost()){
//                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
//                    }else{
//                        $objMetodos = new Metodos();
//                        $objBautismo = new Bautismo($this->dbAdapter);
//                        $objPadresBautismo = new PadresBautismo($this->dbAdapter);
//                        $objPadrinosBautismo = new PadrinosBautismo($this->dbAdapter);
//                        $objConfirmacion = new Confirmacion($this->dbAdapter);
//                        $objPersona = new Persona($this->dbAdapter);
//                        $objSacerdote = new Sacerdotes($this->dbAdapter);
//                        $objTipoPadre = new TipoPadre($this->dbAdapter);
//                        $objPadrinosConfirmacion = new PadrinosConfirmacion($this->dbAdapter);
//                        $objLugar = new LugaresMisa($this->dbAdapter);
//                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
//                        $post = array_merge_recursive(
//                            $request->getPost()->toArray(),
//                            $request->getFiles()->toArray()
//                        );
//                        $idPersonaEncriptado = $post['idPersonaEncriptado'];
//                        $numeroConfirmacion = $post['numeroConfirmacion'];
//                        $idSacerdoteEncriptado = $post['selectSacerdote'];
//                        $fechaConfirmacion = $post['fechaConfirmacion'];
//                        $idLugarConfirmacionEncriptado = $post['lugarConfirmacion'];
//                        $anoConfirmacion = trim($post['anoConfirmacion']);
//                        $tomoConfirmacion = strtoupper(trim($post['tomoConfirmacion']));
//                        $folioConfirmacion = strtoupper(trim($post['folioConfirmacion']));
//                        $actaConfirmacion = strtoupper(trim($post['actaConfirmacion']));
//                        $fechaInscipcionConfirmacion = $post['fechaInscipcionConfirmacion'];
//                        $identificacionPadrino1 = trim($post['identificacionPadrino1']);
//                        $identificacionPadrino2 = trim($post['identificacionPadrino2']);
//
//                        if(empty ($identificacionPadrino1)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACIÓN DEL PADRINO 1</div>';
//                        }else if(empty ($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
//                        }else if(!is_numeric($numeroConfirmacion)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL  NÚMERO</div>';
//                        }else if(empty ($idSacerdoteEncriptado) || $idSacerdoteEncriptado == NULL){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL SACERDOTE</div>';
//                        }else if(empty ($fechaConfirmacion)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE LA CONFIRMACIÓN</div>';
//                        }else if(empty ($idLugarConfirmacionEncriptado) || $idLugarConfirmacionEncriptado == "0"){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE LA IGLESIA</div>';
//                        }else if(!is_numeric ($anoConfirmacion) || strlen($anoConfirmacion) > 4){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL AÑO DEL REGISTRO 4 DÍGITOS</div>';
//                        }else if(empty ($tomoConfirmacion)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL TOMO</div>';
//                        }else if(empty ($folioConfirmacion)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL FOLIO</div>';
//                        }else if(empty ($actaConfirmacion)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL ACTA</div>';
//                        }else if(empty ($fechaInscipcionConfirmacion)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE INSCRIPCIÓN</div>';
//                        }else  if(count ($objConfirmacion->FiltrarConfirmacionPorNumero($numeroConfirmacion)) > 0 ){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA CONFIRMACIÓN CON EL NÚMERO '.$numeroConfirmacion.'</div>';
//                        }else {
//                            $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
//                            $listaPersona = $objPersona->FiltrarPersona($idPersona);
//                            if(count($listaPersona) == 0){
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA QUE DESEA CONFIRMAR NO EXISTE EN LA BASE DE DATOS</div>';
//                            }else{
//                                $listaBautismo = $objBautismo->FiltrarBautismoPorPersona($idPersona);
//                                if(count($listaBautismo) == 0){
//                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA QUE DESEA CONFIRMAR NO HA SIDO BAUTIZADA</div>';
//                                }else{
//                                    $idBautismo = $listaBautismo[0]['idBautismo'];
//                                    $listaConfirmacion = $objConfirmacion->FiltrarConfirmacionPorBautismo($idBautismo);
//                                    if(count($listaConfirmacion)>0){
//                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTAN PERSONA YA CUENTA CON LA CONFIRMACIÓN</div>';
//                                    }else{
//
//
//                                        $idLugarConfirmacion = $objMetodos->desencriptar($idLugarConfirmacionEncriptado);
//                                        $listaLugar = $objLugar->FiltrarLugaresMisa($idLugarConfirmacion);
//                                        if(count($listaLugar) == 0){
//                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IGLESIA SELECCIONADA NO EXISTE EN LA BASE DE DATOS</div>';
//                                        }else{
//                                            $validarPadrino2 = TRUE;
//                                            if(empty($identificacionPadrino2)){
//                                                $validarPadrino2 = FALSE;
//                                            }
//                                            $listaPadrino1 = $objPersona->FiltrarPersonaPorIdentificacion($identificacionPadrino1);
//                                            if(count($listaPadrino1) == 0){
//                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENETIFICACIÓN  '.$identificacionPadrino1.'  NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
//                                            }else if($validarPadrino2 == TRUE && count($objPersona->FiltrarPersonaPorIdentificacion($identificacionPadrino2)) == 0 ){
//                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENETIFICACIÓN  '.$identificacionPadrino2.'  NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
//                                            }else{
//                                                ini_set('date.timezone','America/Bogota');    
//                                                $fechaNacimientoPadrino1 = new \DateTime($listaPadrino1[0]['fechaNacimiento']);
//                                                $fechaActual = new \DateTime(date("d-m-Y"));
//                                                $diffPadrino1 = $fechaActual->diff($fechaNacimientoPadrino1);
//                                                $diffPadrino2 = 0;
//                                                $idPadrino2 = 0;
//                                                if($validarPadrino2 == TRUE){
//                                                    $listaPadrino2 = $objPersona->FiltrarPersonaPorIdentificacion($identificacionPadrino2);
//                                                    $fechaNacimientoPadrino2 = new \DateTime($listaPadrino2[0]['fechaNacimiento']);
//                                                    $diffPadrino2 = $fechaActual->diff($fechaNacimientoPadrino2);
//                                                    $idPadrino2 = $listaPadrino2[0]['idPersona'];
//                                                }
//
//                                                if($diffPadrino1->y < 18){
//                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENTIFICACIÓN '.$identificacionPadrino1.' AÚN ES MENOR DE EDAD</div>';
//                                                }else if($validarPadrino2 == true && $diffPadrino2->y < 18){
//                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENTIFICACIÓN '.$identificacionPadrino2.' AÚN ES MENOR DE EDAD</div>';
//                                                }else{
//                                                    $fechaActualCom =  strtotime(date("d-m-Y"));
//                                                    $fechaConfirmacionCom = strtotime($fechaConfirmacion);
//                                                    $fechaInscripcionConfirmacionCom = strtotime($fechaInscipcionConfirmacion);
//                                                    if($fechaConfirmacionCom > $fechaActualCom){
//                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE LA CONFIRMACIÓN NO DEBE SER MAYOR A LA FECHA ACTUAL</div>';                        
//                                                    }else if($fechaInscripcionConfirmacionCom > $fechaActualCom){
//                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCIÓN NO DEBE SER MAYOR A LA FECHA ACTUAL</div>';                        
//                                                    }else{
//                                                        $idPadrino1 = $listaPadrino1[0]['idPersona'];
//                                                        if($idPersona == $idPadrino1){
//                                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CONFIRMADO NO DEBE SER LA MISMA PERSONA QUE EL PADRINO 1</div>';                        
//                                                        }else if($validarPadrino2 == TRUE && $idPersona == $idPadrino2){
//                                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL CONFIRMADO NO DEBE SER LA MISMA PERSONA QUE EL PADRINO 2</div>';                        
//                                                        }else if($validarPadrino2 == TRUE && $idPadrino1 == $idPadrino2){
//                                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LOS PADRINOS NO DEBEN SER LA MISMA PERSONA</div>';                                              
//                                                        }else{
//                                                            $fechaNacimientoCom = strtotime($listaPersona[0]['fechaNacimiento']);
//                                                            if($fechaNacimientoCom > $fechaInscripcionConfirmacionCom){
//                                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCION DEL REGISTRO NO DEBE SER MENOR A LA DE NACIMIENTO</div>';
//                                                            }else if($fechaNacimientoCom > $fechaConfirmacionCom){
//                                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE LA CONFIRMACIÓN NO DEBE SER MENOR A LA DE NACIMIENTO</div>';
//                                                            }else{
//                                                                $idSacerdote = $objMetodos->desencriptar($idSacerdoteEncriptado);
//                                                                $listaSacerdote = $objSacerdote->FiltrarSacerdote($idSacerdote);
//                                                                if(count($listaSacerdote) == 0){
//                                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SACERDOTE SELECCIONADO NO EXISTE EN LA BASE DE DATOS</div>';
//                                                                }else{
//                                                                    $hoy = getdate();
//                                                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
//                                                                    $resultado = $objConfirmacion->IngresarConfirmacion($idBautismo, $idSacerdote, $fechaConfirmacion, $idLugarConfirmacion, $numeroConfirmacion, $anoConfirmacion, $tomoConfirmacion, $folioConfirmacion, $actaConfirmacion, $fechaInscipcionConfirmacion, $fechaSubida, 1);
//                                                                    if(count($resultado) == 0){
//                                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA CONFIRMACIÓN POR FAVOR INTENTE MÁS TARDE</div>';
//                                                                    }else{ 
//                                                                        $idConfirmacion = $resultado[0]['idConfirmacion'];
//                                                                        $resultPadrino1 = $objPadrinosConfirmacion->IngresarPadrinosConfirmacion($idConfirmacion, $idPadrino1, 1);
//                                                                        if($validarPadrino2 == TRUE){
//                                                                            $resultPadrino1 = $objPadrinosConfirmacion->IngresarPadrinosConfirmacion($idConfirmacion, $idPadrino2, 1);
//                                                                        }
//                                                                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
//                                                                        $validar = TRUE;
//                                                                    }
//                                                                }
//                                                            }
//                                                        }
//    //                                                
//                                                    }
//                                                }
//                                            }
////
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//                
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }
//    
// 
// 
//    
    
    
    
    
    
    
    
    
    
//    
//    
    public function filtrarparroquiasporprovinciacantonAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 21);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
            $request=$this->getRequest();
            if(!$request->isPost()){
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
            }else{
                $objConfigurarCantonProvincia = new ConfigurarCantonProvincia($this->dbAdapter);
                $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                $objParroquias = new Parroquias($this->dbAdapter);
               
                $objMetodos = new Metodos();
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $idProvinciaEncriptado = $post['idProvincia'];
                $idCantonEncriptado = $post['idCanton'];
                if($idProvinciaEncriptado == NULL || $idProvinciaEncriptado == "" || $idProvinciaEncriptado == "0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVINCIA</div>';
                }else if($idCantonEncriptado == NULL || $idCantonEncriptado == "" || $idCantonEncriptado == "0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVINCIA</div>';
                }else{
                    
                    $idProvincia = $objMetodos->desencriptar($idProvinciaEncriptado);
                    $idCanton = $objMetodos->desencriptar($idCantonEncriptado);
                    $listaConfigurarCantonProvincia = $objConfigurarCantonProvincia->FiltrarConfigurarCantonProvinciaPorProvinciaCanton($idProvincia,$idCanton, true);
                    $optionParroquias = '<option value="0">SELECCIONE UNA PARRÓQUIA</option>';
                    foreach ($listaConfigurarCantonProvincia as $valueConfigurarCantonProvincia) {
                        $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvincia($valueConfigurarCantonProvincia['idConfigurarCantonProvincia']);
                        foreach ($listaConfigurarParroquiaCanton as $valueConfigurarParroquiaCanton) {
                            $listaParroquia = $objParroquias->FiltrarParroquia($valueConfigurarParroquiaCanton['idParroquia']);
                            $idConfigurarParroquiaCantonEncriptado = $objMetodos->encriptar($valueConfigurarParroquiaCanton['idConfigurarParroquiaCanton']);
                            $optionParroquias = $optionParroquias.'<option value="'.$idConfigurarParroquiaCantonEncriptado.'">'.$listaParroquia[0]['nombreParroquia'].'</option>';
                        }
                    }                    
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'optionParroquias'=>$optionParroquias));
                }
            }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    public function filtrarcantonesporprovinciaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 21);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objConfigurarCantonProvincia = new ConfigurarCantonProvincia($this->dbAdapter);
                    $objCantones = new Cantones($this->dbAdapter);
                    $objMetodos = new Metodos();
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );

                    $idProvinciaEncriptado = $post['idProvincia'];
                    if($idProvinciaEncriptado == NULL || $idProvinciaEncriptado == "" || $idProvinciaEncriptado == "0"){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVINCIA</div>';
                    }else{
                        $idProvincia = $objMetodos->desencriptar($idProvinciaEncriptado);
                        $listaConfigurarCantonProvincia = $objConfigurarCantonProvincia->FiltrarConfigurarCantonProvinciaPorProvincia($idProvincia, true);
                        $optionCantones = '<option value="0">SELECCIONE UN CANTÓN</option>';
                        foreach ($listaConfigurarCantonProvincia as $valueConfigurarCantonProvincia) {
                            $listaCantones = $objCantones->FiltrarCanton($valueConfigurarCantonProvincia['idCanton']);
                            $idCantonEncriptado = $objMetodos->encriptar($listaCantones[0]['idCanton']);
                            $optionCantones = $optionCantones.'<option value="'.$idCantonEncriptado.'">'.$listaCantones[0]['nombreCanton'].'</option>';
                        }                    
                        $mensaje = '';
                        $validar = TRUE;
                        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'optionCantones'=>$optionCantones));
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
//    public function filtrarpersonaporidentificacionAction()
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
//            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 20);
//            if (count($AsignarModulo)==0)
//                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
//            else {
//                $objMetodosC = new MetodosControladores();
//                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 20, 3);
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
//                                ini_set('date.timezone','America/Bogota'); 
//                                $fechaNacimiento = new \DateTime($listaPersona[0]['fechaNacimiento']);
//                                $fechaActual = new \DateTime(date("d-m-Y"));
//                                $diff = $fechaActual->diff($fechaNacimiento);
//                               
//                                $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
//                                $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
//                                $tabla = '<div class="table-responsive"><table class="table">
//                                    <thead> 
//                                        <tr>
//                                            <th>NOMBRES</th>
//                                            <td>'.$nombres.'</td>
//                                        </tr>
//                                        <tr>
//                                            <th>APELLIDOS</th>
//                                            <td>'.$apellidos.'</td>
//                                        </tr>
//                                        <tr>
//                                            <th>EDAD</th>
//                                            <td>'.$diff->y.'</td>
//                                        </tr>
//                                    </thead>
//                                </table></div>';
//                                $mensaje = '';
//                                $validar = TRUE;
//                                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
//                                
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }
    

}