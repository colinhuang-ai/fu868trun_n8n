<?php
/**
 * Single news article (EN body in the editor, VI body in post meta).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$post_id    = get_the_ID();
	$cover      = cases_cover_style( (string) get_post_meta( $post_id, 'cases_cover', true ) );
	$content_vi = (string) get_post_meta( $post_id, 'cases_content_vi', true );
	$minutes    = cases_read_time( $post_id );

	// Related: same category first, then latest.
	$cats    = wp_get_post_categories( $post_id );
	$related = get_posts(
		array(
			'posts_per_page'      => 3,
			'post__not_in'        => array( $post_id ),
			'category__in'        => $cats,
			'ignore_sticky_posts' => true,
		)
	);
	if ( count( $related ) < 3 ) {
		$more    = get_posts(
			array(
				'posts_per_page'      => 3 - count( $related ),
				'post__not_in'        => array_merge( array( $post_id ), wp_list_pluck( $related, 'ID' ) ),
				'ignore_sticky_posts' => true,
			)
		);
		$related = array_merge( $related, $more );
	}
	?>
<main id="main" class="<?php echo esc_attr( cases_layout()['main'] ); ?>">

<!-- HERO -->
<header class="relative overflow-hidden bg-gradient-to-br <?php echo esc_attr( $cover['classes'] ); ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<?php the_post_thumbnail( 'full', array( 'class' => 'absolute inset-0 w-full h-full object-cover', 'alt' => '' ) ); ?>
		<div class="absolute inset-0 bg-gradient-to-t from-[#071525] via-[#071525]/70 to-[#071525]/30"></div>
	<?php else : ?>
		<div class="absolute inset-0 bg-[#071525]/50"></div>
		<div class="absolute -right-6 -bottom-10 text-[14rem] leading-none opacity-20 select-none pointer-events-none" aria-hidden="true"><?php echo esc_html( $cover['emoji'] ); ?></div>
	<?php endif; ?>
	<div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-8 py-14 md:py-20 text-white">
		<a href="<?php echo esc_url( cases_news_url() ); ?>" class="inline-flex items-center gap-1 text-sm font-bold text-sky-200 hover:text-white mb-6 transition-colors">
			<span class="material-symbols-outlined text-base" aria-hidden="true">arrow_back</span><?php cases_t( 'All news', 'Tất cả tin tức' ); ?>
		</a>
		<div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-5 text-xs">
			<?php cases_category_label( $post_id, 'px-3 py-1 rounded-full bg-white/15 border border-white/20 font-bold uppercase tracking-widest text-[10px]' ); ?>
			<?php cases_date( $post_id, 'text-white/80 font-medium' ); ?>
			<span class="text-white/80 font-medium" data-en="<?php echo esc_attr( $minutes . ' min read' ); ?>" data-vi="<?php echo esc_attr( $minutes . ' phút đọc' ); ?>"><?php echo esc_html( $minutes . ' min read' ); ?></span>
		</div>
		<h1 class="text-3xl sm:text-4xl md:text-5xl font-plus-jakarta-sans font-black leading-tight tracking-tight"<?php cases_post_attrs( $post_id, 'title' ); ?>><?php the_title(); ?></h1>
		<p class="mt-5 text-lg text-white/80 font-light leading-relaxed"<?php cases_post_attrs( $post_id, 'excerpt' ); ?>><?php echo esc_html( cases_post_excerpt_en( $post_id ) ); ?></p>
	</div>
</header>

<!-- ARTICLE -->
<article class="bg-white">
	<div class="max-w-3xl mx-auto px-4 sm:px-8 py-12 md:py-16">
		<?php if ( '' !== $content_vi ) : ?>
			<div class="lang-en entry-content"><?php the_content(); ?></div>
			<div class="lang-vi entry-content"><?php echo wp_kses_post( wpautop( $content_vi ) ); ?></div>
		<?php else : ?>
			<div class="entry-content"><?php the_content(); ?></div>
		<?php endif; ?>
	</div>
</article>

<!-- CTA -->
<section class="bg-white pb-14 md:pb-20">
	<div class="max-w-3xl mx-auto px-4 sm:px-8">
		<div class="rounded-2xl p-8 sm:p-10 text-center text-white" style="background:linear-gradient(135deg,#071525 0%,#0369a1 100%)">
			<h2 class="text-2xl font-plus-jakarta-sans font-black mb-3"><?php cases_t( 'Looking for premium shrimp?', 'Bạn đang tìm tôm chất lượng cao?' ); ?></h2>
			<p class="text-sky-100/80 mb-6 max-w-lg mx-auto text-sm"><?php cases_t( 'Talk to our sales team about specifications, sizes, packing and pricing for your market.', 'Trao đổi với đội ngũ kinh doanh về quy cách, cỡ tôm, đóng gói và báo giá cho thị trường của bạn.' ); ?></p>
			<button type="button" data-open-contact class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-sky-800 rounded-full font-bold text-sm shadow-xl hover:-translate-y-1 transition-all">
				<?php cases_t( 'Contact Sales', 'Liên Hệ Kinh Doanh' ); ?> <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
			</button>
		</div>
	</div>
</section>

<!-- RELATED -->
<?php if ( $related ) : ?>
<section class="py-12 md:py-16 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-8">
		<h2 class="text-2xl md:text-3xl font-plus-jakarta-sans font-bold text-slate-900 mb-8"><?php cases_t( 'Keep reading', 'Đọc thêm' ); ?></h2>
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
			<?php
			global $post;
			foreach ( $related as $post ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				setup_postdata( $post );
				get_template_part( 'template-parts/post-card' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

</main>
<?php endwhile; ?>
<?php
get_footer();
