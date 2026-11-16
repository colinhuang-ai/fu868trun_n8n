<?php
/**
 * Certificates: custom post type "cases_certificate", grouped by the
 * "certificate_group" taxonomy. Drives the Certifications page.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

/** Meta keys (stored with a "cases_cert_" prefix). */
function cases_cert_fields(): array {
	return array(
		'icon'        => array( 'Icon (emoji)', 'text' ),
		'icon_color'  => array( 'Icon colour', 'color' ),
		'badge_color' => array( 'Badge colour', 'color' ),
		'badge_en'    => array( 'Badge text (English)', 'text' ),
		'badge_vi'    => array( 'Badge text (Vietnamese)', 'text' ),
		'desc_vi'     => array( 'Description (Vietnamese)', 'textarea' ),
		'foot_en'     => array( 'Footnote under the card (English)', 'text' ),
		'foot_vi'     => array( 'Footnote under the card (Vietnamese)', 'text' ),
		'issuer'      => array( 'Issuing body', 'text' ),
		'scope_en'    => array( 'Scope (English)', 'text' ),
		'scope_vi'    => array( 'Scope (Vietnamese)', 'text' ),
		'number'      => array( 'Certificate number (optional, internal)', 'text' ),
		'valid_until' => array( 'Valid until (leave empty if no expiry)', 'date' ),
	);
}

/** Colour options shared by icon + badge. */
function cases_cert_colors(): array {
	return array(
		'blue'   => array(
			'label' => 'Blue',
			'rgb'   => '56,189,248',
		),
		'green'  => array(
			'label' => 'Green',
			'rgb'   => '74,222,128',
		),
		'amber'  => array(
			'label' => 'Amber',
			'rgb'   => '251,191,36',
		),
		'purple' => array(
			'label' => 'Purple',
			'rgb'   => '167,139,250',
		),
	);
}

/** Vietnamese names + grid layout for known groups (fallback: EN name, 3 columns). */
function cases_cert_group_info( string $slug ): array {
	$map = array(
		'food-safety-quality'    => array( 'An Toàn Thực Phẩm & Chất Lượng', 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6' ),
		'sustainable-aquaculture' => array( 'Nuôi Trồng Bền Vững', 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6' ),
		'specialist-markets'     => array( 'Thị Trường Đặc Thù', 'grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-3xl mx-auto' ),
	);
	$d = $map[ $slug ] ?? array( '', 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6' );
	return array(
		'vi'   => $d[0],
		'grid' => $d[1],
	);
}

add_action(
	'init',
	static function () {
		register_taxonomy(
			'certificate_group',
			'cases_certificate',
			array(
				'labels'            => array(
					'name'          => __( 'Certificate Groups', 'cases' ),
					'singular_name' => __( 'Certificate Group', 'cases' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
			)
		);

		register_post_type(
			'cases_certificate',
			array(
				'labels'        => array(
					'name'          => __( 'Certificates', 'cases' ),
					'singular_name' => __( 'Certificate', 'cases' ),
					'add_new_item'  => __( 'Add New Certificate', 'cases' ),
					'edit_item'     => __( 'Edit Certificate', 'cases' ),
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-awards',
				'menu_position' => 21,
				'supports'      => array( 'title', 'editor', 'page-attributes' ),
				'taxonomies'    => array( 'certificate_group' ),
			)
		);

		foreach ( array_keys( cases_cert_fields() ) as $key ) {
			register_post_meta(
				'cases_certificate',
				'cases_cert_' . $key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
);

/* --------------------------------------------------------------- admin UI */

add_action(
	'add_meta_boxes',
	static function () {
		add_meta_box( 'cases_cert_details', __( 'Certificate details', 'cases' ), 'cases_cert_details_box', 'cases_certificate', 'normal', 'high' );
	}
);

function cases_cert_details_box( WP_Post $post ): void {
	wp_nonce_field( 'cases_cert_details', 'cases_cert_details_nonce' );
	$colors = cases_cert_colors();

	echo '<p class="description">' . esc_html__( 'Title = certificate name (e.g. "HACCP"). The main editor above = English description. Set the group on the right.', 'cases' ) . '</p>';

	foreach ( cases_cert_fields() as $key => $def ) {
		$name = 'cases_cert_' . $key;
		$val  = (string) get_post_meta( $post->ID, $name, true );
		echo '<p><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $def[0] ) . '</strong></label><br>';
		if ( 'textarea' === $def[1] ) {
			echo '<textarea class="widefat" rows="3" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $val ) . '</textarea>';
		} elseif ( 'color' === $def[1] ) {
			echo '<select id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( $colors as $ck => $cv ) {
				echo '<option value="' . esc_attr( $ck ) . '"' . selected( $val ? $val : 'blue', $ck, false ) . '>' . esc_html( $cv['label'] ) . '</option>';
			}
			echo '</select>';
		} elseif ( 'date' === $def[1] ) {
			echo '<input type="date" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '">';
		} else {
			echo '<input type="text" class="widefat" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '">';
		}
		echo '</p>';
	}
}

add_action(
	'save_post_cases_certificate',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['cases_cert_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cases_cert_details_nonce'] ) ), 'cases_cert_details' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$colors = array_keys( cases_cert_colors() );
		foreach ( cases_cert_fields() as $key => $def ) {
			$name = 'cases_cert_' . $key;
			if ( ! isset( $_POST[ $name ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $name ] );
			if ( 'color' === $def[1] ) {
				$val = in_array( $raw, $colors, true ) ? $raw : 'blue';
			} elseif ( 'date' === $def[1] ) {
				$val = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
			} elseif ( 'textarea' === $def[1] ) {
				$val = sanitize_textarea_field( $raw );
			} else {
				$val = sanitize_text_field( $raw );
			}
			update_post_meta( $post_id, $name, $val );
		}
	}
);

// List-table columns: issuer, valid until, status.
add_filter(
	'manage_cases_certificate_posts_columns',
	static function ( array $cols ): array {
		$out = array();
		foreach ( $cols as $k => $v ) {
			$out[ $k ] = $v;
			if ( 'title' === $k ) {
				$out['cases_issuer'] = __( 'Issuing body', 'cases' );
				$out['cases_valid']  = __( 'Valid until', 'cases' );
				$out['cases_status'] = __( 'Status', 'cases' );
			}
		}
		return $out;
	}
);
add_action(
	'manage_cases_certificate_posts_custom_column',
	static function ( string $col, int $post_id ) {
		if ( 'cases_issuer' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, 'cases_cert_issuer', true ) ?: '—' );
		} elseif ( 'cases_valid' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, 'cases_cert_valid_until', true ) ?: '—' );
		} elseif ( 'cases_status' === $col ) {
			echo cases_cert_is_active( $post_id )
				? '<span style="color:#15803d;font-weight:600">' . esc_html__( 'Active', 'cases' ) . '</span>'
				: '<span style="color:#b91c1c;font-weight:600">' . esc_html__( 'Expired', 'cases' ) . '</span>';
		}
	},
	10,
	2
);

/* --------------------------------------------------------------- data */

/** Active unless a "valid until" date exists and has passed. */
function cases_cert_is_active( int $post_id ): bool {
	$until = (string) get_post_meta( $post_id, 'cases_cert_valid_until', true );
	if ( '' === $until ) {
		return true;
	}
	return $until >= wp_date( 'Y-m-d' );
}

/** Normalise one certificate post into a plain array for templates. */
function cases_cert_data( WP_Post $p ): array {
	$m   = static fn( string $k ): string => (string) get_post_meta( $p->ID, 'cases_cert_' . $k, true );
	$en  = trim( wp_strip_all_tags( $p->post_content ) );
	$col = cases_cert_colors();
	$ic  = $m( 'icon_color' ) ?: 'blue';
	$bc  = $m( 'badge_color' ) ?: 'blue';

	$badge_en = $m( 'badge_en' );
	$foot_en  = $m( 'foot_en' );
	$scope_en = $m( 'scope_en' );

	return array(
		'id'        => $p->ID,
		'name'      => get_the_title( $p ),
		'icon'      => $m( 'icon' ) ?: '✅',
		'icon_rgb'  => $col[ $ic ]['rgb'] ?? $col['blue']['rgb'],
		'badge_cls' => 'badge-' . ( isset( $col[ $bc ] ) ? $bc : 'blue' ),
		'badge_en'  => $badge_en,
		'badge_vi'  => $m( 'badge_vi' ) ?: $badge_en,
		'desc_en'   => $en,
		'desc_vi'   => $m( 'desc_vi' ) ?: $en,
		'foot_en'   => $foot_en,
		'foot_vi'   => $m( 'foot_vi' ) ?: $foot_en,
		'issuer'    => $m( 'issuer' ) ?: '—',
		'scope_en'  => $scope_en,
		'scope_vi'  => $m( 'scope_vi' ) ?: $scope_en,
		'active'    => cases_cert_is_active( $p->ID ),
	);
}

/** All published certificates, ordered. */
function cases_get_certificates(): array {
	$posts = get_posts(
		array(
			'post_type'      => 'cases_certificate',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
		)
	);
	return array_map( 'cases_cert_data', $posts );
}

/**
 * Certificates (cards only) grouped by certificate_group, groups ordered by term meta "cases_order".
 *
 * @return array<int,array{name:string,vi:string,grid:string,certs:array}>
 */
function cases_get_certificate_groups(): array {
	$terms = get_terms(
		array(
			'taxonomy'   => 'certificate_group',
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $terms ) ) {
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
		$posts = get_posts(
			array(
				'post_type'      => 'cases_certificate',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
				'tax_query'      => array(
					array(
						'taxonomy' => 'certificate_group',
						'terms'    => $term->term_id,
					),
				),
			)
		);
		$certs = array_map( 'cases_cert_data', $posts );
		$info     = cases_cert_group_info( $term->slug );
		$name     = wp_specialchars_decode( $term->name ); // WP stores "&" as "&amp;".
		$groups[] = array(
			'name'  => $name,
			'vi'    => $info['vi'] ?: $name,
			'grid'  => $info['grid'],
			'certs' => $certs,
		);
	}
	return $groups;
}
