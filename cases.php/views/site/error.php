<?php
/**
 * VIEW: TRANG LỖI (404 Không tìm thấy, hoặc lỗi khác)
 * ------------------------------------------------------------------
 * Được gọi bởi hàm abort($code, $message) trong app/helpers.php, layout 'site'.
 * abort() được dùng khi: không route nào khớp (Router), không tìm thấy bài viết/trang theo slug,
 * hoặc khách xem bản nháp (bản nháp chỉ admin đăng nhập mới xem được).
 *
 * Biến truyền vào:
 * @var int    $code     Mã lỗi HTTP (404, 500...) – hiện số to ở trên
 * @var string $message  Thông báo riêng (tuỳ chọn); rỗng thì dùng câu mặc định theo mã lỗi
 * (Ngoài ra có $title cho thẻ <title> của layout.)
 */
?>
<section class="error-page">
    <div class="container">
<?php /* (int) ép kiểu số nguyên => in an toàn, không cần e(). */ ?>
        <p class="error-code"><?= (int) $code ?></p>
        <h1><?= $code === 404 ? 'Không tìm thấy trang' : 'Đã có lỗi xảy ra' ?></h1>
        <p><?= e($message ?: ($code === 404 ? 'Trang bạn tìm có thể đã được đổi đường dẫn hoặc không còn tồn tại.' : 'Vui lòng thử lại sau.')) ?></p>
        <a class="btn btn-primary" href="<?= e(url('/')) ?>"><?= icon('arrow-left') ?> Về trang chủ</a>
    </div>
</section>
