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
    const tipo = $(this).data("tipo");
    if (tipo == 2) {
        window.location.href = `visorPagina.html?id=${userId}&volver=novedades`;
    } else {
        window.location.href = `visorPerfil.html?id=${userId}&volver=novedades`;
    }
});

function renderizarPostsPerfil(posts) {
    posts.forEach(post => {
        let html = "";
        const tiempo = calcularTiempo(post.fecha);
        const comentarios = post.comentarios || 0;
        const likes = post.likes || 0;
        const postId = post.id;
        const likeColor = post.liked ? "#615DFA" : "lightgrey";

        // === POST COMPARTIDO ===
        if (post.tipo === 3 && post.compartido) {
            const compartidorNombre = `${post.nombre} ${post.apellidos}`.trim();
            const compartidorFoto = post.foto || "img/default.jpg";

            const compartido = post.compartido;
            const autorNombre = compartido.apellidos ? `${compartido.nombre} ${compartido.apellidos}` : compartido.nombre;
            const autorFoto = compartido.foto || "img/default.jpg";
            const textoOriginal = compartido.texto || '';
            const tiempoOriginal = calcularTiempo(compartido.fecha);
            const imagenCompartida = (compartido.imagenes && compartido.imagenes.length > 0) ? compartido.imagenes[0] : null;
            const colorLike = compartido.liked ? "#615DFA" : "lightgrey";

            html += `
            <div class="container-fluid post" data-id="${postId}">
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${compartidorFoto}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost" data-id="${post.usuario}" data-tipo="${post.tipo}">${compartidorNombre} ha compartido</span><br>
                        <span class="tiempoPost">${tiempo}</span>
                    </div>
                    <div class="col-2" id="btnConfig" data-id="${post.id}" data-usuario="${post.usuario}">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z"/></svg>
                    </div>
                </div>
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${autorFoto}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost" data-id="${compartido.usuario}" data-tipo="${compartido.tipo}">${autorNombre}</span><br>
                        <span class="tiempoPost">${tiempoOriginal}</span>
                    </div>
                </div>
                <div class="row">
                    <span class="textoPost abrirPost" data-id="${postId}">${textoOriginal}</span>
                </div>`;

            if (imagenCompartida) {
                html += `
                <div class="row rowImagenes perfilPost abrirPost" data-id="${postId}">
                    <div class="col-12">
                        <img class="imgPost w-100" src="${imagenCompartida}" alt="">
                    </div>
                </div>`;
            }

            html += `
                <div class="row perfilPost">
                    <div class="col-6">
                        <img src="img/reaction/reacciones.png" alt="">
                        <span class="textareaPost likesNum" data-id="${postId}">+${compartido.likes}</span>
                    </div>
                    <div class="col-6">
                        <span class="textareaPost comentariosNum" data-id="${postId}">${compartido.comentarios} comentarios</span>
                    </div>
                </div>`;
        }

        // === POST NORMAL ===
        else {
            const nombre = `${post.nombre} ${post.apellidos}`.trim();
            const fotoPerfil = post.foto || "img/default.jpg";
            const texto = post.texto || '';
            const imagen = (post.imagenes && post.imagenes.length > 0) ? post.imagenes[0] : null;

            html += `
            <div class="container-fluid post" data-id="${postId}">
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoPerfil}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost" data-id="${post.usuario}" data-tipo="${post.tipo}">${nombre}</span><br>
                        <span class="tiempoPost">${tiempo}</span>
                    </div>
                    <div class="col-2" id="btnConfig" data-id="${post.id}" data-usuario="${post.usuario}">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z"/></svg>
                    </div>
                </div>
                <div class="row">
                    <span class="textoPost" data-id="${post.id}">${texto}</span>
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
                        <span class="textareaPost likesNum" data-id="${postId}">+${likes}</span>
                    </div>
                    <div class="col-6">
                        <span class="textareaPost comentariosNum" data-id="${postId}">${comentarios} comentarios</span>
                    </div>
                </div>`;
        }

        // === BOTONES ===
        html += `
        <div class="row mt-3 d-flex justify-content-around perfilPost">
            <div class="col-2 btnLike" data-id="${postId}">
                <svg fill="${likeColor}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M323.8 34.8c-38.2-10.9-78.1 11.2-89 49.4l-5.7 20c-3.7 13-10.4 25-19.5 35l-51.3 56.4c-8.9 9.8-8.2 25 1.6 33.9s25 8.2 33.9-1.6l51.3-56.4c14.1-15.5 24.4-34 30.1-54.1l5.7-20c3.6-12.7 16.9-20.1 29.7-16.5s20.1 16.9 16.5 29.7l-5.7 20c-5.7 19.9-14.7 38.7-26.6 55.5c-5.2 7.3-5.8 16.9-1.7 24.9s12.3 13 21.3 13L448 224c8.8 0 16 7.2 16 16c0 6.8-4.3 12.7-10.4 15c-7.4 2.8-13 9-14.9 16.7s.1 15.8 5.3 21.7c2.5 2.8 4 6.5 4 10.6c0 7.8-5.6 14.3-13 15.7c-8.2 1.6-15.1 7.3-18 15.2s-1.6 16.7 3.6 23.3c2.1 2.7 3.4 6.1 3.4 9.9c0 6.7-4.2 12.6-10.2 14.9c-11.5 4.5-17.7 16.9-14.4 28.8c.4 1.3 .6 2.8 .6 4.3c0 8.8-7.2 16-16 16l-97.5 0c-12.6 0-25-3.7-35.5-10.7l-61.7-41.1c-11-7.4-25.9-4.4-33.3 6.7s-4.4 25.9 6.7 33.3l61.7 41.1c18.4 12.3 40 18.8 62.1 18.8l97.5 0c34.7 0 62.9-27.6 64-62c14.6-11.7 24-29.7 24-50c0-4.5-.5-8.8-1.3-13c15.4-11.7 25.3-30.2 25.3-51c0-6.5-1-12.8-2.8-18.7C504.8 273.7 512 257.7 512 240c0-35.3-28.6-64-64-64l-92.3 0c4.7-10.4 8.7-21.2 11.8-32.2l5.7-20c10.9-38.2-11.2-78.1-49.4-89zM32 192c-17.7 0-32 14.3-32 32L0 448c0 17.7 14.3 32 32 32l64 0c17.7 0 32-14.3 32-32l0-224c0-17.7-14.3-32-32-32l-64 0z"/>
                </svg>
            </div>
            <div class="col-2 btnComentar" data-id="${postId}">
                <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M168.2 384.9c-15-5.4-31.7-3.1-44.6 6.4c-8.2 6-22.3 14.8-39.4 22.7c5.6-14.7 9.9-31.3 11.3-49.4c1-12.9-3.3-25.7-11.8-35.5C60.4 302.8 48 272 48 240c0-79.5 83.3-160 208-160s208 80.5 208 160s-83.3 160-208 160c-31.6 0-61.3-5.5-87.8-15.1zM26.3 423.8c-1.6 2.7-3.3 5.4-5.1 8.1l-.3 .5c-1.6 2.3-3.2 4.6-4.8 6.9c-3.5 4.7-7.3 9.3-11.3 13.5c-4.6 4.6-5.9 11.4-3.4 17.4c2.5 6 8.3 9.9 14.8 9.9c5.1 0 10.2-.3 15.3-.8l.7-.1c4.4-.5 8.8-1.1 13.2-1.9c.8-.1 1.6-.3 2.4-.5c17.8-3.5 34.9-9.5 50.1-16.1c22.9-10 42.4-21.9 54.3-30.6c31.8 11.5 67 17.9 104.1 17.9c141.4 0 256-93.1 256-208S397.4 32 256 32S0 125.1 0 240c0 45.1 17.7 86.8 47.7 120.9c-1.9 24.5-11.4 46.3-21.4 62.9zM144 272a32 32 0 1 0 0-64 32 32 0 1 0 0 64zm144-32a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm80 32a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"/>
                </svg>
            </div>
            <div class="col-2 btnCompartir" data-id="${postId}">
                <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M307 34.8c-11.5 5.1-19 16.6-19 29.2l0 64-112 0C78.8 128 0 206.8 0 304C0 417.3 81.5 467.9 100.2 478.1c2.5 1.4 5.3 1.9 8.1 1.9c10.9 0 19.7-8.9 19.7-19.7c0-7.5-4.3-14.4-9.8-19.5C108.8 431.9 96 414.4 96 384c0-53 43-96 96-96l96 0 0 64c0 12.6 7.4 24.1 19 29.2s25 3 34.4-5.4l160-144c6.7-6.1 10.6-14.7 10.6-23.8s-3.8-17.7-10.6-23.8l-160-144c-9.4-8.5-22.9-10.6-34.4-5.4z"/>
                </svg>
            </div>
        </div>
        </div>`;

        $(".postPerfil").append(html);
    });
}

$(document).on("click", ".btnLike", function () {
    const postId = $(this).data("id");
    const userId = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    $.ajax({
        url: "API/post/likePost.php",
        type: "POST",
        data: { id: userId, token: token, idpost: postId },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                const $contador = $(`.likesNum[data-id='${postId}']`);
                let actual = parseInt($contador.text().replace("+", "")) || 0;
                $contador.text(response.liked ? `+${actual + 1}` : `+${actual - 1}`);

                const $icono = $(`.btnLike[data-id='${postId}'] svg`);
                $icono.attr("fill", response.liked ? "#615DFA" : "lightgrey");
            } else {
                console.warn("Error al dar like:", response.mensaje);
            }
        },
        error: function (xhr) {
            console.error("Error de conexión al dar like:", xhr.responseText);
        }
    });
});

$(document).on("click", ".btnComentar", function () {
    const idPost = $(this).data("id");
    window.location.href = `visorPost.html?idpost=${idPost}&volver=perfil`;
});

$(document).on("click", ".textoPost", function () {
    const idPost = $(this).data("id");
    window.location.href = `visorPost.html?idpost=${idPost}&volver=perfil`;
});


$(document).on("click", ".btnCompartir", function () {
    const id = $(this).data("id"); // Variable global esperada por el modal
    cargarModalCompartir(id);
});

//Funcionalidad del boton configuracion asociado al post
$(document).on("click", "#btnConfig", function () {
    const id = $(this).data("id");
    const usuario = $(this).data("usuario");
    activarModalOpcionesPost(id, usuario);
});



// Enviar mensaje directo desde el modal
$(document).on("click", "#btnNuevoMensaje", function () {
    const mensaje = $("#textAreaNMensaje").val().trim();
    if (mensaje.length === 0) {
        alert("Escribe un mensaje.");
        return;
    }

    const idUsuario = localStorage.getItem("idUsuario");
    const tokenUsuario = localStorage.getItem("tokenUsuario");

    $.ajax({
        url: "API/usuarios/mensajeDirecto.php",
        type: "POST",
        data: {
            id: idUsuario,
            token: tokenUsuario,
            idreceptor: usuario,
            texto: mensaje
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                cargarMensajeModal("Mensaje enviado correctamente.");
                cerrarModalNuevoMensaje();
            } else {
                cargarMensajeModal("Error al enviar el mensaje");
                cerrarModalNuevoMensaje();
            }
        },
        error: function (xhr) {
            console.error("Error de red:", xhr.responseText);
            alert("No se pudo enviar el mensaje.");
        }
    });
});


$(document).on("click", ".btnCancelarMensaje", function () {
    cerrarModalNuevoMensaje();
});

function cerrarModalNuevoMensaje() {
    $("#modalNuevoMensaje").hide();
    $("#textAreaNMensaje").val("");
}

$("#btnMensaje").click(function() {
    $("#modalNuevoMensaje").fadeIn(150);
});