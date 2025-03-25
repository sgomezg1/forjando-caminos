<?php
/**
 * Forjando Caminos Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Forjando Caminos
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_FORJANDO_CAMINOS_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'forjando-caminos-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_FORJANDO_CAMINOS_VERSION, 'all' );

}

function custom_javascript() {
    wp_enqueue_script(
        'main',
        get_stylesheet_directory_uri() . '/js/main.js',
        array('jquery'),
        '1.0.0',
        false
    );
}

function nuestra_labor_template() {
    require_once get_stylesheet_directory().'/templates/nuestra_labor.php';
}

function nuestro_territorio_template() {
    require_once get_stylesheet_directory().'/templates/presencia_territorio.php';
}

function footer_template() {
    require_once get_stylesheet_directory().'/templates/footer_template.php';
}

function faq_template() {
    require_once get_stylesheet_directory().'/templates/faq_template.php';
}

add_filter( 'astra_tablet_breakpoint', function() {
    return 1024;
});
add_shortcode('nuestra_labor', 'nuestra_labor_template');
add_shortcode('nuestro_territorio', 'nuestro_territorio_template');
add_shortcode('footer', 'footer_template');
add_shortcode('faq', 'faq_template');
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );
add_action('wp_enqueue_scripts', 'custom_javascript');