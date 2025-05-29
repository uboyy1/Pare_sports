document.addEventListener("DOMContentLoaded", function() {
  // Check if user is logged in
  const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
  
  if (!currentUser || currentUser.role !== 'manager') {
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
  
  // Display venue info if available
  if (currentUser.venue) {
    const venueInfo = document.createElement('div');
    venueInfo.className = 'small text-muted';
    venueInfo.textContent = `Lapangan: ${currentUser.venue}`;
    document.querySelector('.user-profile div').appendChild(venueInfo);
  }
});