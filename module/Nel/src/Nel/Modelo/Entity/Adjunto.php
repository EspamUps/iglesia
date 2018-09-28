<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Adjunto extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('adjunto', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function IngresarAdjunto($nombreAdjunto, $rutaAdjunto, $fechaRegistro, $estadoAdjunto){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarAdjunto('{$nombreAdjunto}','{$rutaAdjunto}','{$fechaRegistro}','{$estadoAdjunto}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ModificarEstadoAdjunto($idAdjunto, $estadoNuevoAdjunto){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoAdjunto('{$idAdjunto}','{$estadoNuevoAdjunto}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }


}