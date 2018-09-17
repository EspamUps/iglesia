<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Privilegios extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('modulos', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function ObtenerPrivilegios(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerPrivilegios()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
   
    
   
}