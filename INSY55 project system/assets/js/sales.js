(function () {
  const linesWrap = document.getElementById('saleLines');
  const template = document.getElementById('saleLineTemplate');
  const addBtn = document.getElementById('addLineBtn');

  function money(n) {
    return '₱' + (Number(n) || 0).toFixed(2);
  }

  function recalc() {
    let subtotal = 0;
    document.querySelectorAll('.sale-line').forEach(function (line) {
      const select = line.querySelector('.product-select');
      const qty = Number(line.querySelector('.qty-input')?.value || 0);
      const opt = select?.selectedOptions?.[0];
      const price = Number(opt?.dataset?.price || 0);
      const stock = Number(opt?.dataset?.stock || 0);
      if (qty > stock && stock > 0) {
        line.querySelector('.qty-input').setCustomValidity('Quantity exceeds available stock (' + stock + ')');
      } else {
        line.querySelector('.qty-input').setCustomValidity('');
      }
      subtotal += price * qty;
    });

    const discountPercent = Number(document.getElementById('discountPercent')?.value || 0);
    const vatPercent = Number(document.getElementById('vatPercent')?.value || 0);
    const amountPaid = Number(document.getElementById('amountPaid')?.value || 0);
    const discount = subtotal * (discountPercent / 100);
    const after = Math.max(0, subtotal - discount);
    const vat = after * (vatPercent / 100);
    const total = after + vat;
    const change = Math.max(0, amountPaid - total);

    const set = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = money(val);
    };
    set('subtotalDisplay', subtotal);
    set('discountDisplay', discount);
    set('vatDisplay', vat);
    set('totalDisplay', total);
    set('changeDisplay', change);
  }

  function bindLine(line) {
    line.querySelector('.product-select')?.addEventListener('change', recalc);
    line.querySelector('.qty-input')?.addEventListener('input', recalc);
    line.querySelector('.remove-line')?.addEventListener('click', function () {
      if (document.querySelectorAll('.sale-line').length <= 1) return;
      line.remove();
      recalc();
    });
  }

  document.querySelectorAll('.sale-line').forEach(bindLine);
  addBtn?.addEventListener('click', function () {
    if (!template || !linesWrap) return;
    const node = template.content.cloneNode(true);
    const line = node.querySelector('.sale-line');
    linesWrap.appendChild(node);
    bindLine(line);
    recalc();
  });

  ['discountPercent', 'vatPercent', 'amountPaid'].forEach(function (id) {
    document.getElementById(id)?.addEventListener('input', recalc);
  });

  recalc();
})();
