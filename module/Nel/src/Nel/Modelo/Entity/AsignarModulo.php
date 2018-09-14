<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class AsignarModulo extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('asignarmodulo', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    
    public function FiltrarModulosPorUsuario($idUsuario){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarModulosPorUsuario('{$idUsuario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
        public function FiltrarAsignarModulo($idAsignarModulo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAsignarModulo('{$idAsignarModulo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarModuloPorIdentificadorYUsuario($idUsuario, $identificadorModulo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarModuloPorIdentificadorYUsuario('{$idUsuario}','{$identificadorModulo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

    public function FiltrarAsignarModuloPorUsuarioYModulo($idUsuario, $idModulo, $estadoAsingarModulo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAsignarModuloPorUsuarioYModulo('{$idUsuario}','{$idModulo}','{$estadoAsingarModulo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function ModificarEstadoEnAsginarModulo($idAsignarModulo, $estadoAsignarModuloActual, $estadoAsignarModuloNuevo){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoEnAsginarModulo('{$idAsignarModulo}','{$estadoAsignarModuloActual}','{$estadoAsignarModuloNuevo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function IngresarAsignarModulo($idUsuario, $idModulo, $fechaAsignacion, $estadoAsingarModulo){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarAsignarModulo('{$idUsuario}','{$idModulo}','{$fechaAsignacion}','{$estadoAsingarModulo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }


}