<?php
/**
 * Idempotent seed for the News section. Run with:
 *   wp eval-file /scripts/seed-news.php
 * Requires the "cases" theme to be active (posts need its meta registration).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( 'cases' !== get_stylesheet() ) {
	WP_CLI::error( 'Activate the theme first: wp theme activate cases' );
}

// Trusted seed content: keep HTML verbatim (CLI has no user, so kses would strip attributes).
kses_remove_filters();

$data = require '/scripts/news-data.php';

// 1. "News" page = posts page ------------------------------------------------
$news = get_page_by_path( 'news' );
$news_id = $news ? $news->ID : wp_insert_post(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => 'News',
		'post_name'   => 'news',
	)
);
update_option( 'page_for_posts', $news_id );
update_option( 'posts_per_page', 10 );

// 2. Menu item (last in Primary), only if missing --------------------------
$menu = wp_get_nav_menu_object( 'Primary' );
if ( $menu ) {
	$has = false;
	foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
		if ( (int) $item->object_id === (int) $news_id ) {
			$has = true;
			break;
		}
	}
	if ( ! $has ) {
		wp_update_nav_menu_item(
			$menu->term_id,
			0,
			array(
				'menu-item-title'     => 'News',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $news_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}
}

// 3. Categories (Vietnamese names live in cases_cat_vi(), not in term meta) -
$cat_ids = array();
foreach ( $data['categories'] as $slug => $names ) {
	$term = term_exists( $slug, 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( $names[0], 'category', array( 'slug' => $slug ) );
	}
	if ( is_wp_error( $term ) ) {
		WP_CLI::error( 'Category failed: ' . $slug . ' - ' . $term->get_error_message() );
	}
	$cat_ids[ $slug ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
}

// 4. Posts (idempotent by slug) ----------------------------------------------
$created = 0;
$updated = 0;
foreach ( $data['posts'] as $p ) {
	$existing = get_page_by_path( $p['slug'], OBJECT, 'post' );
	$args     = array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => $p['title'],
		'post_name'     => $p['slug'],
		'post_excerpt'  => $p['excerpt'],
		'post_content'  => $p['content'],
		'post_date'     => $p['date'],
		'post_date_gmt' => get_gmt_from_date( $p['date'] ),
		'post_category' => array( $cat_ids[ $p['cat'] ] ),
	);
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$post_id    = wp_update_post( $args, true );
		++$updated;
	} else {
		$post_id = wp_insert_post( $args, true );
		++$created;
	}
	if ( is_wp_error( $post_id ) ) {
		WP_CLI::error( 'Post failed: ' . $p['slug'] . ' - ' . $post_id->get_error_message() );
	}
	update_post_meta( $post_id, 'cases_title_vi', $p['title_vi'] );
	update_post_meta( $post_id, 'cases_excerpt_vi', $p['excerpt_vi'] );
	update_post_meta( $post_id, 'cases_content_vi', $p['content_vi'] );
	update_post_meta( $post_id, 'cases_cover', $p['cover'] );
}

flush_rewrite_rules( true );
WP_CLI::success( sprintf( 'News seed complete: %d posts created, %d updated, %d categories.', $created, $updated, count( $cat_ids ) ) );
