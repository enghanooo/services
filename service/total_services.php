<?php
header('Content-Type: application/json');
include "connect.php"; 

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $pdo->prepare("
    SELECT 
    r.type,
    r.name ,
    r.description,
    r.Discount,
    r.img,
    sr.price_per_hour,
    sr.rating,
    sr.title,
    COUNT(o.ID) AS total_orders
FROM 
    recommended_service r
JOIN 
    orders o ON o.service_id = r.id
LEFT JOIN 
    services_request sr ON sr.U_ID = r.ID 
WHERE 
    r.u_id = ?
GROUP BY 
    r.id

HAVING 
    total_orders >= 5;
        
    ");
    
    if ($stmt->execute([$id])) {
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } else {
        echo json_encode(["error" => "Query failed"]);
    }
} else {
    echo json_encode(["error" => "Invalid or missing ID"]);
}
