/**
 * Điểm vào (entry) của Cloudflare Worker, trỏ tới bởi `"main": "./src/worker.ts"`
 * trong wrangler.jsonc. Cloudflare chạy file này cho MỌI request tới site (cả
 * trang công khai lẫn /_emdash/admin) và cho các cron trigger.
 *
 * Thành phần:
 *   - `handler` (từ @emdash-cms/cloudflare/worker) bọc handler server của
 *     @astrojs/cloudflare, tức là toàn bộ app Astro SSR (output "server"): các
 *     trang trong src/pages/, API và admin của EmDash.
 *   - `PluginBridge`: Durable Object dùng cho plugin chạy trong sandbox
 *     (Worker Loader). Phải được export từ module entry thì binding mới tìm thấy
 *     nó, kể cả khi dự án chưa bật plugin sandbox (binding LOADER đang để comment
 *     trong wrangler.jsonc).
 *   - `scheduled`: handler cho cron. wrangler.jsonc khai báo cron "* * * * *"
 *     (mỗi phút); mỗi lần chạy, EmDash thực hiện bảo trì chung như xuất bản các
 *     nội dung đã hẹn giờ và làm mới cache liên quan.
 *
 * Dùng file entry riêng này (thay vì entry mặc định của adapter) để vừa giữ
 * `fetch` của Astro vừa thêm `scheduled` và export Durable Object.
 */
import handler, { createScheduledHandler, PluginBridge } from "@emdash-cms/cloudflare/worker";

// Re-export bắt buộc: Cloudflare tìm class Durable Object theo tên ngay trên module entry.
export { PluginBridge };

export default {
	// Giữ nguyên `fetch` (và mọi handler khác) của app Astro.
	...handler,
	// Bổ sung handler cron. Không truyền tuỳ chọn nên chấp nhận mọi cron expression khai
	// báo trong wrangler.jsonc, vì file đó hiện chỉ có một cron.
	scheduled: createScheduledHandler(),
	// `satisfies` kiểm tra object khớp kiểu ExportedHandler của Workers mà không làm
	// rộng kiểu của nó.
} satisfies ExportedHandler;
