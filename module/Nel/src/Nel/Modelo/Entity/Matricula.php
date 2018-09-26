<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Modelo\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Matricula extends TableGateway
{
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('matricula', $adapter, $databaseSchema, $selectResultPrototype);
    }

    public function FiltrarMatriculaPorConfigurarCursoLimit1($idConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatriculaPorConfigurarCursoLimit1('{$idConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarMatriculaPorPersonaPorEstado($idPersona, $estadoMatricula){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatriculaPorPersonaPorEstado('{$idPersona}','{$estadoMatricula}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarMatriculaPorPersona($idPersona){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatriculaPorPersona('{$idPersona}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function IngresarMatricula($idPersona, $idConfigurarCurso, $fechaMatricula){
        $resultado = $this->getAdapter()->query("CALL Sp_IngresarMatricula('{$idPersona}','{$idConfigurarCurso}','{$fechaMatricula}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarMatriculaPorConfigurarCurso($idConfigurarCurso){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatriculaPorConfigurarCurso('{$idConfigurarCurso}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    public function FiltrarMatriculaPorConfigurarCursoYEstado($idConfigurarCurso, $estadoMatricula){
        $resultado = $this->getAdapter()->query("CALL Sp_FiltrarMatriculaPorConfigurarCursoYEstado('{$idConfigurarCurso}','{$estadoMatricula}')", Adapter::QUERY_MODE_EXECUTE)->toArray();
        return $resultado;
    }
    
    


}