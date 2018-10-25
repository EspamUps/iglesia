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
use Nel\Modelo\Entity\Sacerdotes;
use Nel\Modelo\Entity\Cantones;
use Nel\Modelo\Entity\Parroquias;
use Nel\Modelo\Entity\Provincias;
use Nel\Modelo\Entity\ConfigurarCantonProvincia;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Bautismo;
use Nel\Modelo\Entity\Matrimonio;
use Nel\Modelo\Entity\Sexo;
use Nel\Modelo\Entity\PadresBautismo;
use Nel\Modelo\Entity\PadrinosBautismo;
use Nel\Modelo\Entity\TestigosMatrimonio;
use Nel\Modelo\Entity\LugaresMisa;
use Nel\Modelo\Entity\TipoPadre;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class MatrimonioController extends AbstractActionController
{

    public $dbAdapter;
    
    public function ingresarmatrimonioAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 18);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 18, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objBautismo = new Bautismo($this->dbAdapter);
                        $objPadresBautismo = new PadresBautismo($this->dbAdapter);
                        $objPadrinosBautismo = new PadrinosBautismo($this->dbAdapter);
                        $objPersona = new Persona($this->dbAdapter);
                        $objSacerdote = new Sacerdotes($this->dbAdapter);
                        $objTipoPadre = new TipoPadre($this->dbAdapter);
                        $objMatrimonio = new Matrimonio($this->dbAdapter);
                        $objLugar = new LugaresMisa($this->dbAdapter);
                        $objTestigosMatrimonio = new TestigosMatrimonio($this->dbAdapter);
                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idEsposoEncriptado = $post['idEsposoEncriptado'];
                        $idEsposaEncriptado = $post['idEsposaEncriptado'];
                        $numeroPagina = $post['numeroPagina'];
                        $numeroActaMatrimonial = $post['numeroActaMatrimonial'];
                        $idSacerdoteEncriptado = $post['selectSacerdote'];
                        $fechaMatrimonio = $post['fechaMatrimonio'];
                        
                        
                        $idConfigurarParroquiaCantonEncriptado = $post['selectParroquias'];
                        $idLugarEncriptado  = $post['lugarMatrimonio'];
                        
                        
                        $anoRegistroCivil = trim($post['anoRegistroCivil']);
                        $tomoRegistroCivil = strtoupper(trim($post['tomoRegistroCivil']));
                        $folioRegistroCivil = strtoupper(trim($post['folioRegistroCivil']));
                        $actaRegistroCivil = strtoupper(trim($post['actaRegistroCivil']));
                        

                        $nombresTestigo1 = $post['nombresTestigo1'];
                        $nombresTestigo2 = $post['nombresTestigo2'];
                        
                        

                        $fechaNacimientoTestigo1 = $post['fechaNacimientoTestigo1'];
                        $fechaNacimientoTestigo2 = $post['fechaNacimientoTestigo2'];

                        if(empty ($nombresTestigo1)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LOS DATOS DEL TESTIGO 1</div>';
                        }else if(empty ($fechaNacimientoTestigo1)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO DEL TESTIGO 1</div>';
                        }else if(empty ($nombresTestigo2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LOS DATOS DEL TESTIGO 2</div>';
                        }else if(empty ($fechaNacimientoTestigo2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO DEL TESTIGO 2</div>';
                        }else if(empty ($idEsposoEncriptado) || $idEsposoEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL ESPOSO</div>';
                        }else if(empty ($idEsposaEncriptado) || $idEsposaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA ESPOSA</div>';
                        }else if(!is_numeric($numeroPagina)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NÚMERO DE PÁGINA DATOS DEL MATRIMONIO</div>';
                        }else if(empty ($numeroActaMatrimonial)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NÚMERO DE ACTA MATRIMONIAL</div>';
                        }else if(count($objMatrimonio->FiltrarMatrimonioPorNumeroActaMatrimonial($numeroActaMatrimonial)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN NÚMERO DE ACTA MATRIMONIAL '.$numeroActaMatrimonial.'</div>';
                        }else if(empty ($idSacerdoteEncriptado) || $idSacerdoteEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL SACERDOTE</div>';
                        }else  if(empty ($fechaMatrimonio)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DEL MATRIMONIO</div>';
                        }else  if(empty ($idConfigurarParroquiaCantonEncriptado) || $idConfigurarParroquiaCantonEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA DIRECCIÓN</div>';
                        }else  if(empty ($idLugarEncriptado) || $idLugarEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE LA IGLESIA</div>';
                        }else if(!is_numeric ($anoRegistroCivil) || strlen($anoRegistroCivil) > 4){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL AÑO DEL REGISTRO CIVIL 4 DÍGITOS</div>';
                        }else if(empty ($tomoRegistroCivil)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL TOMO</div>';
                        }else if(empty ($folioRegistroCivil)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL FOLIO</div>';
                        }else if(empty ($actaRegistroCivil)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL ACTA</div>';
                        }else{
                            
                            $listaTestigo1 = $objPersona->FiltrarPersonaPorNombres($nombresTestigo1, $fechaNacimientoTestigo1);
                            $listaTestigo2 = $objPersona->FiltrarPersonaPorNombres($nombresTestigo2, $fechaNacimientoTestigo2);
                            
                            if(count($listaTestigo1) != 1){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL TESTIGO 1 CON NOMBRES '.$nombresTestigo1.' NACIDO(A) EN LA FECHA '.$fechaNacimientoTestigo1.' NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if(count($listaTestigo2) != 1){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA TESTIGO 2 CON NOMBRES '.$nombresTestigo2.' NACIDO(A) EN LA FECHA '.$fechaNacimientoTestigo2.' NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else{
                                
                                $idTestigo1 = $listaTestigo1[0]['idPersona'];
                                $idTestigo2 = $listaTestigo2[0]['idPersona'];
                            
                            
                                ini_set('date.timezone','America/Bogota'); 
                                $fechaActualCom =  strtotime(date("d-m-Y"));
                                $fechaMatrimonioCom = strtotime($fechaMatrimonio);
                                if($fechaMatrimonioCom > $fechaActualCom){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE MATRIMONIO NO DEBE SER MAYOR A LA FECHA ACTUAL</div>';                        
                                }else{
                                    $idEsposo = $objMetodos->desencriptar($idEsposoEncriptado);
                                    $idEsposa = $objMetodos->desencriptar($idEsposaEncriptado);
                                    $listaEsposo = $objPersona->FiltrarPersona($idEsposo);
                                    $listaEsposa = $objPersona->FiltrarPersona($idEsposa);
                                    if(count($listaEsposo) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA ESPOSO SELECCIONADO NO EXISTE EN LA BASE DE DATOS</div>';
                                    }else if(count($listaEsposa) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA ESPOSA SELECCIONADA NO EXISTE EN LA BASE DE DATOS</div>';
                                    }else{
                                        
                                        if(count ($objMatrimonio->FiltrarMatrimonioPorEsposoEsposa($idEsposo, $idEsposa)) > 0 ){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LAS PERSONAS SELECCIONADAS YA ESTÁN CASADAS POR FAVOR RECARGUE LA PÁGINA</div>';
                                        }else if(count($objMatrimonio->FiltrarMatrimonioPorEsposo($idEsposo)) > 0){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SEÑOR '.$listaEsposo[0]['primerApellido'].' '.$listaEsposo[0]['primerNombre'].'  YA ESTÁ CASADO CON OTRA PERSONA</div>';
                                        }else if(count($objMatrimonio->FiltrarMatrimonioPorEsposa($idEsposa)) > 0){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA SEÑORA '.$listaEsposa[0]['primerApellido'].' '.$listaEsposa[0]['primerNombre'].'  YA ESTÁ CASADO CON OTRA PERSONA</div>';
                                        }else{
                                            
                                            $idSacerdote = $objMetodos->desencriptar($idSacerdoteEncriptado);
                                            $listaSacerdote = $objSacerdote->FiltrarSacerdote($idSacerdote);
                                            if(count($listaSacerdote) == 0){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SACERDOTE SELECCIONADO NO EXISTE EN LA BASE DE DATOS</div>';
                                            }else{

                                                $idConfigurarParroquiaCanton = $objMetodos->desencriptar($idConfigurarParroquiaCantonEncriptado);
                                                $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCanton($idConfigurarParroquiaCanton);
                                                if(count($listaConfigurarParroquiaCanton) == 0){
                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA DIRECCIÓN DEL LUGAR DE NACIMIENTO SELECCIONADO NO EXISTE EN LA BASE DE DATOS</div>';
                                                }else{
                                                    
                                                    $idLugar = $objMetodos->desencriptar($idLugarEncriptado);
                                                    $listaLugar = $objLugar->FiltrarLugaresMisa($idLugar);
                                                    if(count($listaLugar) == 0){
                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IGLESIA SELECCIONADA NO EXISTE</div>';
                                                    }else{
                                                        $idIglesia = $sesionUsuario->offsetGet('idIglesia');
                                                        $hoy = getdate();
                                                        $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];



                                                        $resultado = $objMatrimonio->IngresarMatrimonio($idEsposo, $idEsposa, $idLugar, $idConfigurarParroquiaCanton, $anoRegistroCivil, $tomoRegistroCivil, $folioRegistroCivil, $actaRegistroCivil, $numeroPagina, $numeroActaMatrimonial, $fechaMatrimonio, $fechaSubida, 1);
                                                        if(count($resultado) == 0){
                                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL CURSO POR FAVOR INTENTE MÁS TARDE</div>';
                                                        }else{ 
                                                            $idMatrimonio = $resultado[0]['idMatrimonio'];
                                                            $resultadoTestigo = $objTestigosMatrimonio->IngresarTestigosMatrimonio($idMatrimonio, $idTestigo1, 1);
                                                            $resultadoTestigo2 = $objTestigosMatrimonio->IngresarTestigosMatrimonio($idMatrimonio, $idTestigo2, 1);
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
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function filtrarmatrimonioporesposoyesposaAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 18);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 18, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objSacerdotes = new Sacerdotes($this->dbAdapter);
                        $objProvincias = new Provincias($this->dbAdapter);
                        $objBautismo = new Bautismo($this->dbAdapter);
                        $objSexo = new Sexo($this->dbAdapter);
                        $objMatrimonio = new Matrimonio($this->dbAdapter);
                        $objLugaresMisa = new LugaresMisa($this->dbAdapter);
                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                        $objPadresBautismo = new PadresBautismo($this->dbAdapter);
                        $objPadrinosBautismo = new PadrinosBautismo($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                         $nombresEsposo = strtoupper(trim($post['nombresEsposo']));
                         $fechaNacimientoEsposo = $post['fechaNacimientoEsposo'];
                         $nombresEsposa = strtoupper(trim($post['nombresEsposa']));
                         $fechaNacimientoEsposa = $post['fechaNacimientoEsposa'];
                        if(empty ($nombresEsposo)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LOS NOMBRES DEL ESPOSO</div>';
                        }else if(empty ($fechaNacimientoEsposo)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO DEL ESPOSO</div>';
                        }else if(empty ($nombresEsposa)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LOS NOMBRES DE LA ESPOSA</div>';
                        }else if(empty ($fechaNacimientoEsposa)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO DE LA ESPOSA</div>';
                        }else{
                          
                            
                            $listaPersonaEsposo = $objPersona->FiltrarPersonaPorNombres($nombresEsposo,$fechaNacimientoEsposo);
                            $listaPersonaEsposa = $objPersona->FiltrarPersonaPorNombres($nombresEsposa,$fechaNacimientoEsposa);
                            if(count($listaPersonaEsposo) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA '.$nombresEsposo.' NACIDO(A) EN LA FECHA '.$fechaNacimientoEsposo.' POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if(count($listaPersonaEsposo) != 1){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">POR FAVOR INGRESE LOS DATOS DEL ESPOSO CORRECTAMENTE O EXISTE MÁS DE UNA PERSONA CON LOS MISMOS NOMBRES APELLIDOS Y FECHA DE NACIMIENTO</div>';
                            }else if($listaPersonaEsposo[0]['estadoPersona'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSO HA SIDO DESHABILITADO POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                            } else if(count($listaPersonaEsposa) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA '.$nombresEsposa.' NACIDO(A) EN LA FECHA '.$fechaNacimientoEsposa.' POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if(count($listaPersonaEsposa) != 1){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">POR FAVOR INGRESE LOS DATOS DE LA ESPOSA CORRECTAMENTE O EXISTE MÁS DE UNA PERSONA CON LOS MISMOS NOMBRES APELLIDOS Y FECHA DE NACIMIENTO</div>';
                            }else if($listaPersonaEsposa[0]['estadoPersona'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                            } else{
                                $idEsposo = $listaPersonaEsposo[0]['idPersona'];
                                $idEsposa = $listaPersonaEsposa[0]['idPersona'];
                                $listaBautismoEsposo  = $objBautismo->FiltrarBautismoPorPersona($idEsposo);
                                $listaBautismoEsposa = $objBautismo->FiltrarBautismoPorPersona($idEsposa);
                                if(count($listaBautismoEsposo) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SEÑOR '.$nombresEsposo.' NO CUENTA COMO BAUTIZADO EN EL SISTEMA</div>';
                                }else if(count($listaBautismoEsposa) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA SEÑORITA '.$nombresEsposa.' NO CUENTA COMO BAUTIZADA EN EL SISTEMA</div>';
                                }else{
                                
                                
                                    $listaMatrimonioEsposoEsposa = $objMatrimonio->FiltrarMatrimonioPorEsposoEsposa($idEsposo, $idEsposa);
                                    $tabla = '';
                                    if(count($listaMatrimonioEsposoEsposa) > 0){

                                        $tabla = 'HOLA';
                                        $mensaje = '';
                                        $validar = TRUE;
                                    }else{

                                        $listaMatrimonioEsposo = $objMatrimonio->FiltrarMatrimonioPorEsposo($idEsposo);
                                        $listaMatrimonioEsposa = $objMatrimonio->FiltrarMatrimonioPorEsposa($idEsposa);
                                        if(count($listaMatrimonioEsposo) > 0){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SEÑOR '.$nombresEsposo.' YA ESTÁ CASADO</div>';
                                        }else if(count($listaMatrimonioEsposa) > 0){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SEÑORITA '.$nombresEsposa.' YA ESTÁ CASADA</div>';
                                        }else{
                                            $idEsposoEncriptado = $objMetodos->encriptar($idEsposo);
                                            $idEsposaEncriptado = $objMetodos->encriptar($idEsposa);
                                            $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarMatrimonio" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
                                            $botonCancelar = '<button id="btnCancelar" onclick="limpiarFormularioMatrimonio();" type="button" class="btn btn-danger pull-right"><i class="fa fa-times"></i>CANCELAR</button>';

                                            $listaPersonasPM = $objPersona->ObtenerPersonas();
     
                                            $option= '';
                                            foreach ($listaPersonasPM as $valuePM){
                                                    $option = $option.'<option value="'.$valuePM['primerApellido'].' '.$valuePM['segundoApellido'].' '.$valuePM['primerNombre'].' '.$valuePM['segundoNombre'].'"></option>';
                                            }
                                            $listaSacerdote = $objSacerdotes->ObtenerSacerdotesEstado(1); 
                                            $optionSelectSacerdote = '<option value="0">SELECCIONE UN SACERDOTE</option>';
                                            foreach ($listaSacerdote as $valueSacerdote) {
                                                $idSacerdoteEncriptado = $objMetodos->encriptar($valueSacerdote['idSacerdote']);
                                                $listaPersona = $objPersona->FiltrarPersona($valueSacerdote['idPersona']);
                                                $nombres = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'].' '.$listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];

                                                $optionSelectSacerdote =$optionSelectSacerdote.'<option value="'.$idSacerdoteEncriptado.'">'.$nombres.'</option>';
                                            }
                                            $listaLugares = $objLugaresMisa->ObtenerObtenerLugaresMisa();
                                            $optionLugares  = '<option value="0">SELECCIONE UNA IGLESIA</option>';
                                            foreach ( $listaLugares as $valuesLugares  ){
                                                $idLugarEncriptado = $objMetodos->encriptar($valuesLugares['idLugarMisa']);
                                                $optionLugares  = $optionLugares.'<option value="'.$idLugarEncriptado.'">'.$valuesLugares['nombreLugar'].'</option>';
                                            }

                                            $selectLugares = '<label for="lugarMatrimonio">SELECCIONE LA IGLESIA</label><select class="form-control" id="lugarMatrimonio" name="lugarMatrimonio">'.$optionLugares.'</select>';
                                    
                                            
                                            $selectTestigo1 = '<div class="form-group col-lg-6">
                                                <label for="nombresTestigo1">APELLIDOS Y NOMBRES DEL TESTIGO 1</label>
                                                <input list="buscadoTestigo1" id="nombresTestigo1" autocomplete="off" name="nombresTestigo1" type="text" class="form-control" placeholder="Buscar">
                                                <datalist id="buscadoTestigo1">
                                                    '.$option.'
                                                </datalist>
                                            </div> 
                                            <div class="form-group col-lg-6">
                                                <label for="fechaNacimientoTestigo1">FECHA DE NACIMIENTO DEL TESTIGO 1</label>
                                                <input type="date" id="fechaNacimientoTestigo1" name="fechaNacimientoTestigo1" class="form-control" >
                                            </div>';
                                            $selectTestigo2 = '<div class="form-group col-lg-6">
                                                    <label for="nombresTestigo2">APELLIDOS Y NOMBRES DEL TESTIGO 2</label>
                                                    <input list="buscadoTestigo2" id="nombresTestigo2" autocomplete="off" name="nombresTestigo2" type="text" class="form-control" placeholder="Buscar">
                                                    <datalist id="buscadoTestigo2">
                                                        '.$option.'
                                                    </datalist>
                                                </div> 
                                                <div class="form-group col-lg-6">
                                                    <label for="fechaNacimientoTestigo2">FECHA DE NACIMIENTO DEL TESTIGO 2</label>
                                                    <input type="date" id="fechaNacimientoTestigo2" name="fechaNacimientoTestigo2" class="form-control" >
                                                </div>';
                                            
                                            
                                            $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
                                            $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                                            foreach ($listaProvincias as $valueProvincias) {
                                                $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                                                $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                                            }
                                            
                                            
                                            $tabla = '<div class="col-lg-12 form-group">
                                                '.$selectTestigo1.$selectTestigo2.'
                                            </div>
                                            <div class="form-group col-lg-4">
                                                <h4 class="text-center">DATOS DEL MATRIMONIO</h4>
                                                <input type="hidden" value="'.$idEsposoEncriptado.'" name="idEsposoEncriptado" id="idEsposoEncriptado">
                                                <input type="hidden" value="'.$idEsposaEncriptado.'" name="idEsposaEncriptado" id="idEsposaEncriptado">
                                                <label for="numeroPagina">NÚMERO DE PÁGINA</label>
                                                <input onkeydown="validarNumeros(\'numeroPagina\');" maxlength="10" autocomplete="off"  type="text" id="numeroPagina" name="numeroPagina" class="form-control">
                                                <label for="numeroActaMatrimonial">NÚMERO DE ACTA</label>
                                                <input  maxlength="10" autocomplete="off"  type="text" id="numeroActaMatrimonial" name="numeroActaMatrimonial" class="form-control">
                                                <label for="sacerdote">SACERDOTE</label>
                                                <select class="form-control" id="selectSacerdote" name="selectSacerdote">
                                                    '.$optionSelectSacerdote.'
                                                </select>
                                                <label for="fechaMatrimonio">FECHA MATRIMONIO</label>
                                                <input type="date" class="form-control" id="fechaMatrimonio" name="fechaMatrimonio">
                                                '.$selectLugares.'
                                            </div>
                                            <div class="form-group col-lg-4">
                                                <h4 class="text-center">LUGAR DEL MATRIMONIO CIVIL</h4>
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
                                                </select>
                                                
                                            </div>
                                            
                                         
                                            <div class="form-group col-lg-4">
                                                <h4 class="text-center">DATOS DEL REGISTRO CIVIL</h4>
                                                <label for="anoRegistroCivil">AÑO</label>
                                                <input onkeydown="validarNumeros(\'anoRegistroCivil\');" maxlength="10" autocomplete="off"  type="text" id="anoRegistroCivil" name="anoRegistroCivil" class="form-control">
                                                <label for="tomoRegistroCivil">TOMO</label>
                                                <input maxlength="10" autocomplete="off"  type="text" id="tomoRegistroCivil" name="tomoRegistroCivil" class="form-control">
                                                <label for="folioRegistroCivil">FOLIO</label>
                                                <input maxlength="10" autocomplete="off" type="text" id="folioRegistroCivil" name="folioRegistroCivil" class="form-control">
                                                <label for="actaRegistroCivil">ACTA</label>
                                                <input maxlength="10" autocomplete="off"  type="text" id="actaRegistroCivil" name="actaRegistroCivil" class="form-control">
                                            </div>
                                            
                                            <div class="form-group col-lg-12">
                                                '.$botonCancelar.' '.$botonGuardar.'
                                            </div>';
                                            $mensaje = '';
                                            $validar = TRUE;
                                        }
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
//    public function obtenerbautismosAction()
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
//                $request=$this->getRequest();
//                if(!$request->isPost()){
//                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
//                }else{
//                    $objBautismo = new Bautismo($this->dbAdapter);
//                    ini_set('date.timezone','America/Bogota'); 
//                    $listaBautismos = $objBautismo->ObtenerBautismos();
//                    $tabla = $this->CargarTablaBautismos($idUsuario, $this->dbAdapter, $listaBautismos, 0, count($listaBautismos));
//                    $mensaje = '';
//                    $validar = TRUE;
//                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
//                }
//            
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }
//    
//    
//     function CargarTablaBautismos($idUsuario,$adaptador,$listaBautismo, $i, $j)
//    {
////        $objConfigurarCurso = new ConfigurarCurso($adaptador);
//        $objMetodos = new Metodos();
//        ini_set('date.timezone','America/Bogota'); 
//        $objMetodosC = new MetodosControladores();
////        $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 15, 1);
////        $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 15, 2);
//        $array1 = array();
//        foreach ($listaBautismo as $value) {
//            $idBautismoEncriptado = $objMetodos->encriptar($value['idBautismo']);
//            $nombres = $value['primerApellido'].' '.$value['segundoApellido'].' '.$value['primerNombre'].' '.$value['segundoNombre'];
//            $nombresPersona = '<input type="hidden" id="estadoBautismoA'.$i.'" name="estadoBautismoA'.$i.'" value="'.$value['estadoBautismo'].'">'.$nombres;
//            $fechaNacimiento = $value['fechaNacimiento'];
//            $botonEliminarBautismo = '';
//            $botonDeshabilitarBautismo = '';
////            if($validarprivilegioEliminar == TRUE){
////                if(count($objConfigurarCurso->FiltrarConfigurarCursoPorCursoLimit1($value['idCurso'])) == 0)
////                if($value['estadoBautismo'] == 0){    
////                    $botonEliminarBautismo = '<button id="btnEliminarBautismo'.$i.'" title="ELIMINAR '.$nombres.'" onclick="eliminarBautismo(\''.$idBautismoEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
////                    $botonDeshabilitarBautismo = '<button id="btnHabilitarBautismo'.$i.'" title="HABILITAR '.$nombres.'" onclick="habilitarBautismo(\''.$idBautismoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
////                }
////                
////            }
//
////            if($validarprivilegioModificar == TRUE){
////                if($value['estadoCurso'] == TRUE)
////                    $botonDeshabilitarCurso = '<button id="btnDeshabilitarCurso'.$i.'" title="DESHABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarCurso(\''.$idCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-success btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
////                else
////                    $botonDeshabilitarCurso = '<button id="btnDeshabilitarCurso'.$i.'" title="HABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarCurso(\''.$idCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-minus-square"></i></button>';
////            }
//            $botones =  $botonDeshabilitarBautismo.' '.$botonEliminarBautismo;     
//            $array1[$i] = array(
//                '_j'=>$j,
//                'nombresPersona'=>$nombresPersona,
//                'fechaNacimiento'=>$fechaNacimiento,
//                'opciones'=>$botones,
//            );
//            $j--;
//            $i++;
//        }
//        
//        return $array1;
//    }
//    
//    
//    

    
    
    
    
    
//    public function filtrarpersonapornombresAction()
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
//                        $objPersona = new Persona($this->dbAdapter);
//                        $objSacerdotes = new Sacerdotes($this->dbAdapter);
//                        $objProvincias = new Provincias($this->dbAdapter);
//                        $objBautismo = new Bautismo($this->dbAdapter);
//                        $objPadresBautismo = new PadresBautismo($this->dbAdapter);
//                        $post = array_merge_recursive(
//                            $request->getPost()->toArray(),
//                            $request->getFiles()->toArray()
//                        );
//                         $primerApellido = strtoupper(trim($post['primerApellido']));
//                         $segundoApellido = strtoupper(trim($post['segundoApellido']));
//                         $primerNombre = strtoupper(trim($post['primerNombre']));
//                         $segundoNombre = strtoupper(trim($post['segundoNombre']));
//                         $fechaNacimiento = $post['fechaNacimiento'];
////                        if(empty($primerApellido) || strlen($primerApellido) > 80){
////                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL PRIMER APELLIDO MÁXIMO 80 CARACTERES</div>';
////                        }else if(empty ($segundoApellido) ||  strlen($segundoApellido) > 80){
////                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO APELLIDO MÁXIMO 80 CARACTERES</div>';
////                        }else if(empty ($primerNombre) || strlen($primerNombre) > 80){
////                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL PRIMER NOMBRE MÁXIMO 80 CARACTERES</div>';
////                        }else if(empty ($segundoNombre) ||  strlen($segundoNombre) > 80){
////                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO NOMBRE MÁXIMO 80 CARACTERES</div>';
////                        }else 
//                            if(empty ($fechaNacimiento)){
//                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO</div>';
//                        }else{
//                            $listaPersona = $objPersona->FiltrarPersonaPorApellidosNombres($primerApellido,$segundoApellido,$primerNombre,$segundoNombre,$fechaNacimiento);
//                            if(count($listaPersona) == 0){
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA '.$primerApellido.' '.$segundoApellido.' '.$primerNombre.' '.$segundoNombre.' POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
//                            }else if($listaPersona[0]['estadoPersona'] == FALSE){
//                                $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
//                            } else{
//                                $tabla = '';
//                                $idPersona = $listaPersona[0]['idPersona'];
//                                $listaBautismo = $objBautismo->FiltrarBautismoPorPersona($idPersona);
//                                if(count($listaBautismo) == 0){
//                                    $identificacion = 'SIN IDENTIFICACIÓN';
//                                    if($listaPersona[0]['identificacion'] != NULL){
//                                        $identificacion = $listaPersona[0]['identificacion'] ;
//                                    }
//                                    
//                                    $idPersonaEncriptado = $objMetodos->encriptar($listaPersona[0]['idPersona']);
//                                    $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarBautismo" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
//                                    $botonCancelar = '<button id="btnCancelar" onclick="limpiarFormularioBautismo();" type="button" class="btn btn-danger pull-right"><i class="fa fa-times"></i>CANCELAR</button>';
//
//                                    $listaSacerdote = $objSacerdotes->ObtenerSacerdotesEstado(1); 
//                                    $optionSelectSacerdote = '<option value="0">SELECCIONE UN SACERDOTE</option>';
//                                    foreach ($listaSacerdote as $valueSacerdote) {
//                                        $idSacerdoteEncriptado = $objMetodos->encriptar($valueSacerdote['idSacerdote']);
//                                        $listaPersona = $objPersona->FiltrarPersona($valueSacerdote['idPersona']);
//                                        $nombres = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'].' '.$listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
//
//                                        $optionSelectSacerdote =$optionSelectSacerdote.'<option value="'.$idSacerdoteEncriptado.'">'.$nombres.'</option>';
//                                    }
//                                    $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
//                                    $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
//                                    foreach ($listaProvincias as $valueProvincias) {
//                                        $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
//                                        $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
//                                    }
//                                    $tabla = '<div class="form-group col-lg-4">
//                                            <h4 class="text-center">DATOS DEL BAUTIZO</h4>
//                                            <input type="hidden" value="'.$idPersonaEncriptado.'" name="idPersonaEncriptado" id="idPersonaEncriptado">
//                                            <label for="numero">NÚMERO</label>
//                                            
//                                            <input onkeydown="validarNumeros(\'numero\');" maxlength="10" autocomplete="off" autofocus="" type="text" id="numero" name="numero" class="form-control">
//                                            <label for="sacerdote">SACERDOTE</label>
//                                            <select class="form-control" id="selectSacerdote" name="selectSacerdote">
//                                                '.$optionSelectSacerdote.'
//                                            </select>
//                                            <label for="fechaBautizo">FECHA BAUTIZO</label>
//                                            <input type="date" class="form-control" id="fechaBautizo" name="fechaBautizo">
//                                        </div>
//                                        <div class="col-lg-4">
//                                            <h4 class="text-center">LUGAR DE NACIMIENTO</h4>
//                                            <label for="selectProvincias">PROVINCIAS</label>
//                                            <select class="form-control" onchange="filtrarCantonesPorProvincia()" id="selectProvincias" name="selectProvincias">
//                                                '.$optionSelectProvincias.'
//                                            </select>
//                                            <label for="selectCantones">CANTÓN</label>
//                                            <select class="form-control" onchange="filtrarParroquiasPorProvinciaCanton()" id="selectCantones" name="selectCantones">
//                                                <option value="0">SELECCIONE UN CANTÓN</option>
//                                            </select>
//                                            <label for="selectParróquia">PARRÓQUIA</label>
//                                            <select class="form-control" id="selectParroquias" name="selectParroquias">
//                                                <option value="0">SELECCIONE UNA PARRÓQUIA</option>
//                                            </select>
//                                        </div>
//                                        <div class="form-group col-lg-4">
//                                            <h4 class="text-center">REGISTRO CIVIL</h4>
//                                            <label for="ano">AÑO</label>
//                                            <input onkeydown="validarNumeros(\'ano\');" maxlength="10" autocomplete="off" autofocus="" type="text" id="ano" name="ano" class="form-control">
//                                            <label for="tomo">TOMO</label>
//                                            <input maxlength="10" autocomplete="off" autofocus="" type="text" id="tomo" name="tomo" class="form-control">
//                                            <label for="folio">FOLIO</label>
//                                            <input maxlength="10" autocomplete="off" autofocus="" type="text" id="folio" name="folio" class="form-control">
//                                            <label for="acta">ACTA</label>
//                                            <input maxlength="10" autocomplete="off" autofocus="" type="text" id="acta" name="acta" class="form-control">
//                                            <label for="fechaInscripcion">FECHA DE INSCRIPCIÓN</label>
//                                            <input type="date" class="form-control" id="fechaInscripcion" name="fechaInscripcion">
//
//                                        </div>
//                                        <div class="form-group col-lg-12">
//                                            '.$botonCancelar.' '.$botonGuardar.'
//                                        </div>';
//                                    $mensaje = '';
//                                    $validar = TRUE;
//                                }else{
//                                    $nombres = $listaBautismo[0]['primerApellido'].' '.$listaBautismo[0]['segundoApellido'].' '.$listaBautismo[0]['primerNombre'].' '.$listaBautismo[0]['segundoNombre'];
//                                    $tablaIzquierda = '<div class="table-responsive">
//                                            <table class="table"> 
//                                                <tbody>
//                                                    <tr> 
//                                                        <th>N°</th>
//                                                        <td>'.$listaBautismo[0]['numero'].'</td>
//                                                    </tr> 
//                                                    <tr> 
//                                                        <th colspan="2">NOMBRE</th>
//                                                    </tr>
//                                                    <tr> 
//                                                        <td colspan="2">'.$nombres.'</td>
//                                                    </tr>
//                                                    <tr> 
//                                                        <th colspan="2">REGISTRO CIVIL</th>
//                                                    </tr>
//                                                    <tr> 
//                                                        <td><b>AÑO</b> '.$listaBautismo[0]['anoRegistroCivil'].'</td>
//                                                        <td><b>TOMO</b> '.$listaBautismo[0]['tomo'].'</td>
//                                                    </tr>
//                                                     <tr> 
//                                                        <td><b>FOLIO</b> '.$listaBautismo[0]['folio'].'</td>
//                                                        <td><b>ACTA</b> '.$listaBautismo[0]['acta'].'</td>
//                                                    </tr>
//                                                </tbody>
//                                            </table>
//                                            </div>';
//                                            
//                                    $tabla = '<div class="col-lg-4">'.$tablaIzquierda.'</div>';
//                                    
//                                    $mensaje = '';
//
////                                    $tabla = '<div class="alert alert-warning text-center" role="alert">ESTA PERSONA YA TIENE UN BAUTIZO AGREGADO POR FAVOR BÚSCALO(A) EN LA TABLA DE ABAJO</div>';
//                                    $validar = TRUE;
//                                }
//                                
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 18);
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 18);
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

}