<div class="wrap">
    <h1>Formulario de donantes</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Telefono</th>
                <th>Correo</th>
                <th>Hora subida</th>
            </tr>
        </thead>
        <tbody>
            <?php if (sizeof($results) === 0) { ?>
            <tr>
                <td colspan = "5">
                    <h3>No hay donantes registrados</h3>
                </td>
            </tr>
            <?php } else {
                foreach ($results as $row): ?>
                <tr>
                    <td><?php echo esc_html($row->name); ?></td>
                    <td><?php echo esc_html($row->lastname); ?></td>
                    <td><?php echo esc_html($row->email); ?></td>
                    <td><?php echo esc_html($row->phone); ?></td>
                    <td><?php echo esc_html(formatear_fecha_crud($row->uploaded_at)); ?></td>
                </tr>
            <?php endforeach; }?>
        </tbody>
    </table>
</div>