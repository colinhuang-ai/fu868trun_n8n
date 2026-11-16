/*
 * i18n/ui.ts — hệ thống i18n tự viết (nhỏ gọn, không dùng thư viện) cho CHROME GIAO DIỆN của site:
 * menu/nút/nhãn ở header, footer, panel liên hệ, catalog, bảng chứng nhận, trang tin tức, 404...
 *
 * PHÂN CHIA TRÁCH NHIỆM (quan trọng):
 *   - File này: chuỗi "khung" của template — ít thay đổi, nằm trong code, build cùng site.
 *   - EmDash CMS / scripts/build-seed.mjs: nội dung trang, sản phẩm, tin tức... mỗi locale là
 *     một bản ghi riêng liên kết với nhau bằng `translationOf`.
 *   - src/site.config.ts: dữ kiện không cần dịch (điện thoại, email, logo).
 *   Theo AGENTS.md: không hard-code copy trang trong component; chuỗi UI chrome phải có ở CẢ HAI
 *   ngôn ngữ trong file này.
 *
 * CẤU TRÚC FILE (đọc từ trên xuống):
 *   1. LOCALES / Locale / DEFAULT_LOCALE  — danh sách ngôn ngữ hỗ trợ.
 *   2. toLocale()                         — ép giá trị bất kỳ (vd Astro.currentLocale) về Locale hợp lệ.
 *   3. `en` (as const)                    — bảng chuỗi tiếng Anh, là "bản gốc" định nghĩa tập key.
 *   4. UiKey                              — kiểu union của mọi key, suy ra TỰ ĐỘNG từ `en`.
 *   5. `vi: Record<UiKey, string>`        — bảng tiếng Việt; trình biên dịch buộc phải đủ key.
 *   6. dictionaries + useTranslations()   — chọn bảng theo locale, trả về hàm t(key).
 *   7. localizePath() / stripLocale()     — thêm/bỏ tiền tố /vi cho đường dẫn nội bộ.
 *
 * AI IMPORT FILE NÀY:
 *   - src/layouts/Base.astro (t(), LOCALES, localizePath, stripLocale, toLocale, kiểu Locale).
 *   - src/pages/*.astro, src/pages/vi/*.astro (useTranslations("en"|"vi") cho 404, trang chủ rỗng).
 *   - src/components/PageView.astro, news/*, blocks/* (toLocale(Astro.currentLocale) + t()).
 *   - src/utils/load-page.ts, news.ts, links.ts (localizePath để dựng URL theo locale / alternates).
 *
 * CÁCH THÊM MỘT CHUỖI MỚI:
 *   1. Thêm key vào bảng `en` (đặt trong nhóm tiền tố phù hợp: "nav.", "footer.", "contact."...).
 *   2. Thêm cùng key vào bảng `vi`. Nếu quên, `pnpm exec astro check` báo lỗi vì `vi` là
 *      Record<UiKey, string> (thiếu thuộc tính). UiKey tự cập nhật, không cần khai báo thêm.
 *   3. Dùng: const t = useTranslations(toLocale(Astro.currentLocale)); rồi t("nhom.key").
 *
 * CÁCH THÊM MỘT NGÔN NGỮ MỚI (ví dụ "fr"):
 *   1. Thêm "fr" vào mảng LOCALES (Locale tự mở rộng).
 *   2. Sửa toLocale(): hiện hard-code `value === "vi"` nên locale mới sẽ bị ép về "en".
 *   3. Tạo bảng `fr: Record<UiKey, string>` đầy đủ key và đăng ký vào `dictionaries`.
 *   4. localizePath()/stripLocale() đã tổng quát theo LOCALES nên tự xử lý tiền tố /fr.
 *   5. Ngoài file này: khai báo locale trong astro.config.mjs (i18n.locales) và cấu hình i18n
 *      của EmDash, tạo thư mục src/pages/fr/ (như src/pages/vi/), thêm bản dịch vào
 *      scripts/build-seed.mjs, và kiểm tra font có subset phù hợp (xem Base.astro).
 */
/**
 * Template chrome strings (header, footer, contact panel, catalog and table
 * labels) in each locale. Editable page copy lives in EmDash, not here.
 */

// Danh sách ngôn ngữ hỗ trợ. `as const` biến mảng thành tuple chỉ-đọc với kiểu literal
// ("en" | "vi") thay vì string[]; Base.astro lặp qua mảng này để dựng nút chuyển EN | VI.
export const LOCALES = ["en", "vi"] as const;
// Kiểu union "en" | "vi" được suy ra trực tiếp từ LOCALES: thêm ngôn ngữ vào mảng là kiểu
// tự cập nhật, không phải sửa ở hai nơi. `(typeof LOCALES)[number]` = kiểu của mọi phần tử.
export type Locale = (typeof LOCALES)[number];
// Ngôn ngữ mặc định, KHÔNG có tiền tố URL (EN ở "/", VI ở "/vi/"). Theo AGENTS.md không được bật
// prefixDefaultLocale vì sẽ làm hỏng /_emdash/admin. localizePath/stripLocale dựa vào hằng số này.
export const DEFAULT_LOCALE: Locale = "en";

/**
 * Chuẩn hóa một giá trị bất kỳ thành Locale hợp lệ.
 *
 * Nhận `Astro.currentLocale` (có thể là undefined khi route không khớp cấu hình i18n, ví dụ
 * một số trang lỗi) hoặc null. Chỉ "vi" mới ra "vi"; MỌI giá trị khác (undefined, null,
 * "en", mã lạ) đều ra "en" = locale mặc định. Nhờ vậy nơi gọi luôn nhận về kiểu Locale
 * chặt chẽ và không cần tự kiểm tra. AGENTS.md yêu cầu mọi lời gọi getEmDashCollection /
 * getEmDashEntry / getMenu đều truyền `locale` lấy từ hàm này.
 */
export function toLocale(value: string | undefined | null): Locale {
	return value === "vi" ? "vi" : "en";
}

// BẢNG TIẾNG ANH — bản gốc, định nghĩa tập key hợp lệ của toàn hệ thống.
// Quy ước key: "<nhóm>.<tên>" bằng camelCase. `as const` (cuối bảng) giữ nguyên kiểu literal
// của từng key để UiKey suy ra được; giá trị vẫn là string.
const en = {
	// --- brand.*: nhận diện thương hiệu, dùng ở header/footer/panel liên hệ và thẻ <title>. ---
	// Tagline dưới tên thương hiệu ở header (chỉ hiện từ breakpoint sm trở lên).
	"brand.tagline": "Ca Mau Seafood",
	// Tên pháp lý đầy đủ: dùng ở <title> trang chủ ("Tên site — Tên pháp lý") và chân panel liên hệ.
	"brand.legalName": "CaMau Seafood Processing & Service JSC",
	// Địa chỉ một dòng (cột trái footer). Hai khóa addressLine1/2 là cùng địa chỉ chia đôi để panel
	// liên hệ xuống dòng đẹp bằng <br/>.
	"brand.address": "04 Nguyen Cong Tru, Ly Van Lam Ward, Camau Province, Viet Nam",
	"brand.addressLine1": "04 Nguyen Cong Tru, Ly Van Lam Ward,",
	"brand.addressLine2": "Camau Province, Vietnam",
	// Năm thành lập, hiện ở chân panel liên hệ cạnh tên pháp lý.
	"brand.est": "Est. 1996",
	// Mô tả ngắn: hiện ở footer và là giá trị DỰ PHÒNG cho meta description khi trang không có
	// mô tả SEO riêng (Base.astro: `description || t("brand.description")`).
	"brand.description":
		"CaMau Seafood Processing and Service Joint-Stock Corporation. Leading Vietnam's premium seafood export market since 1996.",
	// --- nav.*: điều hướng. Chủ yếu là nhãn aria-label (đọc bởi trình đọc màn hình) và nút liên hệ. ---
	// "nav.label": aria-label cho menu chính (cả bản desktop lẫn mobile).
	"nav.label": "Primary",
	// "nav.menu": aria-label cho nút hamburger mở menu mobile.
	"nav.menu": "Menu",
	// "nav.language": aria-label cho cụm liên kết chuyển ngôn ngữ EN | VI.
	"nav.language": "Language",
	// "nav.contact": chữ trên nút mở panel liên hệ ở header.
	"nav.contact": "Contact",
	// Chữ của liên kết "Skip to content" (a11y): hiện khi tab vào đầu trang để nhảy tới #main.
	"skip": "Skip to content",
	// --- footer.*: tiêu đề các cột và dòng bản quyền. ---
	"footer.contactInfo": "Contact Info",
	"footer.mainPlants": "Main Plants",
	"footer.rights": "All Rights Reserved.",
	// --- contact.*: panel liên hệ trượt từ phải (Base.astro) và form bên trong. ---
	// eyebrow = dòng chữ nhỏ viết hoa phía trên tiêu đề; title gắn aria-labelledby của dialog.
	"contact.eyebrow": "Get In Touch",
	"contact.title": "Contact CASES",
	// "contact.close": aria-label cho nút đóng (nút chỉ hiển thị dấu ✕).
	"contact.close": "Close",
	// Nhãn các thẻ thông tin (điện thoại, email, địa chỉ) trong panel.
	"contact.phone": "Phone",
	"contact.email": "Email",
	"contact.address": "Address",
	// Nhãn các trường form: *.name/company/emailAddress/message là label, *Placeholder là gợi ý trong ô nhập.
	"contact.sendMessage": "Send a Message",
	"contact.name": "Full Name",
	"contact.namePlaceholder": "John Smith",
	"contact.company": "Company",
	"contact.companyPlaceholder": "Your Company",
	"contact.emailAddress": "Email Address",
	"contact.emailPlaceholder": "you@company.com",
	"contact.message": "Message",
	"contact.messagePlaceholder":
		"Tell us about your inquiry — product interests, volume requirements, market destination...",
	// Chữ nút gửi và dòng chú thích dưới form (form không gửi lên server mà mở ứng dụng email).
	"contact.submit": "Send Message",
	"contact.note": "Opens your email app with the message filled in.",
	// Tiền tố tiêu đề email, script ghép thành "<subject> <tên> (<công ty>)". Được truyền vào form qua
	// thuộc tính data-subject, không đưa trực tiếp vào JS để script không phải biết ngôn ngữ.
	"contact.subject": "Inquiry from",
	// --- catalog.*: khối danh mục sản phẩm (components/blocks/Catalog.astro) và modal chi tiết. ---
	"catalog.viewProducts": "View products",
	"catalog.collapse": "Collapse",
	"catalog.skus": "SKUs",
	"catalog.addImage": "Photo soon",
	"catalog.size": "Size Range",
	"catalog.pack": "Pack Form",
	"catalog.origin": "Origin",
	"catalog.glaze": "Glaze",
	"catalog.previous": "Previous product",
	"catalog.next": "Next product",
	"catalog.close": "Close",
	"catalog.empty": "No products in this category yet.",
	// --- photo.*: nhãn khung giữ chỗ khi sản phẩm/nhà máy/công nghệ chưa có ảnh. ---
	"photo.placeholder": "Photo",
	// --- certs.*: tiêu đề cột bảng chứng nhận (components/blocks/CertTable.astro). ---
	"certs.certification": "Certification",
	"certs.issuer": "Issuing Body",
	"certs.scope": "Scope",
	"certs.status": "Status",
	// --- news.*: trang danh sách và bài viết tin tức (components/news/*). ---
	"news.eyebrow": "Latest Updates",
	"news.title": "News",
	"news.intro": "Announcements, trade fairs and export updates from CASES.",
	"news.readMore": "Read more",
	"news.all": "All news",
	"news.more": "More news",
	"news.older": "Older news",
	"news.newest": "Latest news",
	"news.empty": "No news yet. Please check back soon.",
	"news.write": "Write an article",
	// --- page.*: thông báo cho biên tập viên khi trang rỗng/chưa tạo (chỉ hiện với người đã đăng nhập). ---
	"page.empty": "This page has no blocks yet. Add some in the editor, then publish.",
	"page.edit": "Edit page",
	"page.createHome": "Create or publish a page with the slug “home” to show it here.",
	"page.create": "Create page",
	// --- notFound.*: trang 404 theo từng locale (src/pages/404.astro và vi/404.astro). ---
	"notFound.title": "Page not found",
	"notFound.body": "The page you were looking for has moved or never existed.",
	"notFound.home": "Back to home",
} as const;

// UiKey = hợp của tất cả key trong `en` (vd "brand.tagline" | "nav.menu" | ...). Đây là kiểu của
// tham số t(). Gõ sai key sẽ là lỗi biên dịch, không phải chuỗi rỗng lúc chạy.
export type UiKey = keyof typeof en;

// BẢNG TIẾNG VIỆT. Khai báo kiểu Record<UiKey, string> nên trình biên dịch BẮT BUỘC có đủ mọi key
// của `en` và không cho key thừa. Giữ nguyên thứ tự/nhóm với bảng `en` để dễ đối chiếu.
// Dịch theo nghĩa, không dịch từng chữ; giữ tên riêng (CASES, SKU) nguyên dạng. Bản dịch chưa
// được người bản ngữ duyệt (lưu ý chung của dự án).
const vi: Record<UiKey, string> = {
	// --- brand.* (thương hiệu, bản tiếng Việt có dấu) ---
	"brand.tagline": "Cà Mau Seafood",
	"brand.legalName": "Công ty CP Chế biến và Dịch vụ Thủy sản Cà Mau",
	"brand.address": "04 Nguyễn Công Trứ, Phường Lý Văn Lâm, Tỉnh Cà Mau, Việt Nam",
	"brand.addressLine1": "04 Nguyễn Công Trứ, Phường Lý Văn Lâm,",
	"brand.addressLine2": "Tỉnh Cà Mau, Việt Nam",
	"brand.est": "Thành lập 1996",
	"brand.description":
		"Công ty CP Chế biến và Dịch vụ Thủy sản Cà Mau. Dẫn đầu thị trường xuất khẩu thủy sản cao cấp Việt Nam từ năm 1996.",
	// --- nav.* ---
	"nav.label": "Điều hướng chính",
	"nav.menu": "Menu",
	"nav.language": "Ngôn ngữ",
	"nav.contact": "Liên Hệ",
	"skip": "Bỏ qua đến nội dung",
	// --- footer.* ---
	"footer.contactInfo": "Thông Tin Liên Hệ",
	"footer.mainPlants": "Nhà Máy Chính",
	"footer.rights": "Bảo lưu mọi quyền.",
	// --- contact.* (nhãn viết hoa chữ cái đầu từng từ để khớp phong cách tiêu đề của bản EN) ---
	"contact.eyebrow": "Kết Nối Với Chúng Tôi",
	"contact.title": "Liên Hệ CASES",
	"contact.close": "Đóng",
	"contact.phone": "Điện thoại",
	"contact.email": "Email",
	"contact.address": "Địa chỉ",
	"contact.sendMessage": "Gửi Tin Nhắn",
	"contact.name": "Họ và Tên",
	"contact.namePlaceholder": "Nguyễn Văn A",
	"contact.company": "Công Ty",
	"contact.companyPlaceholder": "Tên công ty",
	"contact.emailAddress": "Địa Chỉ Email",
	"contact.emailPlaceholder": "ban@congty.com",
	"contact.message": "Nội Dung",
	"contact.messagePlaceholder":
		"Cho chúng tôi biết nhu cầu của bạn — sản phẩm quan tâm, sản lượng, thị trường đích...",
	"contact.submit": "Gửi Tin Nhắn",
	"contact.note": "Mở ứng dụng email của bạn với nội dung đã điền sẵn.",
	"contact.subject": "Yêu cầu từ",
	// --- catalog.* ---
	"catalog.viewProducts": "Xem sản phẩm",
	"catalog.collapse": "Thu gọn",
	"catalog.skus": "SKU",
	"catalog.addImage": "Sắp có ảnh",
	"catalog.size": "Kích cỡ",
	"catalog.pack": "Dạng đóng gói",
	"catalog.origin": "Xuất xứ",
	"catalog.glaze": "Mạ băng",
	"catalog.previous": "Sản phẩm trước",
	"catalog.next": "Sản phẩm sau",
	"catalog.close": "Đóng",
	"catalog.empty": "Danh mục này chưa có sản phẩm.",
	// --- photo.* ---
	"photo.placeholder": "Ảnh",
	// --- certs.* ---
	"certs.certification": "Chứng Nhận",
	"certs.issuer": "Tổ Chức Cấp",
	"certs.scope": "Phạm Vi",
	"certs.status": "Trạng Thái",
	// --- news.* ---
	"news.eyebrow": "Cập Nhật Mới Nhất",
	"news.title": "Tin Tức",
	"news.intro": "Thông báo, hội chợ và tin xuất khẩu mới nhất từ CASES.",
	"news.readMore": "Xem tiếp",
	"news.all": "Tất cả tin tức",
	"news.more": "Tin khác",
	"news.older": "Tin cũ hơn",
	"news.newest": "Tin mới nhất",
	"news.empty": "Chưa có tin nào. Vui lòng quay lại sau.",
	"news.write": "Viết bài",
	// --- page.* (thông báo cho biên tập viên) ---
	"page.empty": "Trang này chưa có block nào. Thêm block trong trình soạn thảo rồi xuất bản.",
	"page.edit": "Sửa trang",
	"page.createHome": "Tạo hoặc xuất bản trang có slug “home” để hiển thị ở đây.",
	"page.create": "Tạo trang",
	// --- notFound.* ---
	"notFound.title": "Không tìm thấy trang",
	"notFound.body": "Trang bạn tìm đã được di chuyển hoặc không tồn tại.",
	"notFound.home": "Về trang chủ",
};

// Tra cứu bảng theo locale. Kiểu Record<Locale, ...> bảo đảm mọi locale trong LOCALES đều có bảng.
const dictionaries: Record<Locale, Record<UiKey, string>> = { en, vi };

/**
 * Tạo hàm dịch t() gắn với một locale. Dùng trong frontmatter: `const t = useTranslations(locale)`.
 *
 * Trả về closure (key: UiKey) => string. Toán tử `??` sang `en[key]` là lớp phòng vệ lúc chạy:
 * về mặt kiểu, bảng locale luôn đủ key, nhưng nếu dữ liệu bị sai (ép kiểu, JS thuần) thì người dùng
 * vẫn thấy chuỗi tiếng Anh thay vì "undefined".
 */
export function useTranslations(locale: Locale) {
	return (key: UiKey) => dictionaries[locale][key] ?? en[key];
}

/** Prefixes a root-relative path for the locale (/about → /vi/about). */
export function localizePath(path: string, locale: Locale): string {
	// Chỉ xử lý đường dẫn gốc-tương đối ("/..."). Chặn "//host" (protocol-relative = trang ngoài) và
	// mọi URL tuyệt đối/mailto:/tel:/#neo — những thứ này trả về nguyên vẹn, không thêm tiền tố.
	if (!path.startsWith("/") || path.startsWith("//")) return path;
	// Locale mặc định không có tiền tố (/about giữ nguyên).
	if (locale === DEFAULT_LOCALE) return path;
	// Trang chủ VI là "/vi/" (có dấu gạch cuối) còn các trang khác là "/vi/about" (không có) — khớp
	// với cách Astro định tuyến src/pages/vi/index.astro và vi/[slug].astro. Query/hash đi kèm được giữ.
	return path === "/" ? `/${locale}/` : `/${locale}${path}`;
}

/** Removes any locale prefix from a pathname (/vi/about → /about). */
export function stripLocale(pathname: string): string {
	// Chỉ thử các locale CÓ tiền tố (bỏ qua locale mặc định, vì nó không có tiền tố để mà gỡ).
	for (const locale of LOCALES) {
		if (locale === DEFAULT_LOCALE) continue;
		// "/vi" hoặc "/vi/" -> "/" (trang chủ). Cần xét riêng vì cắt chuỗi sẽ ra chuỗi rỗng.
		if (pathname === `/${locale}` || pathname === `/${locale}/`) return "/";
		// "/vi/about" -> "/about": cắt (độ dài mã locale + 1) ký tự, tức bỏ "/vi" nhưng giữ dấu "/" kế tiếp.
		// Kiểm tra `startsWith("/vi/")` có dấu gạch cuối nên "/vietnam" KHÔNG bị cắt nhầm.
		if (pathname.startsWith(`/${locale}/`)) return pathname.slice(locale.length + 1);
	}
	// Không có tiền tố locale (đang ở EN) -> trả về nguyên. Base.astro dùng kết quả để so khớp menu
	// đang active và để dựng URL EN | VI cho cùng một trang.
	return pathname;
}
