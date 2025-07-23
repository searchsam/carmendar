<?php
/*
Plugin Name: Carmandar
Description: Muestra un calendario litúrgico con eventos personalizados.
Version: 1.0
Author: Samuel Gutierrez
*/

add_action('init', function () {
    register_post_type('liturgical_event', [
        'labels' => [
            'name' => 'Eventos Litúrgicos',
            'singular_name' => 'Evento Litúrgico',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'carmendar'],
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);
});

add_action('add_meta_boxes', function () {
    add_meta_box('info_evento', 'Información Litúrgica', function ($post) {
        $fecha = get_post_meta($post->ID, 'event_date', true);
        echo '<label>Fecha del evento:</label><br>';
        echo '<input type="date" name="event_date" value="' . esc_attr($fecha) . '" style="width:100%;">';
    }, 'liturgical_event');
});

add_action('save_post', function ($post_id) {
    if (array_key_exists('event_date', $_POST)) {
        update_post_meta($post_id, 'event_date', $_POST['event_date']);
    }
});

require_once plugin_dir_path(__FILE__) . 'includes/events.php';
add_action('wp_ajax_carmandar_events', function () {
    header('Content-Type: application/json');

    $eventos = carmendar_get_events();
    echo json_encode($eventos);
    wp_die();
});

add_action('wp_ajax_nopriv_carmendar_events', function () {
    header('Content-Type: application/json');

    $eventos = carmendar_get_events();
    echo json_encode($eventos);
    wp_die();
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js', [], null, true);
    wp_enqueue_script('custom-calendar', plugin_dir_url(__FILE__) . 'js/calendar.js', ['fullcalendar-js'], null, true);

    wp_localize_script('custom-calendar', 'carmendar_ajax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
});

add_shortcode('carmendar', function () {
    return '<div id="liturgical-calendar" style="max-width: 900px; margin: 0 auto;"></div>';
});
