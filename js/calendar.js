document.addEventListener('DOMContentLoaded', function () {
  var calendarEl = document.getElementById('liturgical-calendar');
  if (!calendarEl) return;

  let calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    themeSystem: 'bootstrap5',
    locale: 'es',
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
    events: carmendar_ajax.ajax_url + '?action=carmendar_events',
    height: 'auto'
  });
  calendar.render();
});