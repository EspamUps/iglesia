<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Usuario extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('usuario', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function LoginUsuario($nombreUsuario){
        $resultado = $this->getAdapter()->query("CALL Sp_LoginUsuario('{$nombreUsuario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
        public function IngresarUsuario($idPersona, $nombreUsuario, $contraseña, $idTipoUsuario, $fechaRegistroUsuario){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarUsuario('{$idPersona}','{$nombreUsuario}','{$contraseña}','{$idTipoUsuario}','{$fechaRegistroUsuario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
        public function ModificarIdUsuarioEnAsignarModulo($idUsuarioAnterior, $idUsuarioNuevo){       
        $resultado =$this->getAdapter()->query("CALL Sp_ModificarIdUsuarioEnAsignarModulo('{$idUsuarioAnterior}','{$idUsuarioNuevo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarUsuario($idUsuario){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarUsuario('{$idUsuario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ModificarUsuario($idUsuario, $contraseña){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarUsuario('{$idUsuario}','{$contraseña}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ModificarEstadoUsuario($idUsuario, $estadoUsuario, $ultimoModificado){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoUsuario('{$idUsuario}','{$estadoUsuario}','{$ultimoModificado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function ObtenerUsuarios(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerUsuarios()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function ModificarEstadoEnAsginarModulo($idUsuario, $estadoUsuarioActual, $estadoUsuarioNuevo){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoEnAsginarModulo('{$idUsuario}','{$estadoUsuarioActual}','{$estadoUsuarioNuevo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
}