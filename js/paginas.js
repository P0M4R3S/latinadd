let filtro = "todos"; // puede ser: 'todos', 'seguidos', 'sugerencias'
let indicePaginas = 1;
let cargandoPaginas = false;
let finPaginas = false;

    // Eventos de los botones
    $("#pulsador1").click(() => cambiarFiltro("todos"));
    $("#pulsador2").click(() => cambiarFiltro("seguidos"));
    $("#pulsador3").click(() => cambiarFiltro("sugerencias"));

    $(window).scroll(() => {
        if (!cargandoPaginas && !finPaginas && $(window).scrollTop() + $(window).height() > $(document).height() - 200) {
            cargarPaginas();
        }
    });

function cambiarFiltro(nuevoFiltro) {
    filtro = nuevoFiltro;
    indicePaginas = 1;
    finPaginas = false;
    $(".listaGrupos").empty();
    cargarPaginas();
}

function cargarPaginas() {
    cargandoPaginas = true;

    $.ajax({
        url: "API/paginas/buscadorPaginas.php",
        method: "POST",
        data: {
            id: localStorage.getItem("idUsuario"),
            token: localStorage.getItem("tokenUsuario"),
            filtro: filtro,
            indice: indicePaginas
        },
        dataType: "json",
        success: function (res) {
            if (res.success && res.paginas.length > 0) {
                res.paginas.forEach(p => renderizarPagina(p));
                indicePaginas++;
            } else {
                finPaginas = true;
            }
            cargandoPaginas = false;
        },
        error: function (err) {
            console.error("Error cargando páginas:", err);
            cargandoPaginas = false;
        }
    });
}

function renderizarPagina(pagina) {
    const imagen = pagina.imagen || "img/default.png";
    const seguirTexto = pagina.seguida ? "Dejar de seguir" : "Seguir +";

    const html = `
        <div class="container-fluid postPerfil mt-3 border rounded p-3 bg-light">
            <div class="row align-items-center">
                <div class="col-3">
                    <img class="img-fluid rounded" src="${imagen}" alt="Imagen de la página">
                </div>
                <div class="col-6">
                    <span class="nombrePost h5">${pagina.nombre}</span>
                </div>
                <div class="col-3 text-end">
                    <button class="btn btn-sm btn-outline-primary btnSeguir" data-id="${pagina.id}" data-seguida="${pagina.seguida}">
                        ${seguirTexto}
                    </button>
                </div>
            </div>
        </div>`;
    
    $(".listaGrupos").append(html);
}

// Evento para seguir/dejar de seguir páginas
$(document).on("click", ".btnSeguir", function () {
    const idpagina = $(this).data("id");
    const seguida = $(this).data("seguida");
    const url = seguida ? "API/paginas/dejarSeguirPagina.php" : "API/paginas/seguirPagina.php";
    const $btn = $(this);

    $.post(url, {
        id: localStorage.getItem("idUsuario"),
        token: localStorage.getItem("tokenUsuario"),
        idpagina: idpagina
    }, function (res) {
        if (res.success) {
            $btn.text(seguida ? "Seguir +" : "Dejar de seguir");
            $btn.data("seguida", !seguida);
        } else {
            console.log("No se pudo actualizar el seguimiento.");
        }
    }, 'json');
});
