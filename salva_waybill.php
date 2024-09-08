<?php
// Connessione al database di WordPress
require_once('../../../wp-load.php');
global $wpdb;

// Recupera i dati della waybill dalla richiesta POST
$data = json_decode(file_get_contents('php://input'), true);

// Verifica se i dati sono presenti
if (isset($data['order_id'], $data['waybill_number'], $data['waybill_url'], $data['pickup_code'], $data['bordero_url'])) {
    $order_id = $data['order_id'];
    $waybill_number = $data['waybill_number'];
    $waybill_url = $data['waybill_url'];
    $pickup_code = $data['pickup_code'];
    $bordero_url = $data['bordero_url'];

    // Aggiorna il record dell'ordine con i dettagli della waybill
    $table_name = $wpdb->prefix . 'orders';
    $result = $wpdb->update(
        $table_name,
        array(
            'waybill_number' => $waybill_number,
            'waybill_url' => $waybill_url,
            'pickup_code' => $pickup_code,
            'bordero_url' => $bordero_url
        ),
        array('order_id' => $order_id)
    );

    if ($result !== false) {
        echo json_encode(array('status' => 'success', 'message' => 'Waybill salvata con successo.'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Errore nel salvataggio della waybill.'));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Dati della waybill mancanti.'));
}
?>