<?php
/**
 * Doctors index: a card per doctor, each linking to the doctor's own page (like the treatments).
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_template_part( 'template-parts/hero', null, array( 'title' => get_the_title() ) );
?>
<section class="section doctors">
  <div class="wrap">
    <div class="section-head">
      <p class="eyebrow"><?php esc_html_e( 'Meet the team', 'the-dental-hauz' ); ?></p>
      <h2><?php esc_html_e( 'The team behind your smile', 'the-dental-hauz' ); ?></h2>
    </div>
    <?php if ( ! dh_doctors() ) : ?>
      <p class="lead"><?php esc_html_e( 'Our team profiles are coming soon. In the meantime, book your visit below or get in touch, and we will be happy to help.', 'the-dental-hauz' ); ?></p>
    <?php else : ?>
    <div class="svc-grid svc-grid--doctors">
      <?php foreach ( dh_doctors() as $dh_n => $dh_d ) : ?>
        <?php $dh_url = dh_page_url( $dh_d['slug'] ); ?>
        <article class="svc doc-card">
          <div class="svc__media">
            <?php echo dh_doctor_photo( $dh_d, 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
          </div>
          <div class="svc__body">
            <span class="svc__num"><?php echo esc_html( sprintf( '%02d', $dh_n + 1 ) ); ?></span>
            <h3><a href="<?php echo esc_url( $dh_url ); ?>"><?php echo esc_html( $dh_d['name'] ); ?></a></h3>
            <?php if ( $dh_d['role'] ) : ?><p class="doc-card__role"><?php echo esc_html( $dh_d['role'] ); ?></p><?php endif; ?>
            <div class="svc__details">
              <div class="svc__details-inner">
                <?php if ( $dh_d['summary'] ) : ?><p><?php echo esc_html( $dh_d['summary'] ); ?></p><?php endif; ?>
                <a class="more" href="<?php echo esc_url( $dh_url ); ?>"><?php esc_html_e( 'View Profile', 'the-dental-hauz' ); ?><span class="screen-reader-text"> <?php echo esc_html( $dh_d['name'] ); ?></span></a>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php get_template_part( 'template-parts/book' ); ?>
