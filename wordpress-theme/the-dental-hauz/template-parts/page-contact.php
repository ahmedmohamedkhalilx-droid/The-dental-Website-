<?php
/**
 * Contact Us.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_k     = dh_content( 'contact' );
$dh_mail  = dh_clinic( 'email' );
$dh_tel   = dh_tel_href();
$dh_addr  = dh_address_lines();
$dh_hours = dh_hours();
$dh_map   = dh_map_src();
get_template_part( 'template-parts/hero', null, array( 'title' => get_the_title(), 'image' => 'clinic-storefront.jpg', 'alt' => '' ) );
?>
<section class="section contact-cards">
  <div class="wrap">
    <div class="section-head">
      <p class="eyebrow"><?php echo esc_html( $dh_k['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_k['title'] ); ?></h2>
    </div>
    <div class="boxes boxes--4">
      <?php foreach ( $dh_k['cards'] as $dh_card ) : ?>
        <?php
        $dh_lines = $dh_card['lines'];
        $dh_href  = '';
        switch ( $dh_card['title'] ) {
          case 'Email':
            $dh_lines = $dh_mail ? array( antispambot( $dh_mail ) ) : array();
            $dh_href  = $dh_mail ? 'mailto:' . antispambot( $dh_mail ) : '';
            break;
          case 'Location':
            $dh_lines = $dh_addr;
            $dh_href  = dh_clinic( 'maps_url' );
            break;
          case 'Call Us':
            $dh_lines = $dh_tel ? array( dh_clinic( 'phone' ) ) : array();
            $dh_href  = $dh_tel;
            break;
          case 'Working Hours':
            $dh_lines = array();
            foreach ( $dh_hours as $dh_row ) {
              $dh_lines[] = $dh_row[0] . ': ' . $dh_row[1];
            }
            break;
        }
        if ( ! $dh_lines ) {
          continue;
        }
        ?>
        <article class="box contact-card">
          <span class="contact-card__icon"><?php echo dh_img( $dh_card['image'], 'thumbnail', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
          <h3><?php echo esc_html( $dh_card['title'] ); ?></h3>
          <?php foreach ( $dh_lines as $dh_line ) : ?>
            <p><?php echo $dh_href ? '<a href="' . esc_url( $dh_href ) . '">' . esc_html( $dh_line ) . '</a>' : esc_html( $dh_line ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
          <?php endforeach; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section book" id="book">
  <div class="wrap book__grid">
    <div class="book__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_k['form']['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_k['form']['title'] ); ?></h2>
    </div>
    <?php dh_render_form( 'booking' ); ?>
  </div>
</section>

<?php if ( $dh_map ) : ?>
<section class="map">
  <iframe title="<?php esc_attr_e( 'Map: The Dental Hauz', 'the-dental-hauz' ); ?>" src="<?php echo esc_url( $dh_map ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
</section>
<?php endif; ?>
