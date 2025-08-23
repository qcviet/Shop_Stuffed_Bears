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
    const card = chip.closest('.card, .product-card');
    if (!card) return;
    card.querySelectorAll('.js-size-chip').forEach(btn => btn.classList.remove('active'));
    chip.classList.add('active');
    const price = parseInt(chip.getAttribute('data-price') || '0', 10);
    const priceEl = card.querySelector('.js-price');
    if (priceEl && !isNaN(price)) {
        // Handle both formats '245.000 đ' and '245,000 VNĐ'
        const suffix = priceEl.textContent.includes('VNĐ') ? ' VNĐ' : ' ₫';
        priceEl.textContent = new Intl.NumberFormat('vi-VN').format(price) + suffix;
    }
    const variantIdInput = card.querySelector('.js-variant-id');
    if (variantIdInput) {
        variantIdInput.value = chip.getAttribute('data-variant-id') || '';
    }
});

// Make product card image and title navigate to details (already anchor-wrapped in templates)


