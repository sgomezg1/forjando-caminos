<?php
    function fcv_send_email_to_registered_user($data, $hoja_de_vida, $tarjeta_profesional) {
        $to = 'voluntarios@forjandocaminos.co';
        $subject = 'Formulario de voluntarios';
        $body = "
            <h4>Hola! ".$data['name']." ".$data['lastname']." se ha registrado como voluntario, por favor, verifica los datos recibidos a continuacion:<h4>
            <p>Nombre: ".$data['name']."</p>
            <p>Apellido: ".$data['lastname']."</p>
            <p>Correo: ".$data['email']."</p>
            <p>Telefono: ".$data['phone']."</p>
            Puedes encontrar adjuntos los documentos subidos por el voluntario en este correo.
        ";
        $headers = array('Content-Type: text/html; charset=UTF-8', 'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>');
        
        $attachments = [$hoja_de_vida, $tarjeta_profesional]; // Agregar el archivo adjunto
    
        wp_mail($to, $subject, $body, $headers, $attachments);
    }
?>