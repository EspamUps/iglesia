<script type="text/javascript">
function EliminarDocente(vari, ID){
    if (confirm('¿DESEAS ELIMINAR A '+$("#nombreDocente"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/docentes/eliminardocente',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarDocente" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaDocente").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    $("#filaTablaDocentes"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFila(data.numeroFila + 1);
                    } else {
                        seleccionarFila(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarDocente" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeTablaDocente").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarDocente" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
//    
//    
function validarIngresoDocente(f){
    var _validar = false;
    if(confirm("¿ESTAS SEGURO DE GUARDAR A ESTE DOCENTE?")){
        _validar = true;
    }
    return _validar;
}
//    
function limpiarFormIngresarDocente()
{
    $('#formIngresoDocente').each(function () {
        this.reset();
    });
    $("#contenedorDatosDocente").html('');
    setTimeout(function() {$("#mensajeFormIngresoDocente").html('');},1500);
}
$(function(){
    $("#formIngresoDocente").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresoDocente").html('');
            $("#btnGuardarDocente").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarDocente();
                obtenerDocentes();
            }
            $("#btnGuardarDocente").button('reset');
            $("#mensajeFormIngresoDocente").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarDocente").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 
//
function filtrarDocentePorIdentificacion(event){
    var codigo = event.which || event.keyCode;
    if (codigo==13){
        return false;
    }else{
        var url = $("#rutaBase").text();
        var identificacion = $("#identificacion").val();
        if(identificacion.length < 10){
            $("#mensajeFormIngresoDocente").html('');
            $("#contenedorDatosDocente").html('');
        }else{
            $.ajax({
                url : url+'/docentes/filtrardocenteporidentificacion',
                type: 'post',
                dataType: 'JSON',
                data: {identificacion:identificacion},
                beforeSend: function(){

                    $("#mensajeFormIngresoDocente").html('');
                    cargandoDocentes('#contenedorDatosDocente');
                },
                uploadProgress: function(event,position,total,percentComplete){
                },
                success: function(data){  
                    if(data.validar == true){
                        $("#contenedorDatosDocente").html(data.tabla);
                    }else{
                        $("#contenedorDatosDocente").html('');
                    }
                    $("#mensajeFormIngresoDocente").html(data.mensaje);
                },
                complete: function(){
                },
                error: function(xhr, textStatus, errorThrown) {
                    $("#contenedorDatosDocente").html('');
                    if(xhr.status === 0){
                        $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                    }else if(xhr.status == 404){
                        $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                    }else if(xhr.status == 500){
                        $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                    }else if(errorThrown === 'parsererror'){
                        $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                    }else if(errorThrown === 'timeout'){
                        $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                    }else if(errorThrown === 'abort'){
                        $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                    }else{
                        $("#mensajeFormIngresoDocente").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                    }
                }
            }); 
        }
    }
}
//
//
function seleccionarFila(ID)
{
    var menues2 = $("#tablaDocente tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaDocentes" + ID + " td").removeAttr("style");
    $("#filaTablaDocentes" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}

function cargandoDocentes(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

function obtenerDocentes(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/docentes/obtenerdocentes',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaDocente").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            
            if(data.validar == true){
                $("#contenedorTablaDocente").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaDocente"></table></div>');
                $('#tablaDocente').DataTable({
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
                        $(row).attr('id', 'filaTablaDocentes' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 2,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombreDocente'+row); 
                           },
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
                            title: 'FECHA DE NACIMIENTO',
                            data: 'fechaNacimiento'
                        },
                        {
                            title: 'EDAD',
                            data: 'edad'
                        },
                        {
                            title: 'TELÉFONO',
                            data: 'numeroTelefono'
                        },
                        {
                            title: 'PROVINCIA',
                            data: 'provincia'
                        },
                        {
                            title: 'CANTÓN',
                            data: 'canton'
                        },
                        {
                            title: 'PARROQUIA',
                            data: 'parroquia'
                        },
                        {
                            title: 'DIRECCIÓN',
                            data: 'direccion'
                        },
                        {
                            title: 'REFERENCIA',
                            data: 'referencia'
                        },
                        {
                            title: 'FECHA DE REGISTRO',
                            data: 'fechaRegistro'
                        },
                        {
                            title: 'OPC.',
                            data: 'opciones'
                        }
                    ],
                });    
                seleccionarFila(0)
            }else{
                $("#contenedorTablaDocente").html('');
            }
            $("#mensajeTablaDocente").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaDocente").html('');
            if(xhr.status === 0){
                $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaDocente").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
</script>