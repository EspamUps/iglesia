function limpiarMensajeModalDescripcion(){
     setTimeout(function() {$("#mensajeModalFormDescripcion").html('');},1500);
}

$(function(){
    $("#formContenedorNuevaDescripcion").ajaxForm({
        beforeSend: function(){
            $("#mensajeModalFormDescripcion").html('');
            $("#btnGuardarDescripcionProducto").button('loading');
        },
        uploadProgress: function(event,position,total,percentComplete){

        },
        success: function(data){
            if(data.validar==true){
                limpiarMensajeModalDescripcion();
                filtrarCaracteristicaProducto(data.idProducto);
            }
            $("#btnGuardarDescripcionProducto").button('reset');
            $("#mensajeModalFormDescripcion").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#btnGuardarDescripcionProducto").button('reset');
            if(xhr.status === 0){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    });    
}); 


function filtrarDescripcionProducto(vari,ID,ID3){
    var cod1 = $("#cod1").text(); 
    var IDT = $("#cod6").text();
    var url = $("#rutaBase").text();
    $.ajax({
        url : url+'/productos/filtrardescripcionproducto',
        type: 'post',
        dataType: 'JSON',
        data: {id:vari,IDT:IDT,cod1:cod1,numeroFila:ID,contador:ID3},
        beforeSend: function(){
            $("#mensajeModalFormDescripcion").html('');
            $("#formContenedorNuevaDescripcion").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
        },
        uploadProgress: function(event,position,total,percentComplete){
        },
        success: function(data){  
            if(data.validar == true){
                $("#formContenedorNuevaDescripcion").html(data.tabla);
            }else{
                 $("#formContenedorNuevaDescripcion").html('');
            }
            $("#mensajeModalFormDescripcion").html(data.mensaje);
        },
        complete: function(){
        },
        error: function(xhr, textStatus, errorThrown) {
             $("#formContenedorNuevaDescripcion").html('');
            if(xhr.status === 0){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
            }else if(xhr.status == 404){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
            }else if(xhr.status == 500){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
            }else if(errorThrown === 'parsererror'){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
            }else if(errorThrown === 'timeout'){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
            }else if(errorThrown === 'abort'){
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
            }else{
                $("#mensajeModalFormDescripcion").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
            }
        }
    }); 
}






function deshabilitarPorducto(vari,ID,ID3){
    if(confirm('¿DESEAS HABILITAR O DESHABILITAR ESTE PRODUCTO?')){
        var cod3 = $("#cod1").text(); 
        var IDT = $("#cod6").text(); 
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/productos/deshabilitarproducto',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,IDT:IDT,cod3:cod3,numeroFila: ID,contador: ID3},
            beforeSend: function(){
                $("#btnDeshabilitarProducto"+ID).button('loading');
                $("#mensaje").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    var table = $('#tablaProductos').DataTable();                    
                    table.row(data.numeroFila).data(data.tabla).draw();
                }else{
                    $("#btnDeshabilitarProducto"+ID).button('reset');
                }
                $("#mensaje").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#btnDeshabilitarProducto"+ID).button('reset');
                if(xhr.status === 0){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}


function eliminarProducto(vari,ID){
    if(confirm('¿DESEAS ELIMINAR0 ESTE PRODUCTO?')){
        var cod3 = $("#cod3").text(); 
        var IDT = $("#cod6").text();
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/productos/eliminarproducto',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,IDT:IDT,cod3:cod3,numeroFila: ID},
            beforeSend: function(){
                $("#btnEliminarProducto"+ID).button('loading');
                $("#mensaje").html('');
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                if(data.validar == true){
                    var table = $('#tablaProductos').DataTable();
                    table.rows(data.numeroFila).remove().draw();
                }else{
                    $("#btnEliminarProducto"+ID).button('reset');
                }
                $("#mensaje").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#btnEliminarProducto"+ID).button('reset');
                if(xhr.status === 0){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }
}


$(function(){
        $("#formIngresarProducto").ajaxForm({
            beforeSend: function(){
                $("#carga").fadeIn(1);
                $(".progress").show();
                $("#mensaje").html('<h4 class="text-center">ESPERE...</h4>');
                $("#btnGuardarProducto").button('loading');
            },
            uploadProgress: function(event,position,total,percentComplete){
                $(".progress-bar").width(percentComplete+'%');
                $("#progreso").html(percentComplete+'%');
                var peso = parseInt(total);
                var progresoSubida = parseInt(position);
                var pesoFinal = peso/1048576;
                var progresoFinalSubida = progresoSubida/1048576;
                $("#megas").html(Math.round(progresoFinalSubida)+' MB/'+Math.round(pesoFinal)+' MB');
            },
            success: function(data){
                if(data.validar==true){
                    limpiarProgress();
                    limpiarFormIngresarProducto();
                    obtenerProductos(data.idAsignarCategoria);
                }
                $("#btnGuardarProducto").button('reset');
                $("#mensaje").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                limpiarProgress();
                $("#btnGuardarProducto").button('reset');
                if(xhr.status === 0){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        });    
    });
    
    
    
    function limpiarProgress(){
        $(".progress").hide();
        $(".progress-bar").width('0%');
        $("#megas").html('');
    }
    
    
    function validarFormularioIngresarProducto(f){
        var ok = false;
        if(f.elements["nombreProducto"].value == "")
        {
            $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">INGRESE EL NOMBRE DEL PRODUCTO MÁXIMO 50 CARACTERES</div>');
        }else if(f.elements["precio"].value == "" || isNaN(f.elements["precio"].value))
        {
            $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">INGRESE EL PRECIO (SÓLO NÚMEROS)</div>');
        }else if(f.elements["descripcionProducto"].length > 450)
        {
            $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LA DESCRIPCIÓN NO DEBE SER MAYOR A 450 CARACTERES</div>');
        }else if(f.elements["caracteristica1"].length > 200)
        {
            $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LAS CARACTERÍSTICAS DEBEN TENER MÁXIMO 200 CARACTERES</div>');
        }else if(f.elements["caracteristica2"].length > 200)
        {
            $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LAS CARACTERÍSTICAS DEBEN TENER MÁXIMO 200 CARACTERES</div>');
        }else{
            $("#mensaje").html('');
            ok = true;
        }
        return ok;
    } 
    
    
    function limpiarFormIngresarProducto()
    {
        $('#formIngresarProducto').each(function () {
            this.reset();
        });
        $("#contenedorVistaPreviaProducto").html('');
        $("#contenedorBtnGuardarProducto").html('');
        setTimeout(function() {$("#mensaje").html('');},1500);
    }
    function limpiarInputFile(){
        var input = $("#fotosProducto");
        input.replaceWith(input.val('').clone(true));
    }
    
    
    function vistaPreviaProducto() {
        limpiarProgress();
        $("#contenedorVistaPreviaProducto").html('');
        $("#contenedorBtnGuardarProducto").html('');
        var archivos = document.getElementById('fotosProducto').files;
        var navegador = window.URL || window.webkitURL;

        if(archivos.length > 3){
            limpiarInputFile();
            $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">SELECCIONE MÁXIMO 3 IMÁGENES JPEG</div>');
        }else{

            for(x=0;x<archivos.length; x++){
                if (!archivos[x].type.match('image/jpeg')) {
                    limpiarInputFile();
                    $("#mensaje").html('<div class="alert alert-danger text-center" role="alert">LAS IMÁGENES DEBEN SER FORMATO JPEG/JPG</div>');
                }else{
                    var radio = '<input style="cursor: pointer;" class="form-control" name="principal" id="principal" type="radio" value="'+x+'">';
                    if(x==0){
                        radio = '<input style="cursor: pointer;" class="form-control" name="principal" id="principal" checked type="radio" value="'+x+'">';
                    }
                    var objetoUrl = navegador.createObjectURL(archivos[x]);
                    $("#contenedorVistaPreviaProducto").append('<div class="col-sm-3">'+radio+'<img style="margin:0 auto 0 auto; text-aling:center; height: 200px;" class="img-responsive" src="'+objetoUrl+'"></div>');
                    $("#contenedorBtnGuardarProducto").html('<button id="btnGuardarProducto" data-loading-text="GUARDANDO..." type="submit" class="btn btn-danger">GUARDAR</button><button type="button" onclick="limpiarFormIngresarProducto();" class="btn btn-default">CANCELAR</button>');
                }
            }
        }
    }
    
    function habilitarFormulario()
    {
        $("#formIngresarProducto .form-control").removeAttr("disabled");
    }
    function cargandoProducto(){
        var url = $("#rutaBase").text();
        $("#contenedorProductos").html('<div class="text-center"><img style="width: 20%;" src="'+url+'/public/images/otras/cargando.gif" /></div>')
    } 

    function obtenerProductos(vari){
        var cod1 = $("#cod1").text(); 
        var cod2 = $("#cod6").text();
        var url = $("#rutaBase").text();
        $.ajax({
            url : url+'/productos/filtrarproductosporcategoria',
            type: 'post',
            dataType: 'JSON',
            data: {id:vari,cod1:cod1,cod2:cod2},
            beforeSend: function(){
                $("#mensajeProductos").html('');
                paginarCategoriaProducto();
                habilitarFormulario();
                $("#idAsignarCategoria").val(vari);
            },
            uploadProgress: function(event,position,total,percentComplete){
            },
            success: function(data){  
                
                if(data.validar == true){
                    $("#contenedorProductos").html('<hr><div class="box-body table-responsive no-padding"><table class="table table-hover" id="tablaProductos"></table></div>');
                    $('#tablaProductos').DataTable({
                        destroy: true,
                        order: [],
                        data: data.tabla,
                        columns: [
                            {
                                title: '#',
                                data: 'idProducto',
                            },
                            {
                                title: 'COD',
                                data: 'codigo',
                            },
                            {
                                title: 'NOMBRE',
                                data: 'nombreProducto',
                            },
                            {
                                title: 'FOTOS',
                                data: 'fotosProducto',
                            },
                            {
                                title: 'PRECIO',
                                data: 'precio',
                            },
                            {
                                title: 'FECHA INGRESO',
                                data: 'fechaIngreso',
                            },
                            {
                                title: 'CARACT.',
                                data: 'caracteristicaProducto',
                            },
                            {
                                title: 'DESCR.',
                                data: 'descripcionProducto',
                            },
                            {
                                title: 'OPC.',
                                data: 'opciones',
                            }
                        ],
                    });    
                }else{
                    $("#contenedorProductos").html('');
                }
                $("#mensajeProductos").html(data.mensaje);
            },
            complete: function(){
            },
            error: function(xhr, textStatus, errorThrown) {
                $("#contenedorProductos").html('');
                if(xhr.status === 0){
                    $("#mensajeProductos").html('<div class="alert alert-danger text-center" role="alert">NO HAY CONEXIÓN A INTERNET. VERIFICA LA RED</div>');
                }else if(xhr.status == 404){
                    $("#mensajeProductos").html('<div class="alert alert-danger text-center" role="alert">ERROR [404]. PÁGINA NO ENCONTRADA</div>');
                }else if(xhr.status == 500){
                    $("#mensajeProductos").html('<div class="alert alert-danger text-center" role="alert">ERROR DEL SERVIDOR [500]</div>');
                }else if(errorThrown === 'parsererror'){
                    $("#mensajeProductos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN JSON HA FALLADO </div>');
                }else if(errorThrown === 'timeout'){
                    $("#mensajeProductos").html('<div class="alert alert-danger text-center" role="alert">TIEMPO DE ESPERA TERMINADO</div>');
                }else if(errorThrown === 'abort'){
                    $("#mensajeProductos").html('<div class="alert alert-danger text-center" role="alert">LA PETICIÓN AJAX FUE ABORTADA</div>');
                }else{
                    $("#mensajeProductos").html('<div class="alert alert-danger text-center" role="alert">OCURRIÓ UN ERROR INESPERADO</div>');
                }
            }
        }); 
    }