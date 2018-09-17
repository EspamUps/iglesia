<script type="text/javascript">
function deshabilitarPeriodo(vari, ID,ID2){
    var _estado = $("#estadoPeriodoA"+ID).val();
    var _mensaje = "HABILITAR";
    if(_estado == true){
        _mensaje = "DESHABILITAR";
    }
    if (confirm('¿DESEAS '+_mensaje+' '+$("#nombrePeriodo"+ID).text()+'?')) {
        var _nombreClase = $("#btnDeshabilitarPeriodo" + ID + " i").attr('class');
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/periodos/modificarestadoperiodo',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID,numeroFila2: ID2 },
            beforeSend: function () {
                $("#btnDeshabilitarPeriodo" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaPeriodos").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    var table = $('#tablaPeriodos').DataTable();
                    table.row(data.numeroFila).data(data.tabla[data.numeroFila]).draw();
                    
                } else {
                    $("#btnDeshabilitarPeriodo" + ID).html('<i class="' + _nombreClase + '"></i>');
                }
                $("#mensajeTablaPeriodos").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnDeshabilitarPeriodo" + ID).html('<i class="' + _nombreClase + '"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}         
function eliminarPeriodo(vari, ID){
    if (confirm('¿DESEAS ELIMINAR '+$("#nombrePeriodo"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/periodos/eliminarperiodo',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarPeriodo" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaPeriodos").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    
                    $("#filaTablaPeriodos"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFila(data.numeroFila + 1);
                    } else {
                        seleccionarFila(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarPeriodo" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeTablaPeriodos").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarPeriodo" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}   
function seleccionarFila(ID)
{
    var menues2 = $("#tablaPeriodos tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaPeriodos" + ID + " td").removeAttr("style");
    $("#filaTablaPeriodos" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerPeriodos(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/periodos/obtenerperiodos',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaPeriodos").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaPeriodos").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaPeriodos"></table></div>');
                $('#tablaPeriodos').DataTable({
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
                        $(row).attr('id', 'filaTablaPeriodos' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombrePeriodo'+row); 
                           },
                        }
                     ],
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'NOMBRE',
                            data: 'nombrePeriodo'
                        },
                        {
                            title: 'INICIO',
                            data: 'fechaInicio'
                        },
                        {
                            title: 'FIN',
                            data: 'fechaFin'
                        },
                        {
                            title: 'FECHA DE REGISTRO',
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
                $("#contenedorTablaPeriodos").html('');
            }
            $("#mensajeTablaPeriodos").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaPeriodos").html('');
            if(xhr.status === 0){
                $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaPeriodos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
function limpiarFormIngresarPeriodo()
{
    $('#formIngresarPeriodo').each(function () {
        this.reset();
    });
    setTimeout(function() {$("#mensajeFormIngresarPeriodo").html('');},1500);
}
$(function(){
    $("#formIngresarPeriodo").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarPeriodo").html('');
            $("#btnGuardarPeriodo").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarPeriodo();
                obtenerPeriodos()
            }
            $("#btnGuardarPeriodo").button('reset');
            $("#mensajeFormIngresarPeriodo").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarPeriodo").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarPeriodo").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarPeriodo").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarPeriodo").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarPeriodo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarPeriodo").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarPeriodo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarPeriodo").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function cargandoPeriodos(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
}
   
</script>
