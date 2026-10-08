<?php $ip_sid = 's-' . wp_unique_id(); ?>
<form role="search" method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr" for="<?php echo esc_attr( $ip_sid ); ?>">Cerca</label>
	<input type="search" id="<?php echo esc_attr( $ip_sid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Cerca nel sito">
	<button type="submit" class="btn btn-primary">Cerca</button>
</form>
