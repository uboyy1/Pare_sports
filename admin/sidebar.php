<?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
<div class="bg-dark border-right" id="sidebar-wrapper">
    <div class="sidebar-heading text-white">
        <a href="../index.php" class="text-white text-decoration-none">
            <img src="../assets/img/Logo pare sports.png" alt="Logo" height="30" class="me-2">PARESPORTS
        </a>
    </div>
    <div class="list-group list-group-flush">
        <a href="dashboard_admin.php" class="list-group-item list-group-item-action <?= $currentPage == 'dashboard-admin.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="verifikasi-data.php" class="list-group-item list-group-item-action <?= $currentPage == 'verifikasi-data.php' ? 'active' : '' ?>">
            <i class="fas fa-user-check me-2"></i>Verifikasi Data
        </a>
        <a href="laporan-keuangan.php" class="list-group-item list-group-item-action <?= $currentPage == 'laporan-keuangan.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line me-2"></i>Laporan Keuangan
        </a>
        <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>
</div>