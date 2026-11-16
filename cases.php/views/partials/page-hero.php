<?php
/**
 * PARTIAL: BANNER ĐẦU TRANG CON (nền navy + breadcrumb + tiêu đề + sóng)
 * ------------------------------------------------------------------
 * Dùng ở: views/site/posts.php, post.php, page.php (mọi trang trừ trang chủ).
 * Gọi:    partial('page-hero', ['heading' => ..., 'crumbs' => [...], 'lead' => ...])
 *
 * Biến truyền vào:
 * @var string      $heading  Tiêu đề lớn (thẻ <h1> của trang)
 * @var array       $crumbs   Breadcrumb sau "Trang chủ". Mỗi phần tử là [nhãn, link]:
 *                            - link là đường dẫn (vd. '/bai-viet') => hiện thành link bấm được
 *                            - link = null => mục hiện tại, hiện chữ thường + aria-current
 *                            Ví dụ: [['Bài viết', '/bai-viet'], ['Sản phẩm', null]]
 *                            => "Trang chủ / Bài viết / Sản phẩm"
 * @var string|null $lead     Dòng mô tả dưới tiêu đề (tuỳ chọn, rỗng thì không hiện)
 */
?>
<section class="page-hero">
    <div class="container">
<?php /* Breadcrumb ("đường dẫn vụn bánh mì"): luôn bắt đầu bằng link "Trang chủ", các mục cách nhau bởi "/". */ ?>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(url('/')) ?>">Trang chủ</a>
            <?php foreach ($crumbs as [$label, $href]): ?>
                <span>/</span>
                <?php if ($href): ?><a href="<?= e(url($href)) ?>"><?= e($label) ?></a><?php else: ?><span aria-current="page"><?= e($label) ?></span><?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <h1><?= e($heading) ?></h1>
        <?php if (!empty($lead)): ?><p class="page-hero-lead"><?= e($lead) ?></p><?php endif; ?>
    </div>
<?php
/* Hai lớp sóng ở đáy banner (giống hero trang chủ nhưng thấp hơn): lớp xanh nhạt phía sau, lớp trắng phía trước.
 * preserveAspectRatio="none" cho phép kéo giãn ngang theo bề rộng màn hình.
 */
?>
    <svg class="page-hero-wave" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 40 C 240 0 480 0 720 34 S 1200 80 1440 26 V80 H0Z" fill="#2f6496"/>
        <path d="M0 62 C 300 30 560 30 800 56 S 1250 84 1440 50 V80 H0Z" fill="#fff"/>
    </svg>
</section>
