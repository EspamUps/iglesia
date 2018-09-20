<script type="text/javascript">
function deshabilitarRangoAsistencia(vari, ID,ID2){
    var _estado = $("#porcentajeA"+ID).val();
    var _mensaje = "HABILITAR";
    if(_estado == true){
        _mensaje = "DESHABILITAR";
    }
    if (confirm('¿DESEAS '+_mensaje+' '+$("#nombreRangoAsistencia"+ID).text()+'?')) {
        var _nombreClase = $("#btnDeshabilitarRangoAsistencia" + ID + " i").attr('class');
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/rangoasistencia/modificarestadorangoasistencia',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID,numeroFila2: ID2 },
            beforeSend: function () {
                $("#btnDeshabilitarRangoAsistencia" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaRangoAsistencia").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    obtenerRangoAsistencia();
                } else {
                    $("#btnDeshabilitarRangoAsistencia" + ID).html('<i class="' + _nombreClase + '"></i>');
                }
                $("#mensajeTablaRangoAsistencia").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnDeshabilitarRangoAsistencia" + ID).html('<i class="' + _nombreClase + '"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}         
function eliminarRangoAsistencia(vari, ID){
    if (confirm('¿DESEAS ELIMINAR '+$("#nombreRangoAsistencia"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/rangoasistencia/eliminarrangoasistencia',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarRangoAsistencia" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaRangoAsistencia").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                console.log(data)
                if (data.validar == true) {
                    $("#filaTablaRangoAsistencia"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFila(data.numeroFila + 1);
                    } else {
                        seleccionarFila(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarRangoAsistencia" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeTablaRangoAsistencia").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarRangoAsistencia" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}   
function seleccionarFila(ID)
{
    var menues2 = $("#tablaRangoAsistencia tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaRangoAsistencia" + ID + " td").removeAttr("style");
    $("#filaTablaRangoAsistencia" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerRangoAsistencia(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/rangoasistencia/obtenerrangosasistencia',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaRangoAsistencia").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaRangoAsistencia").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaRangoAsistencia"></table></div>');
                $('#tablaRangoAsistencia').DataTable({
                    destroy: true,
                    order: [],
                    data: data.tabla,
                    'createdRow': function (row, data, dataIndex) {
                        var division = dataIndex % 2;
                        if (division == "0")
                        {
                            if(data.estadoRangoAsistencia == 1){
                                $(row).attr('style', 'background-color: #C4FEC2;text-align: center;font-weight: bold;');
                            }else{
                                $(row).attr('style', 'background-color: #DCFBFF;text-align: center;font-weight: bold;');
                            }
                        } else {
                            if(data.estadoRangoAsistencia == 1){
                                $(row).attr('style', 'background-color: #C4FEC2;text-align: center;font-weight: bold;');
                            }else{
                                $(row).attr('style', 'background-color: #CFCFCF;text-align: center;font-weight: bold;');
                            }
                        }
                        $(row).attr('onclick', 'seleccionarFila(' + dataIndex + ');');
                        $(row).attr('id', 'filaTablaRangoAsistencia' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombreRangoAsistencia'+row); 
                           },
                        }
                     ],
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'PORCENTAJE MÍNIMO',
                            data: 'porcentaje'
                        },
                        {
                            title: 'FECHA DE INGRESO',
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
                $("#contenedorTablaRangoAsistencia").html('');
            }
            $("#mensajeTablaRangoAsistencia").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaRangoAsistencia").html('');
            if(xhr.status === 0){
                $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaRangoAsistencia").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
function limpiarFormIngresarRangoAsistencia()
{
    $('#formIngresarRangoAsistencia').each(function () {
        this.reset();
    });
    setTimeout(function() {$("#mensajeFormIngresarRango").html('');},1500);
}
$(function(){
    $("#formIngresarRangoAsistencia").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarRango").html('');
            $("#btnGuardarRango").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarRangoAsistencia();
                obtenerRangoAsistencia();
            }
            $("#btnGuardarRango").button('reset');
            $("#mensajeFormIngresarRango").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarRango").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarRango").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarRango").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarRango").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarRango").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarRango").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarRango").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarRango").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function cargandoRangoAsistencia(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
}
   
</script>
