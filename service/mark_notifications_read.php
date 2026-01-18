<?php
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'";
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
