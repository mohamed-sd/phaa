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