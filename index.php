<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

$sport_filter = isset($_GET['sport']) ? $_GET['sport'] : 'all';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination settings
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($current_page - 1) * $per_page;

// Get total number of venues
$total_lapangan = countLapangan($conn, $sport_filter, $search_query);
$total_pages = ceil($total_lapangan / $per_page);

// Validate current page
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// Get venues for current page
$lapangan = getLapangan($conn, $sport_filter, $search_query, $per_page, $offset);

$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : '';
$username = $is_logged_in ? $_SESSION['username'] : '';

$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : '';
$username = $is_logged_in ? $_SESSION['username'] : '';

// Tambahkan profile_picture ke session jika belum ada
if ($is_logged_in && !isset($_SESSION['profile_picture'])) {
    $query = "SELECT profile_picture FROM users WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $_SESSION['profile_picture'] = $user['profile_picture'];
}
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
  <?php include 'includes/header.php'; ?>

  <!-- Hero Section -->
  <section class="hero d-flex flex-column justify-content-center align-items-center text-center">
    <h1 class="fw-bold fst-italic">BOOKING LAPANGAN ONLINE PAREPARE</h1>
    <?php if (!$is_logged_in): ?>
    <button class="btn btn-yellow mt-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar Sekarang</button>
    <?php endif; ?>
  </section>

  <?php include 'includes/terms_modal.php'; ?>
  <?php include 'includes/login_modal.php'; ?>
  <?php include 'includes/register_modal.php'; ?>

  <!-- Filter/Search -->
  <div class="container my-4">
    <form method="GET" action="index.php" id="filterForm">
      <input type="hidden" name="page" value="1" id="pageInput">
      <div class="row g-2">
        <div class="col-md-4">
          <select class="form-select" id="sportFilter" name="sport">
            <option value="all" <?= $sport_filter == 'all' ? 'selected' : '' ?>>Semua Olahraga</option>
            <option value="futsal" <?= $sport_filter == 'futsal' ? 'selected' : '' ?>>Futsal</option>
            <option value="badminton" <?= $sport_filter == 'badminton' ? 'selected' : '' ?>>Badminton</option>
            <option value="basket" <?= $sport_filter == 'basket' ? 'selected' : '' ?>>Basket</option>
            <option value="tenis" <?= $sport_filter == 'tenis' ? 'selected' : '' ?>>Tenis</option>
            <option value="minisoccer" <?= $sport_filter == 'minisoccer' ? 'selected' : '' ?>>Mini Soccer</option>
          </select>
        </div>
        <div class="col-md-6">
          <input type="text" class="form-control search-input" placeholder="Cari Nama Venue" id="searchInput" name="search" value="<?= htmlspecialchars($search_query) ?>" />
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-search w-100" id="searchButton">Cari Venue</button>
        </div>
      </div>
    </form>

    <!-- Rekomendasi -->
    <h5 class="mt-5 mb-3 fw-bold">
      <?php if ($sport_filter != 'all'): ?>
        Lapangan <span class="text-danger"><?= ucfirst($sport_filter) ?></span>
      <?php else: ?>
        Rekomendasi <span class="text-danger">Lapangan</span>
      <?php endif; ?>
    </h5>
    
    <!-- Grid View -->
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
              <img src="assets/img/<?= htmlspecialchars($item['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['nama_venue']) ?>" style="height: 180px; object-fit: cover;">

            </div>
            <div class="card-body d-flex flex-column">
              <h6 class="card-title"><?= htmlspecialchars($item['nama_venue']) ?></h6>
              <div class="mb-2">
                <div class="d-flex align-items-center">
                  <?php
                  $rating = $item['rating'];
                  $fullStars = floor($rating);
                  $halfStar = ($rating - $fullStars) >= 0.5;
                  $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                  ?>
                  
                  <!-- Bintang penuh -->
                  <?php for ($i = 0; $i < $fullStars; $i++): ?>
                    <i class="fas fa-star text-warning"></i>
                  <?php endfor; ?>
                  
                  <!-- Bintang setengah -->
                  <?php if ($halfStar): ?>
                    <i class="fas fa-star-half-alt text-warning"></i>
                  <?php endif; ?>
                  
                  <!-- Bintang kosong -->
                  <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                    <i class="far fa-star text-warning"></i>
                  <?php endfor; ?>
                  
                  <span class="ms-2 text-muted small">(<?= $item['jumlah_review'] ?>)</span>
                </div>
              </div>
              <div class="d-flex align-items-start mb-2">
                <div>
                  <div class="small fw-semibold">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                    <?= htmlspecialchars($item['alamat']) ?>
                  </div>
                </div>
              </div>
              <p class="card-text text-muted small mt-auto">
                Mulai: <strong>Rp<?= number_format($item['harga'], 0, ',', '.') ?></strong>
              </p>
              <a href="booking.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm w-100 mt-2">
                Booking Sekarang
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center" id="pagination">
        <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $current_page - 1 ?>&sport=<?= $sport_filter ?>&search=<?= urlencode($search_query) ?>" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
        
        <?php 
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $start_page + 4);
        
        if ($end_page - $start_page < 4) {
            $start_page = max(1, $end_page - 4);
        }
        
        for ($i = $start_page; $i <= $end_page; $i++): ?>
          <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&sport=<?= $sport_filter ?>&search=<?= urlencode($search_query) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        
        <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $current_page + 1 ?>&sport=<?= $sport_filter ?>&search=<?= urlencode($search_query) ?>" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
  </div>

  <!-- Banner Promo -->
  <div class="container banner my-5 text-center">
    <img src="assets/img/Gambar1.jpg" class="img-fluid" alt="Promo">
  </div>
  
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/login_modal.php'; ?>
  <?php include 'includes/register_modal.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/script.js"></script>
</body>
</html>