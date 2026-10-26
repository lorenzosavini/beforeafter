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

/**
 * Via i titoli rimasti senza contenuto e le frasi sospese: un paragrafo che
 * finisce con i due punti senza nulla dopo (l'elenco manca anche alla fonte o è
 * stato tolto) perde l'ultima frase, o sparisce se è tutto lì.
 */
function ip_smart_drop_empty( $html ) {
	$html = preg_replace_callback( '#(<p\b[^>]*>)((?:(?!</p>).)*?)</p>(?=\s*(?:<p class="smart-more|<h2\b|<aside class="smart-box|$))#isu', function ( $m ) {
		if ( ! preg_match( '/:\s*(?:<[^>]+>\s*)*$/u', $m[2] ) ) {
			return $m[0];
		}
		if ( preg_match( '/^(.*[.!?;])\s+[^.!?;]*:\s*(?:<[^>]+>\s*)*$/su', $m[2], $k ) ) {
			return $m[1] . force_balance_tags( $k[1] ) . '</p>';
		}
		return '';
	}, $html );
	return preg_replace( '#<h2\b[^>]*>(?:(?!</h2>).)*</h2>\s*(?=<h2\b|$)#isu', '', $html );
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
 * Accorcia un paragrafo a fine frase, intorno a $max parole (almeno una frase).
 * Tiene grassetti e corsivi. Restituisce true se ha tagliato.
 */
function ip_smart_cut_sentences( $node, $max ) {
	$doc  = $node->ownerDocument;
	$html = '';
	foreach ( $node->childNodes as $c ) {
		$html .= $doc->saveHTML( $c );
	}
	if ( preg_match( '#<(?!/?(strong|b|em|i|br|a|span)\b)[a-z]#i', $html ) ) {
		return false; // Altri elementi dentro (immagini, elenchi): meglio non toccare.
	}
	preg_match_all( '/[.!?;](?:<\/(?:strong|b|em|i|a|span)>)*(?=(?:\s|<br\s*\/?>)+(?:<[^>]+>)*[A-ZÀ-ÝÈÉ«"“(\d])/u', $html, $m, PREG_OFFSET_CAPTURE );
	$first = isset( $m[0][0] ) ? str_word_count( wp_strip_all_tags( substr( $html, 0, $m[0][0][1] ) ) ) : PHP_INT_MAX;
	if ( $first > $max * 1.6 ) {
		// Frase interminabile: si chiude a una virgola o a un punto e virgola, con i puntini.
		$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( str_replace( array( '<br>', '<br/>', '<br />' ), ' ', $html ) ) ) );
		$w    = preg_split( '/\s+/u', $text );
		if ( count( $w ) <= $max * 1.3 ) {
			return false;
		}
		$part = implode( ' ', array_slice( $w, 0, $max ) );
		$pos  = max( mb_strrpos( $part, ',' ), mb_strrpos( $part, ';' ), mb_strrpos( $part, ' –' ) );
		if ( $pos && $pos > mb_strlen( $part ) * 0.5 ) {
			$part = mb_substr( $part, 0, $pos );
		}
		// Mai dentro una parentesi rimasta aperta.
		if ( substr_count( $part, '(' ) > substr_count( $part, ')' ) ) {
			$part = mb_substr( $part, 0, mb_strrpos( $part, '(' ) );
		}
		while ( $node->firstChild ) {
			$node->removeChild( $node->firstChild );
		}
		$node->appendChild( $doc->createTextNode( rtrim( $part, ' ,;:–-' ) . '…' ) );
		return true;
	}
	if ( empty( $m[0] ) ) {
		return false;
	}
	$cut = 0;
	foreach ( $m[0] as $b ) {
		$end = $b[1] + strlen( $b[0] );
		$w   = str_word_count( wp_strip_all_tags( substr( $html, 0, $end ) ), 0, 'àèéìòùÀÈÉÌÒÙ’\'' );
		if ( ! $cut || $w <= $max ) {
			$cut = $end;
		}
		if ( $w >= $max ) {
			break;
		}
	}
	if ( ! $cut || $cut >= strlen( rtrim( $html ) ) - 2 ) {
		return false;
	}
	$frag = new DOMDocument();
	libxml_use_internal_errors( true );
	$frag->loadHTML( '<?xml encoding="utf-8"?><div>' . force_balance_tags( substr( $html, 0, $cut ) ) . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	while ( $node->firstChild ) {
		$node->removeChild( $node->firstChild );
	}
	foreach ( iterator_to_array( $frag->getElementsByTagName( 'div' )->item( 0 )->childNodes ) as $c ) {
		$node->appendChild( $doc->importNode( $c, true ) );
	}
	return true;
}

function ip_smart_words( $node ) {
	return str_word_count( trim( preg_replace( '/\s+/u', ' ', $node->textContent ) ), 0, 'àèéìòùÀÈÉÌÒÙ’\'' );
}

/** Voce di un elenco scritta come paragrafo («1. …», «– …»). */
function ip_smart_pseudo_item( $node ) {
	return 'p' === strtolower( $node->nodeName ) && preg_match( '/^\s*(\d{1,2}[.)]|[a-z]\)|[–\-•·])\s/u', $node->textContent );
}

/** Righe di servizio rimaste dall'importazione (link dell'Ateneo senza contesto). */
function ip_smart_junk( $node ) {
	return (bool) preg_match( '/^(immatricolazione|tasse e modalit[aà] di pagamento|procedure? d.iscrizione|modalit[aà] di pagamento)$/iu', trim( $node->textContent ) );
}

/** Frase che introduce un elenco («Il corso prepara a:»): non conta come paragrafo. */
function ip_smart_lead_in( $node ) {
	$t = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
	if ( 'p' !== strtolower( $node->nodeName ) ) {
		return false;
	}
	// Anche un'etichetta breve senza punto finale («Struttura del corso») non conta.
	return ( preg_match( '/:$/u', $t ) && mb_strlen( $t ) <= 220 ) || ( mb_strlen( $t ) <= 40 && ! preg_match( '/[.!?;]$/u', $t ) );
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
		} elseif ( ip_smart_words( $c ) > 28 && ip_smart_cut_sentences( $c, 18 ) ) {
			$cut = true; // Voce lunghissima: resta la prima frase.
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
function ip_smart_trim( $html, $paras, $items, $short = 0, $modules = false, $data = false, $words = 0 ) {
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
	// Programmi con sottotitoli sia per i moduli sia per gli argomenti: restano i «Modulo …».
	$modh3 = false;
	$nh3   = 0;
	if ( $modules ) {
		foreach ( $root->getElementsByTagName( 'h3' ) as $h ) {
			if ( preg_match( '/^\s*(modulo|module|unit[aà]|parte|sezione|area)\b/iu', $h->textContent ) ) {
				$modh3 = true;
				break;
			}
		}
	}
	if ( $named && ! $root->getElementsByTagName( 'li' )->length && ! $root->getElementsByTagName( 'table' )->length ) {
		// Programma fatto di soli paragrafi: i titoli degli argomenti sono le righe brevi.
		$named = false;
		$allow = 12;
		$short = min( $short ? $short : 60, 60 );
	}
	$extra  = 0;     // Righe con dati tenute oltre il limite (con $words: al massimo 2).
	$wc     = 0;     // Parole già mostrate (con $words: si mostra circa quel numero di parole).
	$lead   = false; // L'ultimo testo rimasto annuncia un elenco («…in grado di:»).
	$pseudo = -1;    // Voci-paragrafo ancora ammesse nell'elenco in corso.
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
				// Solo i moduli: via gli argomenti elencati sotto ciascuno.
				foreach ( iterator_to_array( $n->getElementsByTagName( 'li' ) ) as $li ) {
					foreach ( iterator_to_array( $li->childNodes ) as $sub ) {
						if ( XML_ELEMENT_NODE === $sub->nodeType && in_array( strtolower( $sub->nodeName ), array( 'ul', 'ol' ), true ) ) {
							$li->removeChild( $sub );
							$cut = true;
						}
					}
				}
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
			$full = $words ? $wc >= $words : $count >= $paras;
			$head = $full && ! $modules ? $n : null; // Nei programmi i titoli dei moduli restano.
			if ( $modules && ( ( $modh3 && ! preg_match( '/^\s*(modulo|module|unit[aà]|parte|sezione|area)\b/iu', $n->textContent ) ) || ( ! $modh3 && ++$nh3 > 8 ) ) ) {
				$root->removeChild( $n );
				$cut   = true;
				$count = max( $count, $paras );
				$allow = 0;
				continue;
			}
			if ( $modules ) {
				// Sotto un «Modulo 1» resta una riga col nome; se il titolo ha già il nome, niente.
				$count = max( $count, $paras );
				$allow = mb_strlen( trim( $n->textContent ) ) <= 15 ? 1 : 0;
			}
			continue;
		}
		if ( 'p' === $tag && ip_smart_junk( $n ) ) {
			$root->removeChild( $n );
			continue;
		}
		// Voci-paragrafo («1. …») di un elenco già iniziato: fino a $items.
		if ( $pseudo >= 0 && ip_smart_pseudo_item( $n ) ) {
			if ( $pseudo > 0 && ( ! $words || $wc < $words * 1.8 ) ) {
				$pseudo--;
				if ( ip_smart_words( $n ) > 35 && ip_smart_cut_sentences( $n, 22 ) ) {
					$cut = true;
				}
				$wc += ip_smart_words( $n );
			} else {
				$pseudo = 0;
				$root->removeChild( $n );
				$cut = true;
			}
			continue;
		}
		$pseudo = -1;
		// Ciò che il testo rimasto annuncia con i due punti resta sempre, accorciato
		// (non nei costi: il dettaglio di rate e scadenze si dà dopo il contatto).
		if ( $lead && ! $modules && ! $data ) {
			$lead = false;
			$head = null;
			if ( $list ) {
				$cut = ip_smart_trim_list( $n, min( $items, 3 ), $data ) || $cut;
				$wc += ip_smart_words( $n );
				if ( $words && $wc >= $words ) {
					$count = max( $count, $paras );
				}
				continue;
			}
			if ( 'p' === $tag ) {
				if ( ip_smart_pseudo_item( $n ) ) {
					$pseudo = min( $items, 3 ) - 1;
					if ( ip_smart_words( $n ) > 35 && ip_smart_cut_sentences( $n, 22 ) ) {
						$cut = true;
					}
				} else {
					if ( $words && ip_smart_words( $n ) > 35 && ip_smart_cut_sentences( $n, 30 ) ) {
						$cut = true;
					}
					$lead = (bool) preg_match( '/:\s*$/u', trim( $n->textContent ) );
				}
				$wc += ip_smart_words( $n );
				if ( $words && $wc >= $words ) {
					$count = max( $count, $paras );
				}
				continue;
			}
		}
		$lead = false;
		$room = $words ? $wc < $words : $count < $paras;
		if ( $room && ! ( $modules && $list ) ) {
			if ( $list && ip_smart_trim_list( $n, $items, $data ) ) {
				$cut = true;
			}
			// Paragrafo troppo lungo per lo spazio rimasto: si chiude a fine frase.
			if ( $words && 'p' === $tag && $wc + ip_smart_words( $n ) > $words * 1.5 && ip_smart_cut_sentences( $n, max( 12, $words - $wc ) ) ) {
				$cut = true;
			}
			$wc += ip_smart_words( $n );
			if ( ! ip_smart_lead_in( $n ) ) {
				$count++;
			}
			if ( $words && $wc >= $words ) {
				$count = max( $count, $paras );
			}
			$lead = ! $list && (bool) preg_match( '/:\s*$/u', trim( $n->textContent ) );
			$head = null;
			continue;
		}
		$keep = false;
		if ( 'table' === $tag ) {
			$keep = $data && ( ! $words || $extra++ < 2 );
		} elseif ( $list ) {
			if ( $data && ! $modules && ! $words ) {
				$cut  = ip_smart_trim_list( $n, 0, true ) || $cut;
				$keep = (bool) $n->getElementsByTagName( 'li' )->length;
				$extra++;
			}
		} else {
			$txt  = trim( preg_replace( '/\s+/u', ' ', $n->textContent ) );
			$len  = mb_strlen( $txt );
			$link = $n->getElementsByTagName( 'a' )->length && trim( preg_replace( '/\s+/u', ' ', $n->getElementsByTagName( 'a' )->item( 0 )->textContent ) ) === $txt;
			$keep = ( $short && $len && $len <= $short && ! $link && ( ! $modules || $allow-- > 0 ) ) || ( $data && ( ! $words || $extra++ < 2 ) && ip_smart_essential( $n ) );
		}
		if ( $keep && $words && 'p' === $tag && ip_smart_words( $n ) > 30 && ip_smart_cut_sentences( $n, 22 ) ) {
			$cut = true; // Riga con dati oltre il limite: resta la prima frase.
		}
		if ( $keep ) {
			$head = null;
			$lead = ! $list && (bool) preg_match( '/:\s*$/u', trim( $n->textContent ) );
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
		// Testo senza titoli: nella scheda corso sono le descrizioni dei curricula.
		list( $t, $cut ) = ip_smart_trim( $html, $p, $it, 0, false, false, 70 * $p );
		return ip_smart_drop_empty( $t ) . ( $cut ? ip_smart_cta( $kinds['programma'][1], $name, 'smart-more', 'il piano di studi' ) : '' );
	}
	// Testo prima del primo titolo: la presentazione.
	list( $out, $cut ) = ip_smart_trim( substr( $html, 0, $m[0][0][1] ), $p, $it, 0, false, false, 80 * $p );
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
				list( $body, $cut ) = ip_smart_trim( $body, 1, $it, 0, false, true, 45 );
				break;
			case 'presentazione':
				list( $body, $cut ) = ip_smart_trim( $body, $p, $it, 0, false, false, 80 * $p );
				break;
			case 'obiettivi':
				list( $body, $cut ) = ip_smart_trim( $body, $p, $it, 0, false, false, 60 * $p );
				break;
			default:
				list( $body, $cut ) = ip_smart_trim( $body, 1, $it, 0, false, false, 55 );
		}
		$out .= $m[0][ $i ][0] . $body . ( $cut ? ip_smart_cta( $label, $name, 'smart-more', trim( wp_strip_all_tags( $m[1][ $i ][0] ) ) ) : '' );
	}
	return ip_smart_drop_empty( $out ) . ip_smart_box( $later, $name );
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
	$state = array( 'count' => 0, 'cut' => false, 'skip' => false, 'later' => array(), 'h2' => -1, 'lead' => false );
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
				$state['lead'] = false;
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
			// Un elenco annunciato dal testo rimasto («segui questi passaggi:») resta, accorciato.
			if ( $state['lead'] && $list ) {
				$state['lead'] = false;
				if ( ip_smart_trim_list( $n, $items ) ) {
					$state['cut'] = true;
				}
				continue;
			}
			$state['lead'] = ! $list && preg_match( '/:\s*$/u', trim( $n->textContent ) );
			if ( $state['count'] < $paras ) {
				if ( $list && ip_smart_trim_list( $n, $items ) ) {
					$state['cut'] = true;
				}
				if ( ! ip_smart_lead_in( $n ) ) {
					$state['count']++;
				}
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
			$state['lead'] = false;
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
	return ip_smart_drop_empty( $out ) . ip_smart_box( $state['later'], $title );
}

add_filter( 'the_content', function ( $html ) {
	if ( ! ip_vis_front() || ! is_page() || ! in_the_loop() || ! is_main_query() || ! ip_smart_page_on( get_the_ID() ) ) {
		return $html;
	}
	$s = ip_smart();
	return ip_smart_trim_page( $html, max( 1, (int) $s['paras'] ), max( 2, (int) $s['items'] ), ip_smart_cta( 'Vuoi il dettaglio completo? Te lo inviamo noi', get_the_title(), 'smart-more', 'i dettagli' ), get_the_title(), (array) $s['drop'] );
}, 21 );
