/**
 * Bộ tải dữ liệu cho các trang CMS (collection `pages`), dùng chung cho
 * src/pages/index.astro, [slug].astro và hai bản vi/. Gom logic ở đây để EN và
 * VI (hai file route riêng, xem src/pages/index.astro) không phải lặp lại.
 *
 * Kết quả trả về chính là tập props của <PageShell>: { page, title,
 * pageTitle, description, image, canonical, robots, alternates }.
 */
import { getContentSeo, getEmDashEntry, getSeoMeta, getTranslations } from "emdash";
import type { AstroGlobal } from "astro";
import { type Locale, localizePath } from "../i18n/ui";

// Slug của trang chủ trong CMS. Trang này sống ở "/" chứ không phải "/home".
const HOME_SLUG = "home";

// Đường dẫn công khai (CHƯA có prefix locale) của một slug: "home" => "/", còn lại "/{slug}".
const pathFor = (slug: string) => (slug === HOME_SLUG ? "/" : `/${slug}`);

/**
 * Loads a published page for a locale (falling back to English), with the SEO
 * fields and the EN | VI switch targets the layout needs. Returns null for a
 * missing page so the route can rewrite to its 404.
 */
// Astro được truyền vào tường minh (thay vì dùng global) vì file .ts thường không có
// biến Astro; cần nó để đọc Astro.url.origin và Astro.cache.
export async function loadPage(Astro: AstroGlobal, slug: string | undefined, locale: Locale) {
	// slug undefined (param hỏng/decodeSlug thất bại) => coi như không tìm thấy.
	if (!slug) return null;
	// Truy vấn đọc D1 lúc request. Luôn truyền `locale` để không lẫn bản dịch. Nếu chưa có
	// bản dịch đã publish, EmDash tự đi theo chuỗi fallback i18n ("vi" -> "en") và trả bản
	// tiếng Anh. `entry` là null khi không có (ở mọi locale trong chuỗi) hoặc chưa publish.
	const { entry: page, cacheHint } = await getEmDashEntry("pages", slug, { locale });
	if (!page) return null;
	// cacheHint (tag/thời hạn cache của entry) chuyển cho route cache của Astro nếu đang bật
	// (Astro.cache có thể undefined), để trang được làm mới khi entry thay đổi.
	if (Astro.cache?.enabled) Astro.cache.set(cacheHint);

	// slug "trần" của trang. page.id ở locale "vi" có tiền tố ("vi/about") nên ưu tiên
	// page.data.slug; nếu thiếu thì dùng slug từ URL.
	const bareSlug = page.data.slug ?? slug;
	// Tính SEO từ panel SEO của entry (fallback: title, excerpt). siteUrl + path dùng để sinh
	// URL canonical tuyệt đối; path phải kèm prefix locale (/vi/...) để canonical đúng ngôn ngữ.
	const seo = getSeoMeta(page, {
		siteUrl: Astro.url.origin,
		path: localizePath(pathFor(bareSlug), locale),
	});
	// The home page is titled after the site unless its SEO panel sets a title.
	// Trang chủ: chỉ dùng tiêu đề SEO do editor nhập (rỗng => undefined), để Base dựng tiêu đề mặc
	// "Tên site | tên pháp lý" thay vì "Home | Tên site". Trang khác dùng seo.title (SEO title hoặc title).
	const title = bareSlug === HOME_SLUG ? getContentSeo(page)?.title || undefined : seo.title;

	// Bản đồ locale -> URL của chính trang này, cho nút chuyển ngôn ngữ EN | VI.
	// Ban đầu rỗng: Base sẽ tự suy ra URL dự phòng (cùng đường dẫn, đổi locale) cho locale thiếu.
	const alternates: Partial<Record<Locale, string>> = {};
	// Các bản dịch dùng chung một "translation group" (liên kết bằng translationOf trong seed).
	// Truyền ULID (page.data.id), không phải slug, vì slug khác nhau giữa các locale.
	const { translations } = await getTranslations("pages", page.data.id);
	// `?? []` phòng khi lỗi truy vấn khiến translations undefined.
	for (const translation of translations ?? []) {
		// Bỏ bản nháp/chưa publish hoặc thiếu slug: không để nút EN | VI dẫn tới trang 404.
		if (translation.status !== "published" || !translation.slug) continue;
		const code = translation.locale as Locale;
		// Mỗi bản dịch có slug riêng, nên dựng URL từ slug của chính nó + prefix locale của nó.
		alternates[code] = localizePath(pathFor(translation.slug), code);
	}

	return {
		// Entry gốc cho PageView.
		page,
		// Tiêu đề cho <title>: chưa có hậu tố tên site (không truyền siteTitle cho getSeoMeta), Base sẽ tự nối.
		title,
		// Tiêu đề thô của trang, dùng cho ngữ cảnh trang của EmDash trong Base.
		pageTitle: page.data.title,
		description: seo.description,
		// URL ảnh Open Graph tuyệt đối (hoặc null).
		image: seo.ogImage,
		canonical: seo.canonical,
		// "noindex, nofollow" nếu editor tick noIndex, ngược lại null.
		robots: seo.robots,
		alternates,
	};
}
