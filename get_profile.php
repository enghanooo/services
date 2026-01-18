<?php
header('Content-Type: application/json');

// إعداد الاتصال بقاعدة البيانات
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';غيّر اسم قاعدة البيانات هنا

$conn = new mysqli($servername, $username, $password, $database);
$conn->set_charset("utf8mb4");
// تحقق من الاتصال
if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

// تأكد أن user_id موجود
if (!isset($_GET['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing user_id parameter."
    ]);
    exit;
}

$user_id = intval($_GET['user_id']);

// استعلام لجلب بيانات المستخدم
$sql = "SELECT `id`, `email`, `password`, `name`, `avatar` 
        FROM `users_flutter`
        WHERE `id` = $user_id
        LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "data" => $row
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);
}

$conn->close();
?>
