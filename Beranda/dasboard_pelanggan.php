<?php
session_start();
require_once 'config/database.php';

// Fungsi untuk mendapatkan data lapangan
function getLapangan($conn, $sport = 'all', $search = '') {
    $query = "SELECT * FROM lapangan";
    
    $conditions = [];
    $params = [];
    
    if ($sport != 'all') {
        $conditions[] = "jenis_olahraga = ?";
        $params[] = $sport;
    }
    
    if (!empty($search)) {
        $conditions[] = "nama_venue LIKE ?";
        $params[] = "%$search%";
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $query .= " ORDER BY rating DESC LIMIT 6";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Cek filter olahraga
$sport_filter = isset($_GET['sport']) ? $_GET['sport'] : 'all';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Dapatkan data lapangan
$lapangan = getLapangan($conn, $sport_filter, $search_query);

// Cek apakah user sudah login
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : '';
$username = $is_logged_in ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Booking Lapangan Online Parepare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard_pelanggan.css">
</head>
<body>
  
  <!-- Navbar -->
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
          <!-- Dropdown User (muncul setelah login) -->
          <?php if ($is_logged_in): ?>
          <div class="user-dropdown dropdown me-3" id="userDropdownContainer">
            <a class="btn btn-outline-secondary dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle me-1"></i>
              <span id="navbarUsername"><?php echo htmlspecialchars($username); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu">
              <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Profil</a></li>
              <!-- Item Booking Saya hanya untuk role user -->
              <?php if ($user_role == 'user'): ?>
              <li id="bookingMenuItem"><a class="dropdown-item" href="booking-saya.php"><i class="fas fa-calendar-alt me-2"></i>Booking Saya</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="logout.php" id="logoutButton"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </div>
          
          <!-- Tombol Admin/Pengelola -->
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
          <!-- Tombol Auth (muncul ketika belum login) -->
          <div class="auth-buttons" id="authButtons">
            <a href="#" class="btn btn-outline-secondary me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
            <a href="#" class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero d-flex flex-column justify-content-center align-items-center text-center">
    <h1 class="fw-bold fst-italic">BOOKING LAPANGAN ONLINE PAREPARE</h1>
    <?php if (!$is_logged_in): ?>
    <button class="btn btn-yellow mt-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar Sekarang</button>
    <?php endif; ?>
  </section>

  <!-- Filter/Search -->
  <div class="container my-4">
    <form method="GET" action="index.php">
      <div class="row g-2">
        <div class="col-md-4">
          <select class="form-select" id="sportFilter" name="sport">
            <option value="all" <?php echo $sport_filter == 'all' ? 'selected' : ''; ?>>Semua Olahraga</option>
            <option value="futsal" <?php echo $sport_filter == 'futsal' ? 'selected' : ''; ?>>Futsal</option>
            <option value="badminton" <?php echo $sport_filter == 'badminton' ? 'selected' : ''; ?>>Badminton</option>
            <option value="basket" <?php echo $sport_filter == 'basket' ? 'selected' : ''; ?>>Basket</option>
            <option value="tenis" <?php echo $sport_filter == 'tenis' ? 'selected' : ''; ?>>Tenis</option>
            <option value="minisoccer" <?php echo $sport_filter == 'minisoccer' ? 'selected' : ''; ?>>Mini Soccer</option>
          </select>
        </div>
        <div class="col-md-6">
          <input type="text" class="form-control search-input" placeholder="Cari Nama Venue" id="searchInput" name="search" value="<?php echo htmlspecialchars($search_query); ?>" />
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-search" id="searchButton">Cari Venue</button>
        </div>
      </div>
    </form>

    <!-- Rekomendasi -->
    <h5 class="mt-5 mb-3 fw-bold">Rekomendasi <span class="text-danger">Lapangan</span></h5>
    <div class="row g-3" id="venueContainer">
      <?php if (empty($lapangan)): ?>
        <div class="col-12 text-center py-5">
          <h6 class="text-muted">Tidak ada lapangan yang ditemukan</h6>
          <a href="index.php" class="btn btn-outline-secondary">Reset Filter</a>
        </div>
      <?php else: ?>
        <?php foreach ($lapangan as $item): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm venue-card">
            <div class="position-relative">
              <img src="assets/img/<?php echo htmlspecialchars($item['gambar']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['nama_venue']); ?>" style="height: 180px; object-fit: cover;">
            </div>
            <div class="card-body d-flex flex-column">
              <h6 class="card-title"><?php echo htmlspecialchars($item['nama_venue']); ?></h6>
              <div class="mb-2">
                <div class="star-rating">
                  <div class="stars-back">★★★★★</div>
                  <div class="stars-front" style="width: <?php echo ($item['rating'] / 5) * 100; ?>%;">★★★★★</div>
                </div>
                <span class="text-muted small">(<?php echo $item['jumlah_review']; ?>)</span>
              </div>
              <div class="d-flex align-items-start mb-2">
                <div>
                  <div class="small fw-semibold"><i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo htmlspecialchars($item['alamat']); ?></div>
                </div>
              </div>
              <p class="card-text text-muted small mt-auto">Mulai: <strong>Rp<?php echo number_format($item['harga'], 0, ',', '.'); ?></strong></p>
              <a href="booking.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm w-100 mt-2">
                Booking Sekarang
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <nav aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center" id="pagination">
        <li class="page-item disabled">
          <a class="page-link" href="#" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item">
          <a class="page-link" href="#" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>

  <!-- Banner Promo -->
  <div class="container banner my-5 text-center">
    <img src="assets/img/Gambar1.jpg" class="img-fluid" alt="Promo">
  </div>

  <!-- Footer -->
  <footer class="footer py-4 bg-dark text-white">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h6>PARESPORTS</h6>
          <p>Jl. Pemuda, Parepare, Sulawesi Selatan</p>
        </div>
        <div class="col-md-4">
          <h6>Perusahaan</h6>
          <p><a href="#" class="text-white-50">Tentang Kami</a></p>
          <p><a href="#" class="text-white-50">Ekosistem</a></p>
        </div>
        <div class="col-md-4">
          <h6>Hubungi Kami</h6>
          <p><a href="#" class="text-white-50">Kontak</a></p>
        </div>
      </div>
      <div class="text-center mt-3">
        <small class="text-white-50">© <?php echo date('Y'); ?> PARESPORTS. All Rights Reserved.</small>
      </div>
    </div>
  </footer>

  <!-- Login Modal -->
  <div class="modal fade login-modal" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-sign-in-alt me-2"></i>MASUK KE AKUN ANDA</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Role Selector -->
          <div class="role-selector mb-3">
            <div class="btn-group w-100" role="group">
              <input type="radio" class="btn-check" name="role" id="roleUser" autocomplete="off" checked>
              <label class="btn btn-outline-secondary" for="roleUser">
                <i class="fas fa-user me-1"></i> Pengguna
              </label>

              <input type="radio" class="btn-check" name="role" id="roleManager" autocomplete="off">
              <label class="btn btn-outline-secondary" for="roleManager">
                <i class="fas fa-user-tie me-1"></i> Pengelola
              </label>

              <input type="radio" class="btn-check" name="role" id="roleAdmin" autocomplete="off">
              <label class="btn btn-outline-secondary" for="roleAdmin">
                <i class="fas fa-user-shield me-1"></i> Admin
              </label>
            </div>
          </div>

          <!-- Login Form -->
          <form id="loginForm" action="proses_login.php" method="POST">
            <input type="hidden" name="role" value="user">
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Masukkan email anda" required>
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Masukkan password" required>
                <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="loginRemember" name="remember">
              <label class="form-check-label" for="loginRemember">Ingat saya</label>
            </div>
            <button type="submit" class="btn btn-danger w-100 mb-3">
              <i class="fas fa-sign-in-alt me-2"></i>MASUK
            </button>
            <div class="text-center">
              <a href="forgot-password.php" class="text-danger">Lupa password?</a>
            </div>
          </form>

          <div class="divider">ATAU</div>

          <div class="text-center">
            <p class="mb-3">Belum punya akun? <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Daftar sekarang</a></p>
            <button class="btn btn-outline-danger w-100 mb-2">
              <i class="fab fa-google me-2"></i>Masuk dengan Google
            </button>
            <button class="btn btn-outline-primary w-100">
              <i class="fab fa-facebook-f me-2"></i>Masuk dengan Facebook
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Register Modal -->
  <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>BUAT AKUN BARU</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="registerForm" action="proses_register.php" method="POST">
            <div class="mb-3">
              <label for="registerName" class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" id="registerName" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="mb-3">
              <label for="registerEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Masukkan email" required>
            </div>
            <div class="mb-3">
              <label for="registerPassword" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Buat password" required minlength="8">
                <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <small class="text-muted">Minimal 8 karakter</small>
            </div>
            <div class="mb-3">
              <label for="registerConfirmPassword" class="form-label">Konfirmasi Password</label>
              <input type="password" class="form-control" id="registerConfirmPassword" name="confirm_password" placeholder="Ulangi password" required>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="registerAgree" name="agree" required>
              <label class="form-check-label" for="registerAgree">Saya menyetujui <a href="#">Syarat dan Ketentuan</a></label>
            </div>
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-user-plus me-2"></i>DAFTAR SEKARANG
            </button>
          </form>

          <div class="text-center mt-3">
            <p>Sudah punya akun? <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Masuk disini</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle password visibility
    document.getElementById('toggleLoginPassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('loginPassword');
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });

    document.getElementById('toggleRegisterPassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('registerPassword');
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });

    // Role selection in login form
    document.querySelectorAll('input[name="role"]').forEach(radio => {
      radio.addEventListener('change', function() {
        document.querySelector('input[name="role"][type="hidden"]').value = this.id.replace('role', '').toLowerCase();
      });
    });

    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      const password = document.getElementById('registerPassword').value;
      const confirmPassword = document.getElementById('registerConfirmPassword').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak sama!');
      }
    });

    // Filter lapangan
    document.getElementById('sportFilter').addEventListener('change', function() {
      this.form.submit();
    });
  </script>
</body>
</html>