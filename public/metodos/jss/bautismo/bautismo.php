<script type="text/javascript">
function validarIngresoBautismo(f){
    var _validar = false;
    if(confirm("¿ESTAS SEGURO DE GUARDAR A ESTE BAUTIZO, VERIFIQUE QUE LOS DATOS INGRESADOS SEAN LOS CORRECTOS UNA VEZ INGRESADOS NO PODRÁN ELIMINARSE O MODIFICARSE?")){
        _validar = true;
    }
    return _validar;
}
function limpiarFormularioBautismo()
{
    $("#contenedorRestoDelFormulario").html('');
    setTimeout(function() {$("#mensajeFormIngresarBautismo").html('');},1500);
}
function filtrarPersonaPorNombres(){
        var url = $("#rutaBase").text();
        var nombres = $("#nombres").val();
        var fechaNacimiento = $("#fechaNacimiento").val();
        $.ajax({
            url : url+'/bautismo/filtrarpersonapornombres',
            type: 'post',
            dataType: 'JSON',
            data: {nombres:nombres,fechaNacimiento:fechaNacimiento},
            beforeSend: function(){
                cargandoBautismo("#contenedorRestoDelFormulario");
                $("#mensajeFormIngresarBautismo").html('');
                
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
                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
}
function seleccionarFila(ID)
{
    var menues2 = $("#tablaBautismo tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaBautismo" + ID + " td").removeAttr("style");
    $("#filaTablaBautismo" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerBautismos(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/bautismo/obtenerbautismos',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaBautismo").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaBautismo").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaBautismo"></table></div>');
                $('#tablaBautismo').DataTable({
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
                        $(row).attr('id', 'filaTablaBautismo' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 1,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','nombreBautismo'+row); 
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
                            title: 'OPC.',
                            data: 'opciones'
                        }
                    ],
                });    
                seleccionarFila(0)
            }else{
                $("#contenedorTablaBautismo").html('');
            }
            $("#mensajeTablaBautismo").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaBautismo").html('');
            if(xhr.status === 0){
                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
$(function(){
    $("#formIngresarBautismo").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarBautismo").html('');
            $("#btnGuardarBautismo").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                filtrarPersonaPorNombres();
                limpiarFormularioBautismo();
                obtenerBautismos();
            }
            $("#btnGuardarBautismo").button('reset');
            $("#mensajeFormIngresarBautismo").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarBautismo").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
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
                url : url+'/bautismo/filtrarpersonaporidentificacion',
                type: 'post',
                dataType: 'JSON',
                data: {identificacion:identificacion},
                beforeSend: function(){

                    cargandoBautismo("#"+contenedor);
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
function cargandoBautismo(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
}
   
</script>
