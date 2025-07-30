<?php

require_once plugin_dir_path(__FILE__) . "simple_html_dom.php";

function scrap_title($url)
{
    $texts = [];
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $htmlContent = curl_exec($curl);

    if ($htmlContent === false) {
        echo "error";
        die();
        $error = curl_error($curl);
        echo "cURL error: " . $error;
        exit;
    }

    curl_close($curl);

    $html = str_get_html($htmlContent);
    $tags = $html->find("strong");

    foreach ($tags as $tag) {
        $texts[] = $tag->plaintext;
    }

    $html->clear();

    return $texts;
}

function spanish_month($date)
{
    $mouth = explode("-", $date)[1];

    return [
        "01" => "ene",
        "02" => "feb",
        "03" => "mar",
        "04" => "abr",
        "05" => "may",
        "06" => "jun",
        "07" => "jul",
        "08" => "ago",
        "09" => "sep",
        "10" => "oct",
        "11" => "nov",
        "12" => "dic"
    ][$mouth];
}

function generate_url($date)
{
    return "https://liturgiadelashoras.github.io/sync/" . $date->format("Y") . "/" . spanish_month($date->format("Y-m-d")) . "/" . $date->format("d") . "/";
}

function get_title($texts)
{
    // if (isset($texts[2])) {
    //     return ucwords(strtolower(trim($texts[2])), ".-/ ");
    // }

    $date = explode(" ", $texts[1]);
    $day = trim($date[0]);
    $week = trim(end($date));
    $title = ucwords(strtolower($day), ".-/ ") . " " . $week . " del " . ucwords(strtolower(trim($texts[0])), ".-/ ");

    return html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function get_color($event)
{
    $name = strtolower($event);
    $colors = [
        "ordinario" => "#008f39",
        "cuaresma" => "#572364",
        "pascua" => "#ffd700",
        "adviento" => "#572364",
        "navidad" => "#ffd700",
        "pedro y pablo" => "#b22222",
        "ramos" => "#b22222",
        "pentecost" => "#b22222",
    ];

    foreach ($colors as $key => $value) {
        if (str_contains($name, $key)) {
            return $value;
        }
    }
}

function get_event_format($name, $date, $url, $color)
{
    if (! isset($name[1])) {
        $name = explode("\n", $name[0]);
    }

    return [
        "title" => get_title($name),
        "start" => $date->format("Y-m-d"),
        "color" => $color ? $color : get_color($name[0]),
        "display" => "list-item", //isset($name[2]) ? "block" : "list-item",
        "url" => $url
    ];
}

function get_events()
{
    date_default_timezone_set("America/Managua");

    $events = [];
    $start = new DateTime("2025-01-01");
    $end = (clone $start)->modify("+1 Years");

    while ($start < $end) {
        $date = clone $start;
        $url = generate_url($date);
        $title_texts = scrap_title($url);
        $event = get_event_format($title_texts, $date, $url, null);

        if (str_contains(strtolower($event["title"]), "not found")) {
            $start->modify("+1 Days");
            continue;
        }

        $events[] = $event;

        // if (isset($title_texts[2])) {
        //     $secundary_event = get_event_format($title_texts, $date, $url, end($events)["color"]);

        //     if (str_contains(strtolower($event["title"]), strtolower($secundary_event["title"]))) {
        //         $start->modify("+1 Days");
        //         continue;
        //     }

        //     if (str_contains(strtolower($secundary_event["title"]), "not found")) {
        //         $start->modify("+1 Days");
        //         continue;
        //     }

        //     $events[] = $secundary_event;
        // }

        $start->modify("+1 Days");
    }

    // Write JSON to file
    header('Content-Type: text/html; charset=utf-8');
    $json = json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $file = plugin_dir_path(__FILE__) . "events.json";

    if (file_put_contents($file, $json) !== false) {
        echo "Data has been written to $file.";
    } else {
        echo "Error occurred while writing to $file.";
    }
}
