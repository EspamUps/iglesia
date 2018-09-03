<script type="text/javascript">
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
                console.log(data);
                limpiarFormIngresarConfigurarMisa();
//                limpiarFormIngresarConfigurarMisa();
//                obtenerLugaresMisa();
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
