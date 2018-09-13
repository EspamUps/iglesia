<script type="text/javascript">
function EliminarConfigurarMisa(vari, ID){
    if (confirm('¿DESEAS ELIMINAR LA MISA '+$("#nombreMisa"+ID).text()+'?')) {
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/configurarmisa/eliminarconfigurarmisa',
            type: 'post',
            dataType: 'JSON',
            data: { id: vari, numeroFila: ID },
            beforeSend: function () {
                $("#btnEliminarConfigurarMisa" + ID).html('<i class="fa fa-spinner"></i>');
                $("#mensajeTablaConfigurarMisas").html('');
            },
            uploadProgress: function (event, position, total, percentComplete) {
            },
            success: function (data) {
                if (data.validar == true) {
                    $("#filaTablaConfigurarMisa"+data.numeroFila).remove();
                    if (data.numeroFila == 0) {
                        seleccionarFila(data.numeroFila + 1);
                    } else {
                        seleccionarFila(data.numeroFila - 1);
                    }
                } else {
                    $("#btnEliminarConfigurarMisa" + ID).html('<i class="fa fa-times"></i>');
                }
                $("#mensajeTablaConfigurarMisas").html(data.mensaje);
            },
            complete: function () {
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#btnEliminarConfigurarMisa" + ID).html('<i class="fa fa-times"></i>');
                if (xhr.status === 0) {
                    $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                } else if (xhr.status == 404) {
                    $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                } else if (xhr.status == 500) {
                    $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                } else if (errorThrown === 'parsererror') {
                    $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                } else if (errorThrown === 'timeout') {
                    $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                } else if (errorThrown === 'abort') {
                    $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                } else {
                    $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
    }
}
    
    
    
function cargandoConfigurarMisa(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}
function seleccionarFila(ID)
{
    var menues2 = $("#tablaConfigurarMisa tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaConfigurarMisa" + ID + " td").removeAttr("style");
    $("#filaTablaConfigurarMisa" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerConfigurarMisa(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/configurarmisa/obtenerconfigurarmisa',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaConfigurarMisas").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaConfigurarMisas").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaConfigurarMisa"></table></div>');
                $('#tablaConfigurarMisa').DataTable({
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
                        $(row).attr('id', 'filaTablaConfigurarMisa' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombreMisa'+row); 
                           },
                        }
                        
                     ],
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'MISA',
                            data: 'nombreMisa'
                        },
                        {
                            title: 'DESCRIPCIÓN',
                            data: 'descripcionMisa'
                        },
                        {
                            title: 'FECHA',
                            data: 'fechaMisa'
                        },
                        {
                            title: 'HORA INICIO',
                            data: 'horaInicio'
                        },
                        {
                            title: 'HORA FIN',
                            data: 'horaFin'
                        },
                        {
                            title: 'VALOR',
                            data: 'valorMisa'
                        },
                        {
                            title: 'SACERDOTE',
                            data: 'sacerdote'
                        },
                        {
                            title: 'OPC.',
                            data: 'opciones'
                        }
                    ],
                });    
                seleccionarFila(0)
            }else{
                $("#contenedorTablaPersonas").html('');
            }
            $("#mensajeTablaConfigurarMisas").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaPersonas").html('');
            if(xhr.status === 0){
                $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaConfigurarMisas").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
    
    
    
function limpiarFormIngresarConfigurarMisa()
{
    $('#formIngresarConfigurarMisa').each(function () {
        this.reset();
    });
    $("#idSacerdoteEncriptado").val('0');
    $("#contenedorDatosSacerdote").html('');
    $("#contenedorDatosLugar").html('');
    $("#mensajeContenedorDatos").html('');
    setTimeout(function() {$("#mensajeFormIngresarConfigurarMisa").html('');},1500);
}
function validarIngresoConfigurarMisa(f){
    var _validar = false;
    var valorMisa = parseFloat(f.valorMisa.value);
    if(valorMisa == 0){
        if(confirm("¿ESTAS SEGURO DE GUARDAR ESTA MISA SIN COSTO MONETARIO?")){
            _validar = true;
        }
    }
    else _validar=true ;
    return _validar;
}
$(function(){
    $("#formIngresarConfigurarMisa").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarConfigurarMisa").html('');
            $("#btnGuardarConfigurarMisa").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarConfigurarMisa();
                obtenerConfigurarMisa();
            }
            $("#btnGuardarConfigurarMisa").button('reset');
            $("#mensajeFormIngresarConfigurarMisa").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarConfigurarMisa").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarConfigurarMisa").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarConfigurarMisa").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarConfigurarMisa").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarConfigurarMisa").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarConfigurarMisa").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarConfigurarMisa").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarConfigurarMisa").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

    
    
function filtrarDatosLugarMisa(){
    var url = $("#rutaBase").text();
    var idLugarMisa = $("#selectLugarMisa").val();
    if(idLugarMisa == 0){
        $("#mensajeContenedorDatos").html('');
        $("#contenedorDatosLugar").html('');
    }else{
        $.ajax({
            url : url+'/configurarmisa/filtrardatoslugarmisa',
            type: 'post',
            dataType: 'JSON',
            data: {idLugarMisa:idLugarMisa},
            beforeSend: function(){
                cargandoSacerdotes("#contenedorDatosLugar");
                $("#mensajeContenedorDatos").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){

            },
            success: function(data){  
                if(data.validar == true)
                {
                     $("#contenedorDatosLugar").html(data.tabla);
                }else{
                     $("#contenedorDatosLugar").html('');
                }
                $("#mensajeContenedorDatos").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorDatosLugar").html('');
                if(xhr.status === 0){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeContenedorDatos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
} 
</script>
