<?php
/**
 * Site footer.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_c     = dh_content();
$dh_hours = dh_hours();
$dh_addr  = dh_address_lines();
$dh_mail  = dh_clinic( 'email' );
$dh_tel   = dh_tel_href();
?>
</main>

<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <?php dh_logo( 'light' ); ?>
        <p><?php esc_html_e( 'The Dental Hauz offers trusted dental care in Kennedy Road, Markham, ON. Book your appointment today for personalized preventive, cosmetic, and restorative dentistry.', 'the-dental-hauz' ); ?></p>
      </div>
      <div>
        <h4><?php esc_html_e( 'Important Link', 'the-dental-hauz' ); ?></h4>
        <ul>
          <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( dh_page_url( 'about-us' ) ); ?>"><?php esc_html_e( 'About Us', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( dh_page_url( 'doctors' ) ); ?>"><?php esc_html_e( 'Doctors', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( dh_page_url( 'our-services' ) ); ?>"><?php esc_html_e( 'Our Services', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( dh_page_url( 'testimonial' ) ); ?>"><?php esc_html_e( 'Testimonial', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( dh_page_url( 'faqs' ) ); ?>"><?php esc_html_e( "FAQ's", 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( dh_page_url( 'forms' ) ); ?>"><?php esc_html_e( 'Forms', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( dh_page_url( 'contact-us' ) ); ?>"><?php esc_html_e( 'Contact Us', 'the-dental-hauz' ); ?></a></li>
        </ul>
      </div>
      <div>
        <h4><?php esc_html_e( 'Our Services', 'the-dental-hauz' ); ?></h4>
        <ul>
          <?php foreach ( $dh_c['services'] as $dh_s ) : ?>
            <li><a href="<?php echo esc_url( dh_page_url( $dh_s['slug'] ) ); ?>"><?php echo esc_html( $dh_c['service_pages'][ $dh_s['slug'] ]['title'] ); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4><?php esc_html_e( 'Get in Touch', 'the-dental-hauz' ); ?></h4>
        <ul>
          <?php if ( $dh_tel ) : ?><li><a href="<?php echo esc_url( $dh_tel ); ?>"><?php echo esc_html( dh_clinic( 'phone' ) ); ?></a></li><?php endif; ?>
          <?php if ( $dh_mail ) : ?><li><a href="mailto:<?php echo esc_attr( antispambot( $dh_mail ) ); ?>"><?php echo esc_html( antispambot( $dh_mail ) ); ?></a></li><?php endif; ?>
          <?php if ( $dh_addr ) : ?><li><a href="<?php echo esc_url( dh_clinic( 'maps_url' ) ); ?>"><?php echo esc_html( implode( ', ', $dh_addr ) ); ?></a></li><?php endif; ?>
        </ul>
        <?php echo dh_social_links( 'social social--footer' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php if ( $dh_hours ) : ?>
          <h4 class="footer-sub"><?php esc_html_e( 'Working Hours', 'the-dental-hauz' ); ?></h4>
          <ul class="hours">
            <?php foreach ( $dh_hours as $dh_row ) : ?>
              <li><span><?php echo esc_html( $dh_row[0] ); ?></span><span><?php echo esc_html( $dh_row[1] ); ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
    <div class="footer-base">
      <span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All Rights Reserved.', 'the-dental-hauz' ); ?></span>
      <span class="tag"><?php esc_html_e( 'Your smile · Your experience · Your Hauz', 'the-dental-hauz' ); ?></span>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
