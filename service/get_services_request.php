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

    $stmt = $conn->prepare("
        SELECT sr.*, rs.name AS service_name, rs.description, rs.Rating, rs.price, rs.img ,rs.id as comments_back,count(o.subService_ID) as orders_count
        FROM services_request sr
        LEFT JOIN recommended_service rs ON sr.service_id = rs.id
        LEFT JOIN orders o ON sr.id = o.subService_ID 
        WHERE sr.service_id    = ?
        ORDER BY 
            FIELD(sr.status, 'نشط', 'معلّق', 'غير نشط', 'منتهي'), 
            sr.id DESC
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $services = [];
    while($row = $result->fetch_assoc()) {
        $services[] = $row;
    }

    echo json_encode($services);
} else {
    echo json_encode(["error" => "No ID provided"]);
}
?>
