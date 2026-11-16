/**
 * ============================================================================
 * astro.config.mjs — Cấu hình trung tâm của website CASES
 * ============================================================================
 *
 * Website này = Astro (SSR) + EmDash CMS + Tailwind v4, chạy trên Cloudflare
 * Workers với D1 (database) và R2 (lưu ảnh/media). Site song ngữ:
 *   - Tiếng Anh (mặc định):  /about, /products, ...
 *   - Tiếng Việt:            /vi/about, /vi/products, ...
 *
 * File này khai báo:
 *   1. adapter + chế độ render (server-side rendering trên Cloudflare)
 *   2. cấu hình i18n (2 locale, KHÔNG prefix locale mặc định)
 *   3. plugin Vite (Tailwind v4)
 *   4. integrations: React (cho admin UI của EmDash) + EmDash (CMS)
 *   5. fonts (Google Fonts, được Astro tải về & self-host lúc build)
 *
 * Liên quan: wrangler.jsonc (khai báo binding DB / MEDIA mà file này tham
 * chiếu), src/styles/global.css (dùng các biến font --ff-*),
 * src/i18n/ui.ts (chuỗi giao diện theo locale).
 */

// Adapter để Astro build ra Cloudflare Worker (thay vì Node server).
import cloudflare from "@astrojs/cloudflare";
// Integration React: bắt buộc vì giao diện admin của EmDash (/_emdash/admin)
// được viết bằng React. Site công khai vẫn chủ yếu là .astro (không ship JS thừa).
import react from "@astrojs/react";
// Hai "driver" của EmDash cho hạ tầng Cloudflare:
//   - d1: database SQLite phía Cloudflare (lưu schema, nội dung, user, menu...)
//   - r2: object storage (lưu ảnh/tệp upload từ admin)
import { d1, r2 } from "@emdash-cms/cloudflare";
// Tailwind v4 chạy như plugin Vite (không còn tailwind.config.js / PostCSS).
// Theme được khai báo bằng CSS trong src/styles/global.css (@theme).
import tailwindcss from "@tailwindcss/vite";
// `fontProviders` là API Fonts của Astro: tự tải font, sinh @font-face,
// preload, và expose thành CSS variable.
import { defineConfig, fontProviders } from "astro/config";
// Integration chính của EmDash CMS: đăng ký route admin/API, schema, loader nội dung...
import emdash from "emdash/astro";

export default defineConfig({
	// "server" = mọi trang được render theo từng request (SSR), không prerender.
	// Lý do: nội dung CMS thay đổi qua admin và phải hiện ngay mà không cần build lại;
	// vì vậy KHÔNG dùng getStaticPaths() cho trang lấy từ CMS (xem AGENTS.md).
	output: "server",

	// Build ra Cloudflare Worker; entry thực tế là src/worker.ts (xem wrangler.jsonc).
	adapter: cloudflare(),

	// Cấu hình mặc định cho <Image> / <Picture> của Astro.
	image: {
		// "constrained": ảnh co giãn theo container nhưng không phóng to quá kích thước gốc;
		// Astro sinh srcset + sizes tự động.
		layout: "constrained",
		// Astro tự chèn CSS responsive cho ảnh (width/height/object-fit) thay vì
		// bắt ta tự viết style cho từng ảnh.
		responsiveStyles: true,
	},

	// English is served unprefixed (/about), Vietnamese under /vi (/vi/about).
	// Never prefix the default locale: that breaks the EmDash admin routes.
	i18n: {
		// Locale mặc định = tiếng Anh, URL không có tiền tố. KHÔNG bật
		// `routing.prefixDefaultLocale` — nếu /en/... xuất hiện thì route admin
		// /_emdash/admin sẽ bị lệch và hỏng.
		defaultLocale: "en",
		// Danh sách locale hỗ trợ. Astro.currentLocale sẽ trả về một trong hai giá trị này
		// (dựa vào tiền tố URL /vi). Muốn thêm ngôn ngữ: thêm vào đây, thêm thư mục
		// src/pages/<locale>/, thêm chuỗi trong src/i18n/ui.ts và bản dịch trong seed.
		locales: ["en", "vi"],
		// Nếu trang /vi/... không tồn tại thì Astro rewrite/fallback về bản tiếng Anh
		// thay vì trả 404 ngay.
		fallback: { vi: "en" },
	},

	vite: {
		// Kích hoạt Tailwind v4 cho toàn bộ build (dev + production).
		plugins: [tailwindcss()],
	},

	integrations: [
		// Cần cho admin UI của EmDash (React 19, xem package.json).
		react(),
		emdash({
			// Database: Cloudflare D1, truy cập qua binding tên "DB" (khai báo trong wrangler.jsonc).
			//  - session: "auto"  → dùng D1 Sessions API để đọc nhất quán sau khi ghi
			//                       (read-your-writes) mà vẫn tận dụng read replica.
			//  - coalesce: true   → gộp các truy vấn giống hệt nhau trong cùng một request
			//                       để giảm số lần gọi D1.
			database: d1({ binding: "DB", session: "auto", coalesce: true }),
			// Lưu media (ảnh upload từ admin) vào R2, binding "MEDIA" (bucket `cases-media`).
			storage: r2({ binding: "MEDIA" }),
		}),
	],

	// Astro Fonts API: mỗi font được tải từ Google Fonts lúc build, self-host,
	// và gắn vào một CSS variable. Các variable này được global.css đưa vào
	// theme Tailwind (font-sans, font-display, font-jakarta, font-headline).
	// `subsets` có "vietnamese" để hiển thị đúng dấu tiếng Việt — rất quan trọng
	// vì site song ngữ; thiếu subset này chữ có dấu sẽ rơi về font fallback.
	fonts: [
		{
			// Inter: font chữ nội dung (body text).
			provider: fontProviders.google(),
			name: "Inter",
			cssVariable: "--ff-inter",
			weights: [300, 400, 500, 600, 700],
			subsets: ["latin", "latin-ext", "vietnamese"],
			fallbacks: ["sans-serif"],
		},
		{
			// Plus Jakarta Sans: font tiêu đề / nhãn (heading).
			provider: fontProviders.google(),
			name: "Plus Jakarta Sans",
			cssVariable: "--ff-jakarta",
			weights: [400, 500, 600, 700, 800],
			subsets: ["latin", "latin-ext", "vietnamese"],
			fallbacks: ["sans-serif"],
		},
		{
			// Playfair Display (chỉ weight 700): font có chân dành riêng cho headline
			// ở khối hero; chỉ tải 1 weight để giảm dung lượng.
			provider: fontProviders.google(),
			name: "Playfair Display",
			cssVariable: "--ff-playfair",
			weights: [700],
			subsets: ["latin", "latin-ext", "vietnamese"],
			// Serif fallback giống kiểu chữ để tránh nhảy layout khi font chưa tải xong.
			fallbacks: ["Georgia", "serif"],
		},
	],

	// Tắt thanh Dev Toolbar của Astro khi chạy `pnpm dev` (đỡ che giao diện/tốn RAM).
	devToolbar: { enabled: false },
});
