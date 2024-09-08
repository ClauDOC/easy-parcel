(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	function clickTruck() {
		$("#comespedire1").val( 'truck' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire1", val: "truck" } );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "FT" } );
		$(".div_dettaglio1").hide();
		$("#div_truck").show();
	}
	
	function clickPlane() {
		$("#comespedire1").val( 'plane' );
		$("#comespedire2").val( '' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire1", val: "plane" } );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "" } );
		$(".div_dettaglio1").hide();
	}
	function clickShip() {
		$("#comespedire1").val( 'ship' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire1", val: "ship" } );
		$("#comespedire2").val( 'LC' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "LC" } );
		$(".div_dettaglio1").hide();
		$("#div_ship").show();
	}
	function clickMultimodale() {
		$("#comespedire1").val( 'multimodale' );
		$("#comespedire2").val( '' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire1", val: "multimodale" } );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "" } );
		$(".div_dettaglio1").hide();
	}
	
	function clickOversize() {
		$("#comespedire1").val( 'oversize' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire1", val: "oversize" } );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "" } );
		$(".div_dettaglio1").hide();
	}
	function clickCome21() {
		$("#comespedire2").val( 'FT' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "FT" } );
	}
	function clickCome22() {
		$("#comespedire2").val( 'LT' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "LT" } );
	}
	function clickCome23() {
		$("#comespedire2").val( 'PL' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "PL" } );
	}
	
	function clickCome24() {
		$("#comespedire24").prop("checked","checked");
		$("#comespedire2").val( 'LC' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "LC" } );
	}
	function clickCome25() {
		$("#comespedire25").prop("checked","checked");
		$("#comespedire2").val( 'FC' );
		$.post( "ajax.php", { azione: "memorizzasessione", key: "comespedire2", val: "FC" } );
	}
	function selezionata_misurapredefinita() {
		arr = $("#misurepredefinite").val().split("|");
		$("#lunghezza").val( arr[0] );
		$("#larghezza").val( arr[1] );
		$("#altezza").val( arr[2] );
		$("#peso").val( arr[3] );
		$("#colli").val( 1 );
		$("#colli").focus();
	}
	
	function calcola_test() {
		$("#btn_procedi").html( "<img class='spinner-small' src='images/ajax-loader2.gif'>" );
		var area = $("#area").val();
		// mittente	
		var mnaz = $("#nazione1").val();
		var mcap = $("#cap1").val();
		var mloc = $("#localita1").val();
		var mpro = $("#provincia1").val();
		// destinatario
		var dcou = $("#country2").val();
		var dnaz = $("#nazione2").val();
		var dcap = $("#cap2").val();
		var dloc = $("#localita2").val();
		var dpro = $("#provincia2").val();
		if( !mnaz || !mcap || !mloc || !mpro || !dnaz || !dcap || !dloc || !dpro || !dcou ) {
			swal("Attenzione !!!", "Non sono stati compilati correttamente tutti i dati della spedizione", "error");
			$("#btn_procedi").html( "Procedi" );
		} else {
			$.post( "ajax.php", { azione: "memorizza_anagrafiche", mnaz: mnaz, mcap: mcap, mloc: mloc, mpro: mpro, dnaz: dnaz, dcap: dcap, dloc: dloc, dpro: dpro, dcou: dcou, area: area } ).done(function() { 
				$.post( "ajax_calcoli2.php", {
					azione: "step1"
				} ).done(function( data ) { 
					if(data == -1) {
						swal("Attenzione !!!", "Il calcolo della spedizione non è riuscito.\nContattateci direttamente", "error");
						$("#btn_procedi").html( "Procedi" );
					} else {
							document.location.href = 'carrello.php';
					}
				});
			});
		}
	}
	
})( jQuery );