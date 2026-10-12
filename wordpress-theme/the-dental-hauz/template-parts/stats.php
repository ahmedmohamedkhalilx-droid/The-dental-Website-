<?php
/**
 * "Our Accomplishments" counters. Hidden unless switched on in the Customizer, because the
 * figures on the old site look like template placeholders.
 *
 * @var array $args eyebrow, title, items ([number, suffix, label]).
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! get_theme_mod( 'dh_show_stats', false ) ) {
	return;
}
?>
<section class="section stats">
  <div class="wrap">
    <div class="section-head section-head--center">
      <p class="eyebrow eyebrow--light"><?php echo esc_html( $args['eyebrow'] ); ?></p>
      <h2><?php echo esc_html( $args['title'] ); ?></h2>
    </div>
    <dl class="stats__grid">
      <?php foreach ( $args['items'] as $dh_s ) : ?>
        <div><dt><?php echo esc_html( $dh_s[2] ); ?></dt><dd><?php echo esc_html( number_format_i18n( $dh_s[0] ) . $dh_s[1] ); ?></dd></div>
      <?php endforeach; ?>
    </dl>
  </div>
</section>
