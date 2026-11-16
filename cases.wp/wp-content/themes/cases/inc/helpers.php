<?php
/**
 * Template helpers: bilingual output, company info, per-page layout classes.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

/**
 * Echo data-en / data-vi attributes for an element (EN/VI client-side toggle).
 * $en / $vi may contain simple inline HTML (br, span).
 */
function cases_attr( string $en, string $vi ): void {
	printf( ' data-en="%s" data-vi="%s"', esc_attr( $en ), esc_attr( $vi ) );
}

/**
 * Echo a bilingual <span>. EN is rendered by default; JS swaps to VI.
 */
function cases_t( string $en, string $vi ): void {
	echo '<span';
	cases_attr( $en, $vi );
	echo '>' . wp_kses_post( $en ) . '</span>';
}

/**
 * Company info, editable in Customizer > Company Info.
 */
function cases_opt( string $key ): string {
	$defaults = array(
		'phone'   => '+84 290 3669 777',
		'email'   => 'salesmanager@cases.com.vn',
		'address' => '04 Nguyen Cong Tru, Ly Van Lam Ward, Camau Province, Vietnam',
		'website' => 'https://www.cases.com.vn',
	);
	$value = get_theme_mod( 'cases_' . $key, $defaults[ $key ] ?? '' );
	return (string) ( '' !== $value ? $value : ( $defaults[ $key ] ?? '' ) );
}

/**
 * Per-page layout (the prototype styles each page's <html>/<body> differently).
 *
 * @return array{html:string,body:string,main:string}
 */
function cases_layout(): array {
	$slug = '';
	if ( is_front_page() ) {
		$slug = 'home';
	} elseif ( is_page() ) {
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
	}

	$pad = 'pt-[88px] sm:pt-[110px] md:pt-[132px]';

	switch ( $slug ) {
		case 'home':
			return array(
				'html' => 'dark',
				'body' => 'bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark font-sans antialiased transition-colors duration-300',
				'main' => $pad,
			);
		case 'products':
			return array(
				'html' => '',
				'body' => 'bg-[#071525] text-slate-900 font-body',
				'main' => '',
			);
		case 'certifications':
			return array(
				'html' => '',
				'body' => 'dark bg-[#071525] text-white font-body',
				'main' => '',
			);
		default: // about, infrastructure, generic pages, 404, search...
			return array(
				'html' => '',
				'body' => 'bg-white text-slate-900 font-body',
				'main' => $pad,
			);
	}
}

/**
 * Placeholder box used where a photo has not been supplied yet.
 */
function cases_photo_placeholder( string $label, string $height_class = 'h-48' ): void {
	?>
	<div class="<?php echo esc_attr( $height_class ); ?> rounded-xl bg-slate-100 border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 gap-2">
		<svg width="32" height="32" fill="none" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#94a3b8" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" fill="#94a3b8"/><path d="M21 15l-5-5L5 21" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/></svg>
		<span class="text-xs font-medium"><?php echo esc_html( $label ); ?></span>
	</div>
	<?php
}
