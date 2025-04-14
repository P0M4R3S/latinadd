//Variable global del numero de post
let numPost;
let idUsuarioPost;

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
                renderizarPost(respuesta.post, respuesta.comentarios);
            } else {
                console.warn("No se pudo cargar el post.");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar el post:", error);
        }
    });
}

function renderizarPost(post, comentarios) {
    const nombreCompleto = `${post.nombre} ${post.apellidos}`;
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

    // Quitar imágenes anteriores
    $("#bloqueImagenesPost").remove();
    $("#bloquePostCompartido").remove();

    // Si es un post compartido
    if (post.tipo == 3 && post.compartido) {
        const compartido = post.compartido;
        const compartidoHTML = $(`
            <div class="row mt-2" id="bloquePostCompartido">
                <div class="col-12 p-2 rounded border">
                    <div class="row">
                        <div class="col-2">
                            <img class="imgPost" src="${compartido.foto || 'img/default.jpg'}" alt="">
                        </div>
                        <div class="col-10">
                            <span class="nombrePost">${compartido.nombre} ${compartido.apellidos}</span><br>
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

        // Agregar imágenes al compartido si las hay
        if (compartido.imagenes && compartido.imagenes.length > 0) {
            const contenedor = compartidoHTML.find("#imagenesCompartido");
            compartido.imagenes.forEach(ruta => {
                const img = $(`<img src="${ruta}" class="img-fluid m-1" style="max-height: 250px;">`);
                contenedor.append(img);
            });
        }

        compartidoHTML.insertAfter("#txtContenidoPost");

    } else {
        // Añadir imágenes del post original si no es compartido
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

    // Renderizar comentarios y respuestas
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
    const margen = esRespuesta ? `
    <div class="col-1"><span></span></div>
    <div class="col-11">
    ` : '';

    const cierre = esRespuesta ? `</div>` : '';
    const colorLike = com.liked ? "#615DFA" : "grey";

    return `
    <div class="row mb-4">
        ${margen}
            <div class="row">
                <div class="col-2">
                    <img class="imgPost" src="${foto}" alt="">
                </div>
                <div class="col-10 globoComentario">
                    <span class="nombreComentario">${nombre}</span><br>
                    <span class="textoComentario">${texto}</span>
                </div>
            </div>
            <div class="row d-flex">
                <div class="col-2"></div>
                <div class="col-3 minitexto">${tiempo}</div>
                <div class="col-3 minitexto" style="cursor:pointer; color:${colorLike};" id="btnLikeComentario" data-id="${com.id}">Me gusta</div>
                <div class="col-3 minitexto btnResponder" data-nombre="${com.nombre + " " + com.apellidos}" data-id="${com.id}">Responder</div>
                <div class="col-2"></div>
            </div>
        ${cierre}
    </div>`;
}


//Funcionalidad para el like
//Funcionalidad del boton asociado al like post
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
                console.log(response.mensaje);

                // Actualizar contador de likes
                const $contador = $("#likesPost");
                let actual = parseInt($contador.text().replace("+", "")) || 0;
                $contador.text(response.liked ? `+${actual + 1}` : `+${actual - 1}`);

                // Cambiar color del icono
                const $icono = $(".btnLike svg");
                $icono.attr("fill", response.liked ? "#615DFA" : "lightgrey");
            } else {
                console.warn("Error al procesar el like:", response.mensaje);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error de conexión:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });
});

//Funcion que redirecciona al perfil del usuario creador del post
$("#nombrePost").click(function() {
    window.location.href = "visorPerfil.html?id=" + idUsuarioPost + "&volver=post&idpost=" + numPost;
});

//Funcionalidad del boton de compartir
$("#btnCompartir").click(function() {
    cargarModalCompartir(numPost);
});

$("#btnMasOpciones").click(function() {
    activarModalOpcionesPost(numPost, idUsuarioPost);
});

//Logica del boton volver que redirecciona
$(document).on("click", "#btnVolver", function () {
    if (typeof VOLVER !== 'undefined' && VOLVER === "novedades") {
        window.location.href = "novedades.html";
    }else if (typeof VOLVER !== 'undefined' && VOLVER === "post") {
        const urlParams = new URLSearchParams(window.location.search);
        const idPost = urlParams.get("idpost");
        window.location.href = "localhost/latinadd/visorPost.html?post=" + idPost;
    }else{
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

$(document).on("click", "#cancelarRespuesta", function () {
    RESPONDIENDO_A = null;
    $("#respondiendoA").addClass("d-none");
});

$(document).on("click", "#btnEnviarComentario", function () {
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
                console.log("Comentario enviado");

                // Limpiar campo y resetear estado
                $("#inputComentario").val("");
                $("#respondiendoA").addClass("d-none");
                RESPONDIENDO_A = null;

                // Recargar comentarios del post
                cargarPost(numPost);
            } else {
                alert(response.mensaje || "No se pudo enviar el comentario.");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al enviar comentario:", error);
            console.log(xhr.responseText);
        }
    });
});


//Funcionalidad del boton me gusta de los comentarios
$(document).on("click", "#btnLikeComentario", function () {
    const idComentario = $(this).data("id");
    const idUsuario = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");
    const $btn = $(this);

    if (!idUsuario || !token) {
        alert("Debes iniciar sesión.");
        return;
    }

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
                // Cambiar visualmente el botón
                if (response.liked) {
                    $btn.css("color", "#615DFA");
                } else {
                    $btn.css("color", "grey");
                }
            } else {
                console.warn("Error:", response.mensaje);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al conectar con el servidor:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });
});
