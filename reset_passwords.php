<?php
/**
 * Reset Users Passwords Script
 * Run this once to set proper hashed passwords
 * Then delete this file for security
 */

require_once 'config.php';

// Passwords to hash
$users = [
    ['username' => 'admin', 'password' => 'admin123', 'full_name' => 'مسؤول النظام', 'email' => 'admin@pharmacy.local', 'role' => 'admin'],
    ['username' => 'cashier1', 'password' => 'cashier123', 'full_name' => 'محمد أحمد', 'email' => 'cashier1@pharmacy.local', 'role' => 'cashier'],
    ['username' => 'cashier2', 'password' => 'cashier123', 'full_name' => 'فاطمة علي', 'email' => 'cashier2@pharmacy.local', 'role' => 'cashier']
];

try {
    global $conn;
    
    // Delete existing users first
    $conn->query("DELETE FROM users WHERE username IN ('admin', 'cashier1', 'cashier2')");
    
    // Insert users with properly hashed passwords
    foreach ($users as $user) {
        $hashed_password = password_hash($user['password'], PASSWORD_BCRYPT);
        $username = $conn->real_escape_string($user['username']);
        $email = $conn->real_escape_string($user['email']);
        $full_name = $conn->real_escape_string($user['full_name']);
        $role = $conn->real_escape_string($user['role']);
        
        $query = "INSERT INTO users (username, password, full_name, email, role, is_active) 
                  VALUES ('$username', '$hashed_password', '$full_name', '$email', '$role', TRUE)";
        
        if ($conn->query($query)) {
            echo "✓ تم إضافة المستخدم: {$user['username']}<br>";
        } else {
            echo "✗ خطأ: {$conn->error}<br>";
        }
    }
    
    echo "<hr>";
    echo "<h3>بيانات المستخدمين الجاهزة للاستخدام:</h3>";
    echo "<ul>";
    foreach ($users as $user) {
        echo "<li><strong>{$user['username']}</strong> - كلمة المرور: <code>{$user['password']}</code></li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>تحديث كلمات المرور</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        h3 { color: #333; }
        li { margin: 8px 0; }
        code { background: #eee; padding: 4px 8px; border-radius: 4px; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
    <h2>تم تحديث كلمات المرور</h2>
    <p>يمكنك الآن تسجيل الدخول باستخدام البيانات أعلاه.</p>
    <p style="color: red;"><strong>⚠️ تنويه أمني:</strong> احذف هذا الملف بعد استخدامه!</p>
</body>
</html>