<?php

// Endpoint AJAX para eventos
function fcw_get_events()
{
    $events = [];
    $query = new WP_Query([
        'post_type' => 'fc_event',
        'posts_per_page' => -1
    ]);

    while ($query->have_posts()) {
        $query->the_post();
        $title = get_post_meta(get_the_ID(), '_fcw_title', true);
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
                'dtstart' => '2000-' . date('m-d', strtotime($date)) // only month and day
            ];
        }

        $events[] = $event;
    }

    wp_reset_postdata();
    return $events;
}
