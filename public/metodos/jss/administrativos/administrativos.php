<script type="text/javascript">
function cargandoAdministrativos(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

function validarIngresoResponsable(f){
    var _validar = false;
    if(confirm("¿ESTAS SEGURO DE ASIGNAR ESTE CARGO ADMINISTRATIVO A LA PERSONA SELECCIONADA?")){
        _validar = true;
    }
    return _validar;
}

function filtrarAdministrativoPorIdentificacion(event){
    var codigo = event.which || event.keyCode;
    if (codigo==13){
        return false;
    }else{
        var url = $("#rutaBase").text();
        var identificacion = $("#identificacion").val();
        if(identificacion.length < 10){
            $("#mensajeFormIngresoResponsable").html('');
            $("#contenedorDatosResponsable").html('');
        }else{
            $.ajax({
                url : url+'/administrativos2/filtraradministrativoporidentificacion',
                type: 'post',
                dataType: 'JSON',
                data: {identificacion:identificacion},
                beforeSend: function(){

                    $("#mensajeFormIngresoResponsable").html('');
                    cargandoAdministrativos('#contenedorDatosResponsable');
                },
                uploadProgress: function(event,position,total,percentComplete){
                },
                success: function(data){  
                    if(data.validar == true){
                        $("#contenedorDatosResponsable").html(data.tabla);
                    }else{
                        $("#contenedorDatosResponsable").html('');
                    }
                    $("#mensajeFormIngresoResponsable").html(data.mensaje);
                },
                complete: function(){
                },
                error: function(xhr, textStatus, errorThrown) {
                    $("#contenedorDatosResponsable").html('');
                    if(xhr.status === 0){
                        $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                    }else if(xhr.status == 404){
                        $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                    }else if(xhr.status == 500){
                        $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                    }else if(errorThrown === 'parsererror'){
                        $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                    }else if(errorThrown === 'timeout'){
                        $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                    }else if(errorThrown === 'abort'){
                        $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                    }else{
                        $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                    }
                }
            }); 
        }
    }
}

$(function(){
    $("#formIngresoResponsable").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresoResponsable").html('');
            $("#btnGuardarResponsable").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
//                limpiarFormIngresarResponsable();
                obtenerAdministrativos();
            }
            $("#btnGuardarResponsable").button('reset');
            $("#mensajeFormIngresoResponsable").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarResponsable").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresoResponsable").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 


function CargarAdministrativos(){
    var url = $("#rutaBase").text();

    $.ajax({
            url : url+'/administrativos2/obteneradministrativos',
            type: 'post',
            dataType: 'JSON',
            beforeSend: function(){
                $("#mensajeTablaResponsables").html("");
                cargandoAdministrativos('#contenedorTablaResponsables');                
            },
            uploadProgress: function(event,position,total,percentComplete){
                
            },
            success: function(data){ 
                if(data.validar == true)
                {                    
                    $("#contenedorTablaResponsables").html(data.tabla);
                }else{
                      $("#contenedorTablaResponsables").html("");
                }
                $("#mensajeTablaResponsables").html(data.mensaje);
                
                setTimeout(function() {$("#mensajeTablaResponsables").html('');},1500);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) { 
                $("#contenedorTablaResponsables").html("");
                if(xhr.status === 0){
                    $("#mensajeTablaResponsables").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeTablaResponsables").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeTablaResponsables").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeTablaResponsables").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeTablaResponsables").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeTablaResponsables").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeTablaResponsables").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
}


</script>