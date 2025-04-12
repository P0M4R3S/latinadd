

function cargarPost(id) {
    const idUsuario = localStorage.getItem("idUsuario");
    const tokenUsuario = localStorage.getItem("tokenUsuario");
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
                console.log("Post cargado correctamente", respuesta);
                renderizarPost(respuesta.post, respuesta.comentarios)
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar el post:", error);
        }
    });
}

$(document).on("click", "#btnVolver", function() {
    if(VOLVER === "novedades"){
        window.location.href = "novedades.html";
    }
});


function renderizarPost(post, comentarios) {
    const nombreCompleto = `${post.nombre} ${post.apellidos}`;
    const fotoPerfil = post.foto || "img/default.jpg";
    const tiempo = calcularTiempo(post.fecha); // Asumimos que hay una función para eso
    const texto = post.texto || "";
    const likes = post.likes || 0;
    const imagenes = post.imagenes || [];

    let html = `
    <div class="visor">
        <div class="container">
            <div class="row">
                <div class="col-2" id="btnVolver">
                    <svg onclick="window.history.back()" fill="#615DFA" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                    </svg>
                </div>
            </div>
            <div class="container-fluid post">
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoPerfil}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost">${nombreCompleto}</span><br>
                        <span class="tiempoPost">${tiempo}</span>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z"/>
                        </svg>
                    </div>
                </div>
                <div class="row"><span class="textoVisor perfilPost">${texto}</span></div>
    `;

    // Agregar imágenes si las hay
    if (imagenes.length > 0) {
        html += '<div class="row mb-3"><div class="col-12 d-flex flex-wrap justify-content-center">';
        imagenes.forEach(ruta => {
            html += `<img src="${ruta}" class="img-fluid m-1" style="max-height: 250px;">`;
        });
        html += '</div></div>';
    }

    html += `
                <div class="row perfilPost">
                    <div class="col-6">
                        <img src="img/reaction/reacciones.png" alt="">
                        <span class="textareaPost">+${likes}</span>
                    </div>
                </div>
                <div class="row mt-3 d-flex justify-content-around perfilPost">
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="..."/> <!-- Aquí puedes reemplazar por el path completo -->
                        </svg>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="..."/>
                        </svg>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="..."/>
                        </svg>
                    </div>
                </div>
    `;

    // Comentarios
    comentarios.forEach(com => {
        const nombreCom = `${com.nombre} ${com.apellidos}`;
        const fotoCom = com.foto || "img/default.jpg";
        const textoCom = com.texto;
        const tiempoCom = calcularTiempo(com.fecha);
        const claseMargen = com.idrespuesta ? 'col-11 offset-1' : 'col-12';
        html += `
        <div class="row mb-4">
            <div class="${claseMargen}">
                <div class="row">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoCom}" alt="">
                    </div>
                    <div class="col-10 globoComentario">
                        <span class="nombreComentario">${nombreCom}</span><br>
                        <span class="textoComentario">${textoCom}</span>
                    </div>
                </div>
                <div class="row d-flex">
                    <div class="col-2"></div>
                    <div class="col-2 minitexto">${tiempoCom}</div>
                    <div class="col-3 minitexto">Me gusta</div>
                    <div class="col-3 minitexto">Responder</div>
                    <div class="col-2"></div>
                </div>
            </div>
        </div>`;
    });

    html += `</div></div></div>`;
    $(".contenido").html(html);
}
