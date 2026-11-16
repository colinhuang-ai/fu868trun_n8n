/**
 * ============================================================================
 *  public/assets/js/admin.js – JAVASCRIPT CHO TRANG QUẢN TRỊ (/admin)
 * ============================================================================
 *
 * Được nạp ở cuối views/layouts/admin.php. Trên trang soạn Trang/Bài viết,
 * layout nạp thư viện Quill (trình soạn thảo) TRƯỚC file này, nên ở đây
 * dùng được biến toàn cục window.Quill.
 *
 * Các chức năng (theo thứ tự trong file):
 *   1. Mở/đóng sidebar trên điện thoại
 *   2. Nút X đóng thông báo (flash message)
 *   3. Hỏi xác nhận trước khi xoá (form có thuộc tính data-confirm)
 *   4. Tự sinh đường dẫn (slug) từ tiêu đề tiếng Việt
 *   5. Xem trước ảnh đại diện ngay khi chọn file
 *   6. Trình soạn thảo Quill: nạp nội dung cũ, lưu HTML khi submit,
 *      cảnh báo khi rời trang chưa lưu, tải ảnh lên server khi chèn ảnh
 *
 * Mọi chức năng đều kiểm tra phần tử có tồn tại trước → dùng chung một file
 * cho mọi trang admin mà không lỗi.
 */
// IIFE: bọc code trong hàm chạy ngay để biến không lọt ra phạm vi toàn cục.
(function () {
    'use strict';
    // Lấy CSRF token từ <meta name="csrf-token"> trong <head> của layout admin.
    // Token này phải gửi kèm mọi request POST; server (csrf_verify trong app/helpers.php)
    // so sánh với token trong session để chắc chắn request đến từ chính trang admin,
    // không phải từ website lạ giả mạo (tấn công CSRF).
    // `|| {}` và `|| ''`: nếu không có thẻ meta thì không lỗi, token = chuỗi rỗng.
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    // ---------- 1. SIDEBAR TRÊN ĐIỆN THOẠI ----------
    // Nút ☰ trên topbar (chỉ hiện ở màn hình ≤ 960px – xem admin.css).
    var toggle = document.querySelector('.sidebar-toggle');
    if (toggle) {
        // Bấm nút: bật/tắt class .sidebar-open trên <body> → CSS trượt sidebar vào/ra.
        toggle.addEventListener('click', function () { document.body.classList.toggle('sidebar-open'); });
        // Bấm vào lớp phủ tối phía sau sidebar thì đóng sidebar.
        document.querySelector('.sidebar-backdrop').addEventListener('click', function () { document.body.classList.remove('sidebar-open'); });
    }

    // ---------- 2. ĐÓNG THÔNG BÁO ----------
    // Event delegation: gắn 1 sự kiện click cho cả document, rồi kiểm tra
    // phần tử được bấm có nằm trong nút .alert-close không. Cách này chạy được
    // cho mọi thông báo, kể cả thông báo thêm vào trang sau này.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.alert-close');
        // Xoá cả hộp thông báo khỏi trang.
        if (btn) { btn.closest('.alert').remove(); }
    });

    // ---------- 3. XÁC NHẬN TRƯỚC KHI XOÁ ----------
    // Mọi form có thuộc tính data-confirm="..." (nút xoá trang/bài/yêu cầu) sẽ hỏi lại
    // bằng hộp thoại confirm() của trình duyệt. Bấm "Huỷ" → preventDefault() chặn gửi form.
    // Tham số cuối `true` = bắt sự kiện ở pha "capture" (chạy trước các xử lý submit khác),
    // nhờ vậy huỷ được sớm, kể cả với form được submit bằng nút nằm ngoài form (thuộc tính form="...").
    document.addEventListener('submit', function (e) {
        var form = e.target;
        var msg = form.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) { e.preventDefault(); }
    }, true);

    // ---------- 4. TỰ SINH ĐƯỜNG DẪN (SLUG) TỪ TIÊU ĐỀ ----------
    // Chuyển chuỗi tiếng Việt thành slug, vd. "Tôm sú loại 1" → "tom-su-loai-1".
    // Các bước:
    //   - toLowerCase(): chữ thường
    //   - đổi "đ" → "d" (đ không phải chữ có dấu tổ hợp nên normalize không tách được)
    //   - normalize('NFD'): tách chữ có dấu thành chữ gốc + dấu, vd. "ô" → "o" + "̂"
    //   - xoá các ký tự dấu tổ hợp (dải Unicode U+0300–U+036F)
    //   - mọi cụm ký tự không phải a-z, 0-9 → một dấu "-"
    //   - bỏ dấu "-" thừa ở đầu và cuối
    // Server cũng có hàm slugify() tương tự (app/helpers.php) và luôn chuẩn hoá lại khi lưu,
    // nên JS ở đây chỉ để người dùng thấy trước kết quả.
    function slugify(s) {
        return s.toLowerCase().replace(/đ/g, 'd').normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }
    // Ô tiêu đề (data-slug-source) và ô đường dẫn (data-slug-target) trong form soạn thảo.
    var src = document.querySelector('[data-slug-source]');
    var target = document.querySelector('[data-slug-target]');
    if (src && target) {
        // Gõ tiêu đề → cập nhật slug, NHƯNG chỉ khi slug đang ở chế độ tự động (data-auto="1").
        // Bài mới: data-auto="1". Bài đã có slug: data-auto="0" để không vô tình đổi URL đã công khai.
        src.addEventListener('input', function () {
            if (target.dataset.auto === '1') { target.value = slugify(src.value); }
        });
        // Người dùng tự gõ vào ô slug → tắt chế độ tự động; xoá trống ô slug → bật lại tự động.
        target.addEventListener('input', function () { target.dataset.auto = target.value === '' ? '1' : '0'; });
        // Rời khỏi ô slug (blur) → chuẩn hoá lại những gì người dùng tự gõ.
        target.addEventListener('blur', function () { target.value = slugify(target.value); });
    }

    // ---------- 5. XEM TRƯỚC ẢNH ĐẠI DIỆN ----------
    // Mỗi khối ảnh đại diện (data-image-field) gồm ô chọn file + khung xem trước.
    document.querySelectorAll('[data-image-field]').forEach(function (wrap) {
        var input = wrap.querySelector('input[type=file]');
        var preview = wrap.querySelector('.image-preview');
        // Khi người dùng chọn file:
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            // Bấm Huỷ trong hộp chọn file → không có file → không làm gì.
            if (!file) { return; }
            // Dùng thẻ <img> có sẵn trong khung, hoặc tạo mới nếu bài chưa có ảnh.
            var img = preview.querySelector('img') || preview.appendChild(document.createElement('img'));
            // URL.createObjectURL tạo URL tạm (blob:...) trỏ tới file trên máy người dùng
            // → hiện ảnh ngay mà chưa cần tải lên server. File thật chỉ gửi lên khi bấm Lưu.
            img.src = URL.createObjectURL(file);
            // Thêm class .has-image để CSS ẩn dòng chữ "Chưa có ảnh".
            preview.classList.add('has-image');
            // Vừa chọn ảnh mới thì bỏ tick ô "Gỡ ảnh hiện tại" (nếu có) để tránh mâu thuẫn.
            var remove = wrap.querySelector('input[name=remove_image]');
            if (remove) { remove.checked = false; }
        });
    });

    // ---------- 6. TRÌNH SOẠN THẢO QUILL ----------
    // Chỉ chạy trên trang có khung #editor (form soạn Trang/Bài viết) và khi thư viện Quill
    // đã tải xong từ CDN (layout chỉ nạp Quill khi controller truyền use_editor = true).
    var editorEl = document.getElementById('editor');
    if (editorEl && window.Quill) {
        // <textarea name="content" hidden> là ô THẬT được gửi lên server.
        // Quill chỉ là giao diện soạn thảo; trước khi submit ta chép HTML từ Quill vào textarea này.
        var textarea = document.getElementById('f-content');
        // Khởi tạo Quill với theme "snow" (thanh công cụ nằm phía trên vùng soạn).
        var quill = new Quill(editorEl, {
            theme: 'snow',
            placeholder: 'Soạn nội dung…',
            modules: {
                // Cấu hình thanh công cụ: mỗi mảng con là một nhóm nút.
                toolbar: {
                    container: [
                        // Chọn kiểu đoạn: Tiêu đề 2, Tiêu đề 3 hoặc đoạn văn thường (false). Không dùng H1 vì H1 là tiêu đề trang.
                        [{ header: [2, 3, false] }],
                        // In đậm, nghiêng, gạch chân, gạch ngang
                        ['bold', 'italic', 'underline', 'strike'],
                        // Danh sách số, danh sách gạch đầu dòng, trích dẫn
                        [{ list: 'ordered' }, { list: 'bullet' }, 'blockquote'],
                        // Căn lề trái/giữa/phải/đều
                        [{ align: [] }],
                        // Chèn link, chèn ảnh
                        ['link', 'image'],
                        // Xoá định dạng
                        ['clean']
                    ],
                    // Thay hành vi mặc định của nút ảnh: Quill mặc định nhúng ảnh dạng base64 vào thẳng HTML
                    // (làm nội dung rất nặng). Ở đây ta tải ảnh lên server và chỉ chèn URL (hàm uploadImage bên dưới).
                    handlers: { image: uploadImage }
                }
            }
        });
        // Nạp nội dung cũ (bài đang sửa) vào editor: chuyển HTML → định dạng nội bộ của Quill (Delta).
        // 'silent' = không phát sự kiện text-change, để không bị tính là "đã sửa".
        if (textarea.value.trim()) {
            quill.setContents(quill.clipboard.convert({ html: textarea.value }), 'silent');
        }

        // Form bao quanh editor (form soạn thảo).
        var form = editorEl.closest('form');
        // Cờ "có thay đổi chưa lưu" – dùng để cảnh báo khi rời trang.
        var dirty = false;
        // Gõ trong editor → đánh dấu đã sửa.
        quill.on('text-change', function () { dirty = true; });
        // Sửa bất kỳ ô nào khác trong form (tiêu đề, tóm tắt…) → cũng đánh dấu đã sửa.
        form.addEventListener('input', function () { dirty = true; });
        // Ngay trước khi form được gửi đi:
        form.addEventListener('submit', function () {
            // Lấy HTML từ Quill và chép vào textarea để gửi lên server.
            // getSemanticHTML() xuất HTML chuẩn (<ul><li>, <ol><li>) thay vì markup nội bộ của Quill 2
            // (<ol><li data-list="bullet">…) → nội dung hiển thị đúng ngoài website và dễ dùng lại.
            // Hàm này đổi dấu cách thành &nbsp; nên ta đổi ngược lại thành dấu cách thường.
            // getLength() <= 1 nghĩa là editor trống (Quill luôn có 1 ký tự xuống dòng) → lưu chuỗi rỗng.
            var html = quill.getLength() <= 1 ? '' : quill.getSemanticHTML().replace(/&nbsp;/g, ' ');
            textarea.value = html;
            // Đang lưu → bỏ cờ, để không hiện cảnh báo rời trang khi trình duyệt chuyển sang trang kết quả.
            dirty = false;
        });
        // Cảnh báo khi đóng tab/bấm link khác mà còn thay đổi chưa lưu.
        // Trình duyệt sẽ tự hiện hộp thoại "Rời khỏi trang?" (nội dung câu chữ do trình duyệt quyết định).
        window.addEventListener('beforeunload', function (e) {
            if (dirty) { e.preventDefault(); e.returnValue = ''; }
        });

        // Xử lý khi bấm nút chèn ảnh trên thanh công cụ Quill.
        // Khai báo dạng function nên được "hoisting" – dùng được ở cấu hình phía trên dù viết ở dưới.
        function uploadImage() {
            // Tạo một ô chọn file ẩn (không gắn vào trang) chỉ để mở hộp thoại chọn ảnh.
            var picker = document.createElement('input');
            picker.type = 'file';
            // Chỉ cho chọn các định dạng ảnh mà server chấp nhận (config 'upload_mime').
            picker.accept = 'image/jpeg,image/png,image/webp,image/gif';
            // Khi đã chọn file:
            picker.onchange = function () {
                var file = picker.files[0];
                if (!file) { return; }
                // Đóng gói file vào FormData (giống form multipart/form-data) với tên trường "file".
                var data = new FormData();
                data.append('file', file);
                // Làm mờ editor trong lúc tải lên (class CSS .editor-uploading).
                editorEl.classList.add('editor-uploading');
                // Gửi ảnh lên route POST /admin/upload (admin_upload() trong app/admin.php).
                // URL lấy từ thuộc tính data-upload-url của khung editor (đã tính sẵn base path).
                fetch(editorEl.dataset.uploadUrl, {
                    method: 'POST',
                    // Gửi CSRF token qua header, vì request fetch không có ô _csrf như form thường.
                    headers: { 'X-CSRF-Token': csrf },
                    body: data,
                    // Gửi kèm cookie session để server biết đây là admin đã đăng nhập.
                    credentials: 'same-origin'
                // Server luôn trả JSON: thành công {"url": "/uploads/..."}, lỗi {"error": "..."} kèm mã 422.
                // r.ok = false khi mã HTTP không phải 2xx → ném lỗi với thông báo từ server.
                }).then(function (r) {
                    return r.json().then(function (j) { if (!r.ok) { throw new Error(j.error || 'Tải ảnh thất bại.'); } return j; });
                // Tải xong: chèn ảnh vào đúng vị trí con trỏ rồi đưa con trỏ ra sau ảnh.
                }).then(function (j) {
                    // getSelection(true): lấy vị trí con trỏ (true = focus lại editor nếu đang mất focus).
                    var range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', j.url, 'user');
                    quill.setSelection(range.index + 1, 0);
                // Lỗi (sai định dạng, quá dung lượng, mất mạng…) → báo cho người dùng.
                }).catch(function (err) {
                    window.alert(err.message);
                // Dù thành công hay lỗi đều bỏ trạng thái làm mờ editor.
                }).finally(function () {
                    editorEl.classList.remove('editor-uploading');
                });
            };
            // Mở hộp thoại chọn file.
            picker.click();
        }
    }
})();
