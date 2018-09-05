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
use Nel\Metodos\Correo;
use Nel\Modelo\Entity\Provincias;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\ConfigurarCantonProvincia;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Nel\Modelo\Entity\DireccionPersona;
use Nel\Modelo\Entity\Cantones;
use Nel\Modelo\Entity\Parroquias;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class ParroquiasController extends AbstractActionController
{
    public $dbAdapter;
    public function ingresarparroquiaAction()
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
                $objParroquias = new Parroquias($this->dbAdapter);
                $objProvincias = new Provincias($this->dbAdapter);
                $objConfigurarCantonProvincia = new ConfigurarCantonProvincia($this->dbAdapter);
                $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                $objMetodos = new Metodos();
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $idProvinciaEncriptado = $post['selectProvinciasParroquias'];
                $idConfigurarCantonProvinciaEncriptado = $post['selectCantonesParroquias'];
                $nombreParroquia = trim(strtoupper($post['nombreParroquia']));
                if(empty($idProvinciaEncriptado) || $idProvinciaEncriptado == NULL || $idProvinciaEncriptado=="0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVÍNCIA</div>';
                }else if(empty($idConfigurarCantonProvinciaEncriptado) || $idConfigurarCantonProvinciaEncriptado == NULL || $idConfigurarCantonProvinciaEncriptado=="0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN CANTÓN</div>';
                }else if(empty($nombreParroquia) || strlen($nombreParroquia) > 100){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DE LA PARRÓQUIA MÁXIMO 100 CARACTERES</div>';
                }else {
                    $idProvincia = $objMetodos->desencriptar($idProvinciaEncriptado);
                    if(count($objProvincias->FiltrarProvincia($idProvincia)) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PROVINCIA SELECCIONADA</div>';
                    }else{
                        $idConfigurarCantonProvincia = $objMetodos->desencriptar($idConfigurarCantonProvinciaEncriptado);
                        if(count($objConfigurarCantonProvincia->FiltrarConfigurarCantonProvincia($idConfigurarCantonProvincia)) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE EL CANTÓN SELECCIONADA</div>';
                        }else{
                        
                        
                            $listaParroquias = $objParroquias->FiltrarParroquiaPorNombreParroquia($nombreParroquia);
                            $idParroquia = 0;
                            $validarParroquiaExiste = TRUE;
                            if(count($listaParroquias) > 0){
                                $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvinciaParroquia($idConfigurarCantonProvincia, $listaParroquias[0]['idParroquia']);
                                if(count($listaConfigurarParroquiaCanton) == 0){
                                    $validarParroquiaExiste = FALSE;
                                    $idParroquia = $listaParroquias[0]['idParroquia'];
                                }
                            }else{
                                ini_set('date.timezone','America/Bogota'); 
                                $hoy = getdate();
                                $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                $resultadoParroquia = $objParroquias->IngresarParroquia($nombreParroquia, $fechaSubida, 1);
                                if(count($resultadoParroquia) > 0){
                                    $idParroquia = $resultadoParroquia[0]['idParroquia'];
                                }
                            }
                            if($idParroquia == 0){
                                if($validarParroquiaExiste == TRUE){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA PARRÓQUIA LLAMADA '.$nombreParroquia.'</div>';
                                }else{
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PARRÓQUIA POR FAVOR INTENTE MÁS TARDE</div>';
                                }
                            }else{
                                $resultado = $objConfigurarParroquiaCanton->IngresarConfigurarParroquiaCanton($idConfigurarCantonProvincia, $idParroquia, 1);
                                if(count($resultado) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PARRÓQUIA POR FAVOR INTENTE MÁS TARDE</div>';
                                }else{
                                    $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADA CORRECTAMENTE</div>';
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 3);
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
                        $idConfigurarCantonProvincia = $objMetodos->encriptar($valueConfigurarCantonProvincia['idConfigurarCantonProvincia']);
                        $optionCantones = $optionCantones.'<option value="'.$idConfigurarCantonProvincia.'">'.$listaCantones[0]['nombreCanton'].'</option>';
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
    
    
    public function obtenerformparroquiasAction()
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
                $objProvincias = new Provincias($this->dbAdapter);
                $objMetodos = new Metodos();
                $listaProvincias = $objProvincias->ObtenerProvincias();
                $optionProvincias = '<option value="0">SELECCIONE UNA PROVINCIA</option>';
                foreach ($listaProvincias as $valueProvincias) {
                    $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                    $optionProvincias = $optionProvincias.'<option value="'.$idProvinciaEncriptado.'">'.$valueProvincias['nombreProvincia'].'</option>';
                }  
                $selectProvincias = '<select onchange="filtrarSelectCantonesPorProvincia();" id="selectProvinciasParroquias" name="selectProvinciasParroquias" class="form-control">
                    '.$optionProvincias.'
                </select>';
                
                $formParroquias = '<div class="form-group col-lg-4">
                        <label>PROVINCIAS</label>
                        <div class=" margin">
                            '.$selectProvincias.'
                        </div>
                   </div>
                   <div class="form-group col-lg-4">
                        <label>CANTONES</label>
                        <div class=" margin">
                            <select onchange="cargandoParroquias(\'#contenedorTablaParroquias\');filtrarParroquiasPorCanton();" class="form-control" id="selectCantonesParroquias" name="selectCantonesParroquias">
                                <option value="0">SELECCIONE UN CANTÓN</option>
                            </select>
                        </div>
                   </div>
                    <div class="form-group col-lg-4">
                        <label for="nombreProvincia">NOMBRE DE LA PARRÓQUIA</label>
                        <div class="input-group margin">
                            <input autofocus  maxlength="100" type="text" id="nombreParroquia" name="nombreParroquia" class="form-control">
                            <span class="input-group-btn">
                                <button style="margin-left: 10%;" name="btnGuardarParroquia" id="btnGuardarParroquia" data-loading-text="GUARDANDO.." class="btn  btn-primary btn-flat"><i class="fa fa-save"></i>GUARDAR</button>
                            </span>
                        </div>
                  </div>';          
                $mensaje = '';
                $validar = TRUE;
                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'formParroquias'=>$formParroquias));
            }
        }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function filtrarparroquiasporcantonAction()
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
                $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                $objMetodos = new Metodos();
                
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                
                $idConfigurarCantonProvinciaEncriptado = $post['id'];
                if(empty($idConfigurarCantonProvinciaEncriptado) || $idConfigurarCantonProvinciaEncriptado == NULL){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CANTÓN</div>';
                }else{
                    
                    $idConfigurarCantonProvincia = $objMetodos->desencriptar($idConfigurarCantonProvinciaEncriptado);
                    $listaConfigurarParroquiaCanton = $objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvincia($idConfigurarCantonProvincia);
                    $array1 = array();
                    $i = 0;
                    $j = count($listaConfigurarParroquiaCanton);
                    foreach ($listaConfigurarParroquiaCanton as $valueConfigurarParroquiaCanton) {
                        
                        $idConfigurarParroquiaCantonEncriptado = $objMetodos->encriptar($valueConfigurarParroquiaCanton['idConfigurarParroquiaCanton']);
                        $botonEliminarParroquia = '';
                        if(count($objDireccionPersona->FiltrarDireccionPersonaPorConfigurarParroquiaCantonLimite1($valueConfigurarParroquiaCanton['idConfigurarParroquiaCanton'])) == 0){
                            $botonEliminarParroquia = '<button id="btnEliminarParroquia'.$i.'" title="ELIMINAR '.$valueConfigurarParroquiaCanton['nombreParroquia'].'" onclick="eliminarParroquia(\''.$idConfigurarParroquiaCantonEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                        }
                        $botones = $botonEliminarParroquia;
                        $array1[$i] = array(
                            '_j'=>$j,
                            'nombreParroquia'=>$valueConfigurarParroquiaCanton['nombreParroquia'],
                            'botones'=>$botones
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
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    public function eliminarparroquiaAction()
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
                $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                $objDireccionPersona = new DireccionPersona($this->dbAdapter);
                $objMetodos = new Metodos();
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $idConfigurarParroquiaCantonEncriptado = $post['id'];
                $numeroFila = $post['numeroFila'];
                if(empty($idConfigurarParroquiaCantonEncriptado) || $idConfigurarParroquiaCantonEncriptado == null){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CANTÓN</div>';
                }else  if(!is_numeric($numeroFila)){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA FILA</div>';
                }else{
                    
                    $idConfigurarParroquiaCanton = $objMetodos->desencriptar($idConfigurarParroquiaCantonEncriptado);
                    if(count($objDireccionPersona->FiltrarDireccionPersonaPorConfigurarParroquiaCantonLimite1($idConfigurarParroquiaCanton)) > 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE PUEDE ELIMINAR LA PARRÓQUIA PORQUE ALGUNAS PERSONAS TIENEN ESTA DIRECCIÓN ASIGNADA</div>';
                    }else{
                        
                        $resultado = $objConfigurarParroquiaCanton->EliminarConfigurarParroquiaCanton($idConfigurarParroquiaCanton);
                        if(count($resultado) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ LA PARRÓQUIA POR FAVOR INTENTE MÁS TARDE</div>';
                        }else{
                            $mensaje = '';
                            $validar = TRUE;
                            return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'numeroFila'=>$numeroFila));
                        }
                    }
                }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
////    

//    

}

