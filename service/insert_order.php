<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';
date_default_timezone_set('Asia/Riyadh');
$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$subService_ID = $_POST['subservice_id'];
$user_ID = $_POST['user_id'];
$workers_count = $_POST['workers'];
$hours_count = $_POST['hours'];
$frequency = $_POST['frequency'];
$need_materials = $_POST['need_materials'];
$total_price = $_POST['total_price'];
$comment = $_POST['comment'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$type_service = $_POST['type_service'];
$service_id = $_POST['service_id'];
$u_id = $_POST['u_id'];

$order_date = date('Y-m-d');
$order_time = date('H:i:s');
$state = 1;
$quantity = 1;

// التحقق إذا كان الطلب موجود بنفس البيانات
$checkStmt = $conn->prepare("SELECT id, quantity FROM orders WHERE 
    subService_ID = ? AND 
    user_ID = ? AND 
    workers_count = ? AND 
    hours_count = ? AND 
    frequency = ? AND 
    need_materials = ? AND 
    comment = ? AND 
    type_service = ? AND
    service_id = ? AND
    state = 1");

$checkStmt->bind_param(
    "iiiisissi",
    $subService_ID,
    $user_ID,
    $workers_count,
    $hours_count,
    $frequency,
    $need_materials,
    $comment,
    $type_service,
    $service_id
);

$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // الطلب موجود، زيد الكمية
    $row = $result->fetch_assoc();
    $existingOrderId = $row['id'];
    $newQuantity = $row['quantity'] + 1;
$now = date("Y-m-d H:i:s");
    $updateStmt = $conn->prepare("UPDATE orders SET quantity = ?, updated_at = ? WHERE id = ?");
    $updateStmt->bind_param("isi", $newQuantity,$now, $existingOrderId);

    if ($updateStmt->execute()) {
        echo json_encode([
            "status" => "success",
            "order_id" => $existingOrderId,
            "new_quantity" => $newQuantity
        ]);
    } else {
        echo json_encode(["error" => "Failed to update order quantity: " . $updateStmt->error]);
    }
} else {
    // الطلب غير موجود، أضف جديد
    $insertStmt = $conn->prepare("INSERT INTO orders 
        (subService_ID, user_ID, workers_count, hours_count, frequency, need_materials, total_price, comment, latitude, 
        longitude, order_date, order_time, created_at, updated_at, state, type_service, service_id, quantity,U_ID) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
$now = date("Y-m-d H:i:s");
    $insertStmt->bind_param(
        "iiiisidsssssssisiii",
        $subService_ID,
        $user_ID,
        $workers_count,
        $hours_count,
        $frequency,
        $need_materials,
        $total_price,
        $comment,
        $latitude,
        $longitude,
        $order_date,
        $order_time,
        $now,
        $now,
        $state,
        $type_service,
        $service_id,
        $quantity,
        $u_id
    );

    if ($insertStmt->execute()) {
        $orderId = $conn->insert_id;
        echo json_encode([
            "status" => "success",
            "order_id" => $orderId
        ]);
    } else {
        echo json_encode(["error" => "Failed to insert order: " . $insertStmt->error]);
    }
}

$conn->close();
?>
