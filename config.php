<?php
/**
 * إعدادات قاعدة البيانات
 * تم إنشاؤها تلقائياً بواسطة معالج التثبيت
 * تاريخ التثبيت: 2026-01-31 20:45:09
 */

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pharmacy_pos');
define('DB_CHARSET', 'utf8mb4');

// إعدادات النظام
define('SYSTEM_VERSION', '1.0.0');
define('SYSTEM_NAME', 'صيدليات الحياة');
define('DEBUG_MODE', false);

// إعدادات الجلسة
define('SESSION_LIFETIME', 3600); // ساعة واحدة

// المنطقة الزمنية
date_default_timezone_set('Africa/Cairo');

// إخفاء أخطاء PHP وإرجاعها كـ JSON
error_reporting(0);
ini_set('display_errors', 0);

// اتصال قاعدة البيانات
function getDBConnection() {
    global $conn;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception($conn->connect_error);
        }
        
        $conn->set_charset(DB_CHARSET);
    }
    
    return $conn;
}

// إنشاء الاتصال عند تحميل الملف
$conn = null;
try {
    $conn = getDBConnection();
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
}

// ============================================
// Helper Functions
// ============================================

/**
 * إرجاع استجابة JSON
 */
function json_response($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * تنظيف النص للاستعلامات
 */
function escape_string($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

/**
 * جلب صف واحد من قاعدة البيانات
 */
function get_row($query) {
    global $conn;
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * جلب جميع الصفوف من قاعدة البيانات
 */
function get_all($query) {
    global $conn;
    $result = $conn->query($query);
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * تسجيل الأخطاء
 */
function log_error($message) {
    $logFile = __DIR__ . '/logs/error.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

/**
 * معرّف المستخدم الحالي من الجلسة (أو null إذا لم يسجّل الدخول)
 */
function current_user_id() {
    return isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
}

/**
 * اسم المستخدم الحالي من الجلسة
 */
function current_username() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * تسجيل عملية في سجل التدقيق (audit_log)
 * يُستخدم لجميع العمليات المالية الحرجة: المبيعات، المرتجعات، المدفوعات، التأمين.
 *
 * @param string $action  نوع العملية (مثل: create_invoice, return_invoice, add_payment)
 * @param string $entity  الكيان المتأثر (مثل: invoice, payment, return)
 * @param int|null $entityId  معرّف الكيان
 * @param mixed $details  تفاصيل إضافية (تُحوّل إلى JSON)
 */
function audit_log($action, $entity = null, $entityId = null, $details = null) {
    global $conn;
    // لا نُفشل العملية الأساسية إذا فشل التدقيق
    try {
        // تأكد من وجود الجدول (في حال لم يُشغَّل الترحيل بعد)
        $check = $conn->query("SELECT 1 FROM information_schema.tables
                               WHERE table_schema = DATABASE() AND table_name = 'audit_log'");
        if (!$check || $check->num_rows === 0) {
            return;
        }

        $uid = current_user_id();
        $uidSql = $uid === null ? 'NULL' : intval($uid);
        $username = current_username();
        $usernameSql = $username === null ? 'NULL' : "'" . $conn->real_escape_string($username) . "'";
        $action = $conn->real_escape_string($action);
        $entitySql = $entity === null ? 'NULL' : "'" . $conn->real_escape_string($entity) . "'";
        $entityIdSql = $entityId === null ? 'NULL' : intval($entityId);
        $detailsStr = is_string($details) ? $details : json_encode($details, JSON_UNESCAPED_UNICODE);
        $detailsSql = $details === null ? 'NULL' : "'" . $conn->real_escape_string($detailsStr) . "'";
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $conn->real_escape_string($_SERVER['REMOTE_ADDR']) : '';

        $conn->query("INSERT INTO audit_log (user_id, username, action, entity, entity_id, details, ip_address)
                      VALUES ($uidSql, $usernameSql, '$action', $entitySql, $entityIdSql, $detailsSql, '$ip')");
    } catch (Exception $e) {
        log_error('audit_log failed: ' . $e->getMessage());
    }
}

/**
 * تنفيذ مجموعة من العمليات داخل معاملة قاعدة بيانات (transaction).
 * يضمن الذرّية (atomicity) للعمليات المالية: إما أن تنجح كلها أو تتراجع كلها.
 *
 * @param callable $callback  دالة تنفّذ الاستعلامات؛ يُمرَّر لها اتصال mysqli.
 * @return mixed قيمة الإرجاع من الـ callback
 * @throws Exception يعيد رمي أي استثناء بعد التراجع (rollback)
 */
function db_transaction(callable $callback) {
    global $conn;
    $conn->begin_transaction();
    try {
        $result = $callback($conn);
        $conn->commit();
        return $result;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * تنفيذ استعلام داخل معاملة؛ يرمي استثناء عند الفشل (للاستخدام داخل db_transaction).
 */
function tx_query($query) {
    global $conn;
    if (!$conn->query($query)) {
        throw new Exception('Database Error: ' . $conn->error);
    }
    return true;
}