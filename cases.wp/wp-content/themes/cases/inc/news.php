<?php
/**
 * News: bilingual fields for core posts, cover styles, helpers.
 *
 * English lives in the normal title / excerpt / editor; Vietnamese lives in
 * post meta (cases_title_vi, cases_excerpt_vi, cases_content_vi).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ meta */
add_action(
	'init',
	static function () {
		$fields = array(
			'cases_title_vi'   => 'sanitize_text_field',
			'cases_excerpt_vi' => 'sanitize_textarea_field',
			'cases_content_vi' => 'wp_kses_post',
			'cases_cover'      => 'sanitize_key',
		);
		foreach ( $fields as $key => $cb ) {
			register_post_meta(
				'post',
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => $cb,
					'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
);

/* ------------------------------------------------------------------ covers */

/**
 * Cover styles used when a post has no featured image. Classes are literal so
 * Tailwind can see them.
 *
 * @return array<string,array{label:string,classes:string,emoji:string}>
 */
function cases_cover_styles(): array {
	return array(
		'shrimp'   => array( 'label' => 'Shrimp', 'classes' => 'from-sky-900 to-sky-600', 'emoji' => '🦐' ),
		'ruler'    => array( 'label' => 'Size / measure', 'classes' => 'from-slate-900 to-sky-700', 'emoji' => '📏' ),
		'freezer'  => array( 'label' => 'Freezing', 'classes' => 'from-cyan-900 to-sky-500', 'emoji' => '❄️' ),
		'ship'     => array( 'label' => 'Shipping / cold chain', 'classes' => 'from-indigo-950 to-sky-700', 'emoji' => '🚢' ),
		'ice'      => array( 'label' => 'Ice / glaze', 'classes' => 'from-sky-800 to-cyan-400', 'emoji' => '🧊' ),
		'badge'    => array( 'label' => 'Certification', 'classes' => 'from-emerald-950 to-teal-600', 'emoji' => '🏅' ),
		'mangrove' => array( 'label' => 'Mangrove / origin', 'classes' => 'from-emerald-900 to-emerald-600', 'emoji' => '🌿' ),
		'fried'    => array( 'label' => 'Value-added', 'classes' => 'from-orange-950 to-amber-600', 'emoji' => '🍤' ),
		'heart'    => array( 'label' => 'Health', 'classes' => 'from-rose-950 to-rose-600', 'emoji' => '💙' ),
		'chef'     => array( 'label' => 'Cooking', 'classes' => 'from-red-950 to-orange-600', 'emoji' => '🍳' ),
	);
}

/**
 * @return array{label:string,classes:string,emoji:string}
 */
function cases_cover_style( ?string $key ): array {
	$all = cases_cover_styles();
	return $all[ $key ] ?? $all['shrimp'];
}

/* ------------------------------------------------------------------ meta box */
add_action(
	'add_meta_boxes',
	static function () {
		add_meta_box( 'cases_post_vi', __( 'Vietnamese version & cover', 'cases' ), 'cases_post_vi_box', 'post', 'normal', 'high' );
	}
);

function cases_post_vi_box( WP_Post $post ): void {
	wp_nonce_field( 'cases_post_vi', 'cases_post_vi_nonce' );
	$title   = (string) get_post_meta( $post->ID, 'cases_title_vi', true );
	$excerpt = (string) get_post_meta( $post->ID, 'cases_excerpt_vi', true );
	$content = (string) get_post_meta( $post->ID, 'cases_content_vi', true );
	$cover   = (string) get_post_meta( $post->ID, 'cases_cover', true );

	echo '<p class="description">' . esc_html__( 'English is the normal title / excerpt / editor. Fill these in so visitors who switch the site to VI see Vietnamese. Leave empty to show English only.', 'cases' ) . '</p>';

	echo '<p><label for="cases_title_vi"><strong>' . esc_html__( 'Title (Vietnamese)', 'cases' ) . '</strong></label><br>';
	echo '<input type="text" class="widefat" id="cases_title_vi" name="cases_title_vi" value="' . esc_attr( $title ) . '"></p>';

	echo '<p><label for="cases_excerpt_vi"><strong>' . esc_html__( 'Excerpt (Vietnamese)', 'cases' ) . '</strong></label><br>';
	echo '<textarea class="widefat" rows="3" id="cases_excerpt_vi" name="cases_excerpt_vi">' . esc_textarea( $excerpt ) . '</textarea></p>';

	echo '<p><label for="cases_content_vi"><strong>' . esc_html__( 'Content (Vietnamese, HTML allowed)', 'cases' ) . '</strong></label><br>';
	echo '<textarea class="widefat code" rows="14" id="cases_content_vi" name="cases_content_vi">' . esc_textarea( $content ) . '</textarea></p>';

	echo '<p><label for="cases_cover"><strong>' . esc_html__( 'Cover style (used when there is no featured image)', 'cases' ) . '</strong></label><br>';
	echo '<select id="cases_cover" name="cases_cover">';
	foreach ( cases_cover_styles() as $key => $def ) {
		printf( '<option value="%s"%s>%s %s</option>', esc_attr( $key ), selected( $cover ?: 'shrimp', $key, false ), esc_html( $def['emoji'] ), esc_html( $def['label'] ) );
	}
	echo '</select></p>';
}

add_action(
	'save_post_post',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['cases_post_vi_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cases_post_vi_nonce'] ) ), 'cases_post_vi' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$map = array(
			'cases_title_vi'   => 'sanitize_text_field',
			'cases_excerpt_vi' => 'sanitize_textarea_field',
			'cases_content_vi' => 'wp_kses_post',
			'cases_cover'      => 'sanitize_key',
		);
		foreach ( $map as $key => $cb ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, $cb( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
	}
);

/* ------------------------------------------------------------------ helpers */

/**
 * Vietnamese names for the news categories (matched by slug).
 */
function cases_cat_vi( string $slug ): string {
	$map = array(
		'product-guide'         => 'Hướng Dẫn Sản Phẩm',
		'quality-safety'        => 'Chất Lượng & An Toàn',
		'origin-sustainability' => 'Nguồn Gốc & Bền Vững',
		'cooking-health'        => 'Ẩm Thực & Sức Khỏe',
	);
	return $map[ $slug ] ?? '';
}

/**
 * First category of a post as [slug, name EN, name VI].
 *
 * @return array{0:string,1:string,2:string}|null
 */
function cases_post_category( int $post_id ): ?array {
	$terms = get_the_category( $post_id );
	if ( empty( $terms ) ) {
		return null;
	}
	$t = $terms[0];
	return array( $t->slug, wp_specialchars_decode( $t->name ), cases_cat_vi( $t->slug ) ?: wp_specialchars_decode( $t->name ) );
}

/**
 * Echo a category <span> with EN/VI labels.
 */
function cases_category_label( int $post_id, string $class = '' ): void {
	$c = cases_post_category( $post_id );
	if ( ! $c ) {
		return;
	}
	printf( '<span class="%s"', esc_attr( $class ) );
	cases_attr( $c[1], $c[2] );
	echo '>' . esc_html( $c[1] ) . '</span>';
}

/**
 * Bilingual title / excerpt attributes (EN default; VI from meta when present).
 */
function cases_post_attrs( int $post_id, string $field = 'title' ): void {
	$vi = (string) get_post_meta( $post_id, 'cases_' . $field . '_vi', true );
	if ( '' === $vi ) {
		return;
	}
	$en = 'title' === $field ? get_the_title( $post_id ) : cases_post_excerpt_en( $post_id );
	cases_attr( wp_specialchars_decode( $en ), $vi );
}

function cases_post_excerpt_en( int $post_id ): string {
	$p = get_post( $post_id );
	if ( ! $p ) {
		return '';
	}
	return '' !== trim( $p->post_excerpt ) ? trim( $p->post_excerpt ) : wp_trim_words( wp_strip_all_tags( $p->post_content ), 30 );
}

/**
 * Reading time in minutes (English body, ~200 wpm).
 */
function cases_read_time( int $post_id ): int {
	$p = get_post( $post_id );
	if ( ! $p ) {
		return 1;
	}
	$words = str_word_count( wp_strip_all_tags( $p->post_content ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Echo a <time> with EN (M j, Y) and VI (d/m/Y) formats.
 */
function cases_date( int $post_id, string $class = '' ): void {
	printf(
		'<time class="%s" datetime="%s" data-en="%s" data-vi="%s">%s</time>',
		esc_attr( $class ),
		esc_attr( get_the_date( 'c', $post_id ) ),
		esc_attr( get_the_date( 'M j, Y', $post_id ) ),
		esc_attr( get_the_date( 'd/m/Y', $post_id ) ),
		esc_html( get_the_date( 'M j, Y', $post_id ) )
	);
}

function cases_news_url(): string {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? (string) get_permalink( $id ) : home_url( '/news/' );
}

/**
 * News list: 10 per page (1 featured + 3×3 grid) on the posts page and categories.
 */
add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( ! is_admin() && $q->is_main_query() && ( $q->is_home() || $q->is_category() ) ) {
			$q->set( 'posts_per_page', 10 );
		}
	}
);
