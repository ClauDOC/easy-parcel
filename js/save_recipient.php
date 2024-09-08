<?php
// Recupera i dati del destinatario dal payload JSON
$recipient_data = json_decode(file_get_contents('php://input'), true);

// Esegui l'operazione di salvataggio dei dati del destinatario nel database o qualsiasi altra operazione necessaria
// Ad esempio, salva i dati nel database utilizzando WordPress
// Includi i file di WordPress per accedere alle funzioni e alle operazioni del database
require_once('../../../wp-load.php');

// Assicurati che i dati del destinatario siano validi prima di salvarli nel database

// Esegui il salvataggio dei dati del destinatario
save_recipient_to_db($recipient_data);

// Invia una risposta JSON di conferma
echo json_encode(['success' => true]);

// Funzione per salvare i dati del destinatario nel database
function save_recipient_to_db($recipient_data) {
    global $wpdb;

    // Nome della tabella dei destinatari nel database WordPress
    $table_name = $wpdb->prefix . 'recipients';

    // Prepara i dati per l'inserimento nel database
    $data = [
        'lastname' => $recipient_data['lastname'],
        'firstname' => $recipient_data['firstname'],
        'address' => $recipient_data['address'],
        'city' => $recipient_data['city'],
        'email' => $recipient_data['email'],
        'phone' => $recipient_data['phone']
    ];

    // Esegui l'operazione di inserimento dei dati nella tabella
    $wpdb->insert($table_name, $data);
}