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
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\Sacerdotes;
use Nel\Modelo\Entity\DireccionLugarMisa;
use Nel\Modelo\Entity\ConfigurarParroquiaCanton;
use Nel\Modelo\Entity\ConfigurarMisa;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class ConfigurarMisaController extends AbstractActionController
{
    public $dbAdapter;
    public function ingresarconfigurarmisaAction()
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
                $objConfigurarMisa = new ConfigurarMisa($this->dbAdapter);
//                $objLugaresMisa = new LugaresMisa($this->dbAdapter);
//                $objDireccionLugarMisa = new DireccionLugarMisa($this->dbAdapter);
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $identifiacion = $post['identificacion'];
                $idLugarMisaEncriptado = $post['selectLugarMisa'];
                
                $idMisaEncriptado = $post['selectMisas'];
                $descripcionMisa = $post['descripcionMisa'];
                $fechaMisa = $post['fechaMisa'];
                $horaInicio = $post['horaInicio'];
                $horaFin = $post['horaFin'];
                $idSacerdoteEncriptado = $post['idSacerdoteEncriptado'];
                $valorMisa = $post['valorMisa'];
                if($identifiacion == NULL || $identifiacion == ""  ){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA IDENTIFICACIÓN DEL SACERDOTE</div>';
                }else if($idSacerdoteEncriptado == NULL || $idSacerdoteEncriptado == "" || $idSacerdoteEncriptado == "0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL SACERDOTE</div>';
                 }else if($idLugarMisaEncriptado == NULL || $idLugarMisaEncriptado == "" || $idLugarMisaEncriptado == "0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UN LUGAR LUGAR</div>';
                 }else if($idMisaEncriptado == NULL || $idMisaEncriptado == "" || $idMisaEncriptado == "0"){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">SELECCIONE UNA MISA</div>';
                 }else if(empty ($descripcionMisa) || $descripcionMisa > 300){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA DESCRIPCIÓN DE LA MISA MÁXIMO 300 CARACTERES</div>';
                 }else if(empty ($fechaMisa)){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA FECHA DE LA MISA</div>';
                 }else if(empty ($horaInicio)){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA HORA INICIO DE LA MISA</div>';
                 }else if(empty ($horaFin)){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA HORA FIN DE LA MISA</div>';
                 }else{
                     ini_set('date.timezone','America/Bogota'); 
                     $fecha_actual = strtotime(date("d-m-Y"));
                     $fecha_entrada = strtotime($fechaMisa);
                     
                    if($fecha_entrada < $fecha_actual)
                    {
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">LA FECHA DE LA MISA DEBE SER IGUAL O MAYOR A LA ACTUAL</div>';                        
                    }else
                    {
                        $horaInicioComparar = strtotime($horaInicio);
                        $horaFinComparar = strtotime($horaFin);
                        if($horaInicioComparar >= $horaFinComparar){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LA HORA DE INICIO NO DEBE SER MAYOR NI IGUAL QUE LA HORA FIN</div>';                        
                        }else{
                            $validarDiaActual = FALSE;
                            $validarHoraActual = FALSE;
                            if($fecha_entrada == $fecha_actual){
                                $validarDiaActual = TRUE;
                            }
                            if($validarDiaActual == TRUE){
                                $hora_actual = strtotime(date("H:i",time()));
                                if($hora_actual >= $horaInicioComparar){
                                    $validarHoraActual = TRUE;
                                }
                            }
                            if($validarHoraActual == TRUE){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">LA HORA DE INICIO NO DEBE SER MENOR NI IGUAL QUE LA HORA ACTUAL</div>';                        
                            }else{
                                $idSacerdote = $objMetodos->desencriptar($idSacerdoteEncriptado);
                                $idLugarMisa = $objMetodos->desencriptar($idLugarMisaEncriptado);
                                $idMisa = $objMetodos->desencriptar($idMisaEncriptado);
                                $listaConfigurarMisaPorFechaSacerdoteHoras = $objConfigurarMisa->FiltrarConfigurarMisaPorFechaHoraSacerdote($fechaMisa, $idSacerdote, $horaInicio, $horaFin);
                                if(count($listaConfigurarMisaPorFechaSacerdoteHoras) > 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">EL SACERDOTE INGRESADO YA TIENE UNA MISA PROGRAMADA QUE CHOCA CON LA FECHA Y HORAS INGRESADAS</div>';                        
                                }else{
                                    $listaConfigurarMisaPorFechaLugarHoras = $objConfigurarMisa->FiltrarConfigurarMisaPorFechaHoraLugar($fechaMisa, $idLugarMisa, $horaInicio, $horaFin);
                                    if(count($listaConfigurarMisaPorFechaLugarHoras) > 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL LUGAR SELECCIONADO PARA CELEBRAR LA MISA NO ESTA DISPONIBLE EN EL HORARIO SELECCIONADO</div>';                        
                                    }else{
                                        $hoy = getdate();
                                        $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                        $resultado = $objConfigurarMisa->IngresarConfigurarMisa($idMisa, $idSacerdote, $idLugarMisa, $descripcionMisa, $fechaMisa, $horaInicio, $horaFin, $fechaSubida, $valorMisa, 1);  
                                        if(count($resultado) > 0){
                                            $validar = TRUE;
                                            $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADA CORRECTAMENTE</div>';
                                        }else{
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA CONFIGURACIÓN POR FAVOR INTENETE MÁS TARDE</div>';                        
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
    
    
    public function filtrardatoslugarmisaAction()
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
                $objLugaresMisa = new LugaresMisa($this->dbAdapter);
                $objDireccionLugarMisa = new DireccionLugarMisa($this->dbAdapter);
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $idLugarMisaEncriptado = $post['idLugarMisa'];
                if($idLugarMisaEncriptado == "" || $idLugarMisaEncriptado == NULL){
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DEL LUGAR</div>';
                }else{
                    $idLugarMisa = $objMetodos->desencriptar($idLugarMisaEncriptado);
                    $listaLugarMisa = $objLugaresMisa->FiltrarLugaresMisa($idLugarMisa);
                    if(count($listaLugarMisa) == 0){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL LUGAR SELECCIONADO NO EXISTE</div>';
                    }else{
                        $listaDireccionLugarMisa = $objDireccionLugarMisa->FiltrarDireccionLugarMisaPorLugarEstado($listaLugarMisa[0]['idLugarMisa'], 1);
                        $tabla = '';
                        if(count($listaDireccionLugarMisa) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL LUGAR SELECCIONADO NO EXISTE</div>';
                        }else{
                            $tabla = '<div class="table-responsive">
                                <table class="table">
                                    <thead> 
                                        <tr>
                                            <th colspan="2">DATOS DEL LUGAR</th>
                                        </tr>
                                        <tr>
                                            <th>PROVINCIA</th>
                                            <td>'.$listaDireccionLugarMisa[0]['nombreProvincia'].'</td>
                                        </tr>
                                        <tr>
                                            <th>CANTÓN</th>
                                            <td>'.$listaDireccionLugarMisa[0]['nombreCanton'].'</td>
                                        </tr>
                                        <tr>
                                            <th>PARROQUIA</th>
                                            <td>'.$listaDireccionLugarMisa[0]['nombreParroquia'].'</td>
                                        </tr>
                                        <tr>
                                            <th>DIRECCIÓN</th>
                                            <td>'.$listaDireccionLugarMisa[0]['direccionLugarMisa'].'</td>
                                        </tr>
                                        <tr>
                                            <th>REFERENCIA</th>
                                            <td>'.$listaDireccionLugarMisa[0]['referenciaLugarMisa'].'</td>
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
    
    public function filtrarsacerdoteporidentificacionAction()
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
                    }else{ 
                        
                        $listaSacerdote = $objSacerdotes->FiltrarSacerdotePorPersona($listaPersona[0]['idPersona']);
                        if(count($listaSacerdote) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UN SACERDOTE CON LA IDENTIFICACIÓN '.$identificacion.'</div>';
                        }else{
                            $idSacerdoteEncriptado = $objMetodos->encriptar($listaSacerdote[0]['idSacerdote']);
                            $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                            $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
                            $tabla = '
                                <div class="table-responsive"><table class="table">
                                <thead> 
                                    <tr>
                                        <th colspan="2">DATOS DEL SACERDOTE</th>
                                    </tr>
                                    <tr>
                                        <th>NOMBRES</th>
                                        <td>'.$nombres.'</td>
                                    </tr>
                                    <tr>
                                        <th>APELLIDOS</th>
                                        <td>'.$apellidos.'</td>
                                    </tr>
                                </thead>
                            </table></div>';
                            $mensaje = '';
                            $validar = TRUE;
                            return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla,'idSacerdoteEncriptado'=>$idSacerdoteEncriptado));
                        }
                    }
                }
                
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    
//    public function obtenermisasAction()
//    {
//        $this->layout("layout/administrador");
//        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
//        $validar = false;
//        $sesionUsuario = new Container('sesionparroquia');
//        if(!$sesionUsuario->offsetExists('idUsuario')){
//            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
//        }else{
//            $request=$this->getRequest();
//            if(!$request->isPost()){
//                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
//            }else{
//                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
//                $objMisas = new Misas($this->dbAdapter);
//                $objMetodos = new Metodos();
//                ini_set('date.timezone','America/Bogota'); 
//                
//                $listaMisas = $objMisas->ObtenerMisas();
//                $array1 = array();
//                $i = 0;
//                $j = count($listaMisas);
//                foreach ($listaMisas as $value) {
//                    
//                    $descripcionMisa = $value['descripcionMisa'];
//                    $fechaRegistro = $objMetodos->obtenerFechaEnLetra($value['fechaRegistro']);
//                    $botones = '';     
//                    $array1[$i] = array(
//                        '_j'=>$j,
//                        'descripcionMisa'=>$descripcionMisa,
//                        'fechaRegistro'=>$fechaRegistro,
//                        'opciones'=>$botones,
//                    );
//                    $j--;
//                    $i++;
//                }
//
//                $mensaje = '';
//                $validar = TRUE;
//                return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$array1));
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }
//    public function ingresarmisaAction()
//    {
//        $this->layout("layout/administrador");
//        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
//        $validar = false;
//        $sesionUsuario = new Container('sesionparroquia');
//        if(!$sesionUsuario->offsetExists('idUsuario')){
//            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
//        }else{
//            $request=$this->getRequest();
//            if(!$request->isPost()){
//                $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
//            }else{
//                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
//                $objMetodos = new Metodos();
//               $objMisas = new Misas($this->dbAdapter);
//                $post = array_merge_recursive(
//                    $request->getPost()->toArray(),
//                    $request->getFiles()->toArray()
//                );
//                $descripcionMisa = trim(strtoupper($post['descripcionMisa']));
//                if(empty ($descripcionMisa) || strlen($descripcionMisa) > 200){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DE LA MISA MÁXIMO 200 CARACTERES</div>';
//                }else if(count( $objMisas->FiltrarMisaPorDescripcion($descripcionMisa)) > 0){
//                    $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UNA MISA LLAMADA '.$descripcionMisa.'</div>';
//                }else{
//                    ini_set('date.timezone','America/Bogota'); 
//                    $hoy = getdate();
//                    $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
//                    $resultado =  $objMisas->IngresarMisa($descripcionMisa, $fechaSubida, 1);
//                    if(count($resultado) == 0){
//                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ LA MISA POR FAVOR INTENTE MÁS TARDE</div>';
//                    }else{ 
//                        $mensaje = '<div class="alert alert-success text-center" role="alert">INGRESADO CORRECTAMENTE</div>';
//                        $validar = TRUE;
//                    }
//                }
//                
//            }
//        }
//        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
//    }
}

