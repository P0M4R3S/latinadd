let paginaActual = "index.html";
let paginaAnterior = "";

function cargaInicial(anterior, actual, id, token){
    paginaAnterior = anterior;
    paginaActual = actual;

    $.ajax({
        url: './PHP/usuarios/cargarUsuario.php',
        type: 'POST',
        dataType: "json",
        data: {
            id: id,
            token: token
        },
        success: function(response) {
            $("#nombreUsuario").text(response.nombre + " " + response.apellidos);
            $("#imgUsuario").attr("src", response.foto);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar usuario:', error);
        }
    });
}

function calcularTiempo(fecha) {
    const fechaPublicacion = new Date(fecha);
    const ahora = new Date();
    
    const diferenciaSegundos = Math.floor((ahora - fechaPublicacion) / 1000);
    const diferenciaMinutos = Math.floor(diferenciaSegundos / 60);
    const diferenciaHoras = Math.floor(diferenciaMinutos / 60);
    const diferenciaDias = Math.floor(diferenciaHoras / 24);

    if (diferenciaMinutos < 60) {
        return `${diferenciaMinutos} min`;
    } else if (diferenciaHoras < 24) {
        return `${diferenciaHoras} h`;
    } else {
        return `${diferenciaDias} d`;
    }
}