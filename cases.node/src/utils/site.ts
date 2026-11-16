/*
 * utils/site.ts — chuẩn hóa "danh tính site" (tên, tagline, logo, ký tự ngăn cách tiêu đề)
 * lấy từ Site Settings của EmDash (admin: Settings -> General/SEO).
 *
 * Vai trò & data flow:
 *   Base.astro gọi getSiteSettingsWithCacheHint() (đọc cài đặt từ D1) -> truyền `settingsResult.data`
 *   vào resolveSiteIdentity() -> nhận về 4 giá trị đã "làm sạch" và luôn có giá trị hợp lý.
 *   Kết quả được dùng cho: tên thương hiệu ở header/footer, logo, thẻ <title>
 *   (dạng "Trang  —  Tên site") và siteName cho SEO/Open Graph.
 *
 * Tại sao cần một hàm riêng thay vì đọc settings trực tiếp trong layout:
 *   - Các trường trong settings đều có thể trống/undefined (site mới, hoặc admin xóa giá trị).
 *   - Gom logic "fallback" vào một chỗ để layout gọn và dễ test.
 *
 * Chỉ import type SiteSettings từ "emdash" (xóa sạch lúc build, không tốn bundle).
 */
// Kiểu dữ liệu cài đặt site của EmDash (title, tagline, logo, seo.titleSeparator...).
import type { SiteSettings } from "emdash";

// Tên dự phòng khi admin chưa đặt (hoặc để trống) title. Header luôn cần một cái tên để hiển thị
// nên đây là trường hợp DUY NHẤT được phép "tự điền". Nên đặt lại title trong admin cho site thật.
const DEFAULT_SITE_TITLE = "My Site";
// Dấu ngăn cách mặc định giữa tên trang và tên site trong <title>. Là em dash (U+2014), có
// dấu cách hai bên khi Base.astro ghép chuỗi. Có thể đổi trong admin (SEO settings).
const DEFAULT_TITLE_SEPARATOR = "—";

/**
 * Site identity from the admin's settings. Cleared values stay cleared: only a
 * missing or blank title falls back, because the header always needs a name.
 */
export function resolveSiteIdentity(settings: Partial<SiteSettings> | undefined) {
	// Bốn trường trả về, mỗi trường có quy tắc fallback riêng (xem từng dòng).
	return {
		// siteTitle: `?.trim()` bỏ khoảng trắng đầu/cuối rồi toán tử `||` (không phải `??`) cố ý:
		// chuỗi rỗng "" là falsy nên cũng rơi về DEFAULT_SITE_TITLE. Tức là title bị xóa trắng
		// vẫn không để header trống.
		siteTitle: settings?.title?.trim() || DEFAULT_SITE_TITLE,
		// siteTagline: dùng `??` (chỉ thay khi null/undefined) nên giữ nguyên "ý định xóa" của admin;
		// kết quả rỗng "" nghĩa là không có tagline. LƯU Ý: Base.astro hiện KHÔNG dùng giá trị này
		// (tagline hiển thị lấy từ i18n key "brand.tagline" để dịch được theo ngôn ngữ).
		siteTagline: settings?.tagline?.trim() ?? "",
		// siteLogo: chỉ trả về object logo khi nó có `url` hợp lệ, ngược lại null để nơi gọi dùng
		// `?? company.logo` (logo mặc định trong site.config.ts). Trả cả object (không chỉ url)
		// để sau này có thể dùng thêm alt/width/height nếu cần.
		siteLogo: settings?.logo?.url ? settings.logo : null,
		// titleSeparator: cùng kiểu với siteTitle: trống/blank thì dùng em dash mặc định.
		titleSeparator: settings?.seo?.titleSeparator?.trim() || DEFAULT_TITLE_SEPARATOR,
	};
}
