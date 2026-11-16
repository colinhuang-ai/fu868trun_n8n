<?php
/**
 * News list (posts page "News") — also used for category archives.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();

$logo        = get_theme_file_uri( 'assets/img/logo.svg' );
$is_category = is_category();
$current_cat = $is_category ? get_queried_object() : null;
$paged       = max( 1, (int) get_query_var( 'paged' ) );
$categories  = get_categories( array( 'hide_empty' => true, 'orderby' => 'term_id' ) );
?>
<main id="main" class="<?php echo esc_attr( cases_layout()['main'] ); ?>">

<!-- HERO -->
<section class="relative overflow-hidden" style="background: linear-gradient(160deg, #071525 0%, #0d2137 60%, #071525 100%);">
	<div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, #38bdf8 1px, transparent 0); background-size: 48px 48px;"></div>
	<div class="absolute right-0 top-0 h-full w-1/2 opacity-10 pointer-events-none overflow-hidden">
		<img alt="" class="h-full w-auto object-contain object-right select-none" src="<?php echo esc_url( $logo ); ?>">
	</div>
	<div class="max-w-7xl mx-auto px-4 sm:px-8 py-14 md:py-20 relative z-10">
		<span class="inline-block py-1.5 px-4 mb-5 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold tracking-widest uppercase border border-sky-500/30"<?php cases_attr( 'News & Insights', 'Tin Tức & Kiến Thức' ); ?>>News &amp; Insights</span>
		<h1 class="text-3xl sm:text-4xl md:text-6xl font-plus-jakarta-sans font-black text-white leading-tight tracking-tighter mb-5">
			<?php cases_t( 'News & Insights', 'Tin Tức & Kiến Thức Về Tôm' ); ?>
		</h1>
		<p class="text-sky-100/60 text-lg max-w-2xl font-light leading-relaxed">
			<?php cases_t( 'Practical guides, quality know-how and stories from the shrimp industry, straight from Cà Mau.', 'Hướng dẫn thực tế, kiến thức chất lượng và những câu chuyện của ngành tôm, ngay từ Cà Mau.' ); ?>
		</p>
	</div>
</section>

<!-- LIST -->
<section class="py-12 md:py-16 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-8">

		<nav class="flex flex-wrap gap-2 mb-10" aria-label="<?php esc_attr_e( 'News categories', 'cases' ); ?>">
			<a href="<?php echo esc_url( cases_news_url() ); ?>" class="px-4 py-2 rounded-full text-sm font-bold transition-all <?php echo $is_category ? 'bg-white text-slate-600 border border-slate-200 hover:border-sky-300 hover:text-sky-700' : 'bg-sky-700 text-white shadow-md'; ?>"<?php echo $is_category ? '' : ' aria-current="page"'; ?>><?php cases_t( 'All', 'Tất Cả' ); ?></a>
			<?php foreach ( $categories as $cat ) : ?>
				<?php
				$active = $current_cat && (int) $current_cat->term_id === (int) $cat->term_id;
				$en     = wp_specialchars_decode( $cat->name );
				$vi     = cases_cat_vi( $cat->slug ) ?: $en;
				?>
				<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>" class="px-4 py-2 rounded-full text-sm font-bold transition-all <?php echo $active ? 'bg-sky-700 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:border-sky-300 hover:text-sky-700'; ?>"<?php echo $active ? ' aria-current="page"' : ''; ?><?php cases_attr( $en, $vi ); ?>><?php echo esc_html( $en ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php if ( have_posts() ) : ?>
			<?php
			$index = 0;
			echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
			while ( have_posts() ) :
				the_post();
				$is_featured = ( 0 === $index && 1 === $paged && ! $is_category );
				if ( $is_featured ) {
					echo '<div class="md:col-span-2 lg:col-span-3">';
					get_template_part( 'template-parts/post-card', null, array( 'featured' => true ) );
					echo '</div>';
				} else {
					get_template_part( 'template-parts/post-card' );
				}
				++$index;
			endwhile;
			echo '</div>';

			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => '←',
					'next_text' => '→',
					'class'     => 'cases-pagination',
				)
			);
			?>
		<?php else : ?>
			<p class="text-center text-slate-500 py-20"><?php cases_t( 'No articles yet. Please check back soon.', 'Chưa có bài viết. Vui lòng quay lại sau.' ); ?></p>
		<?php endif; ?>
	</div>
</section>

</main>
<?php
get_footer();
