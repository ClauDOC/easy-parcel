<?php
function execute_easy_parcel_api_call($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ));
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($result === false || $httpCode !== 200) {
        return false;
    }
    return json_decode($result, true);
}

function salva_dati_ordine($requestData, $responseData) {
    global $wpdb;
    
    $senderData = array(
        'lastname' => $requestData['mittente']['nominativo'],
        'firstname' => '',
        'email' => $requestData['mittente']['email'],
        'phone' => $requestData['mittente']['telefono'],
        'address' => $requestData['mittente']['indirizzo'],
        'city' => $requestData['mittente']['city'] ?? '',
        'country' => $requestData['mittente']['country'] ?? '',
        'cap' => $requestData['mittente']['cap'] ?? '',
        'locality' => $requestData['mittente']['locality'] ?? '',
        'province' => $requestData['mittente']['province'] ?? ''
    );
    $wpdb->insert($wpdb->prefix . 'senders', $senderData);
    $senderId = $wpdb->insert_id;
    
    $recipientData = array(
        'lastname' => $requestData['destinatario']['nominativo'],
        'firstname' => '',
        'email' => $requestData['destinatario']['email'],
        'phone' => $requestData['destinatario']['telefono'],
        'address' => $requestData['destinatario']['indirizzo'],
        'city' => $requestData['destinatario']['city'] ?? '',
        'country' => $requestData['destinatario']['country'] ?? ''
    );
    $wpdb->insert($wpdb->prefix . 'recipients', $recipientData);
    $recipientId = $wpdb->insert_id;
    
    $orderDetailData = array(
        'weight' => $requestData['dettagli']['weight'] ?? 0,
        'width' => $requestData['dettagli']['width'] ?? 0,
        'depth' => $requestData['dettagli']['depth'] ?? 0,
        'height' => $requestData['dettagli']['height'] ?? 0,
        'nr_packages' => $requestData['dettagli']['nr_packages'] ?? 1
    );
    $wpdb->insert($wpdb->prefix . 'order_details', $orderDetailData);
    $orderDetailId = $wpdb->insert_id;
    
    $orderData = array(
        'sender_id' => $senderId,
        'recipient_id' => $recipientId,
        'order_detail_id' => $orderDetailId,
        'easy_parcel_order_id' => $responseData['id_ordine'],
        'order_date' => current_time('mysql'),
        'status' => 'pending',
        'total_price' => $responseData['importo'] ?? 0,
        'waybill_number' => '',
        'waybill_url' => '',
        'pickup_code' => '',
        'bordero_url' => ''
    );
    $wpdb->insert($wpdb->prefix . 'orders', $orderData);
    $orderId = $wpdb->insert_id;
    
    return $orderId;
}

function aggiorna_dati_lettera_di_vettura($orderId, $waybillData) {
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'orders',
        array(
            'waybill_number' => $waybillData['waybill_number'] ?? '',
            'waybill_url' => $waybillData['waybill_url'] ?? '',
            'pickup_code' => $waybillData['pickup_code'] ?? '',
            'bordero_url' => $waybillData['bordero_url'] ?? ''
        ),
        array('order_id' => $orderId)
    );
}

// Recupera l'input JSON dalla richiesta
$jsonRequest = file_get_contents('php://input');
$requestData = json_decode($jsonRequest, true);

// URL dell'API di Easy Parcel
$easyparcel_api_url = "https://api.easyparcel.it/order/";

// Effettua la chiamata per l'ordine
$orderResponse = execute_easy_parcel_api_call($easyparcel_api_url, $requestData);

// Verifica se la chiamata dell'ordine ha avuto successo
if ($orderResponse === false || $orderResponse['result'] !== 'OK') {
    http_response_code(500);
    die(json_encode(array('result' => 'KO', 'message' => 'Errore nell\'esecuzione dell\'ordine')));
}

// Salva i dati dell'ordine nel database
$orderId = salva_dati_ordine($requestData, $orderResponse);

// Prepara i dati per ottenere la lettera di vettura
$waybillRequestData = array(
    'call' => 'getwaybill',
    'details' => array(
        'order_id' => $orderResponse['id_ordine'],
        'waybill_base64' => 'N'
    )
);

// URL dell'API per ottenere la lettera di vettura
$easyparcel_waybill_url = "https://api.easyparcel.it/getwaybill/";

// Effettua la chiamata per ottenere la lettera di vettura
$waybillResponse = execute_easy_parcel_api_call($easyparcel_waybill_url, $waybillRequestData);

// Verifica se la chiamata della lettera di vettura ha avuto successo
if ($waybillResponse === false || $waybillResponse['response']['result'] !== 'OK') {
    http_response_code(500);
    die(json_encode(array('result' => 'KO', 'message' => 'Errore nell\'ottenimento della lettera di vettura')));
}

// Aggiorna l'ordine con i dati della lettera di vettura
aggiorna_dati_lettera_di_vettura($orderId, $waybillResponse['response']);

// Restituisci la risposta JSON completa
http_response_code(200);
die(json_encode(array('result' => 'OK', 'message' => 'Ordine completato con successo')));
?>