<?php

/**
 * Plugin Name: Carmendar
 * Description: Muestra un calendario litúrgico con eventos personalizados.
 * Version: 1.0
 * Author: Samuel Gutierrez
 */

add_action("wp_enqueue_scripts", function () {
    wp_enqueue_script("fullcalendar-rrule", "https://cdn.jsdelivr.net/npm/rrule@2.7.1/dist/es5/rrule.min.js", [], null, true);
    wp_enqueue_script("fullcalendar-js", "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js", ["fullcalendar-rrule"], null, true);
    wp_enqueue_script("fullcalendar-rrule-plugin", "https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.18/index.global.min.js", ["fullcalendar-js"], null, true);
    wp_enqueue_script("custom-calendar", plugin_dir_url(__FILE__) . "js/calendar.js", ["fullcalendar-rrule-plugin"], null, true);
    wp_enqueue_style("fullcalendar-css", plugin_dir_url(__FILE__) . "css/calendar-style.css", [], "1.0", "all");
});

add_action("rest_api_init", function () {
    register_rest_route("carmendar/v1", "/events/", [
        "methods"  => "GET",
        "callback" => function () {
            $json_path = plugin_dir_path(__FILE__) . "includes/events.json";
            if (file_exists($json_path)) {
                $contenido = file_get_contents($json_path);
                return rest_ensure_response(json_decode($contenido));
            } else {
                return new WP_Error('no_encontrado', 'Archivo no encontrado', ['status' => 404]);
            }
        },
        'permission_callback' => '__return_true'
    ]);
});

register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled("calendar_events")) {
        wp_schedule_event(time(), "daily", "calendar_events");
    }
});

register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook("calendar_events");
});

add_action("calendar_events", function () {
    require_once plugin_dir_path(__FILE__) . "includes/events.php";
    get_events();
});

add_shortcode("carmendar", function () {
    return '<div id="liturgical-calendar" style="heigth: auto;"></div>';
});
