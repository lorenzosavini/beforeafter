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
		$text = 'Buongiorno, vorrei informazioni sui corsi UniMarconi.';
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
	return $id ? get_permalink( $id ) : '';
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
		<span class="crow-code"><?php echo esc_html( ip_meta( 'code', $id ) ); ?></span>
		<div class="crow-main">
			<a class="crow-title" href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( ip_course_name( $id ) ); ?></a>
			<span class="crow-meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?><?php if ( $stato && 'aperte' !== $stato ) : ?> · <em><?php echo esc_html( ip_status_label( $stato ) ); ?></em><?php endif; ?></span>
		</div>
		<a class="crow-cta" href="<?php echo esc_url( get_permalink( $id ) ); ?>#richiedi" aria-label="Richiedi informazioni su <?php echo esc_attr( ip_course_name( $id ) ); ?>">Costi e piano di studi <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
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
		$title = 'Non sai ancora quale corso scegliere?';
	}
	$wa = ip_wa_href();
	?>
	<section class="band">
		<div class="wrap band-in">
			<div>
				<h2><?php echo esc_html( $title ); ?></h2>
				<p>Un orientatore ti richiama, ascolta cosa ti serve e ti propone il percorso più adatto, con costi e agevolazioni. Senza impegno.</p>
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

function ip_price_from() {
	$p = trim( (string) ip_opt( 'price_from' ) );
	return $p ? '€ ' . $p : '';
}

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
