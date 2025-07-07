// Booking System JavaScript
// Global variables
let selectedFacility = null;
let selectedDate = null;
let selectedTimeSlots = []; // Changed to array for multiple slots
let availableSlots = {};
let currentDailyBookingCount = 0;
const MAX_CONSECUTIVE_SLOTS = 2; // Maximum 2 consecutive slots

// Time slots (8 AM to 5 PM, 9 slots total)
const timeSlots = [
    '08:00', '09:00', '10:00', '11:00', 
    '12:00', '13:00', '14:00', '15:00', '16:00'
];
const TIME_SLOTS = timeSlots; // For consistency

// Initialize the booking system when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeBookingSystem();
});

function initializeBookingSystem() {
    setupEventListeners();
    setDateConstraints();
}

function setupEventListeners() {
    // Facility selection
    document.querySelectorAll('.facility-card').forEach(card => {
        card.addEventListener('click', function() {
            selectFacility(this);
        });
    });

    // Facility type filter
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            filterFacilities(this.dataset.filter);
            setFilterActive(this);
        });
    });

    // Date selection
    document.getElementById('booking-date').addEventListener('change', function() {
        console.log('Date changed to:', this.value); // Debug log
        onDateChange(this.value);
    });

    // Purpose character counter
    document.getElementById('booking-purpose').addEventListener('input', function() {
        updateCharacterCount(this.value.length);
    });

    // Form submission
    document.getElementById('booking-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitBooking();
    });

    // Reset button
    document.getElementById('reset-booking').addEventListener('click', function() {
        resetBookingForm();
    });
}

function selectFacility(facilityCard) {
    // Remove previous selection
    document.querySelectorAll('.facility-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Add selection to clicked card
    facilityCard.classList.add('selected');

    // Get facility data
    const facilityId = facilityCard.dataset.facilityId;
    const facilityType = facilityCard.dataset.facilityType;
    const advanceDays = parseInt(facilityCard.dataset.advanceDays);
    
    // Get facility details from the card
    const facilityName = facilityCard.querySelector('h5').textContent;
    const facilityCapacity = facilityCard.querySelector('.facility-details').textContent.match(/\d+/)[0];
    const facilityLocation = facilityCard.querySelectorAll('.facility-details')[1].textContent.replace(/.*\s/, '');

    selectedFacility = {
        id: facilityId,
        name: facilityName,
        type: facilityType,
        capacity: facilityCapacity,
        location: facilityLocation,
        advanceDays: advanceDays
    };

    // Update UI
    updateSelectedFacilityDisplay();
    updateDateConstraints();
    hideInitialState();
    showBookingInterface();
    resetTimeSelection();
    resetForm();
}

function updateSelectedFacilityDisplay() {
    if (!selectedFacility) return;

    const facilityInfo = document.getElementById('selected-facility');
    document.getElementById('selected-facility-name').textContent = selectedFacility.name;
    document.getElementById('selected-facility-capacity').textContent = selectedFacility.capacity;
    document.getElementById('selected-facility-location').textContent = selectedFacility.location;
    
    const ruleText = selectedFacility.advanceDays === 0 ? 
        'Same day booking only' : 
        `Book up to ${selectedFacility.advanceDays} days in advance`;
    document.getElementById('selected-facility-rule').textContent = ruleText;
    
    facilityInfo.classList.remove('d-none');
    facilityInfo.classList.add('fade-in');
}

function updateDateConstraints() {
    if (!selectedFacility) return;

    const today = new Date();
    const dateInput = document.getElementById('booking-date');
    
    // Set minimum date to today
    dateInput.min = formatDate(today);
    
    // Set maximum date based on advance booking days
    const maxDate = new Date(today);
    maxDate.setDate(today.getDate() + selectedFacility.advanceDays);
    dateInput.max = formatDate(maxDate);
    
    // Update helper text with more detailed information
    let helperText;
    if (selectedFacility.advanceDays === 0) {
        helperText = 'today only (same day booking only)';
    } else {
        helperText = `${selectedFacility.advanceDays} days in advance`;
    }
    document.getElementById('max-days-text').textContent = helperText;
    
    // Clear previous date selection
    dateInput.value = '';
    selectedDate = null;
    
    // Hide time selection and form
    document.getElementById('time-selection').classList.add('d-none');
    document.getElementById('booking-form-section').classList.add('d-none');
}

function showBookingInterface() {
    document.getElementById('date-selection').classList.remove('d-none');
    document.getElementById('date-selection').classList.add('slide-in');
}

function hideInitialState() {
    document.getElementById('initial-state').classList.add('d-none');
}

function onDateChange(date) {
    if (!date || !selectedFacility) return;

    selectedDate = date;
    
    // Hide help alert when user selects new date
    document.getElementById('date-help-alert').classList.add('d-none');
    
    // Reset time and form selections first
    resetTimeSelection();
    resetForm();
    
    // Load available time slots first, then show interface
    loadAvailableTimeSlots();
    
    // Check daily booking count
    checkDailyBookingCount(date);
}

function loadAvailableTimeSlots() {
    if (!selectedFacility || !selectedDate) return;

    // Show loading state
    showTimeGridLoading();

    // AJAX call to check availability
    fetch('check_availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `facility_id=${selectedFacility.id}&date=${selectedDate}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data); // Debug log
        if (data.success) {
            availableSlots = data.available_slots;
            generateTimeGrid();
            // Show time selection only after successful data load
            document.getElementById('time-selection').classList.remove('d-none');
            document.getElementById('time-selection').classList.add('slide-in');
        } else {
            // Hide time selection on error
            document.getElementById('time-selection').classList.add('d-none');
            
            // Show help alert for date-related errors
            const helpAlert = document.getElementById('date-help-alert');
            if (data.message.includes('date') || data.message.includes('advance') || data.message.includes('past')) {
                helpAlert.classList.remove('d-none');
            }
            
            showAlert('Error loading time slots: ' + data.message, 'danger');
            console.error('API Error:', data.message);
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        // Hide time selection on error
        document.getElementById('time-selection').classList.add('d-none');
        showAlert('Failed to load time slots. Please try again.', 'danger');
    })
    .finally(() => {
        hideTimeGridLoading();
    });
}

function generateTimeGrid() {
    const timeGrid = document.getElementById('time-grid');
    timeGrid.innerHTML = '';

    timeSlots.forEach(time => {
        const slot = document.createElement('div');
        slot.className = 'time-slot';
        slot.textContent = time;
        slot.dataset.time = time;

        // Determine slot status
        const slotData = availableSlots.find(s => s.time === time);
        if (slotData) {
            if (slotData.available) {
                slot.classList.add('available');
                slot.addEventListener('click', () => selectTimeSlot(time));
            } else {
                slot.classList.add('booked');
            }
        } else {
            slot.classList.add('disabled');
        }

        timeGrid.appendChild(slot);
    });
}

function selectTimeSlot(time) {
    const clickedSlot = document.querySelector(`[data-time="${time}"]`);
    
    // Check if slot is already selected
    const isSelected = selectedTimeSlots.includes(time);
    
    if (isSelected) {
        // Deselect the slot
        selectedTimeSlots = selectedTimeSlots.filter(slot => slot !== time);
        clickedSlot.classList.remove('selected');
    } else {
        // Check if we can add more slots
        if (selectedTimeSlots.length >= MAX_CONSECUTIVE_SLOTS) {
            showAlert('You can only select maximum 2 consecutive time slots.', 'warning');
            return;
        }
        
        // Check if the new slot is consecutive with existing selections
        if (selectedTimeSlots.length > 0 && !isConsecutiveSlot(time)) {
            showAlert('Please select consecutive time slots only.', 'warning');
            return;
        }
        
        // Add the slot
        selectedTimeSlots.push(time);
        selectedTimeSlots.sort(); // Keep slots in order
        clickedSlot.classList.add('selected');
    }
    
    // Update UI based on selection
    if (selectedTimeSlots.length > 0) {
        showBookingForm();
        updateBookingSummary();
    } else {
        hideBookingForm();
    }
    
    // Update slot selection indicators
    updateSlotSelectionIndicators();
}

function showBookingForm() {
    document.getElementById('booking-form-section').classList.remove('d-none');
    document.getElementById('booking-form-section').classList.add('fade-in');
}

function updateBookingSummary() {
    if (!selectedFacility || !selectedDate || selectedTimeSlots.length === 0) return;

    document.getElementById('summary-facility').textContent = selectedFacility.name;
    document.getElementById('summary-date').textContent = formatDateDisplay(selectedDate);
    
    // Handle multiple time slots
    if (selectedTimeSlots.length === 1) {
        const endTime = calculateEndTime(selectedTimeSlots[0]);
        document.getElementById('summary-time').textContent = `${selectedTimeSlots[0]} - ${endTime}`;
        document.getElementById('summary-duration').textContent = '1 hour';
    } else if (selectedTimeSlots.length === 2) {
        // For consecutive slots, show start of first to end of last
        const startTime = selectedTimeSlots[0];
        const endTime = calculateEndTime(selectedTimeSlots[selectedTimeSlots.length - 1]);
        document.getElementById('summary-time').textContent = `${startTime} - ${endTime}`;
        document.getElementById('summary-duration').textContent = '2 hours (consecutive slots)';
    }
}

function calculateEndTime(startTime) {
    const [hours, minutes] = startTime.split(':').map(Number);
    const endHours = hours + 1;
    return `${endHours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
}

function formatDateDisplay(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function checkDailyBookingCount(date) {
    fetch('check_availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=check_daily_count&date=${date}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentDailyBookingCount = data.count;
            updateDailyBookingDisplay();
        }
    })
    .catch(error => {
        console.error('Error checking daily booking count:', error);
    });
}

function updateDailyBookingDisplay() {
    const countElement = document.getElementById('daily-booking-count');
    const remaining = 2 - currentDailyBookingCount;
    
    if (remaining <= 0) {
        countElement.textContent = 'You have reached your daily booking limit.';
        countElement.parentElement.classList.remove('alert-warning');
        countElement.parentElement.classList.add('alert-danger');
        document.getElementById('confirm-booking').disabled = true;
    } else {
        countElement.textContent = `You have ${remaining} booking(s) remaining for this day.`;
        countElement.parentElement.classList.remove('alert-danger');
        countElement.parentElement.classList.add('alert-warning');
        document.getElementById('confirm-booking').disabled = false;
    }
}

function submitBooking() {
    if (!validateBookingForm()) return;

    // For multiple slots, create booking data
    const formData = {
        facility_id: selectedFacility.id,
        booking_date: selectedDate,
        time_slots: JSON.stringify(selectedTimeSlots),
        start_time: selectedTimeSlots[0],
        end_time: calculateEndTime(selectedTimeSlots[selectedTimeSlots.length - 1]),
        purpose: document.getElementById('booking-purpose').value.trim(),
        slot_count: selectedTimeSlots.length
    };

    // Show loading modal
    showLoadingModal();

    fetch('process_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: Object.keys(formData).map(key => 
            encodeURIComponent(key) + '=' + encodeURIComponent(formData[key])
        ).join('&')
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingModal();
        
        if (data.success) {
            showAlert('Booking confirmed successfully! Confirmation email has been sent.', 'success');
            resetBookingForm();
            // Refresh availability after successful booking
            loadAvailableTimeSlots();
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        hideLoadingModal();
        console.error('Error:', error);
        showAlert('Failed to process booking. Please try again.', 'danger');
    });
}

function validateBookingForm() {
    const purpose = document.getElementById('booking-purpose').value.trim();
    
    if (!selectedFacility) {
        showAlert('Please select a facility.', 'warning');
        return false;
    }
    
    if (!selectedDate) {
        showAlert('Please select a date.', 'warning');
        return false;
    }
    
    if (selectedTimeSlots.length === 0) {
        showAlert('Please select at least one time slot.', 'warning');
        return false;
    }
    
    if (purpose.length < 10) {
        showAlert('Purpose must be at least 10 characters long.', 'warning');
        document.getElementById('booking-purpose').focus();
        return false;
    }
    
    if (currentDailyBookingCount >= 2) {
        showAlert('You have reached your daily booking limit of 2 slots.', 'danger');
        return false;
    }
    
    return true;
}

function resetBookingForm() {
    // Reset facility selection
    document.querySelectorAll('.facility-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Reset form values
    document.getElementById('booking-date').value = '';
    document.getElementById('booking-purpose').value = '';
    updateCharacterCount(0);

    // Reset variables
    selectedFacility = null;
    selectedDate = null;
    selectedTimeSlots = [];

    // Hide sections
    document.getElementById('selected-facility').classList.add('d-none');
    document.getElementById('date-selection').classList.add('d-none');
    document.getElementById('time-selection').classList.add('d-none');
    document.getElementById('booking-form-section').classList.add('d-none');

    // Show initial state
    document.getElementById('initial-state').classList.remove('d-none');

    // Reset filter
    document.querySelector('[data-filter="all"]').click();
}

function resetTimeSelection() {
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected', 'non-consecutive');
    });
    selectedTimeSlots = [];
    
    // Only hide if we're not loading
    const timeGrid = document.getElementById('time-grid');
    if (!timeGrid.classList.contains('loading')) {
        document.getElementById('time-selection').classList.add('d-none');
    }
    document.getElementById('booking-form-section').classList.add('d-none');
}

function resetForm() {
    document.getElementById('booking-purpose').value = '';
    updateCharacterCount(0);
    document.getElementById('booking-form-section').classList.add('d-none');
}

function filterFacilities(type) {
    const cards = document.querySelectorAll('.facility-card');
    
    cards.forEach(card => {
        if (type === 'all' || card.dataset.facilityType === type) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function setFilterActive(button) {
    document.querySelectorAll('[data-filter]').forEach(btn => {
        btn.classList.remove('active');
    });
    button.classList.add('active');
}

function updateCharacterCount(count) {
    const counter = document.getElementById('char-count');
    counter.textContent = count;
    
    // Update counter color based on count
    counter.classList.remove('warning', 'danger');
    if (count > 450) {
        counter.classList.add('danger');
    } else if (count > 400) {
        counter.classList.add('warning');
    }
}

function showTimeGridLoading() {
    // Show time selection section first
    document.getElementById('time-selection').classList.remove('d-none');
    document.getElementById('time-selection').classList.add('slide-in');
    
    const timeGrid = document.getElementById('time-grid');
    timeGrid.classList.add('loading');
    timeGrid.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading time slots...</div>';
}

function hideTimeGridLoading() {
    const timeGrid = document.getElementById('time-grid');
    timeGrid.classList.remove('loading');
}

function showLoadingModal() {
    const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
    modal.show();
}

function hideLoadingModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
    if (modal) {
        modal.hide();
    }
}

function showAlert(message, type) {
    const alertDiv = document.getElementById('booking-alert');
    const messageSpan = document.getElementById('alert-message');
    
    // Remove existing classes
    alertDiv.classList.remove('alert-info', 'alert-warning', 'alert-success', 'alert-danger');
    
    // Add new class
    alertDiv.classList.add(`alert-${type}`);
    
    // Set message
    messageSpan.textContent = message;
    
    // Show alert
    alertDiv.classList.remove('d-none');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        alertDiv.classList.add('d-none');
    }, 5000);
    
    // Scroll to alert
    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function setDateConstraints() {
    const today = new Date();
    const dateInput = document.getElementById('booking-date');
    dateInput.min = formatDate(today);
}

function formatDate(date) {
    return date.getFullYear() + '-' + 
           (date.getMonth() + 1).toString().padStart(2, '0') + '-' + 
           date.getDate().toString().padStart(2, '0');
}

// Utility function to check if booking is within cancellation window
function canCancelBooking(bookingDateTime) {
    const now = new Date();
    const bookingTime = new Date(bookingDateTime);
    const timeDiff = (bookingTime - now) / (1000 * 60); // difference in minutes
    
    return timeDiff > 30; // Can cancel if more than 30 minutes before booking
}

// Helper function to check if a time slot is consecutive with selected slots
function isConsecutiveSlot(time) {
    if (selectedTimeSlots.length === 0) return true;
    
    const timeIndex = TIME_SLOTS.indexOf(time);
    const selectedIndices = selectedTimeSlots.map(slot => TIME_SLOTS.indexOf(slot));
    
    // Check if the new slot is adjacent to any selected slot
    for (let index of selectedIndices) {
        if (Math.abs(timeIndex - index) === 1) {
            return true;
        }
    }
    
    return false;
}

// Helper function to update slot selection indicators
function updateSlotSelectionIndicators() {
    document.querySelectorAll('.time-slot').forEach(slot => {
        const time = slot.getAttribute('data-time');
        const isDisabled = slot.classList.contains('disabled');
        
        if (!isDisabled && selectedTimeSlots.length > 0 && selectedTimeSlots.length < MAX_CONSECUTIVE_SLOTS) {
            // Show which slots can be selected next
            if (!selectedTimeSlots.includes(time) && !isConsecutiveSlot(time)) {
                slot.classList.add('non-consecutive');
            } else {
                slot.classList.remove('non-consecutive');
            }
        } else {
            slot.classList.remove('non-consecutive');
        }
    });
}

// Helper function to hide booking form
function hideBookingForm() {
    const bookingForm = document.getElementById('booking-form-section');
    if (bookingForm) {
        bookingForm.classList.add('d-none');
    }
}

// Export functions for external use
window.BookingSystem = {
    selectFacility,
    resetBookingForm,
    canCancelBooking,
    showAlert
}; 

function loadBookedSlots(facilityId, selectedDate) {
  fetch(`get_facility_bookings.php?facility_id=${facilityId}`)
    .then(response => response.json())
    .then(bookings => {
      // 清空之前标记
      document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('booked');
      });

      // 遍历所有 booking，看是否跟选的日期相同
      bookings.forEach(b => {
        const bookingDate = b.start.split('T')[0];
        if (bookingDate === selectedDate) {
          const startTime = b.start.split('T')[1].slice(0,5);
          const endTime = b.end.split('T')[1].slice(0,5);

          // 你要根据你的时间 slot 的 id 或 data-time，来把它设为 booked
          document.querySelectorAll('.time-slot').forEach(slot => {
            const slotTime = slot.getAttribute('data-time');
            if (slotTime >= startTime && slotTime < endTime) {
              slot.classList.add('booked');
              slot.classList.remove('available');
            }
          });
        }
      });
    });
}
