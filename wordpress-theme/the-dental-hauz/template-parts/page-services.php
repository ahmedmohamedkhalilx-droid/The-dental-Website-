<?php
/**
 * Services index.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_p = dh_content( 'services_page' );
get_template_part( 'template-parts/hero', null, array( 'title' => get_the_title(), 'image' => 532, 'alt' => '' ) );
?>
<section class="section services">
  <div class="wrap">
    <div class="section-head">
      <p class="eyebrow"><?php echo esc_html( $dh_p['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_p['title'] ); ?></h2>
    </div>
    <?php get_template_part( 'template-parts/service-cards' ); ?>
  </div>
</section>
<?php get_template_part( 'template-parts/reviews', null, $dh_p['reviews'] ); ?>
<?php get_template_part( 'template-parts/book' ); ?>
