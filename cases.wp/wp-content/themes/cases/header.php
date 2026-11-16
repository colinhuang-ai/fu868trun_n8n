<?php
/**
 * Site header (fixed bar: logo, brand, EN/VI toggle, contact button, nav).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

$cases_l    = cases_layout();
$cases_logo = get_theme_file_uri( 'assets/img/logo.svg' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php echo esc_attr( $cases_l['html'] ); ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<script>
// Run before first paint so the saved language does not flash English.
(function () {
	try {
		if (localStorage.getItem('cases_lang') === 'vi') document.documentElement.setAttribute('data-lang', 'vi');
	} catch (e) {}
})();
</script>
<noscript><style>.reveal{opacity:1!important;transform:none!important}html[data-lang="vi"] [data-vi]{visibility:visible!important}</style></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class( $cases_l['body'] ); ?>>
<?php wp_body_open(); ?>
<a class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[300] focus:bg-white focus:text-slate-900 focus:px-4 focus:py-2 focus:rounded" href="#main"><?php esc_html_e( 'Skip to content', 'cases' ); ?></a>

<header id="site-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500">
	<div class="header-inner flex flex-col items-center w-full py-2 px-3 sm:py-4 sm:px-8 max-w-[1920px] mx-auto transition-all duration-500">
		<div class="w-full flex items-center justify-between mb-2 sm:mb-4">
			<!-- Logo -->
			<div class="flex-1">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="inline-block">
					<img alt="CASES Logo" width="56" height="56" class="h-9 sm:h-12 md:h-14 w-auto object-contain transition-transform hover:scale-105" src="<?php echo esc_url( $cases_logo ); ?>">
				</a>
			</div>
			<!-- Brand -->
			<div class="flex-shrink-0 text-center">
				<p class="text-[1.4rem] sm:text-[2rem] md:text-[2.6rem] font-plus-jakarta-sans font-black tracking-tighter text-sky-300 uppercase leading-none">CASES</p>
				<p class="hidden sm:block text-[0.72rem] font-plus-jakarta-sans font-semibold tracking-[0.22em] text-sky-400 uppercase mt-1">CA MAU SEAFOOD</p>
			</div>
			<!-- Language + contact -->
			<div class="flex-1 flex items-center justify-end space-x-2 sm:space-x-6">
				<div class="flex items-center space-x-2 text-sm font-medium text-white/80">
					<button type="button" data-lang-en class="text-sky-400 font-bold hover:opacity-80 transition-all text-xs sm:text-sm">EN</button>
					<span class="text-slate-300" aria-hidden="true">|</span>
					<button type="button" data-lang-vi class="text-white/60 hover:text-sky-400 transition-colors text-xs sm:text-sm">VI</button>
				</div>
				<button type="button" data-open-contact class="px-3 py-1.5 sm:px-6 sm:py-2.5 bg-sky-700 hover:bg-sky-800 text-white rounded-full text-xs sm:text-sm font-bold shadow-md transition-all"<?php cases_attr( 'Contact', 'Liên Hệ' ); ?>>Contact</button>
			</div>
		</div>
		<button type="button" id="hamburger-icon" aria-controls="mobile-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'cases' ); ?>" class="md:hidden text-white text-2xl font-bold px-2">&#9776;</button>
		<nav class="hidden md:flex items-center space-x-10" aria-label="<?php esc_attr_e( 'Primary', 'cases' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
					'walker'         => new Cases_Nav_Walker(),
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>
	<div id="mobile-menu" class="hidden md:hidden bg-[#071525] border-t border-white/10 px-6 py-4 flex flex-col space-y-4">
		<?php
		wp_nav_menu(
			array(
				'theme_location'  => 'primary',
				'container'       => false,
				'items_wrap'      => '%3$s',
				'depth'           => 1,
				'walker'          => new Cases_Nav_Walker(),
				'fallback_cb'     => false,
				'cases_mobile'    => true,
			)
		);
		?>
	</div>
</header>
