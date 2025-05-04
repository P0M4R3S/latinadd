
async function tieneNotificaciones() {
    const id = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    if (!id || !token) return false;

    try {
        const res = await $.post("API/notificaciones/hayNotificaciones.php", { id, token }, null, "json");
        return res.success && res.hay === true;
    } catch {
        console.error("Error al conectar con el servidor de notificaciones.");
        return false;
    }
}

async function tieneMensajes() {
    const id = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    if (!id || !token) return false;

    try {
        const res = await $.post("API/notificaciones/hayMensajes.php", { id, token }, null, "json");
        return res.success && res.hay === true;
    } catch {
        console.error("Error al conectar con el servidor de mensajes.");
        return false;
    }
}

function cargarNotificaciones() {
    const id = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    if (!id || !token) {
        console.warn("Sesión no iniciada.");
        return;
    }

    $.post("API/notificaciones/cargarNotificaciones.php", { id, token }, function (res) {
        if (res.success) {
            renderizarNotificaciones(res.notificaciones);
            marcarNotificacionesLeidas(); // Marcar como leídas al mostrar
        } else {
            console.warn("Error al cargar notificaciones:", res.mensaje);
        }
    }, "json");
}

function renderizarNotificaciones(lista) {
    const contenedor = $(".contenidoNotificaciones");
    contenedor.empty();

    if (!lista || lista.length === 0) {
        contenedor.html("<p class='text-center text-muted mt-5'>No tienes notificaciones.</p>");
        return;
    }

    lista.forEach(noti => {
        const fecha = calcularTiempo(noti.fecha);
        const leidoClass = noti.leido ? "bg-light" : "bg-white fw-bold";
        const nombre = noti.nombreUsuario || "Alguien";
        let texto = "";
        let icono = "";
        let destino = "#";

        switch (parseInt(noti.tipo)) {
            case 1:
                texto = `${nombre} te ha enviado una solicitud de amistad.`;
                icono = "👥";
                destino = `visorPerfil.html?id=${noti.otroUsuario}&volver=notificaciones`;
                break;
            case 2:
                texto = `${nombre}${noti.cantidad > 1 ? ` y ${noti.cantidad - 1} más` : ""} comentaron tu publicación.`;
                icono = "💬";
                destino = `visorPost.html?idpost=${noti.post}&volver=notificaciones`;
                break;
            case 3:
                texto = `${nombre} respondió a tu comentario.`;
                icono = "↩️";
                destino = `visorPost.html?idpost=${noti.post}&volver=notificaciones`;
                break;
            case 4:
                texto = `${nombre} compartió tu publicación.`;
                icono = "🔁";
                destino = `visorPost.html?idpost=${noti.post}&volver=notificaciones`;
                break;
            case 5:
                texto = `${nombre}${noti.cantidad > 1 ? ` y ${noti.cantidad - 1} más` : ""} le dieron like a tu publicación.`;
                icono = "❤️";
                destino = `visorPost.html?idpost=${noti.post}&volver=notificaciones`;
                break;
            default:
                texto = "Tienes una nueva notificación.";
        }

        const html = `
            <div class="card mb-2 p-2 notificacionItem ${leidoClass}" data-url="${destino}" style="cursor:pointer;">
                <div class="d-flex align-items-center">
                    <div class="me-3 fs-4">${icono}</div>
                    <div>
                        <div class="textoNotificacion">${texto}</div>
                        <div class="minitexto text-muted">${fecha}</div>
                    </div>
                </div>
            </div>
        `;

        contenedor.append(html);
    });
}

function marcarNotificacionesLeidas() {
    const id = localStorage.getItem("idUsuario");
    const token = localStorage.getItem("tokenUsuario");

    if (!id || !token) return;

    $.post("API/notificaciones/marcarNotificaciones.php", { id, token }, function (res) {
        if (!res.success) {
            console.warn("No se pudieron marcar las notificaciones:", res.mensaje);
        }
    }, "json");
}

function calcularTiempo(fechaISO) {
    const fecha = new Date(fechaISO);
    const ahora = new Date();
    const diffMs = ahora - fecha;
    const diffMin = Math.floor(diffMs / 60000);

    if (diffMin < 1) return "Justo ahora";
    if (diffMin < 60) return `Hace ${diffMin} minuto${diffMin === 1 ? '' : 's'}`;

    const horas = Math.floor(diffMin / 60);
    if (horas < 24) return `Hace ${horas} hora${horas === 1 ? '' : 's'}`;

    const dias = Math.floor(horas / 24);
    if (dias < 30) return `Hace ${dias} día${dias === 1 ? '' : 's'}`;

    const meses = Math.floor(dias / 30);
    if (meses < 12) return `Hace ${meses} mes${meses === 1 ? '' : 'es'}`;

    const años = Math.floor(meses / 12);
    return `Hace ${años} año${años === 1 ? '' : 's'}`;
}

// Redireccionar al hacer clic
$(document).on("click", ".notificacionItem", function () {
    const destino = $(this).data("url");
    if (destino) window.location.href = destino;
});


