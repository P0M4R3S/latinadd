
    const id = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    if (!id || !token) {
        window.location.href = "index.html";
    }

    

    // Click en imagen para activar input
    $("#previewFoto").click(() => $("#inputFoto").click());

    // Previsualizar imagen
    $("#inputFoto").on("change", function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => $("#previewFoto").attr("src", e.target.result);
            reader.readAsDataURL(file);
        }
    });

    // Guardar cambios
    $("#btnGuardarPerfil").click(function () {
        const formData = new FormData();
        formData.append("id", id);
        formData.append("token", token);
        formData.append("nombre", $("#inputNombre").val());
        formData.append("apellidos", $("#inputApellidos").val());
        formData.append("descripcion", $("#inputBiografia").val());

        const archivo = $("#inputFoto")[0].files[0];
        if (archivo) {
            formData.append("foto", archivo);
        }

        $.ajax({
            url: "API/usuarios/editarPerfil.php",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                cargarMensajeModal("Perfil actualizado correctamente.");
                console.log(res.mensaje || "Perfil actualizado.");
            },
            error: function () {
                cargarMensajeModal("Error al guardar el perfil.");
                console.log("Error al guardar el perfil.");
            }
        });
    });

    function cargarMiPerfil(){

        $.post("API/usuarios/cargarPerfilPropio.php", { id, token }, function (res) {
            if (res.success) {
                $("#inputNombre").val(res.usuario.nombre);
                $("#inputApellidos").val(res.usuario.apellidos);
                $("#inputBiografia").val(res.usuario.descripcion);
                $("#previewFoto").attr("src", res.usuario.foto || "img/default.png");
            }
        }, "json");
    }
