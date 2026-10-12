<?php
/**
 * The ten treatments as photo cards. Name and number show at rest; the description and
 * "Learn More" appear on hover, keyboard focus or tap.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_c = dh_content();
?>
<div class="svc-grid">
  <?php foreach ( $dh_c['services'] as $dh_s ) : ?>
    <article class="svc">
      <div class="svc__media">
        <?php echo dh_img( $dh_s['image'], 'medium_large', array( 'alt' => '' ), 'aligners.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
      <div class="svc__body">
        <span class="svc__num"><?php echo esc_html( $dh_s['num'] ); ?></span>
        <h3><a href="<?php echo esc_url( dh_page_url( $dh_s['slug'] ) ); ?>"><?php echo esc_html( $dh_s['title'] ); ?></a></h3>
        <div class="svc__details">
          <div class="svc__details-inner">
            <p><?php echo esc_html( $dh_s['summary'] ); ?></p>
            <a class="more" href="<?php echo esc_url( dh_page_url( $dh_s['slug'] ) ); ?>"><?php echo esc_html( $dh_s['button'] ); ?><span class="screen-reader-text"> <?php echo esc_html( $dh_s['title'] ); ?></span></a>
          </div>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>
