<?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
<div class="bg-dark border-right" id="sidebar-wrapper">
    <div class="sidebar-heading text-white">
        <a href="../index.php" class="text-white text-decoration-none">
            <img src="../assets/img/Logo pare sports.png" alt="Logo" height="35" class="me-2">
        </a>
    </div>
    <div class="list-group list-group-flush">
        <a href="dashboard-pengelola.php" class="list-group-item list-group-item-action <?= $currentPage == 'dashboard-pengelola.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="kelola-lapangan.php" class="list-group-item list-group-item-action <?= in_array($currentPage, ['kelola-lapangan.php', 'form-lapangan.php']) ? 'active' : '' ?>">
            <i class="fas fa-futbol me-2"></i>Kelola Lapangan
        </a>
        <a href="jadwal-lapangan.php" class="list-group-item list-group-item-action <?= $currentPage == 'jadwal-lapangan.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt me-2"></i>Jadwal Lapangan
        </a>
        <a href="laporan-keuangan.php" class="list-group-item list-group-item-action <?= $currentPage == 'laporan-keuangan.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar me-2"></i>Laporan Keuangan
        </a>
        <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>
</div>