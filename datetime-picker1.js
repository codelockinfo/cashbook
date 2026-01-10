// Custom Date-Time Picker
class DateTimePicker {
    constructor(inputId, options = {}) {
        this.input = document.getElementById(inputId);
        if (!this.input) {
            console.error(`Input element with id "${inputId}" not found`);
            return;
        }

        this.options = {
            format: options.format || 'YYYY-MM-DD HH:mm',
            minDate: options.minDate || null,
            maxDate: options.maxDate || null,
            ...options
        };

        // Initialize with current date/time, but check if input has a value first
        const now = new Date();
        this.selectedDate = new Date(now);
        this.selectedHour = now.getHours();
        this.selectedMinute = now.getMinutes();
        
        // If input already has a value, use it instead
        if (this.input.value) {
            const inputDate = new Date(this.input.value);
            if (!isNaN(inputDate.getTime())) {
                this.selectedDate = inputDate;
                this.selectedHour = inputDate.getHours();
                this.selectedMinute = inputDate.getMinutes();
            }
        }

        this.init();
    }

    init() {
        // Hide the original input
        this.input.style.display = 'none';

        // Derive display element IDs from input ID or use provided options
        const displayId = this.options.displayId || this.input.id + 'Display';
        const textId = this.options.textId || this.input.id + 'Text';

        // Check if display element exists, if not create it
        this.displayElement = document.getElementById(displayId);
        if (!this.displayElement) {
            this.displayElement = document.createElement('div');
            this.displayElement.id = displayId;
            this.displayElement.className = 'datetime-picker-input';
            this.displayElement.style.cssText = 'width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; justify-content: space-between; box-sizing: border-box; position: relative; margin: 0;';
            this.input.parentNode.insertBefore(this.displayElement, this.input.nextSibling);
        } else {
            // Ensure existing element has proper styling and is in the correct position
            this.displayElement.className = 'datetime-picker-input';
            this.displayElement.style.cssText = 'width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; justify-content: space-between; box-sizing: border-box; position: relative; margin: 0;';
            
            // Ensure the element is in the correct position (right after the input)
            // Check if display element is already in the correct parent and position
            const inputParent = this.input.parentNode;
            const inputNextSibling = this.input.nextSibling;
            
            if (this.displayElement.parentNode !== inputParent) {
                // If display element is in wrong parent, move it to correct parent
                if (inputNextSibling) {
                    inputParent.insertBefore(this.displayElement, inputNextSibling);
                } else {
                    inputParent.appendChild(this.displayElement);
                }
            } else if (this.displayElement !== inputNextSibling && inputNextSibling) {
                // If display element exists but is not right after input, move it
                inputParent.insertBefore(this.displayElement, inputNextSibling);
            }
        }

        // Get or create text element
        this.textElement = document.getElementById(textId);
        if (!this.textElement) {
            this.textElement = document.createElement('span');
            this.textElement.id = textId;
            this.displayElement.appendChild(this.textElement);
        }

        // Add calendar icon if not present
        if (!this.displayElement.querySelector('i')) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-calendar-alt';
            icon.style.cssText = 'color: #6b7280;';
            this.displayElement.appendChild(icon);
        }

        // Create modal
        this.createModal();

        // Set initial value
        this.updateInputValue();

        // Event listeners
        this.displayElement.addEventListener('click', () => this.open());
    }

    createModal() {
        this.modal = document.createElement('div');
        this.modal.className = 'datetime-picker-modal';
        this.modal.innerHTML = `
            <div class="datetime-picker-container">
                <div class="datetime-picker-header">
                    <h3>Select Date & Time</h3>
                    <button class="datetime-picker-close" type="button">&times;</button>
                </div>
                <div class="datetime-picker-body">
                    <div class="datetime-picker-calendar">
                        <div class="calendar-header">
                            <div class="calendar-month-year">
                                <span id="calendarMonthYear"></span>
                            </div>
                            <div class="calendar-nav">
                                <button class="calendar-nav-btn" id="prevMonth" type="button">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button class="calendar-nav-btn" id="nextMonth" type="button">
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
                        <div class="calendar-days" id="calendarDays"></div>
                        <div class="calendar-footer">
                            <button class="calendar-footer-btn" id="clearDate" type="button">Clear</button>
                            <button class="calendar-footer-btn" id="todayDate" type="button">Today</button>
                        </div>
                    </div>
                    <div class="datetime-picker-time">
                        <div class="time-section">
                            <div class="time-label">Hour</div>
                            <div class="time-scroll" id="hourScroll">
                                ${this.generateTimeItems(24, 'hour')}
                            </div>
                        </div>
                        <div class="time-section">
                            <div class="time-label">Minute</div>
                            <div class="time-scroll" id="minuteScroll">
                                ${this.generateTimeItems(60, 'minute')}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(this.modal);

        // Event listeners
        this.modal.querySelector('.datetime-picker-close').addEventListener('click', () => this.close());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });

        this.modal.querySelector('#prevMonth').addEventListener('click', () => this.changeMonth(-1));
        this.modal.querySelector('#nextMonth').addEventListener('click', () => this.changeMonth(1));
        this.modal.querySelector('#clearDate').addEventListener('click', () => this.clearDate());
        this.modal.querySelector('#todayDate').addEventListener('click', () => this.setToday());

        // Time scroll listeners
        const hourScroll = this.modal.querySelector('#hourScroll');
        const minuteScroll = this.modal.querySelector('#minuteScroll');

        hourScroll.addEventListener('scroll', () => this.handleTimeScroll('hour', hourScroll));
        minuteScroll.addEventListener('scroll', () => this.handleTimeScroll('minute', minuteScroll));

        // Initialize calendar
        this.renderCalendar();
        this.scrollToSelectedTime();
    }

    generateTimeItems(max, type) {
        let html = '';
        for (let i = 0; i < max; i++) {
            const value = i.toString().padStart(2, '0');
            html += `<div class="time-item" data-${type}="${i}">${value}</div>`;
        }
        return html;
    }

    renderCalendar() {
        // Check if modal exists
        if (!this.modal || !this.modal.parentNode) {
            return;
        }

        const year = this.selectedDate.getFullYear();
        const month = this.selectedDate.getMonth();

        // Update month/year display - use modal-scoped query
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        const monthYearEl = this.modal.querySelector('#calendarMonthYear');
        if (monthYearEl) {
            monthYearEl.textContent = `${monthNames[month]}, ${year}`;
        }

        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        // Adjust first day (Monday = 0)
        const adjustedFirstDay = (firstDay + 6) % 7;

        // Use modal-scoped query instead of global getElementById
        const calendarDays = this.modal.querySelector('#calendarDays');
        if (!calendarDays) {
            return;
        }
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

        // Check if selected
        if (this.isSameDate(date, this.selectedDate)) {
            dayElement.classList.add('selected');
        }

        // Click handler
        dayElement.addEventListener('click', () => {
            this.selectedDate = new Date(date);
            this.renderCalendar();
            this.updateInputValue();
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
        this.selectedHour = this.selectedDate.getHours();
        this.selectedMinute = this.selectedDate.getMinutes();
        this.renderCalendar();
        this.updateTimeSelection();
        this.scrollToSelectedTime();
        this.updateInputValue();
    }

    clearDate() {
        this.selectedDate = new Date();
        this.selectedHour = 0;
        this.selectedMinute = 0;
        this.renderCalendar();
        this.updateTimeSelection();
        this.scrollToSelectedTime();
        this.updateInputValue();
    }

    updateTimeSelection() {
        // Update hour selection
        const hourItems = this.modal.querySelectorAll('[data-hour]');
        hourItems.forEach(item => {
            item.classList.remove('selected');
            if (parseInt(item.dataset.hour) === this.selectedHour) {
                item.classList.add('selected');
            }
        });

        // Update minute selection
        const minuteItems = this.modal.querySelectorAll('[data-minute]');
        minuteItems.forEach(item => {
            item.classList.remove('selected');
            if (parseInt(item.dataset.minute) === this.selectedMinute) {
                item.classList.add('selected');
            }
        });
    }

    handleTimeScroll(type, scrollElement) {
        const items = scrollElement.querySelectorAll('.time-item');
        const scrollTop = scrollElement.scrollTop;
        const itemHeight = items[0]?.offsetHeight || 44;
        const selectedIndex = Math.round(scrollTop / itemHeight);

        if (items[selectedIndex]) {
            // Remove previous selection
            items.forEach(item => item.classList.remove('selected'));

            // Add selection
            items[selectedIndex].classList.add('selected');

            // Update value
            if (type === 'hour') {
                this.selectedHour = parseInt(items[selectedIndex].dataset.hour);
            } else {
                this.selectedMinute = parseInt(items[selectedIndex].dataset.minute);
            }

            this.updateInputValue();
        }
    }

    scrollToSelectedTime() {
        const hourScroll = this.modal.querySelector('#hourScroll');
        const minuteScroll = this.modal.querySelector('#minuteScroll');

        // Scroll to selected hour
        const hourItem = hourScroll.querySelector(`[data-hour="${this.selectedHour}"]`);
        if (hourItem) {
            hourItem.classList.add('selected');
            hourScroll.scrollTop = hourItem.offsetTop - hourScroll.offsetHeight / 2 + hourItem.offsetHeight / 2;
        }

        // Scroll to selected minute
        const minuteItem = minuteScroll.querySelector(`[data-minute="${this.selectedMinute}"]`);
        if (minuteItem) {
            minuteItem.classList.add('selected');
            minuteScroll.scrollTop = minuteItem.offsetTop - minuteScroll.offsetHeight / 2 + minuteItem.offsetHeight / 2;
        }

        // Add click handlers for time items
        hourScroll.querySelectorAll('.time-item').forEach(item => {
            item.addEventListener('click', () => {
                hourScroll.querySelectorAll('.time-item').forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                this.selectedHour = parseInt(item.dataset.hour);
                this.updateInputValue();
                // Scroll to center
                hourScroll.scrollTop = item.offsetTop - hourScroll.offsetHeight / 2 + item.offsetHeight / 2;
            });
        });

        minuteScroll.querySelectorAll('.time-item').forEach(item => {
            item.addEventListener('click', () => {
                minuteScroll.querySelectorAll('.time-item').forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                this.selectedMinute = parseInt(item.dataset.minute);
                this.updateInputValue();
                // Scroll to center
                minuteScroll.scrollTop = item.offsetTop - minuteScroll.offsetHeight / 2 + item.offsetHeight / 2;
            });
        });
    }

    updateInputValue() {
        const year = this.selectedDate.getFullYear();
        const month = String(this.selectedDate.getMonth() + 1).padStart(2, '0');
        const day = String(this.selectedDate.getDate()).padStart(2, '0');
        const hour = String(this.selectedHour).padStart(2, '0');
        const minute = String(this.selectedMinute).padStart(2, '0');

        // Format for datetime-local input
        const datetimeValue = `${year}-${month}-${day}T${hour}:${minute}`;
        this.input.value = datetimeValue;

        // Update display text
        const dateStr = `${day}/${month}/${year}`;
        const timeStr = `${hour}:${minute}`;
        const displayText = `${dateStr} ${timeStr}`;
        
        if (this.textElement) {
            this.textElement.textContent = displayText;
        }
    }

    open() {
        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        this.renderCalendar();
        this.updateTimeSelection();
        this.scrollToSelectedTime();
    }

    close() {
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    getValue() {
        return this.input.value;
    }

    setValue(datetimeString) {
        if (datetimeString) {
            const date = new Date(datetimeString);
            if (!isNaN(date.getTime())) {
                this.selectedDate = date;
                this.selectedHour = date.getHours();
                this.selectedMinute = date.getMinutes();
                this.updateInputValue();
                
                // Re-render calendar if modal already exists
                if (this.modal && this.modal.parentNode) {
                    this.renderCalendar();
                    this.updateTimeSelection();
                    this.scrollToSelectedTime();
                }
            }
        }
    }
}

// Initialize picker when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize if input exists
    const entryDateInput = document.getElementById('entryDate');
    if (entryDateInput && !entryDateInput.dataset.pickerInitialized) {
        window.dateTimePicker = new DateTimePicker('entryDate');
        entryDateInput.dataset.pickerInitialized = 'true';
        
        // Set initial value from input if it exists
        if (entryDateInput.value) {
            window.dateTimePicker.setValue(entryDateInput.value);
        }
    }
});

