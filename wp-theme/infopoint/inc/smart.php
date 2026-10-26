<?php
/**
 * Versione essenziale dei corsi: tutte le sezioni che servono a decidere,
 * ciascuna accorciata, senza le parti accessorie, con l'invito a chiedere
 * il dettaglio completo.
 * Il testo completo resta salvato e continua ad aggiornarsi dal sito ufficiale.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sezioni accessorie che la versione essenziale può togliere, riconosciute dal
 * titolo. Tutto il resto (presentazione, obiettivi, destinatari, programma,
 * sbocchi, titolo, durata, costi…) resta sempre, al massimo accorciato.
 */
function ip_smart_groups() {
	return array(
		'persone'   => array( 'Direttori, docenti e faculty', '/direttor|faculty|docent|teachers|comitato|coordinat|scientific director|tutor/u' ),
		'pagamenti' => array( 'Modalità di pagamento e finanziamenti', '/modalit[aà] di pagamento|pagamento|payment|financing|sepa|directpay|prestito|loan|pa 110|pagopa|rateizz/u' ),
		'partner'   => array( 'Collaborazioni e partner', '/collaborazion|partner|convenzion|con il contributo/u' ),
		'avvio'     => array( 'Scegli come iniziare', '/scegli come|choose how|come iniziare/u' ),
		'valutazione' => array( 'Criteri di valutazione', '/criteri di valutazione|assessment|valutazione/u' ),
	);
}

function ip_smart_defaults() {
	return array(
		'on'     => '',
		'drop'   => array( 'persone', 'pagamenti', 'partner', 'avvio' ),
		'paras'  => 2,
		'items'  => 6,
		'parts'  => array( 'steps', 'related' ),
		'docs'   => 2,
		'pages_on'   => '',
		'pages_full' => array(),
	);
}

function ip_smart() {
	$v = ip_vis();
	return wp_parse_args( isset( $v['smart'] ) && is_array( $v['smart'] ) ? $v['smart'] : array(), ip_smart_defaults() );
}

/** Versione essenziale attiva per questo corso? */
function ip_smart_on( $post_id = null ) {
	$s = ip_smart();
	if ( ! $s['on'] ) {
		return false;
	}
	return ! get_post_meta( $post_id ? $post_id : get_the_ID(), '_ip_full', true );
}

function ip_smart_group_of( $title ) {
	$k = ip_vis_key( $title );
	foreach ( ip_smart_groups() as $g => $d ) {
		if ( preg_match( $d[1], $k ) ) {
			return $g;
		}
	}
	return '';
}

/**
 * Un blocco porta un dato essenziale (importo, data, durata, CFU, scadenza)?
 * Con $short > 0 contano come essenziali anche le righe fino a quella lunghezza.
 * I blocchi essenziali non si tolgono mai: si accorcia solo il testo descrittivo.
 */
function ip_smart_essential( $node, $short = 0 ) {
	$t = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
	if ( '' === $t ) {
		return false;
	}
	if ( $short && mb_strlen( $t ) <= $short ) {
		return true;
	}
	return (bool) preg_match( '/€|\beur\b|\d+([.,]\d+)?\s?(%|cfu|ects|mesi|anni|ore|giorni|gg|settimane|rate)\b|\b\d{1,2}[\/.]\d{1,2}[\/.]\d{2,4}\b|\b\d{1,2} (gennaio|febbraio|marzo|aprile|maggio|giugno|luglio|agosto|settembre|ottobre|novembre|dicembre)\b|scadenz/iu', $t );
}

/**
 * Accorcia un elenco: oltre $items voci restano solo quelle con dati essenziali.
 * Restituisce true se ha tolto qualcosa.
 */
function ip_smart_trim_list( $list, $items, $from = null ) {
	$cut = false;
	$i   = 0;
	foreach ( iterator_to_array( $list->childNodes ) as $c ) {
		if ( XML_ELEMENT_NODE !== $c->nodeType || 'li' !== strtolower( $c->nodeName ) ) {
			continue;
		}
		$i++;
		if ( $i > ( null === $from ? $items : $from ) && ! ip_smart_essential( $c ) ) {
			$list->removeChild( $c );
			$cut = true;
		}
	}
	return $cut;
}

/**
 * Accorcia un blocco di HTML: oltre $paras paragrafi resta solo ciò che porta
 * dati essenziali; elenchi fino a $items voci (più quelle con dati); tabelle
 * intere. Restituisce [html, accorciato?].
 */
function ip_smart_trim( $html, $paras, $items, $short = 0 ) {
	if ( '' === trim( $html ) ) {
		return array( $html, false );
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="ipw">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	$root = $doc->getElementById( 'ipw' );
	if ( ! $root ) {
		return array( $html, false );
	}
	$cut   = false;
	$count = 0;
	$head  = null; // Sottotitolo in attesa: resta solo se resta qualcosa sotto.
	foreach ( iterator_to_array( $root->childNodes ) as $n ) {
		if ( XML_ELEMENT_NODE !== $n->nodeType ) {
			if ( $count >= $paras && trim( $n->textContent ) !== '' ) {
				$root->removeChild( $n );
			}
			continue;
		}
		$tag = strtolower( $n->nodeName );
		if ( in_array( $tag, array( 'h3', 'h4', 'h5', 'h6' ), true ) ) {
			if ( $head ) {
				$root->removeChild( $head );
				$cut = true;
			}
			$head = $count >= $paras ? $n : null;
			continue;
		}
		$list = in_array( $tag, array( 'ul', 'ol' ), true );
		if ( $count < $paras ) {
			if ( $list && ip_smart_trim_list( $n, $items ) ) {
				$cut = true;
			}
			if ( 'table' !== $tag ) {
				$count++;
			}
			continue;
		}
		// Oltre il limite: restano tabelle, blocchi con dati, voci con dati.
		$keep = 'table' === $tag;
		if ( $list ) {
			$cut  = ip_smart_trim_list( $n, $items, 0 ) || $cut;
			$keep = (bool) $n->getElementsByTagName( 'li' )->length;
		} elseif ( ! $keep ) {
			$keep = ip_smart_essential( $n, $short );
		}
		if ( $keep ) {
			$head = null;
		} else {
			$root->removeChild( $n );
			$cut = true;
		}
	}
	if ( $head ) {
		$root->removeChild( $head );
		$cut = true;
	}
	$out = '';
	foreach ( $root->childNodes as $c ) {
		$out .= $doc->saveHTML( $c );
	}
	return array( $out, $cut );
}

// Testo del corso: sezioni essenziali, accorciate. Dopo i filtri di Visibilità.
add_filter( 'the_content', function ( $html ) {
	if ( ! ip_vis_front() || ! is_singular( 'corso' ) || ! in_the_loop() || ! ip_smart_on() ) {
		return $html;
	}
	$s    = ip_smart();
	$drop = (array) $s['drop'];
	$p    = max( 1, (int) $s['paras'] );
	$it   = max( 2, (int) $s['items'] );
	$more = '<p class="smart-more"><a href="#richiedi" data-scroll-form>Vuoi il dettaglio completo? Te lo inviamo noi</a></p>';
	if ( ! preg_match_all( '#<h2\b[^>]*>(.*?)</h2>#is', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		list( $t, $cut ) = ip_smart_trim( $html, $p + 1, $it, 100 );
		return $t . ( $cut ? $more : '' );
	}
	// Testo prima del primo titolo (la presentazione): accorciato come le sezioni.
	list( $out, $cut ) = ip_smart_trim( substr( $html, 0, $m[0][0][1] ), $p, $it, 100 );
	$out .= $cut ? $more : '';
	$n    = count( $m[0] );
	for ( $i = 0; $i < $n; $i++ ) {
		if ( in_array( ip_smart_group_of( $m[1][ $i ][0] ), $drop, true ) ) {
			continue; // Sezione accessoria.
		}
		$start = $m[0][ $i ][1] + strlen( $m[0][ $i ][0] );
		$end   = $i + 1 < $n ? $m[0][ $i + 1 ][1] : strlen( $html );
		list( $body, $cut ) = ip_smart_trim( substr( $html, $start, $end - $start ), $p, $it, 100 );
		$out  .= $m[0][ $i ][0] . $body . ( $cut ? $more : '' );
	}
	return $out;
}, 21 );

// Parti della scheda che la versione essenziale toglie.
add_filter( 'ip_part_on', function ( $on, $key, $post_id ) {
	if ( $on && ip_vis_front() && ip_smart_on( $post_id ) && in_array( $key, (array) ip_smart()['parts'], true ) ) {
		return false;
	}
	return $on;
}, 10, 3 );

// Documenti: solo i primi (di solito brochure e «il corso in breve»).
add_filter( 'ip_course_docs', function ( $docs, $post_id ) {
	if ( ip_vis_front() && ip_smart_on( $post_id ) ) {
		return array_slice( $docs, 0, max( 0, (int) ip_smart()['docs'] ) );
	}
	return $docs;
}, 10, 2 );

add_filter( 'body_class', function ( $c ) {
	if ( is_singular( 'corso' ) && ip_smart_on( get_queried_object_id() ) ) {
		$c[] = 'is-smart';
	}
	return $c;
} );

/* ---------------------------------------------------------------------
 * Pagine informative
 * ------------------------------------------------------------------- */

/** Pagine che non si accorciano mai: legali, contatti, ringraziamento, home. */
function ip_smart_locked_pages() {
	$ids = array( (int) get_option( 'page_on_front' ), (int) ip_opt( 'privacy_page' ), (int) ip_opt( 'cookie_page' ), (int) ip_opt( 'thanks_page' ) );
	foreach ( array( 'contatti', 'grazie' ) as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			$ids[] = (int) $p->ID;
		}
	}
	return array_values( array_filter( $ids ) );
}

function ip_smart_page_on( $id ) {
	$s = ip_smart();
	return ! empty( $s['pages_on'] ) && ! in_array( (int) $id, ip_smart_locked_pages(), true ) && ! in_array( (int) $id, array_map( 'intval', (array) $s['pages_full'] ), true ) && ! get_post_meta( $id, '_ip_full', true ) && ! is_page_template( 'page-templates/landing.php' );
}

/**
 * Accorcia ogni sezione (testo tra due titoli h2) lasciando intatti moduli,
 * schede, elenchi dei corsi e ogni altro blocco del tema. Lavora sulla
 * struttura della pagina, anche quando i titoli sono dentro un contenitore.
 */
function ip_smart_trim_page( $html, $paras, $items, $more ) {
	if ( '' === trim( $html ) ) {
		return $html;
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="ipw">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	$root = $doc->getElementById( 'ipw' );
	if ( ! $root ) {
		return $html;
	}
	$text  = array( 'p', 'ul', 'ol', 'table', 'blockquote', 'pre', 'figure' );
	$state = array( 'count' => 0, 'cut' => false );
	$tpl   = new DOMDocument();
	$tpl->loadHTML( '<?xml encoding="utf-8"?><div>' . $more . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	$tpl   = $tpl->getElementsByTagName( 'div' )->item( 0 )->firstChild;
	$cta   = function ( $before, $parent ) use ( $doc, $tpl, &$state ) {
		if ( ! $state['cut'] ) {
			return;
		}
		$node = $doc->importNode( $tpl, true );
		$before ? $parent->insertBefore( $node, $before ) : $parent->appendChild( $node );
		$state['cut'] = false;
	};
	$walk = function ( $parent ) use ( &$walk, &$state, $text, $paras, $items, $cta ) {
		foreach ( iterator_to_array( $parent->childNodes ) as $n ) {
			if ( XML_ELEMENT_NODE !== $n->nodeType ) {
				continue;
			}
			$tag   = strtolower( $n->nodeName );
			$class = ' ' . $n->getAttribute( 'class' ) . ' ';
			if ( 'h2' === $tag ) {
				$cta( $n, $parent );
				$state['count'] = 0;
				continue;
			}
			// Contenitore dei testi ufficiali: si entra e si continua a contare.
			if ( 'div' === $tag && false !== strpos( $class, ' ufficiale ' ) ) {
				$walk( $n );
				continue;
			}
			if ( in_array( $tag, array( 'h3', 'h4', 'h5', 'h6' ), true ) ) {
				continue; // I sottotitoli restano: introducono dati che restano.
			}
			if ( ! in_array( $tag, $text, true ) || 'table' === $tag ) {
				continue; // Moduli, schede, elenchi dei corsi, contatti, tabelle: intatti.
			}
			$list = in_array( $tag, array( 'ul', 'ol' ), true );
			if ( $state['count'] < $paras ) {
				if ( $list && ip_smart_trim_list( $n, $items ) ) {
					$state['cut'] = true;
				}
				$state['count']++;
				continue;
			}
			// Oltre il limite: restano le righe brevi e tutto ciò che porta dati.
			if ( $list ) {
				if ( ip_smart_trim_list( $n, $items, 0 ) ) {
					$state['cut'] = true;
				}
				if ( $n->getElementsByTagName( 'li' )->length ) {
					continue;
				}
			} elseif ( ip_smart_essential( $n, 160 ) ) {
				continue;
			}
			$parent->removeChild( $n );
			$state['cut'] = true;
			continue;
		}
		// Fine del contenitore: l'invito va in fondo alla sezione.
		if ( 'ipw' === $parent->getAttribute( 'id' ) || false !== strpos( ' ' . $parent->getAttribute( 'class' ) . ' ', ' ufficiale ' ) ) {
			$cta( null, $parent );
		}
	};
	$walk( $root );
	$out = '';
	foreach ( $root->childNodes as $c ) {
		$out .= $doc->saveHTML( $c );
	}
	return $out;
}

add_filter( 'the_content', function ( $html ) {
	if ( ! ip_vis_front() || ! is_page() || ! in_the_loop() || ! is_main_query() || ! ip_smart_page_on( get_the_ID() ) ) {
		return $html;
	}
	$s   = ip_smart();
	$msg = 'Vorrei ricevere il dettaglio completo su: ' . get_the_title();
	return ip_smart_trim_page( $html, max( 1, (int) $s['paras'] ), max( 2, (int) $s['items'] ), '<p class="smart-more"><a href="#richiedi" data-scroll-form data-msg="' . esc_attr( $msg ) . '">Vuoi il dettaglio completo? Te lo inviamo noi</a></p>' );
}, 21 );
