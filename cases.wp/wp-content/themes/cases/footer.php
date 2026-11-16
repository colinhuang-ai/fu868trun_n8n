<?php
/**
 * Site footer + contact slide-over panel.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

$cases_logo  = get_theme_file_uri( 'assets/img/logo.svg' );
$cases_phone = cases_opt( 'phone' );
$cases_email = cases_opt( 'email' );
$cases_site  = cases_opt( 'website' );
?>
<footer class="dark:bg-slate-900 bg-[#071525]" id="contact">
	<div class="max-w-7xl mx-auto px-6 sm:px-12 pt-20 pb-12">
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
			<div class="col-span-1 lg:col-span-2">
				<div class="flex items-center gap-3 mb-6">
					<img alt="" width="48" height="48" class="h-12 w-auto object-contain" src="<?php echo esc_url( $cases_logo ); ?>" loading="lazy">
					<p class="text-2xl font-bold text-white tracking-wide">CASES</p>
				</div>
				<p class="text-sky-100/80 mb-8 max-w-md leading-relaxed">
					<?php cases_t( "CaMau Seafood Processing and Service Joint - Stock Corporation. Leading Vietnam's premium seafood export market since 1996.", 'Công ty CP Chế biến và Dịch vụ Thủy sản Cà Mau. Dẫn đầu thị trường xuất khẩu thủy sản cao cấp Việt Nam từ năm 1996.' ); ?>
				</p>
				<div class="flex items-start space-x-3 group">
					<span class="material-symbols-outlined mt-0.5 text-sky-300" aria-hidden="true">location_on</span>
					<span class="text-sm text-sky-100/90"><?php echo esc_html( cases_opt( 'address' ) ); ?></span>
				</div>
			</div>
			<div>
				<h4 class="text-xs font-bold dark:text-sky-300 uppercase tracking-widest mb-8 text-white"<?php cases_attr( 'Contact Info', 'Thông Tin Liên Hệ' ); ?>>Contact Info</h4>
				<ul class="space-y-4 text-sm font-medium text-sky-100/90">
					<li class="flex items-center space-x-3">
						<span class="material-symbols-outlined text-lg text-sky-400" aria-hidden="true">call</span>
						<a class="hover:text-sky-400 transition-colors" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $cases_phone ) ); ?>"><?php echo esc_html( $cases_phone ); ?></a>
					</li>
					<li class="flex items-center space-x-3">
						<span class="material-symbols-outlined text-lg text-sky-400" aria-hidden="true">mail</span>
						<a class="hover:text-sky-400 transition-colors" href="mailto:<?php echo esc_attr( antispambot( $cases_email ) ); ?>"><?php echo esc_html( antispambot( $cases_email ) ); ?></a>
					</li>
					<li class="flex items-center space-x-3">
						<span class="material-symbols-outlined text-lg text-sky-400" aria-hidden="true">language</span>
						<a class="hover:text-sky-400 transition-colors" href="<?php echo esc_url( $cases_site ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $cases_site ) ) ); ?></a>
					</li>
				</ul>
			</div>
			<div>
				<h4 class="text-xs font-bold dark:text-sky-300 uppercase tracking-widest mb-8 text-white"<?php cases_attr( 'Main Plants', 'Nhà Máy Chính' ); ?>>Main Plants</h4>
				<ul class="space-y-3 text-sm text-sky-100/90">
					<li>DL 113 - Camau</li>
					<li>DL 809 - Camau</li>
					<li>DL 295 - Song Doc</li>
					<li>DL 51 - Tac Cau</li>
					<li>DL 835 - Ganh Hao</li>
				</ul>
			</div>
		</div>
		<div class="flex flex-col md:flex-row justify-between items-center py-12 border-t border-sky-900/50 text-sky-100/70">
			<p class="text-xs font-plus-jakarta-sans uppercase tracking-widest">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> CASES. <?php cases_t( 'All Rights Reserved.', 'Bảo lưu mọi quyền.' ); ?></p>
			<?php
			if ( has_nav_menu( 'legal' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'legal',
						'container'      => false,
						'depth'          => 1,
						'menu_class'     => 'mt-6 md:mt-0 flex flex-wrap gap-x-8 gap-y-2 text-xs font-plus-jakarta-sans uppercase tracking-widest [&_a:hover]:text-sky-400 [&_a:hover]:underline [&_a]:underline-offset-4',
					)
				);
			}
			?>
		</div>
	</div>
</footer>

<?php get_template_part( 'template-parts/contact-panel' ); ?>

<?php wp_footer(); ?>
</body>
</html>
