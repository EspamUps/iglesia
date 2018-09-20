<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Periodos extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('periodo', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    
    public function ObtenerPeriodos(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerPeriodos()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function ObtenerPeriodosEstado($estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerPeriodosEstado('{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
    
    public function IngresarPeriodo($nombrePeriodo,$fechaInicio,$fechaFin,$fechaIngreso,$estadoPeriodo){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarPeriodo('{$nombrePeriodo}','{$fechaInicio}','{$fechaFin}','{$fechaIngreso}','{$estadoPeriodo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarPeriodoPorNombre($nombrePeriodo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPeriodoPorDescripcion('{$nombrePeriodo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
    public function FiltrarPeriodo($idPeriodo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPeriodo('{$idPeriodo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

    
//    
//    public function FiltrarCursoEstado($idCurso,$estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoEstado('{$idCurso}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
//    
    public function EliminarPeriodo($idPeriodo){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarPeriodo('{$idPeriodo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
    public function ModificarEstadoPeriodo($idPeriodo,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoPeriodo('{$idPeriodo}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
//    
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
//    public function EliminarLugarMisa($idLugarMisa){
//        $resultado = $this->getAdapter()->query("CALL Sp_EliminarLugarMisa('{$idLugarMisa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
    
   



}