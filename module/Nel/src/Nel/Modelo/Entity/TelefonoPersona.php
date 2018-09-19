<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class TelefonoPersona extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('telefonopersona', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    public function ObtenerPersonasPorIglesia($idIglesia){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerPersonasPorIglesia('{$idIglesia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    
//    public function FiltrarPersona($idPersona){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
    public function ModificarTelefonoPersona($idTelefonoPersona,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarTelefonoPersona('{$idTelefonoPersona}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
     public function FiltrarTelefonoPersonaPorPersonaEstado($idPersona,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarTelefonoPersonaPorPersonaEstado('{$idPersona}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarTelefonoPersonaPorNumeroPersonaEstado($numeroTelefono,$idPersona){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarTelefonoPorNumeroPersona('{$numeroTelefono}','{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
    public function IngresarTelefonoPersona($idPersona,$idTelefono,$fechaRegistro,$estadoTelefonoPersona)
    {
        $resultado =  $this->getAdapter()->query("CALL Sp_IngresarTelefonoPersona('{$idPersona}','{$idTelefono}','{$fechaRegistro}','{$estadoTelefonoPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
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