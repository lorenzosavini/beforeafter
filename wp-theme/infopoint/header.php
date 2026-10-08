<?php
defined( 'ABSPATH' ) || exit;
$landing = is_page_template( 'page-templates/landing.php' );
$wa      = ip_wa_href();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="<?php echo esc_attr( ip_opt( 'color_brand' ) ? ip_opt( 'color_brand' ) : '#225e48' ); ?>">
<?php if ( ip_opt( 'font_brand' ) ) : ?><link rel="preload" href="<?php echo esc_url( IP_URI . '/assets/fonts/montserrat-latin-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin><?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main">Vai al contenuto</a>

<?php if ( ip_opt( 'disclosure_bar' ) && ip_text( 'disclosure' ) ) : ?>
<div class="disclosure" role="note">
	<div class="wrap"><p><?php echo esc_html( ip_text( 'disclosure' ) ); ?><?php if ( ip_page_url( 'chi-siamo' ) ) : ?> <a href="<?php echo esc_url( ip_page_url( 'chi-siamo' ) ); ?>">Chi siamo</a><?php endif; ?></p></div>
</div>
<?php endif; ?>

<?php if ( ! $landing ) : ?>
<div class="topbar">
	<div class="wrap topbar-in">
		<p><?php echo esc_html( ip_opt( 'topbar_text' ) ); ?></p>
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
				<span class="brand-mark" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( ip_opt( 'brand' ), 0, 1 ) ) ); ?></span>
				<span class="brand-text">
					<strong><?php echo esc_html( ip_opt( 'brand' ) ); ?></strong>
					<small>Agenzia partner UniMarconi<?php echo ip_opt( 'city' ) ? ' · ' . esc_html( ip_opt( 'city' ) ) : ''; ?></small>
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
				<a class="btn btn-accent nav-cta" href="#richiedi" data-scroll-form><?php echo esc_html( ip_opt( 'nav_cta' ) ); ?></a>
			</nav>
		<?php endif; ?>
	</div>
</header>

<main id="main">
