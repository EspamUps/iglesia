<script>
function filtrarSacerdotePorIdentificacion(){
    var url = $("#rutaBase").text();
    var identificacion = $("#identificacion").val();
    if(identificacion.length < 10){
        $("#mensajeContenedorDatos").html('');
        $("#contenedorDatosSacerdote").html('');
    }else{
        $.ajax({
            url : url+'/configurarmisa/filtrarsacerdoteporidentificacion',
            type: 'post',
            dataType: 'JSON',
            data: {identificacion:identificacion},
            beforeSend: function(){
                
                $("#mensajeContenedorDatos").html('');
                $("#idSacerdoteEncriptado").val('0');
                cargandoSacerdotes('#contenedorDatosSacerdote');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#contenedorDatosSacerdote").html(data.tabla);
                    $("#idSacerdoteEncriptado").val(data.idSacerdoteEncriptado);
                    
                }else{
                    $("#contenedorDatosSacerdote").html('');
                    $("#idSacerdoteEncriptado").val('0');
                }
                $("#mensajeContenedorDatos").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#idSacerdoteEncriptado").val('0');
                $("#contenedorDatosSacerdote").html('');
                if(xhr.status === 0){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}
function cargandoSacerdotes(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

</script>