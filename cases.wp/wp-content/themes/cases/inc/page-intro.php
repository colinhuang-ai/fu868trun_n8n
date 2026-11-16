<?php
/**
 * Page intro: prints the page's own editor content (Pages → edit → main editor)
 * at the very top of the page, above the designed sections.
 * Nothing is printed while the editor is empty, so pages look unchanged until content is added.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

/**
 * Echo the current page's editor content.
 *
 * @param string $variant      'light' (white background) or 'dark' (navy background).
 * @param bool   $clear_header Add top padding to clear the fixed header (for templates whose <main> has none).
 * @return bool Whether anything was printed.
 */
function cases_page_intro( string $variant = 'light', bool $clear_header = false ): bool {
	$page = get_queried_object();
	if ( ! $page instanceof WP_Post || '' === trim( $page->post_content ) ) {
		return false;
	}

	$html = apply_filters( 'the_content', $page->post_content );
	if ( '' === trim( wp_strip_all_tags( $html, true ) ) && false === strpos( $html, '<img' ) && false === strpos( $html, '<iframe' ) ) {
		return false;
	}

	$class = 'entry-content' . ( 'dark' === $variant ? ' entry-content--dark' : '' );
	$pad   = $clear_header ? 'pt-36 sm:pt-44' : 'pt-10 sm:pt-14';
	?>
	<section class="page-intro max-w-4xl mx-auto px-4 sm:px-8 <?php echo esc_attr( $pad ); ?> pb-10 sm:pb-14">
		<div class="<?php echo esc_attr( $class ); ?>"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- core the_content filter output. ?></div>
	</section>
	<?php
	return true;
}
