document.addEventListener("DOMContentLoaded", function () {
  var calendarEl = document.getElementById("liturgical-calendar");
  if (!calendarEl) return;

  let calendar = new FullCalendar.Calendar(calendarEl, {
    navLinks: false,
    initialView: "dayGridMonth",
    locale: "es",
    headerToolbar: {
      start: "prev",
      center: "title",
      end: "next"
    },
    dayMaxEvents: true,
    events: carmendar_events.rest_url + "?action=carmendar_events",
    height: "auto"
  });

  calendar.render();
});