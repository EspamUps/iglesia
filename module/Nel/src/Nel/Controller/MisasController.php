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
use Nel\Modelo\Entity\LugaresMisa;
use Nel\Modelo\Entity\Misas;
use Nel\Modelo\Entity\TelefonoPersona;
use Nel\Modelo\Entity\DireccionLugarMisa;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;

use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class MisasController extends AbstractActionController
{
    public $dbAdapter;
    public function obtenermisasAction()
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
                $objMisas = new Misas($this->dbAdapter);
                $objMetodos = new Metodos();
                ini_set('date.timezone','America/Bogota'); 
                
                $listaMisas = $objMisas->ObtenerMisas();
                $array1 = array();
                $i = 0;
                $j = count($listaMisas);
                foreach ($listaMisas as $value) {
                    
                    $descripcionMisa = $value['descripcionMisa'];
                    $fechaRegistro = $objMetodos->obtenerFechaEnLetra($value['fechaRegistro']);
                    $botones = '';     
                    $array1[$i] = array(
                        '_j'=>$j,
                        'descripcionMisa'=>$descripcionMisa,
                        'fechaRegistro'=>$fechaRegistro,
                        'opciones'=>$botones,
                    );
                    $j--;
                    $i++;
                }

                $mensaje = '';
                $validar = TRUE;
                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$array1));
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    public function ingresarmisaAction()
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
                $objMetodos = new Metodos();
               $objMisas = new Misas($this->dbAdapter);
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $descripcionMisa = trim(strtoupper($post['descripcionMisa']));
                if(empty ($descripcionMisa) || strlen($descripcionMisa) > 200){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DE LA MISA MÁXIMO 200 CARACTERES</div>';
                }else if(count( $objMisas->FiltrarMisaPorDescripcion($descripcionMisa)) > 0){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA MISA LLAMADA '.$descripcionMisa.'</div>';
                }else{
                    ini_set('date.timezone','America/Bogota'); 
                    $hoy = getdate();
                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                    $resultado =  $objMisas->IngresarMisa($descripcionMisa, $fechaSubida, 1);
                    if(count($resultado) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA MISA POR FAVOR INTENTE MÁS TARDE</div>';
                    }else{ 
                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
                        $validar = TRUE;
                    }
                }
                
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
}

