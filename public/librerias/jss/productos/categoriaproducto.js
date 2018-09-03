function paginarCategoriaProducto()
{
    var menues = $(".panel-heading .panel-title .finalMenu"); 
    menues.click(function() {
       menues.removeAttr("style");
       menues.css({'cursor': 'pointer'});
       $(this).css({'background-color': '#3A5C83', 'color': 'white'});
       $("#nombreCategoria").html('<h2 class="title text-center">'+$(this).text()+'</h2>');
    });
}
function cargando(){
    var url = $("#rutaBase").text();
    $("#contenedorCategoriaProducto").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
}

function filtrarCategoriaProducto(vari){
        var cod1 = $("#cod1").text(); 
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/productos/filtrarcategoriaproducto',
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
                    paginarCategoriaProducto();

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