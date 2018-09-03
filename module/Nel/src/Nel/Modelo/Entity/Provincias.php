<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Provincias extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('provincias', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function ObtenerProvincias(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerProvincias()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ObtenerProvinciasEstado($estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerProvinciasEstado('{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

    public function FiltrarProvinciaPorNombreProvincia($nombreProvincia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarProvinciaPorNombreProvincia('{$nombreProvincia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarProvincia($idProvincia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarProvincia('{$idProvincia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
    public function IngresarProvincia($nombreProvincia,$fechaIngreso,$estadoProvincia){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarProvincia('{$nombreProvincia}','{$fechaIngreso}','{$estadoProvincia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function EliminarProvincia($idProvincia){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarProvincia('{$idProvincia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
}