<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class AdjuntoMatricula extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('adjuntomatricula', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function IngresarAdjuntoMatricula($idAdjunto, $idMatricula, $estadoAdjuntoMatricula){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarAdjuntoMatricula('{$idAdjunto}','{$idMatricula}','{$estadoAdjuntoMatricula}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    


}