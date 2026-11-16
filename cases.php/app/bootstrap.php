<?php
/**
 * ============================================================================
 *  app/bootstrap.php – KHỞI ĐỘNG ỨNG DỤNG
 * ============================================================================
 *
 * Đây là file được nạp ĐẦU TIÊN trong mọi request (và trong các script CLI như
 * database/reset.php). Nhiệm vụ:
 *   1. Định nghĩa hằng ROOT (thư mục gốc dự án).
 *   2. Đọc cấu hình từ config/config.php.
 *   3. Thiết lập môi trường PHP: múi giờ, hiển thị lỗi, mã hoá UTF-8.
 *   4. Khởi động session (đăng nhập, flash message, CSRF token đều cần session).
 *   5. Nạp các file thư viện nội bộ trong app/.
 *
 * Luồng của một request web:
 *
 *   Trình duyệt → public/index.php (front controller)
 *                    └─ require app/bootstrap.php   ← (file này)
 *                    └─ require app/site.php, app/admin.php (controller)
 *                    └─ Router::dispatch() → gọi hàm controller phù hợp
 *                          └─ controller lấy dữ liệu (db_*) → view() render HTML trong views/
 *
 * Lưu ý: `declare(strict_types=1)` bật kiểm tra kiểu nghiêm ngặt cho file này
 * (vd. truyền int vào tham số string sẽ báo lỗi thay vì tự ép kiểu).
 */
declare(strict_types=1);

// ROOT = thư mục cha của app/, tức thư mục gốc dự án (D:\Training\cases.php hoặc /app trong Docker).
// Mọi đường dẫn file trong code đều tính từ ROOT để không phụ thuộc thư mục đang chạy.
define('ROOT', dirname(__DIR__));

// Nạp mảng cấu hình và lưu vào biến toàn cục để hàm config() đọc lại.
$GLOBALS['config'] = require ROOT . '/config/config.php';

// Đặt múi giờ mặc định cho date(), strtotime()... (Asia/Ho_Chi_Minh).
date_default_timezone_set($GLOBALS['config']['timezone']);
// Báo mọi loại lỗi/cảnh báo (E_ALL) để phát hiện bug sớm.
error_reporting(E_ALL);
// Chỉ hiển thị lỗi ra trình duyệt khi debug = true; khi chạy thật nên tắt để không lộ thông tin.
ini_set('display_errors', $GLOBALS['config']['debug'] ? '1' : '0');
// Mặc định các hàm mb_* (mb_strlen, mb_substr...) làm việc với UTF-8 → xử lý đúng tiếng Việt có dấu.
mb_internal_encoding('UTF-8');

// Khởi động session – chỉ khi chạy qua web (PHP_SAPI khác 'cli') và session chưa được mở.
// Script dòng lệnh (php database/reset.php) không cần session.
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    // Đặt tên cookie session riêng (mặc định là PHPSESSID) để tránh đụng ứng dụng khác cùng domain.
    session_name('cases_sess');
    session_set_cookie_params([
        // httponly: JavaScript không đọc được cookie session → giảm rủi ro bị đánh cắp qua XSS.
        'httponly' => true,
        // samesite=Lax: trình duyệt không gửi cookie trong các request POST từ site khác
        // → thêm một lớp chống CSRF (bên cạnh CSRF token).
        'samesite' => 'Lax',
        // secure: chỉ gửi cookie qua HTTPS. Tự bật khi trang đang chạy HTTPS.
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// Nạp các thư viện nội bộ. Thứ tự quan trọng ở chỗ: helpers.php phải có trước
// vì các file sau dùng config(), e(), url()... (tuy chỉ gọi khi chạy hàm, không gọi lúc nạp).
require ROOT . '/app/helpers.php';  // hàm tiện ích chung: config, e, url, view, slugify, upload...
require ROOT . '/app/icons.php';    // hàm icon() trả về SVG icon
require ROOT . '/app/db.php';       // kết nối SQLite + hàm truy vấn db_*
require ROOT . '/app/auth.php';     // đăng nhập, đăng xuất, người dùng hiện tại
require ROOT . '/app/content.php';  // định nghĩa loại nội dung (Trang/Bài viết), chuyên mục, slug...
require ROOT . '/app/Router.php';   // class Router ánh xạ URL → hàm xử lý
