<?php
/**
 * LAYOUT: KHUNG CHUNG CỦA WEBSITE (phần công khai)
 * ------------------------------------------------------------------
 * Mọi trang ngoài website (trang chủ, trang tĩnh, danh sách/chi tiết bài viết, trang lỗi)
 * đều được bọc trong layout này. Controller gọi: view('site/xxx', $data, 'site')
 * => hàm view() (app/helpers.php) render view con trước, đưa HTML vào biến $content,
 *    rồi render layout này với cùng mảng dữ liệu.
 *
 * Biến dùng trong layout (đều tuỳ chọn, controller truyền nếu cần):
 * @var string      $content           HTML của view con (bắt buộc, do view() tự gán)
 * @var string|null $title             Tiêu đề trang => thẻ <title> "Tiêu đề – CASES"
 * @var string|null $meta_description  Mô tả SEO; không có thì lấy setting 'meta_description'
 * @var string|null $og_image          Ảnh khi chia sẻ lên mạng xã hội (Open Graph)
 * @var string|null $active            Trang đang xem: 'home' | 'posts' | 'page:{slug}' => tô đậm menu
 * @var bool|null   $is_preview        true khi admin xem bản nháp => hiện thanh vàng cảnh báo
 * @var string|null $edit_url          Link sửa nội dung trong admin (hiện cho admin đã đăng nhập)
 *
 * Cấu trúc HTML: <head> (meta, font, CSS) -> thanh xem trước (nếu có) -> header + menu
 *                -> <main> chứa $content -> footer -> script site.js
 */

// Tên website từ Admin → Cài đặt (mặc định 'CASES').
$siteName = setting('site_name', 'CASES');

// Tiêu đề tab trình duyệt: có $title => "Tiêu đề – CASES"; trang chủ => "CASES – khẩu hiệu".
$docTitle = !empty($title) ? $title . ' – ' . $siteName : $siteName . ' – ' . setting('site_tagline');

// Mô tả SEO: ưu tiên của trang hiện tại, không có thì dùng mô tả mặc định trong Cài đặt.
$metaDesc = $meta_description ?? setting('meta_description');

$active = $active ?? '';
$isHome = $active === 'home';

// Tạo link tới một section của trang chủ (vd. 'san-pham').
// Đang ở trang chủ => chỉ cần "#san-pham" (cuộn trong trang);
// ở trang khác => "/#san-pham" (về trang chủ rồi cuộn tới section).
$anchor = fn (string $id) => $isHome ? '#' . $id : url('/#' . $id);

// Danh sách mục menu chính. Mỗi mục: [nhãn, href, có đang active không].
// 4 mục đầu là anchor tới các section trang chủ (cố định theo prototype).
$nav = [
    ['Sản phẩm', $anchor('san-pham'), false],
    ['Vì sao chọn CASES', $anchor('vi-sao'), false],
    ['Chất lượng ISO', $anchor('chat-luong'), false],
    ['Quy trình', $anchor('quy-trinh'), false],
];
// Chèn thêm các TRANG trong CMS có bật "Hiện trên menu" (menu_pages() ở app/content.php,
// đã sắp theo thứ tự menu_order). Active khi đang xem đúng trang đó.
foreach (menu_pages() as $mp) {
    $nav[] = [$mp['title'], url('/' . $mp['slug']), $active === 'page:' . $mp['slug']];
}
// 2 mục cuối cố định: danh sách bài viết và anchor tới form liên hệ.
$nav[] = ['Tin tức', url('/bai-viet'), $active === 'posts'];
$nav[] = ['Liên hệ', $anchor('lien-he'), false];
?>
<!doctype html>
<html lang="vi">
<head>
<?php
/* --- HEAD ---
 * - viewport: để trang hiển thị đúng trên điện thoại (responsive).
 * - title / description / og:*: SEO và thông tin khi chia sẻ link (Facebook, Zalo...).
 * - icon: favicon dạng SVG nhúng thẳng (data URI) – logo tròn chữ C, không cần file ảnh riêng.
 *   rawurlencode() để mã hoá SVG thành chuỗi hợp lệ trong URL.
 * - Google Fonts: Plus Jakarta Sans (tiêu đề) + Source Sans 3 (nội dung), đều hỗ trợ tiếng Việt.
 *   preconnect giúp trình duyệt mở kết nối tới máy chủ font sớm hơn => tải nhanh hơn.
 * - asset(): thêm "?v=thời-gian-sửa-file" vào URL CSS/JS để trình duyệt tải bản mới khi file thay đổi
 *   (tránh bị cache bản cũ).
 */
?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($docTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta property="og:title" content="<?= e($docTitle) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($og_image)): ?><meta property="og:image" content="<?= e(url($og_image)) ?>"><?php endif; ?>
    <link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34 34"><circle cx="17" cy="17" r="17" fill="#1f3d73"/><path d="M22.6 12.2a7.4 7.4 0 1 0 0 9.6" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round"/></svg>') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
</head>
<body>
<?php /* Link ẩn "Bỏ qua tới nội dung": hiện ra khi người dùng bấm Tab, giúp người dùng bàn phím/trình đọc màn hình nhảy thẳng vào <main>. */ ?>
<a class="skip-link" href="#main">Bỏ qua tới nội dung</a>

<?php
/* --- THANH DÀNH CHO ADMIN (chỉ hiện khi đã đăng nhập quản trị) ---
 * - $is_preview = true: đang xem bản nháp/bài hẹn giờ (khách thường sẽ gặp 404) => thanh vàng cảnh báo.
 * - Ngược lại, nếu admin đang đăng nhập và trang có $edit_url => thanh nhỏ "Sửa nội dung này"
 *   để nhảy nhanh vào trang sửa trong admin.
 */
?>
<?php if (!empty($is_preview)): ?>
    <div class="preview-bar">Bạn đang xem bản nháp (chỉ quản trị viên thấy). <a href="<?= e(url($edit_url ?? '/admin')) ?>">Quay lại chỉnh sửa</a></div>
<?php elseif (current_user() && !empty($edit_url)): ?>
    <div class="preview-bar preview-bar-soft"><a href="<?= e(url($edit_url)) ?>"><?= icon('pencil') ?> Sửa nội dung này</a></div>
<?php endif; ?>

<?php
/* --- HEADER (dính trên cùng khi cuộn – position: sticky trong CSS) ---
 * Gồm: logo (SVG tròn chữ C + tên site), menu chính, nút "Nhận báo giá", nút hamburger (mobile).
 * - Menu: lặp mảng $nav; mục active có class "active" + aria-current="page" (hỗ trợ trình đọc màn hình).
 * - .nav-cta-mobile: nút "Nhận báo giá" nằm TRONG menu, chỉ hiện ở màn hình nhỏ (CSS).
 * - .header-cta: nút "Nhận báo giá" ở header, ẩn ở màn hình nhỏ.
 * - .nav-toggle: nút mở/đóng menu trên mobile; site.js bật/tắt class "nav-open" trên <body>
 *   và cập nhật aria-expanded. Hai icon menu/x được CSS ẩn hiện tuỳ trạng thái.
 */
?>
<header class="site-header">
    <div class="container">
        <a class="logo" href="<?= e(url('/')) ?>" aria-label="<?= e($siteName) ?> – Trang chủ">
            <svg class="logo-mark" viewBox="0 0 34 34" aria-hidden="true"><circle cx="17" cy="17" r="17" fill="#1f3d73"/><path d="M22.6 12.2a7.4 7.4 0 1 0 0 9.6" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round"/></svg>
            <span><?= e($siteName) ?></span>
        </a>
        <nav class="main-nav" id="main-nav" aria-label="Menu chính">
            <?php foreach ($nav as [$label, $href, $isActive]): ?>
                <a href="<?= e($href) ?>"<?= $isActive ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
            <a class="btn btn-primary nav-cta-mobile" href="<?= e($anchor('lien-he')) ?>">Nhận báo giá</a>
        </nav>
        <a class="btn btn-primary header-cta" href="<?= e($anchor('lien-he')) ?>">Nhận báo giá</a>
        <button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu">
            <?= icon('menu', 'icon icon-open') ?><?= icon('x', 'icon icon-close') ?>
        </button>
    </div>
</header>

<?php
/* --- NỘI DUNG CHÍNH ---
 * $content là HTML của view con (home.php, post.php...) đã render sẵn.
 * In thẳng KHÔNG qua e() vì đây là HTML do chính hệ thống tạo ra (dữ liệu bên trong đã được escape ở view con).
 * id="main" là đích của link "Bỏ qua tới nội dung".
 */
?>
<main id="main">
    <?= $content ?>
</main>

<?php
/* --- FOOTER: 3 cột ---
 *   1. Tên site + khẩu hiệu (setting 'site_tagline')
 *   2. "Khám phá": các link tới section trang chủ + trang Tin tức
 *   3. "Thông tin liên hệ": địa chỉ, điện thoại, email (setting company_*, sửa ở Admin → Cài đặt)
 * Dòng bản quyền dưới cùng: setting 'footer_note', mặc định "© năm hiện tại + tên site".
 */
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-logo"><?= e($siteName) ?></div>
                <p class="footer-tagline"><?= e(setting('site_tagline')) ?></p>
            </div>
            <div>
                <h4>Khám phá</h4>
                <ul class="footer-links">
                    <li><a href="<?= e($anchor('san-pham')) ?>">Sản phẩm</a></li>
                    <li><a href="<?= e($anchor('vi-sao')) ?>">Vì sao chọn CASES</a></li>
                    <li><a href="<?= e($anchor('chat-luong')) ?>">Chất lượng ISO</a></li>
                    <li><a href="<?= e($anchor('quy-trinh')) ?>">Quy trình</a></li>
                    <li><a href="<?= e(url('/bai-viet')) ?>">Tin tức</a></li>
                </ul>
            </div>
            <div>
                <h4>Thông tin liên hệ</h4>
                <ul class="footer-contact">
                    <li><?= icon('map-pin') ?><span><?= e(setting('company_address')) ?></span></li>
                    <li><?= icon('phone') ?><span><?= e(setting('company_phone')) ?></span></li>
                    <li><?= icon('mail') ?><span><?= e(setting('company_email')) ?></span></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span><?= e(setting('footer_note', '© ' . date('Y') . ' ' . $siteName)) ?></span>
        </div>
    </div>
</footer>

<?php /* JS của website: menu mobile, hiệu ứng hiện dần khi cuộn. "defer" = chạy sau khi HTML tải xong, không chặn hiển thị. */ ?>
<script src="<?= e(asset('assets/js/site.js')) ?>" defer></script>
</body>
</html>
