<?php
/**
 * Infrastructure page (slug: infrastructure).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();

$logo = get_theme_file_uri( 'assets/img/logo.svg' );

// [value, unit label EN, label VI, border class].
$capacity = array(
	array( '20,000', 'Shrimp', 'Tôm', 'border-sky-500' ),
	array( '20,000', 'Surimi', 'Surimi', 'border-sky-500' ),
	array( '3,000', 'Cephalopod', 'Đầu túc', 'border-sky-600' ),
	array( '8,000', 'Fishmeal', 'Bột cá', 'border-sky-600' ),
);

// [code, name EN, name VI, location].
$shrimp_plants = array(
	array( 'DL 113', 'Camau Seafood Processing Corp', 'Nhà Máy Chế Biến Thủy Sản Cà Mau', '04 Nguyen Cong Tru, Ly Van Lam Ward, Camau Province', 'Camau Province' ),
	array( 'DL 809', 'Cangca II Frozen Seafood Factory', 'Nhà Máy Đông Lạnh Cảng Cá II', '04 Nguyen Cong Tru, Ly Van Lam Ward, Camau Province', 'Camau Province' ),
);
$surimi_plants = array(
	array( 'DL 295', 'Songdoc Seafood Processing', 'Chế Biến Thủy Sản Sông Đốc', 'Song Doc Commune, Camau Province' ),
	array( 'DL 51', 'Tac Cau Seafood Processing', 'Chế Biến Thủy Sản Tắc Cậu', 'Tac Cau Industrial Zone, An Giang Province' ),
	array( 'DL 835', 'Ganh Hao Seafood Processing', 'Chế Biến Thủy Sản Gành Hào', 'Ganh Hao Commune, Camau Province' ),
);

// [title EN, title VI, text EN, text VI, photo label].
$tech = array(
	array(
		'IQF Freezing',
		'Đông Lạnh IQF',
		'Super-speed Individually Quick Frozen lines — locking cellular integrity at −40°C for premium texture upon defrost.',
		'Dây chuyền đông lạnh IQF tốc độ cao — khóa độ toàn vẹn tế bào ở −40°C để giữ kết cấu cao cấp sau khi rã đông.',
		'IQF Machine photo',
	),
	array(
		'Blast Freezer',
		'Tủ Đông Gió',
		'Air blast freezing for block and semi-block products — rapid cycles preserve quality for long-haul international export.',
		'Đông lạnh gió cho sản phẩm block và semi-block — chu kỳ nhanh bảo toàn chất lượng cho xuất khẩu quốc tế đường dài.',
		'Blast Freezer photo',
	),
	array(
		'Metal Detector & QC',
		'Máy Dò Kim Loại & QC',
		'100% product scanning at every stage. Zero-tolerance policy before packaging — meeting EU and international food safety standards.',
		'Quét 100% sản phẩm tại mọi công đoạn. Chính sách không khoan nhượng trước đóng gói — đáp ứng tiêu chuẩn ATTP EU và quốc tế.',
		'Metal Detector / QC Lab photo',
	),
	array(
		'Cold Storage',
		'Kho Lạnh',
		'Temperature-controlled cold chain from processing to container loading — maintained at −18°C to −22°C throughout.',
		'Chuỗi lạnh kiểm soát nhiệt độ từ chế biến đến xếp container — duy trì −18°C đến −22°C xuyên suốt.',
		'Cold Storage / Warehouse photo',
	),
);
?>
<main id="main" class="<?php echo esc_attr( cases_layout()['main'] ); ?>">

<?php cases_page_intro( 'light' ); // Page editor content, if any. ?>

<!-- HERO -->
<section class="relative flex items-center min-h-[380px] sm:min-h-[440px] overflow-hidden" style="background:linear-gradient(160deg,#071525 0%,#0d2137 60%,#071525 100%)">
	<div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image:radial-gradient(circle at 1px 1px,#38bdf8 1px,transparent 0);background-size:48px 48px"></div>
	<div class="absolute right-0 top-0 h-full w-1/2 opacity-[0.06] pointer-events-none overflow-hidden flex items-center justify-end pr-8">
		<img alt="" class="h-4/5 w-auto object-contain select-none" src="<?php echo esc_url( $logo ); ?>">
	</div>
	<div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12 py-14 md:py-20 relative z-10 w-full">
		<span class="inline-block py-1.5 px-4 mb-5 rounded-full bg-sky-500/20 text-sky-300 text-xs font-bold tracking-widest uppercase border border-sky-500/30"<?php cases_attr( 'Processing Infrastructure', 'Cơ Sở Hạ Tầng Chế Biến' ); ?>>Processing Infrastructure</span>
		<h1 class="text-3xl sm:text-5xl md:text-6xl font-plus-jakarta-sans font-black text-white leading-tight tracking-tighter mb-5">
			<?php cases_t( 'Where Quality<br/><span class="text-sky-400">Begins.</span>', 'Nơi Chất Lượng<br/><span class="text-sky-400">Khởi Nguồn.</span>' ); ?>
		</h1>
		<p class="text-sky-100/60 text-sm sm:text-base max-w-lg font-light leading-relaxed mb-10">
			<?php cases_t( '5 processing plants · Cà Mau & An Giang · State-of-the-art IQF technology', '5 nhà máy chế biến · Cà Mau & An Giang · Công nghệ IQF tiên tiến hàng đầu' ); ?>
		</p>
		<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-2xl">
			<?php foreach ( $capacity as $c ) : ?>
				<div class="border-l-2 <?php echo esc_attr( $c[3] ); ?> pl-3">
					<div class="text-xl sm:text-2xl font-black text-white"><?php echo esc_html( $c[0] ); ?><span class="text-sky-400 text-xs"> t/yr</span></div>
					<div class="text-sky-100/40 text-xs uppercase tracking-wide mt-0.5"<?php cases_attr( $c[1], $c[2] ); ?>><?php echo esc_html( $c[1] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- PLANTS -->
<section class="py-12 sm:py-16 md:py-20 bg-white">
	<div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12">
		<div class="mb-10">
			<span class="text-sky-600 font-bold uppercase tracking-[0.3em] text-xs mb-2 block"<?php cases_attr( 'Our Facilities', 'Cơ Sở Của Chúng Tôi' ); ?>>Our Facilities</span>
			<h2 class="text-2xl sm:text-3xl font-plus-jakarta-sans font-bold text-slate-900"<?php cases_attr( 'Processing Plants', 'Nhà Máy Chế Biến' ); ?>>Processing Plants</h2>
			<div class="w-12 h-0.5 bg-sky-600 rounded-full mt-3"></div>
		</div>

		<div class="mb-10">
			<div class="flex items-center gap-3 mb-5">
				<span class="px-3 py-1 bg-sky-900 text-sky-200 text-xs font-bold uppercase tracking-widest rounded"><?php cases_t( 'Shrimp', 'Tôm' ); ?> · 18,000–20,000 t/yr</span>
				<div class="flex-1 h-px bg-slate-100"></div>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<?php foreach ( $shrimp_plants as $p ) : ?>
					<div class="border border-slate-200 rounded-xl overflow-hidden hover:border-sky-200 transition-all">
						<?php cases_photo_placeholder( 'Plant photo — ' . $p[0], 'h-48 sm:h-56' ); ?>
						<div class="p-5">
							<div class="flex items-center justify-between mb-2">
								<span class="bg-sky-700 text-white text-xs font-black px-2.5 py-1 rounded"><?php echo esc_html( $p[0] ); ?></span>
								<span class="text-xs text-slate-400"><?php echo esc_html( $p[4] ); ?></span>
							</div>
							<h3 class="font-bold text-slate-900"<?php cases_attr( $p[1], $p[2] ); ?>><?php echo esc_html( $p[1] ); ?></h3>
							<p class="text-slate-400 text-xs mt-1"><?php echo esc_html( $p[3] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div>
			<div class="flex items-center gap-3 mb-5">
				<span class="px-3 py-1 bg-slate-800 text-slate-200 text-xs font-bold uppercase tracking-widest rounded">Surimi · 18,000–20,000 t/yr</span>
				<div class="flex-1 h-px bg-slate-100"></div>
			</div>
			<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
				<?php foreach ( $surimi_plants as $p ) : ?>
					<div class="border border-slate-200 rounded-xl overflow-hidden hover:border-sky-200 transition-all">
						<?php cases_photo_placeholder( 'Plant photo — ' . $p[0], 'h-36' ); ?>
						<div class="p-4">
							<span class="bg-slate-700 text-white text-xs font-black px-2 py-0.5 rounded"><?php echo esc_html( $p[0] ); ?></span>
							<h3 class="font-bold text-slate-900 text-sm mt-2"<?php cases_attr( $p[1], $p[2] ); ?>><?php echo esc_html( $p[1] ); ?></h3>
							<p class="text-slate-400 text-xs mt-1"><?php echo esc_html( $p[3] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<!-- TECHNOLOGY -->
<section class="py-12 sm:py-16 md:py-20 relative overflow-hidden" style="background:linear-gradient(180deg,#071525 0%,#0d2137 50%,#071525 100%)">
	<div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image:radial-gradient(circle at 1px 1px,#38bdf8 1px,transparent 0);background-size:48px 48px"></div>
	<div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12 relative z-10">
		<div class="mb-10">
			<span class="text-sky-400 font-bold uppercase tracking-[0.3em] text-xs mb-2 block"<?php cases_attr( 'Core Technology', 'Công Nghệ Cốt Lõi' ); ?>>Core Technology</span>
			<h2 class="text-2xl sm:text-3xl font-plus-jakarta-sans font-bold text-white"<?php cases_attr( 'Processing Technology', 'Công Nghệ Chế Biến' ); ?>>Processing Technology</h2>
			<div class="w-12 h-0.5 bg-sky-500 rounded-full mt-3"></div>
		</div>
		<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5 mb-8">
			<?php foreach ( $tech as $t ) : ?>
				<div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden hover:border-sky-400/30 transition-all">
					<?php cases_photo_placeholder( $t[4], 'h-44' ); ?>
					<div class="p-6">
						<h3 class="text-white font-bold text-lg mb-2"<?php cases_attr( $t[0], $t[1] ); ?>><?php echo esc_html( $t[0] ); ?></h3>
						<p class="text-sky-100/50 text-sm leading-relaxed"<?php cases_attr( $t[2], $t[3] ); ?>><?php echo esc_html( $t[2] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

</main>
<?php
get_footer();
