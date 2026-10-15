<?php
/**
 * Our Team (address /doctors/): an editorial list, numbered, with name and role on the left and the bio on the right.
 * Dentists link to their own profile page. No photos, at the team's request.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_template_part( 'template-parts/hero', null, array( 'title' => __( 'Our Team', 'the-dental-hauz' ) ) );
$dh_team = dh_team();
?>
<section class="section team">
  <div class="wrap team__grid">
    <div class="team__intro">
      <p class="eyebrow"><?php esc_html_e( 'Meet the team', 'the-dental-hauz' ); ?></p>
      <h2><?php esc_html_e( 'The team behind your smile', 'the-dental-hauz' ); ?></h2>
      <p><?php esc_html_e( 'Dentists, a hygienist, a dental assistant and our front desk, working together so every visit feels calm, clear and personal.', 'the-dental-hauz' ); ?></p>
      <div><a class="btn" href="#book"><?php esc_html_e( 'Book Appointment', 'the-dental-hauz' ); ?></a></div>
    </div>
    <?php if ( $dh_team ) : ?>
      <ol class="team-list">
        <?php foreach ( $dh_team as $dh_n => $dh_m ) : ?>
          <li class="team-row">
            <span class="team-row__num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $dh_n + 1 ) ); ?></span>
            <div class="team-row__who">
              <h3>
                <?php if ( $dh_m['slug'] ) : ?>
                  <a href="<?php echo esc_url( dh_page_url( $dh_m['slug'] ) ); ?>"><?php echo esc_html( $dh_m['name'] ); ?></a>
                <?php else : ?>
                  <?php echo esc_html( $dh_m['name'] ); ?>
                <?php endif; ?>
              </h3>
              <?php if ( $dh_m['role'] ) : ?><p class="team-row__role"><?php echo esc_html( $dh_m['role'] ); ?></p><?php endif; ?>
            </div>
            <div class="team-row__bio">
              <?php foreach ( (array) $dh_m['bio'] as $dh_p ) : ?>
                <p><?php echo esc_html( $dh_p ); ?></p>
              <?php endforeach; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php else : ?>
      <p class="lead"><?php esc_html_e( 'Our team profiles are coming soon. In the meantime, book your visit below or get in touch, and we will be happy to help.', 'the-dental-hauz' ); ?></p>
    <?php endif; ?>
  </div>
</section>
<?php get_template_part( 'template-parts/book' ); ?>
