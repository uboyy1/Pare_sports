<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../index.php');
    exit();
}

$manager_id = $_SESSION['user_id'];
$lapangan_list = getManagedFields($conn, $manager_id);

$selected_lapangan_id = $_GET['lapangan_id'] ?? ($lapangan_list[0]['id'] ?? null);
$selected_date = $_GET['tanggal'] ?? date('Y-m-d');

$bookings = [];
if ($selected_lapangan_id) {
    $bookings = getBookingsForFieldOnDate($conn, $selected_lapangan_id, $selected_date);
}

$page_title = "Jadwal Lapangan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Paresports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-manager.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        <div id="page-content-wrapper">
             <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1"><?= $page_title ?></span>
                    <div class="collapse navbar-collapse">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['username']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="../profil.php">Profil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <main class="container-fluid p-4">
                <h1 class="mb-4"><?= $page_title ?></h1>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="lapangan_id" class="form-label">Pilih Lapangan</label>
                                <select name="lapangan_id" id="lapangan_id" class="form-select">
                                     <?php if (empty($lapangan_list)): ?>
                                        <option>Anda belum punya lapangan</option>
                                    <?php else: ?>
                                        <?php foreach($lapangan_list as $lapangan): ?>
                                            <option value="<?= $lapangan['id'] ?>" <?= $selected_lapangan_id == $lapangan['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lapangan['nama_venue']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="tanggal" class="form-label">Pilih Tanggal</label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= $selected_date ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-danger w-100">Tampilkan Jadwal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Jadwal untuk <?= date('d F Y', strtotime($selected_date)) ?></h5>
                    </div>
                     <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Pemesan</th>
                                    </tr>
                                </thead>
                                    <tbody>
                                        <?php
                                        for ($h = 7; $h <= 23; $h++) {
                                            $time_slot = str_pad($h, 2, '0', STR_PAD_LEFT) . ":00:00";
                                            $booked_info = null;

                                            // Loop untuk memeriksa setiap pemesanan
                                            foreach ($bookings as $booking) {
                                                // Konversi jam ke integer untuk perbandingan
                                                $current_hour = (int)substr($time_slot, 0, 2);
                                                $start_hour = (int)substr($booking['jam_mulai'], 0, 2);
                                                $end_hour = (int)substr($booking['jam_selesai'], 0, 2);

                                                // Cek apakah jam saat ini berada di dalam rentang pemesanan
                                                if ($current_hour >= $start_hour && $current_hour < $end_hour) {
                                                    $booked_info = $booking;
                                                    break; // Keluar dari loop jika sudah ditemukan jadwal yang sesuai
                                                }
                                            }

                                            echo "<tr>";
                                            echo "<td>" . date('H:i', strtotime($time_slot)) . "</td>";
                                            if ($booked_info) {
                                                echo '<td class="table-danger">Dipesan</td>';
                                                echo "<td>" . htmlspecialchars($booked_info['nama_user']) . "</td>";
                                            } else {
                                                echo '<td class="table-success">Tersedia</td>';
                                                echo "<td>-</td>";
                                            }
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>