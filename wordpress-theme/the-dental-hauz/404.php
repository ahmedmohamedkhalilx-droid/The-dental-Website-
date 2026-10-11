<?php
/**
 * 404 page.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<div class="wrap not-found">
  <p class="eyebrow">404</p>
  <h1><?php esc_html_e( 'Page not found', 'the-dental-hauz' ); ?></h1>
  <p><?php esc_html_e( 'The page you were looking for has moved or no longer exists.', 'the-dental-hauz' ); ?></p>
  <a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'the-dental-hauz' ); ?></a>
</div>
<?php get_footer(); ?>
