<?php
/**
 * Custom post types & taxonomy: Products (SKUs), Inquiries (contact form).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		register_taxonomy(
			'product_category',
			'cases_product',
			array(
				'labels'            => array(
					'name'          => __( 'Product Categories', 'cases' ),
					'singular_name' => __( 'Product Category', 'cases' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
			)
		);

		register_post_type(
			'cases_product',
			array(
				'labels'       => array(
					'name'          => __( 'Products', 'cases' ),
					'singular_name' => __( 'Product', 'cases' ),
					'add_new_item'  => __( 'Add New Product (SKU)', 'cases' ),
					'edit_item'     => __( 'Edit Product (SKU)', 'cases' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-carrot',
				'menu_position' => 20,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'custom-fields' ),
				'taxonomies'   => array( 'product_category' ),
			)
		);

		foreach ( array( 'size', 'pack', 'origin', 'glaze', 'desc_vi' ) as $key ) {
			register_post_meta(
				'cases_product',
				'cases_' . $key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
				)
			);
		}

		register_post_type(
			'cases_inquiry',
			array(
				'labels'       => array(
					'name'          => __( 'Inquiries', 'cases' ),
					'singular_name' => __( 'Inquiry', 'cases' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'menu_icon'    => 'dashicons-email-alt',
				'menu_position' => 25,
				'supports'     => array( 'title', 'editor' ),
				'capabilities' => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap' => true,
			)
		);
	}
);

/**
 * Meta box for SKU specs (size, pack, origin, glaze, Vietnamese description).
 */
add_action(
	'add_meta_boxes',
	static function () {
		add_meta_box( 'cases_product_specs', __( 'SKU specifications', 'cases' ), 'cases_product_specs_box', 'cases_product', 'normal', 'high' );
		add_meta_box( 'cases_inquiry_info', __( 'Sender', 'cases' ), 'cases_inquiry_info_box', 'cases_inquiry', 'side' );
	}
);

function cases_product_specs_box( WP_Post $post ): void {
	wp_nonce_field( 'cases_product_specs', 'cases_product_specs_nonce' );
	$fields = array(
		'size'    => __( 'Size range (e.g. 8/12 – 41/50)', 'cases' ),
		'pack'    => __( 'Pack form (e.g. IQF / Block)', 'cases' ),
		'origin'  => __( 'Origin', 'cases' ),
		'glaze'   => __( 'Glaze (e.g. 10–15%)', 'cases' ),
		'desc_vi' => __( 'Description (Vietnamese)', 'cases' ),
	);
	echo '<p class="description">' . esc_html__( 'The English description is the main editor above. Featured image = product photo.', 'cases' ) . '</p>';
	foreach ( $fields as $key => $label ) {
		$val = (string) get_post_meta( $post->ID, 'cases_' . $key, true );
		echo '<p><label for="cases_' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( 'desc_vi' === $key ) {
			echo '<textarea class="widefat" rows="3" id="cases_' . esc_attr( $key ) . '" name="cases_' . esc_attr( $key ) . '">' . esc_textarea( $val ) . '</textarea></p>';
		} else {
			echo '<input type="text" class="widefat" id="cases_' . esc_attr( $key ) . '" name="cases_' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '"></p>';
		}
	}
}

add_action(
	'save_post_cases_product',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['cases_product_specs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cases_product_specs_nonce'] ) ), 'cases_product_specs' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( array( 'size', 'pack', 'origin', 'glaze', 'desc_vi' ) as $key ) {
			if ( isset( $_POST[ 'cases_' . $key ] ) ) {
				update_post_meta( $post_id, 'cases_' . $key, sanitize_textarea_field( wp_unslash( $_POST[ 'cases_' . $key ] ) ) );
			}
		}
	}
);

function cases_inquiry_info_box( WP_Post $post ): void {
	$rows = array(
		__( 'Name', 'cases' )    => get_post_meta( $post->ID, 'cases_name', true ),
		__( 'Company', 'cases' ) => get_post_meta( $post->ID, 'cases_company', true ),
		__( 'Email', 'cases' )   => get_post_meta( $post->ID, 'cases_email', true ),
	);
	foreach ( $rows as $label => $val ) {
		echo '<p><strong>' . esc_html( $label ) . ':</strong><br>' . esc_html( (string) $val ) . '</p>';
	}
}
