document.addEventListener('DOMContentLoaded', function () {
  let calendarEl = document.getElementById('fcw-calendar');
  if (!calendarEl) return;

  let events = carmendar_ajax_events.ajax_url + '?action=fc_events';
  console.log(events);
  let calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'es',
    themeSystem: 'bootstrap5',
    // height: '100%',
    headerToolbar: {
      start: 'prev',
      center: 'title',
      end: 'next'
    },
    footerToolbar: {
      start: 'prev',
      center: '',
      end: 'next'
    },
    initialView: 'dayGridMonth',
    events: events,
    eventClick: function (info) {
      if (info.event.url) {
        window.location.href = info.event.url;
        // window.open(info.event.url, '_blank');
        info.jsEvent.preventDefault();
      }
    }
  });

  calendar.render();
});
