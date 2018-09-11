<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class ConfigurarMisa extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('configurarmisa', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    
    public function FiltrarConfigurarMisaPorSacerdoteLimite1($idSacerdote){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfiMisaPorSacerdoteLimit1('{$idSacerdote}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarConfigurarMisaPorFechaHoraSacerdote($fechaMisa,$idSacerdote,$horaInicio,$horaFin){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarMisaPorFechaHoraSacerdote('{$fechaMisa}','{$idSacerdote}','{$horaInicio}','{$horaFin}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarConfigurarMisaPorFechaHoraLugar($fechaMisa,$idLugarMisa,$horaInicio,$horaFin){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarMisaPorFechaHoraLugar('{$fechaMisa}','{$idLugarMisa}','{$horaInicio}','{$horaFin}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function IngresarConfigurarMisa($idMisa,$idSacerdote,$idLugarMisa,$descripcionMisa,$fechaMisa,$horaInicio,$horaFin,$fechaRegistro,$valorMisa,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarConfigurarMisa('{$idMisa}','{$idSacerdote}','{$idLugarMisa}','{$descripcionMisa}','{$fechaMisa}','{$horaInicio}','{$horaFin}','{$fechaRegistro}','{$valorMisa}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
//    
//    
//     public function FiltrarConfigurarCantonProvincia($idConfigurarCantonProvincia){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCantonProvincia('{$idConfigurarCantonProvincia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    public function FiltrarConfigurarCantonProvinciaPorProvincia($idProvincia,$estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCantonProvinciaPorProvincia('{$idProvincia}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//
//     public function FiltrarConfigurarCantonProvinciaPorProvinciaCanton($idProvincia,$idCanton,$estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarCantonProvinciaPorProvinciaCanton('{$idProvincia}','{$idCanton}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//     public function IngresarConfigurarCantonProvincia($idProvincia,$idCanton,$estado){
//        $resultado = $this->getAdapter()->query("CALL Sp_IngresarConfigurarCantonProvincia('{$idProvincia}','{$idCanton}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//    
//    
//    public function EliminarConfigurarCantonProvincia($idConfigurarCantonProvincia){
//        $resultado = $this->getAdapter()->query("CALL Sp_EliminarConfigurarCantonProvincia('{$idConfigurarCantonProvincia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
}