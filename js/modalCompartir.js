let idCompartir = 0;

//Funcion para cargar los datos del modal de compartir y la muestra
function cargarModalCompartir(idPost){
    //url del post a compartir
    let url = "localhost/latinadd/visorPost.html?post="+idPost;
    idCompartir = idPost; // Guardar el id del post a compartir
    $("#vinculoCompartir").text(url);
    $(".modalCompartir").fadeIn(500);
}

//Ocultar el modal
$("#btnCancelarCompartir").click(function(){
    $(".modalCompartir").fadeOut(500);
});

$(document).on("click", "#btnCompartirPost", function () {
    const idUsuario = localStorage.getItem("idUsuario");
    const tokenUsuario = localStorage.getItem("tokenUsuario");
    const texto = $("#textAreaCompartir").val().trim();
    const idcompartido = idCompartir; // ID del post a compartir

    if (!idUsuario || !tokenUsuario || !idcompartido) {
        console.warn("Faltan datos para compartir.");
        return;
    }

    $.ajax({
        url: "API/post/nuevoPost.php",
        type: "POST",
        data: {
            id: idUsuario,
            token: tokenUsuario,
            texto: texto,
            tipo: 3, // tipo compartido
            idcompartido: idcompartido,
            imagenes: [] // los compartidos no llevan imágenes nuevas
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                console.log("Post compartido exitosamente.");
                $(".modalCompartir").fadeOut(300);
                // Aquí podrías recargar el feed o mostrar el nuevo post
            } else {
                console.warn("Error al compartir:", response.mensaje);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al conectar:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });
});
