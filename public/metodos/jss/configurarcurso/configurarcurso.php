<script type="text/javascript">
function filtrarHorarioCurso(vari){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/configurarcurso/filtrarhorariocurso',
        type: 'post',
        dataType: 'JSON',
        data: {id:vari},
        beforeSend: function(){
            cargandoConfigurarCurso("#contenedorTablaHorario");
            $("#contenedorTablaHorario").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){  
            if(data.validar == true)
            {
                 $("#contenedorTablaHorario").html(data.tabla);
            }else{
                 $("#contenedorTablaHorario").html(data.mensaje);
            }
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaHorario").html('');
            if(xhr.status === 0){
                $("#contenedorTablaHorario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#contenedorTablaHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#contenedorTablaHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#contenedorTablaHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#contenedorTablaHorario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#contenedorTablaHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#contenedorTablaHorario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
} 
    
    
function deshabilitarConfigurarCurso(vari, ID,ID2){
    var _estado = $("#estadoConfigurarCursoA"+ID).val();
    var _mensaje = "HABILITAR";
    if(_estado == true){
        _mensaje = "DESHABILITAR";
    }
    if (confirm('¿DESEAS '+_mensaje+' '+$("#nombreCurso"+ID).text()+'?')) {
        var _nombreClase = $("#btnDeshabilitarConfigurarCurso" + ID + " i").attr('class');
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/configurarcurso/modificarestadoconfigurarcurso',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID,numeroFila2: ID2 },
            beforeSend: function () {
                $("#btnDeshabilitarConfigurarCurso" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaConfigurarCurso").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    var table = $('#tablaConfigurarCurso').DataTable();
                    table.row(data.numeroFila).data(data.tabla[data.numeroFila]).draw();
                    
                } else {
                    $("#btnDeshabilitarConfigurarCurso" + ID).html('<i class="' + _nombreClase + '"></i>');
                }
                $("#mensajeTablaConfigurarCurso").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnDeshabilitarConfigurarCurso" + ID).html('<i class="' + _nombreClase + '"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}         
function eliminarConfigurarCurso(vari, ID){
    if (confirm('¿DESEAS ELIMINAR '+$("#nombreCurso"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/configurarcurso/eliminarconfigurarcurso',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarConfigurarCurso" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaConfigurarCurso").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    $("#filaTablaConfigurarCurso"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFila(data.numeroFila + 1);
                    } else {
                        seleccionarFila(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarConfigurarCurso" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeTablaConfigurarCurso").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarConfigurarCurso" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}      

function seleccionarFila(ID)
{
    var menues2 = $("#tablaConfigurarCurso tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaConfigurarCurso" + ID + " td").removeAttr("style");
    $("#filaTablaConfigurarCurso" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}

$(function(){
    $("#formModificarFechaFin").ajaxForm({
        beforeSend: function(){
            $("#mensajeModificarFechaFin").html('');
            $("#btnModificarFechaFinDeCurso").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){console.log(data)
            if(data.validar==true){
                
                var table = $('#tablaConfigurarCurso').DataTable();
                table.row(data.numeroFila).data(data.tabla[data.numeroFila]).draw();
                setTimeout(function() {$("#mensajeModificarFechaFin").html('');},1500);
            }
            $("#btnModificarFechaFinDeCurso").button('reset');
            $("#mensajeModificarFechaFin").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnModificarFechaFinDeCurso").button('reset');
            if(xhr.status === 0){
                $("#mensajeModificarFechaFin").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModificarFechaFin").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModificarFechaFin").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModificarFechaFin").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModificarFechaFin").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModificarFechaFin").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModificarFechaFin").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 
function obtenerConfigurarCurso(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/configurarcurso/obtenerconfigurarcurso',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaConfigurarCurso").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaConfigurarCurso").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaConfigurarCurso"></table></div>');
                $('#tablaConfigurarCurso').DataTable({
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
                        $(row).attr('onclick', 'seleccionarFila(' + dataIndex + ');filtrarHorarioCurso(\''+data.idConfigurarCursoEncriptado+'\');');
                        $(row).attr('id', 'filaTablaConfigurarCurso' + dataIndex);
                        if(data._j == 1){
                            filtrarHorarioCurso(data.idConfigurarCursoEncriptado);
                        }
                    },
                    'columnDefs': [
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombreCurso'+row); 
                           },
                        },
                        {
                           'targets': 5,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','fechafincurso'+row); 
                                $(td).attr('onclick','obtenerCambiarFechaFinCursoModal('+row+','+rowData._j+',\''+rowData.idConfigurarCursoEncriptado+'\')'); 
                                $(td).attr('data-target','#modalModificarFechaFin'); 
                                $(td).attr('data-toggle','modal'); 
                                
                           }
                        }
                        
                     ],
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'CURSO',
                            data: 'nombreCurso'
                        },
                        {
                            title: 'CATEQUISTA',
                            data: 'docente'
                        },
                        {
                            title: 'PERIODO',
                            data: 'periodo'
                        },
                        {
                            title: 'FECHA INICIO',
                            data: 'fechaInicio'
                        },
                        {
                            title: 'FECHA FIN',
                            data: 'fechaFin'
                        },
                        {
                            title: 'NIVEL',
                            data: 'nivelCurso'
                        },
                        {
                            title: 'VALOR',
                            data: 'valorCurso'
                        },
                        {
                            title: 'FECHA REGISTRO',
                            data: 'fechaIngreso'
                        },
                        
                        {
                            title: 'OPC.',
                            data: 'opciones'
                        }
                    ],
                });    
                seleccionarFila(0)
                
            }else{
                $("#contenedorTablaConfigurarCurso").html('');
            }
            $("#mensajeTablaConfigurarCurso").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaConfigurarCurso").html('');
            if(xhr.status === 0){
                $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}

function obtenerCambiarFechaFinCursoModal(ID,ID2,_idConfigurarCursoEncriptado){
    var fechafincurso = $("#fechafincurso"+ID).text();
    $("#fechaActual").val(fechafincurso);
    $("#numeroFilaT").val(ID);
    $("#numeroFila2T").val(ID2);
    $("#idConfigurarCursoEncriptado").val(_idConfigurarCursoEncriptado);
    $("#mensajeModificarFechaFin").html('');
}
  
function limpiarFormIngresarConfigurarCurso()
{
    $('#formIngresarConfigurarCurso').each(function () {
        this.reset();
    });
    $("#contenedorDatosCurso").html('');
    setTimeout(function() {$("#mensajeFormIngresarConfigurarCurso").html('');},1500);
}
$(function(){
    $("#formIngresarConfigurarCurso").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarConfigurarCurso").html('');
            $("#btnGuardarConfigurarCurso").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarConfigurarCurso();
                obtenerConfigurarCurso();
            }
            $("#btnGuardarConfigurarCurso").button('reset');
            $("#mensajeFormIngresarConfigurarCurso").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarConfigurarCurso").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarConfigurarCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

    
    
function cargandoConfigurarCurso(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}    
    
function filtrarDatosCurso(){
    var url = $("#rutaBase").text();
    var idCurso = $("#selectCurso").val();
    if(idCurso == 0){
        $("#mensajeContenedorDatosCurso").html('');
        $("#contenedorDatosCurso").html('');
    }else{
        $.ajax({
            url : url+'/configurarcurso/filtrardatoscurso',
            type: 'post',
            dataType: 'JSON',
            data: {id:idCurso},
            beforeSend: function(){
                cargandoConfigurarCurso("#contenedorDatosCurso");
                $("#mensajeContenedorDatosCurso").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){

            },
            success: function(data){  
                if(data.validar == true)
                {
                     $("#contenedorDatosCurso").html(data.tabla);
                }else{
                     $("#contenedorDatosCurso").html('');
                }
                $("#mensajeContenedorDatosCurso").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorDatosCurso").html('');
                if(xhr.status === 0){
                    $("#mensajeContenedorDatosCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorDatosCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorDatosCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorDatosCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorDatosCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorDatosCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorDatosCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
} 
</script>
