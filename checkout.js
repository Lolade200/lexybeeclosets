
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("checkout-form");
  const checkoutList = document.getElementById("checkout-products");
  const totalPriceEl = document.getElementById("checkout-total");
  const receiptInput = document.getElementById("receipt-upload");
  const addressInput = document.getElementById("user-address");
  const locationSelect = document.getElementById("delivery-location-select");

  // Reference hidden inputs
  const productsInput = document.getElementById("products-data");
  const totalPriceInput = document.getElementById("total-price-data");

  // Load cart data
  const cartData = JSON.parse(localStorage.getItem("cartData")) || [];

  // Display cart items
  function renderCartItems() {
    checkoutList.innerHTML = "";
    let total = 0;

    if (cartData.length === 0) {
      checkoutList.innerHTML = "<p>Your cart is empty.</p>";
      totalPriceEl.textContent = "Total Price: ₦0.00";
      totalPriceInput.value = "0.00";
      return;
    }

    cartData.forEach((item) => {
      const itemDiv = document.createElement("div");
      itemDiv.className = "cart-item";
      itemDiv.innerHTML = `
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
          <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
          <div>
            <strong class="item-name">${item.name}</strong><br>
            <span>Color: ${item.color}</span><br>
            <span>Size: ${item.size}</span><br>
            <span>Quantity: ${item.quantity}</span><br>
            <span>₦${item.price}</span>
          </div>
        </div>
      `;
      checkoutList.appendChild(itemDiv);

      const rawPrice = item.price.toString().replace(/[^\d.]/g, '');
      const numericPrice = parseFloat(rawPrice) || 0;
      total += numericPrice * (item.quantity || 1);
    });

    totalPriceEl.textContent = `Total Price: ₦${total.toLocaleString()}`;
    totalPriceInput.value = total.toFixed(2);
    productsInput.value = JSON.stringify(cartData);
  }

  // Form submission handler
  form.addEventListener("submit", function (e) {
    const address = addressInput.value.trim();
    const location = locationSelect.value;
    const receipt = receiptInput.files[0];

    if (!address || !location) {
      alert("Please enter your address and select a delivery location.");
      e.preventDefault();
      return;
    }

    if (cartData.length === 0) {
      alert("Your cart is empty.");
      e.preventDefault();
      return;
    }

    if (!receipt) {
      alert("Please upload your receipt image.");
      e.preventDefault();
      return;
    }

    console.log("Submitting order with:");
    console.log("Address:", address);
    console.log("Location:", location);
    console.log("Receipt:", receipt.name);
    console.log("Products:", cartData);
    console.log("Total Price:", totalPriceInput.value);
  });

  // Initialize cart display
  renderCartItems();
});

