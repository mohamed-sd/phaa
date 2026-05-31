
<?php
// Enable error reporting for debugging (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Include config for DB connection and settings
require_once __DIR__ . '/config.php';

// جلب اسم الصيدلية من قاعدة البيانات
$pharmacyName = 'صيدليات الحياة';
$pharmacyDescription = 'نظام إدارة الصيدليات';

try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT setting_key, setting_value FROM pharmacy_settings WHERE setting_key IN ('pharmacy_name', 'description')");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['setting_key'] === 'pharmacy_name') {
                $pharmacyName = $row['setting_value'];
            } elseif ($row['setting_key'] === 'description') {
                $pharmacyDescription = $row['setting_value'];
            }
        }
    }
    // Don't close $conn here; config.php manages connection
} catch (Exception $e) {
    // استخدام القيم الافتراضية
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>تسجيل الدخول - <?php echo htmlspecialchars($pharmacyName); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #22d3ee 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* خلفية متحركة */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255,255,255,0.05) 0%, transparent 30%);
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-5%, 5%) rotate(5deg); }
            50% { transform: translate(5%, -5%) rotate(-5deg); }
            75% { transform: translate(-3%, -3%) rotate(3deg); }
        }
        
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            padding: 40px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0; 
                transform: translateY(40px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: white;
            box-shadow: 0 10px 30px rgba(8, 145, 178, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .header h1 {
            color: #0f172a;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header p {
            color: #64748b;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #374151;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .form-group label i {
            color: #0891b2;
            font-size: 14px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 14px 16px;
            padding-right: 45px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #f9fafb;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #0891b2;
            background: white;
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
        }
        
        .input-wrapper .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            transition: color 0.3s ease;
        }
        
        .input-wrapper input:focus + .input-icon {
            color: #0891b2;
        }
        
        .toggle-password {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            font-size: 16px;
            transition: color 0.3s ease;
        }
        
        .toggle-password:hover {
            color: #0891b2;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(8, 145, 178, 0.4);
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(8, 145, 178, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-login .spinner {
            display: none;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .btn-login.loading .spinner {
            display: inline-block;
        }
        
        .btn-login.loading .btn-text {
            display: none;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .alert.show {
            display: flex;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            animation: none;
        }
        
        .copyright {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
        }
        
        .copyright a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .copyright a:hover {
            text-decoration: underline;
        }
        
        /* تأثيرات إضافية */
        .features {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
        }
        
        .feature {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: #64748b;
            font-size: 12px;
        }
        
        .feature i {
            font-size: 20px;
            color: #0891b2;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 25px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            .features {
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="header">
                <div class="logo-icon">
                    <i class="fas fa-prescription-bottle-medical"></i>
                </div>
                <h1><?php echo htmlspecialchars($pharmacyName); ?></h1>
                <p><?php echo htmlspecialchars($pharmacyDescription); ?></p>
            </div>

            <form id="loginForm">
                <div id="errorMsg" class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span></span>
                </div>
                <div id="successMsg" class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span></span>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-user"></i>
                        اسم المستخدم
                    </label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required autocomplete="username">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-lock"></i>
                        كلمة المرور
                    </label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required autocomplete="current-password">
                        <i class="fas fa-lock input-icon"></i>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <i class="fas fa-spinner spinner"></i>
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt"></i>
                        تسجيل الدخول
                    </span>
                </button>
            </form>

            <div class="features">
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>آمن</span>
                </div>
                <div class="feature">
                    <i class="fas fa-bolt"></i>
                    <span>سريع</span>
                </div>
                <div class="feature">
                    <i class="fas fa-cloud"></i>
                    <span>سحابي</span>
                </div>
            </div>
        </div>

        <div class="copyright">
            <p>© 2026 <a href="#">ITX</a> - جميع الحقوق محفوظة</p>
        </div>
    </div>

    <script>
        const API_BASE = './api.php';

        // إظهار/إخفاء كلمة المرور
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        async function callAPI(action, method = 'POST', data = null) {
            const url = `${API_BASE}?action=${action}`;
            const opts = { method, credentials: 'include' };
            if (method === 'POST' && data) {
                opts.headers = { 'Content-Type': 'application/json' };
                opts.body = JSON.stringify(data);
            }
            const res = await fetch(url, opts);
            return await res.json();
        }

        // التحقق من وجود جلسة نشطة
        async function checkExistingSession() {
            try {
                const res = await callAPI('check_session', 'GET');
                if (res.success) {
                    const user = res.data;
                    if (user.role === 'admin' || user.role === 'manager') {
                        window.location.href = './admin/dashboard.html';
                    } else {
                        window.location.href = './index.html';
                    }
                }
            } catch (err) {
                // لا توجد جلسة نشطة
            }
        }

        // معالجة نموذج تسجيل الدخول
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const errorMsg = document.getElementById('errorMsg');
            const successMsg = document.getElementById('successMsg');
            const submitBtn = document.getElementById('submitBtn');

            // إخفاء الرسائل
            errorMsg.classList.remove('show');
            successMsg.classList.remove('show');

            if (!username || !password) {
                errorMsg.querySelector('span').textContent = 'الرجاء ملء جميع الحقول';
                errorMsg.classList.add('show');
                return;
            }

            try {
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');

                const response = await callAPI('login', 'POST', { username, password });

                if (response.success) {
                    successMsg.querySelector('span').textContent = 'تم تسجيل الدخول بنجاح. جاري التحويل...';
                    successMsg.classList.add('show');
                    
                    localStorage.setItem('auth_user', JSON.stringify(response.data));
                    
                    const redirectUrl = (response.data.role === 'admin' || response.data.role === 'manager') 
                        ? './admin/dashboard.html' 
                        : './index.html';
                    
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1000);
                } else {
                    errorMsg.querySelector('span').textContent = response.message || 'خطأ في تسجيل الدخول';
                    errorMsg.classList.add('show');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                }
            } catch (err) {
                errorMsg.querySelector('span').textContent = 'خطأ في الاتصال بالخادم';
                errorMsg.classList.add('show');
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
            }
        });

        // التحقق من الجلسة عند تحميل الصفحة
        checkExistingSession();
        
        // التركيز على حقل اسم المستخدم
        document.getElementById('username').focus();
    </script>
</body>
</html>
