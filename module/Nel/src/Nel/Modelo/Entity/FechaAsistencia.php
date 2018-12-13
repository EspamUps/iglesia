<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class FechaAsistencia extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('fechaasistencia', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function IngresarFechasAsistencia($idConfigurarCurso, $fechaAsistencia, $estadoFechaAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarFechasAsistencia('{$idConfigurarCurso}','{$fechaAsistencia}','{$estadoFechaAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarFechaAsistenciaPorIdConfCurso($idConfigurarCurso, $estadoFechaAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarFechaAsistenciaPorIdConfCurso('{$idConfigurarCurso}','{$estadoFechaAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarFechaAsistenciaPorFechaAsistencia($idFechaAsistencia, $estadoFechaAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarFechaAsistenciaPorFechaAsistencia('{$idFechaAsistencia}','{$estadoFechaAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarFechaAsistencia($idFechaAsistencia, $estadoFechaAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarFechaAsistencia('{$idFechaAsistencia}','{$estadoFechaAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarFechaAsistenciaPorFecha($fechaAsistencia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarFechaAsistenciaPorFecha('{$fechaAsistencia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    


}