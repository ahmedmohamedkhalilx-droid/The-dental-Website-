<?php
/**
 * One doctor's page.
 *
 * @var array $args slug.
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_doc = null;
foreach ( dh_doctors() as $dh_d ) {
	if ( $dh_d['slug'] === $args['slug'] ) {
		$dh_doc = $dh_d;
	}
}
if ( ! $dh_doc ) {
	return;
}
get_template_part(
	'template-parts/hero',
	null,
	array(
		'title'  => $dh_doc['name'],
		'crumbs' => array( array( __( 'Our Team', 'the-dental-hauz' ), dh_page_url( 'doctors' ) ) ),
		'lead'   => $dh_doc['role'] ? array( $dh_doc['role'] ) : array(),
	)
);
$dh_has_more = $dh_doc['bio'] || $dh_doc['credentials'];
?>
<section class="section doctor<?php echo $dh_doc['photo'] ? '' : ' doctor--text'; ?>">
  <div class="wrap<?php echo $dh_doc['photo'] ? ' split' : ''; ?>">
    <?php if ( $dh_doc['photo'] ) : ?>
      <div class="split__media doctor__photo"><?php echo dh_doctor_photo( $dh_doc, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <?php endif; ?>
    <div class="split__copy">
      <p class="eyebrow"><?php echo esc_html( $dh_doc['role'] ? $dh_doc['role'] : __( 'Meet your dentist', 'the-dental-hauz' ) ); ?></p>
      <h2><?php echo esc_html( $dh_doc['name'] ); ?></h2>
      <?php if ( $dh_doc['bio'] ) : ?>
        <?php foreach ( $dh_doc['bio'] as $dh_p ) : ?>
          <p><?php echo esc_html( $dh_p ); ?></p>
        <?php endforeach; ?>
      <?php else : ?>
        <p><?php echo esc_html( sprintf( /* translators: %s: doctor name */ __( '%s sees patients at The Dental Hauz in Markham, Ontario.', 'the-dental-hauz' ), $dh_doc['name'] ) ); ?></p>
      <?php endif; ?>
      <?php if ( $dh_doc['credentials'] ) : ?>
        <ul class="checks">
          <?php foreach ( $dh_doc['credentials'] as $dh_c ) : ?>
            <li><?php echo esc_html( $dh_c ); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <div><a class="btn" href="#book"><?php esc_html_e( 'Book An Appointment', 'the-dental-hauz' ); ?></a></div>
      <?php if ( ! $dh_has_more && current_user_can( 'edit_theme_options' ) ) : ?>
        <p class="admin-note"><?php esc_html_e( 'Admins: add this doctor\'s photo, role, bio and credentials in the theme file inc/doctors.php.', 'the-dental-hauz' ); ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php get_template_part( 'template-parts/book' ); ?>
