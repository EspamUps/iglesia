 function guardarContactos(vari){
    if(confirm('¿DESEAS MODIFICAR TUS CONTACTOS?')){
    var codMod = $("#codMod").val();
    var correo = $("#correo").val();
    var telefono = $("#telefono").val();
    var whatsapp = $("#whatsapp").val();
    var faceb = $("#facebook").val();
    var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/contactotienda/guardarcontactos',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,codMod:codMod,correo:correo,telefono:telefono,whatsapp:whatsapp,faceb:faceb},
            beforeSend: function(){
                $("#btnGuardarContacto").button('loading');
                $("#mensajeModalForm").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    var nombreUsuario = $("#cod").text();
                    filtrarContactoTienda(vari);
                    obtenerContactoDir(nombreUsuario);
                }else{
                    $("#btnGuardarContacto").button('reset');
                }
                $("#mensajeModalForm").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#btnGuardarContacto").button('reset');
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
 
 
 function filtrarContactoTienda(vari){
    var cod1 = $("#cod1").text();
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/contactotienda/filtrarcontactotienda',
        type: 'post',
        dataType: 'JSON',
        data: {id:vari,cod1:cod1},
        beforeSend: function(){
            $("#mensajeModalForm").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contendorModalForm").html(data.tabla);
            }else{
                $("#contendorModalForm").html('');
            }
            $("#mensajeModalForm").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
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
 
 
 
