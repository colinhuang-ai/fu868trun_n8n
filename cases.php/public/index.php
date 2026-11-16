<?php
/**
 * ============================================================================
 *  public/index.php – FRONT CONTROLLER (CỬA VÀO DUY NHẤT CỦA WEBSITE)
 * ============================================================================
 *
 * Mọi request tới website (trừ file tĩnh CSS/JS/ảnh) đều đi qua file này, nhờ:
 *   - Apache:            public/.htaccess rewrite mọi URL về index.php
 *   - PHP built-in server: public/router.php require file này
 *
 * Luồng xử lý một request:
 *   1. Nạp app/bootstrap.php (cấu hình, session, thư viện) và các file controller.
 *   2. Chuẩn hoá đường dẫn URL (bỏ base path, query string, '/index.php').
 *   3. Khai báo bảng định tuyến: URL + phương thức → hàm controller.
 *   4. Router::dispatch() tìm route khớp và gọi hàm tương ứng;
 *      hàm controller lấy dữ liệu rồi echo HTML từ view (hoặc redirect).
 *
 * Chỉ thư mục public/ được web truy cập; app/, views/, database/, storage/ nằm ngoài
 * nên người ngoài không tải trực tiếp được mã nguồn hay file database.
 */
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/site.php';   // controller website công khai (site_*)
require ROOT . '/app/admin.php';  // controller trang quản trị (admin_*)

// Chuẩn hoá đường dẫn (bỏ base path khi chạy trong thư mục con)
// Vd. REQUEST_URI = '/cases.php/public/bai-viet/tom-su?x=1'
//   → parse_url lấy phần path '/cases.php/public/bai-viet/tom-su'
//   → rawurldecode giải mã %20, %C3%B4... thành ký tự thật
$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$base = base_path();
//   → bỏ base path '/cases.php/public' → '/bai-viet/tom-su'
if ($base !== '' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}
// Luôn có '/' ở đầu và không có '/' ở cuối: '/bai-viet/' → '/bai-viet', '' → '/'.
$path = '/' . trim($path, '/');
// Hỗ trợ URL dạng '/index.php/bai-viet' (khi server không bật rewrite): bỏ '/index.php' (10 ký tự).
if (str_starts_with($path, '/index.php')) {
    $path = '/' . ltrim(substr($path, 10), '/');
}
// Lưu lại để các helper (current_path, query_url) dùng khi tạo link phân trang/bộ lọc.
$GLOBALS['current_path'] = $path;

$router = new Router();

/*
 * LƯU Ý THỨ TỰ: Router thử các route theo đúng thứ tự khai báo bên dưới và dừng ở
 * route ĐẦU TIÊN khớp. Vì vậy route cụ thể (/admin/login, /admin/settings...) phải đứng
 * TRƯỚC route có tham số tổng quát (/admin/{type}), và /{slug} phải đứng CUỐI CÙNG.
 * Tên trong {} phải trùng tên tham số của hàm xử lý (router truyền theo tên).
 */

/* ---------- Frontend ---------- */
$router->get('/', 'site_home');                          // Trang chủ
$router->post('/lien-he', 'site_contact_submit');        // Gửi form "Nhận báo giá" (GET /lien-he vẫn là trang tĩnh "Liên hệ" ở route cuối)
$router->get('/bai-viet', 'site_posts_index');           // Danh sách bài viết (?cat=..., ?page=...)
$router->get('/bai-viet/{slug}', 'site_post_show');      // Chi tiết bài viết → site_post_show(slug: ...)

/* ---------- Admin ---------- */
/*
 * $guard: "bọc" một hàm controller để BẮT BUỘC ĐĂNG NHẬP trước khi chạy.
 *
 *   $guard('admin_dashboard')
 *     → trả về một closure mới: function (...$args) { require_auth(); admin_dashboard(...$args); }
 *
 * require_auth() chuyển hướng về /admin/login (và exit) nếu chưa đăng nhập, nên hàm gốc
 * chỉ chạy khi đã đăng nhập. ...$args chuyển tiếp nguyên các tham số URL (type, id...)
 * kèm tên của chúng (named arguments) xuống hàm gốc.
 *
 * Ba route đăng nhập/đăng xuất KHÔNG bọc $guard (chưa đăng nhập vẫn phải vào được trang đăng nhập).
 */
$guard = fn (callable $handler) => function (...$args) use ($handler) {
    require_auth();
    $handler(...$args);
};

$router->get('/admin/login', 'admin_login_form');        // Form đăng nhập
$router->post('/admin/login', 'admin_login');            // Xử lý đăng nhập
$router->post('/admin/logout', 'admin_logout');          // Đăng xuất (POST + CSRF)

$router->get('/admin', $guard('admin_dashboard'));                              // Tổng quan
$router->post('/admin/upload', $guard('admin_upload'));                         // Upload ảnh từ trình soạn thảo (AJAX, trả JSON)
$router->get('/admin/settings', $guard('admin_settings'));                      // Form cài đặt
$router->post('/admin/settings', $guard('admin_settings_save'));                // Lưu cài đặt
$router->get('/admin/profile', $guard('admin_profile'));                        // Form tài khoản
$router->post('/admin/profile', $guard('admin_profile_save'));                  // Lưu tài khoản
$router->get('/admin/contacts', $guard('admin_contacts'));                      // Danh sách yêu cầu báo giá
$router->post('/admin/contacts/{id}/toggle', $guard('admin_contact_toggle'));   // Đánh dấu đã xử lý / chưa đọc
$router->post('/admin/contacts/{id}/delete', $guard('admin_contact_delete'));   // Xoá yêu cầu

// CRUD Trang & Bài viết: /admin/pages, /admin/posts
// {type} là 'pages' hoặc 'posts'; giá trị khác bị content_type() trả 404.
// Các route này đặt SAU settings/profile/contacts để '/admin/settings' không bị hiểu là {type} = 'settings'.
$router->get('/admin/{type}', $guard('admin_content_index'));                   // Danh sách
$router->get('/admin/{type}/create', $guard('admin_content_create'));           // Form tạo mới
$router->post('/admin/{type}', $guard('admin_content_store'));                  // Lưu bản ghi mới
$router->get('/admin/{type}/{id}/edit', $guard('admin_content_edit'));          // Form sửa
$router->post('/admin/{type}/{id}', $guard('admin_content_update'));            // Lưu thay đổi
$router->post('/admin/{type}/{id}/delete', $guard('admin_content_delete'));     // Xoá
$router->post('/admin/{type}/{id}/duplicate', $guard('admin_content_duplicate')); // Nhân bản thành bản nháp

/* ---------- Trang tĩnh (đặt cuối cùng) ---------- */
// '/{slug}' khớp MỌI đường dẫn một cấp (/gioi-thieu, /lien-he, /abc...), nên phải đặt cuối:
// nếu đặt trước, nó sẽ "nuốt" mất các URL như /bai-viet hay /admin.
// Slug không có trang tương ứng → site_page_show() trả 404.
$router->get('/{slug}', 'site_page_show');

// Chạy: tìm route khớp với phương thức + đường dẫn của request hiện tại và gọi hàm xử lý.
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
