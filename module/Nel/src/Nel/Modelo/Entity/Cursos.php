<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Cursos extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('crusos', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    
    public function ObtenerCursos(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerCursos()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ObtenerCursosEstado($estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerCursosEstado('{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function IngresarCurso($nombreCurso,$nivelCurso,$fechaIngreso,$estadoCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarCurso('{$nombreCurso}','{$nivelCurso}','{$fechaIngreso}','{$estadoCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarCursoPorNivelEstado($nivelCurso,$estadoCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoPorNivelEstado('{$nivelCurso}','{$estadoCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarCursoPorNombre($nombreCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoPorNombre('{$nombreCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
    public function FiltrarCurso($idCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCurso('{$idCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarCursoSiguiente($nivelActual, $estadoCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoSiguiente('{$nivelActual}','{$estadoCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

    public function EliminarCurso($idCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarCurso('{$idCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ModificarEstadoCurso($idCurso,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_ModificarEstadoCurso('{$idCurso}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    
   



}