<?php
/**
 * Certifications page (slug: certifications).
 * Cards and the overview table come from the "Certificates" post type (Admin → Certificates).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();

$groups   = cases_get_certificate_groups();
$all      = cases_get_certificates();
$n_active = count( array_filter( $all, static fn( $c ) => $c['active'] ) );
$banner   = cases_get_cert_banner( get_queried_object_id() ); // Editable: Pages → Certifications → "Highlight banner".
?>
<main id="main">

<?php
// Page editor content, if any. This template has no top padding of its own, so the intro (or the hero) clears the fixed header.
$has_intro = cases_page_intro( 'dark', true );
?>

<!-- HERO -->
<section class="relative min-h-[420px] sm:min-h-[520px] flex items-center justify-center <?php echo $has_intro ? 'pt-16 sm:pt-20' : 'pt-28 sm:pt-36'; ?> pb-16 overflow-hidden" style="background:linear-gradient(135deg,#071525 0%,#0c2340 40%,#0e3560 70%,#071525 100%);">
	<div class="absolute inset-0 pointer-events-none" style="background-image:linear-gradient(rgba(56,189,248,0.05) 1px,transparent 1px),linear-gradient(90deg,rgba(56,189,248,0.05) 1px,transparent 1px);background-size:48px 48px;"></div>
	<div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse 70% 50% at 50% 90%,rgba(56,189,248,0.1) 0%,transparent 70%);"></div>

	<div class="relative z-10 max-w-5xl mx-auto px-6 text-center">
		<div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-full px-4 py-1.5 text-xs font-semibold tracking-widest uppercase text-sky-300 mb-6 backdrop-blur-sm reveal">
			<span aria-hidden="true">✦</span> <?php cases_t( 'International Certifications', 'Chứng Nhận Quốc Tế' ); ?>
		</div>
		<h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-5 reveal">
			<?php cases_t( 'Quality', 'Chất Lượng' ); ?><br/>
			<span class="text-transparent bg-clip-text" style="background-image:linear-gradient(90deg,#38bdf8,#7dd3fc);"<?php cases_attr( 'Recognised Worldwide', 'Được Công Nhận Toàn Cầu' ); ?>>Recognised Worldwide</span>
		</h1>
		<p class="text-slate-300 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed reveal">
			<?php cases_t( "CASES holds the seafood industry's most respected certifications — from HACCP, ISO 22000 and BRC to BAP, ASC, HALAL and ORGANIC — demonstrating our unwavering commitment to food safety and sustainability.", 'CASES tự hào đạt các chứng nhận uy tín nhất ngành thủy sản — từ HACCP, ISO 22000, BRC đến BAP, ASC, HALAL và ORGANIC — khẳng định cam kết không thỏa hiệp về an toàn thực phẩm và phát triển bền vững.' ); ?>
		</p>
		<div class="mt-10 grid grid-cols-3 gap-4 max-w-lg mx-auto reveal">
			<?php
			$stats = array(
				array( (string) $n_active, 'Certifications', 'Chứng Nhận' ),
				array( '30+', 'Years Experience', 'Năm Kinh Nghiệm' ),
				array( '9+', 'Export Markets', 'Thị Trường XK' ),
			);
			foreach ( $stats as $s ) :
				?>
				<div class="bg-white/5 border border-white/10 rounded-2xl py-4 px-2 text-center backdrop-blur-sm">
					<div class="text-2xl sm:text-3xl font-extrabold text-sky-400"><?php echo esc_html( $s[0] ); ?></div>
					<div class="text-xs text-slate-400 mt-1"<?php cases_attr( $s[1], $s[2] ); ?>><?php echo esc_html( $s[1] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php foreach ( $groups as $group ) : ?>
<section class="py-20 px-4 sm:px-8 max-w-7xl mx-auto">
	<div class="text-center mb-14 reveal">
		<p class="text-sky-500 font-bold uppercase tracking-widest text-xs mb-2"<?php cases_attr( 'Certification Group', 'Nhóm Chứng Nhận' ); ?>>Certification Group</p>
		<h2 class="text-3xl sm:text-4xl font-extrabold text-white"<?php cases_attr( $group['name'], $group['vi'] ); ?>><?php echo esc_html( $group['name'] ); ?></h2>
		<div class="section-line"></div>
	</div>
	<div class="<?php echo esc_attr( $group['grid'] ); ?>">
		<?php foreach ( $group['certs'] as $i => $c ) : ?>
			<div class="cert-card p-7 reveal">
				<div class="flex items-start gap-4 mb-4">
					<div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0 float-el" style="background:rgba(<?php echo esc_attr( $c['icon_rgb'] ); ?>,0.12);border:1px solid rgba(<?php echo esc_attr( $c['icon_rgb'] ); ?>,0.2);animation-delay:<?php echo esc_attr( (string) ( $i * 0.4 ) ); ?>s" aria-hidden="true"><?php echo esc_html( $c['icon'] ); ?></div>
					<div>
						<h3 class="text-xl font-extrabold text-white"><?php echo esc_html( $c['name'] ); ?></h3>
						<?php if ( $c['badge_en'] ) : ?>
							<span class="cert-badge <?php echo esc_attr( $c['badge_cls'] ); ?> mt-1"<?php cases_attr( $c['badge_en'], $c['badge_vi'] ); ?>><?php echo esc_html( $c['badge_en'] ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<p class="text-slate-400 text-sm leading-relaxed"<?php cases_attr( $c['desc_en'], $c['desc_vi'] ); ?>><?php echo esc_html( $c['desc_en'] ); ?></p>
				<?php if ( $c['foot_en'] ) : ?>
					<div class="mt-4 pt-4 border-t border-white/10 flex items-center gap-2 text-xs text-slate-500">
						<span class="<?php echo $c['active'] ? 'text-green-400' : 'text-amber-400'; ?>" aria-hidden="true"><?php echo $c['active'] ? '✓' : '!'; ?></span> <span<?php cases_attr( $c['foot_en'], $c['foot_vi'] ); ?>><?php echo esc_html( $c['foot_en'] ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
<?php endforeach; ?>

<?php if ( ! $groups ) : ?>
	<p class="text-center text-slate-400 py-24"><?php esc_html_e( 'Certificates are coming soon.', 'cases' ); ?></p>
<?php endif; ?>

<!-- HIGHLIGHT BANNER (editable: Pages → Certifications → "Highlight banner") -->
<?php if ( $banner['show'] ) : ?>
<section class="py-8 px-4 sm:px-8 max-w-7xl mx-auto pb-20">
	<div class="reveal rounded-3xl overflow-hidden" style="background:linear-gradient(135deg,#0c2340,#071525);border:1px solid rgba(56,189,248,0.2);">
		<div class="flex flex-col lg:flex-row items-center gap-8 p-8 sm:p-12">
			<div class="text-center lg:text-left flex-1">
				<?php if ( '' !== $banner['eyebrow_en'] ) : ?>
					<p class="text-sky-400 font-bold uppercase tracking-widest text-xs mb-3"<?php cases_attr( $banner['eyebrow_en'], $banner['eyebrow_vi'] ); ?>><?php echo esc_html( $banner['eyebrow_en'] ); ?></p>
				<?php endif; ?>
				<h3 class="text-2xl sm:text-3xl font-extrabold text-white mb-4"<?php cases_attr( $banner['title_en'], $banner['title_vi'] ); ?>><?php echo esc_html( $banner['title_en'] ); ?></h3>
				<p class="text-slate-300 text-sm leading-relaxed max-w-lg"<?php cases_attr( $banner['text_en'], $banner['text_vi'] ); ?>><?php echo esc_html( $banner['text_en'] ); ?></p>
				<?php if ( $banner['tag_list'] ) : ?>
					<div class="mt-6 flex flex-wrap gap-2 justify-center lg:justify-start">
						<?php foreach ( $banner['tag_list'] as $tag ) : ?>
							<span class="market-tag"><?php echo esc_html( $tag ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $banner['icon'] || '' !== $banner['badge_main'] || '' !== $banner['badge_sub'] ) : ?>
				<div class="flex-shrink-0 text-center">
					<?php if ( '' !== $banner['icon'] ) : ?>
						<div class="w-28 h-28 rounded-full flex items-center justify-center mx-auto" style="background:rgba(56,189,248,0.1);border:2px solid rgba(56,189,248,0.3);">
							<span class="text-5xl" aria-hidden="true"><?php echo esc_html( $banner['icon'] ); ?></span>
						</div>
					<?php endif; ?>
					<p class="text-sky-400 font-bold text-sm mt-3"><?php echo esc_html( $banner['badge_main'] ); ?></p>
					<p class="text-slate-500 text-xs"><?php echo esc_html( $banner['badge_sub'] ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- FULL TABLE -->
<?php if ( $all ) : ?>
<section class="py-20 px-4 sm:px-8" style="background:rgba(255,255,255,0.02);">
	<div class="max-w-5xl mx-auto">
		<div class="text-center mb-12 reveal">
			<h2 class="text-2xl sm:text-3xl font-extrabold text-white"<?php cases_attr( 'Full Certification Overview', 'Tổng Hợp Chứng Nhận' ); ?>>Full Certification Overview</h2>
			<div class="section-line"></div>
		</div>
		<div class="overflow-x-auto reveal rounded-2xl border border-white/10 cert-table">
			<table class="w-full text-sm">
				<thead>
					<tr>
						<th class="text-left rounded-tl-2xl"<?php cases_attr( 'Certification', 'Chứng Nhận' ); ?>>Certification</th>
						<th class="text-left hidden sm:table-cell"<?php cases_attr( 'Issuing Body', 'Tổ Chức Cấp' ); ?>>Issuing Body</th>
						<th class="text-left hidden md:table-cell"<?php cases_attr( 'Scope', 'Phạm Vi' ); ?>>Scope</th>
						<th class="text-left rounded-tr-2xl"<?php cases_attr( 'Status', 'Trạng Thái' ); ?>>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $all as $c ) : ?>
						<tr>
							<td class="font-bold text-white"><?php echo esc_html( $c['name'] ); ?></td>
							<td class="text-slate-400 hidden sm:table-cell"><?php echo esc_html( $c['issuer'] ); ?></td>
							<td class="text-slate-400 hidden md:table-cell"<?php cases_attr( $c['scope_en'], $c['scope_vi'] ); ?>><?php echo esc_html( $c['scope_en'] ); ?></td>
							<td>
								<?php if ( $c['active'] ) : ?>
									<span class="cert-badge badge-green"<?php cases_attr( 'Active', 'Hiệu lực' ); ?>>Active</span>
								<?php else : ?>
									<span class="cert-badge badge-amber"<?php cases_attr( 'Expired', 'Hết hạn' ); ?>>Expired</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
<?php endif; ?>

</main>
<?php
get_footer();
