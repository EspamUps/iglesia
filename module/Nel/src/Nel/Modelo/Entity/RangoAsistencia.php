<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class RangoAsistencia extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('rangoasistencia', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    
    public function ObtenerRangoAsistenciaAcivo(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerRangoAsistenciaAcivo()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
    public function ObtenerRangosAsistencia(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerRangosAsistencia()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function IngresarRangoAsistencia($porcentaje,$fechaIngreso,$estadoRangoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarRangoAsistencia('{$porcentaje}','{$fechaIngreso}','{$estadoRangoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarRangoAsistenciaPorPorcentaje($porcentaje){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarRangoAsistenciaPorPorcentaje('{$porcentaje}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
////    
    public function FiltrarRangoAsistencia($idRangoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarRangoAsistencia('{$idRangoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
//    public function FiltrarCursoEstado($idCurso,$estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoEstado('{$idCurso}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
////    
    public function EliminarRangoAsistencia($idRangoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarRangoAsistencia('{$idRangoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
    public function ModificarEstadoRangoAsistencia($idRangoAsistencia,$estadoRangoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoRangoAsistencia('{$idRangoAsistencia}','{$estadoRangoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
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