# CASES – Website + CMS (PHP)

Website giới thiệu CASES dựng theo prototype `Trang chủ CASES@2x.png`, kèm CMS quản lý **Trang (pages)** và **Bài viết (posts)**.

- PHP 8.1+ thuần (không framework), PDO + SQLite – không cần cài MySQL.
- Lần chạy đầu tiên tự tạo database `storage/database.sqlite` và dữ liệu mẫu (4 sản phẩm, 2 tin tức, 3 trang).

## Chạy thử

**Docker** (máy chưa cài PHP):

```bash
docker-compose up -d
```

hoặc không dùng compose:

```bash
docker run -d --name cases-cms -p 8000:8000 -v "%cd%:/app" -w /app php:8.3-cli php -S 0.0.0.0:8000 -t public public/router.php
```

**PHP cài sẵn trên máy:**

```bash
php -S localhost:8000 -t public public/router.php
```

**Apache / XAMPP:** trỏ DocumentRoot vào thư mục `public/` (khuyến nghị) và bật `mod_rewrite`. Nếu đặt cả thư mục dự án trong `htdocs`, file `.htaccess` ở gốc sẽ tự chuyển request vào `public/`.

Mở http://localhost:8000 – trang quản trị: http://localhost:8000/admin

### Tài khoản quản trị mặc định (môi trường dev)

Xem hằng `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD` trong [database/seed.php](database/seed.php). **Đổi mật khẩu ngay** ở *Admin → Tài khoản* (góc trên phải) khi triển khai thật.

Tạo lại database về dữ liệu mẫu:

```bash
php database/reset.php
```

(Docker: `docker exec cases-cms php /app/database/reset.php`)

## Tính năng CMS

| Khu vực | Chức năng |
|---|---|
| **Trang** | Thêm/sửa/xoá/nhân bản; slug tự sinh từ tiêu đề tiếng Việt; mẫu trang *Mặc định* hoặc *Liên hệ (kèm form báo giá)*; hiện trên menu + thứ tự; ảnh đại diện; SEO title/description |
| **Bài viết** | Như trên + chuyên mục (Sản phẩm / Tin tức / Kiến thức), nhãn hiển thị trên ảnh, thứ tự, đánh dấu nổi bật |
| **Soạn thảo** | Trình soạn thảo Quill (tiêu đề, định dạng, danh sách, trích dẫn, căn lề, link, chèn ảnh upload lên server) |
| **Xuất bản** | Bản nháp / Đã xuất bản, ngày đăng, hẹn giờ (ngày tương lai). Admin đăng nhập có thể xem trước bản nháp trên website |
| **Yêu cầu báo giá** | Lưu form "Nhận báo giá" từ website, đánh dấu đã xử lý, xoá |
| **Cài đặt** | Tên site, khẩu hiệu, thông tin liên hệ (footer), nội dung banner trang chủ, mô tả SEO mặc định |
| **Tài khoản** | Đổi tên, email, mật khẩu |

Liên kết CMS ↔ giao diện:

- Mục **Sản phẩm** trên trang chủ = 4 bài viết chuyên mục *Sản phẩm* đã xuất bản (sắp theo "Thứ tự"). Bài không có ảnh đại diện sẽ dùng hình minh hoạ con tôm theo prototype.
- Mục **Cập nhật từ CASES** trên trang chủ = 3 bài mới nhất thuộc chuyên mục khác.
- Trang có "Hiện trên menu" được chèn vào menu chính.

## URL

| URL | Nội dung |
|---|---|
| `/` | Trang chủ |
| `/{slug}` | Trang (vd. `/gioi-thieu`, `/lien-he`) |
| `/bai-viet`, `/bai-viet?cat=san-pham` | Danh sách bài viết, lọc theo chuyên mục |
| `/bai-viet/{slug}` | Chi tiết bài viết |
| `/admin` | Quản trị |

## Cấu trúc

```
app/            bootstrap, router, DB helpers, auth, controllers (site.php, admin.php)
config/         config.php
database/       schema.sql, seed.php, reset.php
public/         document root: index.php, router.php, assets/, uploads/
storage/        database.sqlite (tự tạo, không commit)
views/          layouts/, site/, admin/, partials/
```

## Bảo mật đã có

CSRF token cho mọi form POST, mật khẩu `password_hash`, khoá đăng nhập 60s sau 5 lần sai, prepared statement, escape output, kiểm tra MIME ảnh upload bằng `finfo` + tên file ngẫu nhiên, chặn thực thi script trong `uploads/`, lọc `<script>`/`on*=`/`javascript:` khỏi HTML soạn thảo, honeypot chống spam form liên hệ.

> Nội dung HTML trong bài viết do quản trị viên soạn được coi là tin cậy; nếu mở cho nhiều người soạn bài, nên thay `clean_html()` bằng thư viện như HTML Purifier.
