<?php
/**
 * VIEW: TÀI KHOẢN (URL: /admin/profile) – đổi tên, email đăng nhập, mật khẩu
 * ------------------------------------------------------------------
 * Controller (app/admin.php):
 *   - admin_profile($errors)  : GET  – hiện form (cũng được gọi lại khi lưu có lỗi)
 *   - admin_profile_save()    : POST – kiểm tra và lưu; đúng thì redirect kèm thông báo
 *
 * Biến truyền vào:
 * @var array $user    Người đang đăng nhập ['id','name','email']
 * @var array $errors  Lỗi theo từng ô, vd. ['current_password' => 'Mật khẩu hiện tại không đúng.']
 *
 * Quy tắc (kiểm tra ở admin_profile_save()):
 *   - Đổi mật khẩu HOẶC đổi email => bắt buộc nhập đúng mật khẩu hiện tại.
 *   - Mật khẩu mới tối thiểu 8 ký tự và phải nhập lại khớp.
 *   - Bỏ trống ô mật khẩu mới = giữ nguyên mật khẩu cũ.
 */

// Hàm nhỏ (arrow function) dùng lại nhiều lần trong form:
// $err('ten_o') => in thẻ <small> chứa câu lỗi nếu ô đó có lỗi, không thì chuỗi rỗng.
$err = fn (string $k) => isset($errors[$k]) ? '<small class="field-error">' . e($errors[$k]) . '</small>' : '';
// $cls('ten_o') => trả ' has-error' để thêm class viền đỏ cho ô bị lỗi.
$cls = fn (string $k) => isset($errors[$k]) ? ' has-error' : '';
?>
<form method="post" action="<?= e(url('/admin/profile')) ?>" class="profile-form">
    <?= csrf_field() ?>
<?php
/* Nhóm "Thông tin tài khoản".
 * value: input('name') ?: $user['name'] – nếu vừa gửi form bị lỗi thì hiện lại giá trị vừa nhập,
 * nếu không thì hiện giá trị đang lưu trong DB.
 */
?>
    <section class="panel panel-pad">
        <h2 class="panel-title">Thông tin tài khoản</h2>
        <div class="settings-grid">
            <div class="form-field<?= $cls('name') ?>">
                <label for="p-name">Tên hiển thị</label>
                <input id="p-name" type="text" name="name" value="<?= e(input('name') ?: $user['name']) ?>" required>
                <?= $err('name') ?>
            </div>
            <div class="form-field<?= $cls('email') ?>">
                <label for="p-email">Email đăng nhập</label>
                <input id="p-email" type="email" name="email" value="<?= e(input('email') ?: $user['email']) ?>" required autocomplete="username">
                <?= $err('email') ?>
            </div>
        </div>
    </section>
<?php
/* Nhóm "Đổi mật khẩu". Các ô mật khẩu không bao giờ điền sẵn giá trị.
 * autocomplete current-password / new-password: giúp trình duyệt gợi ý & lưu mật khẩu đúng ô.
 */
?>
    <section class="panel panel-pad">
        <h2 class="panel-title">Đổi mật khẩu</h2>
        <p class="muted">Bỏ trống nếu không đổi. Cần mật khẩu hiện tại khi đổi mật khẩu hoặc email.</p>
        <div class="settings-grid">
            <div class="form-field span-2<?= $cls('current_password') ?>">
                <label for="p-cur">Mật khẩu hiện tại</label>
                <input id="p-cur" type="password" name="current_password" autocomplete="current-password">
                <?= $err('current_password') ?>
            </div>
            <div class="form-field<?= $cls('new_password') ?>">
                <label for="p-new">Mật khẩu mới</label>
                <input id="p-new" type="password" name="new_password" autocomplete="new-password" minlength="8">
                <?= $err('new_password') ?>
            </div>
            <div class="form-field<?= $cls('new_password_confirmation') ?>">
                <label for="p-new2">Nhập lại mật khẩu mới</label>
                <input id="p-new2" type="password" name="new_password_confirmation" autocomplete="new-password">
                <?= $err('new_password_confirmation') ?>
            </div>
        </div>
    </section>
    <div class="sticky-actions">
        <button class="btn btn-primary" type="submit">Lưu</button>
    </div>
</form>
