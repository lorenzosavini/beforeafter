<?php
/**
 * Impostazioni del tema: un'unica opzione serializzata, una pagina admin a
 * schede. Ogni testo visibile sul sito che non sta in una pagina o in un
 * corso si modifica da qui. I campi «elenco» si aggiungono, ordinano ed
 * eliminano con i pulsanti.
 */

defined( 'ABSPATH' ) || exit;

function ip_settings_fields() {
	return array(
		'identita'    => array(
			'title'  => 'Identità',
			'fields' => array(
				'brand'        => array( 'Nome del sito/infopoint', 'text', '', 'Se vuoto viene usato il titolo del sito. Il logo si carica da Aspetto → Personalizza → Identità del sito.' ),
				'city'         => array( 'Città dell’infopoint', 'text', '', 'Usata nei titoli: «Infopoint UniMarconi …».' ),
				'company_name' => array( 'Ragione sociale', 'text', '' ),
				'vat'          => array( 'Partita IVA', 'text', '' ),
				'disclaimer'   => array( 'Nota a piè di pagina', 'textarea', 'Infopoint autorizzato per l’orientamento e le iscrizioni all’Università degli Studi Guglielmo Marconi. Questo non è il sito ufficiale dell’Ateneo.' ),
			),
		),
		'contatti'    => array(
			'title'  => 'Contatti',
			'fields' => array(
				'phone1'       => array( 'Telefono principale', 'text', '', 'Mostrato in testata e nei pulsanti «Chiama».' ),
				'phone1_label' => array( 'Etichetta telefono principale', 'text', 'Orientamento' ),
				'phone2'       => array( 'Telefono secondario', 'text', '' ),
				'phone2_label' => array( 'Etichetta telefono secondario', 'text', 'Segreteria' ),
				'whatsapp'     => array( 'Numero WhatsApp', 'text', '', 'Con prefisso, es. +39 334 297 4601. Vuoto = pulsante nascosto.' ),
				'wa_text'      => array( 'Messaggio WhatsApp precompilato', 'text', 'Buongiorno, vorrei informazioni sui corsi UniMarconi.' ),
				'email'        => array( 'Email pubblica', 'text', '' ),
				'address'      => array( 'Indirizzo sede', 'textarea', '' ),
				'address_exam' => array( 'Indirizzo sede d’esame', 'textarea', '' ),
				'hours'        => array( 'Orari', 'textarea', "Lun–Ven 9:00–13:00 / 15:00–19:00\nSabato su appuntamento" ),
				'maps_url'     => array( 'Link Google Maps', 'url', '' ),
				'facebook'     => array( 'Facebook', 'url', '' ),
				'instagram'    => array( 'Instagram', 'url', '' ),
				'linkedin'     => array( 'LinkedIn', 'url', '' ),
				'youtube'      => array( 'YouTube', 'url', '' ),
				'tiktok'       => array( 'TikTok', 'url', '' ),
			),
		),
		'home'        => array(
			'title'  => 'Home page',
			'fields' => array(
				'hero_eyebrow' => array( 'Occhiello sopra il titolo', 'text', '', 'Vuoto = «Infopoint UniMarconi · città».' ),
				'hero_title'   => array( 'Titolo principale', 'text', 'Iscriviti all’Università Marconi con un orientatore al tuo fianco' ),
				'hero_text'    => array( 'Sottotitolo', 'textarea', 'Ti aiutiamo a scegliere il corso, verifichiamo gratis i crediti che puoi farti riconoscere e seguiamo l’immatricolazione al posto tuo.' ),
				'hero_ticks'   => array( 'Punti di forza (accanto al modulo)', 'repeater', array(
					array( 'testo' => 'Iscrizioni aperte tutto l’anno, senza test d’ingresso' ),
					array( 'testo' => 'Lezioni online disponibili 24 ore su 24' ),
					array( 'testo' => 'Esami in presenza in sedi in tutta Italia' ),
					array( 'testo' => 'Valutazione gratuita degli esami già sostenuti' ),
				), '', array( 'testo' => array( 'Testo', 'text' ) ) ),
				'facts'        => array( 'Striscia numeri', 'repeater', array(
					array( 'titolo' => 'Dal 2004', 'testo' => 'prima università digitale riconosciuta dal MUR' ),
					array( 'titolo' => 'Valore legale', 'testo' => 'titoli validi per concorsi pubblici e ordini professionali' ),
					array( 'titolo' => '12 rate', 'testo' => 'retta mensile senza interessi' ),
					array( 'titolo' => 'Tutor', 'testo' => 'un riferimento dall’iscrizione alla laurea' ),
				), 'Consigliate 4 voci.', array( 'titolo' => array( 'Titolo', 'text' ), 'testo' => array( 'Testo', 'text' ) ) ),
				'sections'     => array( 'Sezioni e ordine', 'checklist', array( 'offerta', 'featured', 'contenuto', 'agevolazioni', 'passi', 'cfu', 'recensioni', 'faq', 'news', 'cta' ), '', array(
					'offerta'      => 'Offerta formativa (tipologie)',
					'featured'     => 'Corsi in evidenza',
					'contenuto'    => 'Contenuto della pagina Home (editor)',
					'agevolazioni' => 'Agevolazioni',
					'passi'        => 'Come funziona',
					'cfu'          => 'Box riconoscimento CFU',
					'recensioni'   => 'Recensioni',
					'faq'          => 'Domande frequenti',
					'news'         => 'Ultime news',
					'cta'          => 'Fascia finale di contatto',
				) ),
				't_offerta'    => array( 'Titolo «Offerta formativa»', 'text', 'Offerta formativa' ),
				't_featured'   => array( 'Titolo «Corsi in evidenza»', 'text', 'I corsi più richiesti' ),
				't_agev'       => array( 'Titolo «Agevolazioni»', 'text', 'Agevolazioni sulla retta' ),
				'agev_text'    => array( 'Testo «Agevolazioni»', 'textarea', 'Le agevolazioni si applicano al momento dell’immatricolazione e non sono retroattive: verifichiamo con te quale ti spetta prima dell’iscrizione.' ),
				'agev_count'   => array( 'Quante agevolazioni mostrare in home', 'text', '4' ),
				't_passi'      => array( 'Titolo «Come funziona»', 'text', 'Come funziona l’iscrizione con noi' ),
				'cfu_title'    => array( 'Box CFU: titolo', 'text', 'Hai già sostenuto esami all’università?' ),
				'cfu_text'     => array( 'Box CFU: testo', 'textarea', 'Esami di un percorso interrotto, una prima laurea, certificazioni o esperienza professionale possono valere crediti formativi. Con 30 CFU riconosciuti puoi iscriverti direttamente al secondo anno.' ),
				'cfu_button'   => array( 'Box CFU: pulsante', 'text', 'Richiedi la prevalutazione gratuita' ),
				't_reviews'    => array( 'Titolo «Recensioni»', 'text', 'Cosa dicono gli studenti' ),
				'reviews'      => array( 'Recensioni', 'repeater', array(), 'Solo recensioni reali, con il consenso dell’autore.', array( 'nome' => array( 'Nome', 'text' ), 'corso' => array( 'Corso o ruolo', 'text' ), 'testo' => array( 'Testo', 'textarea' ), 'voto' => array( 'Voto (1–5)', 'text' ) ) ),
				't_faq'        => array( 'Titolo «Domande frequenti»', 'text', 'Domande frequenti' ),
				'faq'          => array( 'Domande frequenti', 'repeater', ip_default_faq(), 'Usate in home e nella pagina Contatti con lo shortcode [ip_faq].', array( 'domanda' => array( 'Domanda', 'text' ), 'risposta' => array( 'Risposta', 'textarea' ) ) ),
				't_news'       => array( 'Titolo «News»', 'text', 'Novità e scadenze' ),
			),
		),
		'testi'       => array(
			'title'  => 'Testi ricorrenti',
			'fields' => array(
				'topbar_text' => array( 'Testo barra in alto', 'text', 'Iscrizioni aperte tutto l’anno · Valutazione dei crediti gratuita' ),
				'nav_cta'     => array( 'Pulsante in testata', 'text', 'Richiedi informazioni' ),
				'steps'       => array( 'Passi «Come funziona»', 'repeater', array(
					array( 'titolo' => 'Ci racconti cosa cerchi', 'testo' => 'Una telefonata o un messaggio: titolo di studio, lavoro, tempo a disposizione, obiettivi.' ),
					array( 'titolo' => 'Valutiamo la tua carriera', 'testo' => 'Se hai già sostenuto esami o hai titoli professionali, chiediamo gratis la prevalutazione dei crediti.' ),
					array( 'titolo' => 'Scegli corso e retta', 'testo' => 'Ti mostriamo piano di studi, costi e agevolazioni a cui hai diritto. Decidi senza fretta.' ),
					array( 'titolo' => 'Ci occupiamo dell’iscrizione', 'testo' => 'Prepariamo con te la domanda di immatricolazione e restiamo il tuo riferimento fino alla laurea.' ),
				), 'Usati in home, nelle schede corso e con lo shortcode [ip_passi].', array( 'titolo' => array( 'Titolo', 'text' ), 'testo' => array( 'Testo', 'textarea' ) ) ),
				'cta_title'   => array( 'Fascia di contatto: titolo', 'text', 'Non sai ancora quale corso scegliere?' ),
				'cta_text'    => array( 'Fascia di contatto: testo', 'textarea', 'Un orientatore ti richiama, ascolta cosa ti serve e ti propone il percorso più adatto, con costi e agevolazioni. Senza impegno.' ),
				'end_title'   => array( 'Modulo in fondo alle pagine: titolo', 'text', 'Parla con un orientatore' ),
				'end_text'    => array( 'Modulo in fondo alle pagine: testo', 'textarea', 'Ti spieghiamo come funziona lo studio online, quanto costa davvero il percorso che ti interessa e quali agevolazioni puoi ottenere. La consulenza è gratuita e non ti impegna.' ),
				'course_empty' => array( 'Scheda corso senza testo', 'textarea', 'Stiamo completando la scheda di questo corso. Lasciaci i tuoi dati: ti inviamo il piano di studi aggiornato, i costi e le agevolazioni a cui hai diritto.' ),
			),
		),
		'conversione' => array(
			'title'  => 'Moduli e richieste',
			'fields' => array(
				'price_from'   => array( 'Retta mensile a partire da (€)', 'text', '135', 'Solo il numero. Vuoto = non mostrato.' ),
				'retta_std'    => array( 'Retta standard corsi di laurea', 'text', '€ 2.760/anno (€ 230/mese)', 'Mostrata nelle schede delle lauree senza un costo specifico.' ),
				'lead_to'      => array( 'Email che ricevono le richieste', 'text', '', 'Più indirizzi separati da virgola. Vuoto = email dell’amministratore.' ),
				'thanks_page'  => array( 'Pagina di ringraziamento', 'page', '' ),
				'privacy_page' => array( 'Pagina informativa privacy', 'page', '' ),
				'webhook_url'  => array( 'Webhook (CRM, Make, Zapier)', 'url', '', 'Ogni richiesta viene inviata qui in JSON. Facoltativo.' ),
				'form_courses' => array( 'Voci del campo «Ti interessa»', 'repeater', array(
					array( 'voce' => 'Laurea triennale' ), array( 'voce' => 'Laurea magistrale' ), array( 'voce' => 'Laurea a ciclo unico' ),
					array( 'voce' => 'Master' ), array( 'voce' => 'Corsi per insegnanti' ), array( 'voce' => 'Corsi di formazione' ), array( 'voce' => 'Non lo so ancora' ),
				), '', array( 'voce' => array( 'Voce', 'text' ) ) ),
				'form_title'   => array( 'Titolo modulo informazioni', 'text', 'Ricevi costi e piano di studi' ),
				'form_text'    => array( 'Testo modulo informazioni', 'text', 'Ti rispondiamo entro un giorno lavorativo, anche su WhatsApp se preferisci.' ),
				'form_button'  => array( 'Pulsante modulo informazioni', 'text', 'Invia la richiesta' ),
				'privacy_text' => array( 'Testo consenso privacy', 'textarea', 'Ho letto l’informativa privacy e acconsento al trattamento dei dati per ricevere le informazioni richieste.', 'Le parole «informativa privacy» diventano il link alla pagina scelta sopra.' ),
				'mkt_text'     => array( 'Testo consenso marketing', 'textarea', 'Voglio ricevere aggiornamenti su corsi, agevolazioni e scadenze (facoltativo).' ),
			),
		),
		'sedi'        => array(
			'title'  => 'Sedi d’esame',
			'fields' => array(
				'exam_sites' => array( 'Sedi d’esame', 'repeater', ip_default_exam_sites(), 'Mostrate con lo shortcode [ip_sedi], raggruppate per regione.', array( 'regione' => array( 'Regione', 'text' ), 'citta' => array( 'Città', 'text' ), 'indirizzo' => array( 'Struttura e indirizzo', 'text' ) ) ),
			),
		),
		'aspetto'     => array(
			'title'  => 'Aspetto',
			'fields' => array(
				'color_brand'       => array( 'Colore principale', 'color', '#225e48', 'Verde UniMarconi.' ),
				'color_brand_dark'  => array( 'Colore principale (hover)', 'color', '#174f3a' ),
				'color_brand_deep'  => array( 'Colore principale scuro (barra in alto)', 'color', '#0e261d' ),
				'color_accent'      => array( 'Colore pulsanti di conversione', 'color', '#a0300e', 'Rosso mattone UniMarconi.' ),
				'color_accent_dark' => array( 'Colore pulsanti di conversione (hover)', 'color', '#ca451d' ),
				'color_soft'        => array( 'Sfondo sezioni', 'color', '#f0f0f0' ),
				'color_dark'        => array( 'Sfondo piè di pagina', 'color', '#373737' ),
				'font_brand'        => array( 'Font Montserrat (come unimarconi.it)', 'checkbox', '1', 'Incluso nel tema, 38 KB. Disattivato = font di sistema, ancora più veloce.' ),
			),
		),
		'tracking'    => array(
			'title'  => 'Tracciamento e cookie',
			'fields' => array(
				'gtm_id'         => array( 'Google Tag Manager ID', 'text', '', 'Es. GTM-XXXXXXX. Caricato con Consent Mode v2 (consensi negati di default).' ),
				'consent_banner' => array( 'Banner cookie integrato', 'checkbox', '1', 'Banner leggero (accetta / rifiuta) quando GTM è attivo. Disattivalo se usi un servizio esterno.' ),
				'consent_text'   => array( 'Testo banner cookie', 'textarea', 'Usiamo cookie tecnici e, solo con il tuo consenso, cookie di statistica e marketing per misurare le visite e migliorare le campagne.' ),
				'cookie_page'    => array( 'Pagina cookie policy', 'page', '' ),
				'head_code'      => array( 'Codice in &lt;head&gt;', 'code', '', 'Es. script di verifica o di un servizio cookie esterno.' ),
				'footer_code'    => array( 'Codice prima di &lt;/body&gt;', 'code', '' ),
			),
		),
		'prestazioni' => array(
			'title'  => 'Prestazioni',
			'fields' => array(
				'inline_css'       => array( 'CSS incorporato nella pagina', 'checkbox', '1', 'Elimina una richiesta di rete: il CSS del tema pesa pochi KB.' ),
				'disable_comments' => array( 'Disattiva commenti', 'checkbox', '1' ),
				'disable_seo'      => array( 'Disattiva SEO integrato', 'checkbox', '', 'Si disattiva da solo se è attivo Yoast, Rank Math o SEOPress.' ),
			),
		),
	);
}

function ip_default_faq() {
	return array(
		array( 'domanda' => 'La laurea online UniMarconi ha lo stesso valore di una laurea tradizionale?', 'risposta' => 'Sì. L’Università degli Studi Guglielmo Marconi è un ateneo riconosciuto dal Ministero dell’Università e della Ricerca dal 2004. I titoli hanno pieno valore legale: valgono per i concorsi pubblici, per l’accesso a master e abilitazioni e per l’iscrizione agli ordini professionali, dove previsto dal corso.' ),
		array( 'domanda' => 'Dove si sostengono gli esami?', 'risposta' => 'Gli esami si svolgono in presenza, nelle sedi d’esame autorizzate distribuite in tutta Italia. Prenoti l’appello dalla piattaforma MyUnimarconi e scegli la sede più comoda.' ),
		array( 'domanda' => 'Quando posso iscrivermi? Serve un test d’ingresso?', 'risposta' => 'Ai corsi di laurea ad accesso libero ci si iscrive in qualsiasi periodo dell’anno, senza test d’ingresso. Dopo l’immatricolazione è previsto solo un test orientativo non selettivo.' ),
		array( 'domanda' => 'Quanto costa?', 'risposta' => 'La retta standard dei corsi di laurea è di € 2.760 l’anno (€ 230 al mese), comprensiva di diritti di segreteria e tasse d’esame; per i curriculum in lingua inglese è di € 3.000. Con le agevolazioni si parte da € 135 al mese. Tassa regionale e tassa di laurea sono a parte.' ),
		array( 'domanda' => 'Lavoro: riuscirò a seguire le lezioni?', 'risposta' => 'Videolezioni e materiali sono disponibili sulla piattaforma 24 ore su 24 e non c’è obbligo di frequenza. È possibile anche l’iscrizione a tempo parziale, con retta al 50%.' ),
		array( 'domanda' => 'Che cosa fa l’infopoint e quanto costa la consulenza?', 'risposta' => 'Siamo un punto informativo per l’orientamento e le iscrizioni all’Università Marconi: ti aiutiamo a scegliere il corso, chiediamo per te la valutazione dei crediti, seguiamo l’immatricolazione e restiamo un riferimento durante il percorso. La consulenza è gratuita.' ),
	);
}

/**
 * Sedi d'esame e poli ufficiali (unimarconi.it, ottobre 2026).
 */
function ip_default_exam_sites() {
	$rows = array(
		array( 'Abruzzo', 'L’Aquila', 'Fondazione OSA/Confindustria, Via Campo di Pile snc, 67100 Nucleo Industriale L’Aquila' ),
		array( 'Abruzzo', 'Pescara', 'Infobasic, Via Tirino 99, 65129 Pescara' ),
		array( 'Calabria', 'Reggio Calabria', 'Centro Studi V. Lanza, Via Filippini 14, 89125 Reggio Calabria' ),
		array( 'Calabria', 'Cosenza', 'Corso Mazzini 166, 87100 Cosenza' ),
		array( 'Campania', 'Napoli', 'Piazzetta Nilo 7, 80134 Napoli' ),
		array( 'Campania', 'Capaccio Paestum', 'Via Stazione di Albanella 10-12, 84047 Capaccio Paestum (SA)' ),
		array( 'Campania', 'Salerno', 'APIS Salerno, Via L. Guercio 423, 84134 Salerno' ),
		array( 'Emilia-Romagna', 'Bologna', 'Istituto Salesiano B.V. di San Luca, Via Jacopo della Quercia 1, 40128 Bologna' ),
		array( 'Emilia-Romagna', 'Reggio Emilia', 'AIS, Via Fratelli Pietro e Alessandro Verri 20/a, 42124 Reggio Emilia' ),
		array( 'Friuli-Venezia Giulia', 'Udine', 'c/o Company Center, Via Paparotti 13, 33100 Udine' ),
		array( 'Lazio', 'Roma', 'Via Paolo Emilio 29, 00192 Roma' ),
		array( 'Lazio', 'Latina', 'SS 156 Dei Monti Lepini 2, 04100 Latina' ),
		array( 'Lazio', 'Viterbo', 'c/o Cowo849, Via G. Fontecedro 2, 01100 Viterbo' ),
		array( 'Liguria', 'Chiavari', 'Istituto Alessandro Manzoni, Piazza Mazzini 1, 16043 Chiavari (GE)' ),
		array( 'Liguria', 'La Spezia', 'CMD Formazione, Viale Piero Pozzoli snc, 19037 Santo Stefano di Magra (SP)' ),
		array( 'Liguria', 'Savona', 'ESE Savona, Via Molinero 4R, 17100 Savona' ),
		array( 'Lombardia', 'Milano', 'c/o Spazio Pin, Viale Sondrio 5, 20124 Milano' ),
		array( 'Lombardia', 'Milano', 'UniMarconi Hub, Via Meravigli 16, 20121 Milano' ),
		array( 'Lombardia', 'Varese', 'Licei Manfredini – Aula Magna, Via Merano 1, 21100 Varese' ),
		array( 'Lombardia', 'Brescia', 'LABA Libera Accademia di Belle Arti, Via Cefalonia 58, 25124 Brescia' ),
		array( 'Marche', 'Ancona', 'Istituti Paritari Caggiari, Via Achille Barilatti 47, 60127 Ancona' ),
		array( 'Piemonte', 'Torino', 'Via Onorato Vigliani 11 int. 9, 10135 Torino' ),
		array( 'Puglia', 'Bari', 'Via San Lorenzo 11/C, 70124 Bari' ),
		array( 'Puglia', 'Foggia', 'Cambridge Academy, Via Vincenzo Gioberti 128, 71122 Foggia' ),
		array( 'Puglia', 'Lecce', 'Associazione Kronos, Via Cavour 23-25-27, 73100 Lecce' ),
		array( 'Sardegna', 'Cagliari', 'Metigroup, Via Sernagiotto 7, 09030 Elmas (CA)' ),
		array( 'Sardegna', 'Olbia', 'Aeroporto Costa Smeralda, 1° piano, 07026 Olbia' ),
		array( 'Sicilia', 'Palermo', 'Cosicert Academy, Via Roma 443, 90193 Palermo' ),
		array( 'Sicilia', 'Catania', 'NH Catania Centro, Piazza Trento 13, 95123 Catania' ),
		array( 'Sicilia', 'Caltanissetta', 'Istituto Oasi Cristo Re, Via Monte S. Giuliano 4, 93100 Caltanissetta' ),
		array( 'Toscana', 'Firenze', 'Il Fuligno – CSF Montedomini, Via Faenza 48, 50123 Firenze' ),
		array( 'Toscana', 'Chiusi', 'Auditorium la Villetta, Via della Villetta 3, 53043 Chiusi (SI)' ),
		array( 'Trentino-Alto Adige', 'Trento', 'Formazione e Sviluppo, Via Zambra 11, 38121 Trento' ),
		array( 'Umbria', 'Perugia', 'Campus coworking, Via Fratelli Cairoli 24, 06125 Perugia' ),
		array( 'Umbria', 'Terni', 'Garden Hotel, Viale D. Bramante 4/6, 05100 Terni' ),
		array( 'Veneto', 'Verona', 'Centro Camilliano di Formazione, Via C.C. Bresciani 2, 37124 Verona' ),
		array( 'Veneto', 'Padova', 'Four Points by Sheraton, Corso Argentina 5, 35129 Padova' ),
		array( 'Veneto', 'Venezia-Mestre', 'Istituto Salesiano San Marco, Via dei Salesiani 15, 30174 Venezia-Mestre' ),
	);
	return array_map( function ( $r ) {
		return array( 'regione' => $r[0], 'citta' => $r[1], 'indirizzo' => $r[2] );
	}, $rows );
}

function ip_settings_defaults() {
	$d = array();
	foreach ( ip_settings_fields() as $section ) {
		foreach ( $section['fields'] as $k => $f ) {
			$d[ $k ] = $f[2];
		}
	}
	return $d;
}

/**
 * Legge un'impostazione. I campi mai salvati prendono il valore predefinito.
 */
function ip_opt( $key ) {
	static $opts = null;
	if ( null === $opts || 'ip_flush' === $key ) {
		$saved = get_option( 'ip_settings', array() );
		$opts  = array_merge( ip_settings_defaults(), is_array( $saved ) ? $saved : array() );
		if ( 'ip_flush' === $key ) {
			return '';
		}
	}
	if ( 'brand' === $key && empty( $opts['brand'] ) ) {
		return get_bloginfo( 'name' );
	}
	return isset( $opts[ $key ] ) ? $opts[ $key ] : '';
}
add_action( 'update_option_ip_settings', function () {
	ip_opt( 'ip_flush' );
} );

/**
 * Righe di un campo elenco, senza righe vuote.
 */
function ip_rows( $key ) {
	$v = ip_opt( $key );
	if ( ! is_array( $v ) ) {
		return array();
	}
	return array_values( array_filter( $v, function ( $r ) {
		return is_array( $r ) && implode( '', array_map( 'trim', $r ) ) !== '';
	} ) );
}

function ip_section_on( $s ) {
	$v = ip_opt( 'sections' );
	return is_array( $v ) && in_array( $s, $v, true );
}

add_action( 'admin_menu', function () {
	add_menu_page( 'Infopoint', 'Infopoint', 'manage_options', 'ip-settings', 'ip_settings_page', 'dashicons-location-alt', 59 );
	add_submenu_page( 'ip-settings', 'Impostazioni', 'Impostazioni', 'manage_options', 'ip-settings', 'ip_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'ip_settings', 'ip_settings', array( 'sanitize_callback' => 'ip_sanitize_settings' ) );
} );

/**
 * Salva solo i campi della scheda inviata, conservando le altre.
 */
function ip_sanitize_settings( $in ) {
	$saved  = get_option( 'ip_settings', array() );
	$out    = is_array( $saved ) ? $saved : array();
	$fields = ip_settings_fields();
	$tab    = isset( $in['_tab'] ) && isset( $fields[ $in['_tab'] ] ) ? $in['_tab'] : null;
	if ( ! $tab ) {
		return $out; // Salvataggi da altre fonti (es. importatore) passano da update_option diretto.
	}
	foreach ( $fields[ $tab ]['fields'] as $k => $f ) {
		$v = isset( $in[ $k ] ) ? wp_unslash( $in[ $k ] ) : '';
		$out[ $k ] = ip_sanitize_field( $f, $v );
	}
	return $out;
}

function ip_sanitize_field( $f, $v ) {
	switch ( $f[1] ) {
		case 'checkbox':
			return $v ? '1' : '';
		case 'url':
			return esc_url_raw( trim( (string) $v ) );
		case 'page':
			return (string) absint( $v );
		case 'color':
			return sanitize_hex_color( $v ) ? sanitize_hex_color( $v ) : $f[2];
		case 'code':
			return current_user_can( 'unfiltered_html' ) ? (string) $v : wp_kses_post( $v );
		case 'textarea':
			return sanitize_textarea_field( (string) $v );
		case 'checklist':
			return array_values( array_intersect( (array) $v, array_keys( $f[4] ) ) );
		case 'repeater':
			$rows = array();
			foreach ( (array) $v as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$clean = array();
				foreach ( $f[4] as $sk => $sf ) {
					$sv           = isset( $row[ $sk ] ) ? $row[ $sk ] : '';
					$clean[ $sk ] = 'textarea' === $sf[1] ? sanitize_textarea_field( $sv ) : ( 'url' === $sf[1] ? esc_url_raw( $sv ) : sanitize_text_field( $sv ) );
				}
				if ( implode( '', $clean ) !== '' ) {
					$rows[] = $clean;
				}
			}
			return $rows;
		default:
			return sanitize_text_field( (string) $v );
	}
}

function ip_settings_page() {
	$fields = ip_settings_fields();
	$tab    = isset( $_GET['tab'] ) && isset( $fields[ $_GET['tab'] ] ) ? sanitize_key( $_GET['tab'] ) : 'identita';
	?>
	<div class="wrap ip-admin">
		<h1>Infopoint — impostazioni</h1>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $fields as $id => $s ) : ?>
				<a class="nav-tab <?php echo $id === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=' . $id ) ); ?>"><?php echo esc_html( $s['title'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form method="post" action="options.php">
			<?php settings_fields( 'ip_settings' ); ?>
			<input type="hidden" name="ip_settings[_tab]" value="<?php echo esc_attr( $tab ); ?>">
			<table class="form-table" role="presentation">
				<?php foreach ( $fields[ $tab ]['fields'] as $k => $f ) : ?>
					<tr>
						<th scope="row"><label for="ip-<?php echo esc_attr( $k ); ?>"><?php echo wp_kses_post( $f[0] ); ?></label></th>
						<td>
							<?php ip_settings_field( $k, $f ); ?>
							<?php if ( ! empty( $f[3] ) ) : ?>
								<p class="description"><?php echo wp_kses_post( $f[3] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function ip_settings_field( $k, $f ) {
	$name  = 'ip_settings[' . $k . ']';
	$id    = 'ip-' . $k;
	$saved = get_option( 'ip_settings', array() );
	$val   = is_array( $saved ) && array_key_exists( $k, $saved ) ? $saved[ $k ] : $f[2];
	switch ( $f[1] ) {
		case 'checkbox':
			printf( '<label><input type="checkbox" id="%2$s" name="%1$s" value="1" %3$s> Attivo</label>', esc_attr( $name ), esc_attr( $id ), checked( $val, '1', false ) );
			break;
		case 'checklist':
			// Ordinabile: l'ordine delle righe è l'ordine delle sezioni sul sito.
			$order = array_merge( array_intersect( (array) $val, array_keys( $f[4] ) ), array_diff( array_keys( $f[4] ), (array) $val ) );
			echo '<div class="ip-rep ip-check" data-name="' . esc_attr( $name ) . '"><div class="ip-rep-list">';
			foreach ( $order as $ok ) {
				printf( '<div class="ip-rep-row"><label class="ip-rep-fields"><span><input type="checkbox" name="%s[]" value="%s" %s> %s</span></label><div class="ip-rep-tools"><button type="button" class="button-link" data-ip-up title="Sposta su">↑</button><button type="button" class="button-link" data-ip-down title="Sposta giù">↓</button></div></div>', esc_attr( $name ), esc_attr( $ok ), checked( in_array( $ok, (array) $val, true ), true, false ), esc_html( $f[4][ $ok ] ) );
			}
			echo '</div></div>';
			break;
		case 'color':
			printf( '<input type="color" id="%s" name="%s" value="%s"> <code>%s</code> <button type="button" class="button-link" data-ip-reset-color="%s">ripristina</button>', esc_attr( $id ), esc_attr( $name ), esc_attr( $val ), esc_html( $val ), esc_attr( $f[2] ) );
			break;
		case 'page':
			wp_dropdown_pages( array(
				'name'              => $name,
				'id'                => $id,
				'selected'          => (int) $val,
				'show_option_none'  => '— Nessuna —',
				'option_none_value' => '0',
			) );
			break;
		case 'textarea':
			printf( '<textarea id="%s" name="%s" rows="3" class="large-text">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $val ) );
			break;
		case 'code':
			printf( '<textarea id="%s" name="%s" rows="5" class="large-text code">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $val ) );
			break;
		case 'repeater':
			ip_repeater( $name, $f[4], is_array( $val ) ? $val : array() );
			break;
		default:
			printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">', 'url' === $f[1] ? 'url' : 'text', esc_attr( $id ), esc_attr( $name ), esc_attr( $val ) );
	}
}

/**
 * Campo elenco riutilizzabile (impostazioni e riquadri dei contenuti).
 *
 * @param string $name   Nome base del campo.
 * @param array  $fields Sottocampi: chiave => array( etichetta, tipo ). Tipi: text, textarea, url, media.
 * @param array  $rows   Valori.
 */
function ip_repeater( $name, $fields, $rows ) {
	$render = function ( $i, $row ) use ( $name, $fields ) {
		echo '<div class="ip-rep-row">';
		echo '<div class="ip-rep-fields">';
		foreach ( $fields as $sk => $sf ) {
			$n = sprintf( '%s[%s][%s]', $name, $i, $sk );
			$v = isset( $row[ $sk ] ) ? $row[ $sk ] : '';
			echo '<label class="ip-rep-f ip-rep-' . esc_attr( $sf[1] ) . '"><span>' . esc_html( $sf[0] ) . '</span>';
			if ( 'textarea' === $sf[1] ) {
				printf( '<textarea name="%s" rows="3">%s</textarea>', esc_attr( $n ), esc_textarea( $v ) );
			} elseif ( 'media' === $sf[1] ) {
				printf( '<span class="ip-media"><input type="url" name="%s" value="%s"><button type="button" class="button" data-ip-media>Scegli file</button></span>', esc_attr( $n ), esc_attr( $v ) );
			} else {
				printf( '<input type="%s" name="%s" value="%s">', 'url' === $sf[1] ? 'url' : 'text', esc_attr( $n ), esc_attr( $v ) );
			}
			echo '</label>';
		}
		echo '</div><div class="ip-rep-tools"><button type="button" class="button-link" data-ip-up title="Sposta su">↑</button><button type="button" class="button-link" data-ip-down title="Sposta giù">↓</button><button type="button" class="button-link ip-rep-del" data-ip-del title="Elimina">Elimina</button></div></div>';
	};
	echo '<div class="ip-rep" data-name="' . esc_attr( $name ) . '">';
	echo '<div class="ip-rep-list">';
	foreach ( array_values( $rows ) as $i => $row ) {
		$render( $i, $row );
	}
	echo '</div><template>';
	$render( '__i__', array() );
	echo '</template><button type="button" class="button" data-ip-add>+ Aggiungi</button></div>';
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	wp_enqueue_style( 'ip-admin', IP_URI . '/assets/admin.css', array(), IP_VERSION );
	wp_enqueue_script( 'ip-admin', IP_URI . '/assets/admin.js', array(), IP_VERSION, true );
	$screen = get_current_screen();
	if ( $screen && in_array( $screen->post_type, array( 'corso', 'curriculum', 'agevolazione' ), true ) ) {
		wp_enqueue_media();
	}
} );
