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
use Nel\Modelo\Entity\LugaresMisa;
use Nel\Modelo\Entity\Sexo;
use Nel\Modelo\Entity\PadresBautismo;
use Nel\Modelo\Entity\PadrinosBautismo;
use Nel\Modelo\Entity\Administrativos;
use Nel\Modelo\Entity\TipoPadre;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class BautismoController extends AbstractActionController
{

    public $dbAdapter;
    
    public function obtenerbautismosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 15);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objBautismo = new Bautismo($this->dbAdapter);
                    ini_set('date.timezone','America/Bogota'); 
                    $listaBautismos = $objBautismo->ObtenerBautismos();
                    $tabla = $this->CargarTablaBautismos($idUsuario, $this->dbAdapter, $listaBautismos, 0, count($listaBautismos));
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }
            
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
     function CargarTablaBautismos($idUsuario,$adaptador,$listaBautismo, $i, $j)
    {
//        $objConfigurarCurso = new ConfigurarCurso($adaptador);
        $objMetodos = new Metodos();
        ini_set('date.timezone','America/Bogota'); 
        $objMetodosC = new MetodosControladores();
//        $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 15, 1);
//        $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 15, 2);
        $array1 = array();
        foreach ($listaBautismo as $value) {
            $identificacion = '';
            if($value['identificacion'] != NULL){
                $identificacion = $value['identificacion'];
            }
            $idBautismoEncriptado = $objMetodos->encriptar($value['idBautismo']);
            $nombres = $value['primerApellido'].' '.$value['segundoApellido'].' '.$value['primerNombre'].' '.$value['segundoNombre'];
            $nombresPersona = '<input type="hidden" id="estadoBautismoA'.$i.'" name="estadoBautismoA'.$i.'" value="'.$value['estadoBautismo'].'">'.$nombres;
            $fechaNacimiento = $value['fechaNacimiento'];
            $botonEliminarBautismo = '';
            $botonDeshabilitarBautismo = '';
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
            $botones =  $botonDeshabilitarBautismo.' '.$botonEliminarBautismo;     
            $array1[$i] = array(
                '_j'=>$j,
                'identificacion'=>$identificacion,
                'nombresPersona'=>$nombresPersona,
                'fechaNacimiento'=>$fechaNacimiento,
                'opciones'=>$botones,
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    
    
    
    public function ingresarbautismoAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 15);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 15, 3);
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
                        $objLugar = new LugaresMisa($this->dbAdapter);
                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idPersonaEncriptado = $post['idPersonaEncriptado'];
                        $numero = $post['numero'];
                        $idSacerdoteEncriptado = $post['selectSacerdote'];
                        $fechaBautizo = $post['fechaBautizo'];
                        $idConfigurarParroquiaCantonEncriptado = $post['selectParroquias'];
                        $idLugarEncriptado = $post['lugarBautizo'];
                        $anoRegistroCivil = trim($post['ano']);
                        $tomo = strtoupper(trim($post['tomo']));
                        $folio = strtoupper(trim($post['folio']));
                        $acta = strtoupper(trim($post['acta']));
                        $fechaInscripcion = $post['fechaInscripcion'];
                        
                        
                        $identificacionPadre = trim($post['identificacionPadre']);
                        $identificacionMadre = trim($post['identificacionMadre']);
                        $identificacionPadrino = trim($post['identificacionPadrino']);
                        $identificacionMadrina = trim($post['identificacionMadrina']);
                        

                        
                        $anoEclesiastico = trim($post['anoEclesiastico']);
                        $tomoEclesiastico = strtoupper(trim($post['tomoEclesiastico']));
                        $folioEclesiastico = strtoupper(trim($post['folioEclesiastico']));
                        $actaEclesiastico = strtoupper(trim($post['actaEclesiastico']));
                        $fechaInscripcionEclesiastico = $post['fechaInscripcionEclesiastico'];
                        if(empty ($identificacionPadre)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACIÓN DEL PADRE</div>';
                        }else if(empty ($identificacionMadre)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACIÓN DE LA MADRE</div>';
                        }else if(empty ($identificacionPadrino)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACIÓN DEL PADRINO</div>';
                        }else if(empty ($identificacionMadrina)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACIÓN DE LA MADRINA</div>';
                        }else if(empty ($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                        }else if(!is_numeric($numero)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL  NÚMERO</div>';
                        }else if(empty ($idSacerdoteEncriptado) || $idSacerdoteEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL SACERDOTE</div>';
                        }else if(empty ($fechaBautizo)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DEL BAUTIZO</div>';
                        }else if(empty ($idLugarEncriptado) || $idLugarEncriptado == "0"){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE LA IGLESIA</div>';
                        }else if(empty ($idConfigurarParroquiaCantonEncriptado) || $idConfigurarParroquiaCantonEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA DIRECCIÓN</div>';
                        }else if(!is_numeric ($anoRegistroCivil) || strlen($anoRegistroCivil) > 4){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL AÑO DEL REGISTRO CIVIL 4 DÍGITOS</div>';
                        }else if(empty ($tomo)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL TOMO</div>';
                        }else if(empty ($folio)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL FOLIO</div>';
                        }else if(empty ($acta)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL ACTA</div>';
                        }else if(empty ($fechaInscripcion)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE INSCRIPCIÓN</div>';
                        }else if(!is_numeric ($anoEclesiastico) || strlen($anoEclesiastico) > 4){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL AÑO ECLESIÁSTICO 4 DÍGITOS</div>';
                        }else if(empty ($tomoEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL TOMO ECLESIÁSTICO</div>';
                        }else if(empty ($folioEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL FOLIO ECLESIÁSTICO</div>';
                        }else if(empty ($actaEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL ACTA ECLESIÁSTICO</div>';
                        }else if(empty ($fechaInscripcionEclesiastico)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE INSCRIPCIÓN ECLESIÁSTICO</div>';
                        }else if(count ($objBautismo->FiltrarBautismoPorNumero($numero)) > 0 ){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN BAUTIZO CON EL NÚMERO '.$numero.'</div>';
                        }else {
                            
                            
                            
                            
                            
                            $idLugar = $objMetodos->desencriptar($idLugarEncriptado);
                            $listaLugar = $objLugar->FiltrarLugaresMisa($idLugar);
                            if(count($listaLugar) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA IGLESIA SELECCIONADA NO EXISTE EN LA BASE DE DATOS</div>';
                            }else{
                            
                                $listaPadre = $objPersona->FiltrarPersonaPorIdentificacion($identificacionPadre);
                                $listaMadre = $objPersona->FiltrarPersonaPorIdentificacion($identificacionMadre);
                                $listaPadrino = $objPersona->FiltrarPersonaPorIdentificacion($identificacionPadrino);
                                $listaMadrina = $objPersona->FiltrarPersonaPorIdentificacion($identificacionMadrina);

                                if(count($listaPadre) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRE CON IDENETIFICACIÓN  '.$identificacionPadre.'  NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                                }else if($listaPadre[0]['identificadorSexo'] != 1){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRE CON IDENTIFICACIÓN '.$identificacionMadrina.' DEBE DE SER SEXO MASCULINO</div>';
                                }else if(count($listaMadre) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRE CON IDENETIFICACIÓN  '.$identificacionMadre.'  NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                                }else  if($listaMadre[0]['identificadorSexo'] != 2){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRE CON IDENTIFICACIÓN '.$identificacionMadrina.' DEBE DE SER SEXO FEMENINO</div>';
                                }else if(count($listaPadrino) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENETIFICACIÓN  '.$identificacionPadrino.'  NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                                }else if($listaPadrino[0]['identificadorSexo'] != 1){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENTIFICACIÓN '.$identificacionMadrina.' DEBE DE SER SEXO MASCULINO</div>';
                                }else if(count($listaMadrina) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRINA CON IDENETIFICACIÓN  '.$identificacionMadrina.'  NO EXISTE POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
                                }else if($listaMadrina[0]['identificadorSexo'] != 2){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRINA CON IDENTIFICACIÓN '.$identificacionMadrina.' DEBE DE SER SEXO FEMENINO</div>';
                                }else{
                                    
                                    
                                    ini_set('date.timezone','America/Bogota');    
                                    $fechaNacimientoPadrino = new \DateTime($listaPadrino[0]['fechaNacimiento']);
                                    $fechaNacimientoMadrina = new \DateTime($listaMadrina[0]['fechaNacimiento']);
                                    $fechaActual = new \DateTime(date("d-m-Y"));
                                    $diffPadrino = $fechaActual->diff($fechaNacimientoPadrino);
                                    $diffMadrina = $fechaActual->diff($fechaNacimientoMadrina);
                                    if($diffPadrino->y < 18){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRINO CON IDENTIFICACIÓN '.$identificacionPadrino.' AÚN ES MENOR DE EDAD</div>';
                                    }else if($diffMadrina->y < 18){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRINA CON IDENTIFICACIÓN '.$identificacionMadrina.' AÚN ES MENOR DE EDAD</div>';
                                    }else{
                                    

                                       
                                        

                                        $fechaActualCom =  strtotime(date("d-m-Y"));
                                        $fechaBautizoCom = strtotime($fechaBautizo);
                                        $fechaInscripcionCom = strtotime($fechaInscripcion);
                                        $fechaInscripcionEclesiasticoCom = strtotime($fechaInscripcionEclesiastico);
                                        if($fechaBautizoCom > $fechaActualCom){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE BAUTIZO NO DEBE SER MAYOR A LA FECHA ACTUAL</div>';                        
                                        }else if($fechaInscripcionCom > $fechaActualCom){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCIÓN NO DEBE SER MAYOR A LA FECHA ACTUAL</div>';                        
                                        }else if($fechaInscripcionEclesiastico > $fechaActualCom){
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCIÓN NO DEBE SER MAYOR A LA FECHA ACTUAL</div>';                        
                                        }else{
                                            $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                                            $idPadre = $listaPadre[0]['idPersona'];
                                            $idMadre = $listaMadre[0]['idPersona'];
                                            $idPadrino = $listaPadrino[0]['idPersona'];
                                            $idMadrina = $listaMadrina[0]['idPersona'];
                                            
                                            
                                            if($idPersona == $idPadre || $idPersona == $idMadre){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL BAUTIZADO NO DEBE SER LA MISMA PERSONA QUE LOS PADRES</div>';                        
                                            }else if($idPersona == $idPadrino || $idPersona == $idMadrina){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL BAUTIZADO NO DEBE SER LA MISMA PERSONA QUE LOS PADRINOS</div>';                        
                                            }else if($idPadre == $idPadrino || $idPadre == $idMadrina){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">EL PADRE NO DEBE SER LA MISMA PERSONA QUE LOS PADRINOS</div>';                        
                                            }else if($idMadre == $idPadrino || $idMadre == $idMadrina){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA MADRE NO DEBE SER LA MISMA PERSONA QUE LOS PADRINOS</div>';                        
                                            }else if($idPadre == $idMadre){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LOS PADRES NO DEBEN SER LA MISMA PERSONA</div>';                        
                                            }else if($idPadrino == $idMadrina){
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LOS PADRINOS NO DEBEN SER LA MISMA PERSONA</div>';                        
                                            }else{
                                                $listaPersona = $objPersona->FiltrarPersona($idPersona);
                                                if(count($listaPersona) == 0){
                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA NO EXISTE EN LA BASE DE DATOS</div>';
                                                }else{
                                                    $fechaInscripcionCom = strtotime($fechaInscripcion);
                                                    $fechaInscripcionEclesiasticoCom = strtotime($fechaInscripcionEclesiastico);
                                                    $fechaNacimientoCom = strtotime($listaPersona[0]['fechaNacimiento']);
                                                    if($fechaNacimientoCom > $fechaInscripcionCom){
                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCION DEL REGISTRO CIVIL NO DEBE SER MENOR A LA DE NACIMIENTO</div>';
                                                    }else if($fechaNacimientoCom > $fechaInscripcionEclesiasticoCom){
                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCION DEL REGISTRO ECLESIÁSTICO NO DEBE SER MENOR A LA DE NACIMIENTO</div>';
                                                    }else if($fechaNacimientoCom > $fechaBautizoCom){
                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DEL BAUTIZO NO DEBE SER MENOR A LA DE NACIMIENTO</div>';
                                                    }else{
                                                        if(count ($objBautismo->FiltrarBautismoPorPersona($idPersona)) > 0 ){
                                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA YA CUENTA CON UN BAUTIZO POR FAVOR RECARGUE LA PÁGINA</div>';
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
                                                                    $idIglesia = $sesionUsuario->offsetGet('idIglesia');
                                                                    $hoy = getdate();
                                                                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                                                    $resultado = $objBautismo->IngresarBautismo($idPersona, $idSacerdote, $idConfigurarParroquiaCanton, $idIglesia,$idLugar, $numero, $fechaBautizo, $anoRegistroCivil, $tomo, $folio, $acta,$anoEclesiastico,$tomoEclesiastico,$folioEclesiastico,$actaEclesiastico,$fechaInscripcionEclesiastico, $fechaInscripcion, $fechaSubida, 0);
                                                                    if(count($resultado) == 0){
                                                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL BAUTIZO POR FAVOR INTENTE MÁS TARDE</div>';
                                                                    }else{ 

                                                                        $idBautismo = $resultado[0]['idBautismo'];
                                                                        $listaTipoPadre = $objTipoPadre->FiltrarTipoPadrePorIdentificador(1);
                                                                        $listaTipoMadre = $objTipoPadre->FiltrarTipoPadrePorIdentificador(2);
                                                                        $idTipoPadre = $listaTipoPadre[0]['idTipoPadre'];
                                                                        $idTipoMadre = $listaTipoMadre[0]['idTipoPadre'];

                                                                        $resultadoPadre = $objPadresBautismo->IngresarPadresBautismo($idTipoPadre, $idPadre, $idBautismo, $fechaSubida, 1);
                                                                        $resultadoMadre = $objPadresBautismo->IngresarPadresBautismo($idTipoMadre, $idMadre, $idBautismo, $fechaSubida, 1);

                                                                        $resultadoPadrino = $objPadrinosBautismo->IngresarPadrinosBautismo($idTipoPadre, $idPadrino, $idBautismo, $fechaSubida, 1);
                                                                        $resultadoMadrina = $objPadrinosBautismo->IngresarPadrinosBautismo($idTipoMadre, $idMadrina, $idBautismo, $fechaSubida, 1);

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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 15);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 15, 3);
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
                        $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                        $objPadresBautismo = new PadresBautismo($this->dbAdapter);
                        $objPadrinosBautismo = new PadrinosBautismo($this->dbAdapter);
                        $objLugaresMisa = new LugaresMisa($this->dbAdapter);
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
                                $tabla = '';
                                $idPersona = $listaPersona[0]['idPersona'];
                                $listaBautismo = $objBautismo->FiltrarBautismoPorPersona($idPersona);
                                if(count($listaBautismo) == 0){
                                    $identificacion = 'SIN IDENTIFICACIÓN';
                                    if($listaPersona[0]['identificacion'] != NULL){
                                        $identificacion = $listaPersona[0]['identificacion'] ;
                                    }
                                    
                                    $idPersonaEncriptado = $objMetodos->encriptar($listaPersona[0]['idPersona']);
                                    $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarBautismo" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
                                    $botonCancelar = '<button id="btnCancelar" onclick="limpiarFormularioBautismo();" type="button" class="btn btn-danger pull-right"><i class="fa fa-times"></i>CANCELAR</button>';

                                    $listaSacerdote = $objSacerdotes->ObtenerSacerdotesEstado(1); 
                                    $optionSelectSacerdote = '<option value="0">SELECCIONE UN SACERDOTE</option>';
                                    foreach ($listaSacerdote as $valueSacerdote) {
                                        $idSacerdoteEncriptado = $objMetodos->encriptar($valueSacerdote['idSacerdote']);
                                        $listaPersona = $objPersona->FiltrarPersona($valueSacerdote['idPersona']);
                                        $nombres = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'].' '.$listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];

                                        $optionSelectSacerdote =$optionSelectSacerdote.'<option value="'.$idSacerdoteEncriptado.'">'.$nombres.'</option>';
                                    }
                                    $listaProvincias = $objProvincias->ObtenerProvinciasEstado(1);
                                    $optionSelectProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                                    foreach ($listaProvincias as $valueProvincias) {
                                        $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                                        $optionSelectProvincias = $optionSelectProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                                    }
//                                    $listaPersonasPM = $objPersona->ObtenerPersonas();
//                                    $optionHombres = '';
//                                    $optionMujeres = '';
//                                    foreach ($listaPersonasPM as $valuePM){
//                                        $listaSexo = $objSexo->FiltrarSexo($valuePM['idSexo']);
//                                        if($listaSexo[0]['identificadorSexo'] == 1){
//                                            $optionHombres = $optionHombres.'<option value="'.$valuePM['primerApellido'].' '.$valuePM['segundoApellido'].' '.$valuePM['primerNombre'].' '.$valuePM['segundoNombre'].'"></option>';
//                                        }else{
//                                            $optionMujeres = $optionMujeres.'<option value="'.$valuePM['primerApellido'].' '.$valuePM['segundoApellido'].' '.$valuePM['primerNombre'].' '.$valuePM['segundoNombre'].'"></option>';
//                                        }
//                                    }
                                    
                                    $listaLugares = $objLugaresMisa->ObtenerObtenerLugaresMisa();
                                    $optionLugares  = '<option value="0">SELECCIONE UNA IGLESIA</option>';
                                    foreach ( $listaLugares as $valuesLugares  ){
                                        $idLugarEncriptado = $objMetodos->encriptar($valuesLugares['idLugarMisa']);
                                        $optionLugares  = $optionLugares.'<option value="'.$idLugarEncriptado.'">'.$valuesLugares['nombreLugar'].'</option>';
                                    }
                                    
                                    $selectLugares = '<label for="lugarBautizo">SELECCIONE LA IGLESIA</label><select class="form-control" id="lugarBautizo" name="lugarBautizo">'.$optionLugares.'</select>';
                                    
                                    
//                                    $selectPadre = '<div class="form-group col-lg-6">
//                                        <label for="nombresPadre">APELLIDOS Y NOMBRES DEL PADRE</label>
//                                        <input list="buscadoPadre" id="nombresPadre" autofocus="" autocomplete="off" name="nombresPadre" type="text" class="form-control" placeholder="Buscar">
//                                        <datalist id="buscadoPadre">
//                                            '.$optionHombres.'
//                                        </datalist>
//                                    </div> 
//                                    <div class="form-group col-lg-6">
//                                        <label for="fechaNacimientoPadre">FECHA DE NACIMIENTO DEL PADRE</label>
//                                        <input type="date" id="fechaNacimientoPadre" name="fechaNacimientoPadre" class="form-control" >
//                                    </div>';
//                                    
//                                    $selectMadre = '<div class="form-group col-lg-6">
//                                        <label for="nombresMadre">APELLIDOS Y NOMBRES DE LA MADRE</label>
//                                        <input list="buscadoMadre" id="nombresMadre" autocomplete="off" name="nombresMadre" type="text" class="form-control" placeholder="Buscar">
//                                        <datalist id="buscadoMadre">
//                                            '.$optionMujeres.'
//                                        </datalist>
//                                    </div> 
//                                    <div class="form-group col-lg-6">
//                                        <label for="fechaNacimientoMadre">FECHA DE NACIMIENTO DE LA MADRE</label>
//                                        <input type="date" id="fechaNacimientoMadre" name="fechaNacimientoMadre" class="form-control" >
//                                    </div>';
//                                    $selectPadrino = '<div class="form-group col-lg-6">
//                                        <label for="nombresPadrino">APELLIDOS Y NOMBRES DEL PADRINO</label>
//                                        <input list="buscadoPadrino" id="nombresPadrino" autocomplete="off" name="nombresPadrino" type="text" class="form-control" placeholder="Buscar">
//                                        <datalist id="buscadoPadrino">
//                                            '.$optionHombres.'
//                                        </datalist>
//                                    </div> 
//                                    <div class="form-group col-lg-6">
//                                        <label for="fechaNacimientoPadrino">FECHA DE NACIMIENTO DEL PADRINO</label>
//                                        <input type="date" id="fechaNacimientoPadrino" name="fechaNacimientoPadrino" class="form-control" >
//                                    </div>';
//                                    $selectMadrina = '<div class="form-group col-lg-6">
//                                            <label for="nombresMadrina">APELLIDOS Y NOMBRES DE LA MADRINA</label>
//                                            <input list="buscadoMadrina" id="nombresMadrina" autocomplete="off" name="nombresMadrina" type="text" class="form-control" placeholder="Buscar">
//                                            <datalist id="buscadoMadrina">
//                                                '.$optionMujeres.'
//                                            </datalist>
//                                        </div> 
//                                        <div class="form-group col-lg-6">
//                                            <label for="fechaNacimientoMadrina">FECHA DE NACIMIENTO DE LA MADRINA</label>
//                                            <input type="date" id="fechaNacimientoMadrina" name="fechaNacimientoMadrina" class="form-control" >
//                                        </div>';
                                    
                                    
                                    
                                    $padresPadrinos = '<h4 class="text-center">DATOS DE LOS PADRES</h4>
                                                    <div class="col-lg-6 form-group">
                                                        <label for="identificacionPadre">IDENTIFICACIÓN DEL PADRE</label>
                                                        <input onkeyup="filtrarPersonaPorIdentificacion(event,\'identificacionPadre\',\'contenedorDatosPadre\');" onkeydown="validarNumeros(\'identificacionPadre\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacionPadre" name="identificacionPadre" class="form-control">
                                                        <div id="contenedorDatosPadre"></div>
                                                    </div>
                                                    <div class="col-lg-6 form-group">
                                                        <label for="identificacionMadre">IDENTIFICACIÓN DE LA MADRE</label>
                                                        <input <input onkeyup="filtrarPersonaPorIdentificacion(event,\'identificacionMadre\',\'contenedorDatosMadre\');" onkeydown="validarNumeros(\'identificacionMadre\')" maxlength="10" autocomplete="off" autofocus="" type="text" id="identificacionMadre" name="identificacionMadre" class="form-control">
                                                        <div id="contenedorDatosMadre"></div>
                                                        
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
                                    
                                    $tabla = '
                                        
                                            <div class="form-group col-lg-12">'.$padresPadrinos.'</div>
                                  


                                        <div class="form-group col-lg-3">
                                            <h4 class="text-center">DATOS DEL BAUTIZO</h4>
                                            <input type="hidden" value="'.$idPersonaEncriptado.'" name="idPersonaEncriptado" id="idPersonaEncriptado">
                                            <label for="numero">NÚMERO</label>
                                            
                                            <input onkeydown="validarNumeros(\'numero\');" maxlength="10" autocomplete="off"  type="text" id="numero" name="numero" class="form-control">
                                            <label for="sacerdote">SACERDOTE</label>
                                            <select class="form-control" id="selectSacerdote" name="selectSacerdote">
                                                '.$optionSelectSacerdote.'
                                            </select>
                                            <label for="fechaBautizo">FECHA BAUTIZO</label>
                                            <input type="date" class="form-control" id="fechaBautizo" name="fechaBautizo">
                                            '.$selectLugares.'
                                        </div>
                                        
                                        <div class="col-lg-3 form-group">
                                            <h4 class="text-center">LUGAR DE NACIMIENTO</h4>
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
                                            <h4 class="text-center">REGISTRO CIVIL</h4>
                                            <label for="ano">AÑO</label>
                                            <input onkeydown="validarNumeros(\'ano\');" maxlength="10" autocomplete="off"  type="text" id="ano" name="ano" class="form-control">
                                            <label for="tomo">TOMO</label>
                                            <input maxlength="10" autocomplete="off"  type="text" id="tomo" name="tomo" class="form-control">
                                            <label for="folio">FOLIO</label>
                                            <input maxlength="10" autocomplete="off" type="text" id="folio" name="folio" class="form-control">
                                            <label for="acta">ACTA</label>
                                            <input maxlength="10" autocomplete="off"  type="text" id="acta" name="acta" class="form-control">
                                            <label for="fechaInscripcion">FECHA DE INSCRIPCIÓN</label>
                                            <input type="date" class="form-control" id="fechaInscripcion" name="fechaInscripcion">
                                        </div>
                                        <div class="form-group col-lg-3">
                                            <h4 class="text-center">REGISTRO ECLESIÁSTICO</h4>
                                            <label for="anoEclesiastico">AÑO</label>
                                            <input onkeydown="validarNumeros(\'anoEclesiastico\');" maxlength="10" autocomplete="off"  type="text" id="anoEclesiastico" name="anoEclesiastico" class="form-control">
                                            <label for="tomoEclesiastico">TOMO</label>
                                            <input maxlength="10" autocomplete="off"  type="text" id="tomoEclesiastico" name="tomoEclesiastico" class="form-control">
                                            <label for="folioEclesiastico">FOLIO</label>
                                            <input maxlength="10" autocomplete="off" type="text" id="folioEclesiastico" name="folioEclesiastico" class="form-control">
                                            <label for="actaEclesiastico">ACTA</label>
                                            <input maxlength="10" autocomplete="off"  type="text" id="actaEclesiastico" name="actaEclesiastico" class="form-control">
                                            <label for="fechaInscripcionEclesiastico">FECHA DE INSCRIPCIÓN</label>
                                            <input type="date" class="form-control" id="fechaInscripcionEclesiastico" name="fechaInscripcionEclesiastico">
                                        </div>
                                        <div class="form-group col-lg-12">
                                            '.$botonCancelar.' '.$botonGuardar.'
                                        </div>';
                                    $mensaje = '';
                                    $validar = TRUE;
                                }else{
                                    $objAdministrativo = new Administrativos($this->dbAdapter);
                                    $listaAdministrativo = $objAdministrativo->FiltrarAdministrativosPorIdentificadorCargo(1);
                                    if(count($listaAdministrativo) != 1){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UN PÁRROCO QUE FIRME EL DOCUMENTO POR FAVOR DIRÍGETE AL MENÚ <b>TALENTO HUMANO->ADMINISTRATIVOS</b>Y AGREGA UN PÁRROCO</div>';
                                    }else{
                                        $nombres = $listaBautismo[0]['primerApellido'].' '.$listaBautismo[0]['segundoApellido'].' '.$listaBautismo[0]['primerNombre'].' '.$listaBautismo[0]['segundoNombre'];

                                        $nombreIglesia = $sesionUsuario->offsetGet('nombreIglesia');

                                        $listaLugar = $objLugaresMisa->FiltrarLugaresMisa($listaBautismo[0]['idLugar']);
                                        $nombreIglesia2 = $listaLugar[0]['nombreLugar'];

                                        $direccionIglesia = $sesionUsuario->offsetGet('direccionIgleisia');
                                        $fechaBautismo = $objMetodos->obtenerFechaEnLetraSinHora($listaBautismo[0]['fechaBautizo']);
                                        $listaSacerdote = $objSacerdotes->FiltrarSacerdote($listaBautismo[0]['idSacerdote']);
                                        $listaPersonaSacerdote = $objPersona->FiltrarPersona($listaSacerdote[0]['idPersona']);
                                        $nombresSacerdote = $listaPersonaSacerdote[0]['primerApellido'].' '.$listaPersonaSacerdote[0]['segundoApellido'].' '.$listaPersonaSacerdote[0]['primerNombre'].' '.$listaPersonaSacerdote[0]['segundoNombre'];
                                        $listaDireccion = $objConfigurarParroquiaCanton->FitrarDireccionesPorConfigurarParroquiaCanton($listaBautismo[0]['idConfigurarParroquiaCanton']);
                                        $direccionNacimiento = $listaDireccion[0]['nombreParroquia'].' - '.$listaDireccion[0]['nombreCanton'].' - '.$listaDireccion[0]['nombreProvincia'];
                                        $fechaNacimiento = $objMetodos->obtenerFechaEnLetraSinHora($fechaNacimiento);
                                        $listaPadres = $objPadresBautismo->FiltrarPadreBautismoPorBautismo($listaBautismo[0]['idBautismo']);
                                        $padre = '';
                                        $madre = '';
                                        foreach ($listaPadres as $valuePadres) {
                                            if($valuePadres['identificadorTipoPadre'] == 1){
                                                $padre = $valuePadres['primerApellido'].' '.$valuePadres['segundoApellido'].' '.$valuePadres['primerNombre'].' '.$valuePadres['segundoNombre'];
                                            }else{
                                                $madre = $valuePadres['primerApellido'].' '.$valuePadres['segundoApellido'].' '.$valuePadres['primerNombre'].' '.$valuePadres['segundoNombre'];
                                            }
                                        }
                                        $listaPadrinos = $objPadrinosBautismo->FiltrarPadrinosBautismoPorBautismo($listaBautismo[0]['idBautismo']);
                                        $padrino = '';
                                        $madrina = '';
                                        foreach ($listaPadrinos as $valuePadrinos) {
                                            if($valuePadrinos['identificadorTipoPadre'] == 1){
                                                $padrino = $valuePadrinos['primerApellido'].' '.$valuePadrinos['segundoApellido'].' '.$valuePadrinos['primerNombre'].' '.$valuePadrinos['segundoNombre'];
                                            }else{
                                                $madrina = $valuePadrinos['primerApellido'].' '.$valuePadrinos['segundoApellido'].' '.$valuePadrinos['primerNombre'].' '.$valuePadrinos['segundoNombre'];
                                            }
                                        }
                                        $tablaDerecha = '<p class="text-justify" style="line-height: 30px;font-size:15px">En la Iglesia Parroquial de <b>'.$nombreIglesia2.'</b> el <b>'.$fechaBautismo.'</b> el 
                                            Padre <b>'.$nombresSacerdote.'</b> bautizó solemnemente a: <b>'.$nombres.'</b> nacido(a) en <b>'.$direccionNacimiento.'</b> el 
                                                <b>'.$fechaNacimiento.'</b> hijo(a) legítimo de <b>'.$padre.'</b> y de <b>'.$madre.'.</b>
                                                </p>
                                                <p class="text-justify" style="line-height: 30px;font-size:15px"> Fueron sus Padrinos: <b>'.$padrino.'</b> y <b>'.$madrina.'.</b>
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
                                                                <h3>CERTIFICADO DE BAUTIZO</h3>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                </table>';
                                        $tablaIzquierda = '<table border="1" class="table text-center" style="width:100%" > 
                                                    <thead>
                                                        <tr> 
                                                            <th colspan="2">N°</th>
                                                            <th colspan="2"> '.$listaBautismo[0]['numero'].'</th>
                                                        </tr> 
                                                        <tr> 
                                                            <th colspan="4" >NOMBRE</th>
                                                        </tr>
                                                        <tr> 
                                                            <th colspan="4" >'.$nombres.'</th>
                                                        </tr>
                                                        <tr> 
                                                            <th colspan="2">REGISTRO CIVIL</th>
                                                            <th colspan="2">REGISTRO ECLESIÁSTICO</th>
                                                        </tr>
                                                        <tr> 
                                                            <th><b>AÑO</b> '.$listaBautismo[0]['anoRegistroCivil'].'</th>
                                                            <th><b>TOMO</b> '.$listaBautismo[0]['tomo'].'</th>
                                                            <th><b>AÑO</b> '.$listaBautismo[0]['anoEclesiastico'].'</th>
                                                            <th><b>TOMO</b> '.$listaBautismo[0]['tomoEclesiastico'].'</th>

                                                        </tr>
                                                         <tr> 
                                                            <th><b>FOLIO</b> '.$listaBautismo[0]['folio'].'</th>
                                                            <th><b>ACTA</b> '.$listaBautismo[0]['acta'].'</th> 
                                                            <th><b>FOLIO</b> '.$listaBautismo[0]['folioEclesiastico'].'</th>
                                                            <th><b>ACTA</b> '.$listaBautismo[0]['actaEclesiastico'].'</th> 
                                                        </tr>
                                                        <tr> 
                                                            <th>FECHA INSCRIPCIÓN</th>
                                                            <th> '.$listaBautismo[0]['fechaInscripcion'].'</th>
                                                            <th>FECHA INSCRIPCIÓN</th>
                                                            <th> '.$listaBautismo[0]['fechaInscripcionEclesiastico'].'</th>
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
                                }
                                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                            }
                        }
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 15);
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 15);
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 15);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 15, 3);
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
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    

}