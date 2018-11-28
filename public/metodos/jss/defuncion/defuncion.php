<script type="text/javascript">
function validarIngresoDefuncion(f){
    var _validar = false;
    if(confirm("¿ESTAS SEGURO DE GUARDAR A ESTA DEFUNCIÓN, VERIFIQUE QUE LOS DATOS INGRESADOS SEAN LOS CORRECTOS UNA VEZ INGRESADOS NO PODRÁN ELIMINARSE O MODIFICARSE?")){
        _validar = true;
    }
    return _validar;
}
function limpiarFormularioDefuncion()
{
    $("#contenedorRestoDelFormulario").html('');
    setTimeout(function() {$("#mensajeFormIngresarDefuncion").html('');},1500);
}
function filtrarPersonaPorNombres(){
        var url = $("#rutaBase").text();
        var nombres = $("#nombres").val();
        var fechaNacimiento = $("#fechaNacimiento").val();
        var sinRequisitos = 1;
        if($('#sinRequisitos').is(':checked') ) {
            sinRequisitos = 2;
        }

        $.ajax({
            url : url+'/defuncion/filtrarpersonapornombres',
            type: 'post',
            dataType: 'JSON',
            data: {nombres:nombres,fechaNacimiento:fechaNacimiento,sinRequisitos:sinRequisitos},
            beforeSend: function(){
                cargandoDefuncion("#contenedorRestoDelFormulario");
                $("#mensajeFormIngresarDefuncion").html('');
                
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                console.log(data)
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
                    $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
}
function seleccionarFila(ID)
{
    var menues2 = $("#tablaDefuncion tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaDefuncion" + ID + " td").removeAttr("style");
    $("#filaTablaDefuncion" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerDefuncion(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/defuncion/obtenerdefuncion',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaDefuncion").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaDefuncion").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaDefuncion"></table></div>');
                $('#tablaDefuncion').DataTable({
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
                        $(row).attr('id', 'filaTablaDefuncion' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombreDefuncion'+row); 
                           },
                        }
                     ],
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'IDENTIFICACIÓN',
                            data: 'identificacion'
                        },
                        {
                            title: 'NOMBRES',
                            data: 'nombresPersona'
                        },
                        {
                            title: 'FECHA DE NACIMIENTO',
                            data: 'fechaNacimiento'
                        },
                        {
                            title: 'FECHA DE FALLECIDO',
                            data: 'fechaFallecimiento'
                        },
                        {
                            title: 'OPC.',
                            data: 'opciones'
                        }
                    ],
                });    
                seleccionarFila(0)
            }else{
                $("#contenedorTablaDefuncion").html('');
            }
            $("#mensajeTablaDefuncion").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaDefuncion").html('');
            if(xhr.status === 0){
                $("#mensajeTablaDefuncion").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaDefuncion").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaDefuncion").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaDefuncion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaDefuncion").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaDefuncion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaDefuncion").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
$(function(){
    $("#formIngresarDefuncion").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarDefuncion").html('');
            $("#btnGuardarDefuncion").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            console.log(data)
            if(data.validar==true){
                filtrarPersonaPorNombres();
                limpiarFormularioDefuncion();
                obtenerDefuncion();
            }
            $("#btnGuardarDefuncion").button('reset');
            $("#mensajeFormIngresarDefuncion").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarDefuncion").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarDefuncion").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 
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
                url : url+'/defuncion/filtrarpersonaporidentificacion',
                type: 'post',
                dataType: 'JSON',
                data: {identificacion:identificacion},
                beforeSend: function(){

                    cargandoDefuncion("#"+contenedor);
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
function cargandoDefuncion(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
}
   
</script>
