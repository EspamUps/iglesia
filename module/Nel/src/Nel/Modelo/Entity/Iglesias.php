<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Iglesias extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('iglesias', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function FiltrarIglesia($idIglesia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarIglesia('{$idIglesia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
}