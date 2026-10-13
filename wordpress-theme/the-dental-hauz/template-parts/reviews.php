<?php
/**
 * Google reviews (Trustindex plugin), with a link to the Google listing when the plugin is not active.
 * "compact" gives the smaller home-page version with a link to the Testimonial page.
 *
 * @var array $args eyebrow, title, compact (bool).
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_compact = ! empty( $args['compact'] );
$dh_html    = dh_reviews_html( $dh_compact ? 'home' : 'full' );
if ( '' === $dh_html ) {
	$dh_html = dh_reviews_fallback();
}
if ( '' === $dh_html ) {
	return;
}
?>
<section class="section reviews<?php echo $dh_compact ? ' reviews--compact' : ''; ?>">
  <div class="wrap">
    <div class="section-head section-head--center">
      <p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $args['title'] ); ?></h2>
    </div>
    <div class="reviews__widget"><?php echo $dh_html; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <?php if ( $dh_compact ) : ?>
      <p class="reviews__all"><a class="btn btn--ghost" href="<?php echo esc_url( dh_page_url( 'testimonial' ) ); ?>"><?php esc_html_e( 'See all reviews', 'the-dental-hauz' ); ?></a></p>
    <?php endif; ?>
  </div>
</section>
