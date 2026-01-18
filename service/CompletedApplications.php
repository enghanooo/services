<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Cache-Control: max-age=60, public");

// Database connection
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
    exit();
}

// Validate input
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID parameter is required'
    ]);
    exit();
}

$userId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($userId === false || $userId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid User ID format'
    ]);
    exit();
}

try {
    // Query to get grouped orders by service_id
 $query = "SELECT 
            o.ID order_id,
            o.`service_id`,
            COUNT(*) as order_count,
            MAX(o.created_at) as last_order_date,
            
            rs.`name` as service_name,
            rs.`description`,
            rs.`Rating`,
            rs.`Discount`,
            rs.`price` as service_price,
            rs.`img` as service_image,
            
            GROUP_CONCAT(DISTINCT o.`ID`) as order_ids,
            GROUP_CONCAT(DISTINCT o.`state`) as states,
            GROUP_CONCAT(DISTINCT o.`subService_ID`) as sub_service_ids,

            GROUP_CONCAT(DISTINCT sr.title) as sub_service_titles,
            GROUP_CONCAT(DISTINCT o.type_service) as type_service,
            GROUP_CONCAT(DISTINCT o.comment) as comments,
            GROUP_CONCAT(DISTINCT o.workers_count) as workers_counts,
            GROUP_CONCAT(DISTINCT o.hours_count) as hours_counts,
            SUM(o.total_price) as total_price

          FROM `orders` o
          LEFT JOIN `recommended_service` rs ON o.`service_id` = rs.`id`
          LEFT JOIN `services_request` sr ON o.`subService_ID` = sr.`id`
          WHERE o.`user_ID` = :user_id
            and o.state = 3
          GROUP BY o.`service_id`
          ORDER BY last_order_date DESC";


    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $groupedOrders = $stmt->fetchAll();

    // Additional query to get sub-service details for each group
   // بعد جلب البيانات وقبل إرسالها كـ JSON، يمكنك تطبيق هذه التعديلات
if (!empty($groupedOrders)) {
    foreach ($groupedOrders as &$group) {
        // تنظيف قيم GROUP_CONCAT إذا كانت موجودة
        $fieldsToClean = [
            'order_ids', 'states', 'sub_service_ids',
            'sub_service_titles', 'type_service', 'comments', 'workers_counts', 'hours_counts'
        ];
        
        foreach ($fieldsToClean as $field) {
            if (isset($group[$field])) {
                $group[$field] = str_replace(',', '', $group[$field]);
            }
        }
        
        // معالجة sub_services إذا كانت موجودة
        if (!empty($group['sub_services'])) {
            foreach ($group['sub_services'] as &$subService) {
                // يمكنك إضافة أي تنظيف إضافي هنا إذا لزم الأمر
                unset($subService); // كسر المرجع
            }
        }
    }
    unset($group); // كسر المرجع
}

    // Format response
    $response = [
        'success' => true,
        'count' => count($groupedOrders),
        'orders' => $groupedOrders
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    http_response_code(500);
    
    echo json_encode([
        'success' => false,
        'message' => 'Database query failed',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred',
        'error' => $e->getMessage()
    ]);
}