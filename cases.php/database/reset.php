<?php
/**
 * ============================================================================
 *  database/reset.php – XOÁ VÀ TẠO LẠI DATABASE VỚI DỮ LIỆU MẪU (chạy bằng dòng lệnh)
 * ============================================================================
 *
 * Xoá và tạo lại database với dữ liệu mẫu.
 *   php database/reset.php
 *
 * Chạy trong Docker:
 *   docker exec cases-cms php /app/database/reset.php
 *
 * CẢNH BÁO: toàn bộ trang, bài viết, yêu cầu báo giá, cài đặt và tài khoản hiện có
 * sẽ bị XOÁ và thay bằng dữ liệu mẫu trong database/seed.php. (Ảnh trong public/uploads
 * không bị xoá.)
 *
 * Cách làm: xoá file SQLite rồi gọi db() → db() thấy database trống nên tự chạy
 * db_install() (schema.sql + seed.php).
 */

// Chỉ cho chạy từ dòng lệnh – không ai có thể reset database bằng cách mở URL trên trình duyệt.
// (File này cũng không nằm trong public/ nên vốn không truy cập được qua web.)
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}
// Nạp cấu hình + các hàm (config(), db()...). Ở chế độ CLI, bootstrap không mở session.
require dirname(__DIR__) . '/app/bootstrap.php';

$path = config('db_path');
// Xoá file database chính cùng 2 file phụ của SQLite (-wal, -shm: chỉ có khi bật chế độ WAL).
foreach ([$path, $path . '-wal', $path . '-shm'] as $f) {
    if (is_file($f)) {
        unlink($f);
    }
}
// Kết nối lại → tạo file mới, tạo bảng và nạp dữ liệu mẫu.
db();
echo "Database đã được tạo lại: $path\n";
