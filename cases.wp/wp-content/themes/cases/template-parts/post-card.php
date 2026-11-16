<?php
/**
 * News card. Args: featured (bool) → large horizontal layout.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

$post_id  = get_the_ID();
$featured = ! empty( $args['featured'] );
$cover    = cases_cover_style( (string) get_post_meta( $post_id, 'cases_cover', true ) );
$minutes  = cases_read_time( $post_id );
$excerpt  = cases_post_excerpt_en( $post_id );
$link     = get_permalink();
?>
<article class="group relative flex flex-col <?php echo $featured ? 'md:grid md:grid-cols-2' : ''; ?> bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
	<div class="relative <?php echo $featured ? 'aspect-[16/10] md:aspect-auto md:min-h-[320px]' : 'aspect-[16/10]'; ?> overflow-hidden bg-gradient-to-br <?php echo esc_attr( $cover['classes'] ); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'large', array( 'class' => 'absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500', 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 70% 30%,#fff 0%,transparent 60%)"></div>
			<div class="absolute inset-0 flex items-center justify-center text-7xl <?php echo $featured ? 'md:text-8xl' : ''; ?> group-hover:scale-110 transition-transform duration-500" aria-hidden="true"><?php echo esc_html( $cover['emoji'] ); ?></div>
		<?php endif; ?>
		<?php if ( $featured ) : ?>
			<span class="absolute top-4 left-4 px-3 py-1 rounded-full bg-white/90 text-sky-800 text-[10px] font-black uppercase tracking-widest"><?php cases_t( 'Featured', 'Nổi Bật' ); ?></span>
		<?php endif; ?>
	</div>

	<div class="flex flex-col flex-1 p-6 <?php echo $featured ? 'md:p-10 md:justify-center' : ''; ?>">
		<div class="flex flex-wrap items-center gap-x-3 gap-y-1 mb-3 text-xs">
			<?php cases_category_label( $post_id, 'px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 font-bold uppercase tracking-wider text-[10px]' ); ?>
			<?php cases_date( $post_id, 'text-slate-400 font-medium' ); ?>
		</div>

		<h3 class="font-plus-jakarta-sans font-extrabold text-slate-900 leading-snug tracking-tight <?php echo $featured ? 'text-2xl md:text-3xl' : 'text-lg'; ?> mb-3 group-hover:text-sky-700 transition-colors"<?php cases_post_attrs( $post_id, 'title' ); ?>>
			<a href="<?php echo esc_url( $link ); ?>" class="after:absolute after:inset-0"><?php the_title(); ?></a>
		</h3>

		<p class="text-slate-500 text-sm leading-relaxed <?php echo $featured ? 'md:text-base line-clamp-4' : 'line-clamp-3'; ?> mb-5"<?php cases_post_attrs( $post_id, 'excerpt' ); ?>><?php echo esc_html( $excerpt ); ?></p>

		<div class="mt-auto flex items-center justify-between text-xs">
			<span class="text-slate-400 font-medium" data-en="<?php echo esc_attr( $minutes . ' min read' ); ?>" data-vi="<?php echo esc_attr( $minutes . ' phút đọc' ); ?>"><?php echo esc_html( $minutes . ' min read' ); ?></span>
			<span class="inline-flex items-center gap-1 font-bold text-sky-700 group-hover:gap-2 transition-all">
				<?php cases_t( 'Read more', 'Đọc tiếp' ); ?>
				<span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
			</span>
		</div>
	</div>
</article>
