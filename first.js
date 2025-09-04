// ===================== SLIDER =====================
const sliderContainer = document.querySelector('.slider-container');
let slides = document.querySelectorAll('.slide');
let currentIndex = 0;
let sliderInterval;

function getVisibleSlidesCount() {
  // ✅ Matches CSS breakpoints
  if (window.innerWidth >= 1024) return 3;
  if (window.innerWidth >= 768) return 2;
  return 1;
}

function showSlides(index) {
  if (!sliderContainer || slides.length === 0) return;

  const visibleSlides = getVisibleSlidesCount();
  const slide = slides[0];

  if (!slide) return;

  // ✅ Include margins/gaps
  const slideStyle = getComputedStyle(slide);
  const slideWidth = slide.offsetWidth + parseInt(slideStyle.marginRight || 0);

  const maxIndex = Math.max(slides.length - visibleSlides, 0);

  if (index > maxIndex) currentIndex = 0;       // loop back
  else if (index < 0) currentIndex = maxIndex;  // loop to end
  else currentIndex = index;

  // ✅ translate by actual pixel width
  const offset = currentIndex * slideWidth;
  sliderContainer.style.transform = `translateX(-${offset}px)`;
}

function nextSlide() {
  currentIndex += getVisibleSlidesCount(); // move by "page"
  showSlides(currentIndex);
}

function startSlider() {
  if (slides.length > 1) {
    showSlides(currentIndex);
    clearInterval(sliderInterval); // avoid duplicate intervals
    sliderInterval = setInterval(nextSlide, 5000); // autoplay every 5s
  }
}

// Reset slider on resize
window.addEventListener('resize', () => {
  slides = document.querySelectorAll('.slide');
  showSlides(currentIndex);
  startSlider();
});

// Ensure correct position on load
window.addEventListener('load', () => {
  slides = document.querySelectorAll('.slide');
  showSlides(currentIndex);
  startSlider();
});



// ===================== CART FUNCTIONALITY =====================
const cartDot = document.querySelector('.cart-dot');
const cartItemsContainer = document.getElementById('cart-items');
const cartDisplay = document.getElementById('cart-display');
const cartIconWrapper = document.querySelector('.cart-icon-wrapper');

if (cartIconWrapper && cartDisplay) {
  cartIconWrapper.addEventListener('click', () => {
    cartDisplay.style.display = cartDisplay.style.display === 'none' ? 'block' : 'none';
  });
}

document.querySelectorAll('.product-card').forEach(card => {
  const addToCartBtn = card.querySelector('.add-to-cart');
  if (addToCartBtn) {
    addToCartBtn.addEventListener('click', () => {
      const name = card.querySelector('h4').textContent;
      const price = card.querySelector('.price').textContent;
      const imageSrc = card.querySelector('img').getAttribute('src');

      if (cartDot) cartDot.style.display = 'block';

      const item = document.createElement('div');
      item.style.display = 'flex';
      item.style.alignItems = 'center';
      item.style.marginBottom = '15px';
      item.innerHTML = `
        <img src="${imageSrc}" style="width:60px; height:60px; object-fit:cover; border-radius:6px; margin-right:10px;">
        <div>
          <p style="margin:0; font-weight:bold;">${name}</p>
          <p style="margin:0; color:#f0c040;">${price}</p>
        </div>
      `;
      cartItemsContainer.appendChild(item);
    });
  }
});

// Close cart button functionality
const closeCart = document.getElementById('close-cart');
if (closeCart && cartDisplay) {
  closeCart.addEventListener('click', () => {
    cartDisplay.style.display = 'none';
  });
}

// Buy Now button
const buyButton = document.getElementById('buy-button');
if (buyButton) {
  buyButton.addEventListener('click', () => {
    window.location.href = 'login.php';
  });
}


// ===================== HAMBURGER MENU =====================
function toggleMenu() {
  const nav = document.querySelector('.nav-links');
  nav.classList.toggle('show');
}

// Optional: close menu when clicking outside
document.addEventListener('click', (e) => {
  const nav = document.querySelector('.nav-links');
  const hamburger = document.querySelector('.hamburger');
  if (nav && nav.classList.contains('show') && !nav.contains(e.target) && !hamburger.contains(e.target)) {
    nav.classList.remove('show');
  }
});
