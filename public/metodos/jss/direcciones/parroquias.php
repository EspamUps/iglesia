<script type="text/javascript">
function eliminarParroquia(vari, ID) {
    if (confirm('¿DESEAS ELIMINAR LA PARROQUIA '+$("#nombreParroquia"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/parroquias/eliminarparroquia',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarParroquia" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeFormParroquias").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    $("#filaTablaParroquias"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFilaParroquias(data.numeroFila + 1);
                    } else {
                        seleccionarFilaParroquias(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarParroquia" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeFormParroquias").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarParroquia" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
//    
function limpiarFormIngresarParroquias()
{
    $("#nombreParroquia").val('');
    setTimeout(function() {$("#mensajeFormParroquias").html('');},1500);
}
$(function(){
    $("#formIngresarParroquias").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormParroquias").html('');
            $("#btnGuardarParroquia").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarParroquias();
                filtrarParroquiasPorCanton();
            }
            $("#btnGuardarParroquia").button('reset');
            $("#mensajeFormParroquias").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarParroquia").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 
//
function seleccionarFilaParroquias(ID)
{
    var menues2 = $("#tablaParroquias tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaParroquias" + ID + " td").removeAttr("style");
    $("#filaTablaParroquias" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
//
function filtrarParroquiasPorCanton(){
    var url = $("#rutaBase").text();
    var idConfigurarCantonProvincia = $("#selectCantonesParroquias").val();
    $("#mensajeFormParroquias").html('');
    if(idConfigurarCantonProvincia == "0"){
        $("#contenedorTablaParroquias").html('');
    }else{
        $.ajax({
            url : url+'/parroquias/filtrarparroquiasporcanton',
            type: 'post',
            dataType: 'JSON',
            data:{id:idConfigurarCantonProvincia},
            beforeSend: function(){
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#contenedorTablaParroquias").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaParroquias"></table></div>');
                    $('#tablaParroquias').DataTable({
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
                            $(row).attr('onclick', 'seleccionarFilaParroquias(' + dataIndex + ');');
                            $(row).attr('id', 'filaTablaParroquias' + dataIndex);
                        },
                        'columnDefs': [
                            {
                               'targets': 1,
                               'createdCell':  function (td, cellData, rowData, row, col) {
                                    $(td).attr('id','nombreParroquia'+row); 
                               },
                            }
                         ],
                        columns: [
                            {
                                title: '#',
                                data: '_j'
                            },
                            {
                                title: 'PARRÓQUIA',
                                data: 'nombreParroquia'
                            },
                            {
                                title: 'OPCIONES',
                                data: 'botones'
                            },
                        ],
                    });    
                    seleccionarFilaParroquias(0);
                }else{
                   $("#contenedorTablaParroquias").html('');
                }
                $("#mensajeFormParroquias").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorTablaParroquias").html('');
                if(xhr.status === 0){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormCantonesmensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">sOCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
    
}
//
//


function filtrarSelectCantonesPorProvincia(){
    var url = $("#rutaBase").text();
    var idProvincia = $("#selectProvinciasParroquias").val();
    $("#contenedorTablaParroquias").html('');
    if(idProvincia == "0"){
        $("#mensajeFormParroquias").html('');
        $("#selectCantonesParroquias").html('<option value="0">SELECCIONE UN CANTÓN</option>');
    }else{
        $.ajax({
            url : url+'/parroquias/filtrarcantonesporprovincia',
            type: 'post',
            dataType: 'JSON',
            data: {idProvincia: idProvincia},
            beforeSend: function(){
                $("#mensajeFormParroquias").html('');
                $("#selectCantonesParroquias").html('<option value="0">CARGANDO...</option>');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#selectCantonesParroquias").html(data.optionCantones);
                }else{
                    $("#selectCantonesParroquias").html('<option value="0">SELECCIONE UN CANTÓN</option>');
                }
                $("#mensajeFormParroquias").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#selectCantonesParroquias").html('<option value="0">SELECCIONE UN CANTÓN</option>');
                if(xhr.status === 0){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}

function obtenerFormParroquias(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/parroquias/obtenerformparroquias',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#formIngresarParroquias").html(data.formParroquias);
            }else{
                $("#formIngresarCantones").html('');
                $("#contenedorTablaParroquias").html('');
            }
            $("#mensajeFormParroquias").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaParroquias").html('');
            $("#formIngresarParroquias").html('');
            if(xhr.status === 0){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormParroquias").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
    
}
function cargandoParroquias(contenedor){
    
    $("#formIngresarProvincias").html('');
    $("#contenedorTablaProvincias").html('');
    $("#contenedorTablaCantones").html('');
    $("#contenedorTablaParroquias").html('');
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
   
}
</script>

