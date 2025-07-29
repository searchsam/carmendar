<?php

require_once plugin_dir_path(__FILE__) . "fixed_days.php";

function spanishMonth($date)
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
        "12" => "dic",
    ][$mouth];
}

function spanishWeekDay($day)
{
    return [
        "Domingo",
        "Lunes",
        "Martes",
        "Miercoles",
        "Jueves",
        "Viernes",
        "Sabado"
    ][$day];
}

function get_event_format($name, $date, $color)
{
    return [
        "title" => $name,
        "start" => $date->format("Y-m-d"),
        "color" => $color,
        "display" => "list-item",
        "url" => "https://liturgiadelashoras.github.io/sync/" . $date->format("Y") . "/" . spanishMonth($date->format("Y-m-d")) . "/" . $date->format("d") . "/"
    ];
}

function to_roman($num)
{
    $map = [
        "M" => 1000,
        "CM" => 900,
        "D" => 500,
        "CD" => 400,
        "C" => 100,
        "XC" => 90,
        "L" => 50,
        "XL" => 40,
        "X" => 10,
        "IX" => 9,
        "V" => 5,
        "IV" => 4,
        "I" => 1
    ];
    $ret = "";

    foreach ($map as $roman => $int) {
        while ($num >= $int) {
            $ret .= $roman;
            $num -= $int;
        }
    }

    return $ret;
}

function generate_liturgical_calendar($year)
{
    $calendar = [];

    // Año anterior
    $prevYear = $year - 1;

    // Fechas clave
    $easter = new DateTimeImmutable("@" . easter_date($year));
    $easter = $easter->setTimezone(new DateTimeZone(date_default_timezone_get()));
    $ash_wednesday = $easter->sub(new DateInterval("P46D"));
    $palm_sunday = $easter->sub(new DateInterval("P7D"));
    $pentecost = $easter->add(new DateInterval("P49D"));
    $holy_trinity = $pentecost->add(new DateInterval("P7D"));
    $corpus_christi = $holy_trinity->add(new DateInterval("P4D"));
    $christ_king = (clone (new DateTime($year . "-12-25"))->modify("last sunday -3 weeks"))->modify("-7 days");
    $advent_start = (new DateTime($prevYear . "-12-25"))->modify("last sunday -3 weeks");
    $advent_end = (new DateTime($year . "-12-25"))->modify("last sunday -3 weeks");
    $baptism_lord = (new DateTime($year . "-01-06"))->modify("next sunday");

    // $fixed_solemnities = [
    //     "01-01" => [
    //         "Santa María Madre de Dios",
    //         "#ffd700"
    //     ],
    //     "01-06" => [
    //         "Epifanía del Señor",
    //         "#ffd700"
    //     ],
    //     $baptism_lord->format("m-d") => [
    //         "Bautismo del Senor",
    //         "#ffd700"
    //     ],
    //     "03-19" => [
    //         "San José",
    //         "#ffd700"
    //     ],
    //     "03-25" => [
    //         "Anunciación del Señor",
    //         "#ffd700"
    //     ],
    //     "06-24" => [
    //         "Natividad de San Juan Bautista",
    //         "#ffd700"
    //     ],
    //     "06-29" => [
    //         "San Pedro y San Pablo",
    //         "#b22222"
    //     ],
    //     "08-15" => [
    //         "Asunción de la Virgen María",
    //         "#ffd700"
    //     ],
    //     "11-01" => [
    //         "Todos los Santos",
    //         "#ffd700"
    //     ],
    //     "12-08" => [
    //         "Inmaculada Concepción",
    //         "#ffd700"
    //     ],
    //     "12-25" => [
    //         "Navidad del Señor",
    //         "#ffd700"
    //     ]
    // ];

    // Inicialización de contadores por tiempo
    $advent_weeks = 0;
    $christmas_weeks = 0;
    $lent_weeks = 0;
    $easter_weeks = 0;
    $ordinary_weeks = 1;
    $holly_weeks = 1;


    // Rango del calendario: de Adviento anterior hasta Adviento siguiente
    $start = clone $advent_start;
    $end = (clone $baptism_lord)->modify("+1 Years");

    while ($start < $end) {
        $date = $start->format("Y-m-d");
        $week_day = $start->format("w"); // 0 = domingo
        $time = "";
        $color = "#008f39";
        $name = null;

        // Determinar tiempo litúrgico y semana
        if (!$time) {
            if ($start >= $advent_start && $start < new DateTime($year . "-12-25")) {
                if ($start < new DateTime($year . "-01-01")) {
                    if ($start < new DateTime($prevYear . "-12-25")) {
                        $color = "#572364";
                        $advent_weeks += ($week_day == 0 || $advent_weeks == 0) ? 1 : 0;
                        $name = spanishWeekDay($week_day) . " " . to_roman($advent_weeks) . " del Adviento";
                    } else {
                        $time = "Tiempo de Navidad";
                        $color = "#ffd700";
                        $christmas_weeks += ($week_day == 0 || $christmas_weeks == 0) ? 1 : 0;
                        $name = spanishWeekDay($week_day) . " " . to_roman($christmas_weeks) . " de Navidad";
                    }
                } else {
                    if ($start < (clone $baptism_lord)->modify("+1 Weeks")) {
                        $color = "#ffd700";
                        $christmas_weeks += ($week_day == 0 || $christmas_weeks == 0) ? 1 : 0;
                        $name = spanishWeekDay($week_day) . " " . to_roman($christmas_weeks) . " de Navidad";
                    } elseif ($start < $ash_wednesday) {
                        $name = spanishWeekDay($week_day) . " " . to_roman($ordinary_weeks) . " del Tiempo Ordinario";
                        if ($week_day == 0) $ordinary_weeks++;
                    } elseif ($start < $palm_sunday) {
                        $color = "#572364";
                        $lent_weeks += ($week_day == 0 || $lent_weeks == 0) ? 1 : 0;
                        $name = spanishWeekDay($week_day) . " " . to_roman($lent_weeks) . " de Cuaresma";
                    } elseif ($start >= $palm_sunday && $start < $easter) {
                        $color = "#572364";
                        $holly_weeks += ($week_day == 0 || $holly_weeks == 0) ? 1 : 0;
                        $name = spanishWeekDay($week_day) . " Santo";
                    } elseif ($start <= (clone $pentecost)->modify("+1 Weeks")) {
                        $color = "#ffd700";
                        $easter_weeks += ($week_day == 0 || $easter_weeks == 0) ? 1 : 0;
                        $name = spanishWeekDay($week_day) . " " . to_roman($easter_weeks) . " de Pascua";
                    } elseif ($start < $advent_end) {
                        $name = spanishWeekDay($week_day) . " " . to_roman($ordinary_weeks) . " del Tiempo Ordinario";
                        if ($week_day == 0) $ordinary_weeks++;
                    } else {
                        $color = "#572364";
                        $advent_weeks += ($week_day == 0 || $advent_weeks == 0) ? 1 : 0;
                        $name = spanishWeekDay($week_day) . " " . to_roman($advent_weeks) . " de Adviento";
                    }
                }
            }
        }

        // Solemnidades fijas
        $fixed_key = $start->format("m-d");
        if (isset($fixed_solemnities[$fixed_key])) {
            [$name, $color] = $fixed_solemnities[$fixed_key];
        }

        // Solemnidades móviles
        if ($date === $easter->format("Y-m-d")) {
            $name = "Pascua de Resurrexion";
            $color = "#ffd700";
            $easter_weeks = 1;
        } elseif ($date === $ash_wednesday->format("Y-m-d")) {
            $name = "Miércoles de Ceniza";
            $color = "#572364";
            $lent_weeks = 1;
        } elseif ($date === $palm_sunday->format("Y-m-d")) {
            $name = "Domingo de Ramos";
            $color = "#b22222";
            $holly_weeks = 1;
        } elseif ($date === $pentecost->format("Y-m-d")) {
            $name = "Domingo de Pentecostés";
            $color = "#b22222";
            $easter_weeks = 7;
        } elseif ($date === $holy_trinity->format("Y-m-d")) {
            $name = "Santísima Trinidad";
            $color = "#ffd700";
        } elseif ($date === $corpus_christi->format("Y-m-d")) {
            $name = "Cuerpo y la Sangre de Cristo";
            $color = "#ffd700";
        } elseif ($date === $christ_king->format("Y-m-d")) {
            $name = "Jesucristo Rey del Universo";
            $color = "#ffd700";
        }

        $calendar[] = get_event_format($name, $start, $color);

        $start->modify("+1 day");
    }

    return $calendar;
}
