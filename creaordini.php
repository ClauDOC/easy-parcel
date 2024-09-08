<?php
require_once('wp-config.php');

// Funzione per registrare i dati del mittente nel database
function registra_dati_mittente($dati_mittente) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'senders',
        array(
            'lastname' => $dati_mittente['lastName'],
            'firstname' => $dati_mittente['firstName'],
            'email' => $dati_mittente['email'],
            'phone' => $dati_mittente['phone'],
            'address' => $dati_mittente['address'],
            'city' => $dati_mittente['city']
        )
    );

    if ($wpdb->last_error) {
        return false;
    }

    return $wpdb->insert_id;
}

// Funzione per registrare i dati del destinatario nel database
function registra_dati_destinatario($dati_destinatario) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'recipients',
        array(
            'lastname' => $dati_destinatario['lastName'],
            'firstname' => $dati_destinatario['firstName'],
            'email' => $dati_destinatario['email'],
            'phone' => $dati_destinatario['phone'],
            'address' => $dati_destinatario['address'],
            'city' => $dati_destinatario['city']
        )
    );

    if ($wpdb->last_error) {
        return false;
    }

    return $wpdb->insert_id;
}

// Funzione per creare un utente WordPress opzionalmente
function crea_utente_wp($dati_utente) {
    $user_id = wp_create_user($dati_utente['email'], wp_generate_password(), $dati_utente['email']);

    if (is_wp_error($user_id)) {
        return false;
    }

    // Aggiorna i metadati dell'utente
    wp_update_user(array(
        'ID' => $user_id,
        'first_name' => $dati_utente['firstName'],
        'last_name' => $dati_utente['lastName'],
        'nickname' => $dati_utente['firstName']
    ));

    update_user_meta($user_id, 'phone', $dati_utente['phone']);
    update_user_meta($user_id, 'address', $dati_utente['address']);
    update_user_meta($user_id, 'city', $dati_utente['city']);
    update_user_meta($user_id, 'country', $dati_utente['country']);
    update_user_meta($user_id, 'cap', $dati_utente['cap']);
    update_user_meta($user_id, 'locality', $dati_utente['locality']);
    update_user_meta($user_id, 'province', $dati_utente['province']);

    return $user_id;
}


















function registra_dati_ordine($dati_ordine, $dati_mittente, $dati_destinatario) {
    global $wpdb;

    // Inserisci i dati del mittente nella tabella wp45259_senders
    $mittente_id = registra_dati_mittente($dati_mittente);

    // Inserisci i dati del destinatario nella tabella wp45259_recipients
    $destinatario_id = registra_dati_destinatario($dati_destinatario);

    // Inserisci i dati dell'ordine nella tabella wp45259_orders
    $result_orders = $wpdb->insert(
        $wpdb->prefix . 'orders',
        array(
            'order_date' => current_time('mysql'), // Data corrente
            'status' => 'In lavorazione', // Stato predefinito
            'total_price' => $dati_ordine['price'], // Prezzo totale
            'sender_id' => $mittente_id, // ID del mittente
            'recipient_id' => $destinatario_id // ID del destinatario
        )
    );

    if ($result_orders === false) {
        return 'Errore nel salvataggio dell\'ordine.';
    }

    // Ottieni l'ID dell'ordine appena inserito
    $order_id = $wpdb->insert_id;

    // Inserisci i dettagli dell'ordine nella tabella wp45259_order_details
    $result_details = inserisci_dettagli_ordine($dati_ordine['package'], $order_id);

    if ($result_details === false) {
        // Se c'è un errore nel salvataggio dei dettagli dell'ordine, elimina l'ordine appena inserito
        $wpdb->delete($wpdb->prefix . 'orders', array('order_id' => $order_id));
        return 'Errore nel salvataggio dei dettagli dell\'ordine.';
    }

    // Restituisci l'ID dell'ordine inserito
    return $order_id;
}

function inserisci_dettagli_ordine($dati_dettagli, $order_id) {
    global $wpdb;

    // Inserisci i dettagli dell'ordine nel database
    $result = $wpdb->insert(
        $wpdb->prefix . 'order_details',
        array(
            'order_id' => $order_id,
            'weight' => $dati_dettagli['weight'],
            'width' => $dati_dettagli['width'],
            'depth' => $dati_dettagli['depth'],
            'height' => $dati_dettagli['height'],
            'nr_packages' => $dati_dettagli['nr_packages']
        )
    );

    if ($result === false) {
        return false;
    }

    // Restituisci true se l'inserimento è avvenuto con successo
    return true;
}













// Controlla se la richiesta è POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dati = json_decode(file_get_contents('php://input'), true);
    print_r($dati);

    // Verifica che il parametro 'action' sia presente nell'URL
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'saveSender':
                if (!empty($dati)) {
                    $result = registra_dati_mittente($dati);
                    if ($result !== false) {
                        echo 'Dati del mittente salvati con ID: ' . $result;
                    } else {
                        http_response_code(500);
                        echo 'Errore nel salvataggio dei dati del mittente.';
                    }
                } else {
                    http_response_code(400);
                    echo 'Dati del mittente non validi.';
                }
                break;

            case 'saveRecipient':
                if (!empty($dati)) {
                    $result = registra_dati_destinatario($dati);
                    if ($result !== false) {
                        echo 'Dati del destinatario salvati con ID: ' . $result;
                    } else {
                        http_response_code(500);
                        echo 'Errore nel salvataggio dei dati del destinatario.';
                    }
                } else {
                    http_response_code(400);
                    echo 'Dati del destinatario non validi.';
                }
                break;

                case 'saveOrder':
                    if (!empty($dati)) {
                        // Assicurati di passare i dati corretti a registra_dati_ordine
                        $result = registra_dati_ordine($dati['order'], $dati['sender'], $dati['recipient']);
                
                        if (is_numeric($result)) {
                            echo 'Ordine registrato con ID: ' . $result;
                        } else {
                            http_response_code(500);
                            echo $result;
                        }
                    } else {
                        http_response_code(400);
                        echo 'Dati dell\'ordine non validi.';
                    }
                    break;

            case 'createWpUser':
                if (!empty($dati)) {
                    $user_id = crea_utente_wp($dati);
                    if ($user_id !== false) {
                        echo 'Utente WordPress creato con ID: ' . $user_id;
                    } else {
                        http_response_code(500);
                        echo 'Errore nella creazione dell\'utente WordPress.';
                    }
                } else {
                    http_response_code(400);
                    echo 'Dati dell\'utente non validi.';
                }
                break;

            default:
                http_response_code(400);
                echo 'Azione non valida.';
                break;
        }
    } else {
        http_response_code(400);
        echo 'Azione non specificata.';
    }
} else {
    http_response_code(405);
    echo 'Metodo non consentito.';
}
?>