<?php
/**
 * PARTIAL: Minh hoạ con tôm bằng SVG.
 * ------------------------------------------------------------------
 * Dùng ở: - Hero trang chủ (views/site/home.php)
 *         - Card bài viết/sản phẩm không có ảnh đại diện (views/partials/post-card.php)
 * Gọi bằng: partial('shrimp', ['theme' => [...], 'class' => '...'])
 *
 * Biến truyền vào:
 * @var array $theme  Bảng màu của hình, gồm các khoá:
 *                    - 'circle' : màu vòng tròn nền phía sau con tôm (null = không vẽ)
 *                    - 'ring'   : màu viền tròn mảnh bao ngoài (null = không vẽ)
 *                    - 'body'   : màu thân tôm (thân, râu, đuôi)
 *                    - 'line'   : màu các vạch "đốt" trên thân (tối hơn màu thân)
 *                    - 'spark'  : màu 3 bông tuyết trang trí (null = không vẽ, dùng cho "Tôm đông lạnh")
 *                    Các bộ theme có sẵn nằm ở hàm shrimp_themes() trong app/content.php.
 * @var string|null $class  Class CSS cho thẻ <svg> (mặc định "shrimp"), CSS sẽ quyết định kích thước/vị trí.
 *
 * Hệ toạ độ: viewBox 0 0 300 300 (hình vuông 300x300 đơn vị), SVG tự co giãn theo kích thước CSS.
 *
 * Ý tưởng hình học:
 * - Thân tôm là MỘT cung tròn rất dày (stroke-width 44) uốn hình chữ "C":
 *   tâm cung ~ (140,158), bán kính 72.
 *   + Điểm đầu (đầu tôm)  ở góc -32°  => toạ độ (201.1, 119.8) – phía trên bên phải.
 *   + Điểm cuối (đuôi tôm) ở góc 70°  => toạ độ (164.6, 225.7) – phía dưới.
 *   Cung đi ngược chiều kim đồng hồ qua phía trên, bên trái rồi xuống dưới (~258°),
 *   vì vậy dùng cờ large-arc = 1, sweep = 0 trong lệnh "A" của path.
 * - stroke-linecap="round" làm hai đầu cung tròn lại => đầu tôm tự có hình tròn.
 * - Các "đốt" trên thân: vẽ lại đúng cung đó lần nữa bằng màu 'line', nét mảnh hơn (38),
 *   và dùng stroke-dasharray="2.4 13.6": cứ 2.4 đơn vị nét rồi 13.6 đơn vị trống
 *   => tạo ra các vạch ngang cắt qua thân, nhìn như đốt tôm. dashoffset dịch vị trí vạch đầu tiên.
 * - Râu: 2 đường cong bậc hai (lệnh Q) mảnh + 1 đoạn thẳng dày (chuỷ/mũi tôm) từ đầu chĩa lên phải.
 * - Đuôi: 3 hình elip xoè ra như cánh quạt, đặt tại điểm cuối cung và xoay -20° cho khớp hướng thân.
 * - Mắt: 1 chấm trắng + 1 chấm đen nhỏ gần đầu.
 *
 * Thứ tự vẽ trong SVG quan trọng: cái vẽ sau nằm đè lên cái vẽ trước
 * (nền -> râu -> đuôi -> thân -> đốt -> mắt).
 *
 * Lưu ý: mọi màu in ra đều qua e() (escape HTML) để an toàn dù dữ liệu chỉ đến từ code.
 */

// Gán ngắn gọn cho dễ đọc trong phần SVG bên dưới.
$t = $theme;

// Path của cung tròn làm thân tôm (dùng 2 lần: thân và đốt).
// Cú pháp: M x y = di chuyển tới điểm đầu; A rx ry xoay large-arc sweep x y = vẽ cung tròn tới điểm cuối.
$arc = 'M201.1 119.8 A72 72 0 1 0 164.6 225.7';
?>
<svg class="<?= e($class ?? 'shrimp') ?>" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
<?php /* 1) Viền tròn mảnh bao ngoài (chỉ vẽ khi theme có 'ring') */ ?>
    <?php if (!empty($t['ring'])): ?>
        <circle cx="150" cy="150" r="146" fill="none" stroke="<?= e($t['ring']) ?>" stroke-width="1.2"/>
    <?php endif; ?>
<?php /* 2) Vòng tròn nền lớn phía sau con tôm (chỉ vẽ khi theme có 'circle') */ ?>
    <?php if (!empty($t['circle'])): ?>
        <circle cx="150" cy="152" r="122" fill="<?= e($t['circle']) ?>"/>
    <?php endif; ?>
<?php
/* 3) Ba bông tuyết trang trí (theme 'spark', dùng cho tôm đông lạnh).
 *    Mỗi phần tử [x, y, r] là tâm và bán kính của một bông tuyết.
 *    Mỗi bông gồm 3 nét qua tâm:
 *      - nét dọc:            M x (y-r)  v 2r
 *      - nét chéo xuống:     M (x - 0.87r) (y - r/2)  l 1.74r  r
 *      - nét chéo lên:       M (x - 0.87r) (y + r/2)  l 1.74r -r
 *    (0.87 ≈ cos 30°, nên 3 nét lệch nhau 60° tạo hình sao 6 cánh.)
 */
?>
    <?php if (!empty($t['spark'])): ?>
        <g stroke="<?= e($t['spark']) ?>" stroke-width="2.4" stroke-linecap="round">
            <?php foreach ([[46, 52, 13], [252, 36, 9], [262, 232, 10]] as [$x, $y, $r]): ?>
                <path d="M<?= $x ?> <?= $y - $r ?>v<?= $r * 2 ?>M<?= $x - $r * .87 ?> <?= $y - $r / 2 ?>l<?= $r * 1.74 ?> <?= $r ?>M<?= $x - $r * .87 ?> <?= $y + $r / 2 ?>l<?= $r * 1.74 ?> -<?= $r ?>"/>
            <?php endforeach; ?>
        </g>
    <?php endif; ?>
<?php
/* 4) Râu và chuỷ (mũi) tôm, vẽ TRƯỚC thân để gốc râu bị thân che, trông liền mạch.
 *    - 2 path đầu: râu dài, đường cong bậc hai "Q" (điểm điều khiển ở giữa), nét mảnh 3.
 *    - path cuối: chuỷ ngắn, đoạn thẳng "L", nét dày 8.
 */
?>
    <g fill="none" stroke="<?= e($t['body']) ?>" stroke-linecap="round">
        <path d="M216 108 Q 250 66 268 26" stroke-width="3"/>
        <path d="M221 115 Q 262 90 284 58" stroke-width="3"/>
        <path d="M212 109 L232 86" stroke-width="8"/>
    </g>
<?php
/* 5) Đuôi tôm: nhóm <g> được dời gốc toạ độ tới điểm cuối cung (164.6, 225.7)
 *    và xoay -20° theo hướng thân. Trong hệ toạ độ mới, 3 elip xoè quanh trục x:
 *    elip trên xoay -28°, elip giữa nằm ngang, elip dưới xoay +28° => hình quạt đuôi.
 */
?>
    <g transform="translate(164.6 225.7) rotate(-20)" fill="<?= e($t['body']) ?>">
        <ellipse cx="36" cy="-12" rx="19" ry="9" transform="rotate(-28 36 -12)"/>
        <ellipse cx="40" cy="0" rx="19" ry="8"/>
        <ellipse cx="36" cy="12" rx="19" ry="9" transform="rotate(28 36 12)"/>
    </g>
<?php /* 6) Thân tôm: cung tròn dày 44, đầu nét bo tròn (tạo luôn hình đầu tôm). */ ?>
    <path d="<?= $arc ?>" fill="none" stroke="<?= e($t['body']) ?>" stroke-width="44" stroke-linecap="round"/>
<?php
/* 7) Các đốt trên thân: vẽ lại cung với nét đứt (dasharray) màu 'line', hơi trong suốt (opacity .55).
 *    Nét 38 < 44 nên vạch đốt nằm gọn bên trong thân, không lòi ra mép.
 */
?>
    <path d="<?= $arc ?>" fill="none" stroke="<?= e($t['line']) ?>" stroke-width="38" stroke-dasharray="2.4 13.6" stroke-dashoffset="-8" opacity=".55"/>
<?php /* 8) Mắt tôm: tròng trắng + con ngươi tối, đặt gần điểm đầu cung. */ ?>
    <circle cx="207" cy="117" r="6" fill="#fff"/>
    <circle cx="208" cy="117" r="3.2" fill="#14213d"/>
</svg>
