<div class="contenedor_forjando_caminos_form">
    <h6>Ingresa tus datos personales</h6>
    <form class = "formulario_donantes" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('fcv_upload', 'wp_nonce'); ?>
        <label for="name">Nombre</label>
        <input class = "input_texto_formulario" type="text" name="name" placeholder="Nombre" required>
        <label for="name">Apellido</label>
        <input class = "input_texto_formulario" type="text" name="lastname" placeholder="Apellido" required>
        <label for="name">Correo</label>
        <input class = "input_texto_formulario" type="email" name="email" placeholder="Correo" required>
        <label for="name">Telefono</label>
        <input class = "input_texto_formulario" type="number" name="phone" placeholder="Telefono" required>
        <label for="name">Aporte economico</label>
        <select name = "aportes_economicos">
            <option value = "Cuenta">Número de cuenta</option>
            <option value = "Residencia">Dirección de residencia</option>
        </select>
        <label for="name">Aporte elementos</label>
        <select name = "aportes_elementos">
            <option value = "alimentos">Alimentos</option>
            <option value = "ropa">Ropa</option>
            <option value = "utiles">Útiles escolares</option>
            <option value = "regalos">Regalos</option>
        </select>
        <button type="submit">Enviar</button>
    </form>
</div>