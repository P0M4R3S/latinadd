let modalConfIdPost;

function activarModalOpcionesPost(id, usuario) {
    let usuarioActivo = localStorage.getItem("idUsuario");
    if(usuarioActivo == usuario) {
        $(".modalPostPropio").show();
        $(".modalPostOtro").hide();
    }else{
        $(".modalPostPropio").hide();
        $(".modalPostOtro").show();
    }
    modalConfIdPost = id;
    $("#modalConfPost").fadeIn(150);
}

$(".btnCancelarConf").click(function() {
    $("#modalConfPost").fadeOut(150);
});

$("#btnEliminarPost").click(function() {
    eliminarPost(modalConfIdPost);
});

$(".btnCopiarPost").click(function() {
    copiarPost(modalConfIdPost);
});

$("#btnDenunciarPost").click(function() {
    denunciarPost(modalConfIdPost);
});


function denunciarPost(idPost) {
    const idUsuario = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    $.ajax({
        url: "API/post/reportarPost.php",
        type: "POST",
        data: {
            id: idUsuario,
            token: token,
            idpost: idPost
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                $("#modalConfPost").hide();
                cargarMensajeModal("Post reportado correctamente.");
            } else {
                $("#modalConfPost").hide();
                cargarMensajeModal("Hubo un error al reportar el post. Inténtalo de nuevo.");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al conectar con el servidor:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });
}

function eliminarPost(idPost) {
    const idUsuario = localStorage.getItem("idUsuario");
    const tokenUsuario = localStorage.getItem("tokenUsuario");

    $.ajax({
        url: 'API/post/eliminarPost.php',
        type: 'POST',
        data: {
            id: idUsuario,
            token: tokenUsuario,
            idpost: idPost
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                console.log("✅ " + response.mensaje);
                $("#modalConfPost").hide();
                cargarMensajeModal("Post eliminado correctamente.");
                // Opcional: eliminar el post del DOM si tiene un contenedor específico
                $(`.post[data-id='${idPost}']`).remove();

            } else {
                console.warn("⚠️ Error al eliminar el post:", response.mensaje);
            }
        },
        error: function (xhr, status, error) {
            console.error("❌ Error de conexión:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });
}

    function copiarPost(idPost) {
        const urlPost = `localhost/latinadd/visorPost.html&post=${idPost}`;
        navigator.clipboard.writeText(urlPost);
    }

