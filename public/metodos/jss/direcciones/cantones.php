<script type="text/javascript">
function eliminarCanton(vari, ID) {
    if (confirm('¿DESEAS ELIMINAR EL CANTÓN '+$("#nombreCanton"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/cantones/eliminarcanton',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarCanton" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeFormCantones").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    $("#filaTablaCantones"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFilaCantones(data.numeroFila + 1);
                    } else {
                        seleccionarFilaCantones(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarCanton" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeFormCantones").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarCanton" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
    
function limpiarFormIngresarCanton()
{
    $("#nombreCanton").val('');
    setTimeout(function() {$("#mensajeFormCantones").html('');},1500);
}
$(function(){
    $("#formIngresarCantones").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormCantones").html('');
            $("#btnGuardarCanton").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            console.log(data)
            if(data.validar==true){
                limpiarFormIngresarCanton();
                filtrarCantonesPorProvincia();
            }
            $("#btnGuardarCanton").button('reset');
            $("#mensajeFormCantones").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarCanton").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function seleccionarFilaCantones(ID)
{
    var menues2 = $("#tablaCantones tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaCantones" + ID + " td").removeAttr("style");
    $("#filaTablaCantones" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}

function filtrarCantonesPorProvincia(){
    var url = $("#rutaBase").text();
    var idProvincia = $("#selectProvinciasCantones").val();
    $("#mensajeFormCantones").html('');
    if(idProvincia == "0"){
        $("#contenedorTablaCantones").html('');
    }else{
        $.ajax({
            url : url+'/cantones/filtrarcantonesporprovincia',
            type: 'post',
            dataType: 'JSON',
            data:{id:idProvincia},
            beforeSend: function(){
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#contenedorTablaCantones").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaCantones"></table></div>');
                    $('#tablaCantones').DataTable({
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
                            $(row).attr('onclick', 'seleccionarFilaCantones(' + dataIndex + ');');
                            $(row).attr('id', 'filaTablaCantones' + dataIndex);
                        },
                        'columnDefs': [
                            {
                               'targets': 1,
                               'createdCell':  function (td, cellData, rowData, row, col) {
                                    $(td).attr('id','nombreCanton'+row); 
                               },
                            }
                         ],
                        columns: [
                            {
                                title: '#',
                                data: '_j'
                            },
                            {
                                title: 'CANTÓN',
                                data: 'nombreCanton'
                            },
                            {
                                title: 'OPCIONES',
                                data: 'botones'
                            },
                        ],
                    });    
                    seleccionarFilaCantones(0);
                }else{
                    $("#contenedorTablaCantones").html('');
                }
                $("#mensajeFormCantones").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorTablaCantones").html('');
                if(xhr.status === 0){
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
    
}


function obtenerFormCantones(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/cantones/obtenerformcantones',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#formIngresarCantones").html(data.formCantones);
            }else{
                $("#formIngresarCantones").html('');
                $("#contenedorTablaCantones").html('');
            }
            $("#mensajeFormCantones").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaCantones").html('');
            $("#formIngresarCantones").html('');
            if(xhr.status === 0){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormCantones").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
    
}
function cargandoCantones(contenedor){
    
    $("#formIngresarProvincias").html('');
    $("#contenedorTablaProvincias").html('');
    $("#contenedorTablaCantones").html('');
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
   
}
</script>

