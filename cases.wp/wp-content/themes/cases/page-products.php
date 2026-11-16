<?php
/**
 * Products page (slug: products). Category cards expand into SKU grids;
 * a SKU opens a lightbox. Data comes from the "cases_product" post type.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();

$groups = cases_get_product_groups();
$rows   = array_chunk( $groups, 3 );

// Data consumed by assets/js/main.js.
$js_groups = array();
foreach ( $groups as $g ) {
	$js_groups[ $g['slug'] ] = array(
		'name'  => $g['name'],
		'vi'    => $g['style']['vi'],
		'emoji' => $g['style']['emoji'],
		'cols'  => $g['style']['cols'],
		'skus'  => $g['skus'],
	);
}
?>
<main id="main" class="max-w-screen-2xl mx-auto px-4 sm:px-8 py-12 bg-slate-50 min-h-screen pt-[132px]">

	<?php cases_page_intro( 'light' ); // Page editor content, if any. ?>

	<section class="relative mb-10 overflow-hidden rounded-2xl h-[260px] sm:h-[320px] flex items-center" style="background:linear-gradient(120deg,#071525 60%,#0369a1 100%)">
		<div class="absolute inset-0 opacity-20" style="background:radial-gradient(ellipse at 75% 50%,#38bdf8 0%,transparent 65%)"></div>
		<div class="relative z-10 px-8 sm:px-16 max-w-3xl">
			<span class="inline-block px-3 py-1 bg-sky-600/70 text-white text-[10px] font-bold uppercase tracking-[.25em] mb-5 rounded-full border border-sky-400/30"<?php cases_attr( 'Full Product Catalog', 'Danh Mục Sản Phẩm' ); ?>>Full Product Catalog</span>
			<h1 class="text-4xl sm:text-5xl font-black text-white font-headline leading-tight tracking-tight mb-4">
				<?php cases_t( 'Ocean Premium,<br/><span class="text-sky-400">Delivered Globally</span>', 'Tinh Hoa Đại Dương,<br/><span class="text-sky-400">Xuất Khẩu Toàn Cầu</span>' ); ?>
			</h1>
			<p class="text-slate-300 text-sm font-light"<?php cases_attr( 'Click a category to see its products', 'Bấm vào từng danh mục để xem chi tiết sản phẩm' ); ?>>Click a category to see its products</p>
		</div>
	</section>

	<?php if ( empty( $groups ) ) : ?>
		<p class="text-center text-slate-500 py-20"><?php esc_html_e( 'Products are coming soon.', 'cases' ); ?></p>
	<?php endif; ?>

	<?php foreach ( $rows as $row_index => $row ) : $row_no = $row_index + 1; ?>
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-5">
			<?php foreach ( $row as $g ) : $s = $g['style']; ?>
				<div class="product-category-card cursor-pointer group" id="card-<?php echo esc_attr( $g['slug'] ); ?>" data-cat="<?php echo esc_attr( $g['slug'] ); ?>" data-row="<?php echo (int) $row_no; ?>" role="button" tabindex="0" aria-expanded="false" aria-controls="panel-row-<?php echo (int) $row_no; ?>">
					<div class="bg-gradient-to-br <?php echo esc_attr( $s['gradient'] ); ?> rounded-2xl overflow-hidden editorial-shadow hover-lift h-full flex flex-col">
						<div class="flex-1 flex items-center justify-center relative overflow-hidden min-h-[11rem]">
							<div class="absolute inset-0 opacity-10" style="background:radial-gradient(circle at 60% 40%,white 0%,transparent 70%)"></div>
							<div class="flex flex-col items-center justify-center select-none">
								<span class="font-headline font-black text-white/25 text-7xl sm:text-8xl leading-none tracking-tighter group-hover:text-white/40 transition-all duration-500"><?php echo esc_html( $s['code'] ); ?></span>
								<span class="font-headline font-bold text-white/15 text-xs uppercase tracking-[0.3em] mt-1 group-hover:text-white/30 transition-all duration-500"><?php echo esc_html( $s['sub'] ); ?></span>
							</div>
						</div>
						<div class="px-5 py-4 flex items-center justify-between bg-black/20">
							<div>
								<h2 class="font-headline font-black text-white text-sm sm:text-base uppercase tracking-tight leading-tight"><?php echo esc_html( $g['name'] ); ?></h2>
								<p class="text-white/60 text-[11px] mt-0.5"<?php echo $s['vi'] ? ' data-en="' . esc_attr( $g['name'] ) . '" data-vi="' . esc_attr( $s['vi'] ) . '"' : ''; ?>><?php echo esc_html( $g['name'] ); ?></p>
							</div>
							<div class="flex flex-col items-end gap-0.5">
								<span class="text-white font-black font-headline text-2xl leading-none"><?php echo (int) count( $g['skus'] ); ?></span>
								<span class="text-white/60 text-[9px] uppercase tracking-widest">SKUs</span>
							</div>
						</div>
						<div class="px-5 py-2.5 flex items-center justify-between bg-black/30">
							<span class="text-white/50 text-[10px] uppercase tracking-widest expand-hint" data-view="<?php esc_attr_e( 'View products', 'cases' ); ?>">View products</span>
							<span class="material-symbols-outlined text-white/50 text-base expand-icon transition-transform duration-300" aria-hidden="true">expand_more</span>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="expand-panel mb-5" id="panel-row-<?php echo (int) $row_no; ?>" hidden></div>
	<?php endforeach; ?>

	<!-- CTA -->
	<section class="rounded-2xl p-10 sm:p-14 text-center text-white mt-5" style="background:linear-gradient(135deg,#071525 0%,#0369a1 100%)">
		<h2 class="text-2xl sm:text-3xl font-black font-headline mb-3"<?php cases_attr( 'Interested in Our Products?', 'Quan Tâm Đến Sản Phẩm?' ); ?>>Interested in Our Products?</h2>
		<p class="text-sky-200 mb-7 max-w-xl mx-auto text-sm"><?php cases_t( 'Contact our sales team for product specifications, pricing, and export inquiries.', 'Liên hệ đội ngũ kinh doanh để biết thông số sản phẩm, báo giá và yêu cầu xuất khẩu.' ); ?></p>
		<a href="mailto:<?php echo esc_attr( antispambot( cases_opt( 'email' ) ) ); ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-sky-800 rounded-full font-bold shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 text-sm">
			<span class="material-symbols-outlined text-lg" aria-hidden="true">mail</span><?php echo esc_html( antispambot( cases_opt( 'email' ) ) ); ?>
		</a>
	</section>
</main>

<!-- Lightbox -->
<div id="lightbox" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Product details', 'cases' ); ?>">
	<div id="lb-card">
		<div class="lb-top">
			<div class="lb-img-side">
				<button type="button" class="lb-nav-btn lb-btn-prev" data-lb-nav="-1" aria-label="<?php esc_attr_e( 'Previous', 'cases' ); ?>">&#8592;</button>
				<span id="lb-emoji" aria-hidden="true">🦐</span>
				<img id="lb-img" class="hidden absolute inset-0 w-full h-full object-cover" alt="" src="">
				<button type="button" class="lb-nav-btn lb-btn-next" data-lb-nav="1" aria-label="<?php esc_attr_e( 'Next', 'cases' ); ?>">&#8594;</button>
			</div>
			<div class="lb-info-side">
				<div class="lb-tag" id="lb-tag"></div>
				<div class="lb-sku-name" id="lb-sku-name"></div>
				<div class="lb-specs-grid">
					<div class="lb-spec-box"><div class="lb-spec-key"<?php cases_attr( 'Size Range', 'Cỡ' ); ?>>Size Range</div><div class="lb-spec-val" id="lb-spec-size"></div></div>
					<div class="lb-spec-box"><div class="lb-spec-key"<?php cases_attr( 'Pack Form', 'Quy Cách' ); ?>>Pack Form</div><div class="lb-spec-val" id="lb-spec-pack"></div></div>
					<div class="lb-spec-box"><div class="lb-spec-key"<?php cases_attr( 'Origin', 'Xuất Xứ' ); ?>>Origin</div><div class="lb-spec-val" id="lb-spec-origin"></div></div>
					<div class="lb-spec-box"><div class="lb-spec-key"<?php cases_attr( 'Glaze', 'Mạ Băng' ); ?>>Glaze</div><div class="lb-spec-val" id="lb-spec-glaze"></div></div>
				</div>
				<div class="lb-desc-text" id="lb-desc"></div>
			</div>
		</div>
		<div class="lb-bottom">
			<div class="lb-counter-txt" id="lb-counter"></div>
			<div class="lb-dots-row" id="lb-dots"></div>
			<button type="button" class="lb-close-btn" data-lb-close aria-label="<?php esc_attr_e( 'Close', 'cases' ); ?>">&#x2715;</button>
		</div>
	</div>
</div>

<script>window.CASES_PRODUCTS = <?php echo wp_json_encode( $js_groups, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ); ?>;</script>
<?php
get_footer();
