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
    die(json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]));
}

// التحقق من وجود subService_ID في الطلب
if (!isset($_GET['subService_ID'])) {
    echo json_encode(["success" => false, "error" => "subService_ID parameter is missing"]);
    exit();
}

$subServiceID = intval($_GET['subService_ID']);

// تحضير الاستعلام
$stmt = $conn->prepare("SELECT c.comment, c.created_at, u.name, u.avatar, c.rating stars
                        FROM comments c
                        JOIN users_flutter u ON c.user_ID = u.id
                        WHERE c.subService_ID = ?
                        order by c.created_at desc");
$stmt->bind_param("i", $subServiceID);

// تنفيذ الاستعلام
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "Failed to execute query"]);
    exit();
}

$result = $stmt->get_result();

// جمع التعليقات
$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

// إرجاع النتيجة بتنسيق JSON
echo json_encode(["success" => true, "comments" => $comments]);

$stmt->close();
$conn->close();
?>
