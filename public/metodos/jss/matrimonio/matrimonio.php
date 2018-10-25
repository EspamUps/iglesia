<script type="text/javascript">
function filtrarPersonaPorIdentificacion(event,idDom,contenedor){
    var codigo = event.which || event.keyCode;
    if (codigo==13){
        return false;
    }else{
        var url = $("#rutaBase").text();
        var identificacion = $("#"+idDom).val();
        if(identificacion.length < 10){
            $("#"+contenedor).html('');
        }else{
            $.ajax({
                url : url+'/matrimonio/filtrarpersonaporidentificacion',
                type: 'post',
                dataType: 'JSON',
                data: {identificacion:identificacion},
                beforeSend: function(){

                    cargandoMatrimonio("#"+contenedor);
                },
                uploadProgress: function(event,position,total,percentComplete){
                },
                success: function(data){  
                    if(data.validar == true){
                        $("#"+contenedor).html(data.tabla);
                    }else{
                       $("#"+contenedor).html(data.mensaje);
                    }
                },
                complete: function(){
                },
                error: function(xhr, textStatus, errorThrown) {
                    $("#"+contenedor).html('');
                    if(xhr.status === 0){
                        $("#"+contenedor).html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                    }else if(xhr.status == 404){
                        $("#"+contenedor).html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                    }else if(xhr.status == 500){
                        $("#"+contenedor).html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                    }else if(errorThrown === 'parsererror'){
                        $("#"+contenedor).html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                    }else if(errorThrown === 'timeout'){
                        $("#"+contenedor).html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                    }else if(errorThrown === 'abort'){
                        $("#"+contenedor).html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                    }else{
                        $("#"+contenedor).html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                    }
                }
            }); 
        }
    }
}

function limpiarFormularioMatrimonio()
{
    $("#contenedorRestoDelFormulario").html('');
    setTimeout(function() {$("#mensajeFormIngresarMatrimonio").html('');},1500);
}
function filtrarMatrimonioPorEsposoEsposa(){
        var url = $("#rutaBase").text();
        var identificacionEsposo = $("#identificacionEsposo").val();
        var identificacionEsposa = $("#identificacionEsposa").val();
        $.ajax({
            url : url+'/matrimonio/filtrarmatrimonioporesposoyesposa',
            type: 'post',
            dataType: 'JSON',
            data: {identificacionEsposo:identificacionEsposo,identificacionEsposa:identificacionEsposa},
            beforeSend: function(){
                cargandoMatrimonio("#contenedorRestoDelFormulario");
                $("#mensajeFormIngresarMatrimonio").html('');
                
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#contenedorRestoDelFormulario").html(data.tabla);
                }else{
                    $("#contenedorRestoDelFormulario").html(data.mensaje);
                }
                $("#btnBuscarPersona").button('reset');
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorRestoDelFormulario").html('');
                if(xhr.status === 0){
                    $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
}
function seleccionarFila(ID)
{
    var menues2 = $("#tablaMatrimonio tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaMatrimonio" + ID + " td").removeAttr("style");
    $("#filaTablaMatrimonio" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerMatrimonios(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/matrimonio/obtenermatrimonios',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaMatrimonio").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaMatrimonio").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaMatrimonio"></table></div>');
                $('#tablaMatrimonio').DataTable({
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
                        $(row).attr('id', 'filaTablaMatrimonio' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombresMatrimonio'+row); 
                           },
                        }
                     ],
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'MATRIMONIO',
                            data: 'nombresMatrimonio'
                        },
                        {
                            title: 'FECHAS DE NACIMIENTO',
                            data: 'fechasNacimientoMatrimonio'
                        },
                        {
                            title: 'OPC.',
                            data: 'opciones'
                        }
                    ],
                });    
                seleccionarFila(0)
            }else{
                $("#contenedorTablaMatrimonio").html('');
            }
            $("#mensajeTablaMatrimonio").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#mensajeTablaMatrimonio").html('');
            if(xhr.status === 0){
                $("#mensajeTablaMatrimonio").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaMatrimonio").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaMatrimonio").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaMatrimonio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaMatrimonio").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaMatrimonio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaMatrimonio").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}

$(function(){
    $("#formIngresarMatrimonio").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarMatrimonio").html('');
            $("#btnGuardarMatrimonio").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                filtrarMatrimonioPorEsposoEsposa();
                limpiarFormularioMatrimonio();
                obtenerMatrimonios();
            }
            $("#btnGuardarMatrimonio").button('reset');
            $("#mensajeFormIngresarMatrimonio").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarMatrimonio").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarMatrimonio").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function cargandoMatrimonio(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
}
   
</script>
