// Custom Date Picker (Date Only - for filters)
class DatePicker {
    constructor(inputId, options = {}) {
        this.input = document.getElementById(inputId);
        if (!this.input) {
            console.error(`Input element with id "${inputId}" not found`);
            return;
        }

        this.options = {
            format: options.format || 'dd-mm-yyyy',
            minDate: options.minDate || null,
            maxDate: options.maxDate || null,
            ...options
        };

        // Don't set default date - start with null
        this.selectedDate = null;
        this.init();
    }

    init() {
        // Hide the original input
        this.input.style.display = 'none';

        // Create display element
        this.displayElement = document.createElement('div');
        this.displayElement.className = 'date-picker-input';
        this.displayElement.style.cssText = 'width: 100%; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 8px; background: white; cursor: pointer; display: flex; align-items: center; justify-content: space-between; font-size: 0.9rem;';
        this.input.parentNode.insertBefore(this.displayElement, this.input.nextSibling);

        // Create text element
        this.textElement = document.createElement('span');
        this.textElement.className = 'date-picker-text';
        this.textElement.textContent = this.input.placeholder || 'dd-mm-yyyy';
        this.displayElement.appendChild(this.textElement);

        // Add calendar icon
        const icon = document.createElement('i');
        icon.className = 'fas fa-calendar';
        icon.style.cssText = 'color: #6b7280; font-size: 0.875rem;';
        this.displayElement.appendChild(icon);

        // Create modal
        this.createModal();

        // Set initial value only if input has a value
        if (this.input.value) {
            this.setValue(this.input.value);
        }
        // Otherwise, keep placeholder text displayed

        // Event listeners
        this.displayElement.addEventListener('click', () => this.open());
    }

    createModal() {
        this.modal = document.createElement('div');
        this.modal.className = 'date-picker-modal';
        this.modal.innerHTML = `
            <div class="date-picker-container">
                <div class="date-picker-header">
                    <h3>Select Date</h3>
                    <button class="date-picker-close" type="button">&times;</button>
                </div>
                <div class="date-picker-body">
                    <div class="date-picker-calendar">
                        <div class="calendar-header">
                            <div class="calendar-month-year">
                                <span class="calendar-month-year-text"></span>
                            </div>
                            <div class="calendar-nav">
                                <button class="calendar-nav-btn calendar-prev-month" type="button">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button class="calendar-nav-btn calendar-next-month" type="button">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="calendar-weekdays">
                            <div class="calendar-weekday">Mo</div>
                            <div class="calendar-weekday">Tu</div>
                            <div class="calendar-weekday">We</div>
                            <div class="calendar-weekday">Th</div>
                            <div class="calendar-weekday">Fr</div>
                            <div class="calendar-weekday">Sa</div>
                            <div class="calendar-weekday">Su</div>
                        </div>
                        <div class="calendar-days"></div>
                        <div class="calendar-footer">
                            <button class="calendar-footer-btn calendar-clear-btn" type="button">Clear</button>
                            <button class="calendar-footer-btn calendar-today-btn" type="button">Today</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(this.modal);

        // Event listeners
        this.modal.querySelector('.date-picker-close').addEventListener('click', () => this.close());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        this.modal.querySelector('.calendar-prev-month').addEventListener('click', () => this.changeMonth(-1));
        this.modal.querySelector('.calendar-next-month').addEventListener('click', () => this.changeMonth(1));
        this.modal.querySelector('.calendar-clear-btn').addEventListener('click', () => this.clearDate());
        this.modal.querySelector('.calendar-today-btn').addEventListener('click', () => this.setToday());

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });

        // Initialize calendar
        this.renderCalendar();
    }

    renderCalendar() {
        // Use current date for display if no date selected
        const displayDate = this.selectedDate || new Date();
        const year = displayDate.getFullYear();
        const month = displayDate.getMonth();

        // Update month/year display (scope to this modal)
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        const monthYearEl = this.modal.querySelector('.calendar-month-year-text');
        if (monthYearEl) {
            monthYearEl.textContent = `${monthNames[month]}, ${year}`;
        }

        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        // Adjust first day (Monday = 0)
        const adjustedFirstDay = (firstDay + 6) % 7;

        const calendarDays = this.modal.querySelector('.calendar-days');
        if (!calendarDays) return;
        calendarDays.innerHTML = '';

        // Previous month days
        for (let i = adjustedFirstDay - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            const date = new Date(year, month - 1, day);
            const dayElement = this.createDayElement(day, date, true);
            calendarDays.appendChild(dayElement);
        }

        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dayElement = this.createDayElement(day, date, false);
            calendarDays.appendChild(dayElement);
        }

        // Next month days (fill remaining cells)
        const totalCells = calendarDays.children.length;
        const remainingCells = 42 - totalCells; // 6 rows * 7 days
        for (let day = 1; day <= remainingCells && day <= 7; day++) {
            const date = new Date(year, month + 1, day);
            const dayElement = this.createDayElement(day, date, true);
            calendarDays.appendChild(dayElement);
        }
    }

    createDayElement(day, date, isOtherMonth) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = day;

        if (isOtherMonth) {
            dayElement.classList.add('other-month');
        }

        // Check if today
        const today = new Date();
        if (date.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }

        // Check if selected (only if a date is selected)
        if (this.selectedDate && this.isSameDate(date, this.selectedDate)) {
            dayElement.classList.add('selected');
        }

        // Click handler
        dayElement.addEventListener('click', () => {
            this.selectedDate = new Date(date);
            this.renderCalendar();
            this.updateInputValue();
            this.close();
        });

        return dayElement;
    }

    isSameDate(date1, date2) {
        return date1.getFullYear() === date2.getFullYear() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getDate() === date2.getDate();
    }

    changeMonth(direction) {
        this.selectedDate.setMonth(this.selectedDate.getMonth() + direction);
        this.renderCalendar();
    }

    setToday() {
        this.selectedDate = new Date();
        this.renderCalendar();
        this.updateInputValue();
        this.close();
    }

    updateInputValue() {
        if (!this.selectedDate) {
            // Clear the input and show placeholder
            this.input.value = '';
            this.textElement.textContent = this.input.placeholder || 'dd-mm-yyyy';
            this.textElement.classList.remove('has-value');
            return;
        }

        const year = this.selectedDate.getFullYear();
        const month = String(this.selectedDate.getMonth() + 1).padStart(2, '0');
        const day = String(this.selectedDate.getDate()).padStart(2, '0');

        // Format for date input (YYYY-MM-DD)
        const dateValue = `${year}-${month}-${day}`;
        this.input.value = dateValue;

        // Format for display (dd-mm-yyyy)
        const displayText = `${day}-${month}-${year}`;
        this.textElement.textContent = displayText;
        this.textElement.classList.add('has-value');

        // Trigger change event on the input so filters work
        const event = new Event('change', { bubbles: true });
        this.input.dispatchEvent(event);
    }

    open() {
        // If no date selected, show current month
        if (!this.selectedDate) {
            this.selectedDate = new Date();
        }
        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        this.renderCalendar();
    }

    close() {
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    getValue() {
        return this.input.value;
    }

    setValue(dateString) {
        if (dateString) {
            const date = new Date(dateString);
            if (!isNaN(date.getTime())) {
                this.selectedDate = date;
                this.updateInputValue();
            }
        } else {
            this.clearDate();
        }
    }

    // Public method to clear date (can be called externally)
    clearDate() {
        this.selectedDate = null;
        this.input.value = '';
        if (this.textElement) {
            this.textElement.textContent = this.input.placeholder || 'dd-mm-yyyy';
            this.textElement.classList.remove('has-value');
        }
        if (this.modal && this.modal.classList.contains('show')) {
            this.renderCalendar(); // Re-render to remove selection highlight
            this.close();
        }
        
        // Trigger change event to update filters
        const event = new Event('change', { bubbles: true });
        this.input.dispatchEvent(event);
    }
}

// Initialize date pickers when DOM is ready
function initializeDatePickers() {
    // Initialize filter date pickers
    const filterDateFrom = document.getElementById('filterDateFrom');
    const filterDateTo = document.getElementById('filterDateTo');
    
    if (filterDateFrom && !filterDateFrom.dataset.pickerInitialized) {
        try {
            window.filterDateFromPicker = new DatePicker('filterDateFrom');
            filterDateFrom.dataset.pickerInitialized = 'true';
        } catch (e) {
            console.error('Error initializing filterDateFrom picker:', e);
        }
    }
    
    if (filterDateTo && !filterDateTo.dataset.pickerInitialized) {
        try {
            window.filterDateToPicker = new DatePicker('filterDateTo');
            filterDateTo.dataset.pickerInitialized = 'true';
        } catch (e) {
            console.error('Error initializing filterDateTo picker:', e);
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDatePickers);
} else {
    // DOM is already ready
    initializeDatePickers();
}

// Also try after a short delay to ensure all scripts are loaded
setTimeout(initializeDatePickers, 100);

