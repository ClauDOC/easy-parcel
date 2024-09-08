<?php

$client_id = get_option('paypal_client_id');
$client_secret = get_option('paypal_client_secret');

// Restituisci le credenziali come JSON
header('Content-Type: application/json');
echo json_encode(array(
    'client_id' => $client_id,
    'client_secret' => $client_secret
));
?>