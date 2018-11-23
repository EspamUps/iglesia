<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Defuncion extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('defuncion', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function ObtenerDefuncion(){
        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerDefuncion()", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarDefuncionPorPersona($idPersona){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarDefuncionPorPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    public function IngresarDefuncion($idPersona, $estadoCivil, $nacionalidad,
    $fechaFallecimiento,$idConfigurarParroquiaCanton, $causaMuerte, $sacramentoDefuncion, 
    $nombrePadre, $nombreMadre,$nombreConyuge,$casadoEcleciastico,
    $anoRegistroCivil, $tomoRegistroCivil, $folioRegistroCivil, $actaRegistroCivil, $fechaInscripcionRegistroCivil, 
    $anoEclesiastico, $tomoEclesiastico, $folioEclesiastico, $actaEclesiastico, $fechaInscripcionEclesiastico,
    $idSacerdote,$idLugar, $fechaRegistro){
        
        
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarDefuncion('{$idPersona}','{$estadoCivil}','{$nacionalidad}',
        '{$fechaFallecimiento}','{$idConfigurarParroquiaCanton}','{$causaMuerte}','{$sacramentoDefuncion}',
        '{$nombrePadre}','{$nombreMadre}','{$nombreConyuge}','{$casadoEcleciastico}',
        '{$anoRegistroCivil}','{$tomoRegistroCivil}','{$folioRegistroCivil}','{$actaRegistroCivil}','{$fechaInscripcionRegistroCivil}',
        '{$anoEclesiastico}','{$tomoEclesiastico}','{$folioEclesiastico}','{$actaEclesiastico}','{$fechaInscripcionEclesiastico}',
        '{$idSacerdote}','{$idLugar}','{$fechaRegistro}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }

}