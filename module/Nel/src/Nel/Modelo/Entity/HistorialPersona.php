<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class HistorialPersona extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('historialpersona', $adapter, $databaseSchema, $selectResultPrototype);
    }

    public function FiltrarHistorialPersonaPorPersona($idPersona){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPersonaHistorialPorPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
 
   
}