<?php
/**
 * Plugin Name: Forjando Caminos Voluntarios
 * Description: Plugin encargado de 
 * Version: 1.0
 * Author: Your Name
 */

require_once plugin_dir_path(__FILE__) . 'validations/validators.php';
require_once plugin_dir_path(__FILE__) . 'mail/send-mail.php';
require_once plugin_dir_path(__FILE__) . 'utils/utils.php';

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Enqueue scripts for AJAX
function forjando_caminos_voluntarios_enqueue_scripts() {
    wp_enqueue_script('forjando-caminos-voluntarios-form-script', plugin_dir_url(__FILE__) . 'js/form-submit.js', ['jquery'], null, true);
    wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);
    wp_localize_script('forjando-caminos-voluntarios-form-script', 'wp_ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
}
add_action('wp_enqueue_scripts', 'forjando_caminos_voluntarios_enqueue_scripts');

// Create database table on activation
function fcv_create_voluntarios_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "forjando_caminos_voluntarios";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        lastname VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(255) NOT NULL,
        hoja_de_vida_url VARCHAR(255) NOT NULL,
        tarjeta_profesional_url VARCHAR(255) NOT NULL,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'fcv_create_voluntarios_table');

function fcv_create_donantes_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "forjando_caminos_donantes";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        lastname VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(255) NOT NULL,
        aportes_economicos VARCHAR(255),
        aportes_elementos VARCHAR(255),
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'fcv_create_donantes_table');

// Add menu to admin panel
function fcv_voluntarios_admin_menu() {
    add_menu_page(
        'Formulario voluntarios',
        'Voluntarios',
        'manage_options',
        'forjando-caminos-voluntarios',
        'fcv_display_voluntarios_page',
        'dashicons-smiley',
        25
    );
}
add_action('admin_menu', 'fcv_voluntarios_admin_menu');

function fcv_donantes_admin_menu() {
    add_menu_page(
        'Formulario donantes',
        'Donantes',
        'manage_options',
        'forjando-caminos-donantes',
        'fcv_display_donantes_page',
        'dashicons-smiley',
        25
    );
}
add_action('admin_menu', 'fcv_donantes_admin_menu');

// Display admin page
function fcv_display_voluntarios_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "forjando_caminos_voluntarios";
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY uploaded_at DESC");
    require_once plugin_dir_path(__FILE__) . 'views/voluntarios/admin-voluntarios.php';
}

function fcv_display_donantes_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "forjando_caminos_donantes";
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY uploaded_at DESC");
    require_once plugin_dir_path(__FILE__) . 'views/donantes/admin-donantes.php';
}

function fcv_voluntarios_form() {
    ob_start();
    require_once plugin_dir_path(__FILE__) . 'views/voluntarios/formulario-voluntarios.php';
    return ob_get_clean();
}
add_shortcode('fcv_voluntarios', 'fcv_voluntarios_form');

function fcv_donantes_form() {
    ob_start();
    require_once plugin_dir_path(__FILE__) . 'views/donantes/formulario-donantes.php';
    return ob_get_clean();
}
add_shortcode('fcv_donantes', 'fcv_donantes_form');

// Create new register for voluntarios

function fcv_handle_voluntarios_register() {
    nonce_validation($_POST);
    $errorInFields = validate_voluntarios_form($_SERVER["REQUEST_METHOD"], $_POST);
    process_errors($errorInFields);

    if (empty($_FILES['hoja_de_vida']['name']) && empty($_FILES['tarjeta_profesional']['name'])) {
        wp_send_json([
            'success' => false,
            'message' => 'Debes subir los documentos para registrarte correctamente'
        ], 400);
        exit;
    }

    $hoja_de_vida_upload_url = wp_handle_upload($_FILES['hoja_de_vida'], ['test_form' => false]);
    $tarjeta_profesional_upload_url = wp_handle_upload($_FILES['tarjeta_profesional'], ['test_form' => false]);
    if (!isset($hoja_de_vida_upload_url['url']) && !isset($tarjeta_profesional_upload_url['url'])) {
        wp_send_json([
            'success' => false,
            'message' => 'Error al subir los documentos'
        ], 400);
        exit;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . "forjando_caminos_voluntarios";
    $wpdb->insert($table_name, [
        'name' => sanitize_text_field($_POST['name']),
        'lastname' => sanitize_text_field($_POST['lastname']),
        'email' => sanitize_text_field($_POST['email']),
        'phone' => sanitize_text_field($_POST['phone']),
        'hoja_de_vida_url' => esc_url($hoja_de_vida_upload_url['url']),
        'tarjeta_profesional_url' => esc_url($tarjeta_profesional_upload_url['url']),
    ]);
    fcv_send_email_to_registered_user($_POST, $hoja_de_vida_upload_url['file'], $tarjeta_profesional_upload_url['file']);
    wp_send_json([
        'success' => true,
        'message' => 'Voluntario registrado correctamente.'
    ], 200);
    exit;
}

add_action('wp_ajax_fcv_handle_voluntarios_register', 'fcv_handle_voluntarios_register');
add_action('wp_ajax_nopriv_fcv_handle_voluntarios_register', 'fcv_handle_voluntarios_register');

// Crear nuevo registro para donantes

function fcv_handle_donantes_register() {
    nonce_validation($_POST);
    $errorInFields = validate_voluntarios_form($_SERVER["REQUEST_METHOD"], $_POST);
    process_errors($errorInFields);
    global $wpdb;
    $table_name = $wpdb->prefix . "forjando_caminos_donantes";
    $wpdb->insert($table_name, [
        'name' => sanitize_text_field($_POST['name']),
        'lastname' => sanitize_text_field($_POST['lastname']),
        'email' => sanitize_text_field($_POST['email']),
        'phone' => sanitize_text_field($_POST['phone']),
        'aportes_economicos' => sanitize_text_field($_POST['aportes_economicos']),
        'aportes_elementos' => sanitize_text_field($_POST['aportes_elementos'])
    ]);
    wp_send_json([
        'success' => true,
        'message' => 'Donacion registrada correctamente.'
    ], 200);
    exit;
}

add_action('wp_ajax_fcv_handle_donantes_register', 'fcv_handle_donantes_register');
add_action('wp_ajax_nopriv_fcv_handle_donantes_register', 'fcv_handle_donantes_register');