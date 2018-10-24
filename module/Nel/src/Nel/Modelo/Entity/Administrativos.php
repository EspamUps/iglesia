<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Administrativos extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('administrativos', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function ObtenerAdministrativos(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerAdministrativos()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function ObtenerAdministrativosPorCargoAdministrativo($idCargoAdministrativo){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerAdministrativosPorCargoAdministrativo('{$idCargoAdministrativo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    public function FiltrarAdministrativo($idAdministrativo){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAdministrativo('{$idAdministrativo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
    
     public function IngresarAdministrativo($idPersona, $idCargoAdministrativo, $fechaIngreso, $estadoAdministrativo){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarAdministrativo('{$idPersona}','{$idCargoAdministrativo}','{$fechaIngreso}','{$estadoAdministrativo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
     public function FiltrarAdministrativoPorPersona($idPersona){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarAdministrativoPorPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
   
}