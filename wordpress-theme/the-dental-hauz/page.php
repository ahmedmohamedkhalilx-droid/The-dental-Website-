<?php
/**
 * Single page.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) :
	the_post();
	?>
<section class="page-hero">
  <div class="wrap">
    <h1><?php the_title(); ?></h1>
  </div>
</section>
<div class="wrap">
  <article <?php post_class( 'entry' ); ?>>
    <?php the_content(); ?>
  </article>
</div>
<?php endwhile; ?>
<?php get_footer(); ?>
