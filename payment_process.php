<?php
require 'vendor/autoload.php'; 
require 'db.php'; 

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$plan = $_GET['plan'];

$stripe = new \Stripe\StripeClient('sk_test_51QNWQKG2zxFLmtj9w1HsGLgAkVByklUMkMC59EYOk9A2XNaL5azhcTTlFT2LE5oJMkYPxOysXU4cdJidanITC70n00S49ksdJ4'); // Use your secret key

$amount = $plan === 'monthly' ? 999 : 10080;
$description = $plan === 'monthly' ? 'Monthly Premium Plan' : 'Annual Premium Plan';

// Create a checkout session
$checkout_session = $stripe->checkout->sessions->create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'lkr',
            'product_data' => [
                'name' => $description,
            ],
            'unit_amount' => $amount,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/petiverse/payment_success.php?session_id={CHECKOUT_SESSION_ID}&plan='.$plan,
    'cancel_url' => 'http://localhost/petiverse/subscription.php',
]);

header('Location: ' . $checkout_session->url);
exit;
?>
