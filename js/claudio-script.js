// Variabili globali per i dati del form
var peso, larghezza, profondita, altezza, nr_colli;
var mnaz, mcap, mloc, mpro, dnaz, dcap, dloc, dpro, dcou, area;

// Funzione per recuperare i dati dal form
function retrieveFormData() {
    // Recupero dei valori dinamici dal form
    peso = $("#peso").val();
    larghezza = $("#larghezza").val();
    profondita = $("#lunghezza").val();
    altezza = $("#altezza").val();
    nr_colli = $("#colli").val();
    mnaz = $("#nazione1").val();
    mcap = $("#cap1").val();
    mloc = $("#localita1").val();
    mpro = $("#provincia1").val();
    dnaz = $("#nazione2").val();
    dcap = $("#cap2").val();
    dloc = $("#localita2").val();
    dpro = $("#provincia2").val();
    dcou = $("#country2").val();
    area = $("#area").val();
}

// Funzione per memorizzare le anagrafiche
function storeAnagrafiche() {
    $.post("ajax.php", {
        azione: "memorizza_anagrafiche",
        mnaz: mnaz,
        mcap: mcap,
        mloc: mloc,
        mpro: mpro,
        dnaz: dnaz,
        dcap: dcap,
        dloc: dloc,
        dpro: dpro,
        dcou: dcou,
        area: area
    }).done(function(response) {
        console.log("Risposta da ajax.php:", response);
        // Continua con le altre operazioni...
    }).fail(function(error) {
        console.error("Errore AJAX in ajax.php:", error);
        $("#btn_procedi").html("RIPROVA");
    });
}








// Funzione per salvare i dati del mittente e del destinatario nel sessionStorage
function salvailFormnelSessionStorage() {
    console.log("STO SALVANDO QUESTI MINCHIA DI DATI...");
    // Recupero dei valori dinamici dal form
    var senderLastName = $("#sender_lastname").val();
    var senderFirstName = $("#sender_firstname").val();
    var senderAddress = $("#sender_address").val();
    var senderCity = $("#sender_city").val();
    var senderEmail = $("#sender_email").val();
    var senderPhone = $("#sender_phone").val();

    var recipientLastName = $("#recipient_lastname").val();
    var recipientFirstName = $("#recipient_firstname").val();
    var recipientAddress = $("#recipient_address").val();
    var recipientCity = $("#recipient_city").val();
    var recipientEmail = $("#recipient_email").val();
    var recipientPhone = $("#recipient_phone").val();

    

    // Creazione dell'oggetto contenente i dati del mittente e del destinatario
    var formData = {
        sender: {
            lastName: senderLastName,
            firstName: senderFirstName,
            address: senderAddress,
            city: senderCity,
            email: senderEmail,
            phone: senderPhone
        },
        recipient: {
            lastName: recipientLastName,
            firstName: recipientFirstName,
            address: recipientAddress,
            city: recipientCity,
            email: recipientEmail,
            phone: recipientPhone
        }
    };

    // Salva i dati nel sessionStorage
    sessionStorage.setItem('formData', JSON.stringify(formData));
    console.log("Dati mittente e destinatario salvati");
}

// Funzione per recuperare i dati del mittente, del destinatario e dei dettagli del pacco dal sessionStorage
function RecuperaFormdalSessionStorage() {
    var formData = JSON.parse(sessionStorage.getItem('formData'));
    return formData;
}






// Funzione per recuperare i dati del form dal sessionStorage e popolare il form
function PopolailForm() {
    // Recupera i dati del form dal sessionStorage
    var formData = RecuperaFormdalSessionStorage();

   
   $("#sender_lastname").val(formData.sender.lastName);
   $("#sender_firstname").val(formData.sender.firstName);
   $("#sender_address").val(formData.sender.address); 
   $("#sender_city").val(formData.sender.city);
   $("#sender_email").val(formData.sender.email);
   $("#sender_phone").val(formData.sender.phone);
   $("#recipient_lastname").val(formData.recipient.lastName);
   $("#recipient_firstname").val(formData.recipient.firstName);
   $("#recipient_address").val(formData.recipient.address);

   $("#recipient_city").val(formData.recipient.city);
   $("#recipient_email").val(formData.recipient.email);
   $("#recipient_phone").val(formData.recipient.phone);

}






function validateOrderData(orderData, selectedOffer) {
    let errors = [];

    // Verifica il contrassegno
    if ($('#contrassegno').is(':checked')) {
        let contrassegnoValore = parseFloat($('#contrassegno_valore').val());
        if (isNaN(contrassegnoValore) || contrassegnoValore > selectedOffer.serviziopzionali.contrassegno.limite_massimo) {
            errors.push('Il valore del contrassegno non è valido o supera il limite massimo consentito.');
        }
    }

    // Verifica l'assicurazione
    if ($('#assicurazione').is(':checked')) {
        let assicurazioneValore = parseFloat($('#assicurazione_valore').val());
        if (isNaN(assicurazioneValore) || assicurazioneValore > selectedOffer.serviziopzionali.assicurazione.limite_massimo) {
            errors.push('Il valore dell\'assicurazione non è valido o supera il limite massimo consentito.');
        }
    }

    return errors;
}

function retrieveOrderData() {
    // Recupera i dati dal sessionStorage
    var packageData = RecuperaFormdalSessionStorage();
    var datidiBase = DatipaccodalSessionStorage();
    var selectedOfferData = retrieveSelectedOfferData();
    var selectedOffer = JSON.parse(sessionStorage.getItem('selectedOffer')); // Aggiungi il recupero dell'offerta selezionata

    // Crea l'oggetto contenente tutti i dati dell'ordine
    var orderData = {
        sender: {
            lastName: packageData.sender.lastName,
            firstName: packageData.sender.firstName,
            address: packageData.sender.address,
            city: datidiBase.mloc,
            email: packageData.sender.email,
            phone: packageData.sender.phone
        },
        recipient: {
            lastName: packageData.recipient.lastName,
            firstName: packageData.recipient.firstName,
            address: packageData.recipient.address,
            city: datidiBase.dloc,
            email: packageData.recipient.email,
            phone: packageData.recipient.phone
        },
        package: {
            weight: datidiBase.peso,
            width: datidiBase.larghezza,
            depth: datidiBase.profondita,
            height: datidiBase.altezza,
            nrPackages: datidiBase.nr_colli
        },
        senderLocation: {
            country: datidiBase.mnaz,
            cap: datidiBase.mcap,
            locality: datidiBase.mloc,
            province: datidiBase.mpro
        },
        destinationLocation: {
            country: datidiBase.dnaz,
            cap: datidiBase.dcap,
            locality: datidiBase.dloc,
            province: datidiBase.dpro,
            county: datidiBase.dcou
        },
        area: datidiBase.area,
        offer: {
            offerId: sessionStorage.getItem('selectedOfferId'),
            price: sessionStorage.getItem('selectedPrice'),
            description: sessionStorage.getItem('selectedDescription'),
            codiceOfferta: sessionStorage.getItem('selectedOfferId')
        }
    };


    console.log("Questi sono tutti i dati dell'ordine: ", orderData);

   

    return orderData;
}













// Funzione per eseguire il calcolo della spedizione
function calcola_test() {
    $("#btn_procedi").html("Sto elaborando i dati, un momento...");
    // Impostazione dei valori statici
    var cosa_spedire = document.querySelector('input[name="cosaspedire"]:checked').value;
    var contenuto = "CONTENUTO";
    var tipoSpedizione = document.getElementById('tipo_spedizione').value;
    // Recupero dei valori dal form
    retrieveFormData();

    // Memorizzazione delle anagrafiche
    storeAnagrafiche();
    
    saveFormDataToSessionStorage();

    // Costruzione del JSON della richiesta
    var jsonRequest = {
        call: "quotation",
        dettagli: {
            tipo_spedizione: tipoSpedizione,
            cosa_spedire: cosa_spedire,
            contenuto: contenuto,
            accessori: {
                contrassegno_importo: 0
            }
        },
        colli: [{
            peso: peso,
            larghezza: larghezza,
            profondita: profondita,
            altezza: altezza,
            nr_colli: nr_colli
        }],
        mittente: {
            cap: mcap,
            localita: mloc,
            provincia: mpro,
            nazione: mnaz
        },
        destinatario: {
            cap: dcap,
            localita: dloc,
            provincia: dpro,
            nazione: dnaz,
            country: dcou
        }
    };

    // Stampa il contenuto di jsonRequest nella console
    console.log("Contenuto di jsonRequest:", jsonRequest);

    // Chiamata AJAX al proxy PHP
    $.ajax({
        url: "/wp-content/plugins/easy-parcel/js/proxy.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(jsonRequest),
        success: function(data) {
            console.log("Risposta da Easy Parcel API:", data);
            // Memorizza la risposta nel session storage
            sessionStorage.setItem('easyParcelResponse', JSON.stringify(data));
            console.log("Dati memorizzati nel session storage:", sessionStorage.getItem('easyParcelResponse'));
            
            showShipmentDetails();
            console.log("Dettagli spedizione salvati");

            // Salvataggio della risposta nel file risposta.json
            $.ajax({
                url: "/wp-content/plugins/easy-parcel/salva_risposta.php",
                type: "POST",
                data: { response: JSON.stringify(data) },
                success: function(response) {
                    console.log("Risposta dal salvataggio:", response);
                    getEasyParcelData(); // Chiamata qui dopo che i dati sono stati salvati
                },
                error: function(error) {
                    console.error("Errore nel salvataggio della risposta:", error);
                }
            });
            
            // Reindirizza l'utente alla pagina del carrello
            window.location.href = "https://www.boxespoint.it/carrello/";
        },
        error: function(error) {
            console.error("Errore AJAX nella chiamata al proxy PHP:", error);
            // Mostra un messaggio di errore all'utente o esegui un'altra azione alternativa
            alert("Si è verificato un errore durante il calcolo della spedizione. Si prega di riprovare più tardi.");
            $("#btn_procedi").html("Sto elaborando i dati, un momento...");
        },
        complete: function() {
            $("#btn_procedi").html("Elaborazione dei Dati completata!");
        }
    });
}

function showShipmentDetails() {
    // Controlla se le variabili globali sono state inizializzate correttamente
    if (peso && larghezza && profondita && altezza && nr_colli &&
        mnaz && mcap && mloc && mpro && dnaz && dcap && dloc && dpro && dcou && area) {
        
        // Costruzione del markup HTML
        var shipmentDetailsHTML = '<p>Pacchi: ' + nr_colli + ' - ' + larghezza + 'cm ' + profondita + 'cm ' + altezza + 'cm - ' + peso + 'kg</p>' +
                         '<p>TOTALE NR. COLLI: ' + nr_colli + ' - TOTALE PESO (kg): ' + peso + '</p>';

        
        // Inserisci il markup HTML nel DOM
        $('#dettagli-pacco').html(shipmentDetailsHTML);
        
        // Inserisci i dettagli della spedizione nel DOM
        $('#localita-mittente').text(mloc);
        $('#localita-destinazione').text(dloc);
    } else {
        console.error("Errore: Dati della spedizione non validi o mancanti.");
    }
}












var corrieriAbilitati = [];
var percentuali = [];
function retrieveCarrierData(callback) {
    $.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
            action: 'get_easy_parcel_data' // Azione AJAX definita sul lato server
        },
        success: function(data) {
            var corrieriAbilitati = [];
            var percentuali = [];

            for (var corriere in data) {
                if (data.hasOwnProperty(corriere)) {
                    if (data[corriere].enabled === "1") {
                        corrieriAbilitati.push(corriere);
                        percentuali.push(data[corriere].percentuale);
                    }
                }
            }

            // Chiamiamo la funzione di callback con i dati recuperati
            if (typeof callback === 'function') {
                callback(corrieriAbilitati, percentuali);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Errore durante il recupero dei dati dei corrieri da Easy Parcel:', textStatus, errorThrown);
        }
    });
}

function popolaCitta() {
    // Recupera i valori di mloc e dloc
    var mloc = sessionStorage.getItem('mloc');
    var dloc = sessionStorage.getItem('dloc');

    // Popola i campi sender_city e recipient_city con i valori recuperati
    $("#sender_city").val(mloc);
    $("#recipient_city").val(dloc);
    
    console.log("Città recuperate e popolate");
}


jQuery(document).ready(function($) {

    
    retrieveFormData();
    retrieveFormDataFromSessionStorage();
    showShipmentDetails();
    popolaCitta();
    $('.modal').modal();



    console.log("Pagina completamente caricata. Avvio recupero dati Easy Parcel...");

    retrieveCarrierData(function(corrieriAbilitati, percentuali) {
        // Ora i corrieri abilitati e le percentuali sono disponibili
        console.log("Corrieri abilitati:", corrieriAbilitati);
        console.log("Percentuali:", percentuali);

        
        getEasyParcelData(corrieriAbilitati, percentuali);
    });
      var formData = retrieveFormDataFromSessionStorage();
    console.log(formData);
    
      	RecuperaFormdalSessionStorage();
        PopolailForm();
        
        
        
        
    
});











function getEasyParcelData(corrieriAbilitati, percentuali) {
    // Variabile per memorizzare la percentuale IVA dal backend
    var percentualeIva = 0;

    // Chiamata AJAX per ottenere la percentuale IVA dalle impostazioni di WordPress
    $.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
            action: 'get_tax_percentage'
        },
        success: function(response) {
            var data = JSON.parse(response);
            percentualeIva = parseFloat(data.tax_percentage);

            // Ora possiamo procedere con le altre chiamate AJAX
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'get_easy_parcel_data' 
                },
                success: function(data) {
                    console.log("Dati Easy Parcel recuperati con successo:", data);

                    for (var corriere in data) {
                        if (data.hasOwnProperty(corriere)) {
                            if (data[corriere].enabled === "1") {
                                corrieriAbilitati.push(corriere);
                                percentuali.push(data[corriere].percentuale);
                            }
                        }
                    }

                    retrieveFormDataFromSessionStorage();

                    $.ajax({
                        url: '/wp-content/plugins/easy-parcel/data/calcola.php',
                        dataType: 'json',
                        success: function(data) {
                            var parsedData = JSON.parse(data);
                            console.log("Risposta grezza di Easy Parcel: ", data);

                            if (Array.isArray(parsedData.quotation)) {
                                var easyParcelDiv = $('#easyParcelDataContainer');
                                easyParcelDiv.empty();
                                $.each(parsedData.quotation, function(index, item) {
                                    if (parseFloat(item.importo_tariffa) > 1 && corrieriAbilitati.includes(item.nome_vettore)) {
                                        console.log("Corriere:", item.nome_vettore);
                                        console.log("Prezzo originale:", item.importo_tariffa);

                                        var percentualeIndex = corrieriAbilitati.indexOf(item.nome_vettore);
                                        var price = parseFloat(item.importo_tariffa);
                                        var newPrice = price * (1 + parseFloat(percentuali[percentualeIndex]) / 100);
                                        newPrice = Math.round(newPrice * 100) / 100;

                                        var iva = ((newPrice * percentualeIva) / 100).toFixed(2);
                                        var prezzoFinaleIvato = (parseFloat(newPrice) + parseFloat(iva)).toFixed(2);
                                        console.log("IVA:", iva);
                                        console.log("Prezzo Modificato IVATO: ", prezzoFinaleIvato);
                                        console.log("Maggiorazione (%): ", percentuali[percentualeIndex]);

                                        var quotationHtml = '<ul class="list-tickets">' +
                                            '<li class="list-item" id="offerta' + index + '">' +
                                            '<div class="list-item-inner">' +
                                            '<div class="list-item-main">' +
                                            '<div class="list-item-logo">' +
                                            '<img src="' + item.logo_vettore + '" alt="' + item.nome_vettore + '" style="width: 120px; height: 60px;" title="' + item.nome_vettore + '">' +
                                            '</div>' +
                                            '<div class="list-item-content">' +
                                            '<div class="list-item-content-line-wrapper small">' +
                                            '<div class="list-item-content-line-top">NAZIONALE</div>' +
                                            '<div class="list-item-content-line"></div>' +
                                            '<div class="list-item-content-line-bottom text-info-dr">' +
                                            '<div style="line-height:normal !important">' +
                                            '<strong>' + item.nome_vettore + '</strong>' +
                                            '<br>' +
                                            '<small><small>ECONOMY</small></small>' +
                                            '</div>' +
                                            '<hr>' +
                                            '<div id="dettagli_messaggiotariffaDOWN' + index + '" style="text-align:left; color:black;">' +
                                            '<a href="javascript:void(0)" onclick="$(\'#dettagli_messaggiotariffaDOWN' + index + '\').hide(); $(\'#dettagli_messaggiotariffaUP' + index + '\').fadeIn();">' +
                                            '<i class="fa fa-arrow-down"></i> <small>Dettagli</small>' +
                                            '</a>' +
                                            '<span style="color:white">' + item.nome_vettore + '</span>' +
                                            '</div>' +
                                            '<div id="dettagli_messaggiotariffaUP' + index + '" style="display:none; text-align:left; color:black; background-color:#fafafa; padding: 3px;">' +
                                            '<a href="javascript:void(0)" onclick="$(\'#dettagli_messaggiotariffaUP' + index + '\').fadeOut(); $(\'#dettagli_messaggiotariffaDOWN' + index + '\').show();">' +
                                            '<i class="fa fa-arrow-up"></i> <small>Dettagli</small>' +
                                            '</a>' +
                                            '<hr>' +
                                            '<div style="line-height:normal !important; color:grey;">' +
                                            '<ul style="list-style:square inside;"> ' + item.messaggio_tariffa + ' <br>Limiti per pacchi:<br>' +
                                            '<li>Peso Dichiarato: ' + item.peso_dichiarato + ' kg </li>' +
                                            '<li>Codice Offerta: ' + item.codice_offerta + ' </li>' +
                                            '<li>Peso Volumetrico: ' + item.peso_volumetrico + ' cm3</li>' +
                                            '<li>Max. somma lati: ' + (item.max_somma_lati || 'N/A') + ' cm</li>' +
                                            '<li>Tempo di transito stimato: ' + (item.tempiconsegna || 'N/A') + '</li>' +
                                            '<li>Consegna: ' + (item.consegna || 'N/A') + '</li>' +
                                            '</ul>';

                                        if (item.serviziopzionali) {
                                            if (item.serviziopzionali.contrassegno && item.serviziopzionali.contrassegno.attivabile === "S") {
                                                quotationHtml += '<ul style="list-style:square inside;"><li>Contrassegno Attivabile: S</li>' +
                                                    '<li>Importo: ' + item.serviziopzionali.contrassegno.importo + '</li>' +
                                                    '<li>Limite Massimo: ' + item.serviziopzionali.contrassegno.limite_massimo + '</li></ul>';
                                            }
                                            if (item.serviziopzionali.assicurazione && item.serviziopzionali.assicurazione.attivabile === "S") {
                                                quotationHtml += '<ul style="list-style:square inside;"><li>Assicurazione Attivabile: S</li>' +
                                                    '<li>Importo: ' + item.serviziopzionali.assicurazione.importo + '</li>' +
                                                    '<li>Limite Massimo: ' + item.serviziopzionali.assicurazione.limite_massimo + '</li></ul>';
                                            }
                                            if (item.serviziopzionali.consegnaalpiano && item.serviziopzionali.consegnaalpiano.attivabile === "S") {
                                                quotationHtml += '<ul style="list-style:square inside;"><li>Consegna al Piano Attivabile: S</li>' +
                                                    '<li>Importo: ' + item.serviziopzionali.consegnaalpiano.importo + '</li></ul>';
                                            }
                                            if (item.serviziopzionali.consegnasuappuntamento && item.serviziopzionali.consegnasuappuntamento.attivabile === "S") {
                                                quotationHtml += '<ul style="list-style:square inside;"><li>Consegna su Appuntamento Attivabile: S</li>' +
                                                    '<li>Importo: ' + item.serviziopzionali.consegnasuappuntamento.importo + '</li></ul>';
                                            }
                                            if (item.serviziopzionali.ritiro && item.serviziopzionali.ritiro.attivabile === "S") {
                                                quotationHtml += '<ul style="list-style:square inside;"><li>Ritiro Attivabile: S</li>' +
                                                    '<li>Importo: ' + item.serviziopzionali.ritiro.importo + '</li></ul>';
                                            }
                                            if (item.serviziopzionali.portoassegnato && item.serviziopzionali.portoassegnato.attivabile === "S") {
                                                quotationHtml += '<ul style="list-style:square inside;"><li>Porto Assegnato Attivabile: S</li>' +
                                                    '<li>Importo: ' + item.serviziopzionali.portoassegnato.importo + '</li></ul>';
                                            }
                                        }
                                       
                                        quotationHtml += '</div>' +
                                            '</div>' +
                                            '</div>' +
                                            '<div class="list-item-content-right">' +
                                            '<div class="text-base text-right">' +
                                            '<label>' +
                                            '<input type="radio" class="radio-inline radio-custom" name="tariffa_scelta" value="' + prezzoFinaleIvato + '">' +
                                            '<span>' + prezzoFinaleIvato + ' IVA inclusa</span>' +
                                            '</label>' +
                                            '</div>' +
                                            '</div>' +
                                            '</div>' +
                                            '</div>' +
                                            '</div>' +
                                            '</li>' +
                                            '</ul>';

                                        easyParcelDiv.append(quotationHtml);
                                    }
                                });

                                $('input[name="tariffa_scelta"]').change(function() {
                                    var selectedOffer = $(this).closest('.list-item');
                                    var codiceOfferta = selectedOffer.find('li:contains("Codice Offerta:")').text().split(':')[1].trim();
                                    var offerId = codiceOfferta;
                                    var price = parseFloat($(this).val());
                                    var iva = (price * percentualeIva).toFixed(2);
                                    var prezzoFinaleIvato = (price + parseFloat(iva)).toFixed(2);
                                    var description = selectedOffer.find('.list-item-content-line-bottom').text().trim();

                                    saveSelectedOfferToSessionStorage(offerId, price, description, codiceOfferta);
                                    sessionStorage.setItem('selectedOfferId', offerId);
                                    sessionStorage.setItem('selectedPrice', price);
                                    sessionStorage.setItem('selectedDescription', description);
                                    sessionStorage.setItem('selectedCodiceOfferta', codiceOfferta);
                                    salvailFormnelSessionStorage();
                                    retrieveFormDataFromSessionStorage();
                                    

                                    $('#paymentMethods').show();
                                    popolaCitta();
                                    salvailFormnelSessionStorage();
                                    retrieveFormDataFromSessionStorage();
                                });

                                $('#payAndContinueBtn').click(function() {
                                    var saveSenderData = $('#save_sender_data').is(':checked');
                                    var saveRecipientData = $('#save_recipient_data').is(':checked');
                                    var selectedOffer = $('input[name="tariffa_scelta"]:checked').closest('.list-item');
                                    var price = parseFloat(selectedOffer.find('input[name="tariffa_scelta"]:checked').val());
                                    var iva = (price * percentualeIva).toFixed(2);
                                    var prezzoFinaleIvato = (price + parseFloat(iva)).toFixed(2);
                                    var description = selectedOffer.find('.list-item-content-line-bottom').text();
                                    var codiceOfferta = selectedOffer.find('li:contains("Codice Offerta:")').text().split(':')[1].trim();
                                    var metodoPagamento = $('input[name="paymentMethod"]:checked').val();
                                    if (saveSenderData) {
                                        inviaDatiMittenteAlBackend();
                                    }
                                    if (saveRecipientData) {
                                        inviaDatiDestinatarioAlBackend();
                                    }

                                    if (metodoPagamento === 'cash') {
                                        saveFormDataToSessionStorage();
                                        eseguiOrdine();
                                    } else if (metodoPagamento === 'paypal') {
                                        salvailFormnelSessionStorage();
                                        RecuperaFormdalSessionStorage();
                                        // inviaDatiAlBackend();
                                        // inviaDatiOrdineAlBackend();
                                        effettuaPagamentoPayPal(prezzoFinaleIvato, description, codiceOfferta);
                                    } else if (metodoPagamento === 'stripe') {
                                        salvailFormnelSessionStorage();
                                        RecuperaFormdalSessionStorage();
                                        // inviaDatiAlBackend();
                                        // inviaDatiOrdineAlBackend();
                                        effettuaPagamentoStripe(price, description, codiceOfferta);
                                    } else {
                                        console.error("Metodo di pagamento non valido");
                                        return;
                                    }
                                });
                            } else {
                                console.log("La proprietà 'quotation' non è definita o non è un array.");
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('Errore durante il recupero dei dati JSON da Easy Parcel:', textStatus, errorThrown);
                        }
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Errore durante il recupero dei dati dei corrieri da Easy Parcel:', textStatus, errorThrown);
                }
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Errore durante il recupero della percentuale IVA dalle impostazioni:', textStatus, errorThrown);
        }
    });
}




















  function inviaDatiOrdineAlBackend() {
    var orderData = retrieveOrderData();

    // Prepara i dati dell'ordine
    var orderDetails = {
        sender: orderData.sender,
        recipient: orderData.recipient,
        offerId: sessionStorage.getItem('selectedOfferId'),
        price: sessionStorage.getItem('selectedPrice'),
        description: sessionStorage.getItem('selectedDescription'),
        codiceOfferta: sessionStorage.getItem('selectedCodiceOfferta'),
        paymentMethod: $('input[name="paymentMethod"]:checked').val(),
        // Accedi ai dati del pacchetto tramite orderData.package
        package: {
            weight: orderData.package.weight,
            width: orderData.package.width,
            depth: orderData.package.depth,
            height: orderData.package.height,
            nr_packages: orderData.package.nrPackages
        }
    };

    // Invia i dati dell'ordine al backend
    $.ajax({
        url: '/creaordini.php?action=saveOrder',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            order: orderDetails,
            sender: retrieveSenderData(),
            recipient: retrieveRecipientData()
        }),
        success: function(response) {
            // Estrai l'ID dell'ordine dalla risposta
            var orderId = response.order_id;

            console.log('Ordine salvato con successo, ID:', orderId);

            // Reindirizza l'utente alla pagina di riepilogo ordine con l'ID dell'ordine nella query string
          //  window.location.href = '/riepilogo-ordine?order_id=' + orderId;
        },
        error: function(xhr, status, error) {
            console.error('Errore durante il salvataggio dell\'ordine:', error);
            // Gestione degli errori
        }
    });
}

    
    function retrieveSenderData() {
        return {
            lastName: sessionStorage.getItem('senderLastName') || '',
            firstName: sessionStorage.getItem('senderFirstName') || '',
            email: sessionStorage.getItem('senderEmail') || '',
            phone: sessionStorage.getItem('senderPhone') || '',
            address: sessionStorage.getItem('senderAddress') || '',
            city: sessionStorage.getItem('senderCity') || ''
        };
    }
    
    function retrieveRecipientData() {
        return {
            lastName: sessionStorage.getItem('recipientLastName') || '',
            firstName: sessionStorage.getItem('recipientFirstName') || '',
            email: sessionStorage.getItem('recipientEmail') || '',
            phone: sessionStorage.getItem('recipientPhone') || '',
            address: sessionStorage.getItem('recipientAddress') || '',
            city: sessionStorage.getItem('recipientCity') || ''
        };
    }
    








    
    function inviaDatiMittenteAlBackend() {
        var senderData = retrieveOrderData().sender;
    
        $.ajax({
            url: '/creaordini.php?action=saveSender',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(senderData),
            success: function(response) {
                console.log('Dati del mittente salvati con successo:', response);
                // Effettua azioni post-salvataggio se necessario
            },
            error: function(xhr, status, error) {
                console.error('Errore durante il salvataggio dei dati del mittente:', error);
                // Gestione degli errori
            }
        });
    }
    
    function inviaDatiDestinatarioAlBackend() {
        var recipientData = retrieveOrderData().recipient;
    
        $.ajax({
            url: '/creaordini.php?action=saveRecipient',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(recipientData),
            success: function(response) {
                console.log('Dati del destinatario salvati con successo:', response);
                // Effettua azioni post-salvataggio se necessario
            },
            error: function(xhr, status, error) {
                console.error('Errore durante il salvataggio dei dati del destinatario:', error);
                // Gestione degli errori
            }
        });
    }
    

















// Funzione per concludere l'ordine e aggiornare lo stato
function concludeOrder(statoPagamento) {
    // Recupera i dati dell'ordine tramite retrieveOrderData
    var orderData = retrieveOrderData();

    // Aggiungi lo stato del pagamento
    orderData.paymentStatus = statoPagamento;

    // Invia i dati dell'ordine al backend
    $.ajax({
        url: '/creaordini.php',
        method: 'POST',
        data: orderData,
        success: function(response) {
            console.log('Ordine salvato con successo:', response, orderData);
        },
        error: function(xhr, status, error) {
            console.error('Errore durante il salvataggio dell\'ordine:', error);
        }
    });
}







function retrieveSelectedOfferData() {
    // Recupera i dati dal sessionStorage
    var selectedOfferId = sessionStorage.getItem('selectedOfferId');
    var selectedPrice = sessionStorage.getItem('selectedPrice');
    var selectedDescription = sessionStorage.getItem('selectedDescription');
    var selectedCodiceOfferta = sessionStorage.getItem('selectedCodiceOfferta');

    // Costruisci l'oggetto datiOfferta
    var datiOfferta = {
        offerId: selectedOfferId,
        price: selectedPrice,
        description: selectedDescription,
        codiceOfferta: selectedCodiceOfferta
    };
    console.log("hai selezionato l'offerta:", datiOfferta);

    return datiOfferta;
}







// Funzione per salvare i dettagli dell'offerta selezionata nel sessionStorage
function saveSelectedOfferToSessionStorage(offerId, price, description, codiceOfferta) {
    sessionStorage.setItem('selectedOfferId', offerId);
    sessionStorage.setItem('selectedPrice', price);
    sessionStorage.setItem('selectedDescription', description);
    sessionStorage.setItem('selectedCodiceOfferta', codiceOfferta);
}


// Salva i dati del modulo nel sessionStorage
// Salvataggio dei valori nel sessionStorage
function saveFormDataToSessionStorage() {
    sessionStorage.setItem('peso', peso);
    sessionStorage.setItem('larghezza', larghezza);
    sessionStorage.setItem('profondita', profondita);
    sessionStorage.setItem('altezza', altezza);
    sessionStorage.setItem('nr_colli', nr_colli);
    sessionStorage.setItem('mnaz', mnaz);
    sessionStorage.setItem('mcap', mcap);
    sessionStorage.setItem('mloc', mloc);
    sessionStorage.setItem('mpro', mpro);
    sessionStorage.setItem('dnaz', dnaz);
    sessionStorage.setItem('dcap', dcap);
    sessionStorage.setItem('dloc', dloc);
    sessionStorage.setItem('dpro', dpro);
    sessionStorage.setItem('dcou', dcou);
    sessionStorage.setItem('area', area);
     // Calcolo del peso volumetrico
     if (larghezza && profondita && altezza) {
        let pesoVolumetrico = (parseFloat(larghezza) * parseFloat(profondita) * parseFloat(altezza)) / 5000;
        sessionStorage.setItem('pesoVolumetrico', pesoVolumetrico.toFixed(2));
    } else {
        sessionStorage.setItem('pesoVolumetrico', 'N/A');
    }
}

// Recupero dei valori dal sessionStorage
function retrieveFormDataFromSessionStorage() {
    peso = sessionStorage.getItem('peso');
    larghezza = sessionStorage.getItem('larghezza');
    profondita = sessionStorage.getItem('profondita');
    altezza = sessionStorage.getItem('altezza');
    nr_colli = sessionStorage.getItem('nr_colli');
    mnaz = sessionStorage.getItem('mnaz');
    mcap = sessionStorage.getItem('mcap');
    mloc = sessionStorage.getItem('mloc');
    mpro = sessionStorage.getItem('mpro');
    dnaz = sessionStorage.getItem('dnaz');
    dcap = sessionStorage.getItem('dcap');
    dloc = sessionStorage.getItem('dloc');
    dpro = sessionStorage.getItem('dpro');
    dcou = sessionStorage.getItem('dcou');
    area = sessionStorage.getItem('area');
}




// Recupero dei valori dal sessionStorage
function DatipaccodalSessionStorage() {
    var datiDelPacco = {
        peso: sessionStorage.getItem('peso'),
        larghezza: sessionStorage.getItem('larghezza'),
        profondita: sessionStorage.getItem('profondita'),
        altezza: sessionStorage.getItem('altezza'),
        nr_colli: sessionStorage.getItem('nr_colli'),
        mnaz: sessionStorage.getItem('mnaz'),
        mcap: sessionStorage.getItem('mcap'),
        mloc: sessionStorage.getItem('mloc'),
        mpro: sessionStorage.getItem('mpro'),
        dnaz: sessionStorage.getItem('dnaz'),
        dcap: sessionStorage.getItem('dcap'),
        dloc: sessionStorage.getItem('dloc'),
        dpro: sessionStorage.getItem('dpro'),
        dcou: sessionStorage.getItem('dcou'),
        area: sessionStorage.getItem('area')
    };
    console.log("Dati del Pacco: ", datiDelPacco);
    return datiDelPacco;
}






// Funzione per trasmettere l'ordine all'API di Easy Parcel
function transmitOrder() {
    console.log("Chiamata all'API Fatta! Ora stuiati u mussu");
    // Effettua la chiamata all'API di Easy Parcel per trasmettere l'ordine e ricevere la lettera di vettura
    // Inserisci qui la logica per chiamare l'API di Easy Parcel
}


    
    
    
    
	

	function selezionata_misurapredefinita() {
		console.log("La funzione selezionata_misurapredefinita è stata chiamata");
		
		// Recupera la chiave API dalla variabile globale
		var apiKey = easyParcelData.apiKey;
		console.log("Chiave API recuperata:", apiKey);
		
		var selectedOption = $("#misurepredefinite option:selected").val();
		var values = selectedOption.split("|");
		$("#lunghezza").val(values[0]);
		$("#larghezza").val(values[1]);
		$("#altezza").val(values[2]);
		$("#peso").val(values[3]);
		$("#colli").val(1);
		
		
	}

function verificaCampiPacco() {
        var lunghezza = document.getElementById('lunghezza').value;
        var larghezza = document.getElementById('larghezza').value;
        var altezza = document.getElementById('altezza').value;
        var colli = document.getElementById('colli').value;
        var peso = document.getElementById('peso').value;

        if (lunghezza === '' && larghezza === '' && altezza === '' && colli === '' && peso === '') {
            document.getElementById('messaggio_nessun_collo').style.display = 'block';
        } else {
            document.getElementById('messaggio_nessun_collo').style.display = 'none';
        }
    }

// JavaScript per gestire la visualizzazione del preloader durante l'invio del modulo
    jQuery(document).ready(function($) {
        $('#form_index').submit(function() {
            $('#div_colli img.spinner-small').show();
            $('#nessun_collo').hide();
        });
    });

function aggiungi() {
    console.log("La funzione AGGIUNGI è stata chiamata");
	  // Verifica se tutti i campi sono compilati prima di procedere
    if (!verificaCampiCompilatiiniziali()) {
        alert("Per favore, compila tutti i campi del form.");
        return;
    }
    // Recupera i valori inseriti dall'utente
    var lunghezza = $("#lunghezza").val();
    var larghezza = $("#larghezza").val();
    var altezza = $("#altezza").val();
    var peso = $("#peso").val();
    var nr_colli = $("#colli").val();

    // Calcola il peso totale
    var peso_totale = peso * nr_colli;

    // Costruisci l'HTML per visualizzare i dettagli della spedizione
    var dettaglio_id = 'dettaglio_' + Date.now(); // Genera un ID univoco
    var dettagli_spedizione = '<div id="' + dettaglio_id + '" class="padding-5">' +
        '<a class="btn btn-warning btn-xxs" style="padding:5px 10px" href="javascript:void(0)" onclick="confermaCancellaDettaglio(\'' + dettaglio_id + '\')"><i class="fa fa-trash manina" style="color:white"></i></a>&nbsp;' +
        'Pacchi: ' + nr_colli + '&nbsp;<i class="fa fa-times"></i>&nbsp;' + lunghezza + 'cm&nbsp;<i class="fa fa-times"></i>&nbsp;' + larghezza + 'cm&nbsp;<i class="fa fa-times"></i>&nbsp;' + altezza + 'cm&nbsp;-&nbsp;' + peso + 'kg' +
        '</div>';

    // Aggiungi i dettagli della spedizione al contenitore
    $("#div_colli").append(dettagli_spedizione);

    // Aggiorna il totale del numero di colli e del peso totale
    $("#colli_nr").val(nr_colli);
    $("#peso_totale").val(peso_totale);

    // Visualizza il contenitore della composizione della spedizione
    $("#div_colli").css("display", "block");

    // Aggiorna il totale visualizzato solo se non è stato ancora aggiunto
    if ($("#totale").length === 0) {
        var totale_html = '<div id="totale" class="cell-sm-12" style="background-color:white; padding:10px; color:black;">' +
            '<hr style="background-color:#333; margin:3px;">' +
            '<div class="padding-5">' +
            '<a class="btn btn-danger btn-xxs" style="padding:5px 10px" href="javascript:void(0)" onclick="confermaCancellaTotale()"><i class="fa fa-trash manina" style="color:white"></i></a>&nbsp;' +
            '<strong>TOTALE NR. COLLI: ' + nr_colli + ' - TOTALE PESO (kg): ' + peso_totale + '</strong>' +
            '</div>' +
            '</div>';
        $("#div_colli").append(totale_html);
    }

    // Visualizza il totale aggiornato
    $("#indicazioni").css("display", "block");
}


function confermaCancellaDettaglio(dettaglio_id) {
    // Utilizza Bootstrap per creare un modal di conferma
    var confermaModal = '<div class="modal fade" id="confermaDettaglioModal" tabindex="-1" role="dialog" aria-labelledby="confermaDettaglioModalLabel" aria-hidden="true">' +
        '<div class="modal-dialog" role="document">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<h5 class="modal-title" id="confermaDettaglioModalLabel">Conferma cancellazione</h5>' +
        '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>' +
        '<div class="modal-body">' +
        'Sei sicuro di voler cancellare questo dettaglio di spedizione?' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>' +
        '<button type="button" class="btn btn-danger" onclick="cancellaDettaglio(\'' + dettaglio_id + '\')">Cancella</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    // Aggiungi il modal al corpo del documento
    $('body').append(confermaModal);

    // Mostra il modal di conferma
    $('#confermaDettaglioModal').modal('show');
}

function cancellaDettaglio(dettaglio_id) {
    $("#" + dettaglio_id).remove(); // Rimuovi il dettaglio della spedizione
    cancella(); // Pulisci il form
    // Aggiungi qui il codice per aggiornare il totale del numero di colli e del peso totale, se necessario

    // Chiudi il modal di conferma
    $('#confermaDettaglioModal').modal('hide');
}


function confermaCancellaTotale() {
    // Utilizza Bootstrap per creare un modal di conferma
    var confermaModal = '<div class="modal fade" id="confermaTotaleModal" tabindex="-1" role="dialog" aria-labelledby="confermaTotaleModalLabel" aria-hidden="true">' +
        '<div class="modal-dialog" role="document">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<h5 class="modal-title" id="confermaTotaleModalLabel">Conferma cancellazione totale</h5>' +
        '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>' +
        '<div class="modal-body">' +
        'Sei sicuro di voler cancellare il totale?' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>' +
        '<button type="button" class="btn btn-danger" onclick="cancellaTotale()">Cancella</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    // Aggiungi il modal al corpo del documento
    $('body').append(confermaModal);

    // Mostra il modal di conferma
    $('#confermaTotaleModal').modal('show');
}

function cancellaTotale() {
    // Rimuovi il totale
    $("#totale").remove();

    // Rimuovi anche tutti i pacchi associati
    $(".padding-5").remove();

    // Controlla se rimane solo il messaggio "Nessun collo da spedire!" e rimuovilo se presente
    if ($("#div_colli").children().length === 1 && $("#nessun_collo").length > 0) {
        $("#nessun_collo").remove();
    }

    // Nascondi le indicazioni
    $("#indicazioni").hide(); // Nasconde l'intera sezione indicazioni

    // Ripristina lo stato originale del form
    $("#lunghezza").val("");
    $("#larghezza").val("");
    $("#altezza").val("");
    $("#colli").val("");
    $("#peso").val("");

    // Chiudi il modal di conferma
    $('#confermaTotaleModal').modal('hide');
}





function cancella(idPacco) {
    document.getElementById("lunghezza").value = "";
    document.getElementById("larghezza").value = "";
    document.getElementById("altezza").value = "";
    document.getElementById("colli").value = "";
    document.getElementById("peso").value = "";
}



function virgola_in_punto() {
	console.log("Ho convertito la virgola in punto, un siamo amiarica!");
    var inputField = document.getElementById("peso");
    var value = inputField.value;
    
    // Sostituisci tutte le virgole con i punti
    value = value.replace(/,/g, ".");
    
    // Aggiorna il valore nell'input
    inputField.value = value;
}












function verificaCampiCompilati() {
    // Recupera i valori inseriti dall'utente relativi alla partenza e alla destinazione
    var capPartenza = $("#cap1").val();
    var localitaPartenza = $("#localita1").val();
    var provinciaPartenza = $("#provincia1").val();
    var nazionePartenza = $("#country1").val();

    var capDestinazione = $("#cap2").val();
    var localitaDestinazione = $("#localita2").val();
    var provinciaDestinazione = $("#provincia2").val();
    var nazioneDestinazione = $("#country2").val();

    // Verifica se tutti i campi sono stati compilati
    if (
        capPartenza === "" ||
        localitaPartenza === "" ||
        provinciaPartenza === "" ||
        nazionePartenza === "" ||
        capDestinazione === "" ||
        localitaDestinazione === "" ||
        provinciaDestinazione === "" ||
        nazioneDestinazione === ""
    ) {
        // Se uno qualsiasi dei campi è vuoto, restituisci false
        return false;
    } else {
        // Altrimenti, tutti i campi sono compilati, quindi restituisci true
        return true;
    }
}

function verificaCampiCompilatiiniziali() {
    // Recupera i valori inseriti dall'utente relativi all'area, cosa spedire e dimensioni
    var area = $("#area").val();
    var cosaSpedire = $("input[name='cosaspedire']:checked").val();
    var misurePredefinite = $("#misurepredefinite").val();
    var lunghezza = $("#lunghezza").val();
    var larghezza = $("#larghezza").val();
    var altezza = $("#altezza").val();
    var nrColli = $("#colli").val();
    var peso = $("#peso").val();

    // Verifica se tutti i campi sono stati compilati e rispettano i requisiti
    if (
        area === "" ||
        cosaSpedire === undefined ||
        misurePredefinite === null ||
        lunghezza === "" ||
        larghezza === "" ||
        altezza === "" ||
        nrColli === "" ||
        peso === "" ||
        parseFloat(peso) <= 0
    ) {
        // Se uno qualsiasi dei campi è vuoto o non rispetta i requisiti, restituisci false
        return false;
    } else {
        // Altrimenti, tutti i campi sono compilati e rispettano i requisiti, quindi restituisci true
        return true;
    }
}























function effettuaPagamentoPayPal(prezzoFinaleIvato, description, codiceOfferta) {
    console.log("Prezzo ricevuto:", prezzoFinaleIvato);

    // Recupera le impostazioni di PayPal dalle variabili globali
    var paypalEmail = paypalSettings.email; // Indirizzo email PayPal
    var url = paypalSettings.baseUrl; // URL base per i pagamenti PayPal
    var currency = paypalSettings.currency; // Valuta PayPal

    // Parametri richiesti per generare un URL di pagamento PayPal
    var parameters = {
        cmd: "_xclick",
        business: paypalEmail,
        item_name: description,
        amount: prezzoFinaleIvato,
        currency_code: currency,
        invoice: codiceOfferta,
        custom: codiceOfferta
    };

    // Stampa tutte le variabili prima di costruire l'URL di pagamento
    console.log("paypalEmail:", paypalEmail);
    console.log("url:", url);
    console.log("parameters:", parameters);

    // Costruisci l'URL di pagamento PayPal aggiungendo i parametri come stringa di query
    var queryString = Object.keys(parameters).map(function(key) {
        return key + "=" + encodeURIComponent(parameters[key]);
    }).join("&");

    // Aggiungi il codice al link di PayPal
    var paymentURL = url + "?" + queryString;

    // Redirect l'utente al link di pagamento PayPal
    window.location.href = paymentURL;
}





// Funzione per effettuare il pagamento tramite Stripe
function effettuaPagamentoStripe(price, description, codiceOfferta) {
    console.log("Pagamento tramite Stripe");
    console.log("ID Offerta:", codiceOfferta);
    console.log("Prezzo:", price);
    console.log("Descrizione:", description);
    console.log("Codice Offerta:", codiceOfferta);

    // Qui inserisci la logica per il pagamento tramite Stripe
}













function clearSessionStorage() {
    sessionStorage.removeItem('easyParcelOrderResponse');
    console.log("Session storage cleared.");
}




function showOrderDetails() {
    // Recupera i dettagli dell'ordine memorizzati nella sessionStorage
    var orderResponse = JSON.parse(sessionStorage.getItem('easyParcelOrderResponse'));

    // Verifica se sono presenti dettagli dell'ordine
    if (orderResponse && orderResponse.result === "OK") {
        // Estrai i dettagli dell'ordine
        var orderDetails = orderResponse.details;

        // Memorizza i dettagli dell'ordine nel sessionStorage
        sessionStorage.setItem('orderDetails', JSON.stringify(orderDetails));

        // Stampa i dettagli dell'ordine nella console
        console.log("Dettagli dell'ordine:", orderDetails);

        // Notifica all'utente che i dettagli dell'ordine sono stati salvati
        alert("I dettagli dell'ordine sono stati salvati.");

        // Puoi reindirizzare l'utente a un'altra pagina qui se necessario
        window.location.href = "https://www.boxespoint.it/riepilogo-ordine-2/";
    } else {
        // Se non ci sono dettagli dell'ordine o se l'ordine non è stato confermato correttamente, mostra un messaggio di errore
        alert("Errore nel recupero dei dettagli dell'ordine. Si prega di contattare l'assistenza clienti.");
    }
}






/*

function getWaybill(orderId) {
    // Costruzione del JSON della richiesta per getwaybill
    var jsonRequest = {
        call: "getwaybill",
        details: {
            order_id: orderId,
            waybill_base64: "N"
        }
    };

    // Stampa il contenuto di jsonRequest nella console
    console.log("Contenuto di jsonRequest per getwaybill:", JSON.stringify(jsonRequest));

    // Chiamata AJAX al proxy PHP per getwaybill
    $.ajax({
        url: "/wp-content/plugins/easy-parcel/js/proxy_getwaybill.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(jsonRequest),
        success: function(data) {
            console.log("Risposta da Easy Parcel API per getwaybill:", data);
            // Memorizza la risposta nel session storage
            sessionStorage.setItem('easyParcelGetWaybillResponse', JSON.stringify(data));
            console.log("Dati memorizzati nel session storage per getwaybill:", sessionStorage.getItem('easyParcelGetWaybillResponse'));
            
            // Azioni da eseguire dopo aver ottenuto la waybill
            mostraDettagliWaybill(data);
        },
        error: function(error) {
            console.error("Errore AJAX nella chiamata al proxy PHP per getwaybill:", error);
            // Mostra un messaggio di errore all'utente o esegui un'altra azione alternativa
            alert("Si è verificato un errore durante il recupero della lettera di vettura. Si prega di riprovare più tardi.");
        }
    });
}

*/










/*
    function getWaybill(orderId) {
        var jsonRequest = {
            call: "getwaybill",
            details: {
                order_id: orderId,
                waybill_base64: "N"
            }
        };

        console.log("Contenuto di jsonRequest per getwaybill:", JSON.stringify(jsonRequest));

        $.ajax({
            url: "/wp-content/plugins/easy-parcel/js/proxy_getwaybill.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(jsonRequest),
            success: function(data) {
                console.log("Risposta da Easy Parcel API per getwaybill:", data);
                var responseData = JSON.parse(data);

                if (responseData && responseData.result === 'OK') {
                    var waybillUrl = responseData.waybill_url;
                    window.open(waybillUrl, '_blank');
                } else {
                    alert('Errore durante il recupero della lettera di vettura.');
                }
            },
            error: function(error) {
                console.error("Errore AJAX nella chiamata al proxy PHP per getwaybill:", error);
                alert("Si è verificato Cazzo di errore durante il recupero della lettera di vettura. Si prega di riprovare più tardi.");
            }
        });
    }
*/
    $(document).on('click', '.get-waybill-btn', function() {
        var orderId = $(this).data('order-id');
        console.log("Ordine: ", orderId);
        getWaybill(orderId);
    });



    function getWaybill(orderId) {
        var jsonRequest = {
            call: "getwaybill",
            details: {
                order_id: orderId,
                waybill_base64: "N"
            }
        };
    
        console.log("Contenuto di jsonRequest per getwaybill:", JSON.stringify(jsonRequest));
    
        return $.ajax({
            url: "/wp-content/plugins/easy-parcel/js/proxy_getwaybill.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(jsonRequest),
            success: function(data) {
                console.log("Risposta da Easy Parcel API per getwaybill:", data);
                var responseData = JSON.parse(data);
    
                if (responseData && responseData.result === 'OK') {
                    var waybillNumber = responseData.waybill_number; // Supponendo che waybill_number sia restituito
                    var waybillUrl = responseData.waybill_url;
                    window.open(waybillUrl, '_blank');
                    return waybillNumber;
                } else {
                    alert('Errore durante il recupero della lettera di vettura.');
                    return null;
                }
            },
            error: function(error) {
                console.error("Errore AJAX nella chiamata al proxy PHP per getwaybill:", error);
                alert("Si è verificato un errore durante il recupero della lettera di vettura. Si prega di riprovare più tardi.");
                return null;
            }
        });
    }
    
















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
        url: "/wp-content/plugins/easy-parcel/js/proxy_getwaybill.php",
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


























// Funzione per mostrare e salvare i dettagli della waybill
function mostraDettagliWaybill(data) {
    // Verifica se la risposta contiene i dettagli della waybill
    if (data && data.response) {
        var response = data.response;
        
        // Verifica se la richiesta è andata a buon fine
        if (response.result === "OK") {
            // Ottieni i dettagli della waybill
            var waybillDetails = {
                order_id: response.order_id,
                waybill_number: response.waybill_number,
                waybill_url: response.waybill_url,
                pickup_code: response.pickup_code,
                bordero_url: response.bordero_url
            };

            // Mostra i dettagli della waybill
            console.log("Dettagli della waybill:", waybillDetails);

            // Salva i dettagli della waybill nel database
            salvaWaybillNelDB(waybillDetails);
        } else {
            // Mostra un messaggio di errore se la richiesta non è andata a buon fine
            console.error("Errore durante il recupero dei dettagli della waybill:", response.error_message);
        }
    } else {
        console.error("Risposta non valida o mancante.");
    }
}

// Funzione per salvare i dettagli della waybill nel database
function salvaWaybillNelDB(waybillDetails) {
    $.ajax({
        url: "/wp-content/plugins/easy-parcel/salva_waybill.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(waybillDetails),
        success: function(response) {
            console.log("Risposta dal salvataggio della waybill:", response);
        },
        error: function(error) {
            console.error("Errore nel salvataggio della waybill:", error);
        }
    });
}









function eseguiOrdineFast() {
    $("#btn_procedi_ordine").html("Sto elaborando l'ordine, un momento...");

    // Recupera i dati dall'ordine
    var orderData = retrieveOrderData();

    // Costruzione del JSON della richiesta per order-fast
    var jsonRequest = {
        call: "order-fast",
        details: {
            client_notes: "TEST-SPEDIZIONE",
            content_notes: "TEST-CONTENUTO",
            return_waybill_file: true,
            customer_id: 12345,
            carrier_code: "SDAM",
            what_to_ship: "M",
            custom: "TEST-CUSTOM",
            accessories: {
                cash_on_delivery_amount: 1000,
                insurance_amount: 500
            },
            shipment_type: "N"
        },
        packages: [
            {
                weight: orderData.package.weight,
                width: orderData.package.width,
                depth: orderData.package.depth,
                height: orderData.package.height,
                number_of_packages: orderData.package.nrPackages
            }
        ],
        sender: {
            name: orderData.sender.lastName + " " + orderData.sender.firstName,
            address: orderData.sender.address,
            postal_code: orderData.senderLocation.cap,
            city: orderData.senderLocation.locality,
            province: orderData.senderLocation.province,
            country_code: orderData.senderLocation.country,
            email: orderData.sender.email,
            phone: orderData.sender.phone,
            mobile: orderData.sender.phone,
            contact: orderData.sender.lastName + " " + orderData.sender.firstName,
            tax_code: "RSSMRA80A01H501U"
        },
        recipient: {
            name: orderData.recipient.lastName + " " + orderData.recipient.firstName,
            address: orderData.recipient.address,
            postal_code: orderData.destinationLocation.cap,
            city: orderData.destinationLocation.locality,
            province: orderData.destinationLocation.province,
            country_code: orderData.destinationLocation.country,
            email: orderData.recipient.email,
            phone: orderData.recipient.phone,
            mobile: orderData.recipient.phone,
            contact: orderData.recipient.lastName + " " + orderData.recipient.firstName,
            tax_code: ""
        },
        ritiro: {
            prenotazione: "S",
            dettagli: {
                ritiro_dove: "M",
                disponibile_dal: "2024-05-21",
                disponibile_ora: "08:00",
                ritiro_mattina_dalle: "08:00",
                ritiro_mattina_alle: "12:00",
                ritiro_pomeriggio_dalle: "14:00",
                ritiro_pomeriggio_alle: "18:00"
            }
        }
    };

    // Stampa il contenuto di jsonRequest nella console
    console.log("Contenuto di jsonRequest:", JSON.stringify(jsonRequest));

    // Chiamata AJAX al proxy PHP per order-fast
    $.ajax({
        url: "/wp-content/plugins/easy-parcel/js/proxy_order_fast.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(jsonRequest),
        success: function(data) {
            console.log("Risposta da Easy Parcel API:", data);
            // Memorizza la risposta nel session storage
            sessionStorage.setItem('easyParcelOrderFastResponse', JSON.stringify(data));
            console.log("Dati memorizzati nel session storage:", sessionStorage.getItem('easyParcelOrderFastResponse'));

            // Estrai l'ID dell'ordine dalla risposta
            var responseData = JSON.parse(data);
            var orderId = responseData.order_id;  // Assicurati che `order_id` sia presente nella risposta

            // Chiamata alla funzione per ottenere la waybill
            getWaybill(orderId);

            // Azioni da eseguire dopo l'ordine
            showOrderDetails();
            console.log("Dettagli ordine salvati");

            // Salvataggio della risposta nel file ordine_fast_risposta.json
            $.ajax({
                url: "/wp-content/plugins/easy-parcel/salva_ordine_fast_risposta.php",
                type: "POST",
                data: { response: JSON.stringify(data) },
                success: function(response) {
                    console.log("Risposta dal salvataggio dell'ordine:", response);
                    getEasyParcelOrderFastData(); // Chiamata qui dopo che i dati sono stati salvati
                },
                error: function(error) {
                    console.error("Errore nel salvataggio della risposta dell'ordine:", error);
                }
            });

            // Reindirizza l'utente alla pagina di conferma ordine
            window.location.href = "https://www.boxespoint.it/conferma-ordine/";
        },
        error: function(error) {
            console.error("Errore AJAX nella chiamata al proxy PHP:", error);
            // Mostra un messaggio di errore all'utente o esegui un'altra azione alternativa
            alert("Si è verificato un errore durante l'ordine. Si prega di riprovare più tardi.");
            $("#btn_procedi_ordine").html("Sto elaborando l'ordine, un momento...");
        },
        complete: function() {
            $("#btn_procedi_ordine").html("Elaborazione dell'Ordine completata!");
        }
    });
}





function cerca_localita(campoCapId, campoLocalitaId, campoProvinciaId) {
    console.log('Sto cercando la città corrispondente al tuo CAP...');

    // Ottieni il valore del CAP dal campo di input corrispondente
    var capValue = document.getElementById(campoCapId).value;

    // Effettua il controllo sulla lunghezza del CAP
    if (capValue.trim().length < 5) {
        // Se il campo del CAP contiene meno di 5 caratteri, non fare nulla
        return;
    }

    // Effettua una chiamata all'API Geonames per ottenere i dati della località in base al CAP
    var url = 'https://secure.geonames.org/postalCodeSearchJSON?postalcode=' + capValue + '&country=IT&username=boxespoint';

    // Effettua la richiesta HTTP GET all'API Geonames
    fetch(url)
    .then(response => {
        if (!response.ok) {
            throw new Error('Errore nella richiesta HTTP, codice ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        // Estrai le località dalla risposta JSON
        if (data.postalCodes.length > 0) {
            // Se ci sono risultati, prendi solo la prima località
            var localita = data.postalCodes[0].placeName;
            var provincia = data.postalCodes[0].adminCode2;

            // Assegna il valore della località e della provincia ai campi corrispondenti
            document.getElementById(campoLocalitaId).value = localita;
            document.getElementById(campoProvinciaId).value = provincia;
        } else {
            // Se non viene trovata nessuna corrispondenza per il CAP, mostra un messaggio di errore
            console.error("Nessuna località trovata per il CAP inserito.");
        }
    })
    .catch(error => {
        // Gestisci eventuali errori di rete o di parsing JSON
        console.error('Si è verificato un errore:', error);
        console.error("Si è verificato un errore durante la ricerca della località. Compila manualmente il campo.");

        // Abilita la modifica manuale del campo della località e della provincia
        document.getElementById(campoLocalitaId).disabled = false;
        document.getElementById(campoProvinciaId).disabled = false;
    });
}



function eseguiOrdine() {
    $("#btn_procedi_ordine").html("Sto elaborando l'ordine, un momento...");

    var orderData = retrieveOrderData();
    var customerNotes = $("#customer_notes").val() || "ACQUISTO DI TEST";

    function getTodayDate() {
        var today = new Date();
        var year = today.getFullYear();
        var month = String(today.getMonth() + 1).padStart(2, '0');
        var day = String(today.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    var jsonRequest = {
        call: "order",
        dettagli: {
            codice_offerta: orderData.offer.offerId,
            note_cliente: customerNotes,
            custom: "BOXES POINT",
        },
        mittente: {
            nominativo: orderData.sender.lastName + " " + orderData.sender.firstName,
            indirizzo: orderData.sender.address,
            email: orderData.sender.email,
            telefono: orderData.sender.phone,
            contatto: orderData.sender.lastName + " " + orderData.sender.firstName,
            codicefiscale: "RSSMRA80A01H501U"
        },
        destinatario: {
            nominativo: orderData.recipient.lastName + " " + orderData.recipient.firstName,
            indirizzo: orderData.recipient.address,
            email: orderData.recipient.email,
            telefono: orderData.recipient.phone,
            contatto: orderData.recipient.lastName + " " + orderData.recipient.firstName
        },
        ritiro: {
            prenotazione: "S",
            dettagli: {
                ritirodove: "M",
                disponibile_dal: getTodayDate(),
                disponibile_ora: "08:00",
                ritiro_mattina_dalle: "08:00",
                ritiro_mattina_alle: "12:00",
                ritiro_pomeriggio_dalle: "14:00",
                ritiro_pomeriggio_alle: "18:00"
            }
        }
    };

    console.log("Contenuto di jsonRequest:", JSON.stringify(jsonRequest));

    // Estrai il codice offerta prima della chiamata AJAX
    var offerCode = jsonRequest.dettagli.codice_offerta;

    $.ajax({
        url: "/wp-content/plugins/easy-parcel/js/proxy_order.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(jsonRequest),
        success: function(data) {
            console.log("Risposta da Easy Parcel API:", data);
            sessionStorage.setItem('easyParcelOrderResponse', JSON.stringify(data));
            console.log("Dati memorizzati nel sessionStorage:", sessionStorage.getItem('easyParcelOrderResponse'));

            console.log("Dettagli ordine salvati");
            
            // Reindirizza alla pagina di riepilogo con il codice dell'offerta salvato prima
            window.location.href = "/riepilogo-ordine/?codice_offerta=" + offerCode;
        },
        error: function(error) {
            console.error("Errore AJAX nella chiamata al proxy PHP:", error);
            alert("Il tuo credito è insufficiente per effettuare l'ordine. Carica i soldi o stuiati u mussu");
        },
        complete: function() {
            $("#btn_procedi_ordine").html("Elaborazione dell'Ordine completata!");
        }
    });
}