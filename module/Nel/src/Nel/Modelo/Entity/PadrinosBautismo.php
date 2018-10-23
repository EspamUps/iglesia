<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class PadrinosBautismo extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('padrinosbautismo', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    
//    public function ObtenerPadreBautismoActivo(){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerPadreBautismoActivo()", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
////    
//    public function FiltrarPadreBautismoPorBautismo($idBautismo){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarPadreBautismoPorBautismo('{$idBautismo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    public function IngresarPadrinosBautismo($idTipoPadre,$idPersona,$idBautismo,$fechaIngreso,$estadoPadrinosBautismo){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarPadrinosBautismo('{$idTipoPadre}','{$idPersona}','{$idBautismo}','{$fechaIngreso}','{$estadoPadrinosBautismo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
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