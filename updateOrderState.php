<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Cache-Control: max-age=60, public");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "يجب استخدام طريقة POST فقط"]);
    exit();
}

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

try {
    if (isset($_POST['ID']) && isset($_POST['state_order']) && is_numeric($_POST['ID']) && is_numeric($_POST['state_order'])) {
        $id = (int) $_POST['ID'];
        $state_order = (int) $_POST['state_order'];

        // تحقق من وجود الطلب
        $checkStmt = $pdo->prepare("SELECT * FROM orders WHERE user_ID = ?");
        $checkStmt->execute([$id]);

        if ($checkStmt->rowCount() == 0) {
            echo json_encode(["error" => "الطلب غير موجود"]);
            exit();
        }

        // تحديث الحالة
        $stmt = $pdo->prepare("UPDATE orders SET state_order = ? WHERE user_ID = ?");
        $stmt->execute([$state_order, $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => "تم تحديث الطلب بنجاح"]);
        } else {
            echo json_encode(["error" => "لم يتم التعديل (ربما نفس القيمة)"]);
        }
    } else {
        echo json_encode(["error" => "لا يوجد معرّف أو حالة طلب صحيحة"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "خطأ في الخادم: " . $e->getMessage()]);
}
?>
