<?php
/////////////////////////////////////
// CHIAMATA cURL in modalità PROXY //
/////////////////////////////////////

// Includi direttamente la chiave API
$apikey = "ffc3586f7a60fc765dc007c7d4e7d53db8861b93f83cc7d94563f08f4b4e6003";

// Cattura l'input JSON dalla richiesta
$jsonRequest = file_get_contents('php://input');

// URL dell'API di Easy Parcel
$_easyparcel_api_url = "https://api.easyparcel.it/quotation/" . $apikey;

// Inizializzazione della sessione cURL
$ch = curl_init($_easyparcel_api_url);

// Impostazioni per la richiesta cURL
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonRequest)
));

// Esecuzione della richiesta cURL e salvataggio della risposta
$jsonResult = curl_exec($ch);

// Verifica se ci sono errori di cURL
if ($jsonResult === false) {
    // Gestisci gli errori di cURL
    $error = curl_error($ch);
    // Restituisci un messaggio di errore
    http_response_code(500);
    die("Errore cURL: " . $error);
}

// Ottieni il codice HTTP della risposta
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Chiudi la sessione cURL
curl_close($ch);

// Imposta lo stato HTTP della risposta
http_response_code($httpCode);

// Restituisci la risposta JSON dell'API di Easy Parcel
die($jsonResult);
?>