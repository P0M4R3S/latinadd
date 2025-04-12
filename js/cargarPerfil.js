

function cargarPerfil(callback){
    let id = localStorage.getItem("idUsuario");
    let token = localStorage.getItem("tokenUsuario");

    if (!id || !token) {
        console.error("No hay sesión iniciada.");
        return;
    }

    $.ajax({
        url: "API/usuarios/cargarUsuario.php",
        type: "POST",
        data: {
            id: id,
            token: token
        },
        dataType: "json",
        success: function (data) {
            if (data.success) {
                // Aquí puedes hacer lo que necesites con los datos:
                // Por ejemplo, mostrar el nombre e imagen de perfil en el lateral
                $(".nombreLateral").text(data.nombre + " " + data.apellidos);
                $("#imgLateral").attr("src", data.foto);
                $(".nombrePost").text(data.nombre + " " + data.apellidos);
                $(".imgPost").attr("src", data.foto);
                if (callback) callback();
                console.log("Perfil cargado:", data);
            } else {
                console.warn("No se pudo cargar el perfil:", data.mensaje);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al cargar el perfil:", error);
        }
    });

}