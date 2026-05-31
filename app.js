/**
 * Pharmacy POS System - App.js
 * Professional Point of Sale Application
 * Connected to MySQL Database
 */

// ============================================
// Configuration
// ============================================

const API_BASE_URL = './api.php';
const API_TIMEOUT = 10000; // 10 seconds

// ============================================
// DOM Elements
// ============================================

const categoriesEl = document.getElementById('categories');
const productsEl = document.getElementById('products');
const cartListEl = document.getElementById('cartList');
const cartCountEl = document.getElementById('cartCount');
const subTotalEl = document.getElementById('subTotal');
const taxEl = document.getElementById('tax');
const grandTotalEl = document.getElementById('grandTotal');
const showInvoiceBtn = document.getElementById('showInvoice');
const clearCartBtn = document.getElementById('clearCart');
const checkoutBtn = document.getElementById('checkout');
const searchInput = document.getElementById('searchInput');
const sortSelect = document.getElementById('sortSelect');
const totalItemsEl = document.getElementById('totalItems');

// ============================================
// Application State
// ============================================

let appState = {
  data: null,
  activeCategory: null,
  cart: JSON.parse(localStorage.getItem('pharmacy_cart_v1') || '{}'),
  filteredProducts: [],
  currentSort: 'name',
  invoiceCounter: parseInt(localStorage.getItem('invoice_counter') || '0') + 1,
  currentInvoiceId: null
};

const CART_KEY = 'pharmacy_cart_v1';
const INVOICE_KEY = 'invoice_counter';

// Normalize cart structure (support legacy format)
appState.cart = normalizeCart(appState.cart);

// ============================================
// API Helper Functions
// ============================================

/**
 * Fetch with timeout
 */
async function fetchWithTimeout(url, options = {}) {
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), API_TIMEOUT);
  
  try {
    const response = await fetch(url, {
      ...options,
      signal: controller.signal
    });
    clearTimeout(timeout);
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    
    return await response.json();
  } catch (error) {
    clearTimeout(timeout);
    throw error;
  }
}

/**
 * Call API Endpoint
 */
async function callAPI(action, method = 'GET', data = null) {
  try {
    let url = `${API_BASE_URL}?action=${action}`;
    let options = {
      method: method,
      headers: {
        'Content-Type': 'application/json'
      }
    };
    
    if (method === 'POST' && data) {
      options.body = JSON.stringify(data);
    }
    
    const response = await fetchWithTimeout(url, options);
    
    if (!response.success) {
      throw new Error(response.message || 'خطأ غير محدد');
    }
    
    return response.data;
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
}



// ============================================
// Utility Functions
// ============================================

/**
 * Format number as Egyptian currency
 */
function formatCurrency(amount) {
  return amount.toLocaleString('ar-EG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }) + ' ج.م';
}

/**
 * Format date for invoice
 */
function formatDate(date = new Date()) {
  return date.toLocaleDateString('ar-EG', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'success') {
  const notification = document.createElement('div');
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#f59e0b'};
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    font-weight: 600;
    z-index: 1000;
    animation: slideInUp 0.3s ease-out;
  `;
  notification.textContent = message;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.style.animation = 'slideInUp 0.3s ease-out reverse';
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// ============================================
// Unit & Packaging Helpers
// ============================================

function normalizeCart(rawCart) {
  const normalized = {};
  if (!rawCart || typeof rawCart !== 'object') return normalized;

  Object.keys(rawCart).forEach(key => {
    const value = rawCart[key];
    if (typeof value === 'number') {
      const productId = key;
      const cartKey = buildCartKey(productId, 'strip');
      normalized[cartKey] = { productId, unit: 'strip', quantity: value };
      return;
    }

    if (value && typeof value === 'object') {
      const productId = value.productId || key.split('::')[0] || key;
      const unit = value.unit || (key.includes('::') ? key.split('::')[1] : 'strip');
      const quantity = Number(value.quantity ?? value.qty ?? 0);
      if (quantity > 0) {
        const cartKey = buildCartKey(productId, unit);
        normalized[cartKey] = { productId, unit, quantity };
      }
    }
  });

  return normalized;
}

function buildCartKey(productId, unit) {
  return `${productId}::${unit}`;
}

function getPackaging(product) {
  const stripsPerBox = parseInt(product.strips_per_box || 0) || 3;
  const boxesPerCarton = parseInt(product.boxes_per_carton || 0) || 12;
  return {
    stripsPerBox,
    boxesPerCarton,
    stripsPerCarton: stripsPerBox * boxesPerCarton
  };
}

function getStockStrips(product) {
  if (product.stock_strips !== null && product.stock_strips !== undefined) {
    const s = Number(product.stock_strips);
    if (!Number.isNaN(s)) return s;
  }
  return Number(product.stock || 0);
}

function getUnitSize(product, unit) {
  if (unit === 'strip') return 1;
  const { stripsPerBox, stripsPerCarton } = getPackaging(product);
  return unit === 'box' ? stripsPerBox : stripsPerCarton;
}

function getUnitPrice(product, unit) {
  const priceStrip = Number(product.price_strip ?? product.price ?? 0);
  const priceBox = Number(product.price_box ?? 0);
  const priceCarton = Number(product.price_carton ?? 0);
  const { stripsPerBox, stripsPerCarton } = getPackaging(product);

  if (unit === 'strip') return priceStrip;
  if (unit === 'box') return priceBox || (priceStrip * stripsPerBox);
  if (unit === 'carton') return priceCarton || (priceStrip * stripsPerCarton);
  return priceStrip;
}

function getUnitLabel(unit) {
  if (unit === 'strip') return 'شريط';
  if (unit === 'box') return 'علبة';
  return 'كرتونة';
}

function getMaxUnits(product, unit) {
  const stockStrips = getStockStrips(product);
  const unitSize = getUnitSize(product, unit);
  return Math.floor(stockStrips / unitSize);
}

// ============================================
// Data Loading
// ============================================

/**
 * Load products data from API (Database Only)
 */
async function loadProducts() {
  try {
    console.log('جاري تحميل البيانات من قاعدة البيانات...');
    
    // Get Categories
    const categories = await callAPI('get_categories');
    
    // Get Products for each category
    const categoriesWithProducts = await Promise.all(
      categories.map(async (cat) => {
        const products = await callAPI(`get_products_by_category&category_id=${cat.id}`);
        return {
          id: cat.id,
          name: cat.name,
          description: cat.description,
          icon: cat.icon,
          products: products || []
        };
      })
    );
    
    console.log('✅ تم تحميل البيانات من قاعدة البيانات');
    return { categories: categoriesWithProducts };
  } catch (err) {
    console.error('❌ خطأ في تحميل البيانات من قاعدة البيانات:', err);
    showNotification('خطأ: فشل الاتصال بقاعدة البيانات', 'error');
    throw err;
  }
}

// ============================================
// Rendering Functions
// ============================================

/**
 * Render category buttons
 */
function renderCategories() {
  categoriesEl.innerHTML = '';
  appState.data.categories.forEach((cat, index) => {
    const btn = document.createElement('button');
    btn.className = 'cat-btn';
    btn.innerHTML = `${cat.name}`;
    
    if (appState.activeCategory === null && index === 0) {
      appState.activeCategory = cat.id;
    }
    
    if (cat.id === appState.activeCategory) {
      btn.classList.add('active');
    }
    
    btn.addEventListener('click', () => {
      appState.activeCategory = cat.id;
      searchInput.value = '';
      renderCategories();
      renderProducts();
    });
    
    categoriesEl.appendChild(btn);
  });
}

/**
 * Render product cards
 */
function renderProducts() {
  productsEl.innerHTML = '';
  
  const category = appState.data.categories.find(c => c.id === appState.activeCategory);
  if (!category) return;
  
  let products = [...category.products];
  
  // Apply search filter
  if (searchInput.value.trim()) {
    const query = searchInput.value.trim().toLowerCase();
    products = products.filter(p => p.name.toLowerCase().includes(query));
  }
  
  // Apply sorting
  switch (appState.currentSort) {
    case 'price-asc':
      products.sort((a, b) => getUnitPrice(a, 'strip') - getUnitPrice(b, 'strip'));
      break;
    case 'price-desc':
      products.sort((a, b) => getUnitPrice(b, 'strip') - getUnitPrice(a, 'strip'));
      break;
    case 'stock':
      products.sort((a, b) => getStockStrips(b) - getStockStrips(a));
      break;
    case 'name':
    default:
      products.sort((a, b) => a.name.localeCompare(b.name, 'ar'));
  }
  
  if (products.length === 0) {
    productsEl.innerHTML = `
      <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-tertiary);">
        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
        <p>لم يتم العثور على منتجات</p>
      </div>
    `;
    return;
  }
  
  products.forEach(product => {
    const stockStrips = getStockStrips(product);
    const { stripsPerBox, stripsPerCarton } = getPackaging(product);
    const stockBoxes = Math.floor(stockStrips / stripsPerBox);
    const stockCartons = Math.floor(stockStrips / stripsPerCarton);

    const stockStatus = stockStrips === 0 ? 'out-of-stock' : stockStrips < 10 ? 'low-stock' : 'in-stock';
    const stockLabel = stockStrips === 0 ? 'غير متوفر' : stockStrips < 10 ? 'كمية محدودة' : 'متوفر';

    const availableUnits = [];
    if (stockStrips >= 1) availableUnits.push('strip');
    if (stockStrips >= stripsPerBox) availableUnits.push('box');
    if (stockStrips >= stripsPerCarton) availableUnits.push('carton');
    const defaultUnit = availableUnits[0] || 'strip';
    const defaultPrice = getUnitPrice(product, defaultUnit);
    
    const card = document.createElement('div');
    card.className = 'product-card fade-in';
    card.innerHTML = `
      <div class="product-image">
        <img src="${product.image}" alt="${product.name}" referrerpolicy="no-referrer" 
             onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22><rect width=%22200%22 height=%22200%22 fill=%22%23e2e8f0%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%2364748b%22 font-size=%2216%22>صورة غير متاحة</text></svg>'">
        <div class="product-badge ${stockStatus}">
          ${stockLabel}
        </div>
      </div>
      <div class="product-info">
        <div class="product-name">${product.name}</div>
        <div class="product-meta">
          <span class="product-price" data-price-id="${product.id}">${formatCurrency(defaultPrice)} / ${getUnitLabel(defaultUnit)}</span>
          <span class="product-stock">المخزون: ${stockStrips} شريط | ${stockBoxes} علبة | ${stockCartons} كرتونة</span>
        </div>
        <div class="product-actions">
          <select class="unit-select" data-id="${product.id}" ${availableUnits.length === 0 ? 'disabled' : ''}>
            ${availableUnits.map(u => `<option value="${u}">${getUnitLabel(u)}</option>`).join('')}
          </select>
          <button class="btn-add-cart" data-id="${product.id}" ${stockStrips === 0 ? 'disabled' : ''}>
            <i class="fas fa-cart-plus"></i> أضف للسلة
          </button>
          <button class="btn-details" data-id="${product.id}" title="عرض التفاصيل">
            <i class="fas fa-info-circle"></i>
          </button>
        </div>
      </div>
    `;
    
    productsEl.appendChild(card);
  });
  
  totalItemsEl.textContent = products.length;
}

/**
 * Render cart items
 */
function renderCart() {
  cartListEl.innerHTML = '';
  const cartItems = Object.values(appState.cart);
  
  if (cartItems.length === 0) {
    cartListEl.innerHTML = `
      <div class="cart-empty">
        <i class="fas fa-inbox"></i>
        السلة فارغة
      </div>
    `;
    cartCountEl.style.display = 'none';
    subTotalEl.textContent = '0.00 ج.م';
    taxEl.textContent = '0.00 ج.م';
    grandTotalEl.textContent = '0.00 ج.م';
    return;
  }
  
  let subtotal = 0;
  let totalQtyStrips = 0;
  
  cartItems.forEach(cartItem => {
    const product = findProduct(cartItem.productId);
    if (!product) return;
    
    const unitLabel = getUnitLabel(cartItem.unit);
    const unitPrice = getUnitPrice(product, cartItem.unit);
    const unitSize = getUnitSize(product, cartItem.unit);
    const maxUnits = getMaxUnits(product, cartItem.unit);
    const baseQty = cartItem.quantity * unitSize;
    const itemTotal = unitPrice * cartItem.quantity;
    subtotal += itemTotal;
    totalQtyStrips += baseQty;
    
    const itemEl = document.createElement('div');
    itemEl.className = 'cart-item fade-in';
    itemEl.innerHTML = `
      <div class="cart-item-image">
        <img src="${product.image}" alt="${product.name}" referrerpolicy="no-referrer"
             onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%25%22 fill=%22%23e2e8f0%22/></svg>'">
      </div>
      <div class="cart-item-info">
        <div class="cart-item-name">${product.name}</div>
        <div class="cart-item-price">${formatCurrency(unitPrice)} × ${cartItem.quantity} ${unitLabel}</div>
        <div class="cart-item-meta">يعادل ${baseQty} شريط</div>
      </div>
      <div class="cart-item-controls">
        <div class="qty-controls">
          <button class="qty-btn qty-minus" data-key="${buildCartKey(cartItem.productId, cartItem.unit)}" data-action="decrease">
            <i class="fas fa-minus"></i>
          </button>
          <span class="qty-display">${cartItem.quantity}</span>
          <button class="qty-btn qty-plus" data-key="${buildCartKey(cartItem.productId, cartItem.unit)}" data-action="increase" data-max="${maxUnits}">
            <i class="fas fa-plus"></i>
          </button>
        </div>
        <button class="btn-remove" data-remove="${buildCartKey(cartItem.productId, cartItem.unit)}">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    `;
    
    cartListEl.appendChild(itemEl);
  });
  
  // Update cart count badge
  cartCountEl.textContent = totalQtyStrips;
  cartCountEl.style.display = 'flex';
  
  // Update totals
  const tax = +(subtotal * 0.05).toFixed(2);
  const total = subtotal + tax;
  
  subTotalEl.textContent = formatCurrency(subtotal);
  taxEl.textContent = formatCurrency(tax);
  grandTotalEl.textContent = formatCurrency(total);
}

// ============================================
// Cart Management
// ============================================

/**
 * Find product by ID
 */
function findProduct(id) {
  for (const category of appState.data.categories) {
    const product = category.products.find(p => p.id === id);
    if (product) return product;
  }
  return null;
}

/**
 * Add product to cart
 */
function addToCart(productId, unit = 'strip', quantity = 1) {
  const product = findProduct(productId);
  if (!product) return;
 
  const cartKey = buildCartKey(productId, unit);
  const currentQty = appState.cart[cartKey]?.quantity || 0;
  const newQty = currentQty + quantity;
  const maxUnits = getMaxUnits(product, unit);
  
  if (newQty > maxUnits) {
    showNotification(`الكمية المطلوبة تتجاوز المخزون (المتوفر: ${maxUnits} ${getUnitLabel(unit)})`, 'error');
    return;
  }

  appState.cart[cartKey] = { productId, unit, quantity: newQty };
  saveCart();
  renderCart();
  showNotification(`تم إضافة "${product.name}" إلى السلة (${getUnitLabel(unit)})`, 'success');
}

/**
 * Remove product from cart
 */
function removeFromCart(cartKey) {
  delete appState.cart[cartKey];
  saveCart();
  renderCart();
  showNotification('تم حذف المنتج من السلة', 'success');
}

/**
 * Update product quantity
 */
function updateQty(cartKey, quantity) {
  quantity = Number(quantity);
  const cartItem = appState.cart[cartKey];
  if (!cartItem) return;
  
  const product = findProduct(cartItem.productId);
  if (!product) return;
  
  if (quantity <= 0) {
    removeFromCart(cartKey);
    return;
  }
  
  const maxUnits = getMaxUnits(product, cartItem.unit);
  if (quantity > maxUnits) {
    showNotification(`الكمية المطلوبة تتجاوز المخزون (المتوفر: ${maxUnits} ${getUnitLabel(cartItem.unit)})`, 'error');
    renderCart();
    return;
  }
  
  appState.cart[cartKey].quantity = quantity;
  saveCart();
  renderCart();
}

/**
 * Save cart to localStorage
 */
function saveCart() {
  localStorage.setItem(CART_KEY, JSON.stringify(appState.cart));
}

/**
 * Clear entire cart
 */
function clearCart() {
  if (Object.keys(appState.cart).length === 0) {
    showNotification('السلة فارغة بالفعل', 'error');
    return;
  }
  
  if (confirm('هل تريد مسح السلة بالكامل؟')) {
    appState.cart = {};
    saveCart();
    renderCart();
    showNotification('تم مسح السلة', 'success');
  }
}

// ============================================
// Invoice Generation
// ============================================

/**
 * Generate and print invoice
 */
function generateInvoice() {
  const cartItems = Object.values(appState.cart);
  
  if (cartItems.length === 0) {
    showNotification('السلة فارغة - لا يمكن طباعة فاتورة', 'error');
    return;
  }
  
  let subtotal = 0;
  let invoiceRows = '';
  
  cartItems.forEach(item => {
    const product = findProduct(item.productId);
    if (!product) return;
    
    const unitLabel = getUnitLabel(item.unit);
    const unitPrice = getUnitPrice(product, item.unit);
    const itemTotal = unitPrice * item.quantity;
    subtotal += itemTotal;
    
    invoiceRows += `
      <tr>
        <td>${product.name}</td>
        <td>${item.quantity} ${unitLabel}</td>
        <td>${parseFloat(unitPrice).toFixed(2)}</td>
        <td>${itemTotal.toFixed(2)}</td>
      </tr>
    `;
  });
  
  const tax = +(subtotal * 0.05).toFixed(2);
  const total = (subtotal + tax).toFixed(2);
  const invoiceNo = String(appState.invoiceCounter).padStart(6, '0');
  const currentDate = formatDate();
  const currentTime = new Date().toLocaleTimeString('ar-EG');
  
  const invoiceHTML = `
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>فاتورة رقم ${invoiceNo}</title>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
          font-family: 'Arial', 'Segoe UI', sans-serif;
          background: #f0f4f8;
          padding: 20px;
          color: #0f172a;
        }
        .invoice-container {
          max-width: 700px;
          background: white;
          margin: 0 auto;
          padding: 40px;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
          border-radius: 12px;
        }
        .header {
          text-align: center;
          border-bottom: 3px solid #2563eb;
          padding-bottom: 20px;
          margin-bottom: 30px;
        }
        .header h1 {
          color: #2563eb;
          font-size: 32px;
          margin-bottom: 8px;
        }
        .header p {
          color: #666;
          font-size: 14px;
        }
        .info-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 20px;
          margin-bottom: 30px;
          font-size: 13px;
          color: #555;
        }
        .info-group strong {
          display: block;
          color: #0f172a;
          margin-bottom: 4px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin: 20px 0;
        }
        th {
          background: #f0f4f8;
          padding: 12px;
          text-align: right;
          font-weight: 600;
          color: #0f172a;
          border-bottom: 2px solid #2563eb;
        }
        td {
          padding: 12px;
          border-bottom: 1px solid #e2e8f0;
          text-align: right;
        }
        tr:hover { background: #fafbfc; }
        .summary {
          margin-top: 30px;
          display: flex;
          flex-direction: column;
          gap: 12px;
        }
        .summary-row {
          display: flex;
          justify-content: space-between;
          font-weight: 600;
          padding: 8px 0;
        }
        .summary-row.total {
          background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
          color: white;
          padding: 16px;
          border-radius: 8px;
          font-size: 18px;
        }
        .footer {
          text-align: center;
          margin-top: 30px;
          padding-top: 20px;
          border-top: 1px solid #e2e8f0;
          color: #999;
          font-size: 12px;
          line-height: 1.8;
        }
        .button-group {
          display: flex;
          gap: 12px;
          margin-top: 24px;
          justify-content: center;
          flex-wrap: wrap;
        }
        button {
          padding: 12px 24px;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          font-weight: 600;
          font-size: 14px;
          transition: all 0.2s ease;
        }
        .btn-print {
          background: #2563eb;
          color: white;
        }
        .btn-print:hover {
          background: #1d4ed8;
        }
        .btn-close {
          background: #e2e8f0;
          color: #0f172a;
        }
        .btn-close:hover {
          background: #cbd5e1;
        }
        @media print {
          body { background: white; }
          .button-group { display: none; }
          .invoice-container { box-shadow: none; padding: 0; }
        }
      </style>
    </head>
    <body>
      <div class="invoice-container">
        <div class="header">
          <h1>🏥 صيدليات الحياة</h1>
          <p>فاتورة مبيعات رسمية</p>
        </div>
        
        <div class="info-grid">
          <div class="info-group">
            <strong>رقم الفاتورة:</strong>
            ${invoiceNo}
            <strong style="margin-top: 12px;">التاريخ:</strong>
            ${currentDate}
            <strong style="margin-top: 12px;">الوقت:</strong>
            ${currentTime}
          </div>
          <div class="info-group">
            <strong>الفرع:</strong>
            الفرع الرئيسي - القاهرة
            <strong style="margin-top: 12px;">الكاشير:</strong>
            نظام محاسبي رقمي
            <strong style="margin-top: 12px;">رقم الجهاز:</strong>
            POS-001
          </div>
        </div>
        
        <table>
          <thead>
            <tr>
              <th>الصنف</th>
              <th>الكمية</th>
              <th>السعر</th>
              <th>الإجمالي</th>
            </tr>
          </thead>
          <tbody>
            ${invoiceRows}
          </tbody>
        </table>
        
        <div class="summary">
          <div class="summary-row">
            <span>المجموع قبل الضريبة:</span>
            <span>${subtotal.toFixed(2)} ج.م</span>
          </div>
          <div class="summary-row">
            <span>الضريبة (5%):</span>
            <span>${tax} ج.م</span>
          </div>
          <div class="summary-row total">
            <span>الإجمالي:</span>
            <span>${total} ج.م</span>
          </div>
        </div>
        
        <div class="footer">
          <p><strong>شكراً لتعاملك معنا!</strong></p>
          <p>🌐 www.pharmacylife.com | 📱 +20 (100) 123-4567</p>
          <p>العنوان: شارع النيل، ناصر، القاهرة</p>
          <p style="margin-top: 16px; font-style: italic;">الفاتورة صادرة من نظام الكاشير الحديث</p>
          <p>يرجى الاحتفاظ بهذه الفاتورة</p>
        </div>
        
        <div class="button-group">
          <button class="btn-print" onclick="window.print()">
            🖨️ طباعة الفاتورة
          </button>
          <button class="btn-close" onclick="closeWindow()">
            إغلاق
          </button>
        </div>
      </div>
      <script>
        function closeWindow() {
          window.close();
        }
      </script>
    </body>
    </html>
  `;
  
  try {
    const invoiceWindow = window.open('', 'invoice_' + Date.now(), 'width=900,height=700,scrollbars=yes');
    if (invoiceWindow === null) {
      showNotification('تم حظر فتح النافذة المنبثقة. يرجى السماح بالنوافذ المنبثقة.', 'warning');
      return;
    }
    invoiceWindow.document.write(invoiceHTML);
    invoiceWindow.document.close();
    invoiceWindow.focus();
    
    // Increment invoice counter
    appState.invoiceCounter++;
    localStorage.setItem(INVOICE_KEY, appState.invoiceCounter);
  } catch (error) {
    console.error('Print error:', error);
    showNotification('خطأ في فتح نافذة الطباعة', 'error');
  }
}

/**
 * Complete checkout
 */
async function checkout() {
  if (Object.keys(appState.cart).length === 0) {
    showNotification('السلة فارغة', 'error');
    return;
  }
  
  try {
    // Calculate totals
    let subtotal = 0;
    Object.values(appState.cart).forEach(item => {
      const product = findProduct(item.productId);
      if (product) {
        const unitPrice = getUnitPrice(product, item.unit);
        subtotal += unitPrice * item.quantity;
      }
    });
    
    const tax_amount = +(subtotal * 0.05).toFixed(2);
    
    // Create invoice in database
    const invoiceResponse = await callAPI('create_invoice', 'POST', {
      branch_id: 1,
      cashier_id: 1,
      subtotal: subtotal,
      tax_amount: tax_amount,
      payment_method: 'cash'
    });
    
    const invoiceId = invoiceResponse.invoice_id;
    appState.currentInvoiceId = invoiceId;
    
    // Add items to invoice
    for (const item of Object.values(appState.cart)) {
      const product = findProduct(item.productId);
      if (product) {
        const unitPrice = getUnitPrice(product, item.unit);
        const unitSize = getUnitSize(product, item.unit);
        const baseQty = item.quantity * unitSize;
        await callAPI('add_invoice_item', 'POST', {
          invoice_id: invoiceId,
          product_id: item.productId,
          unit: item.unit,
          unit_quantity: item.quantity,
          base_quantity: baseQty,
          quantity: baseQty,
          unit_price: unitPrice
        });
      }
    }
    
    // Reload products from the server so updated stock is reflected in the UI
    try {
      appState.data = await loadProducts();
      renderCategories();
      renderProducts();
    } catch (err) {
      console.warn('Failed to refresh products after checkout:', err);
    }

    showNotification('تم حفظ الفاتورة بنجاح', 'success');
    generateInvoice();
    appState.cart = {};
    saveCart();
    renderCart();
    
  } catch (error) {
    console.error('Checkout error:', error);
    showNotification('خطأ في إتمام البيع: ' + error.message, 'error');
  }
}

// ============================================
// Event Listeners
// ============================================

// Add to cart
document.addEventListener('click', (e) => {
  if (e.target.closest('.btn-add-cart')) {
    const btn = e.target.closest('.btn-add-cart');
    const productId = btn.getAttribute('data-id');
    const card = btn.closest('.product-card');
    const unitSelect = card ? card.querySelector('.unit-select') : null;
    const unit = unitSelect ? unitSelect.value : 'strip';
    addToCart(productId, unit, 1);
  }
  
  // Remove from cart
  if (e.target.closest('.btn-remove')) {
    const btn = e.target.closest('.btn-remove');
    const cartKey = btn.getAttribute('data-remove');
    removeFromCart(cartKey);
  }
  
  // Quantity + and - buttons
  if (e.target.closest('.qty-btn')) {
    const btn = e.target.closest('.qty-btn');
    const cartKey = btn.getAttribute('data-key');
    const action = btn.getAttribute('data-action');
    const cartItem = appState.cart[cartKey];
    
    if (cartItem) {
      if (action === 'increase') {
        const maxQty = parseInt(btn.getAttribute('data-max'));
        if (cartItem.quantity < maxQty) {
          updateQty(cartKey, cartItem.quantity + 1);
        } else {
          showNotification('الكمية المطلوبة تتجاوز المخزون المتاح', 'error');
        }
      } else if (action === 'decrease') {
        if (cartItem.quantity > 1) {
          updateQty(cartKey, cartItem.quantity - 1);
        } else {
          removeFromCart(cartKey);
        }
      }
    }
  }
});

// Update price display when unit changes
document.addEventListener('change', (e) => {
  if (e.target.classList.contains('unit-select')) {
    const productId = e.target.getAttribute('data-id');
    const product = findProduct(productId);
    if (!product) return;
    const unit = e.target.value;
    const priceEl = document.querySelector(`.product-price[data-price-id="${productId}"]`);
    if (priceEl) {
      const unitPrice = getUnitPrice(product, unit);
      priceEl.textContent = `${formatCurrency(unitPrice)} / ${getUnitLabel(unit)}`;
    }
  }
});

// Search
searchInput.addEventListener('input', (e) => {
  renderProducts();
});

// Sort
sortSelect.addEventListener('change', (e) => {
  appState.currentSort = e.target.value;
  renderProducts();
});

// Cart buttons
showInvoiceBtn.addEventListener('click', generateInvoice);
clearCartBtn.addEventListener('click', clearCart);
checkoutBtn.addEventListener('click', checkout);

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
  if (e.ctrlKey && e.key === 'p') {
    e.preventDefault();
    generateInvoice();
  }
});

// ============================================
// Initialization
// ============================================

/**
 * Initialize the application
 */
async function initializeApp() {
  appState.data = await loadProducts();
  
  if (!appState.activeCategory && appState.data.categories.length > 0) {
    appState.activeCategory = appState.data.categories[0].id;
  }
  
  renderCategories();
  renderProducts();
  renderCart();
  
  console.log('✅ Pharmacy POS System Initialized');
}

// Start the app when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeApp);
} else {
  initializeApp();
}
