<?php
header("Content-Type: application/json");

$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SELECT `ID_sr`, `name`, `created_at`, `updated_at`, `U_ID`, `img` FROM `services`");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($services as &$row) {
        $row['ID_sr'] = (int)$row['ID_sr'];
        $row['U_ID'] = (int)$row['U_ID'];
    }

    echo json_encode($services ?: ["message" => "No services found"]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
