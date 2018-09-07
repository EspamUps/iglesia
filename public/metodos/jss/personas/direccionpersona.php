<script>
    
function validarModificarDireccion(f){
    var _validar = false;
    if(confirm("¿ESTÁ SEGURO DE MODIFICAR LA DIRECCIÓN?")){
        _validar = true;
    }
   
    return _validar;
}
//    
//function limpiarFormIngresarPersona()
//{
//    $('#formIngresoPersona').each(function () {
//        this.reset();
//    });
//    $("#selectCantones").html('<option value="0">SELECCIONE UN CANTÓN</option>');
//    $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
//    setTimeout(function() {$("#mensajeFormIngresoPersona").html('');},1500);
//}
$(function(){
    $("#contenedorDireccionPersona").ajaxForm({
        beforeSend: function(){
            $("#mensajeDireccionPersona").html('');
            $("#btnModificarDireccion").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            console.log(data.tabla)
            if(data.validar==true){
                FiltrarDireccionPorPersona(data.idpersona, data.i,data.j);
            }
            $("#btnModificarDireccion").button('reset');
            $("#mensajeDireccionPersona").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnModificarDireccion").button('reset');
            if(xhr.status === 0){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

</script>