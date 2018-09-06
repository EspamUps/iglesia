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
use Nel\Modelo\Entity\Provincias;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\ConfigurarCantonProvincia;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Nel\Modelo\Entity\Cantones;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class CantonesController extends AbstractActionController
{
    public $dbAdapter;
    public function obtenerformcantonesAction()
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
                $selectProvincias = '<select onchange="cargandoCantones(\'#contenedorTablaCantones\');filtrarCantonesPorProvincia();" id="selectProvinciasCantones" name="selectProvinciasCantones" class="form-control">
                    '.$optionProvincias.'
                </select>';
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario,3 , 3);
                $formCantones ="";
        
                if($validarprivilegio==true)
                    {
                    $formCantones = '<div class="form-group col-lg-6">
                            <label>PROVINCIAS</label>
                            <div class=" margin">
                                '.$selectProvincias.'
                            </div>
                       </div>
                        <div class="form-group col-lg-6">
                            <label for="nombreProvincia">NOMBRE DEL CANTÓN</label>
                            <div class="input-group margin">
                                <input autofocus  maxlength="100" type="text" id="nombreCanton" name="nombreCanton" class="form-control">
                                <span class="input-group-btn">
                                    <button style="margin-left: 10%;" name="btnGuardarCanton" id="btnGuardarCanton" data-loading-text="GUARDANDO.." class="btn  btn-primary btn-flat"><i class="fa fa-save"></i>GUARDAR</button>
                                </span>
                            </div>
                      </div>';
                    }else{
                        $formCantones = '<label>PROVINCIAS</label>
                            <div class=" margin">
                                '.$selectProvincias.'
                            </div>'; 
                        
                    }
                           
                $mensaje = '';
                $validar = TRUE;
                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'formCantones'=>$formCantones));
            
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
                $objConfigurarParroquiaCanton = new ConfigurarParroquiaCanton($this->dbAdapter);
                $objMetodos = new Metodos();
                
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                
                $idProvinciaEncriptado = $post['id'];
                if(empty($idProvinciaEncriptado) || $idProvinciaEncriptado == NULL){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PROVINCIA</div>';
                }else{
                    
                    $idProvincia = $objMetodos->desencriptar($idProvinciaEncriptado);
                    $listaConfigurarCantonProvincia = $objConfigurarCantonProvincia->FiltrarConfigurarCantonProvinciaPorProvincia($idProvincia, true);
                    $array1 = array();
                    $i = 0;
                    $j = count($listaConfigurarCantonProvincia);
                    foreach ($listaConfigurarCantonProvincia as $valueConfigurarCantonProvincia) {
                        
                        $idConfigurarCantonProvinciaEncriptado = $objMetodos->encriptar($valueConfigurarCantonProvincia['idConfigurarCantonProvincia']);
                        $botonEliminarCanton = '';
                        if(count($objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvinciaLimite1($valueConfigurarCantonProvincia['idConfigurarCantonProvincia'])) == 0){
                            $botonEliminarCanton = '<button id="btnEliminarCanton'.$i.'" title="ELIMINAR '.$valueConfigurarCantonProvincia['nombreCanton'].'" onclick="eliminarCanton(\''.$idConfigurarCantonProvinciaEncriptado.'\','.$i.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                        }
                        $botones = $botonEliminarCanton;
                        $array1[$i] = array(
                            '_j'=>$j,
                            'nombreCanton'=>$valueConfigurarCantonProvincia['nombreCanton'],
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
    
    
    public function eliminarcantonAction()
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
                $objMetodos = new Metodos();
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $idConfigurarCantonProvinciaEncriptado = $post['id'];
                $numeroFila = $post['numeroFila'];
                if(empty($idConfigurarCantonProvinciaEncriptado) || $idConfigurarCantonProvinciaEncriptado == null){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL CANTÓN</div>';
                }else  if(!is_numeric($numeroFila)){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA FILA</div>';
                }else{
                    
                    $idConfigurarCantonProvincia = $objMetodos->desencriptar($idConfigurarCantonProvinciaEncriptado);
                    if(count($objConfigurarParroquiaCanton->FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvinciaLimite1($idConfigurarCantonProvincia)) > 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE PUEDE ELIMINAR EL CANTÓN PORQUE TIENE PARROQUIAS ASIGNADAS</div>';
                    }else{
                        $resultado =  $objConfigurarCantonProvincia->EliminarConfigurarCantonProvincia($idConfigurarCantonProvincia);
                        if(count($resultado) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ EL CANTÓN POR FAVOR INTENTE MÁS TARDE</div>';
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
    public function ingresarcantonAction()
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
                $objCantones = new Cantones($this->dbAdapter);
                $objProvicias = new Provincias($this->dbAdapter);
                $objConfigurarCantonProvincia = new ConfigurarCantonProvincia($this->dbAdapter);
                $objMetodos = new Metodos();
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $idProvinciaEncriptado = $post['selectProvinciasCantones'];
                $nombreCanton = trim(strtoupper($post['nombreCanton']));
                if(empty($idProvinciaEncriptado) || $idProvinciaEncriptado == NULL || $idProvinciaEncriptado=="0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA PROVINCIA</div>';
                }else if(empty($nombreCanton) || strlen($nombreCanton) > 100){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DEl CANTÓN MÁXIMO 100 CARACTERES</div>';
                }else {
                    $idProvincia = $objMetodos->desencriptar($idProvinciaEncriptado);
                    if(count($objProvicias->FiltrarProvincia($idProvincia)) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PROVINCIA SELECCIONADA</div>';
                    }else{
                        $listaCanton = $objCantones->FiltrarCantonPorNombreCanton($nombreCanton);
                        $idCanton = 0;
                        $validarExistenciaCanton = TRUE;
                        if(count($listaCanton) > 0){
                            $listaConfigurarCantonProvincia = $objConfigurarCantonProvincia->FiltrarConfigurarCantonProvinciaPorProvinciaCanton($idProvincia, $listaCanton[0]['idCanton'], true);
                            if(count($listaConfigurarCantonProvincia) == 0){
                                $idCanton = $listaCanton[0]['idCanton'];
                                 $validarExistenciaCanton = FALSE;
                            }
                        }else{
                            ini_set('date.timezone','America/Bogota'); 
                            $hoy = getdate();
                            $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                            $resultadoCanton = $objCantones->IngresarCanton($nombreCanton, $fechaSubida, 1);
                            if(count($resultadoCanton) > 0){
                                $idCanton = $resultadoCanton[0]['idCanton'];
                            }
                        }
                        if($idCanton == 0){
                            if($validarExistenciaCanton == TRUE){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN CANTÓN LLAMADO '.$nombreCanton.'</div>';
                            }else{
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL CANTÓN POR FAVOR INTENTE MÁS TARDE</div>';
                            }
                        }else{
                            $resultado = $objConfigurarCantonProvincia->IngresarConfigurarCantonProvincia($idProvincia, $idCanton, 1);
                            if(count($resultado) == 0){
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL CANTÓN POR FAVOR INTENTE MÁS TARDE</div>';
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
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    

}

