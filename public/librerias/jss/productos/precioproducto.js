
$(function(){
    $("#formContenedorPrecio").ajaxForm({
        beforeSend: function(){
            $("#mensajeModalFormPrecio").html('');
            $("#btnGuardarPrecioProducto").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                filtrarPrecioProducto(data.idProducto);
            }else{
                $("#btnGuardarPrecioProducto").button('reset');
            }
            $("#mensajeModalFormPrecio").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarPrecioProducto").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 



function cargandoPrecio(){
    var url = $("#rutaBase").text();
     $("#formContenedorPrecio").html('');
    $("#precioProductoAnterior").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
}

function filtrarPrecioProducto(vari){
    var cod1 = $("#cod1").text(); 
    var IDT = $("#cod6").text();
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/precioproducto/filtrarprecioproducto',
        type: 'post',
        dataType: 'JSON',
        data: {id:vari,IDT:IDT,cod1:cod1},
        beforeSend: function(){
            $("#mensajeModalFormPrecio").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#precioProductoAnterior").html(data.tabla);
                $("#formContenedorPrecio").html(data.formNuevoPrecioProducto);
            }else{
                $("#precioProductoAnterior").html('');
                 $("#formContenedorPrecio").html('');
            }
            $("#mensajeModalFormPrecio").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
             $("#formContenedorPrecio").html('');
            $("#precioProductoAnterior").html('');
            if(xhr.status === 0){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormPrecio").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}