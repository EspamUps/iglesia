function guardarDireccion(vari){
    if(confirm('¿DESEAS MODIFICAR LA DIRECCIÓN?')){
        var codMod = $("#codMod").val();
        var idProvincia = $("#provincia").val();
        var idCanton = $("#canton").val();
        var idParroquia = $("#parroquia").val();
        var direccion = $("#direccion2").val();
        var referencia = $("#referencia").val();
        var url = $("#rutaBase").text();
            $.ajax({
                url : url+'/direcciontienda/guardardirecciontienda',
                type: 'post',
                dataType: 'JSON',
                data: {id:vari,codMod:codMod,idProvincia:idProvincia,idCanton:idCanton,idParroquia:idParroquia,direccion:direccion,referencia:referencia},
                beforeSend: function(){
                    $("#btnGuardarDireccion").button('loading');
                    $("#mensajeModalForm").html('');
                },
                uploadProgress: function(event,position,total,percentComplete){
                },
                success: function(data){  
                    if(data.validar == true){
                        var nombreUsuario = $("#cod").text();
                        filtrarDireccionTienda(vari);
                        obtenerContactoDir(nombreUsuario);
                    }else{
                        $("#btnGuardarDireccion").button('reset');
                    }
                    $("#mensajeModalForm").html(data.mensaje);
                },
                complete: function(){
                },
                error: function(xhr, textStatus, errorThrown) {
                    $("#btnGuardarDireccion").button('reset');
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

function filtrarParroquiaCanton(){
        $("#mensajeModalForm").html('');
        var idCanton = $("#canton").val();
        if(idCanton == 0){
            $("#parroquia").html('<option value="0">SELECCIONE UNA PARROQUIA</option>');
        }else{
            var url = $("#rutaBase").text();
            var cod1 = $("#cod1").text();
            $.ajax({
                url : url+'/parroquiacanton/filtrarparroquiacantonporcanton',
                type: 'post',
                dataType: 'JSON',
                data: {idCanton:idCanton,cod1:cod1},
                beforeSend: function(){
                    $("#parroquia").html('<option value="0">Cargando...</option>');
                },
                uploadProgress: function(event,position,total,percentComplete){
                },
                success: function(data){  
                    if(data.validar == true){
                        $("#parroquia").html(data.tabla);
                    }else{
                        $("#parroquia").html('<option value="0">NO EXISTEN CANTONES EN ESTA PROVINCIA</option>');
                    }
                    $("#mensajeModalForm").html(data.mensaje);
                },
                complete: function(){
                },
                error: function(xhr, textStatus, errorThrown) {
                    $("#parroquia").html('<option value="0">SELECCIONE UNA PARROQUIA</option>');
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


function filtrarCantonProvincia(){
        $("#mensajeModalForm").html('');
        $("#parroquia").html('<option value="0">SELECCIONE UNA PARROQUIA</option>');
        var idProvincia = $("#provincia").val();
        
        if(idProvincia == 0){
            $("#canton").html('<option value="0">SELECCIONE UN CANTÓN</option>');
        }else{
            var url = $("#rutaBase").text();
            var cod1 = $("#cod1").text();
            $.ajax({
                url : url+'/cantonprovincia/filtrarcantonprovinciaporprovincia',
                type: 'post',
                dataType: 'JSON',
                data: {idProvincia:idProvincia,cod1:cod1},
                beforeSend: function(){
                    $("#canton").html('<option value="0">Cargando...</option>');
                },
                uploadProgress: function(event,position,total,percentComplete){
                },
                success: function(data){  
                    if(data.validar == true){
                        $("#canton").html(data.tabla);
                    }else{
                        $("#canton").html('<option value="0">NO EXISTEN CANTONES EN ESTA PROVINCIA</option>');
                    }
                    $("#mensajeModalForm").html(data.mensaje);
                },
                complete: function(){
                },
                error: function(xhr, textStatus, errorThrown) {
                    $("#canton").html('<option value="0">SELECCIONE UN CANTÓN</option>');
                    $("#parroquia").html('<option value="0">SELECCIONE UNA PARROQUIA</option>');
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




function cargandoFormModal()
{
    var url = $("#rutaBase").text();
    $("#contendorModalForm").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
}




function filtrarDireccionTienda(vari){
    var cod1 = $("#cod1").text();
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/direcciontienda/filtrardirecciontienda',
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


