// My Bookings JavaScript
let cancelModal;
let currentBookingId = null;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeMyBookings();
});

function initializeMyBookings() {
    // Initialize Bootstrap modal
    cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    
    // Setup event listeners
    setupEventListeners();
    
    // Update real-time information
    updateBookingStatuses();
}

function setupEventListeners() {
    // Cancel booking buttons
    document.querySelectorAll('.cancel-booking').forEach(button => {
        button.addEventListener('click', function() {
            showCancelModal(this);
        });
    });

    // Confirm cancel button in modal
    document.getElementById('confirmCancel').addEventListener('click', function() {
        confirmCancelBooking();
    });

    // Reset modal when hidden
    document.getElementById('cancelModal').addEventListener('hidden.bs.modal', function() {
        resetCancelModal();
    });
}

function showCancelModal(button) {
    // Get booking data from button attributes
    const bookingId = button.dataset.bookingId;
    const facilityName = button.dataset.facilityName;
    const bookingDate = button.dataset.bookingDate;
    const bookingTime = button.dataset.bookingTime;

    // Store current booking ID
    currentBookingId = bookingId;

    // Populate modal with booking details
    const detailsContainer = document.querySelector('.cancel-booking-details');
    detailsContainer.innerHTML = `
        <div class="card">
            <div class="card-body">
                <h6 class="card-title text-danger">
                    <i class="fas fa-building me-2"></i>${facilityName}
                </h6>
                <p class="card-text mb-1">
                    <i class="fas fa-calendar me-2"></i><strong>Date:</strong> ${bookingDate}
                </p>
                <p class="card-text mb-0">
                    <i class="fas fa-clock me-2"></i><strong>Time:</strong> ${bookingTime}
                </p>
            </div>
        </div>
    `;

    // Show modal
    cancelModal.show();
}

function confirmCancelBooking() {
    if (!currentBookingId) {
        showAlert('Error: No booking selected', 'danger');
        return;
    }

    // Disable cancel button and show loading
    const confirmButton = document.getElementById('confirmCancel');
    const originalText = confirmButton.innerHTML;
    confirmButton.disabled = true;
    confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Cancelling...';

    // Send cancel request
    fetch('cancel_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `booking_id=${currentBookingId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal
            cancelModal.hide();
            
            // Show success message
            showAlert(data.message, 'success');
            
            // Refresh page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to cancel booking. Please try again.', 'danger');
    })
    .finally(() => {
        // Reset button
        confirmButton.disabled = false;
        confirmButton.innerHTML = originalText;
    });
}

function resetCancelModal() {
    currentBookingId = null;
    document.querySelector('.cancel-booking-details').innerHTML = '';
    
    // Reset confirm button
    const confirmButton = document.getElementById('confirmCancel');
    confirmButton.disabled = false;
    confirmButton.innerHTML = '<i class="fas fa-times me-1"></i> Yes, Cancel Booking';
}

function updateBookingStatuses() {
    // Update any time-sensitive information
    const now = new Date();
    
    // Check for bookings that are about to start (within 1 hour)
    document.querySelectorAll('.booking-card').forEach(card => {
        const dateElements = card.querySelectorAll('small');
        // This is a simplified implementation
        // In a real app, you'd parse the actual booking datetime and compare
    });
}

function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('#alert-container .alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${getAlertIcon(type)} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to container
    document.getElementById('alert-container').appendChild(alertDiv);
    
    // Auto-hide after 5 seconds (except for errors)
    if (type !== 'danger') {
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Scroll to alert
    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function getAlertIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'warning': return 'exclamation-triangle';
        case 'danger': return 'exclamation-circle';
        case 'info': return 'info-circle';
        default: return 'info-circle';
    }
}

// Utility function to format date for display
function formatDateForDisplay(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Utility function to format time for display
function formatTimeForDisplay(timeString) {
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

// Utility function to check if booking can be cancelled
function canCancelBooking(bookingDate, startTime) {
    const bookingDateTime = new Date(bookingDate + 'T' + startTime);
    const now = new Date();
    const timeDiffMinutes = (bookingDateTime - now) / (1000 * 60);
    
    return timeDiffMinutes > 30;
}

// Export functions for external use
window.MyBookings = {
    showCancelModal,
    confirmCancelBooking,
    showAlert,
    canCancelBooking
}; 