<?php
header('Content-Type: application/json');

include "connect.php"; 


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("
    SELECT *
        FROM recommended_service 
        WHERE u_id = ? order by id desc
   ");
    $stmt->execute([$id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} else {
    echo json_encode(["error" => "No ID provided"]);
}