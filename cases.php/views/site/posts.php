<?php
/**
 * VIEW: DANH SÁCH BÀI VIẾT (URL: /bai-viet và /bai-viet?cat=san-pham)
 * ------------------------------------------------------------------
 * Controller gọi: site_posts_index() trong app/site.php, layout 'site'.
 *
 * Biến truyền vào:
 * @var array  $posts       Các bài viết của trang hiện tại (đã lọc theo chuyên mục nếu có, đã phân trang)
 * @var array  $pagination  Kết quả paginate(): ['total','page','pages','per','offset']
 * @var string $cat         Chuyên mục đang lọc ('' = tất cả), vd. 'san-pham'
 * @var array  $categories  Danh sách chuyên mục ['san-pham' => 'Sản phẩm', ...] (post_categories())
 *
 * Partial dùng: page-hero (banner đầu trang), post-card (thẻ bài viết), pagination (phân trang).
 */
?>
<?php
/* Banner đầu trang + breadcrumb.
 * - Có lọc chuyên mục: tiêu đề = tên chuyên mục, breadcrumb "Trang chủ / Bài viết / Tên chuyên mục".
 * - Không lọc: tiêu đề "Tin tức & bài viết", breadcrumb "Trang chủ / Bài viết".
 * Mỗi phần tử crumbs là [nhãn, link]; link = null nghĩa là mục hiện tại (không bấm được).
 */
?>
<?= partial('page-hero', [
    'heading' => $cat ? $categories[$cat] : 'Tin tức & bài viết',
    'crumbs'  => $cat ? [['Bài viết', '/bai-viet'], [$categories[$cat], null]] : [['Bài viết', null]],
    'lead'    => 'Sản phẩm, tin tức và kiến thức từ CASES.',
]) ?>

<section class="section section-tight">
    <div class="container">
<?php /* Thanh tab lọc chuyên mục: "Tất cả" + từng chuyên mục. Tab đang chọn có class "active". Lọc bằng query string ?cat=... */ ?>
        <nav class="tabs" aria-label="Chuyên mục">
            <a href="<?= e(url('/bai-viet')) ?>" class="<?= $cat === '' ? 'active' : '' ?>">Tất cả</a>
            <?php foreach ($categories as $key => $label): ?>
                <a href="<?= e(url('/bai-viet?cat=' . $key)) ?>" class="<?= $cat === $key ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>

<?php
/* Lưới 3 cột các thẻ bài viết. 'index' => $i để xoay vòng bộ màu minh hoạ con tôm (khi bài không có ảnh),
 * 'show_date' => true để hiện ngày đăng trên thẻ.
 * Bên dưới là phân trang (partial tự ẩn nếu chỉ có 1 trang).
 * Không có bài nào => hiện ghi chú trống.
 */
?>
        <?php if ($posts): ?>
            <div class="product-grid product-grid-3">
                <?php foreach ($posts as $i => $p): ?>
                    <?= partial('post-card', ['post' => $p, 'index' => $i, 'show_date' => true]) ?>
                <?php endforeach; ?>
            </div>
            <?= partial('pagination', ['p' => $pagination]) ?>
        <?php else: ?>
            <p class="empty-note">Chưa có bài viết nào trong chuyên mục này.</p>
        <?php endif; ?>
    </div>
</section>
