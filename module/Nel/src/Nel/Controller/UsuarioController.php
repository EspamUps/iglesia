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
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\TipoUsuario;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class UsuarioController extends AbstractActionController
{
    public $dbAdapter;
  

    public function obtenerusuariosAction()
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
            else {
                $request=$this->getRequest();
                if(!$request->isPost()){
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                }else{
                    $objUsuario = new Usuario($this->dbAdapter);
                    $listaUsuarios = $objUsuario->ObtenerUsuarios();
                    $tabla = $this->CargarTablaUsuarioAction($idUsuario,$this->dbAdapter,$listaUsuarios, 0, count($listaUsuarios));
                    
                    $mensaje = '';
                    $validar = TRUE;
                    return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar,'tabla'=>$tabla));
                }                    
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
    
    function CargarTablaUsuarioAction($idUsuario,$adaptador,$listaUsuarios, $i, $j)
    {
        $objMetodos = new Metodos();
        $objMetodosControlador = new MetodosControladores();
        ini_set('date.timezone','America/Bogota'); 
        $array1 = array();
        foreach ($listaUsuarios as $value) {
            $idUsuarioEncriptado = $objMetodos->encriptar($value['idUsuario']);
            
            $botonModificarEstadoUsuario = '';
            $botonModificarUsuario ='';
            
            if($objMetodosControlador->ValidarPrivilegioAction($adaptador, $idUsuario, 7, 1) == true){
                if($idUsuario != $value['idUsuario'])
                {
                    if($value['estadoUsuario']==0)
                    $botonModificarEstadoUsuario = '<button data-target="#modalModificarEstadoUsuario" data-toggle="modal"  id="btnModificarEstadoUsuario'.$i.'" title="HABILITAR A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioModificarEstadoUsuario(\''.$idUsuarioEncriptado.'\','.$i.','.$j.')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>';
                    else
                    $botonModificarEstadoUsuario = '<button data-target="#modalModificarEstadoUsuario" data-toggle="modal"  id="btnModificarEstadoUsuario'.$i.'" title="DESHABILITAR A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioModificarEstadoUsuario(\''.$idUsuarioEncriptado.'\','.$i.','.$j.')" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i></button>';
                }
            }
            
            if($objMetodosControlador->ValidarPrivilegioAction($adaptador, $idUsuario, 7, 2) == true)
                $botonModificarUsuario = '<button data-target="#modalModificarUsuario" data-toggle="modal" id="btnModificarUsuario'.$i.'" title="MODIFICAR A '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioModificarUsuario(\''.$idUsuarioEncriptado.'\','.$i.','.$j.')" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-pencil"></i></button>';
            
            $botonGestionModulos = '<button data-target="#modalAdministrarModulos" data-toggle="modal" id="btnAdministrarModulos'.$i.'" title="ADMINISTRAR MODULOS DE '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioGestionModulos(\''.$idUsuarioEncriptado.'\','.$i.','.$j.')" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-cog"></i></button>';
            $botonGestionPrivilegios = '<button data-target="#modalAdministrarPrivilegios" data-toggle="modal" id="btnAdministrarPrivilegios'.$i.'" title="ADMINISTRAR PRIVILEGIOS DE '.$value['primerNombre'].' '.$value['segundoNombre'].'" onclick="obtenerFormularioGestionPrivilegios(\''.$idUsuarioEncriptado.'\','.$i.','.$j.')" class="btn btn-default btn-sm btn-flat"><i class="fa fa-cogs"></i></button>';

            
            $identificacion = $value['identificacion'];
            $nombres = $value['primerNombre'].' '.$value['segundoNombre'];
            $apellidos = $value['primerApellido'].' '.$value['segundoApellido'];
            $usuario = $value['nombreUsuario'];
            $tipoUsuario = $value['descripcionTipoUsuario'];
            
            $botones = $botonModificarEstadoUsuario .' '.$botonModificarUsuario;  
            $botones2 = $botonGestionModulos.' '.$botonGestionPrivilegios;  

             
            $array1[$i] = array(
                '_j'=>$j,
                '_idUsuarioEncriptado'=>$idUsuarioEncriptado,                
                'identificacion'=>$identificacion,
                'nombres'=>$nombres,
                'apellidos'=>$apellidos,
                'tipousuario'=>$tipoUsuario,
                'usuario'=>$usuario,
                'opciones1'=>$botones,
                'opciones2'=>$botones2
            );
            $j--;
            $i++;
        }
        
        return $array1;
    }
    
    
    public function obtenerformulariomodificarestadousuarioAction()
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
                        $idUsuario = $objMetodos->desencriptar($idUsuarioEncriptado); 
                        $listaUsuarios = $objUsuario->FiltrarUsuario($idUsuario);
                        if(count($listaUsuarios) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL USUARIO SELECCIONADO NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                        }else{
                            
                            
                            $tabla = '';
                            if($listaUsuarios[0]['ultimoModificado']==1 && $listaUsuarios[0]['estadoUsuario']==1){
                            $tabla = '<div class="form-group col-lg-12">
                                    <input type="hidden" value="'.$i.'" id="ime" name="ime">
                                    <input type="hidden" value="'.$j.'" id="jme" name="jme">
                                    <input type="hidden" value="'.$idUsuarioEncriptado.'" name="idUsuarioEncriptadoME" id="idUsuarioEncriptadoME">
                                    <h4>Usted está a punto de deshabilitar al usuario: '.$listaUsuarios[0]['nombreUsuario'].' </h4>
                                    <label>Esto significa que '.$listaUsuarios[0]['nombreUsuario'].' ya no tendrá acceso al sistema y todos</label>
                                    <label>sus permisos y privilegios automáticamente serán desactivados.</label>
                                      

                                </div>
                                <div class="form-group col-lg-12">
                                    <button data-loading-text="DESHABILITANDO..." id="btnModificarEstadoUsuario" type="submit" class="btn btn-danger pull-right"><i class="fa fa-ban"></i> DESHABILITAR USUARIO</button>
                                 </div>
                                ';
                            }else{                            
                            
                                $tabla = '<div class="form-group col-lg-12">
                                    <input type="hidden" value="'.$i.'" id="ime" name="ime">
                                    <input type="hidden" value="'.$j.'" id="jme" name="jme">
                                    <input type="hidden" value="'.$idUsuarioEncriptado.'" name="idUsuarioEncriptadoME" id="idUsuarioEncriptadoME">
                                    <h4>Usted está a punto de habilitar al usuario: '.$listaUsuarios[0]['nombreUsuario'].' </h4>
                                    <label>Esto significa que '.$listaUsuarios[0]['nombreUsuario'].' volverá a tener acceso al sistema</label>
                                    <label>con los permisos y privilegios que tenía anteriormente.</label>
                                      

                                </div>
                                <div class="form-group col-lg-12">
                                    <button data-loading-text="HABILITANDO..." id="btnModificarEstadoUsuario" type="submit" class="btn btn-success pull-right"><i class="fa fa-check"></i> HABILITAR USUARIO</button>
                                 </div>
                                ';
                            }
                            
                            
                            
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
    
    
    public function obtenerformulariomodificarusuarioAction()
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
                        $idUsuario = $objMetodos->desencriptar($idUsuarioEncriptado); 
                        $listaUsuarios = $objUsuario->FiltrarUsuario($idUsuario);
                        if(count($listaUsuarios) == 0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">EL USUARIO SELECCIONADO NO EXISTE EN NUESTRA BASE DE DATOS</div>';
                        }else{
                            $objTipoUsuario = new TipoUsuario($this->dbAdapter);
                            $optionTipoUsuario = '<option value="0">SELECCIONE UN TIPO DE USUARIO</option>';
                            foreach ($objTipoUsuario->ObtenerTiposUsuario() as $valueTipos) {
                                        $idTipoUsuarioEncriptado = $objMetodos->encriptar($valueTipos['idTipoUsuario']);
                                        if($valueTipos['idTipoUsuario'] == $listaUsuarios[0]['idTipoUsuario']){
                                            $optionTipoUsuario = $optionTipoUsuario.'<option selected value="'.$idTipoUsuarioEncriptado.'">'.$valueTipos['descripcionTipoUsuario'].'</option>';
                                        }else
                                            $optionTipoUsuario = $optionTipoUsuario.'<option value="'.$idTipoUsuarioEncriptado.'">'.$valueTipos['descripcionTipoUsuario'].'</option>';
                                        }  
                            
                            
                            $tabla = '';                          
                             
                            $tabla = '<div class="form-group col-lg-12">
                                    <input type="hidden" value="'.$i.'" id="im" name="im">
                                    <input type="hidden" value="'.$j.'" id="jm" name="jm">
                                    <input type="hidden" value="'.$idUsuarioEncriptado.'" name="idUsuarioEncriptadoM" id="idUsuarioEncriptadoM">
                                    <label for="usuario">USUARIO</label>
                                    <input value="'.$listaUsuarios[0]['nombreUsuario'].'" maxlength="20" autocomplete="off" type="text" id="usuarioM" name="usuarioM" class="form-control">
                                    <label for="contraseña">CONTRASEÑA</label>
                                    <input value="'.$listaUsuarios[0]['contrasena'].'" maxlength="20" autocomplete="off" type="text" id="contraseñaM" name="contraseñaM" class="form-control">
                                    <label for="selectTipoUsuarioM">TIPO DE USUARIO</label>
                                    <select id="selectTipoUsuarioM" name="selectTipoUsuarioM" class="form-control">'.$optionTipoUsuario.'</select>
                                            

                                </div>
                                <div class="form-group col-lg-12">
                                    <button data-loading-text="GUARDANDO..." id="btnGuardarUsuarioM" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>
                                </div>';
                            
                            
                            
                            
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
    
    
    public function modificarusuarioAction()
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
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 7, 2);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS PARA MODIFICAR EN ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objUsuario = new Usuario($this->dbAdapter);                        
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idUsuarioEncriptadoM = $post['idUsuarioEncriptadoM'];
                        $im = $post['im'];
                        $jm = $post['jm'];
                        $idIglesia = $sesionUsuario->offsetGet('idIglesia');
                        $usuario = trim($post['usuarioM']);
                        $contraseña = trim(strtoupper($post['contraseñaM']));
                        $idTipoUsuarioE = $post['selectTipoUsuarioM'];
                        
                        if($idIglesia == NULL || $idIglesia == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA IGLESIA</div>';
                        }else if(empty ($usuario) || strlen($usuario) > 20){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL USUARIO MÁXIMO 20 CARACTERES</div>';
                        }else if(empty ($contraseña) || strlen($contraseña) > 20){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA CONTRASEÑA MÁXIMO 20 CARACTERES</div>';
                        }else{   
                            
                            $idUsuarioM= $objMetodos->desencriptar($idUsuarioEncriptadoM);
                            $listaUsuario = $objUsuario->FiltrarUsuario($idUsuarioM);
                            if(count($listaUsuario)>0)
                            {
                                $idTipoUsuario = $objMetodos->desencriptar($idTipoUsuarioE);
                                if(($listaUsuario[0]['idTipoUsuario']==$idTipoUsuario)&& ($listaUsuario[0]['nombreUsuario']==$usuario)) 
                                {
                                    if($listaUsuario[0]['contrasena']==$contraseña)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NINGÚN CAMPO HA SIDO MODIFICADO</div>';
                                    else
                                    {
                                       $resultado = $objUsuario->ModificarUsuario($idUsuarioM, $contraseña, 1);
                                       if(count($resultado) == 0)                                    
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE MODIFICÓ AL USUARIO, POR FAVOR INTENTE MÁS TARDE</div>';
                                        else{ 
                                            $mensaje = '<div class="alert alert-success text-center" role="alert">MODIFICADO CORRECTAMENTE</div>';
                                            $tablaUsuario = $this->CargarTablaUsuarioAction($idUsuario, $this->dbAdapter,$resultado, $im, $jm);                               
                                            $validar = TRUE; 
                                            return new JsonModel(array('tabla'=>$tablaUsuario,'idUsuario'=>$idUsuarioEncriptadoM,'jm'=>$jm,'im'=>$im,'mensaje'=>$mensaje,'validar'=>$validar));

                                        }  
                                    }
                                 }else{
                                        $resultado = $objUsuario->ModificarEstadoUsuario($idUsuarioM, 0, 0);
                                        if(count($resultado) == 0){                                    
                                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE MODIFICÓ EL ESTADO DEL USUARIO, POR FAVOR INTENTE MÁS TARDE</div>';
                                        }else{   
                                            ini_set('date.timezone','America/Bogota'); 
                                            $hoy = getdate();
                                            $fechaSubida = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                                            $resultado2= $objUsuario->IngresarUsuario($listaUsuario[0]['idPersona'], $usuario, $contraseña,  $idTipoUsuario, $fechaSubida);
                                            if(count($resultado2) == 0)                                  
                                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE PUDO INGRESAR AL NUEVO USUARIO, POR FAVOR INTENTE MÁS TARDE</div>';
                                            else
                                            {
                                                $idNuevoUsuario = $resultado2[0]['idUsuario'];
                                                $idUsuarioEncriptado2 = $objMetodos->encriptar($idNuevoUsuario);
                                                $resultado3 = $objUsuario->ModificarIdUsuarioEnAsignarModulo($idUsuarioM, $idNuevoUsuario);
                                                
                                                if($idUsuario == $idUsuarioM)
                                                {
                                                    $sesionUsuario->offsetSet('idUsuario',$idNuevoUsuario);
                                                    $tablaUsuario = $this->CargarTablaUsuarioAction($idNuevoUsuario, $this->dbAdapter,$resultado2, $im, $jm);

                                                }
                                                else
                                                    $tablaUsuario = $this->CargarTablaUsuarioAction($idUsuario, $this->dbAdapter,$resultado2, $im, $jm);
                                                
                                                 $mensaje = '<div class="alert alert-success text-center" role="alert">USUARIO MODIFICADO CORRECTAMENTE</div>';
                                                 $validar = TRUE;
                                                 
                                                 return new JsonModel(array('tabla'=>$tablaUsuario,'idUsuario'=>$idUsuarioEncriptado2,'jm'=>$jm,'im'=>$im,'mensaje'=>$mensaje,'validar'=>$validar));

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

    
    public function modificarestadousuarioAction()
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
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 7, 1);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS PARA MODIFICAR EN ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{               
                        $objMetodos = new Metodos();
                        $objUsuario = new Usuario($this->dbAdapter);                        
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idUsuarioEncriptadoM = $post['idUsuarioEncriptadoME'];
                        $im = $post['ime'];
                        $jm = $post['jme'];

                        $idIglesia = $sesionUsuario->offsetGet('idIglesia');
                        
                        
                        if($idIglesia == NULL || $idIglesia == ""){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA IGLESIA</div>';
                        }else{   
                            
                            $idUsuarioM= $objMetodos->desencriptar($idUsuarioEncriptadoM);
                            $listaUsuario = $objUsuario->FiltrarUsuario($idUsuarioM);
                            if(count($listaUsuario)>0)
                            {
                                if($idUsuario == $idUsuarioM)
                                {
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">UD. NO PUEDE AUTO-DESHABILITARSE. CONTÁCTESE CON EL ADMINISTRADOR.</div>';
                                }
                                else{
                                    $estadoUsuario = 0;
                                    if($listaUsuario[0]['estadoUsuario']==0)
                                    $estadoUsuario=1;
                                    
                                    $resultado = $objUsuario->ModificarEstadoUsuario($idUsuarioM, $estadoUsuario, $listaUsuario[0]['ultimoModificado']);    
                                    if(count($resultado) == 0){                                    
                                       $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE MODIFICÓ, POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{
                                        $tablaUsuario = $this->CargarTablaUsuarioAction($idUsuario, $this->dbAdapter,$resultado, $im, $jm);

                                        $mensaje = '<div class="alert alert-success text-center" role="alert">USUARIO MODIFICADO CORRECTAMENTE</div>';
                                        $validar = TRUE;

                                        return new JsonModel(array( 'tabla'=>$tablaUsuario,'idUsuario'=>$idUsuarioEncriptadoM,'jm'=>$jm,'im'=>$im,'mensaje'=>$mensaje,'validar'=>$validar));
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
    
    public function filtrarusuarioporidentificacionAction()
    {
        $this->layout("layout/administrador");
        $mensaje = '<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>';
        $validar = FALSE;
        $sesionUsuario = new Container('sesionparroquia');
        if(!$sesionUsuario->offsetExists('idUsuario')){
            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO HA INICIADO SESIÓN POR FAVOR RECARGUE LA PÁGINA</div>';
        }
        else{
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $idUsuario = $sesionUsuario->offsetGet('idUsuario');
            $objAsignarModulo = new AsignarModulo($this->dbAdapter);
            $AsignarModulo = $objAsignarModulo->FiltrarModuloPorIdentificadorYUsuario($idUsuario, 7);
            if (count($AsignarModulo)==0)
                $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PERMISOS PARA ESTE MÓDULO</div>';
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 7, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objUsuario = new Usuario($this->dbAdapter);
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
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE UNA PERSONA CON LA IDENTIFICACIÓN '.$identificacion.'. PRIMERO DEBE REGISTRARLA EN EL MÓDULO PERSONAS.</div>';
                            }else{ 
                                $idPersona= $listaPersona[0]['idPersona'];
                                $listaUsuario = $objUsuario->FiltrarUnUsuarioPorPersona($idPersona);
                                if(count($listaUsuario) > 0){
                                    if($listaUsuario[0]['estadoUsuario']==0)
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN USUARIO CON LA IDENTIFICACIÓN '.$identificacion.'. LO PUEDE HABILITAR EN LA TABLA DE ABAJO</div>';
                                    else
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">YA EXISTE UN USUARIO CON LA IDENTIFICACIÓN '.$identificacion.'. Y SE ENCUENTRA HABILITADO</div>';
                                }else{
                                    $idPersonaEncriptado = $objMetodos->encriptar($idPersona);
                                   
                                    $nombres = $listaPersona[0]['primerNombre'].' '.$listaPersona[0]['segundoNombre'];
                                    $apellidos = $listaPersona[0]['primerApellido'].' '.$listaPersona[0]['segundoApellido'];
                                    $botonGuardar = '<button data-loading-text="GUARDANDO..." id="btnGuardarUsuario" type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i>GUARDAR</button>';
                                    
                                    $objTipoUsuario = new TipoUsuario($this->dbAdapter);
                                    $optionTipoUsuario = '<option value="0">SELECCIONE UN TIPO DE USUARIO</option>';
                                    foreach ($objTipoUsuario->ObtenerTiposUsuario() as $valueTipos) {
                                        $idTipoUsuarioEncriptado = $objMetodos->encriptar($valueTipos['idTipoUsuario']);
                                        $optionTipoUsuario = $optionTipoUsuario.'<option value="'.$idTipoUsuarioEncriptado.'">'.$valueTipos['descripcionTipoUsuario'].'</option>';
                                    }
                            
                                    
                                    $tabla = '<div><h4>COMPLETE EL FORMULARIO DE INGRESO</h4><br></div>
                                        <input type="hidden" id="idPersonaEncriptado" name="idPersonaEncriptado" value="'.$idPersonaEncriptado.'">
                                        <div class="table-responsive"><table class="table">
                                        <thead> 
                                            <tr>
                                                <th><label for="nombres">NOMBRES</label></th>
                                                <td>'.$nombres.'</td>
                                            </tr>
                                            <tr>
                                                <th><label for="apellidos">APELLIDOS</label></th>
                                                <td>'.$apellidos.'</td>
                                            </tr>
                                                <th><label for="nombreUsuarioN">USUARIO</label></th>
                                                <td><input maxlength="20" autocomplete="off" type="text" id="nombreUsuarioN" name="nombreUsuarioN" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <th><label for="contrasenaUsuarioN">CONTRASEÑA</label></th>
                                                <td><input type="password" maxlength="20" autocomplete="off" type="text" id="contrasenaUsuarioN" name="contrasenaUsuarioN" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <th><label for="contrasenaUsuarioN2">REPETIR CONTRASEÑA</label></th>
                                                <td><input type="password"  maxlength="20" autocomplete="off" type="text" id="contrasenaUsuarioN2" name="contrasenaUsuarioN2" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <th><label for="selectTipoUsuarioN">TIPO DE USUARIO</label></th>
                                                <td>                                                  
                                                    <select id="selectTipoUsuarioN" name="selectTipoUsuarioN" class="form-control">'.$optionTipoUsuario.'</select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">'.$botonGuardar.'</td>
                                            </tr>
                                        </thead>
                                    </table></div>';
                                    
                                    
                                    $mensaje ='';
                                    $validar = TRUE;
                                    return new JsonModel(array('tabla'=>$tabla,'mensaje'=>$mensaje,'validar'=>$validar,'idPersonaEncriptado'=>$idPersonaEncriptado));
                                }
                            }
                        }
                    }
                    
                }
                
            }
        }
        return new JsonModel(array('mensaje'=>$mensaje,'validar'=>$validar));
    }
    
   public function ingresarusuarioAction()
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
            else {
                $objMetodosC = new MetodosControladores();
                $validarprivilegio = $objMetodosC->ValidarPrivilegioAction($this->dbAdapter,$idUsuario, 7, 3);
                if ($validarprivilegio==false)
                    $mensaje = '<div class="alert alert-danger text-center" role="alert">USTED NO TIENE PRIVILEGIOS DE INGRESAR DATOS PARA ESTE MÓDULO</div>';
                else{
                    $request=$this->getRequest();
                    if(!$request->isPost()){
                        $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/inicio/inicio');
                    }else{
                        $objMetodos = new Metodos();
                        $objPersona = new Persona($this->dbAdapter);
                        $objUsuario= new Usuario($this->dbAdapter);
                        $objTipoUsuario = new TipoUsuario($this->dbAdapter);
                        $post = array_merge_recursive(
                            $request->getPost()->toArray(),
                            $request->getFiles()->toArray()
                        );
                        $idPersonaEncriptado = trim($post['idPersonaEncriptado']); 
                        $nombreUsuario = trim($post['nombreUsuarioN']);  
                        $contrasena = trim($post['contrasenaUsuarioN']); 
                        $contrasena2 = trim($post['contrasenaUsuarioN2']);
                        $idTipoUsuarioE = $post['selectTipoUsuarioN'];
                                                 

                        if(empty($idPersonaEncriptado) || $idPersonaEncriptado == NULL){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE ENCUENTRA EL ÍNDICE DE LA PERSONA</div>';
                        }else if((empty ($nombreUsuario)) || ((strlen($nombreUsuario) < 5) || (strlen($nombreUsuario) > 20))){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL USUARIO MÍNIMO 5 Y MÁXIMO 20 CARACTERES</div>';
                        }else if((empty ($contrasena)) || ((strlen($contrasena) < 8) || (strlen($contrasena) > 20))){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE LA CONTRASEÑA MÍNIMO 8 Y MÁXIMO 20 CARACTERES</div>';
                        }else if(empty ($contrasena2) || strlen($contrasena2) > 20){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INGRESE EL CAMPO REPETIR CONTRASEÑA MÁXIMO 20 CARACTERES</div>';
                        }else if(strlen ($contrasena) != strlen($contrasena2)){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">LAS CONTRASEÑAS DEBEN SER IGUALES</div>';
                        }else if(empty ($idTipoUsuarioE) ){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">POR FAVOR, SELECCIONE UN TIPO DE USUARIO</div>';
                        }else if(count ($objUsuario->FiltrarUsuarioPorNombreUsuario($nombreUsuario))>0){
                            $mensaje = '<div class="alert alert-danger text-center" role="alert">INTENTE CON OTRO USUARIO PORQUE  YA EXISTE ESTE USUARIO EN EL SISTEMA</div>';
                        }else 
                        { 
                            $idTipoUsuario = $objMetodos->desencriptar($idTipoUsuarioE);    
                            if(count($objTipoUsuario->FiltrarTipoUsuario($idTipoUsuario))==0) 
                                $mensaje = '<div class="alert alert-danger text-center" role="alert">TIPO DE USUARIO NO REGISTRADO EN LA BASE DE DATOS</div>';
                            else{                         
                                $idPersona = $objMetodos->desencriptar($idPersonaEncriptado);
                                $listaPersona = $objPersona->FiltrarPersona($idPersona);
                                if(count($listaPersona) == 0){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">NO EXISTE LA PERSONA</div>';
                                }else if($listaPersona[0]['estadoPersona'] == FALSE){
                                    $mensaje = '<div class="alert alert-danger text-center" role="alert">ESTA PERSONA HA SIDO DESHABILITADA POR LO TANTO NO PUEDE SER UTILIZADA HASTA QUE SEA HABILITADA</div>';
                                }else
                                {  
                                    ini_set('date.timezone','America/Bogota'); 
                                    $hoy = getdate();
                                    $fechaRegistroUsuario = $hoy['year']."-".$hoy['mon']."-".$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];

                                    $resultado = $objUsuario->IngresarUsuario($idPersona, $nombreUsuario, $contrasena, $idTipoUsuario, $fechaRegistroUsuario);
                                    if(count($resultado) == 0){
                                        $mensaje = '<div class="alert alert-danger text-center" role="alert">NO SE INGRESÓ EL USUARIO POR FAVOR INTENTE MÁS TARDE</div>';
                                    }else{
                                        $mensaje = '<div class="alert alert-success text-center" role="alert">USUARIO INGRESADO CORRECTAMENTE</div>';
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
}