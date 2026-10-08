<?php
defined( 'ABSPATH' ) || exit;
$landing = is_page_template( 'page-templates/landing.php' );
$wa      = ip_wa_href();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b3b74">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main">Vai al contenuto</a>

<?php if ( ! $landing ) : ?>
<div class="topbar">
	<div class="wrap topbar-in">
		<p>Iscrizioni aperte tutto l’anno · Valutazione dei crediti gratuita</p>
		<ul>
			<?php foreach ( array( 'phone1', 'phone2' ) as $p ) : ?>
				<?php if ( ip_opt( $p ) ) : ?>
					<li><a href="<?php echo esc_attr( ip_tel_href( ip_opt( $p ) ) ); ?>" data-track="call"><?php echo esc_html( ip_opt( $p . '_label' ) ); ?> <strong><?php echo esc_html( ip_opt( $p ) ); ?></strong></a></li>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( $wa ) : ?>
				<li><a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp">WhatsApp</a></li>
			<?php endif; ?>
		</ul>
	</div>
</div>
<?php endif; ?>

<header class="hdr">
	<div class="wrap hdr-in">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand-logo', 'alt' => esc_attr( ip_opt( 'brand' ) ), 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			<?php else : ?>
				<span class="brand-mark" aria-hidden="true">M</span>
				<span class="brand-text">
					<strong><?php echo esc_html( ip_opt( 'brand' ) ); ?></strong>
					<small>Infopoint Università Marconi<?php echo ip_opt( 'city' ) ? ' · ' . esc_html( ip_opt( 'city' ) ) : ''; ?></small>
				</span>
			<?php endif; ?>
		</a>

		<?php if ( $landing ) : ?>
			<?php echo ip_phone_link( 'phone1', 'hdr-phone' ); // phpcs:ignore ?>
		<?php else : ?>
			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav" data-nav-toggle>
				<?php echo ip_icon( 'menu', 22 ); // phpcs:ignore ?><span>Menu</span>
			</button>
			<nav id="nav" class="nav" aria-label="Principale">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'ip_menu_fallback',
				) );
				?>
				<a class="btn btn-accent nav-cta" href="#richiedi" data-scroll-form>Richiedi informazioni</a>
			</nav>
		<?php endif; ?>
	</div>
</header>

<main id="main">
