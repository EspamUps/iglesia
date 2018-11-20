<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Confirmacion extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('confirmacion', $adapter, $databaseSchema, $selectResultPrototype);
    }
    
    public function FiltrarConfirmacionPorBautismo($idBautismo){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfirmacionPorBautismo('{$idBautismo}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    public function ObtenerBautismos(){
//        $resultado = $this->getAdapter()->query("CALL Sp_ObtenerBautismos()", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
//
    public function IngresarConfirmacion($idBautismo, $idSacerdoteConfirmacion,
    $fechaConfirmacion,$idLugarConfirmacion,$numeroConfirmacion,$anoConfirmacion,$tomoConfirmacion,
    $folioConfirmacion,$actaConfirmacion,$fechaInscripcionConfirmacion,$fechaRegistro,
    $estadoConfirmacion){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarConfirmacion('{$idBautismo}','{$idSacerdoteConfirmacion}',
        '{$fechaConfirmacion}','{$idLugarConfirmacion}','{$numeroConfirmacion}','{$anoConfirmacion}','{$tomoConfirmacion}','{$folioConfirmacion}','{$actaConfirmacion}',
        '{$fechaInscripcionConfirmacion}','{$fechaRegistro}','{$estadoConfirmacion}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//    public function FiltrarBautismoPorPersona($idPersona){
//        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarBautismoPorPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
//        return $resultado;
//    }
// 
    
    public function FiltrarConfirmacionPorNumero($numero){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarConfirmacionPorNumero('{$numero}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
//
//


}