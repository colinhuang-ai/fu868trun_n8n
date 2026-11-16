<?php
/**
 * ============================================================================
 *  app/auth.php – XÁC THỰC NGƯỜI DÙNG (ĐĂNG NHẬP / ĐĂNG XUẤT)
 * ============================================================================
 *
 * Cơ chế: sau khi đăng nhập thành công, id người dùng được lưu trong session
 * ($_SESSION['user_id']). Session được lưu phía server, trình duyệt chỉ giữ
 * cookie 'cases_sess' chứa mã session ngẫu nhiên (cấu hình ở app/bootstrap.php).
 *
 * Các hàm:
 *   current_user()          → thông tin người đang đăng nhập (hoặc null)
 *   require_auth()          → chặn truy cập nếu chưa đăng nhập (dùng cho mọi route /admin)
 *   login_locked_seconds()  → còn bao nhiêu giây bị khoá do đăng nhập sai nhiều lần
 *   attempt_login()         → kiểm tra email + mật khẩu, đăng nhập nếu đúng
 *   logout()                → xoá session
 *
 * Được dùng bởi: app/admin.php (đăng nhập/đăng xuất), public/index.php ($guard),
 * views/layouts/*.php (hiện tên người dùng, thanh "Sửa nội dung này").
 *
 * BẢO MẬT:
 *   - Mật khẩu KHÔNG lưu dạng chữ thường mà lưu dạng băm (password_hash, mặc định bcrypt).
 *   - So sánh bằng password_verify() (chống timing attack).
 *   - session_regenerate_id() khi đăng nhập/đăng xuất để chống "session fixation".
 *   - Khoá tạm 60 giây sau 5 lần sai để làm chậm việc dò mật khẩu.
 */
declare(strict_types=1);

/**
 * Lấy thông tin người dùng đang đăng nhập.
 *
 * Kết quả được cache trong biến static: chỉ truy vấn DB một lần cho mỗi request,
 * dù hàm được gọi nhiều lần (layout, controller...).
 * Dùng `false` làm giá trị "chưa tra cứu" để phân biệt với `null` = "đã tra, không có ai đăng nhập".
 *
 * @return array|null  ['id' => .., 'name' => .., 'email' => ..] hoặc null nếu chưa đăng nhập
 *                     (không lấy cột password_hash để tránh lỡ in ra view)
 */
function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $id = $_SESSION['user_id'] ?? null;
        // Nếu session có user_id thì tra DB (phòng trường hợp tài khoản đã bị xoá → trả null).
        $user = $id ? db_one('SELECT id, name, email FROM users WHERE id = ?', [$id]) : null;
    }
    return $user;
}

/**
 * Bắt buộc phải đăng nhập. Gọi ở đầu mọi trang quản trị (qua $guard trong public/index.php).
 *
 * Nếu chưa đăng nhập: lưu lại URL đang muốn vào ($_SESSION['intended']) rồi chuyển
 * tới trang đăng nhập. Sau khi đăng nhập xong, admin_login() sẽ đưa người dùng quay lại URL đó.
 * Hàm redirect() gọi exit nên code phía sau không chạy.
 */
function require_auth(): void
{
    if (!current_user()) {
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect('/admin/login');
    }
}

/**
 * Chặn dò mật khẩu đơn giản: khoá 60s sau 5 lần sai liên tiếp.
 *
 * Thời điểm hết khoá lưu trong session ('login_lock_until', dạng Unix timestamp).
 * Lưu ý: khoá theo session (trình duyệt), là lớp bảo vệ cơ bản; muốn chặt hơn
 * có thể khoá theo IP/tài khoản trong DB.
 *
 * @return int  Số giây còn bị khoá; 0 = không bị khoá
 */
function login_locked_seconds(): int
{
    $until = $_SESSION['login_lock_until'] ?? 0;
    return max(0, $until - time());
}

/**
 * Thử đăng nhập bằng email + mật khẩu.
 *
 * @param string $email     Email người dùng nhập (không phân biệt hoa/thường, tự bỏ khoảng trắng)
 * @param string $password  Mật khẩu dạng chữ thường người dùng nhập
 * @return bool  true nếu đúng và đã đăng nhập; false nếu sai
 */
function attempt_login(string $email, string $password): bool
{
    // Email lưu trong DB ở dạng chữ thường → chuẩn hoá trước khi so sánh.
    $user = db_one('SELECT * FROM users WHERE email = ?', [mb_strtolower(trim($email))]);
    // password_verify so mật khẩu nhập với chuỗi băm đã lưu.
    if ($user && password_verify($password, $user['password_hash'])) {
        // Tạo mã session MỚI (và xoá mã cũ) → kẻ xấu biết trước mã session cũ cũng vô dụng.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        // Đăng nhập đúng → xoá bộ đếm lần sai và trạng thái khoá.
        unset($_SESSION['login_fails'], $_SESSION['login_lock_until']);
        // Nếu PHP có thuật toán băm mới/mạnh hơn (hoặc đổi cost) thì băm lại mật khẩu và cập nhật DB.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            db_update('users', (int) $user['id'], ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        }
        return true;
    }

    // Sai email hoặc mật khẩu → tăng bộ đếm. Không nói rõ sai cái nào để không lộ email nào tồn tại.
    $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
    // Đủ 5 lần sai → khoá 60 giây và đặt lại bộ đếm.
    if ($_SESSION['login_fails'] >= 5) {
        $_SESSION['login_lock_until'] = time() + 60;
        $_SESSION['login_fails'] = 0;
    }
    return false;
}

/**
 * Đăng xuất: xoá toàn bộ dữ liệu session và cấp mã session mới.
 * Được gọi bởi admin_logout() (route POST /admin/logout, có kiểm tra CSRF).
 */
function logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
