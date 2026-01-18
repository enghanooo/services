<?php
header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = ['status' => 'error', 'message' => 'حدث خطأ غير متوقع'];

try {
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = $_POST;

    if (
        isset($data['email'], $data['password'], $data['fName'], $data['name'], $data['city'],
              $data['address'], $data['type'], $data['longitude'], $data['latitude'])
    ) {
        $email = $data['email'];
        $password = $data['password'];
        $fName = $data['fName'];
        $name = $data['name'];
        $city = $data['city'];
        $address = $data['address'];
        $type = $data['type'];
        $longitude = $data['longitude'];
        $latitude = $data['latitude'];

        $avatar = "";
        $img = "";

        // رفع صورة avatar
        if (isset($_FILES['avatar'])) {
            $avatarName = uniqid() . "_" . basename($_FILES['avatar']['name']);
            $avatarTmp = $_FILES['avatar']['tmp_name'];
            $avatarPath = "img/$avatarName";

            if (!move_uploaded_file($avatarTmp, $avatarPath)) {
                $response['message'] = 'فشل في رفع الصورة الشخصية';
                echo json_encode($response);
                exit;
            }
            $avatar = $avatarName;
        }

        // رفع صورة img
        if (isset($_FILES['img'])) {
            $imgName = uniqid() . "_" . basename($_FILES['img']['name']);
            $imgTmp = $_FILES['img']['tmp_name'];
            $imgPath = "img/$imgName";

            if (!move_uploaded_file($imgTmp, $imgPath)) {
                $response['message'] = 'فشل في رفع الصورة الثانية';
                echo json_encode($response);
                exit;
            }
            $img = $imgName;
        }

        // التحقق من البريد
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() > 0) {
            $response['message'] = 'البريد الإلكتروني مستخدم مسبقًا';
            echo json_encode($response);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $currentDate = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO users (
    email, password, fName, name, city, address, avatar, img,
    type, longitude, latitude, status, created_at, updated_at
        ) VALUES (
            :email, :password, :fName, :name, :city, :address, :avatar, :img,
            :type, :longitude, :latitude, 0, :created_at, :updated_at
        )");
        
        $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':fName' => $fName,
            ':name' => $name,
            ':city' => $city,
            ':address' => $address,
            ':avatar' => $avatar,
            ':img' => $img, // ✅ هذا هو السطر الناقص
            ':type' => $type,
            ':longitude' => $longitude,
            ':latitude' => $latitude,
            ':created_at' => $currentDate,
            ':updated_at' => $currentDate
        ]);


        $newUserId = $pdo->lastInsertId();

        $response = [
            'status' => 'success',
            'message' => 'تم التسجيل بنجاح',
            'data' => [
                'id' => $newUserId,
                'fName' => $fName,
                'email' => $email
            ]
        ];
    } else {
        $response['message'] = 'بعض الحقول مفقودة';
    }
} catch (Exception $e) {
    $response['message'] = 'خطأ في السيرفر: ' . $e->getMessage();
}

echo json_encode($response);
exit;
