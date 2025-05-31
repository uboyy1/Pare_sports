<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>BUAT AKUN BARU</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="registerForm" action="proses/proses_register.php" method="POST">
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