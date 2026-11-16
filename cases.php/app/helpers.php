<?php
/**
 * ============================================================================
 *  app/helpers.php – CÁC HÀM TIỆN ÍCH DÙNG CHUNG TOÀN ỨNG DỤNG
 * ============================================================================
 *
 * Được nạp đầu tiên trong app/bootstrap.php, nên mọi controller (app/site.php,
 * app/admin.php) và mọi view (views/**) đều gọi được các hàm này.
 *
 * Nhóm hàm:
 *   - Cấu hình & escape:   config(), e()
 *   - URL:                 base_path(), url(), asset(), current_path(), query_url(), redirect()
 *   - Request:             input(), abort()
 *   - Session:             flash(), flashes(), csrf_token(), csrf_field(), csrf_verify()
 *   - View (giao diện):    view(), partial()
 *   - Chuỗi & ngày:        slugify(), now(), format_date(), str_excerpt(), paginate()
 *   - Cài đặt website:     setting()
 *   - Upload ảnh:          store_upload(), has_upload()
 */
declare(strict_types=1);

/**
 * Đọc một giá trị cấu hình trong config/config.php.
 *
 * Ví dụ: config('per_page_site') → 9
 *
 * @param string $key      Khoá cấu hình
 * @param mixed  $default  Giá trị trả về nếu khoá không tồn tại
 * @return mixed
 */
function config(string $key, mixed $default = null): mixed
{
    return $GLOBALS['config'][$key] ?? $default;
}

/**
 * Escape (mã hoá) chuỗi trước khi in ra HTML – CHỐNG XSS.
 *
 * Biến các ký tự đặc biệt thành thực thể HTML: < → &lt;  > → &gt;  " → &quot;  ' → &#039;  & → &amp;
 * Nhờ đó nếu người dùng nhập "<script>..." thì trình duyệt chỉ hiển thị chữ, không chạy mã.
 *
 * QUY TẮC: mọi dữ liệu động in ra view đều phải bọc e(), trừ HTML nội dung bài viết
 * do admin soạn (đã lọc bằng clean_html khi lưu).
 *
 * Ví dụ: <h1><?= e($post['title']) ?></h1>
 *
 * @param mixed $value  Giá trị bất kỳ (null → chuỗi rỗng)
 * @return string
 */
function e(mixed $value): string
{
    // ENT_QUOTES: escape cả nháy đơn và nháy kép (an toàn khi in trong thuộc tính HTML).
    // ENT_SUBSTITUTE: ký tự UTF-8 lỗi được thay bằng ký tự thay thế thay vì trả chuỗi rỗng.
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ---------- URL ---------- */

/**
 * Đường dẫn gốc khi chạy trong thư mục con (vd. XAMPP: /cases.php/public).
 *
 * - Chạy bằng PHP built-in server (php -S) → website nằm ở gốc domain → trả ''.
 * - Chạy dưới Apache trong thư mục con → lấy thư mục của SCRIPT_NAME
 *   (vd. SCRIPT_NAME = /cases.php/public/index.php → base = /cases.php/public).
 *
 * Kết quả được cache trong biến static (chỉ tính 1 lần mỗi request).
 *
 * @return string  Không có dấu / ở cuối; '' khi chạy ở gốc domain
 */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        if (PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli') {
            $base = '';
        } else {
            // Đổi \ thành / (Windows), rồi bỏ / hoặc . ở cuối (dirname('/index.php') = '/' → '').
            $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/.');
        }
    }
    return $base;
}

/**
 * Tạo URL đầy đủ (có base path) cho một đường dẫn nội bộ.
 *
 * Ví dụ (base path = ''):            url('/bai-viet') → '/bai-viet'
 *       (base path = '/cases/public'): url('/bai-viet') → '/cases/public/bai-viet'
 *
 * URL đã là tuyệt đối thì giữ nguyên: 'https://...', '//cdn...', 'mailto:...', 'tel:...', '#neo'.
 *
 * Luôn dùng url() khi in link trong view để website chạy được cả ở thư mục con.
 *
 * @param string $path  Đường dẫn nội bộ, có hoặc không có / ở đầu
 * @return string
 */
function url(string $path = '/'): string
{
    // ([a-z]+:) bắt scheme như http:, https:, mailto:, tel:; (//) URL không scheme; (\#) neo trong trang.
    if (preg_match('#^([a-z]+:|//|\#)#i', $path)) {
        return $path;
    }
    return base_path() . '/' . ltrim($path, '/');
}

/**
 * URL cho file tĩnh (CSS/JS) trong public/, kèm "?v=<thời gian sửa file>".
 *
 * Mục đích "cache busting": trình duyệt cache file CSS/JS rất lâu; khi file thay đổi,
 * tham số v đổi theo → trình duyệt tự tải bản mới, không cần Ctrl+F5.
 *
 * Ví dụ: asset('assets/css/site.css') → '/assets/css/site.css?v=1793805568'
 *
 * @param string $path  Đường dẫn tương đối trong thư mục public/
 * @return string
 */
function asset(string $path): string
{
    $file = ROOT . '/public/' . ltrim($path, '/');
    return url($path) . '?v=' . (is_file($file) ? filemtime($file) : '1');
}

/**
 * Đường dẫn của request hiện tại (đã bỏ base path và query string), vd. '/admin/posts'.
 * Giá trị được gán trong public/index.php ($GLOBALS['current_path']).
 *
 * @return string
 */
function current_path(): string
{
    return $GLOBALS['current_path'] ?? '/';
}

/**
 * Giữ nguyên query string hiện tại, ghi đè các tham số truyền vào.
 *
 * Dùng cho phân trang và bộ lọc: chuyển trang mà không mất từ khoá tìm kiếm, bộ lọc...
 * Tham số có giá trị '' hoặc null sẽ bị loại khỏi URL (dùng để XOÁ một tham số).
 *
 * Ví dụ: đang ở /admin/posts?q=tom&status=draft
 *   query_url(['page' => 2])      → /admin/posts?q=tom&status=draft&page=2
 *   query_url(['status' => ''])   → /admin/posts?q=tom
 *
 * @param array       $params  Các tham số cần đặt/ghi đè
 * @param string|null $path    Đường dẫn đích; mặc định là trang hiện tại
 * @return string
 */
function query_url(array $params, ?string $path = null): string
{
    $query = array_merge($_GET, $params);
    $query = array_filter($query, fn ($v) => $v !== '' && $v !== null);
    // http_build_query tự mã hoá ký tự đặc biệt (dấu cách, tiếng Việt...) trong URL.
    return url($path ?? current_path()) . ($query ? '?' . http_build_query($query) : '');
}

/**
 * Chuyển hướng trình duyệt sang URL khác rồi DỪNG chương trình.
 *
 * Kiểu trả về `never` (PHP 8.1): hàm không bao giờ trả về vì gọi exit.
 * Dùng sau khi xử lý form POST (mẫu Post/Redirect/Get) để F5 không gửi lại form.
 *
 * @param string $path  Đường dẫn nội bộ (tự thêm base path qua url())
 * @param int    $code  Mã HTTP: 302 = chuyển hướng tạm thời (mặc định), 301 = vĩnh viễn
 */
function redirect(string $path, int $code = 302): never
{
    header('Location: ' . url($path), true, $code);
    exit;
}

/* ---------- Request ---------- */

/**
 * Lấy giá trị người dùng gửi lên: ưu tiên $_POST (form), sau đó $_GET (query string).
 * Chuỗi được tự động trim() bỏ khoảng trắng hai đầu.
 *
 * Ví dụ: input('q') đọc ô tìm kiếm; input('page', 1) đọc số trang, mặc định 1.
 *
 * Lưu ý: giá trị có thể là mảng (nếu form gửi name="x[]"), nên nơi gọi thường ép kiểu (string)/(int).
 *
 * @param string $key      Tên trường
 * @param mixed  $default  Giá trị mặc định nếu không có
 * @return mixed
 */
function input(string $key, mixed $default = ''): mixed
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

/**
 * Dừng request và hiển thị trang lỗi (mặc định 404 – Không tìm thấy).
 * Trang lỗi dùng view views/site/error.php trong layout site.
 *
 * Ví dụ: if (!$post) abort(404);
 *
 * @param int    $code     Mã lỗi HTTP (404, 500...)
 * @param string $message  Thông báo tuỳ chỉnh (để trống dùng câu mặc định trong view)
 */
function abort(int $code = 404, string $message = ''): never
{
    http_response_code($code);
    echo view('site/error', [
        'title'   => $code === 404 ? 'Không tìm thấy trang' : 'Đã có lỗi xảy ra',
        'code'    => $code,
        'message' => $message,
    ], 'site');
    exit;
}

/* ---------- Session: flash + CSRF ---------- */

/**
 * Lưu một "flash message" – thông báo hiển thị MỘT LẦN ở trang kế tiếp.
 *
 * Ví dụ: sau khi lưu bài → flash('success', 'Đã lưu thay đổi.') → redirect →
 * layout admin gọi flashes() để in thông báo rồi xoá đi.
 *
 * @param string $type     'success' | 'error' | 'info' (quyết định màu hộp thông báo)
 * @param string $message  Nội dung thông báo
 */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Lấy toàn bộ flash message đang chờ và XOÁ chúng khỏi session (để không hiện lại).
 * Được gọi trong views/layouts/admin.php.
 *
 * @return array  Danh sách ['type' => .., 'message' => ..]
 */
function flashes(): array
{
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $items;
}

/**
 * Lấy (hoặc tạo mới) CSRF token của phiên làm việc hiện tại.
 *
 * CSRF (Cross-Site Request Forgery) là kiểu tấn công lừa trình duyệt của admin đang
 * đăng nhập gửi một form từ trang web độc hại (vd. form xoá bài). Cách chống: mọi form
 * POST phải kèm một mã bí mật ngẫu nhiên chỉ trang của mình biết; server so khớp mã
 * trước khi xử lý. Trang độc hại không đọc được mã này nên request giả bị từ chối.
 *
 * Token: 32 byte ngẫu nhiên an toàn (random_bytes) → chuỗi hex 64 ký tự, lưu trong session.
 *
 * @return string
 */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/**
 * Trả về thẻ <input hidden> chứa CSRF token để đặt trong mọi <form method="post">.
 *
 * Ví dụ trong view: <form method="post"> <?= csrf_field() ?> ... </form>
 *
 * @return string  HTML
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Kiểm tra CSRF token của request POST. Gọi ở dòng ĐẦU của mọi controller xử lý POST.
 *
 * Token được lấy từ trường form '_csrf', hoặc header 'X-CSRF-Token' (dùng cho request
 * AJAX upload ảnh trong admin.js). Sai/thiếu token → trả lỗi 419 và dừng.
 *
 * hash_equals() so sánh trong thời gian cố định → không đoán được token qua thời gian phản hồi.
 */
function csrf_verify(): void
{
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        // 419 là mã thường dùng (Laravel) cho "phiên hết hạn / token không hợp lệ".
        http_response_code(419);
        exit('Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
    }
}

/* ---------- View ---------- */

/**
 * Render (dựng) một file giao diện trong views/ và trả về HTML dạng chuỗi.
 *
 * Cách hoạt động:
 *   1. Mỗi khoá của $data trở thành một biến trong file view (extract):
 *      view('site/post', ['post' => $p]) → trong views/site/post.php dùng được $post.
 *   2. Output của file view được "bắt" lại bằng output buffering (ob_start/ob_get_clean)
 *      thay vì in thẳng ra trình duyệt.
 *   3. Nếu có $layout: HTML vừa dựng được đưa vào biến $content rồi render tiếp file
 *      views/layouts/<layout>.php (layout chứa <html>, header, footer và in $content ở giữa).
 *      Layout cũng nhận được mọi biến trong $data (vd. $title, $active).
 *
 * Ví dụ: echo view('site/home', ['products' => $products], 'site');
 *
 * @param string      $template  Đường dẫn view không có .php, tính từ views/ (vd. 'admin/dashboard')
 * @param array       $data      Biến truyền cho view
 * @param string|null $layout    Tên layout trong views/layouts/ ('site' | 'admin'), null = không dùng layout
 * @return string  HTML
 */
function view(string $template, array $data = [], ?string $layout = null): string
{
    $__file = ROOT . '/views/' . $template . '.php';
    // Dùng closure static để view chạy trong phạm vi biến riêng, sạch sẽ:
    // view chỉ thấy các biến trong $data (không thấy biến cục bộ của hàm view() như $template, $layout).
    $content = (static function () use ($__file, $data) {
        // EXTR_SKIP: không ghi đè biến đã tồn tại (vd. $__file) nếu $data có khoá trùng tên.
        extract($data, EXTR_SKIP);
        ob_start();
        require $__file;
        return ob_get_clean();
    })();

    if ($layout === null) {
        return $content;
    }

    // Bọc nội dung vào layout: layout sẽ in biến $content.
    $data['content'] = $content;
    return view('layouts/' . $layout, $data);
}

/**
 * Render một "partial" – mảnh giao diện tái sử dụng trong views/partials/
 * (thẻ bài viết, phân trang, form liên hệ, hình con tôm...).
 *
 * Ví dụ trong view: <?= partial('post-card', ['post' => $p, 'index' => 0]) ?>
 *
 * @param string $template  Tên file trong views/partials/ (không có .php)
 * @param array  $data      Biến truyền cho partial
 * @return string  HTML
 */
function partial(string $template, array $data = []): string
{
    return view('partials/' . $template, $data);
}

/* ---------- Chuỗi & ngày ---------- */

/**
 * Chuyển chuỗi tiếng Việt thành "slug" dùng trong URL: chữ thường, không dấu, nối bằng "-".
 *
 * Ví dụ: slugify('Tôm đông lạnh IQF!') → 'tom-dong-lanh-iqf'
 *
 * Không dùng thư viện intl (Normalizer) vì image Docker php:8.3-cli không có sẵn,
 * nên tự thay từng nhóm ký tự có dấu bằng chữ cái gốc.
 *
 * @param string $text
 * @return string  Slug (có thể rỗng nếu chuỗi không có chữ/số nào)
 */
function slugify(string $text): string
{
    // Chuyển về chữ thường (mb_ để xử lý đúng chữ có dấu, vd. 'Đ' → 'đ').
    $text = mb_strtolower($text, 'UTF-8');
    // Bảng thay thế: chữ cái không dấu => tất cả biến thể có dấu của nó.
    $map = [
        'a' => 'àáạảãâầấậẩẫăằắặẳẵ',
        'e' => 'èéẹẻẽêềếệểễ',
        'i' => 'ìíịỉĩ',
        'o' => 'òóọỏõôồốộổỗơờớợởỡ',
        'u' => 'ùúụủũưừứựửữ',
        'y' => 'ỳýỵỷỹ',
        'd' => 'đ',
    ];
    foreach ($map as $ascii => $chars) {
        // [..] là tập ký tự; cờ /u bắt buộc để regex hiểu ký tự UTF-8 nhiều byte.
        $text = preg_replace('/[' . $chars . ']/u', $ascii, $text);
    }
    // Mọi chuỗi ký tự không phải a-z, 0-9 (dấu cách, dấu câu...) thay bằng một dấu "-".
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    // Bỏ "-" thừa ở đầu và cuối.
    return trim((string) $text, '-');
}

/**
 * Thời điểm hiện tại dạng chuỗi chuẩn lưu DB: 'YYYY-mm-dd HH:ii:ss' (theo múi giờ VN).
 * Định dạng này so sánh được bằng toán tử chuỗi (<=, >=) trong SQLite.
 *
 * @return string  vd. '2026-11-05 09:30:00'
 */
function now(): string
{
    return date('Y-m-d H:i:s');
}

/**
 * Định dạng ngày giờ từ DB để hiển thị.
 *
 * Ví dụ: format_date('2026-11-05 09:30:00')            → '05/11/2026'
 *        format_date('2026-11-05 09:30:00', 'd/m H:i') → '05/11 09:30'
 *
 * @param string|null $date    Chuỗi ngày giờ (null/rỗng → trả '')
 * @param string      $format  Định dạng theo hàm date() của PHP
 * @return string
 */
function format_date(?string $date, string $format = 'd/m/Y'): string
{
    return $date ? date($format, strtotime($date)) : '';
}

/**
 * Rút gọn nội dung HTML thành đoạn văn bản ngắn (dùng làm mô tả, tóm tắt).
 *
 * Các bước: bỏ thẻ HTML → giải mã thực thể (&amp; → &) → gộp khoảng trắng/xuống dòng
 * → cắt theo số ký tự (mb_ để không cắt đôi chữ có dấu) → thêm "…" nếu bị cắt.
 *
 * Ví dụ: str_excerpt('<p>Tôm   <b>tươi</b> ngon</p>', 8) → 'Tôm tươi…'
 *
 * @param string|null $html
 * @param int         $length  Số ký tự tối đa
 * @return string  Văn bản thuần (chưa escape – khi in ra view vẫn phải bọc e())
 */
function str_excerpt(?string $html, int $length = 160): string
{
    $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8');
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    return mb_strlen($text) > $length ? rtrim(mb_substr($text, 0, $length)) . '…' : $text;
}

/**
 * Tính thông số phân trang.
 *
 * Ví dụ: 23 bài, mỗi trang 9, đang ở trang 3:
 *   paginate(23, 3, 9) → ['total' => 23, 'page' => 3, 'pages' => 3, 'per' => 9, 'offset' => 18]
 * Sau đó dùng trong SQL: LIMIT {per} OFFSET {offset}
 *
 * Số trang được "kẹp" vào khoảng hợp lệ [1, pages] → người dùng gõ ?page=999 hay ?page=-5
 * vẫn ra trang hợp lệ, và offset luôn là số nguyên an toàn để nối vào SQL.
 *
 * @param int $total    Tổng số bản ghi
 * @param int $page     Trang người dùng yêu cầu
 * @param int $perPage  Số bản ghi mỗi trang
 * @return array
 */
function paginate(int $total, int $page, int $perPage): array
{
    // Ít nhất 1 trang (kể cả khi không có bản ghi nào).
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $pages);
    return [
        'total'  => $total,
        'page'   => $page,
        'pages'  => $pages,
        'per'    => $perPage,
        'offset' => ($page - 1) * $perPage,
    ];
}

/* ---------- Settings ---------- */

/**
 * Đọc một cài đặt website từ bảng settings (sửa ở Admin → Cài đặt).
 *
 * Lần gọi đầu tiên tải TẤT CẢ cài đặt vào biến static $cache (1 câu SQL),
 * các lần sau đọc từ bộ nhớ → một trang gọi setting() hàng chục lần vẫn chỉ tốn 1 truy vấn.
 *
 * Ví dụ: setting('company_phone') → '0283 999 888'
 *        setting('site_name', 'CASES') → 'CASES' nếu cài đặt đang để trống
 *
 * @param string $key      Khoá cài đặt (xem setting_fields() trong app/admin.php)
 * @param string $default  Giá trị dùng khi cài đặt không tồn tại hoặc rỗng
 * @return string
 */
function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db_all('SELECT key, value FROM settings') as $row) {
            $cache[$row['key']] = (string) $row['value'];
        }
    }
    $value = $cache[$key] ?? '';
    return $value !== '' ? $value : $default;
}

/* ---------- Upload ---------- */

/**
 * Lưu ảnh upload, trả về đường dẫn public (vd. /uploads/2026/11/ab12.jpg).
 *
 * Các lớp kiểm tra an toàn:
 *   1. Kiểm tra mã lỗi upload của PHP.
 *   2. Giới hạn dung lượng (config 'upload_max').
 *   3. Xác định kiểu file từ NỘI DUNG thật (finfo đọc "magic bytes"), không tin đuôi file
 *      hay Content-Type do trình duyệt gửi → file PHP đổi tên thành .jpg sẽ bị từ chối.
 *   4. Đặt TÊN FILE NGẪU NHIÊN + đuôi theo MIME thật → không ghi đè file khác,
 *      không chạy được file có tên độc hại, không đoán được tên file.
 *   5. Thư mục public/uploads có .htaccess cấm thực thi script (phòng hờ trên Apache).
 *
 * Ảnh được chia thư mục theo năm/tháng (uploads/2026/11/) để một thư mục không quá nhiều file.
 *
 * Được gọi bởi: admin_content_save() (ảnh đại diện) và admin_upload() (ảnh chèn trong editor).
 *
 * @param array $file  Một phần tử của $_FILES, vd. $_FILES['featured_image']
 * @return string  Đường dẫn URL tương đối của ảnh (chưa có base path; khi in dùng url())
 * @throws RuntimeException với thông báo tiếng Việt
 */
function store_upload(array $file): string
{
    // 1) Lỗi trong quá trình upload (file quá lớn theo php.ini, mất kết nối...)
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $msg = match ($file['error'] ?? null) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ảnh vượt quá dung lượng cho phép.',
            UPLOAD_ERR_PARTIAL => 'Ảnh chỉ được tải lên một phần, vui lòng thử lại.',
            default => 'Không tải được ảnh lên.',
        };
        throw new RuntimeException($msg);
    }
    // 2) Giới hạn dung lượng của ứng dụng (1048576 byte = 1MB).
    if ($file['size'] > config('upload_max')) {
        throw new RuntimeException('Ảnh tối đa ' . round(config('upload_max') / 1048576) . 'MB.');
    }

    // 3) Kiểu MIME thật của file tạm (tmp_name) – vd. 'image/png'.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = config('upload_mime');
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG, WEBP hoặc GIF.');
    }

    // Thư mục con theo năm/tháng, vd. '2026/11'.
    $sub = date('Y/m');
    $dir = config('upload_dir') . '/' . $sub;
    // Tạo thư mục nếu chưa có. Kiểm tra is_dir lần nữa sau mkdir để tránh lỗi khi
    // 2 request cùng tạo thư mục một lúc (request kia tạo trước thì mkdir trả false nhưng thư mục đã có).
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Không tạo được thư mục lưu ảnh.');
    }

    // 4) Tên ngẫu nhiên 16 ký tự hex + đuôi chuẩn theo MIME, vd. 'd3fdda215b04fdfb.png'.
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    // move_uploaded_file chỉ chấp nhận file thật sự do PHP nhận qua upload (an toàn hơn rename/copy).
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Không lưu được ảnh.');
    }
    return rtrim(config('upload_url'), '/') . '/' . $sub . '/' . $name;
}

/**
 * Kiểm tra người dùng CÓ chọn file cho trường $field hay không.
 * (Khi form có ô file nhưng người dùng không chọn, PHP vẫn tạo $_FILES với lỗi UPLOAD_ERR_NO_FILE.)
 *
 * @param string $field  Tên ô input file, vd. 'featured_image'
 * @return bool  true nếu có file được gửi lên (kể cả file lỗi – store_upload sẽ báo lỗi cụ thể)
 */
function has_upload(string $field): bool
{
    return isset($_FILES[$field]) && ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}
