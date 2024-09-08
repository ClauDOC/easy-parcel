<?php
// Recupera i dati del mittente dal payload JSON
$sender_data = json_decode(file_get_contents('php://input'), true);

// Esegui l'operazione di salvataggio dei dati del mittente nel database o qualsiasi altra operazione necessaria
// Ad esempio, salva i dati nel database utilizzando WordPress
// Includi i file di WordPress per accedere alle funzioni e alle operazioni del database
require_once('../../../wp-load.php');

// Assicurati che i dati del mittente siano validi prima di salvarli nel database

// Esegui il salvataggio dei dati del mittente
save_sender_to_db($sender_data);

// Invia una risposta JSON di conferma
echo json_encode(['success' => true]);

// Funzione per salvare i dati del mittente nel database
function save_sender_to_db($sender_data) {
    global $wpdb;

    // Nome della tabella dei mittenti nel database WordPress
    $table_name = $wpdb->prefix . 'senders';

    // Prepara i dati per l'inserimento nel database
    $data = [
        'lastname' => $sender_data['lastname'],
        'firstname' => $sender_data['firstname'],
        'address' => $sender_data['address'],
        'city' => $sender_data['city'],
        'email' => $sender_data['email'],
        'phone' => $sender_data['phone']
    ];

    // Esegui l'operazione di inserimento dei dati nella tabella
    $wpdb->insert($table_name, $data);
}