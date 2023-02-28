var hostcrm = "https://democrm.contactvox.com/crmgrupoexpansionv6";

$(document).ready(function() {

    $.ajax({
        type: "POST",
        url: hostcrm + '/index.php?entryPoint=ApiCrm&type=Modules',
        data: {
        },
        beforeSend: function() {
            console.log("Cargando");
        },
        success: function(text) {
            
            var text = JSON.parse(text);

            Object.entries(text).forEach(element => {

                var modulo = element[0];
                var nombreModulo = element[1];

                $("#lista_modulos tbody").append(`
                    <tr>
                        <td style="overflow-wrap: anywhere;">${nombreModulo}</td>
                        <td style="overflow-wrap: anywhere;">${modulo}</td>
                    </tr>
                `);

            });
        },
        error: function(data) {
            //alert("Error");
            console.log(data);
        }

    });

});

function cambiarSeccion(seccion) {
    $(".menu_secciones").removeClass('active');
    $(`#menu_${seccion}`).addClass('active');
    $(".secciones").hide();
    $(`#seccion_${seccion}`).show();
}

function consultarModulo() {

    var modulo = $("#modulo").val();

    if(modulo != ""){

        $.ajax({
            type: "POST",
            url: hostcrm + `/index.php?entryPoint=ApiCrm&type=Fields&module=${modulo}`,
            data: {
            },
            beforeSend: function() {
                console.log("Cargando");
            },
            success: function(text) {

                var text = JSON.parse(text);
                var search_module = text.search_module;
                var data = text.data;
                
                if(search_module == true){

                    $("#lista_campos_modulo tbody").empty();

                    Object.entries(data).forEach(element => {

                        var item = parseInt(element[0]) + 1;
                        var nombre = element[1];

                        $("#lista_campos_modulo tbody").append(`
                            <tr>
                                <td style="overflow-wrap: anywhere;">${item}</td>
                                <td style="overflow-wrap: anywhere;">${nombre}</td>
                            </tr>
                        `);

                    });

                    $("#lista_campos_modulo").show();

                } else {
                    alert(data);
                }
            },
            error: function(data) {
                //alert("Error");
                console.log(data);
            }

        });

    } else {
        alert("Modulo vacío");
    }

}