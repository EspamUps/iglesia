<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class AsignarPrivilegio extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('asignarprivilegio', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    
//    public function FiltrarModulosPorUsuario($idUsuario){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarModulosPorUsuario('{$idUsuario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
      public function FiltrarPrivilegiosPorU_M_P($idUsuario, $identificadorModulo, $identificadorPrivilegio){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPrivilegiosPorU_M_P('{$idUsuario}','{$identificadorModulo}','{$identificadorPrivilegio}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    public function IngresarMisa($descripcionMisa,$fechaIngresoMisa,$estadoMisa){
//        $resultado = $this->getAdapter()->query("CALL Sp_IngresarMisa('{$descripcionMisa}','{$fechaIngresoMisa}','{$estadoMisa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    public function FiltrarMisaPorDescripcion($descripcionMisa){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMisaPorDescripcion('{$descripcionMisa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    
//    
//    public function FiltrarPersona($idPersona){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    public function FiltrarPersonaPorIdentificacion($identificacion){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPersonaPorIdentificacion('{$identificacion}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//
//    
//    public function EliminarLugarMisa($idLugarMisa){
//        $resultado = $this->getAdapter()->query("CALL Sp_EliminarLugarMisa('{$idLugarMisa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
    
   



}