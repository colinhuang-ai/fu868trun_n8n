<?php
/**
 * ============================================================================
 *  app/site.php – CONTROLLER PHẦN WEBSITE CÔNG KHAI (khách xem)
 * ============================================================================
 *
 * "Controller" là hàm nhận request, lấy dữ liệu từ database rồi gọi view để
 * hiển thị. Mỗi hàm ở đây được gắn với một URL trong public/index.php:
 *
 *   GET  /                  → site_home()           – trang chủ (views/site/home.php)
 *   GET  /bai-viet          → site_posts_index()    – danh sách bài viết (views/site/posts.php)
 *   GET  /bai-viet/{slug}   → site_post_show()      – chi tiết bài viết (views/site/post.php)
 *   GET  /{slug}            → site_page_show()      – trang tĩnh (views/site/page.php)
 *   POST /lien-he           → site_contact_submit() – nhận form "Nhận báo giá"
 *
 * Tất cả view ở đây dùng layout 'site' (views/layouts/site.php: header, menu, footer).
 * Biến 'active' truyền cho layout để tô sáng mục menu đang xem.
 */
declare(strict_types=1);

/* ================= Frontend controllers ================= */

/**
 * Trang chủ.
 *
 * Phần lớn nội dung trang chủ (cam kết, ISO, quy trình...) là cố định trong view
 * views/site/home.php; banner lấy từ Cài đặt (setting). Hai phần lấy từ CMS:
 *   - $products: tối đa 4 bài chuyên mục "Sản phẩm" đang công khai,
 *                sắp theo "Thứ tự" (sort_order) tăng dần, cùng thứ tự thì bài mới trước.
 *   - $latest:   3 bài mới nhất thuộc các chuyên mục khác (Tin tức, Kiến thức),
 *                bài đánh dấu "Nổi bật" (is_featured = 1) được ưu tiên lên đầu.
 */
function site_home(): void
{
    $products = db_all(
        "SELECT * FROM posts WHERE category = 'san-pham' AND " . published_sql() . ' ORDER BY sort_order, published_at DESC LIMIT 4'
    );
    $latest = db_all(
        "SELECT * FROM posts WHERE category != 'san-pham' AND " . published_sql() . ' ORDER BY is_featured DESC, published_at DESC LIMIT 3'
    );
    echo view('site/home', [
        // 'home' báo cho layout biết đang ở trang chủ → link menu dùng neo #san-pham thay vì /#san-pham.
        'active'   => 'home',
        'products' => $products,
        'latest'   => $latest,
    ], 'site');
}

/**
 * Danh sách bài viết, có lọc theo chuyên mục và phân trang.
 *
 * URL ví dụ: /bai-viet, /bai-viet?cat=san-pham, /bai-viet?cat=tin-tuc&page=2
 */
function site_posts_index(): void
{
    $cats = post_categories();
    // Chuyên mục từ query string ?cat=...; nếu không nằm trong danh sách hợp lệ thì bỏ qua (xem tất cả).
    $cat = (string) input('cat');
    if ($cat !== '' && !isset($cats[$cat])) {
        $cat = '';
    }

    // Điều kiện WHERE cơ bản: chỉ bài đang công khai (đã xuất bản + tới ngày đăng).
    $where = published_sql();
    $params = [];
    if ($cat !== '') {
        // Giá trị chuyên mục truyền qua tham số :cat (prepared statement), không nối vào chuỗi.
        $where .= ' AND category = :cat';
        $params['cat'] = $cat;
    }
    // Đếm tổng số bài để tính số trang.
    $total = (int) db_value("SELECT COUNT(*) FROM posts WHERE $where", $params);
    $pg = paginate($total, (int) input('page', 1), (int) config('per_page_site'));
    // LIMIT/OFFSET là số nguyên do paginate() tính (đã ép kiểu, kẹp giới hạn) nên nối vào SQL an toàn.
    // Sắp xếp: mới nhất trước; cùng thời điểm thì id lớn (tạo sau) trước.
    $posts = db_all(
        "SELECT * FROM posts WHERE $where ORDER BY published_at DESC, id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}",
        $params
    );

    echo view('site/posts', [
        'title'      => $cat ? $cats[$cat] : 'Bài viết',
        'active'     => 'posts',
        'posts'      => $posts,
        'pagination' => $pg,
        'cat'        => $cat,
        'categories' => $cats,
    ], 'site');
}

/**
 * Chi tiết một bài viết theo slug.
 *
 * Quy tắc hiển thị:
 *   - Bài đang công khai → ai cũng xem được.
 *   - Bài nháp / hẹn giờ chưa tới → chỉ admin đã đăng nhập xem được (chế độ "xem trước",
 *     layout hiện thanh vàng "Bạn đang xem bản nháp"); khách sẽ nhận 404.
 *   - Không có slug này → 404.
 *
 * @param string $slug  Phần {slug} trong URL /bai-viet/{slug} (router truyền vào theo tên)
 */
function site_post_show(string $slug): void
{
    $post = db_one('SELECT * FROM posts WHERE slug = ?', [$slug]);
    // $isPreview = true khi bài tồn tại nhưng KHÔNG thoả điều kiện công khai (nháp hoặc chưa tới giờ đăng).
    $isPreview = $post && !db_value('SELECT 1 FROM posts WHERE id = ? AND ' . published_sql(), [$post['id']]);
    if (!$post || ($isPreview && !current_user())) {
        abort(404);
    }

    // Tối đa 3 bài công khai khác cùng chuyên mục → khung "Cùng chuyên mục" ở cột phải.
    $related = db_all(
        'SELECT * FROM posts WHERE category = ? AND id != ? AND ' . published_sql() . ' ORDER BY published_at DESC LIMIT 3',
        [$post['category'], $post['id']]
    );

    echo view('site/post', [
        // Thẻ <title>: ưu tiên "Tiêu đề SEO" nếu admin có nhập, không thì dùng tiêu đề bài.
        'title'            => $post['meta_title'] ?: $post['title'],
        // Thẻ meta description: Mô tả SEO → Tóm tắt → tự rút gọn từ nội dung.
        'meta_description' => $post['meta_description'] ?: ($post['excerpt'] ?: str_excerpt($post['content'])),
        // Ảnh khi chia sẻ link lên Facebook/Zalo (thẻ og:image).
        'og_image'         => $post['featured_image'],
        'active'           => 'posts',
        'post'             => $post,
        'related'          => $related,
        'is_preview'       => $isPreview,
        // Link "Sửa nội dung này" hiện cho admin đang đăng nhập.
        'edit_url'         => '/admin/posts/' . $post['id'] . '/edit',
    ], 'site');
}

/**
 * Trang tĩnh theo slug (vd. /gioi-thieu, /lien-he, /chinh-sach-bao-mat).
 * Quy tắc nháp/xem trước giống site_post_show().
 *
 * Route /{slug} được khai báo CUỐI CÙNG trong public/index.php, nên mọi URL một cấp
 * không khớp route nào khác đều rơi vào đây; không có trang nào slug đó → 404.
 *
 * @param string $slug
 */
function site_page_show(string $slug): void
{
    $page = db_one('SELECT * FROM pages WHERE slug = ?', [$slug]);
    $isPreview = $page && !db_value('SELECT 1 FROM pages WHERE id = ? AND ' . published_sql(), [$page['id']]);
    if (!$page || ($isPreview && !current_user())) {
        abort(404);
    }

    echo view('site/page', [
        'title'            => $page['meta_title'] ?: $page['title'],
        'meta_description' => $page['meta_description'] ?: ($page['excerpt'] ?: str_excerpt($page['content'])),
        'og_image'         => $page['featured_image'],
        // 'page:<slug>' để layout tô sáng đúng mục menu của trang này (nếu trang có trên menu).
        'active'           => 'page:' . $page['slug'],
        'page'             => $page,
        'is_preview'       => $isPreview,
        'edit_url'         => '/admin/pages/' . $page['id'] . '/edit',
    ], 'site');
}

/**
 * Nhận form "Nhận báo giá" (ở trang chủ hoặc trang Liên hệ) và lưu vào bảng contacts.
 * Admin xem ở mục "Yêu cầu báo giá".
 *
 * Mẫu Post/Redirect/Get: xử lý xong luôn redirect về trang có form, kết quả
 * (lỗi / dữ liệu cũ / lời cảm ơn) gửi qua session và được partial
 * views/partials/contact-form.php đọc ra rồi xoá. Nhờ vậy F5 không gửi lại form.
 *
 * Trường ẩn '_back' cho biết form nằm ở đâu để quay về đúng chỗ:
 *   'page' → /lien-he#bao-gia      (mặc định) → /#lien-he
 */
function site_contact_submit(): void
{
    // Chống CSRF: form phải có token hợp lệ của phiên.
    csrf_verify();
    $back = input('_back') === 'page' ? '/lien-he#bao-gia' : '/#lien-he';

    // Honeypot: bot thường điền mọi trường
    // Form có một ô "website" bị ẩn bằng CSS; người thật không thấy nên để trống.
    // Bot spam tự điền tất cả ô → nếu ô này có giá trị thì lặng lẽ bỏ qua (không lưu, không báo lỗi).
    if (input('website') !== '') {
        redirect($back);
    }

    // Cắt độ dài tối đa (mb_substr để không cắt đôi chữ có dấu) → tránh dữ liệu quá lớn.
    $data = [
        'name'    => mb_substr((string) input('name'), 0, 120),
        'phone'   => mb_substr((string) input('phone'), 0, 30),
        'message' => mb_substr((string) input('message'), 0, 2000),
    ];
    $errors = [];
    if ($data['name'] === '') {
        $errors['name'] = 'Vui lòng nhập họ và tên.';
    }
    // Số điện thoại: 8–20 ký tự, chỉ gồm chữ số, dấu cách, + ( ) . -  (vd. "0900 000 000", "+84 (28) 3999-888").
    if (!preg_match('/^[0-9 +().-]{8,20}$/', $data['phone'])) {
        $errors['phone'] = 'Số điện thoại chưa hợp lệ.';
    }

    if ($errors) {
        // Lưu lỗi + dữ liệu đã nhập để form hiển thị lại (người dùng không phải gõ lại).
        $_SESSION['contact_old'] = $data;
        $_SESSION['contact_errors'] = $errors;
        redirect($back);
    }

    // Hợp lệ → lưu DB kèm nguồn (form ở trang nào), IP người gửi và thời điểm.
    db_insert('contacts', $data + [
        'source'     => input('_back') === 'page' ? 'Trang liên hệ' : 'Trang chủ',
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
        'created_at' => now(),
    ]);
    // Lời cảm ơn hiển thị trong form sau khi redirect (tên được escape khi in ở view).
    $_SESSION['contact_success'] = 'Cảm ơn ' . $data['name'] . '! CASES đã nhận yêu cầu và sẽ liên hệ với bạn sớm.';
    redirect($back);
}
