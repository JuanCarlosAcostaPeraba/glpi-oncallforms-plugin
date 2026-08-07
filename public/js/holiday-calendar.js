(() => {
    'use strict';

    const root = document.querySelector('[data-oncallforms-holidays]');
    if (!root) {
        return;
    }

    const hidden = root.querySelector('#holidays_json');
    const title = root.querySelector('[data-calendar-title]');
    const daysContainer = root.querySelector('[data-calendar-days]');
    const list = root.querySelector('[data-holidays-list]');
    const empty = root.querySelector('[data-holidays-empty]');
    const previous = root.querySelector('[data-calendar-previous]');
    const next = root.querySelector('[data-calendar-next]');
    if (!hidden || !title || !daysContainer || !list || !empty || !previous || !next) {
        return;
    }

    const holidays = new Map();
    try {
        const initial = JSON.parse(hidden.value);
        if (Array.isArray(initial)) {
            initial.forEach((holiday) => {
                if (holiday && typeof holiday.date === 'string') {
                    holidays.set(holiday.date, typeof holiday.name === 'string' ? holiday.name : '');
                }
            });
        }
    } catch (error) {
        console.warn('No se pudo cargar el calendario de festivos.', error);
    }

    const today = new Date();
    let displayedMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const monthFormatter = new Intl.DateTimeFormat('es-ES', {month: 'long', year: 'numeric'});
    const dateFormatter = new Intl.DateTimeFormat('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    const pad = (value) => String(value).padStart(2, '0');
    const dateKey = (year, month, day) => `${year}-${pad(month + 1)}-${pad(day)}`;
    const parseDate = (value) => {
        const [year, month, day] = value.split('-').map(Number);
        return new Date(year, month - 1, day);
    };

    const sync = () => {
        const values = [...holidays.entries()]
            .sort(([left], [right]) => left.localeCompare(right))
            .map(([date, name]) => ({date, name}));
        hidden.value = JSON.stringify(values);
    };

    const renderList = () => {
        list.replaceChildren();
        const values = [...holidays.entries()].sort(([left], [right]) => left.localeCompare(right));
        empty.hidden = values.length > 0;

        values.forEach(([date, name]) => {
            const row = document.createElement('div');
            row.className = 'oncallforms-holiday-row';

            const label = document.createElement('label');
            label.className = 'form-label mb-0';
            label.textContent = dateFormatter.format(parseDate(date));

            const input = document.createElement('input');
            input.className = 'form-control';
            input.type = 'text';
            input.maxLength = 160;
            input.placeholder = 'Nombre opcional';
            input.value = name;
            input.setAttribute('aria-label', `Nombre del festivo ${date}`);
            input.addEventListener('input', () => {
                holidays.set(date, input.value);
                sync();
            });

            const remove = document.createElement('button');
            remove.className = 'btn btn-outline-danger';
            remove.type = 'button';
            remove.innerHTML = '<i class="ti ti-trash" aria-hidden="true"></i>';
            remove.setAttribute('aria-label', `Eliminar el festivo ${date}`);
            remove.addEventListener('click', () => {
                holidays.delete(date);
                sync();
                renderCalendar();
                renderList();
            });

            const fields = document.createElement('div');
            fields.className = 'oncallforms-holiday-row__fields';
            fields.append(label, input);
            row.append(fields, remove);
            list.append(row);
        });
    };

    const renderCalendar = () => {
        title.textContent = monthFormatter.format(displayedMonth);
        daysContainer.replaceChildren();

        const year = displayedMonth.getFullYear();
        const month = displayedMonth.getMonth();
        const firstWeekday = (new Date(year, month, 1).getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        for (let position = 0; position < 42; position += 1) {
            const day = position - firstWeekday + 1;
            if (day < 1 || day > daysInMonth) {
                const spacer = document.createElement('span');
                spacer.className = 'oncallforms-calendar__spacer';
                spacer.setAttribute('aria-hidden', 'true');
                daysContainer.append(spacer);
                continue;
            }

            const key = dateKey(year, month, day);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'oncallforms-calendar__day';
            button.textContent = String(day);
            button.setAttribute('role', 'gridcell');
            button.setAttribute('aria-label', dateFormatter.format(new Date(year, month, day)));
            button.setAttribute('aria-pressed', holidays.has(key) ? 'true' : 'false');
            if (holidays.has(key)) {
                button.classList.add('is-holiday');
            }
            if (key === dateKey(today.getFullYear(), today.getMonth(), today.getDate())) {
                button.classList.add('is-today');
            }
            button.addEventListener('click', () => {
                if (holidays.has(key)) {
                    holidays.delete(key);
                } else {
                    holidays.set(key, '');
                }
                sync();
                renderCalendar();
                renderList();
            });
            daysContainer.append(button);
        }
    };

    previous.addEventListener('click', () => {
        displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() - 1, 1);
        renderCalendar();
    });
    next.addEventListener('click', () => {
        displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + 1, 1);
        renderCalendar();
    });

    sync();
    renderCalendar();
    renderList();
})();
