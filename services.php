<?php
header("Content-Type: application/json");
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

// استعلام لاسترجاع الخدمات
$sql = "SELECT `ID_sr`, `name`, `created_at`, `updated_at`, `U_ID`, `img` FROM `services` ";
$result = $conn->query($sql);

$services = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // تأكد من أن القيم الرقمية يتم إرسالها كأرقام وليس نصوص
        $row['ID_sr'] = (int)$row['ID_sr'];
        $row['U_ID'] = (int)$row['U_ID'];
        $services[] = $row;
    }
    echo json_encode($services);
} else {
    echo json_encode(array("message" => "No services found"));
}

$conn->close();

?>
