<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class HoraHorario extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('horahorario', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    
//    public function ObtenerDias(){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerDias()", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    public function ObtenerCursosEstado($estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerCursosEstado('{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    public function IngresarHoraHorario($idHorario,$horaInicio,$horaFin,$fechaIngreso,$estadoHoraHorario){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarHoraHorario('{$idHorario}','{$horaInicio}','{$horaFin}','{$fechaIngreso}','{$estadoHoraHorario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarHoraHorarioPorHorario($idHorario){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarHoraHorarioPorHorario('{$idHorario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
////    
    public function FiltrarHoraHorario($idHoraHorario){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarHoraHorario('{$idHoraHorario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
    public function FiltrarChoqueHorasHoraHorario($idHoraHorario,$idHorario,$horaInicio,$horaFin,$estadoHoraHorario){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarChoqueHorasHoraHorario('{$idHoraHorario}','{$idHorario}','{$horaInicio}','{$horaFin}','{$estadoHoraHorario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarHoraHorarioPorHorarioPorHoras($idHorario,$horaInicio,$horaFin){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarHoraHorarioPorHorarioPorHoras('{$idHorario}','{$horaInicio}','{$horaFin}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
//    
////    
    public function EliminarHoraHorario($idHoraHorario){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarHoraHorario('{$idHoraHorario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
    public function ModificarEstadoHoraHorario($idHoraHorario,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoHoraHorario('{$idHoraHorario}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
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