<?php
// Percorso del file di risposta
$file_path = dirname(__FILE__) . '/data/risposta.json';

// Ricevi il JSON di risposta dall'POST
$response_json = isset($_POST['response']) ? $_POST['response'] : '';

// Verifica se il JSON di risposta non è vuoto
if (!empty($response_json)) {
    // Decodifica il JSON in un array associativo
    $response_array = json_decode($response_json, true);

    // Verifica se la decodifica è stata eseguita con successo
    if ($response_array !== null) {
        // Leggi il contenuto attuale del file risposta.json
        $current_content = file_get_contents($file_path);

        // Decodifica il contenuto attuale in un array associativo
        $current_array = json_decode($current_content, true);

        // Verifica se il JSON ricevuto è diverso dal contenuto attuale
        if ($response_array !== $current_array) {
            // Codifica l'array associativo come JSON formattato
            $json_formatted = json_encode($response_array, JSON_PRETTY_PRINT);

            // Scrivi il JSON formattato nel file
            if (file_put_contents($file_path, $json_formatted)) {
                // Rispondi con un messaggio di successo
                echo 'Risposta salvata con successo nel file risposta.json';
            } else {
                // Rispondi con un messaggio di errore
                echo 'Errore durante il salvataggio della risposta nel file risposta.json';
            }
        } else {
            // Rispondi con un messaggio che indica che il JSON è già aggiornato
            echo 'Il JSON di risposta è già aggiornato, nessuna operazione eseguita';
        }
    } else {
        // Rispondi con un messaggio di errore
        echo 'Errore nella decodifica del JSON di risposta';
    }
} else {
    // Rispondi con un messaggio di errore
    echo 'Nessuna risposta ricevuta';
}
?>