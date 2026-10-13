<?php
/**
 * Testimonial.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_t    = dh_content( 'testimonial' );
$dh_html = dh_reviews_html();
get_template_part( 'template-parts/hero', null, array( 'title' => get_the_title(), 'image' => 528, 'alt' => '' ) );
?>
<section class="section reviews">
  <div class="wrap">
    <div class="section-head section-head--center">
      <p class="eyebrow"><?php echo esc_html( $dh_t['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_t['title'] ); ?></h2>
    </div>
    <?php if ( '' !== $dh_html ) : ?>
      <div class="reviews__widget"><?php echo $dh_html; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <?php endif; ?>
    <?php if ( ! shortcode_exists( 'trustindex' ) && current_user_can( 'edit_theme_options' ) ) : ?>
      <p class="admin-note"><?php esc_html_e( 'Admins: these are the reviews saved in the theme. Activate the Trustindex plugin (or add a Trustindex widget ID in the Customizer) to show your live Google reviews here.', 'the-dental-hauz' ); ?></p>
    <?php endif; ?>
  </div>
</section>
<?php get_template_part( 'template-parts/book' ); ?>
