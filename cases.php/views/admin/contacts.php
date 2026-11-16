<?php
/**
 * VIEW: DANH SÁCH YÊU CẦU BÁO GIÁ (URL: /admin/contacts và /admin/contacts?filter=unread)
 * ------------------------------------------------------------------
 * Controller gọi: admin_contacts() trong app/admin.php, layout 'admin'.
 * Dữ liệu đến từ form "Nhận báo giá" ngoài website (bảng contacts, lưu bởi site_contact_submit()).
 *
 * Biến truyền vào:
 * @var array  $rows        Các yêu cầu của trang hiện tại (mới nhất trước)
 * @var array  $pagination  Kết quả paginate() cho phân trang
 * @var string $filter      '' = tất cả, 'unread' = chỉ chưa đọc
 * @var int    $unread      Số yêu cầu chưa đọc (hiện trên tab)
 *
 * Thao tác trên mỗi dòng (đều là form POST có CSRF):
 *   - POST /admin/contacts/{id}/toggle => admin_contact_toggle(): đổi qua lại Đã xử lý / Chưa đọc
 *   - POST /admin/contacts/{id}/delete => admin_contact_delete(): xoá (có hộp xác nhận)
 */
?>
<?php /* Tab lọc: "Tất cả" (đếm toàn bộ bảng contacts) và "Chưa đọc". Tab đang chọn có class active. */ ?>
<div class="toolbar">
    <nav class="status-tabs">
        <a href="<?= e(url('/admin/contacts')) ?>" class="<?= $filter === '' ? 'active' : '' ?>">Tất cả <b><?= (int) db_value('SELECT COUNT(*) FROM contacts') ?></b></a>
        <a href="<?= e(url('/admin/contacts?filter=unread')) ?>" class="<?= $filter === 'unread' ? 'active' : '' ?>">Chưa đọc <b><?= $unread ?></b></a>
    </nav>
</div>

<div class="panel table-panel">
    <?php if ($rows): ?>
<?php
/* Bảng yêu cầu. Trên mobile CSS biến mỗi dòng thành một khối; data-label dùng để hiện tên cột trước giá trị.
 * - Dòng chưa đọc có class "row-unread" (nền vàng nhạt + chấm đỏ).
 * - Số điện thoại là link "tel:" để bấm gọi trên điện thoại; preg_replace bỏ hết ký tự không phải số hoặc dấu +.
 * - Nhu cầu: nl2br(e(...)) – escape trước rồi mới đổi xuống dòng thành <br> (an toàn XSS).
 */
?>
        <table class="table">
            <thead>
            <tr><th>Khách hàng</th><th>Nhu cầu</th><th>Nguồn</th><th>Thời gian</th><th class="col-actions"><span class="sr-only">Thao tác</span></th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr class="<?= $r['is_read'] ? '' : 'row-unread' ?>">
                    <td data-label="Khách hàng">
                        <span class="row-title"><?= e($r['name']) ?></span>
                        <a class="row-slug" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $r['phone'])) ?>"><?= e($r['phone']) ?></a>
                    </td>
                    <td data-label="Nhu cầu" class="cell-message"><?= nl2br(e($r['message'] ?: '—')) ?></td>
                    <td data-label="Nguồn" class="muted"><?= e($r['source']) ?></td>
                    <td data-label="Thời gian" class="muted"><?= e(format_date($r['created_at'], 'd/m/Y H:i')) ?></td>
                    <td class="col-actions">
                        <div class="row-actions">
<?php
/* Nút đổi trạng thái đọc. Gửi kèm "filter" hiện tại để sau khi xử lý quay lại đúng tab đang xem. */
?>
                            <form method="post" action="<?= e(url('/admin/contacts/' . $r['id'] . '/toggle')) ?>">
                                <?= csrf_field() ?><input type="hidden" name="filter" value="<?= e($filter) ?>">
                                <button class="btn btn-light btn-sm" type="submit"><?= $r['is_read'] ? 'Đánh dấu chưa đọc' : 'Đã xử lý' ?></button>
                            </form>
<?php
/* Nút xoá. data-confirm: admin.js hiện hộp xác nhận với câu này trước khi gửi form; bấm Huỷ thì không gửi. */
?>
                            <form method="post" action="<?= e(url('/admin/contacts/' . $r['id'] . '/delete')) ?>" data-confirm="Xoá yêu cầu của <?= e($r['name']) ?>?">
                                <?= csrf_field() ?>
                                <button class="icon-btn icon-btn-danger" type="submit" title="Xoá"><?= icon('trash') ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
<?php /* Trạng thái trống: chưa có yêu cầu (hoặc không có yêu cầu chưa đọc khi đang lọc). */ ?>
        <div class="empty-state">
            <?= icon('inbox', 'icon empty-icon', 1.5) ?>
            <p>Chưa có yêu cầu báo giá<?= $filter ? ' chưa đọc' : '' ?>. Yêu cầu gửi từ form “Nhận báo giá” trên website sẽ xuất hiện ở đây.</p>
        </div>
    <?php endif; ?>
</div>
<?php /* Phân trang (tự ẩn khi chỉ có 1 trang). */ ?>
<?= partial('pagination', ['p' => $pagination]) ?>
