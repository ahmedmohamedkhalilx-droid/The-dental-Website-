<?php
/**
 * About Us.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_a = dh_content( 'about' );
get_template_part( 'template-parts/hero', null, array( 'title' => get_the_title(), 'image' => 248, 'alt' => '' ) );
?>
<section class="section about-intro">
  <div class="wrap split">
    <div class="split__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_a['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_a['title'] ); ?></h2>
      <p class="lead"><?php echo esc_html( $dh_a['lead'] ); ?></p>
      <p><?php echo esc_html( $dh_a['intro'] ); ?></p>
    </div>
    <div class="split__media duo">
      <?php foreach ( $dh_a['images'] as $dh_id ) : ?>
        <?php echo dh_img( $dh_id, 'large', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="wrap">
    <div class="boxes boxes--2">
      <?php foreach ( $dh_a['boxes'] as $dh_b ) : ?>
        <article class="box"><h3><?php echo esc_html( $dh_b[0] ); ?></h3><p><?php echo esc_html( $dh_b[1] ); ?></p></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section mission">
  <div class="wrap split">
    <div class="split__copy">
      <p class="eyebrow eyebrow--light"><?php echo esc_html( $dh_a['mission']['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_a['mission']['title'] ); ?></h2>
      <?php foreach ( $dh_a['mission']['paragraphs'] as $dh_p ) : ?>
        <p><?php echo esc_html( $dh_p ); ?></p>
      <?php endforeach; ?>
    </div>
    <div class="split__media"><?php echo dh_img( $dh_a['mission']['image'], 'large', array( 'alt' => '' ), 'scrubs.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
  </div>
</section>

<section class="section aesthetics">
  <div class="wrap split split--reverse">
    <div class="split__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_a['aesthetics']['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_a['aesthetics']['title'] ); ?></h2>
      <p><?php echo esc_html( $dh_a['aesthetics']['body'] ); ?></p>
      <div class="boxes boxes--1">
        <?php foreach ( $dh_a['aesthetics']['boxes'] as $dh_b ) : ?>
          <article class="box"><h3><?php echo esc_html( $dh_b[0] ); ?></h3><p><?php echo esc_html( $dh_b[1] ); ?></p></article>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="split__media"><?php echo dh_img( $dh_a['aesthetics']['image'], 'large', array( 'alt' => '' ), 'shades.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
  </div>
</section>

<?php get_template_part( 'template-parts/stats', null, $dh_a['stats'] ); ?>
<?php get_template_part( 'template-parts/reviews', null, $dh_a['reviews'] ); ?>
<?php get_template_part( 'template-parts/book' ); ?>
