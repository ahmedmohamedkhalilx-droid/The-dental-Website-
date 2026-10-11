<?php
/**
 * Single post.
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
    <p class="post-meta"><?php echo esc_html( get_the_date() ); ?></p>
    <h1><?php the_title(); ?></h1>
  </div>
</section>
<div class="wrap">
  <article <?php post_class( 'entry' ); ?>>
    <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?>
    <?php the_content(); ?>
  </article>
</div>
<?php endwhile; ?>
<?php get_footer(); ?>
