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
    
    public function FiltrarConfigurarCursoPorDocenteLimit1($idDocente){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorDocenteLimit1('{$idDocente}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarConfigurarCursoPorRangoALimit1($idRangoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCursoPorRangoALimit1('{$idRangoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
//    
//    public function ObtenerCursos(){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerCursos()", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    public function IngresarConfigurarCurso($idCurso,$idDocente,$idPeriodo,$idRangoAsistencia,$fechaInicioMatricula,$fechaFinMatricula,$fechaInicio,$fechaFin,$cupos,$precio,$fechaIngreso,$estadoConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarConfigurarCurso('{$idCurso}','{$idDocente}','{$idPeriodo}','{$idRangoAsistencia}','{$fechaInicioMatricula}','{$fechaFinMatricula}','{$fechaInicio}','{$fechaFin}','{$cupos}','{$precio}','{$fechaIngreso}','{$estadoConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    public function FiltrarCursoPorNombre($nombreCurso){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoPorNombre('{$nombreCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    public function FiltrarCurso($idCurso){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCurso('{$idCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
//    public function FiltrarCursoEstado($idCurso,$estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoEstado('{$idCurso}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
//    
    public function EliminarConfigurarCurso($idConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarConfigurarCurso('{$idConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
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