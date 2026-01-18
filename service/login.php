<?php
header("Content-Type: application/json");

// Database connection
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()
    ]);
    exit();
}

// استلام البيانات القادمة من التطبيق
$data = json_decode(file_get_contents("php://input"), true);



    $email = $data['email'];
    $password = $data['password'];

    // استعلام التحقق من المستخدم
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email ");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // التحقق من حالة الحساب
        if ($user['status'] != 1) {
            echo json_encode([
                'success' => false,
                'message' => 'حالة الحساب غير مفعلة بعد، يرجى المحاولة لاحقًا'
            ]);
            exit();
        }

        // التحقق من كلمة المرور
        if (password_verify($password, $user['password'])) {
            echo json_encode([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'data'    => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'fName'  => $user['fName'],  // إضافة اسم المستخدم
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'بيانات الدخول غير صحيحة أو النوع غير متطابق'
        ]);
    }

?>
