<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class PadrinosMatrimonio extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('padrinosmatrimonio', $adapter, $databaseSchema, $selectResultPrototype);
    }
    

    public function IngresarPadrinosMatrimonio($idMatrimonio, $idPersona,$estadoPadrinosMatrimonio){
        
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarPadrinosMatrimonio('{$idMatrimonio}','{$idPersona}','{$estadoPadrinosMatrimonio}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarPadrinosMatrimonioPorMatrimonio($idMatrimonio){
        
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPadrinosMatrimonioPorMatrimonio('{$idMatrimonio}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }



}