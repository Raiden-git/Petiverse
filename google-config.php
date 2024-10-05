<?php
require_once 'vendor/autoload.php';

$google_client = new Google_Client();
$google_client->setClientId('102043813846-u9hmv0tqkbueasf1cc0obuo7fb3ko1ph.apps.googleusercontent.com');
$google_client->setClientSecret('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');
$google_client->setRedirectUri('http://localhost/Petiverse/google_callback.php');
$google_client->addScope('email');
$google_client->addScope('profile');
?>
