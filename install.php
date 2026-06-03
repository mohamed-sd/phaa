<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * نظام تثبيت صيدليات الحياة
 * Pharmacy POS Installation System
 * 
 * يقوم بإنشاء قاعدة البيانات والجداول والبيانات الافتراضية
 */

session_start();

// منع الوصول إذا كان النظام مثبتاً مسبقاً
$lockFile = __DIR__ . '/install.lock';

// التحقق من وجود ملف القفل
$isInstalled = file_exists($lockFile);

// معالجة الطلبات
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// معالجة POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'check_requirements') {
        // فحص المتطلبات - انتقل للخطوة التالية
        header('Location: install.php?step=2');
        exit;
    }
    
    if ($action === 'test_connection') {
        // اختبار الاتصال بقاعدة البيانات
        $host = $_POST['db_host'] ?? 'localhost';
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';
        
        try {
            $conn = new mysqli($host, $user, $pass);
            if ($conn->connect_error) {
                throw new Exception($conn->connect_error);
            }
            $_SESSION['db_config'] = [
                'host' => $host,
                'user' => $user,
                'pass' => $pass
            ];
            $conn->close();
            header('Location: install.php?step=3');
            exit;
        } catch (Exception $e) {
            $error = 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage();
            $step = 2;
        }
    }
    
    if ($action === 'install') {
        // تنفيذ التثبيت
        $dbConfig = $_SESSION['db_config'] ?? null;
        if (!$dbConfig) {
            header('Location: install.php?step=2');
            exit;
        }
        
        $dbName = $_POST['db_name'] ?? 'pharmacy_pos';
        $pharmacyName = $_POST['pharmacy_name'] ?? 'صيدليات الحياة';
        $adminUser = $_POST['admin_user'] ?? 'admin';
        $adminPass = $_POST['admin_pass'] ?? 'admin123';
        $adminEmail = $_POST['admin_email'] ?? 'admin@pharmacy.com';
        $adminPhone = $_POST['admin_phone'] ?? '01001234567';
        
        // حفظ البيانات للعرض في النهاية
        $_SESSION['install_data'] = [
            'admin_user' => $adminUser,
            'admin_pass' => $adminPass
        ];
        
        try {
            $result = installDatabase(
                $dbConfig['host'],
                $dbConfig['user'],
                $dbConfig['pass'],
                $dbName,
                $pharmacyName,
                $adminUser,
                $adminPass,
                $adminEmail,
                $adminPhone
            );
            
            if ($result['success']) {
                // إنشاء ملف القفل
                file_put_contents($lockFile, date('Y-m-d H:i:s'));
                
                // إنشاء ملف الإعدادات
                createConfigFile($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbName);
                
                $_SESSION['install_success'] = true;
                header('Location: install.php?step=4');
                exit;
            } else {
                throw new Exception($result['message']);
            }
        } catch (Exception $e) {
            $error = 'خطأ في التثبيت: ' . $e->getMessage();
            $step = 3;
        }
    }
}

/**
 * تثبيت قاعدة البيانات
 */
function installDatabase($host, $user, $pass, $dbName, $pharmacyName, $adminUser, $adminPass, $adminEmail, $adminPhone) {
    try {
        // الاتصال بالخادم
        $conn = new mysqli($host, $user, $pass);
        if ($conn->connect_error) {
            throw new Exception('فشل الاتصال: ' . $conn->connect_error);
        }
        
        $conn->set_charset('utf8mb4');
        
        // إنشاء قاعدة البيانات
        $conn->query("DROP DATABASE IF EXISTS `$dbName`");
        $conn->query("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $conn->select_db($dbName);
        
        // تعطيل فحص المفاتيح الأجنبية مؤقتاً
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // =============================================
        // إنشاء الجداول
        // =============================================
        
        // جدول المستخدمين
        $conn->query("
            CREATE TABLE `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(100) NOT NULL,
                `password` varchar(255) NOT NULL,
                `full_name` varchar(150) NOT NULL,
                `email` varchar(100) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `role` enum('admin','cashier','manager') DEFAULT 'cashier',
                `branch_id` int(11) DEFAULT 1,
                `is_active` tinyint(1) DEFAULT 1,
                `last_login` datetime DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `username` (`username`),
                KEY `idx_username` (`username`),
                KEY `idx_role` (`role`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول الفروع
        $conn->query("
            CREATE TABLE `branches` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `address` text DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `manager_id` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `manager_id` (`manager_id`),
                CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول الفئات
        $conn->query("
            CREATE TABLE `categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `description` text DEFAULT NULL,
                `icon` varchar(50) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول المنتجات
        $conn->query("
            CREATE TABLE `products` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `category_id` int(11) NOT NULL,
                `name` varchar(200) NOT NULL,
                `description` text DEFAULT NULL,
                `price` decimal(10,2) NOT NULL CHECK (`price` > 0),
                `stock` int(11) DEFAULT 0 CHECK (`stock` >= 0),
                `stock_strips` int(11) DEFAULT 0 CHECK (`stock_strips` >= 0),
                `strips_per_box` int(11) DEFAULT 3 CHECK (`strips_per_box` >= 1),
                `boxes_per_carton` int(11) DEFAULT 12 CHECK (`boxes_per_carton` >= 1),
                `price_strip` decimal(10,2) DEFAULT NULL,
                `price_box` decimal(10,2) DEFAULT NULL,
                `price_carton` decimal(10,2) DEFAULT NULL,
                `image_url` varchar(500) DEFAULT NULL,
                `barcode` varchar(50) DEFAULT NULL,
                `expiry_date` date DEFAULT NULL,
                `min_stock` int(11) DEFAULT 10,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `is_active` tinyint(1) DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `barcode` (`barcode`),
                KEY `idx_category` (`category_id`),
                KEY `idx_name` (`name`),
                KEY `idx_stock` (`stock_strips`),
                CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول الموردين
        $conn->query("
            CREATE TABLE `suppliers` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(200) NOT NULL,
                `contact_person` varchar(150) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `phone2` varchar(20) DEFAULT NULL,
                `email` varchar(100) DEFAULT NULL,
                `address` text DEFAULT NULL,
                `city` varchar(100) DEFAULT NULL,
                `tax_number` varchar(50) DEFAULT NULL,
                `notes` text DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `total_purchases` decimal(12,2) DEFAULT 0.00,
                `total_paid` decimal(12,2) DEFAULT 0.00,
                `balance` decimal(12,2) DEFAULT 0.00,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_name` (`name`),
                KEY `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول المشتريات
        $conn->query("
            CREATE TABLE `purchases` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `purchase_number` varchar(30) NOT NULL,
                `supplier_id` int(11) NOT NULL,
                `user_id` int(11) DEFAULT NULL,
                `purchase_date` date NOT NULL,
                `due_date` date DEFAULT NULL,
                `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
                `discount_amount` decimal(10,2) DEFAULT 0.00,
                `tax_amount` decimal(10,2) DEFAULT 0.00,
                `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                `paid_amount` decimal(12,2) DEFAULT 0.00,
                `remaining_amount` decimal(12,2) DEFAULT 0.00,
                `status` enum('pending','partial','completed','cancelled') DEFAULT 'pending',
                `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
                `notes` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `purchase_number` (`purchase_number`),
                KEY `idx_purchase_number` (`purchase_number`),
                KEY `idx_supplier` (`supplier_id`),
                KEY `idx_date` (`purchase_date`),
                KEY `idx_status` (`status`),
                CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول عناصر المشتريات
        $conn->query("
            CREATE TABLE `purchase_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `purchase_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
                `unit` enum('strip','box','carton') DEFAULT 'box',
                `unit_quantity` int(11) NOT NULL DEFAULT 1,
                `base_quantity` int(11) NOT NULL DEFAULT 1,
                `purchase_price` decimal(10,2) NOT NULL,
                `selling_price` decimal(10,2) DEFAULT NULL,
                `total_price` decimal(12,2) NOT NULL,
                `expiry_date` date DEFAULT NULL,
                `batch_number` varchar(50) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_purchase` (`purchase_id`),
                KEY `idx_product` (`product_id`),
                CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
                CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول مدفوعات الموردين
        $conn->query("
            CREATE TABLE `supplier_payments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `supplier_id` int(11) NOT NULL,
                `purchase_id` int(11) DEFAULT NULL,
                `amount` decimal(12,2) NOT NULL,
                `payment_method` enum('cash','bank_transfer','check','other') DEFAULT 'cash',
                `payment_date` date NOT NULL,
                `reference_number` varchar(50) DEFAULT NULL,
                `notes` text DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `purchase_id` (`purchase_id`),
                KEY `idx_supplier` (`supplier_id`),
                KEY `idx_date` (`payment_date`),
                CONSTRAINT `supplier_payments_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
                CONSTRAINT `supplier_payments_ibfk_2` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول الفواتير
        $conn->query("
            CREATE TABLE `invoices` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `invoice_number` varchar(30) NOT NULL,
                `branch_id` int(11) DEFAULT 1,
                `cashier_id` int(11) DEFAULT 1,
                `customer_id` int(11) DEFAULT NULL,
                `customer_name` varchar(200) DEFAULT NULL,
                `total_amount` decimal(10,2) NOT NULL CHECK (`total_amount` >= 0),
                `tax_amount` decimal(10,2) DEFAULT 0.00,
                `subtotal` decimal(10,2) NOT NULL,
                `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
                `payment_method` varchar(50) DEFAULT NULL,
                `payment_method_id` int(11) DEFAULT NULL,
                `status` enum('paid','partial','unpaid','returned','partially_returned') NOT NULL DEFAULT 'paid',
                `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
                `remaining_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
                `insurance_company_id` int(11) DEFAULT NULL,
                `insurance_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
                `insurance_due` decimal(10,2) NOT NULL DEFAULT 0.00,
                `return_date` datetime DEFAULT NULL,
                `returned_by` int(11) DEFAULT NULL,
                `notes` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `invoice_number` (`invoice_number`),
                KEY `cashier_id` (`cashier_id`),
                KEY `idx_invoice_number` (`invoice_number`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_status` (`status`),
                KEY `idx_customer` (`customer_id`),
                KEY `idx_insurance` (`insurance_company_id`),
                CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول عناصر الفواتير
        $conn->query("
            CREATE TABLE `invoice_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `invoice_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
                `unit` enum('strip','box','carton') DEFAULT 'strip',
                `unit_quantity` int(11) NOT NULL CHECK (`unit_quantity` > 0),
                `base_quantity` int(11) NOT NULL CHECK (`base_quantity` > 0),
                `unit_price` decimal(10,2) NOT NULL,
                `total_price` decimal(10,2) NOT NULL,
                `returned_qty` int(11) NOT NULL DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_invoice` (`invoice_id`),
                KEY `idx_product` (`product_id`),
                CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
                CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول المبيعات
        $conn->query("
            CREATE TABLE `sales` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `invoice_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                `quantity` int(11) NOT NULL,
                `unit` enum('strip','box','carton') DEFAULT 'strip',
                `unit_quantity` int(11) NOT NULL DEFAULT 1,
                `base_quantity` int(11) NOT NULL DEFAULT 1,
                `amount` decimal(10,2) NOT NULL,
                `sales_date` date NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `invoice_id` (`invoice_id`),
                KEY `idx_sales_date` (`sales_date`),
                KEY `idx_product` (`product_id`),
                CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
                CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول سجل المخزون
        $conn->query("
            CREATE TABLE `stock_history` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `quantity_change` int(11) NOT NULL,
                `operation_type` varchar(50) DEFAULT NULL,
                `notes` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_product` (`product_id`),
                KEY `idx_created_at` (`created_at`),
                CONSTRAINT `stock_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // جدول الإعدادات
        $conn->query("
            CREATE TABLE `pharmacy_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // جدول العملاء
        $conn->query("
            CREATE TABLE `customers` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // جدول طرق الدفع
        $conn->query("
            CREATE TABLE `payment_methods` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // جدول شركات التأمين الصحي
        $conn->query("
            CREATE TABLE `insurance_companies` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(200) NOT NULL,
                `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `notes` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // جدول مرتجعات الفواتير
        $conn->query("
            CREATE TABLE `invoice_returns` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // جدول عناصر المرتجعات
        $conn->query("
            CREATE TABLE `invoice_return_items` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // جدول مدفوعات الفواتير (الدفع الجزئي)
        $conn->query("
            CREATE TABLE `invoice_payments` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // جدول سجل التدقيق
        $conn->query("
            CREATE TABLE `audit_log` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // طرق الدفع الافتراضية
        $conn->query("
            INSERT INTO `payment_methods` (`name`, `is_active`, `is_system`, `sort_order`) VALUES
            ('نقداً', 1, 1, 0),
            ('فيزا', 1, 1, 1),
            ('ماستركارد', 1, 1, 2),
            ('فودافون كاش', 1, 1, 3),
            ('إنستا باي', 1, 1, 4),
            ('تحويل بنكي', 1, 1, 5)
        ");

        // =============================================
        // إدخال البيانات الافتراضية
        // =============================================
        
        // المستخدم الإداري
        $adminPassEscaped = $conn->real_escape_string($adminPass);
        $adminUserEscaped = $conn->real_escape_string($adminUser);
        $adminEmailEscaped = $conn->real_escape_string($adminEmail);
        $adminPhoneEscaped = $conn->real_escape_string($adminPhone);
        
        $conn->query("
            INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `phone`, `role`, `branch_id`, `is_active`) 
            VALUES ('$adminUserEscaped', '$adminPassEscaped', 'مدير النظام', '$adminEmailEscaped', '$adminPhoneEscaped', 'admin', 1, 1)
        ");
        
        // مستخدمين إضافيين للاختبار
        $conn->query("
            INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `phone`, `role`, `branch_id`, `is_active`) VALUES
            ('manager1', 'manager123', 'مدير الفرع', 'manager@pharmacy.com', '01001234568', 'manager', 1, 1),
            ('cashier1', 'cashier123', 'أحمد محمد', 'cashier@pharmacy.com', '01002345678', 'cashier', 1, 1)
        ");
        
        // الفرع الرئيسي
        $conn->query("
            INSERT INTO `branches` (`name`, `address`, `phone`) 
            VALUES ('الفرع الرئيسي', 'العنوان الرئيسي', '01001234567')
        ");
        
        // الفئات الافتراضية
        $conn->query("
            INSERT INTO `categories` (`name`, `description`, `icon`) VALUES
            ('أدوية', 'الأدوية والعلاجات الطبية', 'fa-pills'),
            ('مكملات غذائية', 'الفيتامينات والمكملات الغذائية', 'fa-capsules'),
            ('مستحضرات تجميل', 'منتجات العناية بالبشرة والشعر', 'fa-pump-soap'),
            ('أجهزة طبية', 'أجهزة قياس الضغط والسكر', 'fa-stethoscope'),
            ('منتجات أطفال', 'حليب وحفاضات ومستلزمات الأطفال', 'fa-baby')
        ");
        
        // منتجات تجريبية
        $conn->query("
            INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `stock`, `stock_strips`, `strips_per_box`, `boxes_per_carton`, `price_strip`, `price_box`, `price_carton`, `barcode`, `min_stock`, `is_active`) VALUES
            (1, 'باراسيتامول 500مج', 'مسكن للألم وخافض للحرارة', 2.50, 100, 300, 3, 12, 2.50, 7.00, 80.00, '6281000001', 20, 1),
            (1, 'أموكسيسيلين 500مج', 'مضاد حيوي واسع المجال', 5.00, 50, 150, 3, 10, 5.00, 14.00, 130.00, '6281000002', 15, 1),
            (1, 'أومبيرازول 20مج', 'علاج قرحة المعدة والحموضة', 3.50, 80, 240, 3, 12, 3.50, 10.00, 110.00, '6281000003', 15, 1),
            (2, 'فيتامين سي 1000مج', 'مكمل غذائي لتقوية المناعة', 15.00, 40, 120, 3, 8, 15.00, 42.00, 320.00, '6281000004', 10, 1),
            (2, 'أوميجا 3', 'مكمل غذائي لصحة القلب', 45.00, 30, 90, 3, 6, 45.00, 130.00, 750.00, '6281000005', 8, 1),
            (3, 'كريم مرطب للبشرة', 'كريم ترطيب يومي للبشرة', 25.00, 25, 25, 1, 12, 25.00, 25.00, 280.00, '6281000006', 5, 1),
            (4, 'جهاز قياس الضغط', 'جهاز رقمي لقياس ضغط الدم', 350.00, 10, 10, 1, 4, 350.00, 350.00, 1350.00, '6281000007', 3, 1),
            (5, 'حليب أطفال رقم 1', 'حليب للرضع من الولادة', 120.00, 35, 35, 1, 6, 120.00, 120.00, 680.00, '6281000008', 10, 1)
        ");
        
        // موردين تجريبيين
        $conn->query("
            INSERT INTO `suppliers` (`name`, `contact_person`, `phone`, `email`, `address`, `city`, `is_active`) VALUES
            ('شركة الأدوية المتحدة', 'محمد أحمد', '01001234567', 'united@pharma.com', 'المنطقة الصناعية', 'القاهرة', 1),
            ('شركة فارما للأدوية', 'أحمد علي', '01112345678', 'pharma@med.com', 'شارع التحرير', 'الجيزة', 1),
            ('المستودع الطبي', 'علي محمود', '01223456789', 'medical@store.com', 'المعادي', 'القاهرة', 1)
        ");
        
        // إعدادات النظام
        $pharmacyNameEscaped = $conn->real_escape_string($pharmacyName);
        $conn->query("
            INSERT INTO `pharmacy_settings` (`setting_key`, `setting_value`) VALUES
            ('pharmacy_name', '$pharmacyNameEscaped'),
            ('license_number', 'PH-2024-00000'),
            ('phone', '$adminPhoneEscaped'),
            ('email', '$adminEmailEscaped'),
            ('address', 'العنوان'),
            ('description', 'صيدليتك المفضلة في خدمة صحتكم'),
            ('tax_rate', '5'),
            ('currency_symbol', 'ج.م'),
            ('invoice_notes', 'شكراً لتعاملكم معنا'),
            ('low_stock_threshold', '10'),
            ('uses_health_insurance', '0')
        ");
        
        // إعادة تفعيل فحص المفاتيح الأجنبية
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $conn->close();
        
        return ['success' => true, 'message' => 'تم التثبيت بنجاح'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * إنشاء ملف الإعدادات
 */
function createConfigFile($host, $user, $pass, $dbName) {
    $configContent = "<?php
/**
 * إعدادات قاعدة البيانات
 * تم إنشاؤها تلقائياً بواسطة معالج التثبيت
 * تاريخ التثبيت: " . date('Y-m-d H:i:s') . "
 */

// إعدادات قاعدة البيانات
define('DB_HOST', '$host');
define('DB_USER', '$user');
define('DB_PASS', '$pass');
define('DB_NAME', '$dbName');
define('DB_CHARSET', 'utf8mb4');

// إعدادات النظام
define('SYSTEM_VERSION', '1.0.0');
define('SYSTEM_NAME', 'صيدليات الحياة');
define('DEBUG_MODE', false);

// إعدادات الجلسة
define('SESSION_LIFETIME', 3600); // ساعة واحدة

// المنطقة الزمنية
date_default_timezone_set('Africa/Cairo');

// اتصال قاعدة البيانات
function getDBConnection() {
    static \$conn = null;
    
    if (\$conn === null) {
        \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (\$conn->connect_error) {
            die('فشل الاتصال بقاعدة البيانات: ' . \$conn->connect_error);
        }
        
        \$conn->set_charset(DB_CHARSET);
    }
    
    return \$conn;
}
";
    
    file_put_contents(__DIR__ . '/config.php', $configContent);
}

/**
 * فحص المتطلبات
 */
function checkRequirements() {
    $requirements = [];
    
    // PHP Version
    $requirements['php_version'] = [
        'name' => 'إصدار PHP',
        'required' => '7.4+',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
    ];
    
    // MySQLi Extension
    $requirements['mysqli'] = [
        'name' => 'إضافة MySQLi',
        'required' => 'مفعّل',
        'current' => extension_loaded('mysqli') ? 'مفعّل' : 'غير مفعّل',
        'status' => extension_loaded('mysqli')
    ];
    
    // JSON Extension
    $requirements['json'] = [
        'name' => 'إضافة JSON',
        'required' => 'مفعّل',
        'current' => extension_loaded('json') ? 'مفعّل' : 'غير مفعّل',
        'status' => extension_loaded('json')
    ];
    
    // Session Extension
    $requirements['session'] = [
        'name' => 'إضافة Session',
        'required' => 'مفعّل',
        'current' => extension_loaded('session') ? 'مفعّل' : 'غير مفعّل',
        'status' => extension_loaded('session')
    ];
    
    // File Permissions
    $requirements['writable'] = [
        'name' => 'صلاحيات الكتابة',
        'required' => 'قابل للكتابة',
        'current' => is_writable(__DIR__) ? 'قابل للكتابة' : 'غير قابل للكتابة',
        'status' => is_writable(__DIR__)
    ];
    
    return $requirements;
}

$requirements = checkRequirements();
$allPassed = !in_array(false, array_column($requirements, 'status'));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت نظام صيدليات الحياة</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #22d3ee 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .installer {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 700px;
            overflow: hidden;
        }
        
        .installer-header {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .installer-header .logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }
        
        .installer-header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .installer-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .steps {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            color: #64748b;
            background: white;
            border: 2px solid #e2e8f0;
        }
        
        .step.active {
            color: #0891b2;
            border-color: #0891b2;
            background: #ecfeff;
        }
        
        .step.completed {
            color: #10b981;
            border-color: #10b981;
            background: #d1fae5;
        }
        
        .step i {
            font-size: 16px;
        }
        
        .installer-content {
            padding: 30px;
        }
        
        .section-title {
            font-size: 20px;
            color: #0f172a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #0891b2;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        
        .alert-warning {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        
        .requirements-list {
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .requirement-item:last-child {
            border-bottom: none;
        }
        
        .requirement-name {
            font-weight: 600;
            color: #0f172a;
        }
        
        .requirement-value {
            font-size: 13px;
            color: #64748b;
        }
        
        .requirement-status {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .status-pass {
            background: #d1fae5;
            color: #10b981;
        }
        
        .status-fail {
            background: #fee2e2;
            color: #ef4444;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #0f172a;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #0891b2;
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(8, 145, 178, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(8, 145, 178, 0.5);
        }
        
        .btn-primary:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .success-animation {
            text-align: center;
            padding: 40px 20px;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 48px;
            color: white;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        
        .success-animation h2 {
            color: #10b981;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .success-animation p {
            color: #64748b;
            margin-bottom: 10px;
        }
        
        .credentials-box {
            background: #f0fdfa;
            border: 2px solid #14b8a6;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: right;
        }
        
        .credentials-box h4 {
            color: #0f766e;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #99f6e4;
        }
        
        .credential-item:last-child {
            border-bottom: none;
        }
        
        .credential-label {
            color: #64748b;
        }
        
        .credential-value {
            font-weight: 600;
            color: #0f172a;
            font-family: monospace;
            background: white;
            padding: 4px 10px;
            border-radius: 6px;
        }
        
        .installed-notice {
            text-align: center;
            padding: 60px 20px;
        }
        
        .installed-notice .icon {
            font-size: 64px;
            color: #f59e0b;
            margin-bottom: 20px;
        }
        
        .installed-notice h2 {
            color: #0f172a;
            margin-bottom: 15px;
        }
        
        .installed-notice p {
            color: #64748b;
            margin-bottom: 25px;
        }
        
        .installed-notice code {
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
        }
        
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 25px 0;
        }
        
        .form-section-title {
            font-size: 16px;
            color: #0891b2;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0f2fe;
        }
        
        .tables-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .tables-info h5 {
            color: #0891b2;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .table-item {
            background: white;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
        }
        
        .table-item i {
            color: #0891b2;
            font-size: 11px;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .steps {
                flex-wrap: wrap;
            }
            
            .step span {
                display: none;
            }
            
            .tables-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="installer">
        <div class="installer-header">
            <div class="logo">
                <i class="fas fa-prescription-bottle-medical"></i>
            </div>
            <h1>صيدليات الحياة</h1>
            <p>معالج تثبيت نظام إدارة الصيدليات</p>
        </div>
        
        <?php if (!$isInstalled): ?>
        <div class="steps">
            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                <i class="fas <?php echo $step > 1 ? 'fa-check' : 'fa-clipboard-check'; ?>"></i>
                <span>المتطلبات</span>
            </div>
            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                <i class="fas <?php echo $step > 2 ? 'fa-check' : 'fa-database'; ?>"></i>
                <span>قاعدة البيانات</span>
            </div>
            <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">
                <i class="fas <?php echo $step > 3 ? 'fa-check' : 'fa-cog'; ?>"></i>
                <span>الإعدادات</span>
            </div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">
                <i class="fas fa-flag-checkered"></i>
                <span>الانتهاء</span>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="installer-content">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($isInstalled): ?>
            <!-- النظام مثبت مسبقاً -->
            <div class="installed-notice">
                <div class="icon">
                    <i class="fas fa-shield-check"></i>
                </div>
                <h2>النظام مثبت مسبقاً</h2>
                <p>تم تثبيت النظام بنجاح. إذا كنت ترغب في إعادة التثبيت، يرجى حذف ملف <code>install.lock</code> أولاً.</p>
                <div class="buttons" style="justify-content: center;">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        الذهاب لتسجيل الدخول
                    </a>
                </div>
            </div>
            
            <?php elseif ($step === 1): ?>
            <!-- الخطوة 1: فحص المتطلبات -->
            <h3 class="section-title">
                <i class="fas fa-clipboard-check"></i>
                فحص متطلبات النظام
            </h3>
            
            <div class="requirements-list">
                <?php foreach ($requirements as $req): ?>
                <div class="requirement-item">
                    <div>
                        <div class="requirement-name"><?php echo $req['name']; ?></div>
                        <div class="requirement-value">المطلوب: <?php echo $req['required']; ?> | الحالي: <?php echo $req['current']; ?></div>
                    </div>
                    <div class="requirement-status <?php echo $req['status'] ? 'status-pass' : 'status-fail'; ?>">
                        <i class="fas <?php echo $req['status'] ? 'fa-check' : 'fa-times'; ?>"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!$allPassed): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                بعض المتطلبات غير متوفرة. يرجى إصلاحها قبل المتابعة.
            </div>
            <?php endif; ?>
            
            <div class="tables-info">
                <h5><i class="fas fa-table"></i> الجداول التي سيتم إنشاؤها:</h5>
                <div class="tables-grid">
                    <div class="table-item"><i class="fas fa-circle"></i> users (المستخدمين)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> branches (الفروع)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> categories (الفئات)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> products (المنتجات)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> suppliers (الموردين)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> purchases (المشتريات)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> purchase_items (عناصر المشتريات)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> supplier_payments (المدفوعات)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> invoices (الفواتير)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> invoice_items (عناصر الفواتير)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> sales (المبيعات)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> stock_history (سجل المخزون)</div>
                    <div class="table-item"><i class="fas fa-circle"></i> pharmacy_settings (الإعدادات)</div>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="check_requirements">
                <div class="buttons">
                    <button type="submit" class="btn btn-primary" <?php echo !$allPassed ? 'disabled' : ''; ?>>
                        <i class="fas fa-arrow-left"></i>
                        متابعة
                    </button>
                </div>
            </form>
            
            <?php elseif ($step === 2): ?>
            <!-- الخطوة 2: إعدادات قاعدة البيانات -->
            <h3 class="section-title">
                <i class="fas fa-database"></i>
                إعدادات قاعدة البيانات
            </h3>
            
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i>
                تأكد من تشغيل خادم MySQL قبل المتابعة (XAMPP Control Panel)
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="test_connection">
                
                <div class="form-group">
                    <label>عنوان الخادم (Host)</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" name="db_user" value="root" required>
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" name="db_pass" value="">
                    </div>
                </div>
                
                <div class="buttons">
                    <a href="install.php?step=1" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i>
                        السابق
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plug"></i>
                        اختبار الاتصال
                    </button>
                </div>
            </form>
            
            <?php elseif ($step === 3): ?>
            <!-- الخطوة 3: إعدادات النظام -->
            <h3 class="section-title">
                <i class="fas fa-cog"></i>
                إعدادات النظام
            </h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="install">
                
                <div class="form-section-title">
                    <i class="fas fa-database"></i> قاعدة البيانات
                </div>
                
                <div class="form-group">
                    <label>اسم قاعدة البيانات</label>
                    <input type="text" name="db_name" value="pharmacy_pos" required>
                </div>
                
                <div class="divider"></div>
                
                <div class="form-section-title">
                    <i class="fas fa-store"></i> معلومات الصيدلية
                </div>
                
                <div class="form-group">
                    <label>اسم الصيدلية</label>
                    <input type="text" name="pharmacy_name" value="صيدليات الحياة" required>
                </div>
                
                <div class="divider"></div>
                
                <div class="form-section-title">
                    <i class="fas fa-user-shield"></i> حساب المدير
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" name="admin_user" value="admin" required>
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="text" name="admin_pass" value="admin123" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="admin_email" value="admin@pharmacy.com" required>
                    </div>
                    <div class="form-group">
                        <label>رقم الهاتف</label>
                        <input type="text" name="admin_phone" value="01001234567" required>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>تحذير:</strong> سيتم حذف قاعدة البيانات إذا كانت موجودة مسبقاً!
                </div>
                
                <div class="buttons">
                    <a href="install.php?step=2" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i>
                        السابق
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download"></i>
                        بدء التثبيت
                    </button>
                </div>
            </form>
            
            <?php elseif ($step === 4): ?>
            <!-- الخطوة 4: اكتمال التثبيت -->
            <?php 
            $installData = $_SESSION['install_data'] ?? ['admin_user' => 'admin', 'admin_pass' => 'admin123'];
            ?>
            <div class="success-animation">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2>تم التثبيت بنجاح! 🎉</h2>
                <p>تهانينا! تم تثبيت نظام صيدليات الحياة بنجاح</p>
                
                <div class="credentials-box">
                    <h4><i class="fas fa-key"></i> بيانات تسجيل الدخول</h4>
                    <div class="credential-item">
                        <span class="credential-label">اسم المستخدم:</span>
                        <span class="credential-value"><?php echo htmlspecialchars($installData['admin_user']); ?></span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">كلمة المرور:</span>
                        <span class="credential-value"><?php echo htmlspecialchars($installData['admin_pass']); ?></span>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-shield-alt"></i>
                    يُنصح بشدة بتغيير كلمة المرور الافتراضية بعد تسجيل الدخول لأول مرة
                </div>
                
                <div class="tables-info" style="text-align: right;">
                    <h5><i class="fas fa-check-circle" style="color: #10b981;"></i> ما تم إنشاؤه:</h5>
                    <ul style="list-style: none; padding: 0; margin-top: 10px; font-size: 14px; color: #475569;">
                        <li style="padding: 5px 0;"><i class="fas fa-check" style="color: #10b981; margin-left: 8px;"></i> قاعدة بيانات pharmacy_pos</li>
                        <li style="padding: 5px 0;"><i class="fas fa-check" style="color: #10b981; margin-left: 8px;"></i> 13 جدول لإدارة النظام</li>
                        <li style="padding: 5px 0;"><i class="fas fa-check" style="color: #10b981; margin-left: 8px;"></i> 3 مستخدمين (admin, manager1, cashier1)</li>
                        <li style="padding: 5px 0;"><i class="fas fa-check" style="color: #10b981; margin-left: 8px;"></i> 5 فئات افتراضية</li>
                        <li style="padding: 5px 0;"><i class="fas fa-check" style="color: #10b981; margin-left: 8px;"></i> 8 منتجات تجريبية</li>
                        <li style="padding: 5px 0;"><i class="fas fa-check" style="color: #10b981; margin-left: 8px;"></i> 3 موردين</li>
                        <li style="padding: 5px 0;"><i class="fas fa-check" style="color: #10b981; margin-left: 8px;"></i> إعدادات النظام الأساسية</li>
                    </ul>
                </div>
                
                <div class="buttons" style="justify-content: center; gap: 15px;">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        تسجيل الدخول
                    </a>
                    <a href="admin/dashboard.html" class="btn btn-success">
                        <i class="fas fa-tachometer-alt"></i>
                        لوحة التحكم
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
