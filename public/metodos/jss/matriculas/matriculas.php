<script>


function filtrarUsuarioPorIdentificacionEnMatricula(event){
    var url = $("#rutaBase").text();
    var identificacion = $("#identificacion").val();
    var idConfCurso = $("#selectConfigurarCurso").val();
    if(identificacion.length < 10){
        $("#mensajeFormIngresoMatricula").html("");
        $("#contenedorDatosEstudianteParaMatricular").html("");
        $("#botonMatricular").html("");
    }else{
        $.ajax({
            url : url+'/matriculas/filtrarpersonaporidentificacion',
            type: 'post',
            dataType: 'JSON',
            data: {identificacion:identificacion, idConfCurso:idConfCurso},
            beforeSend: function(){
                $("#mensajeFormIngresoMatricula").html('');
                cargandoMatriculas('#contenedorDatosEstudianteParaMatricular');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){ 
                if(data.validar == true){
                  $("#mensajeFormIngresoMatricula").html(data.mensaje);
                  $("#contenedorDatosEstudianteParaMatricular").html(data.tabla);
                  $("#botonMatricular").html(data.btnMatricular);
                }else{
                    $("#contenedorDatosEstudianteParaMatricular").html('');
                }
                $("#mensajeFormIngresoMatricula").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorDatosEstudianteParaMatricular").html('');
                if(xhr.status === 0){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}
function cargandoMatriculas(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}


function seleccionarFila(ID)
{
    var menues2 = $("#tablaMatriculas tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaMatriculas" + ID + " td").removeAttr("style");
    $("#filaTablaMatriculas" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}

function cargarFormularioIngresarMatricula()
{
    var url = $("#rutaBase").text();
    var idConfCurso = $("#selectConfigurarCurso").val();

    if(idConfCurso == 0){
        $("#contenedorHorarioSeleccionado").html('');
        $("#contenedorInfoGeneralHorarioSeleccionado").html("");
    }else{
        $.ajax({
            url : url+'/matriculas/cargarformularioingreso',
            type: 'post',
            dataType: 'JSON',
            data: {id:idConfCurso},
            beforeSend: function(){
            },
            uploadProgress: function(event,position,total,percentComplete){

            },
            success: function(data){ 
                if(data.validar == true)
                { 
                    cargandoMatriculas("#contenedorFormIngresoMatricula");           
                    $("#contenedorFormIngresoMatricula").html(data.div);
                               
                }else{
                     $("#mensajeFormIngresoMatricula").html(data.mensaje);
                }
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorFormIngresoMatricula").html('');
                if(xhr.status === 0){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}


function obtenerMatriculas(){
    var url = $("#rutaBase").text();
    var idConfCurso = $("#selectConfigurarCurso").val();
    if(idConfCurso == 0){
        $("#mensajeTablaMatriculasActuales").html('');
         $("#contenedorTablaMatriculasActuales").html('');
    }else{
    $.ajax({
        url : url+'/matriculas/obtenermatriculas',
        type: 'post',
        dataType: 'JSON',
        data: {id:idConfCurso},
        beforeSend: function(){
            cargandoMatriculas('#contenedorTablaMatriculasActuales');
            $("#mensajeTablaMatriculasActuales").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaMatriculasActuales").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaMatriculas"></table></div>');
                $('#tablaMatriculas').DataTable({
                    destroy: true,
                    order: [],
                    data: data.tabla,
                    'createdRow': function (row, data, dataIndex) {
                        var division = dataIndex % 2;
                        if (division == "0")
                        {
                            $(row).attr('style', 'background-color: #DCFBFF;text-align: center;font-weight: bold;');
                        } else {
                            $(row).attr('style', 'background-color: #CFCFCF;text-align: center;font-weight: bold;');
                        }
                        
                        $(row).attr('onclick', 'seleccionarFila(' + dataIndex + ');');
                        $(row).attr('id', 'filaTablaMatriculas' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 2,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','identificacion'+row); 
                           }
                        },
                        {
                           'targets': 6,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('style','background-color:green'); 
                           }
                        }
                     ],
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'DNI',
                            data: 'identificacion'
                        },
                        {
                            title: 'NOMBRES',
                            data: 'nombres'
                        },
                        {
                            title: 'APELLIDOS',
                            data: 'apellidos'
                        },
                        {
                            title: 'FECHA DE MATRICULA',
                            data: 'fechaMatricula'
                        },   
                        {
                            title: 'ESTADO CURSO',
                            data: 'estadoCurso'
                        },
                        {
                            title: 'MATRÍCULA',
                            data: 'labelestadoMatricula'
                        },
                        {
                            title: 'OPC.',
                            data: 'opciones1'
                        }
                    ],
                });    
                seleccionarFila(0)
            }else{
                $("#contenedorTablaMatriculasActuales").html('');
            }
            $("#mensajeTablaMatriculasActuales").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaMatriculasActuales").html('');
            if(xhr.status === 0){
                $("#mensajeTablaMatriculasActuales").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaMatriculasActuales").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaMatriculasActuales").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaMatriculasActuales").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaMatriculasActuales").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaMatriculasActuales").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaMatriculasActuales").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
    }
}

function filtrarlistaHorariosCursoSeleccionado(){
    var url = $("#rutaBase").text();
    var idCurso = $("#selectCurso").val();
    var idPeriodo = $("#selectPeriodo").val();

    if(idCurso == 0){
        $("#contenedorHorarioSeleccionado").html("");
        $("#contenedorlistahorarios").html("");
        $("#contenedorInfoGeneralHorarioSeleccionado").html("");
        $("#mensajeTablaMatriculasActuales").html("");
        $("#contenedorTablaMatriculasActuales").html("");
        $("#mensajeFormIngresoMatricula").html("");
        $("#contenedorFormIngresoMatricula").html("");
        $("#contenedorDatosEstudianteParaMatricular").html("");
    }else{
        $.ajax({
            url : url+'/matriculas/obtenerhorarios',
            type: 'post',
            dataType: 'JSON',
            data: {id:idCurso, idPeriodo:idPeriodo},
            beforeSend: function(){
                $("#mensajeContenedorHorario").html("");
                $("#contenedorlistahorarios").html("");
                $("#contenedorHorarioSeleccionado").html("");
                $("#contenedorInfoGeneralHorarioSeleccionado").html("");
                $("#mensajeTablaMatriculasActuales").html("");
                $("#contenedorTablaMatriculasActuales").html("");
                $("#mensajeFormIngresoMatricula").html("");
                $("#contenedorFormIngresoMatricula").html("");
                $("#contenedorDatosEstudianteParaMatricular").html("");
                cargandoMatriculas("#contenedorlistahorarios");
            },
            uploadProgress: function(event,position,total,percentComplete){

            },
            success: function(data){ 
                if(data.validar == true)
                {               
                     $("#contenedorlistahorarios").html(data.div);
                     $("#contenedorInfoGeneralHorarioSeleccionado").html('');
                     $("#contenedorHorarioSeleccionado").html('');

                }else{
                     $("#contenedorlistahorarios").html('');
                }
                $("#mensajeContenedorHorario").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorlistahorarios").html('');
                if(xhr.status === 0){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
} 

function filtrarlistaCursosPorPeriodoSeleccionado(){
    var url = $("#rutaBase").text();
    var idPeriodo = $("#selectPeriodo").val();

    if(idPeriodo == 0){
       $("#mensajeContenedorHorario").html("");
        $("#contenedorlistacursos").html('');
        $("#contenedorlistahorarios").html('');
        $("#contenedorInfoGeneralHorarioSeleccionado").html("");
        $("#contenedorHorarioSeleccionado").html("");
        $("#mensajeFormIngresoMatricula").html("");
        $("#contenedorFormIngresoMatricula").html("");
        $("#contenedorDatosEstudianteParaMatricular").html("");
        $("#mensajeTablaMatriculasActuales").html("");
        $("#contenedorTablaMatriculasActuales").html("");
        
    }else{
        $.ajax({
            url : url+'/matriculas/obtenercursos',
            type: 'post',
            dataType: 'JSON',
            data: {id:idPeriodo},
            beforeSend: function(){
                $("#mensajeContenedorHorario").html("");
                $("#contenedorlistahorarios").html("");
                $("#contenedorlistacursos").html("");
                $("#contenedorHorarioSeleccionado").html("");
                $("#contenedorInfoGeneralHorarioSeleccionado").html("");
                $("#mensajeTablaMatriculasActuales").html("");
                $("#contenedorTablaMatriculasActuales").html("");
                 $("#mensajeFormIngresoMatricula").html("");
                $("#contenedorFormIngresoMatricula").html("");
                $("#contenedorDatosEstudianteParaMatricular").html("");
                 cargandoMatriculas("#contenedorlistacursos"); 
            },
            uploadProgress: function(event,position,total,percentComplete){

            },
            success: function(data){ 
                if(data.validar == true)
                {               
                     $("#contenedorlistacursos").html(data.select);
                     $("#contenedorlistahorarios").html("");
                     $("#contenedorInfoGeneralHorarioSeleccionado").html('');
                     $("#contenedorHorarioSeleccionado").html(""); 
                     $("#mensajeFormIngresoMatricula").html("");
                     $("#contenedorFormIngresoMatricula").html("");
                      $("#contenedorDatosEstudianteParaMatricular").html("");
                }else{
                     $("#contenedorlistacursos").html('');
                }
                $("#mensajeContenedorHorario").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorlistacursos").html('');
                if(xhr.status === 0){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
} 





function filtrarHorarioCursoSeleccionado(){
    var url = $("#rutaBase").text();
    var idConfCurso = $("#selectConfigurarCurso").val();
    if(idConfCurso == 0){
        $("#mensajeContenedorHorario").html("");
        $("#mensajeFormIngresoMatricula").html("");
        $("#contenedorFormIngresoMatricula").html("");
        $("#contenedorDatosEstudianteParaMatricular").html("");
    }else{
        $.ajax({
            url : url+'/matriculas/filtrardatoshorario',
            type: 'post',
            dataType: 'JSON',
            data: {id:idConfCurso},
            beforeSend: function(){
                cargandoMatriculas("#contenedorInfoGeneralHorarioSeleccionado");
                cargandoMatriculas("#contenedorHorarioSeleccionado");                
                $("#mensajeContenedorHorario").html("");
                $("#mensajeFormIngresoMatricula").html("");
                $("#contenedorFormIngresoMatricula").html("");
                $("#contenedorDatosEstudianteParaMatricular").html("");
                $("#mensajeFormIngresoMatricula").html("");
                $("#contenedorFormIngresoMatricula").html("");
                
            },
            uploadProgress: function(event,position,total,percentComplete){

            },
            success: function(data){ 
                if(data.validar == true)
                {
                  $("#contenedorInfoGeneralHorarioSeleccionado").html(data.datosGenerales);
                   $("#contenedorHorarioSeleccionado").html(data.tabla);
                }else{
                     $("#contenedorHorarioSeleccionado").html('');
                }
                $("#mensajeContenedorHorario").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorHorarioSeleccionado").html('');
                if(xhr.status === 0){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorHorario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
} 

function validarIngresoMatricula(f){
    var _validar = false;
    if(confirm("¿ESTAS SEGURO DE REGISTRAR ESTA MATRÍCULA?")){
        _validar = true;
    }
    return _validar;
}

function limpiarFormIngresoMatriculas()
{
//    $('input[id="identificacion"]').val('');
    $("#botonMatricular").html("");
    $("#contenedorDatosEstudianteParaMatricular").html("");
    
    setTimeout(function() {$("#mensajeFormIngresoMatricula").html('');},1500);
}

$(function(){
    $("#formIngresoMatricula").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresoMatricula").html('');
            $("#btnGuardarMatricula").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresoMatriculas();
                filtrarHorarioCursoSeleccionado();
                cargarFormularioIngresarMatricula();
                obtenerMatriculas();
            }
            $("#btnGuardarMatricula").button('reset');
            $("#mensajeFormIngresoMatricula").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarMatricula").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresoMatricula").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function obtenerFormularioModificarEstadoMatricula(id,i,j){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/matriculas/obtenerFormularioModificarEstadoMatricula',
        type: 'post',
        dataType: 'JSON',
        data: {id:id, i:i,j:j},
        beforeSend: function(){
            $("#mensajeModificarEstadoMatricula").html("");
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
     
            if(data.validar == true){
                $("#contenedorModificarEstadoMatricula").html(data.tabla);
               
            }else{
                $("#contenedorModificarEstadoMatricula").html("");
            }
            $("#mensajeModificarEstadoMatricula").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorModificarEstadoMatricula").html("");
            if(xhr.status === 0){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}




</script>
