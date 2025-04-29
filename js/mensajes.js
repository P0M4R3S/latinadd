let idUsuario = localStorage.getItem("idUsuario");
let tokenUsuario = localStorage.getItem("tokenUsuario");
let usuarioConversacion = null;
let indiceConversacion = 1;
let cargandoMensajes = false;


    cargarConversaciones();

    $(document).on("click", ".conversacion", function () {
        const id = $(this).data("id");
        const nombre = $(this).data("nombre");
        const foto = $(this).data("foto");

        abrirConversacion(id, nombre, foto);
    });

    $("#btnCerrarModal").click(() => {
        $("#modalConversacion").addClass("d-none");
        $(".cuerpoConversacion").empty();
        indiceConversacion = 1;
    
        // Volver a cargar la lista de conversaciones
        cargarConversaciones();
    });
    

    

    $(".cuerpoConversacion").on("scroll", function () {
        if (!cargandoMensajes && $(this).scrollTop() === 0) {
            indiceConversacion++;
            cargarMensajes(usuarioConversacion, false);
        }
    });

function cargarConversaciones() {
    $.post("API/usuarios/cargarConversaciones.php", {
        id: idUsuario,
        token: tokenUsuario
    }, res => {
        if (res.success) renderizarConversaciones(res.conversaciones);
    }, 'json');
}

function renderizarConversaciones(conversaciones) {
    $(".listaConversaciones").empty();
    conversaciones.forEach(conv => {
        const hora = conv.fecha ? new Date(conv.fecha).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
        const sinLeerClase = conv.sin_leer > 0 ? 'fw-bold text-dark' : 'text-muted';
        const imagen = conv.foto || "img/default.png";
        const html = `
        <div class="row mt-3 d-flex justify-content-center align-items-center conversacion" 
             data-id="${conv.id}" 
             data-nombre="${conv.nombre}" 
             data-foto="${imagen}">
            <div class="row mb-1 w-100">
                <div class="col-2 p-0">
                    <img class="imgConversacion rounded-circle" src="${imagen}" width="48" height="48" alt="">
                </div>
                <div class="col-8">
                    <div class="row nombreConversacion"><span>${conv.nombre}</span></div>
                    <div class="row textoConversacion ${sinLeerClase}">
                        <span class="ultimoMensaje">${conv.ultimo}</span>
                    </div>
                </div>
                <div class="col-2 d-flex align-items-start justify-content-end">
                    <span class="tiempoConversacion">${hora}</span>
                </div>
            </div>
        </div>`;
        $(".listaConversaciones").append(html);
    });
}

function abrirConversacion(id, nombre, foto) {
    usuarioConversacion = id;
    indiceConversacion = 1;
    $("#modalConversacion").removeClass("d-none");
    $("#nombreConversacion").text(nombre);
    $("#fotoConversacion").attr("src", foto || "img/default.png");
    $(".cuerpoConversacion").empty();
    cargarMensajes(id, true);
}

function cargarMensajes(id, scrollFinal = true) {
    cargandoMensajes = true;

    $.post("API/usuarios/cargarConversacion.php", {
        id: idUsuario,
        token: tokenUsuario,
        idusuario: id
    }, res => {
        if (res.success) {
            // Mostrar imagen y nombre en el header del modal
            const participante = Object.values(res.participantes).find(u => u.id_usuario != idUsuario);
            if (participante) {
                $("#modalTitulo").text(`${participante.nombre} ${participante.apellidos}`);
                $("#modalImagen").attr("src", participante.imagen || "img/default.png");
            }

            // Renderizar mensajes
            const mensajesHtml = res.mensajes.map(m => {
                const clase = m.emisor == idUsuario ? 'text-end text-primary' : 'text-start text-dark';
                return `<div class="${clase} mb-2">
                            <span class="px-3 py-2 border rounded d-inline-block">${m.mensaje}</span>
                        </div>`;
            }).join("");

            $("#contenidoConversacion").html(mensajesHtml);

            // Hacer scroll al final si se solicita
            if (scrollFinal) {
                requestAnimationFrame(() => {
                    const contenedor = document.getElementById("contenidoConversacion");
                    if (contenedor) {
                        contenedor.scrollTop = contenedor.scrollHeight;
                    }
                });
            }
        } else {
            console.log("Error al cargar mensajes:", res.mensaje);
        }

        cargandoMensajes = false;
    }, 'json');
}


$("#btnEnviarMensaje").click(() => {
    const mensaje = $("#inputMensaje").val().trim();
    if (!mensaje || !usuarioConversacion) return;

    $.post("API/usuarios/mensajeDirecto.php", {
        id: idUsuario,
        token: tokenUsuario,
        idreceptor: usuarioConversacion,
        texto: mensaje
    }, res => {
        if (res.success) {
            // Crear HTML del nuevo mensaje
            const htmlMensaje = `
                <div class="text-end text-primary mb-2">
                    <span class="px-3 py-2 border rounded d-inline-block">${mensaje}</span>
                </div>`;

            // Agregar el mensaje al final del contenedor
            $("#contenidoConversacion").append(htmlMensaje);

            // Hacer scroll hacia abajo
            const contenedor = document.getElementById("contenidoConversacion");
            contenedor.scrollTop = contenedor.scrollHeight;

            // Limpiar input
            $("#inputMensaje").val('');
        } else {
            console.log("Error al enviar mensaje:", res.mensaje);
        }
    }, 'json');
});
