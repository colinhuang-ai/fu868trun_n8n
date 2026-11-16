<?php
/**
 * PARTIAL: FORM "NHẬN BÁO GIÁ" (họ tên, số điện thoại, nhu cầu)
 * ------------------------------------------------------------------
 * Dùng ở: - Trang chủ, section #lien-he   => partial('contact-form', ['back' => 'home'])
 *         - Trang có mẫu "Liên hệ"         => partial('contact-form', ['back' => 'page'])
 *
 * @var string $back  'home' | 'page' – cho controller biết gửi xong thì quay lại đâu.
 *
 * Luồng hoạt động (mẫu PRG: Post → Redirect → Get):
 *   1. Người dùng bấm "Gửi yêu cầu" => POST /lien-he => site_contact_submit() (app/site.php).
 *   2. Controller kiểm tra dữ liệu:
 *        - Lỗi  => lưu dữ liệu cũ vào $_SESSION['contact_old'] + lỗi vào $_SESSION['contact_errors'].
 *        - Đúng => lưu vào bảng contacts, đặt $_SESSION['contact_success'].
 *      Rồi redirect về trang cũ (vd. "/#lien-he").
 *   3. Trang được tải lại (GET), partial này đọc các giá trị trong session để hiện thông báo,
 *      điền lại ô đã nhập, đánh dấu ô lỗi; sau đó XOÁ khỏi session để F5 không hiện lại.
 *   Nhờ redirect, bấm F5 sẽ không gửi trùng form.
 */

// Lấy dữ liệu "một lần" mà controller để lại trong session (nếu có).
$old = $_SESSION['contact_old'] ?? [];          // giá trị người dùng đã nhập (khi có lỗi)
$errs = $_SESSION['contact_errors'] ?? [];      // ['name' => 'Vui lòng...', 'phone' => '...']
$success = $_SESSION['contact_success'] ?? null; // câu cảm ơn khi gửi thành công
// Xoá ngay sau khi đọc => chỉ hiện đúng một lần.
unset($_SESSION['contact_old'], $_SESSION['contact_errors'], $_SESSION['contact_success']);
?>
<?php
/* <form> gửi POST tới /lien-he. novalidate: tắt kiểm tra mặc định của trình duyệt,
 * để dùng thông báo lỗi tiếng Việt thống nhất từ server.
 * - csrf_field(): ô ẩn chứa mã CSRF; server so khớp mã này để chặn trang web khác giả mạo gửi form.
 * - _back: ô ẩn báo trang nguồn ('home' hoặc 'page').
 * - .hp (honeypot – "bẫy mật"): ô "website" bị CSS giấu khỏi màn hình. Người thật không thấy nên để trống;
 *   bot tự động thường điền mọi ô => server thấy ô này có giá trị thì bỏ qua yêu cầu.
 *   tabindex="-1" để phím Tab không nhảy vào, autocomplete="off" để trình duyệt không tự điền.
 */
?>
<form class="quote-form" method="post" action="<?= e(url('/lien-he')) ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="_back" value="<?= e($back ?? 'home') ?>">
    <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

<?php
/* Hộp thông báo đầu form:
 * - Gửi thành công => hộp xanh với lời cảm ơn (role="status" để trình đọc màn hình đọc lên).
 * - Có lỗi         => hộp đỏ chung (role="alert"); lỗi chi tiết hiện dưới từng ô.
 */
?>
    <?php if ($success): ?>
        <div class="form-alert form-alert-success" role="status"><?= icon('check-circle') ?><span><?= e($success) ?></span></div>
    <?php elseif ($errs): ?>
        <div class="form-alert form-alert-error" role="alert"><?= icon('alert') ?><span>Vui lòng kiểm tra lại thông tin.</span></div>
    <?php endif; ?>

<?php
/* Các ô nhập. Mỗi ô:
 * - Thêm class "has-error" (viền đỏ) nếu có lỗi tương ứng trong $errs.
 * - value = giá trị cũ ($old) để người dùng không phải gõ lại khi có lỗi; luôn qua e() chống XSS.
 * - <small> hiện câu lỗi riêng của ô đó.
 * - autocomplete name/tel giúp trình duyệt gợi ý tự điền.
 * Ô "Nhu cầu" (textarea) không bắt buộc nên không có thông báo lỗi.
 */
?>
    <div class="field<?= isset($errs['name']) ? ' has-error' : '' ?>">
        <label for="qf-name">Họ và tên</label>
        <input id="qf-name" name="name" type="text" placeholder="Nguyễn Văn A" value="<?= e($old['name'] ?? '') ?>" required autocomplete="name">
        <?php if (isset($errs['name'])): ?><small><?= e($errs['name']) ?></small><?php endif; ?>
    </div>
    <div class="field<?= isset($errs['phone']) ? ' has-error' : '' ?>">
        <label for="qf-phone">Số điện thoại</label>
        <input id="qf-phone" name="phone" type="tel" placeholder="0900 000 000" value="<?= e($old['phone'] ?? '') ?>" required autocomplete="tel">
        <?php if (isset($errs['phone'])): ?><small><?= e($errs['phone']) ?></small><?php endif; ?>
    </div>
    <div class="field">
        <label for="qf-message">Nhu cầu</label>
        <textarea id="qf-message" name="message" rows="3" placeholder="Loại tôm, size, số lượng mỗi tuần"><?= e($old['message'] ?? '') ?></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Gửi yêu cầu <?= icon('arrow-right') ?></button>
</form>
