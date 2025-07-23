<?php

/**
 * Plugin Name: Carmendar
 * Description: Muestra un calendario litúrgico con eventos personalizados.
 * Version: 1.0
 * Author: Samuel Gutierrez
 */

if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles
function carmendar_enqueue_assets()
{
    wp_enqueue_script('fcw-rrule', 'https://cdn.jsdelivr.net/npm/rrule@2.7.1/dist/es5/rrule.min.js', [], null, true);
    wp_enqueue_script('fcw-fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', ['fcw-rrule'], null, true);
    wp_enqueue_script('fcw-rrule-plugin', 'https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.10/index.global.min.js', ['fcw-fullcalendar'], null, true);
    wp_enqueue_script('fcw-init', plugins_url('/js/fullcalendar-init.js', __FILE__), ['fcw-rrule-plugin'], null, true);

    wp_localize_script('fcw-init', 'carmendar_ajax_events', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'carmendar_enqueue_assets');

function carmendar_shortcode()
{
    return '<div id="fcw-calendar"></div>';
}
add_shortcode('carmendar', 'carmendar_shortcode');


require_once plugin_dir_path(__FILE__) . 'includes/events.php';
function carmendar_ajax_events()
{
    header('Content-Type: application/json');

    $events = carmendar_get_events();
    echo json_encode($events);
    wp_die();
}
add_action('wp_ajax_fc_events', 'carmendar_ajax_events');
add_action('wp_ajax_nopriv_fc_events', 'carmendar_ajax_events');

function carmendar_record_events()
{
    register_post_type('fc_event', [
        'labels' => [
            'name' => 'Eventos',
            'singular_name' => 'Evento',
        ],
        'public' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-calendar',
        'supports' => ['title'],
        'has_archive' => false,
        'show_in_rest' => true
    ]);
}
add_action('init', 'carmendar_record_events');

function carmendar_add_event_fields()
{
    add_meta_box('carmendar_event_field', 'Detalles del evento', 'carmendar_event_fields_html', 'fc_event', 'normal', 'default');
}
add_action('add_meta_boxes', 'carmendar_add_event_fields');

function carmendar_event_fields_html($post)
{
    $date = get_post_meta($post->ID, '_fcw_date', true);
    $url = get_post_meta($post->ID, '_fcw_url', true);
?>
    <label>Fecha:</label><br>
    <input type="date" name="fcw_date" value="<?php echo esc_attr($date); ?>"><br><br>

    <label>URL:</label><br>
    <input type="text" name="fcw_url" value="<?php echo esc_attr($url); ?>"><br><br>
<?php
}

function carmendar_save_event_fields($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['fcw_title'])) update_post_meta($post_id, '_fcw_title', sanitize_text_field($_POST['fcw_title']));
    if (isset($_POST['fcw_date'])) update_post_meta($post_id, '_fcw_date', sanitize_text_field($_POST['fcw_date']));
    if (isset($_POST['fcw_url'])) update_post_meta($post_id, '_fcw_url', esc_url_raw($_POST['fcw_url']));
}
add_action('save_post', 'carmendar_save_event_fields');
