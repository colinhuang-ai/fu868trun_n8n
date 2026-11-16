<?php
/**
 * Fallback template (404, search, anything not matched above).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="<?php echo esc_attr( cases_layout()['main'] ); ?>">
	<div class="max-w-3xl mx-auto px-4 sm:px-8 py-24 text-center">
		<?php if ( is_404() ) : ?>
			<p class="text-sky-600 font-black text-7xl mb-4">404</p>
			<h1 class="text-3xl font-plus-jakarta-sans font-black text-slate-900 mb-4"><?php esc_html_e( 'Page not found', 'cases' ); ?></h1>
			<p class="text-slate-500 mb-8"><?php esc_html_e( 'The page you are looking for does not exist or has been moved.', 'cases' ); ?></p>
		<?php else : ?>
			<h1 class="text-3xl font-plus-jakarta-sans font-black text-slate-900 mb-4"><?php bloginfo( 'name' ); ?></h1>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex px-8 py-3.5 bg-sky-700 hover:bg-sky-800 text-white rounded-full font-bold text-sm transition-all"><?php esc_html_e( 'Back to home', 'cases' ); ?></a>
	</div>
</main>
<?php
get_footer();
