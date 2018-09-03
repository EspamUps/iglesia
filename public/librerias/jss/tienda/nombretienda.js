function validarFormularioNombreTienda(){
        var ok = false;
        if(confirm("RECUERDA QUE AL INGRESAR UN NOMBRE NUEVO, DEBES ESPERAR 15 DÍAS PARA ACTUALIZARLO, DESEAS CONTINUAR?")){
            ok = true;
        }
        return ok;
    }
function limpiarFormIngresarNombreTienda()
{
    $('#formNombreTienda').each(function () {
        this.reset();
    });
    setTimeout(function() {$("#mensajeModalFormNombreTienda").html('');},1500);
}
$(function(){
    $("#formNombreTienda").ajaxForm({
        beforeSend: function(){
            $("#mensajeModalFormNombreTienda").html('');
            $("#btnGuardarNombreTienda").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){
            if(data.validar==true){
               limpiarFormIngresarNombreTienda();
               filtrarNombreTienda(data.id);
            }
            $("#btnGuardarNombreTienda").button('reset');
            $("#mensajeModalFormNombreTienda").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarNombreTienda").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalFormNombreTienda").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET </div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormNombreTienda").html('<div class="alert alert-danger text-center" role="alert">PÁGINA NO ENCONTRADA ERROR. [404]</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormNombreTienda").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormNombreTienda").html('<div class="alert alert-danger text-center" role="alert">Requested JSON parse failed</div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormNombreTienda").html('<div class="alert alert-danger text-center" role="alert">Time out error</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormNombreTienda").html('<div class="alert alert-danger text-center" role="alert">Ajax request aborted</div>');
            }else{
                $("#mensajeModalFormNombreTienda").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
});




function cargandoNombreTienda(){
    var url = $("#rutaBase").text();
    $("#contenedorNombreTienda").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
}

