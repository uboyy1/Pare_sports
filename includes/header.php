<nav class="navbar navbar-expand-lg navbar-light shadow-sm p-3">
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
          <a class="btn btn-outline-secondary dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown">
            <i class="fas fa-user-circle me-1"></i>
            <span id="navbarUsername"><?= htmlspecialchars($username) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu">
            <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Profil</a></li>
            <?php if ($user_role == 'user'): ?>
            <li id="bookingMenuItem"><a class="dropdown-item" href="booking-saya.php"><i class="fas fa-calendar-alt me-2"></i>Booking Saya</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php" id="logoutButton"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </div>
        
        <?php if ($user_role == 'admin'): ?>
        <a href="admin/dashboard-admin.php" class="btn btn-danger me-2" id="adminButton">
          <i class="fas fa-user-shield me-1"></i>Admin
        </a>
        <?php elseif ($user_role == 'pengelola'): ?>
        <a href="pengelola/dashboard-pengelola.php" class="btn btn-warning me-2" id="managerButton">
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