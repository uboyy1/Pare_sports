document.addEventListener("DOMContentLoaded", function() {
  // DOM Elements
  const roleOptions = document.querySelectorAll('.role-option');
  const loginForm = document.getElementById('loginForm');
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  
  // User data (in a real app, this would come from a database)
  const users = {
    user: [
      { email: 'user1@example.com', password: 'user123', name: 'John Doe' },
      { email: 'user2@example.com', password: 'user123', name: 'Jane Smith' }
    ],
    manager: [
      { email: 'manager1@example.com', password: 'manager123', name: 'Budi Santoso', venue: 'Grand Sulawesi' },
      { email: 'manager2@example.com', password: 'manager123', name: 'Ani Wijaya', venue: 'Surya Jaya Sports' }
    ],
    admin: [
      { email: 'admin@example.com', password: 'admin123', name: 'Admin PareSports' }
    ]
  };
  
  // Current selected role
  let currentRole = 'user';
  
  // Role selection
  roleOptions.forEach(option => {
    option.addEventListener('click', function() {
      roleOptions.forEach(opt => opt.classList.remove('active'));
      this.classList.add('active');
      currentRole = this.getAttribute('data-role');
    });
  });
  
  // Toggle password visibility
  togglePassword.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
  });
  
  // Login form submission
  loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('rememberMe').checked;
    
    // Find user in the selected role
    const user = users[currentRole].find(u => u.email === email && u.password === password);
    
    if (user) {
      // Store user data in sessionStorage
      const userData = {
        role: currentRole,
        email: user.email,
        name: user.name,
        venue: user.venue || null
      };
      
      sessionStorage.setItem('currentUser', JSON.stringify(userData));
      
      // Remember me functionality (using localStorage)
      if (rememberMe) {
        localStorage.setItem('rememberedEmail', email);
      } else {
        localStorage.removeItem('rememberedEmail');
      }
      
      // Redirect based on role
      switch(currentRole) {
        case 'user':
          window.location.href = 'beranda.html';
          break;
        case 'manager':
          window.location.href = 'dashboard-pengelola.html';
          break;
        case 'admin':
          window.location.href = 'dashboard-admin.html';
          break;
      }
    } else {
      alert('Email atau password salah! Silakan coba lagi.');
    }
  });
  
  // Check for remembered email
  const rememberedEmail = localStorage.getItem('rememberedEmail');
  if (rememberedEmail) {
    document.getElementById('email').value = rememberedEmail;
    document.getElementById('rememberMe').checked = true;
  }
});