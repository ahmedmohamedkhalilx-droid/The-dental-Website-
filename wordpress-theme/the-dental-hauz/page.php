<?php
/**
 * Pages. The pages carried over from the previous site render from the theme's own templates;
 * any other page shows its editor content.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) :
	the_post();
	$dh_slug = get_post_field( 'post_name', get_the_ID() );
	$dh_map  = dh_page_map();
	if ( isset( $dh_map[ $dh_slug ] ) ) {
		get_template_part( 'template-parts/page', $dh_map[ $dh_slug ], array( 'slug' => $dh_slug ) );
		continue;
	}
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
