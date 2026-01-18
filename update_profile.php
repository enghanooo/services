<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database configuration
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
$conn->set_charset("utf8mb4");
// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}

// Check if user is logged in
if (!isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

// Get POST data
$user_id = $conn->real_escape_string($_POST['user_id']);
$fName = $conn->real_escape_string($_POST['name'] ?? '');
$email = $conn->real_escape_string($_POST['email'] ?? '');

// Validate required fields
if (empty($fName) ) {
    echo json_encode(['success' => false, 'message' => 'Name required']);
    exit();
}

// Handle file upload
$imageName = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ .  '/uploads/profiles/';
    // $uploadDir = __DIR__ .  'app/public/';
    
    // Create directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $imageName = 'profile_' . $user_id . '_' . time() . '.' . $ext;
    $targetPath = $uploadDir . $imageName;
    
    // Move uploaded file
    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => false,
            'message' => 'File upload failed',
            'error' => error_get_last()
        ]);
        exit();
    }
    
    // Remove old image if exists
    $oldImageQuery = "SELECT avatar FROM users_flutter WHERE id = ?";
    $stmt = $conn->prepare($oldImageQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['avatar'])) {
            $oldImagePath = $uploadDir . $row['avatar'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }
    $stmt->close();
}

// Prepare SQL query
$sql = "UPDATE users_flutter SET 
        name = ?,
        email = ?";
        
$params = [$fName, $email];
$types = "ss";



// Add avatar if uploaded
if ($imageName) {
    $sql .= ", avatar = ?";
    $params[] = $imageName;
    $types .= "s";
}

$sql .= " WHERE ID = ?";
$params[] = $user_id;
$types .= "i";

// Execute query
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit();
}

$stmt->bind_param($types, ...$params);
$success = $stmt->execute();

if ($success) {
    // Get updated user data
    $selectSql = "SELECT * FROM users_flutter WHERE id = ?";
    $selectStmt = $conn->prepare($selectSql);
    $selectStmt->bind_param("i", $user_id);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $userData = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => $userData
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>