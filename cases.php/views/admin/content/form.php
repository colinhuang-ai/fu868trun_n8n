<?php
/**
 * VIEW: FORM THÊM / SỬA TRANG HOẶC BÀI VIẾT
 * ------------------------------------------------------------------
 * URL: /admin/{type}/create (thêm mới) và /admin/{type}/{id}/edit (sửa), với {type} = pages | posts.
 * Controller gọi: admin_content_form() trong app/admin.php (qua admin_content_create / admin_content_edit,
 * và được gọi lại từ admin_content_save() khi lưu bị lỗi để hiện lỗi + giữ dữ liệu đã nhập).
 * Layout 'admin' với use_editor = true (nạp thư viện soạn thảo Quill).
 *
 * Biến truyền vào:
 * @var string $type    'pages' | 'posts'
 * @var array  $t       Định nghĩa loại nội dung (content_types()): singular, plural, new...
 * @var array  $item    Dữ liệu đang sửa (dòng trong DB), hoặc bản trống admin_content_blank() khi thêm mới,
 *                      hoặc dữ liệu vừa nhập khi lưu bị lỗi.
 * @var array  $errors  Lỗi theo từng ô, vd. ['title' => 'Vui lòng nhập tiêu đề.']
 * @var bool   $is_new  true = đang thêm mới (chưa có id)
 *
 * Gửi form:
 *   - Thêm mới: POST /admin/{type}       => admin_content_store()  => admin_content_save()
 *   - Sửa:      POST /admin/{type}/{id}  => admin_content_update() => admin_content_save()
 *   enctype="multipart/form-data" là BẮT BUỘC để gửi được file ảnh đại diện.
 *
 * Bố cục 2 cột: cột chính (tiêu đề, slug, nội dung, tóm tắt, SEO) | cột phụ (xuất bản, thuộc tính, ảnh, xoá).
 */

// true = đang sửa Bài viết; false = Trang. Một số ô chỉ có ở một loại.
$isPosts = $type === 'posts';

// Đích của form: thêm mới gửi vào danh sách, sửa gửi vào URL có id.
$action = $is_new ? "/admin/$type" : "/admin/$type/{$item['id']}";

// Hai hàm nhỏ dùng lại cho mọi ô:
//   $err('ten_o') => in <small> chứa câu lỗi của ô đó (nếu có)
//   $cls('ten_o') => trả ' has-error' để thêm class viền đỏ
$err = fn (string $k) => isset($errors[$k]) ? '<small class="field-error">' . e($errors[$k]) . '</small>' : '';
$cls = fn (string $k) => isset($errors[$k]) ? ' has-error' : '';

// Ô <input type="datetime-local"> cần định dạng "2026-11-05T09:30" (có chữ T), khác định dạng lưu DB "2026-11-05 09:30:00".
$publishedLocal = $item['published_at'] ? date('Y-m-d\TH:i', strtotime($item['published_at'])) : '';

// Tiền tố đường dẫn hiện trước ô slug: bài viết nằm dưới /bai-viet/, trang nằm ngay gốc /.
$prefix = $isPosts ? '/bai-viet/' : '/';
?>
<?php /* Link quay lại danh sách (mb_strtolower: "Trang" -> "trang", xử lý đúng tiếng Việt). */ ?>
<p class="back-link"><a href="<?= e(url("/admin/$type")) ?>"><?= icon('arrow-left') ?> Danh sách <?= e(mb_strtolower($t['plural'])) ?></a></p>

<?php /* data-editor-form: admin.js tìm form này để gắn trình soạn thảo, cảnh báo khi rời trang chưa lưu. */ ?>
<form class="editor-form" method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" data-editor-form>
    <?= csrf_field() ?>

<?php /* ================= CỘT CHÍNH ================= */ ?>
    <div class="editor-main">
        <div class="panel panel-pad">
<?php /* Tiêu đề (bắt buộc, tối đa 200 ký tự). data-slug-source: admin.js đọc ô này để tự sinh slug khi gõ. */ ?>
            <div class="form-field<?= $cls('title') ?>">
                <label for="f-title">Tiêu đề</label>
                <input id="f-title" class="input-title" type="text" name="title" value="<?= e($item['title']) ?>" required maxlength="200" data-slug-source placeholder="Nhập tiêu đề…">
                <?= $err('title') ?>
            </div>
<?php
/* Đường dẫn (slug) – phần cuối URL, vd. "tom-su-loai-1".
 * - Hiện kèm tiền tố (vd. "/bai-viet/") để admin thấy URL đầy đủ.
 * - data-slug-target + data-auto: khi thêm mới (slug trống, data-auto="1") admin.js tự điền slug theo tiêu đề
 *   (bỏ dấu tiếng Việt); khi admin tự sửa slug thì ngừng tự điền. Bài đã có slug thì data-auto="0" (không tự đổi
 *   để link cũ không bị hỏng).
 * - Để trống: server tự tạo từ tiêu đề; nếu trùng sẽ tự thêm -2, -3...
 */
?>
            <div class="form-field<?= $cls('slug') ?>">
                <label for="f-slug">Đường dẫn</label>
                <div class="input-prefix">
                    <span><?= e(url($prefix)) ?></span>
                    <input id="f-slug" type="text" name="slug" value="<?= e($item['slug']) ?>" data-slug-target data-auto="<?= $item['slug'] === '' ? '1' : '0' ?>" placeholder="tu-dong-tao-tu-tieu-de">
                </div>
                <?= $err('slug') ?>
            </div>
<?php
/* Nội dung – trình soạn thảo Quill.
 * - <textarea name="content" hidden>: chứa HTML thật sẽ được gửi lên server. Giá trị in qua e() để HTML
 *   nằm an toàn trong textarea (không bị trình duyệt hiểu thành thẻ thật).
 * - <div id="editor">: vùng soạn thảo nhìn thấy. admin.js nạp HTML từ textarea vào editor khi mở trang,
 *   và khi bấm Lưu thì chép HTML từ editor ngược lại vào textarea trước khi gửi.
 * - data-upload-url: địa chỉ admin.js gửi ảnh lên khi bấm nút chèn ảnh (POST /admin/upload => admin_upload()).
 */
?>
            <div class="form-field">
                <label>Nội dung</label>
                <textarea name="content" id="f-content" hidden><?= e($item['content']) ?></textarea>
                <div class="editor" id="editor" data-upload-url="<?= e(url('/admin/upload')) ?>"></div>
                <small class="hint">Ảnh chèn vào nội dung sẽ được tải lên máy chủ (JPG, PNG, WEBP, GIF ≤ 5MB).</small>
            </div>
<?php /* Tóm tắt (tối đa 500 ký tự): hiện trên card ở trang chủ/danh sách và dưới tiêu đề trang chi tiết. */ ?>
            <div class="form-field">
                <label for="f-excerpt">Tóm tắt</label>
                <textarea id="f-excerpt" name="excerpt" rows="3" maxlength="500" placeholder="<?= $isPosts ? 'Hiển thị ở thẻ bài viết/sản phẩm trên trang chủ' : 'Hiển thị dưới tiêu đề trang' ?>"><?= e($item['excerpt']) ?></textarea>
            </div>
        </div>

<?php
/* Khối SEO (thu gọn được bằng thẻ <details>; tự mở sẵn nếu đã có dữ liệu SEO).
 * Để trống thì website tự dùng tiêu đề và tóm tắt (xem site_post_show / site_page_show).
 */
?>
        <details class="panel panel-pad seo-box" <?= ($item['meta_title'] || $item['meta_description']) ? 'open' : '' ?>>
            <summary>SEO <span class="muted">– tuỳ chọn</span></summary>
            <div class="form-field">
                <label for="f-meta-title">Tiêu đề SEO</label>
                <input id="f-meta-title" type="text" name="meta_title" value="<?= e($item['meta_title']) ?>" maxlength="200" placeholder="Mặc định dùng tiêu đề">
            </div>
            <div class="form-field">
                <label for="f-meta-desc">Mô tả SEO</label>
                <textarea id="f-meta-desc" name="meta_description" rows="2" maxlength="300" placeholder="Mặc định dùng phần tóm tắt"><?= e($item['meta_description']) ?></textarea>
            </div>
        </details>
    </div>

<?php /* ================= CỘT PHỤ ================= */ ?>
    <aside class="editor-side">
<?php
/* --- Panel "Xuất bản" ---
 * - Trạng thái: Bản nháp (chỉ admin xem) / Đã xuất bản.
 * - Ngày đăng: để trống => server lấy thời điểm xuất bản; chọn ngày tương lai => hẹn giờ
 *   (bài chỉ hiện khi tới giờ, xem published_sql() trong app/content.php).
 * - Thời gian tạo/cập nhật: chỉ hiện khi sửa.
 * - 2 nút submit cùng name="_after" nhưng value khác: "stay" = lưu xong ở lại trang sửa,
 *   "close" = lưu xong về danh sách (server đọc input('_after') để quyết định redirect).
 * - Nút xem: mở trang ngoài website ở tab mới (bản nháp vẫn xem được vì admin đã đăng nhập).
 */
?>
        <div class="panel panel-pad">
            <h3 class="panel-title">Xuất bản</h3>
            <div class="form-field">
                <label for="f-status">Trạng thái</label>
                <select id="f-status" name="status">
                    <?php foreach (statuses() as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= $item['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field<?= $cls('published_at') ?>">
                <label for="f-pub">Ngày đăng</label>
                <input id="f-pub" type="datetime-local" name="published_at" value="<?= e($publishedLocal) ?>">
                <small class="hint">Để trống = thời điểm xuất bản. Chọn ngày tương lai để hẹn giờ.</small>
                <?= $err('published_at') ?>
            </div>
            <?php if (!$is_new): ?>
                <p class="meta-line">Tạo: <?= e(format_date($item['created_at'], 'd/m/Y H:i')) ?><br>Cập nhật: <?= e(format_date($item['updated_at'], 'd/m/Y H:i')) ?></p>
            <?php endif; ?>
            <div class="side-actions">
                <button class="btn btn-primary btn-block" type="submit" name="_after" value="stay">Lưu</button>
                <button class="btn btn-light btn-block" type="submit" name="_after" value="close">Lưu &amp; đóng</button>
                <?php if (!$is_new): ?>
                    <a class="btn btn-ghost btn-block" href="<?= e(url(content_public_url($type, $item))) ?>" target="_blank" rel="noopener"><?= icon('eye') ?> Xem <?= $item['status'] === 'draft' ? 'bản nháp' : 'trên website' ?></a>
                <?php endif; ?>
            </div>
        </div>

<?php /* --- Panel "Thuộc tính": các ô khác nhau giữa Bài viết và Trang --- */ ?>
        <div class="panel panel-pad">
            <h3 class="panel-title">Thuộc tính</h3>
            <?php if ($isPosts): ?>
<?php
/* BÀI VIẾT:
 * - Chuyên mục: chọn "Sản phẩm" thì bài hiện ở mục Sản phẩm trên trang chủ (lấy 4 bài theo "Thứ tự").
 * - Nhãn trên ảnh: chữ nhỏ trên ảnh card (vd. "Tươi sống"); trống thì dùng tên chuyên mục.
 * - Thứ tự (sort_order): số nhỏ đứng trước khi sắp sản phẩm trên trang chủ.
 * - Nổi bật (is_featured): checkbox; bài nổi bật được ưu tiên ở mục "Cập nhật từ CASES".
 *   Checkbox không tích thì trình duyệt không gửi gì => server hiểu là 0.
 */
?>
                <div class="form-field">
                    <label for="f-cat">Chuyên mục</label>
                    <select id="f-cat" name="category">
                        <?php foreach (post_categories() as $k => $v): ?>
                            <option value="<?= e($k) ?>" <?= $item['category'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="hint">Bài thuộc “Sản phẩm” hiển thị ở mục Sản phẩm trên trang chủ (tối đa 4).</small>
                </div>
                <div class="form-field">
                    <label for="f-label">Nhãn trên ảnh</label>
                    <input id="f-label" type="text" name="label" value="<?= e($item['label']) ?>" maxlength="40" placeholder="VD: Tươi sống, Cấp đông">
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="f-sort">Thứ tự</label>
                        <input id="f-sort" type="number" name="sort_order" value="<?= (int) $item['sort_order'] ?>">
                    </div>
                    <label class="check">
                        <input type="checkbox" name="is_featured" value="1" <?= $item['is_featured'] ? 'checked' : '' ?>>
                        <span>Nổi bật</span>
                    </label>
                </div>
            <?php else: ?>
<?php
/* TRANG:
 * - Mẫu trang: "Mặc định" hoặc "Liên hệ (kèm form báo giá)" – quyết định giao diện ở views/site/page.php.
 * - Hiện trên menu: tích để trang xuất hiện trong menu chính của website (menu_pages()).
 * - Thứ tự (menu_order): số nhỏ đứng trước trong menu.
 */
?>
                <div class="form-field">
                    <label for="f-tpl">Mẫu trang</label>
                    <select id="f-tpl" name="template">
                        <?php foreach (page_templates() as $k => $v): ?>
                            <option value="<?= e($k) ?>" <?= $item['template'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label class="check">
                        <input type="checkbox" name="show_in_menu" value="1" <?= $item['show_in_menu'] ? 'checked' : '' ?>>
                        <span>Hiện trên menu</span>
                    </label>
                    <div class="form-field">
                        <label for="f-order">Thứ tự</label>
                        <input id="f-order" type="number" name="menu_order" value="<?= (int) $item['menu_order'] ?>">
                    </div>
                </div>
            <?php endif; ?>
        </div>

<?php
/* --- Panel "Ảnh đại diện" ---
 * - Khung xem trước: có ảnh thì hiện <img>, không thì hiện chữ "Chưa có ảnh" (CSS ẩn/hiện qua class has-image).
 * - Nút "Chọn ảnh…" thực chất là <input type="file"> trong suốt phủ lên nút; khi chọn file,
 *   admin.js hiện ảnh xem trước ngay (chưa upload, chỉ upload khi bấm Lưu cùng form).
 * - accept: chỉ cho chọn các loại ảnh server chấp nhận (server vẫn kiểm tra lại MIME thật bằng finfo).
 * - "Gỡ ảnh hiện tại": checkbox remove_image, chỉ hiện khi đang có ảnh.
 * - data-image-field: admin.js tìm khối này để gắn chức năng xem trước.
 */
?>
        <div class="panel panel-pad">
            <h3 class="panel-title">Ảnh đại diện</h3>
            <div class="image-field<?= $cls('featured_image') ?>" data-image-field>
                <div class="image-preview<?= $item['featured_image'] ? ' has-image' : '' ?>">
                    <?php if ($item['featured_image']): ?>
                        <img src="<?= e(url($item['featured_image'])) ?>" alt="">
                    <?php endif; ?>
                    <span class="image-empty"><?= icon('image', 'icon', 1.5) ?>Chưa có ảnh</span>
                </div>
                <label class="btn btn-light btn-block file-btn">
                    <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp,image/gif">
                    Chọn ảnh…
                </label>
                <?php if ($item['featured_image']): ?>
                    <label class="check check-sm"><input type="checkbox" name="remove_image" value="1"><span>Gỡ ảnh hiện tại</span></label>
                <?php endif; ?>
                <?= $err('featured_image') ?>
                <?php if ($isPosts): ?><small class="hint">Không có ảnh sẽ dùng hình minh hoạ con tôm.</small><?php endif; ?>
            </div>
        </div>

<?php
/* Nút xoá (chỉ khi sửa). Không thể lồng <form> trong <form>, nên nút này dùng thuộc tính form="delete-form"
 * để gửi FORM XOÁ RIÊNG nằm bên ngoài (khai báo ở cuối file), không phải form lưu.
 */
?>
        <?php if (!$is_new): ?>
            <button class="btn btn-danger-ghost btn-block" type="submit" form="delete-form"><?= icon('trash') ?> Xoá <?= e($t['singular']) ?></button>
        <?php endif; ?>
    </aside>
</form>

<?php
/* Form xoá ẩn: POST /admin/{type}/{id}/delete => admin_content_delete().
 * data-confirm: admin.js hỏi "Xoá ...? Thao tác này không thể hoàn tác." trước khi gửi.
 */
?>
<?php if (!$is_new): ?>
    <form id="delete-form" method="post" action="<?= e(url("/admin/$type/{$item['id']}/delete")) ?>" data-confirm="Xoá “<?= e($item['title']) ?>”? Thao tác này không thể hoàn tác." hidden>
        <?= csrf_field() ?>
    </form>
<?php endif; ?>
