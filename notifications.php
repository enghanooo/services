<?php
header('Content-Type: application/json');
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';
$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT 
                notifications.*,
                services_request.title AS service_name
            FROM 
                notifications
            LEFT JOIN 
                services_request 
            ON 
                notifications.SP_ID = services_request.id
            WHERE 
                notifications.user_ID = $id
            ORDER BY 
                notifications.created_at DESC";

    $result = $conn->query($sql);

    $notifications = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        echo json_encode($notifications);
    } else {
        echo json_encode(array("message" => "No notifications found"));
    }
} else {
    echo json_encode(["error" => "No ID provided"]);
}

$conn->close();
