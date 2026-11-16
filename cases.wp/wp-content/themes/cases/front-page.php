<?php
/**
 * Home page.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();

$logo = get_theme_file_uri( 'assets/img/logo.svg' );

// [flag, name, turnover, tons EN, tons VI, bar %].
$markets = array(
	array( '🇪🇺', 'EU', '$56.6M', '7,638 tons', '7.638 tấn', 100 ),
	array( '🇦🇺', 'Australia', '$34.5M', '3,536 tons', '3.536 tấn', 61 ),
	array( '🇰🇷', 'Korea', '$29.9M', '3,913 tons', '3.913 tấn', 53 ),
	array( '🇺🇸', 'USA', '$15.1M', '695 tons', '695 tấn', 27 ),
	array( '🇯🇵', 'Japan', '$2.2M', '135 tons', '135 tấn', 4 ),
	array( '🇦🇪', 'UAE', '$5.6M', '675 tons', '675 tấn', 10 ),
	array( '🇨🇦', 'Canada', '$3.5M', '140 tons', '140 tấn', 6 ),
	array( '🇹🇼', 'Taiwan', '$2.3M', '210 tons', '210 tấn', 4 ),
);

// [icon, title EN, title VI, text EN, text VI].
$advantages = array(
	array(
		'factory',
		'6 Processing Plants',
		'6 Nhà Máy Chế Biến',
		'Strategically located across Ca Mau, An Giang & Song Doc — with IQF machines, blast freezers & metal detectors ensuring premium output.',
		'Tọa lạc chiến lược tại Cà Mau, An Giang & Sông Đốc — trang bị máy IQF, tủ đông gió và máy dò kim loại đảm bảo chất lượng đầu ra cao cấp.',
	),
	array(
		'workspace_premium',
		'Top-Tier Certifications',
		'Chứng Nhận Hàng Đầu',
		'HACCP · ISO 22000 · BRC · HALAL · BAP · ASC — meeting the strictest food safety standards for EU, Japan, USA & global buyers.',
		'HACCP · ISO 22000 · BRC · HALAL · BAP · ASC — đáp ứng tiêu chuẩn ATTP nghiêm ngặt nhất cho EU, Nhật Bản, Mỹ & người mua toàn cầu.',
	),
	array(
		'diversity_3',
		'Full Product Range',
		'Đa Dạng Sản Phẩm',
		'Black Tiger Prawn, Vannamei Shrimp, Surimi, Cephalopods & Fish Meal — raw, cooked, value-added, and custom spec available.',
		'Tôm Sú, Tôm Thẻ, Surimi, Nhuyễn thể & Bột cá — dạng sống, chín, chế biến sâu và theo yêu cầu riêng.',
	),
	array(
		'verified_user',
		'29+ Years Experience',
		'29+ Năm Kinh Nghiệm',
		'Founded 1996. Nearly three decades of expertise in frozen seafood processing, export compliance and international logistics.',
		'Thành lập 1996. Gần ba thập kỷ kinh nghiệm chế biến thủy sản đông lạnh, tuân thủ xuất khẩu và logistics quốc tế.',
	),
	array(
		'public',
		'9 Export Markets',
		'9 Thị Trường Xuất Khẩu',
		'Trusted by buyers across EU, Japan, Korea, Australia, USA, UAE and more — with $184M turnover and 29,024 tons shipped in 2024.',
		'Được tin dùng bởi nhà mua hàng tại EU, Nhật, Hàn, Úc, Mỹ, UAE và nhiều hơn — doanh thu 184 triệu USD và 29.024 tấn xuất khẩu năm 2024.',
	),
);

$products_url       = home_url( '/products/' );
$certifications_url = home_url( '/certifications/' );
?>
<main id="main" class="<?php echo esc_attr( cases_layout()['main'] ); ?>">

<?php cases_page_intro( 'dark' ); // Page editor content, if any. ?>

<!-- HERO -->
<section class="relative min-h-[420px] sm:min-h-[550px] md:h-[700px] flex items-center justify-center hero-gradient text-white overflow-hidden">
	<div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
		<img alt="" class="w-[85vw] h-auto object-contain absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 select-none pointer-events-none max-w-none opacity-20" src="<?php echo esc_url( $logo ); ?>">
	</div>
	<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
		<span class="inline-block py-1.5 px-4 rounded-full bg-sky-500/80 text-xs font-bold tracking-widest uppercase mb-6 backdrop-blur-md border border-white/20"><?php cases_t( 'Premium Seafood Export · Est. 1996', 'Xuất Khẩu Thủy Sản Cao Cấp · Thành Lập 1996' ); ?></span>
		<h1 class="text-5xl md:text-7xl font-display font-bold leading-[1.1] mb-8 drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)] reveal">
			<?php cases_t( "Delivering the Oceans' Best<br/> to the World", 'Mang Tinh Hoa Đại Dương<br/> Ra Thế Giới' ); ?>
		</h1>
		<p class="text-xl md:text-2xl max-w-3xl mx-auto font-light mb-12 text-sky-50/90 drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)] reveal">
			<?php cases_t( 'Specializing in Premium Black Tiger & Vannamei Shrimp — from Cà Mau, Vietnam to global markets.', 'Chuyên xuất khẩu Tôm Sú và Tôm Thẻ chân trắng cao cấp — từ Cà Mau, Việt Nam ra thị trường toàn cầu.' ); ?>
		</p>
		<div class="flex flex-col sm:flex-row justify-center gap-6">
			<a class="px-10 py-4 bg-sky-600 hover:bg-sky-700 text-white rounded-full font-bold shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center group" href="<?php echo esc_url( $products_url ); ?>">
				<?php cases_t( 'Explore Products', 'Xem Sản Phẩm' ); ?>
				<span class="material-symbols-outlined ml-2 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">arrow_forward</span>
			</a>
			<a class="px-10 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-xl border border-white/30 text-white rounded-full font-bold shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center" href="<?php echo esc_url( $certifications_url ); ?>">
				<?php cases_t( 'Our Certifications', 'Chứng Nhận Của Chúng Tôi' ); ?>
			</a>
		</div>
	</div>
</section>

<!-- EU EXPORT CODES -->
<section class="py-12 border-b border-sky-100 bg-slate-50 dark:bg-slate-900 dark:border-slate-800">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="text-center mb-10 reveal">
			<h2 class="text-sm uppercase tracking-[0.25em] mb-3 font-black text-slate-500 dark:text-slate-400"<?php cases_attr( 'Certified European Union Export Codes', 'Mã Xuất Khẩu Liên Minh Châu Âu' ); ?>>Certified European Union Export Codes</h2>
			<div class="w-16 h-0.5 bg-sky-400/40 mx-auto rounded"></div>
		</div>
		<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 reveal">
			<?php foreach ( array( 'DL 113', 'DL 809', 'DL 51', 'DL 295', 'DL 835', 'DL 684' ) as $code ) : ?>
				<div class="backdrop-blur-md border rounded-xl px-4 py-5 flex items-center justify-center group transition-colors cursor-default shadow-lg shadow-black/20 bg-sky-900 hover:bg-sky-800 border-sky-700/50">
					<span class="material-symbols-outlined text-white mr-2 text-xl group-hover:scale-110 transition-transform" aria-hidden="true">verified</span>
					<span class="text-xl font-bold text-white tracking-wide"><?php echo esc_html( $code ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- GLOBAL REACH -->
<section class="py-14 md:py-24 relative overflow-hidden" id="about" style="background: linear-gradient(180deg, #071525 0%, #0d2137 50%, #071525 100%);">
	<div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, #38bdf8 1px, transparent 0); background-size: 48px 48px;"></div>
	<div class="absolute top-1/2 left-1/4 w-96 h-96 bg-sky-500/10 rounded-full blur-[80px] -translate-y-1/2 pointer-events-none"></div>
	<div class="absolute top-1/2 right-1/4 w-96 h-96 bg-sky-500/10 rounded-full blur-[80px] -translate-y-1/2 pointer-events-none"></div>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

		<div class="text-center mb-12 reveal">
			<span class="text-sky-400 font-bold uppercase tracking-[0.3em] text-sm mb-4 block"<?php cases_attr( 'Performance & Export Reach', 'Năng Lực & Thị Trường Xuất Khẩu' ); ?>>Performance &amp; Export Reach</span>
			<h2 class="text-3xl md:text-4xl lg:text-5xl font-display font-bold text-white mb-4"<?php cases_attr( 'Our Global Reach', 'Tầm Vươn Toàn Cầu' ); ?>>Our Global Reach</h2>
			<div class="w-20 h-1 bg-gradient-to-r from-sky-400 to-sky-600 mx-auto mb-6 rounded-full"></div>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 reveal">
			<div class="bg-white/5 border border-white/10 rounded-2xl p-7 text-center hover:bg-white/10 transition-all">
				<div class="text-5xl font-black text-sky-300 mb-2">29,024</div>
				<div class="text-white/50 text-xs uppercase tracking-widest mb-1"<?php cases_attr( 'Tons Exported', 'Tấn Xuất Khẩu' ); ?>>Tons Exported</div>
				<div class="text-white font-semibold"<?php cases_attr( '2024 Total Volume', 'Tổng Sản Lượng 2024' ); ?>>2024 Total Volume</div>
			</div>
			<div class="bg-white/5 border border-sky-400/30 rounded-2xl p-7 text-center hover:bg-white/10 transition-all" style="box-shadow: 0 0 40px rgba(56,189,248,0.08);">
				<div class="text-5xl font-black text-sky-300 mb-2">$184M</div>
				<div class="text-white/50 text-xs uppercase tracking-widest mb-1"<?php cases_attr( 'Export Turnover', 'Doanh Thu Xuất Khẩu' ); ?>>Export Turnover</div>
				<div class="text-white font-semibold"<?php cases_attr( '2024 Revenue', 'Doanh Thu 2024' ); ?>>2024 Revenue</div>
			</div>
			<div class="bg-white/5 border border-white/10 rounded-2xl p-7 text-center hover:bg-white/10 transition-all" id="certifications">
				<div class="text-5xl font-black text-white mb-2">6+</div>
				<div class="text-white/50 text-xs uppercase tracking-widest mb-1"<?php cases_attr( 'Certifications', 'Chứng Nhận' ); ?>>Certifications</div>
				<div class="text-white font-semibold">HACCP · ISO · BRC · BAP · ASC · HALAL</div>
			</div>
		</div>

		<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-10 reveal">
			<?php foreach ( $markets as $m ) : ?>
				<div class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-sky-400/40 rounded-xl p-4 transition-all cursor-default">
					<div class="text-2xl mb-2"><?php echo esc_html( $m[0] ); ?></div>
					<div class="text-white font-bold text-sm mb-1"><?php echo esc_html( $m[1] ); ?></div>
					<div class="text-sky-300 text-lg font-black"><?php echo esc_html( $m[2] ); ?></div>
					<div class="text-white/40 text-xs"<?php cases_attr( $m[3], $m[4] ); ?>><?php echo esc_html( $m[3] ); ?></div>
					<div class="mt-2 h-0.5 rounded-full bg-white/10"><div class="h-full bg-sky-400 rounded-full" style="width:<?php echo (int) $m[5]; ?>%"></div></div>
				</div>
			<?php endforeach; ?>
			<div class="bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl p-4 transition-all cursor-default">
				<div class="text-2xl mb-2">🌏</div>
				<div class="text-white font-bold text-sm mb-1"<?php cases_attr( 'Others', 'Khác' ); ?>>Others</div>
				<div class="text-slate-300 text-lg font-black">$13.2M</div>
				<div class="text-white/40 text-xs"<?php cases_attr( '1,827 tons', '1.827 tấn' ); ?>>1,827 tons</div>
				<div class="mt-2 h-0.5 rounded-full bg-white/10"><div class="h-full bg-slate-400 rounded-full" style="width:23%"></div></div>
			</div>
			<div class="rounded-xl p-4 flex flex-col justify-center" style="background: linear-gradient(135deg,#0369a1,#065f46);">
				<div class="text-white/60 text-xs uppercase tracking-widest mb-1">Total 2024</div>
				<div class="text-white text-2xl font-black">$184M</div>
				<div class="text-sky-200 text-xs"><?php cases_t( '29,024 tons · 9 markets', '29.024 tấn · 9 thị trường' ); ?></div>
			</div>
		</div>
	</div>
</section>

<!-- WHY CHOOSE CASES -->
<section class="py-14 md:py-24 bg-white dark:bg-slate-950 overflow-hidden">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="text-center mb-14 reveal">
			<span class="text-sky-600 font-bold uppercase tracking-[0.3em] text-sm mb-3 block"<?php cases_attr( 'Our Advantages', 'Lợi Thế Của Chúng Tôi' ); ?>>Our Advantages</span>
			<h2 class="text-4xl md:text-5xl font-display font-bold text-slate-900 dark:text-white mb-4"<?php cases_attr( 'Why Choose CASES?', 'Tại Sao Chọn CASES?' ); ?>>Why Choose CASES?</h2>
			<div class="w-20 h-1 bg-gradient-to-r from-sky-500 to-sky-700 mx-auto rounded-full"></div>
		</div>
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 reveal">
			<?php foreach ( $advantages as $a ) : ?>
				<div class="group p-7 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 hover:border-sky-200 hover:shadow-lg transition-all duration-300">
					<div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/50 flex items-center justify-center mb-5">
						<span class="material-symbols-outlined text-sky-600 text-2xl" aria-hidden="true"><?php echo esc_html( $a[0] ); ?></span>
					</div>
					<h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2"<?php cases_attr( $a[1], $a[2] ); ?>><?php echo esc_html( $a[1] ); ?></h3>
					<p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed"<?php cases_attr( $a[3], $a[4] ); ?>><?php echo esc_html( $a[3] ); ?></p>
				</div>
			<?php endforeach; ?>

			<div class="group p-7 rounded-2xl border border-sky-200 dark:border-sky-800 bg-gradient-to-br from-sky-50 to-sky-100 dark:from-sky-950 dark:to-sky-900 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
				<div>
					<div class="w-12 h-12 rounded-xl bg-sky-600 flex items-center justify-center mb-5">
						<span class="material-symbols-outlined text-white text-2xl" aria-hidden="true">handshake</span>
					</div>
					<h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2"<?php cases_attr( 'Ready to Partner?', 'Sẵn Sàng Hợp Tác?' ); ?>>Ready to Partner?</h3>
					<p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-6"<?php cases_attr( 'Contact our sales team for product specs, pricing and custom orders.', 'Liên hệ đội ngũ kinh doanh để biết thông số sản phẩm, báo giá và đơn hàng theo yêu cầu.' ); ?>>Contact our sales team for product specs, pricing and custom orders.</p>
				</div>
				<button type="button" data-open-contact class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-700 hover:bg-sky-800 text-white rounded-full text-sm font-bold transition-all w-fit">
					<?php cases_t( 'Contact Us', 'Liên Hệ' ); ?> <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
				</button>
			</div>
		</div>
	</div>
</section>

</main>
<?php
get_footer();
