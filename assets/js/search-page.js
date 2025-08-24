(function(){
  function formatVnd(value){
    try { return new Intl.NumberFormat('vi-VN').format(value) + ' ₫'; }
    catch(e){ return (value||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' ₫'; }
  }

  document.addEventListener('click', function(e){
    var btn = e.target.closest('.js-size-chip');
    if (!btn) return;

    var card = btn.closest('.product-card');
    if (!card) return;

    card.querySelectorAll('.js-size-chip').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');

    var priceEl = card.querySelector('.js-price');
    if (priceEl) {
      var p = parseInt(btn.getAttribute('data-price') || '0', 10);
      
      // Check if this is a discounted product
      var priceContainer = priceEl.closest('.product-price');
      if (priceContainer && priceContainer.querySelector('.discounted-price')) {
        // This is a discounted product, we need to calculate the discounted price
        var discountBadge = priceContainer.querySelector('.promotion-badge');
        var discountPercent = 0;
        if (discountBadge) {
          discountPercent = parseFloat(discountBadge.textContent.replace('-', '').replace('%', ''));
        }
        var discountedPrice = p * (1 - discountPercent / 100);
        
        // Update the discounted price
        var discountedPriceEl = priceContainer.querySelector('.discounted-price');
        if (discountedPriceEl) {
          discountedPriceEl.textContent = formatVnd(discountedPrice);
          discountedPriceEl.setAttribute('data-price', discountedPrice.toString());
        }
        
        // Update the original price
        var originalPriceEl = priceContainer.querySelector('.original-price');
        if (originalPriceEl) {
          originalPriceEl.textContent = formatVnd(p);
        }
      } else {
        // Regular product, just update the price
        priceEl.textContent = formatVnd(p);
      }
    }

    var hidden = card.querySelector('.js-variant-id');
    if (hidden) {
      hidden.value = btn.getAttribute('data-variant-id') || '';
    }
  });
})();


