(function(){
    function formatVnd(value){
        try { return new Intl.NumberFormat('vi-VN').format(value) + 'đ'; }
        catch(e){ return (value||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + 'đ'; }
    }

    document.addEventListener('click', function(e){
        var btn = e.target.closest('.js-size-chip');
        if (!btn) return;

        var card = btn.closest('.new-topic-product-card');
        if (!card) return;

        var chips = card.querySelectorAll('.js-size-chip');
        chips.forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');

        var priceEl = card.querySelector('.js-price');
        if (priceEl) {
            var p = parseInt(btn.getAttribute('data-price') || '0', 10);
            priceEl.textContent = formatVnd(p);
            priceEl.setAttribute('data-price', String(p));
        }

        var hiddenVariant = card.querySelector('.js-variant-id');
        if (hiddenVariant) {
            hiddenVariant.value = btn.getAttribute('data-variant-id') || '';
        }
    });
})();
