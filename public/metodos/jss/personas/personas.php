<script>
    
function validarIngresoPersona(f){
    var _validar = false;
    var _identificacion = $("#identificacion").val();
    if(_identificacion.length == 0){
        if(confirm("¿ESTAS SEGURO DE GUARDAR A ESTA PERSONA SIN LA IDENTIFICACIÓN?")){
            _validar = true;
        }
    }else{
        _validar = true;
    }
    return _validar;
}
    
function limpiarFormIngresarPersona()
{
    $('#formIngresoPersona').each(function () {
        this.reset();
    });
    $("#selectCantones").html('<option value="0">SELECCIONE UN CANTÓN</option>');
    $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
    setTimeout(function() {$("#mensajeFormIngresoPersona").html('');},1500);
}
$(function(){
    $("#formIngresoPersona").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresoPersona").html('');
            $("#btnGuardarPersona").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarPersona();
                obtenerPersonas();
            }
            $("#btnGuardarPersona").button('reset');
            $("#mensajeFormIngresoPersona").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarPersona").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresoPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function seleccionarFila(ID)
{
    var menues2 = $("#tablaPersonas tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaPersonas" + ID + " td").removeAttr("style");
    $("#filaTablaPersonas" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}

function cargandoPersonas(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

function obtenerPersonas(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/persona/obtenerpersonas',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaPersonas").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaPersonas").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaPersonas"></table></div>');
                $('#tablaPersonas').DataTable({
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
                        $(row).attr('id', 'filaTablaPersonas' + dataIndex);
                    },
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
                            title: 'DIRECCIÓN',
                            data: 'botonVerDireccion'
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
                $("#contenedorTablaPersonas").html('');
            }
            $("#mensajeTablaPersonas").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaPersonas").html('');
            if(xhr.status === 0){
                $("#mensajeTablaPersonas").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaPersonas").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaPersonas").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaPersonas").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaPersonas").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaPersonas").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaPersonas").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}


function FiltrarDireccionPorPersona(id, i){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/persona/obtenerdireccionpersona',
        type: 'post',
        dataType: 'JSON',
        data: {id:id, i:i},
        beforeSend: function(){
            $("#mensajeDireccionPersona").html('');
            cargandoPersonas("#contenedorDireccionPersona")
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorDireccionPersona").html(data.tabla);
               
            }else{
                $("#contenedorDireccionPersona").html('');
            }
            $("#mensajeDireccionPersona").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorDireccionPersona").html('');
            if(xhr.status === 0){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeDireccionPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}


function obtenerFormularioModificarPersona(id, i,j){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/persona/obtenerformulariomodificarpersona',
        type: 'post',
        dataType: 'JSON',
        data: {id:id, i:i,j:j},
        beforeSend: function(){
            $("#mensajeModifcarPersona").html('');
            cargandoPersonas("#contenedorModificarPersona")
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorModificarPersona").html(data.tabla);
               
            }else{
                $("#contenedorModificarPersona").html('');
            }
            $("#mensajeModifcarPersona").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorModificarPersona").html('');
            if(xhr.status === 0){
                $("#mensajeModifcarPersona").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModifcarPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModifcarPersona").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModifcarPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModifcarPersona").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModifcarPersona").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModifcarPersona").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
</script>