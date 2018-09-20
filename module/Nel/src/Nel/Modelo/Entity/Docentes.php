<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Docentes extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('docentes', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    
    public function ObtenerDocentesEstado($estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerDocentesEstado('{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ObtenerDocentes(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerDocentes()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarDocente($idDocente){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarDocente('{$idDocente}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

    
//    
    public function FiltrarDocentePorPersona($idPersona){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarDocentePorPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function IngresarDocente($idPersona,$fechaIngreso, $estado){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarDocente('{$idPersona}','{$fechaIngreso}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function EliminarDocente($idDocente){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarDocente('{$idDocente}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

   
}