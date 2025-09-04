function showSizes(productId, variants, color, image) {
  const imageTag = document.getElementById('variantImage' + productId);
  const sizeContainer = document.getElementById('sizeButtons' + productId);
  const selectedSize = document.getElementById('selectedSize' + productId);
  const selectedStock = document.getElementById('selectedStock' + productId);
  const selectedPrice = document.getElementById('selectedPrice' + productId);

  imageTag.src = image;
  selectedSize.textContent = 'None';
  selectedStock.textContent = '0';
  selectedPrice.textContent = '0.00';
  sizeContainer.innerHTML = '';

  const filtered = variants.filter(v => v.product_id == productId && v.color === color);

  filtered.forEach(v => {
    const btn = document.createElement('button');
    btn.textContent = v.size;
    btn.className = 'size-btn';
    btn.onclick = () => {
      selectedSize.textContent = v.size;
      selectedStock.textContent = v.stock;
      selectedPrice.textContent = parseFloat(v.price).toFixed(2);
    };
    sizeContainer.appendChild(btn);
  });
}

function addToCart(productId) {
  const size = document.getElementById('selectedSize' + productId).textContent;
  const stock = document.getElementById('selectedStock' + productId).textContent;
  const price = document.getElementById('selectedPrice' + productId).textContent;

  if (size === 'None') {
    alert("Please select a size before adding to cart.");
    return;
  }

  alert(`Added to cart:\nProduct ID: ${productId}\nSize: ${size}\nStock: ${stock}\nPrice: ₦${price}`);
}