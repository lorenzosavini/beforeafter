<?php
/**
 * Pannello Infopoint: bacheca, impostazioni raggruppate, menu ordinato,
 * gestione rapida delle richieste e stile comune a tutte le schermate del tema.
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * 1. Impostazioni: gruppi e sezioni
 * ------------------------------------------------------------------- */

/**
 * Struttura delle impostazioni come la vede chi le usa.
 * Ogni sezione elenca le chiavi di ip_settings_fields(); 'if' mostra un campo
 * solo quando un altro è attivo o compilato.
 */
function ip_settings_layout() {
	return array(
		'azienda'  => array(
			'title' => 'Azienda e contatti',
			'icon'  => 'building',
			'desc'  => 'Nome, telefoni, sede, orari e dati legali: compaiono in tutto il sito, nei moduli e nelle email.',
			'sections' => array(
				array( 'Nome e presentazione', 'Come vi chiamate e come vi presentate.', array( 'brand', 'city', 'disclaimer' ) ),
				array( 'Telefoni e WhatsApp', 'Diventano i pulsanti «Chiama» e «WhatsApp» del sito.', array( 'phone1', 'phone1_label', 'phone2', 'phone2_label', 'whatsapp', 'wa_text' ) ),
				array( 'Email, sede e orari', '', array( 'email', 'address', 'address_exam', 'hours', 'maps_url' ) ),
				array( 'Social', 'Vuoto = icona nascosta.', array( 'facebook', 'instagram', 'linkedin', 'youtube', 'tiktok' ) ),
				array( 'Dati legali e trasparenza', 'Richiesti da Google Ads e dalla legge: ragione sociale, P.IVA, PEC, rapporto con l’Ateneo.', array( 'company_name', 'vat', 'legal_address', 'rea', 'pec', 'capital', 'partner_role', 'partner_since', 'partner_letter', 'partner_proof', 'disclosure_bar', 'disclosure', 'disclosure_mobile', 'disclosure_link', 'disclosure_link_text', 'form_notice' ) ),
			),
		),
		'home'     => array(
			'title' => 'Home page',
			'icon'  => 'admin-home',
			'desc'  => 'Testi, immagini e sezioni della pagina principale.',
			'sections' => array(
				array( 'Apertura', 'La prima cosa che si vede: titolo, testo, foto. Le parole tra *asterischi* vengono evidenziate.', array( 'hero_eyebrow', 'hero_title', 'hero_text', 'hero_image', 'hero_ticks' ) ),
				array( 'Sezioni e ordine', 'Attiva le sezioni e spostale con le frecce.', array( 'sections' ) ),
				array( 'Numeri', '', array( 'facts' ) ),
				array( 'Titoli delle sezioni', '', array( 't_offerta', 't_featured', 't_agev', 'agev_text', 'agev_count', 't_passi', 't_reviews', 't_faq', 't_news' ) ),
				array( 'Riquadro riconoscimento CFU', '', array( 'cfu_title', 'cfu_text', 'cfu_button', 'cfu_image' ) ),
				array( 'Recensioni', 'Solo recensioni vere, con il consenso di chi le ha scritte.', array( 'reviews' ) ),
				array( 'Domande frequenti', '', array( 'faq' ) ),
			),
		),
		'moduli'   => array(
			'title' => 'Moduli e testi',
			'icon'  => 'feedback',
			'desc'  => 'Testi dei moduli, consensi, banner d’uscita e frasi che si ripetono nelle pagine.',
			'sections' => array(
				array( 'Banner informativo in alto', 'La barra sottile sopra la testata, da accendere quando serve: iscrizioni aperte, scadenze, open day, chiusure.', array( 'notice_on', 'notice_text', 'notice_text_mobile', 'notice_link_text', 'notice_link_text_mobile', 'notice_link', 'notice_devices', 'notice_style', 'notice_where', 'notice_landing', 'notice_from', 'notice_to', 'notice_close' ) ),
				array( 'Modulo di richiesta', '', array( 'form_title', 'form_text', 'form_button', 'thanks_page' ) ),
				array( 'Consensi', 'Le parole «informativa privacy» diventano il link alla pagina scelta.', array( 'privacy_text', 'privacy_page', 'mkt_text' ) ),
				array( 'Banner d’uscita', 'Compare a chi sta per lasciare il sito. Per provarlo aggiungi <code>?prova-banner</code> all’indirizzo di una pagina.', array( 'exit_on', 'exit_mobile', 'exit_title', 'exit_text', 'exit_points' ) ),
				array( 'Testi ricorrenti', 'Fascia di contatto, modulo in fondo alle pagine, passi «come funziona».', array( 'nav_cta', 'cta_title', 'cta_text', 'end_title', 'end_text', 'steps', 'course_empty' ) ),
				array( 'Prezzi', 'Vuoti = calcolati in automatico dalle agevolazioni.', array( 'price_from', 'retta_std' ) ),
				array( 'Collegamento al CRM', '', array( 'webhook_url' ) ),
			),
			'if'   => array(
				'notice_text'      => 'notice_on',
				'notice_text_mobile' => 'notice_on',
				'notice_link_text_mobile' => 'notice_on',
				'notice_devices'   => 'notice_on',
				'notice_link_text' => 'notice_on',
				'notice_link'      => 'notice_on',
				'notice_style'     => 'notice_on',
				'notice_where'     => 'notice_on',
				'notice_landing'   => 'notice_on',
				'notice_from'      => 'notice_on',
				'notice_to'        => 'notice_on',
				'notice_close'     => 'notice_on',
			),
		),
		'email'    => array(
			'title' => 'Email e notifiche',
			'icon'  => 'email-alt',
			'desc'  => 'Chi riceve le richieste, la conferma automatica a chi scrive e il server di posta.',
			'sections' => array(
				array( 'Notifiche delle richieste', '', array( 'mail_note', 'lead_to', 'notify_admin' ) ),
				array( 'Conferma a chi scrive', '', array( 'autoreply_on', 'autoreply_subject', 'autoreply_text' ) ),
				array( 'Mittente', '', array( 'mail_from', 'mail_from_name' ) ),
				array( 'Server di posta (SMTP)', 'Senza SMTP molti hosting non consegnano le email. I dati sono nel pannello della vostra casella.', array( 'smtp_on', 'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_user', 'smtp_pass' ) ),
			),
			'if'   => array(
				'autoreply_subject' => 'autoreply_on',
				'autoreply_text'    => 'autoreply_on',
				'smtp_host'         => 'smtp_on',
				'smtp_port'         => 'smtp_on',
				'smtp_secure'       => 'smtp_on',
				'smtp_user'         => 'smtp_on',
				'smtp_pass'         => 'smtp_on',
			),
		),
		'tracking' => array(
			'title' => 'Tracciamento e cookie',
			'icon'  => 'chart-area',
			'desc'  => 'Tag Manager, Analytics, Google Ads e banner dei cookie.',
			'sections' => array(
				array( 'Tag di Google', '', array( 'tag_note', 'gtm_id', 'gtm_delay', 'ga4_id' ) ),
				array( 'Google Ads', 'Le conversioni scattano da sole a ogni richiesta e, se vuoi, a ogni clic su telefono o WhatsApp.', array( 'ads_id', 'ads_lead_label', 'ads_call_label', 'ads_value', 'ads_enhanced' ) ),
				array( 'Banner dei cookie', '', array( 'consent_banner', 'consent_always', 'consent_text', 'consent_months', 'consent_log', 'cookie_page' ) ),
				array( 'Codice personalizzato', 'Solo per script che non passano da Tag Manager.', array( 'head_code', 'footer_code' ) ),
			),
			'if'   => array(
				'ads_lead_label' => 'ads_id',
				'ads_call_label' => 'ads_id',
				'ads_value'      => 'ads_id',
				'ads_enhanced'   => 'ads_id',
				'consent_always' => 'consent_banner',
				'consent_text'   => 'consent_banner',
				'consent_months' => 'consent_banner',
				'consent_log'    => 'consent_banner',
			),
		),
		'privacy'  => array(
			'title' => 'Privacy',
			'icon'  => 'shield',
			'desc'  => 'I dati che completano informativa privacy e cookie policy, generate dal tema.',
			'sections' => array(
				array( 'Informativa e cookie policy', '', array( 'legal_updated', 'privacy_retention', 'files_retention', 'mkt_retention', 'privacy_processors', 'dpo', 'privacy_extra', 'cookie_extra' ) ),
			),
		),
		'landing'  => array(
			'title' => 'Landing e sedi',
			'icon'  => 'megaphone',
			'desc'  => 'La squadra mostrata nelle landing e le sedi d’esame.',
			'sections' => array(
				array( 'La squadra nelle landing', '', array( 'lp_team_title', 'lp_team_text', 'lp_team_photo', 'lp_team' ) ),
				array( 'Sedi d’esame', '', array( 'exam_sites' ) ),
			),
		),
		'aspetto'  => array(
			'title' => 'Colori e font',
			'icon'  => 'art',
			'desc'  => 'Palette e carattere del sito.',
			'sections' => array(
				array( 'Palette', '', array( 'palette_note', 'color_brand', 'color_brand_dark', 'color_brand_deep', 'color_accent', 'color_accent_dark', 'color_soft', 'color_dark' ) ),
				array( 'Carattere', '', array( 'font_brand' ) ),
			),
		),
		'sync'     => array(
			'title' => 'Aggiornamenti corsi',
			'icon'  => 'update',
			'desc'  => 'Come e quando il sito si aggiorna dall’offerta ufficiale, e cosa non riportare mai.',
			'sections' => array(
				array( 'Controllo automatico', '', array( 'sync_enabled', 'sync_freq', 'sync_mode', 'sync_scope', 'sync_new', 'sync_retire', 'sync_email' ) ),
				array( 'Esclusioni', 'Frasi e indirizzi che non devono mai comparire sul sito.', array( 'sync_drop', 'sync_blocked' ) ),
			),
		),
		'avanzate' => array(
			'title' => 'Avanzate',
			'icon'  => 'admin-tools',
			'desc'  => 'Velocità e funzioni di WordPress da spegnere.',
			'sections' => array(
				array( 'Prestazioni', '', array( 'inline_css', 'browser_cache', 'disable_comments', 'disable_seo' ) ),
			),
		),
	);
}

/** Vecchie schede → nuovi gruppi (link già salvati, segnalibri). */
function ip_settings_group_alias( $tab ) {
	$map = array(
		'identita'    => 'azienda',
		'trasparenza' => 'azienda',
		'contatti'    => 'azienda',
		'testi'       => 'moduli',
		'conversione' => 'moduli',
		'sedi'        => 'landing',
		'prestazioni' => 'avanzate',
	);
	return isset( $map[ $tab ] ) ? $map[ $tab ] : $tab;
}

/** Tutti i campi definiti, per chiave. */
function ip_settings_flat() {
	static $flat = null;
	if ( null === $flat ) {
		$flat = array();
		foreach ( ip_settings_fields() as $tab ) {
			foreach ( $tab['fields'] as $k => $f ) {
				$flat[ $k ] = $f;
			}
		}
	}
	return $flat;
}

/** Sezioni di un gruppo; i campi non assegnati finiscono in «Avanzate → Altro». */
function ip_settings_group_sections( $group ) {
	$layout = ip_settings_layout();
	if ( ! isset( $layout[ $group ] ) ) {
		return array();
	}
	$sections = $layout[ $group ]['sections'];
	if ( 'avanzate' === $group ) {
		$used = array();
		foreach ( $layout as $g ) {
			foreach ( $g['sections'] as $sec ) {
				$used = array_merge( $used, $sec[2] );
			}
		}
		$rest = array_diff( array_keys( ip_settings_flat() ), $used );
		if ( $rest ) {
			$sections[] = array( 'Altro', '', array_values( $rest ) );
		}
	}
	return $sections;
}

function ip_settings_group_fields( $group ) {
	$flat = ip_settings_flat();
	$out  = array();
	foreach ( ip_settings_group_sections( $group ) as $sec ) {
		foreach ( $sec[2] as $k ) {
			if ( isset( $flat[ $k ] ) ) {
				$out[ $k ] = $flat[ $k ];
			}
		}
	}
	return $out;
}

/** Stato di un gruppo, per il pallino nel menu laterale. */
function ip_settings_group_status( $group ) {
	switch ( $group ) {
		case 'azienda':
			return ip_opt( 'phone1' ) && ip_opt( 'company_name' ) && ip_opt( 'vat' ) ? 'ok' : 'warn';
		case 'email':
			if ( get_option( 'ip_mail_error' ) ) {
				return 'error';
			}
			return ip_opt( 'smtp_on' ) && ip_opt( 'smtp_host' ) ? 'ok' : 'warn';
		case 'tracking':
			return ip_tracking_on() ? 'ok' : 'idle';
	}
	return '';
}

/* ---------------------------------------------------------------------
 * 2. Menu
 * ------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_menu_page( 'Infopoint', 'Infopoint', 'edit_posts', 'ip-settings', 'ip_admin_router', 'dashicons-location-alt', '3.5' );
	add_submenu_page( 'ip-settings', 'Bacheca Infopoint', 'Bacheca', 'edit_posts', 'ip-settings', 'ip_admin_router' );
	add_submenu_page( 'ip-settings', 'Impostazioni', 'Impostazioni', 'manage_options', 'admin.php?page=ip-settings&tab=azienda' );
}, 9 );

/** Bacheca senza «tab», impostazioni con «tab». */
function ip_admin_router() {
	if ( isset( $_GET['tab'] ) && current_user_can( 'manage_options' ) ) {
		ip_settings_page();
	} else {
		ip_dashboard_page();
	}
}

// Voce «Impostazioni» evidenziata quando si è nelle impostazioni.
add_filter( 'submenu_file', function ( $file, $parent ) {
	if ( 'ip-settings' === $parent && isset( $_GET['page'], $_GET['tab'] ) && 'ip-settings' === $_GET['page'] ) {
		return 'admin.php?page=ip-settings&tab=azienda';
	}
	return $file;
}, 10, 2 );

// Ordine del menu: prima ciò che si usa ogni giorno.
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function ( $order ) {
	$first = array( 'index.php', 'ip-leads', 'ip-settings', 'separator1', 'edit.php?post_type=corso', 'edit.php?post_type=ip_landing', 'edit.php?post_type=page', 'upload.php' );
	$head  = array_values( array_intersect( $first, $order ) );
	return array_merge( $head, array_values( array_diff( $order, $head ) ) );
} );

// «Articoli» nascosto finché il blog non ha contenuti.
add_action( 'admin_menu', function () {
	$n = wp_count_posts( 'post' );
	if ( ! $n || ( (int) $n->publish + (int) $n->draft + (int) $n->future ) === 0 ) {
		remove_menu_page( 'edit.php' );
	}
}, 99 );

/* ---------------------------------------------------------------------
 * 3. Stile comune e intestazione
 * ------------------------------------------------------------------- */

function ip_is_theme_screen() {
	$s = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $s ) {
		return false;
	}
	return false !== strpos( $s->id, 'ip-' ) || in_array( $s->post_type, array( 'ip_lead', 'corso', 'curriculum', 'agevolazione', 'ip_landing', 'ip_update' ), true ) || in_array( $s->taxonomy, array( 'tipologia', 'dipartimento', 'area' ), true );
}

add_filter( 'admin_body_class', function ( $c ) {
	return ip_is_theme_screen() ? $c . ' ip-ui' : $c;
} );

add_action( 'admin_head', function () {
	$brand = ip_opt( 'color_brand' ) ? ip_opt( 'color_brand' ) : '#225e48';
	$deep  = ip_opt( 'color_brand_deep' ) ? ip_opt( 'color_brand_deep' ) : '#0e261d';
	$acc   = ip_opt( 'color_accent' ) ? ip_opt( 'color_accent' ) : '#a0300e';
	printf( '<style>:root{--ipa-brand:%s;--ipa-deep:%s;--ipa-accent:%s}</style>', esc_html( $brand ), esc_html( $deep ), esc_html( $acc ) );
} );

/** Intestazione delle pagine Infopoint. */
function ip_admin_header( $title, $lead = '', $actions = array() ) {
	?>
	<header class="ipa-head">
		<div class="ipa-head-t">
			<p class="ipa-kicker"><span class="dashicons dashicons-location-alt"></span> <?php echo esc_html( ip_opt( 'brand' ) ); ?></p>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $lead ) : ?><p class="ipa-lead"><?php echo wp_kses_post( $lead ); ?></p><?php endif; ?>
		</div>
		<?php if ( $actions ) : ?>
			<div class="ipa-head-a">
				<?php foreach ( $actions as $a ) : ?>
					<a class="ipa-btn <?php echo esc_attr( isset( $a[2] ) ? $a[2] : '' ); ?>" href="<?php echo esc_url( $a[1] ); ?>"<?php echo ! empty( $a[3] ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $a[0] ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</header>
	<?php
}

/* ---------------------------------------------------------------------
 * 4. Pagina impostazioni
 * ------------------------------------------------------------------- */

function ip_settings_page() {
	$layout = ip_settings_layout();
	$group  = ip_settings_group_alias( sanitize_key( wp_unslash( $_GET['tab'] ?? 'azienda' ) ) );
	if ( ! isset( $layout[ $group ] ) ) {
		$group = 'azienda';
	}
	$g    = $layout[ $group ];
	$flat = ip_settings_flat();
	$if   = isset( $g['if'] ) ? $g['if'] : array();
	?>
	<div class="wrap ip-admin ipa">
		<?php ip_admin_header( 'Impostazioni', 'Tutto quello che compare sul sito, raggruppato per argomento. Le modifiche valgono appena salvi.', array( array( 'Vedi il sito', home_url( '/' ), 'ipa-btn-line', true ) ) ); ?>
		<?php settings_errors(); ?>
		<div class="ipa-set">
			<nav class="ipa-side" aria-label="Sezioni delle impostazioni">
				<?php foreach ( $layout as $id => $s ) : ?>
					<?php $st = ip_settings_group_status( $id ); ?>
					<a class="ipa-side-a<?php echo $id === $group ? ' is-on' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=' . $id ) ); ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( $s['icon'] ); ?>"></span>
						<span class="ipa-side-t"><?php echo esc_html( $s['title'] ); ?></span>
						<?php if ( $st && 'ok' !== $st ) : ?><span class="ipa-dot ipa-dot-<?php echo esc_attr( $st ); ?>" title="<?php echo 'error' === $st ? 'Da sistemare' : ( 'warn' === $st ? 'Da completare' : 'Non attivo' ); ?>"></span><?php endif; ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<div class="ipa-main">
				<div class="ipa-group-h">
					<h2><span class="dashicons dashicons-<?php echo esc_attr( $g['icon'] ); ?>"></span> <?php echo esc_html( $g['title'] ); ?></h2>
					<p><?php echo esc_html( $g['desc'] ); ?></p>
				</div>
				<form method="post" action="options.php" class="ipa-form">
					<?php settings_fields( 'ip_settings' ); ?>
					<input type="hidden" name="ip_settings[_group]" value="<?php echo esc_attr( $group ); ?>">
					<?php $secs = ip_settings_group_sections( $group ); ?>
					<?php if ( count( $secs ) > 2 ) : ?>
						<p class="ipa-toc">
							<?php foreach ( $secs as $i => $sec ) : ?>
								<a href="#ipa-sec-<?php echo (int) $i; ?>"><?php echo esc_html( $sec[0] ); ?></a>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
					<?php foreach ( $secs as $i => $sec ) : ?>
						<section class="ipa-card" id="ipa-sec-<?php echo (int) $i; ?>">
							<h3><?php echo esc_html( $sec[0] ); ?></h3>
							<?php if ( $sec[1] ) : ?><p class="ipa-card-d"><?php echo wp_kses_post( $sec[1] ); ?></p><?php endif; ?>
							<table class="form-table" role="presentation">
								<?php foreach ( $sec[2] as $k ) : ?>
									<?php
									if ( ! isset( $flat[ $k ] ) ) {
										continue;
									}
									$f    = $flat[ $k ];
									$wide = in_array( $f[1], array( 'repeater', 'checklist', 'note' ), true );
									?>
									<tr class="ipa-row ipa-t-<?php echo esc_attr( $f[1] ); ?><?php echo $wide ? ' ipa-wide' : ''; ?>"<?php echo isset( $if[ $k ] ) ? ' data-ipa-if="' . esc_attr( $if[ $k ] ) . '"' : ''; ?>>
										<?php if ( 'note' === $f[1] ) : ?>
											<td colspan="2"><div class="ipa-note"><?php echo wp_kses_post( $f[3] ); ?></div></td>
										<?php else : ?>
											<th scope="row"><label for="ip-<?php echo esc_attr( $k ); ?>"><?php echo wp_kses_post( $f[0] ); ?></label></th>
											<td>
												<?php ip_settings_field( $k, $f ); ?>
												<?php if ( ! empty( $f[3] ) ) : ?><p class="description"><?php echo wp_kses_post( $f[3] ); ?></p><?php endif; ?>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							</table>
						</section>
					<?php endforeach; ?>
					<div class="ipa-save">
						<span class="ipa-save-t" data-ipa-dirty hidden>Ci sono modifiche non salvate</span>
						<?php submit_button( 'Salva le modifiche', 'primary large', 'submit', false ); ?>
					</div>
				</form>
				<?php do_action( 'ip_settings_after', $group ); ?>
			</div>
		</div>
	</div>
	<?php
}

/* ---------------------------------------------------------------------
 * 5. Bacheca Infopoint
 * ------------------------------------------------------------------- */

function ip_leads_between( $from, $to = null ) {
	$q = new WP_Query( array(
		'post_type'      => 'ip_lead',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'date_query'     => array( array( 'after' => $from, 'before' => $to ? $to : 'tomorrow', 'inclusive' => true ) ),
	) );
	return (int) $q->found_posts;
}

/** Conteggio dei valori di un campo sulle richieste recenti. */
function ip_leads_breakdown( $meta, $days = 30, $limit = 6 ) {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT pm.meta_value v, COUNT(*) n FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = %s
		 WHERE p.post_type = 'ip_lead' AND p.post_status = 'publish' AND p.post_date >= %s GROUP BY pm.meta_value ORDER BY n DESC LIMIT %d",
		$meta,
		gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) - $days * DAY_IN_SECONDS ),
		$limit
	) );
	return $rows ? $rows : array();
}

function ip_setup_checks() {
	$cookie  = (int) ip_opt( 'cookie_page' );
	$privacy = (int) ip_opt( 'privacy_page' );
	$set     = function ( $tab ) {
		return admin_url( 'admin.php?page=ip-settings&tab=' . $tab );
	};
	$checks  = array(
		array( ip_opt( 'smtp_on' ) && ip_opt( 'smtp_host' ) && ! get_option( 'ip_mail_error' ), 'Invio delle email', get_option( 'ip_mail_error' ) ? 'L’ultimo invio non è riuscito: controlla i dati SMTP.' : 'Configura il server SMTP perché notifiche e conferme arrivino davvero.', $set( 'email' ) ),
		array( (bool) ip_opt( 'lead_to' ), 'Chi riceve le richieste', 'Scegli gli indirizzi che ricevono ogni richiesta.', $set( 'email' ) ),
		array( has_custom_logo(), 'Logo', 'Carica il logo: compare in alto, nel piè di pagina e nelle landing.', admin_url( 'customize.php?autofocus[control]=custom_logo' ) ),
		array( ip_opt( 'phone1' ) && ip_opt( 'whatsapp' ), 'Telefono e WhatsApp', 'Servono per i pulsanti «Chiama» e «WhatsApp».', $set( 'azienda' ) ),
		array( ip_opt( 'company_name' ) && ip_opt( 'vat' ) && ip_opt( 'pec' ), 'Dati legali', 'Ragione sociale, P.IVA e PEC: obbligatori e richiesti da Google Ads.', $set( 'azienda' ) . '#ipa-sec-4' ),
		array( $privacy && 'publish' === get_post_status( $privacy ) && $cookie && 'publish' === get_post_status( $cookie ), 'Privacy e cookie policy', 'Le pagine devono essere pubblicate e collegate.', $set( 'moduli' ) . '#ipa-sec-1' ),
		array( ip_tracking_on(), 'Misurazione', 'Inserisci Tag Manager, Analytics o Google Ads per misurare visite e conversioni.', $set( 'tracking' ) ),
	);
	if ( ip_ads_id() ) {
		$checks[] = array( (bool) ip_opt( 'ads_lead_label' ), 'Conversione Google Ads', 'Inserisci l’etichetta della conversione «Richiesta».', $set( 'tracking' ) . '#ipa-sec-1' );
	}
	return $checks;
}

function ip_dashboard_page() {
	$today  = ip_leads_between( 'today' );
	$week   = ip_leads_between( '6 days ago midnight' );
	$month  = ip_leads_between( '29 days ago midnight' );
	$prev   = ip_leads_between( '59 days ago midnight', '30 days ago midnight' );
	$new_q  = new WP_Query( array( 'post_type' => 'ip_lead', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_lead_stato', 'meta_value' => 'nuovo' ) );
	$new    = (int) $new_q->found_posts;
	$trend  = $prev ? round( 100 * ( $month - $prev ) / $prev ) : null;
	$recent = get_posts( array( 'post_type' => 'ip_lead', 'posts_per_page' => 8 ) );
	$ch     = ip_leads_breakdown( '_lead_canale' );
	$pages  = ip_leads_breakdown( '_lead_pagina', 30, 5 );
	$checks = ip_setup_checks();
	$todo   = array_filter( $checks, function ( $c ) {
		return ! $c[0];
	} );
	$states = ip_lead_states();
	$upd    = function_exists( 'ip_sync_open_count' ) ? ip_sync_open_count() : 0;
	$maint  = function_exists( 'ipm_opt' ) && ipm_opt( 'on' );
	$leads  = ip_leads_url();
	?>
	<div class="wrap ip-admin ipa">
		<?php
		ip_admin_header(
			'Bacheca',
			'Le richieste arrivate, da dove arrivano e cosa resta da sistemare.',
			array(
				array( 'Vedi il sito', home_url( '/' ), 'ipa-btn-line', true ),
				array( 'Crea una landing', admin_url( 'edit.php?post_type=ip_landing&page=ip-lp-new' ), 'ipa-btn-line' ),
				array( 'Apri le richieste', $leads, '' ),
			)
		);
		?>
		<?php if ( $maint ) : ?>
			<div class="ipa-alert ipa-alert-warn"><span class="dashicons dashicons-hammer"></span> <strong>Il sito è in manutenzione:</strong> i visitatori vedono la pagina di cortesia. <a href="<?php echo esc_url( admin_url( 'options-general.php?page=ipm' ) ); ?>">Disattiva</a></div>
		<?php endif; ?>
		<?php if ( get_option( 'ip_mail_error' ) ) : ?>
			<div class="ipa-alert ipa-alert-error"><span class="dashicons dashicons-warning"></span> <strong>Le email non partono.</strong> Le richieste restano salvate qui. <a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=email' ) ); ?>">Sistema l’invio</a></div>
		<?php endif; ?>

		<div class="ipa-kpis">
			<a class="ipa-kpi ipa-kpi-hl" href="<?php echo esc_url( add_query_arg( 'stato', 'nuovo', $leads ) ); ?>"><span>Da contattare</span><strong><?php echo (int) $new; ?></strong><small>Apri l’elenco</small></a>
			<div class="ipa-kpi"><span>Oggi</span><strong><?php echo (int) $today; ?></strong><small>richieste</small></div>
			<div class="ipa-kpi"><span>Ultimi 7 giorni</span><strong><?php echo (int) $week; ?></strong><small>richieste</small></div>
			<div class="ipa-kpi"><span>Ultimi 30 giorni</span><strong><?php echo (int) $month; ?></strong>
				<small><?php echo null === $trend ? 'richieste' : ( ( $trend >= 0 ? '▲ +' : '▼ ' ) . (int) $trend . '% rispetto ai 30 precedenti' ); ?></small></div>
		</div>

		<div class="ipa-grid">
			<div class="ipa-span2 ipa-stack">
			<section class="ipa-card">
				<div class="ipa-card-h"><h3>Ultime richieste</h3><a href="<?php echo esc_url( $leads ); ?>">Tutte</a></div>
				<?php if ( ! $recent ) : ?>
					<p class="ipa-empty">Ancora nessuna richiesta. Quando qualcuno compila un modulo la trovi qui, con il pulsante per chiamarlo.</p>
				<?php else : ?>
					<table class="ipa-table">
						<tbody>
							<?php foreach ( $recent as $l ) : ?>
								<?php
								$st  = get_post_meta( $l->ID, '_lead_stato', true );
								$st  = $st ? $st : 'nuovo';
								$tel = get_post_meta( $l->ID, '_lead_telefono', true );
								$can = get_post_meta( $l->ID, '_lead_canale', true );
								?>
								<tr>
									<td><a class="ipa-strong" href="<?php echo esc_url( ip_leads_url( array( 'lead' => $l->ID ) ) ); ?>"><?php echo esc_html( get_the_title( $l ) ); ?></a><br><small><?php echo esc_html( human_time_diff( get_post_time( 'U', true, $l ), time() ) ); ?> fa<?php echo $can ? ' · ' . esc_html( $can ) : ''; ?></small></td>
									<td><span class="ip-st ip-st-<?php echo esc_attr( $st ); ?>"><?php echo esc_html( isset( $states[ $st ] ) ? $states[ $st ] : $st ); ?></span></td>
									<td class="ipa-r"><?php if ( $tel ) : ?><a class="ipa-btn ipa-btn-sm" href="<?php echo esc_attr( ip_tel_href( $tel ) ); ?>"><span class="dashicons dashicons-phone"></span> <?php echo esc_html( $tel ); ?></a><?php endif; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</section>

				<div class="ipa-grid2">
			<section class="ipa-card">
				<div class="ipa-card-h"><h3>Da dove arrivano</h3><small>ultimi 30 giorni</small></div>
				<?php if ( ! $ch ) : ?>
					<p class="ipa-empty">Il canale di ogni richiesta (Google Ads, ricerca, social, diretto…) comparirà qui.</p>
				<?php else : ?>
					<?php $max = max( array_map( function ( $r ) { return (int) $r->n; }, $ch ) ); ?>
					<ul class="ipa-bars">
						<?php foreach ( $ch as $r ) : ?>
							<li><a href="<?php echo esc_url( add_query_arg( 'canale', rawurlencode( $r->v ), $leads ) ); ?>"><span><?php echo esc_html( $r->v ); ?></span><b><?php echo (int) $r->n; ?></b></a><i style="width:<?php echo (int) round( 100 * $r->n / $max ); ?>%"></i></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>

			<section class="ipa-card">
				<div class="ipa-card-h"><h3>Pagine che convertono</h3><small>ultimi 30 giorni</small></div>
				<?php if ( ! $pages ) : ?>
					<p class="ipa-empty">Le pagine da cui partono più richieste compariranno qui.</p>
				<?php else : ?>
					<ul class="ipa-list">
						<?php foreach ( $pages as $r ) : ?>
							<li><a href="<?php echo esc_url( $r->v ); ?>" target="_blank" rel="noopener"><?php echo esc_html( ip_lead_short_url( $r->v ) ); ?></a><b><?php echo (int) $r->n; ?></b></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>

				</div>
			</div>
			<div class="ipa-stack">
			<section class="ipa-card">
				<div class="ipa-card-h"><h3>Da completare</h3><span class="ipa-pill"><?php echo count( $checks ) - count( $todo ); ?>/<?php echo count( $checks ); ?></span></div>
				<ul class="ipa-checks">
					<?php foreach ( $checks as $c ) : ?>
						<li class="<?php echo $c[0] ? 'is-ok' : 'is-todo'; ?>">
							<span class="dashicons dashicons-<?php echo $c[0] ? 'yes-alt' : 'marker'; ?>"></span>
							<div><strong><?php echo esc_html( $c[1] ); ?></strong><?php if ( ! $c[0] ) : ?><br><small><?php echo esc_html( $c[2] ); ?></small> <a href="<?php echo esc_url( $c[3] ); ?>">Sistema</a><?php endif; ?></div>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="ipa-card">
				<div class="ipa-card-h"><h3>Scorciatoie</h3></div>
				<div class="ipa-shortcuts">
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=corso' ) ); ?>"><span class="dashicons dashicons-welcome-learn-more"></span>Nuovo corso</a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ip_landing&page=ip-lp-new' ) ); ?>"><span class="dashicons dashicons-megaphone"></span>Nuova landing</a>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ip_export_leads' ), 'ip_export' ) ); ?>"><span class="dashicons dashicons-download"></span>Esporta richieste</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-sync' ) ); ?>"><span class="dashicons dashicons-update"></span>Aggiornamenti<?php echo $upd ? ' <em>' . (int) $upd . '</em>' : ''; ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=home' ) ); ?>"><span class="dashicons dashicons-admin-home"></span>Home page</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-consents' ) ); ?>"><span class="dashicons dashicons-shield"></span>Consensi cookie</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-visibility' ) ); ?>"><span class="dashicons dashicons-visibility"></span>Mostra / nascondi</a>
				</div>
			</section>
			</div>
		</div>
	</div>
	<?php
}

// Riepilogo anche nella Bacheca di WordPress.
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'ip_dash', 'Infopoint: richieste', function () {
		$new_q = new WP_Query( array( 'post_type' => 'ip_lead', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_lead_stato', 'meta_value' => 'nuovo' ) );
		printf(
			'<p style="font-size:14px"><strong style="font-size:28px;display:block;color:#0e261d">%d</strong>da contattare · %d negli ultimi 7 giorni</p><p><a class="button button-primary" href="%s">Apri le richieste</a> <a class="button" href="%s">Bacheca Infopoint</a></p>',
			(int) $new_q->found_posts,
			(int) ip_leads_between( '6 days ago midnight' ),
			esc_url( ip_leads_url() ),
			esc_url( admin_url( 'admin.php?page=ip-settings' ) )
		);
	} );
	// In cima.
	global $wp_meta_boxes;
	$w = $wp_meta_boxes['dashboard']['normal']['core']['ip_dash'] ?? null;
	if ( $w ) {
		unset( $wp_meta_boxes['dashboard']['normal']['core']['ip_dash'] );
		$wp_meta_boxes['dashboard']['normal']['high']['ip_dash'] = $w;
	}
} );

/* ---------------------------------------------------------------------
 * 6. Richieste: schede per stato, cambio stato al volo, azioni rapide
 * ------------------------------------------------------------------- */

add_filter( 'views_edit-ip_lead', function () {
	global $wpdb;
	$counts = $wpdb->get_results( "SELECT pm.meta_value s, COUNT(*) n FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_lead_stato' WHERE p.post_type = 'ip_lead' AND p.post_status = 'publish' GROUP BY pm.meta_value", OBJECT_K ); // phpcs:ignore
	$all    = (int) wp_count_posts( 'ip_lead' )->publish;
	$cur    = isset( $_GET['ip_stato'] ) ? sanitize_key( $_GET['ip_stato'] ) : '';
	$base   = admin_url( 'edit.php?post_type=ip_lead' );
	$views  = array( 'all' => sprintf( '<a href="%s" class="%s">Tutte <span class="count">(%d)</span></a>', esc_url( $base ), '' === $cur ? 'current' : '', $all ) );
	foreach ( ip_lead_states() as $k => $l ) {
		$n           = isset( $counts[ $k ] ) ? (int) $counts[ $k ]->n : 0;
		$views[ $k ] = sprintf( '<a href="%s" class="%s"><i class="ip-dot ip-dot-%s"></i>%s <span class="count">(%d)</span></a>', esc_url( add_query_arg( 'ip_stato', $k, $base ) ), $cur === $k ? 'current' : '', esc_attr( $k ), esc_html( $l ), $n );
	}
	$trash = (int) wp_count_posts( 'ip_lead' )->trash;
	if ( $trash ) {
		$views['trash'] = sprintf( '<a href="%s">Cestino <span class="count">(%d)</span></a>', esc_url( add_query_arg( 'post_status', 'trash', $base ) ), $trash );
	}
	return $views;
} );

// Stato modificabile direttamente dall'elenco.
add_action( 'manage_ip_lead_posts_custom_column', function ( $col, $id ) {
	if ( 'ip_stato' !== $col ) {
		return;
	}
	echo '<select class="ipa-state" data-lead="' . (int) $id . '" aria-label="Cambia stato">';
	$cur = get_post_meta( $id, '_lead_stato', true );
	foreach ( ip_lead_states() as $k => $l ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $cur ? $cur : 'nuovo', $k, false ), esc_html( $l ) );
	}
	echo '</select>';
}, 20, 2 );

add_action( 'wp_ajax_ip_lead_state', function () {
	check_ajax_referer( 'ip_lead_state_ajax' );
	$id = absint( $_POST['lead'] ?? 0 );
	$s  = sanitize_key( wp_unslash( $_POST['state'] ?? '' ) );
	if ( ! $id || 'ip_lead' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) || ! isset( ip_lead_states()[ $s ] ) ) {
		wp_send_json_error();
	}
	update_post_meta( $id, '_lead_stato', $s );
	wp_send_json_success();
} );

// Azioni rapide sotto il nome: chiama, WhatsApp, email.
add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( 'ip_lead' !== $post->post_type ) {
		return $actions;
	}
	$tel  = get_post_meta( $post->ID, '_lead_telefono', true );
	$mail = get_post_meta( $post->ID, '_lead_email', true );
	$out  = array( 'open' => '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">Apri</a>' );
	if ( $tel ) {
		$out['call'] = '<a href="' . esc_attr( ip_tel_href( $tel ) ) . '">Chiama</a>';
		$wa          = preg_replace( '/\D/', '', $tel );
		$wa          = 10 === strlen( $wa ) && '3' === $wa[0] ? '39' . $wa : $wa;
		if ( '3' === substr( $wa, 2, 1 ) ) {
			$out['wa'] = '<a href="https://wa.me/' . esc_attr( $wa ) . '" target="_blank" rel="noopener">WhatsApp</a>';
		}
	}
	if ( is_email( $mail ) ) {
		$out['mail'] = '<a href="mailto:' . esc_attr( $mail ) . '">Email</a>';
	}
	if ( isset( $actions['trash'] ) ) {
		$out['trash'] = $actions['trash'];
	}
	return $out;
}, 10, 2 );

// Azioni di gruppo: cambia stato a più richieste.
add_filter( 'bulk_actions-edit-ip_lead', function ( $a ) {
	unset( $a['edit'] );
	foreach ( ip_lead_states() as $k => $l ) {
		$a[ 'ip_state_' . $k ] = 'Segna come: ' . $l;
	}
	return $a;
} );
add_filter( 'handle_bulk_actions-edit-ip_lead', function ( $url, $action, $ids ) {
	if ( 0 === strpos( $action, 'ip_state_' ) ) {
		$s = substr( $action, 9 );
		if ( isset( ip_lead_states()[ $s ] ) ) {
			foreach ( $ids as $id ) {
				if ( current_user_can( 'edit_post', $id ) ) {
					update_post_meta( $id, '_lead_stato', $s );
				}
			}
		}
	}
	return $url;
}, 10, 3 );

// Data leggibile al posto di «Pubblicato».
add_filter( 'post_date_column_status', function ( $status, $post ) {
	return 'ip_lead' === $post->post_type ? '' : $status;
}, 10, 2 );
add_filter( 'post_date_column_time', function ( $t, $post ) {
	if ( 'ip_lead' !== $post->post_type ) {
		return $t;
	}
	return esc_html( get_the_date( 'j M Y, H:i', $post ) ) . '<br><small>' . esc_html( human_time_diff( get_post_time( 'U', true, $post ), time() ) ) . ' fa</small>';
}, 10, 2 );

// Scheda della richiesta: intestazione con le azioni principali.
add_action( 'edit_form_after_title', function ( $post ) {
	if ( 'ip_lead' !== $post->post_type ) {
		return;
	}
	$m    = function ( $k ) use ( $post ) {
		return (string) get_post_meta( $post->ID, '_lead_' . $k, true );
	};
	$tel  = $m( 'telefono' );
	$wa   = preg_replace( '/\D/', '', $tel );
	$wa   = 10 === strlen( $wa ) && '3' === $wa[0] ? '39' . $wa : $wa;
	$name = trim( $m( 'nome' ) . ' ' . $m( 'cognome' ) );
	$what = $m( 'corso' ) ? $m( 'corso' ) : $m( 'interesse' );
	?>
	<p class="ipl-back"><a href="<?php echo esc_url( ip_leads_url() ); ?>">← Tutte le richieste</a></p>
	<div class="ipa-lead-h">
		<div>
			<p class="ipa-kicker"><?php echo esc_html( get_the_date( 'j F Y, H:i', $post ) ); ?> · <?php echo esc_html( human_time_diff( get_post_time( 'U', true, $post ), time() ) ); ?> fa<?php echo $m( 'canale' ) ? ' · ' . esc_html( $m( 'canale' ) ) : ''; ?></p>
			<h2><?php echo esc_html( $name ); ?></h2>
			<?php if ( $what ) : ?><p class="ipa-lead"><?php echo esc_html( $what ); ?><?php echo $m( 'fascia' ) ? ' · preferisce essere chiamato: ' . esc_html( $m( 'fascia' ) ) : ''; ?></p><?php endif; ?>
		</div>
		<div class="ipa-head-a">
			<?php if ( $tel ) : ?>
				<a class="ipa-btn" href="<?php echo esc_attr( ip_tel_href( $tel ) ); ?>"><span class="dashicons dashicons-phone"></span> Chiama <?php echo esc_html( $tel ); ?></a>
				<?php if ( '3' === substr( $wa, 2, 1 ) ) : ?><a class="ipa-btn ipa-btn-wa" href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-whatsapp"></span> WhatsApp</a><?php endif; ?>
			<?php endif; ?>
			<?php if ( is_email( $m( 'email' ) ) ) : ?><a class="ipa-btn ipa-btn-line" href="mailto:<?php echo esc_attr( $m( 'email' ) ); ?>"><span class="dashicons dashicons-email-alt"></span> Email</a><?php endif; ?>
		</div>
	</div>
	<?php
} );

/* ---------------------------------------------------------------------
 * 7. Guide brevi in cima agli elenchi
 * ------------------------------------------------------------------- */

add_action( 'admin_notices', function () {
	$s = get_current_screen();
	if ( ! $s ) {
		return;
	}
	$tips = array(
		'edit-corso'      => array( 'Corsi', 'I corsi si aggiornano da soli dall’offerta ufficiale (<a href="' . esc_url( admin_url( 'admin.php?page=ip-sync' ) ) . '">Aggiornamenti</a>). Qui puoi correggere testi, prezzi e immagini, metterne alcuni in evidenza in home o chiuderne le iscrizioni.' ),
		'edit-ip_landing' => array( 'Landing', 'Pagine per le campagne pubblicitarie: niente menu, modulo subito visibile, nessun link che porta fuori. <a href="' . esc_url( admin_url( 'edit.php?post_type=ip_landing&page=ip-lp-new' ) ) . '">Creane una</a> partendo da un corso o da una tipologia: l’indirizzo da usare negli annunci è quello in colonna.' ),
		'edit-ip_lead'    => array( 'Richieste', 'Cambia lo stato direttamente dall’elenco; passa sopra al nome per chiamare, scrivere su WhatsApp o via email. La colonna Provenienza dice da quale campagna arriva ogni contatto.' ),
	);
	if ( isset( $tips[ $s->id ] ) && ! get_user_meta( get_current_user_id(), 'ip_tip_' . $s->id, true ) ) {
		printf(
			'<div class="notice ipa-tip is-dismissible" data-ipa-tip="%s"><p><strong>%s</strong> · %s</p></div>',
			esc_attr( $s->id ),
			esc_html( $tips[ $s->id ][0] ),
			wp_kses_post( $tips[ $s->id ][1] )
		);
	}
} );

add_action( 'wp_ajax_ip_tip_hide', function () {
	check_ajax_referer( 'ip_lead_state_ajax' );
	update_user_meta( get_current_user_id(), 'ip_tip_' . sanitize_key( wp_unslash( $_POST['tip'] ?? '' ) ), 1 );
	wp_send_json_success();
} );

add_action( 'admin_enqueue_scripts', function () {
	wp_localize_script( 'ip-admin', 'ipAdmin', array(
		'ajax'  => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'ip_lead_state_ajax' ),
	) );
}, 20 );

/* ---------------------------------------------------------------------
 * 8. Landing e corsi: meno passaggi
 * ------------------------------------------------------------------- */

// Una sola strada per creare una landing: il generatore.
add_action( 'admin_menu', function () {
	global $submenu;
	remove_submenu_page( 'edit.php?post_type=ip_landing', 'post-new.php?post_type=ip_landing' );
	foreach ( (array) ( $submenu['edit.php?post_type=ip_landing'] ?? array() ) as $i => $item ) {
		if ( 'ip-lp-new' === $item[2] ) {
			$submenu['edit.php?post_type=ip_landing'][ $i ][0] = 'Crea una landing';
		}
	}
}, 99 );
add_action( 'load-post-new.php', function () {
	if ( isset( $_GET['post_type'] ) && 'ip_landing' === $_GET['post_type'] && empty( $_GET['manuale'] ) ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=ip_landing&page=ip-lp-new' ) );
		exit;
	}
} );

// Corsi: colonne essenziali e filtro per tipologia.
add_filter( 'default_hidden_columns', function ( $hidden, $screen ) {
	if ( $screen && 'edit-corso' === $screen->id ) {
		$hidden = array_merge( $hidden, array( 'taxonomy-dipartimento', 'taxonomy-area' ) );
	}
	return $hidden;
}, 10, 2 );
add_action( 'restrict_manage_posts', function ( $pt ) {
	if ( 'corso' !== $pt ) {
		return;
	}
	wp_dropdown_categories( array(
		'taxonomy'        => 'tipologia',
		'name'            => 'tipologia',
		'value_field'     => 'slug',
		'show_option_all' => 'Tutte le tipologie',
		'selected'        => sanitize_title( wp_unslash( $_GET['tipologia'] ?? '' ) ),
		'hide_empty'      => true,
		'orderby'         => 'term_order',
	) );
} );

// Corsi: dati strutturati sempre in vista, senza il pannello a scomparsa dell'editor a blocchi.
add_filter( 'use_block_editor_for_post_type', function ( $use, $pt ) {
	return 'corso' === $pt ? false : $use;
}, 10, 2 );

// Niente finestra di benvenuto dell'editor a blocchi.
add_action( 'enqueue_block_editor_assets', function () {
	wp_add_inline_script( 'wp-data', "window.addEventListener('load',function(){try{wp.data.dispatch('core/preferences').set('core/edit-post','welcomeGuide',false)}catch(e){}});" );
} );

// Riquadri di WordPress che qui non servono.
add_action( 'add_meta_boxes', function () {
	foreach ( array( 'corso', 'agevolazione', 'curriculum', 'ip_landing' ) as $pt ) {
		remove_meta_box( 'postcustom', $pt, 'normal' );
		remove_meta_box( 'trackbacksdiv', $pt, 'normal' );
		remove_meta_box( 'commentsdiv', $pt, 'normal' );
		remove_meta_box( 'commentstatusdiv', $pt, 'normal' );
	}
}, 99 );
