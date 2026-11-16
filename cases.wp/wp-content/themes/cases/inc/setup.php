<?php
/**
 * Theme setup: supports, menus, assets, nav walker.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		register_nav_menus(
			array(
				'primary' => __( 'Primary menu', 'cases' ),
				'legal'   => __( 'Footer legal links', 'cases' ),
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$fonts = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap';
		wp_enqueue_style( 'cases-fonts', $fonts, array(), null );
		wp_enqueue_style(
			'cases-icons',
			'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap',
			array(),
			null
		);
		wp_enqueue_style( 'cases-app', get_theme_file_uri( 'assets/css/app.css' ), array(), (string) filemtime( get_theme_file_path( 'assets/css/app.css' ) ) );
		wp_enqueue_script( 'cases-main', get_theme_file_uri( 'assets/js/main.js' ), array(), (string) filemtime( get_theme_file_path( 'assets/js/main.js' ) ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script(
			'cases-main',
			'CASES',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'cases_contact' ),
			)
		);
	}
);

// Preconnect to Google Fonts.
add_filter(
	'wp_resource_hints',
	static function ( array $urls, string $relation ): array {
		if ( 'preconnect' === $relation ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}
		return $urls;
	},
	10,
	2
);

// Remove emoji scripts (flags are rendered natively) and unneeded head clutter.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

/**
 * Vietnamese labels for the primary menu. Pages are matched by slug, so renaming
 * a page in the CMS does not lose its translation; other items fall back to the title.
 */
function cases_menu_vi( string $title, int $page_id = 0 ): string {
	$by_slug = array(
		'home'           => 'Trang Chủ',
		'about'          => 'Về Chúng Tôi',
		'products'       => 'Sản Phẩm',
		'infrastructure' => 'Cơ Sở Hạ Tầng',
		'certifications' => 'Chứng Nhận',
		'news'           => 'Tin Tức',
	);
	if ( $page_id ) {
		$slug = (string) get_post_field( 'post_name', $page_id );
		if ( isset( $by_slug[ $slug ] ) ) {
			return $by_slug[ $slug ];
		}
	}
	$by_title = array(
		'Home'           => 'Trang Chủ',
		'About Us'       => 'Về Chúng Tôi',
		'Products'       => 'Sản Phẩm',
		'Infrastructure' => 'Cơ Sở Hạ Tầng',
		'Certifications' => 'Chứng Nhận',
		'News'           => 'Tin Tức',
	);
	return $by_title[ $title ] ?? '';
}

/**
 * Walker that outputs bare <a> elements (the prototype's nav has no <li>).
 */
class Cases_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$active  = (bool) array_intersect( array( 'current-menu-item', 'current_page_item', 'current_page_parent', 'current-menu-ancestor' ), (array) $item->classes );
		$mobile  = ! empty( $args->cases_mobile );
		if ( $mobile ) {
			$class = 'text-white/80 font-medium hover:text-sky-400 transition-all' . ( $active ? ' text-sky-400' : '' );
		} elseif ( $active ) {
			$class = 'nav-active text-sky-400 border-b-2 border-sky-400 pb-1 font-bold transition-all duration-300';
		} else {
			$class = 'nav-link text-white/80 font-medium hover:text-sky-400 transition-all duration-300';
		}
		$vi   = cases_menu_vi( $item->title, 'page' === $item->object ? (int) $item->object_id : 0 );
		$attr = $vi ? sprintf( ' data-en="%s" data-vi="%s"', esc_attr( $item->title ), esc_attr( $vi ) ) : '';
		$output .= sprintf(
			'<a class="%s" href="%s"%s%s>%s</a>',
			esc_attr( $class ),
			esc_url( $item->url ),
			$active ? ' aria-current="page"' : '',
			$attr,
			esc_html( $item->title )
		);
	}
}
