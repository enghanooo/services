<?php
// استيراد الإتصال بقاعدة البيانات
// الاتصال بقاعدة البيانات
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// الحصول على البيانات من الـ API (POST Request)
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من وجود البيانات
if (isset($data['email']) && isset($data['new_password'])) {
    $email = $data['email'];
    $new_password = $data['new_password'];

    // تحقق من أن البريد الإلكتروني موجود في قاعدة البيانات
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // البريد الإلكتروني موجود في قاعدة البيانات، تحديث كلمة المرور
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT); // استخدام التجزئة لزيادة الأمان

        $update_query = "UPDATE users SET password = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $hashed_password, $email);

        if ($update_stmt->execute()) {
            // إذا تم التحديث بنجاح
            echo json_encode(["status" => "success", "message" => "Password updated successfully."]);
        } else {
            // في حالة فشل التحديث
            echo json_encode(["status" => "error", "message" => "Failed to update password."]);
        }
    } else {
        // إذا لم يتم العثور على البريد الإلكتروني في قاعدة البيانات
        echo json_encode(["status" => "error", "message" => "Email not found."]);
    }
} else {
    // إذا لم يتم إرسال البيانات
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>