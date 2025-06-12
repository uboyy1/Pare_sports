document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show corresponding pane
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
        });
    });
    
    // File input display
    const fileInput = document.getElementById('profile-picture-input');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
            document.getElementById('file-name').textContent = fileName;
        });
    }
    
    // Password validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordFeedback = document.getElementById('password-feedback');
    
    if (newPasswordInput && confirmPasswordInput && passwordFeedback) {
        function validatePasswords() {
            if (newPasswordInput.value !== confirmPasswordInput.value) {
                passwordFeedback.textContent = 'Password baru dan konfirmasi password tidak cocok';
                passwordFeedback.style.display = 'block';
                return false;
            } else if (newPasswordInput.value.length > 0 && newPasswordInput.value.length < 6) {
                passwordFeedback.textContent = 'Password harus minimal 6 karakter';
                passwordFeedback.style.display = 'block';
                return false;
            } else {
                passwordFeedback.style.display = 'none';
                return true;
            }
        }
        
        newPasswordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
        
        // Validate on form submit
        document.getElementById('password-form').addEventListener('submit', function(e) {
            if (!validatePasswords()) {
                e.preventDefault();
            }
        });
    }
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });
});