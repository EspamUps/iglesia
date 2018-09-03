<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Cantones extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('cantones', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function FiltrarCanton($idCanton){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCanton('{$idCanton}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarCantonPorNombreCanton($nombreCanton){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCantonPorNombreCanton('{$nombreCanton}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function IngresarCanton($nombreCanton,$fechaIngreso,$estadoCanton){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarCanton('{$nombreCanton}','{$fechaIngreso}','{$estadoCanton}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
}