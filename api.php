<?php
/**
 * API Endpoints
 * Pharmacy POS System
 */

// CORS & JSON headers - must be before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Catch any PHP fatal errors and return as JSON
set_error_handler(function($errno, $errstr) {
    echo json_encode(['success' => false, 'message' => 'PHP Error: ' . $errstr]);
    exit;
});

require_once 'config.php';
require_once 'execute_query.php';

// ============================================
// Get Request Method
// ============================================

$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['action']) ? $_GET['action'] : null;

try {
  
  // ============================================
  // CATEGORIES ENDPOINTS
  // ============================================
  
  if ($request === 'get_categories') {
    get_categories();
  }
  
  elseif ($request === 'get_category') {
    get_category();
  }
  
  // Category management (CRUD)
  elseif ($request === 'add_category' && $method === 'POST') {
    add_category();
  }
  elseif ($request === 'update_category' && $method === 'POST') {
    update_category();
  }
  elseif ($request === 'delete_category' && $method === 'POST') {
    delete_category();
  }

  // ============================================
  // PRODUCTS ENDPOINTS
  // ============================================
  
  elseif ($request === 'get_products') {
    get_products();
  }
  
  elseif ($request === 'get_product') {
    get_product();
  }
  
  elseif ($request === 'add_product' && $method === 'POST') {
    add_product();
  }
  
  elseif ($request === 'update_product' && $method === 'POST') {
    update_product();
  }
  
  elseif ($request === 'delete_product' && $method === 'POST') {
    delete_product();
  }
  
  elseif ($request === 'toggle_product_status' && $method === 'POST') {
    toggle_product_status();
  }
  
  elseif ($request === 'get_products_admin') {
    get_products_admin();
  }
  
  elseif ($request === 'search_products') {
    search_products();
  }
  
  elseif ($request === 'get_products_by_category') {
    get_products_by_category();
  }
  
  // ============================================
  // INVOICES ENDPOINTS
  // ============================================
  
  elseif ($request === 'create_invoice' && $method === 'POST') {
    create_invoice();
  }
  
  elseif ($request === 'add_invoice_item' && $method === 'POST') {
    add_invoice_item();
  }
  
  elseif ($request === 'get_invoice') {
    get_invoice();
  }
  
  elseif ($request === 'get_invoices') {
    get_invoices();
  }
  
  elseif ($request === 'get_invoice_items') {
    get_invoice_items();
  }
  
  elseif ($request === 'get_daily_sales') {
    get_daily_sales();
  }
  
  // ============================================
  // STOCK ENDPOINTS
  // ============================================
  
  elseif ($request === 'update_stock' && $method === 'POST') {
    update_stock();
  }
  
  elseif ($request === 'get_stock_history') {
    get_stock_history();
  }
  
  elseif ($request === 'get_inventory_status') {
    get_inventory_status();
  }
  
  // ============================================
  // REPORTS ENDPOINTS
  // ============================================
  
  elseif ($request === 'get_top_products') {
    get_top_products();
  }
  
  elseif ($request === 'get_sales_report') {
    get_sales_report();
  }
  
  // ============================================
  // AUTHENTICATION ENDPOINTS
  // ============================================
  
  elseif ($request === 'login' && $method === 'POST') {
    login();
  }
  
  elseif ($request === 'logout' && $method === 'POST') {
    logout();
  }
  
  elseif ($request === 'check_session' && $method === 'GET') {
    check_session();
  }
  
  // ============================================
  // DASHBOARD ENDPOINTS
  // ============================================
  
  elseif ($request === 'get_dashboard_stats') {
    get_dashboard_stats();
  }
  
  // ============================================
  // USER MANAGEMENT ENDPOINTS
  // ============================================
  
  elseif ($request === 'get_users') {
    get_users();
  }
  
  elseif ($request === 'add_user' && $method === 'POST') {
    add_user();
  }
  
  elseif ($request === 'update_user' && $method === 'POST') {
    update_user();
  }
  
  elseif ($request === 'toggle_user_status' && $method === 'POST') {
    toggle_user_status();
  }
  
  // ============================================
  // SETTINGS ENDPOINTS
  // ============================================
  
  elseif ($request === 'get_settings') {
    get_settings();
  }
  
  elseif ($request === 'update_settings' && $method === 'POST') {
    update_settings();
  }
  
  elseif ($request === 'get_alerts') {
    get_alerts();
  }
  
  // ============================================
  // BACKUP ENDPOINTS
  // ============================================
  
  elseif ($request === 'export_database') {
    export_database();
  }
  
  elseif ($request === 'import_database' && $method === 'POST') {
    import_database();
  }
  
  // ============================================
  // SUPPLIERS ENDPOINTS (الموردين)
  // ============================================
  
  elseif ($request === 'get_suppliers') {
    get_suppliers();
  }
  
  elseif ($request === 'get_supplier') {
    get_supplier();
  }
  
  elseif ($request === 'add_supplier' && $method === 'POST') {
    add_supplier();
  }
  
  elseif ($request === 'update_supplier' && $method === 'POST') {
    update_supplier();
  }
  
  elseif ($request === 'delete_supplier' && $method === 'POST') {
    delete_supplier();
  }
  
  elseif ($request === 'toggle_supplier_status' && $method === 'POST') {
    toggle_supplier_status();
  }
  
  // ============================================
  // PURCHASES ENDPOINTS (المشتريات)
  // ============================================
  
  elseif ($request === 'get_purchases') {
    get_purchases();
  }
  
  elseif ($request === 'get_purchase') {
    get_purchase();
  }
  
  elseif ($request === 'create_purchase' && $method === 'POST') {
    create_purchase();
  }
  
  elseif ($request === 'update_purchase' && $method === 'POST') {
    update_purchase();
  }
  
  elseif ($request === 'delete_purchase' && $method === 'POST') {
    delete_purchase();
  }
  
  elseif ($request === 'get_purchase_items') {
    get_purchase_items();
  }
  
  elseif ($request === 'add_supplier_payment' && $method === 'POST') {
    add_supplier_payment();
  }
  
  elseif ($request === 'get_supplier_payments') {
    get_supplier_payments();
  }

  elseif ($request === 'get_purchase_payments') {
    get_purchase_payments();
  }

  // ============================================
  // PAYMENT METHODS ENDPOINTS (طرق الدفع) - Feature 5
  // ============================================

  elseif ($request === 'get_payment_methods') {
    get_payment_methods();
  }
  elseif ($request === 'add_payment_method' && $method === 'POST') {
    add_payment_method();
  }
  elseif ($request === 'update_payment_method' && $method === 'POST') {
    update_payment_method();
  }
  elseif ($request === 'toggle_payment_method' && $method === 'POST') {
    toggle_payment_method();
  }
  elseif ($request === 'delete_payment_method' && $method === 'POST') {
    delete_payment_method();
  }

  // ============================================
  // CUSTOMERS ENDPOINTS (العملاء) - Feature 4
  // ============================================

  elseif ($request === 'get_customers') {
    get_customers();
  }
  elseif ($request === 'get_customer') {
    get_customer();
  }
  elseif ($request === 'add_customer' && $method === 'POST') {
    add_customer();
  }
  elseif ($request === 'update_customer' && $method === 'POST') {
    update_customer();
  }
  elseif ($request === 'toggle_customer_status' && $method === 'POST') {
    toggle_customer_status();
  }
  elseif ($request === 'get_customer_balances') {
    get_customer_balances();
  }

  // ============================================
  // PARTIAL PAYMENTS ENDPOINTS (الدفع الجزئي) - Feature 4
  // ============================================

  elseif ($request === 'add_invoice_payment' && $method === 'POST') {
    add_invoice_payment();
  }
  elseif ($request === 'get_invoice_payments') {
    get_invoice_payments();
  }
  elseif ($request === 'get_outstanding_invoices') {
    get_outstanding_invoices();
  }
  elseif ($request === 'get_cashier_invoices') {
    get_cashier_invoices();
  }

  // ============================================
  // INVOICE RETURNS ENDPOINTS (مرتجعات الفواتير) - Feature 1
  // ============================================

  elseif ($request === 'search_invoice_for_return') {
    search_invoice_for_return();
  }
  elseif ($request === 'return_invoice_item' && $method === 'POST') {
    return_invoice_item();
  }
  elseif ($request === 'return_full_invoice' && $method === 'POST') {
    return_full_invoice();
  }
  elseif ($request === 'get_returns_report') {
    get_returns_report();
  }
  elseif ($request === 'get_returned_products_report') {
    get_returned_products_report();
  }
  elseif ($request === 'get_return_stats') {
    get_return_stats();
  }

  // ============================================
  // INSURANCE ENDPOINTS (التأمين الصحي) - Feature 2
  // ============================================

  elseif ($request === 'get_insurance_companies') {
    get_insurance_companies();
  }
  elseif ($request === 'get_insurance_company') {
    get_insurance_company();
  }
  elseif ($request === 'add_insurance_company' && $method === 'POST') {
    add_insurance_company();
  }
  elseif ($request === 'update_insurance_company' && $method === 'POST') {
    update_insurance_company();
  }
  elseif ($request === 'toggle_insurance_company' && $method === 'POST') {
    toggle_insurance_company();
  }
  elseif ($request === 'delete_insurance_company' && $method === 'POST') {
    delete_insurance_company();
  }
  elseif ($request === 'get_insurance_claims_report') {
    get_insurance_claims_report();
  }

  else {
    json_response(false, 'طلب غير صحيح');
  }
  
} catch (Exception $e) {
  log_error($e->getMessage());
  json_response(false, $e->getMessage());
}

// ============================================
// CATEGORIES FUNCTIONS
// ============================================

function get_categories() {
  global $conn;
  $query = "SELECT * FROM categories ORDER BY name";
  $result = get_all($query);
  json_response(true, 'تم جلب الفئات', $result);
}

function get_category() {
  global $conn;
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  
  if ($id <= 0) {
    json_response(false, 'معرّف الفئة غير صحيح');
  }
  
  $query = "SELECT * FROM categories WHERE id = $id";
  $result = get_row($query);
  
  if (!$result) {
    json_response(false, 'الفئة غير موجودة');
  }
  
  json_response(true, 'تم جلب الفئة', $result);
}

// Category CRUD functions
function add_category() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);

  if (empty($data['name'])) {
    json_response(false, 'اسم الفئة مطلوب');
  }

  $name = escape_string($data['name']);
  $description = escape_string($data['description'] ?? '');
  $icon = escape_string($data['icon'] ?? '');

  $query = "INSERT INTO categories (name, description, icon) VALUES ('$name', '$description', '$icon')";
  execute_query($query);
  $id = $conn->insert_id;

  json_response(true, 'تم إضافة الفئة', ['id' => $id]);
}

function update_category() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);

  if (empty($data['id']) || empty($data['name'])) {
    json_response(false, 'بيانات ناقصة');
  }

  $id = intval($data['id']);
  $name = escape_string($data['name']);
  $description = escape_string($data['description'] ?? '');
  $icon = escape_string($data['icon'] ?? '');

  $query = "UPDATE categories SET name = '$name', description = '$description', icon = '$icon' WHERE id = $id";
  execute_query($query);

  json_response(true, 'تم تحديث الفئة');
}

function delete_category() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);

  if (empty($data['id'])) {
    json_response(false, 'معرّف الفئة غير صحيح');
  }

  $id = intval($data['id']);

  // Optionally: ensure no products assigned or cascade
  $query = "DELETE FROM categories WHERE id = $id";
  execute_query($query);

  json_response(true, 'تم حذف الفئة');
}

// ============================================
// PRODUCTS FUNCTIONS
// ============================================

function get_products() {
  // المنتجات النشطة فقط للبيع
  $query = "SELECT * FROM products WHERE is_active = TRUE OR is_active IS NULL ORDER BY name";
  $result = get_all($query);
  json_response(true, 'تم جلب المنتجات', $result);
}

// جلب كل المنتجات للإدارة (مع فلترة)
function get_products_admin() {
  $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
  
  if ($filter === 'active') {
    $query = "SELECT * FROM products WHERE is_active = TRUE OR is_active IS NULL ORDER BY name";
  } elseif ($filter === 'disabled') {
    $query = "SELECT * FROM products WHERE is_active = FALSE ORDER BY name";
  } else {
    $query = "SELECT * FROM products ORDER BY name";
  }
  
  $result = get_all($query);
  json_response(true, 'تم جلب المنتجات', $result);
}

// تفعيل/تعطيل المنتج
function toggle_product_status() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id'])) {
    json_response(false, 'معرّف المنتج غير صحيح');
  }
  
  $id = intval($data['id']);
  $is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 0;
  
  $query = "UPDATE products SET is_active = $is_active WHERE id = $id";
  execute_query($query);
  
  $status = $is_active ? 'تم تفعيل المنتج' : 'تم تعطيل المنتج';
  json_response(true, $status);
}

function get_product() {
  global $conn;
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  
  if ($id <= 0) {
    json_response(false, 'معرّف المنتج غير صحيح');
  }
  
  $query = "SELECT * FROM products WHERE id = $id";
  $result = get_row($query);
  
  if (!$result) {
    json_response(false, 'المنتج غير موجود');
  }
  
  json_response(true, 'تم جلب المنتج', $result);
}

function get_products_by_category() {
  global $conn;
  $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
  
  if ($category_id <= 0) {
    json_response(false, 'معرّف الفئة غير صحيح');
  }
  
  // المنتجات النشطة فقط
  $query = "SELECT p.* FROM products p 
            WHERE p.category_id = $category_id 
            AND (p.is_active = TRUE OR p.is_active IS NULL)
            ORDER BY p.name";
  $result = get_all($query);
  json_response(true, 'تم جلب المنتجات', $result);
}

function search_products() {
  global $conn;
  $search = isset($_GET['q']) ? escape_string($_GET['q']) : '';
  
  if (strlen($search) < 2) {
    json_response(false, 'طول البحث قصير جداً');
  }
  
  $query = "SELECT * FROM products 
            WHERE (name LIKE '%$search%' 
            OR barcode LIKE '%$search%'
            OR description LIKE '%$search%')
            AND (is_active = TRUE OR is_active IS NULL)
            ORDER BY name
            LIMIT 20";
  $result = get_all($query);
  json_response(true, 'نتائج البحث', $result);
}

function add_product() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  // Validation
  if (empty($data['category_id']) || empty($data['name']) || empty($data['price'])) {
    json_response(false, 'بيانات ناقصة');
  }
  
  $category_id = intval($data['category_id']);
  $name = escape_string($data['name']);
  $description = escape_string($data['description'] ?? '');
  $price = floatval($data['price']);
  $stock = intval($data['stock'] ?? 0);
  $stock_strips = intval($data['stock_strips'] ?? $stock);
  $strips_per_box = intval($data['strips_per_box'] ?? 3);
  $boxes_per_carton = intval($data['boxes_per_carton'] ?? 12);
  $price_strip = isset($data['price_strip']) ? floatval($data['price_strip']) : $price;
  $price_box = isset($data['price_box']) ? floatval($data['price_box']) : null;
  $price_carton = isset($data['price_carton']) ? floatval($data['price_carton']) : null;
  $image_url = escape_string($data['image_url'] ?? '');
  $barcode = escape_string($data['barcode'] ?? '');
  $expiry_date = !empty($data['expiry_date']) ? "'{$data['expiry_date']}'" : 'NULL';
  $min_stock = intval($data['min_stock'] ?? 10);
  
  if ($price_strip <= 0) {
    json_response(false, 'السعر يجب أن يكون أكبر من صفر');
  }

  $price_box_sql = is_null($price_box) ? 'NULL' : $price_box;
  $price_carton_sql = is_null($price_carton) ? 'NULL' : $price_carton;
  
  $query = "INSERT INTO products (category_id, name, description, price, stock, stock_strips, strips_per_box, boxes_per_carton, price_strip, price_box, price_carton, image_url, barcode, expiry_date, min_stock)
            VALUES ($category_id, '$name', '$description', $price_strip, $stock_strips, $stock_strips, $strips_per_box, $boxes_per_carton, $price_strip, $price_box_sql, $price_carton_sql, '$image_url', '$barcode', $expiry_date, $min_stock)";
  
  execute_query($query);
  $product_id = $conn->insert_id;
  
  json_response(true, 'تم إضافة المنتج', ['id' => $product_id]);
}

function update_product() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  // Validation
  if (empty($data['id']) || empty($data['name']) || empty($data['price'])) {
    json_response(false, 'بيانات ناقصة');
  }
  
  $id = intval($data['id']);
  $name = escape_string($data['name']);
  $description = escape_string($data['description'] ?? '');
  $price = floatval($data['price']);
  $stock = intval($data['stock'] ?? 0);
  $stock_strips = intval($data['stock_strips'] ?? $stock);
  $strips_per_box = intval($data['strips_per_box'] ?? 3);
  $boxes_per_carton = intval($data['boxes_per_carton'] ?? 12);
  $price_strip = isset($data['price_strip']) ? floatval($data['price_strip']) : $price;
  $price_box = isset($data['price_box']) ? floatval($data['price_box']) : null;
  $price_carton = isset($data['price_carton']) ? floatval($data['price_carton']) : null;
  $image_url = escape_string($data['image_url'] ?? '');
  $barcode = escape_string($data['barcode'] ?? '');
  $expiry_date = !empty($data['expiry_date']) ? "'{$data['expiry_date']}'" : 'NULL';
  $min_stock = intval($data['min_stock'] ?? 10);

  $price_box_sql = is_null($price_box) ? 'NULL' : $price_box;
  $price_carton_sql = is_null($price_carton) ? 'NULL' : $price_carton;
  
  $query = "UPDATE products 
            SET name = '$name', 
                description = '$description', 
                price = $price_strip, 
                stock = $stock_strips,
                stock_strips = $stock_strips,
                strips_per_box = $strips_per_box,
                boxes_per_carton = $boxes_per_carton,
                price_strip = $price_strip,
                price_box = $price_box_sql,
                price_carton = $price_carton_sql,
                image_url = '$image_url',
                barcode = '$barcode',
                expiry_date = $expiry_date,
                min_stock = $min_stock
            WHERE id = $id";
  
  execute_query($query);
  json_response(true, 'تم تحديث المنتج');
}

function delete_product() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id'])) {
    json_response(false, 'معرّف المنتج غير صحيح');
  }
  
  $id = intval($data['id']);
  
  // التحقق من وجود فواتير مرتبطة بهذا المنتج
  $check_query = "SELECT COUNT(*) as count FROM invoice_items WHERE product_id = $id";
  $result = $conn->query($check_query);
  $row = $result->fetch_assoc();
  
  if ($row['count'] > 0) {
    // يوجد فواتير مرتبطة - لا يمكن الحذف
    json_response(false, 'لا يمكن حذف هذا المنتج لأنه مرتبط بـ ' . $row['count'] . ' فاتورة. يمكنك تعطيله بدلاً من حذفه أو حذف الفواتير المرتبطة أولاً.');
  }
  
  $query = "DELETE FROM products WHERE id = $id";
  execute_query($query);
  
  json_response(true, 'تم حذف المنتج');
}

// ============================================
// INVOICES FUNCTIONS
// ============================================

/**
 * إنشاء فاتورة بيع كاملة بشكل ذرّي (atomic) داخل معاملة قاعدة بيانات.
 * يدعم: العملاء، الخصم اليدوي، طرق الدفع الديناميكية، التأمين الصحي، والدفع الجزئي.
 *
 * المدخلات المتوقعة (JSON):
 *  - items: [{ product_id, unit, unit_quantity, base_quantity, unit_price }]  (مطلوب)
 *  - branch_id, customer_id, customer_name
 *  - tax_amount, discount_amount
 *  - payment_method_id
 *  - insurance_company_id (اختياري)
 *  - paid_amount (للدفع الجزئي؛ الافتراضي = كامل المبلغ المستحق على العميل)
 */
function create_invoice() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);

  $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
  if (count($items) === 0) {
    // تسجيل تشخيصي: ما الذي وصل فعلاً عند فشل التحقق؟ (لتحديد السبب لاحقاً)
    $received = is_array($data) ? implode(',', array_keys($data)) : gettype($data);
    log_error('create_invoice rejected: empty items. payload keys=[' . $received . ']');
    json_response(false, 'لا يمكن إنشاء فاتورة بدون منتجات — لم تصل أي أصناف إلى الخادم. حدّث الصفحة (Ctrl+F5) وأعد المحاولة.');
  }

  $branch_id    = intval($data['branch_id'] ?? 1);
  // المستخدم الفعلي من الجلسة (مع التراجع إلى المدخل أو 1 توافقاً مع النظام الحالي)
  $cashier_id   = current_user_id() ?? intval($data['cashier_id'] ?? 1);
  $customer_id  = !empty($data['customer_id']) ? intval($data['customer_id']) : null;
  $customer_name = isset($data['customer_name']) ? trim($data['customer_name']) : '';
  $tax_amount      = round(floatval($data['tax_amount'] ?? 0), 2);
  $discount_amount = round(floatval($data['discount_amount'] ?? 0), 2);
  $payment_method_id = !empty($data['payment_method_id']) ? intval($data['payment_method_id']) : null;
  $insurance_company_id = !empty($data['insurance_company_id']) ? intval($data['insurance_company_id']) : null;

  // التحقق من طريقة الدفع وجلب اسمها (لقطة تاريخية)
  $payment_method_name = 'نقداً';
  if ($payment_method_id) {
    $pm = get_row("SELECT id, name FROM payment_methods WHERE id = $payment_method_id AND is_active = 1");
    if (!$pm) {
      json_response(false, 'طريقة الدفع غير صالحة');
    }
    $payment_method_name = $pm['name'];
  } else {
    // التراجع إلى طريقة النظام الافتراضية (نقداً)
    $pm = get_row("SELECT id, name FROM payment_methods WHERE is_system = 1 AND is_active = 1 ORDER BY sort_order LIMIT 1");
    if ($pm) {
      $payment_method_id = intval($pm['id']);
      $payment_method_name = $pm['name'];
    }
  }

  // التأمين الصحي
  $insurance_pct = 0;
  if ($insurance_company_id) {
    if (!insurance_enabled()) {
      json_response(false, 'نظام التأمين الصحي غير مُفعّل');
    }
    $ins = get_row("SELECT id, discount_percentage FROM insurance_companies WHERE id = $insurance_company_id AND is_active = 1");
    if (!$ins) {
      json_response(false, 'شركة التأمين غير صالحة');
    }
    $insurance_pct = floatval($ins['discount_percentage']);
  }

  try {
    $result = db_transaction(function($conn) use (
      $items, $branch_id, $cashier_id, $customer_id, $customer_name,
      $tax_amount, $discount_amount, $payment_method_id, $payment_method_name,
      $insurance_company_id, $insurance_pct, $data
    ) {
      // 1) التحقق من المنتجات والمخزون + حساب المجموع الفرعي من جانب الخادم
      $subtotal = 0;
      $clean_items = [];
      foreach ($items as $it) {
        $product_id    = intval($it['product_id'] ?? 0);
        $unit          = in_array(($it['unit'] ?? 'strip'), ['strip','box','carton']) ? $it['unit'] : 'strip';
        $unit_quantity = intval($it['unit_quantity'] ?? 0);
        $base_quantity = intval($it['base_quantity'] ?? 0);
        $unit_price    = round(floatval($it['unit_price'] ?? 0), 2);

        if ($product_id <= 0 || $unit_quantity <= 0 || $base_quantity <= 0) {
          throw new Exception('بيانات منتج غير صحيحة في الفاتورة');
        }

        $product = get_row("SELECT id, name, COALESCE(stock_strips, stock) AS available FROM products WHERE id = $product_id");
        if (!$product) {
          throw new Exception('منتج غير موجود (#' . $product_id . ')');
        }
        if (intval($product['available']) < $base_quantity) {
          throw new Exception('الكمية المطلوبة من "' . $product['name'] . '" تتجاوز المخزون المتاح');
        }

        $line_total = round($unit_price * $unit_quantity, 2);
        $subtotal += $line_total;
        $clean_items[] = compact('product_id', 'unit', 'unit_quantity', 'base_quantity', 'unit_price', 'line_total');
      }
      $subtotal = round($subtotal, 2);

      // 2) حساب الإجماليات والتأمين
      $total_amount = round($subtotal + $tax_amount - $discount_amount, 2);
      if ($total_amount < 0) $total_amount = 0;

      $insurance_due = $insurance_company_id ? round($total_amount * $insurance_pct / 100, 2) : 0;
      $insurance_discount = $insurance_due;
      $customer_payable = round($total_amount - $insurance_due, 2);

      // 3) الدفع الجزئي: المبلغ المدفوع من العميل
      $paid_amount = isset($data['paid_amount']) ? round(floatval($data['paid_amount']), 2) : $customer_payable;
      if ($paid_amount < 0) $paid_amount = 0;
      if ($paid_amount > $customer_payable) $paid_amount = $customer_payable;
      $remaining = round($customer_payable - $paid_amount, 2);

      if ($remaining > 0.001) {
        $status = ($paid_amount > 0.001) ? 'partial' : 'unpaid';
      } else {
        $status = 'paid';
      }

      // 4) إدراج رأس الفاتورة
      $invoice_number = 'INV-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
      $inv_num_esc = $conn->real_escape_string($invoice_number);
      $cust_name_esc = $conn->real_escape_string($customer_name);
      $pm_id_sql  = $payment_method_id ? intval($payment_method_id) : 'NULL';
      $cust_id_sql = $customer_id ? intval($customer_id) : 'NULL';
      $ins_id_sql  = $insurance_company_id ? intval($insurance_company_id) : 'NULL';
      $pm_name_esc = $conn->real_escape_string($payment_method_name);

      tx_query("INSERT INTO invoices
                 (invoice_number, branch_id, cashier_id, customer_id, customer_name,
                  subtotal, tax_amount, discount_amount, total_amount,
                  payment_method, payment_method_id, status, paid_amount, remaining_amount,
                  insurance_company_id, insurance_discount, insurance_due)
                VALUES
                 ('$inv_num_esc', $branch_id, $cashier_id, $cust_id_sql, " .
                  ($customer_name !== '' ? "'$cust_name_esc'" : 'NULL') . ",
                  $subtotal, $tax_amount, $discount_amount, $total_amount,
                  '$pm_name_esc', $pm_id_sql, '$status', $paid_amount, $remaining,
                  $ins_id_sql, $insurance_discount, $insurance_due)");
      $invoice_id = $conn->insert_id;

      // 5) إدراج العناصر + خصم المخزون + سجل المبيعات + سجل المخزون
      foreach ($clean_items as $ci) {
        $pid = $ci['product_id'];
        $unit = $conn->real_escape_string($ci['unit']);
        $uq = $ci['unit_quantity'];
        $bq = $ci['base_quantity'];
        $up = $ci['unit_price'];
        $lt = $ci['line_total'];

        tx_query("INSERT INTO invoice_items
                   (invoice_id, product_id, quantity, unit, unit_quantity, base_quantity, unit_price, total_price)
                  VALUES ($invoice_id, $pid, $bq, '$unit', $uq, $bq, $up, $lt)");

        tx_query("UPDATE products
                     SET stock = stock - $bq,
                         stock_strips = IFNULL(stock_strips, stock) - $bq
                   WHERE id = $pid");

        tx_query("INSERT INTO sales
                   (invoice_id, product_id, quantity, unit, unit_quantity, base_quantity, amount, sales_date)
                  VALUES ($invoice_id, $pid, $bq, '$unit', $uq, $bq, $lt, CURDATE())");

        tx_query("INSERT INTO stock_history (product_id, quantity_change, operation_type, notes)
                  VALUES ($pid, -$bq, 'sale', 'بيع - فاتورة $inv_num_esc')");
      }

      // 6) تسجيل الدفعة الأولى (إن وُجدت)
      if ($paid_amount > 0.001) {
        $uid_sql = $cashier_id ? intval($cashier_id) : 'NULL';
        tx_query("INSERT INTO invoice_payments
                   (invoice_id, amount, payment_method_id, payment_method_name, user_id, notes)
                  VALUES ($invoice_id, $paid_amount, $pm_id_sql, '$pm_name_esc', $uid_sql, 'دفعة عند إنشاء الفاتورة')");
      }

      // 7) رصيد العميل (للمبلغ المتبقي)
      if ($customer_id && $remaining > 0.001) {
        tx_query("UPDATE customers SET balance = balance + $remaining WHERE id = $customer_id");
      }

      return [
        'invoice_id' => $invoice_id,
        'invoice_number' => $invoice_number,
        'subtotal' => $subtotal,
        'total_amount' => $total_amount,
        'insurance_due' => $insurance_due,
        'customer_payable' => $customer_payable,
        'paid_amount' => $paid_amount,
        'remaining_amount' => $remaining,
        'status' => $status
      ];
    });

    audit_log('create_invoice', 'invoice', $result['invoice_id'], [
      'invoice_number' => $result['invoice_number'],
      'total_amount' => $result['total_amount'],
      'paid_amount' => $result['paid_amount'],
      'remaining' => $result['remaining_amount'],
      'status' => $result['status'],
      'insurance_due' => $result['insurance_due']
    ]);

    json_response(true, 'تم إنشاء الفاتورة', $result);

  } catch (Exception $e) {
    json_response(false, $e->getMessage());
  }
}

/**
 * هل نظام التأمين الصحي مُفعّل في الإعدادات؟
 */
function insurance_enabled() {
  $row = get_row("SELECT setting_value FROM pharmacy_settings WHERE setting_key = 'uses_health_insurance'");
  return $row && $row['setting_value'] === '1';
}

function add_invoice_item() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  // Validation
  if (empty($data['invoice_id']) || empty($data['product_id'])) {
    json_response(false, 'بيانات ناقصة');
  }
  
  $invoice_id = intval($data['invoice_id']);
  $product_id = intval($data['product_id']);
  $unit = escape_string($data['unit'] ?? 'strip');
  $unit_quantity = intval($data['unit_quantity'] ?? $data['quantity'] ?? 0);
  $base_quantity = intval($data['base_quantity'] ?? $data['quantity'] ?? 0);
  $quantity = $base_quantity;
  $unit_price = floatval($data['unit_price']);
  $total_price = $unit_price * $unit_quantity;

  if ($unit_quantity <= 0 || $base_quantity <= 0) {
    json_response(false, 'الكمية غير صحيحة');
  }
  
  $query = "INSERT INTO invoice_items (invoice_id, product_id, quantity, unit, unit_quantity, base_quantity, unit_price, total_price)
            VALUES ($invoice_id, $product_id, $quantity, '$unit', $unit_quantity, $base_quantity, $unit_price, $total_price)";
  
  execute_query($query);
  
  // Update stock
  $query = "UPDATE products 
            SET stock = stock - $base_quantity,
                stock_strips = IFNULL(stock_strips, stock) - $base_quantity
            WHERE id = $product_id";
  execute_query($query);
  
  // Add to sales table
  $query = "INSERT INTO sales (invoice_id, product_id, quantity, unit, unit_quantity, base_quantity, amount, sales_date)
            VALUES ($invoice_id, $product_id, $base_quantity, '$unit', $unit_quantity, $base_quantity, $total_price, CURDATE())";
  execute_query($query);
  
  json_response(true, 'تم إضافة المنتج إلى الفاتورة');
}

function get_invoice() {
  global $conn;
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  
  if ($id <= 0) {
    json_response(false, 'معرّف الفاتورة غير صحيح');
  }
  
  // إضافة أسماء الكاشير والعميل والتأمين (إضافي وآمن — لا يغيّر منطق الإنشاء)
  $query = "SELECT i.*,
                   u.full_name AS cashier_name,
                   c.name      AS customer_db_name,
                   ic.name     AS insurance_name
            FROM invoices i
            LEFT JOIN users u ON i.cashier_id = u.id
            LEFT JOIN customers c ON i.customer_id = c.id
            LEFT JOIN insurance_companies ic ON i.insurance_company_id = ic.id
            WHERE i.id = $id";
  $invoice = get_row($query);

  if (!$invoice) {
    json_response(false, 'الفاتورة غير موجودة');
  }

  // Get Invoice Items
  $query = "SELECT ii.*, p.name as product_name FROM invoice_items ii
            JOIN products p ON ii.product_id = p.id
            WHERE ii.invoice_id = $id";
  $items = get_all($query);

  $invoice['items'] = $items;
  json_response(true, 'تم جلب الفاتورة', $invoice);
}

/**
 * فواتير اليوم للكاشير الحالي (ميزة سجل فواتير الكاشير).
 * - الكاشير يرى فواتيره فقط لليوم الحالي.
 * - المدير/الأدمن يرى كل فواتير اليوم.
 * يدعم البحث برقم الفاتورة والترقيم (pagination).
 */
function get_cashier_invoices() {
  $uid = current_user_id();
  if (!$uid) {
    json_response(false, 'يجب تسجيل الدخول');
  }
  $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'cashier';
  $date = isset($_GET['date']) ? escape_string($_GET['date']) : date('Y-m-d');
  $search = isset($_GET['search']) ? escape_string(trim($_GET['search'])) : '';
  $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
  $offset = isset($_GET['offset']) ? max(0, intval($_GET['offset'])) : 0;

  $where = "WHERE DATE(i.created_at) = '$date'";
  // قاعدة الأمان: الكاشير يرى فواتيره فقط؛ المدير/الأدمن يرى الكل
  if ($role !== 'admin' && $role !== 'manager') {
    $where .= " AND i.cashier_id = " . intval($uid);
  }
  if ($search !== '') {
    $where .= " AND i.invoice_number LIKE '%$search%'";
  }

  $rows = get_all("SELECT i.id, i.invoice_number, i.created_at, i.customer_name,
                          c.name AS customer_db_name, i.total_amount, i.payment_method,
                          i.status, u.full_name AS cashier_name
                   FROM invoices i
                   LEFT JOIN customers c ON i.customer_id = c.id
                   LEFT JOIN users u ON i.cashier_id = u.id
                   $where
                   ORDER BY i.created_at DESC, i.id DESC
                   LIMIT $limit OFFSET $offset");

  $count = get_row("SELECT COUNT(*) AS total FROM invoices i $where");

  json_response(true, 'تم جلب فواتير اليوم', [
    'invoices' => $rows,
    'total' => intval($count['total']),
    'limit' => $limit,
    'offset' => $offset,
    'role' => $role
  ]);
}

function get_invoices() {
  $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
  $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
  
  $query = "SELECT * FROM invoices ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
  $invoices = get_all($query);
  
  // Get total count
  $count_query = "SELECT COUNT(*) as total FROM invoices";
  $count = get_row($count_query);
  
  json_response(true, 'تم جلب الفواتير', [
    'invoices' => $invoices,
    'total' => $count['total']
  ]);
}

function get_invoice_items() {
  $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
  
  if (!$invoice_id) {
    json_response(false, 'معرف الفاتورة مطلوب');
    return;
  }
  
  $query = "SELECT ii.*, p.name as product_name 
            FROM invoice_items ii 
            LEFT JOIN products p ON ii.product_id = p.id 
            WHERE ii.invoice_id = $invoice_id";
  $items = get_all($query);
  
  json_response(true, 'تم جلب منتجات الفاتورة', $items);
}

function get_daily_sales() {
  $date = isset($_GET['date']) ? escape_string($_GET['date']) : date('Y-m-d');
  
  $query = "SELECT * FROM daily_sales_report 
            WHERE sales_date = '$date'";
  $result = get_row($query);
  
  if (!$result) {
    $result = [
      'sales_date' => $date,
      'invoice_count' => 0,
      'total_subtotal' => 0,
      'total_tax' => 0,
      'total_revenue' => 0
    ];
  }
  
  json_response(true, 'تم جلب البيانات', $result);
}

// ============================================
// STOCK FUNCTIONS
// ============================================

function update_stock() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['product_id']) || empty($data['quantity'])) {
    json_response(false, 'بيانات ناقصة');
  }
  
  $product_id = intval($data['product_id']);
  $quantity = intval($data['quantity']);
  $operation = escape_string($data['operation'] ?? 'ADD');
  $notes = escape_string($data['notes'] ?? '');
  
  if ($operation === 'ADD') {
    $query = "UPDATE products 
              SET stock = stock + $quantity,
                  stock_strips = IFNULL(stock_strips, stock) + $quantity
              WHERE id = $product_id";
  } else {
    $query = "UPDATE products 
              SET stock = stock - $quantity,
                  stock_strips = IFNULL(stock_strips, stock) - $quantity
              WHERE id = $product_id AND stock >= $quantity";
  }
  
  execute_query($query);
  
  // Record in history
  $quantity_change = ($operation === 'ADD') ? $quantity : -$quantity;
  $query = "INSERT INTO stock_history (product_id, quantity_change, operation_type, notes)
            VALUES ($product_id, $quantity_change, '$operation', '$notes')";
  execute_query($query);
  
  json_response(true, 'تم تحديث المخزون');
}

function get_stock_history() {
  $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
  $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
  
  if ($product_id > 0) {
    $query = "SELECT * FROM stock_history 
              WHERE product_id = $product_id 
              ORDER BY created_at DESC 
              LIMIT $limit";
  } else {
    $query = "SELECT * FROM stock_history 
              ORDER BY created_at DESC 
              LIMIT $limit";
  }
  
  $result = get_all($query);
  json_response(true, 'تم جلب سجل المخزون', $result);
}

function get_inventory_status() {
  $query = "SELECT * FROM inventory_status";
  $result = get_all($query);
  json_response(true, 'تم جلب حالة المخزون', $result);
}

// ============================================
// REPORTS FUNCTIONS
// ============================================

function get_top_products() {
  $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
  
  $query = "SELECT * FROM top_selling_products LIMIT $limit";
  $result = get_all($query);
  json_response(true, 'تم جلب المنتجات الأكثر مبيعاً', $result);
}

function get_sales_report() {
  $start_date = isset($_GET['start_date']) ? escape_string($_GET['start_date']) : date('Y-m-01');
  $end_date = isset($_GET['end_date']) ? escape_string($_GET['end_date']) : date('Y-m-d');
  
  $query = "SELECT * FROM daily_sales_report 
            WHERE sales_date BETWEEN '$start_date' AND '$end_date'
            ORDER BY sales_date DESC";
  $result = get_all($query);
  json_response(true, 'تم جلب التقرير', $result);
}

// ============================================
// AUTHENTICATION FUNCTIONS
// ============================================

function login() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['username']) || empty($data['password'])) {
    json_response(false, 'اسم المستخدم وكلمة المرور مطلوبان');
  }
  
  $username = escape_string($data['username']);
  $password = escape_string($data['password']);
  
  // Find user
  $query = "SELECT id, username, full_name, role, password FROM users WHERE username = '$username' AND is_active = TRUE";
  $user = get_row($query);
  
  if (!$user) {
    json_response(false, 'اسم المستخدم أو كلمة المرور غير صحيحة');
  }
  
  // Verify password (direct comparison)
  if ($password !== $user['password']) {
    json_response(false, 'اسم المستخدم أو كلمة المرور غير صحيحة');
  }
  
  // Session already started in config.php
  $_SESSION['user_id'] = $user['id'];
  $_SESSION['username'] = $user['username'];
  $_SESSION['full_name'] = $user['full_name'];
  $_SESSION['role'] = $user['role'];
  $_SESSION['logged_in'] = true;
  
  json_response(true, 'تم تسجيل الدخول بنجاح', [
    'user_id' => $user['id'],
    'username' => $user['username'],
    'full_name' => $user['full_name'],
    'role' => $user['role']
  ]);
}

function logout() {
  // Session already started in config.php
  session_destroy();
  json_response(true, 'تم تسجيل الخروج بنجاح');
}

function check_session() {
  // Session already started in config.php
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    json_response(true, 'الجلسة نشطة', [
      'user_id' => $_SESSION['user_id'],
      'username' => $_SESSION['username'],
      'full_name' => $_SESSION['full_name'],
      'role' => $_SESSION['role']
    ]);
  } else {
    json_response(false, 'لا توجد جلسة نشطة');
  }
}

// ============================================
// DASHBOARD FUNCTIONS
// ============================================

function get_dashboard_stats() {
  global $conn;
  
  $stats = [];
  
  // Total products
  $result = $conn->query("SELECT COUNT(*) as count FROM products");
  $row = $result->fetch_assoc();
  $stats['total_products'] = $row['count'] ?? 0;
  
  // Total categories
  $result = $conn->query("SELECT COUNT(*) as count FROM categories");
  $row = $result->fetch_assoc();
  $stats['total_categories'] = $row['count'] ?? 0;
  
  // Total invoices
  $result = $conn->query("SELECT COUNT(*) as count FROM invoices");
  $row = $result->fetch_assoc();
  $stats['total_invoices'] = $row['count'] ?? 0;
  
  // Today's sales
  $today = date('Y-m-d');
  $result = $conn->query("SELECT SUM(total_amount) as total FROM invoices WHERE DATE(created_at) = '$today'");
  $row = $result->fetch_assoc();
  $stats['today_sales'] = $row['total'] ?? 0;
  
  // Total users
  $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE");
  $row = $result->fetch_assoc();
  $stats['total_users'] = $row['count'] ?? 0;
  
  // Low stock products (less than 10)
  $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE COALESCE(stock_strips, stock) < 10");
  $row = $result->fetch_assoc();
  $stats['low_stock_products'] = $row['count'] ?? 0;
  
  json_response(true, 'تم جلب الإحصائيات', $stats);
}

// ============================================
// USER MANAGEMENT FUNCTIONS
// ============================================

function get_users() {
  $query = "SELECT id, username, full_name, email, phone, role, is_active, last_login, created_at 
            FROM users 
            ORDER BY created_at DESC";
  $users = get_all($query);
  json_response(true, 'تم جلب المستخدمين', $users);
}

function add_user() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['username']) || empty($data['full_name']) || empty($data['password'])) {
    json_response(false, 'جميع الحقول المطلوبة يجب ملؤها');
    return;
  }
  
  $username = escape_string($data['username']);
  $full_name = escape_string($data['full_name']);
  $email = isset($data['email']) ? escape_string($data['email']) : NULL;
  $phone = isset($data['phone']) ? escape_string($data['phone']) : NULL;
  $password = escape_string($data['password']); // Plain text as per user requirement
  $role = isset($data['role']) ? escape_string($data['role']) : 'cashier';
  
  // Check if username exists
  $check = "SELECT id FROM users WHERE username = '$username'";
  $result = get_row($check);
  if ($result) {
    json_response(false, 'اسم المستخدم موجود بالفعل');
    return;
  }
  
  $email_sql = $email ? "'$email'" : 'NULL';
  $phone_sql = $phone ? "'$phone'" : 'NULL';
  
  $query = "INSERT INTO users (username, password, full_name, email, phone, role) 
            VALUES ('$username', '$password', '$full_name', $email_sql, $phone_sql, '$role')";
  
  if ($conn->query($query)) {
    json_response(true, 'تم إضافة المستخدم بنجاح', ['id' => $conn->insert_id]);
  } else {
    json_response(false, 'خطأ في إضافة المستخدم: ' . $conn->error);
  }
}

function update_user() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id']) || empty($data['username']) || empty($data['full_name'])) {
    json_response(false, 'جميع الحقول المطلوبة يجب ملؤها');
    return;
  }
  
  $id = intval($data['id']);
  $username = escape_string($data['username']);
  $full_name = escape_string($data['full_name']);
  $email = isset($data['email']) ? escape_string($data['email']) : NULL;
  $phone = isset($data['phone']) ? escape_string($data['phone']) : NULL;
  $role = isset($data['role']) ? escape_string($data['role']) : 'cashier';
  
  // Check if username exists for other user
  $check = "SELECT id FROM users WHERE username = '$username' AND id != $id";
  $result = get_row($check);
  if ($result) {
    json_response(false, 'اسم المستخدم موجود بالفعل');
    return;
  }
  
  $email_sql = $email ? "'$email'" : 'NULL';
  $phone_sql = $phone ? "'$phone'" : 'NULL';
  
  $query = "UPDATE users 
            SET username = '$username', 
                full_name = '$full_name', 
                email = $email_sql, 
                phone = $phone_sql, 
                role = '$role'";
  
  // Update password only if provided
  if (!empty($data['password'])) {
    $password = escape_string($data['password']);
    $query .= ", password = '$password'";
  }
  
  $query .= " WHERE id = $id";
  
  if ($conn->query($query)) {
    json_response(true, 'تم تحديث المستخدم بنجاح');
  } else {
    json_response(false, 'خطأ في تحديث المستخدم: ' . $conn->error);
  }
}

function toggle_user_status() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id'])) {
    json_response(false, 'معرف المستخدم مطلوب');
    return;
  }
  
  $id = intval($data['id']);
  $is_active = isset($data['is_active']) ? intval($data['is_active']) : 0;
  
  $query = "UPDATE users SET is_active = $is_active WHERE id = $id";
  
  if ($conn->query($query)) {
    json_response(true, 'تم تحديث حالة المستخدم بنجاح');
  } else {
    json_response(false, 'خطأ في تحديث الحالة: ' . $conn->error);
  }
}

// ============================================
// SETTINGS FUNCTIONS
// ============================================

function get_settings() {
  $query = "SELECT setting_key, setting_value FROM pharmacy_settings";
  $results = get_all($query);
  
  $settings = [];
  foreach ($results as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
  }
  
  json_response(true, 'تم جلب الإعدادات', $settings);
}

function update_settings() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data)) {
    json_response(false, 'لا توجد بيانات للتحديث');
    return;
  }
  
  $success_count = 0;
  $errors = [];
  
  foreach ($data as $key => $value) {
    $key = escape_string($key);
    $value = escape_string($value);
    
    $query = "INSERT INTO pharmacy_settings (setting_key, setting_value) 
              VALUES ('$key', '$value')
              ON DUPLICATE KEY UPDATE setting_value = '$value'";
    
    if ($conn->query($query)) {
      $success_count++;
    } else {
      $errors[] = "Error updating $key: " . $conn->error;
    }
  }
  
  if (count($errors) > 0) {
    json_response(false, 'حدثت أخطاء: ' . implode(', ', $errors));
  } else {
    json_response(true, "تم حفظ الإعدادات بنجاح ($success_count إعداد)");
  }
}

function get_alerts() {
  global $conn;
  
  $alerts = [];
  
  // Get low stock products (using stock_strips or stock) - المنتجات النشطة فقط
  $low_stock_query = "SELECT p.*, c.name as category_name,
                      COALESCE(p.stock_strips, p.stock) as current_stock
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE COALESCE(p.stock_strips, p.stock) <= COALESCE(p.min_stock, 10) 
                      AND COALESCE(p.stock_strips, p.stock) > 0
                      AND (p.is_active = TRUE OR p.is_active IS NULL)
                      ORDER BY current_stock ASC 
                      LIMIT 20";
  $low_stock = get_all($low_stock_query);
  
  // Get out of stock products - المنتجات النشطة فقط
  $out_stock_query = "SELECT p.*, c.name as category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE COALESCE(p.stock_strips, p.stock) = 0
                      AND (p.is_active = TRUE OR p.is_active IS NULL)
                      ORDER BY p.name ASC 
                      LIMIT 20";
  $out_stock = get_all($out_stock_query);
  
  // Get expired products - المنتجات النشطة فقط
  $expired_query = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.expiry_date IS NOT NULL AND p.expiry_date < CURDATE()
                    AND (p.is_active = TRUE OR p.is_active IS NULL)
                    ORDER BY p.expiry_date ASC 
                    LIMIT 20";
  $expired = get_all($expired_query);
  
  // Get expiring soon (within 30 days) - المنتجات النشطة فقط
  $expiring_query = "SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.expiry_date IS NOT NULL 
                     AND p.expiry_date >= CURDATE() 
                     AND p.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                     AND (p.is_active = TRUE OR p.is_active IS NULL)
                     ORDER BY p.expiry_date ASC 
                     LIMIT 20";
  $expiring = get_all($expiring_query);
  
  json_response(true, 'تم جلب التنبيهات', [
    'low_stock' => $low_stock,
    'out_of_stock' => $out_stock,
    'expired' => $expired,
    'expiring_soon' => $expiring
  ]);
}

// ============================================
// BACKUP FUNCTIONS
// ============================================

function export_database() {
  global $conn;
  
  // تحقق من صلاحيات المستخدم
  if (!isset($_SESSION['user_id'])) {
    json_response(false, 'يجب تسجيل الدخول أولاً');
    return;
  }
  
  $dbName = DB_NAME;
  $tables = [];
  $backup = "-- =============================================\n";
  $backup .= "-- نسخة احتياطية لقاعدة البيانات: $dbName\n";
  $backup .= "-- تاريخ التصدير: " . date('Y-m-d H:i:s') . "\n";
  $backup .= "-- =============================================\n\n";
  
  $backup .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
  
  // جلب جميع الجداول
  $result = $conn->query("SHOW TABLES");
  while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
  }
  
  // تصدير كل جدول
  foreach ($tables as $table) {
    $backup .= "-- =============================================\n";
    $backup .= "-- جدول: $table\n";
    $backup .= "-- =============================================\n\n";
    
    // حذف الجدول إذا كان موجوداً
    $backup .= "DROP TABLE IF EXISTS `$table`;\n";
    
    // جلب بنية الجدول
    $createTable = $conn->query("SHOW CREATE TABLE `$table`");
    $tableInfo = $createTable->fetch_row();
    $backup .= $tableInfo[1] . ";\n\n";
    
    // جلب البيانات
    $data = $conn->query("SELECT * FROM `$table`");
    $numRows = $data->num_rows;
    
    if ($numRows > 0) {
      $backup .= "-- بيانات الجدول: $table ($numRows صف)\n";
      
      while ($row = $data->fetch_assoc()) {
        $columns = array_keys($row);
        $values = array_map(function($val) use ($conn) {
          if ($val === null) {
            return 'NULL';
          }
          return "'" . $conn->real_escape_string($val) . "'";
        }, array_values($row));
        
        $backup .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
      }
      $backup .= "\n";
    }
  }
  
  $backup .= "SET FOREIGN_KEY_CHECKS = 1;\n";
  $backup .= "\n-- انتهت النسخة الاحتياطية\n";
  
  json_response(true, 'تم تصدير قاعدة البيانات بنجاح', [
    'database_name' => $dbName,
    'export_date' => date('Y-m-d H:i:s'),
    'tables_count' => count($tables),
    'sql_content' => $backup
  ]);
}

function import_database() {
  global $conn;
  
  // تحقق من صلاحيات المستخدم
  if (!isset($_SESSION['user_id'])) {
    json_response(false, 'يجب تسجيل الدخول أولاً');
    return;
  }
  
  $input = json_decode(file_get_contents('php://input'), true);
  
  if (!isset($input['sql_content']) || empty($input['sql_content'])) {
    json_response(false, 'محتوى SQL مطلوب');
    return;
  }
  
  $sql = $input['sql_content'];
  
  // تعطيل فحص المفاتيح الأجنبية مؤقتاً
  $conn->query("SET FOREIGN_KEY_CHECKS = 0");
  
  // تقسيم الـ SQL إلى أوامر منفصلة
  $statements = [];
  $currentStatement = '';
  $lines = explode("\n", $sql);
  
  foreach ($lines as $line) {
    // تجاهل التعليقات والأسطر الفارغة
    $trimmedLine = trim($line);
    if (empty($trimmedLine) || strpos($trimmedLine, '--') === 0) {
      continue;
    }
    
    $currentStatement .= $line . "\n";
    
    // إذا انتهى السطر بفاصلة منقوطة، فهذا نهاية الأمر
    if (substr(rtrim($line), -1) === ';') {
      $statements[] = trim($currentStatement);
      $currentStatement = '';
    }
  }
  
  // تنفيذ الأوامر
  $successCount = 0;
  $errorCount = 0;
  $errors = [];
  
  foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    
    // تجاهل أوامر SET
    if (stripos($statement, 'SET FOREIGN_KEY_CHECKS') !== false) {
      continue;
    }
    
    if ($conn->query($statement)) {
      $successCount++;
    } else {
      $errorCount++;
      $errors[] = $conn->error . ' في: ' . substr($statement, 0, 100) . '...';
    }
  }
  
  // إعادة تفعيل فحص المفاتيح الأجنبية
  $conn->query("SET FOREIGN_KEY_CHECKS = 1");
  
  if ($errorCount > 0) {
    json_response(false, "تم تنفيذ $successCount أمر بنجاح، فشل $errorCount أمر", [
      'success_count' => $successCount,
      'error_count' => $errorCount,
      'errors' => array_slice($errors, 0, 5) // أول 5 أخطاء فقط
    ]);
  } else {
    json_response(true, "تم استيراد قاعدة البيانات بنجاح! تم تنفيذ $successCount أمر", [
      'success_count' => $successCount
    ]);
  }
}

// ============================================
// SUPPLIERS FUNCTIONS (وظائف الموردين)
// ============================================

function get_suppliers() {
  global $conn;
  
  $active_only = isset($_GET['active']) ? $_GET['active'] === '1' : false;
  $search = isset($_GET['search']) ? escape_string($_GET['search']) : '';
  
  $where = [];
  if ($active_only) {
    $where[] = "is_active = TRUE";
  }
  if (!empty($search)) {
    $where[] = "(name LIKE '%$search%' OR contact_person LIKE '%$search%' OR phone LIKE '%$search%')";
  }
  
  $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
  
  $query = "SELECT * FROM suppliers $whereClause ORDER BY name";
  $result = get_all($query);
  json_response(true, 'تم جلب الموردين', $result);
}

function get_supplier() {
  global $conn;
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  
  if ($id <= 0) {
    json_response(false, 'معرّف المورد غير صحيح');
    return;
  }
  
  $query = "SELECT * FROM suppliers WHERE id = $id";
  $result = get_row($query);
  
  if (!$result) {
    json_response(false, 'المورد غير موجود');
    return;
  }
  
  json_response(true, 'تم جلب المورد', $result);
}

function add_supplier() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['name'])) {
    json_response(false, 'اسم المورد مطلوب');
    return;
  }
  
  $name = escape_string($data['name']);
  $contact_person = escape_string($data['contact_person'] ?? '');
  $phone = escape_string($data['phone'] ?? '');
  $phone2 = escape_string($data['phone2'] ?? '');
  $email = escape_string($data['email'] ?? '');
  $address = escape_string($data['address'] ?? '');
  $city = escape_string($data['city'] ?? '');
  $tax_number = escape_string($data['tax_number'] ?? '');
  $notes = escape_string($data['notes'] ?? '');
  
  $query = "INSERT INTO suppliers (name, contact_person, phone, phone2, email, address, city, tax_number, notes) 
            VALUES ('$name', '$contact_person', '$phone', '$phone2', '$email', '$address', '$city', '$tax_number', '$notes')";
  
  if ($conn->query($query)) {
    json_response(true, 'تم إضافة المورد بنجاح', ['id' => $conn->insert_id]);
  } else {
    json_response(false, 'خطأ في إضافة المورد: ' . $conn->error);
  }
}

function update_supplier() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id']) || empty($data['name'])) {
    json_response(false, 'معرّف واسم المورد مطلوبان');
    return;
  }
  
  $id = intval($data['id']);
  $name = escape_string($data['name']);
  $contact_person = escape_string($data['contact_person'] ?? '');
  $phone = escape_string($data['phone'] ?? '');
  $phone2 = escape_string($data['phone2'] ?? '');
  $email = escape_string($data['email'] ?? '');
  $address = escape_string($data['address'] ?? '');
  $city = escape_string($data['city'] ?? '');
  $tax_number = escape_string($data['tax_number'] ?? '');
  $notes = escape_string($data['notes'] ?? '');
  $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;

  $query = "UPDATE suppliers SET 
            name = '$name', 
            contact_person = '$contact_person', 
            phone = '$phone', 
            phone2 = '$phone2',
            email = '$email', 
            address = '$address', 
            city = '$city',
            tax_number = '$tax_number',
            notes = '$notes',
            is_active = $is_active
            WHERE id = $id";

  if ($conn->query($query)) {
    json_response(true, 'تم تحديث المورد بنجاح');
  } else {
    json_response(false, 'خطأ في تحديث المورد: ' . $conn->error);
  }
}

function delete_supplier() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id'])) {
    json_response(false, 'معرّف المورد مطلوب');
    return;
  }
  
  $id = intval($data['id']);
  
  // التحقق من عدم وجود مشتريات مرتبطة
  $check = get_row("SELECT COUNT(*) as cnt FROM purchases WHERE supplier_id = $id");
  if ($check && $check['cnt'] > 0) {
    json_response(false, 'لا يمكن حذف المورد لوجود مشتريات مرتبطة به');
    return;
  }
  
  if ($conn->query("DELETE FROM suppliers WHERE id = $id")) {
    json_response(true, 'تم حذف المورد بنجاح');
  } else {
    json_response(false, 'خطأ في حذف المورد: ' . $conn->error);
  }
}

function toggle_supplier_status() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id'])) {
    json_response(false, 'معرّف المورد مطلوب');
    return;
  }
  
  $id = intval($data['id']);
  
  if ($conn->query("UPDATE suppliers SET is_active = NOT is_active WHERE id = $id")) {
    json_response(true, 'تم تغيير حالة المورد');
  } else {
    json_response(false, 'خطأ: ' . $conn->error);
  }
}

// ============================================
// PURCHASES FUNCTIONS (وظائف المشتريات)
// ============================================

function get_purchases() {
  global $conn;
  
  $supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;
  $status = isset($_GET['status']) ? escape_string($_GET['status']) : '';
  $from_date = isset($_GET['from_date']) ? escape_string($_GET['from_date']) : '';
  $to_date = isset($_GET['to_date']) ? escape_string($_GET['to_date']) : '';
  
  $where = [];
  if ($supplier_id > 0) {
    $where[] = "p.supplier_id = $supplier_id";
  }
  if (!empty($status)) {
    $where[] = "p.status = '$status'";
  }
  if (!empty($from_date)) {
    $where[] = "p.purchase_date >= '$from_date'";
  }
  if (!empty($to_date)) {
    $where[] = "p.purchase_date <= '$to_date'";
  }
  
  $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
  
  $query = "SELECT p.*, s.name as supplier_name 
            FROM purchases p 
            LEFT JOIN suppliers s ON p.supplier_id = s.id 
            $whereClause 
            ORDER BY p.created_at DESC";
  
  $result = get_all($query);
  json_response(true, 'تم جلب المشتريات', $result);
}

function get_purchase() {
  global $conn;
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  
  if ($id <= 0) {
    json_response(false, 'معرّف فاتورة الشراء غير صحيح');
    return;
  }
  
  $query = "SELECT p.*, s.name as supplier_name, s.phone as supplier_phone 
            FROM purchases p 
            LEFT JOIN suppliers s ON p.supplier_id = s.id 
            WHERE p.id = $id";
  $purchase = get_row($query);
  
  if (!$purchase) {
    json_response(false, 'فاتورة الشراء غير موجودة');
    return;
  }
  
  // جلب عناصر الفاتورة
  $items_query = "SELECT pi.*, pr.name as product_name, pr.barcode 
                  FROM purchase_items pi 
                  LEFT JOIN products pr ON pi.product_id = pr.id 
                  WHERE pi.purchase_id = $id";
  $items = get_all($items_query);
  
  $purchase['items'] = $items;
  
  json_response(true, 'تم جلب فاتورة الشراء', $purchase);
}

function create_purchase() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['supplier_id']) || empty($data['items']) || count($data['items']) == 0) {
    json_response(false, 'بيانات فاتورة الشراء غير كاملة');
    return;
  }
  
  $supplier_id = intval($data['supplier_id']);
  $purchase_date = escape_string($data['purchase_date'] ?? date('Y-m-d'));
  $due_date = !empty($data['due_date']) ? "'" . escape_string($data['due_date']) . "'" : 'NULL';
  $discount = floatval($data['discount_amount'] ?? 0);
  $tax = floatval($data['tax_amount'] ?? 0);
  $notes = escape_string($data['notes'] ?? '');
  $paid = floatval($data['paid_amount'] ?? 0);
  $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'NULL';
  
  // إنشاء رقم فاتورة الشراء
  $year = date('Y');
  $month = date('m');
  $count_result = get_row("SELECT COUNT(*) as cnt FROM purchases WHERE YEAR(created_at) = $year");
  $count = ($count_result['cnt'] ?? 0) + 1;
  $purchase_number = "PUR-$year$month-" . str_pad($count, 4, '0', STR_PAD_LEFT);
  
  // حساب المجموع
  $subtotal = 0;
  foreach ($data['items'] as $item) {
    $subtotal += floatval($item['total_price']);
  }
  $total = $subtotal - $discount + $tax;
  $remaining = $total - $paid;
  
  // تحديد حالة الدفع
  $payment_status = 'unpaid';
  if ($paid >= $total) {
    $payment_status = 'paid';
  } elseif ($paid > 0) {
    $payment_status = 'partial';
  }
  
  // بدء المعاملة
  $conn->begin_transaction();
  
  try {
    // إنشاء فاتورة الشراء
    $query = "INSERT INTO purchases (purchase_number, supplier_id, user_id, purchase_date, due_date, 
              subtotal, discount_amount, tax_amount, total_amount, paid_amount, remaining_amount, 
              status, payment_status, notes) 
              VALUES ('$purchase_number', $supplier_id, $user_id, '$purchase_date', $due_date, 
              $subtotal, $discount, $tax, $total, $paid, $remaining, 'completed', '$payment_status', '$notes')";
    
    if (!$conn->query($query)) {
      throw new Exception('خطأ في إنشاء فاتورة الشراء: ' . $conn->error);
    }
    
    $purchase_id = $conn->insert_id;
    
    // إضافة عناصر الفاتورة وتحديث المخزون
    foreach ($data['items'] as $item) {
      $product_id = intval($item['product_id']);
      $quantity = intval($item['quantity']);
      $unit = escape_string($item['unit'] ?? 'box');
      $unit_quantity = intval($item['unit_quantity'] ?? $quantity);
      $base_quantity = intval($item['base_quantity'] ?? $quantity);
      $purchase_price = floatval($item['purchase_price']);
      $selling_price = isset($item['selling_price']) ? floatval($item['selling_price']) : 'NULL';
      $total_price = floatval($item['total_price']);
      $expiry_date = !empty($item['expiry_date']) ? "'" . escape_string($item['expiry_date']) . "'" : 'NULL';
      $batch_number = escape_string($item['batch_number'] ?? '');
      
      $item_query = "INSERT INTO purchase_items (purchase_id, product_id, quantity, unit, unit_quantity, 
                     base_quantity, purchase_price, selling_price, total_price, expiry_date, batch_number) 
                     VALUES ($purchase_id, $product_id, $quantity, '$unit', $unit_quantity, 
                     $base_quantity, $purchase_price, $selling_price, $total_price, $expiry_date, '$batch_number')";
      
      if (!$conn->query($item_query)) {
        throw new Exception('خطأ في إضافة عنصر: ' . $conn->error);
      }
      
      // تحديث المخزون (إضافة الكمية)
      $stock_update = "UPDATE products SET stock_strips = COALESCE(stock_strips, 0) + $base_quantity WHERE id = $product_id";
      if (!$conn->query($stock_update)) {
        throw new Exception('خطأ في تحديث المخزون: ' . $conn->error);
      }
      
      // تسجيل في سجل المخزون
      $history_query = "INSERT INTO stock_history (product_id, quantity_change, operation_type, notes) 
                        VALUES ($product_id, $base_quantity, 'purchase', 'شراء - فاتورة رقم $purchase_number')";
      $conn->query($history_query);
      
      // تحديث سعر البيع إذا تم تحديده
      if (isset($item['selling_price']) && $item['selling_price'] > 0) {
        $conn->query("UPDATE products SET price_strip = " . floatval($item['selling_price']) . " WHERE id = $product_id");
      }
    }
    
    // تحديث رصيد المورد
    $update_supplier = "UPDATE suppliers SET 
                        total_purchases = total_purchases + $total,
                        total_paid = total_paid + $paid,
                        balance = balance + $remaining
                        WHERE id = $supplier_id";
    $conn->query($update_supplier);
    
    // إذا تم الدفع، سجل الدفعة
    if ($paid > 0) {
      $payment_query = "INSERT INTO supplier_payments (supplier_id, purchase_id, amount, payment_method, payment_date, user_id) 
                        VALUES ($supplier_id, $purchase_id, $paid, 'cash', '$purchase_date', $user_id)";
      $conn->query($payment_query);
    }
    
    $conn->commit();
    
    json_response(true, 'تم إنشاء فاتورة الشراء بنجاح', [
      'id' => $purchase_id,
      'purchase_number' => $purchase_number
    ]);
    
  } catch (Exception $e) {
    $conn->rollback();
    json_response(false, $e->getMessage());
  }
}

function update_purchase() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id'])) {
    json_response(false, 'معرّف الفاتورة مطلوب');
    return;
  }
  
  $id = intval($data['id']);
  $notes = escape_string($data['notes'] ?? '');
  $due_date = !empty($data['due_date']) ? "'" . escape_string($data['due_date']) . "'" : 'NULL';
  
  $query = "UPDATE purchases SET notes = '$notes', due_date = $due_date WHERE id = $id";
  
  if ($conn->query($query)) {
    json_response(true, 'تم تحديث الفاتورة');
  } else {
    json_response(false, 'خطأ: ' . $conn->error);
  }
}

function delete_purchase() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['id'])) {
    json_response(false, 'معرّف الفاتورة مطلوب');
    return;
  }
  
  $id = intval($data['id']);
  
  // جلب بيانات الفاتورة
  $purchase = get_row("SELECT * FROM purchases WHERE id = $id");
  if (!$purchase) {
    json_response(false, 'الفاتورة غير موجودة');
    return;
  }
  
  $conn->begin_transaction();
  
  try {
    // استرجاع المخزون
    $items = get_all("SELECT * FROM purchase_items WHERE purchase_id = $id");
    foreach ($items as $item) {
      $conn->query("UPDATE products SET stock_strips = stock_strips - {$item['base_quantity']} WHERE id = {$item['product_id']}");
      $conn->query("INSERT INTO stock_history (product_id, quantity_change, operation_type, notes) 
                    VALUES ({$item['product_id']}, -{$item['base_quantity']}, 'purchase_delete', 'حذف فاتورة شراء')");
    }
    
    // تحديث رصيد المورد
    $conn->query("UPDATE suppliers SET 
                  total_purchases = total_purchases - {$purchase['total_amount']},
                  total_paid = total_paid - {$purchase['paid_amount']},
                  balance = balance - {$purchase['remaining_amount']}
                  WHERE id = {$purchase['supplier_id']}");
    
    // حذف المدفوعات
    $conn->query("DELETE FROM supplier_payments WHERE purchase_id = $id");
    
    // حذف الفاتورة (سيحذف العناصر تلقائياً بسبب CASCADE)
    $conn->query("DELETE FROM purchases WHERE id = $id");
    
    $conn->commit();
    json_response(true, 'تم حذف فاتورة الشراء');
    
  } catch (Exception $e) {
    $conn->rollback();
    json_response(false, $e->getMessage());
  }
}

function get_purchase_items() {
  global $conn;
  $purchase_id = isset($_GET['purchase_id']) ? intval($_GET['purchase_id']) : 0;
  
  if ($purchase_id <= 0) {
    json_response(false, 'معرّف الفاتورة مطلوب');
    return;
  }
  
  $query = "SELECT pi.*, pr.name as product_name, pr.barcode 
            FROM purchase_items pi 
            LEFT JOIN products pr ON pi.product_id = pr.id 
            WHERE pi.purchase_id = $purchase_id";
  
  $result = get_all($query);
  json_response(true, 'تم جلب عناصر الفاتورة', $result);
}

function add_supplier_payment() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (empty($data['supplier_id']) || empty($data['amount'])) {
    json_response(false, 'المورد والمبلغ مطلوبان');
    return;
  }
  
  $supplier_id = intval($data['supplier_id']);
  $purchase_id = !empty($data['purchase_id']) ? intval($data['purchase_id']) : 'NULL';
  $amount = floatval($data['amount']);
  $payment_method = escape_string($data['payment_method'] ?? 'cash');
  $payment_date = escape_string($data['payment_date'] ?? date('Y-m-d'));
  $reference = escape_string($data['reference_number'] ?? '');
  $notes = escape_string($data['notes'] ?? '');
  $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'NULL';
  
  $conn->begin_transaction();
  
  try {
    // إضافة الدفعة
    $query = "INSERT INTO supplier_payments (supplier_id, purchase_id, amount, payment_method, payment_date, reference_number, notes, user_id) 
              VALUES ($supplier_id, $purchase_id, $amount, '$payment_method', '$payment_date', '$reference', '$notes', $user_id)";
    
    if (!$conn->query($query)) {
      throw new Exception('خطأ في تسجيل الدفعة: ' . $conn->error);
    }
    
    // تحديث رصيد المورد
    $conn->query("UPDATE suppliers SET total_paid = total_paid + $amount, balance = balance - $amount WHERE id = $supplier_id");
    
    // إذا كانت الدفعة مرتبطة بفاتورة معينة، حدّثها
    if ($purchase_id !== 'NULL') {
      $conn->query("UPDATE purchases SET 
                    paid_amount = paid_amount + $amount,
                    remaining_amount = remaining_amount - $amount,
                    payment_status = CASE 
                      WHEN remaining_amount - $amount <= 0 THEN 'paid'
                      WHEN paid_amount + $amount > 0 THEN 'partial'
                      ELSE 'unpaid'
                    END
                    WHERE id = $purchase_id");
    }
    
    $conn->commit();
    json_response(true, 'تم تسجيل الدفعة بنجاح');
    
  } catch (Exception $e) {
    $conn->rollback();
    json_response(false, $e->getMessage());
  }
}

function get_supplier_payments() {
  global $conn;

  $supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;

  $where = $supplier_id > 0 ? "WHERE sp.supplier_id = $supplier_id" : "";

  $query = "SELECT sp.*, s.name as supplier_name, p.purchase_number
            FROM supplier_payments sp
            LEFT JOIN suppliers s ON sp.supplier_id = s.id
            LEFT JOIN purchases p ON sp.purchase_id = p.id
            $where
            ORDER BY sp.payment_date DESC, sp.created_at DESC";

  $result = get_all($query);
  json_response(true, 'تم جلب المدفوعات', $result);
}

// ============================================
// PURCHASE PAYMENTS LEDGER (مدفوعات فاتورة بعينها)
// ============================================

function get_purchase_payments() {
  $purchase_id = isset($_GET['purchase_id']) ? intval($_GET['purchase_id']) : 0;

  if ($purchase_id <= 0) {
    json_response(false, 'معرّف الفاتورة مطلوب');
    return;
  }

  // Return all payments linked to this specific purchase invoice,
  // including the user's full name for the payment history table.
  $query = "SELECT sp.*,
                   u.full_name   AS user_name,
                   p.purchase_number
            FROM supplier_payments sp
            LEFT JOIN users     u ON sp.user_id     = u.id
            LEFT JOIN purchases p ON sp.purchase_id = p.id
            WHERE sp.purchase_id = $purchase_id
            ORDER BY sp.payment_date ASC, sp.created_at ASC";

  $result = get_all($query);
  json_response(true, 'تم جلب مدفوعات الفاتورة', $result);
}

// ============================================
// PAYMENT METHODS FUNCTIONS (طرق الدفع) - Feature 5
// ============================================

function get_payment_methods() {
  $activeOnly = isset($_GET['active']) && $_GET['active'] == '1';
  $where = $activeOnly ? 'WHERE is_active = 1' : '';
  $query = "SELECT * FROM payment_methods $where ORDER BY sort_order ASC, name ASC";
  json_response(true, 'تم جلب طرق الدفع', get_all($query));
}

function add_payment_method() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['name'])) {
    json_response(false, 'اسم طريقة الدفع مطلوب');
  }
  $name = escape_string(trim($data['name']));
  $notes = escape_string($data['notes'] ?? '');
  $is_active = !empty($data['is_active']) ? 1 : 0;

  if (get_row("SELECT id FROM payment_methods WHERE name = '$name'")) {
    json_response(false, 'طريقة الدفع موجودة مسبقاً');
  }

  execute_query("INSERT INTO payment_methods (name, notes, is_active) VALUES ('$name', '$notes', $is_active)");
  $id = $conn->insert_id;
  audit_log('add_payment_method', 'payment_method', $id, ['name' => $data['name']]);
  json_response(true, 'تم إضافة طريقة الدفع', ['id' => $id]);
}

function update_payment_method() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['id']) || empty($data['name'])) {
    json_response(false, 'بيانات ناقصة');
  }
  $id = intval($data['id']);
  $name = escape_string(trim($data['name']));
  $notes = escape_string($data['notes'] ?? '');
  $is_active = !empty($data['is_active']) ? 1 : 0;

  $dup = get_row("SELECT id FROM payment_methods WHERE name = '$name' AND id <> $id");
  if ($dup) {
    json_response(false, 'يوجد طريقة دفع أخرى بنفس الاسم');
  }

  execute_query("UPDATE payment_methods SET name = '$name', notes = '$notes', is_active = $is_active WHERE id = $id");
  audit_log('update_payment_method', 'payment_method', $id, ['name' => $data['name']]);
  json_response(true, 'تم تحديث طريقة الدفع');
}

function toggle_payment_method() {
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['id'])) {
    json_response(false, 'معرّف غير صحيح');
  }
  $id = intval($data['id']);
  execute_query("UPDATE payment_methods SET is_active = NOT is_active WHERE id = $id");
  audit_log('toggle_payment_method', 'payment_method', $id);
  json_response(true, 'تم تحديث حالة طريقة الدفع');
}

function delete_payment_method() {
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['id'])) {
    json_response(false, 'معرّف غير صحيح');
  }
  $id = intval($data['id']);

  // الحفاظ على السلامة التاريخية: لا نحذف طرق النظام، ولا نؤثر على الفواتير القديمة
  $pm = get_row("SELECT is_system FROM payment_methods WHERE id = $id");
  if (!$pm) {
    json_response(false, 'طريقة الدفع غير موجودة');
  }
  if (intval($pm['is_system']) === 1) {
    json_response(false, 'لا يمكن حذف طريقة دفع أساسية في النظام، يمكنك تعطيلها بدلاً من ذلك');
  }
  // الفواتير القديمة تحتفظ باسم طريقة الدفع كلقطة نصية، لذا الحذف لا يؤثر عليها.
  execute_query("DELETE FROM payment_methods WHERE id = $id");
  audit_log('delete_payment_method', 'payment_method', $id);
  json_response(true, 'تم حذف طريقة الدفع');
}

// ============================================
// CUSTOMERS FUNCTIONS (العملاء) - Feature 4
// ============================================

function get_customers() {
  $search = isset($_GET['search']) ? escape_string(trim($_GET['search'])) : '';
  $where = "WHERE is_active = 1";
  if ($search !== '') {
    $where .= " AND (name LIKE '%$search%' OR phone LIKE '%$search%')";
  }
  $query = "SELECT * FROM customers $where ORDER BY name ASC LIMIT 200";
  json_response(true, 'تم جلب العملاء', get_all($query));
}

function get_customer() {
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  if ($id <= 0) json_response(false, 'معرّف غير صحيح');
  $customer = get_row("SELECT * FROM customers WHERE id = $id");
  if (!$customer) json_response(false, 'العميل غير موجود');
  json_response(true, 'تم جلب العميل', $customer);
}

function add_customer() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['name'])) {
    json_response(false, 'اسم العميل مطلوب');
  }
  $name = escape_string(trim($data['name']));
  $phone = escape_string($data['phone'] ?? '');
  $email = escape_string($data['email'] ?? '');
  $address = escape_string($data['address'] ?? '');
  $notes = escape_string($data['notes'] ?? '');

  execute_query("INSERT INTO customers (name, phone, email, address, notes)
                 VALUES ('$name', '$phone', '$email', '$address', '$notes')");
  $id = $conn->insert_id;
  audit_log('add_customer', 'customer', $id, ['name' => $data['name']]);
  json_response(true, 'تم إضافة العميل', ['id' => $id]);
}

function update_customer() {
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['id']) || empty($data['name'])) {
    json_response(false, 'بيانات ناقصة');
  }
  $id = intval($data['id']);
  $name = escape_string(trim($data['name']));
  $phone = escape_string($data['phone'] ?? '');
  $email = escape_string($data['email'] ?? '');
  $address = escape_string($data['address'] ?? '');
  $notes = escape_string($data['notes'] ?? '');

  execute_query("UPDATE customers SET name='$name', phone='$phone', email='$email', address='$address', notes='$notes' WHERE id = $id");
  audit_log('update_customer', 'customer', $id);
  json_response(true, 'تم تحديث العميل');
}

function toggle_customer_status() {
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['id'])) json_response(false, 'معرّف غير صحيح');
  $id = intval($data['id']);
  execute_query("UPDATE customers SET is_active = NOT is_active WHERE id = $id");
  audit_log('toggle_customer_status', 'customer', $id);
  json_response(true, 'تم تحديث حالة العميل');
}

/**
 * تقرير أرصدة العملاء: إجمالي المستحقات المتبقية لكل عميل.
 */
function get_customer_balances() {
  $query = "SELECT c.id, c.name, c.phone, c.balance,
                   COALESCE(SUM(i.remaining_amount), 0) AS outstanding,
                   COUNT(CASE WHEN i.remaining_amount > 0 THEN 1 END) AS open_invoices
            FROM customers c
            LEFT JOIN invoices i ON i.customer_id = c.id
            GROUP BY c.id, c.name, c.phone, c.balance
            HAVING outstanding > 0 OR c.balance > 0
            ORDER BY outstanding DESC";
  json_response(true, 'تم جلب أرصدة العملاء', get_all($query));
}

// ============================================
// PARTIAL PAYMENTS FUNCTIONS (الدفع الجزئي) - Feature 4
// ============================================

/**
 * استلام دفعة إضافية على فاتورة آجلة/جزئية، وتحديث الحالة والرصيد ذرّياً.
 */
function add_invoice_payment() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  $invoice_id = intval($data['invoice_id'] ?? 0);
  $amount = round(floatval($data['amount'] ?? 0), 2);
  $payment_method_id = !empty($data['payment_method_id']) ? intval($data['payment_method_id']) : null;
  $notes = escape_string($data['notes'] ?? '');

  if ($invoice_id <= 0 || $amount <= 0) {
    json_response(false, 'بيانات الدفعة غير صحيحة');
  }

  $payment_method_name = 'نقداً';
  if ($payment_method_id) {
    $pm = get_row("SELECT name FROM payment_methods WHERE id = $payment_method_id AND is_active = 1");
    if ($pm) $payment_method_name = $pm['name'];
  }

  try {
    $result = db_transaction(function($conn) use ($invoice_id, $amount, $payment_method_id, $payment_method_name, $notes) {
      $invoice = get_row("SELECT id, remaining_amount, paid_amount, customer_id, status FROM invoices WHERE id = $invoice_id FOR UPDATE");
      if (!$invoice) throw new Exception('الفاتورة غير موجودة');

      $remaining = round(floatval($invoice['remaining_amount']), 2);
      if ($remaining <= 0) throw new Exception('الفاتورة مسددة بالكامل');
      if ($amount > $remaining + 0.001) throw new Exception('المبلغ المدفوع أكبر من المبلغ المتبقي (' . $remaining . ')');

      $new_paid = round(floatval($invoice['paid_amount']) + $amount, 2);
      $new_remaining = round($remaining - $amount, 2);
      $new_status = ($new_remaining <= 0.001) ? 'paid' : 'partial';

      $uid = current_user_id();
      $uid_sql = $uid ? intval($uid) : 'NULL';
      $pm_id_sql = $payment_method_id ? intval($payment_method_id) : 'NULL';
      $pm_name_esc = $conn->real_escape_string($payment_method_name);
      $notes_esc = $conn->real_escape_string($notes);

      tx_query("INSERT INTO invoice_payments (invoice_id, amount, payment_method_id, payment_method_name, user_id, notes)
                VALUES ($invoice_id, $amount, $pm_id_sql, '$pm_name_esc', $uid_sql, '$notes_esc')");

      tx_query("UPDATE invoices SET paid_amount = $new_paid, remaining_amount = $new_remaining, status = '$new_status'
                WHERE id = $invoice_id");

      if (!empty($invoice['customer_id'])) {
        $cid = intval($invoice['customer_id']);
        tx_query("UPDATE customers SET balance = GREATEST(0, balance - $amount) WHERE id = $cid");
      }

      return ['paid_amount' => $new_paid, 'remaining_amount' => $new_remaining, 'status' => $new_status];
    });

    audit_log('add_invoice_payment', 'invoice', $invoice_id, ['amount' => $amount] + $result);
    json_response(true, 'تم تسجيل الدفعة بنجاح', $result);

  } catch (Exception $e) {
    json_response(false, $e->getMessage());
  }
}

function get_invoice_payments() {
  $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
  if ($invoice_id <= 0) json_response(false, 'معرّف الفاتورة مطلوب');
  $query = "SELECT ip.*, u.full_name AS user_name
            FROM invoice_payments ip
            LEFT JOIN users u ON ip.user_id = u.id
            WHERE ip.invoice_id = $invoice_id
            ORDER BY ip.created_at ASC";
  json_response(true, 'تم جلب مدفوعات الفاتورة', get_all($query));
}

/**
 * الفواتير غير المسددة بالكامل (آجلة/جزئية) - مع إمكانية البحث.
 */
function get_outstanding_invoices() {
  $search = isset($_GET['search']) ? escape_string(trim($_GET['search'])) : '';
  $where = "WHERE i.remaining_amount > 0 AND i.status IN ('partial','unpaid')";
  if ($search !== '') {
    $where .= " AND (i.invoice_number LIKE '%$search%' OR i.customer_name LIKE '%$search%' OR c.name LIKE '%$search%')";
  }
  $query = "SELECT i.id, i.invoice_number, i.total_amount, i.paid_amount, i.remaining_amount,
                   i.status, i.created_at, i.customer_name, c.name AS customer_db_name, c.phone AS customer_phone
            FROM invoices i
            LEFT JOIN customers c ON i.customer_id = c.id
            $where
            ORDER BY i.created_at DESC
            LIMIT 200";
  $rows = get_all($query);
  $totals = get_row("SELECT COALESCE(SUM(remaining_amount),0) AS total_outstanding,
                            COUNT(*) AS count
                     FROM invoices
                     WHERE remaining_amount > 0 AND status IN ('partial','unpaid')");
  json_response(true, 'تم جلب الفواتير الآجلة', ['invoices' => $rows, 'totals' => $totals]);
}

// ============================================
// INVOICE RETURNS FUNCTIONS (المرتجعات) - Feature 1
// ============================================

/**
 * البحث عن فاتورة لإجراء مرتجع، مع بنودها والكميات القابلة للإرجاع.
 */
function search_invoice_for_return() {
  $number = isset($_GET['invoice_number']) ? escape_string(trim($_GET['invoice_number'])) : '';
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

  if ($number === '' && $id <= 0) {
    json_response(false, 'أدخل رقم الفاتورة');
  }

  $cond = $id > 0 ? "i.id = $id" : "i.invoice_number = '$number'";
  $invoice = get_row("SELECT i.*, c.name AS customer_db_name, pm.name AS payment_method_name_db,
                             u.full_name AS cashier_name, ic.name AS insurance_name
                      FROM invoices i
                      LEFT JOIN customers c ON i.customer_id = c.id
                      LEFT JOIN payment_methods pm ON i.payment_method_id = pm.id
                      LEFT JOIN users u ON i.cashier_id = u.id
                      LEFT JOIN insurance_companies ic ON i.insurance_company_id = ic.id
                      WHERE $cond");
  if (!$invoice) {
    json_response(false, 'الفاتورة غير موجودة');
  }

  $invoice_id = intval($invoice['id']);
  $items = get_all("SELECT ii.*, p.name AS product_name,
                           (ii.quantity - ii.returned_qty) AS returnable_qty
                    FROM invoice_items ii
                    JOIN products p ON ii.product_id = p.id
                    WHERE ii.invoice_id = $invoice_id");

  $invoice['items'] = $items;
  json_response(true, 'تم العثور على الفاتورة', $invoice);
}

/**
 * إرجاع منتج محدّد (أو جزء من كميته) من فاتورة - ذرّياً.
 */
function return_invoice_item() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  $invoice_item_id = intval($data['invoice_item_id'] ?? 0);
  $return_qty_units = intval($data['quantity'] ?? 0); // عدد الوحدات (بوحدة البيع) المراد إرجاعها
  $reason = escape_string($data['reason'] ?? '');

  if ($invoice_item_id <= 0 || $return_qty_units <= 0) {
    json_response(false, 'بيانات المرتجع غير صحيحة');
  }

  try {
    $result = db_transaction(function($conn) use ($invoice_item_id, $return_qty_units, $reason) {
      $item = get_row("SELECT * FROM invoice_items WHERE id = $invoice_item_id FOR UPDATE");
      if (!$item) throw new Exception('بند الفاتورة غير موجود');

      $invoice_id = intval($item['invoice_id']);
      $sold_units = intval($item['unit_quantity']);
      $already_returned = intval($item['returned_qty']);
      $remaining_units = $sold_units - $already_returned;

      if ($return_qty_units > $remaining_units) {
        throw new Exception('الكمية المطلوب إرجاعها تتجاوز الكمية المتاحة للإرجاع');
      }

      $unit_size = $sold_units > 0 ? intval($item['base_quantity']) / $sold_units : 1;
      $base_return = intval(round($return_qty_units * $unit_size));
      $unit_price = floatval($item['unit_price']);
      $refund = round($unit_price * $return_qty_units, 2);
      $unit = $conn->real_escape_string($item['unit']);
      $product_id = intval($item['product_id']);

      $invoice = get_row("SELECT * FROM invoices WHERE id = $invoice_id FOR UPDATE");
      if (!$invoice) throw new Exception('الفاتورة غير موجودة');

      // 1) إنشاء رأس المرتجع
      $return_number = 'RET-' . date('YmdHis') . '-' . mt_rand(100, 999);
      $rn_esc = $conn->real_escape_string($return_number);
      $reason_esc = $conn->real_escape_string($reason);
      $uid = current_user_id();
      $uid_sql = $uid ? intval($uid) : 'NULL';

      tx_query("INSERT INTO invoice_returns (return_number, invoice_id, return_type, total_refund, reason, user_id)
                VALUES ('$rn_esc', $invoice_id, 'partial', $refund, '$reason_esc', $uid_sql)");
      $return_id = $conn->insert_id;

      tx_query("INSERT INTO invoice_return_items
                 (return_id, invoice_item_id, product_id, quantity, unit, unit_quantity, base_quantity, unit_price, amount)
                VALUES ($return_id, $invoice_item_id, $product_id, $base_return, '$unit', $return_qty_units, $base_return, $unit_price, $refund)");

      // 2) تحديث الكمية المرتجعة للبند
      $new_returned = $already_returned + $return_qty_units;
      tx_query("UPDATE invoice_items SET returned_qty = $new_returned WHERE id = $invoice_item_id");

      // 3) إعادة الكمية للمخزون + سجل المخزون
      tx_query("UPDATE products SET stock = stock + $base_return,
                       stock_strips = IFNULL(stock_strips, stock) + $base_return WHERE id = $product_id");
      tx_query("INSERT INTO stock_history (product_id, quantity_change, operation_type, notes)
                VALUES ($product_id, $base_return, 'return', 'مرتجع - $rn_esc')");

      // 4) عكس قيمة المبيعات (سجل سالب في جدول sales)
      tx_query("INSERT INTO sales (invoice_id, product_id, quantity, unit, unit_quantity, base_quantity, amount, sales_date)
                VALUES ($invoice_id, $product_id, -$base_return, '$unit', -$return_qty_units, -$base_return, -$refund, CURDATE())");

      // 5) تعديل إجماليات الفاتورة
      adjust_invoice_after_return($conn, $invoice, $refund);

      return ['return_id' => $return_id, 'return_number' => $return_number, 'refund' => $refund];
    });

    audit_log('return_invoice_item', 'invoice_item', $invoice_item_id, $result);
    json_response(true, 'تم إرجاع المنتج بنجاح', $result);

  } catch (Exception $e) {
    json_response(false, $e->getMessage());
  }
}

/**
 * إرجاع الفاتورة بالكامل (كل الكميات المتبقية غير المرتجعة) - ذرّياً.
 */
function return_full_invoice() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  $invoice_id = intval($data['invoice_id'] ?? 0);
  $reason = escape_string($data['reason'] ?? '');
  if ($invoice_id <= 0) json_response(false, 'معرّف الفاتورة غير صحيح');

  try {
    $result = db_transaction(function($conn) use ($invoice_id, $reason) {
      $invoice = get_row("SELECT * FROM invoices WHERE id = $invoice_id FOR UPDATE");
      if (!$invoice) throw new Exception('الفاتورة غير موجودة');
      if ($invoice['status'] === 'returned') throw new Exception('الفاتورة مرتجعة بالكامل مسبقاً');

      $items = get_all("SELECT * FROM invoice_items WHERE invoice_id = $invoice_id");
      $total_refund = 0;
      $any = false;

      $return_number = 'RET-' . date('YmdHis') . '-' . mt_rand(100, 999);
      $rn_esc = $conn->real_escape_string($return_number);
      $reason_esc = $conn->real_escape_string($reason);
      $uid = current_user_id();
      $uid_sql = $uid ? intval($uid) : 'NULL';

      tx_query("INSERT INTO invoice_returns (return_number, invoice_id, return_type, total_refund, reason, user_id)
                VALUES ('$rn_esc', $invoice_id, 'full', 0, '$reason_esc', $uid_sql)");
      $return_id = $conn->insert_id;

      foreach ($items as $item) {
        $sold_units = intval($item['unit_quantity']);
        $already = intval($item['returned_qty']);
        $remaining_units = $sold_units - $already;
        if ($remaining_units <= 0) continue;
        $any = true;

        $unit_size = $sold_units > 0 ? intval($item['base_quantity']) / $sold_units : 1;
        $base_return = intval(round($remaining_units * $unit_size));
        $unit_price = floatval($item['unit_price']);
        $refund = round($unit_price * $remaining_units, 2);
        $unit = $conn->real_escape_string($item['unit']);
        $product_id = intval($item['product_id']);
        $item_id = intval($item['id']);

        tx_query("INSERT INTO invoice_return_items
                   (return_id, invoice_item_id, product_id, quantity, unit, unit_quantity, base_quantity, unit_price, amount)
                  VALUES ($return_id, $item_id, $product_id, $base_return, '$unit', $remaining_units, $base_return, $unit_price, $refund)");

        tx_query("UPDATE invoice_items SET returned_qty = $sold_units WHERE id = $item_id");

        tx_query("UPDATE products SET stock = stock + $base_return,
                         stock_strips = IFNULL(stock_strips, stock) + $base_return WHERE id = $product_id");
        tx_query("INSERT INTO stock_history (product_id, quantity_change, operation_type, notes)
                  VALUES ($product_id, $base_return, 'return', 'مرتجع كامل - $rn_esc')");

        tx_query("INSERT INTO sales (invoice_id, product_id, quantity, unit, unit_quantity, base_quantity, amount, sales_date)
                  VALUES ($invoice_id, $product_id, -$base_return, '$unit', -$remaining_units, -$base_return, -$refund, CURDATE())");

        $total_refund += $refund;
      }

      if (!$any) throw new Exception('لا توجد كميات قابلة للإرجاع في هذه الفاتورة');
      $total_refund = round($total_refund, 2);

      tx_query("UPDATE invoice_returns SET total_refund = $total_refund WHERE id = $return_id");

      // وضع علامة على الفاتورة كمرتجعة بالكامل
      tx_query("UPDATE invoices
                   SET status = 'returned',
                       return_date = NOW(),
                       returned_by = $uid_sql,
                       remaining_amount = 0
                 WHERE id = $invoice_id");

      // تسوية رصيد العميل إن كان هناك مبلغ آجل
      if (!empty($invoice['customer_id'])) {
        $cid = intval($invoice['customer_id']);
        $prev_remaining = round(floatval($invoice['remaining_amount']), 2);
        if ($prev_remaining > 0) {
          tx_query("UPDATE customers SET balance = GREATEST(0, balance - $prev_remaining) WHERE id = $cid");
        }
      }

      return ['return_id' => $return_id, 'return_number' => $return_number, 'total_refund' => $total_refund];
    });

    audit_log('return_full_invoice', 'invoice', $invoice_id, $result);
    json_response(true, 'تم إرجاع الفاتورة بالكامل', $result);

  } catch (Exception $e) {
    json_response(false, $e->getMessage());
  }
}

/**
 * تعديل إجماليات الفاتورة بعد إرجاع جزئي.
 * يخفّض الإجمالي والمتبقي/المدفوع ويضبط الحالة، ويُسوّي رصيد العميل.
 */
function adjust_invoice_after_return($conn, $invoice, $refund) {
  $invoice_id = intval($invoice['id']);
  $new_total = round(floatval($invoice['total_amount']) - $refund, 2);
  if ($new_total < 0) $new_total = 0;

  $remaining = round(floatval($invoice['remaining_amount']), 2);
  $paid = round(floatval($invoice['paid_amount']), 2);

  // نخصم المبلغ المرتجع أولاً من المتبقي على العميل، ثم من المدفوع
  $reduce_from_remaining = min($remaining, $refund);
  $new_remaining = round($remaining - $reduce_from_remaining, 2);
  $leftover = round($refund - $reduce_from_remaining, 2);
  $new_paid = round(max(0, $paid - $leftover), 2);

  // تسوية رصيد العميل بمقدار ما خُصم من المتبقي
  if (!empty($invoice['customer_id']) && $reduce_from_remaining > 0) {
    $cid = intval($invoice['customer_id']);
    tx_query("UPDATE customers SET balance = GREATEST(0, balance - $reduce_from_remaining) WHERE id = $cid");
  }

  // تحديد الحالة: مرتجع جزئي إذا بقيت بنود، وإلا مرتجع كامل
  $remaining_items = get_row("SELECT COALESCE(SUM(quantity - returned_qty),0) AS units
                              FROM invoice_items WHERE invoice_id = $invoice_id");
  $units_left = intval($remaining_items['units']);

  if ($units_left <= 0) {
    $status = 'returned';
  } else {
    $status = 'partially_returned';
  }

  tx_query("UPDATE invoices
               SET total_amount = $new_total,
                   remaining_amount = $new_remaining,
                   paid_amount = $new_paid,
                   status = '$status'
             WHERE id = $invoice_id");
}

/**
 * تقرير الفواتير المرتجعة.
 */
function get_returns_report() {
  $from = isset($_GET['from']) ? escape_string($_GET['from']) : date('Y-m-01');
  $to = isset($_GET['to']) ? escape_string($_GET['to']) : date('Y-m-d');
  $type = isset($_GET['type']) ? escape_string($_GET['type']) : '';

  $where = "WHERE DATE(r.created_at) BETWEEN '$from' AND '$to'";
  if ($type === 'full' || $type === 'partial') {
    $where .= " AND r.return_type = '$type'";
  }

  $rows = get_all("SELECT r.id, r.return_number, r.return_type, r.total_refund, r.reason, r.created_at,
                          i.invoice_number, i.customer_name, u.full_name AS user_name
                   FROM invoice_returns r
                   JOIN invoices i ON r.invoice_id = i.id
                   LEFT JOIN users u ON r.user_id = u.id
                   $where
                   ORDER BY r.created_at DESC");

  $totals = get_row("SELECT COUNT(*) AS count, COALESCE(SUM(total_refund),0) AS total_refund
                     FROM invoice_returns r $where");

  json_response(true, 'تم جلب تقرير المرتجعات', ['returns' => $rows, 'totals' => $totals]);
}

/**
 * تقرير المنتجات المرتجعة.
 */
function get_returned_products_report() {
  $from = isset($_GET['from']) ? escape_string($_GET['from']) : date('Y-m-01');
  $to = isset($_GET['to']) ? escape_string($_GET['to']) : date('Y-m-d');

  $rows = get_all("SELECT p.id, p.name AS product_name,
                          SUM(ri.base_quantity) AS total_base_qty,
                          SUM(ri.quantity) AS total_units,
                          SUM(ri.amount) AS total_refund,
                          COUNT(*) AS times_returned
                   FROM invoice_return_items ri
                   JOIN invoice_returns r ON ri.return_id = r.id
                   JOIN products p ON ri.product_id = p.id
                   WHERE DATE(r.created_at) BETWEEN '$from' AND '$to'
                   GROUP BY p.id, p.name
                   ORDER BY total_refund DESC");

  json_response(true, 'تم جلب تقرير المنتجات المرتجعة', $rows);
}

/**
 * إحصائيات المرتجعات.
 */
function get_return_stats() {
  $from = isset($_GET['from']) ? escape_string($_GET['from']) : date('Y-m-01');
  $to = isset($_GET['to']) ? escape_string($_GET['to']) : date('Y-m-d');

  $stats = get_row("SELECT
                      COUNT(*) AS total_returns,
                      COALESCE(SUM(total_refund),0) AS total_refund,
                      SUM(CASE WHEN return_type='full' THEN 1 ELSE 0 END) AS full_returns,
                      SUM(CASE WHEN return_type='partial' THEN 1 ELSE 0 END) AS partial_returns
                    FROM invoice_returns
                    WHERE DATE(created_at) BETWEEN '$from' AND '$to'");

  // مبيعات الفترة لحساب نسبة المرتجعات
  $sales = get_row("SELECT COALESCE(SUM(amount),0) AS net_sales FROM sales WHERE sales_date BETWEEN '$from' AND '$to'");
  $stats['net_sales'] = $sales['net_sales'];

  json_response(true, 'تم جلب إحصائيات المرتجعات', $stats);
}

// ============================================
// INSURANCE FUNCTIONS (التأمين الصحي) - Feature 2
// ============================================

function get_insurance_companies() {
  $activeOnly = isset($_GET['active']) && $_GET['active'] == '1';
  $where = $activeOnly ? 'WHERE is_active = 1' : '';
  json_response(true, 'تم جلب شركات التأمين', get_all("SELECT * FROM insurance_companies $where ORDER BY name ASC"));
}

function get_insurance_company() {
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  if ($id <= 0) json_response(false, 'معرّف غير صحيح');
  $row = get_row("SELECT * FROM insurance_companies WHERE id = $id");
  if (!$row) json_response(false, 'الشركة غير موجودة');
  json_response(true, 'تم جلب الشركة', $row);
}

function add_insurance_company() {
  global $conn;
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['name'])) json_response(false, 'اسم الشركة مطلوب');
  $name = escape_string(trim($data['name']));
  $pct = round(floatval($data['discount_percentage'] ?? 0), 2);
  if ($pct < 0 || $pct > 100) json_response(false, 'نسبة الخصم يجب أن تكون بين 0 و 100');
  $notes = escape_string($data['notes'] ?? '');
  $is_active = !empty($data['is_active']) ? 1 : 0;

  execute_query("INSERT INTO insurance_companies (name, discount_percentage, notes, is_active)
                 VALUES ('$name', $pct, '$notes', $is_active)");
  $id = $conn->insert_id;
  audit_log('add_insurance_company', 'insurance_company', $id, ['name' => $data['name'], 'pct' => $pct]);
  json_response(true, 'تم إضافة شركة التأمين', ['id' => $id]);
}

function update_insurance_company() {
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['id']) || empty($data['name'])) json_response(false, 'بيانات ناقصة');
  $id = intval($data['id']);
  $name = escape_string(trim($data['name']));
  $pct = round(floatval($data['discount_percentage'] ?? 0), 2);
  if ($pct < 0 || $pct > 100) json_response(false, 'نسبة الخصم يجب أن تكون بين 0 و 100');
  $notes = escape_string($data['notes'] ?? '');
  $is_active = !empty($data['is_active']) ? 1 : 0;

  execute_query("UPDATE insurance_companies SET name='$name', discount_percentage=$pct, notes='$notes', is_active=$is_active WHERE id = $id");
  audit_log('update_insurance_company', 'insurance_company', $id);
  json_response(true, 'تم تحديث شركة التأمين');
}

function toggle_insurance_company() {
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['id'])) json_response(false, 'معرّف غير صحيح');
  $id = intval($data['id']);
  execute_query("UPDATE insurance_companies SET is_active = NOT is_active WHERE id = $id");
  audit_log('toggle_insurance_company', 'insurance_company', $id);
  json_response(true, 'تم تحديث حالة الشركة');
}

function delete_insurance_company() {
  $data = json_decode(file_get_contents('php://input'), true);
  if (empty($data['id'])) json_response(false, 'معرّف غير صحيح');
  $id = intval($data['id']);
  // الفواتير القديمة تحتفظ بقيم insurance_due/discount، لذا الحذف لا يؤثر على سجلاتها المالية
  $used = get_row("SELECT COUNT(*) AS c FROM invoices WHERE insurance_company_id = $id");
  if ($used && intval($used['c']) > 0) {
    json_response(false, 'لا يمكن حذف شركة مرتبطة بفواتير، يمكنك تعطيلها بدلاً من ذلك');
  }
  execute_query("DELETE FROM insurance_companies WHERE id = $id");
  audit_log('delete_insurance_company', 'insurance_company', $id);
  json_response(true, 'تم حذف شركة التأمين');
}

/**
 * تقرير مطالبات شركة التأمين: المبالغ المستحقة على الشركة لتسويتها.
 */
function get_insurance_claims_report() {
  $company_id = isset($_GET['company_id']) ? intval($_GET['company_id']) : 0;
  $from = isset($_GET['from']) ? escape_string($_GET['from']) : date('Y-m-01');
  $to = isset($_GET['to']) ? escape_string($_GET['to']) : date('Y-m-d');

  $where = "WHERE i.insurance_company_id IS NOT NULL AND i.insurance_due > 0
            AND DATE(i.created_at) BETWEEN '$from' AND '$to'";
  if ($company_id > 0) {
    $where .= " AND i.insurance_company_id = $company_id";
  }

  $rows = get_all("SELECT i.id, i.invoice_number, i.created_at,
                          i.customer_name, COALESCE(c.name, i.customer_name) AS customer,
                          i.total_amount, i.insurance_discount, i.insurance_due,
                          ic.name AS company_name, ic.discount_percentage
                   FROM invoices i
                   JOIN insurance_companies ic ON i.insurance_company_id = ic.id
                   LEFT JOIN customers c ON i.customer_id = c.id
                   $where
                   ORDER BY i.created_at DESC");

  $totals = get_row("SELECT COUNT(*) AS count,
                            COALESCE(SUM(i.total_amount),0) AS total_sales,
                            COALESCE(SUM(i.insurance_discount),0) AS total_discount,
                            COALESCE(SUM(i.insurance_due),0) AS total_claims
                     FROM invoices i $where");

  json_response(true, 'تم جلب تقرير مطالبات التأمين', ['claims' => $rows, 'totals' => $totals]);
}

?>