<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// الاتصال بقاعدة البيانات
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';
date_default_timezone_set('Asia/Riyadh');
$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
// التحقق من الاتصال بقاعدة البيانات
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $comment = $_POST['comment'] ?? '';
        $subServiceID = intval($_POST['subService_ID'] ?? 0);
        $userID = intval($_POST['user_ID'] ?? 0);
        $rating = floatval($_POST['rating'] ?? 0);
        $U_ID = floatval($_POST['U_ID'] ?? 0);

        // ✅ 1. إدخال التقييم الجديد في جدول comments
        $stmt = $conn->prepare("INSERT INTO comments (comment, subService_ID, user_ID, rating,U_ID,created_at, updated_at) VALUES (?, ?, ?, ?,?,?,?)");
  
$now = date("Y-m-d H:i:s");  // يتم توليد الوقت بالتوقيت المحلي للسعودية


        $stmt->bind_param("siiiiss", $comment, $subServiceID, $userID, $rating,$U_ID, $now, $now);
        $stmt->execute();
        $stmt->close();

        // ✅ 2. حساب متوسط التقييم الجديد لهذه الخدمة
        $query = "SELECT AVG(rating) as avg_rating FROM comments WHERE subService_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $subServiceID);
        $stmt->execute();
        $result = $stmt->get_result();
        $avg_rating = $result->fetch_assoc()['avg_rating'];
        $stmt->close();

        // ✅ 3. تحديث متوسط التقييم في جدول الخدمات
        $update = $conn->prepare("UPDATE services_request SET rating = ? WHERE id = ?");
        $update->bind_param("di", $avg_rating, $subServiceID);
        $update->execute();
        $update->close();

        // ✅ 4. إرسال الاستجابة بنجاح
        echo json_encode([
            "success" => true,
            "message" => "تمت إضافة التعليق والتقييم بنجاح",
            "new_rating" => $avg_rating
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "خطأ في التنفيذ: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "طلب غير صالح"
    ]);
}

$conn->close();
?>
