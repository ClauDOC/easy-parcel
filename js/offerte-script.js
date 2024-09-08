jQuery(document).ready(function($) {
    // Funzione per manipolare il prezzo dell'offerta
    function aumentaPrezzo() {
        // Seleziona tutti gli elementi che contengono il prezzo originale delle offerte
        $('.offer p:contains("Importo originale")').each(function() {
            // Estrai il testo che rappresenta il prezzo originale
            var prezzoOriginaleText = $(this).text();
            // Estrai l'importo numerico dal testo
            var prezzoOriginale = parseFloat(prezzoOriginaleText.replace('Importo originale: €', ''));
            // Calcola il nuovo prezzo aumentato del 50%
            var nuovoPrezzo = prezzoOriginale * 1.5;
            // Aggiorna il testo per visualizzare il nuovo prezzo
            $(this).next('p').text('Importo maggiorato del 50%: €' + nuovoPrezzo.toFixed(2));
        });
    }

    // Chiama la funzione per aumentare il prezzo quando la pagina è pronta
    aumentaPrezzo();
});
