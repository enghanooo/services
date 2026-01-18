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

if (!empty($_POST['user_id']) && !empty($_POST['message'])) {

    $userID = intval($_POST['user_id']);
    $message = htmlspecialchars(strip_tags($_POST['message']));

    $stmt = $conn->prepare("INSERT INTO support_messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $userID, $message);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Message submitted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to submit message."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
}

$conn->close();
?>
