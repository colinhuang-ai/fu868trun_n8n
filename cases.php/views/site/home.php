<?php
/**
 * VIEW: TRANG CHỦ (theo prototype "Trang chủ CASES@2x.png")
 * ------------------------------------------------------------------
 * Controller gọi: site_home() trong app/site.php
 *   echo view('site/home', [...], 'site');  => bọc trong layout views/layouts/site.php
 *
 * Biến truyền vào:
 * @var array $products  Tối đa 4 bài viết chuyên mục "san-pham" đã xuất bản,
 *                       sắp theo sort_order => hiển thị ở section "Sản phẩm".
 * @var array $latest    Tối đa 3 bài viết mới nhất KHÔNG thuộc "san-pham"
 *                       (tin tức, kiến thức) => section "Cập nhật từ CASES".
 * (Biến $active = 'home' cũng được truyền nhưng chỉ layout dùng để biết đang ở trang chủ.)
 *
 * Các section theo thứ tự (id dùng làm anchor cho menu, vd. href="#san-pham"):
 *   1. Hero (banner xanh, con tôm)       – nội dung chữ lấy từ Cài đặt (setting())
 *   2. Marquee (dải chữ chạy ngang)
 *   3. #san-pham   – lấy từ CMS (bài viết chuyên mục Sản phẩm)
 *   4. #vi-sao     – 6 cam kết (dữ liệu cố định trong file này)
 *   5. #chat-luong – Chất lượng ISO + sơ đồ chu trình PDCA + 3 ô giấy chứng nhận
 *   6. #quy-trinh  – 6 bước + 3 ô hình nhà xưởng
 *   7. Tin tức (chỉ hiện khi có bài) – lấy từ CMS
 *   8. #lien-he    – form "Nhận báo giá" (partial contact-form)
 *
 * Helper hay dùng trong view:
 *   e($x)        : escape HTML (chống XSS) – mọi dữ liệu in ra đều nên đi qua e().
 *   setting($k)  : đọc giá trị trong bảng settings (sửa ở Admin → Cài đặt).
 *   icon($name)  : in ra icon SVG (định nghĩa trong app/icons.php).
 *   partial($n)  : nhúng file views/partials/$n.php.
 *   url($path)   : tạo URL đúng kể cả khi site chạy trong thư mục con.
 *
 * Class "reveal" + biến CSS --d: hiệu ứng hiện dần khi cuộn tới (xử lý trong public/assets/js/site.js,
 * --d là độ trễ để các card lần lượt hiện ra).
 */

// Bảng màu con tôm ở hero (xem giải thích các khoá trong views/partials/shrimp.php).
$heroTheme = ['circle' => '#3a5684', 'ring' => null, 'body' => '#d9776d', 'line' => '#b0554d', 'spark' => null];

// Các cụm từ chạy ngang trong dải marquee dưới hero.
$marquee = ['Nguồn gốc rõ ràng', 'Quy trình khép kín', 'Kiểm soát theo chuẩn ISO', 'Chuỗi lạnh liên tục', 'Kiểm tra từng lô', 'Giao hàng đúng hẹn'];

// 6 cam kết ở section "Vì sao chọn CASES". Mỗi phần tử: [tên icon, tiêu đề, mô tả].
$features = [
    ['map-pin', 'Nguồn gốc rõ ràng', 'Mỗi lô tôm có thông tin vùng nuôi và ngày thu hoạch để bạn truy xuất khi cần.'],
    ['shield-check', 'Kiểm soát theo chuẩn ISO', 'Hệ thống quản lý chất lượng được thiết lập, ghi chép và đánh giá định kỳ.'],
    ['rotate-cw', 'Quy trình khép kín', 'Từ tiếp nhận, sơ chế đến đóng gói đều nằm trong một chuỗi được giám sát.'],
    ['asterisk', 'Chuỗi lạnh liên tục', 'Nhiệt độ được theo dõi từ kho đến xe giao, giữ tôm tươi đến tay bạn.'],
    ['package', 'Đóng gói an toàn', 'Quy cách đóng gói theo nhu cầu, hạn chế dập nát và thất thoát khi vận chuyển.'],
    ['users', 'Đồng hành cùng khách hàng', 'Tư vấn size, số lượng và lịch giao phù hợp với quy mô kinh doanh của bạn.'],
];

// 3 ý có dấu tick ở section ISO. Mỗi phần tử: [phần in đậm, phần mô tả tiếp theo].
$isoPoints = [
    ['Tiêu chí đầu vào rõ ràng.', 'Tôm không đạt yêu cầu sẽ không được nhập kho.'],
    ['Hồ sơ theo từng lô.', 'Ghi nhận nguồn gốc, nhiệt độ và kết quả kiểm tra.'],
    ['Đánh giá và cải tiến định kỳ.', 'Vận hành theo chu trình hoạch định, thực hiện, kiểm tra, cải tiến.'],
];

// Tên 3 ô "Giấy chứng nhận và hồ sơ chất lượng" (hiện là khung trống như prototype).
$certs = ['Giấy chứng nhận ISO', 'Kiểm nghiệm chất lượng', 'Hồ sơ theo lô'];

// 6 bước quy trình. Mỗi phần tử: [icon, tiêu đề (cho phép <br> để ngắt dòng), mô tả].
// Lưu ý: tiêu đề chứa <br> nên khi in ra KHÔNG dùng e() – an toàn vì dữ liệu do code viết, không do người dùng nhập.
$steps = [
    ['waves', 'Chọn nguồn tôm', 'Làm việc với vùng nuôi đã đánh giá, thống nhất tiêu chí size và độ tươi.'],
    ['search', 'Kiểm tra đầu vào', 'Kiểm tra cảm quan, nhiệt độ và hồ sơ lô trước khi nhận hàng.'],
    ['layers', 'Sơ chế và<br>phân cỡ', 'Rửa, phân loại theo size trong khu vực sạch, nhân viên tuân thủ vệ sinh.'],
    ['asterisk', 'Cấp đông,<br>bảo quản', 'Làm lạnh nhanh và lưu kho ở nhiệt độ phù hợp từng dòng sản phẩm.'],
    ['clipboard-check', 'Kiểm tra<br>thành phẩm', 'QC lấy mẫu từng lô, đối chiếu tiêu chí trước khi cho phép xuất kho.'],
    ['truck', 'Đóng gói,<br>giao hàng', 'Đóng gói đúng quy cách, vận chuyển lạnh và xác nhận khi giao.'],
];

// 3 ô hình minh hoạ dưới quy trình. Mỗi phần tử: [icon, nhãn, màu nền].
$gallery = [
    ['factory', 'Nhà xưởng', '#dbe8f8'],
    ['layers', 'Khu sơ chế', '#eaf4fc'],
    ['package', 'Đóng gói', '#fbecea'],
];
?>

<!-- ============ HERO ============ -->
<?php
/* HERO: banner nền xanh navy, chia 2 cột (.hero-grid):
 *   - Cột trái (.hero-copy): dòng giới thiệu nhỏ, tiêu đề lớn, mô tả, 2 nút, 3 dấu tick.
 *   - Cột phải (.hero-visual): thẻ bo góc chứa con tôm SVG, sóng nước, và 3 "viên thuốc" nổi (float-pill).
 * Các <span class="bubble"> là bong bóng trang trí, vị trí đặt bằng style inline (% theo khung hero).
 * Cuối section là SVG sóng (.hero-wave) để chuyển mượt từ nền xanh sang nền trắng.
 */
?>
<section class="hero">
    <span class="bubble" style="left:6%;top:30%;width:22px;height:22px"></span>
    <span class="bubble" style="left:27%;top:86%;width:12px;height:12px"></span>
    <span class="bubble" style="left:41%;top:78%;width:24px;height:24px"></span>
    <span class="bubble" style="left:15%;bottom:16%;width:32px;height:32px"></span>
    <span class="bubble" style="right:6%;bottom:22%;width:9px;height:9px"></span>

    <div class="container hero-grid">
<?php
/* Cột chữ của hero. Toàn bộ chữ lấy từ Admin → Cài đặt → "Banner trang chủ":
 *   hero_eyebrow     : dòng nhỏ phía trên (có gạch đỏ + chấm xanh trang trí bằng CSS)
 *   hero_title       : tiêu đề; nl2br() đổi ký tự xuống dòng thành <br> để admin tự ngắt dòng
 *                      (e() chạy TRƯỚC nl2br() nên vẫn an toàn XSS).
 *   hero_highlight   : phần cuối tiêu đề được tô màu + gạch chân vàng (vd. "ISO.")
 *   hero_description : đoạn mô tả
 * Nút "Nhận báo giá" và "Xem quy trình" là anchor cuộn tới #lien-he và #quy-trinh trong cùng trang.
 */
?>
        <div class="hero-copy reveal">
            <p class="eyebrow eyebrow-light"><span class="dot"></span><?= e(setting('hero_eyebrow')) ?></p>
            <h1><?= nl2br(e(setting('hero_title'))) ?> <span class="hl"><?= e(setting('hero_highlight')) ?></span></h1>
            <p class="hero-desc"><?= e(setting('hero_description')) ?></p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="#lien-he">Nhận báo giá <?= icon('arrow-right') ?></a>
                <a class="btn btn-outline-light" href="#quy-trinh">Xem quy trình</a>
            </div>
            <ul class="hero-checks">
                <li><?= icon('check') ?>Nguồn gốc rõ ràng</li>
                <li><?= icon('check') ?>Kiểm tra từng lô</li>
                <li><?= icon('check') ?>Giao hàng chuỗi lạnh</li>
            </ul>
        </div>

<?php
/* Cột hình của hero (aria-hidden: chỉ trang trí, trình đọc màn hình bỏ qua).
 *   .hero-orbit : vòng tròn mờ lớn phía sau thẻ.
 *   .hero-card  : thẻ bo góc nền xanh đậm, bên trong:
 *                 - con tôm SVG (partial shrimp, class hero-shrimp có animation lơ lửng),
 *                 - 2 lớp sóng ở đáy thẻ (SVG, preserveAspectRatio="none" để kéo giãn theo bề rộng),
 *                 - 2 bong bóng nhỏ.
 *   .float-pill : 3 nhãn trắng nổi đè lên mép thẻ (vị trí bằng style inline, có animation nhấp nhô).
 */
?>
        <div class="hero-visual reveal" aria-hidden="true">
            <div class="hero-orbit"></div>
            <div class="hero-card">
                <?= partial('shrimp', ['theme' => $heroTheme, 'class' => 'shrimp hero-shrimp']) ?>
                <svg class="hero-card-waves" viewBox="0 0 520 120" preserveAspectRatio="none">
                    <path d="M0 60 C 90 20 170 20 260 55 S 430 100 520 50 V120 H0Z" fill="#234679"/>
                    <path d="M0 88 C 120 60 220 64 300 86 S 450 110 520 84 V120 H0Z" fill="#152c58"/>
                </svg>
                <span class="hero-bubble" style="left:72%;top:58%;width:18px;height:18px"></span>
                <span class="hero-bubble" style="left:88%;top:62%;width:32px;height:32px"></span>
            </div>
            <span class="float-pill" style="top:78px;left:-28px"><i><?= icon('shield-check') ?></i>Chứng nhận ISO</span>
            <span class="float-pill" style="top:295px;right:-22px"><i><?= icon('map-pin') ?></i>Truy xuất nguồn gốc</span>
            <span class="float-pill" style="bottom:-26px;left:40px"><i><?= icon('asterisk') ?></i>Chuỗi lạnh liên tục</span>
        </div>
    </div>

<?php /* Sóng chuyển tiếp ở đáy hero: lớp xanh nhạt phía sau + lớp trắng phía trước. */ ?>
    <svg class="hero-wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 52 C 200 20 420 14 640 48 S 1080 100 1440 30 V120 H0Z" fill="#2f6496"/>
        <path d="M0 78 C 260 40 520 44 760 78 S 1200 116 1440 66 V120 H0Z" fill="#fff"/>
    </svg>
</section>

<!-- ============ MARQUEE ============ -->
<?php
/* MARQUEE: dải chữ chạy ngang vô tận.
 * Danh sách được in 2 lần liên tiếp ($r = 0 và 1) trong .marquee-track; CSS dịch track sang trái 50%
 * rồi lặp lại => nhìn như chạy liên tục không bị giật.
 * Bản sao thứ 2 có aria-hidden="true" để trình đọc màn hình không đọc lặp.
 */
?>
<div class="marquee" aria-label="Cam kết của CASES">
    <div class="marquee-track">
        <?php for ($r = 0; $r < 2; $r++): ?>
            <ul<?= $r ? ' aria-hidden="true"' : '' ?>>
                <?php foreach ($marquee as $m): ?><li><?= e($m) ?></li><?php endforeach; ?>
            </ul>
        <?php endfor; ?>
    </div>
</div>

<!-- ============ SẢN PHẨM ============ -->
<?php
/* SẢN PHẨM (#san-pham): lấy từ CMS.
 * $products = các bài viết chuyên mục "Sản phẩm" (tối đa 4) do site_home() truy vấn.
 * Mỗi sản phẩm render bằng partial post-card; $i (0..3) quyết định bộ màu minh hoạ con tôm
 * và độ trễ hiệu ứng (--d: 0ms, 80ms, 160ms...).
 * Nếu chưa có sản phẩm nào: hiện ghi chú hướng dẫn thêm trong trang quản trị.
 */
?>
<section class="section section-gray" id="san-pham">
    <div class="container">
        <div class="section-head reveal">
            <p class="eyebrow">Sản phẩm</p>
            <h2>Tôm cho từng nhu cầu của bạn</h2>
            <p class="lead">Danh mục của CASES dành cho nhà hàng, đại lý và nhà phân phối. Chọn dòng sản phẩm phù hợp với quy mô của bạn.</p>
        </div>
        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $i => $p): ?>
                    <div class="reveal" style="--d:<?= $i * 80 ?>ms"><?= partial('post-card', ['post' => $p, 'index' => $i]) ?></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-note">Chưa có sản phẩm. Thêm bài viết thuộc chuyên mục “Sản phẩm” trong trang quản trị.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ============ VÌ SAO CHỌN ============ -->
<?php
/* VÌ SAO CHỌN CASES (#vi-sao): lưới 3 cột x 2 hàng, dữ liệu từ mảng $features ở đầu file.
 * Mỗi card: số thứ tự to mờ góc phải (sprintf('%02d') => "01", "02"...), ô icon, tiêu đề, mô tả.
 * Độ trễ hiệu ứng theo cột: ($i % 3) * 80ms => mỗi hàng hiện từ trái sang phải.
 */
?>
<section class="section" id="vi-sao">
    <div class="container">
        <div class="section-head reveal">
            <p class="eyebrow">Vì sao chọn CASES</p>
            <h2>Sáu cam kết chúng tôi giữ ở mọi đơn hàng</h2>
            <p class="lead">Khách hàng chọn CASES vì sự ổn định: cùng một tiêu chuẩn cho lô đầu tiên và lô thứ một trăm.</p>
        </div>
        <div class="feature-grid">
            <?php foreach ($features as $i => [$ic, $h, $p]): ?>
                <article class="feature-card reveal" style="--d:<?= ($i % 3) * 80 ?>ms">
                    <span class="feature-num"><?= sprintf('%02d', $i + 1) ?></span>
                    <span class="feature-icon"><?= icon($ic) ?></span>
                    <h3><?= e($h) ?></h3>
                    <p><?= e($p) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ CHẤT LƯỢNG ISO ============ -->
<?php
/* CHẤT LƯỢNG ISO (#chat-luong): nền navy, 2 cột (.iso-grid).
 *   - Cột trái: tiêu đề, mô tả, danh sách 3 ý có tick (mảng $isoPoints).
 *   - Cột phải: sơ đồ chu trình PDCA vẽ bằng SVG (viewBox 440x440, tâm 220,220):
 *       + vòng ngoài mảnh (r=208)
 *       + vòng nét đứt xanh nhạt (r=186) – class iso-spin: CSS cho quay chậm
 *       + vòng nét đứt dài (r=146) – iso-spin-rev: quay ngược chiều
 *       + hình tròn đặc ở giữa (r=102) chứa chữ "ISO" và "Quản lý chất lượng"
 *       + chấm tròn nhỏ trên vòng ngoài
 *     4 nhãn (.iso-pill) Hoạch định / Thực hiện / Kiểm tra / Cải tiến đặt bằng position absolute
 *     ở trên / phải / dưới / trái vòng tròn.
 *   - Phía dưới: 3 ô "Giấy chứng nhận..." (mảng $certs) – hiện là khung trống chờ ảnh.
 */
?>
<section class="section section-navy" id="chat-luong">
    <div class="container">
        <div class="iso-grid">
            <div class="reveal">
                <p class="eyebrow eyebrow-light">Chất lượng ISO</p>
                <h2>Chất lượng được kiểm chứng, không chỉ là lời hứa</h2>
                <p class="lead">CASES vận hành theo hệ thống quản lý chất lượng đạt chuẩn ISO. Mọi bước đều có tiêu chí, hồ sơ ghi chép và người chịu trách nhiệm.</p>
                <ul class="iso-list">
                    <?php foreach ($isoPoints as [$b, $t]): ?>
                        <li><span class="iso-check"><?= icon('check') ?></span><p><strong><?= e($b) ?></strong> <?= e($t) ?></p></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="iso-diagram reveal" aria-label="Chu trình PDCA: Hoạch định, Thực hiện, Kiểm tra, Cải tiến">
                <svg viewBox="0 0 440 440" aria-hidden="true">
                    <circle cx="220" cy="220" r="208" fill="none" stroke="rgba(255,255,255,.22)" stroke-width="1.5"/>
                    <circle class="iso-spin" cx="220" cy="220" r="186" fill="none" stroke="#86cdea" stroke-width="2" stroke-dasharray="5 9" stroke-linecap="round"/>
                    <circle class="iso-spin-rev" cx="220" cy="220" r="146" fill="none" stroke="rgba(255,255,255,.4)" stroke-width="2" stroke-dasharray="70 24" stroke-linecap="round"/>
                    <circle cx="220" cy="220" r="102" fill="#18315f" stroke="rgba(255,255,255,.45)" stroke-width="1.5"/>
                    <circle cx="286" cy="88" r="6" fill="#86cdea"/>
                    <text x="220" y="226" text-anchor="middle" class="iso-text">ISO</text>
                    <text x="220" y="256" text-anchor="middle" class="iso-sub">Quản lý chất</text>
                    <text x="220" y="276" text-anchor="middle" class="iso-sub">lượng</text>
                </svg>
                <span class="iso-pill" style="top:-4px;left:50%;transform:translateX(-50%)"><b>1</b>Hoạch định</span>
                <span class="iso-pill" style="top:50%;right:-36px;transform:translateY(-50%)"><b>2</b>Thực hiện</span>
                <span class="iso-pill" style="bottom:-4px;left:50%;transform:translateX(-50%)"><b>3</b>Kiểm tra</span>
                <span class="iso-pill" style="top:50%;left:-36px;transform:translateY(-50%)"><b>4</b>Cải tiến</span>
            </div>
        </div>

<?php /* Lưới 3 ô giấy chứng nhận: icon ảnh, tên hồ sơ, nguồn. */ ?>
        <h3 class="cert-heading reveal">Giấy chứng nhận và hồ sơ chất lượng</h3>
        <div class="cert-grid">
            <?php foreach ($certs as $i => $c): ?>
                <div class="cert-card reveal" style="--d:<?= $i * 80 ?>ms">
                    <?= icon('image', 'icon cert-icon', 1.6) ?>
                    <strong><?= e($c) ?></strong>
                    <span>Từ cases.com.vn</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ QUY TRÌNH ============ -->
<?php
/* QUY TRÌNH (#quy-trinh):
 *   - <ol class="steps">: 6 bước nằm ngang (mảng $steps). Mỗi bước có vòng tròn icon,
 *     số thứ tự đỏ ở góc (<b>), tiêu đề và mô tả. Đường nối giữa các bước vẽ bằng CSS (::before):
 *     3 đoạn đầu màu xanh đậm, các đoạn sau xanh nhạt.
 *     Trên mobile CSS đổi thành dạng dọc (timeline).
 *   - .gallery-grid: 3 ô minh hoạ (Nhà xưởng, Khu sơ chế, Đóng gói) với màu nền riêng,
 *     vòng tròn viền + hình tròn trắng chứa icon, nhãn ở góc dưới trái.
 */
?>
<section class="section" id="quy-trinh">
    <div class="container">
        <div class="section-head reveal">
            <p class="eyebrow">Quy trình chuyên nghiệp</p>
            <h2>Sáu bước từ nguồn tôm<br>đến tay khách hàng</h2>
            <p class="lead">Mỗi bước có người phụ trách, tiêu chí kiểm tra và hồ sơ ghi chép. Lô hàng chỉ đi tiếp khi đạt yêu cầu của bước trước.</p>
        </div>
        <ol class="steps">
            <?php foreach ($steps as $i => [$ic, $h, $p]): ?>
                <li class="step reveal" style="--d:<?= $i * 70 ?>ms">
                    <span class="step-icon"><?= icon($ic) ?><b><?= $i + 1 ?></b></span>
                    <h3><?= $h /* chứa <br> do code định nghĩa */ ?></h3>
                    <p><?= e($p) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
        <div class="gallery-grid">
            <?php foreach ($gallery as $i => [$ic, $label, $bg]): ?>
                <figure class="gallery-card reveal" style="background:<?= $bg ?>;--d:<?= $i * 80 ?>ms">
                    <span class="gallery-ring"></span>
                    <span class="gallery-circle"><?= icon($ic, 'icon', 1.6) ?></span>
                    <figcaption class="chip"><?= e($label) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
/* TIN TỨC: section này không có trong prototype, thêm vào để hiển thị bài viết từ CMS.
 * Chỉ render khi $latest có dữ liệu (tối đa 3 bài không thuộc chuyên mục Sản phẩm).
 * 'index' => $i + 1 để bộ màu con tôm khác với hàng sản phẩm; 'show_date' => true để hiện ngày đăng.
 */
?>
<?php if ($latest): ?>
<!-- ============ TIN TỨC (từ CMS) ============ -->
<section class="section section-gray section-tight">
    <div class="container">
        <div class="section-head section-head-row reveal">
            <div>
                <p class="eyebrow">Tin tức &amp; kiến thức</p>
                <h2>Cập nhật từ CASES</h2>
            </div>
            <a class="link-arrow" href="<?= e(url('/bai-viet')) ?>">Xem tất cả <?= icon('arrow-right') ?></a>
        </div>
        <div class="product-grid product-grid-3">
            <?php foreach ($latest as $i => $p): ?>
                <div class="reveal" style="--d:<?= $i * 80 ?>ms"><?= partial('post-card', ['post' => $p, 'index' => $i + 1, 'show_date' => true]) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ LIÊN HỆ ============ -->
<?php
/* LIÊN HỆ (#lien-he): 2 cột – chữ giới thiệu bên trái, thẻ form bên phải.
 * Form dùng partial contact-form với 'back' => 'home': sau khi gửi (POST /lien-he, xử lý ở
 * site_contact_submit() trong app/site.php) người dùng được chuyển về lại "/#lien-he"
 * để thấy thông báo thành công/lỗi ngay trong form.
 */
?>
<section class="section section-blue" id="lien-he">
    <div class="container contact-grid">
        <div class="contact-copy reveal">
            <p class="eyebrow">Liên hệ</p>
            <h2>Cần báo giá cho<br>lô hàng của bạn?</h2>
            <p class="lead">Để lại thông tin, đội ngũ CASES sẽ liên hệ tư vấn size, số lượng và lịch giao phù hợp.</p>
        </div>
        <div class="contact-card reveal">
            <?= partial('contact-form', ['back' => 'home']) ?>
        </div>
    </div>
</section>
