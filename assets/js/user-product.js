// Dynamic price update for size selection on product cards (category and search pages)
document.addEventListener('change', function(e) {
    const target = e.target;
    if (target.classList && target.classList.contains('js-variant')) {
        const card = target.closest('.card, .product-card');
        if (!card) return;
        const selectedOption = target.options[target.selectedIndex];
        const price = selectedOption ? parseInt(selectedOption.getAttribute('data-price'), 10) : 0;
        const priceEl = card.querySelector('.js-price');
        if (priceEl && !isNaN(price)) {
            priceEl.textContent = new Intl.NumberFormat('vi-VN').format(price) + ' ₫';
        }
        const variantIdInput = card.querySelector('.js-variant-id');
        if (variantIdInput) {
            variantIdInput.value = selectedOption ? selectedOption.value : '';
        }
    }
});

// Support clicking size chips to update price and hidden variant id
document.addEventListener('click', function(e){
    const chip = e.target.closest('.js-size-chip');
    if (!chip) return;
    const card = chip.closest('.card, .product-card, .new-topic-product-card');
    if (!card) return;
    card.querySelectorAll('.js-size-chip').forEach(btn => btn.classList.remove('active'));
    chip.classList.add('active');
    const price = parseInt(chip.getAttribute('data-price') || '0', 10);
    const priceEl = card.querySelector('.js-price');
    if (priceEl && !isNaN(price)) {
        // Check if this is a discounted product
        const priceContainer = priceEl.closest('.product-price, .new-topic-product-card-price');
        if (priceContainer && priceContainer.querySelector('.discounted-price')) {
            // This is a discounted product, we need to calculate the discounted price
            const discountPercent = parseFloat(priceContainer.querySelector('.promotion-badge')?.textContent.replace('-', '').replace('%', '') || '0');
            const discountedPrice = price * (1 - discountPercent / 100);
            
            // Update the discounted price
            const discountedPriceEl = priceContainer.querySelector('.discounted-price');
            if (discountedPriceEl) {
                discountedPriceEl.textContent = new Intl.NumberFormat('vi-VN').format(discountedPrice) + ' ₫';
                discountedPriceEl.setAttribute('data-price', discountedPrice.toString());
            }
            
            // Update the original price
            const originalPriceEl = priceContainer.querySelector('.original-price');
            if (originalPriceEl) {
                originalPriceEl.textContent = new Intl.NumberFormat('vi-VN').format(price) + ' ₫';
            }
        } else {
            // Regular product, just update the price
            const suffix = priceEl.textContent.includes('VNĐ') ? ' VNĐ' : ' ₫';
            priceEl.textContent = new Intl.NumberFormat('vi-VN').format(price) + suffix;
        }
    }
    const variantIdInput = card.querySelector('.js-variant-id');
    if (variantIdInput) {
        variantIdInput.value = chip.getAttribute('data-variant-id') || '';
    }
});

// Make product card image and title navigate to details (already anchor-wrapped in templates)


