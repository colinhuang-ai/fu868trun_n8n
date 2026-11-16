<?php
/**
 * Idempotent content seed for the CASES site. Run with:
 *   wp eval-file /scripts/seed.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Theme + basic settings ------------------------------------------------
// The theme registers post types/taxonomies on "init", so it must already be
// active when this script boots (setup.sh activates it first).
if ( 'cases' !== get_stylesheet() ) {
	WP_CLI::error( 'Activate the theme first: wp theme activate cases' );
}
update_option( 'blogname', 'CASES - CaMau Seafood Processing and Service Joint-Stock Corporation' );
update_option( 'blogdescription', 'Premium frozen seafood export from Cà Mau, Vietnam since 1996' );
update_option( 'timezone_string', 'Asia/Ho_Chi_Minh' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );

// 2. Remove WordPress sample content ----------------------------------------
foreach ( array( 'hello-world' ) as $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $p ) {
		wp_delete_post( $p->ID, true );
	}
}
$sample = get_page_by_path( 'sample-page' );
if ( $sample ) {
	wp_delete_post( $sample->ID, true );
}

// 3. Pages ----------------------------------------------------------------
$pages = array(
	'home'           => 'Home',
	'about'          => 'About Us',
	'products'       => 'Products',
	'infrastructure' => 'Infrastructure',
	'certifications' => 'Certifications',
);
$ids = array();
foreach ( $pages as $slug => $title ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		$ids[ $slug ] = $existing->ID;
		continue;
	}
	$ids[ $slug ] = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $title,
			'post_name'   => $slug,
		)
	);
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ids['home'] );

// 4. Primary menu ---------------------------------------------------------
$menu_name = 'Primary';
$menu      = wp_get_nav_menu_object( $menu_name );
if ( ! $menu ) {
	$menu_id = wp_create_nav_menu( $menu_name );
	foreach ( $pages as $slug => $title ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $ids[ $slug ],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}
} else {
	$menu_id = $menu->term_id;
}
$locations            = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

// 5. Products -------------------------------------------------------------
$data = json_decode( (string) file_get_contents( '/scripts/products.json' ), true );
if ( ! is_array( $data ) ) {
	WP_CLI::error( 'products.json missing or invalid' );
}
$created = 0;
foreach ( $data as $cat ) {
	$term = term_exists( $cat['slug'], 'product_category' );
	if ( ! $term ) {
		$term = wp_insert_term( $cat['name'], 'product_category', array( 'slug' => $cat['slug'] ) );
	}
	$term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
	update_term_meta( $term_id, 'cases_order', (int) $cat['order'] );

	foreach ( $cat['skus'] as $i => $sku ) {
		$exists = get_posts(
			array(
				'post_type'      => 'cases_product',
				'post_status'    => 'any',
				'title'          => $sku['name'],
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'tax_query'      => array( array( 'taxonomy' => 'product_category', 'terms' => $term_id ) ),
			)
		);
		if ( $exists ) {
			continue;
		}
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'cases_product',
				'post_status'  => 'publish',
				'post_title'   => $sku['name'],
				'post_content' => $sku['desc'],
				'menu_order'   => $i + 1,
			)
		);
		wp_set_object_terms( $post_id, array( $term_id ), 'product_category' );
		update_post_meta( $post_id, 'cases_size', $sku['size'] );
		update_post_meta( $post_id, 'cases_pack', $sku['pack'] );
		update_post_meta( $post_id, 'cases_origin', $sku['origin'] );
		update_post_meta( $post_id, 'cases_glaze', $sku['glaze'] );
		update_post_meta( $post_id, 'cases_desc_vi', $sku['desc_vi'] );
		++$created;
	}
}

flush_rewrite_rules( true );
WP_CLI::success( sprintf( 'Seed complete: %d pages, %d new products.', count( $ids ), $created ) );
