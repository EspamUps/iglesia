<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class TestigosMatrimonio extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('testigosmatrimonio', $adapter, $databaseSchema, $selectResultPrototype);
    }
    

    public function IngresarTestigosMatrimonio($idMatrimonio, $idPersona,
    $estadoTestigoMatrimonio){
        
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarTestigosMatrimonio('{$idMatrimonio}','{$idPersona}',
        '{$estadoTestigoMatrimonio}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
  



}