<script type="text/javascript">

function limpiarFormularioMatrimonio()
{
    $("#contenedorRestoDelFormulario").html('');
    setTimeout(function() {$("#mensajeFormIngresarMatrimonio").html('');},1500);
}
function filtrarMatrimonioPorEsposoEsposa(){
        var url = $("#rutaBase").text();
        var nombresEsposo = $("#nombresEsposo").val();
        var fechaNacimientoEsposo = $("#fechaNacimientoEsposo").val();
        var nombresEsposa = $("#nombresEsposa").val();
        var fechaNacimientoEsposa = $("#fechaNacimientoEsposa").val();
        $.ajax({
            url : url+'/matrimonio/filtrarmatrimonioporesposoyesposa',
            type: 'post',
            dataType: 'JSON',
            data: {nombresEsposo:nombresEsposo,fechaNacimientoEsposo:fechaNacimientoEsposo,nombresEsposa:nombresEsposa,fechaNacimientoEsposa:fechaNacimientoEsposa},
            beforeSend: function(){
                cargandoMatrimonio("#contenedorRestoDelFormulario");
                $("#mensajeFormIngresarMatrimonio").html('');
                
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

//function filtrarPersonaPorNombres(){
//        var url = $("#rutaBase").text();
//        var primerApellido = $("#primerApellido").val();
//        var segundoApellido = $("#segundoApellido").val();
//        var primerNombre = $("#primerNombre").val();
//        var segundoNombre = $("#segundoNombre").val();
//        var fechaNacimiento = $("#fechaNacimiento").val();
//        $.ajax({
//            url : url+'/bautismo/filtrarpersonapornombres',
//            type: 'post',
//            dataType: 'JSON',
//            data: {primerApellido:primerApellido,segundoApellido:segundoApellido,primerNombre:primerNombre,segundoNombre:segundoNombre,fechaNacimiento:fechaNacimiento},
//            beforeSend: function(){
//
//                $("#btnBuscarPersona").button('loading');
//                $("#mensajeFormIngresarBautismo").html('');
//                cargandoBautismo('#contenedorRestoDelFormulario');
//            },
//            uploadProgress: function(event,position,total,percentComplete){
//            },
//            success: function(data){  
//                if(data.validar == true){
//                    $("#contenedorRestoDelFormulario").html(data.tabla);
//                }else{
//                    $("#contenedorRestoDelFormulario").html(data.mensaje);
//                }
//                $("#btnBuscarPersona").button('reset');
////                $("#mensajeFormIngresarBautismo").html(data.mensaje);
//            },
//            complete: function(){
//            },
//            error: function(xhr, textStatus, errorThrown) {
//                $("#contenedorRestoDelFormulario").html('');
//                $("#btnBuscarPersona").button('reset');
//                if(xhr.status === 0){
//                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
//                }else if(xhr.status == 404){
//                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
//                }else if(xhr.status == 500){
//                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
//                }else if(errorThrown === 'parsererror'){
//                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
//                }else if(errorThrown === 'timeout'){
//                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
//                }else if(errorThrown === 'abort'){
//                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
//                }else{
//                    $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
//                }
//            }
//        }); 
//    
//    
//}
//function seleccionarFila(ID)
//{
//    var menues2 = $("#tablaBautismo tbody tr td");
//    menues2.removeAttr("style");
//    menues2.css({ 'cursor': 'pointer' });
//    $("#filaTablaBautismo" + ID + " td").removeAttr("style");
//    $("#filaTablaBautismo" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
//}
//function obtenerBautismos(){
//    var url = $("#rutaBase").text();
//    $.ajax({
//        url : url+'/bautismo/obtenerbautismos',
//        type: 'post',
//        dataType: 'JSON',
//        beforeSend: function(){
//            $("#mensajeTablaBautismo").html('');
//            
//        },
//        uploadProgress: function(event,position,total,percentComplete){
//        },
//        success: function(data){  
//            if(data.validar == true){
//                $("#contenedorTablaBautismo").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaBautismo"></table></div>');
//                $('#tablaBautismo').DataTable({
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
//                        $(row).attr('id', 'filaTablaBautismo' + dataIndex);
//                    },
//                    'columnDefs': [
//                        {
//                           'targets': 1,
//                           'createdCell':  function (td, cellData, rowData, row, col) {
//                                $(td).attr('id','nombreBautismo'+row); 
//                           },
//                        }
//                     ],
//                    columns: [
//                        {
//                            title: '#',
//                            data: '_j'
//                        },
//                        {
//                            title: 'NOMBRES',
//                            data: 'nombresPersona'
//                        },
//                        {
//                            title: 'FECHA DE NACIMIENTO',
//                            data: 'fechaNacimiento'
//                        },
//                        {
//                            title: 'OPC.',
//                            data: 'opciones'
//                        }
//                    ],
//                });    
//                seleccionarFila(0)
//            }else{
//                $("#contenedorTablaBautismo").html('');
//            }
//            $("#mensajeTablaBautismo").html(data.mensaje);
//        },
//        complete: function(){
//        },
//        error: function(xhr, textStatus, errorThrown) {
//            $("#contenedorTablaBautismo").html('');
//            if(xhr.status === 0){
//                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
//            }else if(xhr.status == 404){
//                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
//            }else if(xhr.status == 500){
//                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
//            }else if(errorThrown === 'parsererror'){
//                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
//            }else if(errorThrown === 'timeout'){
//                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
//            }else if(errorThrown === 'abort'){
//                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
//            }else{
//                $("#mensajeTablaBautismo").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
//            }
//        }
//    }); 
//}

//$(function(){
//    $("#formIngresarBautismo").ajaxForm({
//        beforeSend: function(){
//            $("#mensajeFormIngresarBautismo").html('');
//            $("#btnGuardarBautismo").button('loading');
//        },
//        uploadProgress: function(event,position,total,percentComplete){
//
//        },
//        success: function(data){
//            if(data.validar==true){
//                filtrarPersonaPorNombres();
//                limpiarFormularioBautismo();
//                obtenerBautismos();
//            }
//            $("#btnGuardarBautismo").button('reset');
//            $("#mensajeFormIngresarBautismo").html(data.mensaje);
//        },
//        complete: function(){
//        },
//        error: function(xhr, textStatus, errorThrown) {
//            $("#btnGuardarBautismo").button('reset');
//            if(xhr.status === 0){
//                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
//            }else if(xhr.status == 404){
//                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
//            }else if(xhr.status == 500){
//                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
//            }else if(errorThrown === 'parsererror'){
//                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
//            }else if(errorThrown === 'timeout'){
//                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
//            }else if(errorThrown === 'abort'){
//                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
//            }else{
//                $("#mensajeFormIngresarBautismo").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
//            }
//        }
//    });    
//}); 

function cargandoMatrimonio(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
}
   
</script>
