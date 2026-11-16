/**
 * Tiện ích cho khu vực tin tức (collection `posts`): dựng URL, định dạng ngày
 * và bộ tải một bài viết. Được dùng bởi NewsList, NewsCard, NewsArticle và hai
 * route src/pages/news/[slug].astro, src/pages/vi/news/[slug].astro.
 */
import { getEmDashEntry, getSeoMeta, getTranslations } from "emdash";
import type { AstroGlobal } from "astro";
import { type Locale, localizePath } from "../i18n/ui";

// Đường dẫn gốc của khu tin tức, chưa có prefix locale. Phải khớp thư mục src/pages/news/.
export const NEWS_PATH = "/news";

/** URL of the news list or one article, prefixed for the locale. */
export function newsPath(locale: Locale, slug?: string | null): string {
	// Có slug => URL bài viết "/news/{slug}"; không có (undefined/null/rỗng) => URL danh sách "/news".
	// localizePath thêm "/vi" cho tiếng Việt, tiếng Anh giữ nguyên (không prefix).
	return localizePath(slug ? `${NEWS_PATH}/${slug}` : NEWS_PATH, locale);
}

/** "5 November 2026" / "5 tháng 11, 2026". */
export function formatNewsDate(date: Date | null | undefined, locale: Locale): string | undefined {
	// Không có ngày => undefined (template in rỗng thay vì "Invalid Date").
	if (!date) return undefined;
	// Intl.DateTimeFormat có sẵn trên runtime Workers, không cần thư viện ngày tháng.
	// "en-GB" cho thứ tự ngày-tháng-năm kiểu Anh ("5 November 2026"); "vi-VN" cho
	// định dạng tiếng Việt ("5 tháng 11, 2026").
	return new Intl.DateTimeFormat(locale === "vi" ? "vi-VN" : "en-GB", {
		day: "numeric",
		month: "long",
		year: "numeric",
	}).format(date);
}

/**
 * Loads a published article for a locale (falling back to English), with the
 * SEO fields and the EN | VI switch targets the layout needs. Returns null for
 * a missing article so the route can rewrite to its 404.
 */
// Tương tự loadPage() trong load-page.ts nhưng cho collection `posts`. Trả về
// tập props của <NewsArticle>: { post, title, description, image, canonical, robots, alternates }.
export async function loadArticle(Astro: AstroGlobal, slug: string | undefined, locale: Locale) {
	// slug undefined (decodeSlug thất bại) => coi như không có bài.
	if (!slug) return null;
	// Đọc bài đã publish theo slug và locale; thiếu bản dịch thì EmDash fallback sang "en"
	// theo cấu hình i18n. Không thấy => entry null.
	const { entry: post, cacheHint } = await getEmDashEntry("posts", slug, { locale });
	if (!post) return null;
	// Chuyển cacheHint cho route cache của Astro nếu đang bật (Astro.cache có thể undefined).
	if (Astro.cache?.enabled) Astro.cache.set(cacheHint);

	// Slug "trần" (không tiền tố locale), ưu tiên data.slug rồi mới tới slug từ URL.
	const bareSlug = post.data.slug ?? slug;
	// SEO từ panel của bài (fallback title/excerpt). path có prefix locale để canonical tuyệt đối đúng.
	const seo = getSeoMeta(post, {
		siteUrl: Astro.url.origin,
		path: newsPath(locale, bareSlug),
	});

	// Without a translation, the switch falls back to the other locale's news list.
	// Mặc định mỗi locale trỏ tới danh sách tin của nó; locale hiện tại thì trỏ tới chính bài này.
	// Bản dịch có thật (vòng lặp dưới) sẽ ghi đè mặc định.
	const alternates: Partial<Record<Locale, string>> = { en: newsPath("en"), vi: newsPath("vi") };
	alternates[locale] = newsPath(locale, bareSlug);
	// Lấy mọi bản dịch cùng "translation group" qua ULID của bài (slug khác nhau giữa các locale).
	const { translations } = await getTranslations("posts", post.data.id);
	// `?? []` phòng khi lỗi truy vấn khiến translations undefined.
	for (const translation of translations ?? []) {
		// Bỏ bản chưa publish hoặc thiếu slug để nút chuyển ngôn ngữ không dẫn tới 404.
		if (translation.status !== "published" || !translation.slug) continue;
		const code = translation.locale as Locale;
		alternates[code] = newsPath(code, translation.slug);
	}

	return {
		post,
		// Chỉ tiêu đề; Base tự nối tên site. (title để trống thì Base dùng tiêu đề mặc định.)
		title: seo.title,
		// Ưu tiên mô tả SEO; rỗng thì rơi về đoạn trích của bài (`||` coi "" và null đều là thiếu).
		description: seo.description || post.data.excerpt,
		// URL ảnh Open Graph tuyệt đối (hoặc null).
		image: seo.ogImage,
		canonical: seo.canonical,
		// "noindex, nofollow" nếu bài bật noIndex, ngược lại null.
		robots: seo.robots,
		alternates,
	};
}
