<?php
/**
 * Home page.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_h     = dh_content( 'home' );
$dh_c     = dh_content();
$dh_video = wp_get_attachment_url( $dh_h['intro']['video_url_id'] );
get_header();
?>

<section class="hero">
  <img class="hero-mark" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/mark-dark.png' ) ); ?>" alt="" aria-hidden="true">
  <div class="wrap">
    <div class="hero-copy">
      <p class="eyebrow"><?php echo esc_html( $dh_h['hero']['subtitle'] ); ?></p>
      <h1><?php echo esc_html( $dh_h['hero']['title'] ); ?></h1>
      <p class="hero-lead"><?php echo esc_html( $dh_h['hero']['body'] ); ?></p>
      <div class="hero-actions">
        <a class="btn" href="<?php echo esc_url( dh_page_url( $dh_h['hero']['button_link'] ) ); ?>"><?php echo esc_html( $dh_h['hero']['button'] ); ?></a>
      </div>
    </div>
    <div class="hero-media">
      <div class="frame"><?php echo dh_img( $dh_h['hero']['image'], 'large', array( 'alt' => $dh_h['hero']['subtitle'], 'loading' => 'eager', 'fetchpriority' => 'high' ), 'craft.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    </div>
  </div>
</section>

<section class="section intro" id="about">
  <div class="wrap intro__grid">
    <div class="intro__media">
      <?php if ( $dh_video ) : ?>
        <figure class="video">
          <video controls preload="none" playsinline <?php echo wp_get_attachment_image_url( $dh_h['intro']['video_poster'], 'large' ) ? 'poster="' . esc_url( wp_get_attachment_image_url( $dh_h['intro']['video_poster'], 'large' ) ) . '"' : ''; ?>>
            <source src="<?php echo esc_url( $dh_video ); ?>" type="video/mp4">
          </video>
          <figcaption><?php echo esc_html( $dh_h['intro']['video_label'] ); ?></figcaption>
        </figure>
      <?php else : ?>
        <?php echo dh_img( $dh_h['intro']['video_poster'], 'large', array( 'alt' => '' ), 'scrubs.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php endif; ?>
    </div>
    <div class="intro__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_h['intro']['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_h['intro']['title'] ); ?></h2>
      <?php foreach ( $dh_h['intro']['paragraphs'] as $dh_p ) : ?>
        <p><?php echo esc_html( $dh_p ); ?></p>
      <?php endforeach; ?>
      <div><a class="btn btn--ghost" href="<?php echo esc_url( dh_page_url( $dh_h['intro']['button_link'] ) ); ?>"><?php echo esc_html( $dh_h['intro']['button'] ); ?></a></div>
    </div>
  </div>
</section>

<section class="section services" id="services">
  <div class="wrap">
    <div class="section-head">
      <p class="eyebrow"><?php echo esc_html( $dh_h['services_head']['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_h['services_head']['title'] ); ?></h2>
    </div>
    <?php get_template_part( 'template-parts/service-cards' ); ?>
  </div>
</section>

<section class="cta-band">
  <div class="wrap">
    <a class="btn btn--light" href="<?php echo esc_url( $dh_h['cta']['call_link'] ); ?>"><?php echo esc_html( $dh_h['cta']['call'] ); ?></a>
    <a class="btn btn--outline-light" href="<?php echo esc_url( $dh_h['cta']['msg_link'] ); ?>"><?php echo esc_html( $dh_h['cta']['msg'] ); ?></a>
  </div>
</section>

<section class="section programs">
  <div class="wrap split">
    <div class="split__media"><?php echo dh_img( $dh_h['programs']['image'], 'large', array( 'alt' => '' ), 'shades.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <div class="split__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_h['programs']['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_h['programs']['title'] ); ?></h2>
      <p><?php echo esc_html( $dh_h['programs']['body'] ); ?></p>
      <ul class="checks">
        <?php foreach ( $dh_h['programs']['items'] as $dh_i ) : ?>
          <li><?php echo esc_html( $dh_i ); ?></li>
        <?php endforeach; ?>
      </ul>
      <div><a class="btn" href="<?php echo esc_url( $dh_h['programs']['button_link'] ); ?>"><?php echo esc_html( $dh_h['programs']['button'] ); ?></a></div>
    </div>
  </div>
</section>

<?php get_template_part( 'template-parts/reviews', null, $dh_h['reviews'] ); ?>

<section class="section smile">
  <div class="wrap split split--reverse">
    <div class="split__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_h['smile']['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_h['smile']['title'] ); ?></h2>
      <?php foreach ( $dh_h['smile']['paragraphs'] as $dh_p ) : ?>
        <p><?php echo esc_html( $dh_p ); ?></p>
      <?php endforeach; ?>
      <div><a class="btn btn--ghost" href="<?php echo esc_url( dh_page_url( $dh_h['smile']['button_link'] ) ); ?>"><?php echo esc_html( $dh_h['smile']['button'] ); ?></a></div>
    </div>
    <div class="split__media photo-grid">
      <?php foreach ( $dh_h['smile']['images'] as $dh_id ) : ?>
        <?php echo dh_img( $dh_id, 'medium_large', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section devices">
  <div class="wrap">
    <div class="section-head">
      <p class="eyebrow"><?php echo esc_html( $dh_h['devices']['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $dh_h['devices']['title'] ); ?></h2>
    </div>
    <div class="boxes boxes--3">
      <?php foreach ( $dh_h['devices']['items'] as $dh_b ) : ?>
        <article class="box"><h3><?php echo esc_html( $dh_b[0] ); ?></h3><p><?php echo esc_html( $dh_b[1] ); ?></p></article>
      <?php endforeach; ?>
    </div>
    <p class="devices__closing"><?php echo esc_html( $dh_h['devices']['closing'] ); ?></p>
  </div>
</section>

<section class="section faq" id="faq">
  <div class="wrap faq__grid">
    <div class="faq__media"><?php echo dh_img( $dh_h['faq']['image'], 'large', array( 'alt' => '' ), 'aligners.jpg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <div>
      <div class="section-head"><h2><?php echo esc_html( $dh_h['faq']['title'] ); ?></h2></div>
      <div class="faq-list">
        <?php foreach ( $dh_h['faq']['items'] as $dh_n => $dh_q ) : ?>
          <details<?php echo 0 === $dh_n ? ' open' : ''; ?>>
            <summary><?php echo esc_html( $dh_q[0] ); ?></summary>
            <p><?php echo esc_html( $dh_q[1] ); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section manifesto">
  <div class="wrap manifesto__inner">
    <div class="mantra"><span><?php esc_html_e( 'Your smile', 'the-dental-hauz' ); ?></span><span><?php esc_html_e( 'Your experience', 'the-dental-hauz' ); ?></span><span><?php esc_html_e( 'Your', 'the-dental-hauz' ); ?> <b><?php esc_html_e( 'Hauz', 'the-dental-hauz' ); ?></b></span></div>
  </div>
</section>

<?php get_template_part( 'template-parts/book' ); ?>

<?php get_footer(); ?>
