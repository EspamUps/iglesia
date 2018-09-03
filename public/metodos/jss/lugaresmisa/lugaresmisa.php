<script type="text/javascript">
function seleccionarFila(ID)
{
    var menues2 = $("#tablaLugaresMisa tbody tr td");
    menues2.removeAttr("style");
    menues2.css({ 'cursor': 'pointer' });
    $("#filaTablaLugaresMisa" + ID + " td").removeAttr("style");
    $("#filaTablaLugaresMisa" + ID + " td").css({ 'background-color': 'black', 'color': 'white' });
}
function obtenerLugaresMisa(){
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/lugaresmisa/obtenerlugaresmisa',
        type: 'post',
        dataType: 'JSON',
        beforeSend: function(){
            $("#mensajeTablaLugaresMisa").html('');
            
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#contenedorTablaLugaresMisa").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaLugaresMisa"></table></div>');
                $('#tablaLugaresMisa').DataTable({
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
                        $(row).attr('id', 'filaTablaLugaresMisa' + dataIndex);
                    },
                    columns: [
                        {
                            title: '#',
                            data: '_j'
                        },
                        {
                            title: 'NOMBRE',
                            data: 'nombreLugar'
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
                $("#contenedorTablaLugaresMisa").html('');
            }
            $("#mensajeTablaLugaresMisa").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#contenedorTablaLugaresMisa").html('');
            if(xhr.status === 0){
                $("#mensajeTablaLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeTablaLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeTablaLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeTablaLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeTablaLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeTablaLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeTablaLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}
    
function limpiarFormIngresarLugaresMisa()
{
    $('#formIngresarLugaresMisa').each(function () {
        this.reset();
    });
    $("#selectCantones").html('<option value="0">SELECCIONE UN CANTÓN</option>');
    $("#selectParroquias").html('<option value="0">SELECCIONE UNA PARRÓQUIA</option>');
    setTimeout(function() {$("#mensajeFormIngresarLugaresMisa").html('');},1500);
}
$(function(){
    $("#formIngresarLugaresMisa").ajaxForm({
        beforeSend: function(){
            $("#mensajeFormIngresarLugaresMisa").html('');
            $("#btnGuardarLugarMisa").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarFormIngresarLugaresMisa();
                obtenerLugaresMisa();
            }
            $("#btnGuardarLugarMisa").button('reset');
            $("#mensajeFormIngresarLugaresMisa").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarLugarMisa").button('reset');
            if(xhr.status === 0){
                $("#mensajeFormIngresarLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeFormIngresarLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeFormIngresarLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeFormIngresarLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeFormIngresarLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeFormIngresarLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeFormIngresarLugaresMisa").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

function cargandoLugaresMisa(contenedor){
    var url = $("#rutaBase").text();
    $(contenedor).html('<img style="margin:0 auto 0 auto; text-aling:center; width: 10%;" class="img-responsive" src="'+url+'/public/librerias/images/pagina/cargando.gif">');
}
   
</script>
