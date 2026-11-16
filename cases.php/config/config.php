<?php
/**
 * ============================================================================
 *  config/config.php – CẤU HÌNH ỨNG DỤNG CASES CMS
 * ============================================================================
 *
 * File này chỉ `return` một mảng cấu hình. Nó được nạp DUY NHẤT một lần trong
 * app/bootstrap.php:
 *
 *     $GLOBALS['config'] = require ROOT . '/config/config.php';
 *
 * Sau đó mọi nơi trong code đọc cấu hình qua hàm helper `config('key')`
 * (định nghĩa trong app/helpers.php), ví dụ: config('db_path').
 *
 * Một số giá trị có thể ghi đè bằng BIẾN MÔI TRƯỜNG (environment variable)
 * để không phải sửa code khi triển khai lên server khác:
 *   - APP_DEBUG = 0  → tắt hiển thị lỗi PHP ra trình duyệt (nên tắt khi chạy thật)
 *   - DB_PATH        → đường dẫn tới file SQLite khác
 *
 * Lưu ý: file này KHÔNG có `declare(strict_types=1)` vì nó chỉ trả về dữ liệu.
 */
return [
    // Tên ứng dụng (dùng làm giá trị mặc định; tên hiển thị thật lấy từ bảng settings)
    'app_name'    => 'CASES',

    // Bật/tắt chế độ debug.
    // getenv('APP_DEBUG') trả về false nếu biến môi trường không tồn tại → dùng '1' (bật).
    // filter_var(..., FILTER_VALIDATE_BOOLEAN) chuyển chuỗi '1'/'true'/'on' → true, '0'/'false'/'off' → false.
    'debug'       => filter_var(getenv('APP_DEBUG') ?: '1', FILTER_VALIDATE_BOOLEAN),

    // Múi giờ Việt Nam – ảnh hưởng tới date(), now(), ngày đăng bài, hẹn giờ xuất bản...
    'timezone'    => 'Asia/Ho_Chi_Minh',

    // SQLite: file DB tự tạo + seed dữ liệu mẫu ở lần chạy đầu tiên
    // dirname(__DIR__) = thư mục gốc dự án (cha của thư mục config/).
    // File mặc định: <gốc dự án>/storage/database.sqlite (thư mục storage/ bị chặn truy cập web).
    'db_path'     => getenv('DB_PATH') ?: dirname(__DIR__) . '/storage/database.sqlite',

    // Upload ảnh
    // upload_dir: thư mục vật lý trên ổ đĩa nơi lưu file ảnh (nằm trong public/ để web truy cập được).
    'upload_dir'  => dirname(__DIR__) . '/public/uploads',
    // upload_url: đường dẫn URL tương ứng với upload_dir (vd. /uploads/2026/11/abc.jpg).
    'upload_url'  => '/uploads',
    // Dung lượng tối đa cho mỗi ảnh (tính bằng byte).
    'upload_max'  => 5 * 1024 * 1024, // 5MB
    // Danh sách kiểu MIME được phép → phần mở rộng file sẽ lưu.
    // MIME được xác định từ NỘI DUNG file (finfo), không tin vào tên file người dùng gửi lên.
    'upload_mime' => [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ],

    // Số dòng mỗi trang trong danh sách ở trang quản trị (Trang, Bài viết, Yêu cầu báo giá).
    'per_page_admin' => 15,
    // Số bài viết mỗi trang ở trang danh sách công khai /bai-viet.
    'per_page_site'  => 9,
];
