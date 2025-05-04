let filtro = "todos"; // 'todos', 'amigos', 'sugerencias'
let indiceAmigos = 1;
let cargandoAmigos = false;
let finAmigos = false;

// Filtros
$("#pulsador1").click(() => cambiarFiltro("todos"));
$("#pulsador2").click(() => cambiarFiltro("amigos"));
$("#pulsador3").click(() => cambiarFiltro("sugerencias"));

// Scroll infinito
$(window).scroll(() => {
    if (!cargandoAmigos && !finAmigos && $(window).scrollTop() + $(window).height() > $(document).height() - 200) {
        cargarAmigos();
    }
});

// Cambiar filtro
function cambiarFiltro(nuevoFiltro) {
    filtro = nuevoFiltro;
    indiceAmigos = 1;
    finAmigos = false;
    $(".listaAmigos").empty();
    cargarAmigos();
}

// Cargar usuarios
function cargarAmigos() {
    cargandoAmigos = true;

    $.ajax({
        url: "API/usuarios/cargarAmigos.php",
        method: "POST",
        data: {
            id: localStorage.getItem("idUsuario"),
            token: localStorage.getItem("tokenUsuario"),
            filtro: filtro,
            indice: indiceAmigos
        },
        dataType: "json",
        success: function (res) {
            if (res.success && res.usuarios.length > 0) {
                res.usuarios.forEach(u => renderizarUsuario(u));
                indiceAmigos++;
            } else {
                finAmigos = true;
            }
            cargandoAmigos = false;
        },
        error: function (err) {
            console.error("Error cargando amigos:", err);
            cargandoAmigos = false;
        }
    });
}

// Renderizar usuario individual
function renderizarUsuario(usuario) {
    const imagen = usuario.imagen || "img/default.png";
    let textoBoton = "", claseBoton = "btn-primary", accion = "", extra = "";

    switch (usuario.estado) {
        case "amigo":
            textoBoton = "Eliminar amigo";
            claseBoton = "btnEliminarAmigo";
            accion = "eliminarAmistad";
            break;
        case "solicitado":
            textoBoton = "Cancelar solicitud";
            claseBoton = "btnCancelarSolicitud";
            accion = "eliminarPeticionAmistad";
            break;
        case "recibido":
            textoBoton = "Aceptar solicitud";
            claseBoton = "btnCancelarSolicitud";
            accion = "aceptarAmistad";
            extra = "data-peticion='1'";
            break;
        case "ninguno":
        default:
            textoBoton = "Agregar amigo";
            claseBoton = "btnAgregarAmigo";
            accion = "peticionAmistad";
            break;
    }

    const html = `
        <div class="container-fluid postPerfil mt-3 border rounded p-3 bg-light">
            <div class="row align-items-center">
                <div class="col-3">
                    <img class="img-fluid rounded imgAmigo" data-id="${usuario.id}" src="${imagen}" alt="Imagen del usuario">
                </div>
                <div class="col-6">
                    <span class="nombrePost h5 nombreAmigo" data-id="${usuario.id}">${usuario.nombre}</span>
                </div>
                <div class="col-3 text-end">
                    <button class="btn btn-sm ${claseBoton} btnAmistad" 
                        data-id="${usuario.id}" 
                        data-accion="${accion}" ${extra}>
                        ${textoBoton}
                    </button>
                </div>
            </div>
        </div>`;

    $(".listaAmigos").append(html);
}

// Botón de amistad
$(document).on("click", ".btnAmistad", function () {
    const $btn = $(this);
    const idamigo = $btn.data("id");
    const accion = $btn.data("accion");
    const peticion = $btn.data("peticion") || 0;

    const data = {
        id: localStorage.getItem("idUsuario"),
        token: localStorage.getItem("tokenUsuario")
    };

    if (accion === "aceptarAmistad") {
        data.idpeticion = idamigo;
        data.respuesta = peticion;
    } else if (accion === "peticionAmistad" || accion === "eliminarPeticionAmistad") {
        data.idusuario = idamigo;
    } else {
        data.idamigo = idamigo;
    }

    $btn.prop("disabled", true);

    $.post(`API/usuarios/${accion}.php`, data, function (res) {
        if (res.success) {
            let nuevoTexto = "", nuevaClase = "", nuevaAccion = "", nuevoExtra = "";

            switch (accion) {
                case "peticionAmistad":
                    nuevoTexto = "Cancelar solicitud";
                    nuevaClase = "btnCancelarSolicitud";
                    nuevaAccion = "eliminarPeticionAmistad";

                    // Crear notificación de tipo 1
                    $.post("API/notificaciones/crearNotificacion.php", {
                        id: localStorage.getItem("idUsuario"),
                        token: localStorage.getItem("tokenUsuario"),
                        tipo: 1,
                        receptor: idamigo
                    }, function (resNotif) {
                        if (!resNotif.success) {
                            console.warn("Notificación no creada:", resNotif.mensaje);
                        }
                    }, "json");

                    break;

                case "eliminarPeticionAmistad":
                case "eliminarAmistad":
                    nuevoTexto = "Agregar amigo";
                    nuevaClase = "btnAgregarAmigo";
                    nuevaAccion = "peticionAmistad";
                    break;

                case "aceptarAmistad":
                    nuevoTexto = "Eliminar amigo";
                    nuevaClase = "btnEliminarAmigo";
                    nuevaAccion = "eliminarAmistad";
                    break;
            }

            $btn
                .text(nuevoTexto)
                .removeClass()
                .addClass(`btn btn-sm ${nuevaClase} btnAmistad`)
                .data("accion", nuevoAccion)
                .removeAttr("data-peticion");

        } else {
            console.warn(res.mensaje || "Error en la operación.");
        }
    }, 'json').always(() => {
        $btn.prop("disabled", false);
    });
});



// Acciones sobre nombre o imagen
$(document).on("click", ".nombreAmigo", function () {
    const id = $(this).data("id");
    window.location.href = `visorPerfil.html?id=${id}&volver=novedades`;
});

$(document).on("click", ".imgAmigo", function () {
    const id = $(this).data("id");
    window.location.href = `visorPerfil.html?id=${id}&volver=novedades`;
});
