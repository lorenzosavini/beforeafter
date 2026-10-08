<?php
/**
 * Home: ogni testo e ogni sezione si gestisce da Infopoint → Impostazioni →
 * Home page. Il contenuto scritto nell'editor della pagina Home viene
 * mostrato nella posizione «Contenuto della pagina».
 */
defined( 'ABSPATH' ) || exit;
get_header();

$price   = ip_price_from();
$agev    = ip_page_url( 'convenzioni-e-agevolazioni' );
$cfu     = ip_page_url( 'riconoscimento-cfu' );
$eyebrow = ip_opt( 'hero_eyebrow' ) ? ip_opt( 'hero_eyebrow' ) : ( ip_opt( 'city' ) ? 'Infopoint UniMarconi · ' . ip_opt( 'city' ) : 'Infopoint Università Marconi' );
$arrow   = ip_icon( 'arrow', 16 );
?>

<section class="hero">
	<div class="wrap hero-in">
		<div class="hero-text">
			<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h1><?php echo esc_html( ip_opt( 'hero_title' ) ); ?></h1>
			<p class="hero-lead"><?php echo esc_html( ip_opt( 'hero_text' ) ); ?></p>
			<?php if ( ip_rows( 'hero_ticks' ) ) : ?>
				<ul class="ticks">
					<?php foreach ( ip_rows( 'hero_ticks' ) as $t ) : ?>
						<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?><?php echo esc_html( $t['testo'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
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

<?php if ( ip_rows( 'facts' ) ) : ?>
<section class="facts" aria-label="L’ateneo in breve">
	<div class="wrap facts-in">
		<?php foreach ( ip_rows( 'facts' ) as $f ) : ?>
			<p><strong><?php echo esc_html( $f['titolo'] ); ?></strong><span><?php echo esc_html( $f['testo'] ); ?></span></p>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<?php
foreach ( (array) ip_opt( 'sections' ) as $section ) :
	switch ( $section ) :

		case 'offerta':
			$tips = ip_tipologie();
			if ( ! $tips ) {
				break;
			}
			?>
			<section class="sec">
				<div class="wrap">
					<header class="sec-head">
						<h2><?php echo esc_html( ip_opt( 't_offerta' ) ); ?></h2>
						<a href="<?php echo esc_url( ip_courses_url() ); ?>">Tutti i corsi <?php echo $arrow; // phpcs:ignore ?></a>
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
			<?php
			break;

		case 'featured':
			$featured = new WP_Query( array(
				'post_type'      => 'corso',
				'posts_per_page' => 10,
				'no_found_rows'  => true,
				'meta_key'       => '_ip_featured',
				'meta_value'     => '1',
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			) );
			if ( ! $featured->have_posts() ) {
				break;
			}
			?>
			<section class="sec sec-alt">
				<div class="wrap">
					<header class="sec-head">
						<h2><?php echo esc_html( ip_opt( 't_featured' ) ); ?></h2>
						<a href="<?php echo esc_url( ip_courses_url() ); ?>">Cerca tra tutti i corsi <?php echo $arrow; // phpcs:ignore ?></a>
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
			<?php
			break;

		case 'contenuto':
			while ( have_posts() ) {
				the_post();
				if ( trim( get_the_content() ) ) {
					echo '<section class="sec"><div class="wrap prose">';
					the_content();
					echo '</div></section>';
				}
			}
			break;

		case 'agevolazioni':
			if ( ! wp_count_posts( 'agevolazione' )->publish ) {
				break;
			}
			?>
			<section class="sec">
				<div class="wrap">
					<header class="sec-head">
						<h2><?php echo esc_html( ip_opt( 't_agev' ) ); ?></h2>
						<?php if ( $agev ) : ?><a href="<?php echo esc_url( $agev ); ?>">Tutte le agevolazioni <?php echo $arrow; // phpcs:ignore ?></a><?php endif; ?>
					</header>
					<?php if ( ip_opt( 'agev_text' ) ) : ?><p class="sec-lead"><?php echo esc_html( ip_opt( 'agev_text' ) ); ?></p><?php endif; ?>
					<?php ip_agevolazioni( max( 1, (int) ip_opt( 'agev_count' ) ) ); ?>
				</div>
			</section>
			<?php
			break;

		case 'passi':
			?>
			<section class="sec sec-alt">
				<div class="wrap">
					<header class="sec-head"><h2><?php echo esc_html( ip_opt( 't_passi' ) ); ?></h2></header>
					<?php ip_steps(); ?>
				</div>
			</section>
			<?php
			break;

		case 'cfu':
			?>
			<section class="sec">
				<div class="wrap split">
					<div>
						<h2><?php echo esc_html( ip_opt( 'cfu_title' ) ); ?></h2>
						<p><?php echo esc_html( ip_opt( 'cfu_text' ) ); ?></p>
					</div>
					<div class="split-cta">
						<a class="btn btn-primary" href="<?php echo esc_url( $cfu ? $cfu : '#richiedi' ); ?>"><?php echo esc_html( ip_opt( 'cfu_button' ) ); ?></a>
						<p class="muted">Risposta via email, nessun impegno.</p>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'recensioni':
			$reviews = ip_rows( 'reviews' );
			if ( ! $reviews ) {
				break;
			}
			?>
			<section class="sec">
				<div class="wrap">
					<header class="sec-head"><h2><?php echo esc_html( ip_opt( 't_reviews' ) ); ?></h2></header>
					<div class="reviews">
						<?php foreach ( $reviews as $r ) : ?>
							<?php $stars = min( 5, max( 0, (int) $r['voto'] ) ); ?>
							<figure class="review">
								<?php if ( $stars ) : ?><p class="review-stars" aria-label="<?php echo esc_attr( $stars ); ?> su 5"><?php echo esc_html( str_repeat( '★', $stars ) . str_repeat( '☆', 5 - $stars ) ); ?></p><?php endif; ?>
								<blockquote><?php echo esc_html( $r['testo'] ); ?></blockquote>
								<figcaption><strong><?php echo esc_html( $r['nome'] ); ?></strong><?php if ( $r['corso'] ) : ?> · <?php echo esc_html( $r['corso'] ); ?><?php endif; ?></figcaption>
							</figure>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'faq':
			if ( ! ip_rows( 'faq' ) ) {
				break;
			}
			?>
			<section class="sec sec-alt">
				<div class="wrap narrow">
					<header class="sec-head"><h2><?php echo esc_html( ip_opt( 't_faq' ) ); ?></h2></header>
					<?php ip_faq_list( ip_rows( 'faq' ) ); ?>
				</div>
			</section>
			<?php
			break;

		case 'news':
			$news = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
			if ( ! $news->have_posts() ) {
				break;
			}
			$blog = (int) get_option( 'page_for_posts' );
			?>
			<section class="sec">
				<div class="wrap">
					<header class="sec-head">
						<h2><?php echo esc_html( ip_opt( 't_news' ) ); ?></h2>
						<?php if ( $blog ) : ?><a href="<?php echo esc_url( get_permalink( $blog ) ); ?>">Tutte le news <?php echo $arrow; // phpcs:ignore ?></a><?php endif; ?>
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
			<?php
			break;

		case 'cta':
			ip_cta_band();
			break;

	endswitch;
endforeach;

get_footer();
