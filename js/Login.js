$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        $.post('php/Login/acceso.php', $('#loginForm').serialize(), function (data) {
            
            if (data == 0) {
                Swal.fire({
                    icon: "error",
                    title: "Ha ocurrido un error",
                    text: "La contraseña o el nombre de usario es incorrecto",
                    footer: 'Si esta teniendo problemas contacte con el administrador',
                    timer: 2000
                });
            } else if (data == 1) {
                var url = "php/inicio.php";
                $(location).attr('href', url);
            } else {
                Swal.fire({
                    icon: "info",
                    title: "Atención",
                    text: "Respuesta inesperada del servidor.",
                    timer: 2000
                    /* showConfirmButton: false */
                });
            }
        }).fail(function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo conectar con el servidor.",
                timer: 2000,
            });
        });
    });
});
