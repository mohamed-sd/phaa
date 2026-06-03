<?php
/**
 * ============================================================
 * ترحيل قاعدة البيانات - الإصدار 2 (V2 Migration)
 * ============================================================
 * يضيف الميزات الجديدة دون المساس بالبيانات الحالية:
 *  - مرتجعات الفواتير (Feature 1)
 *  - شركات التأمين الصحي (Feature 2)
 *  - الدفع الجزئي والعملاء (Feature 4)
 *  - طرق الدفع الديناميكية (Feature 5)
 *  - سجل التدقيق (Audit Log)
 *
 * هذا الملف "آمن للتكرار" (idempotent): يفحص information_schema
 * قبل كل تعديل، لذا يمكن تشغيله أكثر من مرة بأمان.
 *
 * طريقة التشغيل:
 *   - من المتصفح:  http://localhost/phaa/migrate_v2.php
 *   - من سطر الأوامر:  php migrate_v2.php
 * ============================================================
 */

require_once __DIR__ . '/config.php';

// عرض النتائج كنص عادي (يعمل في المتصفح وسطر الأوامر)
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

$conn = getDBConnection();
$conn->set_charset(DB_CHARSET);

$log = [];
function step($msg, $ok = true) {
    global $log;
    $prefix = $ok ? '[OK]   ' : '[SKIP] ';
    $line = $prefix . $msg;
    $log[] = $line;
    echo $line . "\n";
}
function fail($msg) {
    echo "[FAIL] " . $msg . "\n";
    exit(1);
}

// ------------------------------------------------------------
// أدوات فحص الهيكل (متوافقة مع MySQL 8 الذي لا يدعم IF NOT EXISTS للأعمدة)
// ------------------------------------------------------------
function table_exists($name) {
    global $conn;
    $name = $conn->real_escape_string($name);
    $r = $conn->query("SELECT COUNT(*) AS c FROM information_schema.tables
                       WHERE table_schema = DATABASE() AND table_name = '$name'");
    $row = $r->fetch_assoc();
    return intval($row['c']) > 0;
}
function column_exists($table, $column) {
    global $conn;
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $r = $conn->query("SELECT COUNT(*) AS c FROM information_schema.columns
                       WHERE table_schema = DATABASE() AND table_name = '$table' AND column_name = '$column'");
    $row = $r->fetch_assoc();
    return intval($row['c']) > 0;
}
function run($sql, $label) {
    global $conn;
    if (!$conn->query($sql)) {
        fail($label . ' :: ' . $conn->error);
    }
    step($label);
}
function add_column($table, $column, $definition) {
    if (column_exists($table, $column)) {
        step("$table.$column موجود مسبقاً", false);
        return;
    }
    run("ALTER TABLE `$table` ADD COLUMN $definition", "إضافة عمود $table.$column");
}

echo "============================================\n";
echo " ترحيل قاعدة البيانات V2 - " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n\n";

// ============================================================
// 1) جدول العملاء (customers)
// ============================================================
if (!table_exists('customers')) {
    run("CREATE TABLE `customers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(200) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `address` text DEFAULT NULL,
            `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
            `notes` text DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_name` (`name`),
            KEY `idx_phone` (`phone`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "إنشاء جدول العملاء customers");
} else {
    step("جدول customers موجود مسبقاً", false);
}

// ============================================================
// 2) طرق الدفع (payment_methods) - Feature 5
// ============================================================
if (!table_exists('payment_methods')) {
    run("CREATE TABLE `payment_methods` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `is_system` tinyint(1) NOT NULL DEFAULT 0,
            `notes` text DEFAULT NULL,
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`),
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "إنشاء جدول طرق الدفع payment_methods");
} else {
    step("جدول payment_methods موجود مسبقاً", false);
}

// بذر طرق الدفع الافتراضية (Cash يكون طريقة نظام افتراضية)
$defaultMethods = [
    ['نقداً', 1, 1],
    ['فيزا', 0, 1],
    ['ماستركارد', 0, 2],
    ['فودافون كاش', 0, 3],
    ['إنستا باي', 0, 4],
    ['تحويل بنكي', 0, 5],
];
foreach ($defaultMethods as $m) {
    $name = $conn->real_escape_string($m[0]);
    $isSystem = intval($m[1]);
    $sort = intval($m[2]);
    $exists = get_row("SELECT id FROM payment_methods WHERE name = '$name'");
    if (!$exists) {
        run("INSERT INTO payment_methods (name, is_active, is_system, sort_order) VALUES ('$name', 1, $isSystem, $sort)",
            "بذر طريقة الدفع: {$m[0]}");
    } else {
        step("طريقة الدفع موجودة: {$m[0]}", false);
    }
}

// ============================================================
// 3) شركات التأمين الصحي (insurance_companies) - Feature 2
// ============================================================
if (!table_exists('insurance_companies')) {
    run("CREATE TABLE `insurance_companies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(200) NOT NULL,
            `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00 CHECK (`discount_percentage` >= 0 AND `discount_percentage` <= 100),
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `notes` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "إنشاء جدول شركات التأمين insurance_companies");
} else {
    step("جدول insurance_companies موجود مسبقاً", false);
}

// ============================================================
// 4) تعديل جدول الفواتير (invoices) - إضافة أعمدة (Feature 1,2,4,5)
// ============================================================
add_column('invoices', 'customer_id',          "`customer_id` int(11) DEFAULT NULL");
add_column('invoices', 'payment_method_id',     "`payment_method_id` int(11) DEFAULT NULL");
add_column('invoices', 'status',                "`status` enum('paid','partial','unpaid','returned','partially_returned') NOT NULL DEFAULT 'paid'");
add_column('invoices', 'paid_amount',           "`paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00");
add_column('invoices', 'remaining_amount',      "`remaining_amount` decimal(10,2) NOT NULL DEFAULT 0.00");
add_column('invoices', 'discount_amount',       "`discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00");
add_column('invoices', 'insurance_company_id',  "`insurance_company_id` int(11) DEFAULT NULL");
add_column('invoices', 'insurance_discount',    "`insurance_discount` decimal(10,2) NOT NULL DEFAULT 0.00");
add_column('invoices', 'insurance_due',         "`insurance_due` decimal(10,2) NOT NULL DEFAULT 0.00");
add_column('invoices', 'customer_name',         "`customer_name` varchar(200) DEFAULT NULL");
add_column('invoices', 'return_date',           "`return_date` datetime DEFAULT NULL");
add_column('invoices', 'returned_by',           "`returned_by` int(11) DEFAULT NULL");

// فهارس مساعدة للتقارير الجديدة
foreach ([
    ['invoices', 'idx_status', 'status'],
    ['invoices', 'idx_customer', 'customer_id'],
    ['invoices', 'idx_insurance', 'insurance_company_id'],
] as $idx) {
    list($tbl, $idxName, $col) = $idx;
    $r = $conn->query("SELECT COUNT(*) AS c FROM information_schema.statistics
                       WHERE table_schema = DATABASE() AND table_name = '$tbl' AND index_name = '$idxName'");
    $row = $r->fetch_assoc();
    if (intval($row['c']) === 0) {
        run("ALTER TABLE `$tbl` ADD INDEX `$idxName` (`$col`)", "إضافة فهرس $tbl.$idxName");
    } else {
        step("الفهرس $tbl.$idxName موجود", false);
    }
}

// توسيع رقم الفاتورة ليتسع لصيغة التوليد (INV-YmdHis-####) = 23 حرفاً
$col = $conn->query("SELECT CHARACTER_MAXIMUM_LENGTH AS len FROM information_schema.columns
                     WHERE table_schema = DATABASE() AND table_name = 'invoices' AND column_name = 'invoice_number'");
$colRow = $col ? $col->fetch_assoc() : null;
if ($colRow && intval($colRow['len']) < 30) {
    run("ALTER TABLE `invoices` MODIFY `invoice_number` varchar(30) NOT NULL", "توسيع invoices.invoice_number إلى varchar(30)");
} else {
    step("invoices.invoice_number بالعرض المناسب", false);
}

// ============================================================
// 5) تعديل عناصر الفاتورة (invoice_items) - الكمية المرتجعة
// ============================================================
add_column('invoice_items', 'returned_qty', "`returned_qty` int(11) NOT NULL DEFAULT 0");

// ============================================================
// 6) جداول المرتجعات (invoice_returns / invoice_return_items) - Feature 1
// ============================================================
if (!table_exists('invoice_returns')) {
    run("CREATE TABLE `invoice_returns` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `return_number` varchar(30) NOT NULL,
            `invoice_id` int(11) NOT NULL,
            `return_type` enum('full','partial') NOT NULL DEFAULT 'partial',
            `total_refund` decimal(10,2) NOT NULL DEFAULT 0.00,
            `reason` text DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `return_number` (`return_number`),
            KEY `idx_invoice` (`invoice_id`),
            KEY `idx_created` (`created_at`),
            CONSTRAINT `invoice_returns_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "إنشاء جدول المرتجعات invoice_returns");
} else {
    step("جدول invoice_returns موجود مسبقاً", false);
}

if (!table_exists('invoice_return_items')) {
    run("CREATE TABLE `invoice_return_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `return_id` int(11) NOT NULL,
            `invoice_item_id` int(11) DEFAULT NULL,
            `product_id` int(11) NOT NULL,
            `quantity` int(11) NOT NULL,
            `unit` enum('strip','box','carton') DEFAULT 'strip',
            `unit_quantity` int(11) NOT NULL DEFAULT 1,
            `base_quantity` int(11) NOT NULL DEFAULT 1,
            `unit_price` decimal(10,2) NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_return` (`return_id`),
            KEY `idx_product` (`product_id`),
            CONSTRAINT `invoice_return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `invoice_returns` (`id`) ON DELETE CASCADE,
            CONSTRAINT `invoice_return_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "إنشاء جدول عناصر المرتجعات invoice_return_items");
} else {
    step("جدول invoice_return_items موجود مسبقاً", false);
}

// ============================================================
// 7) سجل مدفوعات الفواتير (invoice_payments) - Feature 4
// ============================================================
if (!table_exists('invoice_payments')) {
    run("CREATE TABLE `invoice_payments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` int(11) NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `payment_method_id` int(11) DEFAULT NULL,
            `payment_method_name` varchar(100) DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_invoice` (`invoice_id`),
            KEY `idx_created` (`created_at`),
            CONSTRAINT `invoice_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "إنشاء جدول مدفوعات الفواتير invoice_payments");
} else {
    step("جدول invoice_payments موجود مسبقاً", false);
}

// ============================================================
// 8) سجل التدقيق (audit_log)
// ============================================================
if (!table_exists('audit_log')) {
    run("CREATE TABLE `audit_log` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) DEFAULT NULL,
            `username` varchar(100) DEFAULT NULL,
            `action` varchar(80) NOT NULL,
            `entity` varchar(60) DEFAULT NULL,
            `entity_id` int(11) DEFAULT NULL,
            `details` text DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_action` (`action`),
            KEY `idx_entity` (`entity`, `entity_id`),
            KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "إنشاء جدول سجل التدقيق audit_log");
} else {
    step("جدول audit_log موجود مسبقاً", false);
}

// ============================================================
// 9) تعبئة البيانات الموجودة (Backfill) - الفواتير القديمة مدفوعة بالكامل
// ============================================================
run("UPDATE invoices
        SET paid_amount = total_amount,
            remaining_amount = 0,
            status = 'paid'
      WHERE paid_amount = 0 AND remaining_amount = 0 AND status = 'paid'",
    "تعبئة حالة الدفع للفواتير القديمة (مدفوعة بالكامل)");

// ربط payment_method_id للفواتير القديمة حسب النص المخزّن (نقداً افتراضياً)
$cashRow = get_row("SELECT id FROM payment_methods WHERE is_system = 1 AND name = 'نقداً' LIMIT 1");
if ($cashRow) {
    $cashId = intval($cashRow['id']);
    run("UPDATE invoices SET payment_method_id = $cashId
          WHERE payment_method_id IS NULL",
        "ربط طريقة الدفع الافتراضية (نقداً) للفواتير القديمة");
}

// ============================================================
// 10) إعداد التأمين الصحي (افتراضياً: معطّل)
// ============================================================
$insSetting = get_row("SELECT id FROM pharmacy_settings WHERE setting_key = 'uses_health_insurance'");
if (!$insSetting) {
    run("INSERT INTO pharmacy_settings (setting_key, setting_value) VALUES ('uses_health_insurance', '0')",
        "إضافة إعداد uses_health_insurance = 0");
} else {
    step("إعداد uses_health_insurance موجود", false);
}

echo "\n============================================\n";
echo " اكتمل الترحيل بنجاح ✔\n";
echo "============================================\n";
