/**
 * Tiện ích liên kết cho nội dung do editor nhập trong CMS (nút CTA, hero,
 * menu...). Quy tắc dự án: mọi liên kết nội bộ lấy từ CMS PHẢI đi qua
 * resolveHref() trước khi đưa vào thuộc tính href, vì hai lý do:
 *   1. An toàn: URL do người dùng nhập có thể là "javascript:..." hay "data:..."
 *      (XSS). sanitizeHref() của EmDash chỉ cho qua http(s), mailto, tel, đường
 *      dẫn tương đối của site ("/...") và neo ("#...").
 *   2. Đa ngôn ngữ: editor viết "/about" cho mọi bản dịch; site VI cần "/vi/about".
 *      localizePath() thêm tiền tố /vi cho locale không phải mặc định.
 */
import { sanitizeHref } from "emdash";
import { type Locale, localizePath } from "../i18n/ui";

/** A CMS-authored link, sanitized and prefixed for the current locale. */
export function resolveHref(url: string | null | undefined, locale: Locale): string | undefined {
	// Field trống/null (editor chưa nhập link) => undefined để component biết là "không có liên kết"
	// và không in thẻ <a> rỗng.
	if (!url) return undefined;
	// Bước 1, lọc an toàn. Lưu ý: sanitizeHref (emdash) trả "#" (không phải chuỗi rỗng/undefined)
	// khi URL không an toàn, nên link xấu bị vô hiệu hoá thành "#" chứ không biến mất.
	const safe = sanitizeHref(url);
	// Kiểm tra phòng thủ: với hành vi hiện tại của sanitizeHref nhánh này không bao giờ chạy
	// (kết quả luôn truthy), giữ lại phòng khi phiên bản sau trả giá trị rỗng.
	if (!safe) return undefined;
	// Bước 2, thêm tiền tố locale. localizePath chỉ sửa đường dẫn gốc "/..." (EN giữ nguyên,
	// VI thành "/vi/..."); URL tuyệt đối (https://...), "//host", "#neo", mailto:, tel: để nguyên.
	return localizePath(safe, locale);
}
