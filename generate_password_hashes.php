<?php
/**
 * Generate Password Hashes
 * Run this file to generate correct password hashes for database
 */

// Password list
$passwords = [
    'admin' => 'admin123',
    'cashier1' => 'cashier123',
    'cashier2' => 'cashier123',
    'manager' => 'manager123'
];

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>توليد كلمات مرور مشفرة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
        }
        .hash-item {
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .username {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 16px;
        }
        .password {
            color: #27ae60;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .hash {
            background: white;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            color: #2c3e50;
            word-break: break-all;
            cursor: pointer;
            transition: all 0.3s;
        }
        .hash:hover {
            background: #ecf0f1;
        }
        .sql-section {
            margin-top: 40px;
            border-top: 2px solid #e2e8f0;
            padding-top: 20px;
        }
        .sql-code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            line-height: 1.6;
        }
        .note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            color: #856404;
        }
        .copy-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
        }
        .copy-btn:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔐 توليد كلمات المرور المشفرة</h1>";

// Generate and display hashes
echo "<div>";
foreach ($passwords as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "
        <div class='hash-item'>
            <div class='username'>👤 اسم المستخدم: <strong>$username</strong></div>
            <div class='password'>🔑 كلمة المرور: <strong>$password</strong></div>
            <div class='hash' onclick='copyToClipboard(this)'>$hash</div>
            <button class='copy-btn' onclick='copyHash(this)'>نسخ</button>
        </div>";
}
echo "</div>";

// Generate SQL INSERT statement
echo "
        <div class='sql-section'>
            <h2 style='color: #667eea; margin-bottom: 15px;'>✏️ جملة SQL للإدراج</h2>
            <p style='margin-bottom: 10px; color: #555;'>انسخ هذه الجملة وشغلها في phpMyAdmin:</p>
            <div class='sql-code'>";

echo "INSERT INTO users (username, password, full_name, email, phone, role, branch_id, is_active) VALUES\n";
echo "('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'مدير النظام', 'admin@pharmacy.com', '01001234567', 'admin', 1, TRUE),\n";
echo "('cashier1', '" . password_hash('cashier123', PASSWORD_DEFAULT) . "', 'أحمد محمد', 'cashier1@pharmacy.com', '01002345678', 'cashier', 1, TRUE),\n";
echo "('cashier2', '" . password_hash('cashier123', PASSWORD_DEFAULT) . "', 'فاطمة علي', 'cashier2@pharmacy.com', '01003456789', 'cashier', 1, TRUE),\n";
echo "('manager', '" . password_hash('manager123', PASSWORD_DEFAULT) . "', 'محمود حسن', 'manager@pharmacy.com', '01004567890', 'manager', 1, TRUE);";

echo "
            </div>
            <button class='copy-btn' onclick='copySQLCode()' style='width: 100%;'>📋 نسخ جملة SQL كاملة</button>
        </div>";

echo "
        <div class='note'>
            <strong>⚠️ تنبيه مهم:</strong><br>
            1. الهashes أعلاه عشوائية في كل مرة تقوم بتحديث الصفحة<br>
            2. استخدم الـ SQL الموضح أعلاه لتحديث قاعدة البيانات<br>
            3. احذف جميع المستخدمين القدماء أولاً:<br>
            <code style='background: white; padding: 5px;'>DELETE FROM users;</code><br>
            4. ثم شغل جملة SQL الجديدة
        </div>
    </div>

    <script>
        function copyHash(btn) {
            const hash = btn.previousElementSibling.textContent;
            navigator.clipboard.writeText(hash).then(() => {
                const oldText = btn.textContent;
                btn.textContent = '✓ تم النسخ';
                setTimeout(() => {
                    btn.textContent = oldText;
                }, 2000);
            });
        }

        function copySQLCode() {
            const code = document.querySelector('.sql-code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                alert('تم نسخ جملة SQL! الصقها في phpMyAdmin');
            });
        }
    </script>
</body>
</html>";
?>
