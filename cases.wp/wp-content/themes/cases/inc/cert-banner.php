<?php
/**
 * Editable highlight banner on the Certifications page (the "US FDA Registration" block).
 * Fields live in a meta box on the page itself: Pages → Certifications → "Highlight banner".
 * Built-in defaults are used until a field has been saved, so the page looks the same before any edit.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions: key => [label, input type, default].
 */
function cases_banner_fields(): array {
	return array(
		'eyebrow_en' => array( 'Small label (English)', 'text', '🇺🇸 USA Market' ),
		'eyebrow_vi' => array( 'Small label (Vietnamese)', 'text', '🇺🇸 Thị Trường Hoa Kỳ' ),
		'title_en'   => array( 'Heading (English)', 'text', 'US FDA Registration' ),
		'title_vi'   => array( 'Heading (Vietnamese)', 'text', 'Đăng Ký FDA Hoa Kỳ' ),
		'text_en'    => array(
			'Text (English)',
			'textarea',
			'CASES is registered with the US Food & Drug Administration — a prerequisite for seafood exports into the American market. Combined with our HACCP standard, this ensures full FDA import-regulation compliance.',
		),
		'text_vi'    => array(
			'Text (Vietnamese)',
			'textarea',
			'CASES được Cục Quản lý Thực phẩm và Dược phẩm Hoa Kỳ (FDA) đăng ký — điều kiện tiên quyết để xuất khẩu thủy sản vào thị trường Mỹ. Kết hợp với tiêu chuẩn HACCP, điều này đảm bảo tuân thủ đầy đủ quy định nhập khẩu của FDA.',
		),
		'tags'       => array( 'Market tags (one per line, emoji allowed)', 'textarea', "🇺🇸 USA\n🇪🇺 EU\n🇯🇵 Japan\n🇰🇷 Korea\n🇦🇺 Australia\n🇨🇦 Canada\n🇦🇪 UAE" ),
		'icon'       => array( 'Badge icon (emoji)', 'text', '🏛️' ),
		'badge_main' => array( 'Badge title', 'text', 'FDA Registered' ),
		'badge_sub'  => array( 'Badge subtitle', 'text', 'Registrar Corp' ),
	);
}

/**
 * Banner values for a page (saved value if one exists, otherwise the default).
 *
 * @return array<string,mixed>
 */
function cases_get_cert_banner( int $page_id ): array {
	$out = array(
		'show' => '0' !== (string) get_post_meta( $page_id, 'cases_banner_show', true ),
	);
	foreach ( cases_banner_fields() as $key => $def ) {
		$out[ $key ] = metadata_exists( 'post', $page_id, 'cases_banner_' . $key )
			? (string) get_post_meta( $page_id, 'cases_banner_' . $key, true )
			: $def[2];
	}
	// Vietnamese falls back to English when left blank.
	foreach ( array( 'eyebrow', 'title', 'text' ) as $k ) {
		if ( '' === $out[ $k . '_vi' ] ) {
			$out[ $k . '_vi' ] = $out[ $k . '_en' ];
		}
	}
	$out['tag_list'] = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $out['tags'] ) ) ) );
	return $out;
}

add_action(
	'add_meta_boxes_page',
	static function ( WP_Post $post ) {
		if ( 'certifications' !== $post->post_name ) {
			return;
		}
		add_meta_box( 'cases_cert_banner', __( 'Highlight banner (US FDA block)', 'cases' ), 'cases_cert_banner_box', 'page', 'normal', 'high' );
	}
);

function cases_cert_banner_box( WP_Post $post ): void {
	wp_nonce_field( 'cases_cert_banner', 'cases_cert_banner_nonce' );
	$b = cases_get_cert_banner( $post->ID );

	echo '<p class="description">' . esc_html__( 'This is the dark banner below the certificate cards. Leave a Vietnamese field empty to reuse the English text.', 'cases' ) . '</p>';
	echo '<p><label><input type="checkbox" name="cases_banner_show" value="1"' . checked( $b['show'], true, false ) . '> <strong>' . esc_html__( 'Show this banner', 'cases' ) . '</strong></label></p>';

	foreach ( cases_banner_fields() as $key => $def ) {
		$name = 'cases_banner_' . $key;
		echo '<p><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $def[0] ) . '</strong></label><br>';
		if ( 'textarea' === $def[1] ) {
			$rows = 'tags' === $key ? 7 : 4;
			echo '<textarea class="widefat" rows="' . (int) $rows . '" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( (string) $b[ $key ] ) . '</textarea>';
		} else {
			echo '<input type="text" class="widefat" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $b[ $key ] ) . '">';
		}
		echo '</p>';
	}
}

add_action(
	'save_post_page',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['cases_cert_banner_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cases_cert_banner_nonce'] ) ), 'cases_cert_banner' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( cases_banner_fields() as $key => $def ) {
			$name = 'cases_banner_' . $key;
			if ( ! isset( $_POST[ $name ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $name ] );
			update_post_meta( $post_id, $name, 'textarea' === $def[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw ) );
		}
		// Unticked checkboxes are absent from the request, so store the state explicitly.
		update_post_meta( $post_id, 'cases_banner_show', isset( $_POST['cases_banner_show'] ) ? '1' : '0' );
	}
);
