<script>
function validarIngresoSacerdote(f){
    var _validar = false;
    if(confirm("¿ESTAS SEGURO DE GUARDAR A ESTE SACERDOTE?")){
        _validar = true;
    }
    return _validar;
}
    
function limpiarFormIngresarSacerdote()
{
    $('#formIngresoSacerdote').each(function () {
        this.reset();
    });
    $("#contenedorDatosSacerdote").html('');
    setTimeout(function() {$("#mensajeFormIngresoSacerdote").html('');},1500);
}
$(function(){
    $("#formIngresoSacerdote").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresoSacerdote").html('');
            $("#btnGuardarSacerdote").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarSacerdote();
                obtenerSacerdotes();
            }
            $("#btnGuardarSacerdote").button('reset');
            $("#mensajeFormIngresoSacerdote").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarSacerdote").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function filtrarSacerdotePorIdentificacion(){
    var url = $("#rutaBase").text();
    var identificacion = $("#identificacion").val();
    if(identificacion.length < 10){
        $("#mensajeFormIngresoSacerdote").html('');
        $("#contenedorDatosSacerdote").html('');
    }else{
        $.ajax({
            url : url+'/sacerdote/filtrarsacerdoteporidentificacion',
            type: 'post',
            dataType: 'JSON',
            data: {identificacion:identificacion},
            beforeSend: function(){
                
                $("#mensajeFormIngresoSacerdote").html('');
                cargandoSacerdotes('#contenedorDatosSacerdote');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#contenedorDatosSacerdote").html(data.tabla);
                }else{
                    $("#contenedorDatosSacerdote").html('');
                }
                $("#mensajeFormIngresoSacerdote").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorDatosSacerdote").html('');
                if(xhr.status === 0){
                    $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeFormIngresoSacerdote").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}


function seleccionarFila(ID)
{
    var menues2 = $("#tablaSacerdotes tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaSacerdotes" + ID + " td").removeAttr("style");
    $("#filaTablaSacerdotes" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}

function cargandoSacerdotes(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

function obtenerSacerdotes(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/sacerdote/obtenersacerdotes',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaSacerdotes").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaSacerdotes").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaSacerdotes"></table></div>');
                $('#tablaSacerdotes').DataTable({
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
                        $(row).attr('id', 'filaTablaSacerdotes' + dataIndex);
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
                $("#contenedorTablaSacerdotes").html('');
            }
            $("#mensajeTablaSacerdotes").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaSacerdotes").html('');
            if(xhr.status === 0){
                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaSacerdotes").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
</script>