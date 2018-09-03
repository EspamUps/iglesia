<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class DireccionPersona extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('direccionpersona', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    public function ObtenerPersonasPorIglesia($idIglesia){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerPersonasPorIglesia('{$idIglesia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    
    public function FiltrarDireccionPersonaPorPersonaEstado($idPersona,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarDireccionPersonaPorPersonaEstado('{$idPersona}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarDireccionPersonaPorConfigurarParroquiaCantonLimite1($idConfigurarParroquiaCanton){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarDireccionPersonaPorConfigurarPCLimite1('{$idConfigurarParroquiaCanton}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
//    
//    public function FiltrarPersonaPorIdentificacion($identificacion){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPersonaPorIdentificacion('{$identificacion}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
    public function IngresarDireccionPersona($array)
    {
        $idPersona = $array['idPersona'];
        $idConfigurarParroquiaCanton = $array['idConfigurarParroquiaCanton'];
        $direccionPersona = $array['direccionPersona'];
        $referenciaDireccionPersona = $array['referenciaDireccionPersona'];
        $fechaIngresoDireccionPersona = $array['fechaIngresoDireccionPersona'];
        $estadoDireccionPersona = $array['estadoDireccionPersona'];
        $resultado =  $this->getAdapter()->query("CALL Sp_IngresarDireccionPersona('{$idPersona}','{$idConfigurarParroquiaCanton}','{$direccionPersona}','{$referenciaDireccionPersona}','{$fechaIngresoDireccionPersona}','{$estadoDireccionPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
//    public function obtenerPersonas(){
////        $sql="CALL Sp_ObtenerPersonas()";
////        $statement = $this->getAdapter()->createStatement($sql);
//        
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerPersonas()", Adapter::QUERY_MODE_EXECUTE)->toArray();
////        $fila = $statement->execute();
////        $resultado = $fila->current();
//        return $resultado;
//        
//    }









    
//    public  function obtenerUsuarios()
//    {
//        return  $this->select()->toArray();
//    }
//  
//    public function LoginUsuario($correo,$contrasena)
//    {
//        return $this->select(array('correo=?'=>$correo,'contrasena=?'=>$contrasena))->toArray();
//    }
    
//    public function filtrarUsuarioPorCorreo($correo)
//    {
//        return $this->select(array('correo=?'=>$correo))->toArray();
//    }
    
//    public function filtrarPersona($idPersona)
//    {
//        return $this->select(array('idPersona=?'=>$idPersona))->toArray();
//    }
    
//    public function filtrarTiendaActivo($idTienda)
//    {
//        return $this->select(array('idTienda=?'=>$idTienda,'estado=?'=>true))->toArray();
//    }
//    
//    
//    public function filtrarTiendaPorNombreUsuarioActivo($nombreUsuario)
//    {
//        return $this->select(array('nombreUsuario=?'=>$nombreUsuario,'estado=?'=>true))->toArray();
//    }
   
    
    
//    public function filtrarUsuarioPorUsuario($nombreUsuario)
//    {
//        return $this->select(array('nombreUsuario=?'=>$nombreUsuario))->toArray();
//    }
    
//    public function login($nombreUsuario)
//    {
//        return $this->select(array('nombreUsuario=?'=>$nombreUsuario))->toArray();
//    }
    

//    
//    public function filtrarUsuarioPorNombreUsuario($nombreUsuario)
//    {
//        return $this->select(array('nombreUsuario=?'=>$nombreUsuario))->toArray();
//    }
//    
//    public function filtrarUsuarioPorTipo($idTipoUSuario,$idUsuario)
//    {
//        return $this->select(array('idTipoUsuario=?'=>$idTipoUSuario,'idUsuario !=?'=>$idUsuario))->toArray();
//    }
    
//    public function ingresarTienda($array)
//    {
//        $inserted = $this->insert($array);
//        if($inserted)
//        {
//            return  $this->getLastInsertValue();
//        }  else {
//            return 0;
//        }
//    }
//    
//    public function actualizarUsuario($idUsuario, $array)
//    {
//        return (bool) $this->update($array,array('idUsuario=?'=>$idUsuario));
//    }

//    public function eliminarUsuario($idUsuario)
//    {
//        return $this->delete(array('idUsuario=?'=>$idUsuario));
//    }
   
}