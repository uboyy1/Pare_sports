<div class="modal fade login-modal" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-sign-in-alt me-2"></i>MASUK KE AKUN ANDA</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="role-selector mb-3">
          <div class="btn-group w-100" role="group">
            <input type="radio" class="btn-check" name="role" id="roleUser" autocomplete="off" value="user" checked>
            <label class="btn btn-outline-secondary" for="roleUser">
              <i class="fas fa-user me-1"></i> Pengguna
            </label>

            <input type="radio" class="btn-check" name="role" id="roleManager" autocomplete="off" value="pengelola">
            <label class="btn btn-outline-secondary" for="roleManager">
              <i class="fas fa-user-tie me-1"></i> Pengelola
            </label>

            <input type="radio" class="btn-check" name="role" id="roleAdmin" autocomplete="off" value="admin">
            <label class="btn btn-outline-secondary" for="roleAdmin">
              <i class="fas fa-user-shield me-1"></i> Admin
            </label>
          </div>
        </div>

        <form id="loginForm" action="proses/proses_login.php" method="POST">
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

        <div class="text-center mt-3">
          <p class="mb-0">Belum punya akun? <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Daftar sekarang</a></p>
        </div>
      </div>
    </div>
  </div>
</div>