<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// الاتصال بقاعدة البيانات
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';
date_default_timezone_set('Asia/Riyadh');
$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
// التحقق من الاتصال بقاعدة البيانات
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// استخراج بيانات الشكوى
$complainant = $_POST['complainant'];
$sp_id = $_POST['SP_ID'];
$user_id = $_POST['user_ID'];
$u_id = $_POST['u_id'];

// التأكد من أن البيانات ليست فارغة
if (empty($complainant) || empty($sp_id) || empty($user_id) ) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

$sql = "INSERT INTO complainants (complainant, SP_ID, user_ID, U_ID, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?)";

$now = date("Y-m-d H:i:s");  // يتم توليد الوقت بالتوقيت المحلي للسعودية

$stmt = $conn->prepare($sql);
$stmt->bind_param("siiiss", $complainant, $sp_id, $user_id, $u_id, $now, $now);


if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Complaint submitted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to submit complaint"]);
}

$stmt->close();
$conn->close();
?>
