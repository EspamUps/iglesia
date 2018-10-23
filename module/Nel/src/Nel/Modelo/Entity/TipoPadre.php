<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class TipoPadre extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('tipopadre', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    
//    public function ObtenerSexoActivo(){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerSexoActivo()", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    public function ObtenerCursosEstado($estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerCursosEstado('{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    public function IngresarCurso($nombreCurso,$fechaIngreso,$estadoCurso){
//        $resultado = $this->getAdapter()->query("CALL Sp_IngresarCurso('{$nombreCurso}','{$fechaIngreso}','{$estadoCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    public function FiltrarCursoPorNombre($nombreCurso){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarCursoPorNombre('{$nombreCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
////    
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
    public function FiltrarTipoPadrePorIdentificador($identificadorTipoPadre){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarTipoPadrePorIdentificador('{$identificadorTipoPadre}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
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