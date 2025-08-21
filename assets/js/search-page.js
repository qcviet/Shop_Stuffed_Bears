(function(){
  function formatVnd(value){
    try { return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ'; }
    catch(e){ return (value||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' VNĐ'; }
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
      priceEl.textContent = formatVnd(p);
    }

    var hidden = card.querySelector('.js-variant-id');
    if (hidden) {
      hidden.value = btn.getAttribute('data-variant-id') || '';
    }
  });
})();


