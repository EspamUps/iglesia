<script>
    
//function limpiarFormIngresarPersona()
//{
//    $('#formIngresoPersona').each(function () {
//        this.reset();
//    });
//    $("#selectCantones").html('<option value="0">SELECCIONE UN CANTÓN</option>');
//    $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
//    setTimeout(function() {$("#mensajeFormIngresoPersona").html('');},1500);
////}
//$(function(){
//    $("#formIngresoPersona").ajaxForm({
//        beforeSend: function(){
//            $("#mensajeFormIngresoPersona").html('');
//            $("#btnGuardarPersona").button('loading');
//        },
//        uploadProgress: function(event,position,total,percentComplete){
//
//        },
//        success: function(data){
//            if(data.validar==true){
//                limpiarFormIngresarPersona();
//                obtenerPersonas();
//            }
//            $("#btnGuardarPersona").button('reset');
//            $("#mensajeFormIngresoPersona").html(data.mensaje);
//        },
//        complete: function(){
//        },
//        error: function(xhr, textStatus, errorThrown) {
//            $("#btnGuardarPersona").button('reset');
//            if(xhr.status === 0){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
//            }else if(xhr.status == 404){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
//            }else if(xhr.status == 500){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
//            }else if(errorThrown === 'parsererror'){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
//            }else if(errorThrown === 'timeout'){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
//            }else if(errorThrown === 'abort'){
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
//            }else{
//                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
//            }
//        }
//    });    
//}); 


function seleccionarFila(ID)
{
    var menues2 = $("#tablaUsuarios tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaUsuarios" + ID + " td").removeAttr("style");
    $("#filaTablaUsuarios" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}

function cargandoUsuarios(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

function obtenerUsuarios(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/usuario/obtenerusuarios',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaUsuarios").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaUsuarios").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaUsuarios"></table></div>');
                $('#tablaUsuarios').DataTable({
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
                        $(row).attr('id', 'filaTablaUsuarios' + dataIndex);
                    },
                    'columnDefs': [
                        {
                           'targets': 2,
                           'createdCell':  function (td, cellData, rowData, row, col) {
                                $(td).attr('id','usuario'+row); 
                           }
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
                            title: 'USUARIO',
                            data: 'usuario'
                        },
                        {
                            title: 'TIPO DE USUARIO',
                            data: 'tipousuario'
                        },
                        {
                            title: 'PRIVILEGIOS',
                            data: 'opciones2'
                        },
                        {
                            title: 'OPC.',
                            data: 'opciones1'
                        }
                    ],
                });    
                seleccionarFila(0)
            }else{
                $("#contenedorTablaUsuarios").html('');
            }
            $("#mensajeTablaUsuarios").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaUsuarios").html('');
            if(xhr.status === 0){
                $("#mensajeTablaUsuarios").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaUsuarios").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaUsuarios").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaUsuarios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaUsuarios").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaUsuarios").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaPersonas").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}


function obtenerFormularioModificarUsuario(id, i,j){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/usuario/obtenerformulariomodificarusuario',
        type: 'post',
        dataType: 'JSON',
        data: {id:id, i:i,j:j},
        beforeSend: function(){
            $("#mensajeModificarUsuario").html('');
            cargandoUsuarios("#contenedorModificarUsuario")
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            
            if(data.validar == true){
                $("#contenedorModificarUsuario").html(data.tabla);
               
            }else{
                $("#contenedorModificarUsuario").html('');
            }
            $("#mensajeModificarUsuario").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorModificarUsuario").html('');
            if(xhr.status === 0){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}

$(function(){
    $("#contenedorModificarUsuario").ajaxForm({
        beforeSend: function(){
            $("#mensajeModificarUsuario").html('');
            $("#btnGuardarUsuarioM").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                var table = $('#tablaUsuarios').DataTable();
                table.row(data.im).data(data.tabla[data.im]).draw();
                obtenerFormularioModificarUsuario(data.idUsuario, data.im, data.jm);
            }
            $("#btnGuardarUsuarioM").button('reset');
            $("#mensajeModificarUsuario").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarUsuarioM").button('reset');
            if(xhr.status === 0){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModificarUsuario").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 
</script>