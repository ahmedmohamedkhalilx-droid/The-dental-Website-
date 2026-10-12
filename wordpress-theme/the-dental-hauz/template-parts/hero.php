<?php
/**
 * Inner-page hero: breadcrumb, title, optional lead lines and image.
 *
 * @var array $args title, crumbs (list of [label, url]), lead (string[]), image (attachment ID), alt.
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_title  = isset( $args['title'] ) ? $args['title'] : get_the_title();
$dh_crumbs = isset( $args['crumbs'] ) ? $args['crumbs'] : array();
$dh_lead   = isset( $args['lead'] ) ? (array) $args['lead'] : array();
$dh_image  = ! empty( $args['image'] ) ? dh_img( $args['image'], 'large', array( 'alt' => isset( $args['alt'] ) ? $args['alt'] : '', 'loading' => 'eager', 'fetchpriority' => 'high' ) ) : '';
?>
<section class="page-hero<?php echo $dh_image ? ' has-media' : ''; ?>">
  <div class="wrap page-hero__grid">
    <div class="page-hero__copy">
      <nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'the-dental-hauz' ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-dental-hauz' ); ?></a>
        <?php foreach ( $dh_crumbs as $dh_crumb ) : ?>
          <span aria-hidden="true">/</span><a href="<?php echo esc_url( $dh_crumb[1] ); ?>"><?php echo esc_html( $dh_crumb[0] ); ?></a>
        <?php endforeach; ?>
        <span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html( $dh_title ); ?></span>
      </nav>
      <h1><?php echo esc_html( $dh_title ); ?></h1>
      <?php foreach ( $dh_lead as $dh_line ) : ?>
        <p class="page-hero__lead"><?php echo esc_html( $dh_line ); ?></p>
      <?php endforeach; ?>
    </div>
    <?php if ( $dh_image ) : ?>
      <div class="page-hero__media"><?php echo $dh_image; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <?php endif; ?>
  </div>
</section>
