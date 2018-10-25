<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class CargosAdministrativos extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('cargosadministrativos', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function ObtenerCargosAdministrativos(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerCargosAdministrativos()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ObtenerTodosCargosAdministrativos(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerTodosCargosAdministrativos()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarCargoAdministrativo($idCargoAdministrativo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCargoAdministrativo('{$idCargoAdministrativo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
      public function FiltrarCargoAdministrativoPorDescripcion($descripcion){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCargoAdministrativoPorDescripcion('{$descripcion}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
      public function IngresarCargoAdministrativo($descripcion, $identificador, $fechaIngreso, $estado){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarCargoAdministrativo('{$descripcion}','{$identificador}','{$fechaIngreso}', '{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
      public function FiltrarCargoAdministrativoLast(){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCargoAdministrativoLast()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
      public function ModificarEstadoCargoAdministrativo($idCargoAdministrativo, $nuevoEstado){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoCargoAdministrativo('{$idCargoAdministrativo}','{$nuevoEstado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
      public function FiltrarCargoAdministrativoSinImportarEstado($idCargoAdministrativo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCargoAdministrativoSinImportarEstado('{$idCargoAdministrativo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
   
   
}