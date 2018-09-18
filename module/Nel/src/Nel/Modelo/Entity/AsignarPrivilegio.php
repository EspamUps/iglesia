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
    
    public function FiltrarPrivilegiosPorU_M_P($idUsuario, $identificadorModulo, $identificadorPrivilegio){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPrivilegiosPorU_M_P('{$idUsuario}','{$identificadorModulo}','{$identificadorPrivilegio}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function IngresarAsignarPrivilegio($idPrivilegio, $idAsignarModulo, $fechaAsignacion, $estadoAsignacion){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarAsignarPrivilegio('{$idPrivilegio}','{$idAsignarModulo}','{$fechaAsignacion}','{$estadoAsignacion}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ModificarEstadoAsignarPrivilegio($idAsignarPrivilegio, $fechaAsignacion, $estadoAsignacion){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoAsignarPrivilegio('{$idAsignarPrivilegio}','{$fechaAsignacion}','{$estadoAsignacion}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarAsignarPrivilegio($idPrivilegio,$idAsignarModulo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAsignarPrivilegio('{$idPrivilegio}','{$idAsignarModulo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarAsignarPrivilegioPorIdAsignarModulo($idAsignarModulo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAsignarPrivilegioPorIdAsignarModulo('{$idAsignarModulo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

    public function FiltrarAsignarPrivilegioPorId($idAsignarPrivilegio){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAsignarPrivilegioPorId('{$idAsignarPrivilegio}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
}