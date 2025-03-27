<?php
    function validate_voluntarios_form($serverRequestType, $serverRequest) {
        if ($serverRequestType == "POST") {
            $errors = [];
            if (empty($serverRequest['name'])) {
                $errors[] = "Name is required.";
            } elseif (!preg_match("/^[a-zA-Z ]+$/", $serverRequest['name'])) {
                $errors[] = "El nombre solo puede contener letras y espacios.";
            }
        
            if (empty($serverRequest['lastname'])) {
                $errors[] = "Lastname is required.";
            } elseif (!preg_match("/^[a-zA-Z ]+$/", $serverRequest['lastname'])) {
                $errors[] = "El apellido solo puede contener letras y espacios.";
            }
        
            if (empty($serverRequest['email'])) {
                $errors[] = "Email is required.";
            } elseif (!filter_var($serverRequest['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Correo invalido.";
            }
        
            if (empty($serverRequest['phone'])) {
                $errors[] = "Phone number is required.";
            } elseif (!preg_match("/^[0-9]{6,15}$/", $serverRequest['phone'])) {
                $errors[] = "El telefono debe tener entre 6 y 10 digitos.";
            }
            return $errors;
        } else {
            return wp_send_json([
                'success' => false,
                'message' => "Tipo de peticion invalido"
            ], 422);
        }
    }

    function nonce_validation($data) {
        if (!isset($data['wp_nonce']) || !wp_verify_nonce($data['wp_nonce'], 'fcv_upload')) {
            wp_send_json([
                'success' => false,
                'message' => 'Verificacion de seguridad fallido.'
            ], 400);
            exit;
        }
    }
?>