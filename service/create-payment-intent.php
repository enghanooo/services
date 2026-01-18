<?php
require 'vendor/autoload.php'; // تأكد أنك مثبت stripe عبر composer

\Stripe\Stripe::setApiKey('sk_test_51RJVKBQnuxexJHFP4J0T8J1j0WSImne04tnxMMdC2Izo1V7H8uTqRjpn4PehNrPKskQtFT0aTUqMQcefHa5a2VW500OTArRNDN'); // secret key (آمن ولا يُستخدم في Flutter)

header('Content-Type: application/json');

// استقبل المبلغ من التطبيق (مثال: 5000 = 50 ريال)
$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// إنشاء PaymentIntent
$intent = \Stripe\PaymentIntent::create([
    'amount' => $amount,
    'currency' => 'sar', // الريال السعودي
    'payment_method_types' => ['card'],
]);

echo json_encode([
    'clientSecret' => $intent->client_secret,
]);
