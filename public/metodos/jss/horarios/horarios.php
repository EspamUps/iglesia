<script type="text/javascript">
function ModificarEstadoHoraHorario(vari, IDH,IDD){
    if (confirm('¿DESEAS MODIFICAR LAS HORAS '+$("#nombreHorarios"+IDH).text()+' DEL DÍA '+$("#nombreDia"+IDD).text()+'?')) {
        var url = $("#rutaBase").text();
        var _nombreClase = $("#btnModificarEstadoHoraHorario" + IDH + " i").attr('class');
        $.ajax({
            url : url+'/horarios/modificarestadohorahorario',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari},
            beforeSend: function () {
                $("#btnModificarEstadoHoraHorario" + IDH).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaHorarios").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    filtrarHorariosPorCurso();
                } else {
                    $("#btnModificarEstadoHoraHorario" + IDH).html('<i class="' + _nombreClase + '"></i>');
                }
                $("#mensajeTablaHorarios").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnModificarEstadoHoraHorario" + IDH).html('<i class="' + _nombreClase + '"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
    
function EliminarHoraHorario(vari, IDH,IDD){
    if (confirm('¿DESEAS ELIMINAR LAS HORAS '+$("#nombreHorarios"+IDH).text()+' DEL DÍA '+$("#nombreDia"+IDD).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/horarios/eliminarhorahorario',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari},
            beforeSend: function () {
                $("#btnEliminarHoraHorario" + IDH).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaHorarios").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    filtrarHorariosPorCurso();
                } else {
                    $("#btnEliminarHoraHorario" + IDH).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeTablaHorarios").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarHoraHorario" + IDH).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
    
function limpiarFormIngresarHoraHorario()
{
    $('#formIngresarHoraHorario').each(function () {
        this.reset();
    });
    $("#horaInicio").val('');
    $("#horaFin").val('');
    setTimeout(function() {$("#mensajeFormHoraHorario").html('');},1500);
}
$(function(){
    $("#formIngresarHoraHorario").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormHoraHorario").html('');
            $("#btnGuardarHoraHorario").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarHoraHorario();
                filtrarHorariosPorCurso();
            }
            $("#btnGuardarHoraHorario").button('reset');
            $("#mensajeFormHoraHorario").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarHoraHorario").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormHoraHorario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormHoraHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormHoraHorario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormHoraHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormHoraHorario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormHoraHorario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormHoraHorario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 
//
function obtenerIdHorario(vari,ID){
    $("#contenedorTitulo").html($("#nombreDia"+ID).text());
    $("#idHorarioEncriptado").val(vari);
    $("#mensajeFormHoraHorario").html('');
}



function filtrarHorariosPorCurso(){
    var url = $("#rutaBase").text();
    var idCurso = $("#selectCurso").val();
    if(idCurso == "0"){
        $("#mensajeTablaHorarios").html('');
        $("#contenedorTablaHorarios").html('');
    }else{
        
        $.ajax({
            url : url+'/horarios/filtrarhorarioporcurso',
            type: 'post',
            dataType: 'JSON',
            data: {idCurso:idCurso},
            beforeSend: function(){

                $("#mensajeTablaHorarios").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                console.log(data)
                if(data.validar == true){
                    $("#contenedorTablaHorarios").html(data.tabla);
                }else{
                    $("#contenedorTablaHorarios").html('');
                }
                $("#mensajeTablaHorarios").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorTablaHorarios").html('');
                if(xhr.status === 0){
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeTablaHorarios").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
    
}
//
//
//function seleccionarFila(ID)
//{
//    var menues2 = $("#tablaSacerdotes tbody tr td");
//    menues2.removeAttr("style");
//    menues2.css({ 'cursor': 'pointer' });
//    $("#filaTablaSacerdotes" + ID + " td").removeAttr("style");
//    $("#filaTablaSacerdotes" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
//}
//
function cargandoHorarios(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}
//
//function obtenerSacerdotes(){
//    var url = $("#rutaBase").text();
//    $.ajax({
//        url : url+'/sacerdote/obtenersacerdotes',
//        type: 'post',
//        dataType: 'JSON',
//        beforeSend: function(){
//            $("#mensajeTablaSacerdotes").html('');
//            
//        },
//        uploadProgress: function(event,position,total,percentComplete){
//        },
//        success: function(data){  
//            if(data.validar == true){
//                $("#contenedorTablaSacerdotes").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaSacerdotes"></table></div>');
//                $('#tablaSacerdotes').DataTable({
//                    destroy: true,
//                    order: [],
//                    data: data.tabla,
//                    'createdRow': function (row, data, dataIndex) {
//                        var division = dataIndex % 2;
//                        if (division == "0")
//                        {
//                            $(row).attr('style', 'background-color: #DCFBFF;text-align: center;font-weight: bold;');
//                        } else {
//                            $(row).attr('style', 'background-color: #CFCFCF;text-align: center;font-weight: bold;');
//                        }
//                        $(row).attr('onclick', 'seleccionarFila(' + dataIndex + ');');
//                        $(row).attr('id', 'filaTablaSacerdotes' + dataIndex);
//                    },
//                    'columnDefs': [
//                        {
//                           'targets': 2,
//                           'createdCell':  function (td, cellData, rowData, row, col) {
//                                $(td).attr('id','nombreSacerdote'+row); 
//                           },
//                        }
//                     ],
//                    columns: [
//                        {
//                            title: '#',
//                            data: '_j'
//                        },
//                        {
//                            title: 'DNI',
//                            data: 'identificacion'
//                        },
//                        {
//                            title: 'NOMBRES',
//                            data: 'nombres'
//                        },
//                        {
//                            title: 'APELLIDOS',
//                            data: 'apellidos'
//                        },
//                        {
//                            title: 'FECHA DE NACIMIENTO',
//                            data: 'fechaNacimiento'
//                        },
//                        {
//                            title: 'EDAD',
//                            data: 'edad'
//                        },
//                        {
//                            title: 'TELÉFONO',
//                            data: 'numeroTelefono'
//                        },
//                        {
//                            title: 'PROVINCIA',
//                            data: 'provincia'
//                        },
//                        {
//                            title: 'CANTÓN',
//                            data: 'canton'
//                        },
//                        {
//                            title: 'PARROQUIA',
//                            data: 'parroquia'
//                        },
//                        {
//                            title: 'DIRECCIÓN',
//                            data: 'direccion'
//                        },
//                        {
//                            title: 'REFERENCIA',
//                            data: 'referencia'
//                        },
//                        {
//                            title: 'FECHA DE REGISTRO',
//                            data: 'fechaRegistro'
//                        },
//                        {
//                            title: 'OPC.',
//                            data: 'opciones'
//                        }
//                    ],
//                });    
//                seleccionarFila(0)
//            }else{
//                $("#contenedorTablaSacerdotes").html('');
//            }
//            $("#mensajeTablaSacerdotes").html(data.mensaje);
//        },
//        complete: function(){
//        },
//        error: function(xhr, textStatus, errorThrown) {
//            $("#contenedorTablaSacerdotes").html('');
//            if(xhr.status === 0){
//                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
//            }else if(xhr.status == 404){
//                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
//            }else if(xhr.status == 500){
//                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
//            }else if(errorThrown === 'parsererror'){
//                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
//            }else if(errorThrown === 'timeout'){
//                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
//            }else if(errorThrown === 'abort'){
//                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
//            }else{
//                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
//            }
//        }
//    }); 
//}
</script>