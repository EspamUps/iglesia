<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class HorarioCurso extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('horariocurso', $adapter, $databaseSchema, $selectResultPrototype);
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
    public function IngresarHorarioCurso($idConfigurarCurso,$idHoraHorario,$estadoHorarioCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarHorarioCurso('{$idConfigurarCurso}','{$idHoraHorario}','{$estadoHorarioCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarHorarioCursoPorHoraHorarioLimit1($idHoraHorario){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarHorarioCursoPorHoraHorarioLimit1('{$idHoraHorario}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
////    
    public function FiltrarHorarioCursoPorConfiguCurso($idConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarHorarioCursoPorConfiguCurso('{$idConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarHorarioCursoPorConfiguCursoDistinctIdentificadorDia($idConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarHorarioCursoPorConfiguCursoDistinctIdentificadorDia('{$idConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
//    public function FiltrarCursoEstado($idCurso,$estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoEstado('{$idCurso}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
////    
//    public function EliminarCurso($idCurso){
//        $resultado = $this->getAdapter()->query("CALL Sp_EliminarCurso('{$idCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    public function ModificarEstadoCurso($idCurso,$estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoCurso('{$idCurso}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
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