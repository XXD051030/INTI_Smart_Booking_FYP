/**
 * Notification System JavaScript
 * Handles notification dropdown, updates, and interactions
 */

class NotificationManager {
    constructor() {
        this.notificationCount = 0;
        this.notifications = [];
        this.isDropdownOpen = false;
        this.refreshInterval = null;
        
        this.init();
    }
    
    init() {
        this.createNotificationDropdown();
        this.bindEvents();
        this.loadNotifications();
        this.startAutoRefresh();
    }
    
    createNotificationDropdown() {
        const notificationIcon = document.getElementById('notification-icon');
        if (!notificationIcon) return;
        
        // Create dropdown container
        const dropdown = document.createElement('div');
        dropdown.className = 'notification-dropdown';
        dropdown.id = 'notification-dropdown';
        dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            max-height: 400px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
            overflow: hidden;
        `;
        
        // Create dropdown header
        const header = document.createElement('div');
        header.className = 'notification-header';
        header.style.cssText = `
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        `;
        header.innerHTML = `
            <h6 class="mb-0 fw-bold">Notifications</h6>
            <button class="btn btn-sm btn-outline-primary" onclick="notificationManager.markAllAsRead()">
                Mark All Read
            </button>
        `;
        
        // Create dropdown body
        const body = document.createElement('div');
        body.className = 'notification-body';
        body.id = 'notification-body';
        body.style.cssText = `
            max-height: 300px;
            overflow-y: auto;
        `;
        
        dropdown.appendChild(header);
        dropdown.appendChild(body);
        
        // Insert dropdown after notification icon
        notificationIcon.parentElement.style.position = 'relative';
        notificationIcon.parentElement.appendChild(dropdown);
    }
    
    bindEvents() {
        const notificationIcon = document.getElementById('notification-icon');
        if (!notificationIcon) return;
        
        // Toggle dropdown on click
        notificationIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.notification-dropdown') && 
                !e.target.closest('#notification-icon')) {
                this.closeDropdown();
            }
        });
        
        // Prevent dropdown from closing when clicking inside
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    }
    
    toggleDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (!dropdown) return;
        
        if (this.isDropdownOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }
    
    openDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (!dropdown) return;
        
        dropdown.style.display = 'block';
        this.isDropdownOpen = true;
        
        // Load fresh notifications
        this.loadNotifications();
    }
    
    closeDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (!dropdown) return;
        
        dropdown.style.display = 'none';
        this.isDropdownOpen = false;
    }
    
    loadNotifications() {
        fetch('get_notifications.php?limit=5')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.notifications = data.notifications;
                    this.notificationCount = data.unread_count;
                    this.updateNotificationCount();
                    this.renderNotifications();
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
    }
    
    updateNotificationCount() {
        const countElement = document.getElementById('notification-count');
        if (countElement) {
            countElement.textContent = this.notificationCount;
            countElement.style.display = this.notificationCount > 0 ? 'block' : 'none';
        }
    }
    
    renderNotifications() {
        const body = document.getElementById('notification-body');
        if (!body) return;
        
        if (this.notifications.length === 0) {
            body.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-bell-slash text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No notifications</p>
                </div>
            `;
            return;
        }
        
        const notificationItems = this.notifications.map(notification => {
            const unreadClass = notification.is_read ? '' : 'notification-unread';
            return `
                <div class="notification-item ${unreadClass}" 
                     data-notification-id="${notification.id}"
                     data-is-read="${notification.is_read}"
                     style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; cursor: pointer;">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="${notification.icon} text-${notification.color}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold" style="font-size: 14px;">${notification.title}</h6>
                            <p class="mb-1 text-muted" style="font-size: 12px;">${notification.message}</p>
                            <small class="text-muted">${notification.time_formatted}</small>
                        </div>
                        ${!notification.is_read ? '<div class="notification-dot" style="width: 8px; height: 8px; background: #007bff; border-radius: 50%; margin-left: 8px;"></div>' : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        body.innerHTML = notificationItems;
        
        // Add click handlers to notification items
        body.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => {
                const notificationId = item.dataset.notificationId;
                const isRead = item.dataset.isRead === 'true';
                
                if (!isRead) {
                    this.markAsRead(notificationId);
                }
            });
        });
    }
    
    markAsRead(notificationId) {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the notification in the array
                const notification = this.notifications.find(n => n.id == notificationId);
                if (notification) {
                    notification.is_read = true;
                }
                
                // Update count and re-render
                this.notificationCount = Math.max(0, this.notificationCount - 1);
                this.updateNotificationCount();
                this.renderNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
    
    markAllAsRead() {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'mark_all=true'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update all notifications to read
                this.notifications.forEach(notification => {
                    notification.is_read = true;
                });
                
                // Update count and re-render
                this.notificationCount = 0;
                this.updateNotificationCount();
                this.renderNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    }
    
    startAutoRefresh() {
        // Refresh notifications every 30 seconds
        this.refreshInterval = setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }
    
    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
    
    destroy() {
        this.stopAutoRefresh();
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.remove();
        }
    }
}

// Initialize notification manager when DOM is loaded
let notificationManager;

document.addEventListener('DOMContentLoaded', function() {
    notificationManager = new NotificationManager();
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (notificationManager) {
        notificationManager.destroy();
    }
});

// Additional CSS for notifications
const notificationStyles = `
    .notification-unread {
        background-color: #f8f9ff !important;
        border-left: 3px solid #007bff !important;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa !important;
    }
    
    .notification-dropdown {
        animation: fadeIn 0.2s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .notification-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .notification-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .notification-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .notification-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet); 