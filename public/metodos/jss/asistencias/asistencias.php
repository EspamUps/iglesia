<script>




function cargando(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}




function filtrarlistaCursosPorPeriodo(){
    var url = $("#rutaBase").text();
    var idPeriodo = $("#selectPeriodoAsistencia").val();

    if(idPeriodo == 0){
       $("#mensajeContenedorHorarioAsistencia").html("");
        $("#contenedorlistacursosAsistencia").html('');
        $("#contenedorlistahorariosAsistencia").html('');
        $("#contenedorInfoGeneralHorarioSeleccionadoAsistencia").html("");
        $("#contenedorHorarioSeleccionadoAsistencia").html("");
        $("#mensajeContenedorListaAsistencia").html("");
        $("#contenedorContenedorListaAsistencia").html("");
        $("#contenedorOpcionesAsistencia").html("");
        $("#contenderListaFechaAsistencia").html("");
        
    }else{
        $.ajax({
            url : url+'/asistencias/obtenercursos',
            type: 'post',
            dataType: 'JSON',
            data: {idPeriodo:idPeriodo},
            beforeSend: function(){
                cargando("#contenedorlistahorariosAsistencia");
                $("#mensajeContenedorHorarioAsistencia").html("");
                $("#contenedorlistahorariosAsistencia").html("");
                $("#contenedorlistacursosAsistencia").html("");
                $("#contenedorHorarioSeleccionadoAsistencia").html("");
                $("#contenedorInfoGeneralHorarioSeleccionadoAsistencia").html("");
                $("#mensajeContenedorListaAsistencia").html("");
                $("#contenedorContenedorListaAsistencia").html("");
                $("#contenedorOpcionesAsistencia").html("");
                $("#contenderListaFechaAsistencia").html("");
            },
            uploadProgress: function(event,position,total,percentComplete){
                
            },
            success: function(data){ 
                if(data.validar == true)
                {               
                     $("#contenedorlistacursosAsistencia").html(data.select);
                     $("#contenedorlistahorariosAsistencia").html("");
                     $("#contenedorInfoGeneralHorarioSeleccionadoAsistencia").html('');
                     $("#contenedorHorarioSeleccionadoAsistencia").html(""); 
                }else{
                     $("#contenedorlistacursosAsistencia").html('');
                }
                $("#mensajeContenedorHorarioAsistencia").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) { 
                $("#contenedorlistacursosAsistencia").html("");
                if(xhr.status === 0){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
} 

function filtrarlistaHorariosPorCurso(){
    var url = $("#rutaBase").text();
    var idCurso = $("#selectCursoAsistencia").val();
    var idPeriodo = $("#selectPeriodoAsistencia").val();

    if(idCurso == 0){
        $("#contenedorHorarioSeleccionadoAsistencia").html("");
        $("#contenedorlistahorariosAsistencia").html("");
        $("#contenedorInfoGeneralHorarioSeleccionadoAsistencia").html("");
        $("#mensajeContenedorListaAsistencia").html("");
        $("#contenedorContenedorListaAsistencia").html("");
        $("#contenedorOpcionesAsistencia").html("");
        $("#contenderListaFechaAsistencia").html("");
    }else{
        $.ajax({
            url : url+'/asistencias/obtenerhorarios',
            type: 'post',
            dataType: 'JSON',
            data: {id:idCurso, idPeriodo:idPeriodo},
            beforeSend: function(){
                cargando("#contenedorlistahorariosAsistencia");
                $("#mensajeContenedorHorarioAsistencia").html("");
                $("#contenedorlistahorariosAsistencia").html("");
                $("#contenedorHorarioSeleccionadoAsistencia").html("");
                $("#contenedorInfoGeneralHorarioSeleccionadoAsistencia").html("");
                $("#mensajeContenedorListaAsistencia").html("");
                $("#contenedorContenedorListaAsistencia").html("");
                $("#contenedorOpcionesAsistencia").html("");
                $("#contenderListaFechaAsistencia").html("");
            },
            uploadProgress: function(event,position,total,percentComplete){

            },
            success: function(data){ 
                if(data.validar == true)
                {               
                     $("#contenedorlistahorariosAsistencia").html(data.div);
                     $("#contenedorInfoGeneralHorarioSeleccionadoAsistencia").html("");
                     $("#contenedorHorarioSeleccionadoAsistencia").html("");

                }else{
                     $("#contenedorlistahorariosAsistencia").html("");
                }
                $("#mensajeContenedorHorarioAsistencia").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorlistahorariosAsistencia").html("");
                if(xhr.status === 0){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}


function filtrarHorarioPorCurso(){
    var url = $("#rutaBase").text();
    var idConfCurso = $("#selectConfigurarCursoAsistencia").val();
    if(idConfCurso == 0){
        $("#mensajeContenedorHorarioAsistencia").html("");
        $("#mensajeContenedorListaAsistencia").html("");
        $("#contenedorContenedorListaAsistencia").html("");
        $("#contenedorOpcionesAsistencia").html("");
        $("#contenedorInfoGeneralHorarioSeleccionadoAsistencia").html("");
        $("#contenedorHorarioSeleccionadoAsistencia").html("");
        $("#contenderListaFechaAsistencia").html("");
    }else{
        $.ajax({
            url : url+'/asistencias/filtrardatoshorario',
            type: 'post',
            dataType: 'JSON',
            data: {id:idConfCurso},
            beforeSend: function(){
                cargando("#contenedorInfoGeneralHorarioSeleccionadoAsistencia");
                cargando("#contenedorHorarioSeleccionadoAsistencia");                
                $("#mensajeContenedorHorarioAsistencia").html("");
                $("#mensajeContenedorListaAsistencia").html("");
                $("#contenedorContenedorListaAsistencia").html("");
                $("#contenedorOpcionesAsistencia").html("");
                $("#contenderListaFechaAsistencia").html("");
                
            },
            uploadProgress: function(event,position,total,percentComplete){

            },
            success: function(data){ 
                if(data.validar == true)
                {
                    $("#contenedorInfoGeneralHorarioSeleccionadoAsistencia").html(data.datosGenerales);
                    $("#contenedorHorarioSeleccionadoAsistencia").html(data.tabla);
                    $("#contenedorOpcionesAsistencia").html(data.tablaAsistencia);
                    $("#contenderListaFechaAsistencia").html(data.select);
                    cargarTablaListaAsistencia();
                         
                }else{
                     $("#contenedorHorarioSeleccionadoAsistencia").html("");
                }
                $("#mensajeContenedorHorarioAsistencia").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorHorarioSeleccionadoAsistencia").html('');
                if(xhr.status === 0){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorHorarioAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
} 


function GenerarAsistencia(){
    var url = $("#rutaBase").text();
    var idConfCurso = $("#selectConfigurarCursoAsistencia").val();

    if(idConfCurso == 0){
       $("#contenedorContenedorListaAsistencia").html("");
       $("#contenderListaFechaAsistencia").html("");
        
    }else{
        
        $.ajax({
            url : url+'/asistencias/generarasistencia',
            type: 'post',
            dataType: 'JSON',
            data: {idConfCurso:idConfCurso},
            beforeSend: function(){
                cargando('#contenedorContenedorListaAsistencia');
                $("#contenedorContenedorListaAsistencia").html("");
            },
            uploadProgress: function(event,position,total,percentComplete){
                
            },
            success: function(data){ 
                if(data.validar == true)
                {
                    cargarTablaListaAsistencia();
                    $("#contenderListaFechaAsistencia").html(data.select);
                    filtrarHorarioPorCurso();
                }else{
                     
                }
                $("#mensajeContenedorListaAsistencia").html(data.mensaje);
                
                setTimeout(function() {$("#mensajeContenedorListaAsistencia").html('');},1500);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) { 
                $("#contenedorContenedorListaAsistencia").html("");
                if(xhr.status === 0){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}

function cargarTablaListaAsistencia(){
    var url = $("#rutaBase").text();
    var idFechaAsistencia = $("#selectListaFechasAsistencia").val();

    if(idFechaAsistencia == null || idFechaAsistencia==0){
        $("#contenedorContenedorListaAsistencia").html("");
        $("#mensajeContenedorListaAsistencia").html("");
        
    }else{
    $.ajax({
        url : url+'/asistencias/obtenerasistencias',
        type: 'post',
        dataType: 'JSON',
        data: {id:idFechaAsistencia},
        beforeSend: function(){
           cargando('#contenedorContenedorListaAsistencia');
            $("#mensajeContenedorListaAsistencia").html("");
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                 $("#contenedorContenedorListaAsistencia").html(data.tabla);
            }else{
                $("#contenedorContenedorListaAsistencia").html("");
            }
            $("#mensajeContenedorListaAsistencia").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenderListaFechaAsistencia").html('');
            if(xhr.status === 0){
                $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
    }
}


function cambiarAsistenciaHoy(id, Nfila){
    var url = $("#rutaBase").text();
    if(id==null)
    {
        $("#mensajeContenedorListaAsistencia").html("");
        $("#contenedorContenedorListaAsistencia").html("");
    }
    else{
        $.ajax({
            url : url+'/asistencias/asistenciahoy',
            type: 'post',
            dataType: 'JSON',
            data: {idAsistenciaEncriptado:id, Nfila:Nfila},
            beforeSend: function(){
                $("#mensajeContenedorListaAsistencia").html("");

            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#numerofila"+data.numeroFila).html(data.nuevafila);
                }else{
                    $("#contenedorContenedorListaAsistencia").html("");
                }
                $("#mensajeContenedorListaAsistencia").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenderListaFechaAsistencia").html('');
                if(xhr.status === 0){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorListaAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}



</script>
