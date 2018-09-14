<script>
    
//function limpiarFormIngresarPersona()
//{
//    $('#formIngresoPersona').each(function () {
//        this.reset();
//    });
//    $("#selectCantones").html('<option value="0">SELECCIONE UN CANTÓN</option>');
//    $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
//    setTimeout(function() {$("#mensajeFormIngresoPersona").html('');},1500);
////}
//$(function(){
//    $("#formIngresoPersona").ajaxForm({
//        beforeSend: function(){
//            $("#mensajeFormIngresoPersona").html('');
//            $("#btnGuardarPersona").button('loading');
//        },
//        uploadProgress: function(event,position,total,percentComplete){
//
//        },
//        success: function(data){
//            if(data.validar==true){
//                limpiarFormIngresarPersona();
//                obtenerPersonas();
//            }
//            $("#btnGuardarPersona").button('reset');
//            $("#mensajeFormIngresoPersona").html(data.mensaje);
//        },
//        complete: function(){
//        },
//        error: function(xhr, textStatus, errorThrown) {
//            $("#btnGuardarPersona").button('reset');
//            if(xhr.status === 0){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
//            }else if(xhr.status == 404){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
//            }else if(xhr.status == 500){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
//            }else if(errorThrown === 'parsererror'){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
//            }else if(errorThrown === 'timeout'){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
//            }else if(errorThrown === 'abort'){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
//            }else{
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
//            }
//        }
//    });    
//}); 





function obtenerFormularioGestionModulos(id,i,j){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/gestionarmodulosprivilegios/obtenerformularioadministrarmodulos',
        type: 'post',
        dataType: 'JSON',
        data: {id:id, i:i,j:j},
        beforeSend: function(){
            $("#mensajeAdministrarModulos").html('');
            cargandoUsuarios("#contenedorAdministrarModulos")
            $("#contenedorTablaModulos").html("");
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
     
            if(data.validar == true){
                $("#contenedorAdministrarModulos").html(data.select);
               $("#contenedorTablaModulos").html(data.tabla);
            }else{
                $("#contenedorAdministrarModulos").html('');
                $("#contenedorTablaModulos").html("");
            }
            $("#mensajeAdministrarModulos").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorAdministrarModulos").html('');
            $("#contenedorTablaModulos").html("");
            if(xhr.status === 0){
                $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}





//
//$(function(){
//    $("#contenedorModificarEstadoUsuario").ajaxForm({
//        beforeSend: function(){
//            $("#mensajeModificarEstadoUsuario").html('');
//            $("#btnModificarEstadoUsuario").button('loading');
//        },
//        uploadProgress: function(event,position,total,percentComplete){
//
//        },
//        success: function(data){
//            if(data.validar==true){
//                var table = $('#tablaUsuarios').DataTable();
//                table.row(data.im).data(data.tabla[data.im]).draw();
//                obtenerFormularioModificarEstadoUsuario(data.idUsuario, data.im, data.jm);
//            }
//            $("#btnModificarEstadoUsuario").button('reset');
//            $("#mensajeModificarEstadoUsuario").html(data.mensaje);
//        },
//        complete: function(){
//        },
//        error: function(xhr, textStatus, errorThrown) {
//            $("#btnModificarEstadoUsuario").button('reset');
//            if(xhr.status === 0){
//                $("#mensajeModificarEstadoUsuario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
//            }else if(xhr.status == 404){
//                $("#mensajeModificarEstadoUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
//            }else if(xhr.status == 500){
//                $("#mensajeModificarEstadoUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
//            }else if(errorThrown === 'parsererror'){
//                $("#mensajeModificarEstadoUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
//            }else if(errorThrown === 'timeout'){
//                $("#mensajeModificarEstadoUsuario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
//            }else if(errorThrown === 'abort'){
//                $("#mensajeModificarEstadoUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
//            }else{
//                $("#mensajeModificarEstadoUsuario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
//            }
//        }
//    });    
//}); 
</script>