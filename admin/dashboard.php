<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Include database connection
require_once '../db.php';

try {
    // Get users data
    $users_stmt = $pdo->prepare("SELECT user_id, username, email, is_verified, created_at, updated_at FROM users ORDER BY created_at DESC");
    $users_stmt->execute();
    $users = $users_stmt->fetchAll();

    // Get OTP data with user information
    $otp_stmt = $pdo->prepare("
        SELECT uo.id, uo.user_id, uo.otp_code, uo.expires_at, uo.created_at, u.username, u.email 
        FROM user_otp uo 
        LEFT JOIN users u ON uo.user_id = u.user_id 
        ORDER BY uo.created_at DESC
    ");
    $otp_stmt->execute();
    $otps = $otp_stmt->fetchAll();

    // Get statistics
    $stats_stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified_users,
            SUM(CASE WHEN is_verified = 0 THEN 1 ELSE 0 END) as unverified_users
        FROM users
    ");
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch();

    $otp_stats_stmt = $pdo->prepare("SELECT COUNT(*) as active_otps FROM user_otp WHERE expires_at > NOW()");
    $otp_stats_stmt->execute();
    $otp_stats = $otp_stats_stmt->fetch();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Reservation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg,rgb(246, 31, 31) 0%,rgb(229, 30, 30) 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-nav {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }
        
        .stats-card.users { border-left-color: #28a745; }
        .stats-card.verified { border-left-color: rgb(246, 31, 31); }
        .stats-card.unverified { border-left-color: #ffc107; }
        .stats-card.otps { border-left-color: #dc3545; }
        
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, rgb(246, 31, 31) 0%, rgb(212, 26, 26) 100%);
            color: white;
            padding: 1rem 1.5rem;
            margin: 0;
            font-weight: 600;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .badge-verified {
            background-color: #28a745;
        }
        
        .badge-unverified {
            background-color: #dc3545;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        
        .admin-logo {
            height: 40px;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="../images/logo/inti_logo.png" alt="INTI Logo" class="admin-logo">
                    <h3 class="mb-0">Admin Dashboard</h3>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="admin-nav">
        <div class="container">
            <nav class="nav">
                <a class="nav-link active" href="#users"><i class="fas fa-users me-2"></i>Users</a>
                <a class="nav-link" href="#otps"><i class="fas fa-key me-2"></i>OTP Verification</a>
                <a class="nav-link" href="#stats"><i class="fas fa-chart-bar me-2"></i>Statistics</a>
                <a class="nav-link" href="bookings.php"><i class="fas fa-calendar-alt me-2"></i>Bookings</a>
                <div class="ms-auto">
                    <button class="btn btn-warning btn-sm me-2" onclick="bulkDeleteExpiredOTPs()">
                        <i class="fas fa-trash me-1"></i>Clean Expired OTPs
                    </button>
                    <button class="btn btn-info btn-sm" onclick="exportData()">
                        <i class="fas fa-download me-1"></i>Export Data
                    </button>
                </div>
            </nav>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Section -->
        <div id="stats" class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card users">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-muted mb-1">Total Users</h5>
                            <h2 class="mb-0"><?php echo $stats['total_users'] ?? 0; ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card verified">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-muted mb-1">Verified Users</h5>
                            <h2 class="mb-0"><?php echo $stats['verified_users'] ?? 0; ?></h2>
                        </div>
                        <i class="fas fa-user-check fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card unverified">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-muted mb-1">Unverified Users</h5>
                            <h2 class="mb-0"><?php echo $stats['unverified_users'] ?? 0; ?></h2>
                        </div>
                        <i class="fas fa-user-times fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card otps">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-muted mb-1">Active OTPs</h5>
                            <h2 class="mb-0"><?php echo $otp_stats['active_otps'] ?? 0; ?></h2>
                        </div>
                        <i class="fas fa-key fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div id="users" class="table-card">
            <h4 class="table-header">
                <i class="fas fa-users me-2"></i>Registered Users
            </h4>
            <div class="p-3 border-bottom">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="userSearch" placeholder="Search users by username or email...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="verificationFilter">
                            <option value="">All Users</option>
                            <option value="1">Verified Only</option>
                            <option value="0">Unverified Only</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-secondary w-100" onclick="clearFilters()">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['is_verified']): ?>
                                            <span class="badge badge-verified">Verified</span>
                                        <?php else: ?>
                                            <span class="badge badge-unverified">Unverified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($user['updated_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (!$user['is_verified']): ?>
                                                <button class="btn btn-success btn-sm" onclick="verifyUser(<?php echo $user['user_id']; ?>)" title="Verify User">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-primary btn-sm" onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>')" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-warning btn-sm" onclick="resetPassword(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- OTP Table -->
        <div id="otps" class="table-card">
            <h4 class="table-header">
                <i class="fas fa-key me-2"></i>OTP Verification History
            </h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>OTP Code</th>
                            <th>Expires At</th>
                            <th>Created At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($otps)): ?>
                            <?php foreach ($otps as $otp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($otp['id']); ?></td>
                                    <td><?php echo htmlspecialchars($otp['username'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($otp['email'] ?? 'N/A'); ?></td>
                                    <td><code><?php echo htmlspecialchars($otp['otp_code']); ?></code></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($otp['expires_at'])); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($otp['created_at'])); ?></td>
                                    <td>
                                        <?php if (strtotime($otp['expires_at']) > time()): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Expired</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="deleteOTP(<?php echo $otp['id']; ?>)" title="Delete OTP">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No OTP records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="editUserId">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveUserChanges()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset User Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="resetPasswordForm">
                        <input type="hidden" id="resetUserId">
                        <div class="mb-3">
                            <label for="resetUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="resetUsername" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" required minlength="6">
                            <div class="form-text">Password must be at least 6 characters long</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="savePasswordReset()">Reset Password</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // User management functions
        function deleteUser(userId, username) {
            if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
                fetch('actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_user&user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting user: ' + error);
                });
            }
        }

        function verifyUser(userId) {
            if (confirm('Are you sure you want to verify this user?')) {
                fetch('actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=verify_user&user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User verified successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error verifying user: ' + error);
                });
            }
        }

        function editUser(userId, username, email) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        }

        function saveUserChanges() {
            const userId = document.getElementById('editUserId').value;
            const username = document.getElementById('editUsername').value;
            const email = document.getElementById('editEmail').value;

            fetch('actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=edit_user&user_id=${userId}&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error updating user: ' + error);
            });
        }

        function deleteOTP(otpId) {
            if (confirm('Are you sure you want to delete this OTP record?')) {
                fetch('actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_otp&otp_id=${otpId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('OTP record deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting OTP: ' + error);
                });
            }
        }

        function resetPassword(userId, username) {
            document.getElementById('resetUserId').value = userId;
            document.getElementById('resetUsername').value = username;
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            modal.show();
        }

        function savePasswordReset() {
            const userId = document.getElementById('resetUserId').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }

            if (newPassword.length < 6) {
                alert('Password must be at least 6 characters long!');
                return;
            }

            if (confirm('Are you sure you want to reset this user\'s password?')) {
                fetch('actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=reset_user_password&user_id=${userId}&new_password=${encodeURIComponent(newPassword)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Password reset successfully!');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal'));
                        modal.hide();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error resetting password: ' + error);
                });
            }
        }

        function bulkDeleteExpiredOTPs() {
            if (confirm('Are you sure you want to delete all expired OTP records? This action cannot be undone.')) {
                fetch('actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=bulk_delete_expired_otps'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting expired OTPs: ' + error);
                });
            }
        }

        function exportData() {
            // Create a simple CSV export
            const users = <?php echo json_encode($users); ?>;
            const otps = <?php echo json_encode($otps); ?>;
            
            let csvContent = "data:text/csv;charset=utf-8,";
            
            // Export users data
            csvContent += "USERS DATA\n";
            csvContent += "ID,Username,Email,Verified,Created,Updated\n";
            users.forEach(user => {
                csvContent += `${user.user_id},"${user.username}","${user.email}",${user.is_verified ? 'Yes' : 'No'},"${user.created_at}","${user.updated_at}"\n`;
            });
            
            csvContent += "\n\nOTP DATA\n";
            csvContent += "ID,User,Email,OTP Code,Expires At,Created At\n";
            otps.forEach(otp => {
                csvContent += `${otp.id},"${otp.username || 'N/A'}","${otp.email || 'N/A'}","${otp.otp_code}","${otp.expires_at}","${otp.created_at}"\n`;
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `admin_data_export_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Search and filter functions
        function filterUsers() {
            const searchValue = document.getElementById('userSearch').value.toLowerCase();
            const verificationFilter = document.getElementById('verificationFilter').value;
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const username = row.cells[1]?.textContent.toLowerCase() || '';
                const email = row.cells[2]?.textContent.toLowerCase() || '';
                const isVerified = row.cells[3]?.textContent.includes('Verified');

                let showRow = true;

                // Apply search filter
                if (searchValue && !username.includes(searchValue) && !email.includes(searchValue)) {
                    showRow = false;
                }

                // Apply verification filter
                if (verificationFilter !== '') {
                    if (verificationFilter === '1' && !isVerified) {
                        showRow = false;
                    } else if (verificationFilter === '0' && isVerified) {
                        showRow = false;
                    }
                }

                row.style.display = showRow ? '' : 'none';
            }
        }

        function clearFilters() {
            document.getElementById('userSearch').value = '';
            document.getElementById('verificationFilter').value = '';
            filterUsers();
        }

        // Add event listeners for search and filter
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('userSearch').addEventListener('input', filterUsers);
            document.getElementById('verificationFilter').addEventListener('change', filterUsers);
        });

        // Auto refresh data every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>