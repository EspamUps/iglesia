$(function(){
    $("#formMoverCategoria").ajaxForm({
        beforeSend: function(){
            $("#mensajeModalFormMoverCategoria").html('');
            $("#btnGuardarMoverCategoria").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){
            if(data.validar==true){
               var IDT = $("#cod6").text(); 
               filtrarCategoriaProducto(data.idT);
               filtrarFormMoverCategoria(data.id);
               setTimeout(function() {$("#mensajeModalFormMoverCategoria").html('');},1500);
            }
            $("#btnGuardarMoverCategoria").button('reset');
            $("#mensajeModalFormMoverCategoria").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarMoverCategoria").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET </div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">PÁGINA NO ENCONTRADA ERROR. [404]</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">Requested JSON parse failed</div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">Time out error</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">Ajax request aborted</div>');
            }else{
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
});








function filtrarFormMoverCategoria(vari){
    var cod3 = $("#cod1").text(); 
    var IDT = $("#cod6").text(); 
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/categoriaproducto/filtrarformmmovercategoria',
        type: 'post',
        dataType: 'JSON',
        data: {id:vari,IDT:IDT,cod3:cod3},
        beforeSend: function(){
            $("#formMoverCategoria").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
            $("#mensajeModalFormMoverCategoria").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#formMoverCategoria").html(data.tabla);
            }else{
                $("#formMoverCategoria").html('');
            }
            $("#mensajeModalFormMoverCategoria").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#formMoverCategoria").html('');
            if(xhr.status === 0){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormMoverCategoria").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}


function deshabilitarCategoria(vari,ID){
    if(confirm('¿DESEAS DESHABILITAR O HABILITAR ESTA CETEGORÍA?')){
        var cod3 = $("#cod3").text(); 
        var IDT = $("#cod6").text(); 
        var url = $("#rutaBase").text();
        var nombreClase = '';
        $.ajax({
            url : url+'/categoriaproducto/deshabilitarcategoriaproducto',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,IDT:IDT,cod3:cod3},
            beforeSend: function(){
                nombreClase = $("#iconDesh"+ID+" i").attr('class');
                $("#iconDesh"+ID).html('<i class="fa fa-spinner"></i>');
                $("#mensaje").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    filtrarCategoriaProducto(data.id);
                }else{
                    $("#iconDesh"+ID).html('<i class="'+nombreClase+'"></i>');
                }
                $("#mensaje").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#iconDesh"+ID).html('<i class="'+nombreClase+'"></i>');
                if(xhr.status === 0){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}


function eliminarCategoria(vari,ID){
    if(confirm('¿DESEAS ELIMINAR ESTA CETEGORÍA?')){
        var cod3 = $("#cod3").text(); 
        var IDT = $("#cod6").text(); 
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/categoriaproducto/eliminarcategoriaproducto',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,IDT:IDT,cod3:cod3},
            beforeSend: function(){
                $("#icon"+ID).html('<i class="fa fa-spinner"></i>');
                $("#mensaje").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    filtrarCategoriaProducto(data.id);
                }else{
                    $("#icon"+ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensaje").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#icon"+ID).html('<i class="fa fa-times"></i>');
                if(xhr.status === 0){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}


function limpiarFormIngresarCategoriaProducto()
{
    $('#formIngresarCategoria').each(function () {
        this.reset();
    });
    setTimeout(function() {$("#mensajeModalForm").html('');},1500);
}

function obtenerPerteneceA(vari){
    limpiarFormIngresarCategoriaProducto();
    $("#mensajeModalForm").html('');
    $("#pertenecea").val(vari);
}


$(function(){
    $("#formIngresarCategoria").ajaxForm({
        beforeSend: function(){
            $("#mensajeModalForm").html('');
            $("#btnGuardarCategoria").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){
            if(data.validar==true){
                var IDT = $("#cod6").text(); 
               filtrarCategoriaProducto(IDT);
               limpiarFormIngresarCategoriaProducto();
            }
            $("#btnGuardarCategoria").button('reset');
            $("#mensajeModalForm").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarCategoria").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET </div>');
            }else if(xhr.status == 404){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">PÁGINA NO ENCONTRADA ERROR. [404]</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">Requested JSON parse failed</div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">Time out error</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">Ajax request aborted</div>');
            }else{
                $("#mensajeModalForm").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
});




function cargando(){
    var url = $("#rutaBase").text();
    $("#contenedorCategoriaProducto").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
}

function filtrarCategoriaProducto(vari){
    var cod1 = $("#cod1").text(); 
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/categoriaproducto/filtrarcategoriaproducto',
        type: 'post',
        dataType: 'JSON',
        data: {id:vari,cod1:cod1},
        beforeSend: function(){
            $("#mensaje").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorCategoriaProducto").html(data.tabla);
            }else{
                $("#contenedorCategoriaProducto").html('');
            }
            $("#mensaje").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorCategoriaProducto").html('');
            if(xhr.status === 0){
                $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}