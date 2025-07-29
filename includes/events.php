<?php

require_once plugin_dir_path(__FILE__) . "utils.php";

function carmendar_get_events()
{
    $events = [];
    $year = date("Y");

    $events = array_merge($events, generate_liturgical_calendar($year));

    return $events;
}
