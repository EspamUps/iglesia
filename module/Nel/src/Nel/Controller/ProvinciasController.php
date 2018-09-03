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
use Nel\Modelo\Entity\ConfigurarCantonProvincia;
use Nel\Modelo\Entity\TipoUsuario;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class ProvinciasController extends AbstractActionController
{
    public $dbAdapter;
     public function eliminarprovinciaAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $request=$this->getRequest();
            if(!$request->isPost()){
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
            }else{
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $objProvincia = new Provincias($this->dbAdapter);
                $objConfigurarCantonProvincia = new ConfigurarCantonProvincia($this->dbAdapter);
                $objMetodos = new Metodos();
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $idProvinciaEncriptado = $post['id'];
                $numeroFila = $post['numeroFila'];
                if(empty($idProvinciaEncriptado) || $idProvinciaEncriptado == null){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PROVINCIA</div>';
                }else  if(!is_numeric($numeroFila)){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA FILA</div>';
                }else{
                    
                    $idProvincia = $objMetodos->desencriptar($idProvinciaEncriptado);
                    if(count($objConfigurarCantonProvincia->FiltrarConfigurarCantonProvinciaPorProvinciaLimite1($idProvincia)) > 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE PUEDE ELIMINAR LA PROVINCIA PORQUE TIENE CANTONES ASIGNADOS</div>';
                    }else{
                        $resultado =  $objProvincia->EliminarProvincia($idProvincia);
                        if(count($resultado) > 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ELIMINÓ LA PROVINCIA POR FAVOR INTENTE MÁS TARDE</div>';
                        }else{
                            $mensaje = '';
                            $validar = TRUE;
                            return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'numeroFila'=>$numeroFila));
                        }
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    public function ingresarprovinciaAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $request=$this->getRequest();
            if(!$request->isPost()){
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
            }else{
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $objProvincia = new Provincias($this->dbAdapter);
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $nombreProvincia = trim(strtoupper($post['nombreProvincia']));
                if(empty($nombreProvincia) || strlen($nombreProvincia) > 100){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DE LA PROVINCIA MÁXIMO 100 CARACTERES</div>';
                }else if(count($objProvincia->FiltrarProvinciaPorNombreProvincia($nombreProvincia)) > 0){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA PROVINCIA LLAMADA '.$nombreProvincia.'</div>';
                }else{
                    ini_set('date.timezone','America/Bogota'); 
                    $hoy = getdate();
                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                    $resultado =  $objProvincia->IngresarProvincia($nombreProvincia,$fechaSubida,1);
                    if(count($resultado) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA PROVINCIA POR FAVOR INTENTE MÁS TARDE</div>';
                    }else{
                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADA CORRECTAMENTE</div>';
                        $validar = TRUE;
                    }
                }
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    public function obtenerprovinciasAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = false;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }else{
            $request=$this->getRequest();
            if(!$request->isPost()){
                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
            }else{
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $objProvincias = new Provincias($this->dbAdapter);
                $objConfigurarCantonProvincia = new ConfigurarCantonProvincia($this->dbAdapter);
                $objMetodos = new Metodos();
                $formProvincias = '<div class="form-group col-lg-6">
                        <label for="nombreProvincia">NOMBRE DE LA PROVINCIA</label>
                        <div class="input-group margin">
                            <input autofocus  maxlength="100" type="text" id="nombreProvincia" name="nombreProvincia" class="form-control">
                            <span class="input-group-btn">
                                <button style="margin-left: 10%;" name="btnGuardarProvincia" id="btnGuardarProvincia" data-loading-text="GUARDANDO.." class="btn  btn-primary btn-flat"><i class="fa fa-save"></i>GUARDAR</button>
                            </span>
                        </div>
                  </div>';
                $listaProvincias = $objProvincias->ObtenerProvincias();
                $array1 = array();
                $i = 0;
                $j = count($listaProvincias);
                foreach ($listaProvincias as $valueProvincias) {
                    $idProvinciaEncriptado = $objMetodos->encriptar($valueProvincias['idProvincia']);
                    $validarBoton = false;
                    if(count($objConfigurarCantonProvincia->FiltrarConfigurarCantonProvinciaPorProvinciaLimite1($valueProvincias['idProvincia'])) > 0)
                    {
                        $validarBoton = true;
                    }
                    $array1[$i] = array(
                        '_j'=>$j,
                        'idProvinciaEncriptado'=>$idProvinciaEncriptado,
                        'nombreProvincia'=>$valueProvincias['nombreProvincia'],
                        'validarBoton'=>$validarBoton,
                        'botones'=>''
                    );
                    $j--;
                    $i++;
                }             
                $mensaje = '';
                $validar = TRUE;
                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'formProvincias'=>$formProvincias,'tabla'=>$array1));
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }

}

