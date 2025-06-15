<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login status and user information
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : '';
$username = $is_logged_in ? $_SESSION['username'] : '';
$currentPage = basename($_SERVER['PHP_SELF']);
$is_profile_page = ($currentPage === 'profil.php');
$is_dashboard_page = (
    $currentPage === 'dashboard-admin.php' || 
    $currentPage === 'dashboard-pengelola.php' || 
    ($currentPage === 'index.php' && $user_role === 'user')
);
?>

<style>
    /* Custom dropdown styling */
    .user-dropdown .dropdown-toggle {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #495057;
        transition: all 0.3s ease;
    }
    
    .user-dropdown .dropdown-toggle:hover {
        background-color: #e9ecef;
        border-color: #ced4da;
        color: #212529;
        transform: translateY(-1px);
    }
    
    .user-dropdown .dropdown-item {
        color: #495057;
        transition: all 0.2s;
        padding: 8px 16px;
    }
    
    .user-dropdown .dropdown-item:hover,
    .user-dropdown .dropdown-item:focus {
        background-color: #dc3545;
        color: white !important;
    }
    
    .user-dropdown .dropdown-item.text-danger:hover {
        background-color: #dc3545;
        color: white !important;
    }
    
    .navbar {
        background-color: #ffffff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        transition: all 0.3s ease;
    }
    
    .btn-danger:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
        transform: translateY(-1px);
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light p-3">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
      <img src="assets/img/Logo pare sports.png" alt="Logo" height="40" class="me-2">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="ms-auto d-flex align-items-center">
        <?php if ($is_logged_in): ?>
        <div class="user-dropdown dropdown me-3" id="userDropdownContainer">
          <a class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown">
            <i class="fas fa-user-circle me-2"></i>
            <span id="navbarUsername" class="text-truncate" style="max-width: 120px;"><?= htmlspecialchars($username) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow" id="userDropdownMenu">
            <?php if ($is_dashboard_page): ?>
                <!-- Show Profile and Booking on dashboard pages -->
                <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                <?php if ($user_role == 'user' && $currentPage !== 'booking-saya.php'): ?>
                <li><a class="dropdown-item" href="booking-saya.php"><i class="fas fa-calendar-alt me-2"></i>Booking Saya</a></li>
                <?php endif; ?>
                
            <?php elseif ($is_profile_page): ?>
                <!-- Show Dashboard and Booking on profile page -->
                <li>
                    <a class="dropdown-item" href="<?= 
                        ($user_role == 'admin') ? 'admin/dashboard-admin.php' : 
                        (($user_role == 'pengelola') ? 'pengelola/dashboard-pengelola.php' : 'index.php')
                    ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <?php if ($user_role == 'user' && $currentPage !== 'booking-saya.php'): ?>
                <li><a class="dropdown-item" href="booking-saya.php"><i class="fas fa-calendar-alt me-2"></i>Booking Saya</a></li>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Show all options on other pages -->
                <li>
                    <a class="dropdown-item" href="<?= 
                        ($user_role == 'admin') ? 'admin/dashboard-admin.php' : 
                        (($user_role == 'pengelola') ? 'pengelola/dashboard-pengelola.php' : 'index.php')
                    ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                <?php if ($user_role == 'user' && $currentPage !== 'booking-saya.php'): ?>
                <li><a class="dropdown-item" href="booking-saya.php"><i class="fas fa-calendar-alt me-2"></i>Booking Saya</a></li>
                <?php endif; ?>
            <?php endif; ?>
            
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </div>
        
        <?php if ($user_role == 'admin' && $currentPage !== 'dashboard-admin.php'): ?>
        <a href="admin/dashboard-admin.php" class="btn btn-danger me-2">
          <i class="fas fa-user-shield me-1"></i>Admin
        </a>
        <?php elseif ($user_role == 'pengelola' && $currentPage !== 'dashboard-pengelola.php'): ?>
        <a href="pengelola/dashboard-pengelola.php" class="btn btn-warning me-2">
          <i class="fas fa-user-tie me-1"></i>Pengelola
        </a>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="auth-buttons" id="authButtons">
          <a href="#" class="btn btn-outline-secondary me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
          <a href="#" class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>