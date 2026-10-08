<?php
defined( 'ABSPATH' ) || exit;
get_header();

$price = ip_price_from();
$agev  = ip_page_url( 'convenzioni-e-agevolazioni' );
$cfu   = ip_page_url( 'riconoscimento-cfu' );
$sedi  = ip_page_url( 'sedi-esame' );
?>

<section class="hero">
	<div class="wrap hero-in">
		<div class="hero-text">
			<p class="eyebrow"><?php echo esc_html( ip_opt( 'city' ) ? 'Infopoint UniMarconi · ' . ip_opt( 'city' ) : 'Infopoint Università Marconi' ); ?></p>
			<h1><?php echo esc_html( ip_opt( 'hero_title' ) ); ?></h1>
			<p class="hero-lead"><?php echo esc_html( ip_opt( 'hero_text' ) ); ?></p>
			<ul class="ticks">
				<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?>Iscrizioni aperte tutto l’anno, senza test d’ingresso</li>
				<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?>Lezioni online disponibili 24 ore su 24</li>
				<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?>Esami in presenza in sedi in tutta Italia</li>
				<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?>Valutazione gratuita degli esami già sostenuti</li>
			</ul>
			<div class="hero-actions">
				<?php echo ip_phone_link( 'phone1', 'btn btn-primary' ); // phpcs:ignore ?>
				<a class="btn btn-line" href="<?php echo esc_url( ip_courses_url() ); ?>">Vedi tutti i corsi</a>
			</div>
			<?php if ( $price ) : ?>
				<p class="hero-price">Retta da <strong><?php echo esc_html( $price ); ?> al mese</strong> con le agevolazioni<?php if ( $agev ) : ?> · <a href="<?php echo esc_url( $agev ); ?>">vedi tutte</a><?php endif; ?></p>
			<?php endif; ?>
		</div>
		<div class="hero-form">
			<?php ip_form(); ?>
		</div>
	</div>
</section>

<section class="facts" aria-label="L’ateneo in breve">
	<div class="wrap facts-in">
		<p><strong>2004</strong><span>prima università digitale riconosciuta dal MUR</span></p>
		<p><strong>Valore legale</strong><span>titoli validi per concorsi pubblici e ordini professionali</span></p>
		<p><strong>12 rate</strong><span>retta mensile senza interessi</span></p>
		<p><strong>Tutor</strong><span>un riferimento dall’iscrizione alla laurea</span></p>
	</div>
</section>

<?php $tips = ip_tipologie(); ?>
<?php if ( $tips ) : ?>
<section class="sec">
	<div class="wrap">
		<header class="sec-head">
			<h2>Offerta formativa</h2>
			<a href="<?php echo esc_url( ip_courses_url() ); ?>">Tutti i corsi <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
		</header>
		<ul class="tiplist">
			<?php foreach ( $tips as $t ) : ?>
				<li>
					<a href="<?php echo esc_url( get_term_link( $t ) ); ?>">
						<span class="tip-name"><?php echo esc_html( $t->name ); ?></span>
						<span class="tip-count"><?php echo (int) $t->count; ?> <?php echo 1 === (int) $t->count ? 'corso' : 'corsi'; ?></span>
						<?php echo ip_icon( 'arrow', 18 ); // phpcs:ignore ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<?php
$featured = new WP_Query( array(
	'post_type'      => 'corso',
	'posts_per_page' => 8,
	'no_found_rows'  => true,
	'meta_key'       => '_ip_featured',
	'meta_value'     => '1',
	'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
) );
if ( $featured->have_posts() ) :
	?>
<section class="sec sec-alt">
	<div class="wrap">
		<header class="sec-head">
			<h2>I corsi più richiesti</h2>
			<a href="<?php echo esc_url( ip_courses_url() ); ?>">Cerca tra tutti i corsi <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
		</header>
		<ul class="crows">
			<?php
			while ( $featured->have_posts() ) {
				$featured->the_post();
				ip_course_row();
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>
</section>
<?php endif; ?>

<?php
while ( have_posts() ) :
	the_post();
	if ( trim( get_the_content() ) ) :
		?>
<section class="sec">
	<div class="wrap prose">
		<?php the_content(); ?>
	</div>
</section>
		<?php
	endif;
endwhile;
?>

<?php if ( wp_count_posts( 'agevolazione' )->publish ) : ?>
<section class="sec">
	<div class="wrap">
		<header class="sec-head">
			<h2>Agevolazioni sulla retta</h2>
			<?php if ( $agev ) : ?><a href="<?php echo esc_url( $agev ); ?>">Tutte le agevolazioni <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a><?php endif; ?>
		</header>
		<p class="sec-lead">Le agevolazioni si applicano al momento dell’immatricolazione e non sono retroattive: verifichiamo con te quale ti spetta prima dell’iscrizione.</p>
		<?php ip_agevolazioni( 4 ); ?>
	</div>
</section>
<?php endif; ?>

<section class="sec sec-alt">
	<div class="wrap">
		<header class="sec-head"><h2>Come funziona l’iscrizione con noi</h2></header>
		<?php ip_steps(); ?>
	</div>
</section>

<?php if ( $cfu ) : ?>
<section class="sec">
	<div class="wrap split">
		<div>
			<h2>Hai già sostenuto esami all’università?</h2>
			<p>Esami di un percorso interrotto, una prima laurea, certificazioni o esperienza professionale possono valere crediti formativi. Con 30 CFU riconosciuti puoi iscriverti direttamente al secondo anno.</p>
		</div>
		<div class="split-cta">
			<a class="btn btn-primary" href="<?php echo esc_url( $cfu ); ?>">Richiedi la prevalutazione gratuita</a>
			<p class="muted">Risposta via email, nessun impegno.</p>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="sec sec-alt">
	<div class="wrap narrow">
		<header class="sec-head"><h2>Domande frequenti</h2></header>
		<div class="faq">
			<details>
				<summary>La laurea online UniMarconi ha lo stesso valore di una laurea tradizionale?</summary>
				<p>Sì. L’Università degli Studi Guglielmo Marconi è un ateneo riconosciuto dal Ministero dell’Università e della Ricerca dal 2004. I titoli hanno pieno valore legale: valgono per i concorsi pubblici, per l’accesso a master e abilitazioni e per l’iscrizione agli ordini professionali, dove previsto dal corso.</p>
			</details>
			<details>
				<summary>Dove si sostengono gli esami?</summary>
				<p>Gli esami si svolgono in presenza, nelle sedi d’esame autorizzate distribuite in tutta Italia. Prenoti l’appello dalla piattaforma e scegli la sede più comoda.<?php if ( $sedi ) : ?> <a href="<?php echo esc_url( $sedi ); ?>">Elenco delle sedi d’esame</a>.<?php endif; ?></p>
			</details>
			<details>
				<summary>Quando posso iscrivermi? Serve un test d’ingresso?</summary>
				<p>Ai corsi di laurea ad accesso libero ci si iscrive in qualsiasi periodo dell’anno, senza test d’ingresso. Dopo l’immatricolazione è previsto solo un test orientativo non selettivo, che serve a capire da dove partire.</p>
			</details>
			<details>
				<summary>Quanto costa?</summary>
				<p>La retta standard dei corsi di laurea è di € 2.760 l’anno, rateizzabile anche mensilmente senza costi aggiuntivi, ed è comprensiva di diritti di segreteria e tasse d’esame.<?php if ( $price ) : ?> Con le agevolazioni si parte da <?php echo esc_html( $price ); ?> al mese.<?php endif; ?> Tassa regionale e tassa di laurea sono a parte.</p>
			</details>
			<details>
				<summary>Lavoro: riuscirò a seguire le lezioni?</summary>
				<p>Le videolezioni e i materiali sono disponibili sulla piattaforma 24 ore su 24 e non c’è obbligo di frequenza. Organizzi lo studio sui tuoi orari e sostieni gli esami quando sei pronto.</p>
			</details>
			<details>
				<summary>Che cosa fa l’infopoint e quanto costa la consulenza?</summary>
				<p>Siamo un punto informativo per l’orientamento e le iscrizioni all’Università Marconi: ti aiutiamo a scegliere il corso, chiediamo per te la valutazione dei crediti, seguiamo l’immatricolazione e restiamo un riferimento durante il percorso. La consulenza è gratuita.</p>
			</details>
		</div>
	</div>
</section>

<?php
$news = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
if ( $news->have_posts() ) :
	$blog = (int) get_option( 'page_for_posts' );
	?>
<section class="sec">
	<div class="wrap">
		<header class="sec-head">
			<h2>Novità e scadenze</h2>
			<?php if ( $blog ) : ?><a href="<?php echo esc_url( get_permalink( $blog ) ); ?>">Tutte le news <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a><?php endif; ?>
		</header>
		<ul class="newslist">
			<?php
			while ( $news->have_posts() ) :
				$news->the_post();
				?>
				<li>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></time>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</li>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<?php ip_cta_band(); ?>

<?php
get_footer();
