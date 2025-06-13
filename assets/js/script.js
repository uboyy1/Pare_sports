// Fungsi toggle password yang diperbaiki
function setupPasswordToggle(passwordFieldId, toggleButtonId) {
  const toggleButton = document.getElementById(toggleButtonId);
  if (toggleButton) {
    toggleButton.addEventListener('click', function() {
      const passwordInput = document.getElementById(passwordFieldId);
      if (passwordInput) {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Update icon
        const icon = this.querySelector('i');
        if (icon) {
          if (type === 'password') {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
          } else {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
          }
        }
      }
    });
  }
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
  // Setup toggle password
  setupPasswordToggle('loginPassword', 'toggleLoginPassword');
  setupPasswordToggle('registerPassword', 'toggleRegisterPassword');
  setupPasswordToggle('registerConfirmPassword', 'toggleConfirmPassword');

  // Role selection in login form
  document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', function() {
      // Update hidden input value
      const roleValue = this.value;
      document.querySelector('input[name="role"][type="hidden"]').value = roleValue;
    });
  });

  // Form validation for register
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
      const password = document.getElementById('registerPassword').value;
      const confirmPassword = document.getElementById('registerConfirmPassword').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak sama!');
        document.getElementById('registerConfirmPassword').focus();
      }
    });
  }

  // Auto submit on sport select change
  const sportFilter = document.getElementById('sportFilter');
  if (sportFilter) {
    sportFilter.addEventListener('change', function() {
      document.getElementById('pageInput').value = 1;
      document.getElementById('filterForm').submit();
    });
  }

  // Search input handling
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        document.getElementById('pageInput').value = 1;
        document.getElementById('filterForm').submit();
      }
    });
  }

  // Pagination improvements
  document.querySelectorAll('.page-link').forEach(link => {
    link.addEventListener('click', function(e) {
      if (this.parentElement.classList.contains('disabled')) {
        e.preventDefault();
      }
    });
  });
});