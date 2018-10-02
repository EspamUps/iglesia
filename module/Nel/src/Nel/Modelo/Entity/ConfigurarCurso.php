<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class ConfigurarCurso extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('configurarcurso', $adapter, $databaseSchema, $selectResultPrototype);
    }
    public function FiltrarConfigurarCursoPorCursoLimit1($idCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorCursoLimit1('{$idCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarConfigurarCursoPorPeriodoLimit1($idPeriodo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorPeriodoLimit1('{$idPeriodo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarConfigurarCursoPorPeriodo($idPeriodo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorPeriodo('{$idPeriodo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarConfigurarCursoPorDocenteLimit1($idDocente){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorDocenteLimit1('{$idDocente}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarConfigurarCursoPorPeriodoDocente($idPeriodo,$idDocente){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorPeriodoDocente('{$idPeriodo}','{$idDocente}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarConfigurarCursoPorRangoALimit1($idRangoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorRangoALimit1('{$idRangoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
//    
    public function ObtenerConfigurarCurso(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerConfigurarCurso()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function IngresarConfigurarCurso($idCurso,$idDocente,$idPeriodo,$idRangoAsistencia,$fechaInicioMatricula,$fechaFinMatricula,$fechaInicio,$fechaFin,$cupos,$precio,$fechaIngreso,$estadoConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarConfigurarCurso('{$idCurso}','{$idDocente}','{$idPeriodo}','{$idRangoAsistencia}','{$fechaInicioMatricula}','{$fechaFinMatricula}','{$fechaInicio}','{$fechaFin}','{$cupos}','{$precio}','{$fechaIngreso}','{$estadoConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarConfigurarCurso($idConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCurso('{$idConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarConfigurarCursoSiguienteDisponiblesParaMatricula($nivelActual, $fechaActual, $estadoConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoSiguienteDisponiblesParaMatricula('{$nivelActual}','{$fechaActual}','{$estadoConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarConfigurarCursoPorEstado($estadoConfigurarCurso, $fechaActual){
    $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorEstado('{$estadoConfigurarCurso}','{$fechaActual}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
    return $resultado;
    }
    
    public function EliminarConfigurarCurso($idConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarConfigurarCurso('{$idConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

    
     public function ModificarEstadoConfigurarCurso($idConfigurarCurso,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoConfigurarCurso('{$idConfigurarCurso}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
     public function FiltrarListaHorariosPorCursoYFechaActual($fechaActual,$idCurso, $estadoConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarListaHorariosPorCursoYFechaActual('{$fechaActual}','{$idCurso}','{$estadoConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarListaHorariosPorCurso($idCurso, $estadoConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarListaHorariosPorCurso('{$idCurso}','{$estadoConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarListaHorariosPorCursoPorPeriodo($idPeriodo, $idCurso, $estadoConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarListaHorariosPorCursoPorPerido('{$idPeriodo}','{$idCurso}','{$estadoConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
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