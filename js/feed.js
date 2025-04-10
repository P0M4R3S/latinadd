let indiceFeed = 1; // Variable global para el índice del feed
let loading = false;
            let scroll = true;


function cargarFeed() {
    const id = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    if (!id || !token) {
        console.error("Faltan datos de sesión.");
        return;
    }

    // Si no está definida globalmente, iniciamos en 1
    if (typeof indiceFeed === "undefined") {
        indiceFeed = 1;
    }

    fetch("API/post/cargarFeed.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            id: id,
            token: token,
            indice: indiceFeed
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log("POSTS CARGADOS:", data.posts);
            indiceFeed += 1;
            imprimirPosts(data.posts);
            loading = false; // Desactivar loading
        } else {
            console.error("Error del servidor:", data.mensaje);
        }
    })
    .catch(err => {
        console.error("Error en la petición AJAX:", err);
    });
}

function imprimirPosts(posts) {
    const contenedor = document.querySelector('.contenido');
    if (!contenedor) return;

    posts.forEach(post => {
        const tiempo = calcularTiempo(post.fecha);
        const comentarios = post.comentarios || 0;
        const likes = post.likes || 0;

        let html = '';

        // === POST COMPARTIDO ===
        if (post.tipo == 3 && post.compartido) {
            const nombreCompartido = `${post.usuario_nombre} ${post.usuario_apellidos}`;
            const fotoCompartido = post.usuario_foto || "img/default.jpg";

            const compartido = post.compartido;
            const nombreOriginal = `${compartido.nombre} ${compartido.apellidos}`;
            const fotoOriginal = compartido.foto || "img/default.jpg";
            const textoOriginal = compartido.texto || '';
            const tiempoOriginal = calcularTiempo(compartido.fecha);
            const imagenCompartida = (compartido.imagenes && compartido.imagenes.length > 0) ? compartido.imagenes[0] : null;

            html += `
            <div class="container-fluid post">
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoCompartido}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost">${nombreCompartido} ha compartido</span><br>
                        <span class="tiempoPost">${tiempo}</span>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z"/></svg>
                    </div>
                </div>
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoOriginal}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost">${nombreOriginal}</span><br>
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
                <div class="row mt-3 d-flex justify-content-around">
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="..."/></svg>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="..."/></svg>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="..."/></svg>
                    </div>
                </div>
            </div>`;

        } else {
            // === POST NORMAL (tipo 1 o 2) ===
            const nombre = `${post.usuario_nombre} ${post.usuario_apellidos}`;
            const fotoPerfil = post.usuario_foto || "img/default.jpg";
            const texto = post.texto || '';
            const imagen = (post.imagenes && post.imagenes.length > 0) ? post.imagenes[0] : null;

            html += `
            <div class="container-fluid post">
                <div class="row perfilPost">
                    <div class="col-2">
                        <img class="imgPost" src="${fotoPerfil}" alt="">
                    </div>
                    <div class="col-8">
                        <span class="nombrePost">${nombre}</span><br>
                        <span class="tiempoPost">${tiempo}</span>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z"/></svg>
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
                <div class="row mt-3 d-flex justify-content-around">
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="..."/></svg>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="..."/></svg>
                    </div>
                    <div class="col-2">
                        <svg fill="lightgrey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="..."/></svg>
                    </div>
                </div>
            </div>`;
        }

        contenedor.innerHTML += html;
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
