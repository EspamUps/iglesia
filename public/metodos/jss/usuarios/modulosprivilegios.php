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


function obtenerFormularioGestionPrivilegios(id,i,j){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/gestionarmodulosprivilegios/obtenerformularioadministrarprivilegios',
        type: 'post',
        dataType: 'JSON',
        data: {id:id, i:i,j:j},
        beforeSend: function(){
            $("#mensajeAdministrarPrivilegios").html('');
            cargandoUsuarios("#contenedorAdministrarPrivilegios")
            $("#contenedorTablaPrivilegios").html("");
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorAdministrarPrivilegios").html(data.select);
            }else{
                $("#contenedorAdministrarPrivilegios").html('');
                $("#contenedorTablaPrivilegios").html("");
            }
            $("#mensajeAdministrarPrivilegios").html(data.mensaje);
            
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorAdministrarPrivilegios").html('');
            $("#contenedorTablaPrivilegios").html("");
            if(xhr.status === 0){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}



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


function EliminarModulo(vari, ID, j, idAM){
    if (confirm('¿DESEAS DESHABILITARLE EL ACCESO A ESTE MÓDULO?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/gestionarmodulosprivilegios/eliminarmodulo',
            type: 'post',
            dataType: 'JSON',
            data: { usuario: vari, fila: ID, fila2:j, idAsignarModulo:idAM},
            beforeSend: function () {
                $("#btnEliminarAsignarModulo"+ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeAdministrarModulos").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
              
                if (data.validar == true) {
                   obtenerFormularioGestionModulos(data.idUsuarioEncriptado,data.fila, data.fila2);
                } else {
                    $("#btnEliminarAsignarModulo"+ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeAdministrarModulos").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarAsignarModulo"+ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeAdministrarModulos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}



$(function(){
    $("#contenedorAdministrarModulos").ajaxForm({
        beforeSend: function(){
            $("#mensajeAdministrarModulos").html('');
            $("#btnGuardarAsignarModulos").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                obtenerFormularioGestionModulos(data.idUsuarioEncriptado,data.imm,data.jmm);
            }
            $("#btnGuardarAsignarModulos").button('reset');
            $("#mensajeAdministrarModulos").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarAsignarModulos").button('reset');
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
}); 




function cargandoPrivilegios(contenedor){
    $("#contenedorTablaPrivilegios").html('');
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

function CambiarEstadoPrivilegio(id,i,j){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/gestionarmodulosprivilegios/administrarprivilegio',
        type: 'post',
        dataType: 'JSON',
        data: {id:id,i:i,j:j},
        beforeSend: function(){
            $("#mensajeAdministrarPrivilegios").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
               
              CargarPrivilegiosPorModulo();
            }else{
                $("#contenedorTablaPrivilegios").html("");
            }
            $("#mensajeAdministrarPrivilegios").html(data.mensaje);
            
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#mensajeAdministrarPrivilegios").html("");
            if(xhr.status === 0){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}

function CargarPrivilegiosPorModulo(){
    var url = $("#rutaBase").text();
    var idAsignarModuloEncriptado = $("#selectModulosE").val();
    var i = $("#ip").val();
    var j = $("#jp").val();
    $.ajax({
        url : url+'/gestionarmodulosprivilegios/cargarprivilegiospormodulo',
        type: 'post',
        dataType: 'JSON',
        data: {id:idAsignarModuloEncriptado,i:i,j:j},
        beforeSend: function(){
            $("#mensajeAdministrarPrivilegios").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaPrivilegios").html(data.tabla);
                
            }else{
                $("#contenedorTablaPrivilegios").html("");
            }
            $("#mensajeAdministrarPrivilegios").html("");
            
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaPrivilegios").html("");
            $("#mensajeAdministrarPrivilegios").html('');
            if(xhr.status === 0){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeAdministrarPrivilegios").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
</script>