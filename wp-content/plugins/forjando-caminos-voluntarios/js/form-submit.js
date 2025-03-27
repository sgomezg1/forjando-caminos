$(document).ready(function() {
    $(".formulario_voluntarios").submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append('action', 'fcv_handle_voluntarios_register');
        formData.append('file', $('#hoja_de_vida')[0].files[0]);
        formData.append('file', $('#tarjeta_profesional')[0].files[0]);
        $.ajax({
            url: wp_ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: "Exito",
                        text: response.message,
                        icon: "success",
                        confirmButtonText: "Cerrar"
                    });
                    $('.formulario_voluntarios')[0].reset();
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        });
    });

    $(".formulario_donantes").submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append('action', 'fcv_handle_donantes_register');
        $.ajax({
            url: wp_ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: "Exito",
                        text: response.message,
                        icon: "success",
                        confirmButtonText: "Cerrar"
                    });
                    $('.formulario_donantes')[0].reset();
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        });
    });
});