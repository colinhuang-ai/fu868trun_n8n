<?php
/**
 * ============================================================================
 *  app/content.php – ĐỊNH NGHĨA NỘI DUNG CMS & CÁC HÀM LIÊN QUAN
 * ============================================================================
 *
 * CMS quản lý 2 loại nội dung có cấu trúc gần giống nhau:
 *   - pages (Trang):    trang tĩnh như Giới thiệu, Liên hệ → URL /{slug}
 *   - posts (Bài viết): sản phẩm, tin tức, kiến thức       → URL /bai-viet/{slug}
 *
 * Vì giống nhau nên trang quản trị dùng CHUNG một bộ controller CRUD
 * (admin_content_* trong app/admin.php) và chung view (views/admin/content/*).
 * Sự khác biệt giữa 2 loại được mô tả ở content_types() bên dưới.
 *
 * File này cũng chứa: danh sách chuyên mục, mẫu trang, trạng thái, slug dành riêng,
 * hàm tạo slug không trùng, điều kiện SQL "đang công khai", menu, bảng màu
 * hình con tôm và hàm lọc HTML từ trình soạn thảo.
 */
declare(strict_types=1);

/**
 * Định nghĩa 2 loại nội dung CMS quản lý: Trang (pages) và Bài viết (posts).
 *
 * Khoá ngoài cùng ('pages' / 'posts') chính là tham số {type} trong URL admin
 * (/admin/pages, /admin/posts/3/edit...). Mỗi loại gồm:
 *   - table:    tên bảng trong database
 *   - singular: tên số ít, chữ thường (dùng trong câu "Sửa trang", "Xoá bài viết")
 *   - plural:   tên hiển thị trên tiêu đề danh sách
 *   - new:      nhãn nút tạo mới
 *   - icon:     tên icon (xem app/icons.php)
 *
 * Muốn thêm loại nội dung thứ 3 (vd. "Dự án"): thêm phần tử ở đây, tạo bảng tương ứng
 * trong database/schema.sql và bổ sung trường riêng trong admin_content_save()/form.
 *
 * @return array
 */
function content_types(): array
{
    return [
        'pages' => [
            'table'    => 'pages',
            'singular' => 'trang',
            'plural'   => 'Trang',
            'new'      => 'Thêm trang',
            'icon'     => 'file-text',
        ],
        'posts' => [
            'table'    => 'posts',
            'singular' => 'bài viết',
            'plural'   => 'Bài viết',
            'new'      => 'Viết bài mới',
            'icon'     => 'newspaper',
        ],
    ];
}

/**
 * Lấy định nghĩa của một loại nội dung theo tên trong URL.
 *
 * Đây cũng là lớp KIỂM TRA AN TOÀN: tham số {type} đến từ URL người dùng gõ,
 * nếu không phải 'pages' hoặc 'posts' thì trả 404 ngay. Nhờ vậy tên bảng
 * ($t['table']) dùng trong SQL luôn là giá trị do code định nghĩa, không phải input.
 *
 * @param string $type  'pages' | 'posts'
 * @return array  Phần tử tương ứng trong content_types()
 */
function content_type(string $type): array
{
    $types = content_types();
    if (!isset($types[$type])) {
        abort(404);
    }
    return $types[$type];
}

/**
 * Đường dẫn công khai (ngoài website) của một trang/bài viết.
 *
 * Ví dụ: content_public_url('pages', ['slug' => 'gioi-thieu']) → '/gioi-thieu'
 *        content_public_url('posts', ['slug' => 'tom-so-che'])  → '/bai-viet/tom-so-che'
 *
 * @param string $type  'pages' | 'posts'
 * @param array  $row   Dòng dữ liệu có khoá 'slug'
 * @return string  Chưa có base path (khi in dùng url())
 */
function content_public_url(string $type, array $row): string
{
    return $type === 'pages' ? '/' . $row['slug'] : '/bai-viet/' . $row['slug'];
}

/**
 * Danh sách chuyên mục bài viết: mã (lưu trong cột posts.category, dùng trên URL ?cat=) => tên hiển thị.
 *
 * Chuyên mục 'san-pham' đặc biệt: các bài thuộc chuyên mục này hiển thị ở mục
 * "Sản phẩm" trên trang chủ (xem site_home() trong app/site.php).
 *
 * @return array
 */
function post_categories(): array
{
    return [
        'san-pham'  => 'Sản phẩm',
        'tin-tuc'   => 'Tin tức',
        'kien-thuc' => 'Kiến thức',
    ];
}

/**
 * Các mẫu giao diện cho Trang (cột pages.template).
 *   - default: tiêu đề + nội dung bình thường
 *   - contact: nội dung + thông tin liên hệ + form báo giá (xem views/site/page.php)
 *
 * @return array  mã => tên hiển thị trong ô chọn ở trang sửa
 */
function page_templates(): array
{
    return [
        'default' => 'Mặc định',
        'contact' => 'Trang liên hệ (kèm form báo giá)',
    ];
}

/**
 * Trạng thái xuất bản dùng chung cho Trang và Bài viết.
 *   - published: hiển thị công khai (khi đã tới ngày đăng)
 *   - draft:     bản nháp, chỉ admin xem trước được
 *
 * @return array  mã => tên hiển thị
 */
function statuses(): array
{
    return ['published' => 'Đã xuất bản', 'draft' => 'Bản nháp'];
}

/**
 * Slug không được trùng với route hệ thống.
 *
 * Trang có URL /{slug} nên nếu tạo trang slug 'admin' sẽ đụng khu vực quản trị,
 * 'bai-viet' đụng trang danh sách bài viết, 'assets'/'uploads' đụng thư mục file tĩnh...
 * admin_content_save() sẽ báo lỗi nếu người dùng chọn một slug trong danh sách này.
 *
 * @return array
 */
function reserved_page_slugs(): array
{
    return ['admin', 'bai-viet', 'uploads', 'assets', 'index', 'index-php', 'router-php'];
}

/**
 * Đảm bảo slug là DUY NHẤT trong bảng: nếu đã có bản ghi khác dùng slug này
 * thì thêm hậu tố -2, -3, ... cho tới khi không trùng.
 *
 * Ví dụ: đã có 'tom-su' → unique_slug('posts', 'tom-su') → 'tom-su-2'
 *
 * @param string   $table     Tên bảng ('pages' | 'posts' – từ content_type(), không phải input)
 * @param string   $slug      Slug mong muốn
 * @param int|null $exceptId  id của chính bản ghi đang sửa (bỏ qua chính nó khi kiểm tra trùng)
 * @return string  Slug không trùng
 */
function unique_slug(string $table, string $slug, ?int $exceptId = null): string
{
    $base = $slug;
    $i = 2;
    // Khi tạo mới ($exceptId = null) dùng id != 0 → không loại trừ bản ghi nào (id luôn >= 1).
    while (db_value(
        "SELECT 1 FROM $table WHERE slug = ? AND id != ?",
        [$slug, $exceptId ?? 0]
    )) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

/**
 * Điều kiện "đang hiển thị công khai": đã xuất bản và tới giờ đăng.
 *
 * Trả về một đoạn SQL để chèn vào WHERE. Đây là cơ chế HẸN GIỜ ĐĂNG BÀI:
 * bài có status = 'published' nhưng published_at ở tương lai sẽ chưa hiện,
 * tới đúng giờ thì tự hiện (vì mỗi request so sánh với now()).
 *
 * Ví dụ: published_sql()    → "status = 'published' AND (published_at IS NULL OR published_at <= '2026-11-05 09:30:00')"
 *        published_sql('p') → "p.status = 'published' AND (p.published_at IS NULL OR ...)" (khi JOIN có alias)
 *
 * Giá trị now() do server tạo (không phải input người dùng) nên nối thẳng vào chuỗi SQL là an toàn.
 * So sánh chuỗi ngày hoạt động đúng vì định dạng 'Y-m-d H:i:s' sắp xếp theo thứ tự thời gian.
 *
 * @param string $alias  Bí danh bảng trong câu SQL (để trống nếu không dùng)
 * @return string
 */
function published_sql(string $alias = ''): string
{
    $p = $alias ? $alias . '.' : '';
    return "{$p}status = 'published' AND ({$p}published_at IS NULL OR {$p}published_at <= '" . now() . "')";
}

/**
 * Danh sách Trang được bật "Hiện trên menu" (và đang công khai), sắp theo thứ tự menu.
 * Dùng trong views/layouts/site.php để chèn vào menu chính.
 *
 * Toán tử ??= : nếu $pages đang null thì truy vấn và gán, ngược lại dùng lại giá trị cũ
 * → cache trong request (layout có thể gọi nhiều lần).
 *
 * @return array  [['title' => 'Giới thiệu', 'slug' => 'gioi-thieu'], ...]
 */
function menu_pages(): array
{
    static $pages = null;
    return $pages ??= db_all(
        'SELECT title, slug FROM pages WHERE show_in_menu = 1 AND ' . published_sql() . ' ORDER BY menu_order, title'
    );
}

/**
 * Theme minh hoạ con tôm cho các card không có ảnh.
 *
 * 4 bộ màu lấy theo prototype, các card sẽ lần lượt dùng bộ 0,1,2,3,0,1... (xem views/partials/post-card.php).
 * Mỗi bộ gồm:
 *   - bg:     màu nền khung ảnh của card
 *   - circle: màu vòng tròn lớn phía sau con tôm
 *   - ring:   màu viền tròn mảnh bao ngoài (null = không vẽ)
 *   - body:   màu thân tôm
 *   - line:   màu vạch đốt trên thân
 *   - spark:  màu bông tuyết trang trí (null = không vẽ; dùng cho "tôm đông lạnh")
 *
 * @return array
 */
function shrimp_themes(): array
{
    return [
        ['bg' => '#1b3566', 'circle' => '#34507f', 'ring' => null,      'body' => '#d9776d', 'line' => '#b0554d', 'spark' => null],
        ['bg' => '#dceaf9', 'circle' => '#f1f7fe', 'ring' => null,      'body' => '#4fb3d9', 'line' => '#2b8db5', 'spark' => '#2b8db5'],
        ['bg' => '#fbe9e7', 'circle' => '#ffffff', 'ring' => '#b9c3d6', 'body' => '#eea29a', 'line' => '#cf7d74', 'spark' => null],
        ['bg' => '#f7e2b2', 'circle' => '#fdf4e2', 'ring' => '#d8b878', 'body' => '#c9504a', 'line' => '#9f3934', 'spark' => null],
    ];
}

/**
 * Lọc HTML từ editor: bỏ script/style/iframe và thuộc tính sự kiện. Admin là người tin cậy, đây là lớp phòng hờ.
 *
 * Nội dung bài viết là HTML (do trình soạn thảo Quill tạo) và được in RA NGUYÊN VĂN
 * ngoài website (không qua e()), nên trước khi lưu cần gỡ những thứ có thể chạy mã:
 *   1. Thẻ <script>, <style>, <iframe>, <object>, <embed> có nội dung → xoá cả cặp.
 *   2. Các thẻ đó dạng đơn/tự đóng (vd. <embed src=...>) → xoá.
 *   3. Thuộc tính sự kiện onclick=, onerror=, onload=... → xoá.
 *   4. Link/ảnh dạng href="javascript:..." hoặc src="javascript:..." → đổi thành "#".
 *   5. Nội dung rỗng của editor ('<p><br></p>') → lưu thành chuỗi rỗng.
 *
 * Đây là bộ lọc bằng regex đơn giản, đủ cho trường hợp chỉ admin soạn bài.
 * Nếu mở cho nhiều người không tin cậy soạn bài, nên dùng thư viện HTML Purifier.
 *
 * @param string $html  HTML thô gửi lên từ form
 * @return string  HTML đã lọc
 */
function clean_html(string $html): string
{
    // 1) Cờ i: không phân biệt hoa/thường; s: dấu . khớp cả xuống dòng; .*? khớp ngắn nhất; \1 = tên thẻ mở.
    $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html);
    // 2) Thẻ nguy hiểm còn sót lại không có thẻ đóng.
    $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*/?>#i', '', (string) $html);
    // 3) Thuộc tính on*= với giá trị trong "..." hoặc '...' hoặc không có nháy.
    $html = preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', (string) $html);
    // 4) href/src bắt đầu bằng javascript: → thay bằng "#". $1 = tên thuộc tính, \2 = dấu nháy mở.
    $html = preg_replace('#(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2#i', '$1="#"', (string) $html);
    $html = trim((string) $html);
    // 5) Editor trống vẫn sinh ra đoạn văn rỗng → coi như không có nội dung.
    return in_array($html, ['<p><br></p>', '<p></p>'], true) ? '' : $html;
}
