 $(function(){
    $("#contendorModalFormFotoTienda").ajaxForm({
        beforeSend: function(){
            $("#progressModal").show();
            $("#mensajeModalFormFotoTienda").html('<h4 class="text-center">ESPERE...</h4>');
            $("#btnGuardarFotoTienda").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){
            $("#progress-barModal").width(percentComplete+'%');
            $("#progresoModal").html(percentComplete+'%');
            var peso = parseInt(total);
            var progresoSubida = parseInt(position);
            var pesoFinal = peso/1048576;
            var progresoFinalSubida = progresoSubida/1048576;
            $("#megasModal").html(Math.round(progresoFinalSubida)+' MB/'+Math.round(pesoFinal)+' MB');
        },
        success: function(data){
            if(data.validar==true){
                limpiarProgressModal();
                limpiarInputFileModal();
                obtenerContactoDir(data.nombreUsuario);
            }
            $("#btnGuardarFotoTienda").button('reset');
            $("#mensajeModalFormFotoTienda").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            limpiarProgressModal();
            $("#btnGuardarFotoTienda").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
});  
 
 
 function limpiarProgressModal(){
    $("#progressModal").hide();
    $("#progress-barModal").width('0%');
    $("#megasModal").html('');
}
 
 function limpiarInputFileModal(){
     limpiarProgressModal();
    var input = $("#fotoTiendaModal");
    input.replaceWith(input.val('').clone(true));
    $("#contenedorVistaPreviaFotoTienda").html('');
    $("#contenedorBtnGuardarFotoTienda").html('');
    setTimeout(function() {$("#mensajeModalFormFotoTienda").html('');},1500);
} 

function vistaPreviaFotoTienda() {
    limpiarProgressModal();
    $("#contenedorVistaPreviaFotoTienda").html('');
    $("#contenedorBtnGuardarFotoTienda").html('');
    var archivos = document.getElementById('fotoTiendaModal').files;
    var navegador = window.URL || window.webkitURL;
    if(archivos.length > 1){
        limpiarInputFileModal();
        $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">SELECCIONE SÓLO UNA FOTO JPEG</div>');
    }else{
        if (!archivos[0].type.match('image/jpeg')) {
            limpiarInputFileModal();
            $("#mensajeModalFormFotoTienda").html('<div class="alert alert-danger text-center" role="alert">LAS IMAGEN DEBE SER FORMATO JPEG/JPG</div>');
        }else{
            var objetoUrl = navegador.createObjectURL(archivos[0]); 
            $("#contenedorVistaPreviaFotoTienda").append('<div class="col-sm-12"><img style="margin:0 auto 0 auto; text-aling:center; height: 200px;" class="img-responsive" src="'+objetoUrl+'"></div>');
            $("#contenedorBtnGuardarFotoTienda").html('<button id="btnGuardarFotoTienda" data-loading-text="GUARDANDO..." type="submit" class="btn btn-danger">GUARDAR</button><button type="button" onclick="limpiarInputFileModal();" class="btn btn-default">CANCELAR</button>');
        }
    }
}