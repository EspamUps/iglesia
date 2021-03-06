<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Asistencia extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('asistencia', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function IngresarAsistenciaHoy($idFechaAsistencia, $idMatricula,$estadoAsistenciaTomada, $fechaTomada,$estadoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarAsistenciaHoy('{$idFechaAsistencia}','{$idMatricula}','{$estadoAsistenciaTomada}','{$fechaTomada}','{$estadoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarAsistenciaPorFechaAsistencia($idFechaAsistencia, $estadoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAsistenciaPorFechaAsistencia('{$idFechaAsistencia}','{$estadoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarAsistencia($idAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAsistencia('{$idAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ActualizarEstadoAsistencia($idAsistencia, $nuevoEstadoAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_ActualizarAsistenciaHoy('{$idAsistencia}','{$nuevoEstadoAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
       public function FiltrarAsistenciaPorMatricula($idMatricula,$estadoFechaAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAsistenciaPorMatricula('{$idMatricula}', '{$estadoFechaAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }


}