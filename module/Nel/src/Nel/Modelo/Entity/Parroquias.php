<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Parroquias extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('parroquias', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function FiltrarParroquia($idParroquia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarParroquia('{$idParroquia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarParroquiaPorNombreParroquia($nombreParroquia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarParroquiaPorNombreParroquia('{$nombreParroquia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function IngresarParroquia($nombreParroquia,$fechaIngreso,$estadoParroquia){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarParroquia('{$nombreParroquia}','{$fechaIngreso}','{$estadoParroquia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
}