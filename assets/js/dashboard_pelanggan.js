// Toggle password visibility
document.getElementById('toggleLoginPassword')?.addEventListener('click', function() {
  const passwordInput = document.getElementById('loginPassword');
  const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordInput.setAttribute('type', type);
  this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
});

document.getElementById('toggleRegisterPassword')?.addEventListener('click', function() {
  const passwordInput = document.getElementById('registerPassword');
  const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordInput.setAttribute('type', type);
  this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
});

// Role selection in login form
document.querySelectorAll('input[name="role"]')?.forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelector('input[name="role"][type="hidden"]').value = this.id.replace('role', '').toLowerCase();
  });
});

// Form validation
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
  const password = document.getElementById('registerPassword').value;
  const confirmPassword = document.getElementById('registerConfirmPassword').value;
  
  if (password !== confirmPassword) {
    e.preventDefault();
    alert('Password dan konfirmasi password tidak sama!');
  }
});

// Filter handling
document.getElementById('sportFilter')?.addEventListener('change', function() {
  document.getElementById('pageInput').value = 1;
  document.getElementById('filterForm').submit();
});

// Search input handling
document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    document.getElementById('pageInput').value = 1;
    document.getElementById('filterForm').submit();
  }
});

// Pagination improvements
document.querySelectorAll('.page-link')?.forEach(link => {
  link.addEventListener('click', function(e) {
    if (this.parentElement.classList.contains('disabled')) {
      e.preventDefault();
    }
  });
});

// Reset filter via link
document.querySelector('a.btn-outline-secondary')?.addEventListener('click', function(e) {
  e.preventDefault();
  window.location.href = 'index.php';
});