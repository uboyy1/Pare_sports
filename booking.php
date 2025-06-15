<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'config/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$lapangan_id = (int)$_GET['id'];
$lapangan = getLapanganById($conn, $lapangan_id);

if (!$lapangan) {
    header("Location: index.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isManager = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'pengelola';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($lapangan['nama_venue']) ?> | Paresports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/booking.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="hero" style="background-image: url('assets/img/bg.cont.png')">
  <nav>
    <a href="index.php">Home -</a>
    <a><?= htmlspecialchars($lapangan['nama_venue']) ?></a>
  </nav>
</div>

<?php include 'includes/terms_modal.php'; ?>
<?php include 'includes/login_modal.php'; ?>
<?php include 'includes/register_modal.php'; ?>

<main class="container">
  <section class="venue-header">
    <div class="venue-image-container">
      <img src="assets/img/<?= htmlspecialchars($lapangan['gambar']) ?>" alt="<?= htmlspecialchars($lapangan['nama_venue']) ?>" class="venue-img">
    </div>
    <div class="venue-bottom d-flex flex-wrap">
      <div class="venue-info">
        <h1><?= htmlspecialchars($lapangan['nama_venue']) ?></h1>

        <div class="rating">
          <?php
            $rating = $lapangan['rating'];
            for ($i = 1; $i <= 5; $i++) {
                if ($rating >= $i) echo '<i class="fas fa-star text-warning"></i>';
                else if ($rating > ($i - 1)) echo '<i class="fas fa-star-half-alt text-warning"></i>';
                else echo '<i class="far fa-star text-warning"></i>';
            }
          ?>
          <span class="rating-value ms-2"><?= number_format($rating, 1) ?> (<?= $lapangan['jumlah_review'] ?> reviews)</span>
        </div>

        <div class="venue-desc mt-3">
          <h3>Deskripsi</h3>
          <p><?= nl2br(htmlspecialchars($lapangan['deskripsi'] ?? 'Deskripsi tidak tersedia.')) ?></p>

          <h3>Alamat</h3>
          <?php if (!empty($lapangan['maps_link'])): ?>
              <p><a href="<?= htmlspecialchars($lapangan['maps_link']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($lapangan['alamat'] ?? 'Alamat tidak tersedia.') ?> <i class="fas fa-external-link-alt fa-xs"></i></a></p>
          <?php else: ?>
              <p><?= htmlspecialchars($lapangan['alamat'] ?? 'Alamat tidak tersedia.') ?></p>
          <?php endif; ?>

          <h3>Fasilitas</h3>
          <p><?= htmlspecialchars($lapangan['fasilitas'] ?? 'Fasilitas tidak tersedia.') ?></p>

          <h3>Aturan</h3>
          <p><?= nl2br(htmlspecialchars($lapangan['aturan'] ?? 'Aturan tidak tersedia.')) ?></p>
        </div>
      </div>

      <div class="venue-price">
        <p>Harga per jam</p>
        <h2>Rp <?= number_format($lapangan['harga'], 0, ',', '.') ?></h2>
        <div class="mt-3">
          <label for="bookingDate" class="form-label fw-bold">Pilih Tanggal:</label>
          <input type="date" class="form-control" id="bookingDate" name="bookingDate" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
        </div>
        <button id="checkScheduleBtn" class="check-availability mt-3"
                data-field-id="<?= $lapangan['id'] ?>"
                data-field-name="<?= htmlspecialchars($lapangan['nama_venue']) ?>"
                data-field-price="<?= $lapangan['harga'] ?>">
          Booking Sekarang
        </button>
      </div>
    </div>
  </section>
</main>

<!-- Modal Booking -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bookingModalLabel">Booking Lapangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-7">
            <h4 id="fieldName"></h4>
            <p id="fieldDate"></p>
            <div class="time-slots-container">
              <h5>Pilih Waktu Mulai</h5>
              <div class="time-slots"></div>
            </div>
            <div class="duration-selector mt-3">
              <h5>Durasi</h5>
              <select class="form-select" id="durationSelect">
                <option value="1" selected>1 Jam</option>
                <option value="2">2 Jam</option>
                <option value="3">3 Jam</option>
              </select>
            </div>
          </div>
          <div class="col-md-5">
            <div class="booking-summary">
              <h5>Ringkasan Booking</h5>
              <p><strong>Lapangan:</strong> <span id="summaryField">-</span></p>
              <p><strong>Tanggal:</strong> <span id="summaryDate">-</span></p>
              <p><strong>Waktu:</strong> <span id="summaryTime">-</span></p>
              <p><strong>Durasi:</strong> <span id="summaryDuration">-</span></p>
              <hr>
              <p class="fs-5"><strong>Total:</strong> <span id="summaryTotal" class="fw-bold">Rp 0</span></p>
              <div class="payment-methods mt-3">
                <h5>Metode Pembayaran</h5>
                <div class="payment-method" data-method="qris">
                  <span>QRIS</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="proceedPaymentBtn" disabled>Lanjutkan Pembayaran</button>
      </div>
    </div>
  </div>
</div>

<!-- Form Tersembunyi untuk Kirim Data ke payment.php -->
<form id="bookingForm" method="post" action="payment.php" style="display: none;">
  <input type="hidden" name="field_id" id="formFieldId">
  <input type="hidden" name="field_name" id="formFieldName">
  <input type="hidden" name="date" id="formDate">
  <input type="hidden" name="start_time" id="formStartTime">
  <input type="hidden" name="duration" id="formDuration">
  <input type="hidden" name="total_price" id="formTotalPrice">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false'; ?>;
  const bookingModalEl = document.getElementById('bookingModal');
  const bookingModal = new bootstrap.Modal(bookingModalEl);
  const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
  const dateInput = document.getElementById('bookingDate');
  const checkScheduleBtn = document.getElementById('checkScheduleBtn');
  const timeSlotsContainer = bookingModalEl.querySelector('.time-slots');
  const proceedPaymentBtn = bookingModalEl.querySelector('#proceedPaymentBtn');
  const durationSelect = bookingModalEl.querySelector('#durationSelect');

  let state = {
    fieldId: null,
    fieldName: null,
    fieldPrice: 0,
    time: null,
    paymentMethod: null,
    date: null,
  };

  function formatToRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
  }

  function formatDate(dateString) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
  }

  function resetBookingState() {
    state = { fieldId: null, fieldName: null, fieldPrice: 0, time: null, paymentMethod: null, date: null };
    bookingModalEl.querySelectorAll('.time-slot.selected').forEach(s => s.classList.remove('selected'));
    bookingModalEl.querySelectorAll('.payment-method.selected').forEach(m => m.classList.remove('selected'));
    proceedPaymentBtn.disabled = true;
  }

  function updateBookingSummary() {
    if (!state.time || !state.fieldId) return;

    const duration = parseInt(durationSelect.value);
    const totalPrice = state.fieldPrice * duration;
    const [hours, minutes] = state.time.split(':').map(Number);
    const endTime = `${String(hours + duration).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;

    bookingModalEl.querySelector('#summaryTime').textContent = `${state.time} - ${endTime}`;
    bookingModalEl.querySelector('#summaryDuration').textContent = `${duration} Jam`;
    bookingModalEl.querySelector('#summaryTotal').textContent = formatToRupiah(totalPrice);

    if (state.paymentMethod) {
      proceedPaymentBtn.disabled = false;
    }
  }

  async function fetchAndRenderTimeSlots() {
    if (!state.fieldId || !state.date) return;

    timeSlotsContainer.innerHTML = `<div class="text-center py-3"><div class="spinner-border text-danger" role="status"></div><p class="mt-2">Memuat jadwal...</p></div>`;

    try {
      const response = await fetch(`proses/get_jadwal.php?lapangan_id=${state.fieldId}&date=${state.date}`);
      const data = await response.json();

      timeSlotsContainer.innerHTML = '';
      if (data.all_slots.length === 0) {
        timeSlotsContainer.innerHTML = '<p class="text-center text-muted">Jadwal tidak tersedia.</p>';
        return;
      }

      data.all_slots.forEach(time => {
        const isBooked = data.booked_slots.includes(time);
        const slotDiv = document.createElement('div');
        slotDiv.className = `time-slot ${isBooked ? 'booked' : ''}`;
        slotDiv.textContent = time;

        if (!isBooked) {
          slotDiv.dataset.time = time;
          slotDiv.addEventListener('click', function() {
            bookingModalEl.querySelectorAll('.time-slot.selected').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            state.time = this.dataset.time;
            updateBookingSummary();
          });
        }

        timeSlotsContainer.appendChild(slotDiv);
      });

    } catch (error) {
      console.error('Gagal mengambil jadwal:', error);
      timeSlotsContainer.innerHTML = `<div class="alert alert-danger">Gagal memuat jadwal. Silakan coba lagi.</div>`;
    }
  }

  checkScheduleBtn.addEventListener('click', function() {
    if (!isLoggedIn) {
      loginModal.show();
      return;
    }

    resetBookingState();

    state.fieldId = this.dataset.fieldId;
    state.fieldName = this.dataset.fieldName;
    state.fieldPrice = parseFloat(this.dataset.fieldPrice);
    state.date = dateInput.value;

    bookingModalEl.querySelector('#fieldName').textContent = state.fieldName;
    bookingModalEl.querySelector('#fieldDate').textContent = formatDate(state.date);
    bookingModalEl.querySelector('#summaryField').textContent = state.fieldName;
    bookingModalEl.querySelector('#summaryDate').textContent = formatDate(state.date);

    fetchAndRenderTimeSlots();
    bookingModal.show();
  });

  durationSelect.addEventListener('change', updateBookingSummary);

  bookingModalEl.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
      bookingModalEl.querySelectorAll('.payment-method.selected').forEach(m => m.classList.remove('selected'));
      this.classList.add('selected');
      state.paymentMethod = this.dataset.method;
      updateBookingSummary();
    });
  });

  proceedPaymentBtn.addEventListener('click', function() {
    if (!state.time || !state.paymentMethod) {
      alert('Silakan pilih waktu dan metode pembayaran.');
      return;
    }

    document.getElementById('formFieldId').value = state.fieldId;
    document.getElementById('formFieldName').value = state.fieldName;
    document.getElementById('formDate').value = state.date;
    document.getElementById('formStartTime').value = state.time;
    document.getElementById('formDuration').value = durationSelect.value;
    document.getElementById('formTotalPrice').value = state.fieldPrice * parseInt(durationSelect.value);

    document.getElementById('bookingForm').submit();
  });
});
</script>
</body>
</html>
