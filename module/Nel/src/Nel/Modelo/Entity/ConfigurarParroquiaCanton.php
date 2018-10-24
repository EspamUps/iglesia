<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class ConfigurarParroquiaCanton extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('configurarparroquiacanton', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
//    public function FiltrarConfigurarDirecciones($idConfigurarDirecciones){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarDirecciones('{$idConfigurarDirecciones}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    public function FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvincia($idConfigurarCantonProvincia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvincia('{$idConfigurarCantonProvincia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvinciaParroquia($idConfigurarCantonProvincia,$idParroquia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarParroquiaCantonPorCCPPRR('{$idConfigurarCantonProvincia}','{$idParroquia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FiltrarConfigurarParroquiaCantonPorConfigurarCantonProvinciaLimite1($idConfigurarCantonProvincia){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarParroquiaCantonPorCCPLimite1('{$idConfigurarCantonProvincia}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarConfigurarParroquiaCanton($idConfigurarParroquiaCanton){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfigurarParroquiaCanton('{$idConfigurarParroquiaCanton}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function FitrarDireccionesPorConfigurarParroquiaCanton($idConfigurarParroquiaCanton){
        $resultado = $this->getAdapter()->query("CALL Sp_FitrarDireccionesPorConfigurarParroquiaCanton('{$idConfigurarParroquiaCanton}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function IngresarConfigurarParroquiaCanton($idConfigurarCantonProvincia,$idParroquia,$estado){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarConfigurarParroquiaCanton('{$idConfigurarCantonProvincia}','{$idParroquia}','{$estado}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function EliminarConfigurarParroquiaCanton($idConfigurarParroquiaCanton){
        $resultado = $this->getAdapter()->query("CALL Sp_EliminarConfigurarParroquiaCanton('{$idConfigurarParroquiaCanton}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

    
}