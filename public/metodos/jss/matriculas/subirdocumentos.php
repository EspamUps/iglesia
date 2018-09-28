
<script type="text/javascript">
    
$(function(){
    $("#contenedorModificarEstadoMatricula").ajaxForm({
        beforeSend: function(){
//            $("#mensajeModificarEstadoMatricula").html('');
            $("#btnModificarEstadoMatricula").button('loading');
            $("#progressDocDeshabilitarMat").show();
            $("#mensajeModificarEstadoMatricula").html('<h4 class="text-center">ESPERE...</h4>');
           
        },
        uploadProgress: function(event,position,total,percentComplete){
            $("#progress-bar-DocDeshabilitarMat").width(percentComplete+'%');
            $("#progresoDocDeshabilitarMat").html(percentComplete+'%');
            var peso = parseInt(total);
            var progresoSubida = parseInt(position);
            var pesoFinal = peso/1048576;
            var progresoFinalSubida = progresoSubida/1048576;
            $("#megasDocDeshabilitarMat").html(Math.round(progresoFinalSubida)+' MB/'+Math.round(pesoFinal)+' MB');
        },
        success: function(data){ 
            if(data.validar==true){
                filtrarHorarioCursoSeleccionado();
                var table = $('#tablaMatriculas').DataTable();
                table.row(data.im).data(data.tabla[data.im]).draw();
                obtenerFormularioModificarEstadoMatricula(data.idMatricula, data.im, data.jm);
                 limpiarProgress();
            }
            $("#btnModificarEstadoMatricula").button('reset');
            $('#modalModificarEstadoMatricula').modal('hide');
            $("#mensajeTablaMatriculasActuales").html(data.mensaje);
            setTimeout(function() {$("#mensajeTablaMatriculasActuales").html('');},1500);
            
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            //limpiarProgress();
            $("#btnModificarEstadoMatricula").button('reset');
            if(xhr.status === 0){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 

//$(function(){
//        $("#formIngresarProducto").ajaxForm({
//            beforeSend: function(){
//                $("#carga").fadeIn(1);
//                $(".progress").show();
//                $("#mensaje").html('<h4 class="text-center">ESPERE...</h4>');
//                $("#btnGuardarProducto").button('loading');
//            },
//            uploadProgress: function(event,position,total,percentComplete){
//                $(".progress-bar").width(percentComplete+'%');
//                $("#progreso").html(percentComplete+'%');
//                var peso = parseInt(total);
//                var progresoSubida = parseInt(position);
//                var pesoFinal = peso/1048576;
//                var progresoFinalSubida = progresoSubida/1048576;
//                $("#megas").html(Math.round(progresoFinalSubida)+' MB/'+Math.round(pesoFinal)+' MB');
//            },
//            success: function(data){
//                if(data.validar==true){
//                    limpiarProgress();
//                    limpiarFormIngresarProducto();
//                    obtenerProductos(data.idAsignarCategoria);
//                }
//                $("#btnGuardarProducto").button('reset');
//                $("#mensaje").html(data.mensaje);
//            },
//            complete: function(){
//            },
//            error: function(xhr, textStatus, errorThrown) {
//                limpiarProgress();
//                $("#btnGuardarProducto").button('reset');
//                if(xhr.status === 0){
//                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
//                }else if(xhr.status == 404){
//                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
//                }else if(xhr.status == 500){
//                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
//                }else if(errorThrown === 'parsererror'){
//                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
//                }else if(errorThrown === 'timeout'){
//                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
//                }else if(errorThrown === 'abort'){
//                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
//                }else{
//                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
//                }
//            }
//        });    
//    });
    
    
    
    function limpiarProgress(){
        $("#progressDocDeshabilitarMat").hide();
        $("#progress-bar-DocDeshabilitarMat").width('0%');
        $("#megasDocDeshabilitarMat").html('');
    }
    
    
    
    function limpiarFormIngresarDocumento()
    {
        $('#contenedorModificarEstadoMatricula').each(function () {
            this.reset();
        });
        $("#contenedorVistaPreviaDeshabilitarMat").html('');
        $("#contenedorBtnAplicarDeshabilitarMat").html('');
        setTimeout(function() {$("#mensajeModificarEstadoMatricula").html('');},1500);
    }
    function limpiarInputFile(){
        var input = $("#documentoDeshabilitarMatModal");
        input.replaceWith(input.val('').clone(true));
    }
    
    
    function vistaPreviaMatricula() {
        limpiarProgress();
        $("#contenedorVistaPreviaDeshabilitarMat").html('');
        $("#contenedorBtnAplicarDeshabilitarMat").html('');
        var archivos = document.getElementById('documentoDeshabilitarMatModal').files;
        var navegador = window.URL || window.webkitURL;

        if(archivos.length > 1){
            limpiarInputFile();
            $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">SELECCIONE SOLO 1 DOCUMENTO PDF</div>');
        }else{
        $("#mensajeModificarEstadoMatricula").html("");
            for(x=0;x<archivos.length; x++){
                if (!archivos[x].type.match('application/pdf')) {
                    limpiarInputFile();
                    $("#mensajeModificarEstadoMatricula").html('<div class="alert alert-danger text-center" role="alert">EL DOCUMENTO DEBE SER FORMATO PDF</div>');
                }else{
                    
//                    var objetoUrl = navegador.createObjectURL(archivos[x]);
//                    $("#contenedorVistaPreviaDeshabilitarMat").append('<div class="col-sm-12"><img style="margin:0 auto 0 auto; text-aling:center; height: 200px;" class="img-responsive" src="'+objetoUrl+'"></div>');
                    $("#contenedorVistaPreviaDeshabilitarMat").html('<div class="col-lg-12 form-group">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;VISTA PREVIA DEL DOCUMENTO<div class="col-sm-2"><img style="margin:0 auto 0 auto; text-aling:center; height: 50px;" class="img-responsive" src="<?php echo $this->basePath();?>/public/librerias/images/pdf.png"></div><div class="col-sm-10">'+archivos[x].name+'</div></div>');
                    
                    $("#contenedorBtnAplicarDeshabilitarMat").html('<button data-loading-text="DESHABILITANDO..." id="btnModificarEstadoMatricula" type="submit" class="btn btn-danger pull-right"><i class="fa fa-ban"></i> DESHABILITAR MATRÍCULA</button><button type="button" onclick="limpiarFormIngresarDocumento();" class="btn btn-default pull-right"><i class="fa fa-close"></i>CANCELAR DOCUMENTO</button>');
                }
            }
        }
    }

</script>
