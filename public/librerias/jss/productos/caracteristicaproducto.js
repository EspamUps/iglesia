function eliminarCaracteristicaProducto(vari,vari2){
    if(confirm('¿DESEAS ELIMINAR ESTA CARACTERÍSTICA?')){
        var cod3 = $("#cod3").text(); 
        var IDT =  $("#cod6").text();
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/caracteristicaproducto/eliminarcaracteristicaproducto',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,id2:vari2,IDT:IDT,cod3:cod3},
            beforeSend: function(){
                $("#mensajeModalFormCaracteristica").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    filtrarCaracteristicaProducto(data.idProducto);
                }
                $("#mensajeModalFormCaracteristica").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                if(xhr.status === 0){
                    $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}

$(function(){
    $("#formContenedorNuevaCaracteristica").ajaxForm({
        beforeSend: function(){
            $("#mensajeModalFormCaracteristica").html('');
            $("#btnGuardarCaracteristicaProducto").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                filtrarCaracteristicaProducto(data.idProducto);
            }else{
                $("#btnGuardarCaracteristicaProducto").button('reset');
            }
            $("#mensajeModalFormCaracteristica").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarCaracteristicaProducto").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 



function cargandoCaracteristica(){
    var url = $("#rutaBase").text();
    $("#formContenedorNuevaCaracteristica").html('');
    $("#contendorModalFormCaracteristica").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
}

function filtrarCaracteristicaProducto(vari){
    var cod1 = $("#cod1").text(); 
    var IDT = $("#cod6").text();
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/caracteristicaproducto/filtrarcaracteristicaproducto',
        type: 'post',
        dataType: 'JSON',
        data: {id:vari,IDT:IDT,cod1:cod1},
        beforeSend: function(){
            $("#mensajeModalFormCaracteristica").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contendorModalFormCaracteristica").html(data.tabla);
                $("#formContenedorNuevaCaracteristica").html(data.formNuevaCaracteristica);
            }else{
                $("#contendorModalFormCaracteristica").html('');
                 $("#formContenedorNuevaCaracteristica").html('');
            }
            $("#mensajeModalFormCaracteristica").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
             $("#formContenedorNuevaCaracteristica").html('');
            $("#contendorModalFormCaracteristica").html('');
            if(xhr.status === 0){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormCaracteristica").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}