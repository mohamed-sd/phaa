/**
 * Unified Sidebar Component
 * يتم استخدامه في جميع صفحات لوحة الإدارة
 */

// قائمة عناصر القائمة الجانبية
const sidebarMenuItems = [
  { href: 'dashboard.html', icon: 'fas fa-chart-line', text: 'لوحة التحكم' },
  { href: 'manage_products.html', icon: 'fas fa-pills', text: 'المنتجات' },
  { href: 'manage_categories.html', icon: 'fas fa-list', text: 'الفئات' },
  { href: 'manage_invoices.html', icon: 'fas fa-receipt', text: 'الفواتير' },
  { type: 'divider' },
  { href: 'manage_suppliers.html', icon: 'fas fa-truck', text: 'الموردين' },
  { href: 'manage_purchases.html', icon: 'fas fa-shopping-cart', text: 'المشتريات' },
  { type: 'divider' },
  { href: 'manage_reports.html', icon: 'fas fa-chart-bar', text: 'التقارير' },
  { href: 'manage_users.html', icon: 'fas fa-users', text: 'المستخدمين' },
  { href: 'settings.html', icon: 'fas fa-cog', text: 'الإعدادات' }
];

// CSS للقائمة الجانبية
const sidebarStyles = `
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
    box-shadow: -5px 0 25px rgba(8, 145, 178, 0.3);
  }

  .sidebar-header {
    padding: 24px 20px;
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
  }

  .sidebar-logo .logo-icon {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
  }

  .sidebar-logo .logo-text {
    display: flex;
    flex-direction: column;
  }

  .sidebar-logo .logo-text span:first-child {
    font-size: 18px;
    font-weight: 700;
  }

  .sidebar-logo .logo-text span:last-child {
    font-size: 11px;
    opacity: 0.8;
    font-weight: normal;
  }

  .nav-menu {
    list-style: none;
    margin: 0;
    padding: 16px 12px;
  }

  .nav-menu li {
    margin: 4px 0;
  }

  .nav-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 14px;
    border-radius: 10px;
    font-weight: 500;
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
    width: 4px;
    height: 70%;
    background: white;
    border-radius: 4px 0 0 4px;
  }

  .nav-menu a {
    position: relative;
  }

  .nav-menu i {
    width: 20px;
    text-align: center;
    font-size: 16px;
  }

  .nav-divider {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 12px 16px;
  }

  .nav-section-title {
    padding: 8px 16px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.5);
    font-weight: 600;
    margin-top: 8px;
  }

  .logout-section {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 16px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(255,255,255,0.1);
  }

  #logoutBtn {
    width: 100%;
    padding: 12px 16px;
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  #logoutBtn:hover {
    background: rgba(239,68,68,0.9);
    border-color: rgba(239,68,68,0.9);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(239,68,68,0.4);
  }

  /* تعديل المحتوى الرئيسي */
  main, .main-content {
    margin-right: 280px !important;
    width: calc(100% - 280px) !important;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .sidebar {
      width: 100%;
      height: auto;
      position: relative;
    }
    main, .main-content {
      margin-right: 0 !important;
      width: 100% !important;
    }
    .logout-section {
      position: relative;
      bottom: auto;
      margin-top: 20px;
    }
  }
`;

/**
 * إنشاء القائمة الجانبية
 */
function createSidebar() {
  // الحصول على اسم الصفحة الحالية
  const currentPage = window.location.pathname.split('/').pop() || 'dashboard.html';
  
  // إنشاء عناصر القائمة
  let menuHTML = '';
  sidebarMenuItems.forEach(item => {
    if (item.type === 'divider') {
      menuHTML += '<div class="nav-divider"></div>';
    } else {
      const isActive = currentPage === item.href ? ' class="active"' : '';
      menuHTML += `<li><a href="${item.href}"${isActive}><i class="${item.icon}"></i>${item.text}</a></li>`;
    }
  });

  // إنشاء HTML القائمة الجانبية
  const sidebarHTML = `
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <div class="logo-icon">
            <i class="fas fa-prescription-bottle-medical"></i>
          </div>
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

  return sidebarHTML;
}

/**
 * إضافة الأنماط CSS
 */
function injectSidebarStyles() {
  // التحقق من عدم وجود الأنماط مسبقاً
  if (document.getElementById('sidebar-styles')) return;
  
  const styleElement = document.createElement('style');
  styleElement.id = 'sidebar-styles';
  styleElement.textContent = sidebarStyles;
  document.head.appendChild(styleElement);
}

/**
 * تهيئة القائمة الجانبية
 */
function initSidebar() {
  // إضافة الأنماط أولاً
  injectSidebarStyles();
  
  // إزالة أي sidebar موجود قبل إنشاء الجديد
  const existingSidebars = document.querySelectorAll('.sidebar, aside.sidebar, nav.sidebar');
  existingSidebars.forEach(el => el.remove());
  
  // إضافة القائمة في بداية body
  document.body.insertAdjacentHTML('afterbegin', createSidebar());

  // إضافة حدث تسجيل الخروج
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', handleLogout);
  }
}

/**
 * معالجة تسجيل الخروج
 */
async function handleLogout() {
  try {
    await fetch('../api.php?action=logout', { 
      method: 'POST', 
      credentials: 'include' 
    });
  } catch (err) {
    console.error('Logout error:', err);
  }
  localStorage.removeItem('auth_user');
  window.location.href = '../login.php';
}

// تشغيل تلقائي عند تحميل الصفحة
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSidebar);
} else {
  // DOM already loaded
  initSidebar();
}

// تصدير للاستخدام الخارجي إذا لزم الأمر
window.SidebarManager = {
  init: initSidebar,
  create: createSidebar,
  menuItems: sidebarMenuItems
};
