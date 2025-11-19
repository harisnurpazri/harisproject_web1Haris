
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
          const icon = btn.querySelector('i');
          if (icon) {
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid');
            btn.classList.add('active');
          }
        }
      }
    }
  });
});

// ==================== SCROLL TO TOP BUTTON ====================
(function() {
  // Create button if not exists
  if (!document.querySelector('.scroll-to-top')) {
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.setAttribute('aria-label', 'Scroll to top');
    scrollBtn.onclick = () => {
      window.scrollTo({ 
        top: 0, 
        behavior: 'smooth' 
      });
    };
    document.body.appendChild(scrollBtn);
  }
  
  // Show/hide on scroll
  window.addEventListener('scroll', () => {
    const scrollBtn = document.querySelector('.scroll-to-top');
    if (scrollBtn) {
      if (window.scrollY > 300) {
        scrollBtn.classList.add('show');
      } else {
        scrollBtn.classList.remove('show');
      }
    }
  });
})();

// ==================== PREVENT MULTIPLE FORM SUBMISSIONS ====================
document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn && !submitBtn.disabled) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.7';
        
        // Re-enable after 3 seconds as fallback
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.style.opacity = '1';
        }, 3000);
      }
    });
  });
});

// ==================== PRODUCT CARD HOVER EFFECTS ====================
document.addEventListener('DOMContentLoaded', () => {
  const productCards = document.querySelectorAll('.product-card');
  
  productCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transition = 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
    });
  });
});

// ==================== ERROR IMAGE HANDLER ====================
document.addEventListener('DOMContentLoaded', () => {
  const images = document.querySelectorAll('img');
  images.forEach(img => {
    img.addEventListener('error', function() {
      if (!this.dataset.errorHandled) {
        this.dataset.errorHandled = 'true';
        this.src = 'assets/img/placeholder.jpg';
        this.alt = 'Image not found';
      }
    });
  });
});

// ==================== PREVENT ENTER KEY ON SEARCH ====================
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('search');
  if (searchInput) {
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        filterProducts();
      }
    });
  }
});

// ==================== AUTO-HIDE ALERTS ====================
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
  alerts.forEach(alert => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });
});

// ==================== CONSOLE BRANDING ====================
console.log('%c🪑 Meubeul Dua Putra', 'font-size: 20px; font-weight: bold; color: #CD7F32;');
console.log('%cFurniture Marketplace System', 'font-size: 14px; color: #5C4033;');
console.log('%c✨ Enhanced UI/UX with AJAX Cart v2.1 - Fixed Animations', 'font-size: 12px; color: #8B6F47;');

// ==================== PERFORMANCE MONITORING (DEV ONLY) ====================
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
  window.addEventListener('load', () => {
    const loadTime = performance.now();
    console.log(`%c⚡ Page loaded in ${loadTime.toFixed(2)}ms`, 'color: #10B981; font-weight: bold;');
  });
}

// ==================== LOOPING TYPING EFFECT WITH GRADIENT ====================
document.addEventListener('DOMContentLoaded', function() {
  const heroTitle = document.querySelector('.hero-professional .hero-content h1');
  
  if (heroTitle) {
    // Array teks yang akan ditampilkan secara bergantian
    const textArray = [
      'Furniture Berkualitas untuk Rumah Impian Anda',
      'Desain Elegan dengan Kayu Pilihan Terbaik',
      'Craftsmanship Sempurna untuk Hunian Anda',
      'Furniture Premium dengan Harga Terjangkau'
    ];
    
    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typingSpeed = 100;
    
    function typeWriter() {
      const currentText = textArray[textIndex];
      
      if (isDeleting) {
        // Menghapus karakter
        heroTitle.textContent = currentText.substring(0, charIndex - 1);
        charIndex--;
        typingSpeed = 50;
      } else {
        // Menambah karakter
        heroTitle.textContent = currentText.substring(0, charIndex + 1);
        charIndex++;
        typingSpeed = Math.random() * 40 + 80;
        
        // Efek shake saat mengetik
        if (currentText.charAt(charIndex - 1) !== ' ') {
          heroTitle.style.transform = 'translateX(' + (Math.random() * 2 - 1) + 'px)';
          setTimeout(() => {
            heroTitle.style.transform = 'translateX(0)';
          }, 50);
        }
      }
      
      // Jika selesai mengetik
      if (!isDeleting && charIndex === currentText.length) {
        typingSpeed = 2000;
        isDeleting = true;
        heroTitle.classList.add('typing-complete');
      }
      
      // Jika selesai menghapus
      if (isDeleting && charIndex === 0) {
        isDeleting = false;
        textIndex = (textIndex + 1) % textArray.length;
        typingSpeed = 500;
        heroTitle.classList.remove('typing-complete');
      }
      
      setTimeout(typeWriter, typingSpeed);
    }
    
    // Start typing
    heroTitle.style.opacity = '0';
    setTimeout(() => {
      heroTitle.style.transition = 'opacity 0.5s ease';
      heroTitle.style.opacity = '1';
      typeWriter();
    }, 500);
  }
  
  // Enhanced scroll indicator
  const scrollIndicator = document.querySelector('.scroll-indicator');
  if (scrollIndicator) {
    scrollIndicator.addEventListener('click', function() {
      const targetScroll = window.innerHeight - 100;
      window.scrollTo({
        top: targetScroll,
        behavior: 'smooth'
      });
    });
  }
});

// Add parallax effect to hero
window.addEventListener('scroll', function() {
  const hero = document.querySelector('.hero-professional');
  if (hero) {
    const scrolled = window.pageYOffset;
    const parallaxSpeed = 0.5;
    hero.style.backgroundPositionY = (scrolled * parallaxSpeed) + 'px';
  }
});

/**
 * Initialize Cart Page
 */
document.addEventListener('DOMContentLoaded', function() {
    initQtyControls();
    initRemoveButtons();
    initSmoothScroll();
    updateCartSummary();
    initAnimations();
});

/**
 * ==================== QUANTITY CONTROLS ====================
 * Handle quantity increase/decrease buttons
 */
function initQtyControls() {
    const qtyMinusButtons = document.querySelectorAll('.qty-minus');
    const qtyPlusButtons = document.querySelectorAll('.qty-plus');

    qtyMinusButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('.qty-form');
            const input = form.querySelector('.qty-input');
            let currentQty = parseInt(input.value);
            
            if (currentQty > 1) {
                currentQty--;
                input.value = currentQty;
                submitQtyForm(form);
            }
        });
    });

    qtyPlusButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('.qty-form');
            const input = form.querySelector('.qty-input');
            let currentQty = parseInt(input.value);
            currentQty++;
            input.value = currentQty;
            submitQtyForm(form);
        });
    });
}

/**
 * Submit quantity form
 */
function submitQtyForm(form) {
    // Show loading state
    const buttons = form.querySelectorAll('.qty-btn');
    buttons.forEach(btn => btn.disabled = true);
    
    // Simulate smooth update (fade out, update, fade in)
    const row = form.closest('.cart-item-row');
    row.style.opacity = '0.6';
    row.style.transition = 'opacity 0.3s ease';
    
    // Submit form
    setTimeout(() => {
        form.submit();
    }, 300);
}

/**
 * ==================== REMOVE BUTTONS ====================
 * Handle remove product from cart
 */
function initRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-form');

    removeButtons.forEach(form => {
        form.addEventListener('submit', function(e) {
            const productName = this.closest('.cart-item-row').querySelector('.product-info h6').textContent;
            
            if (!confirm(`Yakin ingin menghapus "${productName}" dari keranjang?`)) {
                e.preventDefault();
            } else {
                // Show removal animation
                const row = this.closest('.cart-item-row');
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(100px)';
                
                setTimeout(() => {
                    this.submit();
                }, 300);
            }
        });
    });
}

/**
 * ==================== SMOOTH SCROLL ====================
 * Handle smooth scrolling for links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
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
}

/**
 * ==================== UPDATE CART SUMMARY ====================
 * Recalculate and update cart totals
 */
function updateCartSummary() {
    const items = document.querySelectorAll('.cart-item-row');
    const itemCount = items.length;
    
    let totalPrice = 0;
    
    items.forEach(item => {
        const subtotalText = item.querySelector('.subtotal-cell').textContent;
        const subtotalNum = parseFloat(subtotalText.replace(/[^\d]/g, '')) || 0;
        totalPrice += subtotalNum;
    });

    // Update summary
    const itemCountEl = document.querySelector('.item-count');
    const totalPriceEl = document.querySelector('.total-price');
    const finalTotalEl = document.getElementById('final-total');

    if (itemCountEl) {
        itemCountEl.textContent = itemCount + ' produk';
    }

    if (totalPriceEl) {
        totalPriceEl.textContent = 'Rp ' + formatCurrency(totalPrice);
    }

    if (finalTotalEl) {
        finalTotalEl.textContent = 'Rp ' + formatCurrency(totalPrice);
    }
}

/**
 * ==================== FORMAT CURRENCY ====================
 * Format number to Indonesian currency format
 */
function formatCurrency(num) {
    return Math.round(num).toLocaleString('id-ID');
}

/**
 * ==================== ANIMATIONS ====================
 * Initialize page animations
 */
function initAnimations() {
    // Cart items entrance animation
    const cartRows = document.querySelectorAll('.cart-item-row');
    cartRows.forEach((row, index) => {
        row.style.animation = `fadeInUp 0.6s ease backwards`;
        row.style.animationDelay = `${index * 0.1}s`;
    });

    // Stagger animation for better visual effect
    const cartTable = document.querySelector('.cart-table-wrapper');
    if (cartTable) {
        cartTable.addEventListener('mouseenter', function() {
            // Subtle highlight effect on hover
            cartRows.forEach((row, index) => {
                row.style.transition = 'background-color 0.3s ease';
            });
        });
    }

    // Scroll to summary on large screens
    if (window.innerWidth > 992) {
        const summaryCard = document.querySelector('.cart-summary-card');
        if (summaryCard) {
            window.addEventListener('scroll', function() {
                updateSummaryPosition();
            });
        }
    }
}

/**
 * ==================== UPDATE SUMMARY POSITION ====================
 * Update sticky summary position on scroll
 */
function updateSummaryPosition() {
    const summaryCard = document.querySelector('.cart-summary-card');
    if (!summaryCard) return;

    const scrollPosition = window.scrollY;
    const summaryCard_rect = summaryCard.getBoundingClientRect();
    const windowHeight = window.innerHeight;

    // Check if summary should stick
    if (summaryCard_rect.top > 0 && scrollPosition > 200) {
        summaryCard.style.boxShadow = '0 15px 50px rgba(92, 64, 51, 0.15)';
    } else {
        summaryCard.style.boxShadow = '0 10px 40px rgba(92, 64, 51, 0.08)';
    }
}

/**
 * ==================== PREVENT MULTIPLE SUBMISSIONS ====================
 * Prevent accidental double submission of forms
 */
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const buttons = this.querySelectorAll('button[type="submit"]');
        buttons.forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.7';
            
            // Re-enable after 2 seconds as fallback
            setTimeout(() => {
                btn.disabled = false;
                btn.style.opacity = '1';
            }, 2000);
        });
    });
});

/**
 * ==================== KEYBOARD SHORTCUTS ====================
 * Add keyboard support for better UX
 */
document.addEventListener('keydown', function(e) {
    // ESC key: go back to shopping
    if (e.key === 'Escape') {
        const continueBtn = document.querySelector('.btn-continue');
        if (continueBtn) {
            continueBtn.click();
        }
    }

    // ENTER key on checkout button: proceed to checkout
    if (e.key === 'Enter' && e.ctrlKey) {
        const checkoutBtn = document.querySelector('.btn-checkout');
        if (checkoutBtn) {
            checkoutBtn.click();
        }
    }
});

/**
 * ==================== MOBILE OPTIMIZATION ====================
 * Handle mobile-specific interactions
 */
function initMobileOptimizations() {
    if (window.innerWidth <= 768) {
        // Add swipe to delete on mobile
        const cartRows = document.querySelectorAll('.cart-item-row');
        let startX = 0;

        cartRows.forEach(row => {
            row.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
            });

            row.addEventListener('touchend', function(e) {
                const endX = e.changedTouches[0].clientX;
                const diff = startX - endX;

                if (diff > 100) {
                    // Swiped left - show remove button
                    const removeBtn = row.querySelector('.btn-remove');
                    if (removeBtn) {
                        removeBtn.focus();
                        removeBtn.style.animation = 'pulse 0.5s ease';
                    }
                }
            });
        });
    }
}

// Call mobile optimizations on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileOptimizations);
} else {
    initMobileOptimizations();
}

/**
 * ==================== CART PAGE CONSOLE ====================
 * Console branding
 */
console.log(
    '%c🛒 Cart Management System',
    'font-size: 16px; font-weight: bold; color: #CD7F32;'
);
console.log(
    '%c✨ Enhanced UX with smooth interactions',
    'font-size: 12px; color: #8B6F47;'
);

/**
 * ==================== PERFORMANCE MONITORING ====================
 * Log page load performance (dev only)
 */
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.addEventListener('load', () => {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log(
            `%c⚡ Cart page loaded in ${pageLoadTime}ms`,
            'color: #10B981; font-weight: bold;'
        );
    });
}

// ==================== UPDATE CART COUNT - COMPLETELY FIXED ====================
function updateCartCount(count) {
  const cartBtn = document.querySelector('.btn-cart-custom');
  
  if (!cartBtn) {
    console.error('Cart button not found!');
    return;
  }
  
  // Cari badge yang sudah ada
  let badge = cartBtn.querySelector('.cart-count-badge');
  
  if (count > 0) {
    if (!badge) {
      // Buat badge baru
      badge = document.createElement('span');
      badge.className = 'cart-count-badge';
      badge.textContent = count;
      cartBtn.appendChild(badge);
      
      console.log('Badge created:', badge);
    } else {
      // Update badge yang sudah ada
      badge.textContent = count;
      
      // Trigger pulse animation
      badge.classList.remove('pulse');
      void badge.offsetWidth; // Reflow
      badge.classList.add('pulse');
      
      console.log('Badge updated:', count);
    }
  } else {
    // Hapus badge jika count = 0
    if (badge) {
      badge.style.animation = 'badgeDisappear 0.3s ease';
      badge.style.opacity = '0';
      badge.style.transform = 'scale(0)';
      
      setTimeout(() => {
        if (badge.parentNode) {
          badge.remove();
        }
      }, 300);
      
      console.log('Badge removed');
    }
  }
}

// Tambahkan CSS animation untuk disappear
const style = document.createElement('style');
style.textContent = `
  @keyframes badgeDisappear {
    from {
      transform: scale(1);
      opacity: 1;
    }
    to {
      transform: scale(0) rotate(-180deg);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);

// ==================== ADD TO CART WITH AJAX - IMPROVED ====================
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
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
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
      showNotification('success', data.message || 'Produk berhasil ditambahkan ke keranjang! 🛒');
      
      // Optional: Log untuk debugging
      console.log('Cart updated:', data);
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

// ==================== CART BADGE UPDATE - FIXED ====================
/**
 * Update cart count badge dengan animasi
 * @param {number} count - Jumlah item di cart
 */
function updateCartCount(count) {
  const cartBtn = document.querySelector('.btn-cart');
  
  if (!cartBtn) {
    console.error('Cart button not found!');
    return;
  }
  
  // Cari atau buat badge
  let badge = cartBtn.querySelector('.cart-badge');
  
  if (count > 0) {
    if (!badge) {
      // Buat badge baru jika belum ada
      badge = document.createElement('span');
      badge.className = 'cart-badge';
      cartBtn.appendChild(badge);
      
      // Animasi muncul pertama kali
      setTimeout(() => {
        badge.classList.add('badge-added');
      }, 10);
    } else {
      // Badge sudah ada, tambah animasi pulse
      badge.classList.remove('badge-pulse');
      void badge.offsetWidth; // Trigger reflow
      badge.classList.add('badge-pulse');
    }
    
    // Update angka
    badge.textContent = count;
    
    // Hapus class hidden jika ada
    badge.classList.remove('badge-hidden');
    
  } else {
    // Cart kosong, sembunyikan badge
    if (badge) {
      badge.classList.add('badge-hidden');
      
      // Hapus badge dari DOM setelah animasi selesai
      setTimeout(() => {
        if (badge.parentNode) {
          badge.remove();
        }
      }, 300);
    }
  }
  
  console.log('Cart badge updated:', count);
}

// ==================== ADD TO CART WITH AJAX - FIXED ====================
function addToCart(productId) {
  console.log('addToCart called with productId:', productId);
  
  // Cek apakah user sudah login
  if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    loginModal.show();
    return false;
  }
  
  // Cari semua button add to cart untuk produk ini
  const buttons = document.querySelectorAll(`button[onclick*="addToCart(${productId})"]`);
  
  // Set loading state
  buttons.forEach(btn => {
    btn.classList.add('btn-cart-loading');
    btn.disabled = true;
    const originalHTML = btn.innerHTML;
    btn.dataset.originalHtml = originalHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
  });
  
  console.log('Sending AJAX request to add_to_cart.php...');
  
  // Kirim request AJAX
  fetch('pages/add_to_cart.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `product_id=${productId}`
  })
  .then(response => {
    console.log('Response status:', response.status);
    
    if (!response.ok) {
      throw new Error('Network response was not ok: ' + response.status);
    }
    
    return response.text(); // Ambil sebagai text dulu untuk debugging
  })
  .then(text => {
    console.log('Raw response:', text);
    
    // Parse JSON
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      console.error('JSON parse error:', e);
      throw new Error('Invalid JSON response');
    }
    
    console.log('Parsed data:', data);
    
    // Restore button state
    buttons.forEach(btn => {
      btn.classList.remove('btn-cart-loading');
      btn.disabled = false;
      btn.innerHTML = btn.dataset.originalHtml || '<i class="fa-solid fa-cart-plus"></i>';
    });
    
    if (data.success) {
      // Update cart badge - INI YANG PENTING!
      console.log('Updating cart count to:', data.cartCount);
      updateCartCount(data.cartCount);
      
      // Show success notification
      showNotification('success', data.message || 'Produk berhasil ditambahkan ke keranjang! 🛒');
    } else {
      // Show error notification
      showNotification('error', data.message || 'Gagal menambahkan produk');
    }
  })
  .catch(error => {
    console.error('Error in addToCart:', error);
    
    // Restore button state on error
    buttons.forEach(btn => {
      btn.classList.remove('btn-cart-loading');
      btn.disabled = false;
      btn.innerHTML = btn.dataset.originalHtml || '<i class="fa-solid fa-cart-plus"></i>';
    });
    
    showNotification('error', 'Terjadi kesalahan: ' + error.message);
  });
  
  return false;
}

// ==================== SHOW NOTIFICATION (TOAST) ====================
/**
 * Tampilkan notifikasi toast
 * @param {string} type - 'success', 'error', 'warning', 'info'
 * @param {string} message - Pesan yang ditampilkan
 */
function showNotification(type, message) {
  // Cari atau buat container
  let toastContainer = document.getElementById('toastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toastContainer';
    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    toastContainer.style.zIndex = '9999';
    document.body.appendChild(toastContainer);
  }

  // Buat toast element
  const toastId = 'toast-' + Date.now();
  const bgClass = type === 'success' ? 'bg-success' : 
                  type === 'error' ? 'bg-danger' : 
                  type === 'warning' ? 'bg-warning' : 'bg-info';
  const icon = type === 'success' ? 'fa-check-circle' : 
               type === 'error' ? 'fa-exclamation-circle' : 
               type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
  
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

// ==================== INITIALIZE CART COUNT ON PAGE LOAD ====================
document.addEventListener('DOMContentLoaded', function() {
  // Ambil cart count dari server jika perlu
  // Atau bisa langsung dari PHP di navbar
  const cartBtn = document.querySelector('.btn-cart');
  if (cartBtn) {
    const existingBadge = cartBtn.querySelector('.badge');
    if (existingBadge) {
      // Jika badge sudah ada dari PHP, konversi ke format baru
      const count = parseInt(existingBadge.textContent) || 0;
      existingBadge.remove(); // Hapus badge lama
      
      if (count > 0) {
        updateCartCount(count); // Buat badge baru dengan format yang benar
      }
    }
  }
  
  console.log('Cart badge system initialized');
});

// ==================== PREVENT MULTIPLE SUBMISSIONS ====================
document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn && !submitBtn.disabled) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.7';
        
        // Re-enable after 3 seconds as fallback
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.style.opacity = '1';
        }, 3000);
      }
    });
  });
});

// ==================== CONSOLE BRANDING ====================
console.log('%c🛒 Cart System v2.2', 'font-size: 16px; font-weight: bold; color: #CD7F32;');
console.log('%c✨ Fixed badge positioning & animation', 'font-size: 12px; color: #8B6F47;');

// ==================== ENHANCED REMOVE CONFIRMATION MODAL ====================

/**
 * Show modern confirmation modal before removing item from cart
 * @param {string} productName - Name of the product to be removed
 * @param {HTMLFormElement} form - The form element to submit after confirmation
 */
function showRemoveConfirmation(productName, form) {
  // Create modal overlay
  const overlay = document.createElement('div');
  overlay.className = 'remove-confirmation-overlay';
  overlay.id = 'removeConfirmationOverlay';
  
  // Create modal container
  const modal = document.createElement('div');
  modal.className = 'remove-confirmation-modal';
  
  // Modal content
  modal.innerHTML = `
    <div class="remove-modal-header">
      <div class="remove-modal-icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <button class="remove-modal-close" onclick="closeRemoveConfirmation()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    
    <div class="remove-modal-body">
      <h3>Hapus Produk dari Keranjang?</h3>
      <p>Apakah Anda yakin ingin menghapus <strong>"${escapeHtml(productName)}"</strong> dari keranjang belanja?</p>
      <div class="remove-modal-note">
        <i class="fa-solid fa-circle-info"></i>
        <span>Tindakan ini tidak dapat dibatalkan</span>
      </div>
    </div>
    
    <div class="remove-modal-footer">
      <button class="btn-cancel-remove" onclick="closeRemoveConfirmation()">
        <i class="fa-solid fa-xmark me-2"></i>
        Batal
      </button>
      <button class="btn-confirm-remove" onclick="confirmRemove()">
        <i class="fa-solid fa-trash me-2"></i>
        Ya, Hapus
      </button>
    </div>
  `;
  
  overlay.appendChild(modal);
  document.body.appendChild(overlay);
  
  // Store form reference
  overlay.dataset.formId = form.id || 'remove-form-' + Date.now();
  if (!form.id) {
    form.id = overlay.dataset.formId;
  }
  
  // Trigger animation
  setTimeout(() => {
    overlay.classList.add('show');
    modal.classList.add('show');
  }, 10);
  
  // Prevent body scroll
  document.body.style.overflow = 'hidden';
  
  // Close on overlay click
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) {
      closeRemoveConfirmation();
    }
  });
  
  // Close on ESC key
  document.addEventListener('keydown', handleEscapeKey);
}

/**
 * Close the remove confirmation modal
 */
function closeRemoveConfirmation() {
  const overlay = document.getElementById('removeConfirmationOverlay');
  if (!overlay) return;
  
  const modal = overlay.querySelector('.remove-confirmation-modal');
  
  // Animate out
  overlay.classList.remove('show');
  modal.classList.remove('show');
  
  // Remove from DOM after animation
  setTimeout(() => {
    overlay.remove();
    document.body.style.overflow = '';
    document.removeEventListener('keydown', handleEscapeKey);
  }, 300);
}

/**
 * Confirm and submit the remove form
 */
function confirmRemove() {
  const overlay = document.getElementById('removeConfirmationOverlay');
  if (!overlay) return;
  
  const formId = overlay.dataset.formId;
  const form = document.getElementById(formId);
  
  if (form) {
    // Show loading state
    const confirmBtn = overlay.querySelector('.btn-confirm-remove');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menghapus...';
    
    // Animate row removal
    const row = form.closest('.cart-item-row');
    if (row) {
      row.style.transition = 'all 0.4s ease';
      row.style.opacity = '0';
      row.style.transform = 'translateX(100px) scale(0.9)';
    }
    
    // Submit form after animation
    setTimeout(() => {
      form.submit();
    }, 400);
    
    // Close modal
    setTimeout(() => {
      closeRemoveConfirmation();
    }, 200);
  }
}

/**
 * Handle ESC key press
 */
function handleEscapeKey(e) {
  if (e.key === 'Escape') {
    closeRemoveConfirmation();
  }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Initialize remove buttons with enhanced confirmation
 */
function initEnhancedRemoveButtons() {
  const removeButtons = document.querySelectorAll('.remove-form');

  removeButtons.forEach((form, index) => {
    // Generate unique ID if not exists
    if (!form.id) {
      form.id = 'remove-form-' + index;
    }
    
    form.addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent default submission
      
      // Get product name
      const row = this.closest('.cart-item-row');
      const productName = row ? 
        row.querySelector('.product-info h6')?.textContent || 'produk ini' : 
        'produk ini';
      
      // Show custom confirmation modal
      showRemoveConfirmation(productName, this);
    });
  });
}

// ==================== INITIALIZE ON PAGE LOAD ====================
document.addEventListener('DOMContentLoaded', function() {
  // Initialize enhanced remove buttons
  initEnhancedRemoveButtons();
  
  console.log('%c✨ Enhanced Remove Confirmation Loaded', 'color: #10B981; font-weight: bold;');
});

// ==================== SUCCESS NOTIFICATION AFTER REMOVAL ====================
/**
 * Show success notification after item removed
 */
function showRemovalSuccess(productName) {
  showNotification('success', `"${productName}" berhasil dihapus dari keranjang`);
  
  // Update cart count if needed
  updateCartSummary();
}

// ==================== PREVENT MULTIPLE FORM SUBMISSIONS ====================
document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('.remove-form');
  forms.forEach(form => {
    let isSubmitting = false;
    
    form.addEventListener('submit', function(e) {
      if (isSubmitting) {
        e.preventDefault();
        return false;
      }
      
      isSubmitting = true;
      
      // Reset after 3 seconds as failsafe
      setTimeout(() => {
        isSubmitting = false;
      }, 3000);
    });
  });
});