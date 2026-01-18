<?php
header('Content-Type: application/json'); // مهم لتحديد نوع الإخراج
ini_set('display_errors', 1); // عرض الأخطاء
error_reporting(E_ALL); // تقرير بجميع الأخطاء

$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$order_id = $_POST['order_id'];
$user_id = $_POST['user_id'];
$quantity = $_POST['quantity'];
$total = $_POST['total']; // ← اجمالي السعر الجديد

$stmt = $conn->prepare("UPDATE orders SET quantity = ?, total_price = ? WHERE ID = ? AND user_ID = ?");
$stmt->bind_param("iiii", $quantity,$total, $order_id, $user_id); // ← ربط القيم
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "fail"]);
}
?>
