document.addEventListener("DOMContentLoaded", async function () {
  let calendarEl = document.getElementById("liturgical-calendar");

  if (!calendarEl) return;

  const response = await fetch(carmendar_events.rest_url_links);

  if (!response.ok) {
    const text = await response.text();
    console.error("Error en la respuesta:", text);
  }

  const links = await response.json();

  let calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    locale: "es",
    headerToolbar: {
      start: "prev",
      center: "title",
      end: "next"
    },
    dateClick: function (info) {
      const date = info.dateStr;
      window.location.assign(links[date]);
    },
    dayMaxEvents: true,
    height: "auto"
  });

  calendar.render();
});