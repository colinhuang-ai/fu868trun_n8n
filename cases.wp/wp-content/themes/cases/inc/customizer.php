<?php
/**
 * Customizer: company contact info used in header panel and footer.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section(
			'cases_company',
			array(
				'title'    => __( 'Company Info', 'cases' ),
				'priority' => 30,
			)
		);

		$fields = array(
			'phone'   => array( __( 'Phone', 'cases' ), 'sanitize_text_field', 'text' ),
			'email'   => array( __( 'Sales e-mail (also receives contact-form inquiries)', 'cases' ), 'sanitize_email', 'text' ),
			'address' => array( __( 'Address', 'cases' ), 'sanitize_text_field', 'textarea' ),
			'website' => array( __( 'Website URL', 'cases' ), 'esc_url_raw', 'url' ),
		);

		foreach ( $fields as $key => $def ) {
			$wp_customize->add_setting(
				'cases_' . $key,
				array(
					'default'           => cases_opt( $key ),
					'sanitize_callback' => $def[1],
				)
			);
			$wp_customize->add_control(
				'cases_' . $key,
				array(
					'label'   => $def[0],
					'section' => 'cases_company',
					'type'    => $def[2],
				)
			);
		}
	}
);
