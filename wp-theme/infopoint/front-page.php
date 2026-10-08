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
$eyebrow = ip_opt( 'hero_eyebrow' ) ? ip_text( 'hero_eyebrow' ) : ip_opt( 'brand' ) . ' · Agenzia partner UniMarconi' . ( ip_opt( 'city' ) ? ' a ' . ip_opt( 'city' ) : '' );
?>

<?php $hero_img = ip_hero_image_id(); ?>
<section class="hero hero-photo">
	<?php if ( $hero_img ) : ?>
		<?php echo wp_get_attachment_image( $hero_img, 'full', false, array( 'class' => 'hero-bg', 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ) ); ?>
	<?php endif; ?>
	<div class="wrap hero-in">
		<div class="hero-text">
			<p class="hero-kicker"><?php echo esc_html( $eyebrow ); ?></p>
			<h1><?php echo esc_html( ip_opt( 'hero_title' ) ); ?></h1>
			<p class="hero-lead"><?php echo esc_html( ip_opt( 'hero_text' ) ); ?></p>
			<?php if ( ip_rows( 'hero_ticks' ) ) : ?>
				<ul class="hero-points">
					<?php foreach ( ip_rows( 'hero_ticks' ) as $t ) : ?>
						<li><?php echo esc_html( $t['testo'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="hero-actions">
				<a class="btn btn-white" href="<?php echo esc_url( ip_courses_url() ); ?>">Tutti i corsi</a>
				<?php echo ip_phone_link( 'phone1', 'btn btn-line-light' ); // phpcs:ignore ?>
			</div>
			<?php if ( $price ) : ?>
				<p class="hero-price">Retta da <strong><?php echo esc_html( $price ); ?> al mese</strong> con le agevolazioni dell’Ateneo<?php if ( $agev ) : ?> · <a href="<?php echo esc_url( $agev ); ?>">vedi tutte</a><?php endif; ?></p>
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
			<section class="sec sec-alt">
				<div class="wrap">
					<header class="sec-head">
						<h2><?php echo esc_html( ip_opt( 't_offerta' ) ); ?></h2>
						<a class="more-link" href="<?php echo esc_url( ip_courses_url() ); ?>">Tutti i corsi</a>
					</header>
					<ul class="tiles">
						<?php foreach ( $tips as $t ) : ?>
							<?php $img = ip_term_image_id( $t ); ?>
							<li>
								<a href="<?php echo esc_url( get_term_link( $t ) ); ?>">
									<?php echo $img ? wp_get_attachment_image( $img, 'ip-card', false, array( 'loading' => 'lazy', 'alt' => '' ) ) : ''; ?>
									<span class="tile-label"><strong><?php echo esc_html( $t->name ); ?></strong><span><?php echo (int) $t->count; ?> <?php echo 1 === (int) $t->count ? 'corso' : 'corsi'; ?></span></span>
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
			<section class="sec">
				<div class="wrap">
					<header class="sec-head">
						<h2><?php echo esc_html( ip_opt( 't_featured' ) ); ?></h2>
						<a class="more-link" href="<?php echo esc_url( ip_courses_url() ); ?>">Cerca tra tutti i corsi</a>
					</header>
					<div class="cards">
						<?php
						while ( $featured->have_posts() ) {
							$featured->the_post();
							ip_course_card();
						}
						wp_reset_postdata();
						?>
					</div>
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
						<?php if ( $agev ) : ?><a class="more-link" href="<?php echo esc_url( $agev ); ?>">Tutte le agevolazioni</a><?php endif; ?>
					</header>
					<?php if ( ip_opt( 'agev_text' ) ) : ?><p class="sec-lead"><?php echo esc_html( ip_opt( 'agev_text' ) ); ?></p><?php endif; ?>
					<?php ip_agevolazioni_table( max( 1, (int) ip_opt( 'agev_count' ) ) ); ?>
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
			<section class="sec sec-alt">
				<div class="wrap promo">
					<div class="promo-text">
						<h2><?php echo esc_html( ip_opt( 'cfu_title' ) ); ?></h2>
						<p><?php echo esc_html( ip_opt( 'cfu_text' ) ); ?></p>
						<p><a class="btn btn-primary" href="<?php echo esc_url( $cfu ? $cfu : '#richiedi' ); ?>"><?php echo esc_html( ip_opt( 'cfu_button' ) ); ?></a></p>
						<p class="muted">Risposta via email, nessun impegno.</p>
					</div>
					<?php
					$promo = get_posts( array( 'post_type' => 'corso', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_thumbnail_id', 'tax_query' => array( array( 'taxonomy' => 'tipologia', 'field' => 'slug', 'terms' => 'laurea-magistrale' ) ), 'orderby' => 'title', 'order' => 'DESC' ) );
					$promo = $promo ? (int) get_post_thumbnail_id( $promo[0] ) : 0;
					if ( ! (int) ip_opt( 'cfu_image' ) && $promo ) :
						echo wp_get_attachment_image( $promo, 'medium_large', false, array( 'class' => 'promo-img', 'loading' => 'lazy', 'alt' => '' ) );
					elseif ( (int) ip_opt( 'cfu_image' ) ) :
						echo wp_get_attachment_image( (int) ip_opt( 'cfu_image' ), 'medium_large', false, array( 'class' => 'promo-img', 'loading' => 'lazy', 'alt' => '' ) );
					endif;
					?>
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
			<section class="sec">
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
						<?php if ( $blog ) : ?><a class="more-link" href="<?php echo esc_url( get_permalink( $blog ) ); ?>">Tutte le news</a><?php endif; ?>
					</header>
					<div class="ncards">
						<?php
						while ( $news->have_posts() ) :
							$news->the_post();
							?>
							<article class="ncard">
								<?php if ( has_post_thumbnail() ) : ?>
									<a class="ncard-img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'ip-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?></a>
								<?php endif; ?>
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							</article>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					</div>
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
