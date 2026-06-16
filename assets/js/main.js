// Interactive Event Calendar and UI Utilities
document.addEventListener('DOMContentLoaded', () => {
    // 1. Ticket Price Live Calculator (for event-details.php)
    const quantityInput = document.getElementById('ticket_quantity');
    const ticketPriceVal = document.getElementById('ticket_price_value');
    const totalPriceDisplay = document.getElementById('total_price_display');
    const hiddenTotalPrice = document.getElementById('hidden_total_price');

    if (quantityInput && ticketPriceVal && totalPriceDisplay) {
        const price = parseFloat(ticketPriceVal.value);
        
        const calculateTotal = () => {
            const quantity = parseInt(quantityInput.value) || 1;
            const total = (price * quantity).toFixed(2);
            totalPriceDisplay.textContent = '$' + total;
            if (hiddenTotalPrice) {
                hiddenTotalPrice.value = total;
            }
        };

        quantityInput.addEventListener('input', calculateTotal);
        quantityInput.addEventListener('change', calculateTotal);
        // Run once initially
        calculateTotal();
    }

    // 2. Interactive Event Calendar Sidebar
    const calendarEl = document.getElementById('interactive-calendar');
    if (calendarEl) {
        // Read dates from attribute or global window variable
        const activeDates = window.activeEventDates || [];
        renderCalendar(calendarEl, activeDates);
    }

    // 3. Client-Side Live Search & Filtering on events.php
    const searchInput = document.getElementById('event-search');
    const filterSelect = document.getElementById('event-filter-category'); // Price or Date filter
    const eventCards = document.querySelectorAll('.event-card-item');

    if (searchInput || filterSelect) {
        const filterEvents = () => {
            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const priceFilter = filterSelect ? filterSelect.value : 'all';

            eventCards.forEach(card => {
                const title = card.getAttribute('data-title').toLowerCase();
                const venue = card.getAttribute('data-venue').toLowerCase();
                const description = card.getAttribute('data-desc').toLowerCase();
                const price = parseFloat(card.getAttribute('data-price'));

                let matchesSearch = title.includes(query) || venue.includes(query) || description.includes(query);
                let matchesPrice = true;

                if (priceFilter === 'free') {
                    matchesPrice = price === 0;
                } else if (priceFilter === 'paid') {
                    matchesPrice = price > 0;
                } else if (priceFilter === 'under-50') {
                    matchesPrice = price < 50;
                } else if (priceFilter === 'above-50') {
                    matchesPrice = price >= 50;
                }

                if (matchesSearch && matchesPrice) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        };

        if (searchInput) searchInput.addEventListener('keyup', filterEvents);
        if (filterSelect) filterSelect.addEventListener('change', filterEvents);
    }
});

// Dynamic Calendar Renderer
function renderCalendar(container, eventDates) {
    const today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();

    const monthNames = [
        "January", "February", "March", "April", "May", "June", 
        "July", "August", "September", "October", "November", "December"
    ];

    function draw() {
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        let html = `
            <div class="calendar-header">
                <button class="btn btn-sm btn-outline-light" id="prev-month">&lt;</button>
                <div class="fw-bold">${monthNames[currentMonth]} ${currentYear}</div>
                <button class="btn btn-sm btn-outline-light" id="next-month">&gt;</button>
            </div>
            <div class="calendar-grid">
                <div class="calendar-day-label">Su</div>
                <div class="calendar-day-label">Mo</div>
                <div class="calendar-day-label">Tu</div>
                <div class="calendar-day-label">We</div>
                <div class="calendar-day-label">Th</div>
                <div class="calendar-day-label">Fr</div>
                <div class="calendar-day-label">Sa</div>
        `;

        // Empty spots before first day
        for (let i = 0; i < firstDay; i++) {
            html += `<div class="calendar-day text-muted" style="opacity:0.3"></div>`;
        }

        // Days of month
        for (let day = 1; day <= daysInMonth; day++) {
            // Format date string as YYYY-MM-DD
            const mStr = String(currentMonth + 1).padStart(2, '0');
            const dStr = String(day).padStart(2, '0');
            const dateStr = `${currentYear}-${mStr}-${dStr}`;

            let classes = "calendar-day";
            if (eventDates.includes(dateStr)) {
                classes += " has-event";
            }
            
            // Check if today
            if (day === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear()) {
                classes += " today";
            }

            // Click behavior
            if (eventDates.includes(dateStr)) {
                html += `<div class="${classes}" onclick="filterByCalendarDate('${dateStr}')">${day}</div>`;
            } else {
                html += `<div class="${classes}">${day}</div>`;
            }
        }

        html += `</div>`;
        container.innerHTML = html;

        // Hook navigation buttons
        document.getElementById('prev-month').addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            draw();
        });

        document.getElementById('next-month').addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            draw();
        });
    }

    draw();
}

// Redirects or filters event lists based on clicking a calendar date
function filterByCalendarDate(dateStr) {
    // Redirect to events.php?date=YYYY-MM-DD
    window.location.href = 'events.php?date=' + dateStr;
}
