<div class="wrap">
    <h1>Formulario de voluntarios</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Telefono</th>
                <th>Correo</th>
                <th>Hoja de vida</th>
                <th>Tarjeta Profesional</th>
                <th>Hora subida</th>
            </tr>
        </thead>
        <tbody>
        <?php if (sizeof($results) === 0) { ?>
            <tr>
                <td colspan = "7">
                    <h3>No hay voluntarios registrados</h3>
                </td>
            </tr>
            <?php } else { foreach ($results as $row): ?>
                <tr>
                    <td><?php echo esc_html($row->name); ?></td>
                    <td><?php echo esc_html($row->lastname); ?></td>
                    <td><?php echo esc_html($row->email); ?></td>
                    <td><?php echo esc_html($row->phone); ?></td>
                    <td>
                        <a href="<?php echo esc_url($row->hoja_de_vida_url); ?>" download = "Hoja de vida <?php echo esc_html($row->name) . esc_html($row->lastname); ?>" target="_blank">
                            <?php echo esc_html(basename($row->hoja_de_vida_url)); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo esc_url($row->tarjeta_profesional_url); ?>" target="_blank" download = "Tarjeta Profesional <?php echo esc_html($row->name) . esc_html($row->lastname); ?>">
                            <?php echo esc_html(basename($row->tarjeta_profesional_url)); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html(formatear_fecha_crud($row->uploaded_at)); ?></td>
                </tr>
            <?php endforeach; }?>
        </tbody>
    </table>
</div>