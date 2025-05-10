<?php
/*
Plugin Name: FullCalendar WP
Description: Calendario FullCalendar con eventos dinámicos desde AJAX y shortcode.
Version: 1.0
Author: Samuel Gutierrez
*/

if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles
function fcw_enqueue_assets()
{
    wp_enqueue_script('fcw-rrule', 'https://cdn.jsdelivr.net/npm/rrule@2.7.1/dist/es5/rrule.min.js', [], null, true);
    wp_enqueue_script('fcw-fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', ['fcw-rrule'], null, true);
    wp_enqueue_script('fcw-rrule-plugin', 'https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.10/index.global.min.js', ['fcw-fullcalendar'], null, true);
    wp_enqueue_script('fcw-init', plugins_url('/js/fullcalendar-init.js', __FILE__), ['fcw-rrule-plugin'], null, true);

    wp_localize_script('fcw-init', 'fcw_ajax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'fcw_enqueue_assets');

// Shortcode [fullcalendar]
function fcw_calendar_shortcode()
{
    return '<div id="calendar"></div>';
}
add_shortcode('fullcalendar', 'fcw_calendar_shortcode');

require_once plugin_dir_path(__FILE__) . 'includes/events.php';

// Endpoint AJAX para eventos
function fcw_ajax_events()
{
    header('Content-Type: application/json');

    $eventos = fcw_get_events();
    echo json_encode($eventos);
    wp_die();
}
add_action('wp_ajax_fc_events', 'fcw_ajax_events');
add_action('wp_ajax_nopriv_fc_events', 'fcw_ajax_events');

function fcw_record_cpt_events()
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
add_action('init', 'fcw_record_cpt_events');

function fcw_add_event_fields()
{
    add_meta_box('fcw_event_field', 'Detalles del evento', 'fcw_event_fields_html', 'fc_event', 'normal', 'default');
}
add_action('add_meta_boxes', 'fcw_add_event_fields');

function fcw_event_fields_html($post)
{
    $title = get_post_meta($post->ID, '_fcw_title', true);
    $date = get_post_meta($post->ID, '_fcw_date', true);
    $url = get_post_meta($post->ID, '_fcw_url', true);
?>
    <label>Nombre:</label><br>
    <input type="text" name="fcw_title" value="<?php echo esc_attr($title); ?>"><br><br>

    <label>Fecha:</label><br>
    <input type="date" name="fcw_date" value="<?php echo esc_attr($date); ?>"><br><br>

    <label>URL:</label><br>
    <input type="text" name="fcw_url" value="<?php echo esc_attr($url); ?>"><br><br>
<?php
}

function fcw_save_event_fields($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['fcw_title'])) update_post_meta($post_id, '_fcw_title', sanitize_text_field($_POST['fcw_title']));
    if (isset($_POST['fcw_date'])) update_post_meta($post_id, '_fcw_date', sanitize_text_field($_POST['fcw_date']));
    if (isset($_POST['fcw_url'])) update_post_meta($post_id, '_fcw_url', esc_url_raw($_POST['fcw_url']));
}
add_action('save_post', 'fcw_save_event_fields');
