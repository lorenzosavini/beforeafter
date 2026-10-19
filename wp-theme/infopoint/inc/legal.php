<?php
/**
 * Informativa privacy e cookie policy generate dai dati della società.
 * Le pagine contengono solo [ip_privacy] e [ip_cookie]: cambiando ragione
 * sociale, indirizzi, email o strumenti attivi (GTM, webhook, WhatsApp) i
 * testi si aggiornano da soli. Chi preferisce un testo del proprio
 * consulente può sostituire il contenuto della pagina.
 */

defined( 'ABSPATH' ) || exit;

/** Titolare del trattamento: ragione sociale, sede legale, P.IVA. */
function ip_legal_owner() {
	$name = ip_opt( 'company_name' ) ? ip_opt( 'company_name' ) : ip_opt( 'brand' );
	$out  = $name;
	if ( ip_opt( 'legal_address' ) ) {
		$out .= ', con sede legale in ' . preg_replace( '/\s*\n\s*/', ', ', trim( ip_opt( 'legal_address' ) ) );
	}
	if ( ip_opt( 'vat' ) ) {
		$out .= ', P.IVA ' . ip_opt( 'vat' );
	}
	return $out;
}

/** Recapiti per esercitare i diritti, come elenco HTML. */
function ip_legal_contacts() {
	$rows = array();
	if ( ip_opt( 'email' ) ) {
		$rows[] = 'email: <a href="mailto:' . esc_attr( ip_opt( 'email' ) ) . '">' . esc_html( ip_opt( 'email' ) ) . '</a>';
	}
	if ( ip_opt( 'pec' ) ) {
		$rows[] = 'PEC: <a href="mailto:' . esc_attr( ip_opt( 'pec' ) ) . '">' . esc_html( ip_opt( 'pec' ) ) . '</a>';
	}
	if ( ip_opt( 'legal_address' ) ) {
		$rows[] = 'posta: ' . esc_html( ( ip_opt( 'company_name' ) ? ip_opt( 'company_name' ) . ', ' : '' ) . preg_replace( '/\s*\n\s*/', ', ', trim( ip_opt( 'legal_address' ) ) ) );
	}
	if ( ip_opt( 'phone1' ) ) {
		$rows[] = 'telefono: ' . esc_html( ip_opt( 'phone1' ) );
	}
	return $rows ? '<ul><li>' . implode( '</li><li>', $rows ) . '</li></ul>' : '';
}

/** «S.r.l.» a fine frase non deve lasciare due punti di fila. */
function ip_legal_tidy( $html ) {
	return preg_replace( '/(?<!\.)\.\.(?!\.)/', '.', $html );
}

function ip_legal_updated() {
	return ip_opt( 'legal_updated' ) ? '<p class="legal-upd">Ultimo aggiornamento: ' . esc_html( ip_opt( 'legal_updated' ) ) . '</p>' : '';
}

/** Pulsante che riapre il banner dei cookie (solo se il banner è attivo). */
function ip_consent_button( $class = 'btn btn-line', $label = 'Modifica le preferenze sui cookie' ) {
	if ( ! ip_gtm_id() || ! ip_opt( 'consent_banner' ) ) {
		return '';
	}
	return '<button type="button" class="' . esc_attr( $class ) . '" data-consent-open>' . esc_html( $label ) . '</button>';
}

add_shortcode( 'ip_privacy', function () {
	$brand   = ip_opt( 'brand' );
	$owner   = ip_legal_owner();
	$name    = ip_opt( 'company_name' ) ? ip_opt( 'company_name' ) : $brand;
	$cookie  = (int) ip_opt( 'cookie_page' );
	$gtm     = (bool) ip_gtm_id();
	$hook    = (bool) ip_opt( 'webhook_url' );
	$wa      = (bool) ip_opt( 'whatsapp' );
	$proc    = array_filter( array_map( 'trim', explode( "\n", (string) ip_opt( 'privacy_processors' ) ) ) );
	if ( $hook ) {
		$proc[] = 'fornitore del gestionale (CRM) in cui registriamo le richieste';
	}
	ob_start();
	?>
	<div class="prose legal">
		<?php echo ip_legal_updated(); // phpcs:ignore ?>
		<p>Questa informativa spiega, ai sensi degli articoli 13 e 14 del Regolamento (UE) 2016/679 («GDPR») e del D.Lgs. 196/2003 come modificato dal D.Lgs. 101/2018, come trattiamo i dati personali di chi visita questo sito e di chi ci contatta.</p>

		<h2>1. Titolare del trattamento</h2>
		<p>Il titolare del trattamento è <strong><?php echo esc_html( $owner ); ?></strong>, che gestisce <?php echo esc_html( $brand ); ?>, <?php echo esc_html( ip_opt( 'partner_role' ) ); ?><?php echo ip_opt( 'address' ) ? ', con sede operativa in ' . esc_html( preg_replace( '/\s*\n\s*/', ', ', trim( ip_opt( 'address' ) ) ) ) : ''; ?>.</p>
		<p>Puoi contattarci per qualsiasi domanda sui tuoi dati:</p>
		<?php echo ip_legal_contacts(); // phpcs:ignore ?>
		<?php if ( ip_opt( 'dpo' ) ) : ?>
			<p>Responsabile della protezione dei dati (DPO): <?php echo esc_html( ip_opt( 'dpo' ) ); ?>.</p>
		<?php endif; ?>
		<p><strong>Non siamo l’Università.</strong> L’Università degli Studi Guglielmo Marconi tratta i dati di chi si immatricola come titolare autonomo, secondo la propria informativa pubblicata su <a href="https://www.unimarconi.it" target="_blank" rel="noopener">www.unimarconi.it</a>. Questa informativa riguarda solo i trattamenti svolti da <?php echo esc_html( $name ); ?>.</p>

		<h2>2. Quali dati trattiamo</h2>
		<ul>
			<li><strong>Dati che ci fornisci con i moduli</strong>: nome e cognome, telefono, email, corso o area di interesse, messaggio e, a seconda del modulo, anno di nascita, regione, fascia oraria preferita per essere richiamato.</li>
			<li><strong>Documenti per la prevalutazione dei CFU</strong>: piano di studi, autocertificazione degli esami o altri documenti che scegli di allegare. Possono contenere dati sulla tua carriera universitaria: ti chiediamo di non inserire informazioni che non servono alla valutazione.</li>
			<li><strong>Dati che ci comunichi per telefono, email<?php echo $wa ? ' o WhatsApp' : ''; ?></strong>, quando ci scrivi o ci chiami.</li>
			<li><strong>Dati tecnici di navigazione</strong>: indirizzo IP, tipo di browser, pagine visitate, data e ora della richiesta, raccolti dai sistemi che fanno funzionare il sito e lo proteggono da abusi. Con il modulo registriamo anche la pagina da cui ci scrivi e, se presente, la campagna pubblicitaria di provenienza.</li>
			<?php if ( $gtm ) : ?>
				<li><strong>Dati di statistica e pubblicità</strong>, solo se acconsenti ai relativi cookie (vedi punto 9).</li>
			<?php endif; ?>
		</ul>

		<h2>3. Perché li trattiamo e su quale base giuridica</h2>
		<table class="legal-table">
			<thead><tr><th>Finalità</th><th>Base giuridica</th></tr></thead>
			<tbody>
				<tr><td>Rispondere alla tua richiesta: orientamento sulla scelta del corso, informazioni su costi e agevolazioni, prevalutazione dei CFU, assistenza nella procedura di iscrizione, richiamata telefonica.</td><td>Esecuzione di misure precontrattuali adottate su tua richiesta (art. 6.1.b GDPR).</td></tr>
				<tr><td>Inviarti aggiornamenti su corsi, agevolazioni e scadenze per email, telefono<?php echo $wa ? ' o WhatsApp' : ''; ?>, solo se hai spuntato la casella facoltativa.</td><td>Consenso (art. 6.1.a GDPR), revocabile in qualsiasi momento.</td></tr>
				<tr><td>Proteggere il sito e i moduli da spam e abusi, conservare la prova dei consensi ricevuti, difendere i nostri diritti.</td><td>Legittimo interesse del titolare (art. 6.1.f GDPR).</td></tr>
				<tr><td>Adempiere a obblighi di legge, ad esempio fiscali o richieste delle autorità.</td><td>Obbligo legale (art. 6.1.c GDPR).</td></tr>
				<?php if ( $gtm ) : ?>
					<tr><td>Misurare le visite e l’efficacia delle campagne pubblicitarie.</td><td>Consenso espresso tramite il banner dei cookie (art. 6.1.a GDPR e art. 122 del Codice privacy).</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
		<p>Non prendiamo decisioni basate unicamente su trattamenti automatizzati, compresa la profilazione, che producano effetti giuridici su di te.</p>

		<h2>4. Cosa succede se non fornisci i dati</h2>
		<p>Nome, telefono ed email (dove richiesti) servono per risponderti: senza, non possiamo dare seguito alla richiesta. Gli altri campi sono facoltativi. Il consenso alle comunicazioni promozionali è facoltativo: se non lo dai, ti rispondiamo comunque.</p>

		<h2>5. Come trattiamo i dati</h2>
		<p>I dati sono trattati con strumenti elettronici, da personale autorizzato e istruito, con misure di sicurezza adeguate: connessione cifrata, accesso al pannello protetto da password, documenti allegati salvati in una cartella non accessibile dal web.</p>

		<h2>6. A chi possono essere comunicati</h2>
		<ul>
			<li>Al nostro personale e ai collaboratori autorizzati che si occupano di orientamento e assistenza.</li>
			<?php if ( $proc ) : ?>
				<li>A fornitori che ci prestano servizi, nominati responsabili del trattamento (art. 28 GDPR): <?php echo esc_html( implode( '; ', $proc ) ); ?>.</li>
			<?php endif; ?>
			<?php if ( $gtm ) : ?>
				<li>A Google Ireland Ltd, per i servizi di statistica e pubblicità, solo se acconsenti ai cookie.</li>
			<?php endif; ?>
			<li>All’Università degli Studi Guglielmo Marconi, solo quando ci chiedi di avviare per tuo conto una pratica (ad esempio la prevalutazione dei CFU o l’iscrizione) e solo i dati necessari a quella pratica.</li>
			<li>Ad autorità e soggetti pubblici, quando lo prevede la legge.</li>
		</ul>
		<p>Non vendiamo i tuoi dati e non li cediamo a terzi per finalità pubblicitarie proprie.</p>

		<h2>7. Trasferimenti fuori dall’Unione europea</h2>
		<p>I dati sono trattati di norma nell’Unione europea. Se un fornitore li tratta fuori dall’UE (ad esempio negli Stati Uniti), il trasferimento avviene solo verso paesi con una decisione di adeguatezza della Commissione europea, come l’EU-U.S. Data Privacy Framework, o con le clausole contrattuali standard approvate dalla Commissione.</p>

		<h2>8. Per quanto tempo li conserviamo</h2>
		<ul>
			<li>Richieste di informazioni e relativi contatti: <?php echo esc_html( ip_opt( 'privacy_retention' ) ); ?>.</li>
			<li>Documenti allegati per la prevalutazione dei CFU: <?php echo esc_html( ip_opt( 'files_retention' ) ); ?>.</li>
			<li>Dati usati per le comunicazioni promozionali: <?php echo esc_html( ip_opt( 'mkt_retention' ) ); ?>.</li>
			<li>Prova dei consensi e dati necessari a difendere i nostri diritti: per il tempo di prescrizione previsto dalla legge.</li>
		</ul>
		<p>Scaduti questi termini i dati vengono cancellati o resi anonimi.</p>

		<h2>9. Cookie</h2>
		<p>Il sito usa strumenti tecnici necessari al suo funzionamento<?php echo $gtm ? ' e, solo con il tuo consenso, cookie di statistica e pubblicità' : ''; ?>. Trovi tutti i dettagli nella <?php echo $cookie ? '<a href="' . esc_url( get_permalink( $cookie ) ) . '">cookie policy</a>' : 'cookie policy'; ?>.</p>

		<h2>10. I tuoi diritti</h2>
		<p>In ogni momento puoi chiedere di:</p>
		<ul>
			<li>accedere ai tuoi dati e riceverne copia (art. 15);</li>
			<li>correggerli o completarli (art. 16);</li>
			<li>cancellarli (art. 17) o limitarne il trattamento (art. 18);</li>
			<li>riceverli in un formato strutturato e trasmetterli a un altro titolare (art. 20);</li>
			<li>opporti al trattamento basato sul legittimo interesse e, sempre e senza motivazione, a quello per finalità promozionali (art. 21);</li>
			<li>revocare il consenso dato, senza effetti sulla liceità del trattamento svolto prima della revoca (art. 7.3).</li>
		</ul>
		<p>Per esercitarli scrivi ai recapiti del punto 1: ti rispondiamo entro un mese. Per smettere di ricevere comunicazioni promozionali basta chiedercelo, anche rispondendo a un nostro messaggio.</p>
		<p>Se ritieni che il trattamento violi la normativa puoi proporre reclamo al Garante per la protezione dei dati personali (<a href="https://www.garanteprivacy.it" target="_blank" rel="noopener">www.garanteprivacy.it</a>) o all’autorità di controllo del paese in cui vivi o lavori.</p>

		<h2>11. Minori</h2>
		<p>I nostri servizi sono rivolti a chi vuole iscriversi all’università. Se hai meno di 14 anni, la richiesta deve essere inviata da chi esercita la responsabilità genitoriale.</p>

		<?php if ( ip_opt( 'privacy_extra' ) ) : ?>
			<?php echo wp_kses_post( wpautop( ip_text( 'privacy_extra' ) ) ); ?>
		<?php endif; ?>

		<h2>12. Modifiche</h2>
		<p>Possiamo aggiornare questa informativa, ad esempio per cambiamenti normativi o dei servizi offerti. La versione in vigore è sempre quella pubblicata in questa pagina, con la data dell’ultimo aggiornamento.</p>
	</div>
	<?php
	return ip_legal_tidy( ob_get_clean() );
} );

add_shortcode( 'ip_cookie', function () {
	$name    = ip_opt( 'company_name' ) ? ip_opt( 'company_name' ) : ip_opt( 'brand' );
	$gtm     = (bool) ip_gtm_id();
	$privacy = ip_privacy_url();
	ob_start();
	?>
	<div class="prose legal">
		<?php echo ip_legal_updated(); // phpcs:ignore ?>
		<p>Questa pagina spiega quali cookie e strumenti simili usa il sito, a cosa servono e come puoi scegliere, secondo l’art. 122 del Codice privacy e le Linee guida del Garante per la protezione dei dati personali del 10 giugno 2021. Il titolare è <strong><?php echo esc_html( ip_legal_owner() ); ?></strong><?php echo $privacy ? '; per il resto del trattamento dei dati vedi l’<a href="' . esc_url( $privacy ) . '">informativa privacy</a>' : ''; ?>.</p>

		<h2>Cosa sono</h2>
		<p>I cookie sono piccoli file che un sito salva nel browser per ricordare informazioni tra una pagina e l’altra o tra una visita e l’altra. Strumenti simili sono la memoria locale del browser (localStorage e sessionStorage) e i pixel. Qui li chiamiamo tutti «cookie».</p>

		<h2>Cookie tecnici: sempre attivi</h2>
		<p>Servono a far funzionare il sito e a ricordare le tue scelte. Non richiedono consenso e non ci dicono chi sei.</p>
		<table class="legal-table">
			<thead><tr><th>Nome</th><th>A cosa serve</th><th>Durata</th></tr></thead>
			<tbody>
				<?php if ( $gtm && ip_opt( 'consent_banner' ) ) : ?>
					<tr><td><code>ip_consent</code> (localStorage)</td><td>Ricorda se hai accettato o rifiutato i cookie di statistica e pubblicità, per non chiedertelo a ogni pagina.</td><td>Finché non lo cancelli dal browser o non cambi scelta</td></tr>
				<?php endif; ?>
				<tr><td><code>ip_lead_sent</code> (localStorage)</td><td>Ricorda che hai già inviato una richiesta, per non mostrarti di nuovo l’invito a contattarci.</td><td>Finché non lo cancelli dal browser</td></tr>
				<tr><td><code>ip_exit</code> (sessionStorage)</td><td>Evita di mostrarti più di una volta l’invito a contattarci quando stai per lasciare la pagina.</td><td>Fino alla chiusura della scheda</td></tr>
				<tr><td><code>wordpress_*</code>, <code>wp-settings-*</code></td><td>Solo per chi accede all’area riservata del sito (personale di <?php echo esc_html( $name ); ?>): mantengono l’accesso e le preferenze del pannello.</td><td>Sessione o fino a 1 anno</td></tr>
			</tbody>
		</table>

		<?php if ( $gtm ) : ?>
			<h2>Cookie di statistica e pubblicità: solo con il tuo consenso</h2>
			<p>Li usiamo, tramite Google Tag Manager, per contare le visite in forma aggregata e per sapere quali campagne pubblicitarie portano richieste. Finché non scegli, o se rifiuti, restano disattivati: Google riceve al massimo segnali senza cookie, che non permettono di riconoscerti (modalità di consenso di Google).</p>
			<table class="legal-table">
				<thead><tr><th>Servizio</th><th>Cookie</th><th>A cosa serve</th><th>Durata</th></tr></thead>
				<tbody>
					<tr><td>Google Analytics 4 (Google Ireland Ltd)</td><td><code>_ga</code>, <code>_ga_*</code></td><td>Statistiche sulle visite: pagine viste, durata, provenienza</td><td>Fino a 2 anni</td></tr>
					<tr><td>Google Ads (Google Ireland Ltd)</td><td><code>_gcl_au</code>, <code>_gcl_aw</code></td><td>Misurare le richieste arrivate dagli annunci</td><td>90 giorni</td></tr>
					<tr><td>Google Ads remarketing (Google Ireland Ltd)</td><td><code>IDE</code>, <code>test_cookie</code> (dominio doubleclick.net)</td><td>Mostrare annunci pertinenti a chi ha già visitato il sito</td><td>Fino a 13 mesi</td></tr>
				</tbody>
			</table>
			<?php if ( ip_opt( 'cookie_extra' ) ) : ?>
				<?php echo wp_kses_post( wpautop( ip_text( 'cookie_extra' ) ) ); ?>
			<?php endif; ?>
			<p>Informativa di Google: <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">policies.google.com/privacy</a>. Come Google usa i dati dei siti partner: <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener">policies.google.com/technologies/partner-sites</a>. I dati possono essere trasferiti negli Stati Uniti, sulla base dell’EU-U.S. Data Privacy Framework.</p>
		<?php else : ?>
			<h2>Cookie di statistica e pubblicità</h2>
			<p>Al momento il sito non usa cookie di statistica, di profilazione o pubblicitari. Se li attiveremo, ti chiederemo prima il consenso con un banner e aggiorneremo questa pagina.</p>
		<?php endif; ?>

		<h2>Link a siti esterni</h2>
		<p>I collegamenti a WhatsApp, Google Maps, unimarconi.it e ai social network portano a siti di terzi, che usano i propri cookie secondo le proprie informative. Nessuno di questi servizi viene caricato finché non fai clic sul collegamento.</p>

		<h2>Come scegliere o cambiare idea</h2>
		<?php if ( $gtm && ip_opt( 'consent_banner' ) ) : ?>
			<p>Alla prima visita un banner ti chiede se accettare i cookie di statistica e pubblicità: «Rifiuta» e «Accetta» hanno lo stesso peso e, se rifiuti, il sito funziona allo stesso modo. Puoi cambiare scelta in qualsiasi momento:</p>
			<p><?php echo ip_consent_button(); // phpcs:ignore ?></p>
		<?php endif; ?>
		<p>Puoi anche cancellare o bloccare i cookie dalle impostazioni del browser: <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Chrome</a>, <a href="https://support.mozilla.org/it/kb/protezione-antitracciamento-avanzata-firefox-desktop" target="_blank" rel="noopener">Firefox</a>, <a href="https://support.apple.com/it-it/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Safari</a>, <a href="https://support.microsoft.com/it-it/microsoft-edge/eliminare-i-cookie-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener">Edge</a>. Bloccando i cookie tecnici alcune funzioni potrebbero non funzionare.</p>

		<h2>Contatti</h2>
		<p>Per domande sui cookie o per esercitare i tuoi diritti:</p>
		<?php echo ip_legal_contacts(); // phpcs:ignore ?>
	</div>
	<?php
	return ip_legal_tidy( ob_get_clean() );
} );

/**
 * Crea (o ripara) le pagine «Informativa privacy» e «Cookie policy» e le
 * collega alle impostazioni. Una pagina privacy già scritta a mano resta
 * com'è; si sostituisce solo la bozza predefinita di WordPress.
 */
function ip_legal_pages() {
	$o = get_option( 'ip_settings', array() );
	$o = is_array( $o ) ? $o : array();
	$pages = array(
		'privacy_page' => array( 'privacy-policy', 'Informativa privacy', '[ip_privacy]', 'Chi tratta i tuoi dati quando visiti il sito o ci contatti, perché, per quanto tempo e come esercitare i tuoi diritti.' ),
		'cookie_page'  => array( 'cookie-policy', 'Cookie policy', '[ip_cookie]', 'Quali cookie usa il sito, a cosa servono e come accettarli, rifiutarli o cambiare scelta.' ),
	);
	foreach ( $pages as $key => $p ) {
		$id = ! empty( $o[ $key ] ) ? (int) $o[ $key ] : 0;
		if ( ! $id && 'privacy_page' === $key ) {
			$id = (int) get_option( 'wp_page_for_privacy_policy' );
		}
		$post = $id ? get_post( $id ) : null;
		if ( ! $post || 'trash' === $post->post_status ) {
			$post = get_page_by_path( $p[0] );
		}
		$content = "<!-- wp:shortcode -->\n" . $p[2] . "\n<!-- /wp:shortcode -->";
		if ( $post ) {
			$wp_default = false !== strpos( $post->post_content, 'wp:heading' ) && ( false !== strpos( $post->post_content, 'Who we are' ) || false !== strpos( $post->post_content, 'Chi siamo' ) || false !== strpos( $post->post_content, 'Suggested text' ) || false !== strpos( $post->post_content, 'Testo suggerito' ) );
			$update     = array( 'ID' => $post->ID );
			if ( '' === trim( $post->post_content ) || $wp_default ) {
				$update['post_content'] = $content;
				$update['post_title']   = $p[1];
				$update['post_excerpt'] = $p[3];
			}
			if ( 'publish' !== $post->post_status && ( isset( $update['post_content'] ) || false !== strpos( $post->post_content, $p[2] ) ) ) {
				$update['post_status'] = 'publish';
			}
			if ( count( $update ) > 1 ) {
				wp_update_post( wp_slash( $update ) );
			}
			$id = $post->ID;
		} else {
			$id = wp_insert_post( wp_slash( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $p[1],
				'post_name'    => $p[0],
				'post_excerpt' => $p[3],
				'post_content' => $content,
			) ) );
		}
		if ( $id && ! is_wp_error( $id ) ) {
			$o[ $key ] = (string) $id;
			update_post_meta( $id, '_ip_seo_noindex', '1' );
			if ( 'privacy_page' === $key ) {
				update_option( 'wp_page_for_privacy_policy', $id );
			}
		}
	}
	update_option( 'ip_settings', $o );
}
