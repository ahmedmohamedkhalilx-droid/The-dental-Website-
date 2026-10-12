<?php
/**
 * FAQ's.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_f = dh_content( 'faq' );
get_template_part( 'template-parts/hero', null, array( 'title' => get_the_title(), 'image' => 557, 'alt' => '' ) );
?>
<section class="section faq">
  <div class="wrap faq__grid">
    <div class="faq__media"><?php echo dh_img( $dh_f['image'], 'large', array( 'alt' => '' ), 'aligners.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <div>
      <div class="section-head"><h2><?php echo esc_html( $dh_f['title'] ); ?></h2></div>
      <div class="faq-list">
        <?php foreach ( $dh_f['items'] as $dh_n => $dh_q ) : ?>
          <details<?php echo 0 === $dh_n ? ' open' : ''; ?>>
            <summary><?php echo esc_html( $dh_q[0] ); ?></summary>
            <p><?php echo esc_html( $dh_q[1] ); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php get_template_part( 'template-parts/stats', null, $dh_f['stats'] ); ?>
<?php get_template_part( 'template-parts/reviews', null, $dh_f['reviews'] ); ?>
<?php get_template_part( 'template-parts/book' ); ?>
