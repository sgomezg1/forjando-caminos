<div class="contenedor_forjando_caminos_form">
    <h6>Ingresa tus datos personales</h6>
    <form class = "formulario_voluntarios" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('fcv_upload', 'wp_nonce'); ?>
        <label for="name">Nombre</label>
        <input class = "input_texto_formulario" type="text" name="name" placeholder="Nombre" required>
        <label for="name">Apellido</label>
        <input class = "input_texto_formulario" type="text" name="lastname" placeholder="Apellido" required>
        <label for="name">Correo</label>
        <input class = "input_texto_formulario" type="email" name="email" placeholder="Correo" required>
        <label for="name">Telefono</label>
        <input class = "input_texto_formulario" type="number" name="phone" placeholder="Telefono" required>
        <label for="name">Hoja de vida</label>
        <input type="file" id = "hoja_de_vida" name="hoja_de_vida" accept=".pdf,.doc,.docx,.jpg,.png" required>
        <label for="name">Tarjeta profesional</label>
        <input type="file" id = "tarjeta_profesional" name="tarjeta_profesional" accept=".pdf,.doc,.docx,.jpg,.png" required>
        <button type="submit">Enviar</button>
    </form>
</div>