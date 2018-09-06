<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Metodos;
use Nel\Modelo\Entity\Persona;
use Nel\Modelo\Entity\AsignarPrivilegio;
use Nel\Modelo\Entity\AsignarModulo;
use Zend\Crypt\BlockCipher;
use Zend\Db\Adapter\Adapter;

class MetodosControladores
{
   public $dbAdapter;
     
     
   function ValidarPrivilegioAction($adaptador,$idUsuario, $identificadorModulo, $identificadorPrivilegio)
   {   
       $objAsignarPrivilegio = new AsignarPrivilegio($adaptador);
       $objPrivilegio = $objAsignarPrivilegio->FiltrarPrivilegiosPorU_M_P($idUsuario, $identificadorModulo, $identificadorPrivilegio);
       $validar = false;
       if(count($objPrivilegio)>0)
        $validar=true;
       return $validar;
    }
   
    
    
}