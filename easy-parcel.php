<?php


/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://claudio-lombardo.it
 * @since             1.10.13
 * @package           Easy_Parcel
 *
 * @wordpress-plugin
 * Plugin Name:       Easy Parcel
 * Plugin URI:        https://claudio-lombardo.it
 * Description:       Plugin per l'integrazione di Easy Parcel con WordPress
 * Version:           5.1.1
 * Author:            Dr. Claudio Lombardo
 * Author URI:        https://claudio-lombardo.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       easy-parcel
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EASY_PARCEL_VERSION', '5.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-easy-parcel-activator.php
 */
function activate_easy_parcel() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-easy-parcel-activator.php';
    Easy_Parcel_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-easy-parcel-deactivator.php
 */
function deactivate_easy_parcel() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-easy-parcel-deactivator.php';
    Easy_Parcel_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_easy_parcel' );
register_deactivation_hook( __FILE__, 'deactivate_easy_parcel' );

// Aggiungi azione per la creazione delle tabelle del database al momento dell'attivazione del plugin





// La funzione 'crea_tabelle_database' sarà chiamata quando il plugin viene attivato
register_activation_hook(__FILE__, 'crea_tabelle_database');

// Funzione per creare tutte le tabelle del database necessarie
function crea_tabelle_database() {
    crea_tabella_mittenti();
    crea_tabella_destinatari();
    crea_tabella_dettagli_ordine();
    crea_tabella_ordini();
}

function crea_tabella_mittenti() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'senders';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        sender_id mediumint(9) NOT NULL AUTO_INCREMENT,
        lastname varchar(255) NOT NULL,
        firstname varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        address varchar(255) NOT NULL,
        city varchar(255) NOT NULL,
        country varchar(255) NOT NULL,
        cap varchar(10) NOT NULL,
        locality varchar(255) NOT NULL,
        province varchar(255) NOT NULL,
        PRIMARY KEY  (sender_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function crea_tabella_destinatari() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'recipients';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        recipient_id mediumint(9) NOT NULL AUTO_INCREMENT,
        lastname varchar(255) NOT NULL,
        firstname varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        address varchar(255) NOT NULL,
        city varchar(255) NOT NULL,
        country varchar(255) NOT NULL,
        PRIMARY KEY  (recipient_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function crea_tabella_dettagli_ordine() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'order_details';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        detail_id mediumint(9) NOT NULL AUTO_INCREMENT,
        order_id mediumint(9) NOT NULL,
        weight decimal(10, 2) NOT NULL,
        width decimal(10, 2) NOT NULL,
        depth decimal(10, 2) NOT NULL,
        height decimal(10, 2) NOT NULL,
        nr_packages int NOT NULL,
        PRIMARY KEY  (detail_id),
        FOREIGN KEY (order_id) REFERENCES {$wpdb->prefix}orders(order_id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function crea_tabella_ordini() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'orders';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        order_id mediumint(9) NOT NULL AUTO_INCREMENT,
        sender_id mediumint(9) NOT NULL,
        recipient_id mediumint(9) NOT NULL,
        order_detail_id mediumint(9) NOT NULL,
        easy_parcel_order_id varchar(50) NOT NULL,  // Nuovo campo
        order_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status varchar(20) NOT NULL,
        total_price decimal(10, 2) NOT NULL,
        waybill_number varchar(255) DEFAULT NULL,
        waybill_url varchar(255) DEFAULT NULL,
        pickup_code varchar(255) DEFAULT NULL,
        bordero_url varchar(255) DEFAULT NULL,
        PRIMARY KEY  (order_id),
        FOREIGN KEY (sender_id) REFERENCES {$wpdb->prefix}senders(sender_id),
        FOREIGN KEY (recipient_id) REFERENCES {$wpdb->prefix}recipients(recipient_id),
        FOREIGN KEY (order_detail_id) REFERENCES {$wpdb->prefix}order_details(detail_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}



// Funzione per registrare un nuovo ordine nel database
function registra_ordine($dati_ordine) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'orders';
    
    // Inserisci i dati dell'ordine nel database
    $wpdb->insert(
        $table_name,
        $dati_ordine
    );
}


// Aggiungi azione AJAX per gestire la conclusione dell'ordine
add_action('wp_ajax_conclude_order', 'handle_conclude_order');

function handle_conclude_order() {
    // Chiama la funzione per registrare l'ordine
    registra_ordine($dati_ordine);

    // Restituisci una risposta al client (opzionale)
    wp_send_json_success('Ordine registrato con successo.');
    wp_die();
}

// Aggiungi azione AJAX per gestire il contrassegno dell'ordine come Pagato
add_action('wp_ajax_mark_order_as_paid', 'handle_mark_order_as_paid');

function handle_mark_order_as_paid() {
    // Aggiorna lo stato dell'ordine nel database come Pagato
    global $wpdb;
    
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    
    if ($order_id > 0) {
        $table_name = $wpdb->prefix . 'easy_parcel_orders';
        
        // Aggiorna lo stato dell'ordine nel database
        $wpdb->update(
            $table_name,
            array('status' => 'Pagato'), // Nuovo stato dell'ordine
            array('id' => $order_id),
            array('%s'), // Formato dei dati (stringa)
            array('%d') // Formato dei dati (intero)
        );
        
        // Invia una risposta JSON per confermare l'aggiornamento dello stato dell'ordine
        wp_send_json_success('Ordine contrassegnato come Pagato.');
    } else {
        // Se l'ID dell'ordine non è valido, invia una risposta JSON con errore
        wp_send_json_error('ID dell\'ordine non valido.');
    }
    
    // Termina l'esecuzione
    wp_die();
}

// Funzione per chiamare l'API di Easy Parcel per trasmettere l'ordine e aggiornare lo stato dell'ordine
function transmitOrderAndUpdateStatus($order_id) {


    // Effettua la chiamata all'API di Easy Parcel per trasmettere l'ordine e ricevere la lettera di vettura
    // Inserisci qui la logica per chiamare l'API di Easy Parcel
    
    // Aggiorna lo stato dell'ordine nel database come "Concluso"
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'easy_parcel_orders';
    
    // Aggiorna lo stato dell'ordine nel database
    $wpdb->update(
        $table_name,
        array('status' => 'Concluso'), // Nuovo stato dell'ordine
        
        array('id' => $order_id),
        array('%s'), // Formato dei dati (stringa)
        array('%d') // Formato dei dati (intero)
    );
}



// Funzione per aggiornare lo stato di un ordine nel database
function aggiorna_stato_ordine($ordine_id, $nuovo_stato) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'easy_parcel_orders';
    
    // Aggiorna lo stato dell'ordine nel database
    $wpdb->update(
        $table_name,
        array( 'status' => $nuovo_stato ),
        array( 'id' => $ordine_id )
    );
}






/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-easy-parcel.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

// Registra e collega lo script JavaScript personalizzato
function my_plugin_enqueue_scripts() {
// Carica Popper.js
    wp_register_script('popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js', array(), null, true);
    wp_enqueue_script('popper');

    wp_enqueue_script('jquery');


    // Registra lo script
    wp_register_script('claudio-script', plugin_dir_url(__FILE__) . 'js/claudio-script.js', array('jquery'), '1.0', true);
    
    // Passa lo stato di login dell'utente al JavaScript
    wp_localize_script('claudio-script', 'userLoggedIn', array(
        'status' => is_user_logged_in() ? 'logged-in' : 'logged-out'
    ));
    
    // Collega lo script
    wp_enqueue_script('claudio-script');
}
// Aggiungi l'azione per l'inclusione degli script
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_scripts');

// Registra e collega lo stile CSS personalizzato
function my_plugin_enqueue_styles() {
    // Registra lo stile
    wp_register_style('my-plugin-style', plugins_url('css/style.css', __FILE__));
    // Collega lo stile
    wp_enqueue_style('my-plugin-style');
}
// Aggiungi l'azione per l'inclusione degli stili
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_styles');

// Aggiungi la chiave API come variabile JavaScript globale
function add_easy_parcel_api_key() {
    // Verifica se è settata la chiave API
    if (get_option('easy_parcel_api_key')) {
        // Registra e collega lo script
        wp_enqueue_script('claudio-script', plugin_dir_url(__FILE__) . 'js/claudio-script.js', array('jquery'), '1.0', true);
        wp_localize_script('claudio-script', 'easyParcelData', array(
            'apiKey' => get_option('easy_parcel_api_key')
        ));
    }
}
// Aggiungi l'azione per l'inclusione della chiave API
add_action('wp_enqueue_scripts', 'add_easy_parcel_api_key');



function my_enqueue_scripts() {
    // Disabilita la versione di jQuery predefinita di WordPress
    wp_deregister_script('jquery');

    // Carica jQuery tramite CDN
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', array(), '3.6.0', true);

    // Carica il tuo script personalizzato che dipende da jQuery
    wp_enqueue_script('claudio-script', get_template_directory_uri() . '/js/claudio-script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

// Aggiungi le impostazioni di PayPal come variabili globali JavaScript
function add_paypal_settings() {
    // Verifica se le impostazioni PayPal sono settate
    if (get_option('paypal_currency') && get_option('paypal_base_url') && get_option('paypal_email')) {
        // Registra e collega lo script
        wp_enqueue_script('claudio-script', plugin_dir_url(__FILE__) . 'js/claudio-script.js', array('jquery'), '1.0', true);
        wp_localize_script('claudio-script', 'paypalSettings', array(
            'currency' => get_option('paypal_currency'),
            'baseUrl' => get_option('paypal_base_url'),
            'email' => get_option('paypal_email')
        ));
    }
}
// Aggiungi l'azione per l'inclusione delle impostazioni PayPal
add_action('wp_enqueue_scripts', 'add_paypal_settings');




// Funzione per caricare Font Awesome
function load_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
}
// Aggiungi l'azione per l'inclusione di Font Awesome
add_action('wp_enqueue_scripts', 'load_font_awesome');


function shipment_details_shortcode() {
    // Inizio della cattura dell'output
    ob_start();
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Dettagli della spedizione -->
            <div class="col-md-12">
                <div class="shipment-details">
                    <h3>Dettagli della Spedizione</h3>
             
                    
                    <div class="row row-custom">
              
                        <div class="col-md-6">
                            <h4>Mittente</h4>
                            <p><i class="bi bi-geo-alt"></i><strong> Località:</strong> <span id="mittente-localita"></span></p>
                            <p><i class="bi bi-postage"></i><strong> CAP:</strong> <span id="mittente-cap"></span></p>
                            <p><i class="bi bi-house-door"></i><strong> Provincia:</strong> <span id="mittente-provincia"></span></p>
                        </div>
                      
                        <div class="col-md-6">
                            <h4>Destinatario</h4>
                            <p><i class="bi bi-geo-alt"></i><strong> Località:</strong> <span id="destinatario-localita"></span></p>
                            <p><i class="bi bi-postage"></i><strong> CAP:</strong> <span id="destinatario-cap"></span></p>
                            <p><i class="bi bi-house-door"></i><strong> Provincia:</strong> <span id="destinatario-provincia"></span></p>
                        </div>
                    </div>
                    
                    <hr> 
                    <br>

                    
                    <div class="row row-custom">
                        <div class="col-md-12">
                            <p>
                                <h3>Misure del Pacco: </h3>
                            <i class="bi bi-rulers icon-blue"></i><strong> Larghezza:</strong> <span id="larghezza"></span> | 
                            <i class="bi bi-rulers icon-blue"></i><strong> Profondità:</strong> <span id="profondita"></span> | 
                            <i class="bi bi-rulers icon-blue"></i><strong> Altezza:</strong> <span id="altezza"></span> | 
                            <br><br><br><hr><br>
                            <h3>Dettagli del Pacco: </h3>
                            <i class="bi bi-box-arrow-up icon-yellow"></i><strong> Peso:</strong> <span id="peso"></span> | 
                            <i class="bi bi-box icon-yellow"></i><strong> Num. Colli:</strong> <span id="colli"></span> |
                            <i class="bi bi-box icon-yellow"></i><strong> Peso Volumetrico:</strong> <span id="peso-volumetrico"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        function loadFormData() {
            document.getElementById('peso').textContent = sessionStorage.getItem('peso') || 'N/A';
            document.getElementById('larghezza').textContent = sessionStorage.getItem('larghezza') || 'N/A';
            document.getElementById('profondita').textContent = sessionStorage.getItem('profondita') || 'N/A';
            document.getElementById('altezza').textContent = sessionStorage.getItem('altezza') || 'N/A';
            document.getElementById('colli').textContent = sessionStorage.getItem('nr_colli') || 'N/A';
            document.getElementById('mittente-localita').textContent = sessionStorage.getItem('mloc') || 'N/A';
            document.getElementById('mittente-cap').textContent = sessionStorage.getItem('mcap') || 'N/A';
            document.getElementById('mittente-provincia').textContent = sessionStorage.getItem('mpro') || 'N/A';
            document.getElementById('destinatario-localita').textContent = sessionStorage.getItem('dloc') || 'N/A';
            document.getElementById('destinatario-cap').textContent = sessionStorage.getItem('dcap') || 'N/A';
            document.getElementById('destinatario-provincia').textContent = sessionStorage.getItem('dpro') || 'N/A';
            document.getElementById('peso-volumetrico').textContent = sessionStorage.getItem('pesoVolumetrico') || 'N/A';
        }

        // Carica i dati quando il documento è pronto
        document.addEventListener('DOMContentLoaded', loadFormData);
    </script>

    <?php
    return ob_get_clean(); // Restituisce l'output catturato
}
add_shortcode('shipment_details', 'shipment_details_shortcode');











// Funzione per generare il modulo di input
function easy_parcel_quote_form() {
    ob_start(); ?>

<div class="cell-xl-4 cell-lg-5 cell-md-6 text-left" style="opacity:0.9">


<script>
        // Recupera il valore di session_id() dal div e stampalo nella console
        var sessionId = document.getElementById('session-id').dataset.sessionId;
        console.log('Il valore di session_id() è:', sessionId);
    </script>

<div data-type="horizontal" class="text-md-left nav-custom-dark view-animate fadeInUpSmall">
<div class="resp-tabs-container nav-custom-tab nav-custom-wide">
<div id="div_inc_form" class="element-minheight-500">
<form id="form_index" class="small">
<!-- Nuovo Campo per Tipo di Spedizione -->
                <div class="range offset-top-10">
                    <div class="input-group-sm">
                        <label><strong>Tipo di Spedizione:</strong></label><br>
                        <select name="tipo_spedizione" id="tipo_spedizione" class="form-control" onchange="toggleProvincia()">
                            <option value="N" selected>Nazionale</option>
                            <option value="E">Estero</option>
                            <option value="I">Import</option>
                        </select>
                    </div>
                </div>
    
<div class="range offset-top-10">
<div class="cell-sm-12 offset-top-0">
<label><strong>COSA SPEDIRE:</strong></label><label class="pull-right">(AREA SPEDIZIONI)</label><br>
<input name="area" id="area" value="SPEDIZIONI" type="hidden" class="form-control">
<div class="form-group radio-inline-wrapper offset-top-0">
<label class="radio-inline">
<input name="cosaspedire" id="cosaspedirem" value="M" type="radio" checked readonly>Pacchi
</label>
<label class="radio-inline">
<input name="cosaspedire" id="cosaspedired" value="D" type="radio" readonly>Documenti
</label>
<label class="radio-inline">
<input name="cosaspedire" id="cosaspedirep" value="P" type="radio" readonly>Pallet
</label>
</div>
<hr class="hr-grigio">


<div class="range offset-top-10">
<div class="cell-sm-4 offset-top-0">
<div class="input-group-sm">
<label>Lunghezza <small>(cm)</small></label>
<input type="text" data-constraints="@Required @Integer" id="lunghezza" class="form-control">
</div>
</div>
<div class="cell-sm-4 offset-top-0">
<div class="input-group-sm">
<label>Larghezza <small>(cm)</small></label>
<input type="text" data-constraints="@Required @Integer" id="larghezza" class="form-control">
</div>
</div>
<div class="cell-sm-4 offset-top-0">
<div class="input-group-sm">
<label>Altezza <small>(cm)</small></label>
<input type="text" data-constraints="@Required @Integer" id="altezza" class="form-control">
</div>
</div>
<div class="cell-sm-12 offset-top-0" id="div_alertdimensioni"></div>
<div class="cell-sm-4 offset-top-0">
<div class="input-group-sm">
<label>Nr. Colli</label>
<input type="text" data-constraints="@Required @Integer" id="colli" class="form-control">
</div>
</div>
<div class="cell-sm-4 offset-top-0">
<div class="input-group-sm">
<label>Peso totale <small>(Kg)</small></label>
<input type="text" data-constraints="@Required" id="peso" class="form-control" onkeyup="virgola_in_punto()">
</div>
</div>
<div class="cell-sm-4 offset-top-0">
<div class="input-group-sm">
<button id="btn_aggiungi" type="button" class="btn btn-primary btn-xxs offset-top-30" onclick="aggiungi()"><i class="fa fa-plus"></i> aggiungi</button>
</div>
</div>
</div>
    
    
<div class="cell-sm-12 form-group offset-top-10">
    <hr class="hr-grigio">
    <label>Composizione della spedizione:</label>
    <input type="hidden" id="peso_totale" value="0.000">
    <input type="hidden" id="colli_nr" value="0">
    <div id="div_colli" class="cell-sm-12 form-group">
        <img class="spinner-small" src="<?php echo plugins_url( 'easy-parcel/images/ajax-loader2.gif', dirname(__FILE__) ); ?>">
    </div>
</div>

    
<div id="indicazioni" class="cell-sm-12 nascosto">
<div class="range offset-top-10">
<div class="cell-sm-12">
<hr class="hr-grigio">
<label><strong>PARTENZA:</strong></label>
</div>
</div>
<div class="range offset-top-0">
<div class="cell-sm-9 offset-top-0">
<label>Stato</label>
<div class="form-group">
<select id="country1" class="form-control" onchange="selezionata_nazione_account(1)">
<option value="IT" selected="selected">ITALIA</option>
</select>
</div>
</div>
<div class="cell-sm-3 offset-top-0">
<div class="input-group-sm">
<label>Codice</label>
<input type="text" class="form-control" id="nazione1" readonly="readonly" value="IT">
</div>
</div>
<div class="cell-sm-3 offset-top-0">
<div class="input-group-sm">
<label>CAP</label>
<input type="text" class="form-control" id="cap1" placeholder="CAP" maxlength="5" value size="10" class="numeric-input" autocomplete="off" onKeyUp="cerca_localita('cap1', 'localita1', 'provincia1');"/>
</div>
</div>
<div class="cell-sm-6 offset-top-0">
<div class="input-group-sm">

<label>Località</label>
<div id="div_localita1">
<input type="text" class="form-control" id="localita1" value placeholder="Città Es. Palermo" />
</div>
</div>
</div>
<div class="cell-sm-3 offset-top-0">
<div class="input-group-sm">
<label>Provincia</label>
<input type="text" class="form-control bg-white" id="provincia1" value>

</div>
</div>
</div>
<div class="range offset-top-10">
<div class="cell-sm-12">
<hr class="hr-grigio">
<label><strong>DESTINAZIONE:</strong></label>
</div>
</div>
<div class="range offset-top-0">
<div class="cell-sm-9 offset-top-0">
<label>Stato</label>
<div class="form-group">
<select id="country2" class="form-control" onchange="updateNazione()">
<option value="IT">ITALIA</option>

<option value="AF"> Afghanistan</option><option value="AL"> Albania</option><option value="DZ"> Algeria</option><option value="AD"> Andorra</option><option value="AO"> Angola</option><option value="AI"> Anguilla</option><option value="AG"> Antigua e Barbuda</option><option value="AN"> Antille Olandesi</option><option value="SA"> Arabia Saudita</option><option value="AR"> Argentina</option><option value="AM"> Armenia</option><option value="AW"> Aruba</option><option value="AU"> Australia</option><option value="AT"> Austria</option><option value="AZ"> Azerbaijan</option><option value="BS"> Bahamas</option><option value="BH"> Bahrain</option><option value="ESB"> Baleari (Isole)</option><option value="BD"> Bangladesh</option><option value="BB"> Barbados</option><option value="BE"> Belgio</option><option value="BZ"> Belize</option><option value="BJ"> Benin</option><option value="BM"> Bermuda</option><option value="BT"> Bhutan</option><option value="BY"> Bielorussia</option><option value="MMB"> Birmania</option><option value="BO"> Bolivia</option><option value="BA"> Bosnia Herzegovina</option><option value="BW"> Botswana</option><option value="BR"> Brasile</option><option value="BN"> Brunei Darussalam</option><option value="BG"> Bulgaria</option><option value="BF"> Burkina Faso</option><option value="BI"> Burundi</option><option value="KH"> Cambogia</option><option value="CM"> Camerun</option><option value="CA" disabled> Canada</option><option value="CA-AB">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Alberta</option><option value="CA-BC">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Columbia Britannica</option><option value="CA-PE">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Isola del Principe Edoardo</option><option value="CA-MB">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Manitoba</option><option value="CA-NU">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Nunavut</option><option value="CA-NS">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Nuova Scozia</option><option value="CA-NB">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Nuovo Brunswick</option><option value="CA-ON">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Ontario</option><option value="CA-QC">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Qu&eacute;bec</option><option value="CA-SK">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Saskatchewan</option><option value="CA-NL">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Terranova e Labrador</option><option value="CA-NT">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Territori del Nord-Ovest</option><option value="CA-YT">&nbsp;&nbsp;&nbsp;&nbsp;Canada - Yukon</option><option value="ESC"> Canarie (Isole)</option><option value="CV"> Capo Verde</option><option value="KY"> Cayman (Isole)</option><option value="TD"> Ciad</option><option value="CL"> Cile</option><option value="CN"> Cina</option><option value="CY"> Cipro</option><option value="VA"> Citta' del Vaticano</option><option value="AUC"> Cocos (Isole)</option><option value="CO"> Colombia</option><option value="KM"> Comore (Isole)</option><option value="CG"> Congo</option><option value="CD"> Congo (Rep.Dem.del)</option><option value="CK"> Cook (Isole)</option><option value="KP"> Corea Del Nord</option><option value="KR"> Corea Del Sud</option><option value="CI"> Costa D'Avorio</option><option value="CR"> Costa Rica</option><option value="HR"> Croazia</option><option value="CU"> Cuba</option><option value="DK"> Danimarca</option><option value="DJ"> Djibouti</option><option value="DM"> Dominica</option><option value="EC"> Ecuador</option><option value="EG"> Egitto</option><option value="SV"> El Salvador</option><option value="AE"> Emirati Arabi Uniti</option><option value="ER"> Eritrea</option><option value="EE"> Estonia</option><option value="ET"> Etiopia</option><option value="FO"> Faroe (Isole)</option><option value="RU"> Federazione Russa</option><option value="FJ"> Fiji (Isole)</option><option value="PH"> Filippine</option><option value="FI"> Finlandia</option><option value="FR"> Francia</option><option value="GA"> Gabon</option><option value="GM"> Gambia</option><option value="GE"> Georgia</option><option value="DE"> Germania</option><option value="GH"> Ghana</option><option value="JM"> Giamaica</option><option value="JP"> Giappone</option><option value="GI"> Gibilterra</option><option value="JO"> Giordania</option><option value="GB"> Gran Bretagna</option><option value="GR"> Grecia</option><option value="GD"> Grenada</option><option value="GL"> Groenlandia</option><option value="GP"> Guadalupa</option><option value="GU"> Guam</option><option value="GT"> Guatemala</option><option value="GY"> Guiana</option><option value="GF"> Guiana Francese</option><option value="GN"> Guinea</option><option value="GW"> Guinea Bissau</option><option value="GQ"> Guinea Equatoriale</option><option value="HT"> Haiti</option><option value="HN"> Honduras</option><option value="HK"> Hong Kong</option><option value="IN"> India</option><option value="ID"> Indonesia</option><option value="IR"> Iran</option><option value="IQ"> Iraq</option><option value="IE"> Irlanda</option><option value="IS"> Islanda</option><option value="IL"> Israele</option><option value="KHK"> Kamputchea</option><option value="KZ"> Kazakhistan</option><option value="KE"> Kenya</option><option value="KI"> Kiribati</option><option value="YK"> Kosovo</option><option value="KW"> Kuwait</option><option value="KG"> Kyrgyzstan</option><option value="LA"> Laos (Rep.Dem.Pop.del)</option><option value="LS"> Lesotho</option><option value="LV"> Lettonia</option><option value="LB"> Libano</option><option value="LR"> Liberia</option><option value="LY"> Libia</option><option value="LI"> Liechtenstein</option><option value="LT"> Lituania</option><option value="LU"> Lussemburgo</option><option value="MO"> Macao</option><option value="MK"> Macedonia</option><option value="MG"> Madagascar</option><option value="MW"> Malawi</option><option value="MV"> Maldive (Isole)</option><option value="MY"> Malesia</option><option value="ML"> Mali</option><option value="MT"> Malta</option><option value="GBM"> Manica (Isole della)</option><option value="MP"> Mariana del Nord (Isole)</option><option value="MA"> Marocco</option><option value="MH"> Marshall (Isole)</option><option value="MQ"> Martinica</option><option value="MR"> Mauritania</option><option value="MU"> Mauritius</option><option value="YT"> Mayotte</option><option value="MX"> Messico</option><option value="FM"> Micronesia</option><option value="MD"> Moldavia</option><option value="MN"> Mongolia</option><option value="ME"> Montenegro</option><option value="MS"> Montserrat</option><option value="MZ"> Mozambico</option><option value="MM"> Myanmar</option><option value="NA"> Namibia</option><option value="AUN"> Natale (Isola)</option><option value="NR"> Nauru</option><option value="NP"> Nepal</option><option value="NI"> Nicaragua</option><option value="NE"> Niger</option><option value="NG"> Nigeria</option><option value="NU"> Niue (Isole)</option><option value="NF"> Norfolk (Isole)</option><option value="NO"> Norvegia</option><option value="NC"> Nuova Caledonia</option><option value="NZ"> Nuova Zelanda</option><option value="NL"> Olanda</option><option value="OM"> Oman</option><option value="PK"> Pakistan</option><option value="PW"> Palau (Isole)</option><option value="PS"> Palestina</option><option value="PA"> Panama</option><option value="PG"> Papua Nuova Guinea</option><option value="PY"> Paraguay</option><option value="PE"> Peru'</option><option value="PF"> Polinesia Francese</option><option value="PL"> Polonia</option><option value="PR"> Porto Rico</option><option value="PT"> Portogallo</option><option value="MC"> Principato di Monaco</option><option value="QA"> Qatar</option><option value="CF"> Rep. Centro Africana</option><option value="CZ"> Repubblica Ceca</option><option value="SM"> Repubblica di San Marino</option><option value="DO"> Repubblica Dominicana</option><option value="RE"> Reunion (Isola)</option><option value="RO"> Romania</option><option value="RW"> Ruanda</option><option value="XY"> Saint Barthelemy</option><option value="KN"> Saint Kitts e Nevis</option><option value="PM"> Saint Pierre e Miquelon</option><option value="VC"> Saint Vincent</option><option value="SB"> Salomone (Isole)</option><option value="WS"> Samoa (Isole)</option><option value="AS"> Samoa Americane (Isole)</option><option value="LC"> Santa Lucia</option><option value="ST"> SaoTome e Principe</option><option value="SN"> Senegal</option><option value="RS"> Serbia</option><option value="SC"> Seychelles</option><option value="SL"> Sierra Leone</option><option value="SG"> Singapore</option><option value="SY"> Siria</option><option value="SK"> Slovacchia</option><option value="SI"> Slovenia</option><option value="SO"> Somalia</option><option value="ES"> Spagna</option><option value="LK"> Sri Lanka</option><option value="US" disabled> Stati Uniti d'America</option><option value="US-AL">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Alabama</option><option value="US-AK">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Alaska</option><option value="US-AZ">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Arizona</option><option value="US-AR">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Arkansas</option><option value="US-CA">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - California</option><option value="US-CO">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Colorado</option><option value="US-CT">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Connecticut</option><option value="US-DE">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Delaware</option><option value="US-FL">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Florida</option><option value="US-GA">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Georgia</option><option value="US-HI">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Hawaii</option><option value="US-ID">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Idaho</option><option value="US-IL">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Illinois</option><option value="US-IN">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Indiana</option><option value="US-IA">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Iowa</option><option value="US-KS">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Kansas</option><option value="US-KY">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Kentucky</option><option value="US-LA">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Louisiana</option><option value="US-ME">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Maine</option><option value="US-MD">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Maryland</option><option value="US-MA">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Massachusetts</option><option value="US-MI">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Michigan</option><option value="US-MN">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Minnesota</option><option value="US-MS">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Mississippi</option><option value="US-MO">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Missouri</option><option value="US-MT">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Montana</option><option value="US-NE">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Nebraska</option><option value="US-NV">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Nevada</option><option value="US-NH">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - New Hampshire</option><option value="US-NJ">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - New Jersey</option><option value="US-NM">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - New Mexico</option><option value="US-NY">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - New York</option><option value="US-NC">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - North Carolina</option><option value="US-ND">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - North Dakota</option><option value="US-OH">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Ohio</option><option value="US-OK">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Oklahoma</option><option value="US-OR">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Oregon</option><option value="US-PA">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Pennsylvania</option><option value="US-RI">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Rhode Island</option><option value="US-SC">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - South Carolina</option><option value="US-SD">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - South Dakota</option><option value="US-TN">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Tennessee</option><option value="US-TX">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Texas</option><option value="US-UT">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Utah</option><option value="US-VT">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Vermont</option><option value="US-VA">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Virginia</option><option value="US-WA">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Washington</option><option value="US-DC">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Washington DC</option><option value="US-WV">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - West Virginia</option><option value="US-WI">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Wisconsin</option><option value="US-WY">&nbsp;&nbsp;&nbsp;&nbsp;Stati Uniti d'America - Wyoming</option><option value="ZA"> Sud Africa</option><option value="SD"> Sudan</option><option value="SR"> Suriname</option><option value="SE"> Svezia</option><option value="CH"> Svizzera</option><option value="SZ"> Swaziland</option><option value="TW"> Taiwan</option><option value="TJ"> Tajikistan</option><option value="TZ"> Tanzania</option><option value="TH"> Thailandia</option><option value="TL"> Timor Est</option><option value="TG"> Togo</option><option value="TO"> Tonga</option><option value="TT"> Trinidad eTobago</option><option value="TN"> Tunisia</option><option value="TR"> Turchia</option><option value="TM"> Turkmenistan</option><option value="TC"> Turks e Caicos (Isole)</option><option value="TV"> Tuvalu</option><option value="UA"> Ucraina</option><option value="UG"> Uganda</option><option value="HU"> Ungheria</option><option value="UY"> Uruguay</option><option value="UZ"> Uzbekistan</option><option value="VU"> Vanuatu</option><option value="VE"> Venezuela</option><option value="VI"> Vergini Americane (Isole)</option><option value="VG"> Vergini Britanniche (Isole)</option><option value="VN"> Vietnam</option><option value="FRW"> Wallis e Futuna (Isole)</option><option value="YE"> Yemen</option><option value="ZM"> Zambia</option><option value="ZW"> Zimbabwe</option> </select>
</div>
</div>
<div class="cell-sm-3 offset-top-0">
<div class="input-group-sm">
<label>Codice</label>
<input type="text" class="form-control" id="nazione2" readonly="readonly" value="IT">
</div>
</div>
</div>
<div class="range offset-top-0">
<div class="cell-sm-3 offset-top-0">
<div class="input-group-sm">
<label>CAP</label>
<input type="text" class="form-control" id="cap2" placeholder="CAP" maxlength="10" size="10" class="numeric-input" autocomplete="off" onKeyUp="cerca_localita('cap2', 'localita2', 'provincia2');"/>

</div>
</div>
<div class="cell-sm-6 offset-top-0">
<label>Località</label>
<div id="div_localita2">
<input type="text" class="form-control" id="localita2" value placeholder="Country/City" />
</div>
</div>
<div class="cell-sm-3 offset-top-0">
                                            <div class="input-group-sm">
                                                <label>Provincia</label>
                                                <input type="text" class="form-control non-modificabile" id="provincia2" value="--" readonly>
                                            </div>
                                        </div>
</div>
<hr class="hr-grigio">
<div class="range-xs-justify range-xs-middle range offset-top-20 text-center text-xs-left">
<div class="cell-lg-clear-flex cell-xs-6 text-xs-right offset-top-15 offset-xs-top-0">
    <button id="btn_procedi" type="button" class="btn btn-success" onclick="calcola_test()"><span>>>> PROCEDI >>></span></button>


<div id="div_calcoli" class="form-group"></div>
</div>
</div>
</div>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
</div>
<script>
        // Funzione per aggiornare il valore del campo nazione2
        function updateNazione() {
            var countrySelect = document.getElementById('country2');
            var nazioneField = document.getElementById('nazione2');
            nazioneField.value = countrySelect.value;
        }

        // Funzione per abilitare/disabilitare il campo provincia2
        function toggleProvincia() {
            var tipoSpedizione = document.getElementById('tipo_spedizione').value;
            var provincia2 = document.getElementById('provincia2');
            
            if (tipoSpedizione === 'E' || tipoSpedizione === 'I') {
                provincia2.value = '--';
                provincia2.classList.add('non-modificabile');
                provincia2.setAttribute('readonly', 'readonly');
            } else {
                provincia2.classList.remove('non-modificabile');
                provincia2.removeAttribute('readonly');
                provincia2.value = ''; // Puoi impostare un valore predefinito se necessario
            }
        }

        // Imposta lo stato iniziale del campo provincia2
        window.onload = function() {
            toggleProvincia();
        }
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode( 'easy_parcel_quote_form', 'easy_parcel_quote_form' );











// Funzione per creare lo shortcode
function custom_shipping_details_shortcode() {
    ob_start(); // Avvia l'output buffer

    // Recupera i dati della spedizione dalla tua fonte dati JSON
    $shipping_data_json = get_easy_parcel_data(); // Funzione ipotetica per recuperare i dati JSON
    $shipping_data = json_decode($shipping_data_json);

    // Verifica se i dati sono stati recuperati correttamente
    if ($shipping_data && isset($shipping_data->result) && $shipping_data->result === "OK") {
        ?>
        <div class="container">
            <div class="row">
                <!-- Dettagli della spedizione -->
                <div class="col-md-4">
                    <h2>Dettagli della spedizione</h2>
                    <p>Località del mittente: <?php echo $shipping_data->mittente->localita; ?></p>
                    <p>Località destinazione: <?php echo $shipping_data->destinazione->localita; ?></p>
                    <h3>Dettagli del pacco</h3>
                    <!-- Inserisci qui i dettagli del pacco -->
                </div>

                <!-- Offerte -->
                <div class="col-md-5">
                    <h2>Le nostre offerte</h2>
                    <!-- Inserisci qui le offerte -->
                </div>

                <!-- Servizi opzionali -->
                <div class="col-md-3">
                    <h2>Servizi opzionali</h2>
                    <!-- Inserisci qui i servizi opzionali -->
                    <h2>Seleziona una delle nostre offerte</h2>
                    <!-- Inserisci qui le offerte -->
                </div>
            </div>

            <!-- Altro info -->
            <div class="row">
                <div class="col-md-6">
                    <h2>Altro info</h2>
                    <h3>Descrizione contenuto</h3>
                    <!-- Inserisci qui la descrizione dettagliata -->
                    <h3>Note sulla spedizione</h3>
                    <!-- Inserisci qui le note sulla spedizione -->
                </div>

                <!-- Mittente -->
                <div class="col-md-3">
                    <h2>Mittente</h2>
                    <!-- Inserisci qui i dettagli del mittente -->
                </div>

                <!-- Destinatario -->
                <div class="col-md-3">
                    <h2>Destinatario</h2>
                    <!-- Inserisci qui i dettagli del destinatario -->
                </div>
            </div>
        </div>
        <?php
    } else {
        echo "Errore nel recupero dei dati della spedizione.";
    }

    return ob_get_clean(); // Restituisci il contenuto dell'output buffer
}
add_shortcode('custom_shipping_details', 'custom_shipping_details_shortcode');





















// Aggiungi azione per creare il menu di amministrazione di Easy Parcel
add_action('admin_menu', 'easy_parcel_add_admin_menu');

// Funzione per aggiungere il menu di amministrazione di Easy Parcel
function easy_parcel_add_admin_menu() {
    add_menu_page(
        'Easy Parcel Settings',   // Titolo della pagina
        'Easy Parcel',            // Titolo del menu
        'manage_options',         // Capability necessaria per vedere questa opzione
        'easy-parcel-settings',   // Slug del menu
        'easy_parcel_settings_page', // Funzione che renderizza la pagina di impostazioni
        'dashicons-admin-generic', // Icona del menu
        6                          // Posizione nel menu
    );
    
    // Aggiungi una voce di menu per la configurazione dei corrieri
    add_submenu_page(
        'easy-parcel-settings',
        'Configurazione Corrieri', 
        'Corrieri',
        'manage_options',
        'easy-parcel-corrieri-settings',
        'easy_parcel_corrieri_settings_page'
    );
    
    // Aggiungi una voce di menu per le anagrafiche dei mittenti e dei destinatari
    add_submenu_page(
        'easy-parcel-settings', // Slug della pagina genitore
        'Anagrafiche Easy Parcel', // Titolo della pagina
        'Anagrafiche', // Etichetta del menu
        'manage_options', // Capacità richiesta per accedere alla pagina
        'easy-parcel-addresses', // Slug della pagina
        'render_easy_parcel_addresses_page' // Funzione per il rendering della pagina
    );
    
    
    // Aggiungi una voce di menu per la configurazione delle Tasse
    add_submenu_page(
        'easy-parcel-settings', // Slug della pagina genitore
        'Configurazione Tasse', // Titolo della pagina
        'Tasse', // Etichetta del menu
        'manage_options', // Capacità richiesta per accedere alla pagina
        'easy-parcel-taxes', // Slug della pagina
        'easy_parcel_taxes_page' // Funzione per il rendering della pagina
    );

    // Aggiungi una voce di menu per le impostazioni di Stripe
    add_submenu_page(
        'easy-parcel-settings', // Slug della pagina genitore
        'Impostazioni Stripe', // Titolo della pagina
        'Stripe', // Etichetta del menu
        'manage_options', // Capacità richiesta per accedere alla pagina
        'easy-parcel-stripe-settings', // Slug della pagina
        'easy_parcel_stripe_settings_page' // Funzione per il rendering della pagina
    );



}

















// Funzione di rendering per la pagina delle tasse
function easy_parcel_taxes_page() {
    echo '<h1>Configurazione Tasse</h1>';
    // Il contenuto della pagina di configurazione delle tasse va qui
    // Esempio di campo per inserire la percentuale IVA
    echo '<form method="post" action="options.php">';
    settings_fields('easy_parcel_taxes_group');
    do_settings_sections('easy-parcel-taxes');
    submit_button();
    echo '</form>';
}

// Funzione di rendering per la pagina delle impostazioni di Stripe
function easy_parcel_stripe_settings_page() {
    echo '<h1>Impostazioni Stripe</h1>';
    // Il contenuto della pagina delle impostazioni di Stripe va qui
    echo '<form method="post" action="options.php">';
    settings_fields('easy_parcel_stripe_group');
    do_settings_sections('easy-parcel-stripe-settings');
    submit_button();
    echo '</form>';
}

// Registra le impostazioni per le tasse
add_action('admin_init', 'easy_parcel_taxes_settings_init');
function easy_parcel_taxes_settings_init() {
    register_setting('easy_parcel_taxes_group', 'easy_parcel_tax_percentage');

    add_settings_section(
        'easy_parcel_taxes_section',
        'Impostazioni Tasse',
        'easy_parcel_taxes_section_callback',
        'easy-parcel-taxes'
    );

    add_settings_field(
        'easy_parcel_tax_percentage',
        'Percentuale IVA',
        'easy_parcel_tax_percentage_callback',
        'easy-parcel-taxes',
        'easy_parcel_taxes_section'
    );
}

function easy_parcel_taxes_section_callback() {
    echo 'Inserisci le impostazioni per la percentuale IVA.';
}

function easy_parcel_tax_percentage_callback() {
    $tax_percentage = get_option('easy_parcel_tax_percentage');
    echo '<input type="text" name="easy_parcel_tax_percentage" value="' . esc_attr($tax_percentage) . '" />';
}


// Registra le impostazioni per Stripe
add_action('admin_init', 'easy_parcel_stripe_settings_init');
function easy_parcel_stripe_settings_init() {
    register_setting('easy_parcel_stripe_group', 'easy_parcel_stripe_test_mode');
    register_setting('easy_parcel_stripe_group', 'easy_parcel_stripe_test_publishable_key');
    register_setting('easy_parcel_stripe_group', 'easy_parcel_stripe_test_secret_key');
    register_setting('easy_parcel_stripe_group', 'easy_parcel_stripe_live_publishable_key');
    register_setting('easy_parcel_stripe_group', 'easy_parcel_stripe_live_secret_key');

    add_settings_section(
        'easy_parcel_stripe_section',
        'Impostazioni Stripe',
        'easy_parcel_stripe_section_callback',
        'easy-parcel-stripe-settings'
    );

    add_settings_field(
        'easy_parcel_stripe_test_mode',
        'Modalità Test',
        'easy_parcel_stripe_test_mode_callback',
        'easy-parcel-stripe-settings',
        'easy_parcel_stripe_section'
    );

    add_settings_field(
        'easy_parcel_stripe_test_publishable_key',
        'Chiave Pubblicabile Test',
        'easy_parcel_stripe_test_publishable_key_callback',
        'easy-parcel-stripe-settings',
        'easy_parcel_stripe_section'
    );

    add_settings_field(
        'easy_parcel_stripe_test_secret_key',
        'Chiave Segreta Test',
        'easy_parcel_stripe_test_secret_key_callback',
        'easy-parcel-stripe-settings',
        'easy_parcel_stripe_section'
    );

    add_settings_field(
        'easy_parcel_stripe_live_publishable_key',
        'Chiave Pubblicabile Live',
        'easy_parcel_stripe_live_publishable_key_callback',
        'easy-parcel-stripe-settings',
        'easy_parcel_stripe_section'
    );

    add_settings_field(
        'easy_parcel_stripe_live_secret_key',
        'Chiave Segreta Live',
        'easy_parcel_stripe_live_secret_key_callback',
        'easy-parcel-stripe-settings',
        'easy_parcel_stripe_section'
    );
}

function easy_parcel_stripe_section_callback() {
    echo 'Inserisci le impostazioni per integrare Stripe come metodo di pagamento.';
}

function easy_parcel_stripe_test_mode_callback() {
    $test_mode = get_option('easy_parcel_stripe_test_mode');
    echo '<input type="checkbox" name="easy_parcel_stripe_test_mode" value="1"' . checked(1, $test_mode, false) . ' /> Attiva modalità test';
}

function easy_parcel_stripe_test_publishable_key_callback() {
    $test_publishable_key = get_option('easy_parcel_stripe_test_publishable_key');
    echo '<input type="text" name="easy_parcel_stripe_test_publishable_key" value="' . esc_attr($test_publishable_key) . '" />';
}

function easy_parcel_stripe_test_secret_key_callback() {
    $test_secret_key = get_option('easy_parcel_stripe_test_secret_key');
    echo '<input type="text" name="easy_parcel_stripe_test_secret_key" value="' . esc_attr($test_secret_key) . '" />';
}

function easy_parcel_stripe_live_publishable_key_callback() {
    $live_publishable_key = get_option('easy_parcel_stripe_live_publishable_key');
    echo '<input type="text" name="easy_parcel_stripe_live_publishable_key" value="' . esc_attr($live_publishable_key) . '" />';
}

function easy_parcel_stripe_live_secret_key_callback() {
    $live_secret_key = get_option('easy_parcel_stripe_live_secret_key');
    echo '<input type="text" name="easy_parcel_stripe_live_secret_key" value="' . esc_attr($live_secret_key) . '" />';
}









// Registra l'azione AJAX per ottenere la percentuale IVA
add_action('wp_ajax_get_tax_percentage', 'get_tax_percentage');
add_action('wp_ajax_nopriv_get_tax_percentage', 'get_tax_percentage');

function get_tax_percentage() {
    // Recupera la percentuale IVA dalle opzioni
    $tax_percentage = get_option('easy_parcel_tax_percentage');
    // Invia la percentuale IVA come risposta JSON
    echo json_encode(array('tax_percentage' => $tax_percentage));
    wp_die();
}










// Aggiungi voce di menu per lo storico ordini
add_action('admin_menu', 'add_order_history_menu');

function add_order_history_menu() {
    add_submenu_page(
        'easy-parcel-settings', // Slug della pagina genitore
        'Storico Ordini Easy Parcel', // Titolo della pagina
        'Storico Ordini', // Etichetta del menu
        'manage_options', // Capacità richiesta per accedere alla pagina
        'easy-parcel-order-history', // Slug della pagina
        'render_order_history_page' // Funzione per il rendering della pagina
    );
}










function render_order_history_page() {
    ?>
    <div class="wrap">
        <h1>Storico Ordini Boxes Point</h1>
        <h2 id="order_count_title"></h2>
        <form id="order_history_form" method="post">
            <label for="start_date">Data di inizio:</label>
            <input type="date" id="start_date" name="start_date" required>
            <label for="end_date">Data di fine:</label>
            <input type="date" id="end_date" name="end_date" required>
            <button type="submit" id="view_orders_btn">Visualizza Ordini</button>
        </form>
        
        <div id="order_results"></div> <!-- Spazio per visualizzare gli ordini recuperati -->
        <div id="pagination_controls"></div> <!-- Controlli di paginazione -->
    </div>

    <!-- <script>
    jQuery(document).ready(function($) {
        var currentPage = 1;
        var ordersPerPage = 25;

        $('#order_history_form').on('submit', function(e) {
            e.preventDefault(); // Evita il comportamento predefinito del form
            var formData = {
                'call': 'listorder',
                'dettagli': {
                    'data_inizio': $('#start_date').val(),
                    'data_fine': $('#end_date').val(),
                    'paginazione': ordersPerPage,
                    'pagina': currentPage
                }
            };
            console.log("Richiesta: ", formData);
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>', // URL per la chiamata AJAX al backend
                data: {
                    action: 'get_order_history', 
                    form_data: JSON.stringify(formData) 
                },
                success: function(response) {
                    // Decodifica la risposta JSON
                    var responseData = JSON.parse(response);
                    console.log("Risposta JSON:", response);
                    // Verifica se la risposta è stata decodificata correttamente e se ci sono ordini
                    if (responseData !== null && responseData.hasOwnProperty('orders')) {
                        // Chiama la funzione per costruire la tabella degli ordini
                        renderOrderTable(responseData.orders);
                        // Chiama la funzione per generare i controlli di paginazione
                        renderPaginationControls(responseData.nrordini);
                    } else {
                        // Mostra un messaggio di errore se non ci sono ordini
                        $('#order_results').html('<p>Nessun ordine trovato.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); // Gestisci eventuali errori
                }
            });
        });



        // Funzione per generare i controlli di paginazione
        function renderPaginationControls(totalOrders) {
            var totalPages = Math.ceil(totalOrders / ordersPerPage);
            var paginationHtml = '<ul class="pagination">';
            for (var i = 1; i <= totalPages; i++) {
                paginationHtml += '<li class="page-item"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            paginationHtml += '</ul>';
            paginationHtml += '<p>Pagina ' + currentPage + ' di ' + totalPages + '</p>';
            $('#pagination_controls').html(paginationHtml);
            $('#order_count_title').text('Numero totale di ordini: ' + totalOrders);
        }

        // Aggiungi un event listener per i link di paginazione
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            currentPage = $(this).data('page');
            $('#order_history_form').submit(); // Invia nuovamente il modulo per ottenere i dati della nuova pagina
        });



        
        // Funzione per costruire la tabella degli ordini
        function renderOrderTable(orders) {
            var tableHtml = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>ID Ordine</th><th>Codice Offerta</th><th>Data</th><th>Mittente</th><th>Destinatario</th><th>Totale Dovuto</th><th>Vettore</th><th>Lettera di Vettura</th></tr></thead><tbody>';
            
            // Itera attraverso gli ordini e costruisci le righe della tabella
            orders.forEach(function(order) {
                tableHtml += '<tr>';
                tableHtml += '<td>' + order.idordine + '</td>';
                tableHtml += '<td>' + order.codice_offerta + '</td>';
                tableHtml += '<td>' + order.data + '</td>';
                tableHtml += '<td>' + order.mittente_nominativo + ', ' + order.mittente_localita + ', ' + order.mittente_cap + ', ' + order.mittente_provincia + '</td>';
                tableHtml += '<td>' + order.destinatario_nominativo + ', ' + order.destinatario_localita + ', ' + order.destinatario_cap + ', ' + order.destinatario_provincia + '</td>';
                tableHtml += '<td>' + order.totale_dovuto + '</td>';
                tableHtml += '<td><img src="' + order.logo_vettore + '" alt="' + order.nome_vettore + '" width="50"></td>';
                tableHtml += '<td><button class="get-waybill-btn" data-order-id="' + order.idordine + '">Scarica</button></td>';
                tableHtml += '</tr>';
            });
            
            tableHtml += '</tbody></table></div>';
            
            // Inserisci la tabella HTML nell'elemento con id "order_results"
            $('#order_results').html(tableHtml);
        }
        
        $(document).on('click', '.get-waybill-btn', function() {
            var orderId = $(this).data('order-id');
            console.log("Ordine: ", orderId);
            getWaybill(orderId);
        });
        
    // Funzione per recuperare la lettera di vettura
function getWaybill(orderId) {
    var formData = {
        'call': 'getwaybill',
        'details': {
            'order_id': orderId,
            'waybill_base64': 'N'
        }
    };
    $.ajax({
        type: 'POST',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        data: {
            action: 'get_waybill',
            form_data: JSON.stringify(formData)
        },
        success: function(response) {
            console.log("Risposta JSON:", response);
            var responseData = JSON.parse(response);
            if (responseData !== null && responseData.response && responseData.response.result === 'OK') {
                var waybillUrl = responseData.response.waybill_url;
                window.open(waybillUrl, '_blank');
            } else {
                alert('Errore durante il recupero della lettera di vettura.');
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
}
    });
    </script>-->
    <script>
jQuery(document).ready(function($) {
    var currentPage = 1;
    var ordersPerPage = 25;

    $('#order_history_form').on('submit', function(e) {
        e.preventDefault(); // Evita il comportamento predefinito del form
        var formData = {
            'call': 'listorder',
            'dettagli': {
                'data_inizio': $('#start_date').val(),
                'data_fine': $('#end_date').val(),
                'paginazione': ordersPerPage,
                'pagina': currentPage
            }
        };
        console.log("Richiesta: ", formData);
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>', // URL per la chiamata AJAX al backend
            data: {
                action: 'get_order_history', 
                form_data: JSON.stringify(formData) 
            },
            success: function(response) {
                var responseData = JSON.parse(response);
                console.log("Risposta JSON:", response);
                if (responseData !== null && responseData.hasOwnProperty('orders')) {
                    renderOrderTable(responseData.orders);
                    renderPaginationControls(responseData.nrordini);
                } else {
                    $('#order_results').html('<p>Nessun ordine trovato.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); 
            }
        });
    });

    
    function renderPaginationControls(totalOrders) {
        var totalPages = Math.ceil(totalOrders / ordersPerPage);
        var paginationHtml = '<ul class="pagination">';
        for (var i = 1; i <= totalPages; i++) {
            paginationHtml += '<li class="page-item"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
        }
        paginationHtml += '</ul>';
        $('#pagination_controls').html(paginationHtml);
    }

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        currentPage = $(this).data('page');
        $('#order_history_form').submit();
    });

    function renderOrderTable(orders) {
        var tableHtml = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th></th><th>ID Ordine</th><th>Codice Offerta</th><th>Data</th><th>Mittente</th><th>Destinatario</th><th>Totale Dovuto</th><th>Vettore</th></tr></thead><tbody>';
        
        orders.forEach(function(order) {
            tableHtml += '<tr>';
            tableHtml += '<td><button class="toggle-subtable-btn" data-order-id="' + order.idordine + '">+</button></td>';
            tableHtml += '<td>' + order.idordine + '</td>';
            tableHtml += '<td>' + order.codice_offerta + '</td>';
            tableHtml += '<td>' + order.data + '</td>';
            tableHtml += '<td>' + order.mittente_nominativo + ', ' + order.mittente_localita + ', ' + order.mittente_cap + ', ' + order.mittente_provincia + '</td>';
            tableHtml += '<td>' + order.destinatario_nominativo + ', ' + order.destinatario_localita + ', ' + order.destinatario_cap + ', ' + order.destinatario_provincia + '</td>';
            tableHtml += '<td>' + order.totale_dovuto + '</td>';
            tableHtml += '<td><img src="' + order.logo_vettore + '" alt="' + order.nome_vettore + '" width="50"></td>';
            
            tableHtml += '</tr>';
            tableHtml += '<tr class="subtable-row" id="subtable-' + order.idordine + '" style="display:none;"><td colspan="9"><div class="subtable-content"></div></td></tr>';
        });
        
        tableHtml += '</tbody></table></div>';
        
        $('#order_results').html(tableHtml);
    }

    $(document).on('click', '.toggle-subtable-btn', function() {
        var orderId = $(this).data('order-id');
        var subtableRow = $('#subtable-' + orderId);
        if (subtableRow.is(':visible')) {
            subtableRow.hide();
        } else {
            subtableRow.show();
            if (!subtableRow.data('loaded')) {
                getWaybill(orderId);
            }
        }
    });

    $(document).on('click', '.get-waybill-btn', function() {
        var orderId = $(this).data('order-id');
        console.log("Ordine: ", orderId);
        getWaybill(orderId);
    });

    function getWaybill(orderId) {
        var formData = {
            'call': 'getwaybill',
            'details': {
                'order_id': orderId,
                'waybill_base64': 'N'
            }
        };
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'get_waybill',
                form_data: JSON.stringify(formData)
            },
            success: function(response) {
                console.log("Risposta JSON:", response);
                var responseData = JSON.parse(response);
                if (responseData && responseData.result === 'OK') {
                    var waybillHtml = '<p>Numero Lettera di Vettura: ' + responseData.waybill_number + '</p>';
                    waybillHtml += '<p><a href="' + responseData.waybill_url + '" target="_blank">Scarica Lettera di Vettura</a></p>';
                    waybillHtml += '<p>Codice di Ritiro: ' + responseData.pickup_code + '</p>';
                    waybillHtml += '<p><a href="' + responseData.bordero_url + '" target="_blank">Scarica Bordereau</a></p>';
                    $('#subtable-' + orderId + ' .subtable-content').html(waybillHtml);
                    $('#subtable-' + orderId).data('loaded', true);
                } else {
                    alert('Errore durante il recupero della lettera di vettura.');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }
});
</script>









    <?php
}

// Aggiungi azione AJAX per la gestione della richiesta di recupero degli ordini
add_action('wp_ajax_get_order_history', 'get_order_history');
add_action('wp_ajax_nopriv_get_order_history', 'get_order_history');






function render_custom_order_history_shortcode() {
    // Calcola la data di inizio (primo giorno del mese corrente) e la data di fine (oggi)
    $start_date = date('Y-m-01'); // Primo giorno del mese corrente
    $end_date = date('Y-m-d');    // Data di oggi

    ob_start();
    ?>
    <div class="wrap">
        <h3>Storico Ordini Boxes Point</h3>
        <h5 id="custom_order_count_title"></h5>
        <form id="custom_order_history_form" method="post">
            <label for="start_date">Data di inizio:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
            <label for="end_date">Data di fine:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
            <button type="submit" id="custom_view_orders_btn">Visualizza Ordini</button>
        </form>
        
        <div id="custom_order_results"></div> <!-- Spazio per visualizzare gli ordini recuperati -->
        <div id="custom_pagination_controls"></div> <!-- Controlli di paginazione -->
    </div>

    <script>
    jQuery(document).ready(function($) {
        var currentPage = 1;
        var ordersPerPage = 25;

        function fetchCustomOrders() {
            var formData = {
                'call': 'listorder',
                'dettagli': {
                    'data_inizio': $('#start_date').val(),
                    'data_fine': $('#end_date').val(),
                    'paginazione': ordersPerPage,
                    'pagina': currentPage
                }
            };
            console.log("Richiesta: ", formData);
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'get_custom_order_history',
                    form_data: JSON.stringify(formData)
                },
                success: function(response) {
                    var responseData = JSON.parse(response);
                    console.log("Risposta JSON:", response);
                    if (responseData !== null && responseData.hasOwnProperty('orders')) {
                        renderCustomOrderTable(responseData.orders);
                        renderCustomPaginationControls(responseData.nrordini);
                    } else {
                        $('#custom_order_results').html('<p>Nessun ordine trovato.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        fetchCustomOrders();

        $('#custom_order_history_form').on('submit', function(e) {
            e.preventDefault();
            fetchCustomOrders();
        });

        function renderCustomPaginationControls(totalOrders) {
            var totalPages = Math.ceil(totalOrders / ordersPerPage);
            var paginationHtml = '<ul class="pagination">';
            for (var i = 1; i <= totalPages; i++) {
                paginationHtml += '<li class="page-item"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            paginationHtml += '</ul>';
            paginationHtml += '<p>Pagina ' + currentPage + ' di ' + totalPages + '</p>';
            $('#custom_pagination_controls').html(paginationHtml);
            $('#custom_order_count_title').text('Numero totale di ordini: ' + totalOrders);
        }

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            currentPage = $(this).data('page');
            fetchCustomOrders();
        });

        function renderCustomOrderTable(orders) {
            var tableHtml = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>ID Ordine</th><th>Codice Offerta</th><th>Data</th><th>Mittente</th><th>Destinatario</th><th>Totale Dovuto</th><th>Vettore</th><th>Lettera di Vettura</th></tr></thead><tbody>';
            orders.forEach(function(order) {
                tableHtml += '<tr>';
                tableHtml += '<td>' + order.idordine + '</td>';
                tableHtml += '<td>' + order.codice_offerta + '</td>';
                tableHtml += '<td>' + order.data + '</td>';
                tableHtml += '<td>' + order.mittente_nominativo + ', ' + order.mittente_localita + ', ' + order.mittente_cap + ', ' + order.mittente_provincia + '</td>';
                tableHtml += '<td>' + order.destinatario_nominativo + ', ' + order.destinatario_localita + ', ' + order.destinatario_cap + ', ' + order.destinatario_provincia + '</td>';
                tableHtml += '<td>' + order.totale_dovuto + '</td>';
                tableHtml += '<td><img src="' + order.logo_vettore + '" alt="' + order.nome_vettore + '" width="50"></td>';
                tableHtml += '<td><button class="get-waybill-btn" data-order-id="' + order.idordine + '">Scarica</button></td>';
                tableHtml += '</tr>';
            });
            tableHtml += '</tbody></table></div>';
            $('#custom_order_results').html(tableHtml);
        }
        $(document).on('click', '.get-waybill-btn', function() {
            var orderId = $(this).data('order-id');
            console.log("Ordine: ", orderId);
            getWaybill(orderId);
        });


        $(document).on('click', '.custom_get-waybill-btn', function() {
            var orderId = $(this).data('order-id');
            console.log("Ordine: ", orderId);
            getCustomWaybill(orderId);
        });

        function getCustomWaybill(orderId) {
            var formData = {
                'call': 'getwaybill',
                'details': {
                    'order_id': orderId,
                    'waybill_base64': 'N'
                }
            };
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'get_custom_waybill',
                    form_data: JSON.stringify(formData)
                },
                success: function(response) {
                    console.log("Risposta JSON:", response);
                    var responseData = JSON.parse(response);
                    if (responseData !== null && responseData.response && responseData.response.result === 'OK') {
                        var waybillUrl = responseData.response.waybill_url;
                        window.open(waybillUrl, '_blank');
                    } else {
                        alert('Errore durante il recupero della lettera di vettura.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}

// Registra lo shortcode
add_shortcode('custom_order_history_page', 'render_custom_order_history_shortcode');






function get_custom_order_history() {
    $form_data = json_decode(stripslashes($_POST['form_data']), true);
    $response = execute_custom_easy_parcel_api_call($form_data, 'listorder');
    
    if ($response !== false) {
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Errore durante il recupero degli ordini.']);
    }

    wp_die();
}

add_action('wp_ajax_get_custom_order_history', 'get_custom_order_history');
add_action('wp_ajax_nopriv_get_custom_order_history', 'get_custom_order_history');

function get_custom_waybill() {
    $form_data = json_decode(stripslashes($_POST['form_data']), true);
    $response = execute_custom_easy_parcel_api_call($form_data, 'getwaybill');
    
    if ($response !== false) {
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Errore durante il recupero della lettera di vettura.']);
    }

    wp_die();
}

add_action('wp_ajax_get_custom_waybill', 'get_custom_waybill');
add_action('wp_ajax_nopriv_get_custom_waybill', 'get_custom_waybill');

function execute_custom_easy_parcel_api_call($data, $endpoint) {
    $jsonRequest = json_encode($data);
    $ch = curl_init();
    $url = 'https://api.easyparcel.it/' . $endpoint . '/ffc3586f7a60fc765dc007c7d4e7d53db8861b93f83cc7d94563f08f4b4e6003';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonRequest)
    ));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    $decoded_response = json_decode($response, true);
    return $decoded_response !== null ? $decoded_response : false;
}

























// Funzione per la gestione della richiesta di recupero degli ordini
function get_order_history() {
    // Recupera i dati del form
    $form_data = json_decode(stripslashes($_POST['form_data']), true);
    
    // Esegui la chiamata all'API diretta a listorder
    $response = execute_easy_parcel_api_call($form_data, 'listorder');
    
    // Visualizza i risultati
    if ($response !== false) {
        echo json_encode($response); // Restituisci i risultati come JSON
    } else {
        echo json_encode(['error' => 'Errore durante il recupero degli ordini.']);
    }

    wp_die(); // Termina il processo AJAX
}

// Funzione per la gestione della richiesta di recupero della lettera di vettura
add_action('wp_ajax_get_waybill', 'get_waybill');
add_action('wp_ajax_nopriv_get_waybill', 'get_waybill');

function get_waybill() {
    // Recupera i dati del form
    $form_data = json_decode(stripslashes($_POST['form_data']), true);
    
    // Esegui la chiamata all'API diretta a getwaybill
    $response = execute_easy_parcel_api_call($form_data, 'getwaybill');
    
    // Visualizza i risultati
    if ($response !== false) {
        echo json_encode($response); // Restituisci i risultati come JSON
    } else {
        echo json_encode(['error' => 'Errore durante il recupero della lettera di vettura.']);
    }

    wp_die(); // Termina il processo AJAX
}

// Funzione per eseguire la chiamata all'API diretta
function execute_easy_parcel_api_call($data, $endpoint) {
    // Dati JSON per la richiesta
    $jsonRequest = json_encode($data);

    // Crea un'istanza di cURL
    $ch = curl_init();

    // Imposta l'URL per la chiamata diretta con la tua chiave API
    $url = 'https://api.easyparcel.it/' . $endpoint . '/ffc3586f7a60fc765dc007c7d4e7d53db8861b93f83cc7d94563f08f4b4e6003';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonRequest)
    ));

    // Esegui la richiesta cURL
    $response = curl_exec($ch);

    // Verifica se ci sono errori durante la richiesta
    if (curl_errno($ch)) {
        $error_message = curl_error($ch);
        curl_close($ch);
        return false; // Errore durante la richiesta cURL
    }

    // Chiudi la risorsa cURL
    curl_close($ch);

    // Decodifica la risposta JSON
    $decoded_response = json_decode($response, true);

    // Verifica se la decodifica è stata effettuata correttamente
    if ($decoded_response === null) {
        return false; // Errore durante la decodifica della risposta JSON
    }

    // Restituisci la risposta decodificata
    return $decoded_response;
}








// Funzione per renderizzare la pagina del bilancio
function render_balance_page() {
    ?>
    <div class="wrap">
        <h1>Bilancio Boxes Point</h1>
        <form id="balance_form" method="post">
            <label for="idcustomer">ID Cliente (opzionale):</label>
            <input type="number" id="idcustomer" name="idcustomer">
            <button type="submit" id="view_balance_btn">Visualizza Bilancio</button>
        </form>
        
        <div id="balance_result"></div> <!-- Spazio per visualizzare il bilancio -->
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#balance_form').on('submit', function(e) {
            e.preventDefault(); // Evita il comportamento predefinito del form
            var formData = {
                'call': 'balance',
                'dettagli': {}
            };
            var idcustomer = $('#idcustomer').val();
            if (idcustomer) {
                formData.dettagli.idcustomer = idcustomer;
            }
            console.log("Richiesta: ", formData);
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>', // URL per la chiamata AJAX al backend
                data: {
                    action: 'get_balance', // Azione personalizzata per la gestione della richiesta
                    form_data: JSON.stringify(formData) // Dati del form
                },
                success: function(response) {
                    // Decodifica la risposta JSON
                    var responseData = JSON.parse(response);
                    
                    // Verifica se la risposta è stata decodificata correttamente
                    if (responseData !== null && responseData.result === 'OK') {
                        // Chiama la funzione per costruire la visualizzazione del bilancio
                        renderBalance(responseData);
                    } else {
                        // Mostra un messaggio di errore se la richiesta non è andata a buon fine
                        $('#balance_result').html('<p>Errore durante il recupero del bilancio: ' + responseData.errormessage + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); // Gestisci eventuali errori
                }
            });
        });
        
        // Funzione per costruire la visualizzazione del bilancio
        function renderBalance(data) {
            var balanceHtml = '<div class="alert alert-info">';
            balanceHtml += '<p><strong>Bilancio Master:</strong> ' + data.balance_master + ' €</p>';
            if (data.balance_customer) {
                balanceHtml += '<p><strong>Bilancio Cliente:</strong> ' + data.balance_customer + ' €</p>';
            }
            balanceHtml += '<p><strong>Data:</strong> ' + data.timestamp + '</p>';
            balanceHtml += '</div>';
            
            // Inserisci il bilancio HTML nell'elemento con id "balance_result"
            $('#balance_result').html(balanceHtml);
        }
    });
    </script>
    <?php
}

// Aggiungi azione AJAX per la gestione della richiesta di recupero del bilancio
add_action('wp_ajax_get_balance', 'get_balance');
add_action('wp_ajax_nopriv_get_balance', 'get_balance');

// Funzione per la gestione della richiesta di recupero del bilancio
function get_balance() {
    // Recupera i dati del form
    $form_data = json_decode(stripslashes($_POST['form_data']), true);
    
    // Esegui la chiamata all'API diretta a balance
    $response = execute_easy_parcel_api_call_balance($form_data);
    
    // Visualizza i risultati
    if ($response !== false) {
        echo json_encode($response); // Restituisci i risultati come JSON
    } else {
        echo json_encode(['error' => 'Errore durante il recupero del bilancio.']);
    }

    wp_die(); // Termina il processo AJAX
}

// Funzione per eseguire la chiamata all'API diretta a balance
function execute_easy_parcel_api_call_balance($data) {
    // Dati JSON per la richiesta
    $jsonRequest = json_encode($data);

    // Crea un'istanza di cURL
    $ch = curl_init();

    // Imposta l'URL per la chiamata diretta a balance con la tua chiave API
    $url = 'https://api.easyparcel.it/balance/ffc3586f7a60fc765dc007c7d4e7d53db8861b93f83cc7d94563f08f4b4e6003';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonRequest)
    ));

    // Esegui la richiesta cURL
    $response = curl_exec($ch);

    // Verifica se ci sono errori durante la richiesta
    if (curl_errno($ch)) {
        $error_message = curl_error($ch);
        curl_close($ch);
        return false; // Errore durante la richiesta cURL
    }

    // Chiudi la risorsa cURL
    curl_close($ch);

    // Decodifica la risposta JSON
    $decoded_response = json_decode($response, true);

    // Verifica se la decodifica è stata effettuata correttamente
    if ($decoded_response === null) {
        return false; // Errore durante la decodifica della risposta JSON
    }

    // Restituisci la risposta decodificata
    return $decoded_response;
}


// Aggiungi la pagina del bilancio al menu di amministrazione di WordPress
function add_balance_menu_page() {
    add_menu_page(
        'Bilancio Boxes Point', // Titolo della pagina
        'Bilancio', // Testo del menu
        'manage_options', // Capacità richiesta
        'balance-page', // Slug della pagina
        'render_balance_page', // Funzione di callback per renderizzare la pagina
        'dashicons-chart-line', // Icona del menu (dashicons)
        6 // Posizione del menu
    );
}
add_action('admin_menu', 'add_balance_menu_page');





$corrieri = array(
    'DVA' => array(
        'enabled' => true,
        'enabled_option_name' => 'dva_enabled',
        'option_name' => 'dva_percentuale'
    ),
    'SDA (SDAM)' => array(
        'enabled' => true,
        'enabled_option_name' => 'sda_sdam_enabled',
        'option_name' => 'sda_sdam_percentuale'
    ),
    'TNT (TNTM)' => array(
        'enabled' => true,
        'enabled_option_name' => 'tnt_tntm_enabled',
        'option_name' => 'tnt_tntm_percentuale'
    ),
    'SDA PROMO 2022 (2SDA)' => array(
        'enabled' => true,
        'enabled_option_name' => 'sda_promo_2sda_enabled',
        'option_name' => 'sda_promo_2sda_percentuale'
    ),
    'Poste Delivery Business (PDB)' => array(
        'enabled' => true,
        'enabled_option_name' => 'pdb_enabled',
        'option_name' => 'pdb_percentuale'
    ),
    'BRT SPA (BRT)' => array(
        'enabled' => true,
        'enabled_option_name' => 'brt_enabled',
        'option_name' => 'brt_percentuale'
    ),
    'UPS STANDARD MONOCOLLO (UPS21)' => array(
        'enabled' => true,
        'enabled_option_name' => 'ups_ups21_enabled',
        'option_name' => 'ups_ups21_percentuale'
    ),
    'DHL' => array(
        'enabled' => true,
        'enabled_option_name' => 'dhl_enabled',
        'option_name' => 'dhl_percentuale'
    ),
    'MEDICAL EXPRESS (DHLMED)' => array(
        'enabled' => true,
        'enabled_option_name' => 'medical_express_dhlmed_enabled',
        'option_name' => 'medical_express_dhlmed_percentuale'
    ),
    // Corrieri internazionali aggiunti
    'DVALM' => array(
        'enabled' => true,
        'enabled_option_name' => 'dvalm_enabled',
        'option_name' => 'dvalm_percentuale'
    ),
    'UPS21' => array(
        'enabled' => true,
        'enabled_option_name' => 'ups21_enabled',
        'option_name' => 'ups21_percentuale'
    ),
    'SDAI' => array(
        'enabled' => true,
        'enabled_option_name' => 'sdai_enabled',
        'option_name' => 'sdai_percentuale'
    ),
    'DHLR' => array(
        'enabled' => true,
        'enabled_option_name' => 'dhlr_enabled',
        'option_name' => 'dhlr_percentuale'
    ),
    'EXPM' => array(
        'enabled' => true,
        'enabled_option_name' => 'expm_enabled',
        'option_name' => 'expm_percentuale'
    ),
    'UPS21S' => array(
        'enabled' => true,
        'enabled_option_name' => 'ups21s_enabled',
        'option_name' => 'ups21s_percentuale'
    ),
    'DHLMED' => array(
        'enabled' => true,
        'enabled_option_name' => 'dhlmed_enabled',
        'option_name' => 'dhlmed_percentuale'
    ),
    'DHLM' => array(
        'enabled' => true,
        'enabled_option_name' => 'dhlm_enabled',
        'option_name' => 'dhlm_percentuale'
    )
    // Aggiungi altri corrieri se necessario
);


function easy_parcel_settings_page() {
    ?>
    <div class="wrap">
        <h1>Impostazioni di Easy Parcel</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('easy_parcel_options_group');
            do_settings_sections('easy-parcel-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function easy_parcel_corrieri_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configurazione Corrieri di Easy Parcel</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('easy_parcel_corrieri_options_group');
            do_settings_sections('easy-parcel-corrieri-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function easy_parcel_register_settings() {
    // Registra le impostazioni per la chiave API di Easy Parcel
    register_setting('easy_parcel_options_group', 'easy_parcel_api_key');
    add_settings_section('easy_parcel_main_section', 'Impostazioni API', 'easy_parcel_settings_section_cb', 'easy-parcel-settings');
    add_settings_field('easy_parcel_api_key_field', 'Chiave API', 'easy_parcel_api_key_field_cb', 'easy-parcel-settings', 'easy_parcel_main_section');

    // Registra le impostazioni per i corrieri
    register_setting('easy_parcel_corrieri_options_group', 'corrieri_settings');
    global $corrieri;
    foreach ($corrieri as $nome_corriere => $info_corriere) {
        register_setting('easy_parcel_corrieri_options_group', $info_corriere['option_name']);
        register_setting('easy_parcel_corrieri_options_group', $info_corriere['enabled_option_name']);
    }
    add_settings_section('easy_parcel_corrieri_section', 'Configurazione Corrieri', 'easy_parcel_corrieri_section_cb', 'easy-parcel-corrieri-settings');
    foreach ($corrieri as $nome_corriere => $info_corriere) {
        add_settings_field($info_corriere['enabled_option_name'], $nome_corriere, 'easy_parcel_corriere_field_cb', 'easy-parcel-corrieri-settings', 'easy_parcel_corrieri_section', $info_corriere);
    }
}
add_action('admin_init', 'easy_parcel_register_settings');

function easy_parcel_settings_section_cb() {
    echo '<p>Inserisci la tua chiave API di Easy Parcel qui.</p>';
}

function easy_parcel_api_key_field_cb() {
    $api_key = get_option('easy_parcel_api_key');
    echo '<input type="text" id="easy_parcel_api_key" name="easy_parcel_api_key" value="' . esc_attr($api_key) . '" />';
}

function easy_parcel_corrieri_section_cb() {
    echo '<p>Imposta la percentuale di ricarico per ciascun corriere qui sotto.</p>';
}

function easy_parcel_corriere_field_cb($args) {
    $enabled = get_option($args['enabled_option_name']);
    $percentuale = get_option($args['option_name']);
    ?>
    <input type="checkbox" name="<?php echo $args['enabled_option_name']; ?>" value="1" <?php checked($enabled, 1); ?> />
    <input type="number" name="<?php echo $args['option_name']; ?>" value="<?php echo esc_attr($percentuale); ?>" style="width: 100px;" /> %
    <?php
}




function render_easy_parcel_addresses_page() {
    ?>
    <div class="wrap">
        <h2>Anagrafiche Easy Parcel</h2>

        <h3>Mittenti</h3>
        <?php render_senders_table(); ?>

        <h3>Destinatari</h3>
        <?php render_recipients_table(); ?>
    </div>
    <?php
}

// Funzione per visualizzare la tabella dei mittenti
function render_senders_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'senders';
    $senders = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Cognome</th>
                <th>Indirizzo</th>
                <th>Città</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($senders as $sender) : ?>
                <tr>
                    <td><?php echo $sender['sender_id']; ?></td>
                    <td><?php echo $sender['firstname']; ?></td>
                    <td><?php echo $sender['lastname']; ?></td>
                    <td><?php echo $sender['address']; ?></td>
                    <td><?php echo $sender['city']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

// Funzione per visualizzare la tabella dei destinatari
function render_recipients_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'recipients';
    $recipients = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Cognome</th>
                <th>Indirizzo</th>
                <th>Città</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recipients as $recipient) : ?>
                <tr>
                    <td><?php echo $recipient['recipient_id']; ?></td>
                    <td><?php echo $recipient['firstname']; ?></td>
                    <td><?php echo $recipient['lastname']; ?></td>
                    <td><?php echo $recipient['address']; ?></td>
                    <td><?php echo $recipient['city']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}







add_action('wp_ajax_nopriv_get_quote', 'handle_easy_parcel_quote_request');

add_action('admin_post_calcola_api', 'calcola_api');

/*
function get_easy_parcel_data_ajax() {
    // Recupera i dati dei corrieri abilitati e le percentuali impostate
    $corrieri = array(
        'DVA' => array(
            'enabled' => get_option('dva_enabled'),
            'percentuale' => get_option('dva_percentuale')
        ),
        'SDA' => array(
            'enabled' => get_option('sda_sdam_enabled'),
            'percentuale' => get_option('sda_sdam_percentuale')
        ),
        'TNT' => array(
            'enabled' => get_option('tnt_tntm_enabled'),
            'percentuale' => get_option('tnt_tntm_percentuale')
        ),
        'SDA PROMO 2022' => array(
            'enabled' => get_option('sda_promo_2sda_enabled'),
            'percentuale' => get_option('sda_promo_2sda_percentuale')
        ),
        'Poste Delivery Business' => array(
            'enabled' => get_option('pdb_enabled'),
            'percentuale' => get_option('pdb_percentuale')
        ),
        'BRT SPA' => array(
            'enabled' => get_option('brt_enabled'),
            'percentuale' => get_option('brt_percentuale')
        ),
        'UPS STANDARD MONOCOLLO' => array(
            'enabled' => get_option('ups_ups21_enabled'),
            'percentuale' => get_option('ups_ups21_percentuale')
        ),
        'DHL' => array(
            'enabled' => get_option('dhl_enabled'),
            'percentuale' => get_option('dhl_percentuale')
        ),
        'MEDICAL EXPRESS' => array(
            'enabled' => get_option('medical_express_dhlmed_enabled'),
            'percentuale' => get_option('medical_express_dhlmed_percentuale')
        )
        // Aggiungi altri corrieri se necessario
    );

    // Passa le variabili al JavaScript
    wp_localize_script('claudio-script', 'easyParcelSettings', array(
        'corrieri' => $corrieri,
    ));
    error_log('Dati corrieri: ' . print_r($corrieri, true));

    // Restituisci i dati come risposta JSON
    wp_send_json($corrieri);
}

// Aggiungi l'hook per la gestione dell'azione AJAX
add_action('wp_ajax_get_easy_parcel_data', 'get_easy_parcel_data_ajax');
add_action('wp_ajax_nopriv_get_easy_parcel_data', 'get_easy_parcel_data_ajax'); // Per gli utenti non autenticati

*/





function get_easy_parcel_data_ajax() {
    // Recupera i dati dei corrieri abilitati e le percentuali impostate
    $corrieri = array(
        'DVA' => array(
            'enabled' => get_option('dva_enabled'),
            'percentuale' => get_option('dva_percentuale')
        ),
        'SDA' => array(
            'enabled' => get_option('sda_sdam_enabled'),
            'percentuale' => get_option('sda_sdam_percentuale')
        ),
        'TNT' => array(
            'enabled' => get_option('tnt_tntm_enabled'),
            'percentuale' => get_option('tnt_tntm_percentuale')
        ),
        'SDA PROMO 2022' => array(
            'enabled' => get_option('sda_promo_2sda_enabled'),
            'percentuale' => get_option('sda_promo_2sda_percentuale')
        ),
        'Poste Delivery Business' => array(
            'enabled' => get_option('pdb_enabled'),
            'percentuale' => get_option('pdb_percentuale')
        ),
        'BRT SPA' => array(
            'enabled' => get_option('brt_enabled'),
            'percentuale' => get_option('brt_percentuale')
        ),
        'UPS STANDARD MONOCOLLO' => array(
            'enabled' => get_option('ups_ups21_enabled'),
            'percentuale' => get_option('ups_ups21_percentuale')
        ),
        'DHL' => array(
            'enabled' => get_option('dhl_enabled'),
            'percentuale' => get_option('dhl_percentuale')
        ),
        'MEDICAL EXPRESS' => array(
            'enabled' => get_option('medical_express_dhlmed_enabled'),
            'percentuale' => get_option('medical_express_dhlmed_percentuale')
        ),
        // Corrieri internazionali aggiunti
        'DVALM' => array(
            'enabled' => get_option('dvalm_enabled'),
            'percentuale' => get_option('dvalm_percentuale')
        ),
        'UPS21' => array(
            'enabled' => get_option('ups21_enabled'),
            'percentuale' => get_option('ups21_percentuale')
        ),
        'SDAI' => array(
            'enabled' => get_option('sdai_enabled'),
            'percentuale' => get_option('sdai_percentuale')
        ),
        'DHLR' => array(
            'enabled' => get_option('dhlr_enabled'),
            'percentuale' => get_option('dhlr_percentuale')
        ),
        'EXPM' => array(
            'enabled' => get_option('expm_enabled'),
            'percentuale' => get_option('expm_percentuale')
        ),
        'UPS21S' => array(
            'enabled' => get_option('ups21s_enabled'),
            'percentuale' => get_option('ups21s_percentuale')
        ),
        'DHLMED' => array(
            'enabled' => get_option('dhlmed_enabled'),
            'percentuale' => get_option('dhlmed_percentuale')
        ),
        'DHLM' => array(
            'enabled' => get_option('dhlm_enabled'),
            'percentuale' => get_option('dhlm_percentuale')
        )
        // Aggiungi altri corrieri se necessario
    );

    // Passa le variabili al JavaScript
    wp_localize_script('claudio-script', 'easyParcelSettings', array(
        'corrieri' => $corrieri,
    ));
    error_log('Dati corrieri: ' . print_r($corrieri, true));

    // Restituisci i dati come risposta JSON
    wp_send_json($corrieri);
}

// Aggiungi l'hook per la gestione dell'azione AJAX
add_action('wp_ajax_get_easy_parcel_data', 'get_easy_parcel_data_ajax');
add_action('wp_ajax_nopriv_get_easy_parcel_data', 'get_easy_parcel_data_ajax'); // Per gli utenti non autenticati

















// Registrazione delle impostazioni di PayPal
function paypal_register_settings() {
    // Registra le impostazioni per PayPal
    register_setting('paypal_settings_group', 'paypal_currency');
    register_setting('paypal_settings_group', 'paypal_base_url');
    register_setting('paypal_settings_group', 'paypal_email');
}
add_action('admin_init', 'paypal_register_settings');

// Aggiunta di una pagina di menu per le impostazioni di PayPal
function paypal_settings_menu() {
    add_submenu_page(
        'easy-parcel-settings', // Slug del menu genitore (Easy Parcel)
        'Impostazioni PayPal', // Titolo della pagina
        'Impostazioni PayPal', // Etichetta del menu
        'manage_options', // Capacità richiesta per accedere alla pagina
        'paypal-settings', // Slug della pagina
        'paypal_settings_page' // Funzione per il rendering della pagina
    );
}
add_action('admin_menu', 'paypal_settings_menu');

// Rendering della pagina delle impostazioni di PayPal
function paypal_settings_page() {
    ?>

    <div class="wrap">
        <h2>Impostazioni PayPal</h2>
        <form method="post" action="options.php">
            <?php settings_fields('paypal_settings_group'); ?>
            <table class="form-table">
                <!-- Campi delle impostazioni per PayPal -->
                <tr valign="top">
                    <th scope="row">Valuta</th>
                    <td>
                        <select name="paypal_currency">
                            <option value="EUR" <?php echo selected(get_option('paypal_currency'), 'EUR'); ?>>Euro (EUR)</option>
                            <option value="USD" <?php echo selected(get_option('paypal_currency'), 'USD'); ?>>Dollaro (USD)</option>
                            <!-- Aggiungi altre valute se necessario -->
                        </select>
                    </td>
                </tr>
                
                <tr valign="top">
    <th scope="row">URL base per i pagamenti PayPal</th>
    <td><input type="text" name="paypal_base_url" value="<?php echo esc_attr(get_option('paypal_base_url', 'https://www.paypal.com/cgi-bin/webscr')); ?>" /></td>
</tr>
                
                
                
                
            
                <tr valign="top">
                    <th scope="row">Email PayPal</th>
                    <td><input type="email" name="paypal_email" value="<?php echo esc_attr(get_option('paypal_email')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}









// Funzione per renderizzare la pagina di aggiunta ordine
function render_add_easy_parcel_order_page() {
    ?>
    <div class="wrap">
        <h4>Compila con le informazioni richieste</h4>
        
        <!-- Form per aggiungere un nuovo ordine -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="add_easy_parcel_order">
            
            <!-- Colonna Mittente -->
            <div style="float: left; width: 50%;">
                <h3>Mittente</h3>

                <?php if (is_user_logged_in()): ?>
                    <!-- Campo di ricerca per il mittente -->
                    <label for="search_sender">Cerca Mittente:</label>
                    <input type="text" id="search_sender" onkeyup="searchSender()">
                    <div id="senderSuggestions" class="autocomplete-suggestions"></div>
                <?php else: ?>
                    <p>Effettua il <a href="<?php echo wp_login_url(get_permalink()); ?>">login</a> per cercare il mittente.</p>
                <?php endif; ?>

                <label for="sender_lastname">Cognome:</label>
                <input type="text" id="sender_lastname" name="sender_lastname" required><br><br>
                
                <label for="sender_firstname">Nome:</label>
                <input type="text" id="sender_firstname" name="sender_firstname" required><br><br>
                
                <label for="sender_address">Indirizzo:</label>
                <input type="text" id="sender_address" name="sender_address" required><br><br>
                
                <label for="sender_city">Città:</label>
                <input type="text" id="sender_city" name="sender_city" required><br><br>

                <label for="sender_email">Email:</label>
                <input type="email" id="sender_email" name="sender_email" required><br><br>
                
                <label for="sender_phone">Cellulare:</label>
                <input type="text" id="sender_phone" name="sender_phone" required><br><br>

                <?php if (is_user_logged_in()): ?>
            <button type="button" onclick="saveSenderData()">Salva Dati Mittente</button>
        <?php else: ?>
            <p>Effettua il <a href="<?php echo wp_login_url(get_permalink()); ?>">login</a> o <a href="<?php echo wp_registration_url(); ?>">registrati</a> per salvare i dati del mittente.</p>
        <?php endif; ?>

                <br><br>
            </div>
            
            <!-- Colonna Destinatario -->
            <div style="float: right; width: 50%;">
                <h3>Destinatario</h3>

                <?php if (is_user_logged_in()): ?>
                    <!-- Campo di ricerca per il destinatario -->
                    <label for="search_recipient">Cerca Destinatario:</label>
                    <input type="text" id="search_recipient" onkeyup="searchRecipient()">
                    <div id="recipientSuggestions" class="autocomplete-suggestions"></div>
                <?php else: ?>
                    <p>Effettua il <a href="<?php echo wp_login_url(get_permalink()); ?>">login</a> per cercare il destinatario.</p>
                <?php endif; ?>

                <label for="recipient_lastname">Cognome:</label>
                <input type="text" id="recipient_lastname" name="recipient_lastname" required><br><br>
                
                <label for="recipient_firstname">Nome:</label>
                <input type="text" id="recipient_firstname" name="recipient_firstname" required><br><br>
                
                <label for="recipient_address">Indirizzo:</label>
                <input type="text" id="recipient_address" name="recipient_address" required><br><br>
                
                <label for="recipient_city">Città:</label>
                <input type="text" id="recipient_city" name="recipient_city" required><br><br>

                <label for="recipient_email">Email:</label>
                <input type="email" id="recipient_email" name="recipient_email" required><br><br>
                
                <label for="recipient_phone">Cellulare:</label>
                <input type="text" id="recipient_phone" name="recipient_phone" required><br><br>

                <?php if (is_user_logged_in()): ?>
            <button type="button" onclick="saveRecipientData()">Salva Dati Destinatario</button>
        <?php else: ?>
            <p>Effettua il <a href="<?php echo wp_login_url(get_permalink()); ?>">login</a> o <a href="<?php echo wp_registration_url(); ?>">registrati</a> per salvare i dati del destinatario.</p>
        <?php endif; ?>

                <br><br>
            </div>
            
            <div style="clear:both;"></div>
            
        
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function searchSender() {
    let query = $("#search_sender").val();
    if (query.length > 2) {
        $.ajax({
            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
            method: 'GET',
            data: {
                action: 'search_sender',
                query: query
            },
            success: function(response) {
                if (response.success) {
                    let results = response.data;
                    let suggestions = '';
                    results.forEach(function(item) {
                        suggestions += `<div class="autocomplete-suggestion" onclick="fillSender('${item.lastname}', '${item.firstname}', '${item.address}', '${item.city}', '${item.email}', '${item.phone}')">${item.lastname} ${item.firstname} - ${item.address}</div>`;
                    });
                    $("#senderSuggestions").html(suggestions);
                } else {
                    console.error(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Errore nella richiesta AJAX:', error);
            }
        });
    } else {
        $("#senderSuggestions").html('');
    }
}


function searchRecipient() {
    let query = $("#search_recipient").val();
    if (query.length > 2) {
        $.ajax({
            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
            method: 'GET',
            data: { 
                action: 'search_recipient', 
                query: query 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let results = response.data;
                    let suggestions = '';
                    results.forEach(function(item) {
                        suggestions += `<div class="autocomplete-suggestion" onclick="fillRecipient('${item.lastname}', '${item.firstname}', '${item.address}', '${item.city}', '${item.email}', '${item.phone}')">${item.lastname} ${item.firstname} - ${item.address}</div>`;
                    });
                    $("#recipientSuggestions").html(suggestions);
                } else {
                    console.error('Errore nella risposta:', response.data);
                    $("#recipientSuggestions").html('<div class="autocomplete-suggestion">Nessun risultato trovato</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Errore nella richiesta AJAX:', error);
                $("#recipientSuggestions").html('<div class="autocomplete-suggestion">Errore nella ricerca</div>');
            }
        });
    } else {
        $("#recipientSuggestions").html('');
    }
}


        function fillSender(lastname, firstname, address, city, email, phone) {
            $("#sender_lastname").val(lastname);
            $("#sender_firstname").val(firstname);
            $("#sender_address").val(address);
            $("#sender_city").val(city);
            $("#sender_email").val(email);
            $("#sender_phone").val(phone);
            $("#senderSuggestions").html('');
        }

        function fillRecipient(lastname, firstname, address, city, email, phone) {
            $("#recipient_lastname").val(lastname);
            $("#recipient_firstname").val(firstname);
            $("#recipient_address").val(address);
            $("#recipient_city").val(city);
            $("#recipient_email").val(email);
            $("#recipient_phone").val(phone);
            $("#recipientSuggestions").html('');
        }


        function saveSenderData() {
        let senderData = {
            sender_lastname: $("#sender_lastname").val(),
            sender_firstname: $("#sender_firstname").val(),
            sender_address: $("#sender_address").val(),
            sender_city: $("#sender_city").val(),
            sender_email: $("#sender_email").val(),
            sender_phone: $("#sender_phone").val()
        };

        $.ajax({
            url: '<?php echo esc_url(admin_url('admin-post.php')); ?>',
            method: 'POST',
            data: {
                action: 'save_sender_data',
                sender_data: senderData
            },
            success: function(response) {
                if (response.success) {
                    alert('Dati del mittente salvati con successo!');
                } else {
                    alert('Errore nel salvataggio dei dati del mittente.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Errore nella richiesta AJAX:', error);
                alert('Errore nel salvataggio dei dati del mittente.');
            }
        });
    }

    function saveRecipientData() {
        let recipientData = {
            recipient_lastname: $("#recipient_lastname").val(),
            recipient_firstname: $("#recipient_firstname").val(),
            recipient_address: $("#recipient_address").val(),
            recipient_city: $("#recipient_city").val(),
            recipient_email: $("#recipient_email").val(),
            recipient_phone: $("#recipient_phone").val()
        };

        $.ajax({
            url: '<?php echo esc_url(admin_url('admin-post.php')); ?>',
            method: 'POST',
            data: {
                action: 'save_recipient_data',
                recipient_data: recipientData
            },
            success: function(response) {
                if (response.success) {
                    alert('Dati del destinatario salvati con successo!');
                } else {
                    alert('Errore nel salvataggio dei dati del destinatario.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Errore nella richiesta AJAX:', error);
                alert('Errore nel salvataggio dei dati del destinatario.');
            }
        });
    }














    </script>
    <?php
}
add_shortcode('add_easy_parcel_order_form', 'render_add_easy_parcel_order_page');












// Gestisci la richiesta di salvataggio dei dati del mittente
add_action('admin_post_save_sender_data', 'handle_save_sender_data');
add_action('admin_post_nopriv_save_sender_data', 'handle_save_sender_data');

function handle_save_sender_data() {
    global $wpdb;
    $sender_data = isset($_POST['sender_data']) ? $_POST['sender_data'] : [];

    if (!empty($sender_data)) {
        $sender_data = array(
            'lastname' => sanitize_text_field($sender_data['sender_lastname']),
            'firstname' => sanitize_text_field($sender_data['sender_firstname']),
            'address' => sanitize_text_field($sender_data['sender_address']),
            'city' => sanitize_text_field($sender_data['sender_city']),
            'email' => sanitize_email($sender_data['sender_email']),
            'phone' => sanitize_text_field($sender_data['sender_phone']),
        );
        save_sender_to_db($sender_data);
        wp_send_json_success('Dati salvati con successo');
    } else {
        wp_send_json_error('Nessun dato fornito');
    }
}

// Gestisci la richiesta di salvataggio dei dati del destinatario
add_action('admin_post_save_recipient_data', 'handle_save_recipient_data');
add_action('admin_post_nopriv_save_recipient_data', 'handle_save_recipient_data');

function handle_save_recipient_data() {
    global $wpdb;
    $recipient_data = isset($_POST['recipient_data']) ? $_POST['recipient_data'] : [];

    if (!empty($recipient_data)) {
        $recipient_data = array(
            'lastname' => sanitize_text_field($recipient_data['recipient_lastname']),
            'firstname' => sanitize_text_field($recipient_data['recipient_firstname']),
            'address' => sanitize_text_field($recipient_data['recipient_address']),
            'city' => sanitize_text_field($recipient_data['recipient_city']),
            'email' => sanitize_email($recipient_data['recipient_email']),
            'phone' => sanitize_text_field($recipient_data['recipient_phone']),
        );
        save_recipient_to_db($recipient_data);
        wp_send_json_success('Dati salvati con successo');
    } else {
        wp_send_json_error('Nessun dato fornito');
    }
}










// Registra le azioni AJAX per la ricerca del mittente
add_action('wp_ajax_search_sender', 'search_sender_callback');
add_action('wp_ajax_nopriv_search_sender', 'search_sender_callback');

function search_sender_callback() {
    global $wpdb;

    // Verifica che il parametro 'query' sia presente nella richiesta
    if (!isset($_GET['query'])) {
        wp_send_json_error('Parametro query mancante', 400);
        return;
    }

    $query = sanitize_text_field($_GET['query']);
    $table_name = $wpdb->prefix . 'senders';

    // Esegui una query al database per cercare i mittenti corrispondenti
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT lastname, firstname, address, city, email, phone 
        FROM $table_name 
        WHERE lastname LIKE %s OR firstname LIKE %s OR address LIKE %s OR city LIKE %s",
        '%' . $wpdb->esc_like($query) . '%',
        '%' . $wpdb->esc_like($query) . '%',
        '%' . $wpdb->esc_like($query) . '%',
        '%' . $wpdb->esc_like($query) . '%'
    ));

    if (empty($results)) {
        wp_send_json_error('Nessun risultato trovato', 404);
        return;
    }

    wp_send_json_success($results);
}

// Registra le azioni AJAX per la ricerca del destinatario
add_action('wp_ajax_search_recipient', 'search_recipient_callback');
add_action('wp_ajax_nopriv_search_recipient', 'search_recipient_callback');

function search_recipient_callback() {
    global $wpdb;

    // Verifica che il parametro 'query' sia presente nella richiesta
    if (!isset($_GET['query'])) {
        wp_send_json_error('Parametro query mancante', 400);
        return;
    }

    $query = sanitize_text_field($_GET['query']);
    $table_name = $wpdb->prefix . 'recipients';

    // Esegui una query al database per cercare i destinatari corrispondenti
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT lastname, firstname, address, city, email, phone 
        FROM $table_name 
        WHERE lastname LIKE %s OR firstname LIKE %s OR address LIKE %s OR city LIKE %s",
        '%' . $wpdb->esc_like($query) . '%',
        '%' . $wpdb->esc_like($query) . '%',
        '%' . $wpdb->esc_like($query) . '%',
        '%' . $wpdb->esc_like($query) . '%'
    ));

    if (empty($results)) {
        wp_send_json_error('Nessun risultato trovato', 404);
        return;
    }

    wp_send_json_success($results);
}



















// Funzione per gestire la richiesta del form di aggiunta ordine
function handle_add_easy_parcel_order() {
    // Verifica nonce, permessi utente, ecc.

    // Recupera i dati dal form
    $sender_data = isset($_POST['sender_data']) ? $_POST['sender_data'] : [];
    $recipient_data = isset($_POST['recipient_data']) ? $_POST['recipient_data'] : [];

    $save_sender_data = isset($sender_data['save_sender_data']) ? 1 : 0;
    $save_recipient_data = isset($recipient_data['save_recipient_data']) ? 1 : 0;

    // Se le checkbox sono flaggate, salva i dati del mittente e/o del destinatario
    if ($save_sender_data) {
        $sender_data = array(
            'lastname' => sanitize_text_field($sender_data['sender_lastname']),
            'firstname' => sanitize_text_field($sender_data['sender_firstname']),
            'address' => sanitize_text_field($sender_data['sender_address']),
            'city' => sanitize_text_field($sender_data['sender_city']),
            'email' => sanitize_email($sender_data['sender_email']),
            'phone' => sanitize_text_field($sender_data['sender_phone']),
        );
        // Salva i dati del mittente nel database
        save_sender_to_db($sender_data);
    }

    if ($save_recipient_data) {
        $recipient_data = array(
            'lastname' => sanitize_text_field($recipient_data['recipient_lastname']),
            'firstname' => sanitize_text_field($recipient_data['recipient_firstname']),
            'address' => sanitize_text_field($recipient_data['recipient_address']),
            'city' => sanitize_text_field($recipient_data['recipient_city']),
            'email' => sanitize_email($recipient_data['recipient_email']),
            'phone' => sanitize_text_field($recipient_data['recipient_phone']),
        );
        // Salva i dati del destinatario nel database
        save_recipient_to_db($recipient_data);
    }

    // Invia una risposta JSON
    wp_send_json_success('Dati salvati con successo');
}
add_action('admin_post_add_easy_parcel_order', 'handle_add_easy_parcel_order');
add_action('admin_post_nopriv_add_easy_parcel_order', 'handle_add_easy_parcel_order');

// Funzioni per salvare i dati nel database (implementale in base al tuo database)
function save_sender_to_db($sender_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'senders';
    $wpdb->insert($table_name, $sender_data);
}

function save_recipient_to_db($recipient_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'recipients';
    $wpdb->insert($table_name, $recipient_data);
}









// Funzione per caricare Bootstrap nel backend del plugin
function load_bootstrap_admin() {
    // Include Font Awesome CSS
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    wp_enqueue_style('bootstrap-icons', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.2/font/bootstrap-icons.min.css');

    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
}

// Aggiungi l'azione per caricare Bootstrap nel backend
add_action('admin_enqueue_scripts', 'load_bootstrap_admin');




function get_sender_info($sender_id) {
    global $wpdb;

    $senders_table = $wpdb->prefix . 'senders';
    $sender_info = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $senders_table WHERE sender_id = %d", $sender_id),
        ARRAY_A
    );

    if ($sender_info) {
        return $sender_info['firstname'] . ' ' . $sender_info['lastname'];
    } else {
        return 'Informazioni mittente non trovate';
    }
}

function get_sender_address($sender_id) {
    global $wpdb;

    $senders_table = $wpdb->prefix . 'senders';
    $sender_info = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $senders_table WHERE sender_id = %d", $sender_id),
        ARRAY_A
    );

    if ($sender_info) {
        return $sender_info['address'];
    } else {
        return 'Indirizzo mittente non trovato';
    }
}

function get_sender_city($sender_id) {
    global $wpdb;

    $senders_table = $wpdb->prefix . 'senders';
    $sender_info = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $senders_table WHERE sender_id = %d", $sender_id),
        ARRAY_A
    );

    if ($sender_info) {
        return $sender_info['city'];
    } else {
        return 'Città mittente non trovata';
    }
}

function get_recipient_info($recipient_id) {
    global $wpdb;

    $recipients_table = $wpdb->prefix . 'recipients';
    $recipient_info = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $recipients_table WHERE recipient_id = %d", $recipient_id),
        ARRAY_A
    );

    if ($recipient_info) {
        return $recipient_info['firstname'] . ' ' . $recipient_info['lastname'];
    } else {
        return 'Informazioni destinatario non trovate';
    }
}

function get_recipient_address($recipient_id) {
    global $wpdb;

    $recipients_table = $wpdb->prefix . 'recipients';
    $recipient_info = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $recipients_table WHERE recipient_id = %d", $recipient_id),
        ARRAY_A
    );

    if ($recipient_info) {
        return $recipient_info['address'];
    } else {
        return 'Indirizzo destinatario non trovato';
    }
}

function get_recipient_city($recipient_id) {
    global $wpdb;

    $recipients_table = $wpdb->prefix . 'recipients';
    $recipient_info = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $recipients_table WHERE recipient_id = %d", $recipient_id),
        ARRAY_A
    );

    if ($recipient_info) {
        return $recipient_info['city'];
    } else {
        return 'Città destinatario non trovata';
    }
}













// Funzione per ottenere la classe Bootstrap in base allo stato dell'ordine
function get_order_class($status) {
    switch ($status) {
        case 'Non Pagato':
            return 'table-danger'; // rosso per ordini non pagati
        case 'Annullato':
            return 'table-warning'; // arancione per ordini annullati
        case 'In Lavorazione':
            return 'table-success';
        case 'Pagato':
            return 'table-success';
        case 'Concluso':
            return 'table-success';
// verde per ordini in lavorazione
        default:
            return ''; // nessuna classe aggiuntiva per lo stato predefinito
    }
}

function enqueue_mdb5_assets() {
    wp_enqueue_style('mdb5-css', 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css', array(), '3.10.2');
    wp_enqueue_script('mdb5-js', 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.js', array('jquery'), '3.10.2', true);
}
add_action('wp_enqueue_scripts', 'enqueue_mdb5_assets');


function enqueue_bootstrap() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-icons', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.2/font/bootstrap-icons.min.css');
    // Include Font Awesome CSS
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap');



// Aggiungi azione per gestire l'eliminazione di un ordine
function handle_delete_easy_parcel_order() {
    global $wpdb;

    if (isset($_POST['delete_order'])) {
        // Verifica nonce per la sicurezza
        if (!isset($_POST['delete_order_nonce_field']) || !wp_verify_nonce($_POST['delete_order_nonce_field'], 'delete_order_nonce')) {
            wp_die('Nonce di sicurezza non verificato.');
        }

        $order_id = intval($_POST['order_id']);

        // Effettua l'eliminazione dell'ordine dal database
        $orders_table = $wpdb->prefix . 'orders';
        $result = $wpdb->delete($orders_table, array('order_id' => $order_id));

        if ($result !== false) {
            // Redirect alla pagina degli ordini dopo l'eliminazione dell'ordine con un parametro di successo
            wp_redirect(admin_url('admin.php?page=easy-parcel-orders&delete=success'));
            exit;
        } else {
            // Redirect alla pagina degli ordini dopo l'eliminazione dell'ordine con un parametro di errore
            wp_redirect(admin_url('admin.php?page=easy-parcel-orders&delete=error'));
            exit;
        }
    }
}
add_action('admin_post_delete_easy_parcel_order', 'handle_delete_easy_parcel_order');


// Aggiungi azione per gestire la modifica dello stato di un ordine
function handle_change_order_status() {
    global $wpdb;

    if (isset($_POST['change_status'])) {
        // Verifica nonce per la sicurezza
        if (!isset($_POST['change_status_nonce_field']) || !wp_verify_nonce($_POST['change_status_nonce_field'], 'change_status_nonce')) {
            wp_die('Nonce di sicurezza non verificato.');
        }

        $order_id = intval($_POST['order_id']);
        $new_status = sanitize_text_field($_POST['new_status']);

        // Effettua l'aggiornamento dello stato dell'ordine nel database
        $orders_table = $wpdb->prefix . 'orders';
        $result = $wpdb->update(
            $orders_table,
            array('status' => $new_status),
            array('order_id' => $order_id)
        );

        if ($result !== false) {
            // Se l'aggiornamento è avvenuto con successo, reindirizza con un parametro di successo
            wp_redirect(admin_url('admin.php?page=easy-parcel-orders&status=success'));
            exit;
        } else {
            // Se si verifica un errore nell'aggiornamento, reindirizza con un parametro di errore
            wp_redirect(admin_url('admin.php?page=easy-parcel-orders&status=error'));
            exit;
        }
    }
}
add_action('admin_post_change_order_status', 'handle_change_order_status');


function order_details_shortcode() {
    ob_start();
    ?>
    <div id="order-details-container">
        <table id="order-details-table" class="table table-striped">
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valore</th>
                </tr>
            </thead>
            <tbody>
                <!-- I dettagli dell'ordine saranno aggiunti qui da JavaScript -->
            </tbody>
        </table>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var orderDetails = JSON.parse(sessionStorage.getItem('orderDetails'));

            if (orderDetails) {
                var tbody = document.querySelector('#order-details-table tbody');
                for (var key in orderDetails) {
                    if (orderDetails.hasOwnProperty(key)) {
                        var tr = document.createElement('tr');
                        var tdKey = document.createElement('td');
                        var tdValue = document.createElement('td');
                        
                        tdKey.textContent = key;
                        tdValue.textContent = orderDetails[key];
                        
                        tr.appendChild(tdKey);
                        tr.appendChild(tdValue);
                        
                        tbody.appendChild(tr);
                    }
                }
            } else {
                var container = document.getElementById('order-details-container');
                container.innerHTML = '<p>Nessun dettaglio dell\'ordine trovato. Si prega di effettuare un ordine prima.</p>';
            }
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('order_details', 'order_details_shortcode');


function easy_parcel_tracking_shortcode() {
    ob_start();
    ?>
    <form id="easy-parcel-tracking-form">
        <label for="tracking-type">Seleziona il tipo di parametro di ricerca:</label>
        <select id="tracking-type" name="tracking-type">
            <option value="codice_offerta">Codice Offerta</option>
            <option value="ldv">LDV</option>
            <option value="custom">Custom</option>
        </select>
        <div id="tracking-inputs">
            <div id="codice_offerta_div">
                <label for="codice_offerta">Codice Offerta:</label>
                <input type="text" id="codice_offerta" name="codice_offerta">
            </div>
            <div id="ldv_div" style="display: none;">
                <label for="ldv">LDV:</label>
                <input type="text" id="ldv" name="ldv">
            </div>
            <div id="custom_div" style="display: none;">
                <label for="custom">Custom:</label>
                <input type="text" id="custom" name="custom">
            </div>
        </div>
        <button type="submit">Traccia Spedizione</button>
    </form>
    <div id="tracking-result"></div>
    <script>
    (function($) {
        $(document).ready(function() {
            // Gestione della visibilità dei campi in base alla selezione
            $('#tracking-type').on('change', function() {
                var selectedType = $(this).val();
                $('#tracking-inputs > div').hide(); // Nascondi tutti i div
                $('#' + selectedType + '_div').show(); // Mostra solo il div selezionato
            });

            $('#easy-parcel-tracking-form').on('submit', function(e) {
                e.preventDefault();
                
                var trackingType = $('#tracking-type').val();
                var trackingParam = $('#' + trackingType).val();
                
                // Prepara il payload della richiesta JSON
                var requestData = {
                    call: 'tracking',
                    dettagli: {}
                };
                requestData.dettagli[trackingType] = trackingParam;

                $.ajax({
                    url: "/wp-content/plugins/easy-parcel/js/proxy_tracking.php",
                    type: "POST",
                    contentType: "application/json",
                    dataType: "json", // Specifica il tipo di dato che ci si aspetta
                    data: JSON.stringify(requestData),
                    success: function(data) {
                        console.log('Response Data:', data); // Log della risposta
                        if (data.result === 'OK') {
                            // Crea il markup per visualizzare le informazioni di tracking
                            var trackingInfo = data.tracking;
                            var details = data.dettagli;

                            var resultHtml = '<h4>Tracking Information</h4>';
                            resultHtml += '<p><strong>Codice Offerta:</strong> ' + (trackingInfo.codice_offerta || 'N/A') + '</p>';
                            resultHtml += '<p><strong>Custom:</strong> ' + (trackingInfo.custom || 'N/A') + '</p>';
                            resultHtml += '<p><strong>Letterà Vettura:</strong> ' + (trackingInfo.lettera_vettura || 'N/A') + '</p>';
                            resultHtml += '<p><strong>URL Tracking:</strong> <a href="' + (trackingInfo.url_tracking || '#') + '" target="_blank">' + (trackingInfo.url_tracking || 'N/A') + '</a></p>';

                            if (details && details.length > 0) {
                                resultHtml += '<h5>Dettagli Tracking</h5><ul class="list-group">';
                                details.forEach(function(detail) {
                                    resultHtml += '<li class="list-group-item">';
                                    resultHtml += '<strong>' + detail.data + ' ' + detail.ora + ':</strong> ' + detail.descrizione + ' - ' + detail.note;
                                    if (detail.filiale) {
                                        resultHtml += ' (Filiale: ' + detail.filiale + ')';
                                    }
                                    resultHtml += '</li>';
                                });
                                resultHtml += '</ul>';
                            }

                            $('#tracking-result').html(resultHtml);
                        } else {
                            $('#tracking-result').html('<p>Errore: ' + data.message + '</p>');
                        }
                    },
                    error: function(error) {
                        console.error('AJAX Error:', error); // Log degli errori AJAX
                        $('#tracking-result').html('<p>Errore nella chiamata di tracking. Si prega di riprovare più tardi.</p>');
                    }
                });
            });

            // Imposta il valore iniziale del campo di input
            $('#tracking-type').trigger('change');
        });
    })(jQuery);
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('easy_parcel_tracking', 'easy_parcel_tracking_shortcode');















/*

function riepilogo_ordine_shortcode() {
    ob_start();
    
    // Recupera il codice dell'offerta dalla query string
    $codice_offerta = isset($_GET['codice_offerta']) ? sanitize_text_field($_GET['codice_offerta']) : '';

    if ($codice_offerta) {
        ?>
        <div class="container">
            <h3>Dettagli Ordine</h3>
            <div id="order-details" style="margin-top: 20px;"></div>
            <script>
            jQuery(document).ready(function($) {
                // Funzione per ottenere i dettagli dell'ordine
                function getOrderDetails(codice_offerta) {
                    return $.ajax({
                        url: '/wp-content/plugins/easy-parcel/js/proxy-get-order.php',
                        type: 'GET',
                        data: {
                            search_by: 'codice_offerta',
                            search_value: codice_offerta
                        }
                    });
                }

                // Funzione per ottenere la lettera di vettura
                function getWaybill(orderId) {
                    return $.ajax({
                        url: '/wp-content/plugins/easy-parcel/js/proxy_getwaybill.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            "call": "getwaybill",
                            "details": {
                                "order_id": orderId,
                                "waybill_base64": "Y",
                                "single_waybills": "Y"
                            }
                        })
                    });
                }

                // Recupera i dettagli dell'ordine e visualizza le informazioni
                getOrderDetails('<?php echo $codice_offerta; ?>').done(function(data) {
                    if (data.result === "OK") {
                        var order = data.order;

                        var dettagli = '';
                        if (data.dettagli && data.dettagli.length > 0) {
                            dettagli = data.dettagli.map(function(item) {
                                return `
                                    <tr>
                                        <td>${item.peso}</td>
                                        <td>${item.larghezza}</td>
                                        <td>${item.profondita}</td>
                                        <td>${item.altezza}</td>
                                        <td>${item.nr_colli}</td>
                                    </tr>
                                `;
                            }).join('');
                        }

                        var html = `
                            <div class="order-info">
                                <h4>Di seguito tutti i Dettagli dell'Ordine</h4>
                                <table>
                                    <tr><th>Campo</th><th>Valore</th></tr>
                                    <tr><td>Codice Offerta</td><td>${order.codice_offerta}</td></tr>
                                    <tr><td>Data</td><td>${order.data}</td></tr>
                                    <tr><td>Utente</td><td>${order.utente}</td></tr>
                                    <tr><td>Ragione Sociale</td><td>${order.ragionesociale}</td></tr>
                                    <tr><td>Indirizzo</td><td>${order.indirizzo}</td></tr>
                                    <tr><td>CAP</td><td>${order.cap}</td></tr>
                                    <tr><td>Località</td><td>${order.localita}</td></tr>
                                    <tr><td>Provincia</td><td>${order.provincia}</td></tr>
                                    <tr><td>Codice Fiscale</td><td>${order.codicefiscale}</td></tr>
                                    <tr><td>Partita IVA</td><td>${order.partitaiva}</td></tr>
                                    <tr><td>Telefono</td><td>${order.telefono}</td></tr>
                                    <tr><td>Cellulare</td><td>${order.cellulare}</td></tr>
                                    <tr><td>Email</td><td>${order.email}</td></tr>
                                    <tr><td>Cosa</td><td>${order.cosa}</td></tr>
                                    <tr><td>Peso Totale</td><td>${order.peso_totale}</td></tr>
                                    <tr><td>Tipo Spedizione</td><td>${order.tipo_spedizione}</td></tr>
                                    <tr><td>Vettore</td><td>${order.vettore}</td></tr>
                                    <tr><td>Nome Vettore</td><td>${order.nome_vettore}</td></tr>
                                    <tr><td>Logo Vettore</td><td><img src="${order.logo_vettore}" alt="Logo Vettore" style="width: 100px;"></td></tr>
                                    <tr><td>Mittente Nominativo</td><td>${order.mittente_nominativo}</td></tr>
                                    <tr><td>Mittente Indirizzo</td><td>${order.mittente_indirizzo}</td></tr>
                                    <tr><td>Mittente Località</td><td>${order.mittente_localita}</td></tr>
                                    <tr><td>Mittente Provincia</td><td>${order.mittente_provincia}</td></tr>
                                    <tr><td>Mittente CAP</td><td>${order.mittente_cap}</td></tr>
                                    <tr><td>Mittente Email</td><td>${order.mittente_email}</td></tr>
                                    <tr><td>Mittente Telefono</td><td>${order.mittente_telefono}</td></tr>
                                    <tr><td>Mittente Cellulare</td><td>${order.mittente_cellulare}</td></tr>
                                    <tr><td>Mittente Contatto</td><td>${order.mittente_contatto}</td></tr>
                                    <tr><td>Mittente CF</td><td>${order.mittente_cf}</td></tr>
                                    <tr><td>Destinatario Nominativo</td><td>${order.destinatario_nominativo}</td></tr>
                                    <tr><td>Destinatario Indirizzo</td><td>${order.destinatario_indirizzo}</td></tr>
                                    <tr><td>Destinatario Località</td><td>${order.destinatario_localita}</td></tr>
                                    <tr><td>Destinatario CAP</td><td>${order.destinatario_cap}</td></tr>
                                    <tr><td>Destinatario Provincia</td><td>${order.destinatario_provincia}</td></tr>
                                    <tr><td>Destinatario Nazione</td><td>${order.destinatario_nazione}</td></tr>
                                    <tr><td>Destinatario Email</td><td>${order.destinatario_email}</td></tr>
                                    <tr><td>Destinatario Telefono</td><td>${order.destinatario_telefono}</td></tr>
                                    <tr><td>Destinatario Cellulare</td><td>${order.destinatario_cellulare}</td></tr>
                                    <tr><td>Destinatario Contatto</td><td>${order.destinatario_contatto}</td></tr>
                                    <tr><td>Consegna</td><td>${order.consegna}</td></tr>
                                    <tr><td>Totale Dovuto</td><td>${order.totale_dovuto}</td></tr>
                                    <tr><td>Percentuale IVA</td><td>${order.percentualeiva}</td></tr>
                                    <tr><td>Importo Tariffa</td><td>${order.importo_tariffa}</td></tr>
                                    <tr><td>Peso Volumetrico</td><td>${order.peso_volumetrico}</td></tr>
                                    <tr><td>Contrassegno</td><td>${order.contrassegno}</td></tr>
                                    <tr><td>Contrassegno Importo</td><td>${order.contrassegno_importo}</td></tr>
                                    <tr><td>Contrassegno Modalità</td><td>${order.contrassegno_modalita}</td></tr>
                                    <tr><td>Assicurazione</td><td>${order.assicurazione}</td></tr>
                                    <tr><td>Assicurazione Importo</td><td>${order.assicurazione_importo}</td></tr>
                                    <tr><td>Consegna al Piano</td><td>${order.consegnaalpiano}</td></tr>
                                    <tr><td>Appuntamento</td><td>${order.appuntamento}</td></tr>
                                    <tr><td>Prior Notice</td><td>${order.priornotice}</td></tr>
                                    <tr><td>Ritiro</td><td>${order.ritiro}</td></tr>
                                    <tr><td>Ritiro Dove</td><td>${order.ritiro_dove}</td></tr>
                                    <tr><td>Ritiro Disponibile Data</td><td>${order.ritiro_disp_data}</td></tr>
                                    <tr><td>Ritiro Disponibile Ora</td><td>${order.ritiro_disp_ora}</td></tr>
                                    <tr><td>Note Cliente</td><td>${order.note_cliente}</td></tr>
                                    <tr><td>Note Contenuto</td><td>${order.note_contenuto}</td></tr>
                                    <tr><td>Custom</td><td>${order.custom}</td></tr>
                                    <tr><td>Lettera di Vettura</td><td>${order.lettera_vettura ? order.lettera_vettura : 'Non disponibile'}</td></tr>
                                    <tr><td>ID Ordine</td><td>${order.idordine}</td></tr>
                                    <tr><td>URL Borderò</td><td><a href="${order.url_bordero}" target="_blank">Apri Borderò</a></td></tr>
                                </table>

                                <h4>Dettagli Colli</h4>
                                <table>
                                    <tr><th>Peso</th><th>Larghezza</th><th>Profondità</th><th>Altezza</th><th>Nr Colli</th></tr>
                                    ${dettagli}
                                </table>

                                <div class="download-section">
                                    <button id="download-waybill" class="btn btn-secondary">Scarica Lettera di Vettura</button>
                                </div>
                            </div>
                        `;

                        $('#order-details').html(html);

                        // Aggiungi l'evento click al pulsante di download della lettera di vettura
                        $('#download-waybill').on('click', function() {
                            getWaybill(order.idordine).done(function(response) {
                                if (response.result === "OK" && response.url_ldv) {
                                    window.location.href = response.url_ldv;
                                } else {
                                    alert('Errore nel recupero della lettera di vettura.');
                                }
                            }).fail(function() {
                                alert('Errore nella richiesta della lettera di vettura.');
                            });
                        });
                    } else {
                        $('#order-details').html('<p>Errore nella richiesta: ' + data.errormessage + '</p>');
                    }
                }).fail(function() {
                    $('#order-details').html('<p>Errore nella richiesta dei dettagli dell\'ordine.</p>');
                });
            });
            </script>
        </div>
        <?php
    } else {
        ?>
        <div class="container">
            <p>Codice Offerta non specificato.</p>
        </div>
        <?php
    }

    return ob_get_clean();
}
add_shortcode('riepilogo_ordine', 'riepilogo_ordine_shortcode');



*/





function riepilogo_ordine_shortcode() {
ob_start();

// Recupera il codice dell'offerta dalla query string
$codice_offerta = isset($_GET['codice_offerta']) ? sanitize_text_field($_GET['codice_offerta']) : '';

if ($codice_offerta) {
    ?>
    <div class="container">
        <h3>Dettagli Ordine</h3>
        <div id="order-details" style="margin-top: 20px;"></div>
        <script>
            jQuery(document).ready(function($) {
// Funzione per ottenere i dettagli dell'ordine
function getOrderDetails(codice_offerta) {
    return $.ajax({
        url: '/wp-content/plugins/easy-parcel/js/proxy-get-order.php',
        type: 'GET',
        data: {
            search_by: 'codice_offerta',
            search_value: codice_offerta
        }
    });
}

// Recupera i dettagli dell'ordine e visualizza le informazioni
getOrderDetails('<?php echo $codice_offerta; ?>').done(function(data) {
    if (data.result === "OK") {
        var order = data.order;
        var waybillNumber = order.lettera_vettura; // Usa il valore restituito dalla chiamata

        var dettagli = '';
        if (data.dettagli && data.dettagli.length > 0) {
            dettagli = data.dettagli.map(function(item) {
                return `
                    <tr>
                        <td>${item.peso}</td>
                        <td>${item.larghezza}</td>
                        <td>${item.profondita}</td>
                        <td>${item.altezza}</td>
                        <td>${item.nr_colli}</td>
                    </tr>
                `;
            }).join('');
        }

        var html = `
        <!-- Aggiungi questo tag nella sezione <head> del tuo documento HTML -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<div class="order-info container mt-4 p-3 border rounded">
<h4 class="mb-4">Dettagli dell'Ordine</h4>

<!-- Informazioni Generali -->
<h5 class="section-title"><i class="bi bi-info-circle"></i> Informazioni Generali</h5>
<table class="table table-striped table-sm">
    <tr><th>Codice Offerta</th><td>${order.codice_offerta}</td></tr>
    <tr><th>Data</th><td>${order.data}</td></tr>
    <tr><th>Utente</th><td>${order.utente}</td></tr>
    <tr><th>Ragione Sociale</th><td>${order.ragionesociale}</td></tr>
    <tr><th>Cosa Spedire</th><td>${order.cosa} = Merce/Pacchi</td></tr>
    <tr><th>Peso Totale</th><td>${order.peso_totale} (Grammi)</td></tr>
    <tr><th>Tipo Spedizione</th><td>${order.tipo_spedizione} (Nazionale)</td></tr>
</table>

<!-- Informazioni sul Vettore -->
<h5 class="section-title mt-4"><i class="bi bi-truck"></i> Informazioni sul Vettore</h5>
<table class="table table-striped table-sm">
    <tr><th>Nome Vettore</th><td>${order.nome_vettore}</td></tr>
    <tr><th>Logo Vettore</th><td><img src="${order.logo_vettore}" alt="Logo Vettore" class="img-fluid" style="width: 100px;"></td></tr>
</table>

<!-- Informazioni sul Mittente -->
<h5 class="section-title mt-4"><i class="bi bi-person-fill"></i> Informazioni sul Mittente</h5>
<table class="table table-striped table-sm">
    <tr><th>Nominativo</th><td>${order.mittente_nominativo}</td></tr>
    <tr><th>Indirizzo</th><td>${order.mittente_indirizzo}</td></tr>
    <tr><th>Località</th><td>${order.mittente_localita}</td></tr>
    <tr><th>Provincia</th><td>${order.mittente_provincia}</td></tr>
    <tr><th>CAP</th><td>${order.mittente_cap}</td></tr>
    <tr><th>Email</th><td>${order.mittente_email}</td></tr>
    <tr><th>Telefono</th><td>${order.mittente_telefono}</td></tr>
    <tr><th>Cellulare</th><td>${order.mittente_cellulare}</td></tr>
    <tr><th>Contatto</th><td>${order.mittente_contatto}</td></tr>
    <tr><th>Codice Fiscale</th><td>${order.mittente_cf}</td></tr>
</table>

<!-- Informazioni sul Destinatario -->
<h5 class="section-title mt-4"><i class="bi bi-person-check-fill"></i> Informazioni sul Destinatario</h5>
<table class="table table-striped table-sm">
    <tr><th>Nominativo</th><td>${order.destinatario_nominativo}</td></tr>
    <tr><th>Indirizzo</th><td>${order.destinatario_indirizzo}</td></tr>
    <tr><th>Località</th><td>${order.destinatario_localita}</td></tr>
    <tr><th>CAP</th><td>${order.destinatario_cap}</td></tr>
    <tr><th>Provincia</th><td>${order.destinatario_provincia}</td></tr>
    <tr><th>Nazione</th><td>${order.destinatario_nazione}</td></tr>
    <tr><th>Email</th><td>${order.destinatario_email}</td></tr>
    <tr><th>Telefono</th><td>${order.destinatario_telefono}</td></tr>
    <tr><th>Cellulare</th><td>${order.destinatario_cellulare}</td></tr>
    <tr><th>Contatto</th><td>${order.destinatario_contatto}</td></tr>
</table>

<!-- Dettagli della Spedizione -->
<h5 class="section-title mt-4"><i class="bi bi-box"></i> Dettagli della Spedizione</h5>
<table class="table table-striped table-sm">
    <tr><th>Consegna</th><td>${order.consegna}</td></tr>
    <tr><th>Totale Dovuto</th><td>${order.totale_dovuto}</td></tr>
    <tr><th>Percentuale IVA</th><td>${order.percentualeiva}</td></tr>
    <tr><th>Importo Tariffa</th><td>${order.importo_tariffa}</td></tr>
    <tr><th>Peso Volumetrico</th><td>${order.peso_volumetrico}</td></tr>
</table>

<!-- Servizi Aggiuntivi -->
<h5 class="section-title mt-4"><i class="bi bi-gear"></i> Servizi Aggiuntivi</h5>
<table class="table table-striped table-sm">
    <tr><th>Contrassegno</th><td>${order.contrassegno}</td></tr>
    <tr><th>Importo Contrassegno</th><td>${order.contrassegno_importo}</td></tr>
    <tr><th>Modalità Contrassegno</th><td>${order.contrassegno_modalita}</td></tr>
    <tr><th>Assicurazione</th><td>${order.assicurazione}</td></tr>
    <tr><th>Importo Assicurazione</th><td>${order.assicurazione_importo}</td></tr>
    <tr><th>Consegna al Piano</th><td>${order.consegnaalpiano}</td></tr>
    <tr><th>Appuntamento</th><td>${order.appuntamento}</td></tr>
    <tr><th>Prior Notice</th><td>${order.priornotice}</td></tr>
</table>

<!-- Informazioni sul Ritiro -->
<h5 class="section-title mt-4"><i class="bi bi-box-arrow-in-down"></i> Informazioni sul Ritiro</h5>
<table class="table table-striped table-sm">
    <tr><th>Ritiro</th><td>${order.ritiro}</td></tr>
    <tr><th>Ritiro Dove</th><td>${order.ritiro_dove}</td></tr>
    <tr><th>Disponibilità Data</th><td>${order.ritiro_disp_data}</td></tr>
    <tr><th>Disponibilità Ora</th><td>${order.ritiro_disp_ora}</td></tr>
</table>

<!-- Note e Altre Informazioni -->
<h5 class="section-title mt-4"><i class="bi bi-pencil-square"></i> Note e Altre Informazioni</h5>
<table class="table table-striped table-sm">
    <tr><th>Note Cliente</th><td>${order.note_cliente}</td></tr>
    <tr><th>Note Contenuto</th><td>${order.note_contenuto}</td></tr>
    <tr><th>Custom</th><td>${order.custom}</td></tr>
    <tr><th>Lettera di Vettura</th><td>${waybillNumber ? waybillNumber : 'Non disponibile'}</td></tr>
    <tr><th>ID Ordine</th><td>${order.idordine}</td></tr>
    <tr><th>URL Borderò</th><td><a href="${order.url_bordero}" target="_blank">Apri Borderò</a></td></tr>
</table>

<!-- Dettagli Colli -->
<h5 class="section-title mt-4"><i class="bi bi-stack"></i> Dettagli Colli</h5>
<table class="table table-striped table-sm">
    <tr><th>Peso</th><th>Larghezza</th><th>Profondità</th><th>Altezza</th><th>Nr Colli</th></tr>
    ${dettagli}
</table>

<!-- Scarica Lettera di Vettura -->
<div class="download-section text-end">
    <button id="download-waybill" class="btn btn-secondary">
        <i class="bi bi-download"></i> Scarica Lettera di Vettura
    </button>
</div>
</div>
        `;

        $('#order-details').html(html);

        // Aggiungi l'evento click al pulsante di download della lettera di vettura
        $('#download-waybill').on('click', function() {
            console.log("Download della Lettera di Vettura per l'ordine: " + order.idordine);
            getWaybill(order.idordine).done(function(response) {
                if (response.result === "OK" && response.waybill_url) {
                    window.location.href = response.waybill_url;
                } else {
                    alert('Errore nel recupero della lettera di vettura.');
                }
            }).fail(function() {
                alert('Errore nella richiesta della lettera di vettura.');
            });
        });
    } else {
        $('#order-details').html('<p>Errore nella richiesta: ' + data.errormessage + '</p>');
    }
}).fail(function() {
    $('#order-details').html('<p>Errore nella richiesta dei dettagli dell\'ordine.</p>');
});
});

        </script>
    </div>
    <?php
} else {
    ?>
    <div class="container">
        <p>Codice Offerta non specificato.</p>
    </div>
    <?php
}

return ob_get_clean();
}
add_shortcode('riepilogo_ordine', 'riepilogo_ordine_shortcode');










































function run_easy_parcel() {

    $plugin = new Easy_Parcel();
    $plugin->run();

}
run_easy_parcel();














function get_order_shortcode() {
ob_start();
?>
<div class="container">
    <h3>Visualizza Dettagli Ordine</h3>
    <form id="get-order-form">
        <div class="form-group">
            <label for="search_by">Cerca per:</label>
            <select id="search_by" name="search_by" class="form-control">
                <option value="codice_offerta">Codice Offerta</option>
                <option value="custom">Custom</option>
            </select>
        </div>
        <div class="form-group">
            <label for="search_value">Valore:</label>
            <input type="text" id="search_value" name="search_value" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Cerca</button>
    </form>
    <div id="order-details" style="margin-top: 20px;"></div>
</div>
<style>
    .order-info {
        margin: 20px 0;
    }
    .order-info h4 {
        margin-top: 10px;
    }
    .order-info p {
        margin: 5px 0;
    }
    .order-info .detail {
        margin-bottom: 10px;
    }
    .order-info .detail span {
        font-weight: bold;
    }
    .order-info .detail .label {
        font-size: 1.1em;
        margin-right: 10px;
    }
    .order-info .detail .value {
        color: #333;
    }
    .order-info .detail img {
        max-width: 100px;
    }
</style>
<script>
    jQuery(document).ready(function($) {
        $('#get-order-form').on('submit', function(e) {
            e.preventDefault();
            var searchBy = $('#search_by').val();
            var searchValue = $('#search_value').val();

            $.ajax({
                url: '/wp-content/plugins/easy-parcel/js/proxy-get-order.php',
                type: 'GET',
                data: {
                    search_by: searchBy,
                    search_value: searchValue
                },
                success: function(data) {
                    if (data.result === "OK") {
                        var order = data.order;
                        var dettagli = data.dettagli[0]; // Assuming there's only one detail object

                        var html = `
                            <div class="order-info">
                                <h4>Dettagli Ordine</h4>
                                <div class="detail"><span class="label">Codice Offerta:</span> <span class="value">${order.codice_offerta}</span></div>
                                <div class="detail"><span class="label">Data:</span> <span class="value">${order.data}</span></div>
                                <div class="detail"><span class="label">Mittente:</span> <span class="value">${order.mittente_nominativo}, ${order.mittente_indirizzo}, ${order.mittente_localita}, ${order.mittente_cap}</span></div>
                                <div class="detail"><span class="label">Destinatario:</span> <span class="value">${order.destinatario_nominativo}, ${order.destinatario_indirizzo}, ${order.destinatario_localita}, ${order.destinatario_cap}</span></div>
                                <div class="detail"><span class="label">Peso Totale:</span> <span class="value">${order.peso_totale} ${data.unitamisura.split('-')[1]}</span></div>
                                <div class="detail"><span class="label">Tipo Spedizione:</span> <span class="value">${order.tipo_spedizione}</span></div>
                                <div class="detail"><span class="label">Vettore:</span> <span class="value">${order.nome_vettore}</span></div>
                                <div class="detail"><span class="label">Tracking:</span> <span class="value">${order.trackingavanzato === 'Y' ? 'Abilitato' : 'Non Abilitato'}</span></div>
                                <div class="detail"><span class="label">Peso Volumetrico:</span> <span class="value">${order.peso_volumetrico} ${data.unitamisura.split('-')[1]}</span></div>
                                <div class="detail"><span class="label">Consegna:</span> <span class="value">${order.consegna}</span></div>
                                <div class="detail"><span class="label">Totale Dovuto:</span> <span class="value">${order.totale_dovuto} ${data.valuta}</span></div>
                                <div class="detail"><span class="label">Ritiro:</span> <span class="value">${order.ritiro}</span></div>
                                <div class="detail"><span class="label">Ritiro Disponibile:</span> <span class="value">${order.ritiro_disp_data} ${order.ritiro_disp_ora}</span></div>
                                <div class="detail"><span class="label">URL Logo Vettore:</span> <span class="value"><img src="${order.logo_vettore}" alt="Logo Vettore"></span></div>
                            </div>
                        `;
                        $('#order-details').html(html);
                    } else {
                        $('#order-details').html('<p>Errore nella richiesta: ' + data.errormessage + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#order-details').html('<p>Errore nella richiesta: ' + error + '</p>');
                }
            });
        });
    });
</script>
<?php
return ob_get_clean();
}
add_shortcode('get_order', 'get_order_shortcode');







// Definisci lo shortcode per visualizzare il bilancio
function balance_shortcode() {
ob_start();
?>
<div class="balance-container">
    <h4>Bilancio Boxes Point</h4>
    <form id="balance_form" method="post">
        <label for="idcustomer">ID Cliente (opzionale):</label>
        <input type="number" id="idcustomer" name="idcustomer">
        <button type="submit" id="view_balance_btn">Visualizza Bilancio</button>
    </form>
    
    <div id="balance_result"></div> <!-- Spazio per visualizzare il bilancio -->
</div>

<script>
jQuery(document).ready(function($) {
    $('#balance_form').on('submit', function(e) {
        e.preventDefault(); // Evita il comportamento predefinito del form
        var formData = {
            'call': 'balance',
            'dettagli': {}
        };
        var idcustomer = $('#idcustomer').val();
        if (idcustomer) {
            formData.dettagli.idcustomer = idcustomer;
        }
        console.log("Richiesta: ", formData);
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>', // URL per la chiamata AJAX al backend
            data: {
                action: 'get_balance', // Azione personalizzata per la gestione della richiesta
                form_data: JSON.stringify(formData) // Dati del form
            },
            success: function(response) {
                // Decodifica la risposta JSON
                var responseData = JSON.parse(response);
                
                // Verifica se la risposta è stata decodificata correttamente
                if (responseData !== null && responseData.result === 'OK') {
                    // Chiama la funzione per costruire la visualizzazione del bilancio
                    renderBalance(responseData);
                } else {
                    // Mostra un messaggio di errore se la richiesta non è andata a buon fine
                    $('#balance_result').html('<p>Errore durante il recupero del bilancio: ' + responseData.errormessage + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // Gestisci eventuali errori
            }
        });
    });
    
    // Funzione per costruire la visualizzazione del bilancio
    function renderBalance(data) {
        var balanceHtml = '<div class="alert alert-info">';
        balanceHtml += '<p><strong>Bilancio Master:</strong> ' + data.balance_master + ' €</p>';
        if (data.balance_customer) {
            balanceHtml += '<p><strong>Bilancio Cliente:</strong> ' + data.balance_customer + ' €</p>';
        }
        balanceHtml += '<p><strong>Data:</strong> ' + data.timestamp + '</p>';
        balanceHtml += '</div>';
        
        // Inserisci il bilancio HTML nell'elemento con id "balance_result"
        $('#balance_result').html(balanceHtml);
    }
});
</script>
<?php
return ob_get_clean();
}
add_shortcode('balance', 'balance_shortcode');