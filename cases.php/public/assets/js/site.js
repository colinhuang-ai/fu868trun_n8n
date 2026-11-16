/**
 * ============================================================================
 *  public/assets/js/site.js – JAVASCRIPT CHO WEBSITE (phía khách xem)
 * ============================================================================
 *
 * Được nạp ở cuối views/layouts/site.php với thuộc tính `defer`
 * (trình duyệt tải song song, chỉ chạy sau khi HTML đã dựng xong → luôn tìm thấy phần tử).
 *
 * File làm 3 việc:
 *   1. Bật/tắt menu trên điện thoại (nút hamburger).
 *   2. Hiệu ứng "hiện dần khi cuộn tới" cho các phần tử có class .reveal.
 *   3. Sau khi gửi form báo giá, hiện ngay khu vực liên hệ để người dùng thấy thông báo.
 *
 * Viết bằng JavaScript thuần (không jQuery, không thư viện), cú pháp ES5 (var, function)
 * để chạy được cả trên trình duyệt cũ.
 */
// IIFE (Immediately Invoked Function Expression): bọc toàn bộ code trong một hàm chạy ngay,
// để các biến bên trong (toggle, nav, io…) không lọt ra phạm vi toàn cục (window).
(function () {
    // Chế độ nghiêm ngặt: báo lỗi với các cú pháp dễ gây bug (vd. dùng biến chưa khai báo).
    'use strict';

    // ---------- 1. MENU TRÊN ĐIỆN THOẠI ----------
    // Nút hamburger (chỉ hiện ở màn hình ≤ 1080px – xem site.css) và khối menu.
    // Kiểm tra cả hai tồn tại trước khi gắn sự kiện, tránh lỗi nếu layout thay đổi.
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('main-nav');
    if (toggle && nav) {
        // Bấm nút: thêm/bỏ class .nav-open trên <body>. CSS dựa vào class này để hiện menu và đổi icon ☰ ↔ ✕.
        toggle.addEventListener('click', function () {
            var open = document.body.classList.toggle('nav-open');
            // aria-expanded báo cho trình đọc màn hình biết menu đang mở hay đóng (khả năng truy cập).
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        // Bấm vào một link trong menu (vd. #san-pham) thì tự đóng menu,
        // để nội dung trang không bị menu che sau khi cuộn tới.
        // Dùng "event delegation": gắn 1 sự kiện cho cả khối nav thay vì từng link;
        // e.target.closest('a') tìm thẻ <a> gần nhất chứa phần tử được bấm.
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) {
                document.body.classList.remove('nav-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ---------- 2. HIỆU ỨNG HIỆN DẦN KHI CUỘN TỚI ----------
    // Lấy mọi phần tử cần hiệu ứng. Ban đầu CSS cho chúng mờ + lệch xuống;
    // khi thêm class .is-visible thì hiện dần (transition trong site.css).
    var items = document.querySelectorAll('.reveal');
    // Trình duyệt quá cũ không có IntersectionObserver → hiện tất cả ngay rồi thoát, không để nội dung bị ẩn mãi.
    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('is-visible'); });
        return;
    }
    // IntersectionObserver: API của trình duyệt báo cho ta biết khi một phần tử đi vào/ra khỏi màn hình.
    // Hiệu quả hơn nhiều so với lắng nghe sự kiện scroll rồi tự tính vị trí.
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            // Phần tử đã vào vùng nhìn thấy
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                // Chỉ chạy hiệu ứng một lần: hiện xong thì ngừng theo dõi phần tử này.
                io.unobserve(entry.target);
            }
        });
    // rootMargin '-8%' ở đáy: phần tử phải đi lên khỏi mép dưới màn hình thêm 8% mới tính là "thấy"
    // → hiệu ứng xảy ra khi người dùng thật sự nhìn tới.
    // threshold 0.08: chỉ cần 8% diện tích phần tử lọt vào vùng đó là kích hoạt.
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    // Bắt đầu theo dõi từng phần tử .reveal.
    items.forEach(function (el) { io.observe(el); });

    // ---------- 3. SAU KHI GỬI FORM BÁO GIÁ ----------
    // Sau khi gửi form, server chuyển hướng về /#lien-he và in ra hộp thông báo .form-alert
    // (thành công hoặc lỗi). Trang cuộn thẳng tới section liên hệ nên không kịp "cuộn qua"
    // để kích hoạt hiệu ứng → ép hiện ngay toàn bộ phần tử .reveal trong section đó.
    // `alert && alert.closest(...)`: nếu không có thông báo thì section = null và bỏ qua.
    var alert = document.querySelector('.form-alert');
    var section = alert && alert.closest('section');
    if (section) {
        section.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('is-visible'); });
    }
})();
