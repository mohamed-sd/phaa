/**
 * Unified Sidebar Component
 * يتم استخدامه في جميع صفحات لوحة الإدارة
 */

// ============================================================
// Global settings exposed to all admin pages
// ============================================================
window.systemCurrency = 'ر.س';
window.systemSettings = {};

async function loadSystemSettings() {
  try {
    const res = await fetch('../api.php?action=get_settings');
    const json = await res.json();
    if (json.success && json.data) {
      window.systemSettings = json.data;
      window.systemCurrency = json.data.currency_symbol || 'ر.س';
    }
  } catch (e) {
    console.warn('Could not load system settings:', e);
  }
}

// ============================================================
// Notification data & badge updates
// ============================================================
window._alertData = null;

async function loadNotificationCount() {
  try {
    const res = await fetch('../api.php?action=get_alerts');
    const json = await res.json();
    if (json.success && json.data) {
      const d = json.data;
      window._alertData = d;
      const count = (d.expired?.length      || 0) +
                    (d.expiring_soon?.length || 0) +
                    (d.out_of_stock?.length  || 0) +
                    (d.low_stock?.length     || 0);

      // Topbar bell badge
      const topbarBadge = document.getElementById('topbarBellBadge');
      if (topbarBadge) {
        topbarBadge.textContent = count > 99 ? '99+' : count;
        topbarBadge.style.display = count > 0 ? 'flex' : 'none';
      }

      return d;
    }
  } catch (e) {
    console.warn('Could not load notifications:', e);
  }
  return null;
}

// ============================================================
// Shared notification items renderer
// ============================================================
function renderNotifItemsIn(bodyEl) {
  if (!bodyEl) return;
  const d = window._alertData;

  if (!d) {
    bodyEl.innerHTML = '<div class="notif-empty"><i class="fas fa-spinner fa-spin"></i><br>جاري التحميل...</div>';
    return;
  }

  const items = [];
  (d.expired       || []).forEach(p => items.push({ p, icon: 'fa-skull-crossbones', label: 'منتهي الصلاحية', cls: 'expired'   }));
  (d.expiring_soon || []).forEach(p => items.push({ p, icon: 'fa-clock',             label: 'ينتهي قريباً',   cls: 'expiring'  }));
  (d.out_of_stock  || []).forEach(p => items.push({ p, icon: 'fa-box-open',          label: 'نفد من المخزون', cls: 'out-stock' }));
  (d.low_stock     || []).forEach(p => items.push({ p, icon: 'fa-exclamation-triangle', label: 'مخزون منخفض', cls: 'low-stock' }));

  if (items.length === 0) {
    bodyEl.innerHTML = '<div class="notif-empty"><i class="fas fa-check-circle" style="color:#10b981"></i><br>لا توجد تنبيهات حالياً</div>';
    return;
  }

  const qty = p => p.stock_strips ?? p.current_stock ?? p.stock ?? 0;

  bodyEl.innerHTML = items.slice(0, 10).map(({ p, icon, label, cls }) => `
    <div class="notif-item">
      <div class="notif-icon ${cls}"><i class="fas ${icon}"></i></div>
      <div class="notif-item-info">
        <div class="notif-item-name">${p.name}</div>
        <div class="notif-item-meta">الكمية: ${qty(p)}${p.expiry_date ? ' • ' + p.expiry_date : ''}</div>
        <span class="notif-item-badge ${cls}">${label}</span>
      </div>
    </div>
  `).join('');
}

// ============================================================
// Topbar bell (injected into .user-section of every page)
// ============================================================
function injectTopbarBell() {
  if (document.getElementById('topbarBellWrap')) return;

  // Support multiple possible topbar container selectors
  const target = document.querySelector('.topbar .user-section') ||
                 document.querySelector('.user-section') ||
                 document.querySelector('.topbar .user-info') ||
                 document.querySelector('.user-info');
  if (!target) return;

  target.insertAdjacentHTML('afterbegin', `
    <div id="topbarBellWrap" style="position:relative;flex-shrink:0;">
      <button id="topbarBellBtn" class="topbar-bell" onclick="toggleTopbarPanel()" title="تنبيهات المخزون">
        <i class="fas fa-bell"></i>
        <span id="topbarBellBadge" class="topbar-bell-badge" style="display:none">0</span>
      </button>
      <div id="topbarBellPanel" class="topbar-bell-panel" style="display:none;">
        <div class="topbar-notif-hdr">
          <span><i class="fas fa-bell"></i> تنبيهات المخزون</span>
          <a href="inventory_alerts.html" class="topbar-notif-link">عرض الكل</a>
        </div>
        <div id="topbarBellBody" class="notif-scroll-body">
          <div class="notif-empty">
            <i class="fas fa-spinner fa-spin"></i><br>جاري التحميل...
          </div>
        </div>
      </div>
    </div>
  `);

  // Close panel when clicking outside
  document.addEventListener('click', e => {
    const wrap  = document.getElementById('topbarBellWrap');
    const panel = document.getElementById('topbarBellPanel');
    if (wrap && panel && !wrap.contains(e.target)) panel.style.display = 'none';
  });
}

function toggleTopbarPanel() {
  const panel = document.getElementById('topbarBellPanel');
  if (!panel) return;
  if (panel.style.display === 'none') {
    panel.style.display = 'block';
    renderNotifItemsIn(document.getElementById('topbarBellBody'));
  } else {
    panel.style.display = 'none';
  }
}

// ============================================================
// Sidebar menu items
// ============================================================
const sidebarMenuItems = [
  { href: 'dashboard.html',       icon: 'fas fa-chart-line',  text: 'لوحة التحكم'    },
  { href: 'manage_products.html', icon: 'fas fa-pills',        text: 'المنتجات'        },
  { href: 'manage_categories.html', icon: 'fas fa-list',       text: 'الفئات'          },
  { href: 'manage_invoices.html', icon: 'fas fa-receipt',      text: 'الفواتير'        },
  { type: 'divider' },
  { href: 'manage_suppliers.html',  icon: 'fas fa-truck',      text: 'الموردين'        },
  { href: 'manage_purchases.html',  icon: 'fas fa-shopping-cart', text: 'المشتريات'   },
  { type: 'divider' },
  { href: 'manage_reports.html',  icon: 'fas fa-chart-bar',    text: 'التقارير'        },
  { href: 'manage_users.html',    icon: 'fas fa-users',        text: 'المستخدمين'      },
  { href: 'settings.html',        icon: 'fas fa-cog',          text: 'الإعدادات'       }
];

// ============================================================
// CSS (injected once into <head>)
// ============================================================
const sidebarStyles = `
  /* ── Sidebar ── */
  .sidebar {
    width: 280px;
    background: linear-gradient(180deg, #0891b2 0%, #0e7490 50%, #155e75 100%);
    color: white;
    padding: 0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    right: 0;
    top: 0;
    z-index: 1000;
    box-shadow: -5px 0 25px rgba(8,145,178,0.3);
  }

  .sidebar-header {
    padding: 20px 20px 12px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.1);
  }

  .sidebar-logo {
    font-size: 22px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
  }

  .sidebar-logo .logo-icon {
    width: 45px; height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
  }

  .sidebar-logo .logo-text { display: flex; flex-direction: column; }
  .sidebar-logo .logo-text span:first-child { font-size: 18px; font-weight: 700; }
  .sidebar-logo .logo-text span:last-child  { font-size: 11px; opacity: 0.8; font-weight: normal; }

  /* ── Shared Notification Items ── */
  .notif-item {
    display: flex; gap: 10px;
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
  }
  .notif-item:hover { background: #f8fafc; }
  .notif-item:last-child { border-bottom: none; }

  .notif-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
  }
  .notif-icon.expired   { background: #fee2e2; color: #ef4444; }
  .notif-icon.expiring  { background: #fef3c7; color: #f59e0b; }
  .notif-icon.out-stock { background: #fce7f3; color: #ec4899; }
  .notif-icon.low-stock { background: #fef9c3; color: #eab308; }

  .notif-item-info { flex: 1; min-width: 0; }

  .notif-item-name {
    font-size: 12px; font-weight: 600; color: #1e293b;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }

  .notif-item-meta { font-size: 11px; color: #64748b; margin-top: 2px; }

  .notif-item-badge {
    font-size: 10px; font-weight: 600;
    padding: 1px 7px; border-radius: 10px;
    margin-top: 3px; display: inline-block;
  }
  .notif-item-badge.expired   { background: #fee2e2; color: #ef4444; }
  .notif-item-badge.expiring  { background: #fef3c7; color: #d97706; }
  .notif-item-badge.out-stock { background: #fce7f3; color: #be185d; }
  .notif-item-badge.low-stock { background: #fef9c3; color: #a16207; }

  .notif-empty {
    text-align: center; padding: 24px 16px;
    color: #64748b; font-size: 12px; line-height: 1.8;
  }
  .notif-empty i { font-size: 28px; display: block; margin-bottom: 6px; color: #94a3b8; }

  /* ── Topbar Bell (injected into .user-section) ── */
  .user-section, .user-info {
    display: flex !important;
    align-items: center;
    gap: 10px;
  }

  .topbar-bell {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: #f1f5f9;
    border: 2px solid #e2e8f0;
    color: #475569;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    position: relative;
    transition: all 0.2s;
    flex-shrink: 0;
  }
  .topbar-bell:hover {
    background: rgba(8,145,178,0.1);
    color: #0891b2;
    border-color: #0891b2;
  }

  .topbar-bell-badge {
    position: absolute;
    top: -5px; right: -5px;
    background: #ef4444;
    color: white;
    font-size: 10px; font-weight: 700;
    min-width: 18px; height: 18px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 3px;
    border: 2px solid white;
    line-height: 1;
    pointer-events: none;
  }

  .topbar-bell-panel {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    width: 320px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    z-index: 5000;
    border: 1px solid #e2e8f0;
    overflow: hidden;
  }

  .topbar-notif-hdr {
    background: linear-gradient(135deg, #0891b2, #0e7490);
    color: white;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px; font-weight: 600;
  }

  .topbar-notif-link {
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    font-size: 11px; font-weight: 500;
    background: rgba(255,255,255,0.2);
    padding: 4px 10px;
    border-radius: 6px;
    transition: background 0.2s;
  }
  .topbar-notif-link:hover { background: rgba(255,255,255,0.35); }

  .notif-scroll-body { max-height: 360px; overflow-y: auto; }

  /* ── Nav Menu ── */
  .nav-menu { list-style: none; margin: 0; padding: 16px 12px; }
  .nav-menu li { margin: 4px 0; }

  .nav-menu a {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 14px;
    border-radius: 10px;
    font-weight: 500;
    position: relative;
  }
  .nav-menu a:hover {
    background: rgba(255,255,255,0.15);
    color: white;
    transform: translateX(-5px);
  }
  .nav-menu a.active {
    background: rgba(255,255,255,0.2);
    color: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }
  .nav-menu a.active::before {
    content: '';
    position: absolute;
    right: 0;
    width: 4px; height: 70%;
    background: white;
    border-radius: 4px 0 0 4px;
  }
  .nav-menu i { width: 20px; text-align: center; font-size: 16px; }

  .nav-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 12px 16px; }

  /* ── Logout ── */
  .logout-section {
    position: absolute; bottom: 0; width: 100%;
    padding: 16px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(255,255,255,0.1);
  }

  #logoutBtn {
    width: 100%;
    padding: 12px 16px;
    background: rgba(239,68,68,0.2);
    border: 1px solid rgba(239,68,68,0.3);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  #logoutBtn:hover {
    background: rgba(239,68,68,0.9);
    border-color: rgba(239,68,68,0.9);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(239,68,68,0.4);
  }

  main, .main-content {
    margin-right: 280px !important;
    width: calc(100% - 280px) !important;
  }

  @media (max-width: 768px) {
    .sidebar { width: 100%; height: auto; position: relative; }
    main, .main-content { margin-right: 0 !important; width: 100% !important; }
    .logout-section { position: relative; bottom: auto; margin-top: 20px; }
    .topbar-bell-panel { left: auto; right: 0; }
  }
`;

// ============================================================
// Build sidebar HTML
// ============================================================
function createSidebar() {
  const currentPage = window.location.pathname.split('/').pop() || 'dashboard.html';

  let menuHTML = '';
  sidebarMenuItems.forEach(item => {
    if (item.type === 'divider') {
      menuHTML += '<div class="nav-divider"></div>';
    } else {
      const isActive = currentPage === item.href ? ' class="active"' : '';
      menuHTML += `<li><a href="${item.href}"${isActive}><i class="${item.icon}"></i>${item.text}</a></li>`;
    }
  });

  return `
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <div class="logo-icon"><i class="fas fa-prescription-bottle-medical"></i></div>
          <div class="logo-text">
            <span>الصيدلية</span>
            <span>نظام الإدارة</span>
          </div>
        </div>
      </div>

      <ul class="nav-menu">
        ${menuHTML}
      </ul>
      <div class="logout-section">
        <button id="logoutBtn"><i class="fas fa-sign-out-alt"></i>تسجيل الخروج</button>
      </div>
    </aside>
  `;
}

// ============================================================
// Inject styles
// ============================================================
function injectSidebarStyles() {
  if (document.getElementById('sidebar-styles')) return;
  const el = document.createElement('style');
  el.id = 'sidebar-styles';
  el.textContent = sidebarStyles;
  document.head.appendChild(el);
}

// ============================================================
// Init
// ============================================================
function initSidebar() {
  injectSidebarStyles();

  // Remove any previously rendered sidebar
  document.querySelectorAll('.sidebar, aside.sidebar, nav.sidebar').forEach(el => el.remove());

  document.body.insertAdjacentHTML('afterbegin', createSidebar());

  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);

  // Inject topbar bell into .user-section (exists on every admin page)
  injectTopbarBell();

  // Load settings and notifications asynchronously
  loadSystemSettings();
  loadNotificationCount();
}

async function handleLogout() {
  try {
    await fetch('../api.php?action=logout', { method: 'POST', credentials: 'include' });
  } catch (err) {
    console.error('Logout error:', err);
  }
  localStorage.removeItem('auth_user');
  window.location.href = '../login.php';
}

// Auto-run when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSidebar);
} else {
  initSidebar();
}

window.SidebarManager = {
  init:                 initSidebar,
  create:               createSidebar,
  menuItems:            sidebarMenuItems,
  refreshNotifications: loadNotificationCount
};
