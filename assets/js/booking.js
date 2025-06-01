document.addEventListener('DOMContentLoaded', function() {
  // Sample data - in a real app, this would come from an API
  const availabilityData = {
    'futsal1': {
      '2024-05-20': ['08:00', '09:00', '10:00', '14:00', '15:00', '16:00', '17:00', '20:00'],
      '2024-05-21': ['09:00', '10:00', '11:00', '15:00', '16:00', '17:00', '19:00', '20:00']
    },
    'futsal2': {
      '2024-05-20': ['08:00', '10:00', '11:00', '14:00', '16:00', '18:00', '19:00', '20:00'],
      '2024-05-21': ['08:00', '09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '20:00']
    },
    'badminton1': {
      '2024-05-20': ['07:00', '08:00', '09:00', '13:00', '14:00', '15:00', '18:00', '19:00'],
      '2024-05-21': ['08:00', '09:00', '10:00', '14:00', '15:00', '16:00', '19:00', '20:00']
    }
  };
  
  // Initialize variables
  let selectedDate = '2024-05-20';
  let selectedField = '';
  let selectedTime = '';
  let selectedDuration = 1;
  let selectedPaymentMethod = '';
  
  // Initialize modals
  const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
  const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
  const successModal = new bootstrap.Modal(document.getElementById('successModal'));
  
  // When field card is clicked, open modal with schedule
  document.querySelectorAll('.field-card').forEach(card => {
    card.addEventListener('click', function(e) {
      if (e.target.classList.contains('schedule-btn')) return;
      
      selectedField = this.getAttribute('data-field');
      const fieldName = this.querySelector('h3').textContent;
      const fieldPrice = this.getAttribute('data-price');
      
      // Set field info in modal
      document.getElementById('fieldName').textContent = fieldName;
      document.getElementById('summaryField').textContent = fieldName;
      document.getElementById('summaryDate').textContent = 'Selasa, 20 Mei 2024';
      
      // Generate time slots
      const timeSlotsContainer = document.querySelector('.time-slots');
      timeSlotsContainer.innerHTML = '';
      
      // Get available times for this field and date
      const availableTimes = availabilityData[selectedField][selectedDate] || [];
      
      // Generate time slots from 07:00 to 22:00
      for (let hour = 7; hour <= 22; hour++) {
        const time = `${hour.toString().padStart(2, '0')}:00`;
        const isAvailable = availableTimes.includes(time);
        
        const slot = document.createElement('div');
        slot.className = `time-slot ${isAvailable ? '' : 'booked'}`;
        slot.textContent = time;
        slot.setAttribute('data-time', time);
        
        if (isAvailable) {
          slot.addEventListener('click', function() {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            selectedTime = this.getAttribute('data-time');
            updateBookingSummary();
          });
        }
        
        timeSlotsContainer.appendChild(slot);
      }
      
      // Reset selection
      selectedTime = '';
      selectedPaymentMethod = '';
      document.getElementById('proceedPaymentBtn').disabled = true;
      document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
      
      // Open modal
      bookingModal.show();
    });
  });
  
  // Schedule button click handler
  document.querySelectorAll('.schedule-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const card = this.closest('.field-card');
      card.click();
    });
  });
  
  // Duration select change
  document.getElementById('durationSelect').addEventListener('change', function() {
    selectedDuration = parseInt(this.value);
    updateBookingSummary();
  });
  
  // Payment method selection
  document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
      document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
      this.classList.add('selected');
      selectedPaymentMethod = this.getAttribute('data-method');
      document.getElementById('proceedPaymentBtn').disabled = !selectedTime;
    });
  });
  
  // Proceed to payment button
  document.getElementById('proceedPaymentBtn').addEventListener('click', function() {
    bookingModal.hide();
    
    // Show payment method
    if (selectedPaymentMethod === 'qris') {
      document.getElementById('qrisPayment').style.display = 'block';
      document.getElementById('bankPayment').style.display = 'none';
      document.getElementById('qrisAmount').textContent = document.getElementById('summaryTotal').textContent;
    } else {
      document.getElementById('qrisPayment').style.display = 'none';
      document.getElementById('bankPayment').style.display = 'block';
      document.getElementById('bankAmount').textContent = document.getElementById('summaryTotal').textContent;
    }
    
    paymentModal.show();
  });
  
  // Confirm payment button
  document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
    paymentModal.hide();
    
    // Set success details
    document.getElementById('successField').textContent = document.getElementById('summaryField').textContent;
    document.getElementById('successDate').textContent = document.getElementById('summaryDate').textContent;
    document.getElementById('successTime').textContent = document.getElementById('summaryTime').textContent;
    
    successModal.show();
  });
  
  // Update booking summary
  function updateBookingSummary() {
    if (selectedTime) {
      const fieldPrice = parseInt(document.querySelector(`.field-card[data-field="${selectedField}"]`).getAttribute('data-price'));
      const totalPrice = fieldPrice * selectedDuration;
      
      // Calculate end time
      const [hours, minutes] = selectedTime.split(':').map(Number);
      const endHours = hours + selectedDuration;
      const endTime = `${endHours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
      
      document.getElementById('summaryTime').textContent = `${selectedTime} - ${endTime}`;
      document.getElementById('summaryDuration').textContent = `${selectedDuration} Jam`;
      document.getElementById('summaryTotal').textContent = `Rp ${totalPrice.toLocaleString('id-ID')}`;
      
      // Enable proceed button if payment method selected
      document.getElementById('proceedPaymentBtn').disabled = !selectedPaymentMethod;
    }
  }
  
  // Date selection
  document.querySelectorAll('.date-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.date-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      // In a real app, you would update the selectedDate and reload availability
    });
  });
  
  // Filter selection
  document.querySelectorAll('.time-filter, .sport-filter').forEach(filter => {
    filter.addEventListener('change', function() {
      // In a real app, you would filter the fields based on selection
    });
  });
});