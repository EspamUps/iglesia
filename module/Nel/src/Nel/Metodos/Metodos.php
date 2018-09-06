<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nel\Metodos;
use Zend\Crypt\BlockCipher;
class Metodos 
{
    function comprobarCadenaSoloLetras($cadena){ 
        $permitidos = "aábcdeéfghiíjklmnñoópqrstuúvwxyzAÁBCDEÉFGHIÍJKLMNÑOÓPQRSTUÚVWXYZ0123456789_."; 
        $validar = TRUE;
        for ($i=0; $i<strlen($cadena); $i++){ 
           if (strpos($permitidos, substr($cadena,$i,1))===false){ 
              $validar = false; 
           } 
        } 
        return $validar; 
    }
    function comprobarCadena($cadena){ 
        $permitidos = "aábcdeéfghiíjklmnñoópqrstuúvwxyzAÁBCDEÉFGHIÍJKLMNÑOÓPQRSTUÚVWXYZ0123456789-_@.,;:() "; 
        $validar = TRUE;
        for ($i=0; $i<strlen($cadena); $i++){ 
           if (strpos($permitidos, substr($cadena,$i,1))===false){ 
              $validar = false; 
           } 
        } 
        return $validar; 
    }
    
    
    
    
    
    public function validarIdentificacion($strCedula)
    {
        $validar = TRUE;
        if(is_null($strCedula) || empty($strCedula)){
            $validar = FALSE;
        }else{//caso contrario sigo el proceso
            if(is_numeric($strCedula)){
                $total_caracteres=strlen($strCedula);// se suma el total de caracteres
                if($total_caracteres==10){//compruebo que tenga 10 digitos la cedula
                    $nro_region=substr($strCedula, 0,2);//extraigo los dos primeros caracteres de izq a der
                    if($nro_region>=1 && $nro_region<=24){// compruebo a que region pertenece esta cedula//
                        $ult_digito=substr($strCedula, -1,1);//extraigo el ultimo digito de la cedula
                        //extraigo los valores pares//
                        $valor2=substr($strCedula, 1, 1);
                        $valor4=substr($strCedula, 3, 1);
                        $valor6=substr($strCedula, 5, 1);
                        $valor8=substr($strCedula, 7, 1);
                        $suma_pares=($valor2 + $valor4 + $valor6 + $valor8);
                        //extraigo los valores impares//
                        $valor1=substr($strCedula, 0, 1);
                        $valor1=($valor1 * 2);
                        if($valor1>9){ $valor1=($valor1 - 9); }else{ }
                        $valor3=substr($strCedula, 2, 1);
                        $valor3=($valor3 * 2);
                        if($valor3>9){ $valor3=($valor3 - 9); }else{ }
                        $valor5=substr($strCedula, 4, 1);
                        $valor5=($valor5 * 2);
                        if($valor5>9){ $valor5=($valor5 - 9); }else{ }
                        $valor7=substr($strCedula, 6, 1);
                        $valor7=($valor7 * 2);
                        if($valor7>9){ $valor7=($valor7 - 9); }else{ }
                        $valor9=substr($strCedula, 8, 1);
                        $valor9=($valor9 * 2);
                        if($valor9>9){ $valor9=($valor9 - 9); }else{ }

                        $suma_impares=($valor1 + $valor3 + $valor5 + $valor7 + $valor9);
                        $suma=($suma_pares + $suma_impares);
                        $dis=substr($suma, 0,1);//extraigo el primer numero de la suma
                        $dis=(($dis + 1)* 10);//luego ese numero lo multiplico x 10, consiguiendo asi la decena inmediata superior
                        $digito=($dis - $suma);
                        if($digito==10){ $digito='0'; }else{ }//si la suma nos resulta 10, el decimo digito es cero
                        if ($digito!=$ult_digito){//comparo los digitos final y ultimo
                            $validar = FALSE;
                        }
                    }else{
                        $validar = FALSE;
                    }
                }else if($total_caracteres==13){
                    $validar = TRUE;
                }else{
                    $validar = FALSE;
                }
            }else{
                $validar = FALSE;
            }
        }
        return $validar;
    }
    
    public function obtenerFechaEnLetraSinHora($fecha){
        $dia= $this->conocerDiaSemanaFecha($fecha);
        $num = date("j", strtotime($fecha));
        $anno = date("Y", strtotime($fecha));
        $mes = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
        $mes = $mes[(date('m', strtotime($fecha))*1)-1];
        return $dia.', '.$num.' de '.$mes.' del '.$anno;
    }
    
    public function obtenerFechaEnLetra($fecha){
        $dia= $this->conocerDiaSemanaFecha($fecha);
        $num = date("j", strtotime($fecha));
        $anno = date("Y", strtotime($fecha));
        $hora = date("H:i:s",  strtotime($fecha));
        $mes = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
        $mes = $mes[(date('m', strtotime($fecha))*1)-1];
        return $dia.', '.$num.' de '.$mes.' del '.$anno.' a las '.$hora;
    }
    public function conocerDiaSemanaFecha($fecha) {
        $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
        $dia = $dias[date('w', strtotime($fecha))];
        return $dia;
    }
    
    public function encriptar($cadena)
    {
        $blockCipher = BlockCipher::factory('mcrypt', array(
                'algo' => 'blowfish',
                'mode' => 'cfb',
                'hash' => 'sha512'
            ));
        $blockCipher->setKey('NLLACUCARACHA');
        $result = $blockCipher->encrypt($cadena);
        return $result;
    }
    
    public function desencriptar($cadena)
    {
        $blockCipher = BlockCipher::factory('mcrypt', array(
                'algo' => 'blowfish',
                'mode' => 'cfb',
                'hash' => 'sha512'
            ));
        $blockCipher->setKey('NLLACUCARACHA');
        $result = $blockCipher->decrypt($cadena);
        return $result;
    }
    
    
    public function soloLetras($cadena){ 
        $permitidos = "1234567890aábcdeéfghiíjklmnoópqrstuúvwxyzAÁBCDEÉFGHIÍJKLMNOÓPQRSTUÚVWXYZ-_ "; 
        for ($i=0; $i<strlen($cadena); $i++){ 
            if (strpos($permitidos, substr($cadena,$i,1))===false){ 
                //no es válido; 
                return false; 
            } 
        }  
            //si estoy aqui es que todos los caracteres son validos 
        return true; 
   }
   
   
   public function compararFechas($primera, $segunda)
   {
       $fecha = 0;
        $valoresPrimera = explode ("/", date_format(date_create($primera), 'd/m/Y'));   
        $valoresSegunda = explode ("/", date_format(date_create($segunda), 'd/m/Y')); 

        $diaPrimera    = $valoresPrimera[0];  
        $mesPrimera  = $valoresPrimera[1];  
        $anyoPrimera   = $valoresPrimera[2]; 

        $diaSegunda   = $valoresSegunda[0];  
        $mesSegunda = $valoresSegunda[1];  
        $anyoSegunda  = $valoresSegunda[2];
         
        $diasPrimeraJuliano = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);  
        $diasSegundaJuliano = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);     
        
        if(!checkdate($mesPrimera, $diaPrimera, $anyoPrimera)){
          $fecha = 0;
        }elseif(!checkdate($mesSegunda, $diaSegunda, $anyoSegunda)){
          $fecha = 0;
        }else{
          $fecha =  $diasPrimeraJuliano - $diasSegundaJuliano;
        } 
        return $fecha;

    }
   
   
    
    
}