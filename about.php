<?php
header('Content-Type: application/json');

// الاتصال بقاعدة البيانات
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

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // استخدام Prepared Statement لتجنب الثغرات الأمنية
    $stmt = $conn->prepare("SELECT 
        about.id, 
        about.description, 
        about.time,
        about.phone,
        users.address, 
        users.city,
        users.longitude,
        users.latitude
    FROM 
        about
    JOIN 
        users 
    ON 
        about.service_id = users.id
    WHERE 
        about.service_id = ?");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} else {
    echo json_encode(["error" => "No ID provided"]);
}

$conn->close();
?>
