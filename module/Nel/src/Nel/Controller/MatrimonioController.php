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
use Nel\Modelo\Entity\Administrativos;
use Nel\Modelo\Entity\TestigosMatrimonio;
use Nel\Modelo\Entity\LugaresMisa;
use Nel\Modelo\Entity\TipoPadre;
use Nel\Modelo\Entity\PadrinosMatrimonio;
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
                        $objAdministrativos = new Administrativos($this->dbAdapter);
                        $objPadrinosMatrimonio = new PadrinosMatrimonio($this->dbAdapter);
                        $objTestigosMatrimonio = new TestigosMatrimonio($this->dbAdapter);
                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idEsposoEncriptado = $post['idEsposoEncriptado'];
                        $idEsposaEncriptado = $post['idEsposaEncriptado'];
                        $fechaMatrimonio = $post['fechaMatrimonio'];
                        $idLugarEncriptado  = $post['lugarMatrimonio'];
                        $idConfigurarParroquiaCantonEncriptado = $post['selectParroquias'];
                        $anoRegistroCivil = trim($post['anoRegistroCivil']);
                        $tomoRegistroCivil = strtoupper(trim($post['tomoRegistroCivil']));
                        $folioRegistroCivil = strtoupper(trim($post['folioRegistroCivil']));
                        $actaRegistroCivil = strtoupper(trim($post['actaRegistroCivil']));
                        $fechaInscripcionResgistroCivil = $post['fechaInscripcionRegistroCivil'];
                        $anoEclesiastico = trim($post['anoEclesiastico']);
                        $tomoEclesiastico = strtoupper(trim($post['tomoEclesiastico']));
                        $numeroPagina = $post['numeroPagina'];
                        $numeroActaMatrimonial = $post['numeroActaMatrimonial'];
                        $fechaInscripcionEclesiastico = $post['fechaInscripcionEclesiastico'];
                        $identificacionTestigo1 = $post['identificacionTestigo1'];
                        $identificacionTestigo2 = $post['identificacionTestigo2'];
                        $identificacionPadrino = $post['identificacionPadrino'];
                        $identificacionMadrina = $post['identificacionMadrina'];
                        if(empty ($identificacionTestigo1)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACION DEL TESTIGO 1</div>';
                        }else if(empty ($identificacionTestigo2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACION DEL TESTIGO 2</div>';
                        }else if(empty ($identificacionPadrino)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACION DEL PADRINO</div>';
                        }else if(empty ($identificacionMadrina)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACION DE LA MADRINA</div>';
                        }else if(empty ($idEsposoEncriptado) || $idEsposoEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL ESPOSO</div>';
                        }else if(empty ($idEsposaEncriptado) || $idEsposaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA ESPOSA</div>';
                        }else if(!is_numeric ($anoEclesiastico) || strlen($anoEclesiastico) > 4){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL AÑO DEL REGISTRO ECLESIÁSTICO 4 DÍGITOS</div>';
                        }else if(empty ($tomoEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL TOMO DEL REGISTRO ECLESIÁSTICO</div>';
                        }else if(!is_numeric($numeroPagina)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NÚMERO DE PÁGINA DATOS DEL MATRIMONIO</div>';
                        }else if(empty ($numeroActaMatrimonial)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NÚMERO DE ACTA MATRIMONIAL</div>';
                        }else if(empty ($fechaInscripcionEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE INSCRIPCIÓN DEL REGISTRO ECLESIÁSTICO</div>';
                        }else if(count($objMatrimonio->FiltrarMatrimonioPorNumeroActaMatrimonial($numeroActaMatrimonial)) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN NÚMERO DE ACTA MATRIMONIAL '.$numeroActaMatrimonial.'</div>';
                        }else   if(empty ($fechaMatrimonio)){
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
                        }else if(empty ($fechaInscripcionResgistroCivil)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE INSCRIPCIÓN DEL REGISTRO CIVIL</div>';
                        }else{
                            $listaTestigo1 = $objPersona->FiltrarPersonaPorIdentificacion($identificacionTestigo1);
                            $listaTestigo2 = $objPersona->FiltrarPersonaPorIdentificacion($identificacionTestigo2);
                            $listaPadrino = $objPersona->FiltrarPersonaPorIdentificacion($identificacionPadrino);
                            $listaMadrina = $objPersona->FiltrarPersonaPorIdentificacion($identificacionMadrina);
                            if(count($listaTestigo1) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL TESTIGO 1 CON IDENTIFICACIÓN '.$identificacionTestigo1.' NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if(count($listaTestigo2) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL TESTIGO 2 CON IDENTIFICACIÓN '.$identificacionTestigo2.' NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if(count($listaPadrino) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENTIFICACIÓN '.$identificacionPadrino.' NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if($listaPadrino[0]['identificadorSexo'] != 1){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENTIFICACIÓN '.$identificacionMadrina.' DEBE DE SER SEXO MASCULINO</div>';
                            }else if(count($listaMadrina) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRINA CON IDENTIFICACIÓN '.$identificacionMadrina.' NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if($listaMadrina[0]['identificadorSexo'] != 2){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRINA CON IDENTIFICACIÓN '.$identificacionMadrina.' DEBE DE SER SEXO FEMENINO</div>';
                            }else{
                                ini_set('date.timezone','America/Bogota'); 
                                $fechaNacimientoTestigo1 = new \DateTime($listaTestigo1[0]['fechaNacimiento']);
                                $fechaNacimientoTestigo2 = new \DateTime($listaTestigo2[0]['fechaNacimiento']);
                                $fechaNacimientoPadrino = new \DateTime($listaPadrino[0]['fechaNacimiento']);
                                $fechaNacimientoMadrina = new \DateTime($listaMadrina[0]['fechaNacimiento']);
                                $fechaActual = new \DateTime(date("d-m-Y"));
                                $diffTestigo1 = $fechaActual->diff($fechaNacimientoTestigo1);
                                $diffTestigo2 = $fechaActual->diff($fechaNacimientoTestigo2);
                                $diffPadrino = $fechaActual->diff($fechaNacimientoPadrino);
                                $diffMadrina = $fechaActual->diff($fechaNacimientoMadrina);
                                if($diffTestigo1->y < 18){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL TESTIGO 1 CON IDENTIFICACIÓN '.$identificacionTestigo1.' AÚN ES MENOR DE EDAD</div>';
                                }else  if($diffTestigo2->y < 18){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL TESTIGO 2 CON IDENTIFICACIÓN '.$identificacionTestigo2.' AÚN ES MENOR DE EDAD</div>';
                                }else if($diffPadrino->y < 18){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENTIFICACIÓN '.$identificacionPadrino.' AÚN ES MENOR DE EDAD</div>';
                                }else if($diffMadrina->y < 18){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRINA CON IDENTIFICACIÓN '.$identificacionMadrina.' AÚN ES MENOR DE EDAD</div>';
                                }else{
                                    $fechaMatrimonioCom = strtotime($fechaMatrimonio);
                                    $fechaInscripcionRegistroCivilCom = strtotime($fechaInscripcionResgistroCivil);
                                    $fechaInscripcionEclesiasticoCom = strtotime($fechaInscripcionEclesiastico);
                                    if($fechaInscripcionRegistroCivilCom > $fechaMatrimonioCom){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCION DE REGISTRO CIVIL NO DEBE SER MAYOR A LA FECHA DEL MATRIMONIO</div>';                        
                                    }else if($fechaInscripcionEclesiasticoCom > $fechaMatrimonioCom){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCION DE REGISTRO ECLESIÁSTICO NO DEBE SER MAYOR A LA FECHA DEL MATRIMONIO</div>';                        
                                    }else{
                                        $idTestigo1 = $listaTestigo1[0]['idPersona'];
                                        $idTestigo2 = $listaTestigo2[0]['idPersona'];
                                        $idPadrino = $listaPadrino[0]['idPersona'];
                                        $idMadrina = $listaMadrina[0]['idPersona'];
                                        $idEsposo = $objMetodos->desencriptar($idEsposoEncriptado);
                                        $idEsposa = $objMetodos->desencriptar($idEsposaEncriptado);
                                        if($idEsposo == $idTestigo1 || $idEsposo == $idTestigo2 ){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSO NO PUEDE SER TESTIGO</div>';                        
                                        }else if($idEsposo == $idPadrino || $idEsposo == $idMadrina){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSO NO PUEDE SER PADRINO</div>';                        
                                        }else if($idEsposo == $idEsposa){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSO Y LA ESPOSA SON LA MISMA PERSONA</div>';                        
                                        }else if($idEsposa == $idTestigo1 || $idEsposa == $idTestigo2 ){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA ESPOSA NO PUEDE SER TESTIGO</div>';                        
                                        }else if($idEsposa == $idPadrino || $idEsposa == $idMadrina){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA ESPOSA NO PUEDE SER PADRINO</div>';                        
                                        }else if($idTestigo1 == $idPadrino || $idTestigo1 == $idMadrina){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LOS TESTIGOS Y LOS PADRINOS NO PUEDEN SER LAS MISMAS PERSONAS</div>';                        
                                        }else if($idTestigo2 == $idPadrino || $idTestigo2 == $idMadrina){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LOS TESTIGOS Y LOS PADRINOS NO PUEDEN SER LAS MISMAS PERSONAS</div>';                        
                                        }else if($idTestigo1 == $idTestigo2){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL TESTIGO 1 Y EL TESTIGO 2 NO PUEDEN SER LA MISMA PERSONA</div>';                        
                                        }else if($idPadrino == $idMadrina){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO Y LA MADRINA NO PUEDEN SER LA MISMA PERSONA</div>';                        
                                        }else{
                                            $listaEsposo = $objPersona->FiltrarPersona($idEsposo);
                                            $listaEsposa = $objPersona->FiltrarPersona($idEsposa);
                                            $fechaNacimientoEsposo = new \DateTime($listaEsposo[0]['fechaNacimiento']);
                                            $fechaNacimientoEsposa = new \DateTime($listaEsposa[0]['fechaNacimiento']);
                                            $diffEsposo = $fechaActual->diff($fechaNacimientoEsposo);
                                            $diffEsposa = $fechaActual->diff($fechaNacimientoEsposa);
                                            if($diffEsposo->y < 18){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSO CON IDENTIFICACIÓN '.$identificacionTestigo1.' AÚN ES MENOR DE EDAD</div>';
                                            }else  if($diffEsposa->y < 18){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA ESPOSA CON IDENTIFICACIÓN '.$identificacionTestigo2.' AÚN ES MENOR DE EDAD</div>';
                                            }else{
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
                                                                $resultado = $objMatrimonio->IngresarMatrimonio($idEsposo, $idEsposa,$idIglesia, $idLugar, $idConfigurarParroquiaCanton, $anoRegistroCivil, $tomoRegistroCivil, $folioRegistroCivil, $actaRegistroCivil,$anoEclesiastico,$tomoEclesiastico, $numeroPagina, $numeroActaMatrimonial,$fechaInscripcionResgistroCivil,$fechaInscripcionEclesiastico, $fechaMatrimonio, $fechaSubida, 1);
                                                                if(count($resultado) == 0){
                                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL MATRIMONIO POR FAVOR INTENTE MÁS TARDE</div>';
                                                                }else{ 
                                                                    $idMatrimonio = $resultado[0]['idMatrimonio'];
                                                                    $resultadoTestigo = $objTestigosMatrimonio->IngresarTestigosMatrimonio($idMatrimonio, $idTestigo1, 1);
                                                                    $resultadoTestigo2 = $objTestigosMatrimonio->IngresarTestigosMatrimonio($idMatrimonio, $idTestigo2, 1);
                                                                    $resultadoPadrino = $objPadrinosMatrimonio->IngresarPadrinosMatrimonio($idMatrimonio, $idPadrino, 1);
                                                                    $resultadoMadrina = $objPadrinosMatrimonio->IngresarPadrinosMatrimonio($idMatrimonio, $idMadrina, 1);
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
                        $objPadrinosMatrimonio = new PadrinosMatrimonio($this->dbAdapter);
                        $objTestigosMatrimonio = new TestigosMatrimonio($this->dbAdapter);
                        $objAdministrativo = new Administrativos($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $identificacionEsposo = trim($post['identificacionEsposo']);
                        $identificacionEsposa = trim($post['identificacionEsposa']);
                        if(empty ($identificacionEsposo)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACIÓN DEL ESPOSO</div>';
                        }else if(empty ($identificacionEsposa)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACIÓN DE LA ESPOSA</div>';
                        }else{
                            $listaPersonaEsposo = $objPersona->FiltrarPersonaPorIdentificacion($identificacionEsposo);
                            $listaPersonaEsposa = $objPersona->FiltrarPersonaPorIdentificacion($identificacionEsposa);
                            if(count($listaPersonaEsposo) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA CON IDENTIFICACIÓN '.$identificacionEsposo.'  POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if($listaPersonaEsposo[0]['estadoPersona'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSO HA SIDO DESHABILITADO POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                            } else if($listaPersonaEsposo[0]['identificadorSexo'] != 1){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSO DEBE DE SER SEXO MASCULINO</div>';
                            } else if(count($listaPersonaEsposa) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA CON IDENTIFICACIÓN '.$identificacionEsposa.'  POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                            }else if($listaPersonaEsposa[0]['estadoPersona'] == FALSE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA ESPOSA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                            } else if($listaPersonaEsposa[0]['identificadorSexo'] != 2){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA ESPOSA DEBE DE SER SEXO FEMENINO</div>';
                            } else{
                                $nombresEsposo = $listaPersonaEsposo[0]['primerApellido'].' '.$listaPersonaEsposo[0]['segundoApellido'].' '.$listaPersonaEsposo[0]['primerNombre'].' '.$listaPersonaEsposo[0]['segundoNombre'];
                                $nombresEsposa = $listaPersonaEsposa[0]['primerApellido'].' '.$listaPersonaEsposa[0]['segundoApellido'].' '.$listaPersonaEsposa[0]['primerNombre'].' '.$listaPersonaEsposa[0]['segundoNombre'];
                                ini_set('date.timezone','America/Bogota'); 
                                $fechaNacimientoEsposo = new \DateTime($listaPersonaEsposo[0]['fechaNacimiento']);
                                $fechaNacimientoEsposa = new \DateTime($listaPersonaEsposa[0]['fechaNacimiento']);
                                $fechaActual = new \DateTime(date("d-m-Y"));
                                $diffEsposo = $fechaActual->diff($fechaNacimientoEsposo);
                                $diffEsposa = $fechaActual->diff($fechaNacimientoEsposa);
                                if($diffEsposo->y < 18){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL ESPOSO '.$nombresEsposo.' AÚN ES MENOR DE EDAD TIENE '.$diffEsposo->y.' AÑOS</div>';
                                }else if ($diffEsposa->y < 18){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA ESPOSA '.$nombresEsposa.' AÚN ES MENOR DE EDAD TIENE '.$diffEsposa->y.' AÑOS</div>';
                                }else{
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
                                            $listaAdministrativo = $objAdministrativo->FiltrarAdministrativosPorIdentificadorCargo(1);
                                            if(count($listaAdministrativo) != 1){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UN PÁRROCO QUE FIRME EL DOCUMENTO POR FAVOR DIRÍGETE AL MENÚ <b>TALENTO HUMANO->ADMINISTRATIVOS</b>Y AGREGA UN PÁRROCO</div>';
                                            }else{
                                                $listaPadrinos = $objPadrinosMatrimonio->FiltrarPadrinosMatrimonioPorMatrimonio($listaMatrimonioEsposoEsposa[0]['idMatrimonio']);
                                                $nombresPadrino = '';
                                                $nombresMadrina = '';
                                                foreach ($listaPadrinos as $valuePadrinos) {
                                                   if($valuePadrinos['identificadorSexo'] == 1){
                                                        $nombresPadrino = $valuePadrinos['primerApellido'].' '.$valuePadrinos['segundoApellido'].' '.$valuePadrinos['primerNombre'].' '.$valuePadrinos['segundoNombre'];
                                                   }else{
                                                        $nombresMadrina = $valuePadrinos['primerApellido'].' '.$valuePadrinos['segundoApellido'].' '.$valuePadrinos['primerNombre'].' '.$valuePadrinos['segundoNombre'];
                                                   }
                                                }
                                                $listaPadresBautismoEsposo = $objPadresBautismo->FiltrarPadreBautismoPorBautismo($listaBautismoEsposo[0]['idBautismo']);
                                                $listaPadresBautismoEsposa = $objPadresBautismo->FiltrarPadreBautismoPorBautismo($listaBautismoEsposa[0]['idBautismo']);
                                                $padreEsposo = '';
                                                $madreEsposo = '';
                                                foreach ($listaPadresBautismoEsposo as $valuePadresEsposo) {
                                                    if($valuePadresEsposo['identificadorTipoPadre'] == 1){
                                                        $padreEsposo = $valuePadresEsposo['primerApellido'].' '.$valuePadresEsposo['segundoApellido'].' '.$valuePadresEsposo['primerNombre'].' '.$valuePadresEsposo['segundoNombre'];
                                                    }else{
                                                        $madreEsposo = $valuePadresEsposo['primerApellido'].' '.$valuePadresEsposo['segundoApellido'].' '.$valuePadresEsposo['primerNombre'].' '.$valuePadresEsposo['segundoNombre'];
                                                    }
                                                }
                                                $padreEsposa = '';
                                                $madreEsposa = '';
                                                 foreach ($listaPadresBautismoEsposa as $valuePadresEsposa) {
                                                    if($valuePadresEsposa['identificadorTipoPadre'] == 1){
                                                        $padreEsposa = $valuePadresEsposa['primerApellido'].' '.$valuePadresEsposa['segundoApellido'].' '.$valuePadresEsposa['primerNombre'].' '.$valuePadresEsposa['segundoNombre'];
                                                    }else{
                                                        $madreEsposa = $valuePadresEsposa['primerApellido'].' '.$valuePadresEsposa['segundoApellido'].' '.$valuePadresEsposa['primerNombre'].' '.$valuePadresEsposa['segundoNombre'];
                                                    }
                                                }
                                                $nombreIglesia = $sesionUsuario->offsetGet('nombreIglesia');
                                                $listaLugar = $objLugaresMisa->FiltrarLugaresMisa($listaMatrimonioEsposoEsposa[0]['idLugar']);
                                                $nombreIglesia2 = $listaLugar[0]['nombreLugar'];
                                                $direccionIglesia = $sesionUsuario->offsetGet('direccionIgleisia');
                                                $fechaMatrimonio = $objMetodos->obtenerFechaEnLetraSinHora($listaMatrimonioEsposoEsposa[0]['fechaMatrimonio']);
                                                $tablaDerecha = '<p class="text-justify" style="line-height: 30px;font-size:15px">En la Iglesia Parroquial de <b>'.$nombreIglesia2.'</b> el día <b>'.$fechaMatrimonio.'</b>, cumplido 
                                                    los requisitos canónicos, y debida preparación se presenció y bendijo el matrimonio eclesiástico del señor <b>'.$nombresEsposo.'</b> hijo de <b>'.$padreEsposo.'</b> y de <b>'.$madreEsposo.'</b> con  
                                                        la señorita <b>'.$nombresEsposa.'</b> hija de <b>'.$padreEsposa.'</b> y de <b>'.$madreEsposa.'.</b> 
                                                        </p>
                                                        <p class="text-justify" style="line-height: 30px;font-size:15px"> Fueron sus Padrinos:  <b>'.$nombresPadrino.'</b> y <b>'.$nombresMadrina.'.</b>
                                                        </p>';
                                                $tablaCaabecera = '<table class="table" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th> 
                                                                        <img style="width:10%" src="'.$this->getRequest()->getBaseUrl().'/public/librerias/images/pagina/logoiglesia.png" >
                                                                        <br><label style="font-size:24px" class="box-title ">'.$nombreIglesia.'<br>'.$direccionIglesia.'</label>
                                                                        <br> <label>Sistema Web de Gestión Parroquial</label>
                                                                    </th>
                                                                </tr>
                                                                <tr>
                                                                    <th> 
                                                                        <h3>PARTIDA MATRIMONIAL</h3>
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                        </table>';
                                                $tablaIzquierda = '<table border="1" class="table text-center" style="width:100%" > 
                                                            <thead>
                                                                <tr> 
                                                                    <th colspan="2">PÁGINA</th>
                                                                    <th colspan="2"> '.$listaMatrimonioEsposoEsposa[0]['paginaActaMatrimonial'].'</th>
                                                                </tr> 
                                                                <tr> 
                                                                    <th colspan="2">REGISTRO CIVIL</th>
                                                                    <th colspan="2">REGISTRO MATRIMONIAL</th>
                                                                </tr> 
                                                                <tr> 
                                                                    <th><b>AÑO</b> '.$listaMatrimonioEsposoEsposa[0]['anoRegistroCivil'].'</th>
                                                                    <th><b>TOMO</b> '.$listaMatrimonioEsposoEsposa[0]['tomoRegistroCivil'].'</th>
                                                                    <th><b>AÑO</b> '.$listaMatrimonioEsposoEsposa[0]['anoEcleciastico'].'</th>
                                                                    <th><b>TOMO</b> '.$listaMatrimonioEsposoEsposa[0]['tomoEcleciastico'].'</th>

                                                                </tr>
                                                                <tr> 

                                                                    <th><b>PÁGINA</b> '.$listaMatrimonioEsposoEsposa[0]['paginaRegistroCivil'].'</th>
                                                                    <th><b>ACTA</b> '.$listaMatrimonioEsposoEsposa[0]['numeroRegistroCivil'].'</th>
                                                                    <th><b>PÁGINA</b> '.$listaMatrimonioEsposoEsposa[0]['paginaActaMatrimonial'].'</th>
                                                                    <th><b>ACTA</b> '.$listaMatrimonioEsposoEsposa[0]['actaMatrimonial'].'</th>
                                                                </tr>
                                                                <tr> 
                                                                    <th>FECHA INSCRIPCIÓN</th>
                                                                    <th>'.$listaMatrimonioEsposoEsposa[0]['fechaInscripcionRegistroCivil'].'</th>
                                                                    <th>FECHA INSCRIPCIÓN</th>
                                                                    <th>'.$listaMatrimonioEsposoEsposa[0]['fechaInsciripcionEclesiastico'].'</th>
                                                                </tr> 
                                                            </thead>
                                                        </table>';
                                                        $listaPersonaFirma = $objPersona->FiltrarPersona($listaAdministrativo[0]['idPersona']);
                                                        $tablaFirma = '<table class="table text-center" style="width:100%" > 
                                                            <thead>
                                                                <tr> 
                                                                    <th>_________________________________________<br>
                                                                    '.$listaPersonaFirma[0]['primerNombre'].' '.$listaPersonaFirma[0]['segundoNombre'].' '.$listaPersonaFirma[0]['primerApellido'].' '.$listaPersonaFirma[0]['segundoApellido'].'
                                                                    <br>'.$listaAdministrativo[0]['descripcion'].'</th>
                                                                </tr> 
                                                            </thead>
                                                        </table>';
                                                $tabla = '<div class="col-lg-3"></div><div class="col-lg-6"><div id="contenedorImprimirReporte">'.$tablaCaabecera.'<br><br><br>'.$tablaDerecha.'<br><br>'.$tablaIzquierda.'<br><br><br><br>'.$tablaFirma.'</div></div><div class="col-lg-3"></div><button type="button" onclick="imprimir(\'contenedorImprimirReporte\')" class="btn btn-warning btn-flat pull-right"><i class="fa fa-print"></i>Imprimir</button>';
                                                $mensaje = '';
                                                $validar = TRUE;
                                            }
                                        }else{
                                            $objMetodosC = new MetodosControladores();
                                            $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 18, 3);
                                            if ($validarprivilegio==false)
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                                            else{
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
    //                                                $listaSacerdote = $objSacerdotes->ObtenerSacerdotesEstado(1); 
    //                                                $optionSelectSacerdote = '<option value="0">SELECCIONE UN SACERDOTE</option>';
    //                                                foreach ($listaSacerdote as $valueSacerdote) {
    //                                                    $idSacerdoteEncriptado = $objMetodos->encriptar($valueSacerdote['idSacerdote']);
    //                                                    $listaPersona = $objPersona->FiltrarPersona($valueSacerdote['idPersona']);
    //                                                    $nombres = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'].' '.$listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
    //
    //                                                    $optionSelectSacerdote =$optionSelectSacerdote.'<option value="'.$idSacerdoteEncriptado.'">'.$nombres.'</option>';
    //                                                }
                                                    $listaLugares = $objLugaresMisa->ObtenerObtenerLugaresMisa();
                                                    $optionLugares  = '<option value="0">SELECCIONE UNA IGLESIA</option>';
                                                    foreach ( $listaLugares as $valuesLugares  ){
                                                        $idLugarEncriptado = $objMetodos->encriptar($valuesLugares['idLugarMisa']);
                                                        $optionLugares  = $optionLugares.'<option value="'.$idLugarEncriptado.'">'.$valuesLugares['nombreLugar'].'</option>';
                                                    }
                                                    $selectLugares = '<label for="lugarMatrimonio">SELECCIONE LA IGLESIA</label><select class="form-control" id="lugarMatrimonio" name="lugarMatrimonio">'.$optionLugares.'</select>';
                                                    $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
                                                    $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                                                    foreach ($listaProvincias as $valueProvincias) {
                                                        $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                                                        $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                                                    }
                                                    $testigosPadrinos = '<h4 class="text-center">DATOS DE LOS TESTIGOS</h4>
                                                        <div class="col-lg-6 form-group">
                                                            <label for="identificacionTestigo1">IDENTIFICACIÓN DEL TESTIGO 1</label>
                                                            <input onkeyup="filtrarPersonaPorIdentificacion(event,\'identificacionTestigo1\',\'contenedorDatosTestigo1\');" onkeydown="validarNumeros(\'identificacionTestigo1\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacionTestigo1" name="identificacionTestigo1" class="form-control">
                                                            <div id="contenedorDatosTestigo1"></div>
                                                        </div>
                                                        <div class="col-lg-6 form-group">
                                                            <label for="identificacionTestigo2">IDENTIFICACIÓN DEL TESTIGO 2</label>
                                                            <input <input onkeyup="filtrarPersonaPorIdentificacion(event,\'identificacionTestigo2\',\'contenedorDatosTestigo2\');" onkeydown="validarNumeros(\'identificacionTestigo2\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacionTestigo2" name="identificacionTestigo2" class="form-control">
                                                            <div id="contenedorDatosTestigo2"></div>
                                                        </div>
                                                        <h4 class="text-center">DATOS DE LOS PADRINOS</h4>
                                                        <div class="col-lg-6 form-group">
                                                            <label for="identificacionPadrino">IDENTIFICACIÓN DEL PADRINO</label>
                                                            <input onkeyup="filtrarPersonaPorIdentificacion(event,\'identificacionPadrino\',\'contenedorDatosPadrino\');" onkeydown="validarNumeros(\'identificacionPadrino\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacionPadrino" name="identificacionPadrino" class="form-control">
                                                            <div id="contenedorDatosPadrino"></div>
                                                        </div>

                                                        <div class="col-lg-6 form-group">
                                                            <label for="identificacionMadrina">IDENTIFICACIÓN DE LA MADRINA</label>
                                                            <input onkeyup="filtrarPersonaPorIdentificacion(event,\'identificacionMadrina\',\'contenedorDatosMadrina\');" onkeydown="validarNumeros(\'identificacionMadrina\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacionMadrina" name="identificacionMadrina" class="form-control">
                                                            <div id="contenedorDatosMadrina"></div>
                                                        </div>';
                                                    $tabla = '<div class="col-lg-12 form-group">
                                                        '.$testigosPadrinos.'
                                                    </div>
                                                    <div class="form-group col-lg-3">
                                                        <h4 class="text-center">DATOS DEL MATRIMONIO</h4>
                                                        <input type="hidden" value="'.$idEsposoEncriptado.'" name="idEsposoEncriptado" id="idEsposoEncriptado">
                                                        <input type="hidden" value="'.$idEsposaEncriptado.'" name="idEsposaEncriptado" id="idEsposaEncriptado">

                                                        <label for="fechaMatrimonio">FECHA MATRIMONIO</label>
                                                        <input type="date" class="form-control" id="fechaMatrimonio" name="fechaMatrimonio">
                                                        '.$selectLugares.'
                                                    </div>
                                                    <div class="form-group col-lg-3">
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
                                                    <div class="form-group col-lg-3">
                                                        <h4 class="text-center">DATOS DEL REGISTRO CIVIL</h4>
                                                        <label for="anoRegistroCivil">AÑO</label>
                                                        <input onkeydown="validarNumeros(\'anoRegistroCivil\');" maxlength="10" autocomplete="off"  type="text" id="anoRegistroCivil" name="anoRegistroCivil" class="form-control">
                                                        <label for="tomoRegistroCivil">TOMO</label>
                                                        <input maxlength="10" autocomplete="off"  type="text" id="tomoRegistroCivil" name="tomoRegistroCivil" class="form-control">
                                                        <label for="folioRegistroCivil">PÁGINA</label>
                                                        <input maxlength="10" autocomplete="off" type="text" id="folioRegistroCivil" name="folioRegistroCivil" class="form-control">
                                                        <label for="actaRegistroCivil">ACTA</label>
                                                        <input maxlength="10" autocomplete="off"  type="text" id="actaRegistroCivil" name="actaRegistroCivil" class="form-control">
                                                        <label for="fechaInscripcionRegistroCivil">FECHA DE INSCRIPCIÓN</label>
                                                        <input type="date" class="form-control" id="fechaInscripcionRegistroCivil" name="fechaInscripcionRegistroCivil">
                                                    </div>
                                                    <div class="form-group col-lg-3">
                                                        <h4 class="text-center">DATOS DEL REGISTRO ECLESIÁSTICO</h4>
                                                        <label for="anoEclesiastico">AÑO</label>
                                                        <input onkeydown="validarNumeros(\'anoEclesiastico\');" maxlength="10" autocomplete="off"  type="text" id="anoEclesiastico" name="anoEclesiastico" class="form-control">
                                                        <label for="tomoEclesiastico">TOMO</label>
                                                        <input maxlength="10" autocomplete="off"  type="text" id="tomoEclesiastico" name="tomoEclesiastico" class="form-control">
                                                        <label for="numeroPagina">PÁGINA</label>
                                                        <input onkeydown="validarNumeros(\'numeroPagina\');" maxlength="10" autocomplete="off"  type="text" id="numeroPagina" name="numeroPagina" class="form-control">
                                                        <label for="numeroActaMatrimonial">ACTA</label>
                                                        <input  maxlength="10" autocomplete="off"  type="text" id="numeroActaMatrimonial" name="numeroActaMatrimonial" class="form-control">    
                                                        <label for="fechaInscripcionEclesiastico">FECHA DE INSCRIPCIÓN</label>
                                                        <input type="date" class="form-control" id="fechaInscripcionEclesiastico" name="fechaInscripcionEclesiastico">
                                                    </div>
                                                    <div class="form-group col-lg-12">
                                                        '.$botonCancelar.' '.$botonGuardar.'
                                                    </div>';
                                                $mensaje = '';
                                                $validar = TRUE;
                                            }
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
    public function obtenermatrimoniosAction()
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
                    $objMatrimonio = new Matrimonio($this->dbAdapter);
                    ini_set('date.timezone','America/Bogota'); 
                    $listaMatrimonios = $objMatrimonio->ObtenerMatrimonios();
                    $tabla = $this->CargarTablaMatrimonios($idUsuario, $this->dbAdapter, $listaMatrimonios, 0, count($listaMatrimonios));
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    function CargarTablaMatrimonios($idUsuario,$adaptador,$listaMatrimonios, $i, $j)
    {
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $objMetodosC = new MetodosControladores();
//        $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 15, 1);
//        $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 15, 2);
        $array1 = array();
        foreach ($listaMatrimonios as $value) {
            $identificacionEsposo = '';
            if($value['identificacionEsposo'] != NULL){
                $identificacionEsposo = $value['identificacionEsposo'];
            }
            $identificacionEsposa = '';
            if($value['identificacionEsposa'] != NULL){
                $identificacionEsposa = $value['identificacionEsposa'];
            }
            $idMatrimonioEncriptado = $objMetodos->encriptar($value['idMatrimonio']);
            $nombresEsposo = $value['primerApellidoEsposo'].' '.$value['segundoApellidoEsposo'].' '.$value['primerNombreEsposo'].' '.$value['segundoNombreEsposo'];
            $nombresEsposa = $value['primerApellidoEsposa'].' '.$value['segundoApellidoEsposa'].' '.$value['primerNombreEsposa'].' '.$value['segundoNombreEsposa'];
            $fechaNacimientoEsposo = $value['fechaNacimientoEsposo'];
            $fechaNacimientoEsposa = $value['fechaNacimientoEsposa'];
            $botonEliminarMatrimonio = '';
            $botonDeshabilitarMatrimonio = '';
//            if($validarprivilegioEliminar == TRUE){
//                if(count($objConfigurarCurso->FiltrarConfigurarCursoPorCursoLimit1($value['idCurso'])) == 0)
//                if($value['estadoBautismo'] == 0){    
//                    $botonEliminarBautismo = '<button id="btnEliminarBautismo'.$i.'" title="ELIMINAR '.$nombres.'" onclick="eliminarBautismo(\''.$idBautismoEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
//                    $botonDeshabilitarBautismo = '<button id="btnHabilitarBautismo'.$i.'" title="HABILITAR '.$nombres.'" onclick="habilitarBautismo(\''.$idBautismoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
//                }
//                
//            }

//            if($validarprivilegioModificar == TRUE){
//                if($value['estadoCurso'] == TRUE)
//                    $botonDeshabilitarCurso = '<button id="btnDeshabilitarCurso'.$i.'" title="DESHABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarCurso(\''.$idCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-success btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
//                else
//                    $botonDeshabilitarCurso = '<button id="btnDeshabilitarCurso'.$i.'" title="HABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarCurso(\''.$idCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-minus-square"></i></button>';
//            }
            $botones =  $botonDeshabilitarMatrimonio.' '.$botonEliminarMatrimonio;     
            $array1[$i] = array(
                '_j'=>$j,
                'identificacionEsposo'=>$identificacionEsposo,
                'nombresEsposo'=>$nombresEsposo,
                'fechasNacimientoEsposo'=>$fechaNacimientoEsposo,
                'identificacionEsposa'=>$identificacionEsposa,
                'nombresEsposa'=>$nombresEsposa,
                'fechasNacimientoEsposa'=>$fechaNacimientoEsposa,
                'opciones'=>$botones,
            );
            $j--;
            $i++;
        }
        return $array1;
    }
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
    
    
    public function filtrarpersonaporidentificacionAction()
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
                    $objMetodos = new Metodos();
                    $objPersona = new Persona($this->dbAdapter);
                    $objSacerdotes = new Sacerdotes($this->dbAdapter);
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
                            ini_set('date.timezone','America/Bogota'); 
                            $fechaNacimiento = new \DateTime($listaPersona[0]['fechaNacimiento']);
                            $fechaActual = new \DateTime(date("d-m-Y"));
                            $diff = $fechaActual->diff($fechaNacimiento);

                            $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                            $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
                            $tabla = '<div class="table-responsive"><table class="table">
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
                                        <th>EDAD</th>
                                        <td>'.$diff->y.'</td>
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
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
}