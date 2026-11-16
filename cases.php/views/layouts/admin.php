<?php
/**
 * LAYOUT: KHUNG CHUNG CỦA TRANG QUẢN TRỊ (/admin)
 * ------------------------------------------------------------------
 * Mọi màn hình admin (trừ trang đăng nhập) được bọc trong layout này.
 * Controller gọi qua admin_render($template, $data) trong app/admin.php
 *   => view('admin/xxx', $data, 'admin').
 *
 * Biến dùng trong layout:
 * @var string      $content     HTML của màn hình con (do view() tự gán)
 * @var string|null $title       Tiêu đề màn hình (hiện ở topbar và thẻ <title>)
 * @var string|null $active      Mục menu đang chọn: 'dashboard' | 'pages' | 'posts' | 'contacts' | 'settings' | 'profile'
 * @var bool|null   $use_editor  true => nạp thêm thư viện soạn thảo Quill (chỉ màn hình thêm/sửa Trang/Bài viết)
 *
 * Bố cục: sidebar trái (logo + menu) | cột chính (topbar + vùng nội dung có thông báo flash).
 * Trên mobile sidebar trượt ra từ trái (admin.js bật class "sidebar-open" trên <body>).
 */

// Người đang đăng nhập (chắc chắn có, vì route admin đã qua require_auth()).
$user = current_user();
$active = $active ?? '';

// Đếm số yêu cầu báo giá chưa đọc để hiện chấm đỏ có số trên menu.
$unread = (int) db_value('SELECT COUNT(*) FROM contacts WHERE is_read = 0');

// Menu sidebar. Mỗi mục: [khoá active, đường dẫn, icon, nhãn, số badge (null = không hiện)].
// "$unread ?: null" => nếu 0 thì không hiện badge.
$menu = [
    ['dashboard', '/admin', 'dashboard', 'Tổng quan', null],
    ['pages', '/admin/pages', 'file-text', 'Trang', null],
    ['posts', '/admin/posts', 'newspaper', 'Bài viết', null],
    ['contacts', '/admin/contacts', 'inbox', 'Yêu cầu báo giá', $unread ?: null],
    ['settings', '/admin/settings', 'sliders', 'Cài đặt', null],
];
?>
<!doctype html>
<html lang="vi">
<head>
<?php
/* --- HEAD ---
 * - robots noindex,nofollow: không cho Google lập chỉ mục trang quản trị.
 * - meta csrf-token: admin.js đọc token này để gửi kèm header X-CSRF-Token khi upload ảnh bằng AJAX
 *   (route POST /admin/upload kiểm tra token qua csrf_verify()).
 * - Quill (trình soạn thảo) chỉ nạp khi $use_editor = true để các trang khác nhẹ hơn.
 *   Nạp từ CDN jsdelivr, cố định phiên bản 2.0.3 để không bị thay đổi bất ngờ.
 */
?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(($title ?? 'Quản trị') . ' – ' . setting('site_name', 'CASES') . ' CMS') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <?php if (!empty($use_editor)): ?>
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">
<div class="admin-shell">
<?php
/* --- SIDEBAR ---
 * Logo (link về Tổng quan) + menu lặp từ mảng $menu.
 * Mục đang mở có class "active" (nền trắng). Badge đỏ hiện số yêu cầu báo giá chưa đọc.
 * Cuối sidebar: link "Xem website" mở tab mới (rel="noopener" để tab mới không truy cập được window.opener).
 */
?>
    <aside class="sidebar" id="sidebar">
        <a class="sidebar-logo" href="<?= e(url('/admin')) ?>">
            <svg viewBox="0 0 34 34" aria-hidden="true"><circle cx="17" cy="17" r="17" fill="#fff"/><path d="M22.6 12.2a7.4 7.4 0 1 0 0 9.6" fill="none" stroke="#1f3d73" stroke-width="3.4" stroke-linecap="round"/></svg>
            <span><?= e(setting('site_name', 'CASES')) ?> <small>CMS</small></span>
        </a>
        <nav class="sidebar-nav">
            <?php foreach ($menu as [$key, $href, $ic, $label, $badge]): ?>
                <a href="<?= e(url($href)) ?>" class="<?= $active === $key ? 'active' : '' ?>">
                    <?= icon($ic) ?><span><?= e($label) ?></span>
                    <?php if ($badge): ?><b class="nav-badge"><?= (int) $badge ?></b><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-foot">
            <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener"><?= icon('external') ?><span>Xem website</span></a>
        </div>
    </aside>

    <div class="main">
<?php
/* --- TOPBAR ---
 * - Nút hamburger (.sidebar-toggle): chỉ hiện trên mobile, mở sidebar.
 * - Tiêu đề màn hình ($title).
 * - Avatar (chữ cái đầu của tên, mb_* để xử lý đúng ký tự tiếng Việt) + tên => link tới trang Tài khoản.
 * - Nút đăng xuất: là một FORM POST tới /admin/logout kèm csrf_field()
 *   (đăng xuất bằng POST thay vì link GET để trang web khác không thể lừa trình duyệt đăng xuất bạn).
 */
?>
        <header class="topbar">
            <button class="sidebar-toggle" type="button" aria-label="Mở menu" aria-controls="sidebar"><?= icon('menu') ?></button>
            <h1 class="topbar-title"><?= e($title ?? '') ?></h1>
            <div class="topbar-user">
                <a href="<?= e(url('/admin/profile')) ?>" class="<?= $active === 'profile' ? 'active' : '' ?>">
                    <span class="avatar"><?= e(mb_strtoupper(mb_substr($user['name'] ?? 'A', 0, 1))) ?></span>
                    <span class="topbar-name"><?= e($user['name'] ?? '') ?></span>
                </a>
                <form method="post" action="<?= e(url('/admin/logout')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="icon-btn" title="Đăng xuất" aria-label="Đăng xuất"><?= icon('log-out') ?></button>
                </form>
            </div>
        </header>

<?php
/* --- VÙNG NỘI DUNG ---
 * flashes(): lấy và XOÁ các thông báo một lần (flash) đã lưu trong session bằng flash('success'|'error'|'info', ...)
 * ở controller, vd. "Đã lưu thay đổi." sau khi redirect. Mỗi thông báo có nút x để đóng (admin.js).
 * Sau đó in $content – HTML của màn hình con.
 */
?>
        <div class="content">
            <?php foreach (flashes() as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?>" role="status">
                    <?= icon($f['type'] === 'error' ? 'alert' : 'check-circle') ?><span><?= e($f['message']) ?></span>
                    <button type="button" class="alert-close" aria-label="Đóng"><?= icon('x') ?></button>
                </div>
            <?php endforeach; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<?php /* Lớp nền mờ phía sau sidebar khi mở trên mobile; bấm vào để đóng sidebar. */ ?>
<div class="sidebar-backdrop"></div>

<?php /* Thư viện Quill phải nạp TRƯỚC admin.js vì admin.js khởi tạo editor bằng window.Quill. */ ?>
<?php if (!empty($use_editor)): ?>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<?php endif; ?>
<script src="<?= e(asset('assets/js/admin.js')) ?>"></script>
</body>
</html>
