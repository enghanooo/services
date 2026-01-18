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
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // استعلام للربط بين service_providers و sub_services باستخدام INNER JOIN الصحيح
    $stmt = $conn->prepare("
        SELECT 
            id AS service_provider_id,
            email,
            password,
            name,
            city,
            address,
            longitude,
            latitude,
            status,
            img,
            avatar,
            created_at AS provider_created_at,
            updated_at AS provider_updated_at

        FROM users sp
        WHERE type  = ?
        ORDER BY id DESC
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }

    echo json_encode($services);

} else {
    echo json_encode(["error" => "No ID provided"]);
}
// ss.SP_ID AS sub_service_SP_ID,

            // ss.price AS sub_service_price,
            // ss.currency AS sub_service_currency,
?>
