<?php
    function process_errors($errorInFields) {
        if(!empty($errorInFields)) {
            $errors = array();
            foreach($errorInFields as $error) {
                array_push($errors, $error);
            }
            wp_send_json([
                'success' => false,
                'errors' => $errors
            ], 422);
            exit;
        }
    }

    function formatear_fecha_crud($fecha) {
        $meses = [
            'January' => 'enero', 'February' => 'febrero', 'March' => 'marzo',
            'April' => 'abril', 'May' => 'mayo', 'June' => 'junio',
            'July' => 'julio', 'August' => 'agosto', 'September' => 'septiembre',
            'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'
        ];
    
        $date = new DateTime($fecha);
        $mes = $meses[$date->format('F')];
        $fecha_formateada = $date->format('d') . " de $mes de " . $date->format('Y') . " - " . $date->format("g:i a");
        return $fecha_formateada;
    }
?>