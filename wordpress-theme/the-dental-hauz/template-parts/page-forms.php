<?php
/**
 * Forms index.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_cards = dh_content( 'forms_page' )['cards'];
get_template_part( 'template-parts/hero', null, array( 'title' => get_the_title(), 'image' => 532, 'alt' => '' ) );
?>
<section class="section forms-index">
  <div class="wrap">
    <div class="boxes boxes--2 form-cards">
      <?php foreach ( $dh_cards as $dh_card ) : ?>
        <article class="form-card">
          <a class="form-card__media" href="<?php echo esc_url( dh_page_url( $dh_card['slug'] ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo dh_img( $dh_card['image'], 'medium_large', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
          <div class="form-card__body">
            <h3><?php echo esc_html( $dh_card['title'] ); ?></h3>
            <a class="btn" href="<?php echo esc_url( dh_page_url( $dh_card['slug'] ) ); ?>"><?php echo esc_html( $dh_card['button'] ); ?><span class="screen-reader-text"> <?php echo esc_html( $dh_card['title'] ); ?></span></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
