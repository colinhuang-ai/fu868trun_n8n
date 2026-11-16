<?php
/**
 * PARTIAL: THẺ (CARD) BÀI VIẾT / SẢN PHẨM
 * ------------------------------------------------------------------
 * Dùng ở: trang chủ (section Sản phẩm và Tin tức), trang danh sách /bai-viet.
 * Gọi:    partial('post-card', ['post' => $p, 'index' => $i, 'show_date' => true])
 *
 * Biến truyền vào:
 * @var array $post       Một dòng bảng posts
 * @var int   $index      Vị trí của thẻ trong danh sách (0, 1, 2...) – dùng để chọn bộ màu minh hoạ
 * @var bool  $show_date  (tuỳ chọn) true => hiện ngày đăng dưới mô tả
 *
 * Cấu trúc thẻ: vùng ảnh (ảnh đại diện HOẶC con tôm SVG + nhãn) -> tiêu đề -> mô tả -> (ngày) -> "Xem chi tiết".
 */

// Chọn bộ màu theo vị trí: $index % số theme => xoay vòng 4 bộ màu (navy, xanh nhạt, hồng, vàng)
// giống 4 card sản phẩm trong prototype. Các theme định nghĩa ở shrimp_themes() (app/content.php).
$themes = shrimp_themes();
$theme = $themes[$index % count($themes)];

// Nhãn nhỏ trên ảnh: ưu tiên "Nhãn trên ảnh" admin nhập (cột label, vd. "Tươi sống"),
// nếu trống thì dùng tên chuyên mục.
$cats = post_categories();
$badge = $post['label'] ?: ($cats[$post['category']] ?? '');
?>
<article class="product-card">
<?php
/* Vùng ảnh là một link tới bài viết, nền tô màu theo theme.
 * tabindex="-1" + aria-hidden: link này trùng đích với link ở tiêu đề, nên bỏ khỏi thứ tự phím Tab
 * và trình đọc màn hình để tránh đọc lặp.
 * - Có ảnh đại diện => <img> (loading="lazy": chỉ tải ảnh khi sắp cuộn tới).
 * - Không có ảnh    => vẽ con tôm SVG bằng partial shrimp với bộ màu đã chọn.
 */
?>
    <a class="product-media" href="<?= e(url('/bai-viet/' . $post['slug'])) ?>" style="background:<?= e($theme['bg']) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($post['featured_image']): ?>
            <img src="<?= e(url($post['featured_image'])) ?>" alt="" loading="lazy">
        <?php else: ?>
            <?= partial('shrimp', ['theme' => $theme]) ?>
        <?php endif; ?>
        <?php if ($badge): ?><span class="chip"><?= e($badge) ?></span><?php endif; ?>
    </a>
<?php
/* Phần chữ của thẻ:
 * - Mô tả: dùng "Tóm tắt" (excerpt) nếu có, nếu không thì tự cắt 110 ký tự đầu của nội dung
 *   (str_excerpt() bỏ thẻ HTML rồi cắt, thêm dấu "…").
 * - Ngày đăng chỉ hiện khi truyền show_date (dùng !empty để không lỗi khi biến không tồn tại).
 */
?>
    <div class="product-body">
        <h3><a href="<?= e(url('/bai-viet/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
        <p><?= e($post['excerpt'] ?: str_excerpt($post['content'], 110)) ?></p>
        <?php if (!empty($show_date)): ?>
            <p class="card-meta"><?= icon('calendar') ?> <?= e(format_date($post['published_at'])) ?></p>
        <?php endif; ?>
        <a class="link-arrow" href="<?= e(url('/bai-viet/' . $post['slug'])) ?>">Xem chi tiết <?= icon('arrow-right') ?></a>
    </div>
</article>
