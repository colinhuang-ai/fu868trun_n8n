<?php
/**
 * VIEW: TRANG ĐĂNG NHẬP QUẢN TRỊ (URL: /admin/login)
 * ------------------------------------------------------------------
 * Controller gọi (app/admin.php):
 *   - admin_login_form() : GET  /admin/login – hiện form (đã đăng nhập rồi thì chuyển về /admin)
 *   - admin_login()      : POST /admin/login – kiểm tra email/mật khẩu; sai thì render lại view này kèm $error
 *
 * Trang này KHÔNG dùng layout admin (vì chưa đăng nhập, không có sidebar) nên tự viết đủ <html>.
 * Được gọi: view('admin/login', [...]) – không truyền tham số layout.
 *
 * Biến truyền vào:
 * @var string      $email  Email đã nhập (điền lại khi đăng nhập sai, đỡ phải gõ lại)
 * @var string|null $error  Câu báo lỗi: sai thông tin, hoặc bị khoá tạm do sai quá 5 lần
 */
?>
<!doctype html>
<html lang="vi">
<head>
<?php /* robots noindex: không cho máy tìm kiếm lập chỉ mục trang đăng nhập. */ ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Đăng nhập – <?= e(setting('site_name', 'CASES')) ?> CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="login-page">
<main class="login-wrap">
<?php /* Logo (CSS đổi màu SVG: nền trắng, chữ C xanh) – bấm vào để về website. */ ?>
    <a class="login-logo" href="<?= e(url('/')) ?>">
        <svg viewBox="0 0 34 34" aria-hidden="true"><circle cx="17" cy="17" r="17" fill="#1f3d73"/><path d="M22.6 12.2a7.4 7.4 0 1 0 0 9.6" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round"/></svg>
        <span><?= e(setting('site_name', 'CASES')) ?></span>
    </a>
<?php
/* Form đăng nhập: POST tới /admin/login.
 * - csrf_field(): mã chống giả mạo request (CSRF), admin_login() kiểm tra bằng csrf_verify().
 * - Ô email: value điền lại email cũ (qua e() chống XSS); autofocus để con trỏ nằm sẵn ở ô này.
 * - Ô mật khẩu: KHÔNG bao giờ điền lại giá trị cũ.
 * - autocomplete username/current-password: giúp trình quản lý mật khẩu của trình duyệt nhận diện đúng ô.
 */
?>
    <form class="login-card" method="post" action="<?= e(url('/admin/login')) ?>">
        <h1>Đăng nhập quản trị</h1>
        <p class="muted">Quản lý trang, bài viết và yêu cầu báo giá.</p>
        <?= csrf_field() ?>
        <?php if ($error): ?>
            <div class="alert alert-error" role="alert"><?= icon('alert') ?><span><?= e($error) ?></span></div>
        <?php endif; ?>
        <label class="form-field">
            <span>Email</span>
            <input type="email" name="email" value="<?= e($email) ?>" required autofocus autocomplete="username">
        </label>
        <label class="form-field">
            <span>Mật khẩu</span>
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button class="btn btn-primary btn-block" type="submit">Đăng nhập</button>
    </form>
    <a class="login-back" href="<?= e(url('/')) ?>"><?= icon('arrow-left') ?> Về website</a>
</main>
</body>
</html>
