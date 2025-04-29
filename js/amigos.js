let filtro = "todos"; // 'todos', 'amigos', 'sugerencias'
let indiceAmigos = 1;
let cargandoAmigos = false;
let finAmigos = false;

$("#pulsador1").click(() => cambiarFiltro("todos"));
$("#pulsador2").click(() => cambiarFiltro("amigos"));
$("#pulsador3").click(() => cambiarFiltro("sugerencias"));

$(window).scroll(() => {
    if (!cargandoAmigos && !finAmigos && $(window).scrollTop() + $(window).height() > $(document).height() - 200) {
        cargarAmigos();
    }
});

function cambiarFiltro(nuevoFiltro) {
    filtro = nuevoFiltro;
    indiceAmigos = 1;
    finAmigos = false;
    $(".listaAmigos").empty();
    cargarAmigos();
}

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

function renderizarUsuario(usuario) {
    const imagen = usuario.imagen || "img/default.png";
    let textoBoton = "", claseBoton = "btn-primary", accion = "", extra = "";

    switch (usuario.estado) {
        case "amigo":
            textoBoton = "Eliminar amigo";
            claseBoton = "btn-danger";
            accion = "eliminarAmistad";
            break;
        case "solicitado":
            textoBoton = "Cancelar solicitud";
            claseBoton = "btn-secondary";
            accion = "eliminarPeticionAmistad";
            break;
        case "recibido":
            textoBoton = "Aceptar solicitud";
            claseBoton = "btn-success";
            accion = "aceptarAmistad";
            extra = "data-peticion='1'"; // Acepta por defecto
            break;
        case "ninguno":
        default:
            textoBoton = "Agregar amigo";
            claseBoton = "btn-primary";
            accion = "peticionAmistad";
            break;
    }

    const html = `
        <div class="container-fluid postPerfil mt-3 border rounded p-3 bg-light">
            <div class="row align-items-center">
                <div class="col-3">
                    <img class="img-fluid rounded" src="${imagen}" alt="Imagen del usuario">
                </div>
                <div class="col-6">
                    <span class="nombrePost h5">${usuario.nombre}</span>
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

$(document).on("click", ".btnAmistad", function () {
    const idamigo = $(this).data("id");
    const accion = $(this).data("accion");
    const peticion = $(this).data("peticion") || 0;
    const $btn = $(this);

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

    $.post(`API/usuarios/${accion}.php`, data, function (res) {
        if (res.success) {
            $btn.closest(".postPerfil").fadeOut(200, function () {
                $(this).remove();
            });
        } else {
            alert(res.mensaje || "Ocurrió un error.");
        }
    }, 'json');
});
