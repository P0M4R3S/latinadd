function cargarMensajeModal(mensaje) {
    // Cargar el mensaje en el modal
    $(".txtMensajeModal").text(mensaje);
    // Mostrar el modal
    $('#modalMensaje').fadeIn(150);
}

$("#btnAceptarMensaje").click(function() {
    // Ocultar el modal al hacer clic en el botón "Aceptar"
    $('#modalMensaje').fadeOut(150);
});