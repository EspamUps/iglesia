function eliminarFotoProducto(vari,vari2){
    if(confirm('¿DESEAS ELIMINAR ESTA FOTO?')){
        var cod3 = $("#cod3").text(); 
        var IDT =  $("#cod6").text();
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/fotosproductos/eliminarfotoproducto',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,id2:vari2,IDT:IDT,cod3:cod3},
            beforeSend: function(){
                $("#mensajeModalForm").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    filtrarFotosProducto(data.idProducto);
                }
                $("#mensajeModalForm").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                if(xhr.status === 0){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}


function cambiarFotoPrincipal(vari,vari2){
        var cod3 = $("#cod4").text(); 
        var IDT = $("#cod6").text(); 
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/fotosproductos/cambiarfotoprincipal',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,id2:vari2,IDT:IDT,cod3:cod3},
            beforeSend: function(){
                $("#mensajeModalForm").html('');
                $("#formContenedorNuevaFoto").html('');
                $("#contendorModalForm").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    filtrarFotosProducto(data.idProducto);
                }
                $("#mensajeModalForm").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                if(xhr.status === 0){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
function limpiarProgressModal(){
    $("#progressModal").hide();
    $("#progress-barModal").width('0%');
    $("#megasModal").html('');
}
$(function(){
    $("#formContenedorNuevaFoto").ajaxForm({
        beforeSend: function(){
            $("#progressModal").show();
            $("#mensajeModalForm").html('<h4 class="text-center">ESPERE...</h4>');
            $("#btnGuardarProductoModal").button('loading');
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
                filtrarFotosProducto(data.idProducto);
            }
            $("#btnGuardarProductoModal").button('reset');
            $("#mensajeModalForm").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            limpiarProgressModal();
            $("#btnGuardarProductoModal").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
});   
    
 function limpiarInputFileModal(){
    var input = $("#fotosProductoModal");
    input.replaceWith(input.val('').clone(true));
    $("#contenedorVistaPreviaProductoModal").html('');
    $("#contenedorBtnGuardarProductoModal").html('');
    setTimeout(function() {$("#mensajeModalForm").html('');},1500);
}   

function vistaPreviaProductoModal() {
    limpiarProgressModal();
    $("#contenedorVistaPreviaProductoModal").html('');
    $("#contenedorBtnGuardarProductoModal").html('');
    var archivos = document.getElementById('fotosProductoModal').files;
    var navegador = window.URL || window.webkitURL;
    
    if(archivos.length > 1){
        limpiarInputFileModal();
        $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">SELECCIONE SÓLO UNA FOTO JPEG</div>');
    }else{
        
        for(x=0;x<archivos.length; x++){
            if (!archivos[x].type.match('image/jpeg')) {
                limpiarInputFileModal();
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LAS IMÁGENES DEBEN SER FORMATO JPEG/JPG</div>');
            }else{
                var objetoUrl = navegador.createObjectURL(archivos[x]);
                $("#contenedorVistaPreviaProductoModal").append('<div class="col-sm-9"><img style="margin:0 auto 0 auto; text-aling:center; height: 200px;" class="img-responsive" src="'+objetoUrl+'"></div>');
                $("#contenedorBtnGuardarProductoModal").html('<button id="btnGuardarProductoModal" data-loading-text="GUARDANDO..." type="submit" class="btn btn-danger">GUARDAR</button><button type="button" onclick="limpiarInputFileModal();" class="btn btn-default">CANCELAR</button>');
            }
        }
    }
}





function filtrarFotosProducto(vari){
    var cod1 = $("#cod1").text(); 
    var IDT = $("#cod6").text(); 
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/fotosproductos/filtrarfotosproducto',
        type: 'post',
        dataType: 'JSON',
        data: {id:vari,IDT:IDT,cod1:cod1},
        beforeSend: function(){
            $("#contendorModalForm").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
            $("#formContenedorNuevaFoto").html('');
            $("#mensajeModalForm").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contendorModalForm").html(data.tabla);
                $("#formContenedorNuevaFoto").html(data.formNuevaFoto);
            }else{
                $("#contendorModalForm").html('');
                 $("#formContenedorNuevaFoto").html('');
            }
            $("#mensajeModalForm").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
             $("#formContenedorNuevaFoto").html('');
            $("#contendorModalForm").html('');
            if(xhr.status === 0){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}