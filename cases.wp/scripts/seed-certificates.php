<?php
/**
 * Idempotent seed for the Certificates entity (data taken from the prototype).
 *   wp eval-file /scripts/seed-certificates.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! post_type_exists( 'cases_certificate' ) ) {
	WP_CLI::error( 'Theme "cases" must be active (certificate post type missing).' );
}

$groups = array(
	'food-safety-quality'     => array( 'Food Safety & Quality', 1 ),
	'sustainable-aquaculture' => array( 'Sustainable Aquaculture', 2 ),
	'specialist-markets'      => array( 'Specialist Markets', 3 ),
	'registrations'           => array( 'Registrations', 4 ),
);
$term_ids = array();
foreach ( $groups as $slug => $g ) {
	$t = term_exists( $slug, 'certificate_group' );
	if ( ! $t ) {
		$t = wp_insert_term( $g[0], 'certificate_group', array( 'slug' => $slug ) );
	}
	$term_ids[ $slug ] = is_array( $t ) ? (int) $t['term_id'] : (int) $t;
	update_term_meta( $term_ids[ $slug ], 'cases_order', $g[1] );
}

// name, group, icon, icon colour, badge colour, badge EN/VI, desc EN/VI, foot EN/VI, issuer, scope EN/VI, show card.
$certs = array(
	array(
		'HACCP', 'food-safety-quality', '🛡️', 'blue', 'blue',
		'Hazard Analysis', 'Phân Tích Mối Nguy',
		"Hazard Analysis and Critical Control Points — CASES' foundational food-safety management system since 2000, ensuring full processing-chain control from raw material to final product.",
		'Hệ thống phân tích mối nguy và kiểm soát điểm tới hạn — nền tảng quản lý an toàn thực phẩm của CASES từ năm 2000, đảm bảo kiểm soát toàn bộ chuỗi từ nguyên liệu đến thành phẩm.',
		'Active · Full facility scope', 'Hiệu lực · Toàn nhà máy',
		'NAFIQAD', 'Full facility', 'Toàn nhà máy', '1',
	),
	array(
		'ISO 22000:2018', 'food-safety-quality', '📋', 'blue', 'green',
		'Food Safety Mgmt', 'Quản Lý ATTP',
		'International standard for Food Safety Management Systems — issued by NAFIQAD covering seafood processing and freezing operations at CASES facilities.',
		'Tiêu chuẩn quốc tế về hệ thống quản lý an toàn thực phẩm — do NAFIQAD cấp cho hoạt động chế biến và cấp đông thủy sản tại cơ sở CASES.',
		'TCVN ISO 22000:2018 · NAFIQAD', 'TCVN ISO 22000:2018 · NAFIQAD',
		'NAFIQAD', 'Seafood processing', 'Chế biến thủy sản', '1',
	),
	array(
		'IFS Food v8', 'food-safety-quality', '🏆', 'amber', 'amber',
		'Retail Standard', 'Tiêu Chuẩn Bán Lẻ',
		'IFS Food Version 8 — the international retail standard awarded by Intertek, opening access to European supermarket chains with a high audit score.',
		'IFS Food phiên bản 8 — tiêu chuẩn bán lẻ quốc tế do Intertek cấp, mở cánh cửa vào chuỗi siêu thị châu Âu với điểm đánh giá cao.',
		'Intertek · EU Retail Markets', 'Intertek · Thị trường bán lẻ EU',
		'Intertek', 'EU retail market', 'Thị trường bán lẻ EU', '1',
	),
	array(
		'BAP ★★', 'sustainable-aquaculture', '🐟', 'green', 'green',
		'Best Aquaculture Practices', 'Thực Hành Nuôi Trồng Tốt Nhất',
		'2-star BAP certification (plant + farm) from the Global Seafood Alliance — the highest-tier standard for responsible aquaculture, preferred by US and EU importers.',
		'Chứng nhận BAP 2 sao (nhà máy + trang trại) từ Global Seafood Alliance — tiêu chuẩn cao nhất về nuôi trồng thủy sản có trách nhiệm, được nhà nhập khẩu Mỹ và EU ưu tiên.',
		'GSA · Dam Doi Farm · Plant', 'GSA · Trại Dam Doi · Nhà máy',
		'Global Seafood Alliance', 'Plant + farm', 'Nhà máy + trang trại', '1',
	),
	array(
		'ASC CoC', 'sustainable-aquaculture', '🌊', 'green', 'green',
		'Chain of Custody', 'Chuỗi Hành Trình',
		'ASC Chain of Custody — product traceability certification to Aquaculture Stewardship Council standards, enabling ASC labelling on exported products.',
		'ASC Chain of Custody — chứng nhận truy xuất nguồn gốc theo tiêu chuẩn Hội đồng Quản lý Nuôi trồng, cho phép dán nhãn ASC trên sản phẩm xuất khẩu.',
		'Control Union · ASC-CoC-010639', 'Control Union · ASC-CoC-010639',
		'Control Union', 'Product traceability', 'Truy xuất nguồn gốc', '1',
	),
	array(
		'Global G.A.P', 'sustainable-aquaculture', '🌱', 'green', 'blue',
		'Pond Farming', 'Nuôi Ao',
		'Global Seafood Alliance Pond Farming standard awarded by Bureau Veritas for Dam Doi Farm — confirming raw-material control from the start of the supply chain.',
		'Tiêu chuẩn nuôi ao của Global Seafood Alliance do Bureau Veritas cấp cho Trại Dam Doi — xác nhận kiểm soát nguyên liệu từ đầu chuỗi cung ứng.',
		'Bureau Veritas · Dam Doi Farm', 'Bureau Veritas · Trại Dam Doi',
		'Bureau Veritas', 'Dam Doi Farm', 'Trại Dam Doi', '1',
	),
	array(
		'HALAL', 'specialist-markets', '☪️', 'purple', 'purple',
		'Islamic Market', 'Thị Trường Hồi Giáo',
		'HALAL certification ensuring products meet Islamic food requirements, expanding market access to UAE, Malaysia and Southeast Asian markets.',
		'Chứng nhận HALAL đảm bảo sản phẩm đáp ứng yêu cầu thực phẩm Hồi giáo, mở rộng thị trường sang UAE, Malaysia và các nước Đông Nam Á.',
		'Active · UAE, Malaysia, SE Asia', 'Hiệu lực · UAE, Malaysia, Đông Nam Á',
		'—', 'UAE, Malaysia, SE Asia', 'UAE, Malaysia, Đông Nam Á', '1',
	),
	array(
		'ORGANIC', 'specialist-markets', '🌿', 'green', 'green',
		'Organic Certified', 'Hữu Cơ',
		'Organic certification — products farmed without synthetic chemicals, meeting premium-segment demand across EU and North American markets.',
		'Chứng nhận hữu cơ — sản phẩm nuôi trồng không dùng hóa chất tổng hợp, đáp ứng phân khúc cao cấp tại EU và Bắc Mỹ.',
		'Active · EU & North America', 'Hiệu lực · EU & Bắc Mỹ',
		'—', 'EU, North America', 'EU, Bắc Mỹ', '1',
	),
	array(
		'US FDA', 'registrations', '🏛️', 'blue', 'blue',
		'Registered', 'Đã Đăng Ký',
		'Registered with the US Food & Drug Administration — a prerequisite for seafood exports into the American market.',
		'Đăng ký với Cục Quản lý Thực phẩm và Dược phẩm Hoa Kỳ (FDA) — điều kiện tiên quyết để xuất khẩu thủy sản vào thị trường Mỹ.',
		'Registrar Corp · US Market', 'Registrar Corp · Thị trường Mỹ',
		'Registrar Corp', 'US Market', 'Thị Trường Mỹ',
	),
);

$created = 0;
foreach ( $certs as $i => $c ) {
	$exists = get_posts(
		array(
			'post_type'      => 'cases_certificate',
			'post_status'    => 'any',
			'title'          => $c[0],
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( $exists ) {
		continue;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'cases_certificate',
			'post_status'  => 'publish',
			'post_title'   => $c[0],
			'post_content' => $c[7],
			'menu_order'   => $i + 1,
		)
	);
	wp_set_object_terms( $id, array( $term_ids[ $c[1] ] ), 'certificate_group' );
	$meta = array(
		'icon'        => $c[2],
		'icon_color'  => $c[3],
		'badge_color' => $c[4],
		'badge_en'    => $c[5],
		'badge_vi'    => $c[6],
		'desc_vi'     => $c[8],
		'foot_en'     => $c[9],
		'foot_vi'     => $c[10],
		'issuer'      => $c[11],
		'scope_en'    => $c[12],
		'scope_vi'    => $c[13],
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, 'cases_cert_' . $k, $v );
	}
	++$created;
}

WP_CLI::success( sprintf( 'Certificates seeded: %d new.', $created ) );
