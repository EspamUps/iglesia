<script>
function filtrarUsuarioPorIdentificacion(){
    var url = $("#rutaBase").text();
    var identificacion = $("#identificacion").val();
    if(identificacion.length < 10){
        $("#mensajeFormIngresoUsuario").html('');
        $("#contenedorDatosPersona").html('');
    }else{
        $.ajax({
            url : url+'/usuario/filtrarusuarioporidentificacion',
            type: 'post',
            dataType: 'JSON',
            data: {identificacion:identificacion},
            beforeSend: function(){
                $("#mensajeFormIngresoUsuario").html('');
                $("#idPersonaEncriptado").val('0');
                cargandoUsuarios('#mensajeFormIngresoUsuario');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){ 
                if(data.validar == true){
                  $("#contenedorDatosPersona").html(data.tabla);
                  $("#idPersonaEncriptado").val(data.idPersonaEncriptado);
                    
                }else{
                    $("#contenedorDatosPersona").html('');
                    $("#idPersonaEncriptado").val('0');
                }
                $("#mensajeFormIngresoUsuario").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#idPersonaEncriptado").val('0');
                $("#contenedorDatosPersona").html('');
                if(xhr.status === 0){
                    $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}
function cargandoUsuarios(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

function validarIngresoUsuario(f){
    var _validar = false;
    if(confirm("¿ESTAS SEGURO DE GUARDAR A ESTE USUARIO?")){
        _validar = true;
    }
    return _validar;
}

function limpiarFormIngresarUsuarios()
{
    $('#formIngresoUsuario').each(function () {
        this.reset();
    });
    $("#contenedorDatosPersona").html('');
    setTimeout(function() {$("#mensajeFormIngresoUsuario").html('');},1500);
}


$(function(){
    $("#formIngresoUsuario").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresoUsuario").html('');
            $("#btnGuardarUsuario").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarUsuarios();
                obtenerUsuarios();
            }
            $("#btnGuardarUsuario").button('reset');
            $("#mensajeFormIngresoUsuario").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarUsuario").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresoUsuario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

   

</script>