(function( $ ) {
    'use strict';
    $(document).ready(function() {
        // Gestione della selezione delle dimensioni predefinite
        $('#dimensioni_predefinite').change(function() {
            var dimensioni = $(this).find(':selected').text().match(/\(([^)]+)\)/)[1].split("x").map(Number); // Ottieni le dimensioni come array di numeri
            $('#lunghezza').val(dimensioni[0]);
            $('#larghezza').val(dimensioni[1]);
            $('#altezza').val(dimensioni[2]);
        });

        // Aggiunta di un pacco alla lista dei colli
        $('#aggiungi_button').click(function() {
            var lunghezza = parseFloat($('#lunghezza').val());
            var larghezza = parseFloat($('#larghezza').val());
            var altezza = parseFloat($('#altezza').val());
            var colliNr = parseInt($('#colli_nr').val());
            var pesoTotale = parseFloat($('#peso_totale').val());

            // Verifica se le dimensioni sono valide
            if (!isNaN(lunghezza) && !isNaN(larghezza) && !isNaN(altezza)) {
                // Aggiungi il pacco alla lista dei colli
                var nuovoColloHtml = "<div>Collo " + (colliNr + 1) + ": " + lunghezza + " x " + larghezza + " x " + altezza + "</div>";
                $('#div_colli').append(nuovoColloHtml);

                // Aggiorna il numero di colli e il peso totale
                $('#colli_nr').val(colliNr + 1);
                $('#peso_totale').val(pesoTotale + (lunghezza * larghezza * altezza));
            } else {
                alert("Inserisci dimensioni valide per il pacco.");
            }
        });
    });
})( jQuery );
