function validarFormularioDescripcion(){
        var ok = false;
        if(confirm("RECUERDA QUE AL INGRESAR UNA DESCRIPCIÓN NUEVA, DEBES ESPERAR 15 DÍAS PARA ACTUALIZARLA, DESEAS CONTINUAR?")){
            ok = true;
        }
        return ok;
    }

function limpiarFormIngresarDescripcionTienda()
{
    $('#formDescripcionTienda').each(function () {
        this.reset();
    });
    setTimeout(function() {$("#mensajeModalFormDescripcionTienda").html('');},1500);
}

$(function(){
    $("#formDescripcionTienda").ajaxForm({
        beforeSend: function(){
            $("#mensajeModalFormDescripcionTienda").html('');
            $("#btnGuardarDescripcionTienda").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){
            if(data.validar==true){
                
               limpiarFormIngresarDescripcionTienda();
               filtrarNombreTienda(data.id);
            }
            $("#btnGuardarDescripcionTienda").button('reset');
            $("#mensajeModalFormDescripcionTienda").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarDescripcionTienda").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalFormDescripcionTienda").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET </div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormDescripcionTienda").html('<div class="alert alert-danger text-center" role="alert">PÁGINA NO ENCONTRADA ERROR. [404]</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormDescripcionTienda").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormDescripcionTienda").html('<div class="alert alert-danger text-center" role="alert">Requested JSON parse failed</div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormDescripcionTienda").html('<div class="alert alert-danger text-center" role="alert">Time out error</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormDescripcionTienda").html('<div class="alert alert-danger text-center" role="alert">Ajax request aborted</div>');
            }else{
                $("#mensajeModalFormDescripcionTienda").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
});