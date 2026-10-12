<?php
/**
 * The ten treatments as numbered cards.
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
      <a class="svc__media" href="<?php echo esc_url( dh_page_url( $dh_s['slug'] ) ); ?>" tabindex="-1" aria-hidden="true">
        <?php echo dh_img( $dh_s['image'], 'medium_large', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </a>
      <div class="svc__body">
        <span class="svc__num"><?php echo esc_html( $dh_s['num'] ); ?></span>
        <h3><a href="<?php echo esc_url( dh_page_url( $dh_s['slug'] ) ); ?>"><?php echo esc_html( $dh_s['title'] ); ?></a></h3>
        <p><?php echo esc_html( $dh_s['summary'] ); ?></p>
        <a class="more" href="<?php echo esc_url( dh_page_url( $dh_s['slug'] ) ); ?>"><?php echo esc_html( $dh_s['button'] ); ?><span class="screen-reader-text"> <?php echo esc_html( $dh_s['title'] ); ?></span></a>
      </div>
    </article>
  <?php endforeach; ?>
</div>
