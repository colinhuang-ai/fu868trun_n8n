<?php
/**
 * VIEW: TRANG TĨNH (URL: /{slug}, vd. /gioi-thieu, /lien-he)
 * ------------------------------------------------------------------
 * Controller gọi: site_page_show($slug) trong app/site.php, layout 'site'.
 *
 * Biến truyền vào:
 * @var array $page  Trang (một dòng bảng pages: title, slug, excerpt, content, featured_image, template...)
 * (Ngoài ra controller truyền $title, $meta_description, $og_image, $is_preview, $edit_url cho layout.)
 *
 * Trang có 2 mẫu (cột "template", chọn trong Admin → Trang → Thuộc tính → Mẫu trang):
 *   - 'default' : nội dung một cột ở giữa, có ảnh bìa nếu có.
 *   - 'contact' : 2 cột – nội dung + thông tin liên hệ (bên trái), form báo giá (bên phải).
 * Danh sách mẫu định nghĩa ở page_templates() trong app/content.php.
 */
?>
<?php /* Banner: tiêu đề trang, breadcrumb "Trang chủ / Tên trang", tóm tắt (excerpt). */ ?>
<?= partial('page-hero', [
    'heading' => $page['title'],
    'crumbs'  => [[$page['title'], null]],
    'lead'    => $page['excerpt'],
]) ?>

<section class="section section-tight">
    <div class="container">
        <?php if ($page['template'] === 'contact'): ?>
<?php
/* --- MẪU "LIÊN HỆ" ---
 * Cột trái: nội dung trang (HTML do admin soạn, không escape – đã lọc khi lưu) + địa chỉ/điện thoại/email
 *           lấy từ Cài đặt (setting company_*).
 * Cột phải: form báo giá (partial contact-form) với 'back' => 'page' => sau khi gửi sẽ quay về
 *           "/lien-he#bao-gia" (id="bao-gia" đặt trên thẻ chứa form để cuộn tới đúng chỗ).
 */
?>
            <div class="contact-grid contact-grid-page">
                <div>
                    <div class="prose"><?= $page['content'] ?></div>
                    <ul class="contact-info">
                        <li><span><?= icon('map-pin') ?></span><?= e(setting('company_address')) ?></li>
                        <li><span><?= icon('phone') ?></span><?= e(setting('company_phone')) ?></li>
                        <li><span><?= icon('mail') ?></span><?= e(setting('company_email')) ?></li>
                    </ul>
                </div>
                <div class="contact-card" id="bao-gia">
                    <?= partial('contact-form', ['back' => 'page']) ?>
                </div>
            </div>
        <?php else: ?>
<?php
/* --- MẪU "MẶC ĐỊNH" ---
 * Một cột hẹp (article-narrow) cho dễ đọc: ảnh bìa (nếu có) + nội dung HTML.
 * Nội dung in không qua e() vì là HTML do quản trị viên soạn, đã lọc bằng clean_html() khi lưu.
 */
?>
            <article class="article article-narrow">
                <?php if ($page['featured_image']): ?>
                    <img class="article-cover" src="<?= e(url($page['featured_image'])) ?>" alt="<?= e($page['title']) ?>">
                <?php endif; ?>
                <div class="prose"><?= $page['content'] /* HTML do quản trị viên soạn, đã lọc khi lưu */ ?></div>
            </article>
        <?php endif; ?>
    </div>
</section>
