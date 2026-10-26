<?php
/**
 * Versione essenziale: un infopoint non è l'università. Corsi e pagine mostrano
 * ciò che serve per decidere di contattarci (cos'è, obiettivo, a chi è rivolto,
 * moduli, sbocchi, titolo, durata, costo), in breve; gli approfondimenti che si
 * danno dopo il contatto (materie, docenti, pagamenti, procedure, normative…)
 * diventano inviti specifici con il messaggio già scritto nel modulo.
 * Il testo completo resta salvato e continua ad aggiornarsi dal sito ufficiale.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Argomenti da approfondire dopo il contatto: titolo nel pannello, riconoscimento
 * dal titolo della sezione, testo dell'invito.
 */
function ip_smart_groups() {
	return array(
		'persone'     => array( 'Direttori, docenti e faculty', '/direttor|faculty|docent|teachers|comitato|coordinat|scientific director|tutor/u', 'Chi sono i docenti' ),
		'pagamenti'   => array( 'Pagamenti, rate e finanziamenti', '/modalit[aà] di pagamento|pagament|payment|financing|sepa|directpay|prestito|loan|pa 110|pagopa|rateizz|rate mensili/u', 'Come pagare a rate' ),
		'avvio'       => array( 'Quando e come iniziare', '/scegli come|choose how|come iniziare|sessioni di|finestre di/u', 'Quando puoi iniziare' ),
		'partner'     => array( 'Collaborazioni e partner', '/collaborazion|partner|con il contributo/u', 'Collaborazioni e partner' ),
		'valutazione' => array( 'Esami e criteri di valutazione', '/criteri di valutazione|assessment|valutazione|modalit[aà] d.esame|prove d.esame|esami/u', 'Come funzionano gli esami' ),
		'normativa'   => array( 'Normative, decreti e regolamenti', '/normativ|decret|\\bd\\.?m\\.?\\b|regolament|\\bbando|\\blegge\\b|riferimenti normativi|allegat/u', 'Normativa e regolamento' ),
		'certificati' => array( 'Certificati, more e tasse accessorie', '/certificat[oi] (di|uso|storico|e attestat)|tasse certificati|indennit[aà] di mora|\\bmora\\b|duplicat|tasse tirocin|bollettin/u', 'Altre tasse e certificati' ),
		'stage'       => array( 'Stage, tirocini e project work', '/stage|tirocin|internship|project work/u', 'Stage e tirocini' ),
		'iscrizione'  => array( 'Procedura di iscrizione', '/come (ci )?si iscrive|come iscriversi|^iscrizion[ei]( e agevolazioni)?$|procedura|immatricolazion|domanda di (iscrizione|ammissione)|presentazione della domanda|enrol|documenti (da|necessari)|modulistic/u', 'Come ci si iscrive' ),
		'crediti'     => array( 'Riconoscimento dei crediti', '/riconosciment|crediti gi[aà]|convalida|\\batp\\b/u', 'Quanti crediti puoi farti riconoscere' ),
		'altro'       => array( 'Altre sezioni non riconosciute', '/.^/u', 'Altre informazioni' ),
	);
}

/**
 * Argomenti che servono per decidere di contattarci: restano sempre, in breve.
 * [riconoscimento, invito se accorciato]
 */
function ip_smart_kinds() {
	return array(
		'obiettivi'     => array( '/obiettiv|objective|outcome|focus|punti chiave|competenze|perch[eéè] scegliere|cosa imparer/u', 'Vuoi saperne di più? Te lo spieghiamo noi' ),
		'presentazione' => array( '/presentazione(?! della domanda)|descrizione|description|in breve|il corso|il master|il percorso|contesto|scenario|premessa|overview/u', 'Vuoi saperne di più? Te lo spieghiamo noi' ),
		'costi'         => array( '/\\bcost[oi]\\b|quota|retta|price|\\bfees?\\b|tasse|prezzo/u', 'Ti spieghiamo costi e agevolazioni' ),
		'destinatari'   => array( '/destinatari|a chi [eè] rivolt|requisit|ammission|accesso|admission|requirement|class[ei] di concorso|chi pu[oò]|possessori/u', 'Verifichiamo insieme i tuoi requisiti' ),
		'sbocchi'       => array( '/sbocch|profili professional|career|opportunit[aà]|mondo del lavoro|lavorare/u', 'Scopri dove può portarti' ),
		'programma'     => array( '/programma|program|piano di stud|insegnament|articolazione|attivit[aà] formative|struttura|moduli|materie|curricul|^[a-f]\\) /u', 'Ricevi il piano di studi completo' ),
		'titolo'        => array( '/conseguimento|titolo (rilasciato|finale|conseguito|da conseguire)|diploma|attestato|certificazione|qualification|prova finale/u', '' ),
		'faq'           => array( '/domande|faq/u', 'Hai altre domande? Chiedici' ),
		'durata'        => array( '/durata|frequenza|calendario|cicli attivi|erogazione|duration|online|blended|in presenza|posti|allievi/u', '' ),
	);
}

function ip_smart_defaults() {
	return array(
		'on'     => '',
		'drop'   => array_keys( ip_smart_groups() ),
		'paras'  => 1,
		'items'  => 4,
		'parts'  => array( 'steps', 'related' ),
		'docs'   => 0,
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

/**
 * Di che cosa parla una sezione? Restituisce [tipo, chiave]: 'tieni' per ciò che
 * serve a decidere, 'dopo' per gli approfondimenti da dare dopo il contatto.
 * Alcuni approfondimenti si riconoscono prima (un «pagamento» non è un «costo»),
 * altri dopo (una «quota di iscrizione» è un costo, non una procedura).
 */
function ip_smart_classify( $title ) {
	$k = ip_vis_key( $title );
	$g = ip_smart_groups();
	foreach ( array( 'persone', 'pagamenti', 'avvio', 'partner', 'certificati', 'normativa', 'crediti' ) as $key ) {
		if ( preg_match( $g[ $key ][1], $k ) ) {
			return array( 'dopo', $key );
		}
	}
	foreach ( ip_smart_kinds() as $key => $d ) {
		if ( preg_match( $d[0], $k ) ) {
			return array( 'tieni', $key );
		}
	}
	foreach ( array( 'valutazione', 'stage', 'iscrizione' ) as $key ) {
		if ( preg_match( $g[ $key ][1], $k ) ) {
			return array( 'dopo', $key );
		}
	}
	return array( 'dopo', 'altro' );
}

/** Compatibilità: argomento «da approfondire» di un titolo, o ''. */
function ip_smart_group_of( $title ) {
	$c = ip_smart_classify( $title );
	return 'dopo' === $c[0] ? $c[1] : '';
}

/** Invito con il messaggio già scritto nel modulo. */
function ip_smart_cta( $label, $subject, $class = 'smart-more', $topic = '' ) {
	$msg = 'Vorrei informazioni su: ' . wp_strip_all_tags( $topic ? $topic : $label ) . ( $subject ? ' – ' . $subject : '' );
	return '<p class="' . esc_attr( $class ) . '"><a href="#richiedi" data-scroll-form data-msg="' . esc_attr( $msg ) . '">' . esc_html( $label ) . '</a></p>';
}

/** Riquadro finale con gli approfondimenti rimandati al contatto. */
function ip_smart_box( $topics, $subject ) {
	if ( ! $topics ) {
		return '';
	}
	$out = '<aside class="smart-box"><p class="smart-box-t"><strong>Da approfondire con un orientatore</strong><span>Queste informazioni te le diamo noi, su misura per te e senza impegno.</span></p><ul>';
	foreach ( array_unique( $topics ) as $label ) {
		$msg  = 'Vorrei informazioni su: ' . $label . ( $subject ? ' – ' . $subject : '' );
		$out .= '<li><a href="#richiedi" data-scroll-form data-msg="' . esc_attr( $msg ) . '">' . esc_html( $label ) . '</a></li>';
	}
	return $out . '</ul></aside>';
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
 * Accorcia un elenco: oltre $items voci si tolgono; con $data restano quelle
 * con dati essenziali. Restituisce true se ha tolto qualcosa.
 */
function ip_smart_trim_list( $list, $items, $data = true ) {
	$cut = false;
	$i   = 0;
	foreach ( iterator_to_array( $list->childNodes ) as $c ) {
		if ( XML_ELEMENT_NODE !== $c->nodeType || 'li' !== strtolower( $c->nodeName ) ) {
			continue;
		}
		$i++;
		if ( $i > $items && ! ( $data && ip_smart_essential( $c ) ) ) {
			$list->removeChild( $c );
			$cut = true;
		}
	}
	return $cut;
}

/**
 * Accorcia un blocco di HTML: $paras paragrafi, elenchi fino a $items voci.
 * Oltre il limite restano solo le righe fino a $short caratteri (es. i nomi
 * dei moduli) e, con $data, importi, date e tabelle.
 * Con $modules gli elenchi si tolgono sempre (restano i titoli dei moduli).
 * Restituisce [html, accorciato?].
 */
function ip_smart_trim( $html, $paras, $items, $short = 0, $modules = false, $data = false ) {
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
	// Programma senza sottotitoli: i moduli sono il primo elenco o la prima tabella.
	$named = $modules && ! $root->getElementsByTagName( 'h3' )->length && ! $root->getElementsByTagName( 'h4' )->length;
	$allow = $named ? 0 : PHP_INT_MAX; // Righe brevi ammesse (nei programmi: una sotto ogni modulo).
	foreach ( iterator_to_array( $root->childNodes ) as $n ) {
		if ( XML_ELEMENT_NODE !== $n->nodeType ) {
			if ( $count >= $paras && trim( $n->textContent ) !== '' ) {
				$root->removeChild( $n );
			}
			continue;
		}
		$tag  = strtolower( $n->nodeName );
		$list = in_array( $tag, array( 'ul', 'ol' ), true );
		$tbl  = 'table' === $tag || ( 'figure' === $tag && $n->getElementsByTagName( 'table' )->length );
		if ( $named && ( $list || $tbl ) ) {
			$named = false;
			$count = max( $count, $paras ); // Dopo l'elenco dei moduli non resta altro testo.
			if ( $list ) {
				$cut = ip_smart_trim_list( $n, 8, false ) || $cut;
			} else {
				$rows = $n->getElementsByTagName( 'tr' );
				for ( $i = $rows->length - 1; $i > 8; $i-- ) {
					$r = $rows->item( $i );
					$r->parentNode->removeChild( $r );
					$cut = true;
				}
			}
			continue;
		}
		if ( in_array( $tag, array( 'h3', 'h4', 'h5', 'h6' ), true ) ) {
			if ( $head ) {
				$root->removeChild( $head );
				$cut = true;
			}
			$head = $count >= $paras && ! $modules ? $n : null; // Nei programmi i titoli dei moduli restano.
			if ( $modules ) {
				// Sotto un «Modulo 1» resta una riga col nome; se il titolo ha già il nome, niente.
				$count = max( $count, $paras );
				$allow = mb_strlen( trim( $n->textContent ) ) <= 15 ? 1 : 0;
			}
			continue;
		}
		if ( $count < $paras && ! ( $modules && $list ) ) {
			if ( $list && ip_smart_trim_list( $n, $items, $data ) ) {
				$cut = true;
			}
			$count++;
			$head = null;
			continue;
		}
		$keep = false;
		if ( 'table' === $tag ) {
			$keep = $data;
		} elseif ( $list ) {
			if ( $data && ! $modules ) {
				$cut  = ip_smart_trim_list( $n, 0, true ) || $cut;
				$keep = (bool) $n->getElementsByTagName( 'li' )->length;
			}
		} else {
			$txt  = trim( preg_replace( '/\s+/u', ' ', $n->textContent ) );
			$len  = mb_strlen( $txt );
			$link = $n->getElementsByTagName( 'a' )->length && trim( preg_replace( '/\s+/u', ' ', $n->getElementsByTagName( 'a' )->item( 0 )->textContent ) ) === $txt;
			$keep = ( $short && $len && $len <= $short && ! $link && ( ! $modules || $allow-- > 0 ) ) || ( $data && ip_smart_essential( $n ) );
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

// Testo del corso: ciò che serve per decidere, in breve; il resto diventa invito.
add_filter( 'the_content', function ( $html ) {
	if ( ! ip_vis_front() || ! is_singular( 'corso' ) || ! in_the_loop() || ! ip_smart_on() ) {
		return $html;
	}
	$s      = ip_smart();
	$drop   = (array) $s['drop'];
	$groups = ip_smart_groups();
	$kinds  = ip_smart_kinds();
	$p      = max( 1, (int) $s['paras'] );
	$it     = max( 2, (int) $s['items'] );
	$name   = ip_meta( 'short', get_the_ID() ) ? ip_meta( 'short', get_the_ID() ) : get_the_title();
	$later  = array();
	if ( ! preg_match_all( '#<h2\b[^>]*>(.*?)</h2>#is', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		list( $t, $cut ) = ip_smart_trim( $html, $p + 1, $it, 100 );
		return $t . ( $cut ? ip_smart_cta( $kinds['presentazione'][1], $name, 'smart-more', 'il corso' ) : '' );
	}
	// Testo prima del primo titolo: la presentazione.
	list( $out, $cut ) = ip_smart_trim( substr( $html, 0, $m[0][0][1] ), $p, $it );
	$out .= $cut ? ip_smart_cta( $kinds['presentazione'][1], $name, 'smart-more', 'il corso' ) : '';
	$n    = count( $m[0] );
	for ( $i = 0; $i < $n; $i++ ) {
		list( $type, $key ) = ip_smart_classify( $m[1][ $i ][0] );
		if ( 'dopo' === $type && in_array( $key, $drop, true ) ) {
			$later[] = $groups[ $key ][2];
			continue;
		}
		$start = $m[0][ $i ][1] + strlen( $m[0][ $i ][0] );
		$end   = $i + 1 < $n ? $m[0][ $i + 1 ][1] : strlen( $html );
		$body  = substr( $html, $start, $end - $start );
		$label = 'tieni' === $type && $kinds[ $key ][1] ? $kinds[ $key ][1] : 'Vuoi il dettaglio completo? Te lo inviamo noi';
		switch ( 'tieni' === $type ? $key : '' ) {
			case 'programma':
				// Solo i nomi dei moduli: le materie si mandano dopo il contatto.
				list( $body, $cut ) = ip_smart_trim( $body, 1, 0, 100, true );
				break;
			case 'costi':
			case 'titolo':
			case 'durata':
				list( $body, $cut ) = ip_smart_trim( $body, 1, $it, 0, false, true );
				break;
			case 'presentazione':
			case 'obiettivi':
				list( $body, $cut ) = ip_smart_trim( $body, $p, $it );
				break;
			default:
				list( $body, $cut ) = ip_smart_trim( $body, 1, $it );
		}
		$out .= $m[0][ $i ][0] . $body . ( $cut ? ip_smart_cta( $label, $name, 'smart-more', trim( wp_strip_all_tags( $m[1][ $i ][0] ) ) ) : '' );
	}
	return $out . ip_smart_box( $later, $name );
}, 21 );

// Parti della scheda che la versione essenziale toglie.
add_filter( 'ip_part_on', function ( $on, $key, $post_id ) {
	if ( $on && ip_vis_front() && ip_smart_on( $post_id ) && in_array( $key, (array) ip_smart()['parts'], true ) ) {
		return false;
	}
	return $on;
}, 10, 3 );

// Documenti: di norma su richiesta (la brochure si riceve lasciando il contatto).
add_filter( 'ip_course_docs', function ( $docs, $post_id ) {
	if ( ip_vis_front() && ip_smart_on( $post_id ) ) {
		return array_slice( $docs, 0, max( 0, (int) ip_smart()['docs'] ) );
	}
	return $docs;
}, 10, 2 );

/** Brochure e documenti non mostrati: pulsante per riceverli. */
function ip_smart_docs_cta( $post_id ) {
	if ( ! ip_vis_front() || ! ip_smart_on( $post_id ) || ! ip_part_on( 'docs', $post_id ) ) {
		return '';
	}
	$all = ip_meta_rows( 'docs', $post_id );
	if ( count( $all ) <= max( 0, (int) ip_smart()['docs'] ) ) {
		return '';
	}
	$name = ip_meta( 'short', $post_id ) ? ip_meta( 'short', $post_id ) : get_the_title( $post_id );
	return ip_smart_cta( 'Ricevi la brochure e il piano di studi', $name, 'smart-more smart-docs' );
}

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
function ip_smart_trim_page( $html, $paras, $items, $more, $title = '', $drop = array() ) {
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
	$state = array( 'count' => 0, 'cut' => false, 'skip' => false, 'later' => array(), 'h2' => -1 );
	// Approfondimenti da rimandare al contatto: mai l'argomento della pagina stessa,
	// e mai tutte le sezioni (la pagina resterebbe vuota).
	$groups = ip_smart_groups();
	$own    = ip_smart_group_of( $title );
	$defer  = array();
	$all    = 0;
	foreach ( $doc->getElementsByTagName( 'h2' ) as $h ) {
		$all++;
		$g = ip_smart_group_of( $h->textContent );
		if ( $g && 'altro' !== $g && $g !== $own && in_array( $g, (array) $drop, true ) ) {
			$defer[ $all - 1 ] = $groups[ $g ][2]; // Per posizione: i nodi DOM non hanno un'identità stabile.
		}
	}
	if ( count( $defer ) >= $all ) {
		$defer = array();
	}
	// Sezioni-elenco lunghissime (es. il prezzo di ogni master): resta solo l'apertura.
	$blocks = array();
	$idx    = -1;
	foreach ( ( new DOMXPath( $doc ) )->query( '//h2 | //p | //ul | //ol | //table' ) as $x ) {
		if ( 'h2' === strtolower( $x->nodeName ) ) {
			$idx++;
			continue;
		}
		$par = $x->parentNode;
		if ( 'ipw' === $par->getAttribute( 'id' ) || false !== strpos( ' ' . $par->getAttribute( 'class' ) . ' ', ' ufficiale ' ) ) {
			$blocks[ $idx ] = ( $blocks[ $idx ] ?? 0 ) + 1;
		}
	}
	$long = array_keys( array_filter( $blocks, function ( $c ) {
		return $c > 24;
	} ) );
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
	$walk = function ( $parent ) use ( &$walk, &$state, $text, $paras, $items, $cta, $defer, $long ) {
		foreach ( iterator_to_array( $parent->childNodes ) as $n ) {
			if ( XML_ELEMENT_NODE !== $n->nodeType ) {
				continue;
			}
			$tag   = strtolower( $n->nodeName );
			$class = ' ' . $n->getAttribute( 'class' ) . ' ';
			if ( 'h2' === $tag ) {
				$cta( $n, $parent );
				$state['count'] = 0;
				$state['h2']++;
				$state['skip'] = isset( $defer[ $state['h2'] ] );
				if ( $state['skip'] ) {
					$state['later'][] = $defer[ $state['h2'] ];
					$parent->removeChild( $n );
				}
				continue;
			}
			// Sezione rimandata al contatto: via il testo, restano moduli e schede.
			if ( $state['skip'] && ( in_array( $tag, $text, true ) || in_array( $tag, array( 'h3', 'h4', 'h5', 'h6' ), true ) ) ) {
				$parent->removeChild( $n );
				continue;
			}
			// Contenitore dei testi ufficiali: si entra e si continua a contare.
			if ( 'div' === $tag && false !== strpos( $class, ' ufficiale ' ) ) {
				$walk( $n );
				continue;
			}
			if ( in_array( $tag, array( 'h3', 'h4', 'h5', 'h6' ), true ) ) {
				if ( $state['count'] >= $paras && in_array( $state['h2'], $long, true ) ) {
					$parent->removeChild( $n );
				}
				continue; // Altrimenti i sottotitoli restano: introducono dati che restano.
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
			// Oltre il limite: restano le righe brevi e tutto ciò che porta dati,
			// salvo nelle sezioni-elenco lunghissime.
			if ( in_array( $state['h2'], $long, true ) ) {
				$parent->removeChild( $n );
				$state['cut'] = true;
				continue;
			}
			if ( $list ) {
				if ( ip_smart_trim_list( $n, 0, true ) ) {
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
	return $out . ip_smart_box( $state['later'], $title );
}

add_filter( 'the_content', function ( $html ) {
	if ( ! ip_vis_front() || ! is_page() || ! in_the_loop() || ! is_main_query() || ! ip_smart_page_on( get_the_ID() ) ) {
		return $html;
	}
	$s = ip_smart();
	return ip_smart_trim_page( $html, max( 1, (int) $s['paras'] ), max( 2, (int) $s['items'] ), ip_smart_cta( 'Vuoi il dettaglio completo? Te lo inviamo noi', get_the_title(), 'smart-more', 'i dettagli' ), get_the_title(), (array) $s['drop'] );
}, 21 );
