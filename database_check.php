<?php
/**
 * Database Check Script
 * التحقق من قاعدة البيانات والجداول والبيانات
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'pharmacy_pos');

// إضافة headers في البداية
header('Content-Type: text/html; charset=utf-8');

// الاتصال
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

if ($conn->connect_error) {
    die("<h1>❌ خطأ الاتصال</h1>" . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

echo "<h1>🔍 فحص قاعدة البيانات والجداول</h1>";
echo "<style>
    body { font-family: Arial; direction: rtl; padding: 20px; background: #f5f5f5; }
    .ok { color: green; padding: 10px; background: #d4edda; border: 1px solid #28a745; border-radius: 5px; margin: 10px 0; }
    .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
    .info { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { padding: 10px; text-align: right; border: 1px solid #ddd; }
    th { background: #667eea; color: white; }
</style>";

// 1. التحقق من قاعدة البيانات
echo "<h2>1️⃣ قاعدة البيانات</h2>";
$db_result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
if ($db_result && $db_result->num_rows > 0) {
    echo "<div class='ok'>✅ قاعدة البيانات <strong>" . DB_NAME . "</strong> موجودة</div>";
    // اختر القاعدة
    $conn->select_db(DB_NAME);
} else {
    echo "<div class='error'>❌ قاعدة البيانات <strong>" . DB_NAME . "</strong> غير موجودة</div>";
    echo "<div class='info'>⚠️ قم باستيراد ملف database.sql من phpMyAdmin</div>";
    exit;
}

// 2. التحقق من جدول users
echo "<h2>2️⃣ جدول Users</h2>";
$table_result = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_result && $table_result->num_rows > 0) {
    echo "<div class='ok'>✅ جدول <strong>users</strong> موجود</div>";
} else {
    echo "<div class='error'>❌ جدول <strong>users</strong> غير موجود</div>";
    echo "<div class='info'>⚠️ قم باستيراد ملف database.sql من phpMyAdmin</div>";
    exit;
}

// 3. عدد المستخدمين
echo "<h2>3️⃣ بيانات المستخدمين</h2>";
$users_result = $conn->query("SELECT id, username, full_name, role FROM users");
if ($users_result) {
    $count = $users_result->num_rows;
    if ($count > 0) {
        echo "<div class='ok'>✅ عدد المستخدمين: <strong>$count</strong></div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>اسم المستخدم</th><th>الاسم الكامل</th><th>الدور</th></tr>";
        while ($row = $users_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ لا توجد بيانات مستخدمين في الجدول</div>";
        echo "<div class='info'>⚠️ قم باستيراد ملف database.sql من phpMyAdmin (افتح ملف database.sql واختر SQL وشغل DELETE FROM users أولاً)</div>";
    }
} else {
    echo "<div class='error'>❌ خطأ في استعلام المستخدمين: " . $conn->error . "</div>";
}

// 4. اختبر بيانات محددة
echo "<h2>4️⃣ اختبر المستخدم admin</h2>";
$test_result = $conn->query("SELECT * FROM users WHERE username = 'admin' AND is_active = TRUE");
if ($test_result && $test_result->num_rows > 0) {
    $user = $test_result->fetch_assoc();
    echo "<div class='ok'>✅ المستخدم <strong>admin</strong> موجود ونشط</div>";
    echo "<div class='info'>";
    echo "اسم المستخدم: <strong>" . htmlspecialchars($user['username']) . "</strong><br>";
    echo "كلمة المرور المحفوظة: <strong>" . htmlspecialchars($user['password']) . "</strong><br>";
    echo "الدور: <strong>" . htmlspecialchars($user['role']) . "</strong>";
    echo "</div>";
    
    // اختبر مقارنة كلمة المرور
    echo "<h3>اختبار كلمة المرور</h3>";
    $test_password = 'admin123';
    if ($user['password'] === $test_password) {
        echo "<div class='ok'>✅ كلمة المرور صحيحة!</div>";
    } else {
        echo "<div class='error'>❌ كلمة المرور غير متطابقة</div>";
        echo "<div class='info'>المحفوظة: <code>" . htmlspecialchars($user['password']) . "</code><br>";
        echo "المدخلة: <code>$test_password</code></div>";
    }
} else {
    echo "<div class='error'>❌ المستخدم <strong>admin</strong> غير موجود</div>";
}

// 5. الاتصال API
echo "<h2>5️⃣ اختبر الاتصال بـ API</h2>";
echo "<div class='info'>";
echo "جرب هذا الرابط في المتصفح:<br>";
echo "<code>http://localhost/phaa/api.php?action=check_session</code><br><br>";
echo "إذا ظهرت رسالة JSON بدون أخطاء HTML، فالاتصال سليم ✓";
echo "</div>";

$conn->close();
echo "<h2>✅ انتهى الفحص</h2>";
?>
