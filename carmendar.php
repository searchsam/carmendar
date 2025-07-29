<?php

/**
 * Plugin Name: Carmendar
 * Description: Muestra un calendario litúrgico con eventos personalizados.
 * Version: 1.0
 * Author: Samuel Gutierrez
 */

require_once plugin_dir_path(__FILE__) . "includes/events.php";
add_action("wp_ajax_carmendar_events", function () {
    header("Content-Type: application/json");

    $events = carmendar_get_events();
    echo json_encode($events);
    wp_die();
});

add_action("wp_enqueue_scripts", function () {
    wp_enqueue_script("fullcalendar-rrule", "https://cdn.jsdelivr.net/npm/rrule@2.7.1/dist/es5/rrule.min.js", [], null, true);
    wp_enqueue_script("fullcalendar-js", "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js", ["fullcalendar-rrule"], null, true);
    wp_enqueue_script("fullcalendar-rrule-plugin", "https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.18/index.global.min.js", ["fullcalendar-js"], null, true);
    wp_enqueue_script("custom-calendar", plugin_dir_url(__FILE__) . "js/calendar.js", ["fullcalendar-rrule-plugin"], null, true);

    wp_localize_script("custom-calendar", "carmendar_ajax", [
        "ajax_url" => admin_url("admin-ajax.php")
    ]);
});

add_shortcode("carmendar", function () {
    return '<div id="liturgical-calendar" style="heigth: auto;"></div>';
});
