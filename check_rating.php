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
$subServiceID = intval($_GET['subService_ID'] ?? 0);
$userID = intval($_GET['user_ID'] ?? 0);

$stmt = $conn->prepare("SELECT ID_CO FROM comments WHERE subService_ID = ? AND user_ID = ?");
$stmt->bind_param("ii", $subServiceID, $userID);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode([
    'hasRated' => $result->num_rows > 0
]);

$stmt->close();
$conn->close();
?>