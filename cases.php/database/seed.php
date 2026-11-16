<?php
/**
 * ============================================================================
 *  database/seed.php – DỮ LIỆU MẪU KHI CÀI ĐẶT LẦN ĐẦU
 * ============================================================================
 *
 * Được db_install() (app/db.php) nạp và gọi seed_database() ngay sau khi chạy
 * database/schema.sql, trong cùng một transaction. Tạo:
 *   - 1 tài khoản quản trị
 *   - Các cài đặt website (tên, thông tin liên hệ, nội dung banner trang chủ...)
 *   - 3 trang: Giới thiệu (hiện trên menu), Liên hệ (mẫu "contact"), Chính sách bảo mật
 *   - 4 bài viết chuyên mục "Sản phẩm" (hiện ở mục Sản phẩm trang chủ)
 *   - 2 bài viết Tin tức / Kiến thức
 *
 * Muốn đổi dữ liệu mẫu: sửa file này rồi chạy `php database/reset.php`.
 */
declare(strict_types=1);

/**
 * Dữ liệu mẫu. Tài khoản quản trị mặc định (chỉ dùng cho môi trường dev,
 * hãy đổi mật khẩu ở Admin → Tài khoản ngay sau khi đăng nhập):
 *   Email:    admin@cases.local
 *   Mật khẩu: admin123
 */
const SEED_ADMIN_EMAIL = 'admin@cases.local';
const SEED_ADMIN_PASSWORD = 'admin123';

/**
 * Nạp toàn bộ dữ liệu mẫu vào database vừa tạo bảng.
 *
 * Dùng thẳng đối tượng $pdo được truyền vào (cùng kết nối đang mở transaction)
 * thay vì các hàm db_* cho rõ ràng và độc lập.
 *
 * @param PDO $pdo  Kết nối SQLite (từ db_install)
 */
function seed_database(PDO $pdo): void
{
    // Mọi bản ghi mẫu dùng chung một thời điểm tạo / đăng.
    $now = date('Y-m-d H:i:s');
    // Hàm nhỏ chèn một dòng: $ins('tên_bảng', ['cột' => giá_trị, ...]).
    // Sinh câu INSERT với placeholder :cột rồi execute với chính mảng $row (prepared statement).
    $ins = function (string $table, array $row) use ($pdo): void {
        $cols = array_keys($row);
        $stmt = $pdo->prepare(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(',', $cols),
            implode(',', array_map(fn ($c) => ':' . $c, $cols))
        ));
        $stmt->execute($row);
    };

    // ---------- Tài khoản quản trị ----------
    // Mật khẩu được băm bằng password_hash trước khi lưu, không lưu dạng chữ thường.
    $ins('users', [
        'name'          => 'Quản trị viên',
        'email'         => SEED_ADMIN_EMAIL,
        'password_hash' => password_hash(SEED_ADMIN_PASSWORD, PASSWORD_DEFAULT),
        'created_at'    => $now,
    ]);
    // id của admin vừa tạo – gán làm tác giả (author_id) cho các trang/bài mẫu.
    $adminId = (int) $pdo->lastInsertId();

    // ---------- Cài đặt website (bảng settings) ----------
    // Các khoá phải khớp với setting_fields() trong app/admin.php để sửa được ở Admin → Cài đặt.
    // Giá trị trong [ngoặc vuông] là chỗ trống, cần thay bằng thông tin thật của công ty.
    // hero_title dùng chuỗi nháy kép nhiều dòng: mỗi lần xuống dòng = một lần ngắt dòng tiêu đề hero (nl2br ở view).
    $settings = [
        'site_name'        => 'CASES',
        'site_tagline'     => 'Tôm chất lượng, kiểm soát từng khâu theo chuẩn ISO.',
        'meta_description' => 'CASES cung cấp tôm có nguồn gốc rõ ràng, sơ chế và bảo quản trong quy trình khép kín theo chuẩn ISO.',
        'company_address'  => '[Địa chỉ công ty]',
        'company_phone'    => '[Số điện thoại]',
        'company_email'    => '[Email liên hệ]',
        'hero_eyebrow'     => 'Tôm sạch, truy xuất được nguồn gốc',
        'hero_title'       => "Tôm chất lượng,
kiểm soát
từng khâu
theo chuẩn",
        'hero_highlight'   => 'ISO.',
        'hero_description' => 'CASES cung cấp tôm có nguồn gốc rõ ràng, sơ chế và bảo quản trong quy trình khép kín. Mỗi lô đều được kiểm tra trước khi đến tay khách hàng.',
        'footer_note'      => '© CASES. Bản prototype thiết kế lại giao diện.',
    ];
    foreach ($settings as $key => $value) {
        $ins('settings', ['key' => $key, 'value' => $value]);
    }

    // ---------- Pages ----------
    // Mỗi trang chỉ khai báo các cột khác mặc định; phần chung được gộp ở vòng foreach bên dưới.
    $pages = [
        [
            'title' => 'Giới thiệu', 'slug' => 'gioi-thieu', 'show_in_menu' => 1, 'menu_order' => 1,
            'excerpt' => 'CASES – đơn vị cung cấp tôm vận hành theo hệ thống quản lý chất lượng ISO.',
            'content' => '<h2>Về CASES</h2><p>CASES là đơn vị cung cấp tôm cho nhà hàng, đại lý và nhà phân phối. Chúng tôi làm việc trực tiếp với các vùng nuôi đã được đánh giá, kiểm soát từ khâu thu hoạch, sơ chế, cấp đông cho đến khi giao hàng.</p><h2>Sứ mệnh</h2><p>Mang đến nguồn tôm <strong>ổn định, minh bạch và an toàn</strong> – cùng một tiêu chuẩn cho lô đầu tiên và lô thứ một trăm.</p><h2>Giá trị cốt lõi</h2><ul><li>Nguồn gốc rõ ràng, truy xuất được theo từng lô.</li><li>Quy trình khép kín, có hồ sơ ghi chép ở mọi bước.</li><li>Đồng hành lâu dài cùng khách hàng.</li></ul>',
        ],
        [
            'title' => 'Liên hệ', 'slug' => 'lien-he', 'template' => 'contact', 'show_in_menu' => 0,
            'excerpt' => 'Để lại thông tin, đội ngũ CASES sẽ liên hệ tư vấn size, số lượng và lịch giao phù hợp.',
            'content' => '<p>Bạn cần báo giá hoặc tư vấn sản phẩm? Hãy gọi cho chúng tôi hoặc gửi yêu cầu qua biểu mẫu bên dưới. Đội ngũ CASES phản hồi trong giờ làm việc.</p>',
        ],
        [
            'title' => 'Chính sách bảo mật', 'slug' => 'chinh-sach-bao-mat', 'show_in_menu' => 0,
            'excerpt' => 'Cách CASES thu thập và sử dụng thông tin khách hàng.',
            'content' => '<p>CASES chỉ sử dụng thông tin bạn cung cấp (họ tên, số điện thoại, nhu cầu) để liên hệ tư vấn và báo giá. Chúng tôi không chia sẻ thông tin cho bên thứ ba khi chưa có sự đồng ý của bạn.</p>',
        ],
    ];
    foreach ($pages as $p) {
        // Toán tử + gộp mảng: chỉ thêm các khoá $p CHƯA có (vd. trang Liên hệ đã có 'template' => 'contact'
        // nên giữ nguyên, các trang khác nhận 'template' => 'default').
        $ins('pages', $p + [
            'template' => 'default', 'menu_order' => 0, 'status' => 'published', 'author_id' => $adminId,
            'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    // ---------- Posts: sản phẩm (hiển thị ở trang chủ) ----------
    // Mỗi phần tử: [tiêu đề, nhãn trên ảnh, tóm tắt, nội dung HTML].
    // Thứ tự trong mảng = thứ tự hiển thị trên trang chủ (sort_order = vị trí + 1).
    $products = [
        ['Tôm tươi nguyên con', 'Tươi sống', 'Tuyển chọn theo size, bảo quản lạnh ngay sau thu hoạch.',
            '<p>Tôm được tuyển chọn theo size ngay tại vùng nuôi, làm lạnh nhanh để giữ độ tươi và độ ngọt tự nhiên của thịt.</p><h2>Quy cách</h2><ul><li>Size: 20 – 60 con/kg</li><li>Bảo quản: 0 – 4°C</li><li>Đóng thùng xốp kèm đá vảy</li></ul><h2>Phù hợp cho</h2><p>Nhà hàng, quán hải sản, bếp ăn cần nguồn tôm tươi hằng ngày.</p>'],
        ['Tôm đông lạnh', 'Cấp đông', 'Cấp đông giữ độ chắc thịt, tiện tồn kho và vận chuyển đường xa.',
            '<p>Tôm được cấp đông nhanh (IQF hoặc block) ngay sau sơ chế, giữ cấu trúc thịt chắc và hạn chế mất nước khi rã đông.</p><h2>Quy cách</h2><ul><li>Đông IQF hoặc đông block</li><li>Bảo quản: ≤ -18°C</li><li>Hạn dùng: 12 tháng</li></ul>'],
        ['Tôm sơ chế', 'Bóc vỏ, phân cỡ', 'Bóc vỏ, rút chỉ, phân cỡ sẵn, giảm thời gian chuẩn bị cho bếp.',
            '<p>Tôm được bóc vỏ, rút chỉ và phân cỡ trong khu vực sạch, giúp bếp của bạn tiết kiệm thời gian và nhân công.</p><h2>Các dạng sơ chế</h2><ul><li>PD – bóc vỏ, bỏ đầu, rút chỉ</li><li>PTO – bóc vỏ, chừa đuôi</li><li>Xẻ bướm theo yêu cầu</li></ul>'],
        ['Tôm chế biến sẵn', 'Đóng gói sẵn', 'Đóng gói theo yêu cầu, sẵn sàng cho dây chuyền của bạn.',
            '<p>Các sản phẩm tôm hấp, tôm tẩm bột, tôm xiên… được chế biến và đóng gói theo yêu cầu riêng của từng khách hàng.</p><h2>Ưu điểm</h2><ul><li>Định lượng đồng đều</li><li>In nhãn theo thương hiệu khách hàng</li><li>Sẵn sàng đưa vào dây chuyền phục vụ</li></ul>'],
    ];
    // [$title, $label, ...] = "destructuring": tách 4 phần tử của mỗi mảng con vào 4 biến.
    foreach ($products as $i => [$title, $label, $excerpt, $content]) {
        $ins('posts', [
            'title' => $title, 'slug' => seed_slug($title), 'excerpt' => $excerpt, 'content' => $content,
            'category' => 'san-pham', 'label' => $label, 'sort_order' => $i + 1, 'status' => 'published',
            'author_id' => $adminId, 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    // ---------- Posts: tin tức ----------
    // Mỗi phần tử: [tiêu đề, mã chuyên mục, tóm tắt, nội dung HTML].
    $news = [
        ['CASES hoàn tất đánh giá giám sát ISO năm 2026', 'tin-tuc',
            'Hệ thống quản lý chất lượng của CASES tiếp tục được duy trì sau đợt đánh giá giám sát định kỳ.',
            '<p>Đợt đánh giá giám sát định kỳ vừa qua ghi nhận hệ thống quản lý chất lượng của CASES được vận hành ổn định, hồ sơ ghi chép đầy đủ theo từng lô.</p><blockquote>Mỗi bước đều có tiêu chí, hồ sơ ghi chép và người chịu trách nhiệm.</blockquote><p>CASES sẽ tiếp tục cải tiến theo chu trình hoạch định – thực hiện – kiểm tra – cải tiến.</p>'],
        ['5 lưu ý khi rã đông tôm để giữ độ ngọt thịt', 'kien-thuc',
            'Rã đông đúng cách giúp tôm giữ được kết cấu và vị ngọt tự nhiên.',
            '<ol><li>Rã đông trong ngăn mát tủ lạnh 8 – 12 giờ.</li><li>Không rã đông bằng nước nóng.</li><li>Có thể ngâm túi kín trong nước lạnh nếu cần nhanh.</li><li>Chế biến ngay sau khi rã đông.</li><li>Không cấp đông lại tôm đã rã đông.</li></ol>'],
    ];
    foreach ($news as [$title, $cat, $excerpt, $content]) {
        $ins('posts', [
            'title' => $title, 'slug' => seed_slug($title), 'excerpt' => $excerpt, 'content' => $content,
            'category' => $cat, 'status' => 'published', 'author_id' => $adminId,
            'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}

/**
 * Tạo slug cho dữ liệu mẫu.
 *
 * Bình thường dùng slugify() (app/helpers.php – bỏ dấu tiếng Việt đúng cách).
 * Nhánh dự phòng chỉ để file vẫn chạy được nếu được nạp riêng mà chưa có helpers.php.
 *
 * @param string $title  Tiêu đề, vd. 'Tôm đông lạnh'
 * @return string  vd. 'tom-dong-lanh'
 */
function seed_slug(string $title): string
{
    return function_exists('slugify') ? slugify($title) : strtolower(preg_replace('/\W+/', '-', $title));
}
