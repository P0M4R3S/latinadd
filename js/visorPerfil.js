let indice = 1;
let usuario;
let cargando = false;
let finScroll = false;
let estadoAmistad = 3;

function cargarPerfilUsuario(idPerfil) {
    const idUsuario = localStorage.getItem("idUsuario");
    const tokenUsuario = localStorage.getItem("tokenUsuario");
    usuario = idPerfil;

    $.ajax({
        url: 'API/usuarios/cargarPerfil.php',
        type: 'POST',
        data: {
            id: idUsuario,
            token: tokenUsuario,
            idusuario: idPerfil
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                renderizarPerfil(response.perfil);
            } else {
                console.log("Error al cargar el perfil: " + response.mensaje);
            }
        },
        error: function () {
            console.log("Error al conectar con el servidor.");
        }
    });
}

function renderizarPerfil(perfil) {
    const img = perfil.foto || "img/default.png";
    const nombre = `${perfil.nombre} ${perfil.apellidos}`;
    const descripcion = perfil.descripcion || "No hay descripción disponible.";
    const pais = perfil.pais;
    const nacimiento = perfil.nacimiento || "No hay fecha de nacimiento disponible.";
    estadoAmistad = perfil.vinculo || 3;

    $("#nombrePerfil").text(nombre);
    $("#imgPerfil").attr("src", img);
    $("#descripcionPerfil").text(descripcion);
    $("#banderaPerfil").attr("src", `img/banderas/${pais}.png`);
    $("#cumplePerfil").text(nacimiento);
    if(estadoAmistad === 1) {
        //perfil propio, no existe boton
        $("#btnAmigo").remove();
        $("#btnMensaje").remove();
    }else if(estadoAmistad === 2) {
        //Son amigos
        $("#btnAmigo").text("Eliminar amistad");
    }else if(estadoAmistad === 3) {
        //Ni amigos ni solicitud
        $("#btnAmigo").text("Amigo/a+");
    }else if(estadoAmistad === 4) {
        //Solicitud recibida
        $("#btnAmigo").text("Aceptar solicitud");
    }else{
        //Solicitud enviada
        $("#btnAmigo").text("Cancelar solicitud");
    }

    cargarPostUsuario(usuario, indice);
}


//Control del boton de amistad y mensaje y sus funciones
$("#btnAmigo").on("click", function () {
    if (estadoAmistad === 2) {
        eliminarAmistad(usuario);
    } else if (estadoAmistad === 3) {
        enviarSolicitudAmistad(usuario);
    } else if (estadoAmistad === 4) {
        aceptarSolicitud(usuario);
    } else if (estadoAmistad === 5) {
        cancelarSolicitud(usuario);
    }
});

function cancelarSolicitud(idUsuario) {
    $.ajax({
        url: 'API/usuarios/eliminarPeticionAmistad.php',
        type: 'POST',
        data: {
            id: localStorage.getItem("idUsuario"),
            token: localStorage.getItem("tokenUsuario"),
            idusuario: idUsuario
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                estadoAmistad = 3;
                $("#btnAmigo").text("Amigo/a+");
                console.log("Solicitud de amistad cancelada.");
            } else {
                console.log("Error al cancelar la solicitud de amistad: " + response.mensaje);
            }
        },
        error: function () {
            console.log("Error al conectar con el servidor.");
        }
    });
}

function aceptarSolicitud(idUsuario) {
    $.ajax({
        url: 'API/usuarios/aceptarAmistad.php',
        type: 'POST',
        data: {
            id: localStorage.getItem("idUsuario"),
            token: localStorage.getItem("tokenUsuario"),
            idusuario: idUsuario
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                estadoAmistad = 2;
                $("#btnAmigo").text("Eliminar amistad");
                console.log("Solicitud de amistad aceptada.");
            } else {
                console.log("Error al aceptar la solicitud de amistad: " + response.mensaje);
            }
        },
        error: function () {
            console.log("Error al conectar con el servidor.");
        }
    });
}

function enviarSolicitudAmistad(idUsuario) {
    $.ajax({
        url: 'API/usuarios/peticionAmistad.php',
        type: 'POST',
        data: {
            id: localStorage.getItem("idUsuario"),
            token: localStorage.getItem("tokenUsuario"),
            idusuario: idUsuario
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                estadoAmistad = 5;
                $("#btnAmigo").text("Cancelar solicitud");
                console.log("Solicitud de amistad enviada.");
            } else {
                console.log("Error al enviar la solicitud de amistad: " + response.mensaje);
            }
        },
        error: function () {
            console.log("Error al conectar con el servidor.");
        }
    });
}

function eliminarAmistad(idUsuario) {
    $.ajax({
        url: 'API/usuarios/eliminarAmistad.php',
        type: 'POST',
        data: {
            id: localStorage.getItem("idUsuario"),
            token: localStorage.getItem("tokenUsuario"),
            idusuario: idUsuario
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                estadoAmistad = 3;
                $("#btnAmigo").text("Amigo/a+");
                console.log("Amistad eliminada.");
            } else {
                console.log("Error al eliminar la amistad: " + response.mensaje);
            }
        },
        error: function () {
            console.log("Error al conectar con el servidor.");
        }
    });
}

//Logica del boton volver que redirecciona
$(document).on("click", "#btnVolver", function () {
    if (typeof VOLVER !== 'undefined' && VOLVER === "novedades") {
        window.location.href = "novedades.html";
    }else{
        window.location.href = "novedades.html";
    }
});



//Control del scroll infinito
$(window).on("scroll", function () {
    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
        if (!cargando && !finScroll) {
            indice++;
            cargarPostUsuario(usuario, indice);
        }
    }
});



//Control de la carga de posts del usuario
function cargarPostUsuario(id, indice){
    $.ajax({
        url: 'API/post/cargarPostsUsuario.php',
        type: 'POST',
        data: {
            id: localStorage.getItem("idUsuario"),
            token: localStorage.getItem("tokenUsuario"),
            idusuario: id,
            indice: indice
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                if (response.posts.length === 0) {
                    finScroll = true;
                    console.log("No hay más posts para cargar.");
                } else {
                    console.log(response.posts);
                    renderizarPostsPerfil(response.posts);
                    cargando = false;
                }
                cargando = false;
            } else {
                console.log("Error al cargar los posts: " + response.mensaje);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al conectar con el servidor.");
            console.log("Status:", status);
            console.log("Error:", error);
            console.log("Response:", xhr.responseText);
        }
        
    });
}

function calcularTiempo(fechaISO) {
    const fechaPost = new Date(fechaISO);
    const ahora = new Date();
    const diffMin = Math.floor((ahora - fechaPost) / 60000);

    if (diffMin < 1) return "Justo ahora";
    if (diffMin < 60) return `Hace ${diffMin} minutos`;
    const horas = Math.floor(diffMin / 60);
    if (horas < 24) return `Hace ${horas} horas`;
    const dias = Math.floor(horas / 24);
    return `Hace ${dias} días`;
}


$(document).on("click", ".nombrePost", function () {
    const userId = $(this).data("id");
    window.location.href = `visorPerfil.html?usuario=${userId}&volver=novedades`;
});

function renderizarPostsPerfil(posts) {
    posts.forEach(post => {
        // Reiniciamos html por cada post
        let html = "";
        const tiempo = calcularTiempo(post.fecha);
        const comentarios = post.comentarios || 0;
        const likes = post.likes || 0;

        if (post.tipo === 3 && post.compartido) {
            // === POST COMPARTIDO ===
            const compartidor = `${post.nombre} ${post.apellidos}`;
            const fotoCompartidor = post.foto || "img/default.jpg";

            const compartido = post.compartido;
            const autorOriginal = `${compartido.nombre} ${compartido.apellidos}`;
            const fotoOriginal = compartido.foto || "img/default.jpg";
            const textoOriginal = compartido.texto || '';
            const tiempoOriginal = calcularTiempo(compartido.fecha);
            const imagenCompartida = (compartido.imagenes && compartido.imagenes.length > 0) ? compartido.imagenes[0] : null;

            html += `
            <div class="container-fluid post">
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoCompartidor}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost" data-id="${post.usuario}">${compartidor} ha compartido</span><br>
                        <span class="tiempoPost">${tiempo}</span>
                    </div>
                </div>
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoOriginal}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost" data-id="${compartido.usuario}">${autorOriginal}</span><br>
                        <span class="tiempoPost">${tiempoOriginal}</span>
                    </div>
                </div>
                <div class="row">
                    <span class="textoPost">${textoOriginal}</span>
                </div>`;

            if (imagenCompartida) {
                html += `
                <div class="row rowImagenes perfilPost">
                    <div class="col-12">
                        <img class="imgPost w-100" src="${imagenCompartida}" alt="">
                    </div>
                </div>`;
            }

            html += `
                <div class="row perfilPost">
                    <div class="col-6">
                        <img src="img/reaction/reacciones.png" alt="">
                        <span class="textareaPost">+${compartido.likes}</span>
                    </div>
                    <div class="col-6">
                        <span class="textareaPost">${compartido.comentarios} comentarios</span>
                    </div>
                </div>
            </div>`;

        } else {
            // === POST NORMAL ===
            const nombre = `${post.nombre} ${post.apellidos}`;
            const fotoPerfil = post.foto || "img/default.jpg";
            const texto = post.texto || '';
            const imagen = (post.imagenes && post.imagenes.length > 0) ? post.imagenes[0] : null;

            html += `
            <div class="container-fluid post">
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoPerfil}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost" data-id="${post.usuario}">${nombre}</span><br>
                        <span class="tiempoPost">${tiempo}</span>
                    </div>
                </div>
                <div class="row">
                    <span class="textoPost">${texto}</span>
                </div>`;

            if (imagen) {
                html += `
                <div class="row rowImagenes perfilPost">
                    <div class="col-12">
                        <img class="imgPost w-100" src="${imagen}" alt="">
                    </div>
                </div>`;
            }

            html += `
                <div class="row perfilPost">
                    <div class="col-6">
                        <img src="img/reaction/reacciones.png" alt="">
                        <span class="textareaPost">+${likes}</span>
                    </div>
                    <div class="col-6">
                        <span class="textareaPost">${comentarios} comentarios</span>
                    </div>
                </div>
            </div>`;
        }

        $(".postPerfil").append(html);
    });
}
