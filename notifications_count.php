<?php
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';
$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
// التحقق من الاتصال
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
$userId = $_GET['user_id'];
$query = "SELECT COUNT(*) as count FROM notifications WHERE user_ID = '$userId' AND is_read = 0"; 
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

echo json_encode(['count' => $data['count']]);
?>
