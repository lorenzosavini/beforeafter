<?php
/**
 * Impostazioni del tema: un'unica opzione serializzata, una pagina admin.
 */

defined( 'ABSPATH' ) || exit;

function ip_settings_fields() {
	return array(
		'identita'   => array(
			'title'  => 'Identità',
			'fields' => array(
				'brand'        => array( 'Nome del sito/infopoint', 'text', '', 'Se vuoto viene usato il titolo del sito.' ),
				'city'         => array( 'Città dell’infopoint', 'text', '', 'Usata nei titoli: «Infopoint UniMarconi di …».' ),
				'company_name' => array( 'Ragione sociale', 'text', '' ),
				'vat'          => array( 'Partita IVA', 'text', '' ),
				'disclaimer'   => array( 'Nota a piè di pagina', 'textarea', 'Infopoint autorizzato per l’orientamento e le iscrizioni all’Università degli Studi Guglielmo Marconi. Questo non è il sito ufficiale dell’Ateneo.' ),
			),
		),
		'contatti'   => array(
			'title'  => 'Contatti',
			'fields' => array(
				'phone1'       => array( 'Telefono principale', 'text', '', 'Formato libero, es. 334 297 4601. Viene mostrato in testata e nei pulsanti «Chiama».' ),
				'phone1_label' => array( 'Etichetta telefono principale', 'text', 'Orientamento' ),
				'phone2'       => array( 'Telefono secondario', 'text', '' ),
				'phone2_label' => array( 'Etichetta telefono secondario', 'text', 'Segreteria' ),
				'whatsapp'     => array( 'Numero WhatsApp', 'text', '', 'Con prefisso internazionale, es. +39 334 297 4601. Vuoto = pulsante nascosto.' ),
				'email'        => array( 'Email pubblica', 'text', '' ),
				'address'      => array( 'Indirizzo sede', 'textarea', '' ),
				'address_exam' => array( 'Indirizzo sede d’esame', 'textarea', '' ),
				'hours'        => array( 'Orari', 'textarea', "Lun–Ven 9:00–13:00 / 15:00–19:00\nSabato su appuntamento" ),
				'maps_url'     => array( 'Link Google Maps', 'url', '' ),
				'facebook'     => array( 'Facebook', 'url', '' ),
				'instagram'    => array( 'Instagram', 'url', '' ),
			),
		),
		'conversione' => array(
			'title'  => 'Conversione',
			'fields' => array(
				'hero_title'   => array( 'Titolo home', 'text', 'Iscriviti all’Università Marconi con un orientatore al tuo fianco' ),
				'hero_text'    => array( 'Sottotitolo home', 'textarea', 'Ti aiutiamo a scegliere il corso, verifichiamo gratis i crediti che puoi farti riconoscere e seguiamo l’immatricolazione al posto tuo.' ),
				'price_from'   => array( 'Retta mensile a partire da (€)', 'text', '135', 'Solo il numero. Vuoto = non mostrato.' ),
				'lead_to'      => array( 'Email che ricevono le richieste', 'text', '', 'Più indirizzi separati da virgola. Vuoto = email dell’amministratore.' ),
				'thanks_page'  => array( 'Pagina di ringraziamento', 'page', '' ),
				'privacy_page' => array( 'Pagina informativa privacy', 'page', '' ),
				'webhook_url'  => array( 'Webhook (CRM, Make, Zapier)', 'url', '', 'Ogni richiesta viene inviata qui in JSON. Facoltativo.' ),
				'form_courses' => array( 'Voci del campo «Interessato a»', 'textarea', "Laurea triennale\nLaurea magistrale\nLaurea a ciclo unico\nMaster\nCorsi per insegnanti\nNon lo so ancora" ),
			),
		),
		'sedi'       => array(
			'title'  => 'Sedi d’esame',
			'fields' => array(
				'exam_sites' => array( 'Elenco sedi', 'textarea_big', ip_default_exam_sites(), 'Una riga per sede: Regione | Città | Indirizzo. Mostrato con lo shortcode [ip_sedi].' ),
			),
		),
		'tracking'   => array(
			'title'  => 'Tracciamento e cookie',
			'fields' => array(
				'gtm_id'         => array( 'Google Tag Manager ID', 'text', '', 'Es. GTM-XXXXXXX. Viene caricato con Consent Mode v2 (consensi negati di default).' ),
				'consent_banner' => array( 'Banner cookie integrato', 'checkbox', '1', 'Mostra un banner leggero (accetta / rifiuta) quando GTM è attivo. Disattivalo se usi un servizio esterno.' ),
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

function ip_default_exam_sites() {
	return "Abruzzo | L'Aquila | Via Campo di Pile, snc – 67100 Nucleo Industriale\nAbruzzo | Pescara | Via Tirino, 99 – 65129\nLazio | Roma | Via Paolo Emilio, 29 – 00192\nLazio | Latina | SS 156 Dei Monti Lepini, 2 – 04100\nLazio | Viterbo | Via G. Fontecedro, 2 – 01100\nCalabria | Reggio Calabria | Via Filippini, 14 – 89125\nCalabria | Cosenza | Corso Mazzini, 166 – 87100\nCampania | Napoli | Piazzetta Nilo, 7 – 80134\nCampania | Salerno | Via L. Guercio, 423 – 84134\nCampania | Capaccio Paestum | Via Stazione di Albanella, 12 – 84047\nEmilia-Romagna | Bologna | Via Jacopo della Quercia, 1 – 40128\nFriuli-Venezia Giulia | Udine | Via Paparotti, 13 – 33100\nLiguria | Chiavari | Piazza Mazzini, 1 – 16043\nLiguria | Santo Stefano di Magra | Viale Piero Pozzoli, snc – 19037\nLombardia | Milano | Viale Sondrio, 5 – 20124\nLombardia | Milano (Hub) | Via Meravigli, 16 – 20121\nLombardia | Varese | Via Merano, 3 – 21100\nLombardia | Brescia | Via Privata de Vitalis, 1 – 25124\nMarche | Ancona | Via Achille Barilatti, 47 – 60127\nPiemonte | Torino | Via Onorato Vigliani, 11 int. 9 – 10135\nPuglia | Bari | Via San Lorenzo, 11/C – 70124\nPuglia | Lecce | Via Cavour, 23-25-27 – 73100\nSardegna | Cagliari | Via Sernagiotto, 7 – 09030 Elmas\nSardegna | Olbia | Aeroporto Costa Smeralda – 07026\nSicilia | Palermo | Via Roma, 443 – 90193\nSicilia | Catania | Piazza Trento, 13 – 95123\nSicilia | Caltanissetta | Via Monte S. Giuliano, 4 – 93100\nToscana | Firenze | Via Faenza, 48 – 50123\nToscana | Chiusi | Via della Villetta, 3 – 53043\nTrentino-Alto Adige | Trento | Via Zambra, 11 – 38121\nUmbria | Perugia | Via Fratelli Cairoli, 24 – 06125\nUmbria | Terni | Viale D. Bramante, 4/6 – 05100\nVeneto | Verona | Via Francesco Rismondo, 10 – 37129\nVeneto | Padova | Corso Argentina, 5 – 35129\nVeneto | Venezia-Mestre | Via dei Salesiani, 15 – 30174";
}

/**
 * Legge un'impostazione. Le opzioni sono in cache statica per la richiesta.
 */
function ip_opt( $key ) {
	static $opts = null;
	if ( null === $opts ) {
		$opts = get_option( 'ip_settings', null );
		if ( ! is_array( $opts ) ) {
			$opts = array();
			foreach ( ip_settings_fields() as $section ) {
				foreach ( $section['fields'] as $k => $f ) {
					$opts[ $k ] = $f[2];
				}
			}
		}
	}
	if ( 'brand' === $key && empty( $opts['brand'] ) ) {
		return get_bloginfo( 'name' );
	}
	return isset( $opts[ $key ] ) ? $opts[ $key ] : '';
}

/**
 * Come ip_opt() ma senza ripieghi: serve alla pagina impostazioni.
 */
function ip_opt_raw( $key ) {
	$opts = get_option( 'ip_settings', null );
	if ( is_array( $opts ) ) {
		return isset( $opts[ $key ] ) ? $opts[ $key ] : '';
	}
	return 'brand' === $key ? '' : ip_opt( $key );
}

add_action( 'admin_menu', function () {
	add_menu_page( 'Infopoint', 'Infopoint', 'manage_options', 'ip-settings', 'ip_settings_page', 'dashicons-location-alt', 59 );
	add_submenu_page( 'ip-settings', 'Impostazioni', 'Impostazioni', 'manage_options', 'ip-settings', 'ip_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'ip_settings', 'ip_settings', array( 'sanitize_callback' => 'ip_sanitize_settings' ) );
} );

function ip_sanitize_settings( $in ) {
	$out = array();
	foreach ( ip_settings_fields() as $section ) {
		foreach ( $section['fields'] as $k => $f ) {
			$v = isset( $in[ $k ] ) ? wp_unslash( $in[ $k ] ) : '';
			switch ( $f[1] ) {
				case 'checkbox':
					$out[ $k ] = $v ? '1' : '';
					break;
				case 'url':
					$out[ $k ] = esc_url_raw( trim( $v ) );
					break;
				case 'page':
					$out[ $k ] = (string) absint( $v );
					break;
				case 'code':
					$out[ $k ] = current_user_can( 'unfiltered_html' ) ? $v : wp_kses_post( $v );
					break;
				case 'textarea':
				case 'textarea_big':
					$out[ $k ] = sanitize_textarea_field( $v );
					break;
				default:
					$out[ $k ] = sanitize_text_field( $v );
			}
		}
	}
	return $out;
}

function ip_settings_page() {
	$fields = ip_settings_fields();
	$tab    = isset( $_GET['tab'] ) && isset( $fields[ $_GET['tab'] ] ) ? sanitize_key( $_GET['tab'] ) : 'identita';
	?>
	<div class="wrap">
		<h1>Infopoint — impostazioni</h1>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $fields as $id => $s ) : ?>
				<a class="nav-tab <?php echo $id === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=' . $id ) ); ?>"><?php echo esc_html( $s['title'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form method="post" action="options.php">
			<?php settings_fields( 'ip_settings' ); ?>
			<?php
			// Tutti i campi viaggiano nel form: quelli delle altre schede come hidden,
			// così salvare una scheda non azzera le altre.
			foreach ( $fields as $id => $s ) {
				if ( $id === $tab ) {
					continue;
				}
				foreach ( $s['fields'] as $k => $f ) {
					printf( '<input type="hidden" name="ip_settings[%s]" value="%s">', esc_attr( $k ), esc_attr( ip_opt_raw( $k ) ) );
				}
			}
			?>
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
	$name = 'ip_settings[' . $k . ']';
	$id   = 'ip-' . $k;
	$val  = ip_opt_raw( $k );
	switch ( $f[1] ) {
		case 'checkbox':
			printf( '<input type="hidden" name="%1$s" value=""><label><input type="checkbox" id="%2$s" name="%1$s" value="1" %3$s> Attivo</label>', esc_attr( $name ), esc_attr( $id ), checked( $val, '1', false ) );
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
		case 'textarea_big':
		case 'code':
			printf( '<textarea id="%s" name="%s" rows="%d" class="large-text code">%s</textarea>', esc_attr( $id ), esc_attr( $name ), 'code' === $f[1] ? 5 : 18, esc_textarea( $val ) );
			break;
		default:
			printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">', 'url' === $f[1] ? 'url' : 'text', esc_attr( $id ), esc_attr( $name ), esc_attr( $val ) );
	}
}
