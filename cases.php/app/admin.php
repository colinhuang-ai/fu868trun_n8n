<?php
/**
 * ============================================================================
 *  app/admin.php – CONTROLLER KHU VỰC QUẢN TRỊ (CMS)
 * ============================================================================
 *
 * Mọi hàm ở đây xử lý các URL bắt đầu bằng /admin (khai báo trong public/index.php).
 * Trừ trang đăng nhập, tất cả route admin đều được bọc bởi $guard → gọi require_auth()
 * trước, nên khi vào tới các hàm này thì người dùng chắc chắn đã đăng nhập.
 *
 * Nhóm chức năng:
 *   - Đăng nhập / đăng xuất:       admin_login_form, admin_login, admin_logout
 *   - Tổng quan (dashboard):       admin_dashboard
 *   - CRUD Trang & Bài viết:       admin_content_* (dùng CHUNG cho cả 2 loại, phân biệt bằng $type)
 *   - Upload ảnh trong editor:     admin_upload (AJAX, trả JSON)
 *   - Yêu cầu báo giá:             admin_contacts, admin_contact_toggle, admin_contact_delete
 *   - Cài đặt website:             setting_fields, admin_settings, admin_settings_save
 *   - Tài khoản:                   admin_profile, admin_profile_save
 *
 * Quy ước chung cho các hàm xử lý POST (lưu/xoá...):
 *   1. Gọi csrf_verify() đầu tiên (chống giả mạo request).
 *   2. Kiểm tra dữ liệu; nếu lỗi → render lại form kèm thông báo (không redirect, để giữ dữ liệu đã nhập).
 *   3. Thành công → flash() thông báo → redirect() (mẫu Post/Redirect/Get, tránh gửi lại form khi F5).
 *
 * View nằm trong views/admin/, layout views/layouts/admin.php (sidebar, topbar, flash message).
 */
declare(strict_types=1);

/* ================= Admin controllers ================= */

/**
 * Render một view admin bên trong layout admin.
 *
 * Ví dụ: admin_render('dashboard', ['title' => 'Tổng quan', ...])
 *        → render views/admin/dashboard.php trong views/layouts/admin.php
 *
 * Biến thường truyền: 'title' (tiêu đề topbar + thẻ <title>), 'active' (mục sidebar đang chọn).
 *
 * @param string $template  Đường dẫn view tính từ views/admin/ (không có .php)
 * @param array  $data      Biến cho view và layout
 */
function admin_render(string $template, array $data = []): void
{
    echo view('admin/' . $template, $data, 'admin');
}

/* ---------- Auth ---------- */

/**
 * GET /admin/login – hiển thị form đăng nhập.
 * Đã đăng nhập rồi thì chuyển thẳng vào dashboard.
 * Trang đăng nhập là một view độc lập (views/admin/login.php), không dùng layout admin.
 */
function admin_login_form(): void
{
    if (current_user()) {
        redirect('/admin');
    }
    echo view('admin/login', ['email' => '', 'error' => null]);
}

/**
 * POST /admin/login – xử lý đăng nhập.
 *
 * Thứ tự kiểm tra:
 *   1. CSRF token.
 *   2. Có đang bị khoá do sai quá 5 lần không (login_locked_seconds).
 *   3. Email + mật khẩu (attempt_login).
 *   Đúng → chuyển về trang admin người dùng định vào trước đó (nếu có) hoặc dashboard.
 *   Sai  → hiển thị lại form với thông báo lỗi, giữ email đã nhập, mã HTTP 422.
 */
function admin_login(): void
{
    csrf_verify();
    $email = (string) input('email');
    $error = null;

    if ($wait = login_locked_seconds()) {
        $error = "Đăng nhập sai nhiều lần. Vui lòng thử lại sau {$wait} giây.";
    } elseif (attempt_login($email, (string) ($_POST['password'] ?? ''))) {
        // URL mà require_auth() đã ghi nhớ khi người dùng bị đá ra trang đăng nhập.
        $to = $_SESSION['intended'] ?? '';
        unset($_SESSION['intended']);
        // Chỉ chuyển về URL nội bộ trong khu vực admin
        // Chống "open redirect": chỉ chấp nhận URL bắt đầu bằng <base>/admin, không cho
        // chuyển hướng sang domain khác (vd. //trang-lua-dao.com) dù giá trị lấy từ session.
        if (is_string($to) && preg_match('#^' . preg_quote(base_path(), '#') . '/admin(/|$|\?)#', $to)) {
            header('Location: ' . $to);
            exit;
        }
        redirect('/admin');
    } else {
        // Thông báo chung, không nói rõ sai email hay sai mật khẩu (không lộ email nào tồn tại).
        $error = 'Email hoặc mật khẩu không đúng.';
    }

    // 422 Unprocessable Entity: dữ liệu gửi lên không hợp lệ.
    http_response_code(422);
    echo view('admin/login', ['email' => $email, 'error' => $error]);
}

/**
 * POST /admin/logout – đăng xuất.
 * Dùng POST + CSRF (không dùng link GET) để trang khác không thể lừa trình duyệt đăng xuất admin.
 */
function admin_logout(): void
{
    csrf_verify();
    logout();
    redirect('/admin/login');
}

/* ---------- Dashboard ---------- */

/**
 * GET /admin – trang Tổng quan.
 * Hiển thị các con số thống kê, nút tạo nhanh, bài viết/trang sửa gần đây và yêu cầu báo giá mới nhất.
 */
function admin_dashboard(): void
{
    // Các thẻ thống kê (views/admin/dashboard.php). Ép (int) vì SQLite trả COUNT(*) dạng số/chuỗi.
    $stats = [
        'pages'           => (int) db_value('SELECT COUNT(*) FROM pages'),
        'pages_published' => (int) db_value("SELECT COUNT(*) FROM pages WHERE status = 'published'"),
        'posts'           => (int) db_value('SELECT COUNT(*) FROM posts'),
        'posts_published' => (int) db_value("SELECT COUNT(*) FROM posts WHERE status = 'published'"),
        'products'        => (int) db_value("SELECT COUNT(*) FROM posts WHERE category = 'san-pham' AND status = 'published'"),
        'contacts'        => (int) db_value('SELECT COUNT(*) FROM contacts'),
        'contacts_unread' => (int) db_value('SELECT COUNT(*) FROM contacts WHERE is_read = 0'),
    ];
    admin_render('dashboard', [
        'title'    => 'Tổng quan',
        'active'   => 'dashboard',
        'stats'    => $stats,
        // 6 bài viết / 6 trang được sửa gần nhất, 5 yêu cầu báo giá mới nhất.
        'posts'    => db_all('SELECT id, title, status, category, updated_at FROM posts ORDER BY updated_at DESC LIMIT 6'),
        'pages'    => db_all('SELECT id, title, status, updated_at FROM pages ORDER BY updated_at DESC LIMIT 6'),
        'contacts' => db_all('SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5'),
    ]);
}

/* ---------- Pages & Posts (CRUD dùng chung) ---------- */

/**
 * GET /admin/pages và GET /admin/posts – danh sách Trang / Bài viết.
 *
 * Hỗ trợ (qua query string):
 *   ?q=...         tìm theo tiêu đề hoặc slug
 *   ?status=...    lọc theo trạng thái (published | draft)
 *   ?category=...  lọc theo chuyên mục (chỉ với posts)
 *   ?page=N        phân trang (config 'per_page_admin' dòng/trang)
 *
 * Câu SQL được ghép từ các điều kiện, NHƯNG mọi giá trị người dùng nhập đều đi qua tham số
 * (:q, :status, :category). Giá trị status/category còn được kiểm tra nằm trong danh sách hợp lệ.
 *
 * @param string $type  'pages' | 'posts' (lấy từ URL; content_type() trả 404 nếu không hợp lệ)
 */
function admin_content_index(string $type): void
{
    $t = content_type($type);
    $table = $t['table'];

    $q = (string) input('q');
    $status = (string) input('status');
    $category = (string) input('category');

    // Gom từng điều kiện WHERE và tham số tương ứng.
    $where = [];
    $params = [];
    if ($q !== '') {
        // LIKE '%từ khoá%' = chứa từ khoá ở bất kỳ vị trí nào. Cùng tham số :q dùng 2 lần được.
        $where[] = '(title LIKE :q OR slug LIKE :q)';
        $params['q'] = '%' . $q . '%';
    }
    if (isset(statuses()[$status])) {
        $where[] = 'status = :status';
        $params['status'] = $status;
    }
    if ($type === 'posts' && isset(post_categories()[$category])) {
        $where[] = 'category = :category';
        $params['category'] = $category;
    }
    $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Đếm tổng (sau khi lọc) → tính phân trang → lấy đúng các dòng của trang hiện tại.
    $total = (int) db_value("SELECT COUNT(*) FROM $table $sqlWhere", $params);
    $pg = paginate($total, (int) input('page', 1), (int) config('per_page_admin'));
    // Trang: sắp theo thứ tự menu rồi tên; Bài viết: sửa gần nhất lên đầu.
    $order = $type === 'pages' ? 'menu_order, title' : 'updated_at DESC, id DESC';
    $rows = db_all("SELECT * FROM $table $sqlWhere ORDER BY $order LIMIT {$pg['per']} OFFSET {$pg['offset']}", $params);

    // Số lượng cho các tab trạng thái: '' = Tất cả, 'published', 'draft' (không phụ thuộc bộ lọc).
    $counts = ['' => (int) db_value("SELECT COUNT(*) FROM $table")];
    foreach (array_keys(statuses()) as $s) {
        $counts[$s] = (int) db_value("SELECT COUNT(*) FROM $table WHERE status = ?", [$s]);
    }

    admin_render('content/index', [
        'title'      => $t['plural'],
        'active'     => $type,
        'type'       => $type,
        't'          => $t,
        'rows'       => $rows,
        'pagination' => $pg,
        'counts'     => $counts,
        // compact('q', 'status', 'category') = ['q' => $q, 'status' => $status, 'category' => $category]
        'filters'    => compact('q', 'status', 'category'),
    ]);
}

/**
 * Dữ liệu mặc định cho một Trang/Bài viết MỚI (form tạo mới hiển thị các giá trị này).
 * Phần chung + phần riêng của từng loại (toán tử + gộp mảng, không ghi đè khoá đã có).
 *
 * @param string $type  'pages' | 'posts'
 * @return array
 */
function admin_content_blank(string $type): array
{
    $base = [
        'id' => null, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'featured_image' => null,
        'status' => 'draft', 'meta_title' => '', 'meta_description' => '', 'published_at' => null,
        'created_at' => null, 'updated_at' => null,
    ];
    return $type === 'pages'
        // Riêng Trang: mẫu giao diện, có hiện trên menu không, thứ tự menu.
        ? $base + ['template' => 'default', 'show_in_menu' => 0, 'menu_order' => 0]
        // Riêng Bài viết: chuyên mục, nhãn trên ảnh, thứ tự (trang chủ), nổi bật.
        : $base + ['category' => 'tin-tuc', 'label' => '', 'sort_order' => 0, 'is_featured' => 0];
}

/**
 * Tìm một Trang/Bài viết theo id; không có thì trả 404.
 *
 * @param string $type  'pages' | 'posts'
 * @param string $id    id lấy từ URL (dạng chuỗi) → ép (int) khi truy vấn
 * @return array  Dòng dữ liệu
 */
function admin_content_find(string $type, string $id): array
{
    $t = content_type($type);
    $row = db_one("SELECT * FROM {$t['table']} WHERE id = ?", [(int) $id]);
    if (!$row) {
        abort(404);
    }
    return $row;
}

/**
 * Hiển thị form tạo mới / chỉnh sửa (views/admin/content/form.php).
 *
 * Dùng cho 3 tình huống:
 *   - Tạo mới:        $id = null, $item = null → dùng dữ liệu trống.
 *   - Sửa:            $id có giá trị           → lấy dữ liệu từ DB.
 *   - Lưu bị lỗi:     truyền $errors + $item (dữ liệu người dùng vừa nhập) → hiện lại form kèm lỗi.
 *
 * 'use_editor' => true báo layout admin nạp thư viện trình soạn thảo Quill (CSS + JS).
 *
 * @param string      $type    'pages' | 'posts'
 * @param string|null $id      id cần sửa
 * @param array       $errors  ['tên_trường' => 'thông báo lỗi']
 * @param array|null  $item    Dữ liệu điền sẵn vào form
 */
function admin_content_form(string $type, ?string $id = null, array $errors = [], ?array $item = null): void
{
    $t = content_type($type);
    // ??= chỉ gán khi $item đang null (không có dữ liệu truyền vào).
    $item ??= $id ? admin_content_find($type, $id) : admin_content_blank($type);
    $isNew = empty($item['id']);
    admin_render('content/form', [
        'title'  => $isNew ? $t['new'] : 'Sửa ' . $t['singular'],
        'active' => $type,
        'type'   => $type,
        't'      => $t,
        'item'   => $item,
        'errors' => $errors,
        'is_new' => $isNew,
        'use_editor' => true,
    ]);
}

/**
 * GET /admin/{type}/create – form tạo mới.
 * @param string $type
 */
function admin_content_create(string $type): void
{
    admin_content_form($type);
}

/**
 * GET /admin/{type}/{id}/edit – form chỉnh sửa.
 * @param string $type
 * @param string $id
 */
function admin_content_edit(string $type, string $id): void
{
    admin_content_form($type, $id);
}

/**
 * Lưu Trang/Bài viết (dùng chung cho tạo mới và cập nhật).
 * Được gọi bởi admin_content_store() (tạo mới) và admin_content_update() (sửa).
 *
 * Các bước:
 *   1. Kiểm tra CSRF; nếu sửa thì tìm bản ghi cũ (không có → 404).
 *   2. Đọc & chuẩn hoá dữ liệu form (cắt độ dài, slug, trạng thái, ngày đăng, lọc HTML nội dung).
 *   3. Đọc các trường riêng của Trang hoặc Bài viết; giá trị chọn từ danh sách đều được kiểm tra hợp lệ.
 *   4. Kiểm tra lỗi (thiếu tiêu đề, slug rỗng, slug trùng route hệ thống).
 *   5. Xử lý ảnh đại diện (giữ ảnh cũ / gỡ ảnh / upload ảnh mới).
 *   6. Có lỗi → hiển thị lại form. Không lỗi → đảm bảo slug duy nhất → INSERT hoặc UPDATE.
 *   7. Flash thông báo rồi chuyển hướng: "Lưu" → về trang sửa; "Lưu & đóng" → về danh sách.
 *
 * @param string      $type  'pages' | 'posts'
 * @param string|null $id    null = tạo mới; có giá trị = cập nhật bản ghi đó
 */
function admin_content_save(string $type, ?string $id = null): void
{
    csrf_verify();
    $t = content_type($type);
    $table = $t['table'];
    $existing = $id ? admin_content_find($type, $id) : null;
    $errors = [];

    // Tiêu đề tối đa 200 ký tự.
    $title = mb_substr((string) input('title'), 0, 200);
    // Slug: người dùng tự nhập thì chuẩn hoá lại; để trống thì tạo từ tiêu đề.
    $slug = slugify((string) input('slug') ?: $title);
    // Trạng thái phải là giá trị hợp lệ, nếu không mặc định là bản nháp.
    $status = isset(statuses()[input('status')]) ? (string) input('status') : 'draft';

    // Ngày đăng (ô datetime-local gửi dạng '2026-11-05T08:00').
    $publishedAt = null;
    $rawDate = (string) input('published_at');
    if ($rawDate !== '') {
        // Đổi sang định dạng chuẩn lưu DB; strtotime trả false nếu chuỗi không phải ngày hợp lệ.
        $ts = strtotime($rawDate);
        $ts === false ? $errors['published_at'] = 'Ngày đăng không hợp lệ.' : $publishedAt = date('Y-m-d H:i:s', $ts);
    } elseif ($status === 'published') {
        // Không nhập ngày mà chọn "Đã xuất bản": giữ ngày đăng cũ (nếu đã từng xuất bản), không thì lấy bây giờ.
        $publishedAt = $existing['published_at'] ?? now();
    }

    // Các cột chung của cả pages và posts. Tên khoá = tên cột trong DB (db_insert/db_update dùng trực tiếp).
    $data = [
        'title'            => $title,
        'slug'             => $slug,
        'excerpt'          => mb_substr((string) input('excerpt'), 0, 500),
        // Nội dung HTML từ trình soạn thảo: KHÔNG trim/escape, chỉ lọc thẻ nguy hiểm bằng clean_html().
        'content'          => clean_html((string) ($_POST['content'] ?? '')),
        'status'           => $status,
        'meta_title'       => mb_substr((string) input('meta_title'), 0, 200),
        'meta_description' => mb_substr((string) input('meta_description'), 0, 300),
        'published_at'     => $publishedAt,
    ];

    if ($type === 'pages') {
        $tpl = (string) input('template');
        $data += [
            'template'     => isset(page_templates()[$tpl]) ? $tpl : 'default',
            // Checkbox: chỉ được gửi lên khi được tích → có giá trị = 1, không có = 0.
            'show_in_menu' => input('show_in_menu') ? 1 : 0,
            'menu_order'   => (int) input('menu_order', 0),
        ];
    } else {
        $cat = (string) input('category');
        $data += [
            'category'    => isset(post_categories()[$cat]) ? $cat : 'tin-tuc',
            'label'       => mb_substr((string) input('label'), 0, 40),
            'sort_order'  => (int) input('sort_order', 0),
            'is_featured' => input('is_featured') ? 1 : 0,
        ];
    }

    // ---- Kiểm tra lỗi ----
    if ($title === '') {
        $errors['title'] = 'Vui lòng nhập tiêu đề.';
    }
    if ($slug === '') {
        // Xảy ra khi tiêu đề/slug chỉ toàn ký tự đặc biệt (slugify trả rỗng).
        $errors['slug'] = 'Đường dẫn không hợp lệ.';
    } elseif ($type === 'pages' && in_array($slug, reserved_page_slugs(), true)) {
        // Trang có URL /{slug} → không được trùng các đường dẫn hệ thống như /admin, /bai-viet.
        $errors['slug'] = 'Đường dẫn "' . $slug . '" được hệ thống dùng, hãy chọn đường dẫn khác.';
    }

    // Ảnh đại diện
    // Mặc định giữ nguyên ảnh cũ (khi sửa) hoặc không có ảnh (khi tạo mới).
    $data['featured_image'] = $existing['featured_image'] ?? null;
    // Tích "Gỡ ảnh hiện tại" → bỏ ảnh (file ảnh cũ vẫn còn trên ổ đĩa).
    if (input('remove_image')) {
        $data['featured_image'] = null;
    }
    // Có chọn file mới → upload (kiểm tra dung lượng, MIME...). Lỗi upload được hiện dưới ô ảnh.
    if (has_upload('featured_image')) {
        try {
            $data['featured_image'] = store_upload($_FILES['featured_image']);
        } catch (RuntimeException $e) {
            $errors['featured_image'] = $e->getMessage();
        }
    }

    if ($errors) {
        // Render lại form NGAY trong request này (không redirect) với dữ liệu người dùng vừa nhập
        // gộp lên dữ liệu cũ → người dùng không mất những gì đã gõ. Flash được hiện luôn ở lần render này.
        flash('error', 'Chưa lưu được, vui lòng kiểm tra các trường được đánh dấu.');
        admin_content_form($type, $id, $errors, array_merge($existing ?? admin_content_blank($type), $data));
        return;
    }

    // Slug trùng bản ghi khác → tự thêm hậu tố -2, -3... (bỏ qua chính bản ghi đang sửa).
    $original = $data['slug'];
    $data['slug'] = unique_slug($table, $data['slug'], $existing ? (int) $existing['id'] : null);
    $data['updated_at'] = now();

    if ($existing) {
        db_update($table, (int) $existing['id'], $data);
        $newId = (int) $existing['id'];
        flash('success', 'Đã lưu thay đổi.');
    } else {
        // Chỉ khi tạo mới mới ghi thời điểm tạo và người tạo.
        $data['created_at'] = now();
        $data['author_id'] = current_user()['id'];
        $newId = db_insert($table, $data);
        flash('success', 'Đã tạo ' . $t['singular'] . ' mới.');
    }
    // Báo cho người dùng biết slug đã bị đổi so với mong muốn.
    if ($original !== $data['slug']) {
        flash('info', 'Đường dẫn đã tồn tại nên được đổi thành "' . $data['slug'] . '".');
    }

    // Nút "Lưu & đóng" gửi _after=close → về danh sách; nút "Lưu" → ở lại trang sửa.
    redirect(input('_after') === 'close' ? "/admin/$type" : "/admin/$type/$newId/edit");
}

/**
 * POST /admin/{type} – lưu bản ghi MỚI.
 * @param string $type
 */
function admin_content_store(string $type): void
{
    admin_content_save($type);
}

/**
 * POST /admin/{type}/{id} – cập nhật bản ghi đã có.
 * @param string $type
 * @param string $id
 */
function admin_content_update(string $type, string $id): void
{
    admin_content_save($type, $id);
}

/**
 * POST /admin/{type}/{id}/delete – xoá vĩnh viễn một Trang/Bài viết.
 * (Trình duyệt hỏi xác nhận trước nhờ thuộc tính data-confirm trên form – xem admin.js.)
 *
 * @param string $type
 * @param string $id
 */
function admin_content_delete(string $type, string $id): void
{
    csrf_verify();
    $t = content_type($type);
    $row = admin_content_find($type, $id);
    db_run("DELETE FROM {$t['table']} WHERE id = ?", [(int) $row['id']]);
    flash('success', 'Đã xoá "' . $row['title'] . '".');
    redirect("/admin/$type");
}

/**
 * POST /admin/{type}/{id}/duplicate – nhân bản một Trang/Bài viết thành bản nháp mới.
 *
 * Bản sao: tiêu đề thêm " (bản sao)", slug thêm "-ban-sao" (và -2, -3... nếu vẫn trùng),
 * trạng thái nháp, chưa có ngày đăng, người tạo là admin hiện tại.
 * Xong chuyển tới trang sửa của bản sao.
 *
 * @param string $type
 * @param string $id
 */
function admin_content_duplicate(string $type, string $id): void
{
    csrf_verify();
    $t = content_type($type);
    $row = admin_content_find($type, $id);
    // Bỏ id để DB tự cấp id mới khi INSERT.
    unset($row['id']);
    $row['title'] .= ' (bản sao)';
    $row['slug'] = unique_slug($t['table'], $row['slug'] . '-ban-sao');
    $row['status'] = 'draft';
    $row['published_at'] = null;
    $row['created_at'] = $row['updated_at'] = now();
    $row['author_id'] = current_user()['id'];
    $newId = db_insert($t['table'], $row);
    flash('success', 'Đã tạo bản sao (bản nháp).');
    redirect("/admin/$type/$newId/edit");
}

/* ---------- Upload ảnh từ editor (AJAX) ---------- */

/**
 * POST /admin/upload – nhận ảnh khi người dùng bấm nút "chèn ảnh" trong trình soạn thảo Quill.
 *
 * Được gọi bằng fetch() trong public/assets/js/admin.js; CSRF token gửi qua header X-CSRF-Token.
 * Trả về JSON:
 *   Thành công: {"url": "/uploads/2026/11/abc.png"}   → JS chèn <img src="..."> vào nội dung
 *   Lỗi (422):  {"error": "Chỉ chấp nhận ảnh ..."}     → JS hiện alert
 */
function admin_upload(): void
{
    csrf_verify();
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!has_upload('file')) {
            throw new RuntimeException('Chưa chọn ảnh.');
        }
        $path = store_upload($_FILES['file']);
        // Trả URL đầy đủ (có base path) để ảnh hiển thị đúng ngay trong editor.
        echo json_encode(['url' => url($path)]);
    } catch (RuntimeException $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/* ---------- Yêu cầu báo giá ---------- */

/**
 * GET /admin/contacts – danh sách yêu cầu báo giá gửi từ website.
 * ?filter=unread → chỉ hiện yêu cầu chưa xử lý. Có phân trang.
 */
function admin_contacts(): void
{
    $filter = (string) input('filter');
    // Điều kiện WHERE là chuỗi cố định do code chọn (không chứa input) → an toàn.
    $where = $filter === 'unread' ? 'WHERE is_read = 0' : '';
    $total = (int) db_value("SELECT COUNT(*) FROM contacts $where");
    $pg = paginate($total, (int) input('page', 1), (int) config('per_page_admin'));
    admin_render('contacts', [
        'title'      => 'Yêu cầu báo giá',
        'active'     => 'contacts',
        'rows'       => db_all("SELECT * FROM contacts $where ORDER BY created_at DESC, id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}"),
        'pagination' => $pg,
        'filter'     => $filter,
        // Số chưa đọc cho tab "Chưa đọc".
        'unread'     => (int) db_value('SELECT COUNT(*) FROM contacts WHERE is_read = 0'),
    ]);
}

/**
 * POST /admin/contacts/{id}/toggle – đảo trạng thái đã xử lý / chưa đọc của một yêu cầu.
 * "is_read = 1 - is_read": 0 → 1, 1 → 0. Xong quay lại danh sách, giữ bộ lọc đang chọn.
 *
 * @param string $id
 */
function admin_contact_toggle(string $id): void
{
    csrf_verify();
    db_run('UPDATE contacts SET is_read = 1 - is_read WHERE id = ?', [(int) $id]);
    // urlencode: mã hoá giá trị trước khi ghép vào URL.
    redirect('/admin/contacts' . (input('filter') ? '?filter=' . urlencode((string) input('filter')) : ''));
}

/**
 * POST /admin/contacts/{id}/delete – xoá một yêu cầu báo giá.
 * @param string $id
 */
function admin_contact_delete(string $id): void
{
    csrf_verify();
    db_run('DELETE FROM contacts WHERE id = ?', [(int) $id]);
    flash('success', 'Đã xoá yêu cầu.');
    redirect('/admin/contacts');
}

/* ---------- Cài đặt ---------- */

/**
 * Danh sách các ô trong trang Cài đặt, chia theo nhóm.
 *
 * Cấu trúc: 'Tên nhóm' => [ 'khoá_setting' => ['Nhãn hiển thị', 'text' | 'textarea'], ... ]
 *   - khoá_setting: tên lưu trong bảng settings và dùng với hàm setting('khoá') ở view.
 *   - 'text' = ô một dòng, 'textarea' = ô nhiều dòng.
 *
 * Đây cũng là "danh sách trắng": admin_settings_save() CHỈ lưu các khoá có trong đây,
 * nên người dùng không thể chèn khoá lạ vào bảng settings.
 *
 * Muốn thêm cài đặt mới: thêm một dòng vào nhóm phù hợp, rồi dùng setting('khoá_mới') trong view.
 *
 * @return array
 */
function setting_fields(): array
{
    return [
        'Thông tin chung' => [
            'site_name'        => ['Tên website', 'text'],
            'site_tagline'     => ['Khẩu hiệu (hiển thị ở footer)', 'textarea'],
            'meta_description' => ['Mô tả SEO mặc định', 'textarea'],
            'footer_note'      => ['Dòng bản quyền', 'text'],
        ],
        'Thông tin liên hệ' => [
            'company_address' => ['Địa chỉ', 'text'],
            'company_phone'   => ['Số điện thoại', 'text'],
            'company_email'   => ['Email', 'text'],
        ],
        'Banner trang chủ' => [
            'hero_eyebrow'     => ['Dòng giới thiệu nhỏ', 'text'],
            'hero_title'       => ['Tiêu đề chính (xuống dòng = ngắt dòng)', 'textarea'],
            'hero_highlight'   => ['Phần nhấn mạnh cuối tiêu đề', 'text'],
            'hero_description' => ['Mô tả', 'textarea'],
        ],
    ];
}

/**
 * GET /admin/settings – hiển thị form Cài đặt website với giá trị hiện tại.
 */
function admin_settings(): void
{
    // Chuyển các dòng (key, value) thành mảng ['site_name' => 'CASES', ...] cho view tra cứu.
    $values = [];
    foreach (db_all('SELECT key, value FROM settings') as $r) {
        $values[$r['key']] = $r['value'];
    }
    admin_render('settings', [
        'title'  => 'Cài đặt website',
        'active' => 'settings',
        'groups' => setting_fields(),
        'values' => $values,
    ]);
}

/**
 * POST /admin/settings – lưu toàn bộ cài đặt.
 *
 * Dùng câu "UPSERT" của SQLite: INSERT ... ON CONFLICT(key) DO UPDATE
 *   → khoá chưa có thì thêm mới, đã có thì cập nhật giá trị (excluded.value = giá trị vừa định chèn).
 * Câu lệnh được prepare MỘT lần rồi execute nhiều lần với từng cặp (khoá, giá trị) → nhanh hơn.
 * Mỗi giá trị tối đa 1000 ký tự.
 */
function admin_settings_save(): void
{
    csrf_verify();
    $stmt = db()->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    foreach (setting_fields() as $fields) {
        foreach (array_keys($fields) as $key) {
            $stmt->execute([$key, mb_substr((string) input($key), 0, 1000)]);
        }
    }
    flash('success', 'Đã lưu cài đặt.');
    redirect('/admin/settings');
}

/* ---------- Tài khoản ---------- */

/**
 * GET /admin/profile – form sửa tài khoản (tên, email, đổi mật khẩu).
 * Cũng được admin_profile_save() gọi lại để hiện lỗi.
 *
 * @param array $errors  ['tên_trường' => 'thông báo lỗi']
 */
function admin_profile(array $errors = []): void
{
    admin_render('profile', [
        'title'  => 'Tài khoản',
        'active' => 'profile',
        'user'   => current_user(),
        'errors' => $errors,
    ]);
}

/**
 * POST /admin/profile – lưu thông tin tài khoản.
 *
 * Quy tắc:
 *   - Tên bắt buộc; email phải hợp lệ và không trùng tài khoản khác.
 *   - Khi ĐỔI MẬT KHẨU hoặc ĐỔI EMAIL (thông tin đăng nhập) phải nhập đúng mật khẩu hiện tại
 *     → người khác ngồi vào máy đang đăng nhập cũng không chiếm được tài khoản.
 *   - Mật khẩu mới tối thiểu 8 ký tự và phải nhập lại khớp.
 *   - Mật khẩu được băm bằng password_hash trước khi lưu.
 */
function admin_profile_save(): void
{
    csrf_verify();
    // Lấy đủ thông tin (gồm password_hash) của người đang đăng nhập.
    $user = db_one('SELECT * FROM users WHERE id = ?', [current_user()['id']]);
    $name = mb_substr((string) input('name'), 0, 100);
    $email = mb_strtolower((string) input('email'));
    // Mật khẩu đọc thẳng từ $_POST, KHÔNG qua input() vì input() trim mất khoảng trắng – mật khẩu phải giữ nguyên.
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['new_password_confirmation'] ?? '');
    $errors = [];

    if ($name === '') {
        $errors['name'] = 'Vui lòng nhập tên.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    } elseif (db_value('SELECT 1 FROM users WHERE email = ? AND id != ?', [$email, $user['id']])) {
        $errors['email'] = 'Email đã được dùng.';
    }
    // Đổi mật khẩu hoặc email → bắt buộc xác minh mật khẩu hiện tại.
    if ($new !== '' || $email !== $user['email']) {
        if (!password_verify($current, $user['password_hash'])) {
            $errors['current_password'] = 'Mật khẩu hiện tại không đúng.';
        }
    }
    if ($new !== '') {
        if (mb_strlen($new) < 8) {
            $errors['new_password'] = 'Mật khẩu mới tối thiểu 8 ký tự.';
        } elseif ($new !== $confirm) {
            $errors['new_password_confirmation'] = 'Xác nhận mật khẩu không khớp.';
        }
    }

    if ($errors) {
        flash('error', 'Chưa lưu được, vui lòng kiểm tra lại.');
        admin_profile($errors);
        return;
    }

    $data = ['name' => $name, 'email' => $email];
    // Chỉ cập nhật mật khẩu khi người dùng có nhập mật khẩu mới.
    if ($new !== '') {
        $data['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
    }
    db_update('users', (int) $user['id'], $data);
    flash('success', 'Đã cập nhật tài khoản.');
    redirect('/admin/profile');
}
