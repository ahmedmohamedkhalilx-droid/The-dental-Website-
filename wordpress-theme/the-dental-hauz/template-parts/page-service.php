<?php
/**
 * One treatment page.
 *
 * @var array $args slug.
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_s = dh_content( 'service_pages' )[ $args['slug'] ];
get_template_part(
	'template-parts/hero',
	null,
	array(
		'title'  => get_the_title(),
		'crumbs' => array( array( __( 'Services', 'the-dental-hauz' ), dh_page_url( 'our-services' ) ) ),
		'image'  => $dh_s['hero'],
		'alt'    => '',
	)
);
?>
<section class="section service-intro">
  <div class="wrap split">
    <div class="split__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_s['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_s['heading'] ); ?></h2>
      <p class="lead"><?php echo esc_html( $dh_s['lead'] ); ?></p>
    </div>
    <div class="split__media duo">
      <?php foreach ( $dh_s['images'] as $dh_id ) : ?>
        <?php echo dh_img( $dh_id, 'large', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="wrap">
    <div class="boxes boxes--2">
      <?php foreach ( $dh_s['intro_boxes'] as $dh_b ) : ?>
        <article class="box"><h3><?php echo esc_html( $dh_b[0] ); ?></h3><p><?php echo esc_html( $dh_b[1] ); ?></p></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section expect">
  <div class="wrap">
    <div class="section-head">
      <p class="eyebrow eyebrow--light"><?php echo esc_html( $dh_s['expect_eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_s['expect_heading'] ); ?></h2>
    </div>
    <div class="boxes boxes--2 boxes--dark">
      <?php foreach ( $dh_s['expect_boxes'] as $dh_b ) : ?>
        <article class="box"><h3><?php echo esc_html( $dh_b[0] ); ?></h3><p><?php echo esc_html( $dh_b[1] ); ?></p></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section service-care">
  <div class="wrap split split--reverse">
    <div class="split__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_s['care_eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_s['care_heading'] ); ?></h2>
      <p><?php echo esc_html( $dh_s['care_text'] ); ?></p>
      <div class="boxes boxes--1">
        <?php foreach ( $dh_s['care_boxes'] as $dh_b ) : ?>
          <article class="box"><h3><?php echo esc_html( $dh_b[0] ); ?></h3><p><?php echo esc_html( $dh_b[1] ); ?></p></article>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="split__media"><?php echo dh_img( $dh_s['care_image'], 'large', array( 'alt' => '' ), 'shades.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
  </div>
</section>

<?php get_template_part( 'template-parts/book' ); ?>
