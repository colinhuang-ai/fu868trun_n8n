<?php
/**
 * Generic page fallback (pages without a dedicated template).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="<?php echo esc_attr( cases_layout()['main'] ); ?>">
	<div class="max-w-4xl mx-auto px-4 sm:px-8 py-16">
		<?php while ( have_posts() ) : the_post(); ?>
			<h1 class="text-4xl font-plus-jakarta-sans font-black text-slate-900 mb-8"><?php the_title(); ?></h1>
			<div class="entry-content"><?php the_content(); ?></div>
		<?php endwhile; ?>
	</div>
</main>
<?php
get_footer();
