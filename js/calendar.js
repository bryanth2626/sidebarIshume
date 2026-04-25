// ════════════════════════════════════════════════════════════════════════════════
// CALENDAR.JS - Calendario interactivo con eventos
// ════════════════════════════════════════════════════════════════════════════════

(function () {
  // ── Selectores del DOM ──────────────────────────────────
  const dateEl        = document.querySelector(".cal-date");
  const daysContainer = document.querySelector(".cal-days");
  const prevBtn       = document.querySelector(".cal-prev");
  const nextBtn       = document.querySelector(".cal-next");
  const todayBtn      = document.querySelector(".cal-today-btn");
  const gotoBtn       = document.querySelector(".cal-goto-btn");
  const dateInput     = document.querySelector(".cal-date-input");
  const eventDayEl    = document.querySelector(".event-day");
  const eventDateEl   = document.querySelector(".event-date");
  const eventsEl      = document.querySelector(".cal-events");

  if (!daysContainer || !eventsEl) {
    console.warn("Calendar.js: No se encontraron los elementos del calendario.");
    return;
  }

  const today  = new Date();
  let month    = today.getMonth();
  let year     = today.getFullYear();
  let activeDay = today.getDate();

  const MESES = [
    "Enero","Febrero","Marzo","Abril","Mayo","Junio",
    "Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
  ];

  const DIAS = [
    "Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"
  ];

  let eventsArr = [];

  // ── Cargar eventos desde PHP ────────────────────────────
  if (typeof eventosDB !== "undefined" && Array.isArray(eventosDB) && eventosDB.length > 0) {
    const grouped = {};

    eventosDB.forEach(function (ev) {
      const key = ev.day + "-" + ev.month + "-" + ev.year;
      if (!grouped[key]) {
        grouped[key] = {
          day:    ev.day,
          month:  ev.month,   // 1-12
          year:   ev.year,
          events: []
        };
      }
      grouped[key].events.push({
        id:       ev.id       || 0,
        title:    ev.title    || "",
        time:     ev.time     || "",
        lugar:    ev.lugar    || "",
        cliente:  ev.cliente  || "",
        personal: ev.personal || "",
        servicio: ev.servicio || ""
      });
    });

    eventsArr = Object.values(grouped);
  }

  // ── Funciones auxiliares ────────────────────────────────
  /**
   * Verificar si hay eventos en un día específico
   */
  function hasEvents(d, m, y) {
    return eventsArr.some(function (obj) {
      return obj.day === d && obj.month === m && obj.year === y;
    });
  }

  /**
   * Obtener eventos de un día específico
   */
  function getEvents(d, m, y) {
    const found = eventsArr.find(function (obj) {
      return obj.day === d && obj.month === m && obj.year === y;
    });
    return found ? found.events : [];
  }

  /**
   * Escapar caracteres HTML para prevenir XSS
   */
  function escHtml(str) {
    if (!str) return "";
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // ── Renderizar calendario ────────────────────────────────
  function renderCalendar() {
    const firstDay   = new Date(year, month, 1).getDay();
    const lastDate   = new Date(year, month + 1, 0).getDate();
    const prevLast   = new Date(year, month, 0).getDate();
    const nextDays   = 7 - new Date(year, month + 1, 0).getDay() - 1;

    if (dateEl) dateEl.textContent = MESES[month] + " " + year;

    let html = "";

    // Días del mes anterior (grises)
    for (let x = firstDay; x > 0; x--) {
      html += '<div class="day prev-date">' + (prevLast - x + 1) + "</div>";
    }

    // Días del mes actual
    for (let i = 1; i <= lastDate; i++) {
      const isToday  = i === today.getDate() && month === today.getMonth() && year === today.getFullYear();
      const isActive = i === activeDay && month === today.getMonth() && year === today.getFullYear();
      const hasEv    = hasEvents(i, month + 1, year);

      let classes = "day";
      if (isToday)  classes += " today";
      if (isActive) classes += " active";
      if (hasEv)    classes += " event";

      html += '<div class="' + classes + '">' + i + "</div>";
    }

    // Días del mes siguiente (grises)
    for (let j = 1; j <= nextDays; j++) {
      html += '<div class="day next-date">' + j + "</div>";
    }

    daysContainer.innerHTML = html;
    attachDayListeners();

    // Mostrar eventos del día activo
    updateDayInfo(activeDay);
    renderEvents(activeDay);
  }

  /**
   * Actualizar información del día seleccionado
   */
  function updateDayInfo(d) {
    const dateObj = new Date(year, month, d);
    if (eventDayEl)  eventDayEl.textContent  = DIAS[dateObj.getDay()];
    if (eventDateEl) eventDateEl.textContent = d + " de " + MESES[month] + " de " + year;
  }

  /**
   * Renderizar eventos del día seleccionado
   */
  function renderEvents(d) {
    const list = getEvents(d, month + 1, year);

    if (list.length === 0) {
      eventsEl.innerHTML =
        '<div class="no-event">' +
          '<i class="fas fa-calendar-times"></i>' +
          "<h3>No hay eventos programados</h3>" +
          "<p>No hay eventos para este día</p>" +
        "</div>";
      return;
    }

    let html = "";
    list.forEach(function (ev) {
      html +=
        '<div class="cal-event">' +
          '<div class="ev-title-row">' +
            '<i class="fas fa-circle"></i>' +
            '<span class="ev-name">' + escHtml(ev.title) + "</span>" +
          "</div>";

      // Hora
      if (ev.time) {
        html += '<div class="ev-detail"><i class="fas fa-clock"></i>' + escHtml(ev.time) + "</div>";
      }

      // Servicio
      if (ev.servicio) {
        html += '<div class="ev-detail"><i class="fas fa-star"></i>' + escHtml(ev.servicio) + "</div>";
      }

      // Lugar
      if (ev.lugar) {
        html += '<div class="ev-detail"><i class="fas fa-map-marker-alt"></i>' + escHtml(ev.lugar) + "</div>";
      }

      // Cliente
      if (ev.cliente) {
        html += '<div class="ev-detail"><i class="fas fa-user"></i>' + escHtml(ev.cliente) + "</div>";
      }

      // Personal
      if (ev.personal) {
        html += '<div class="ev-detail"><i class="fas fa-users"></i>' + escHtml(ev.personal) + "</div>";
      }

      html += "</div>";
    });

    eventsEl.innerHTML = html;
  }

  /**
   * Agregar listeners a los días del calendario
   */
  function attachDayListeners() {
    const dayEls = daysContainer.querySelectorAll(".day");

    dayEls.forEach(function (el) {
      el.addEventListener("click", function () {
        const num = parseInt(el.textContent);

        // Quitar active de todos
        dayEls.forEach(function (d) { d.classList.remove("active"); });

        if (el.classList.contains("prev-date")) {
          month--;
          if (month < 0) { month = 11; year--; }
          activeDay = num;
          renderCalendar();
          return;
        }

        if (el.classList.contains("next-date")) {
          month++;
          if (month > 11) { month = 0; year++; }
          activeDay = num;
          renderCalendar();
          return;
        }

        el.classList.add("active");
        activeDay = num;
        updateDayInfo(num);
        renderEvents(num);
      });
    });
  }

  /**
   * Ir al mes anterior
   */
  function prevMonth() {
    month--;
    if (month < 0) { month = 11; year--; }
    renderCalendar();
  }

  /**
   * Ir al mes siguiente
   */
  function nextMonth() {
    month++;
    if (month > 11) { month = 0; year++; }
    renderCalendar();
  }

  if (prevBtn) prevBtn.addEventListener("click", prevMonth);
  if (nextBtn) nextBtn.addEventListener("click", nextMonth);

  // ── Botón Hoy ──────────────────────────────────────────
  if (todayBtn) {
    todayBtn.addEventListener("click", function () {
      month    = today.getMonth();
      year     = today.getFullYear();
      activeDay = today.getDate();
      renderCalendar();
    });
  }

  // ── Input ir a fecha (mm/yyyy) ──────────────────────────
  if (dateInput) {
    dateInput.addEventListener("input", function (e) {
      dateInput.value = dateInput.value.replace(/[^0-9/]/g, "");
      if (dateInput.value.length === 2 && e.inputType !== "deleteContentBackward") {
        dateInput.value += "/";
      }
      if (dateInput.value.length > 7) {
        dateInput.value = dateInput.value.slice(0, 7);
      }
    });
  }

  if (gotoBtn) {
    gotoBtn.addEventListener("click", function () {
      const parts = (dateInput ? dateInput.value : "").split("/");
      if (parts.length === 2 && parts[0] > 0 && parts[0] < 13 && parts[1].length === 4) {
        month = parseInt(parts[0]) - 1;
        year  = parseInt(parts[1]);
        renderCalendar();
      } else {
        alert("Fecha inválida. Use el formato mm/aaaa");
      }
    });
  }

  // ── Iniciar calendario ──────────────────────────────────
  renderCalendar();

})();