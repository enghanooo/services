<?php
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

// الحصول على البريد الإلكتروني من الطلب
$email = isset($_GET['email']) ? $_GET['email'] : '';

// التحقق من وجود البريد الإلكتروني
if (!empty($email)) {
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // إذا كان البريد موجودًا
        echo json_encode(['exists' => true]);
    } else {
        // إذا لم يكن موجودًا
        echo json_encode(['exists' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['exists' => false]);
}

$conn->close();
?>
