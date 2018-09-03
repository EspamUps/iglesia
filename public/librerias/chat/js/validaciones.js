$(document).ready(function () {
   // $('#cabeza').hide();
   
    function hasExtension(inputID, exts) {
        var fileName = document.getElementById(inputID).value;
        return (new RegExp('(' + exts.join('|').replace(/\./g, '\\.') + ')$')).test(fileName);
    }

    $("#ContentPlaceHolderNotas_btnUpload").click(function () {
        
        $("#ContentPlaceHolderNotas_GridView1").html("");
        if ($("#ContentPlaceHolderNotas_FileUpload1").val() == "") {
            $("#error_div").html("<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Atencion!</strong> Debe seleccionar un archivo.</div>");
            return false;
         
            
        }else {         
            if (!hasExtension('ContentPlaceHolderNotas_FileUpload1', ['.xls', '.xlsx'])) {
                $("#error_div").html("<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Atencion!</strong> Extension NO válida.</div>");
                return false;
            }
            

        }
    });



    $("#salir").click(function () {

        window.location.href = "../usuariosLogin/Login.aspx";
    });
       
    $("#guardarNotas").click(function () {

        var  _dato= {
            Numero: 5,
            idMatricula: 3,
            totalHoras: 200
        }

        $.ajax({
            url: "index.aspx/cerrar", //Direccion del servicio web segido de /Nombre del metodo a llamar
            type: "POST",
            data: "{_dato}", //Esto se utiliza si deseamos pasar algun paramentro al metodo del servicio web ejm: {'indentificacion':'1234'}
            contentType: "application/json; charset=utf-8",
            dataType: "json", //Esto quiere decir que los datos nos llegaran como un objeto json
            success: OnSuccessCall, 
            error: OnErrorCall 
        });
                           
    
        
    function OnSuccessCall(response){
        $("#prueba").html("SUCCES");
    }
        
    function OnErrorCall(response){
        $("#prueba").html(""+response.error);
    }

    });

    
});
