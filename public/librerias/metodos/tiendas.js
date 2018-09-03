function FiltrarTiendas()
{
    $.ajax({
        url : '<?php echo $this->basePath();?>/rifa/suertes/modificarsuertes',
        type: 'post',
        dataType: 'JSON',
        data: {idRifa:ID, valorSuertes: suertes},
        beforeSend: function(){
            $("#mensajeModalSuertes").html('');
            $("#btnModificarSuertes").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true)
            {
                obtenerSuertesPorRifa(ID);
                cargarCabeceraRifas();
            }
            $("#btnModificarSuertes").button('reset');
            $("#mensajeModalSuertes").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#mensajeModalSuertes").html('');
            $("#btnModificarSuertes").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalSuertes").append('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalSuertes").append('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalSuertes").append('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalSuertes").append('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalSuertes").append('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalSuertes").append('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalSuertes").append('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}