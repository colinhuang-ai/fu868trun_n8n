<?php
/**
 * VIEW: CÀI ĐẶT WEBSITE (URL: /admin/settings)
 * ------------------------------------------------------------------
 * Controller (app/admin.php):
 *   - admin_settings()      : GET  – đọc toàn bộ bảng settings, hiện form
 *   - admin_settings_save() : POST – lưu từng ô vào bảng settings (thêm mới hoặc ghi đè theo key)
 *
 * Biến truyền vào:
 * @var array $groups  Danh sách ô cần hiện, nhóm theo tiêu đề – lấy từ setting_fields() (app/admin.php):
 *                     ['Thông tin chung' => ['site_name' => ['Tên website', 'text'], ...], ...]
 *                     Mỗi ô: key => [nhãn, kiểu ô 'text' | 'textarea']
 * @var array $values  Giá trị hiện tại: ['site_name' => 'CASES', ...]
 *
 * Muốn thêm một cài đặt mới: chỉ cần khai báo trong setting_fields(), form này tự hiện ô,
 * rồi ở view dùng setting('key_moi') để đọc ra.
 */
?>
<form method="post" action="<?= e(url('/admin/settings')) ?>" class="settings-form">
    <?= csrf_field() ?>
<?php
/* Mỗi nhóm là một panel; bên trong là lưới 2 cột.
 * - Ô 'textarea' chiếm cả 2 cột (class span-2). Riêng 'hero_title' cao 4 dòng vì admin ngắt dòng tiêu đề hero bằng Enter.
 * - id="s-{key}" khớp với for của <label> để bấm vào nhãn thì con trỏ nhảy vào ô.
 * - Giá trị luôn qua e() để ký tự đặc biệt (dấu ", <, >) không làm vỡ HTML.
 */
?>
    <?php foreach ($groups as $group => $fields): ?>
        <section class="panel panel-pad">
            <h2 class="panel-title"><?= e($group) ?></h2>
            <div class="settings-grid">
                <?php foreach ($fields as $key => [$label, $kind]): ?>
                    <div class="form-field<?= $kind === 'textarea' ? ' span-2' : '' ?>">
                        <label for="s-<?= e($key) ?>"><?= e($label) ?></label>
                        <?php if ($kind === 'textarea'): ?>
                            <textarea id="s-<?= e($key) ?>" name="<?= e($key) ?>" rows="<?= $key === 'hero_title' ? 4 : 3 ?>"><?= e($values[$key] ?? '') ?></textarea>
                        <?php else: ?>
                            <input id="s-<?= e($key) ?>" type="text" name="<?= e($key) ?>" value="<?= e($values[$key] ?? '') ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
<?php /* Nút lưu dính ở đáy màn hình khi cuộn (position: sticky trong admin.css). */ ?>
    <div class="sticky-actions">
        <button class="btn btn-primary" type="submit">Lưu cài đặt</button>
    </div>
</form>
