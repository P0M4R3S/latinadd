function cargarBusqueda() {
    const urlParams = new URLSearchParams(window.location.search);
    const query = urlParams.get('query');

    if (!query) {
        $("#tituloBusqueda").text("No se ingresó ningún término de búsqueda.");
        return;
    }

    $("#tituloBusqueda").text(`Resultados para: ${query}`);

    $.ajax({
        url: "API/post/busqueda.php",
        method: "POST",
        data: { query: query },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                if (res.resultados.length === 0) {
                    $("#resultados").html("<p>No se encontraron resultados.</p>");
                } else {
                    $("#resultados").empty();
                    renderizarResultadosBusqueda(res)
                }
            } else {
                $("#resultados").html("<p>Hubo un error al buscar.</p>");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error en la búsqueda:", error);
            console.log(xhr.responseText); 
        }
    });
}



function renderizarResultadosBusqueda(res) {
    $("#resultados").empty(); // Limpiar resultados anteriores
    console.log("Resultados de búsqueda:", res);
    if (res.resultados.length === 0) {
        $("#resultados").html("<p>No se encontraron resultados.</p>");
        return;
    }

    res.resultados.forEach(item => {
        let html = "";
        let rutaImagen = "";

        if (item.tipo === "usuario") {
            // Asumimos que las fotos de usuarios están en "imagenes/usuarios/"
            rutaImagen = item.foto;
            html = `
                <div class="resultado d-flex align-items-center mb-3" onclick="irAPerfil(${item.id})">
                    <img src="${rutaImagen}" alt="Usuario" class="imgResultado rounded-circle me-3" width="50" height="50">
                    <div>
                        <h6 class="mb-0">${item.nombre}</h6>
                        <small>Perfil de usuario</small>
                    </div>
                </div>
            `;
        } else if (item.tipo === "pagina") {
            // Asumimos que las fotos de páginas están en "imagenes/paginas/"
            rutaImagen = item.foto;
            html = `
                <div class="resultado d-flex align-items-center mb-3" onclick="irAPagina(${item.id})">
                    <img src="${rutaImagen}" alt="Página" class="imgResultado rounded-circle me-3" width="50" height="50">
                    <div>
                        <h6 class="mb-0">${item.nombre}</h6>
                        <small>Página</small>
                    </div>
                </div>
            `;
        }

        $("#resultados").append(html);
    });
}

// Función para ir al perfil de usuario
function irAPerfil(idUsuario) {
    window.location.href = `visorPerfil.html?id=${idUsuario}`;
}

// Función para ir al perfil de página
function irAPagina(idPagina) {
    window.location.href = `visorPagina.html?id=${idPagina}`;
}
