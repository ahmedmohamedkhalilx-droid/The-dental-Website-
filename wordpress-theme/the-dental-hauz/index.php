<?php
/**
 * Blog / archive listing.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow"><?php esc_html_e( 'News & advice', 'the-dental-hauz' ); ?></p>
    <h1><?php echo is_search() ? esc_html( sprintf( /* translators: %s: search query */ __( 'Search: %s', 'the-dental-hauz' ), get_search_query() ) ) : wp_kses_post( get_the_archive_title() ?: __( 'Latest articles', 'the-dental-hauz' ) ); ?></h1>
  </div>
</section>
<div class="wrap">
  <div class="post-list">
    <?php if ( have_posts() ) : ?>
      <?php while ( have_posts() ) : the_post(); ?>
        <article <?php post_class(); ?>>
          <span class="post-meta"><?php echo esc_html( get_the_date() ); ?></span>
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <div><?php the_excerpt(); ?></div>
        </article>
      <?php endwhile; ?>
    <?php else : ?>
      <p><?php esc_html_e( 'Nothing found here yet.', 'the-dental-hauz' ); ?></p>
    <?php endif; ?>
  </div>
  <?php
  the_posts_pagination( array( 'class' => 'pagination', 'mid_size' => 1 ) );
  ?>
</div>
<?php get_footer(); ?>
