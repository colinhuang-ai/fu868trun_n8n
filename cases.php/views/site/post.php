<?php
/**
 * VIEW: CHI TIẾT BÀI VIẾT (URL: /bai-viet/{slug})
 * ------------------------------------------------------------------
 * Controller gọi: site_post_show($slug) trong app/site.php, layout 'site'.
 *
 * Biến truyền vào:
 * @var array $post     Bài viết (một dòng bảng posts: title, slug, excerpt, content, featured_image,
 *                      category, label, published_at...)
 * @var array $related  Tối đa 3 bài cùng chuyên mục (khác bài hiện tại) => khung "Cùng chuyên mục"
 * (Ngoài ra controller truyền $title, $meta_description, $og_image, $is_preview, $edit_url cho layout.)
 *
 * Bố cục: banner (page-hero) -> 2 cột: nội dung bài (trái) | cột phụ (phải): khung kêu gọi báo giá + bài liên quan.
 */

// Tên các chuyên mục để hiện nhãn (vd. 'san-pham' => 'Sản phẩm').
$cats = post_categories();
$catLabel = $cats[$post['category']] ?? 'Bài viết';

// Bài thuộc chuyên mục Sản phẩm => đổi câu chữ ở khung kêu gọi báo giá.
$isProduct = $post['category'] === 'san-pham';
?>
<?php /* Banner: tiêu đề bài, breadcrumb "Trang chủ / Bài viết / Chuyên mục", dòng tóm tắt (excerpt). */ ?>
<?= partial('page-hero', [
    'heading' => $post['title'],
    'crumbs'  => [['Bài viết', '/bai-viet'], [$catLabel, '/bai-viet?cat=' . $post['category']]],
    'lead'    => $post['excerpt'],
]) ?>

<section class="section section-tight">
    <div class="container article-layout">
<?php
/* --- CỘT NỘI DUNG ---
 * - Dòng meta: nhãn (label của bài, không có thì tên chuyên mục) + ngày đăng.
 * - Ảnh bìa: chỉ hiện nếu bài có ảnh đại diện; alt = tiêu đề (hỗ trợ SEO/trình đọc màn hình).
 * - Nội dung: in $post['content'] KHÔNG qua e(), vì đây là HTML do quản trị viên soạn bằng trình soạn thảo
 *   (cần giữ thẻ <h2>, <ul>, <img>...). HTML này đã được lọc bỏ <script>, thuộc tính on*=, javascript:
 *   bằng clean_html() (app/content.php) khi lưu. Class "prose" trong CSS định dạng chữ cho đẹp.
 */
?>
        <article class="article">
            <p class="article-meta">
                <span class="chip chip-soft"><?= e($post['label'] ?: $catLabel) ?></span>
                <?php if ($post['published_at']): ?><span><?= icon('calendar') ?> <?= e(format_date($post['published_at'])) ?></span><?php endif; ?>
            </p>
            <?php if ($post['featured_image']): ?>
                <img class="article-cover" src="<?= e(url($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>">
            <?php endif; ?>
            <div class="prose"><?= $post['content'] /* HTML do quản trị viên soạn, đã lọc khi lưu */ ?></div>
        </article>

<?php
/* --- CỘT PHỤ (dính khi cuộn trên desktop) ---
 * 1. Khung kêu gọi: nút "Nhận báo giá" dẫn về form ở trang chủ (/#lien-he).
 * 2. "Cùng chuyên mục": danh sách link bài liên quan (chỉ hiện khi $related có dữ liệu).
 */
?>
        <aside class="article-aside">
            <div class="aside-card aside-cta">
                <h3><?= $isProduct ? 'Nhận báo giá sản phẩm này' : 'Cần tư vấn nguồn tôm?' ?></h3>
                <p>Đội ngũ CASES tư vấn size, số lượng và lịch giao phù hợp với bạn.</p>
                <a class="btn btn-primary" href="<?= e(url('/#lien-he')) ?>">Nhận báo giá <?= icon('arrow-right') ?></a>
            </div>
            <?php if ($related): ?>
                <div class="aside-card">
                    <h3>Cùng chuyên mục</h3>
                    <ul class="aside-list">
                        <?php foreach ($related as $r): ?>
                            <li><a href="<?= e(url('/bai-viet/' . $r['slug'])) ?>"><?= e($r['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
