<?php
/**
 * Google reviews (Trustindex plugin). Renders nothing if the plugin is not active.
 *
 * @var array $args eyebrow, title.
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_html = dh_reviews_html();
if ( '' === $dh_html ) {
	return;
}
?>
<section class="section reviews">
  <div class="wrap">
    <div class="section-head section-head--center">
      <p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $args['title'] ); ?></h2>
    </div>
    <div class="reviews__widget"><?php echo $dh_html; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
  </div>
</section>
