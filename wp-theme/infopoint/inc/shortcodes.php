<?php
/**
 * Shortcode per comporre le pagine dall'editor senza page builder.
 *
 * [ip_modulo tipo="info|cfu|callback" titolo="" testo=""]
 * [ip_corsi tipologia="laurea-triennale,laurea-magistrale" filtro="si"]
 * [ip_agevolazioni]
 * [ip_sedi]
 * [ip_contatti]
 * [ip_passi]
 * [ip_cta titolo=""]
 */

defined( 'ABSPATH' ) || exit;

function ip_buffer( $cb ) {
	ob_start();
	$cb();
	return ob_get_clean();
}

add_shortcode( 'ip_modulo', function ( $a ) {
	$a = shortcode_atts( array( 'tipo' => 'info', 'titolo' => '', 'testo' => '', 'corso' => 0, 'pulsante' => '' ), $a );
	return ip_buffer( function () use ( $a ) {
		ip_form( array( 'type' => $a['tipo'], 'title' => $a['titolo'], 'text' => $a['testo'], 'course' => $a['corso'], 'button' => $a['pulsante'] ) );
	} );
} );

add_shortcode( 'ip_corsi', function ( $a ) {
	$a = shortcode_atts( array( 'tipologia' => '', 'filtro' => 'si', 'limite' => 300 ), $a );
	return ip_buffer( function () use ( $a ) {
		ip_course_list( array_filter( array_map( 'trim', explode( ',', $a['tipologia'] ) ) ), 'no' !== $a['filtro'], (int) $a['limite'] );
	} );
} );

/**
 * Elenco corsi raggruppato per tipologia, con filtro testuale e per tipo.
 */
function ip_course_list( $slugs = array(), $filter = true, $limit = 300 ) {
	$tips = ip_tipologie();
	if ( $slugs ) {
		$tips = array_values( array_filter( $tips, function ( $t ) use ( $slugs ) {
			return in_array( $t->slug, $slugs, true );
		} ) );
	}
	if ( ! $tips ) {
		echo '<p>Nessun corso pubblicato.</p>';
		return;
	}
	?>
	<div class="clist" data-clist>
		<?php if ( $filter ) : ?>
			<div class="cfilter">
				<label class="cfilter-search">
					<?php echo ip_icon( 'search', 18 ); // phpcs:ignore ?>
					<span class="sr">Cerca un corso</span>
					<input type="search" placeholder="Cerca per nome o classe (es. psicologia, L-18)" data-cf-q>
				</label>
				<?php if ( count( $tips ) > 1 ) : ?>
					<div class="chips" role="group" aria-label="Filtra per tipologia">
						<button type="button" class="chip" aria-pressed="true" data-cf-t="">Tutti</button>
						<?php foreach ( $tips as $t ) : ?>
							<button type="button" class="chip" aria-pressed="false" data-cf-t="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?> <span><?php echo (int) $t->count; ?></span></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php foreach ( $tips as $t ) : ?>
			<?php
			$q = new WP_Query( array(
				'post_type'      => 'corso',
				'posts_per_page' => $limit,
				'no_found_rows'  => true,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'tax_query'      => array( array( 'taxonomy' => 'tipologia', 'terms' => $t->term_id, 'include_children' => false ) ),
			) );
			if ( ! $q->have_posts() ) {
				continue;
			}
			?>
			<section class="cgroup" data-group="<?php echo esc_attr( $t->slug ); ?>">
				<h2 class="cgroup-title"><?php echo esc_html( $t->name ); ?> <span><?php echo (int) $q->post_count; ?></span></h2>
				<?php if ( $t->description ) : ?>
					<p class="cgroup-desc"><?php echo esc_html( $t->description ); ?></p>
				<?php endif; ?>
				<ul class="crows">
					<?php
					while ( $q->have_posts() ) {
						$q->the_post();
						ip_course_row();
					}
					wp_reset_postdata();
					?>
				</ul>
			</section>
		<?php endforeach; ?>
		<p class="cempty" hidden>Nessun corso corrisponde alla ricerca. <a href="#richiedi" data-scroll-form>Chiedi a un orientatore</a>: l’offerta si aggiorna spesso.</p>
	</div>
	<?php
}

add_shortcode( 'ip_agevolazioni', function () {
	return ip_buffer( 'ip_agevolazioni' );
} );

function ip_agevolazioni( $limit = -1 ) {
	$q = get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => $limit, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
	if ( ! $q ) {
		return;
	}
	echo '<div class="agev">';
	foreach ( $q as $p ) {
		$retta = ip_meta( 'retta', $p->ID );
		$rata  = ip_meta( 'rata', $p->ID );
		$cond  = array_filter( array_map( 'trim', explode( "\n", ip_meta( 'cond', $p->ID ) ) ) );
		?>
		<article class="agev-item">
			<header>
				<h3><?php echo esc_html( get_the_title( $p ) ); ?></h3>
				<p class="agev-dest"><?php echo esc_html( ip_meta( 'dest', $p->ID ) ); ?></p>
			</header>
			<p class="agev-price">
				<?php if ( $rata ) : ?>
					<strong>€ <?php echo esc_html( $rata ); ?></strong><span>/mese</span>
					<?php if ( $retta ) : ?><small>€ <?php echo esc_html( number_format_i18n( (float) $retta ) ); ?> l’anno</small><?php endif; ?>
				<?php elseif ( $retta ) : ?>
					<strong>€ <?php echo esc_html( number_format_i18n( (float) $retta ) ); ?></strong><span>/anno</span>
				<?php endif; ?>
			</p>
			<?php if ( $cond ) : ?>
				<details>
					<summary>Condizioni</summary>
					<ul><?php foreach ( $cond as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?></ul>
				</details>
			<?php endif; ?>
			<a class="agev-cta" href="#richiedi" data-scroll-form data-interest="<?php echo esc_attr( get_the_title( $p ) ); ?>">Verifica se ne hai diritto <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
		</article>
		<?php
	}
	echo '</div>';
}

add_shortcode( 'ip_sedi', function () {
	$rows = array_filter( array_map( 'trim', explode( "\n", (string) ip_opt( 'exam_sites' ) ) ) );
	$by   = array();
	foreach ( $rows as $r ) {
		$p = array_map( 'trim', explode( '|', $r ) );
		if ( count( $p ) >= 3 ) {
			$by[ $p[0] ][] = $p;
		}
	}
	if ( ! $by ) {
		return '';
	}
	return ip_buffer( function () use ( $by ) {
		echo '<div class="sedi">';
		foreach ( $by as $region => $list ) {
			echo '<section><h3>' . esc_html( $region ) . '</h3><ul>';
			foreach ( $list as $s ) {
				printf(
					'<li><strong>%s</strong><a href="%s" target="_blank" rel="noopener">%s</a></li>',
					esc_html( $s[1] ),
					esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $s[2] . ' ' . $s[1] ) ),
					esc_html( $s[2] )
				);
			}
			echo '</ul></section>';
		}
		echo '</div>';
	} );
} );

add_shortcode( 'ip_contatti', function () {
	return ip_buffer( 'ip_contact_block' );
} );

function ip_contact_block() {
	$wa = ip_wa_href();
	?>
	<div class="contacts">
		<?php foreach ( array( 'phone1', 'phone2' ) as $p ) : ?>
			<?php if ( ip_opt( $p ) ) : ?>
				<a class="contact" href="<?php echo esc_attr( ip_tel_href( ip_opt( $p ) ) ); ?>" data-track="call">
					<?php echo ip_icon( 'phone', 22 ); // phpcs:ignore ?>
					<span><small><?php echo esc_html( ip_opt( $p . '_label' ) ); ?></small><?php echo esc_html( ip_opt( $p ) ); ?></span>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( $wa ) : ?>
			<a class="contact" href="<?php echo esc_url( $wa ); ?>" data-track="whatsapp" target="_blank" rel="noopener">
				<?php echo ip_icon( 'whatsapp', 22 ); // phpcs:ignore ?>
				<span><small>WhatsApp</small>Scrivici un messaggio</span>
			</a>
		<?php endif; ?>
		<?php if ( ip_opt( 'email' ) ) : ?>
			<a class="contact" href="mailto:<?php echo esc_attr( ip_opt( 'email' ) ); ?>" data-track="email">
				<?php echo ip_icon( 'mail', 22 ); // phpcs:ignore ?>
				<span><small>Email</small><?php echo esc_html( ip_opt( 'email' ) ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( ip_opt( 'address' ) ) : ?>
			<div class="contact">
				<?php echo ip_icon( 'pin', 22 ); // phpcs:ignore ?>
				<span><small>Sede</small><?php echo nl2br( esc_html( ip_opt( 'address' ) ) ); ?>
				<?php if ( ip_opt( 'maps_url' ) ) : ?><br><a href="<?php echo esc_url( ip_opt( 'maps_url' ) ); ?>" target="_blank" rel="noopener">Indicazioni stradali</a><?php endif; ?></span>
			</div>
		<?php endif; ?>
		<?php if ( ip_opt( 'address_exam' ) ) : ?>
			<div class="contact">
				<?php echo ip_icon( 'doc', 22 ); // phpcs:ignore ?>
				<span><small>Sede d’esame</small><?php echo nl2br( esc_html( ip_opt( 'address_exam' ) ) ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( ip_opt( 'hours' ) ) : ?>
			<div class="contact">
				<?php echo ip_icon( 'clock', 22 ); // phpcs:ignore ?>
				<span><small>Orari</small><?php echo nl2br( esc_html( ip_opt( 'hours' ) ) ); ?></span>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

add_shortcode( 'ip_passi', function () {
	return ip_buffer( 'ip_steps' );
} );

function ip_steps() {
	$steps = array(
		array( 'Ci racconti cosa cerchi', 'Una telefonata o un messaggio: titolo di studio, lavoro, tempo a disposizione, obiettivi.' ),
		array( 'Valutiamo la tua carriera', 'Se hai già sostenuto esami o hai titoli professionali, chiediamo gratis la prevalutazione dei crediti.' ),
		array( 'Scegli corso e retta', 'Ti mostriamo piano di studi, costi e agevolazioni a cui hai diritto. Decidi senza fretta.' ),
		array( 'Ci occupiamo dell’iscrizione', 'Prepariamo con te la domanda di immatricolazione e restiamo il tuo riferimento fino alla laurea.' ),
	);
	echo '<ol class="steps">';
	foreach ( $steps as $s ) {
		printf( '<li><h3>%s</h3><p>%s</p></li>', esc_html( $s[0] ), esc_html( $s[1] ) );
	}
	echo '</ol>';
}

add_shortcode( 'ip_cta', function ( $a ) {
	$a = shortcode_atts( array( 'titolo' => '' ), $a );
	return ip_buffer( function () use ( $a ) {
		ip_cta_band( $a['titolo'] );
	} );
} );
