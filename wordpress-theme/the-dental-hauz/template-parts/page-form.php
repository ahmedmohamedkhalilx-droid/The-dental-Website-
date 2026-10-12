<?php
/**
 * Medical history, request for records and payment agreement pages.
 *
 * @var array $args slug.
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_lead = array();
if ( in_array( $args['slug'], array( 'medical-history-form', 'request-for-records' ), true ) ) {
	$dh_lead[] = implode( ', ', dh_address_lines() );
	$dh_lead[] = dh_clinic( 'phone' );
}
get_template_part(
	'template-parts/hero',
	null,
	array(
		'title'  => get_the_title(),
		'crumbs' => array( array( __( 'Forms', 'the-dental-hauz' ), dh_page_url( 'forms' ) ) ),
		'lead'   => array_filter( $dh_lead ),
		'image'  => 532,
		'alt'    => '',
	)
);
?>
<section class="section form-page">
  <div class="wrap wrap--narrow">
    <?php dh_render_form( $args['slug'], array( 'wide' => true ) ); ?>
    <?php if ( in_array( $args['slug'], array( 'dental-treatment-payment-agreement', 'payment-agreement' ), true ) ) : ?>
      <p class="form-address"><?php echo esc_html( implode( ', ', dh_address_lines() ) . ' · ' . dh_clinic( 'phone' ) ); ?></p>
    <?php endif; ?>
  </div>
</section>
