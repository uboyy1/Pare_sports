document.addEventListener("DOMContentLoaded", function() {
  // Check if user is logged in
  const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
  
  if (!currentUser || currentUser.role !== 'admin') {
    window.location.href = 'login.html';
    return;
  }
  
  // Set user info
  document.getElementById('userName').textContent = currentUser.name;
  
  // Sidebar toggle for mobile
  document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('active');
  });
  
  // Logout functionality
  document.getElementById('logoutBtn').addEventListener('click', function(e) {
    e.preventDefault();
    sessionStorage.removeItem('currentUser');
    window.location.href = 'login.html';
  });
  
  // Initialize chart
  const ctx = document.getElementById('bookingChart').getContext('2d');
  const bookingChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
      datasets: [{
        label: 'Jumlah Booking',
        data: [45, 60, 75, 82, 90, 110],
        backgroundColor: '#dc3545',
        borderColor: '#dc3545',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
});