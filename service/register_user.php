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
        'message' => 'Database connection failed',
        'details' => $e->getMessage()
    ]);
    exit();
}

$name = $_POST['name'];
$email = $_POST['email'];
$provider = $_POST['provider'];
$provider_id = $_POST['provider_id'];
$img = $_POST['img'];

// تحقق هل المستخدم موجود بالبريد أو الـ provider_id
$stmt = $pdo->prepare("SELECT * FROM users_flutter WHERE provider_id = ? OR email = ?");
$stmt->execute([$provider_id, $email]);

if ($stmt->rowCount() == 0) {
    // مستخدم جديد
    try {
        $insert = $pdo->prepare("INSERT INTO users_flutter (name, email, password, provider, provider_id) VALUES (?,?,?,?,?)");
        $insert->execute([$name, $email, '0', $provider, $provider_id]);

        // بعد الإدخال، نجلب بيانات المستخدم من جديد
        $newUser = $pdo->prepare("SELECT id, name AS fName FROM users_flutter WHERE email = ?");
        $newUser->execute([$email]);
        $userData = $newUser->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "status" => "new",
            "message" => "User registered successfully",
            "data" => $userData
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "error" => true,
            "message" => "Database error",
            "details" => $e->getMessage()
        ]);
    }
} else {
    // مستخدم موجود
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "status" => "exists",
        "message" => "User already exists",
        "data" => [
            "id" => $existingUser['id'],
            "fName" => $existingUser['name']
        ]
    ]);
}
?>
