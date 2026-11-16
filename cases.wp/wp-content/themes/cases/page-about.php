<?php
/**
 * About Us page (slug: about).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();

$logo = get_theme_file_uri( 'assets/img/logo.svg' );

// [year, title EN, title VI, text EN, text VI, icon, caption EN, caption VI].
$timeline = array(
	array(
		'1996',
		'Founded as State-Owned Enterprise',
		'Thành Lập Doanh Nghiệp Nhà Nước',
		"CaMau Seafood Processing and Service Corp established in Cà Mau — Vietnam's southernmost province and seafood capital of the Mekong Delta.",
		'Công ty Chế biến Thủy sản Cà Mau thành lập tại Cà Mau — tỉnh cực nam Việt Nam và thủ đô thủy sản đồng bằng sông Cửu Long.',
		'corporate_fare',
		'Began with shrimp processing for domestic & early export markets',
		'Bắt đầu chế biến tôm phục vụ thị trường trong nước & xuất khẩu ban đầu',
	),
	array(
		'2000s',
		'Expansion & Certification',
		'Mở Rộng & Chứng Nhận',
		'Invested in IQF technology, blast freezers and metal detectors. Obtained HACCP, ISO 22000 and BRC certifications — opening doors to EU and Japanese markets.',
		'Đầu tư công nghệ IQF, tủ đông gió và máy dò kim loại. Đạt chứng nhận HACCP, ISO 22000 và BRC — mở cửa thị trường EU và Nhật Bản.',
		'workspace_premium',
		'First EU export certifications secured · Global reach begins',
		'Đạt mã xuất khẩu EU đầu tiên · Bắt đầu vươn ra thị trường quốc tế',
	),
	array(
		'2016',
		'Equitization to Joint-Stock',
		'Cổ Phần Hóa',
		'Under government policy, CASES transitioned from state-owned to joint-stock company — unlocking capital for modernisation and accelerating global expansion.',
		'Theo chính sách nhà nước, CASES chuyển đổi từ doanh nghiệp nhà nước sang công ty cổ phần — mở khóa vốn để hiện đại hóa và đẩy nhanh mở rộng toàn cầu.',
		'trending_up',
		'Joint-stock model · Trade name "CASES" officially adopted',
		'Mô hình cổ phần · Tên thương mại "CASES" được chính thức đặt',
	),
	array(
		'Today',
		'Top-Tier Global Exporter',
		'Nhà Xuất Khẩu Hàng Đầu Toàn Cầu',
		'6 processing plants across Ca Mau & An Giang. $184M export turnover in 2024. Supplying EU, Japan, Korea, Australia, USA and more with premium frozen seafood.',
		'6 nhà máy chế biến tại Cà Mau & An Giang. Doanh thu xuất khẩu 184 triệu USD năm 2024. Cung cấp thủy sản đông lạnh cao cấp cho EU, Nhật, Hàn, Úc, Mỹ và nhiều hơn.',
		'public',
		'$184M turnover · 29,024 tons · 9 export markets · 2024',
		'184 triệu USD · 29.024 tấn · 9 thị trường xuất khẩu · 2024',
	),
);
$last = count( $timeline ) - 1;
?>
<main id="main" class="<?php echo esc_attr( cases_layout()['main'] ); ?>">

<?php cases_page_intro( 'light' ); // Page editor content, if any. ?>

<!-- HERO -->
<section class="relative min-h-[380px] sm:min-h-[450px] md:h-[520px] flex items-center overflow-hidden" style="background: linear-gradient(160deg, #071525 0%, #0d2137 60%, #071525 100%);">
	<div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, #38bdf8 1px, transparent 0); background-size: 48px 48px;"></div>
	<div class="absolute right-0 top-0 h-full w-1/2 opacity-10 pointer-events-none overflow-hidden">
		<img alt="" class="h-full w-auto object-contain object-right select-none" src="<?php echo esc_url( $logo ); ?>">
	</div>
	<div class="max-w-7xl mx-auto px-8 relative z-10">
		<span class="inline-block py-1.5 px-4 mb-6 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold tracking-widest uppercase border border-sky-500/30 reveal">Est. 1996 · Cà Mau, Vietnam</span>
		<h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl font-plus-jakarta-sans font-black text-white leading-tight tracking-tighter mb-6">
			<?php cases_t( 'Our Story,<br/><span class="text-sky-400">Our Ocean.</span>', 'Câu Chuyện Của Chúng Tôi,<br/><span class="text-sky-400">Đại Dương Của Chúng Tôi.</span>' ); ?>
		</h1>
		<p class="text-sky-100/60 text-xl max-w-xl font-light leading-relaxed">
			<?php cases_t( 'From a state-owned enterprise in the Mekong Delta to a global seafood powerhouse — nearly 3 decades of excellence.', 'Từ doanh nghiệp nhà nước ở đồng bằng sông Cửu Long đến cường quốc thủy sản toàn cầu — gần 3 thập kỷ xuất sắc.' ); ?>
		</p>
	</div>
</section>

<!-- TIMELINE -->
<section class="py-14 md:py-24 bg-white">
	<div class="max-w-7xl mx-auto px-4 sm:px-8">
		<div class="text-center mb-16 reveal">
			<span class="text-sky-600 font-bold uppercase tracking-[0.3em] text-sm mb-3 block"<?php cases_attr( 'Our Journey', 'Hành Trình Của Chúng Tôi' ); ?>>Our Journey</span>
			<h2 class="text-4xl md:text-5xl font-plus-jakarta-sans font-bold text-slate-900 mb-4"<?php cases_attr( 'The CASES Story', 'Hành Trình CASES' ); ?>>The CASES Story</h2>
			<div class="w-20 h-1 bg-gradient-to-r from-sky-500 to-sky-700 mx-auto rounded-full"></div>
		</div>
		<div class="relative">
			<div class="absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-0.5 bg-sky-100 hidden md:block"></div>
			<div class="space-y-12">
				<?php foreach ( $timeline as $i => $t ) : ?>
					<?php
					$odd    = ( 0 === $i % 2 );
					$is_now = ( $i === $last );
					?>
					<div class="relative flex flex-col <?php echo $odd ? 'md:flex-row' : 'md:flex-row-reverse'; ?> items-center gap-8 reveal">
						<div class="md:w-1/2 <?php echo $odd ? 'md:text-right md:pr-16' : 'md:text-left md:pl-16'; ?>">
							<span class="text-sky-600 font-black text-4xl block mb-2"><?php echo esc_html( $t[0] ); ?></span>
							<h3 class="text-xl font-bold text-slate-900 mb-2"<?php cases_attr( $t[1], $t[2] ); ?>><?php echo esc_html( $t[1] ); ?></h3>
							<p class="text-slate-500 leading-relaxed"<?php cases_attr( $t[3], $t[4] ); ?>><?php echo esc_html( $t[3] ); ?></p>
						</div>
						<div class="hidden md:flex absolute left-1/2 -translate-x-1/2 w-5 h-5 rounded-full <?php echo $is_now ? 'bg-sky-700 ring-4 ring-sky-200' : 'bg-sky-600'; ?> border-4 border-white shadow-lg"></div>
						<div class="md:w-1/2 <?php echo $odd ? 'md:pl-16' : 'md:pr-16 md:text-right'; ?>">
							<?php if ( $is_now ) : ?>
								<div class="bg-gradient-to-br from-sky-600 to-sky-800 rounded-2xl p-5">
									<span class="material-symbols-outlined text-white text-3xl" aria-hidden="true"><?php echo esc_html( $t[5] ); ?></span>
									<p class="text-sm text-sky-100 font-medium mt-2"<?php cases_attr( $t[6], $t[7] ); ?>><?php echo esc_html( $t[6] ); ?></p>
								</div>
							<?php else : ?>
								<div class="bg-sky-50 rounded-2xl p-5 border border-sky-100">
									<span class="material-symbols-outlined text-sky-600 text-3xl" aria-hidden="true"><?php echo esc_html( $t[5] ); ?></span>
									<p class="text-sm text-sky-700 font-medium mt-2"<?php cases_attr( $t[6], $t[7] ); ?>><?php echo esc_html( $t[6] ); ?></p>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<!-- MISSION & VISION -->
<section class="py-14 md:py-24 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-8">
		<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-14 reveal">
			<div class="bg-white rounded-2xl p-10 border border-slate-100 shadow-sm hover:shadow-md transition-all">
				<div class="w-12 h-12 rounded-xl bg-sky-100 flex items-center justify-center mb-6">
					<span class="material-symbols-outlined text-sky-600 text-2xl" aria-hidden="true">flag</span>
				</div>
				<h3 class="text-2xl font-bold text-slate-900 mb-4"<?php cases_attr( 'Our Mission', 'Sứ Mệnh' ); ?>>Our Mission</h3>
				<p class="text-slate-500 leading-relaxed text-lg italic"<?php cases_attr( "\"To deliver the finest frozen seafood from Vietnam's richest waters to the world's most discerning buyers — with uncompromising quality, traceability and service.\"", '"Mang tinh hoa thủy sản đông lạnh từ vùng biển giàu có nhất Việt Nam đến tay khách hàng khó tính nhất thế giới — với chất lượng không thỏa hiệp, truy xuất nguồn gốc và dịch vụ tận tâm."' ); ?>>"To deliver the finest frozen seafood from Vietnam's richest waters to the world's most discerning buyers — with uncompromising quality, traceability and service."</p>
			</div>
			<div class="bg-white rounded-2xl p-10 border border-slate-100 shadow-sm hover:shadow-md transition-all">
				<div class="w-12 h-12 rounded-xl bg-sky-100 flex items-center justify-center mb-6">
					<span class="material-symbols-outlined text-sky-600 text-2xl" aria-hidden="true">visibility</span>
				</div>
				<h3 class="text-2xl font-bold text-slate-900 mb-4"<?php cases_attr( 'Our Vision', 'Tầm Nhìn' ); ?>>Our Vision</h3>
				<p class="text-slate-500 leading-relaxed text-lg italic"<?php cases_attr( "\"To be Vietnam's most trusted seafood export partner — recognised globally for sustainable practices, premium products and long-term buyer relationships.\"", '"Trở thành đối tác xuất khẩu thủy sản uy tín nhất Việt Nam — được công nhận toàn cầu vì thực hành bền vững, sản phẩm cao cấp và quan hệ lâu dài với người mua."' ); ?>>"To be Vietnam's most trusted seafood export partner — recognised globally for sustainable practices, premium products and long-term buyer relationships."</p>
			</div>
		</div>

		<div class="rounded-2xl p-10 text-center reveal" style="background: linear-gradient(135deg, #071525, #0d2137);">
			<span class="material-symbols-outlined text-sky-400 text-4xl mb-4 block" aria-hidden="true">format_quote</span>
			<h3 class="text-3xl md:text-4xl font-plus-jakarta-sans font-black text-white mb-4 italic"<?php cases_attr( '"Quality is Life"', '"Chất Lượng Là Sự Sống Còn"' ); ?>>"Quality is Life"</h3>
			<p class="text-sky-100/60 text-lg max-w-2xl mx-auto"><?php cases_t( '“Chất lượng là sự sống còn” — the founding principle that has guided every decision at CASES for nearly three decades.', '“Chất lượng là sự sống còn” — nguyên tắc sáng lập đã định hướng mọi quyết định tại CASES trong gần ba thập kỷ.' ); ?></p>
			<div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
				<a href="<?php echo esc_url( home_url( '/products/' ) ); ?>" class="px-8 py-3.5 bg-sky-700 hover:bg-sky-600 text-white rounded-full font-bold text-sm transition-all inline-flex items-center justify-center gap-2">
					<?php cases_t( 'View Our Products', 'Xem Sản Phẩm' ); ?> <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
				</a>
				<button type="button" data-open-contact class="px-8 py-3.5 border border-white/20 hover:bg-white/10 text-white rounded-full font-bold text-sm transition-all">
					<?php cases_t( 'Contact Sales Team', 'Liên Hệ Đội Bán Hàng' ); ?>
				</button>
			</div>
		</div>
	</div>
</section>

</main>
<?php
get_footer();
