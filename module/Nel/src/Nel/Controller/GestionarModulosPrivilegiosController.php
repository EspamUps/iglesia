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
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Usuario;
use Nel\Modelo\Entity\Modulos;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class GestionarModulosPrivilegiosController extends AbstractActionController
{
    public $dbAdapter;
    
    public function obtenerformularioadministrarmodulosAction()
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
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 7);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else{
                $objMetodosControlador =  new MetodosControladores();
                
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{               
                    $objMetodos = new Metodos();
                    $objUsuario = new Usuario($this->dbAdapter);
                    $objModulo = new Modulos($this->dbAdapter);
                    $post = array_merge_recursive(
                        $request->getPost()->toArray(),
                        $request->getFiles()->toArray()
                    );


                    $idUsuarioEncriptado = $post['id'];
                    $i = $post['i'];
                    $j = $post['j'];
                    if($idUsuarioEncriptado == NULL || $idUsuarioEncriptado == "" ){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';

                    }else if(!is_numeric($i)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else if(!is_numeric($j)){
                        $mensaje = '<div class="alert alert-danger text-center" role="alert">EL IDENTIFICADOR DE LA FILA DEBE SER UN NÚMERO</div>';
                    }else{
                        $idUsuarioM = $objMetodos->desencriptar($idUsuarioEncriptado); 
                        $listaUsuarios = $objUsuario->FiltrarUsuario($idUsuario);
                        if(count($listaUsuarios) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL USUARIO SELECCIONADO NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                        }else{
                            
                            $listaModulos = $objModulo->ObtenerModulos();
                            $cuerpoTabla  = '';
                            $optionModulos = '<option value="0">SELECCIONE LOS MÓDULOS A ASIGNAR</option>';
                            foreach ($listaModulos as $valueModulos) {
                                $listaAsignarModulos = $objAsignarModulo->FiltrarAsignarModuloPorUsuarioYModulo($idUsuarioM, $valueModulos['idModulo'], 1);
                                $idModuloEncriptado = $objMetodos->encriptar($valueModulos['idModulo']);
                                if(count($listaAsignarModulos)==0){
                                    $optionModulos = $optionModulos.'<option value="'.$idModuloEncriptado.'">'.$valueModulos['nombreModulo'].'</option>';
                                }else{
                                    $botonEliminarModulo = '<button id="btnEliminarModulo'.$i.'" title="ELIMINAR MÓDULO '.$valueModulos['nombreModulo'].'" onclick="EliminarModulo(\''.$listaAsignarModulos[0]['idAsignarModulo'].'\')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
            
                                    $cuerpoTabla =$cuerpoTabla. '<tr>
                                            <td>'.$valueModulos['nombreModulo'].'</td>
                                            <td>'.$botonEliminarModulo.'</td>
                                            </tr>';

                                }     
                            }
                           
                           $selectModulo = '<div class="col-lg-9"><input type="hidden" id="usuarioEncriptadoMO" name ="usuarioEncriptadoMO" value="'.$idUsuarioEncriptado.'">
                                        <select class="form-control" id="selectModulos" name="selectModulos">
                                        '.$optionModulos.'
                                    </select> <br><br></div><div class="col-lg-3"><button type="submit" data-loading-text="GUARDANDO..." class="btn btn-primary btn-sm btn-flat" id="btnGuardarAsignarModulos" ><i class="fa fa-save"></i>GUARDAR</button></div>';
                            
                            $tabla = '';
                            if(!empty($cuerpoTabla)){
                            $tabla =$tabla. '<div class="col-lg-12"><label>MÓDULOS QUE YA HAN SIDO ASIGNADOS</label>                                                  
                                                    <table class="table table-bordered table-hover dataTable">
                                                    <thead>
                                                        <tr>
                                                            <td><b>NOMBRE MÓDULO</b></td>
                                                            <td><b>OPCIONES</b></td>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                            '.$cuerpoTabla.'
                                                        </tbody>
                                                    </table>
                                                    </div>';
                            }
                           
                            
                            
                            
                            $mensaje = '';
                            $validar = TRUE;
                            return new JsonModel(array('select'=>$selectModulo,'mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                        }
                    }

                }  
            }
        }
        
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
      


}