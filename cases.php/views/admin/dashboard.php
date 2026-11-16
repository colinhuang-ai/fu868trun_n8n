<?php
/**
 * VIEW: TỔNG QUAN (DASHBOARD) – màn hình đầu tiên sau khi đăng nhập (URL: /admin)
 * ------------------------------------------------------------------
 * Controller gọi: admin_dashboard() trong app/admin.php, layout 'admin'.
 *
 * Biến truyền vào:
 * @var array $stats     Các con số thống kê:
 *                       pages, pages_published      – tổng số trang / số trang đã xuất bản
 *                       posts, posts_published      – tổng số bài viết / số bài đã xuất bản
 *                       products                    – số sản phẩm đang hiện (bài chuyên mục Sản phẩm, đã xuất bản)
 *                       contacts, contacts_unread   – tổng yêu cầu báo giá / số chưa đọc
 * @var array $posts     6 bài viết sửa gần nhất
 * @var array $pages     6 trang sửa gần nhất
 * @var array $contacts  5 yêu cầu báo giá mới nhất
 *
 * Bố cục: 4 thẻ số liệu -> hàng nút thao tác nhanh -> lưới 2 cột các danh sách gần đây.
 */

// Tên chuyên mục để hiện cạnh tên bài viết.
$cats = post_categories();

// 4 thẻ thống kê. Mỗi thẻ: [icon, nhãn, con số lớn, dòng phụ, link khi bấm vào thẻ].
// Thẻ "Sản phẩm" link tới danh sách bài viết đã lọc sẵn chuyên mục san-pham.
$cards = [
    ['file-text', 'Trang', $stats['pages'], $stats['pages_published'] . ' đã xuất bản', '/admin/pages'],
    ['newspaper', 'Bài viết', $stats['posts'], $stats['posts_published'] . ' đã xuất bản', '/admin/posts'],
    ['package', 'Sản phẩm trên trang chủ', $stats['products'], 'Chuyên mục “Sản phẩm”', '/admin/posts?category=san-pham'],
    ['inbox', 'Yêu cầu báo giá', $stats['contacts'], $stats['contacts_unread'] . ' chưa đọc', '/admin/contacts'],
];
?>
<?php /* --- 4 THẺ SỐ LIỆU: cả thẻ là một link. (int) ép kiểu số để in an toàn. --- */ ?>
<div class="stat-grid">
    <?php foreach ($cards as [$ic, $label, $num, $sub, $href]): ?>
        <a class="stat-card" href="<?= e(url($href)) ?>">
            <span class="stat-icon"><?= icon($ic) ?></span>
            <span class="stat-label"><?= e($label) ?></span>
            <strong class="stat-num"><?= (int) $num ?></strong>
            <span class="stat-sub"><?= e($sub) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<?php /* --- NÚT THAO TÁC NHANH: viết bài mới, thêm trang, mở cài đặt. --- */ ?>
<div class="quick-actions">
    <a class="btn btn-primary" href="<?= e(url('/admin/posts/create')) ?>"><?= icon('plus') ?> Viết bài mới</a>
    <a class="btn btn-light" href="<?= e(url('/admin/pages/create')) ?>"><?= icon('plus') ?> Thêm trang</a>
    <a class="btn btn-light" href="<?= e(url('/admin/settings')) ?>"><?= icon('sliders') ?> Cài đặt website</a>
</div>

<?php /* --- CÁC DANH SÁCH GẦN ĐÂY (lưới 2 cột; trên mobile thành 1 cột) --- */ ?>
<div class="dash-grid">
<?php
/* Bài viết gần đây: tên (link tới trang sửa), chuyên mục · thời gian cập nhật, nhãn trạng thái.
 * Class badge-published / badge-draft đổi màu nhãn theo trạng thái; statuses() đổi mã sang chữ tiếng Việt.
 */
?>
    <section class="panel">
        <header class="panel-head"><h2>Bài viết gần đây</h2><a href="<?= e(url('/admin/posts')) ?>">Tất cả</a></header>
        <ul class="list">
            <?php foreach ($posts as $p): ?>
                <li>
                    <a href="<?= e(url('/admin/posts/' . $p['id'] . '/edit')) ?>"><?= e($p['title']) ?></a>
                    <span class="list-meta"><?= e($cats[$p['category']] ?? '') ?> · <?= e(format_date($p['updated_at'], 'd/m/Y H:i')) ?></span>
                    <span class="badge badge-<?= e($p['status']) ?>"><?= e(statuses()[$p['status']]) ?></span>
                </li>
            <?php endforeach; ?>
            <?php if (!$posts): ?><li class="empty">Chưa có bài viết.</li><?php endif; ?>
        </ul>
    </section>
<?php
/* Yêu cầu báo giá mới: tên – SĐT, trích nhu cầu (cắt 70 ký tự) · ngày giờ gửi.
 * Yêu cầu chưa đọc (is_read = 0) có class "unread" => CSS thêm chấm đỏ phía trước.
 */
?>
    <section class="panel">
        <header class="panel-head"><h2>Yêu cầu báo giá mới</h2><a href="<?= e(url('/admin/contacts')) ?>">Tất cả</a></header>
        <ul class="list">
            <?php foreach ($contacts as $c): ?>
                <li class="<?= $c['is_read'] ? '' : 'unread' ?>">
                    <a href="<?= e(url('/admin/contacts')) ?>"><?= e($c['name']) ?> – <?= e($c['phone']) ?></a>
                    <span class="list-meta"><?= e(str_excerpt($c['message'], 70) ?: '(không ghi nhu cầu)') ?> · <?= e(format_date($c['created_at'], 'd/m H:i')) ?></span>
                </li>
            <?php endforeach; ?>
            <?php if (!$contacts): ?><li class="empty">Chưa có yêu cầu nào.</li><?php endif; ?>
        </ul>
    </section>
<?php /* Trang sửa gần đây: tên (link sửa), thời gian cập nhật, trạng thái. */ ?>
    <section class="panel">
        <header class="panel-head"><h2>Trang</h2><a href="<?= e(url('/admin/pages')) ?>">Tất cả</a></header>
        <ul class="list">
            <?php foreach ($pages as $p): ?>
                <li>
                    <a href="<?= e(url('/admin/pages/' . $p['id'] . '/edit')) ?>"><?= e($p['title']) ?></a>
                    <span class="list-meta"><?= e(format_date($p['updated_at'], 'd/m/Y H:i')) ?></span>
                    <span class="badge badge-<?= e($p['status']) ?>"><?= e(statuses()[$p['status']]) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
