<?php
/**
 * Piccoli helper di presentazione.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icone SVG inline: poche, disegnate a tratto, senza librerie.
 */
function ip_icon( $name, $size = 20 ) {
	$paths = array(
		'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'whatsapp' => '<path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.5-4.3a8.5 8.5 0 1 1 15.5-4.6z"/><path d="M9 8.5c0 3.6 2.9 6.5 6.5 6.5l1-1.6-2-1-1 1a4 4 0 0 1-2.4-2.4l1-1-1-2z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 7 9 6 9-6"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'check'    => '<path d="m5 12 5 5 9-10"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
		'doc'      => '<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5M10 13h6M10 17h6"/>',
		'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6.5 6.5 0 0 1 3.5 6"/>',
		'shield'   => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
		'building' => '<path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6M8 11h.01M12 11h.01M16 11h.01"/>',
		'cap'      => '<path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5M22 9v6"/>',
		'trophy'   => '<path d="M8 4h8v5a4 4 0 0 1-8 0zM8 6H5a3 3 0 0 0 3 4M16 6h3a3 3 0 0 1-3 4M12 13v4M8 21h8M9 17h6"/>',
		'heart'    => '<path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.5-7 10-7 10z"/>',
		'gear'     => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9 7 7M17 17l2.1 2.1M4.9 19.1 7 17M17 7l2.1-2.1"/>',
		'euro'     => '<path d="M17 5.5a7 7 0 1 0 0 13M4 10h10M4 14h10"/>',
		'star'     => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
		'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>',
		'book'     => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2zM4 19V5M8 7h7"/>',
		'percent'  => '<path d="M19 5 5 19"/><circle cx="7" cy="7" r="2.5"/><circle cx="17" cy="17" r="2.5"/>',
		'target'   => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
		'key'      => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3M14 9l2 2"/>',
		'list'     => '<path d="M9 6h11M9 12h11M9 18h11M4.5 6h.01M4.5 12h.01M4.5 18h.01"/>',
		'question' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6M12 17h.01"/>',
		'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5h.01"/>',
		'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
		'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'card'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/>',
		'layers'   => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="i i-%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ]
	);
}

function ip_tel_href( $phone ) {
	$n = preg_replace( '/[^0-9+]/', '', (string) $phone );
	if ( $n && '+' !== $n[0] ) {
		$n = '+39' . $n;
	}
	return 'tel:' . $n;
}

function ip_wa_href( $text = '' ) {
	$n = preg_replace( '/[^0-9]/', '', (string) ip_opt( 'whatsapp' ) );
	if ( ! $n ) {
		return '';
	}
	if ( 10 === strlen( $n ) && '3' === $n[0] ) {
		$n = '39' . $n;
	}
	if ( ! $text ) {
		$text = ip_opt( 'wa_text' );
	}
	return 'https://wa.me/' . $n . '?text=' . rawurlencode( $text );
}

function ip_phone_link( $which = 'phone1', $class = '' ) {
	$p = ip_opt( $which );
	if ( ! $p ) {
		return '';
	}
	return sprintf( '<a class="%s" href="%s" data-track="call">%s<span>%s</span></a>', esc_attr( $class ), esc_attr( ip_tel_href( $p ) ), ip_icon( 'phone', 18 ), esc_html( $p ) );
}

function ip_thanks_url() {
	$id = (int) ip_opt( 'thanks_page' );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

function ip_privacy_url() {
	$id = (int) ip_opt( 'privacy_page' );
	if ( ! $id ) {
		$id = (int) get_option( 'wp_page_for_privacy_policy' );
	}
	return $id && 'publish' === get_post_status( $id ) ? get_permalink( $id ) : '';
}

function ip_courses_url( $tipologia = '' ) {
	if ( $tipologia ) {
		$link = get_term_link( $tipologia, 'tipologia' );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return get_post_type_archive_link( 'corso' );
}

/**
 * Nome del corso da mostrare negli elenchi: il nome breve se c'è.
 */
function ip_course_name( $id = null ) {
	$short = ip_meta( 'short', $id );
	return $short ? $short : get_the_title( $id );
}

function ip_course_tipologia( $id = null ) {
	$t = get_the_terms( $id ? $id : get_the_ID(), 'tipologia' );
	return ( $t && ! is_wp_error( $t ) ) ? $t[0] : null;
}

/**
 * Riga corso: classe, nome, dati essenziali, due azioni.
 */
function ip_course_row( $id = null ) {
	$id   = $id ? $id : get_the_ID();
	$tip  = ip_course_tipologia( $id );
	$meta = array_filter( array(
		$tip ? $tip->name : '',
		ip_meta( 'cfu', $id ) ? ip_meta( 'cfu', $id ) . ' CFU' : '',
		ip_meta( 'durata', $id ),
	) );
	$stato = ip_meta( 'stato', $id );
	$hay   = strtolower( remove_accents( get_the_title( $id ) . ' ' . ip_meta( 'short', $id ) . ' ' . ip_meta( 'code', $id ) ) );
	?>
	<li class="crow" data-tip="<?php echo esc_attr( $tip ? $tip->slug : '' ); ?>" data-s="<?php echo esc_attr( $hay ); ?>">
		<a class="crow-img" href="<?php echo esc_url( get_permalink( $id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ) : ''; // phpcs:ignore ?></a>
		<div class="crow-main">
			<a class="crow-title" href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( ip_course_name( $id ) ); ?><?php if ( ip_meta( 'code', $id ) ) : ?> <span class="crow-code"><?php echo esc_html( ip_meta( 'code', $id ) ); ?></span><?php endif; ?></a>
			<span class="crow-meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?><?php if ( $stato && 'aperte' !== $stato ) : ?> · <em><?php echo esc_html( ip_status_label( $stato ) ); ?></em><?php endif; ?></span>
		</div>
		<a class="crow-cta" href="<?php echo esc_url( get_permalink( $id ) ); ?>">Scopri il corso</a>
	</li>
	<?php
}

function ip_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = array( array( home_url( '/' ), 'Home' ) );
	if ( is_singular( 'corso' ) ) {
		$items[] = array( get_post_type_archive_link( 'corso' ), 'Corsi' );
		$tip     = ip_course_tipologia();
		if ( $tip ) {
			$items[] = array( get_term_link( $tip ), $tip->name );
		}
		$items[] = array( '', ip_course_name() );
	} elseif ( is_singular( 'post' ) ) {
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog ) {
			$items[] = array( get_permalink( $blog ), get_the_title( $blog ) );
		}
		$items[] = array( '', get_the_title() );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $a ) {
			$items[] = array( get_permalink( $a ), get_the_title( $a ) );
		}
		$items[] = array( '', get_the_title() );
	} elseif ( is_tax( 'tipologia' ) || is_tax( 'area' ) ) {
		$items[] = array( get_post_type_archive_link( 'corso' ), 'Corsi' );
		$items[] = array( '', single_term_title( '', false ) );
	} elseif ( is_post_type_archive( 'corso' ) ) {
		$items[] = array( '', 'Corsi' );
	} elseif ( is_home() ) {
		$items[] = array( '', 'News' );
	} elseif ( is_search() ) {
		$items[] = array( '', 'Ricerca' );
	} elseif ( is_archive() ) {
		$items[] = array( '', wp_strip_all_tags( get_the_archive_title() ) );
	} else {
		return;
	}
	$GLOBALS['ip_breadcrumbs'] = $items;
	echo '<nav class="crumbs" aria-label="Percorso"><ol>';
	foreach ( $items as $i => $it ) {
		if ( $it[0] && $i < count( $items ) - 1 ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $it[0] ), esc_html( $it[1] ) );
		} else {
			printf( '<li aria-current="page">%s</li>', esc_html( $it[1] ) );
		}
	}
	echo '</ol></nav>';
}

/**
 * Fascia finale di contatto, ripetuta in fondo a ogni pagina di contenuto.
 */
function ip_cta_band( $title = '' ) {
	if ( ! $title ) {
		$title = ip_opt( 'cta_title' );
	}
	$wa = ip_wa_href();
	?>
	<section class="band">
		<div class="wrap band-in">
			<div>
				<h2><?php echo esc_html( $title ); ?></h2>
				<p><?php echo esc_html( ip_opt( 'cta_text' ) ); ?></p>
			</div>
			<div class="band-actions">
				<a class="btn btn-accent" href="#richiedi" data-scroll-form>Richiedi informazioni</a>
				<?php echo ip_phone_link( 'phone1', 'btn btn-line-light' ); // phpcs:ignore ?>
				<?php if ( $wa ) : ?>
					<a class="btn btn-line-light" href="<?php echo esc_url( $wa ); ?>" data-track="whatsapp" target="_blank" rel="noopener"><?php echo ip_icon( 'whatsapp', 18 ); // phpcs:ignore ?><span>WhatsApp</span></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Ragione sociale con i dati di legge, su una riga.
 */
/**
 * Ruolo rispetto all'Ateneo in forma breve, per testate e occhielli:
 * «Centro di orientamento accreditato dall’Università Guglielmo Marconi».
 */
function ip_role_label() {
	$r = trim( (string) ip_opt( 'partner_role' ) );
	$r = str_replace( 'dall’Università degli Studi Guglielmo Marconi', 'dall’Università Guglielmo Marconi', $r );
	return $r ? mb_strtoupper( mb_substr( $r, 0, 1 ) ) . mb_substr( $r, 1 ) : '';
}

function ip_legal_line() {
	// Il nome del polo viene prima; la società che lo gestisce lo accompagna, non lo sostituisce.
	$parts = array( ip_opt( 'brand' ) );
	if ( ip_opt( 'company_name' ) && ip_opt( 'company_name' ) !== ip_opt( 'brand' ) ) {
		$parts[] = 'gestito da ' . ip_opt( 'company_name' );
	}
	if ( ip_opt( 'legal_address' ) ) {
		$parts[] = 'Sede legale ' . preg_replace( '/\s*\n\s*/', ', ', trim( ip_opt( 'legal_address' ) ) );
	}
	foreach ( array( 'vat' => 'P.IVA ', 'rea' => 'REA ', 'capital' => 'Cap. soc. ', 'pec' => 'PEC ' ) as $k => $l ) {
		if ( ip_opt( $k ) ) {
			$parts[] = $l . ip_opt( $k );
		}
	}
	return implode( ' · ', $parts );
}

function ip_price_from() {
	$p = trim( (string) ip_opt( 'price_from' ) );
	if ( $p ) {
		return '€ ' . $p;
	}
	$f = ip_fees();
	return $f['min_rata'] ? ip_eur( $f['min_rata'] ) : '';
}

/**
 * Retta standard dei corsi di laurea, es. «€ 2.760 l’anno (€ 230 al mese)».
 */
function ip_retta_std() {
	$p = trim( (string) ip_opt( 'retta_std' ) );
	if ( $p ) {
		return $p;
	}
	$f = ip_fees();
	if ( ! $f['std_retta'] ) {
		return '';
	}
	return ip_eur( $f['std_retta'] ) . ' l’anno' . ( $f['std_rata'] ? ' (' . ip_eur( $f['std_rata'] ) . ' al mese)' : '' );
}

function ip_eur( $v ) {
	$v = trim( (string) $v );
	if ( '' === $v ) {
		return '';
	}
	return '€ ' . ( false !== strpos( $v, ',' ) ? $v : number_format( (float) $v, 0, ',', '.' ) );
}

/**
 * Importi presi dalle agevolazioni, che si aggiornano dal sito ufficiale:
 * retta standard e importi minimi. Nessuna cifra scritta a mano nei testi.
 */
function ip_fees() {
	static $f = null;
	if ( null !== $f ) {
		return $f;
	}
	$f = array( 'std_retta' => '', 'std_rata' => '', 'min_rata' => '', 'min_retta' => '' );
	$num = function ( $v ) {
		return (float) str_replace( ',', '.', str_replace( '.', '', (string) $v ) );
	};
	foreach ( get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $p ) {
		$retta = ip_meta( 'retta', $p->ID );
		$rata  = ip_meta( 'rata', $p->ID );
		$key   = get_post_meta( $p->ID, '_ip_fonte', true );
		if ( 'retta-standard' === ( $key ? $key : $p->post_name ) ) {
			$f['std_retta'] = $retta;
			$f['std_rata']  = $rata;
		}
		if ( $num( $rata ) > 0 && ( '' === $f['min_rata'] || $num( $rata ) < $num( $f['min_rata'] ) ) ) {
			$f['min_rata'] = $rata;
		}
		if ( $num( $retta ) > 0 && ( '' === $f['min_retta'] || $num( $retta ) < $num( $f['min_retta'] ) ) ) {
			$f['min_retta'] = $retta;
		}
	}
	return $f;
}

/**
 * Scadenza di un'agevolazione, letta dalle condizioni ufficiali
 * («richieste presentate entro il 30 novembre 2026»). Vuota se passata.
 */
function ip_agev_deadline( $id ) {
	return function_exists( 'ip_lp_deadline' ) ? ip_lp_deadline( ip_meta( 'cond', $id ) . "\n" . ip_meta( 'det', $id ) ) : '';
}

/**
 * Agevolazioni pubblicate con una scadenza ancora valida: [titolo, data].
 */
function ip_agev_deadlines() {
	static $out = null;
	if ( null === $out ) {
		$out = array();
		foreach ( get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $p ) {
			$d = ip_agev_deadline( $p->ID );
			if ( $d ) {
				$out[] = array( html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ), $d );
			}
		}
	}
	return $out;
}

/**
 * Promemoria delle scadenze, da mettere ovunque si parli di agevolazioni.
 */
function ip_agev_deadline_text() {
	return implode( ' ', array_map( function ( $x ) {
		return $x[0] . ': richieste entro il ' . $x[1] . '.';
	}, ip_agev_deadlines() ) );
}

function ip_agev_deadline_note( $class = 'agev-due' ) {
	$t = ip_agev_deadline_text();
	return $t ? '<p class="' . esc_attr( $class ) . '">' . esc_html( $t ) . '</p>' : '';
}

/**
 * Sostituisce i segnaposto degli importi: {retta_std}, {retta_min}, {rata_min},
 * e quello delle scadenze delle agevolazioni: {scadenze}.
 */
function ip_fill( $t ) {
	$t = (string) $t;
	if ( false === strpos( $t, '{' ) ) {
		return $t;
	}
	$f = ip_fees();
	return strtr( $t, array(
		'{retta_std}' => ip_retta_std(),
		'{retta_min}' => ip_eur( $f['min_retta'] ),
		'{rata_min}'  => ip_price_from(),
		'{scadenze}'  => ip_agev_deadline_text(),
	) );
}
add_filter( 'the_content', 'ip_fill', 1 );
add_filter( 'get_the_excerpt', 'ip_fill', 20 );

/**
 * Menu di riserva finché non ne viene assegnato uno.
 */
function ip_menu_fallback() {
	$links = array( array( get_post_type_archive_link( 'corso' ), 'Corsi' ) );
	foreach ( array( 'master' => 'Master', 'corsi-insegnanti' => 'Insegnanti', 'convenzioni-e-agevolazioni' => 'Agevolazioni', 'riconoscimento-cfu' => 'Riconoscimento CFU', 'contatti' => 'Contatti' ) as $slug => $label ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			$links[] = array( get_permalink( $p ), $label );
		}
	}
	echo '<ul class="menu">';
	foreach ( $links as $l ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $l[0] ), esc_html( $l[1] ) );
	}
	echo '</ul>';
}

function ip_page_url( $slug ) {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : '';
}

/**
 * Immagine di una tipologia: quella scelta nel termine, altrimenti la
 * copertina del primo corso della tipologia che ne ha una.
 */
function ip_term_image_id( $term ) {
	$id = (int) get_term_meta( $term->term_id, 'immagine', true );
	if ( $id ) {
		return $id;
	}
	$q = get_posts( array(
		'post_type'      => 'corso',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'meta_key'       => '_thumbnail_id',
		'tax_query'      => array( array( 'taxonomy' => $term->taxonomy, 'terms' => $term->term_id ) ),
	) );
	return $q ? (int) get_post_thumbnail_id( $q[0] ) : 0;
}

/**
 * Foto della home: quella scelta nelle impostazioni, altrimenti la
 * copertina del primo corso in evidenza.
 */
function ip_hero_image_id() {
	$id = (int) ip_opt( 'hero_image' );
	if ( $id && wp_attachment_is_image( $id ) ) {
		return $id;
	}
	$q = get_posts( array( 'post_type' => 'corso', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_ip_featured', 'value' => '1' ), array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ) ) );
	return $q ? (int) get_post_thumbnail_id( $q[0] ) : 0;
}

/**
 * Card corso con copertina: usata in home e nei corsi correlati.
 */
function ip_course_card( $id = null ) {
	$id    = $id ? $id : get_the_ID();
	$tip   = ip_course_tipologia( $id );
	$code  = ip_meta( 'code', $id );
	$stato = ip_meta( 'stato', $id ) ? ip_meta( 'stato', $id ) : 'aperte';
	$meta  = array_filter( array( ip_meta( 'cfu', $id ) ? ip_meta( 'cfu', $id ) . ' CFU' : '', ip_meta( 'durata', $id ) ) );
	?>
	<article class="ccard">
		<a class="ccard-img" href="<?php echo esc_url( get_permalink( $id ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail( $id ) ) : ?>
				<?php echo get_the_post_thumbnail( $id, 'ip-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
			<?php endif; ?>
			<?php if ( $code ) : ?><span class="ccard-code"><?php echo esc_html( $code ); ?></span><?php endif; ?>
		</a>
		<div class="ccard-body">
			<?php if ( $tip ) : ?><p class="ccard-type"><?php echo esc_html( $tip->name ); ?></p><?php endif; ?>
			<h3><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( ip_course_name( $id ) ); ?></a></h3>
			<?php if ( $meta ) : ?><p class="ccard-meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p><?php endif; ?>
			<p class="ccard-foot">
				<span class="dot dot-<?php echo esc_attr( $stato ); ?>"><?php echo esc_html( ip_status_label( $stato ) ); ?></span>
				<a href="<?php echo esc_url( get_permalink( $id ) ); ?>">Scopri il corso</a>
			</p>
		</div>
	</article>
	<?php
}

/**
 * Testo con parole evidenziate: *così* diventa <span class="hl">così</span>.
 * Con $auto, in un titolo senza asterischi si evidenzia la parte dopo i due
 * punti (es. «Futuro Giovani: retta € 1.620 l’anno»).
 */
function ip_hl( $text, $auto = false ) {
	$text = (string) $text;
	if ( $auto && false === strpos( $text, '*' ) && preg_match( '/^(.{3,60}?):\s+(.{3,})$/u', $text, $m ) ) {
		$text = $m[1] . ': *' . $m[2] . '*';
	}
	return preg_replace( '/\*([^*]+)\*/u', '<span class="hl">$1</span>', esc_html( $text ) );
}

/** Lo stesso testo senza asterischi, per title, meta e attributi. */
function ip_hl_plain( $text ) {
	return str_replace( '*', '', (string) $text );
}

/**
 * Nessun rimando al sito dell'Ateneo: i link a unimarconi.it nei testi
 * importati puntano alla pagina corrispondente di questo sito (se esiste)
 * oppure diventano testo semplice. I documenti (PDF, Word…) restano e si
 * aprono in un'altra finestra.
 */
function ip_local_url( $url ) {
	static $map = null;
	if ( null === $map ) {
		global $wpdb;
		$map  = array();
		$rows = $wpdb->get_results( "SELECT m.post_id, m.meta_value FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE m.meta_key = '_ip_fonte' AND p.post_status = 'publish' AND p.post_type IN ('page','corso')" );
		foreach ( $rows as $r ) {
			$map[ ip_url_key( $r->meta_value ) ] = (int) $r->post_id;
		}
	}
	$k = ip_url_key( $url );
	return isset( $map[ $k ] ) ? get_permalink( $map[ $k ] ) : '';
}

function ip_url_key( $url ) {
	$p = wp_parse_url( trim( $url ) );
	return isset( $p['path'] ) ? trailingslashit( strtolower( $p['path'] ) ) : '/';
}

function ip_local_links( $html ) {
	if ( false === stripos( (string) $html, 'unimarconi.it' ) ) {
		return $html;
	}
	return preg_replace_callback( '#<a\b[^>]*\bhref=(["\'])(https?://(?:[a-z0-9-]+\.)*unimarconi\.it[^"\']*)\1[^>]*>(.*?)</a>#is', function ( $m ) {
		$url = html_entity_decode( $m[2] );
		if ( preg_match( '/\.(pdf|docx?|xlsx?|pptx?|zip)(\?|#|$)/i', $url ) ) {
			return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $m[3] . '</a>';
		}
		$local = ip_local_url( $url );
		return $local ? '<a href="' . esc_url( $local ) . '">' . $m[3] . '</a>' : $m[3];
	}, $html );
}
add_filter( 'the_content', 'ip_local_links', 99 );

/**
 * Icone delle tendine: stampate una volta in fondo alla pagina, lo script le
 * mette a sinistra di ogni titolo secondo l'argomento (obiettivi, sbocchi,
 * piani di studio, costi, domande…).
 */
add_action( 'wp_footer', function () {
	echo '<template id="ip-acc-icons">';
	foreach ( array( 'target', 'briefcase', 'book', 'doc', 'key', 'euro', 'check', 'list', 'pin', 'clock', 'calendar', 'question', 'info', 'cap', 'users', 'percent' ) as $n ) {
		echo '<span data-i="' . esc_attr( $n ) . '">' . ip_icon( $n, 18 ) . '</span>'; // phpcs:ignore
	}
	echo '</template>';
}, 5 );

/** Icona di un'agevolazione, dal nome. */
function ip_agev_icon( $title ) {
	$map = array(
		'/standard/i'                  => 'euro',
		'/110 e lode/i'                => 'star',
		'/continuit/i'                 => 'cap',
		'/divisa|forze/i'              => 'shield',
		'/pubblica amministrazione/i'  => 'building',
		'/giovani/i'                   => 'user',
		'/sport/i'                     => 'trophy',
		'/docente|ata\b/i'            => 'book',
		'/laureati/i'                  => 'cap',
		'/famiglia/i'                  => 'users',
		'/ingegner/i'                  => 'gear',
		'/borse|disco/i'               => 'star',
		'/disabilit/i'                 => 'heart',
		'/prestito/i'                  => 'percent',
		'/convenzion|enti\b/i'        => 'briefcase',
	);
	foreach ( $map as $re => $icon ) {
		if ( preg_match( $re, $title ) ) {
			return $icon;
		}
	}
	return 'percent';
}

/**
 * Testata a banda verde delle pagine (come schede corso e ricerca corsi):
 * titolo piccolo sopra, titolo nella fascia arancione, sottotitolo e
 * pulsanti di contatto.
 */
function ip_page_band( $title, $lead = '', $pre = '', $acts = true ) {
	?>
	<header class="phead phead-band">
		<div class="wrap">
			<?php ip_breadcrumbs(); ?>
			<h1><?php if ( $pre ) : ?><span class="h1-pre"><?php echo esc_html( $pre ); ?></span> <?php endif; ?><span class="hl"><?php echo esc_html( $title ); ?></span></h1>
			<?php if ( $lead ) : ?>
				<p class="phead-lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<?php if ( $acts ) : ?>
				<div class="phead-acts band-acts">
					<a class="btn btn-accent" href="#richiedi" data-scroll-form>Chiedi a un orientatore</a>
					<?php echo ip_phone_link( 'phone1', 'btn btn-white' ); // phpcs:ignore ?>
				</div>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/** La pagina mostra i pulsanti di contatto in testata? Non su privacy, cookie e ringraziamento. */
function ip_page_band_acts( $id ) {
	$no = array_filter( array( (int) ip_opt( 'privacy_page' ), (int) ip_opt( 'cookie_page' ), (int) ip_opt( 'thanks_page' ), (int) get_option( 'wp_page_for_privacy_policy' ) ) );
	return ! in_array( (int) $id, $no, true );
}

/** Titolo piccolo sopra quello della pagina: la pagina madre, se c'è. */
function ip_page_band_pre( $id ) {
	$parent = wp_get_post_parent_id( $id );
	return $parent ? get_the_title( $parent ) : '';
}

/** Icona di una voce del menu principale, dal titolo. */
function ip_menu_icon( $title ) {
	$map = array(
		'/stranier|ester/i'             => 'globe',
		'/sedi/i'                       => 'pin',
		'/requisit/i'                   => 'key',
		'/riconosciment|cfu/i'          => 'percent',
		'/tasse/i'                      => 'doc',
		'/pagament/i'                   => 'card',
		'/110 e lode/i'                 => 'building',
		'/agevolazion|convenzion|cost/i' => 'euro',
		'/sostegno/i'                   => 'heart',
		'/abilitant/i'                  => 'check',
		'/didattica/i'                  => 'book',
		'/insegnant/i'                  => 'user',
		'/master/i'                     => 'briefcase',
		'/singol/i'                     => 'book',
		'/doppia/i'                     => 'layers',
		'/formazione/i'                 => 'target',
		'/microcredenzial/i'            => 'star',
		'/dottorat/i'                   => 'trophy',
		'/altri corsi/i'                => 'list',
		'/laurea|corsi di laurea/i'     => 'cap',
		'/iscriv/i'                     => 'check',
		'/chi siamo/i'                  => 'users',
		'/contatt/i'                    => 'phone',
	);
	foreach ( $map as $re => $icon ) {
		if ( preg_match( $re, $title ) ) {
			return $icon;
		}
	}
	return '';
}

// Icone nel menu principale (testata e menu a tendina del telefono).
add_filter( 'nav_menu_item_title', function ( $title, $item, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $title;
	}
	$icon = ip_menu_icon( wp_strip_all_tags( $title ) );
	return $icon ? '<span class="mi">' . ip_icon( $icon, 16 ) . '</span><span class="mt">' . $title . '</span>' : $title;
}, 10, 3 );

// Pulsante per aprire i sottomenu nel menu del telefono.
add_filter( 'walker_nav_menu_start_el', function ( $out, $item, $depth, $args ) {
	if ( ! empty( $args->theme_location ) && 'primary' === $args->theme_location && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$out .= '<button type="button" class="sub-toggle" aria-expanded="false" aria-label="Apri ' . esc_attr( wp_strip_all_tags( $item->title ) ) . '"></button>';
	}
	return $out;
}, 10, 4 );

