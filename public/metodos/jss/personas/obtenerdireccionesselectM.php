<script type="text/javascript">
    function filtrarConfigurarParroquiaCantonPorConfigurarCantonProvinciaM(){
    var url = $("#rutaBase").text();
    var idProvincia = $("#selectProvinciasM").val();
    var idCanton = $("#selectCantonesM").val();
    if(idCanton == "0"){
        $("#mensajeDireccionPersona").html('');
        $("#selectParroquiasM").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
    }else{
        $.ajax({
            url : url+'/configurarparroquiacanton/filtrarconfigurarparroquiacantonporconfigurarcantonprovincia',
            type: 'post',
            dataType: 'JSON',
            data: {idProvincia: idProvincia,idCanton:idCanton},
            beforeSend: function(){
                $("#mensajeDireccionPersona").html('');
                $("#selectParroquiasM").html('<option value="0">CARGANDO...</option>');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#selectParroquiasM").html(data.optionParroquias);
                    
                }else{
                    $("#selectParroquiasM").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
                }
                $("#mensajeDireccionPersona").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#selectParroquiasM").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
                if(xhr.status === 0){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}
function filtrarConfigurarCantonProvinciaPorProvinciaM(){
    var url = $("#rutaBase").text();
    var idProvincia = $("#selectProvinciasM").val();
    if(idProvincia == "0"){
        $("#mensajeDireccionPersona").html('');
        $("#selectCantonesM").html('<option value="0">SELECCIONE UN CANTÓN</option>');
        $("#selectParroquiasM").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
    }else{
        $.ajax({
            url : url+'/configurarcantonprovincia/filtrarconfigurarcantonprovinciaporprovincia',
            type: 'post',
            dataType: 'JSON',
            data: {idProvincia: idProvincia},
            beforeSend: function(){
                $("#mensajeDireccionPersona").html('');
                $("#selectCantonesM").html('<option value="0">CARGANDO...</option>');
                $("#selectParroquiasM").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#selectCantonesM").html(data.optionCantones);
                }else{
                    $("#selectCantonesM").html('<option value="0">SELECCIONE UN CANTÓN</option>');
                    $("#selectParroquiasM").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
                }
                $("#mensajeDireccionPersona").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#selectCantonesM").html('<option value="0">SELECCIONE UN CANTÓN</option>');
                $("#selectParroquiasM").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
                if(xhr.status === 0){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}
</script>