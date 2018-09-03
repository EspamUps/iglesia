<script type="text/javascript">
function eliminarProvincia(vari, ID) {
    if (confirm('¿DESEAS ELIMINAR LA PROVINCIA '+$("#nombreProvincia"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/provincias/eliminarprovincia',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarProvincia" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeFormProvincias").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    $("#filaTablaProvincias"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFilaProvincias(data.numeroFila + 1);
                    } else {
                        seleccionarFilaProvincias(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarProvincia" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeFormProvincias").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarProvincia" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
    
function limpiarFormIngresarProvincia()
{
    $('#formIngresarProvincias').each(function () {
        this.reset();
    });
    setTimeout(function() {$("#mensajeFormProvincias").html('');},1500);
}
$(function(){
    $("#formIngresarProvincias").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormProvincias").html('');
            $("#btnGuardarProvincia").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            console.log(data)
            if(data.validar==true){
                limpiarFormIngresarProvincia();
                obtenerProvincias();
            }
            $("#btnGuardarProvincia").button('reset');
            $("#mensajeFormProvincias").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarProvincia").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function seleccionarFilaProvincias(ID)
{
    var menues2 = $("#tablaProvincias tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaProvincias" + ID + " td").removeAttr("style");
    $("#filaTablaProvincias" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerProvincias(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/provincias/obtenerprovincias',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeFormProvincias").html('');
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#formIngresarProvincias").html(data.formProvincias);
                $("#contenedorTablaProvincias").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaProvincias"></table></div>');
                $('#tablaProvincias').DataTable({
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
                        $(row).attr('onclick', 'seleccionarFilaProvincias(' + dataIndex + ');');
                        $(row).attr('id', 'filaTablaProvincias' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 2,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                               if(rowData.validarBoton ==false){
                                    $(td).html('<button id="btnEliminarProvincia'+row+'" title="ELIMINAR '+rowData.nombreProvincia+'" onclick="eliminarProvincia(\''+rowData.idProvinciaEncriptado+'\','+row+')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times"></i></button>'); 
                               }else{
                                    $(td).html(''); 
                               }
                           },
                        },
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombreProvincia'+row); 
                           },
                        }
                     ],
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'PROVINCIA',
                            data: 'nombreProvincia'
                        },
                        {
                            title: 'OPCIONES',
                            data: 'botones'
                        },
                    ],
                });    
                seleccionarFilaProvincias(0);
            }else{
                $("#formIngresarProvincias").html('');
                $("#contenedorTablaProvincias").html('');
            }
            $("#mensajeFormProvincias").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaProvincias").html('');
            $("#formIngresarProvincias").html('');
            if(xhr.status === 0){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormProvincias").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
    
}
function cargandoProvincias(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    $("#formIngresarProvincias").html('');
    $("#contenedorTablaCantones").html('');
    $("#formIngresarCantones").html('');
}
</script>
