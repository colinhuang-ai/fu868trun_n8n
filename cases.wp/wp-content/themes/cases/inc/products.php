<?php
/**
 * Product catalog data layer (categories + SKUs → JSON for the Products page).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

/**
 * Presentation settings for a category card, keyed by term slug.
 * Gradient classes are literal so Tailwind can see them.
 *
 * @return array{code:string,sub:string,vi:string,emoji:string,gradient:string,cols:string}
 */
function cases_category_style( string $slug ): array {
	$map = array(
		'black-tiger-prawn'    => array( 'BTP', 'Black Tiger', 'Tôm Sú', '🦐', 'from-sky-900 to-sky-700', 'grid-cols-5 lg:grid-cols-10' ),
		'vannamei-shrimp'      => array( 'VAN', 'Vannamei', 'Tôm Thẻ', '🦐', 'from-sky-800 to-cyan-600', 'grid-cols-3 sm:grid-cols-5 lg:grid-cols-9' ),
		'value-added-btp'      => array( 'VAP', 'BTP', 'Sản phẩm GTGT · Tôm Sú', '🍤', 'from-red-950 to-red-800', 'grid-cols-5' ),
		'value-added-vannamei' => array( 'VAP', 'Vannamei', 'Sản phẩm GTGT · Tôm Thẻ', '🍤', 'from-orange-950 to-orange-700', 'grid-cols-3 sm:grid-cols-6' ),
		'surimi-fish-meal'     => array( 'SRM', 'Surimi', 'Surimi & Bột Cá', '🐟', 'from-teal-900 to-teal-600', 'grid-cols-2 sm:grid-cols-4' ),
		'cephalopod'           => array( 'CPD', 'Cephalopod', 'Đầu Túc Động Vật', '🐙', 'from-slate-800 to-slate-600', 'grid-cols-2 sm:grid-cols-4' ),
	);
	$d = $map[ $slug ] ?? array( strtoupper( substr( $slug, 0, 3 ) ), '', '', '🦐', 'from-sky-900 to-sky-700', 'grid-cols-3 sm:grid-cols-5' );
	return array(
		'code'     => $d[0],
		'sub'      => $d[1],
		'vi'       => $d[2],
		'emoji'    => $d[3],
		'gradient' => $d[4],
		'cols'     => $d[5],
	);
}

/**
 * All categories (ordered by term meta "cases_order", then ID) with their SKUs.
 *
 * @return array<int,array<string,mixed>>
 */
function cases_get_product_groups(): array {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_category',
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}
	usort(
		$terms,
		static function ( WP_Term $a, WP_Term $b ): int {
			$oa = (int) get_term_meta( $a->term_id, 'cases_order', true );
			$ob = (int) get_term_meta( $b->term_id, 'cases_order', true );
			return array( $oa ?: 999, $a->term_id ) <=> array( $ob ?: 999, $b->term_id );
		}
	);

	$groups = array();
	foreach ( $terms as $term ) {
		$style = cases_category_style( $term->slug );
		$posts = get_posts(
			array(
				'post_type'      => 'cases_product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_category',
						'terms'    => $term->term_id,
					),
				),
			)
		);

		$skus = array();
		foreach ( $posts as $p ) {
			$skus[] = array(
				'name'   => get_the_title( $p ),
				'size'   => (string) get_post_meta( $p->ID, 'cases_size', true ),
				'pack'   => (string) get_post_meta( $p->ID, 'cases_pack', true ),
				'origin' => (string) get_post_meta( $p->ID, 'cases_origin', true ),
				'glaze'  => (string) get_post_meta( $p->ID, 'cases_glaze', true ),
				'desc'   => trim( wp_strip_all_tags( $p->post_content ) ),
				'descVi' => (string) get_post_meta( $p->ID, 'cases_desc_vi', true ),
				'image'  => (string) get_the_post_thumbnail_url( $p, 'medium_large' ),
			);
		}

		$groups[] = array(
			'slug'  => $term->slug,
			'name'  => wp_specialchars_decode( $term->name ), // WP stores "&" as "&amp;"; escaping happens on output.
			'style' => $style,
			'skus'  => $skus,
		);
	}
	return $groups;
}
