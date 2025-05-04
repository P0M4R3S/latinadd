//Variable global del número de post
let numPost;
let idUsuarioPost;
let tipoAutorPost;

function cargarPost(id) {
    const idUsuario = localStorage.getItem("idUsuario");
    const tokenUsuario = localStorage.getItem("tokenUsuario");
    numPost = id;

    if (!idUsuario || !tokenUsuario) {
        window.location.href = "index.html";
        return;
    }

    $.ajax({
        url: "API/post/cargarPost.php",
        type: "POST",
        data: {
            id: idUsuario,
            token: tokenUsuario,
            idpost: id
        },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                console.log(respuesta);
                idUsuarioPost = respuesta.post.usuario;
                tipoAutorPost = respuesta.post.tipoAutor; // 'usuario' o 'pagina'
                renderizarPost(respuesta.post, respuesta.comentarios);
            } else {
                console.warn(respuesta.mensaje || "No se pudo cargar el post.");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar el post:", error);
        }
    });
}

function renderizarPost(post, comentarios) {
    const nombreCompleto = post.tipoAutor === "pagina" ? post.nombre : `${post.nombre} ${post.apellidos}`;
    const fotoPerfil = post.foto || "img/default.jpg";
    const tiempo = calcularTiempo(post.fecha);
    const texto = post.texto || "";
    const likes = post.likes || 0;

    $("#nombrePost").text(nombreCompleto);
    $("#tiempoPost").text(tiempo);
    $(".imgPost").first().attr("src", fotoPerfil);
    $("#txtContenidoPost").text(texto);
    $("#likesPost").text(`+${likes}`);

    if (post.liked) {
        $(".btnLike svg").attr("fill", "#615DFA");
    }

    $("#bloqueImagenesPost").remove();
    $("#bloquePostCompartido").remove();

    if (post.tipo == 3 && post.compartido) {
        const compartido = post.compartido;
        const nombreCompartido = post.tipoAutorCompartido === "pagina"
            ? compartido.nombre
            : `${compartido.nombre} ${compartido.apellidos}`;

        const compartidoHTML = $(`
            <div class="row mt-2" id="bloquePostCompartido">
                <div class="col-12 p-2 rounded border">
                    <div class="row">
                        <div class="col-2">
                            <img class="imgPost" src="${compartido.foto || 'img/default.jpg'}" alt="">
                        </div>
                        <div class="col-10">
                            <span class="nombrePost">${nombreCompartido}</span><br>
                            <span class="tiempoPost">${calcularTiempo(compartido.fecha)}</span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <span class="textoVisor">${compartido.texto || ''}</span>
                        </div>
                    </div>
                    <div class="row mt-2" id="imagenesCompartido"></div>
                </div>
            </div>
        `);

        if (compartido.imagenes && compartido.imagenes.length > 0) {
            const contenedor = compartidoHTML.find("#imagenesCompartido");
            compartido.imagenes.forEach(ruta => {
                const img = $(`<img src="${ruta}" class="img-fluid m-1" style="max-height: 250px;">`);
                contenedor.append(img);
            });
        }

        compartidoHTML.insertAfter("#txtContenidoPost");

    } else {
        if (post.imagenes && post.imagenes.length > 0) {
            const bloqueImagenes = $(`
                <div class="row mb-3" id="bloqueImagenesPost">
                    <div class="col-12 d-flex flex-wrap justify-content-center"></div>
                </div>
            `);
            post.imagenes.forEach(ruta => {
                const img = $(`<img src="${ruta}" class="img-fluid m-1" style="max-height: 250px;">`);
                bloqueImagenes.find("div").append(img);
            });
            bloqueImagenes.insertAfter("#txtContenidoPost");
        }
    }

    const bloque = $("#bloqueComentarios");
    bloque.empty();

    const respuestas = {};
    comentarios.forEach(com => {
        if (com.idrespuesta) {
            if (!respuestas[com.idrespuesta]) respuestas[com.idrespuesta] = [];
            respuestas[com.idrespuesta].push(com);
        }
    });

    comentarios.filter(com => !com.idrespuesta).forEach(com => {
        bloque.append(crearComentarioHTML(com, false));
        if (respuestas[com.id]) {
            respuestas[com.id].forEach(res => {
                bloque.append(crearComentarioHTML(res, true));
            });
        }
    });
}

function crearComentarioHTML(com, esRespuesta = false) {
    const nombre = `${com.nombre} ${com.apellidos}`;
    const foto = com.foto || "img/default.jpg";
    const texto = com.texto;
    const tiempo = calcularTiempo(com.fecha);
    const margen = esRespuesta ? `<div class="col-1"></div><div class="col-11">` : '';
    const cierre = esRespuesta ? `</div>` : '';
    const colorLike = com.liked ? "#2ecc71" : "grey";
    return `
    <div class="row mb-4">
        ${margen}
            <div class="row">
                <div class="col-2">
                    <img class="imgPost" src="${foto}" alt="">
                </div>
                <div class="col-10 globoComentario">
                    <span class="nombreComentario" data-id="${com.idusuario}">${nombre}</span><br>
                    <span class="textoComentario">${texto}</span>
                </div>
            </div>
            <div class="row d-flex">
                <div class="col-2"></div>
                <div class="col-3 minitexto">${tiempo}</div>
                <div class="col-3 minitexto" style="cursor:pointer; color:${colorLike};" id="btnLikeComentario" data-id="${com.id}">Me gusta</div>
                <div class="col-3 minitexto btnResponder" data-nombre="${com.nombre + " " + com.apellidos}" data-id="${com.id}">Responder</div>
                <div class="col-1"></div>
            </div>
        ${cierre}
    </div>`;
}

$(document).on("click", ".btnLike", function () {
    const userId = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    $.ajax({
        url: "API/post/likePost.php",
        type: "POST",
        data: {
            id: userId,
            token: token,
            idpost: numPost
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                const $contador = $("#likesPost");
                let actual = parseInt($contador.text().replace("+", "")) || 0;
                $contador.text(response.liked ? `+${actual + 1}` : `+${actual - 1}`);
                const $icono = $(".btnLike svg");
                $icono.attr("fill", response.liked ? "#2ecc71" : "lightgrey");
            }
        }
    });
});

$(document).on("click", ".nombreComentario", function () {
    const idCom = $(this).data("id");
    window.location.href = "visorPerfil.html?id=" + idCom + "&volver=post";
});

$("#nombrePost").click(function () {
    if (tipoAutorPost === "pagina") {
        window.location.href = "visorPagina.html?id=" + idUsuarioPost + "&volver=post";
    } else {
        window.location.href = "visorPerfil.html?id=" + idUsuarioPost + "&volver=post";
    }
});

$("#btnCompartir").click(function () {
    cargarModalCompartir(numPost);
});

$("#btnMasOpciones").click(function () {
    activarModalOpcionesPost(numPost, idUsuarioPost);
});

$(document).on("click", "#btnVolver", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const volver = urlParams.get("volver");
    if (volver === "post") {
        const idPost = urlParams.get("idpost");
        window.location.href = `visorPost.html?idpost=${idPost}`;
    } else {
        window.location.href = "novedades.html";
    }
});

let RESPONDIENDO_A = null;

$(document).on("click", ".btnResponder", function () {
    const nombre = $(this).data("nombre");
    const idComentario = $(this).data("id");
    RESPONDIENDO_A = idComentario;
    $("#comentarioFlotante").removeClass("d-none");
    $("#respondiendoA").removeClass("d-none");
    $("#nombreRespondiendo").text(nombre);
    $("#inputComentario").val("").focus();
});

$("#cancelarRespuesta").click(function () {
    RESPONDIENDO_A = null;
    $("#respondiendoA").addClass("d-none");
});

$("#btnEnviarComentario").click(function () {
    const texto = $("#inputComentario").val().trim();
    const idUsuario = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    if (!texto) {
        alert("Escribe algo para comentar.");
        return;
    }

    $.ajax({
        url: "API/post/comentarPost.php",
        type: "POST",
        data: {
            id: idUsuario,
            token: token,
            texto: texto,
            idpost: numPost,
            idrespuesta: RESPONDIENDO_A
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                $("#inputComentario").val("");
                $("#respondiendoA").addClass("d-none");
                RESPONDIENDO_A = null;
                cargarPost(numPost);

                // Crear notificación de comentario en post propio (tipo 2)
                $.post("API/notificaciones/crearNotificacion.php", {
                    id: idUsuario,
                    token: token,
                    tipo: 2,
                    post: numPost
                }, function (resNotif) {
                    if (!resNotif.success && resNotif.mensaje !== 'No se notifica al autor') {
                        console.warn("Error creando notificación:", resNotif.mensaje);
                    }
                }, "json");

            } else {
                alert(response.mensaje || "No se pudo enviar el comentario.");
            }
        }
    });
});

$(document).on("click", "#btnLikeComentario", function () {
    const idComentario = $(this).data("id");
    const idUsuario = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");
    const $btn = $(this);

    $.ajax({
        url: "API/post/likeComentario.php",
        type: "POST",
        data: {
            id: idUsuario,
            token: token,
            idcomentario: idComentario
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                $btn.css("color", response.liked ? "#2ecc71" : "grey");
            }
        }
    });
});
