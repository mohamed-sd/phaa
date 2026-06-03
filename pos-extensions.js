/**
 * Pharmacy POS - Extensions
 * نوافذ الدفع المتقدمة، المرتجعات، والفواتير الآجلة.
 * يعتمد على الدوال العامة في app.js:
 *   appState, buildCartPayload, findProduct, getUnitSize, getUnitPrice,
 *   loadProducts, renderCategories, renderProducts, renderCart,
 *   activeWindow, closeSaleWindow, formatCurrency, showNotification, callAPI
 */

(function () {
  'use strict';

  // ============================================
  // الحالة المخزّنة مؤقتاً
  // ============================================
  let _settings = null;
  let _paymentMethods = [];
  let _insuranceCompanies = [];
  let _customers = [];

  function taxRate() {
    const r = parseFloat(_settings && _settings.tax_rate);
    return isNaN(r) ? 5 : r;
  }
  function insuranceEnabled() {
    return _settings && _settings.uses_health_insurance === '1';
  }
  function money(n) {
    return (typeof formatCurrency === 'function') ? formatCurrency(+(+n).toFixed(2)) : (+n).toFixed(2);
  }
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
  }

  /** تحميل البيانات المرجعية مرة واحدة (طرق الدفع، الإعدادات، التأمين، العملاء). */
  async function ensureRefData(force) {
    if (_settings && !force) return;
    try {
      const res = await fetch('./api.php?action=get_settings');
      const json = await res.json();
      _settings = (json.success && json.data) ? json.data : {};
    } catch (e) { _settings = {}; }

    try { _paymentMethods = await callAPI('get_payment_methods&active=1'); } catch (e) { _paymentMethods = []; }
    try { _customers = await callAPI('get_customers'); } catch (e) { _customers = []; }
    if (insuranceEnabled()) {
      try { _insuranceCompanies = await callAPI('get_insurance_companies&active=1'); } catch (e) { _insuranceCompanies = []; }
    } else {
      _insuranceCompanies = [];
    }
  }

  // ============================================
  // أدوات النافذة المنبثقة
  // ============================================
  function openModal(html, wide) {
    const host = document.getElementById('posModalHost');
    host.innerHTML = `<div class="pos-modal-overlay">
      <div class="pos-modal${wide ? ' wide' : ''}">${html}</div>
    </div>`;
    const overlay = host.querySelector('.pos-modal-overlay');
    overlay.addEventListener('mousedown', (e) => { if (e.target === overlay) closeModal(); });
    return host.querySelector('.pos-modal');
  }
  function closeModal() {
    document.getElementById('posModalHost').innerHTML = '';
  }
  window.closePosModal = closeModal;

  // ============================================
  // Feature 4 + 5 + 2: نافذة الدفع (Checkout)
  // ============================================
  window.openCheckoutModal = async function () {
    const payload = buildCartPayload();
    const cartSize = Object.keys(appState.cart || {}).length;

    // تشخيص دقيق: نميّز بين سلة فارغة فعلاً وعناصر تعذّر حلّها
    console.log('[POS] محاولة دفع — عناصر السلة:', cartSize,
                '| قابلة للبيع:', payload.items.length,
                '| غير قابلة للحلّ:', payload.unresolved.length, payload);

    if (!payload.items.length) {
      if (cartSize === 0) {
        showNotification('السلة فارغة — أضف منتجات أولاً', 'error');
      } else {
        const names = payload.unresolved.map(u => u.name).join('، ');
        showNotification('تعذّر تجهيز المنتجات للبيع (قد تكون أُلغيت أو نفد سعرها): ' + names + '. أعد إضافتها.', 'error');
      }
      return;
    }

    // تحذير إن سقط بعض العناصر فقط (نكمل بالباقي ولا نفشل بصمت)
    if (payload.unresolved.length) {
      const names = payload.unresolved.map(u => u.name).join('، ');
      showNotification('تنبيه: لم يتم تضمين بعض المنتجات غير المتاحة: ' + names, 'error');
    }

    await ensureRefData();
    const win = activeWindow();

    const pmOptions = _paymentMethods.map(m => `<option value="${m.id}">${esc(m.name)}</option>`).join('');
    const custOptions = _customers.map(c => `<option value="${esc(c.name)}"></option>`).join('');
    const insBlock = insuranceEnabled() ? `
      <div class="pos-field">
        <label><i class="fas fa-shield-heart"></i> شركة التأمين</label>
        <select id="coInsurance">
          <option value="">بدون تأمين</option>
          ${_insuranceCompanies.map(ic => `<option value="${ic.id}" data-pct="${ic.discount_percentage}">${esc(ic.name)} (${(+ic.discount_percentage).toFixed(0)}%)</option>`).join('')}
        </select>
      </div>` : '';

    const html = `
      <div class="pos-modal-head">
        <h3><i class="fas fa-cash-register"></i> إتمام البيع</h3>
        <button class="pos-modal-close" onclick="closePosModal()">&times;</button>
      </div>
      <div class="pos-modal-body">
        <div class="pos-grid-2">
          <div class="pos-field">
            <label><i class="fas fa-user"></i> العميل (اختياري)</label>
            <input id="coCustomer" list="posCustomerList" value="${esc(win.customerName||'')}" placeholder="اسم العميل">
            <datalist id="posCustomerList">${custOptions}</datalist>
          </div>
          <div class="pos-field">
            <label><i class="fas fa-credit-card"></i> طريقة الدفع</label>
            <select id="coPayment">${pmOptions}</select>
          </div>
        </div>
        <div class="pos-grid-2">
          <div class="pos-field">
            <label><i class="fas fa-tag"></i> خصم (مبلغ)</label>
            <input id="coDiscount" type="number" min="0" step="0.01" value="0">
          </div>
          ${insBlock || '<div></div>'}
        </div>

        <div class="pos-summary" id="coSummary"></div>

        <div class="pos-field">
          <label><i class="fas fa-money-bill-wave"></i> المبلغ المدفوع من العميل</label>
          <input id="coPaid" type="number" min="0" step="0.01" value="0">
          <small style="color:#64748b">اتركه مساوياً للمستحق للدفع الكامل، أو أقل للدفع الجزئي (فاتورة آجلة).</small>
        </div>
      </div>
      <div class="pos-modal-foot">
        <button class="pos-btn pos-btn-secondary" onclick="closePosModal()">إلغاء</button>
        <button class="pos-btn pos-btn-primary" id="coConfirm"><i class="fas fa-check"></i> تأكيد البيع</button>
      </div>`;

    openModal(html);

    const elDiscount = document.getElementById('coDiscount');
    const elPaid = document.getElementById('coPaid');
    const elIns = document.getElementById('coInsurance');
    let _customerPayable = 0;

    function recompute() {
      const subtotal = payload.subtotal;
      const tax = +(subtotal * taxRate() / 100).toFixed(2);
      let discount = parseFloat(elDiscount.value) || 0;
      if (discount < 0) discount = 0;
      let total = +(subtotal + tax - discount).toFixed(2);
      if (total < 0) total = 0;

      let insPct = 0, insName = '';
      if (elIns && elIns.value) {
        const opt = elIns.options[elIns.selectedIndex];
        insPct = parseFloat(opt.getAttribute('data-pct')) || 0;
        insName = opt.textContent;
      }
      const insDue = +(total * insPct / 100).toFixed(2);
      _customerPayable = +(total - insDue).toFixed(2);

      let rows = `
        <div class="row"><span>المجموع قبل الضريبة</span><span>${money(subtotal)}</span></div>
        <div class="row"><span>الضريبة (${taxRate()}%)</span><span>${money(tax)}</span></div>
        <div class="row"><span>الخصم</span><span>- ${money(discount)}</span></div>
        <div class="row grand"><span>الإجمالي</span><span>${money(total)}</span></div>`;
      if (insDue > 0) {
        rows += `<div class="row insurance"><span>خصم التأمين (${insName})</span><span>- ${money(insDue)}</span></div>
                 <div class="row insurance"><span>مستحق على شركة التأمين</span><span>${money(insDue)}</span></div>
                 <div class="row grand"><span>المطلوب من العميل</span><span>${money(_customerPayable)}</span></div>`;
      }
      const paid = parseFloat(elPaid.value) || 0;
      const remaining = +(_customerPayable - Math.min(paid, _customerPayable)).toFixed(2);
      if (remaining > 0) {
        rows += `<div class="row remaining"><span>المتبقي (آجل)</span><span>${money(remaining)}</span></div>`;
      }
      document.getElementById('coSummary').innerHTML = rows;
    }

    // المدفوع الافتراضي = كامل المستحق
    function setFullPaid() { recompute(); elPaid.value = _customerPayable.toFixed(2); recompute(); }

    elDiscount.addEventListener('input', () => { recompute(); });
    elPaid.addEventListener('input', recompute);
    if (elIns) elIns.addEventListener('change', setFullPaid);
    setFullPaid();

    document.getElementById('coConfirm').addEventListener('click', async () => {
      const btn = document.getElementById('coConfirm');
      // حارس نهائي: نتحقق من العناصر لحظة التأكيد (تفادي إرسال فاتورة فارغة)
      if (!payload.items || !payload.items.length) {
        showNotification('لا توجد منتجات صالحة لإتمام البيع', 'error');
        return;
      }
      btn.disabled = true;
      try {
        const custName = document.getElementById('coCustomer').value.trim();
        const matched = _customers.find(c => c.name === custName);
        const discount = parseFloat(elDiscount.value) || 0;
        const tax = +(payload.subtotal * taxRate() / 100).toFixed(2);
        const paid = parseFloat(elPaid.value) || 0;

        const body = {
          branch_id: 1,
          items: payload.items,
          tax_amount: tax,
          discount_amount: discount,
          payment_method_id: parseInt(document.getElementById('coPayment').value) || null,
          customer_id: matched ? parseInt(matched.id) : null,
          customer_name: custName,
          paid_amount: paid
        };
        if (elIns && elIns.value) body.insurance_company_id = parseInt(elIns.value);

        console.log('[POS] إرسال إنشاء الفاتورة — عدد العناصر:', body.items.length, body);
        const result = await callAPI('create_invoice', 'POST', body);

        // طباعة الفاتورة قبل تفريغ النافذة
        printSaleInvoice(result, payload, {
          customer: custName,
          payment: document.getElementById('coPayment').options[document.getElementById('coPayment').selectedIndex].text,
          discount, tax
        });

        // تفريغ النافذة الحالية وتحديث المخزون
        const w = activeWindow();
        w.cart = {}; w.customerName = ''; w.customerId = null;
        appState.cart = w.cart;
        saveCart();
        try {
          appState.data = await loadProducts();
          renderCategories(); renderProducts();
        } catch (e) { /* تجاهل */ }
        renderCart();
        closeModal();
        showNotification('تم حفظ الفاتورة ' + result.invoice_number, 'success');
      } catch (err) {
        showNotification('خطأ في إتمام البيع: ' + err.message, 'error');
        btn.disabled = false;
      }
    });
  };

  // ============================================
  // الفاتورة المطبوعة للعميل (Feature 1)
  // فاتورة مخصّصة للعميل: لا تتضمن أي معلومات تأمين أو رصيد آجل/متبقي.
  // ملاحظة: تفاصيل التأمين والائتمان تبقى متاحة في شاشة التفاصيل والتقارير فقط.
  // ============================================
  function currentCashierName() {
    try { return (JSON.parse(localStorage.getItem('auth_user') || '{}').full_name) || ''; }
    catch (e) { return ''; }
  }
  function pharmacyName() {
    return (_settings && _settings.pharmacy_name) ? _settings.pharmacy_name : 'صيدليات الحياة';
  }

  /**
   * مُولّد إيصال العميل من كائن موحّد.
   * @param {{invoice_number, datetime, customer, cashier, payment, items:[{name,qty,price,total}], subtotal, tax, discount, total}} d
   */
  function printReceipt(d) {
    const rows = (d.items || []).map(it =>
      `<tr><td>${esc(it.name)}</td><td>${it.qty}</td><td>${money(it.price)}</td><td>${money(it.total)}</td></tr>`
    ).join('');

    const taxRow = (d.tax && d.tax > 0) ? `<div class="srow"><span>الضريبة:</span><span>${money(d.tax)}</span></div>` : '';
    const discRow = (d.discount && d.discount > 0) ? `<div class="srow"><span>الخصم:</span><span>- ${money(d.discount)}</span></div>` : '';
    const custRow = d.customer ? `<br><strong>العميل:</strong> ${esc(d.customer)}` : '';

    const w = window.open('', '_blank', 'width=720,height=900');
    if (!w) { showNotification('فشل فتح نافذة الطباعة (مانع النوافذ المنبثقة)', 'error'); return; }
    w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>فاتورة ${esc(d.invoice_number)}</title>
      <style>
        body{font-family:'Segoe UI',Arial,sans-serif;padding:24px;color:#0f172a}
        h1{color:#0891b2;text-align:center;font-size:26px;margin:0 0 4px}
        .sub{text-align:center;color:#64748b;font-size:13px;margin-bottom:8px}
        .meta{display:flex;justify-content:space-between;font-size:13px;background:#f0fdfa;padding:14px;border-radius:10px;margin:16px 0}
        table{width:100%;border-collapse:collapse;margin:14px 0}
        th{background:#0891b2;color:#fff;padding:10px;text-align:right}
        td{padding:10px;border-bottom:1px solid #e2e8f0;text-align:right}
        .summary{margin-top:16px}
        .srow{display:flex;justify-content:space-between;padding:5px 0;font-weight:600}
        .srow.total{background:#0891b2;color:#fff;padding:12px;border-radius:8px;font-size:17px;margin-top:8px}
        .footer{text-align:center;margin-top:24px;color:#64748b;font-size:12px;border-top:2px dashed #e2e8f0;padding-top:14px}
        @media print{.noprint{display:none}}
      </style></head><body>
      <h1>💊 ${esc(pharmacyName())}</h1>
      <div class="sub">فاتورة مبيعات${(_settings && _settings.address) ? ' — ' + esc(_settings.address) : ''}${(_settings && _settings.phone) ? ' — هاتف: ' + esc(_settings.phone) : ''}</div>
      <div class="meta">
        <div><strong>رقم الفاتورة:</strong> ${esc(d.invoice_number)}<br><strong>التاريخ:</strong> ${esc(d.datetime)}${custRow}</div>
        <div><strong>طريقة الدفع:</strong> ${esc(d.payment || 'نقداً')}<br><strong>الكاشير:</strong> ${esc(d.cashier || '')}</div>
      </div>
      <table><thead><tr><th>الصنف</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead><tbody>${rows}</tbody></table>
      <div class="summary">
        <div class="srow"><span>المجموع قبل الضريبة:</span><span>${money(d.subtotal)}</span></div>
        ${taxRow}
        ${discRow}
        <div class="srow total"><span>الإجمالي النهائي:</span><span>${money(d.total)}</span></div>
      </div>
      <div class="footer">شكراً لتعاملكم معنا 💚</div>
      <div class="noprint" style="text-align:center;margin-top:18px"><button onclick="window.print()" style="padding:10px 26px;background:#0891b2;color:#fff;border:none;border-radius:8px;cursor:pointer">🖨️ طباعة</button></div>
      </body></html>`);
    w.document.close();
  }

  // طباعة فاتورة بيع جديدة (بعد إتمامها) — تستخدم الإيصال الموحّد بدون معلومات تأمين/آجل
  function printSaleInvoice(result, payload, meta) {
    printReceipt({
      invoice_number: result.invoice_number,
      datetime: new Date().toLocaleString('ar-EG'),
      customer: meta.customer || '',
      cashier: currentCashierName(),
      payment: meta.payment || '',
      items: payload.items.map(it => ({
        name: it.name || (findProduct(String(it.product_id)) || {}).name || ('#' + it.product_id),
        qty: it.unit_quantity,
        price: it.unit_price,
        total: it.unit_price * it.unit_quantity
      })),
      subtotal: result.subtotal,
      tax: meta.tax,
      discount: meta.discount,
      total: result.total_amount
    });
  }

  // إعادة طباعة فاتورة محفوظة كما صدرت — بدون أي تعديل (Feature 2)
  async function reprintInvoice(invoiceId) {
    try {
      const inv = await callAPI('get_invoice&id=' + invoiceId);
      printReceipt({
        invoice_number: inv.invoice_number,
        datetime: new Date(inv.created_at).toLocaleString('ar-EG'),
        customer: inv.customer_db_name || inv.customer_name || '',
        cashier: inv.cashier_name || '',
        payment: inv.payment_method || '',
        items: (inv.items || []).map(it => ({
          name: it.product_name,
          qty: it.unit_quantity,
          price: it.unit_price,
          total: parseFloat(it.total_price)
        })),
        subtotal: parseFloat(inv.subtotal),
        tax: parseFloat(inv.tax_amount),
        discount: parseFloat(inv.discount_amount || 0),
        total: parseFloat(inv.total_amount)
      });
    } catch (err) { showNotification('تعذّر طباعة الفاتورة: ' + err.message, 'error'); }
  }
  window.reprintInvoice = reprintInvoice;

  function statusLabel(s) {
    return ({ paid:'مدفوعة', partial:'مدفوعة جزئياً', unpaid:'غير مدفوعة', returned:'مرتجعة', partially_returned:'مرتجعة جزئياً' }[s]) || s;
  }
  window.posStatusLabel = statusLabel;

  // ============================================
  // Feature 1: نافذة إرجاع فاتورة
  // ============================================
  window.openReturnModal = async function () {
    const html = `
      <div class="pos-modal-head">
        <h3><i class="fas fa-rotate-left"></i> إرجاع فاتورة</h3>
        <button class="pos-modal-close" onclick="closePosModal()">&times;</button>
      </div>
      <div class="pos-modal-body">
        <div class="pos-field">
          <label>رقم الفاتورة</label>
          <div style="display:flex;gap:8px">
            <input id="retNumber" placeholder="INV-...">
            <button class="pos-btn pos-btn-primary" style="flex:0 0 120px" id="retSearch"><i class="fas fa-search"></i> بحث</button>
          </div>
        </div>
        <div id="retResult"></div>
      </div>`;
    openModal(html, true);
    document.getElementById('retSearch').addEventListener('click', doReturnSearch);
    document.getElementById('retNumber').addEventListener('keydown', e => { if (e.key === 'Enter') doReturnSearch(); });
  };

  async function doReturnSearch() {
    const num = document.getElementById('retNumber').value.trim();
    if (!num) { showNotification('أدخل رقم الفاتورة', 'error'); return; }
    try {
      const inv = await callAPI('search_invoice_for_return&invoice_number=' + encodeURIComponent(num));
      renderReturnResult(inv);
    } catch (err) {
      document.getElementById('retResult').innerHTML = `<p style="color:#dc2626">${esc(err.message)}</p>`;
    }
  }

  function renderReturnResult(inv) {
    const items = inv.items || [];
    const rows = items.map(it => {
      const returnable = parseInt(it.returnable_qty);
      return `<tr>
        <td>${esc(it.product_name)}</td>
        <td>${it.unit_quantity} (${esc(it.unit)})</td>
        <td>${money(it.unit_price)}</td>
        <td>${it.returned_qty}</td>
        <td>
          ${returnable > 0 ? `<input type="number" min="1" max="${returnable}" value="${returnable}" class="ret-qty" data-id="${it.id}" style="width:70px;padding:6px;border:2px solid #e2e8f0;border-radius:8px">
          <button class="pos-btn pos-btn-secondary ret-item-btn" data-id="${it.id}" style="flex:0;padding:6px 12px;display:inline-block">إرجاع</button>`
          : '<span style="color:#94a3b8">تم الإرجاع</span>'}
        </td></tr>`;
    }).join('');

    document.getElementById('retResult').innerHTML = `
      <div class="pos-summary">
        <div class="row"><span>رقم الفاتورة</span><span>${esc(inv.invoice_number)}</span></div>
        <div class="row"><span>العميل</span><span>${esc(inv.customer_db_name || inv.customer_name || 'نقدي')}</span></div>
        <div class="row"><span>طريقة الدفع</span><span>${esc(inv.payment_method || inv.payment_method_name_db || '')}</span></div>
        <div class="row"><span>الحالة</span><span><span class="pos-status ${inv.status}">${statusLabel(inv.status)}</span></span></div>
        <div class="row grand"><span>الإجمالي</span><span>${money(inv.total_amount)}</span></div>
      </div>
      <table class="pos-table">
        <thead><tr><th>الصنف</th><th>الكمية المباعة</th><th>السعر</th><th>المرتجع</th><th>إجراء</th></tr></thead>
        <tbody>${rows}</tbody>
      </table>
      <div class="pos-modal-foot" style="padding:16px 0 0">
        <button class="pos-btn pos-btn-secondary" onclick="closePosModal()">إغلاق</button>
        <button class="pos-btn pos-btn-primary" id="retFullBtn" ${inv.status === 'returned' ? 'disabled' : ''}>
          <i class="fas fa-rotate-left"></i> إرجاع الفاتورة بالكامل
        </button>
      </div>`;

    document.querySelectorAll('.ret-item-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-id');
        const qty = parseInt(document.querySelector(`.ret-qty[data-id="${id}"]`).value);
        if (!qty || qty <= 0) { showNotification('أدخل كمية صحيحة', 'error'); return; }
        if (!confirm('تأكيد إرجاع هذا المنتج؟')) return;
        try {
          await callAPI('return_invoice_item', 'POST', { invoice_item_id: parseInt(id), quantity: qty });
          showNotification('تم إرجاع المنتج', 'success');
          const inv2 = await callAPI('search_invoice_for_return&id=' + inv.id);
          renderReturnResult(inv2);
          refreshAfterStockChange();
        } catch (err) { showNotification('خطأ: ' + err.message, 'error'); }
      });
    });

    const fullBtn = document.getElementById('retFullBtn');
    if (fullBtn) fullBtn.addEventListener('click', async () => {
      if (!confirm('تأكيد إرجاع كامل الفاتورة؟')) return;
      try {
        const r = await callAPI('return_full_invoice', 'POST', { invoice_id: parseInt(inv.id) });
        showNotification('تم إرجاع الفاتورة بالكامل (' + money(r.total_refund) + ')', 'success');
        const inv2 = await callAPI('search_invoice_for_return&id=' + inv.id);
        renderReturnResult(inv2);
        refreshAfterStockChange();
      } catch (err) { showNotification('خطأ: ' + err.message, 'error'); }
    });
  }

  async function refreshAfterStockChange() {
    try {
      appState.data = await loadProducts();
      renderCategories(); renderProducts(); renderCart();
    } catch (e) { /* تجاهل */ }
  }

  // ============================================
  // Feature 4: نافذة الفواتير الآجلة (Outstanding)
  // ============================================
  window.openOutstandingModal = async function () {
    await ensureRefData();
    const html = `
      <div class="pos-modal-head">
        <h3><i class="fas fa-hand-holding-dollar"></i> الفواتير الآجلة وغير المسددة</h3>
        <button class="pos-modal-close" onclick="closePosModal()">&times;</button>
      </div>
      <div class="pos-modal-body">
        <div class="pos-field">
          <div style="display:flex;gap:8px">
            <input id="outSearch" placeholder="بحث برقم الفاتورة أو اسم العميل">
            <button class="pos-btn pos-btn-primary" style="flex:0 0 120px" id="outSearchBtn"><i class="fas fa-search"></i> بحث</button>
          </div>
        </div>
        <div id="outResult">جاري التحميل...</div>
      </div>`;
    openModal(html, true);
    document.getElementById('outSearchBtn').addEventListener('click', () => loadOutstanding());
    document.getElementById('outSearch').addEventListener('keydown', e => { if (e.key === 'Enter') loadOutstanding(); });
    loadOutstanding();
  };

  async function loadOutstanding() {
    const q = (document.getElementById('outSearch').value || '').trim();
    try {
      const data = await callAPI('get_outstanding_invoices' + (q ? '&search=' + encodeURIComponent(q) : ''));
      const rows = (data.invoices || []).map(i => `
        <tr>
          <td>${esc(i.invoice_number)}</td>
          <td>${esc(i.customer_db_name || i.customer_name || 'نقدي')}</td>
          <td>${money(i.total_amount)}</td>
          <td>${money(i.paid_amount)}</td>
          <td style="color:#dc2626;font-weight:700">${money(i.remaining_amount)}</td>
          <td><button class="pos-btn pos-btn-primary pay-btn" data-id="${i.id}" data-rem="${i.remaining_amount}" data-num="${esc(i.invoice_number)}" style="padding:6px 12px">دفع</button></td>
        </tr>`).join('');
      document.getElementById('outResult').innerHTML = `
        <div class="pos-summary"><div class="row grand"><span>إجمالي المتبقي</span><span>${money(data.totals.total_outstanding)}</span></div>
          <div class="row"><span>عدد الفواتير</span><span>${data.totals.count}</span></div></div>
        <table class="pos-table"><thead><tr><th>رقم</th><th>العميل</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>إجراء</th></tr></thead>
        <tbody>${rows || '<tr><td colspan="6" style="text-align:center;color:#94a3b8">لا توجد فواتير آجلة</td></tr>'}</tbody></table>`;
      document.querySelectorAll('.pay-btn').forEach(b => b.addEventListener('click', () => openPaymentForm(b.dataset.id, b.dataset.rem, b.dataset.num)));
    } catch (err) {
      document.getElementById('outResult').innerHTML = `<p style="color:#dc2626">${esc(err.message)}</p>`;
    }
  }

  function openPaymentForm(id, remaining, number) {
    const pmOptions = _paymentMethods.map(m => `<option value="${m.id}">${esc(m.name)}</option>`).join('');
    const html = `
      <div class="pos-modal-head">
        <h3><i class="fas fa-money-bill-wave"></i> استلام دفعة - ${esc(number)}</h3>
        <button class="pos-modal-close" onclick="closePosModal()">&times;</button>
      </div>
      <div class="pos-modal-body">
        <div class="pos-summary"><div class="row remaining"><span>المتبقي</span><span>${money(remaining)}</span></div></div>
        <div class="pos-field"><label>المبلغ المدفوع</label>
          <input id="payAmount" type="number" min="0.01" step="0.01" max="${remaining}" value="${(+remaining).toFixed(2)}"></div>
        <div class="pos-field"><label>طريقة الدفع</label><select id="payMethod">${pmOptions}</select></div>
      </div>
      <div class="pos-modal-foot">
        <button class="pos-btn pos-btn-secondary" id="payBack">رجوع</button>
        <button class="pos-btn pos-btn-primary" id="paySubmit"><i class="fas fa-check"></i> تأكيد الدفعة</button>
      </div>`;
    openModal(html);
    document.getElementById('payBack').addEventListener('click', () => window.openOutstandingModal());
    document.getElementById('paySubmit').addEventListener('click', async () => {
      const amount = parseFloat(document.getElementById('payAmount').value);
      if (!amount || amount <= 0) { showNotification('أدخل مبلغاً صحيحاً', 'error'); return; }
      try {
        const r = await callAPI('add_invoice_payment', 'POST', {
          invoice_id: parseInt(id), amount,
          payment_method_id: parseInt(document.getElementById('payMethod').value) || null
        });
        showNotification(r.status === 'paid' ? 'تم سداد الفاتورة بالكامل ✔' : 'تم تسجيل الدفعة', 'success');
        window.openOutstandingModal();
      } catch (err) { showNotification('خطأ: ' + err.message, 'error'); }
    });
  }

  // ============================================
  // Feature 2: سجل فواتير الكاشير لليوم الحالي
  // ============================================
  const HIST_PAGE = 10;
  let _histOffset = 0;
  let _histSearch = '';

  window.openHistoryModal = async function () {
    _histOffset = 0; _histSearch = '';
    await ensureRefData();
    const html = `
      <div class="pos-modal-head">
        <h3><i class="fas fa-clock-rotate-left"></i> فواتير اليوم</h3>
        <button class="pos-modal-close" onclick="closePosModal()">&times;</button>
      </div>
      <div class="pos-modal-body">
        <div class="pos-field">
          <div style="display:flex;gap:8px">
            <input id="histSearch" placeholder="بحث برقم الفاتورة">
            <button class="pos-btn pos-btn-primary" style="flex:0 0 120px" id="histSearchBtn"><i class="fas fa-search"></i> بحث</button>
          </div>
        </div>
        <div id="histResult">جاري التحميل...</div>
        <div id="histPager" style="display:flex;justify-content:space-between;align-items:center;margin-top:12px"></div>
      </div>`;
    openModal(html, true);
    document.getElementById('histSearchBtn').addEventListener('click', () => { _histOffset = 0; _histSearch = document.getElementById('histSearch').value.trim(); loadHistory(); });
    document.getElementById('histSearch').addEventListener('keydown', e => { if (e.key === 'Enter') { _histOffset = 0; _histSearch = e.target.value.trim(); loadHistory(); } });
    loadHistory();
  };

  async function loadHistory() {
    const host = document.getElementById('histResult');
    if (!host) return;
    host.innerHTML = 'جاري التحميل...';
    try {
      const qs = `&limit=${HIST_PAGE}&offset=${_histOffset}` + (_histSearch ? '&search=' + encodeURIComponent(_histSearch) : '');
      const data = await callAPI('get_cashier_invoices' + qs);
      const rows = (data.invoices || []).map(i => `
        <tr>
          <td>${esc(i.invoice_number)}</td>
          <td>${new Date(i.created_at).toLocaleString('ar-EG')}</td>
          <td>${esc(i.customer_db_name || i.customer_name || 'نقدي')}</td>
          <td>${money(i.total_amount)}</td>
          <td>${esc(i.payment_method || 'نقداً')}</td>
          <td><span class="pos-status ${i.status}">${statusLabel(i.status)}</span></td>
          <td style="white-space:nowrap">
            <button class="pos-btn pos-btn-secondary hist-view" data-id="${i.id}" style="padding:5px 10px">عرض</button>
            <button class="pos-btn pos-btn-primary hist-print" data-id="${i.id}" style="padding:5px 10px">طباعة</button>
          </td>
        </tr>`).join('');

      host.innerHTML = `
        <table class="pos-table">
          <thead><tr><th>رقم</th><th>التاريخ والوقت</th><th>العميل</th><th>الإجمالي</th><th>الدفع</th><th>الحالة</th><th>إجراءات</th></tr></thead>
          <tbody>${rows || '<tr><td colspan="7" style="text-align:center;color:#94a3b8">لا توجد فواتير اليوم</td></tr>'}</tbody>
        </table>`;

      // الترقيم
      const total = data.total || 0;
      const from = total ? _histOffset + 1 : 0;
      const to = Math.min(_histOffset + HIST_PAGE, total);
      document.getElementById('histPager').innerHTML = `
        <span style="color:#64748b;font-size:13px">عرض ${from}–${to} من ${total}</span>
        <span>
          <button class="pos-btn pos-btn-secondary" id="histPrev" style="padding:6px 14px" ${_histOffset <= 0 ? 'disabled' : ''}>السابق</button>
          <button class="pos-btn pos-btn-secondary" id="histNext" style="padding:6px 14px" ${to >= total ? 'disabled' : ''}>التالي</button>
        </span>`;
      const prev = document.getElementById('histPrev'), next = document.getElementById('histNext');
      if (prev) prev.addEventListener('click', () => { _histOffset = Math.max(0, _histOffset - HIST_PAGE); loadHistory(); });
      if (next) next.addEventListener('click', () => { _histOffset += HIST_PAGE; loadHistory(); });

      host.querySelectorAll('.hist-view').forEach(b => b.addEventListener('click', () => viewInvoice(b.dataset.id)));
      host.querySelectorAll('.hist-print').forEach(b => b.addEventListener('click', () => reprintInvoice(b.dataset.id)));
    } catch (err) {
      host.innerHTML = `<p style="color:#dc2626">${esc(err.message)}</p>`;
    }
  }

  // شاشة تفاصيل الفاتورة الكاملة (هنا يُسمح بعرض معلومات التأمين والآجل)
  async function viewInvoice(invoiceId) {
    try {
      const inv = await callAPI('get_invoice&id=' + invoiceId);
      const itemRows = (inv.items || []).map(it =>
        `<tr><td>${esc(it.product_name)}</td><td>${it.unit_quantity} (${esc(it.unit)})</td><td>${money(it.unit_price)}</td><td>${money(it.total_price)}</td></tr>`
      ).join('');

      const insBlock = (parseFloat(inv.insurance_due) > 0) ? `
        <div class="row insurance"><span>شركة التأمين</span><span>${esc(inv.insurance_name || '')}</span></div>
        <div class="row insurance"><span>خصم التأمين</span><span>${money(inv.insurance_discount)}</span></div>
        <div class="row insurance"><span>مستحق على التأمين</span><span>${money(inv.insurance_due)}</span></div>` : '';
      const credBlock = (parseFloat(inv.remaining_amount) > 0) ? `
        <div class="row remaining"><span>المدفوع</span><span>${money(inv.paid_amount)}</span></div>
        <div class="row remaining"><span>المتبقي (آجل)</span><span>${money(inv.remaining_amount)}</span></div>` : '';

      const html = `
        <div class="pos-modal-head">
          <h3><i class="fas fa-file-invoice"></i> تفاصيل الفاتورة ${esc(inv.invoice_number)}</h3>
          <button class="pos-modal-close" onclick="closePosModal()">&times;</button>
        </div>
        <div class="pos-modal-body">
          <div class="pos-summary">
            <div class="row"><span>التاريخ</span><span>${new Date(inv.created_at).toLocaleString('ar-EG')}</span></div>
            <div class="row"><span>العميل</span><span>${esc(inv.customer_db_name || inv.customer_name || 'نقدي')}</span></div>
            <div class="row"><span>الكاشير</span><span>${esc(inv.cashier_name || '')}</span></div>
            <div class="row"><span>طريقة الدفع</span><span>${esc(inv.payment_method || 'نقداً')}</span></div>
            <div class="row"><span>الحالة</span><span><span class="pos-status ${inv.status}">${statusLabel(inv.status)}</span></span></div>
          </div>
          <table class="pos-table">
            <thead><tr><th>الصنف</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead>
            <tbody>${itemRows}</tbody>
          </table>
          <div class="pos-summary">
            <div class="row"><span>المجموع قبل الضريبة</span><span>${money(inv.subtotal)}</span></div>
            <div class="row"><span>الضريبة</span><span>${money(inv.tax_amount)}</span></div>
            <div class="row"><span>الخصم</span><span>- ${money(inv.discount_amount || 0)}</span></div>
            <div class="row grand"><span>الإجمالي</span><span>${money(inv.total_amount)}</span></div>
            ${insBlock}
            ${credBlock}
          </div>
        </div>
        <div class="pos-modal-foot">
          <button class="pos-btn pos-btn-secondary" onclick="window.openHistoryModal()">رجوع</button>
          <button class="pos-btn pos-btn-primary" id="viewPrintBtn"><i class="fas fa-print"></i> طباعة</button>
        </div>`;
      openModal(html, true);
      document.getElementById('viewPrintBtn').addEventListener('click', () => reprintInvoice(invoiceId));
    } catch (err) { showNotification('تعذّر عرض الفاتورة: ' + err.message, 'error'); }
  }
  window.viewInvoicePos = viewInvoice;

  // ============================================
  // ربط أزرار شريط الأدوات
  // ============================================
  document.addEventListener('DOMContentLoaded', wireToolbar);
  if (document.readyState !== 'loading') wireToolbar();
  function wireToolbar() {
    const r = document.getElementById('returnInvoiceBtn');
    const o = document.getElementById('outstandingBtn');
    const h = document.getElementById('historyBtn');
    if (r && !r._wired) { r._wired = true; r.addEventListener('click', () => window.openReturnModal()); }
    if (o && !o._wired) { o._wired = true; o.addEventListener('click', () => window.openOutstandingModal()); }
    if (h && !h._wired) { h._wired = true; h.addEventListener('click', () => window.openHistoryModal()); }
  }
})();
