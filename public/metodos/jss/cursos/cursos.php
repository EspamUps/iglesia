<script type="text/javascript">
function deshabilitarCurso(vari, ID,ID2){
    var _estado = $("#estadoCursoA").val();
    var _mensaje = "HABILITAR";
    if(_estado == true){
        _mensaje = "DESHABILITAR";
    }
    if (confirm('¿DESEAS '+_mensaje+' '+$("#nombreCurso"+ID).text()+'?')) {
        var _nombreClase = $("#btnDeshabilitarCurso" + ID + " i").attr('class');
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/cursos/modificarestadocurso',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID,numeroFila2: ID2 },
            beforeSend: function () {
                $("#btnDeshabilitarCurso" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaCurso").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                console.log(data)
                if (data.validar == true) {
                    var table = $('#tablaCruso').DataTable();
                    table.row(data.numeroFila).data(data.tabla[data.numeroFila]).draw();
                    
                } else {
                    $("#btnDeshabilitarCurso" + ID).html('<i class="' + _nombreClase + '"></i>');
                }
                $("#mensajeTablaCurso").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnDeshabilitarCurso" + ID).html('<i class="' + _nombreClase + '"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
            
            
            
function eliminarCurso(vari, ID){
    if (confirm('¿DESEAS ELIMINAR '+$("#nombreCurso"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/cursos/eliminarcurso',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarCurso" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaCurso").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                console.log(data)
                if (data.validar == true) {
                    
                    $("#filaTablaCurso"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFila(data.numeroFila + 1);
                    } else {
                        seleccionarFila(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarCurso" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeTablaCurso").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarCurso" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
//    
function seleccionarFila(ID)
{
    var menues2 = $("#tablaCruso tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaCurso" + ID + " td").removeAttr("style");
    $("#filaTablaCurso" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerCursos(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/cursos/obtenercursos',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaCurso").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaCurso").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaCruso"></table></div>');
                $('#tablaCruso').DataTable({
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
                        $(row).attr('id', 'filaTablaCurso' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombreCurso'+row); 
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
                            data: 'nombreCurso'
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
                $("#contenedorTablaCurso").html('');
            }
            $("#mensajeTablaCurso").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaCurso").html('');
            if(xhr.status === 0){
                $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
////    
function limpiarFormIngresarCurso()
{
    $('#formIngresarCurso').each(function () {
        this.reset();
    });
    setTimeout(function() {$("#mensajeFormIngresarCurso").html('');},1500);
}
$(function(){
    $("#formIngresarCurso").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarCurso").html('');
            $("#btnGuardarCurso").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarCurso();
                obtenerCursos()
            }
            $("#btnGuardarCurso").button('reset');
            $("#mensajeFormIngresarCurso").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarCurso").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarCurso").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarCurso").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarCurso").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarCurso").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarCurso").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function cargandoCursos(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
}
   
</script>
