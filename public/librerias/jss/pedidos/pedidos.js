
 
 
 $(function(){
    $("#formFormSubirDocumentoEnvio").ajaxForm({
        beforeSend: function(){
            $(".progress").show();
            $("#mensajeModalFormSubirDocumentoEnvio").html('<h4 class="text-center">ESPERE...</h4>');
            $("#btnGuardarDocumento").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){
            $(".progress-bar").width(percentComplete+'%');
            $("#progreso").html(percentComplete+'%');
            var peso = parseInt(total);
            var progresoSubida = parseInt(position);
            var pesoFinal = peso/1048576;
            var progresoFinalSubida = progresoSubida/1048576;
            $("#megas").html(Math.round(progresoFinalSubida)+' MB/'+Math.round(pesoFinal)+' MB');
        },
        success: function(data){
            if(data.validar==true){
                limpiarProgressModal();
                limpiarInputFileModal();
                filtrarDocumentoEnvio(data.idCabeceraPedido,data.iCabecera);
                var id = $("#cod7").text();
                filtrarpedidos(id);
            }
            $("#btnGuardarDocumento").button('reset');
            $("#mensajeModalFormSubirDocumentoEnvio").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            limpiarProgressModal();
            $("#btnGuardarDocumento").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
});
 
 
 function limpiarProgressModal(){
    $(".progress").hide();
    $(".progress-bar").width('0%');
    $("#megas").html('');
}   
    
 function limpiarInputFileModal(){
    var input = $("#formFormSubirDocumentoEnvio");
    input.replaceWith(input.val('').clone(true));
    $("#contenedorVistaPreviaDocumentoEnvio").html('');
    $("#contenedorBtnGuardarDocumentoEnvio").html('');
    setTimeout(function() {$("#mensajeModalFormSubirDocumentoEnvio").html('');},1500);
}       
    
function vistaPreviaDocumentoEnvio() {
    limpiarProgressModal();
    $("#contenedorVistaPreviaDocumentoEnvio").html('');
    $("#contenedorBtnGuardarDocumentoEnvio").html('');
    var archivos = document.getElementById('fotoDocumentoEnvio').files;
    var navegador = window.URL || window.webkitURL;
    
    if(archivos.length > 1){
        limpiarInputFileModal();
        $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">SELECCIONE SÓLO UNA FOTO JPEG</div>');
    }else{
        
        for(x=0;x<archivos.length; x++){
            if (!archivos[x].type.match('image/jpeg') && !archivos[x].type.match('image/png')) {
                limpiarInputFileModal();
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">LAS IMÁGENES DEBEN SER FORMATO JPEG/JPG/PNG</div>');
            }else{
                var objetoUrl = navegador.createObjectURL(archivos[x]);
                $("#contenedorVistaPreviaDocumentoEnvio").append('<img style="margin:0 auto 0 auto; text-aling:center; height: 200px;" class="img-responsive" src="'+objetoUrl+'">');
                $("#contenedorBtnGuardarDocumentoEnvio").html('<button id="btnGuardarDocumento" data-loading-text="GUARDANDO..." type="submit" class="btn btn-danger">GUARDAR</button><button type="button" onclick="limpiarInputFileModal();" class="btn btn-default">CANCELAR</button>');
            }
        }
    }
}

function filtrarDocumentoEnvio(vari,vari2){
    var cod2 = $("#cod2").text(); 
    var rutaBase = $("#rutaBase").text();
    $.ajax({
        url : rutaBase+'/cabecerapedido/filtrardocumentoenvio',
        type: 'post',
        dataType: 'JSON',
        data: {vari:vari,vari2:vari2,cod2:cod2},
        beforeSend: function(){
            $("#mensajeModalFormSubirDocumentoEnvio").html('');
            $("#formFormSubirDocumentoEnvio").html('<div class="text-center"><img style="width: 20%;" src="'+rutaBase+'/public/images/otras/cargando.gif" /></div>')
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#formFormSubirDocumentoEnvio").html(data.tabla);
            }
            $("#mensajeModalFormSubirDocumentoEnvio").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#formFormSubirDocumentoEnvio").html('');
            if(xhr.status === 0){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormSubirDocumentoEnvio").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });
}




function filtrarDocumentoDepostio(vari,vari2){
    var cod2 = $("#cod2").text(); 
    var rutaBase = $("#rutaBase").text();
    $.ajax({
        url : rutaBase+'/cabecerapedido/filtrardocumentodeposito',
        type: 'post',
        dataType: 'JSON',
        data: {vari:vari,vari2:vari2,cod2:cod2},
        beforeSend: function(){
            $("#mensajeModalDocumentoDeposito").html('');
            $("#contendorModalDocumentoDeposito").html('<div class="text-center"><img style="width: 20%;" src="'+rutaBase+'/public/images/otras/cargando.gif" /></div>')
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contendorModalDocumentoDeposito").html(data.tabla);
            }
            $("#mensajeModalDocumentoDeposito").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contendorModalDocumentoDeposito").html('');
            if(xhr.status === 0){
                $("#mensajeModalDocumentoDeposito").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalDocumentoDeposito").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalDocumentoDeposito").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalDocumentoDeposito").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalDocumentoDeposito").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalDocumentoDeposito").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalDocumentoDeposito").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });
}


function filtrarpedidos(vari)
{
    var tipoPedido = $("#tiposPedidos").val();
    var cod2 = $("#cod2").text(); 
    var rutaBase = $("#rutaBase").text();
    $.ajax({
        url : rutaBase+'/pedidos/filtrarpedidos',
        type: 'post',
        dataType: 'JSON',
        data: {vari:vari,tipoPedido:tipoPedido,cod2:cod2},
        beforeSend: function(){
            $("#mensajeTablaPedidos").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaPedidos").html(data.tabla);
            }else{
                $("#contenedorTablaPedidos").html('');
            }
            $("#mensajeTablaPedidos").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaPedidos").html('');
            if(xhr.status === 0){
                $("#mensajeTablaPedidos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaPedidos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaPedidos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaPedidos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaPedidos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaPedidos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaPedidos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });
}

function cargandoPedidos(){
    var rutaBase = $("#rutaBase").text();
    $("#contenedorTablaPedidos").html('<div class="text-center"><img style="width: 20%;" src="'+rutaBase+'/public/images/otras/cargando.gif" /></div>')
}  