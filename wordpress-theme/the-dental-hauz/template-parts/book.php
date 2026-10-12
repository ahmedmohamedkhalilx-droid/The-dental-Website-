<?php
/**
 * "Book An Appointment" section with the booking form.
 *
 * @var array $args eyebrow, title, suffix.
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_book = dh_content( 'home' )['book'];
$dh_eye  = isset( $args['eyebrow'] ) ? $args['eyebrow'] : $dh_book['eyebrow'];
$dh_ttl  = isset( $args['title'] ) ? $args['title'] : $dh_book['title'];
?>
<section class="section book" id="book">
  <div class="wrap book__grid">
    <div class="book__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_eye ); ?></p>
      <h2><?php echo esc_html( $dh_ttl ); ?></h2>
      <?php if ( dh_tel_href() ) : ?>
        <p><a class="book__phone" href="<?php echo esc_url( dh_tel_href() ); ?>"><?php echo esc_html( dh_clinic( 'phone' ) ); ?></a></p>
      <?php endif; ?>
    </div>
    <?php dh_render_form( 'booking' ); ?>
  </div>
</section>
