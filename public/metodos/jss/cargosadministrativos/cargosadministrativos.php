<script type="text/javascript">
function cargandoCargosAdministrativos(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
    
}

function CargarCargosAdministrativos(){
    var url = $("#rutaBase").text();

    $.ajax({
            url : url+'/cargosadministrativos/obtenercargosadministrativos',
            type: 'post',
            dataType: 'JSON',
            beforeSend: function(){
                $("#mensajeTablaCargosAdministrativos").html("");
                cargandoCargosAdministrativos('#contenedorTablaCargosAdministrativos');                
            },
            uploadProgress: function(event,position,total,percentComplete){
                
            },
            success: function(data){ 
                if(data.validar == true)
                {                    
                    $("#contenedorTablaCargosAdministrativos").html(data.tabla);
                }else{
                      $("#contenedorTablaCargosAdministrativos").html("");
                }
                $("#mensajeTablaCargosAdministrativos").html(data.mensaje);
                setTimeout(function() {$("#mensajeTablaCargosAdministrativos").html('');},1500);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) { 
                $("#contenedorTablaCargosAdministrativos").html("");
                if(xhr.status === 0){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });
}


function CambiarEstadoCargo(idCargo, Nfila){
    var url = $("#rutaBase").text();
    if(idCargo==null ||Nfila==null )
    {
        $("#mensajeTablaCargosAdministrativos").html("");
        $("#contenedorTablaCargosAdministrativos").html("");
    }
    else{
        $.ajax({
            url : url+'/cargosadministrativos/modificarestado',
            type: 'post',
            dataType: 'JSON',
            data: {idCargoAdministrativoEncriptado:idCargo, Nfila:Nfila},
            beforeSend: function(){
                $("#mensajeTablaCargosAdministrativos").html("");
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    $("#numerofila"+data.numeroFila).html(data.nuevafila);
                    $("#contenedorDatosResponsable").html("");
                    limpiarFormIngresarCargoAdministrativo();
                }else{
                }
                
                $("#mensajeTablaCargosAdministrativos").html(data.mensaje);
                 setTimeout(function() {$("#mensajeTablaCargosAdministrativos").html('');},1500);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorTablaCargosAdministrativos").html('');
                if(xhr.status === 0){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}



function limpiarFormIngresarCargoAdministrativo()
{
    $('#formIngresoCargo').each(function () {
        this.reset();
    });
    setTimeout(function() {$("#mensajeTablaCargosAdministrativos").html('');},1500);
}

function validarIngresoCargo(f){
    var _validar = false;
    if(confirm("¿ESTAS SEGURO DE REGISTRAR EL NUEVO CARGO ADMINISTRATIVO?")){
        _validar = true;
    }
    return _validar;
}

$(function(){
    $("#formIngresoCargo").ajaxForm({
        beforeSend: function(){
            $("#mensajeTablaCargosAdministrativos").html('');
            $("#btnGuardarCargo").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarCargoAdministrativo();
                limpiarFormIngresarAdministrativo();
                CargarCargosAdministrativos();
                 $("#contenedorDatosResponsable").html("");
            }
            $("#btnGuardarCargo").button('reset');
            $("#mensajeTablaCargosAdministrativos").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarCargo").button('reset');
            if(xhr.status === 0){
                $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaCargosAdministrativos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

</script>