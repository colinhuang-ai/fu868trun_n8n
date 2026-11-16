-- ============================================================================
--  database/schema.sql - CẤU TRÚC DATABASE CASES CMS (SQLite)
-- ============================================================================
--
--  File này được chạy tự động MỘT lần bởi db_install() (app/db.php) khi
--  database còn trống (lần chạy đầu tiên hoặc sau khi chạy database/reset.php).
--  Ngay sau đó database/seed.php nạp dữ liệu mẫu.
--
--  "IF NOT EXISTS": nếu bảng đã có thì bỏ qua, không báo lỗi, không xoá dữ liệu.
--
--  Ghi chú kiểu dữ liệu SQLite:
--    - INTEGER PRIMARY KEY AUTOINCREMENT: khoá chính tự tăng 1, 2, 3... (không dùng lại id đã xoá).
--    - TEXT: chuỗi. Ngày giờ cũng lưu dạng TEXT theo định dạng 'YYYY-mm-dd HH:ii:ss'
--      (hàm now() trong app/helpers.php) - định dạng này so sánh/sắp xếp đúng theo thời gian.
--    - SQLite không có kiểu BOOLEAN: dùng INTEGER 0 = không / 1 = có.
--    - Chuỗi nội dung tiếng Việt lưu UTF-8.
--
--  Để ý: không dùng dấu chấm phẩy trong comment vì cả file được chạy như một chuỗi lệnh.
-- ============================================================================

-- CASES CMS schema (SQLite)

-- ----------------------------------------------------------------------------
-- Bảng users: tài khoản quản trị viên đăng nhập vào /admin
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT, -- mã tài khoản
    name          TEXT    NOT NULL,                  -- tên hiển thị (góc trên phải trang admin)
    email         TEXT    NOT NULL UNIQUE,           -- email đăng nhập, luôn lưu chữ thường, không trùng
    password_hash TEXT    NOT NULL,                  -- mật khẩu đã băm bằng password_hash() - KHÔNG lưu mật khẩu gốc
    created_at    TEXT    NOT NULL                   -- thời điểm tạo tài khoản
);

-- ----------------------------------------------------------------------------
-- Bảng pages: TRANG tĩnh (Giới thiệu, Liên hệ, Chính sách...) - URL /{slug}
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pages (
    id               INTEGER PRIMARY KEY AUTOINCREMENT, -- mã trang
    title            TEXT    NOT NULL,                  -- tiêu đề trang (thẻ h1)
    slug             TEXT    NOT NULL UNIQUE,           -- đường dẫn, vd. 'gioi-thieu' - duy nhất
    excerpt          TEXT    NOT NULL DEFAULT '',       -- tóm tắt, hiện dưới tiêu đề ở banner
    content          TEXT    NOT NULL DEFAULT '',       -- nội dung HTML soạn bằng trình soạn thảo
    featured_image   TEXT,                              -- đường dẫn ảnh đại diện, vd. '/uploads/2026/11/ab.jpg' - NULL = không có
    template         TEXT    NOT NULL DEFAULT 'default', -- mẫu giao diện: 'default' hoặc 'contact' (xem page_templates())
    status           TEXT    NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published')), -- trạng thái - CHECK chặn giá trị khác 2 giá trị này
    show_in_menu     INTEGER NOT NULL DEFAULT 0,        -- 1 = hiện trên menu chính của website
    menu_order       INTEGER NOT NULL DEFAULT 0,        -- thứ tự trên menu (nhỏ đứng trước)
    meta_title       TEXT    NOT NULL DEFAULT '',       -- tiêu đề SEO (thẻ title) - rỗng = dùng title
    meta_description TEXT    NOT NULL DEFAULT '',       -- mô tả SEO (thẻ meta description) - rỗng = dùng excerpt
    author_id        INTEGER REFERENCES users (id) ON DELETE SET NULL, -- người tạo - xoá user thì cột này thành NULL (không xoá trang)
    published_at     TEXT,                              -- ngày đăng - ngày tương lai = hẹn giờ (xem published_sql())
    created_at       TEXT    NOT NULL,                  -- thời điểm tạo
    updated_at       TEXT    NOT NULL                   -- thời điểm sửa gần nhất
);

-- ----------------------------------------------------------------------------
-- Bảng posts: BÀI VIẾT (sản phẩm, tin tức, kiến thức) - URL /bai-viet/{slug}
-- Các cột giống pages, khác ở: category, label, sort_order, is_featured
-- (pages có template, show_in_menu, menu_order).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
    id               INTEGER PRIMARY KEY AUTOINCREMENT, -- mã bài viết
    title            TEXT    NOT NULL,                  -- tiêu đề bài
    slug             TEXT    NOT NULL UNIQUE,           -- đường dẫn, vd. 'tom-dong-lanh' - duy nhất
    excerpt          TEXT    NOT NULL DEFAULT '',       -- tóm tắt - hiện trên thẻ sản phẩm/bài viết
    content          TEXT    NOT NULL DEFAULT '',       -- nội dung HTML
    featured_image   TEXT,                              -- ảnh đại diện - NULL = card dùng hình minh hoạ con tôm
    category         TEXT    NOT NULL DEFAULT 'tin-tuc', -- chuyên mục: 'san-pham' | 'tin-tuc' | 'kien-thuc' (xem post_categories())
    label            TEXT    NOT NULL DEFAULT '',       -- nhãn nhỏ trên ảnh card, vd. 'Tươi sống', 'Cấp đông'
    sort_order       INTEGER NOT NULL DEFAULT 0,        -- thứ tự hiển thị sản phẩm ở trang chủ (nhỏ đứng trước)
    is_featured      INTEGER NOT NULL DEFAULT 0,        -- 1 = nổi bật, ưu tiên ở mục tin tức trang chủ
    status           TEXT    NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published')), -- trạng thái xuất bản
    meta_title       TEXT    NOT NULL DEFAULT '',       -- tiêu đề SEO
    meta_description TEXT    NOT NULL DEFAULT '',       -- mô tả SEO
    author_id        INTEGER REFERENCES users (id) ON DELETE SET NULL, -- người tạo
    published_at     TEXT,                              -- ngày đăng / hẹn giờ
    created_at       TEXT    NOT NULL,                  -- thời điểm tạo
    updated_at       TEXT    NOT NULL                   -- thời điểm sửa gần nhất
);

-- Chỉ mục (index) giúp tăng tốc các truy vấn danh sách bài viết công khai,
-- vốn luôn lọc theo status + category và sắp xếp theo published_at.
CREATE INDEX IF NOT EXISTS idx_posts_listing ON posts (status, category, published_at);

-- ----------------------------------------------------------------------------
-- Bảng settings: cài đặt website dạng khoá - giá trị (sửa ở Admin > Cài đặt)
-- Vd. ('site_name', 'CASES'), ('company_phone', '0283 999 888').
-- Danh sách khoá được phép: setting_fields() trong app/admin.php.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    key   TEXT PRIMARY KEY,         -- tên cài đặt (khoá chính - mỗi khoá chỉ một dòng)
    value TEXT NOT NULL DEFAULT ''  -- giá trị
);

-- ----------------------------------------------------------------------------
-- Bảng contacts: yêu cầu báo giá gửi từ form "Nhận báo giá" trên website
-- (xem site_contact_submit() trong app/site.php và Admin > Yêu cầu báo giá).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contacts (
    id         INTEGER PRIMARY KEY AUTOINCREMENT, -- mã yêu cầu
    name       TEXT    NOT NULL,                  -- họ và tên khách
    phone      TEXT    NOT NULL,                  -- số điện thoại
    message    TEXT    NOT NULL DEFAULT '',       -- nhu cầu (loại tôm, size, số lượng...)
    source     TEXT    NOT NULL DEFAULT '',       -- gửi từ đâu: 'Trang chủ' hoặc 'Trang liên hệ'
    ip         TEXT    NOT NULL DEFAULT '',       -- địa chỉ IP người gửi (để tra cứu khi bị spam)
    is_read    INTEGER NOT NULL DEFAULT 0,        -- 0 = chưa đọc, 1 = đã xử lý
    created_at TEXT    NOT NULL                   -- thời điểm gửi
);
