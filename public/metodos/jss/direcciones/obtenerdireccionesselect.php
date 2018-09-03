<script type="text/javascript">
    function filtrarConfigurarParroquiaCantonPorConfigurarCantonProvincia(){
    var url = $("#rutaBase").text();
    var idProvincia = $("#selectProvincias").val();
    var idCanton = $("#selectCantones").val();
    if(idCanton == "0"){
        $("#mensajeFormIngresoPersona").html('');
        $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
    }else{
        $.ajax({
            url : url+'/configurarparroquiacanton/filtrarconfigurarparroquiacantonporconfigurarcantonprovincia',
            type: 'post',
            dataType: 'JSON',
            data: {idProvincia: idProvincia,idCanton:idCanton},
            beforeSend: function(){
                $("#mensajeFormIngresoPersona").html('');
                $("#selectParroquias").html('<option value="0">CARGANDO...</option>');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#selectParroquias").html(data.optionParroquias);
                    
                }else{
                    $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
                }
                $("#mensajeFormIngresoPersona").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
                if(xhr.status === 0){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}
function filtrarConfigurarCantonProvinciaPorProvincia(){
    var url = $("#rutaBase").text();
    var idProvincia = $("#selectProvincias").val();
    if(idProvincia == "0"){
        $("#mensajeFormIngresoPersona").html('');
        $("#selectCantones").html('<option value="0">SELECCIONE UN CANTÓN</option>');
        $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
    }else{
        $.ajax({
            url : url+'/configurarcantonprovincia/filtrarconfigurarcantonprovinciaporprovincia',
            type: 'post',
            dataType: 'JSON',
            data: {idProvincia: idProvincia},
            beforeSend: function(){
                $("#mensajeFormIngresoPersona").html('');
                $("#selectCantones").html('<option value="0">CARGANDO...</option>');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#selectCantones").html(data.optionCantones);
                }else{
                    $("#selectCantones").html('<option value="0">SELECCIONE UN CANTÓN</option>');
                    $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
                }
                $("#mensajeFormIngresoPersona").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#selectCantones").html('<option value="0">SELECCIONE UN CANTÓN</option>');
                $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
                if(xhr.status === 0){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}
    </script>