<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class LugaresMisa extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('lugaresmisa', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    
    public function ObtenerObtenerLugaresMisa(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerLugaresMisa()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function IngresarLugaresMisa($nombreLugar,$fechaIngresoLugarMisa,$estadoLugarMisa){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarLugaresMisa('{$nombreLugar}','{$fechaIngresoLugarMisa}','{$estadoLugarMisa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarLugaresMisaPorNombreCoincidencia($nombreLugar){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarLugaresMisaPorNombreCoincidencia('{$nombreLugar}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarLugaresMisa($idLugarMisa){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarLugaresMisa('{$idLugarMisa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
//    
//    
//    public function FiltrarPersona($idPersona){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    public function FiltrarPersonaPorIdentificacion($identificacion){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPersonaPorIdentificacion('{$identificacion}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//
//    
    public function EliminarLugarMisa($idLugarMisa){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarLugaresMisa('{$idLugarMisa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
   



}