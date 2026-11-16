<?php
/**
 * ============================================================================
 *  public/router.php – "ROUTER SCRIPT" CHO PHP BUILT-IN SERVER
 * ============================================================================
 *
 * Router cho PHP built-in server:
 *   php -S localhost:8000 -t public public/router.php
 *
 * PHP built-in server (lệnh php -S, dùng trong Docker và khi dev) không đọc file
 * .htaccess như Apache. File này đóng vai trò thay thế quy tắc rewrite:
 *   - Request tới file tĩnh có thật (CSS, JS, ảnh trong uploads/...) → để server tự trả file.
 *   - Mọi request khác (/, /bai-viet/abc, /admin/...) → chuyển vào public/index.php.
 *
 * Khi chạy bằng Apache thì KHÔNG dùng file này; public/.htaccess làm việc tương tự.
 */

// Lấy phần đường dẫn của URL (bỏ query string), vd. '/assets/css/site.css'.
$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
// Đường dẫn file tương ứng trên ổ đĩa (__DIR__ = thư mục public/).
$file = __DIR__ . $path;

// Phục vụ file tĩnh có sẵn (trừ file .php)
// "return false" là quy ước của built-in server: "tôi không xử lý, hãy tự trả file này".
// Loại trừ file .php/.phtml/.phar để không ai gọi thẳng /index.php hay chạy file PHP lạ trong uploads/.
if ($path !== '/' && is_file($file) && !preg_match('/\.(php|phtml|phar)$/i', $file)) {
    return false;
}

// Còn lại: giao cho front controller xử lý định tuyến.
require __DIR__ . '/index.php';
