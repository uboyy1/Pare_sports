// Venue Class
class Venue {
  constructor(id, name, image, price, sports, rating, reviewCount, location) {
    this.id = id;
    this.name = name;
    this.image = image;
    this.price = price;
    this.sports = sports;
    this.rating = rating;
    this.reviewCount = reviewCount;
    this.location = location;
  }

  static generateStarRating(rating) {
    const maxStars = 5;
    let starsHTML = '<div class="star-rating" style="display: inline-block; position: relative; font-size: 1rem;">';

    // Background stars (empty)
    starsHTML += '<div class="stars-back" style="color: #ddd;">';
    for (let i = 0; i < maxStars; i++) {
      starsHTML += '★';
    }
    starsHTML += '</div>';

    // Foreground stars (filled)
    starsHTML += `<div class="stars-front" style="color: #ffc107; position: absolute; top: 0; left: 0; width: ${(rating / maxStars) * 100}%; overflow: hidden; white-space: nowrap;">`;
    for (let i = 0; i < maxStars; i++) {
      starsHTML += '★';
    }
    starsHTML += '</div>';

    starsHTML += '</div>';
    return starsHTML;
  }

  static formatSportsDisplay(sports) {
    if (Array.isArray(sports)) {
      return sports.map(s => {
        if (s === 'mini-soccer') return 'Mini Soccer';
        return s.charAt(0).toUpperCase() + s.slice(1);
      }).join(' + ');
    }
    if (sports === 'mini-soccer') return 'Mini Soccer';
    return sports.charAt(0).toUpperCase() + sports.slice(1);
  }

  static generateLocationHTML(location, distance) {
    return `
      <div class="d-flex align-items-start mb-2">
        <div>
          <div class="small fw-semibold"><i class="fas fa-map-marker-alt text-danger me-1"></i>${location}</div>
          ${distance ? `<div class="small text-muted">${distance}</div>` : ''}
        </div>
      </div>
    `;
  }
}

// Venue Data
const venues = [
  new Venue(1, "Grand Sulawesi - Futsal", "Grand Futsal.png", "Rp120.000", ["futsal"], 4.3, 65, "Jl. Jend Jl. Moh. Yusuf, Lompoe, Kec. Bacukiki, Kota Parepare"),
  new Venue(2, "Surya Jaya Sports - Badminton", "GOR.jpg", "Rp40.000", ["badminton"], 4.8, 23, "Watang Soreang, Kec. Soreang, Kota Parepare"),
  new Venue(3, "Jati Diri - Basket", "Jati Diri Basket.jpg", "Rp150.000", ["basket"], 3.9, 42, "Mallusetasi, Kec. Ujung, Kota Parepare"),
  new Venue(4, "Pasar Kuliner - Badminton", "Kuliner.jpg", "Rp25.000", ["badminton"], 3.6, 100, "Labukkang, Kec. Ujung, Kota Parepare"),
  new Venue(5, "Masagenae - Bandminton", "Masagenae.jpg", "Rp35.000", ["badminton"], 5.0, 7, "Kp. Pisang, Kec. Soreang, Kota Parepare"),
  new Venue(6, "Galaxy Futsal Center - Futsal", "Galaxy.jpg", "Rp150.000", ["futsal"], 4.5, 161, "Jl. Pelita No.Utara, Lakessi, Kec. Soreang, Kota Parepare"),
  new Venue(7, "Pelti - Tenis", "Pelti.jpg", "Rp200.000", ["tenis"], 5.0, 1, "Jl. Sultan Hasanuddin No.4, Mallusetasi, Kec. Ujung, Kota Parepare"),
  new Venue(8, "R57 - Mini Soccer", "R57.jpg", "Rp850.000", ["minisoccer"], 4.7, 57, "Watang Soreang, Kec. Soreang, Kota Parepare"),
  new Venue(9, "Sansiro - Futsal", "Sansiro.jpg", "Rp120.000", ["futsal"], 4.1, 62, "Tiro Sompe, Kec. Bacukiki Bar., Kota Parepare"),
  new Venue(10, "Bayam - Futsal", "Bayam.jpg", "Rp100.000", ["futsal"], 3.8, 6, "Labukkang, Kec. Ujung, Kota Parepare"),
  new Venue(11, "A4 - Badminton", "A4.jpg", "Rp35.000", ["badminton"], 5.0, 9, "Jalan sapta marga, Watang Soreang, Soreang, Parepare"),
  new Venue(12, "Grand Sulawesi - Badminton", "GS Bultang.jpg", "Rp35.000", ["badminton"], 4.3, 65, "Jl. Jend Jl. Moh. Yusuf, Lompoe, Kec. Bacukiki, Kota Parepare"),
  new Venue(14, "Titik Kumpul Sportainment - Mini Soccer", "Titik Kumpul.jpg", "Rp800.000", ["mini soccer"], 4.9, 28, "Jl. Petta Unga, Watang Soreang, Kec. Soreang, Kota Parepare"),
  new Venue(15, "Arsy Sport - Badminton", "arsy.jpg", "Rp40.000", ["badminton"], 4.9, 16, "Jl. Lasangga No.1, Lompoe, Kec. Bacukiki, Kota Parepare")
];

// User Class
class User {
  constructor(email, password, name, venue = null) {
    this.email = email;
    this.password = password;
    this.name = name;
    this.venue = venue;
  }
}

// User Data
const users = {
  user: [
    new User('ichi@gmai.com', '123', 'ichi')
  ],
  manager: [
    new User('rama@gmail.com', '123', 'rama', 'Grand Sulawesi,Surya Jaya Sports')
  ],
  admin: [
    new User('parri@gmail.com', '123', 'Admin PareSports')
  ]
};

// Venue Listing System
class VenueListingSystem {
  constructor(venues, containerId, paginationId) {
    this.venues = venues;
    this.filteredVenues = [...venues];
    this.container = document.getElementById(containerId);
    this.pagination = document.getElementById(paginationId);
    this.itemsPerPage = 6;
    this.currentPage = 1;
  }

  renderVenues() {
    this.container.innerHTML = '';
    
    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    const venuesToShow = this.filteredVenues.slice(startIndex, endIndex);
    
    if (venuesToShow.length === 0) {
      this.container.innerHTML = '<div class="col-12 text-center py-5"><h5>Tidak ada venue yang ditemukan</h5></div>';
      return;
    }
    
    venuesToShow.forEach(venue => {
      const venueCard = document.createElement('div');
      venueCard.className = 'col-md-4 mb-4';
      
      venueCard.innerHTML = `
        <div class="card h-100 shadow-sm ${venue.comingSoon ? 'border-danger' : ''}">
          <div class="position-relative">
            <img src="${venue.image}" class="card-img-top" alt="${venue.name}" style="height: 180px; object-fit: cover;">
            ${venue.comingSoon ? '<span class="badge bg-danger position-absolute top-0 end-0 m-2">COMING SOON</span>' : ''}
          </div>
          <div class="card-body d-flex flex-column">
            <h6 class="card-title">${venue.name}</h6>
            <div class="mb-2">${Venue.generateStarRating(venue.rating)} <span class="text-muted small">(${venue.reviewCount})</span></div>
            ${Venue.generateLocationHTML(venue.location, venue.distance)}
            <p class="card-text text-muted small mt-auto">Mulai: <strong>${venue.price}</strong></p>
            <a href="booking.html?id=${venue.id}" class="btn btn-danger btn-sm w-100 mt-2">
              Booking Sekarang
            </a>
          </div>
        </div>
      `;
      
      this.container.appendChild(venueCard);
    });
  }

  renderPagination() {
    this.pagination.innerHTML = '';
    
    const totalPages = Math.ceil(this.filteredVenues.length / this.itemsPerPage);
    
    if (totalPages <= 1) return;
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${this.currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = '<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>';
    prevLi.addEventListener('click', (e) => {
      e.preventDefault();
      if (this.currentPage > 1) {
        this.currentPage--;
        this.renderVenues();
        this.renderPagination();
        window.scrollTo({top: 0, behavior: 'smooth'});
      }
    });
    this.pagination.appendChild(prevLi);
    
    // Page numbers with limited visible pages
    const maxVisiblePages = 5;
    let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page
    if (startPage > 1) {
      const firstLi = document.createElement('li');
      firstLi.className = 'page-item';
      firstLi.innerHTML = '<a class="page-link" href="#">1</a>';
      firstLi.addEventListener('click', (e) => {
        e.preventDefault();
        this.currentPage = 1;
        this.renderVenues();
        this.renderPagination();
        window.scrollTo({top: 0, behavior: 'smooth'});
      });
      this.pagination.appendChild(firstLi);
      
      if (startPage > 2) {
        const ellipsisLi = document.createElement('li');
        ellipsisLi.className = 'page-item disabled';
        ellipsisLi.innerHTML = '<a class="page-link" href="#">...</a>';
        this.pagination.appendChild(ellipsisLi);
      }
    }
    
    // Middle pages
    for (let i = startPage; i <= endPage; i++) {
      const pageLi = document.createElement('li');
      pageLi.className = `page-item ${i === this.currentPage ? 'active' : ''}`;
      pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      pageLi.addEventListener('click', (e) => {
        e.preventDefault();
        this.currentPage = i;
        this.renderVenues();
        this.renderPagination();
        window.scrollTo({top: 0, behavior: 'smooth'});
      });
      this.pagination.appendChild(pageLi);
    }
    
    // Last page
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        const ellipsisLi = document.createElement('li');
        ellipsisLi.className = 'page-item disabled';
        ellipsisLi.innerHTML = '<a class="page-link" href="#">...</a>';
        this.pagination.appendChild(ellipsisLi);
      }
      
      const lastLi = document.createElement('li');
      lastLi.className = 'page-item';
      lastLi.innerHTML = `<a class="page-link" href="#">${totalPages}</a>`;
      lastLi.addEventListener('click', (e) => {
        e.preventDefault();
        this.currentPage = totalPages;
        this.renderVenues();
        this.renderPagination();
        window.scrollTo({top: 0, behavior: 'smooth'});
      });
      this.pagination.appendChild(lastLi);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${this.currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = '<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>';
    nextLi.addEventListener('click', (e) => {
      e.preventDefault();
      if (this.currentPage < totalPages) {
        this.currentPage++;
        this.renderVenues();
        this.renderPagination();
        window.scrollTo({top: 0, behavior: 'smooth'});
      }
    });
    this.pagination.appendChild(nextLi);
  }

  filterVenues(searchTerm, sportType) {
    this.filteredVenues = this.venues.filter(venue => {
      const nameMatch = venue.name.toLowerCase().includes(searchTerm);
      let sportMatch;
      
      if (Array.isArray(venue.sports)) {
        sportMatch = sportType === 'all' || venue.sports.includes(sportType);
      } else {
        sportMatch = sportType === 'all' || venue.sport === sportType;
      }
      
      return nameMatch && sportMatch;
    });
    
    this.currentPage = 1;
    this.renderVenues();
    this.renderPagination();
  }

  setupEventListeners(searchInputId, sportFilterId, searchButtonId) {
    const searchInput = document.getElementById(searchInputId);
    const sportFilter = document.getElementById(sportFilterId);
    const searchButton = document.getElementById(searchButtonId);
    const daftarBtn = document.querySelector('.btn-yellow');

    const filterHandler = () => {
      this.filterVenues(
        searchInput.value.toLowerCase(),
        sportFilter.value
      );
    };

    searchInput.addEventListener('input', filterHandler);
    sportFilter.addEventListener('change', filterHandler);
    searchButton.addEventListener('click', filterHandler);
    
    daftarBtn.addEventListener('click', function() {
      alert("Fitur pendaftaran venue belum aktif. Silakan hubungi admin.");
    });
  }
}

// Login System
class LoginSystem {
  constructor(users) {
    this.users = users;
    this.currentRole = 'user';
    this.body = document.body;
  }

  setupRoleSelection() {
    const roleOptions = document.querySelectorAll('.role-option');
    roleOptions.forEach(option => {
      option.addEventListener('click', () => {
        roleOptions.forEach(opt => opt.classList.remove('active'));
        option.classList.add('active');
        this.currentRole = option.getAttribute('data-role');
      });
    });
  }

  setupPasswordToggle() {
    const toggleLoginPassword = document.getElementById('toggleLoginPassword');
    const toggleRegisterPassword = document.getElementById('toggleRegisterPassword');
    const loginPassword = document.getElementById('loginPassword');
    const registerPassword = document.getElementById('registerPassword');

    toggleLoginPassword.addEventListener('click', () => {
      const type = loginPassword.getAttribute('type') === 'password' ? 'text' : 'password';
      loginPassword.setAttribute('type', type);
    });

    toggleRegisterPassword.addEventListener('click', () => {
      const type = registerPassword.getAttribute('type') === 'password' ? 'text' : 'password';
      registerPassword.setAttribute('type', type);
    });
  }

  setupLoginForm() {
    const loginForm = document.getElementById('loginForm');
    
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const email = document.getElementById('loginEmail').value;
      const password = document.getElementById('loginPassword').value;
      
      // Find user in the selected role
      const user = this.users[this.currentRole].find(u => 
        u.email === email && u.password === password
      );
      
      if (user) {
        // Store user data
        const userData = {
          role: this.currentRole,
          email: user.email,
          name: user.name,
          venue: user.venue || null
        };
        
        localStorage.setItem('currentUser', JSON.stringify(userData));
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
        modal.hide();
        
        // Update UI
        this.updateUI(userData);
      } else {
        alert('Email atau password salah! Silakan coba lagi.');
      }
    });
  }

  setupRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    
    registerForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const name = document.getElementById('registerName').value;
      const email = document.getElementById('registerEmail').value;
      const password = document.getElementById('registerPassword').value;
      const confirmPassword = document.getElementById('registerConfirmPassword').value;
      
      if (password !== confirmPassword) {
        alert('Password dan konfirmasi password tidak sama!');
        return;
      }
      
      // In a real app, you would send this data to your backend
      alert('Pendaftaran berhasil! Silakan login.');
      
      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
      modal.hide();
      
      // Show login modal
      const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
      loginModal.show();
    });
  }

  setupLogout() {
    const logoutButton = document.getElementById('logoutButton');
    
    logoutButton.addEventListener('click', (e) => {
      e.preventDefault();
      localStorage.removeItem('currentUser');
      this.updateUI(null);
    });
  }

  updateUI(user) {
    if (user) {
      this.body.classList.add('logged-in');
      document.getElementById('navbarUsername').textContent = user.name;
      
      // Show appropriate buttons based on role
      if (user.role === 'admin') {
        document.getElementById('adminButton').classList.remove('d-none');
      } else if (user.role === 'manager') {
        document.getElementById('managerButton').classList.remove('d-none');
      }
    } else {
      this.body.classList.remove('logged-in');
      document.getElementById('adminButton').classList.add('d-none');
      document.getElementById('managerButton').classList.add('d-none');
    }
  }

  initialize() {
    this.setupRoleSelection();
    this.setupPasswordToggle();
    this.setupLoginForm();
    this.setupRegisterForm();
    this.setupLogout();
    
    // Check login status and update UI
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    this.updateUI(currentUser);
  }
}

// Initialize the page when DOM is fully loaded
document.addEventListener("DOMContentLoaded", function() {
  // Initialize venue listing system
  const venueSystem = new VenueListingSystem(venues, 'venueContainer', 'pagination');
  venueSystem.renderVenues();
  venueSystem.renderPagination();
  venueSystem.setupEventListeners('searchInput', 'sportFilter', 'searchButton');
  
  // Initialize login system
  const loginSystem = new LoginSystem(users);
  loginSystem.initialize();
});