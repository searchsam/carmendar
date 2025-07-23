<?php

function carmendar_get_first_advent_sunday($year)
{
    $christmas = new DateTime("$year-12-25");
    $interval = new DateInterval('P28D'); // 4 semanas
    $start = $christmas->sub($interval);
    $start->modify('next sunday');
    return $start->format('Y-m-d');
}

function carmendar_get_liturgical_cycle($year = null)
{
    $year = $year ?: date('Y');
    $sunday_cycle = ['A', 'B', 'C'];
    $cycle_festive = ['I', 'II'];

    $first_advent_sunday = carmendar_get_first_advent_sunday($year);
    $before_advent = (strtotime("today") < strtotime($first_advent_sunday));

    $sunday_cycle = $sunday_cycle[($year - 2023 + ($before_advent ? -1 : 0)) % 3];
    $cycle_festive = $sunday_cycle[($year - 2024 + ($before_advent ? -1 : 0)) % 2];

    return [
        'sunday' => $sunday_cycle,
        'festive' => $cycle_festive
    ];
}

function carmendar_name_liturgical_day(DateTime $date)
{
    $week_day = $date->format('N');
    $ordinary_weeks = floor((int)$date->format('z') / 7);

    return match ($week_day) {
        1 => "Lunes de la semana $ordinary_weeks del Tiempo Ordinario",
        2 => "Martes de la semana $ordinary_weeks del Tiempo Ordinario",
        3 => "Miércoles de la semana $ordinary_weeks del Tiempo Ordinario",
        4 => "Jueves de la semana $ordinary_weeks del Tiempo Ordinario",
        5 => "Viernes de la semana $ordinary_weeks del Tiempo Ordinario",
        6 => "Sábado de la semana $ordinary_weeks del Tiempo Ordinario",
        7 => "Domingo de la semana $ordinary_weeks del Tiempo Ordinario",
    };
}

function carmendar_complete_events($year)
{
    $events = [];
    $start = new DateTime("$year-01-01");
    $end = new DateTime("$year-12-31");
    $cycle = carmendar_get_liturgical_cycle($year);

    while ($start <= $end) {
        $date = $start->format('Y-m-d');
        $liturgical_name = carmendar_name_liturgical_day($start);
        $its_sunday = $start->format('w') == 0;

        $events[] = [
            'title' => $liturgical_name . ' (' . ($its_sunday ? 'Ciclo ' . $cycle['sunday'] : 'Ciclo ' . $cycle['festive']) . ')',
            // 'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/01/',
            'allDay' => true,
            'start' => $date,
            'end' => $date
            // 'display' => 'auto'
        ];

        $start->modify('+1 day');
    }

    return $events;
}

function carmendar_fixed_events($year)
{
    return [
        [
            'title' => 'Santa María, Madre de Dios',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/01/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-01-01']
        ],
        [
            'title' => 'Epifanía',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/06/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-01-06']
        ],
        [
            'title' => 'El Bautismo del Señor',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/21/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-01-12']
        ],
        [
            'title' => 'San José',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/enemar/19/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-03-19']
        ],
        [
            'title' => 'Anunciación del Señor',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/25/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-03-25']
        ],
        [
            'title' => 'Natividad de San Juan Bautista',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/jun/24/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-06-24']
        ],
        [
            'title' => 'San Pedro y San Pablo',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/jun/29/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-06-29']
        ],
        [
            'title' => 'Asunción de la Virgen María',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ago/15/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-08-15']
        ],
        [
            'title' => 'Todos los Santos',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/nov/01/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-11-01']
        ],
        [
            'title' => 'Inmaculada Concepción de María',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/dic/08/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-12-08']
        ],
        [
            'title' => 'Navidad del Señor',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/dic/25/',
            'allDay' => true,
            'rrule' => ['freq' => 'yearly', 'dtstart' => '2000-12-25']
        ],
    ];
}

function carmendar_mobile_events($year)
{
    $easter = date("Y-m-d", easter_date($year));
    $lent = date("Y-m-d", strtotime("$easter -46 days"));
    $ascension = date("Y-m-d", strtotime("$easter +39 days"));
    $pentecost = date("Y-m-d", strtotime("$easter +49 days"));

    return [
        [
            'title' => 'Miércoles de Ceniza',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/01/',
            'allDay' => true,
            'start' => $lent,
            'color' => '#6a1b9a'
        ],
        [
            'title' => 'Domingo de Pascua',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/01/',
            'allDay' => true,
            'start' => $easter,
            'color' => '#ffd700'
        ],
        [
            'title' => 'Ascensión del Señor',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/01/',
            'allDay' => true,
            'start' => $ascension,
            'color' => '#1565c0'
        ],
        [
            'title' => 'Pentecostés',
            'url' => 'https://liturgiadelashoras.github.io/sync/' . $year . '/ene/01/',
            'allDay' => true,
            'start' => $pentecost,
            'color' => '#f44336'
        ]
    ];
}

function carmendar_get_events()
{
    $year = date('Y');
    $events = [];

    // $events = array_merge($events, carmendar_complete_events($year));
    // $events = array_merge($events, carmendar_fixed_events($year));
    $events = array_merge($events, carmendar_mobile_events($year));

    var_dump($events);
    die();

    // Eventos personalizados del usuario (CPT)
    $query = new WP_Query([
        'post_type' => 'fc_event',
        'posts_per_page' => -1
    ]);

    while ($query->have_posts()) {
        $query->the_post();
        $date = get_post_meta(get_the_ID(), '_fcw_date', true);
        $url = get_post_meta(get_the_ID(), '_fcw_url', true);

        $event = [
            'title' => get_the_title(),
            'url'   => $url ?: null,
            'allDay' => true
        ];

        if ($date) {
            $event['rrule'] = [
                'freq' => 'yearly',
                'dtstart' => '2000-' . date('m-d', strtotime($date))
            ];
        }

        $events[] = $event;
    }

    wp_reset_postdata();
    return $events;
}
