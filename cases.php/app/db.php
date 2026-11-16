<?php
/**
 * ============================================================================
 *  app/db.php – KẾT NỐI CƠ SỞ DỮ LIỆU (SQLite qua PDO) + HÀM TRUY VẤN
 * ============================================================================
 *
 * Ứng dụng dùng SQLite: toàn bộ dữ liệu nằm trong MỘT file
 * (mặc định storage/database.sqlite) → không cần cài MySQL.
 * Truy cập qua PDO (PHP Data Objects) – lớp chuẩn của PHP, nên sau này muốn
 * chuyển sang MySQL chỉ cần đổi chuỗi kết nối và một vài cú pháp SQL.
 *
 * Các hàm trong file:
 *   db()          → trả về đối tượng PDO (kết nối 1 lần, dùng lại cho cả request)
 *   db_install()  → tạo bảng (database/schema.sql) + dữ liệu mẫu (database/seed.php)
 *   db_query()    → chạy câu SQL có tham số, trả về PDOStatement
 *   db_all()      → lấy NHIỀU dòng (mảng các mảng)
 *   db_one()      → lấy MỘT dòng (mảng) hoặc null
 *   db_value()    → lấy MỘT giá trị (ô đầu tiên của dòng đầu tiên), vd. COUNT(*)
 *   db_run()      → chạy INSERT/UPDATE/DELETE, trả về số dòng bị ảnh hưởng
 *   db_insert()   → thêm dòng từ mảng ['cột' => giá trị], trả về id mới
 *   db_update()   → sửa dòng theo id từ mảng ['cột' => giá trị]
 *
 * BẢO MẬT – CHỐNG SQL INJECTION:
 *   Mọi giá trị từ người dùng đều được truyền qua THAM SỐ (? hoặc :ten) của
 *   prepared statement, KHÔNG BAO GIỜ nối thẳng vào chuỗi SQL. Ví dụ đúng:
 *       db_one('SELECT * FROM posts WHERE slug = ?', [$slug]);
 *   PDO sẽ tự escape → kẻ xấu không chèn được lệnh SQL.
 */
declare(strict_types=1);

/**
 * Trả về kết nối PDO dùng chung cho cả request.
 *
 * Dùng biến `static` để chỉ kết nối MỘT lần: lần gọi đầu tạo kết nối,
 * các lần sau trả lại đúng đối tượng đó (pattern "lazy singleton").
 *
 * Ở lần kết nối đầu tiên, nếu database chưa có bảng (file mới tạo) thì
 * tự động gọi db_install() để tạo bảng + dữ liệu mẫu → chạy là dùng được ngay.
 *
 * @return PDO
 */
function db(): PDO
{
    // Biến static giữ giá trị giữa các lần gọi hàm trong cùng một request.
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Đường dẫn file SQLite lấy từ config ('db_path').
    $path = config('db_path');
    // Tạo thư mục chứa file DB nếu chưa có (0775 = quyền đọc/ghi cho owner & group; true = tạo cả thư mục cha).
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    // Mở kết nối. SQLite tự tạo file nếu chưa tồn tại. Không cần username/password (null, null).
    $pdo = new PDO('sqlite:' . $path, null, null, [
        // Lỗi SQL sẽ ném Exception (thay vì âm thầm trả false) → dễ phát hiện bug.
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // fetch() mặc định trả mảng kết hợp ['title' => '...'] thay vì cả khoá số lẫn khoá tên.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Dùng prepared statement thật của driver (không giả lập) → an toàn và đúng kiểu dữ liệu hơn.
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    // SQLite mặc định TẮT kiểm tra khoá ngoại; bật lên để REFERENCES/ON DELETE trong schema có hiệu lực.
    $pdo->exec('PRAGMA foreign_keys = ON');
    // Nếu file DB đang bị request khác khoá ghi, chờ tối đa 5000ms thay vì báo lỗi "database is locked" ngay.
    $pdo->exec('PRAGMA busy_timeout = 5000');

    // Lần chạy đầu: tạo bảng + dữ liệu mẫu
    // Kiểm tra bảng 'settings' đã tồn tại chưa (sqlite_master là bảng hệ thống liệt kê mọi bảng).
    // Gán $pdo vào biến static TRƯỚC khi install, nên các hàm db_* gọi bên trong seed sẽ dùng lại kết nối này.
    $installed = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetchColumn();
    if (!$installed) {
        db_install($pdo);
    }

    return $pdo;
}

/**
 * Tạo cấu trúc bảng và nạp dữ liệu mẫu.
 *
 * Toàn bộ chạy trong một TRANSACTION: nếu có lỗi giữa chừng thì rollBack()
 * huỷ mọi thay đổi → không để lại database "nửa vời" (có bảng mà thiếu dữ liệu).
 * Lần request sau sẽ thử cài lại từ đầu.
 *
 * Được gọi tự động bởi db() và gián tiếp bởi database/reset.php.
 *
 * @param PDO $pdo  Kết nối vừa mở
 * @throws Throwable  Ném lại lỗi gốc sau khi rollback để hiển thị/ghi log
 */
function db_install(PDO $pdo): void
{
    // Nạp file chứa hàm seed_database() (require_once: chỉ nạp 1 lần dù gọi nhiều lần).
    require_once ROOT . '/database/seed.php';
    $pdo->beginTransaction();
    try {
        // Chạy toàn bộ câu CREATE TABLE trong schema.sql (exec chạy được nhiều câu cách nhau bởi ;).
        $pdo->exec((string) file_get_contents(ROOT . '/database/schema.sql'));
        // Thêm tài khoản admin, cài đặt, trang và bài viết mẫu.
        seed_database($pdo);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Chuẩn bị (prepare) và chạy một câu SQL với tham số.
 * Đây là hàm nền mà các hàm db_* khác đều gọi tới.
 *
 * Ví dụ:
 *   db_query('SELECT * FROM posts WHERE id = ?', [5]);              // tham số vị trí
 *   db_query('SELECT * FROM posts WHERE slug = :s', ['s' => 'abc']); // tham số đặt tên
 *
 * @param string $sql     Câu SQL, giá trị động thay bằng ? hoặc :ten
 * @param array  $params  Giá trị tương ứng cho các tham số
 * @return PDOStatement   Kết quả để fetch tiếp
 */
function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Lấy tất cả các dòng kết quả.
 *
 * Ví dụ: db_all('SELECT * FROM posts ORDER BY id DESC LIMIT 5')
 *        → [['id' => 9, 'title' => '...'], ['id' => 8, ...], ...]
 *
 * @param string $sql
 * @param array  $params
 * @return array  Mảng các dòng (mỗi dòng là mảng kết hợp); mảng rỗng nếu không có dòng nào
 */
function db_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

/**
 * Lấy một dòng duy nhất (dòng đầu tiên).
 *
 * Ví dụ: $post = db_one('SELECT * FROM posts WHERE slug = ?', [$slug]);
 *        if (!$post) abort(404);
 *
 * @param string $sql
 * @param array  $params
 * @return array|null  Dòng dữ liệu, hoặc null nếu không tìm thấy (PDO trả false → đổi thành null cho dễ dùng)
 */
function db_one(string $sql, array $params = []): ?array
{
    $row = db_query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/**
 * Lấy một giá trị đơn lẻ: cột đầu tiên của dòng đầu tiên.
 * Hay dùng cho COUNT(*), kiểm tra tồn tại (SELECT 1 ...), lấy 1 trường.
 *
 * Ví dụ: $total = (int) db_value('SELECT COUNT(*) FROM posts');
 *
 * @param string $sql
 * @param array  $params
 * @return mixed  Giá trị (chuỗi/số), hoặc null nếu không có dòng nào
 */
function db_value(string $sql, array $params = []): mixed
{
    $val = db_query($sql, $params)->fetchColumn();
    return $val === false ? null : $val;
}

/**
 * Chạy câu lệnh thay đổi dữ liệu (INSERT/UPDATE/DELETE).
 *
 * Ví dụ: db_run('DELETE FROM contacts WHERE id = ?', [3]);
 *
 * @param string $sql
 * @param array  $params
 * @return int  Số dòng bị ảnh hưởng
 */
function db_run(string $sql, array $params = []): int
{
    return db_query($sql, $params)->rowCount();
}

/**
 * Thêm một dòng mới từ mảng dữ liệu.
 *
 * Ví dụ: db_insert('contacts', ['name' => 'An', 'phone' => '0900...'])
 *   → sinh SQL: INSERT INTO contacts (name, phone) VALUES (:name, :phone)
 *   → giá trị thật được bind qua tham số (an toàn).
 *
 * Tên bảng/cột chỉ đến từ code nội bộ, không từ input người dùng.
 * (Tên bảng/cột KHÔNG thể truyền qua tham số PDO nên được nối vào chuỗi SQL;
 *  vì vậy tuyệt đối không truyền khoá mảng lấy trực tiếp từ $_POST vào đây.)
 *
 * @param string $table  Tên bảng
 * @param array  $data   ['tên_cột' => giá_trị, ...]
 * @return int  id (khoá chính AUTOINCREMENT) của dòng vừa thêm
 */
function db_insert(string $table, array $data): int
{
    // Lấy danh sách tên cột từ khoá của mảng.
    $cols = array_keys($data);
    // Ghép câu SQL: mỗi cột có một placeholder cùng tên (:title, :slug...).
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $cols),
        implode(', ', array_map(fn ($c) => ':' . $c, $cols))
    );
    db_run($sql, $data);
    return (int) db()->lastInsertId();
}

/**
 * Cập nhật một dòng theo id.
 *
 * Ví dụ: db_update('posts', 5, ['title' => 'Mới', 'status' => 'published'])
 *   → UPDATE posts SET title = :title, status = :status WHERE id = :__id
 *
 * Placeholder id đặt tên '__id' (có 2 gạch dưới) để không trùng với một cột tên 'id'
 * có thể nằm trong $data.
 *
 * @param string $table  Tên bảng (từ code nội bộ)
 * @param int    $id     Khoá chính của dòng cần sửa
 * @param array  $data   ['tên_cột' => giá_trị_mới, ...]
 */
function db_update(string $table, int $id, array $data): void
{
    // Tạo phần SET: "title = :title, status = :status"
    $set = implode(', ', array_map(fn ($c) => "$c = :$c", array_keys($data)));
    $data['__id'] = $id;
    db_run("UPDATE $table SET $set WHERE id = :__id", $data);
}
