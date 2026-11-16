<?php
/**
 * VIEW: DANH SÁCH TRANG / BÀI VIẾT (URL: /admin/pages hoặc /admin/posts)
 * ------------------------------------------------------------------
 * Dùng CHUNG cho cả 2 loại nội dung; khác nhau ở vài cột (xem $isPosts bên dưới).
 * Controller gọi: admin_content_index($type) trong app/admin.php, layout 'admin'.
 *
 * Biến truyền vào:
 * @var string $type        'pages' | 'posts' – loại nội dung đang xem (lấy từ {type} trên URL)
 * @var array  $t           Định nghĩa loại nội dung (content_types() trong app/content.php):
 *                          ['table','singular','plural','new','icon']
 * @var array  $rows        Các dòng của trang hiện tại (đã lọc + phân trang)
 * @var array  $pagination  Kết quả paginate()
 * @var array  $counts      Số lượng theo trạng thái để hiện trên tab: ['' => tất cả, 'published' => .., 'draft' => ..]
 * @var array  $filters     Bộ lọc đang áp dụng: ['q' => từ khoá, 'status' => .., 'category' => ..]
 *
 * Lọc hoạt động bằng query string (GET), vd. /admin/posts?status=draft&q=tom&category=san-pham&page=2.
 * query_url([...]) tạo link giữ nguyên các tham số khác và chỉ đổi tham số truyền vào
 * (đặt 'page' => '' để quay về trang 1 khi đổi tab).
 */

// true = đang ở danh sách Bài viết (có cột Chuyên mục, Ngày đăng); false = Trang (cột Mẫu trang, Menu).
$isPosts = $type === 'posts';
$cats = post_categories();   // mã chuyên mục => tên
$tpls = page_templates();    // mã mẫu trang  => tên
?>
<?php
/* --- THANH CÔNG CỤ ---
 * Trái: tab trạng thái (Tất cả / Đã xuất bản / Bản nháp) kèm số lượng.
 * Phải: nút tạo mới => GET /admin/{type}/create.
 */
?>
<div class="toolbar">
    <nav class="status-tabs">
        <a href="<?= e(query_url(['status' => '', 'page' => ''])) ?>" class="<?= $filters['status'] === '' ? 'active' : '' ?>">Tất cả <b><?= $counts[''] ?></b></a>
        <?php foreach (statuses() as $key => $label): ?>
            <a href="<?= e(query_url(['status' => $key, 'page' => ''])) ?>" class="<?= $filters['status'] === $key ? 'active' : '' ?>"><?= e($label) ?> <b><?= $counts[$key] ?></b></a>
        <?php endforeach; ?>
    </nav>
    <a class="btn btn-primary" href="<?= e(url("/admin/$type/create")) ?>"><?= icon('plus') ?> <?= e($t['new']) ?></a>
</div>

<?php
/* --- BỘ LỌC (form GET) ---
 * - Giữ trạng thái đang chọn bằng ô ẩn "status" để tìm kiếm không làm mất tab đang lọc.
 * - Ô tìm kiếm q: tìm trong tiêu đề hoặc slug (LIKE trong SQL).
 * - Ô chọn chuyên mục (chỉ với Bài viết): đổi lựa chọn là gửi form ngay (onchange submit).
 * - "Xoá lọc": chỉ hiện khi đang có từ khoá hoặc chuyên mục.
 */
?>
<form class="filters" method="get" action="<?= e(url("/admin/$type")) ?>">
    <?php if ($filters['status']): ?><input type="hidden" name="status" value="<?= e($filters['status']) ?>"><?php endif; ?>
    <label class="search-input">
        <?= icon('search') ?>
        <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Tìm theo tiêu đề hoặc đường dẫn…">
    </label>
    <?php if ($isPosts): ?>
        <select name="category" onchange="this.form.submit()" aria-label="Chuyên mục">
            <option value="">Mọi chuyên mục</option>
            <?php foreach ($cats as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $filters['category'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <button class="btn btn-light" type="submit">Lọc</button>
    <?php if ($filters['q'] || $filters['category']): ?>
        <a class="btn btn-ghost" href="<?= e(url("/admin/$type")) ?>">Xoá lọc</a>
    <?php endif; ?>
</form>

<div class="panel table-panel">
    <?php if ($rows): ?>
<?php
/* --- BẢNG DỮ LIỆU ---
 * Cột 2 và 4 đổi nội dung theo loại:
 *   Bài viết: Chuyên mục (+ nhãn "Nổi bật") | Ngày đăng
 *   Trang:    Mẫu trang                     | Menu (có hiện trên menu không, thứ tự)
 * data-label trên mỗi ô: trên mobile bảng thành dạng thẻ, CSS dùng data-label để hiện tên cột.
 * $publicUrl = đường dẫn ngoài website của dòng đó (content_public_url()) – dùng cho dòng slug và nút "Xem".
 */
?>
        <table class="table">
            <thead>
            <tr>
                <th>Tiêu đề</th>
                <th><?= $isPosts ? 'Chuyên mục' : 'Mẫu trang' ?></th>
                <th>Trạng thái</th>
                <th><?= $isPosts ? 'Ngày đăng' : 'Menu' ?></th>
                <th>Cập nhật</th>
                <th class="col-actions"><span class="sr-only">Thao tác</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): $publicUrl = content_public_url($type, $r); ?>
                <tr>
<?php /* Tiêu đề (link tới trang sửa) + đường dẫn công khai bên dưới. */ ?>
                    <td data-label="Tiêu đề">
                        <a class="row-title" href="<?= e(url("/admin/$type/{$r['id']}/edit")) ?>"><?= e($r['title']) ?></a>
                        <span class="row-slug"><?= e($publicUrl) ?></span>
                    </td>
                    <td data-label="<?= $isPosts ? 'Chuyên mục' : 'Mẫu' ?>">
                        <?php if ($isPosts): ?>
                            <?= e($cats[$r['category']] ?? $r['category']) ?>
                            <?php if ($r['is_featured']): ?><span class="badge badge-featured">Nổi bật</span><?php endif; ?>
                        <?php else: ?>
                            <?= e($tpls[$r['template']] ?? $r['template']) ?>
                        <?php endif; ?>
                    </td>
<?php
/* Trạng thái. Nếu đã xuất bản nhưng ngày đăng > hiện tại (so sánh chuỗi 'Y-m-d H:i:s' với now())
 * => bài đang HẸN GIỜ, hiện thêm nhãn "Hẹn giờ" (khách chưa thấy cho tới đúng giờ).
 */
?>
                    <td data-label="Trạng thái">
                        <span class="badge badge-<?= e($r['status']) ?>"><?= e(statuses()[$r['status']]) ?></span>
                        <?php if ($r['status'] === 'published' && $r['published_at'] > now()): ?><span class="badge badge-scheduled">Hẹn giờ</span><?php endif; ?>
                    </td>
                    <td data-label="<?= $isPosts ? 'Ngày đăng' : 'Menu' ?>">
                        <?php if ($isPosts): ?>
                            <?= e(format_date($r['published_at'], 'd/m/Y H:i') ?: '—') ?>
                        <?php else: ?>
                            <?= $r['show_in_menu'] ? 'Có (thứ tự ' . (int) $r['menu_order'] . ')' : '—' ?>
                        <?php endif; ?>
                    </td>
                    <td data-label="Cập nhật" class="muted"><?= e(format_date($r['updated_at'], 'd/m/Y H:i')) ?></td>
<?php
/* Các nút thao tác của dòng:
 *   - Xem: mở trang ngoài website ở tab mới (bản nháp chỉ admin đã đăng nhập mới xem được).
 *   - Sửa: GET /admin/{type}/{id}/edit
 *   - Nhân bản: POST /admin/{type}/{id}/duplicate – tạo bản sao ở trạng thái nháp
 *   - Xoá: POST /admin/{type}/{id}/delete – data-confirm khiến admin.js hỏi xác nhận trước
 * Nhân bản/Xoá là form POST + csrf_field() (thao tác thay đổi dữ liệu không dùng link GET).
 */
?>
                    <td class="col-actions">
                        <div class="row-actions">
                            <a class="icon-btn" href="<?= e(url($publicUrl)) ?>" target="_blank" rel="noopener" title="Xem"><?= icon('eye') ?></a>
                            <a class="icon-btn" href="<?= e(url("/admin/$type/{$r['id']}/edit")) ?>" title="Sửa"><?= icon('pencil') ?></a>
                            <form method="post" action="<?= e(url("/admin/$type/{$r['id']}/duplicate")) ?>">
                                <?= csrf_field() ?>
                                <button class="icon-btn" type="submit" title="Nhân bản"><?= icon('copy') ?></button>
                            </form>
                            <form method="post" action="<?= e(url("/admin/$type/{$r['id']}/delete")) ?>" data-confirm="Xoá “<?= e($r['title']) ?>”? Thao tác này không thể hoàn tác.">
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
<?php /* Không có dòng nào (hoặc không khớp từ khoá tìm kiếm) => trạng thái trống + nút tạo mới. */ ?>
        <div class="empty-state">
            <?= icon($t['icon'], 'icon empty-icon', 1.5) ?>
            <p>Không có <?= e($t['singular']) ?> nào<?= $filters['q'] ? ' khớp với “' . e($filters['q']) . '”' : '' ?>.</p>
            <a class="btn btn-primary" href="<?= e(url("/admin/$type/create")) ?>"><?= icon('plus') ?> <?= e($t['new']) ?></a>
        </div>
    <?php endif; ?>
</div>

<?php /* Phân trang: link giữ nguyên bộ lọc hiện tại (partial pagination dùng query_url). */ ?>
<?= partial('pagination', ['p' => $pagination]) ?>
