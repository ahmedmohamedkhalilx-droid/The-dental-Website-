<?php
/**
 * Site footer.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_hours = dh_hours();
$home     = home_url( '/' );
?>
</main>

<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <?php dh_logo( 'light' ); ?>
        <p><?php esc_html_e( 'Implants, oral surgery and modern dentistry in a clinic that feels like home.', 'the-dental-hauz' ); ?></p>
      </div>
      <div>
        <h4><?php esc_html_e( 'Treatments', 'the-dental-hauz' ); ?></h4>
        <ul>
          <li><a href="<?php echo esc_url( $home . '#implants' ); ?>"><?php esc_html_e( 'Dental Implants', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( $home . '#services' ); ?>"><?php esc_html_e( 'Wisdom Teeth', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( $home . '#services' ); ?>"><?php esc_html_e( 'Clear Aligners', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( $home . '#services' ); ?>"><?php esc_html_e( 'Veneers & Crowns', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( $home . '#services' ); ?>"><?php esc_html_e( 'Whitening', 'the-dental-hauz' ); ?></a></li>
        </ul>
      </div>
      <div>
        <h4><?php esc_html_e( 'Patients', 'the-dental-hauz' ); ?></h4>
        <ul>
          <li><a href="<?php echo esc_url( $home . '#visit' ); ?>"><?php esc_html_e( 'Your Visit', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( $home . '#faq' ); ?>"><?php esc_html_e( 'FAQ', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( $home . '#book' ); ?>"><?php esc_html_e( 'Book a Consultation', 'the-dental-hauz' ); ?></a></li>
          <li><a href="<?php echo esc_url( $home . '#about' ); ?>"><?php esc_html_e( 'About the Hauz', 'the-dental-hauz' ); ?></a></li>
        </ul>
      </div>
      <?php if ( $dh_hours ) : ?>
      <div>
        <h4><?php esc_html_e( 'Hours', 'the-dental-hauz' ); ?></h4>
        <ul class="hours">
          <?php foreach ( $dh_hours as $row ) : ?>
            <li><span><?php echo esc_html( $row[0] ); ?></span><span><?php echo esc_html( $row[1] ); ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
    <div class="footer-base">
      <span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'the-dental-hauz' ); ?></span>
      <span class="tag"><?php esc_html_e( 'Your smile · Your experience · Your Hauz', 'the-dental-hauz' ); ?></span>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
