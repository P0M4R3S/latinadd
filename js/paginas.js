let filtro = "todos"; // puede ser: 'todos', 'seguidos', 'sugerencias'
let indicePaginas = 1;
let cargandoPaginas = false;
let finPaginas = false;

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
                    <img class="img-fluid rounded imgPagina" data-id="${pagina.id}" src="${imagen}" alt="Imagen de la página" style="cursor:pointer;">
                </div>
                <div class="col-6">
                    <span class="nombrePost h5 nombrePagina" data-id="${pagina.id}" style="cursor:pointer;">${pagina.nombre}</span>
                </div>
                <div class="col-3 text-end">
                    <button class="btn btn-sm btnAgregarAmigo btnSeguir" 
                        data-id="${pagina.id}" 
                        data-seguida="${pagina.seguida ? 1 : 0}">
                        ${seguirTexto}
                    </button>
                </div>
            </div>
        </div>`;

    $(".listaGrupos").append(html);
}

// Redirección al hacer clic en imagen o nombre
$(document).on("click", ".imgPagina, .nombrePagina", function () {
    const id = $(this).data("id");
    window.location.href = `visorPagina.html?id=${id}&volver=paginas`;
});

// Seguir o dejar de seguir
$(document).on("click", ".btnSeguir", function () {
    const $btn = $(this);
    const idpagina = $btn.data("id");
    const seguida = $btn.data("seguida") == 1;
    const url = seguida ? "API/paginas/dejarSeguirPagina.php" : "API/paginas/seguirPagina.php";

    $btn.prop("disabled", true);

    $.post(url, {
        id: localStorage.getItem("idUsuario"),
        token: localStorage.getItem("tokenUsuario"),
        idpagina: idpagina
    }, function (res) {
        if (res.success) {
            const nuevaSeguida = !seguida;
            $btn.text(nuevaSeguida ? "Dejar de seguir" : "Seguir +");
            $btn.data("seguida", nuevaSeguida ? 1 : 0);
        } else {
            console.warn("No se pudo actualizar el seguimiento.");
        }
    }, 'json').always(() => {
        $btn.prop("disabled", false);
    });
});
