document.addEventListener('DOMContentLoaded', () => {
  const cartDot = document.querySelector('.cart-dot');
  const cartItemsContainer = document.getElementById('cart-items');
  const cartDisplay = document.getElementById('cart-display');
  const cartIconWrapper = document.querySelector('.cart-icon-wrapper');
  const closeCart = document.getElementById('close-cart');
  const buyButton = document.getElementById('buy-button');
  let cartCount = 0;

  // Toggle cart overlay
  cartIconWrapper?.addEventListener('click', () => {
    cartDisplay.style.display = (cartDisplay.style.display === 'none') ? 'block' : 'none';
  });

  // Close cart overlay
  closeCart?.addEventListener('click', () => {
    cartDisplay.style.display = 'none';
  });

  // Add to cart buttons
  document.querySelectorAll('.product-card .add-to-cart').forEach(button => {
    button.addEventListener('click', () => {
      const card = button.closest('.product-card');
      const name = card.querySelector('h4').textContent;
      const price = card.querySelector('.price').textContent;
      const imageSrc = card.querySelector('img').src;
      const productId = card.dataset.id;

      cartCount++;
      cartDot.textContent = cartCount;
      cartDot.style.display = 'flex';

      // Clear previous cart items (optional)
      cartItemsContainer.innerHTML = '';

      // Create cart item
      const item = document.createElement('div');
      item.classList.add('cart-item');
      item.innerHTML = `
        <div style="display:flex; align-items:center;">
          <img src="${imageSrc}" style="width:50px; height:50px; object-fit:cover; border-radius:6px; margin-right:8px;">
          <div>
            <p style="margin:0;font-weight:bold;">${name}</p>
            <p style="margin:0;color:#f0c040;">${price}</p>
          </div>
        </div>
        <button style="background:red; color:white; border:none; border-radius:4px; padding:5px 8px; cursor:pointer; font-size:12px;">Delete</button>
      `;

      // Delete button functionality
      item.querySelector('button').addEventListener('click', () => {
        item.remove();
        cartCount--;
        cartDot.textContent = cartCount > 0 ? cartCount : '';
        if (cartCount === 0) {
          cartDot.style.display = 'none';
          buyButton.removeAttribute('data-id');
        }
      });

      cartItemsContainer.appendChild(item);

      // Attach product ID to Buy button
      buyButton.setAttribute('data-id', productId);
    });
  });

  // Buy button redirect
  buyButton.addEventListener('click', () => {
    const productId = buyButton.getAttribute('data-id');
    if (productId) {
      window.location.href = `viewproduct.php?id=${productId}`;
    } else {
      alert('Your cart is empty');
    }
  });
});

// ==================== SLIDER ====================
const slider = document.querySelector('.slider-container');
const slides = document.querySelectorAll('.slider-container img');
let index = 0;

function slideRight() {
  const slideWidth = slides[0].clientWidth;
  index = (index + 1) % slides.length;
  slider.style.transition = 'none';
  slider.style.transform = `translateX(-${index * slideWidth}px)`;
}

window.addEventListener('resize', () => {
  const slideWidth = slides[0].clientWidth;
  slider.style.transform = `translateX(-${index * slideWidth}px)`;
});

setInterval(slideRight, 3000);
