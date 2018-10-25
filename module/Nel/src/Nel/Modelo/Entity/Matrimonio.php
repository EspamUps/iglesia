<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Matrimonio extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('matrimonio', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    
    public function ObtenerMatrimonios(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerMatrimonios()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    
//    public function ObtenerCursosEstado($estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerCursosEstado('{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    public function IngresarMatrimonio($idEsposo, $idEsposa,$idIglesia,
    $idLugar,$idConfigurarParroquiaCanton,$anoRegistroCivil,$tomoRegistroCivil,$paginaRegistroCivil,
    $numeroRegistroCivil,$anoEcleciastico,$tomoEcleciastico,
    $paginaActaMatrimonial,$actaMatrimonial,$fechaInscripcionRegistroCivil,$fechaInsciripcionEclesiastico,
    $fechaMatrimonio,
    $fechaIngreso,$estadoMatrimonio){
        
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarMatrimonio('{$idEsposo}','{$idEsposa}','{$idIglesia}',
        '{$idLugar}','{$idConfigurarParroquiaCanton}','{$anoRegistroCivil}','{$tomoRegistroCivil}','{$paginaRegistroCivil}','{$numeroRegistroCivil}',
        '{$anoEcleciastico}','{$tomoEcleciastico}','{$paginaActaMatrimonial}','{$actaMatrimonial}','{$fechaInscripcionRegistroCivil}','{$fechaInsciripcionEclesiastico}','{$fechaMatrimonio}','{$fechaIngreso}',
        '{$estadoMatrimonio}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarMatrimonioPorEsposoEsposa($idEsposo,$idEsposa){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatrimonioPorEsposoEsposa('{$idEsposo}','{$idEsposa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarMatrimonioPorEsposo($idEsposo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatrimonioPorEsposo('{$idEsposo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarMatrimonioPorEsposa($idEsposa){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatrimonioPorEsposa('{$idEsposa}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
////    
////    
    public function FiltrarMatrimonioPorNumeroActaMatrimonial($actaMatrimonial){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatrimonioPorNumeroActaMatrimonial('{$actaMatrimonial}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
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