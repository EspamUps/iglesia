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
use Nel\Modelo\Entity\PadresBautismo;
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
        $validarprivilegioEliminar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 15, 1);
//        $validarprivilegioModificar = $objMetodosC->ValidarPrivilegioAction($adaptador,$idUsuario, 15, 2);
        $array1 = array();
        foreach ($listaBautismo as $value) {
            $idBautismoEncriptado = $objMetodos->encriptar($value['idBautismo']);
            $nombres = $value['primerApellido'].' '.$value['segundoApellido'].' '.$value['primerNombre'].' '.$value['segundoNombre'];
            $nombresPersona = '<input type="hidden" id="estadoBautismoA'.$i.'" name="estadoBautismoA'.$i.'" value="'.$value['estadoBautismo'].'">'.$nombres;
            $fechaIngreso = $objMetodos->obtenerFechaEnLetra($value['fechaRegistro']);

            $botonEliminarBautismo = '';
            $botonDeshabilitarBautismo = '';
            if($validarprivilegioEliminar == TRUE){
//                if(count($objConfigurarCurso->FiltrarConfigurarCursoPorCursoLimit1($value['idCurso'])) == 0)
                if($value['estadoBautismo'] == 0){    
                    $botonEliminarBautismo = '<button id="btnEliminarBautismo'.$i.'" title="ELIMINAR '.$nombres.'" onclick="eliminarBautismo(\''.$idBautismoEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                    $botonDeshabilitarBautismo = '<button id="btnHabilitarBautismo'.$i.'" title="HABILITAR '.$nombres.'" onclick="habilitarBautismo(\''.$idBautismoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
                }
                
            }

//            if($validarprivilegioModificar == TRUE){
//                if($value['estadoCurso'] == TRUE)
//                    $botonDeshabilitarCurso = '<button id="btnDeshabilitarCurso'.$i.'" title="DESHABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarCurso(\''.$idCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-success btn-sm btn-flat"><i class="fa  fa-plus-square"></i></button>';
//                else
//                    $botonDeshabilitarCurso = '<button id="btnDeshabilitarCurso'.$i.'" title="HABILITAR '.$value['nombreCurso'].'" onclick="deshabilitarCurso(\''.$idCursoEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-minus-square"></i></button>';
//            }
            $botones =  $botonDeshabilitarBautismo.' '.$botonEliminarBautismo;     
            $array1[$i] = array(
                '_j'=>$j,
                'nombresPersona'=>$nombresPersona,
                'fechaIngreso'=>$fechaIngreso,
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
                        $objPersona = new Persona($this->dbAdapter);
                        $objSacerdote = new Sacerdotes($this->dbAdapter);
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
                        $anoRegistroCivil = trim($post['ano']);
                        $tomo = strtoupper(trim($post['tomo']));
                        $folio = strtoupper(trim($post['folio']));
                        $acta = strtoupper(trim($post['acta']));
                        $fechaInscripcion = $post['fechaInscripcion'];
                        if(empty ($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                        }else if(!is_numeric($numero)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL  NÚMERO</div>';
                        }else if(empty ($idSacerdoteEncriptado) || $idSacerdoteEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL SACERDOTE</div>';
                        }else if(empty ($fechaBautizo)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DEL BAUTIZO</div>';
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
                        }else if(count ($objBautismo->FiltrarBautismoPorNumero($numero)) > 0 ){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN BAUTIZO CON EL NÚMERO '.$numero.'</div>';
                        }else {
                            ini_set('date.timezone','America/Bogota'); 
                            $fechaActualCom =  strtotime(date("d-m-Y"));
                            $fechaBautizoCom = strtotime($fechaBautizo);
                            $fechaInscripcionCom = strtotime($fechaInscripcion);
                            if($fechaBautizoCom > $fechaActualCom){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE BAUTIZO NO DEBE SER MAYOR A LA FECHA ACTUAL</div>';                        
                            }else if($fechaInscripcionCom > $fechaActualCom){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE INSCRIPCIÓN NO DEBE SER MAYOR A LA FECHA ACTUAL</div>';                        
                            }else{
                                $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                                $listaPersona = $objPersona->FiltrarPersona($idPersona);
                                if(count($listaPersona) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA NO EXISTE EN LA BASE DE DATOS</div>';
                                }else{
                                    if(count ($objBautismo->FiltrarBautismoPorPersona($idPersona)) > 0 ){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA PERSONA SELECCIONADA YA CUENTA CON UN BAUTIZO POR FAVOR RECARGUE LA PÁGINA</div>';
                                    }else {
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
                                                $resultado = $objBautismo->IngresarBautismo($idPersona, $idSacerdote, $idConfigurarParroquiaCanton, $idIglesia, $numero, $fechaBautizo, $anoRegistroCivil, $tomo, $folio, $acta, $fechaInscripcion, $fechaSubida, 0);
                                                if(count($resultado) == 0){
                                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL CURSO POR FAVOR INTENTE MÁS TARDE</div>';
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
                        $objPadresBautismo = new PadresBautismo($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                         $primerApellido = strtoupper(trim($post['primerApellido']));
                         $segundoApellido = strtoupper(trim($post['segundoApellido']));
                         $primerNombre = strtoupper(trim($post['primerNombre']));
                         $segundoNombre = strtoupper(trim($post['segundoNombre']));
                         $fechaNacimiento = $post['fechaNacimiento'];
                        if(empty($primerApellido) || strlen($primerApellido) > 80){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL PRIMER APELLIDO MÁXIMO 80 CARACTERES</div>';
                        }else if(empty ($segundoApellido) ||  strlen($segundoApellido) > 80){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO APELLIDO MÁXIMO 80 CARACTERES</div>';
                        }else if(empty ($primerNombre) || strlen($primerNombre) > 80){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL PRIMER NOMBRE MÁXIMO 80 CARACTERES</div>';
                        }else if(empty ($segundoNombre) ||  strlen($segundoNombre) > 80){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL SEGUNDO NOMBRE MÁXIMO 80 CARACTERES</div>';
                        }else if(empty ($fechaNacimiento)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE NACIMIENTO</div>';
                        }else{
                            $listaPersona = $objPersona->FiltrarPersonaPorApellidosNombres($primerApellido,$segundoApellido,$primerNombre,$segundoNombre,$fechaNacimiento);
                            if(count($listaPersona) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA '.$primerApellido.' '.$segundoApellido.' '.$primerNombre.' '.$segundoNombre.' POR FAVOR DIRÍJASE AL MÓDULO DE PERSONAS Y REGISTRELA</div>';
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
                                    $tabla = '<div class="form-group col-lg-4">
                                            <h4 class="text-center">DATOS DEL BAUTIZO</h4>
                                            <input type="hidden" value="'.$idPersonaEncriptado.'" name="idPersonaEncriptado" id="idPersonaEncriptado">
                                            <label for="numero">NÚMERO</label>
                                            
                                            <input onkeydown="validarNumeros(\'numero\');" maxlength="10" autocomplete="off" autofocus="" type="text" id="numero" name="numero" class="form-control">
                                            <label for="sacerdote">SACERDOTE</label>
                                            <select class="form-control" id="selectSacerdote" name="selectSacerdote">
                                                '.$optionSelectSacerdote.'
                                            </select>
                                            <label for="fechaBautizo">FECHA BAUTIZO</label>
                                            <input type="date" class="form-control" id="fechaBautizo" name="fechaBautizo">
                                        </div>
                                        <div class="col-lg-4">
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
                                        <div class="form-group col-lg-4">
                                            <h4 class="text-center">REGISTRO CIVIL</h4>
                                            <label for="ano">AÑO</label>
                                            <input onkeydown="validarNumeros(\'ano\');" maxlength="10" autocomplete="off" autofocus="" type="text" id="ano" name="ano" class="form-control">
                                            <label for="tomo">TOMO</label>
                                            <input maxlength="10" autocomplete="off" autofocus="" type="text" id="tomo" name="tomo" class="form-control">
                                            <label for="folio">FOLIO</label>
                                            <input maxlength="10" autocomplete="off" autofocus="" type="text" id="folio" name="folio" class="form-control">
                                            <label for="acta">ACTA</label>
                                            <input maxlength="10" autocomplete="off" autofocus="" type="text" id="acta" name="acta" class="form-control">
                                            <label for="fechaInscripcion">FECHA DE INSCRIPCIÓN</label>
                                            <input type="date" class="form-control" id="fechaInscripcion" name="fechaInscripcion">

                                        </div>
                                        <div class="form-group col-lg-12">
                                            '.$botonCancelar.' '.$botonGuardar.'
                                        </div>';
                                    $mensaje = '';
                                    $validar = TRUE;
                                }else{
                                    $nombres = $listaBautismo[0]['primerApellido'].' '.$listaBautismo[0]['segundoApellido'].' '.$listaBautismo[0]['primerNombre'].' '.$listaBautismo[0]['segundoNombre'];
                                    $tablaIzquierda = '<div class="table-responsive">
                                            <table class="table"> 
                                                <tbody>
                                                    <tr> 
                                                        <th>N°</th>
                                                        <td>'.$listaBautismo[0]['numero'].'</td>
                                                    </tr> 
                                                    <tr> 
                                                        <th colspan="2">NOMBRE</th>
                                                    </tr>
                                                    <tr> 
                                                        <td colspan="2">'.$nombres.'</td>
                                                    </tr>
                                                    <tr> 
                                                        <th colspan="2">REGISTRO CIVIL</th>
                                                    </tr>
                                                    <tr> 
                                                        <td><b>AÑO</b> '.$listaBautismo[0]['anoRegistroCivil'].'</td>
                                                        <td><b>TOMO</b> '.$listaBautismo[0]['tomo'].'</td>
                                                    </tr>
                                                     <tr> 
                                                        <td><b>FOLIO</b> '.$listaBautismo[0]['folio'].'</td>
                                                        <td><b>ACTA</b> '.$listaBautismo[0]['acta'].'</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            </div>';
                                            
                                    $tabla = '<div class="col-lg-4">'.$tablaIzquierda.'</div>';
                                    
                                    $mensaje = '';

//                                    $tabla = '<div class="alert alert-warning text-center" role="alert">ESTA PERSONA YA TIENE UN BAUTIZO AGREGADO POR FAVOR BÚSCALO(A) EN LA TABLA DE ABAJO</div>';
                                    $validar = TRUE;
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 3);
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

}