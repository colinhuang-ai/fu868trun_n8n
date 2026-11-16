<?php
/**
 * ============================================================================
 *  app/Router.php – BỘ ĐỊNH TUYẾN (ROUTER) TỐI GIẢN
 * ============================================================================
 *
 * Router quyết định: với một URL + phương thức HTTP (GET/POST) thì gọi hàm nào.
 * Các route được khai báo trong public/index.php, ví dụ:
 *
 *     $router->get('/bai-viet/{slug}', 'site_post_show');
 *
 * Nghĩa là: request GET /bai-viet/tom-su  → gọi site_post_show(slug: 'tom-su').
 *
 * Cách hoạt động:
 *   - Khi khai báo, mỗi pattern như '/admin/{type}/{id}/edit' được đổi thành biểu
 *     thức chính quy (regex) có "named group":  #^/admin/(?P<type>[^/]+)/(?P<id>[^/]+)/edit$#u
 *   - Khi dispatch, router duyệt lần lượt từng route THEO THỨ TỰ KHAI BÁO; route
 *     đầu tiên khớp cả URL lẫn phương thức sẽ được gọi. Vì vậy route cụ thể phải
 *     khai báo trước route tổng quát (xem public/index.php).
 *   - Tham số {name} được truyền vào hàm xử lý dưới dạng NAMED ARGUMENT (PHP 8),
 *     nên tên trong {} phải trùng tên tham số của hàm (vd. {slug} ↔ $slug).
 */
declare(strict_types=1);

/**
 * Router tối giản: GET/POST + tham số dạng {name}.
 *
 * `final` = không cho class khác kế thừa (không cần thiết ở đây, giữ code đơn giản).
 */
final class Router
{
    /**
     * Danh sách route đã khai báo. Mỗi phần tử là mảng 3 giá trị:
     *   [0] => phương thức HTTP ('GET' hoặc 'POST')
     *   [1] => regex đã biên dịch từ pattern
     *   [2] => hàm xử lý (tên hàm dạng chuỗi hoặc closure)
     *
     * @var array<int, array{0:string,1:string,2:callable}>
     */
    private array $routes = [];

    /**
     * Khai báo route cho phương thức GET (xem trang).
     *
     * @param string   $pattern  Mẫu URL, vd. '/bai-viet/{slug}'
     * @param callable $handler  Hàm sẽ được gọi khi khớp, vd. 'site_post_show'
     */
    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /**
     * Khai báo route cho phương thức POST (gửi form: lưu, xoá, đăng nhập...).
     *
     * @param string   $pattern  Mẫu URL
     * @param callable $handler  Hàm xử lý
     */
    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /**
     * Biên dịch pattern thành regex rồi lưu vào danh sách route.
     *
     * Ví dụ: '/admin/{type}/{id}' → '#^/admin/(?P<type>[^/]+)/(?P<id>[^/]+)$#u'
     *   - (?P<type>...) là "named group": kết quả preg_match sẽ có khoá 'type'.
     *   - [^/]+ nghĩa là "một hoặc nhiều ký tự không phải dấu /" → mỗi tham số chỉ là 1 đoạn URL.
     *   - ^ và $ buộc khớp TOÀN BỘ đường dẫn, không khớp một phần.
     *   - cờ u: xử lý chuỗi UTF-8 (slug tiếng Việt đã được bỏ dấu nhưng vẫn an toàn).
     *
     * @param string   $method   'GET' | 'POST'
     * @param string   $pattern  Mẫu URL
     * @param callable $handler  Hàm xử lý
     */
    private function add(string $method, string $pattern, callable $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#u';
        $this->routes[] = [$method, $regex, $handler];
    }

    /**
     * Tìm route khớp với request hiện tại và gọi hàm xử lý.
     *
     * Kết quả có thể là:
     *   - Khớp URL + đúng phương thức → gọi handler với các tham số trong URL.
     *   - Khớp URL nhưng sai phương thức (vd. GET vào URL chỉ nhận POST) → 405 Method Not Allowed.
     *   - Không khớp route nào → abort(404) hiển thị trang "Không tìm thấy".
     *
     * @param string $method  Phương thức HTTP của request ($_SERVER['REQUEST_METHOD'])
     * @param string $path    Đường dẫn đã chuẩn hoá (đã bỏ base path, query string), vd. '/bai-viet/tom-su'
     */
    public function dispatch(string $method, string $path): void
    {
        // Ghi nhớ: đã có route khớp URL nhưng khác phương thức chưa? (để trả 405 thay vì 404)
        $allowed = false;
        foreach ($this->routes as [$m, $regex, $handler]) {
            // URL không khớp pattern → thử route kế tiếp.
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            // URL khớp nhưng phương thức khác → đánh dấu rồi thử tiếp
            // (có thể có route khác cùng URL đúng phương thức, vd. GET và POST /admin/settings).
            if ($m !== $method) {
                $allowed = true;
                continue;
            }
            // preg_match trả về cả khoá số (0, 1, 2...) lẫn khoá tên ('slug', 'id'...).
            // Chỉ giữ khoá tên (chuỗi) để truyền thành named arguments.
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            // Gọi hàm xử lý: ['slug' => 'tom-su'] → $handler(slug: 'tom-su').
            $handler(...$params);
            return;
        }
        if ($allowed) {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }
        // Không route nào khớp → trang lỗi 404 (hàm abort trong app/helpers.php, sẽ exit).
        abort(404);
    }
}
