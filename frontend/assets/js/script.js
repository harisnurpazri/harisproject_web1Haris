// ==================== LOGIN VALIDATION ====================
function validateLogin(){
  const email = document.getElementById('email').value.trim();
  const pw = document.getElementById('password').value;
  if(email === ''){ alert('Email wajib diisi'); return false; }
  if(pw === ''){ alert('Password wajib diisi'); return false; }
  return true;
}

// ==================== FILTER PRODUCTS ====================
function filterProducts(){
  const q = document.getElementById('search') ? document.getElementById('search').value.toLowerCase() : '';
  const selectedCat = document.getElementById('cat') ? document.getElementById('cat').value : '';
  
  let visibleCount = 0;
  
  document.querySelectorAll('.product-card').forEach(card => {
    const title = card.querySelector('.card-title').innerText.toLowerCase();
    const cat = card.getAttribute('data-cat') ? card.getAttribute('data-cat').toLowerCase() : '';
    const matchesQ = title.indexOf(q) !== -1;
    const matchesCat = selectedCat === '' || cat === selectedCat.toLowerCase();
    
    if(matchesQ && matchesCat) {
      card.style.display='block';
      visibleCount++;
    } else {
      card.style.display='none';
    }
  });
  
  // Show no results message
  showNoResultsMessage(visibleCount);
}

// ==================== NO RESULTS MESSAGE ====================
function showNoResultsMessage(count) {
  let noResultsDiv = document.getElementById('noResultsMessage');
  
  if (count === 0) {
    if (!noResultsDiv) {
      const productGrid = document.querySelector('.product-grid');
      if (productGrid) {
        noResultsDiv = document.createElement('div');
        noResultsDiv.id = 'noResultsMessage';
        noResultsDiv.className = 'col-12 text-center py-5';
        noResultsDiv.innerHTML = `
          <i class="fa-solid fa-search fs-1 text-muted mb-3"></i>
          <h4 class="text-muted">Produk tidak ditemukan</h4>
          <p class="text-muted">Coba kata kunci atau kategori lain</p>
        `;
        productGrid.appendChild(noResultsDiv);
      }
    }
    if (noResultsDiv) noResultsDiv.style.display = 'block';
  } else {
    if (noResultsDiv) noResultsDiv.style.display = 'none';
  }
}

// ==================== ADD TO CART WITH AJAX (NO PAGE RELOAD) ====================
function addToCart(id) {
  // Check if user is logged in
  if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    loginModal.show();
    return false;
  }
  
  // Find all buttons for this product and add loading state
  const buttons = document.querySelectorAll(`button[onclick*="addToCart(${id})"]`);
  buttons.forEach(btn => {
    btn.classList.add('btn-cart-loading');
    btn.disabled = true;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    btn.dataset.originalHtml = originalHTML;
  });
  
  // Send AJAX request
  fetch('pages/add_to_cart.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `product_id=${id}`
  })
  .then(response => response.json())
  .then(data => {
    // Remove loading state
    buttons.forEach(btn => {
      btn.classList.remove('btn-cart-loading');
      btn.disabled = false;
      btn.innerHTML = btn.dataset.originalHtml || '<i class="fa-solid fa-cart-plus"></i>';
    });
    
    if (data.success) {
      // Update cart count in navbar
      updateCartCount(data.cartCount);
      
      // Show success notification
      showNotification('success', 'Produk berhasil ditambahkan ke keranjang! 🛒');
    } else {
      showNotification('error', data.message || 'Gagal menambahkan produk');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    
    // Remove loading state on error
    buttons.forEach(btn => {
      btn.classList.remove('btn-cart-loading');
      btn.disabled = false;
      btn.innerHTML = btn.dataset.originalHtml || '<i class="fa-solid fa-cart-plus"></i>';
    });
    
    showNotification('error', 'Terjadi kesalahan saat menambahkan produk');
  });
  
  return false;
}

// ==================== UPDATE CART COUNT - FIXED ====================
function updateCartCount(count) {
  console.log('updateCartCount called with count:', count);
  
  const cartBtn = document.querySelector('.btn-cart-custom');
  
  if (!cartBtn) {
    console.error('Cart button (.btn-cart-custom) not found!');
    return;
  }
  
  // Cari badge yang sudah ada
  let badge = cartBtn.querySelector('.cart-count-badge');
  
  if (count > 0) {
    if (!badge) {
      // Buat badge baru
      console.log('Creating new badge...');
      badge = document.createElement('span');
      badge.className = 'cart-count-badge';
      badge.textContent = count;
      cartBtn.appendChild(badge);
      
      console.log('Badge created and appended:', badge);
    } else {
      // Update badge yang sudah ada
      console.log('Updating existing badge from', badge.textContent, 'to', count);
      badge.textContent = count;
      
      // Trigger pulse animation
      badge.classList.remove('pulse');
      void badge.offsetWidth; // Reflow
      badge.classList.add('pulse');
    }
  } else {
    // Hapus badge jika count = 0
    if (badge) {
      console.log('Removing badge...');
      badge.style.animation = 'badgeDisappear 0.3s ease';
      badge.style.opacity = '0';
      badge.style.transform = 'scale(0)';
      
      setTimeout(() => {
        if (badge.parentNode) {
          badge.remove();
        }
      }, 300);
    }
  }
  
  console.log('Cart count updated successfully!');
}

// ==================== SHOW NOTIFICATION (TOAST) ====================
function showNotification(type, message) {
  // Create container if not exists
  let toastContainer = document.getElementById('toastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toastContainer';
    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    toastContainer.style.zIndex = '9999';
    document.body.appendChild(toastContainer);
  }

  // Create toast element
  const toastId = 'toast-' + Date.now();
  const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
  const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
  
  const toastHTML = `
    <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <i class="fa-solid ${icon} me-2"></i>
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  
  toastContainer.insertAdjacentHTML('beforeend', toastHTML);
  
  // Show toast
  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, {
    autohide: true,
    delay: 3000
  });
  toast.show();
  
  // Remove after hidden
  toastElement.addEventListener('hidden.bs.toast', function() {
    toastElement.remove();
  });
}

// ==================== WISHLIST FUNCTIONALITY ====================
let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');

function toggleWishlist(event, productId) {
  event.stopPropagation();
  
  const btn = event.currentTarget;
  const icon = btn.querySelector('i');
  
  if (wishlist.includes(productId)) {
    // Remove from wishlist
    wishlist = wishlist.filter(id => id !== productId);
    icon.classList.remove('fa-solid');
    icon.classList.add('fa-regular');
    btn.classList.remove('active');
    showNotification('info', 'Dihapus dari wishlist');
  } else {
    // Add to wishlist
    wishlist.push(productId);
    icon.classList.remove('fa-regular');
    icon.classList.add('fa-solid');
    btn.classList.add('active');
    showNotification('success', 'Ditambahkan ke wishlist ❤️');
  }
  
  localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

// ==================== SHARE PRODUCT ====================
function shareProduct(productId) {
  const url = `${window.location.origin}/pages/detail.php?id=${productId}`;
  const productCard = event.target.closest('.product-card');
  const productName = productCard ? productCard.querySelector('.card-title').textContent : 'Produk Furniture';
  
  if (navigator.share) {
    navigator.share({
      title: productName,
      text: `Lihat produk furniture ini: ${productName}`,
      url: url
    }).catch(err => console.log('Error sharing:', err));
  } else {
    // Fallback: Copy to clipboard
    navigator.clipboard.writeText(url).then(() => {
      showNotification('success', 'Link produk disalin ke clipboard! 📋');
    }).catch(err => {
      console.error('Failed to copy:', err);
      showNotification('error', 'Gagal menyalin link');
    });
  }
}

// ==================== SMOOTH SCROLL ====================
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#') return;
      
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
  
  // Scroll indicator
  const scrollIndicator = document.querySelector('.scroll-indicator');
  if (scrollIndicator) {
    scrollIndicator.addEventListener('click', function() {
      window.scrollBy({
        top: window.innerHeight - 100,
        behavior: 'smooth'
      });
    });
  }
});

// ==================== NAVBAR SCROLL EFFECT ====================
document.addEventListener('DOMContentLoaded', function() {
  let lastScroll = 0;
  const navbar = document.querySelector('.navbar');
  
  window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
  });
});

// ==================== IMAGE LAZY LOADING WITH ANIMATION ====================
document.addEventListener('DOMContentLoaded', () => {
  const images = document.querySelectorAll('img[loading="lazy"]');
  
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.style.opacity = '0';
          img.style.transition = 'opacity 0.5s ease';
          
          img.onload = () => {
            img.style.opacity = '1';
          };
          
          // If image already loaded (cached)
          if (img.complete) {
            img.style.opacity = '1';
          }
          
          observer.unobserve(img);
        }
      });
    }, {
      rootMargin: '50px'
    });
    
    images.forEach(img => imageObserver.observe(img));
  }
});

// ==================== PRODUCT CARDS FADE IN ANIMATION ====================
// FIXED: Only observe product cards, NOT category cards
document.addEventListener('DOMContentLoaded', function() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '0';
        entry.target.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
          entry.target.style.transition = 'all 0.5s ease';
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }, 100);
        
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  // Only observe PRODUCT cards, exclude category cards
  const productCards = document.querySelectorAll('.product-card:not(.category-card-pro)');
  productCards.forEach(card => observer.observe(card));
  
  // Ensure category cards are always visible with correct styles
  const categoryCards = document.querySelectorAll('.category-card-pro');
  categoryCards.forEach(card => {
    card.style.opacity = '1';
    card.style.transform = 'none';
  });
});

// ==================== INIT WISHLIST STATES ====================
document.addEventListener('DOMContentLoaded', () => {
  const wishlistButtons = document.querySelectorAll('.wishlist-btn');
  
  wishlistButtons.forEach(btn => {
    const onclickAttr = btn.getAttribute('onclick');
    if (onclickAttr) {
      const match = onclickAttr.match(/\d+/);
      if (match) {
        const productId = parseInt(match[0]);

        if (wishlist.includes(productId)) {
