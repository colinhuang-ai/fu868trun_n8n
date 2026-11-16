<?php
/**
 * PARTIAL: THANH PHÂN TRANG (‹ 1 2 3 ›)
 * ------------------------------------------------------------------
 * Dùng chung cho website (/bai-viet) và admin (danh sách Trang, Bài viết, Yêu cầu báo giá).
 * Gọi: partial('pagination', ['p' => $pagination])
 *
 * @var array $p  Kết quả hàm paginate() (app/helpers.php):
 *                ['total' => tổng số dòng, 'page' => trang hiện tại, 'pages' => tổng số trang,
 *                 'per' => số dòng/trang, 'offset' => vị trí bắt đầu trong SQL]
 *
 * Link trang dùng query_url(['page' => n]): GIỮ NGUYÊN các tham số lọc đang có trên URL
 * (vd. ?q=tom&status=draft) và chỉ đổi số trang.
 */

// Chỉ có 1 trang => không cần hiện gì. "return" trong file được include sẽ dừng file này ngay.
if ($p['pages'] <= 1) {
    return;
}

// Chỉ hiện tối đa 5 số trang quanh trang hiện tại (hiện tại ± 2), không vượt quá 1..tổng số trang.
$start = max(1, $p['page'] - 2);
$end = min($p['pages'], $p['page'] + 2);
?>
<nav class="pagination" aria-label="Phân trang">
<?php /* Nút "trang trước" – chỉ hiện khi không ở trang 1. */ ?>
    <?php if ($p['page'] > 1): ?>
        <a href="<?= e(query_url(['page' => $p['page'] - 1])) ?>" aria-label="Trang trước">‹</a>
    <?php endif; ?>
<?php /* Các số trang: trang hiện tại là <span> (không bấm được, aria-current), các trang khác là link. */ ?>
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i === $p['page']): ?>
            <span class="current" aria-current="page"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= e(query_url(['page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
<?php /* Nút "trang sau" – chỉ hiện khi chưa ở trang cuối. */ ?>
    <?php if ($p['page'] < $p['pages']): ?>
        <a href="<?= e(query_url(['page' => $p['page'] + 1])) ?>" aria-label="Trang sau">›</a>
    <?php endif; ?>
</nav>
