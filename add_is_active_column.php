<?php
require_once 'config.php';

// التحقق من وجود عمود is_active
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'is_active'");

if ($result->num_rows == 0) {
    // إضافة العمود
    $sql = "ALTER TABLE products ADD COLUMN is_active BOOLEAN DEFAULT TRUE";
    if ($conn->query($sql)) {
        echo "تم إضافة عمود is_active بنجاح!";
    } else {
        echo "خطأ: " . $conn->error;
    }
} else {
    echo "عمود is_active موجود مسبقاً";
}
?>
