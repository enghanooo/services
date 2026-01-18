<?php
require_once('vendor/autoload.php');
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Stripe secret key (للتعامل مع Stripe في حالة الدفع عبر "مدى")
\Stripe\Stripe::setApiKey('sk_test_51RJVKBQnuxexJHFP4J0T8J1j0WSImne04tnxMMdC2Izo1V7H8uTqRjpn4PehNrPKskQtFT0aTUqMQcefHa5a2VW500OTArRNDN');

// الاتصال بقاعدة البيانات
$host = 'sql300.infinityfree.com';
$dbname = 'if0_40921484_service';
$username = 'if0_40921484';
$password = 'gmiBM2Sbcj9an';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// استقبال البيانات
$card_number = $_POST['card_number'];
$cvv = $_POST['cvv'];
$epired_date = $_POST['epired_date'];
$card_holder = $_POST['card_holder'];
$u_id = $_POST['u_id'];
$total = $_POST['total'];
$service_ids = $_POST['service_id'];
$payment_method = $_POST['payment_method'];
$order_ids = $_POST['order_ids'];

if (!is_array($order_ids)) {
    $order_ids = explode(',', $order_ids);
}
if (!is_array($service_ids)) {
    $service_ids = explode(',', $service_ids);
}

// ✅ التعامل مع الدفع حسب طريقة الدفع
$all_success = true;

if ($payment_method == '2') { // الدفع عبر "مدى" باستخدام Stripe
    try {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $total * 100, 
            'currency' => 'sar',
            'payment_method_types' => ['card'],
            'description' => 'Service payment for user ID ' . $u_id,
        ]);

        // إذا تم الدفع بنجاح عبر Stripe
        foreach ($service_ids as $service_id) {
            $service_id = trim($service_id);

            // حفظ بيانات الدفع عبر "مدى" (Stripe)
            $sql_payment = "INSERT INTO pay_method (card_number, cvv, epired_date, card_holder, u_id, total, service_id) 
                            VALUES ('$card_number', '$cvv', '$epired_date', '$card_holder', '$u_id', '$total', '$service_id')";

            if ($conn->query($sql_payment) !== TRUE) {
                $all_success = false;
                echo "Error saving payment data for service ID $service_id: " . $conn->error . "<br>";
            }

            // تحديث حالة الطلبات بعد الدفع
            foreach ($order_ids as $order_id) {
                $order_id = trim($order_id);
                $sql_update_order = "UPDATE orders SET pay = '$payment_method', state_order = 2 WHERE ID = '$order_id'";
                if (!$conn->query($sql_update_order)) {
                    $all_success = false;
                    echo "Error updating order ID $order_id: " . $conn->error . "<br>";
                }
            }
        }

        if ($all_success) {
            echo json_encode(["status" => "success", "message" => "تم الدفع بنجاح  ."]);
        }

    } catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500); // ← هنا
    echo json_encode(["status" => "error", "message" => "❌ Payment Error: " . $e->getMessage()]);
}

} elseif ($payment_method == '1') { // الدفع عبر "كاش"
    // لا حاجة لإرسال بيانات البطاقة هنا
    foreach ($service_ids as $service_id) {
        $service_id = trim($service_id);

        // حفظ بيانات الدفع عبر "كاش"
        $sql_payment = "INSERT INTO pay_method (card_number, cvv, epired_date, card_holder, u_id, total, service_id) 
                        VALUES ('', '', '', '', '$u_id', '$total', '$service_id')";

        if ($conn->query($sql_payment) !== TRUE) {
            $all_success = false;
            echo "Error saving payment data for service ID $service_id: " . $conn->error . "<br>";
        }

        // تحديث حالة الطلبات بعد الدفع
        foreach ($order_ids as $order_id) {
            $order_id = trim($order_id);
            $sql_update_order = "UPDATE orders SET pay = '$payment_method', state_order = 2 WHERE ID = '$order_id'";
            if (!$conn->query($sql_update_order)) {
                $all_success = false;
                echo "Error updating order ID $order_id: " . $conn->error . "<br>";
            }
        }
    }

    if ($all_success) {
        echo json_encode(["status" => "success", "message" => "تمت العملية بنجاح."]);
    }
}else {
    echo json_encode(["status" => "error", "message" => "طريقة الدفع غير معروفة."]);
}


$conn->close();
?>
