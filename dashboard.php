<?php
// File: dashboard.php

// Mulai session dan cek autentikasi
session_start();

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Include koneksi database
require_once 'config/db.php';

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Jika user tidak ditemukan (mungkin dihapus admin)
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Update last login time
$update_stmt = $mysqli->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$update_stmt->bind_param("i", $user_id);
$update_stmt->execute();
$update_stmt->close();

// Set header keamanan
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User Dashboard">
    <title>Dashboard - <?php echo htmlspecialchars($user['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-5px);
        }
        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }
        .security-alert {
            border-left: 4px solid #dc3545;
        }
        .last-login {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">SecureAuth</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">Settings</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card profile-card">
                    <div class="card-body text-center">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=0D6EFD&color=fff&size=128" 
                             class="rounded-circle mb-3" alt="Profile" width="128" height="128">
                        <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="last-login">
                            Member since: <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </p>
                        <a href="profile.php" class="btn btn-outline-primary btn-sm">Edit Profile</a>
                    </div>
                </div>

                <!-- Security Card -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-shield-lock"></i> Security</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Password strength: Strong</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>2FA: Not enabled</span>
                        </div>
                        <a href="security.php" class="btn btn-sm btn-outline-secondary w-100 mt-2">Security Settings</a>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard -->
            <div class="col-lg-9">
                <!-- Welcome Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h4>
                        <p class="card-text">You last logged in on <?php echo date('l, F j, Y \a\t g:i A'); ?>.</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Your account is secure. Remember to logout when using public computers.
                        </div>
                    </div>
                </div>

                <!-- Security Alert -->
                <div class="alert security-alert alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Security Recommendation:</strong> Enable two-factor authentication for added security.
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-person text-primary" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-2">Profile</h5>
                                <p class="card-text">Update your personal information</p>
                                <a href="profile.php" class="btn btn-sm btn-outline-primary">Go to Profile</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-shield-lock text-success" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-2">Security</h5>
                                <p class="card-text">Manage password and security</p>
                                <a href="security.php" class="btn btn-sm btn-outline-success">Security Settings</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-gear text-warning" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-2">Settings</h5>
                                <p class="card-text">Customize your preferences</p>
                                <a href="settings.php" class="btn btn-sm btn-outline-warning">Account Settings</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-log">
                            <div class="list-group">
                                <div class="list-group-item border-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <small class="text-muted">Today, 10:30 AM</small>
                                    </div>
                                    <p class="mb-1">You logged in from <strong>Chrome on Windows</strong></p>
                                    <small class="text-muted">IP: <?php echo $_SERVER['REMOTE_ADDR']; ?></small>
                                </div>
                                <div class="list-group-item border-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <small class="text-muted">Yesterday, 2:15 PM</small>
                                    </div>
                                    <p class="mb-1">Password changed successfully</p>
                                </div>
                                <div class="list-group-item border-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <small class="text-muted">Monday, 9:00 AM</small>
                                    </div>
                                    <p class="mb-1">You logged in from <strong>Safari on iPhone</strong></p>
                                    <small class="text-muted">IP: 192.168.1.100</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> SecureAuth. All rights reserved.</p>
            <small class="text-muted">Last login: <?php echo date('F j, Y, g:i a'); ?></small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Logout setelah 30 menit inactivity
        let inactivityTime = function() {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            
            function logout() {
                window.location.href = 'logout.php?reason=inactivity';
            }
            
            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(logout, 1800000); // 30 menit
            }
        };
        
        inactivityTime();
        
        // Deteksi perubahan ukuran layar untuk Android desktop mode
        function handleResize() {
            const isMobile = window.innerWidth <= 768;
            const isAndroid = navigator.userAgent.toLowerCase().includes('android');
            
            if (isAndroid && !isMobile) {
                document.querySelector('.container').style.maxWidth = '90%';
                document.querySelectorAll('.card').forEach(card => {
                    card.style.fontSize = '0.95rem';
                });
            }
        }
        
        window.addEventListener('resize', handleResize);
        handleResize();
    </script>
</body>
</html>
