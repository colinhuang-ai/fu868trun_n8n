/**
 * build-seed.mjs - SOURCE OF TRUTH của seed cho website CASES (EmDash CMS + Astro, song ngữ EN/VI).
 *
 * MỤC ĐÍCH
 *   Script này sinh ra file `seed/seed.json` (KHÔNG sửa tay file đó, mọi chỉnh sửa phải làm ở đây rồi
 *   chạy lại script). Toàn bộ schema (block types, collections, fields), menu, trang và dữ liệu mẫu
 *   (sản phẩm, chứng nhận, nhà máy) đều được khai báo ở đây dưới dạng JavaScript để:
 *     - mỗi đoạn văn chỉ viết MỘT lần dưới dạng T(en, vi) thay vì phải nhân đôi trong JSON;
 *     - tái sử dụng helper (str / text / link / repeater / blockType ...) giúp schema ngắn gọn;
 *     - dữ liệu lặp (68 sản phẩm...) được sinh từ bảng tuple thay vì gõ tay từng object.
 *
 * CÁCH CHẠY
 *   node scripts/build-seed.mjs                  # sinh lại seed/seed.json (ghi đè hoàn toàn)
 *   npx emdash seed seed/seed.json --validate    # kiểm tra seed đúng schema EmDash (nên chạy sau khi sinh)
 *   Không cần cài thêm package: script chỉ dùng module built-in của Node (node:fs, node:url).
 *   Đường dẫn output được tính từ vị trí file này (../seed/seed.json) nên chạy từ thư mục nào cũng được.
 *
 * OUTPUT
 *   seed/seed.json gồm: $schema, version, meta, settings, blockTypes, collections, menus và
 *   content.{pages, products, certifications, plants}. JSON được in bằng tab + xuống dòng cuối file.
 *   Lưu ý: seed này KHÔNG có taxonomies, widgets, bylines, redirects (EmDash hỗ trợ nhưng site chưa cần),
 *   và collection `posts` (tin tức) được khai báo schema nhưng chưa có bài mẫu.
 *
 * SEED ĐƯỢC ÁP DỤNG NHƯ THẾ NÀO
 *   Trên một database MỚI, mở http://localhost:4321/_emdash/api/setup/dev-bypass?redirect=/ một lần:
 *   EmDash áp dụng seed (settings, schema, menu, nội dung mẫu) rồi đăng nhập dev admin.
 *   Khi deploy thật, quá trình setup của EmDash cũng đọc seed theo cách tương tự.
 *   QUY TẮC QUAN TRỌNG: seed KHÔNG BAO GIỜ được áp dụng lại lên một database đã tồn tại. Sau khi site
 *   đã setup, nội dung được sửa trong admin (/_emdash/admin); sửa script này chỉ ảnh hưởng database
 *   mới. Muốn thấy thay đổi của script ở môi trường dev: tạo database local mới (ví dụ xóa state D1
 *   local của wrangler) rồi chạy lại bước setup ở trên.
 *
 * SONG NGỮ VÀ `translationOf`
 *   Mỗi entry (trang, sản phẩm, chứng nhận, nhà máy, menu) được phát ra thành 2 bản:
 *     - bản English (locale "en", locale mặc định, URL không có prefix) = bản GỐC, có id = `id`;
 *     - bản Vietnamese (locale "vi", URL /vi/...) có id = `${id}-vi` và thuộc tính
 *       `translationOf: id` trỏ về id của bản gốc.
 *   `translationOf` là cách EmDash biết hai entry là bản dịch của nhau (cùng một "translation group"),
 *   nhờ đó admin có bộ chuyển ngôn ngữ và frontend lấy đúng bản theo `locale`. Bản gốc phải đứng
 *   TRƯỚC bản dịch trong seed (helper bilingual() đảm bảo thứ tự en rồi vi). Cả hai bản dùng CHUNG slug.
 *   Số liệu "68 sản phẩm" trong AGENTS.md = 34 dòng dữ liệu x 2 ngôn ngữ (tương tự các collection khác).
 *
 * QUY ƯỚC KHI SỬA
 *   - Chỉ thêm văn bản hiển thị qua T(en, vi); giá trị trung tính về ngôn ngữ (URL, mã DL 113, số liệu
 *     đã format, email) viết thẳng, không bọc T().
 *   - Field rỗng ("" / null / undefined) bị loại khỏi seed (xem resolve()).
 *   - Thêm block type mới: khai báo trong `blockTypes` (tên bắt đầu bằng cases_), tạo component
 *     src/components/blocks/*.astro và đăng ký trong src/components/CasesBlocks.astro.
 *   - Select ở cấp cao nhất cần `validation.options`, repeater sub-field dùng `options` trực tiếp
 *     (script tự chuyển, xem moveOptions ở cuối file).
 */
// Chỉ dùng 2 module built-in của Node (không phụ thuộc package, chạy được cả khi chưa `pnpm install`):
// - writeFileSync: ghi seed.json ra đĩa ở cuối script (đồng bộ, ghi đè file cũ).
// - fileURLToPath: đổi `import.meta.url` (dạng file:///D:/...) thành đường dẫn hệ điều hành hợp lệ
//   (trên Windows có ổ đĩa), để định vị seed/seed.json tương đối với file script này, không phụ thuộc
//   thư mục làm việc (cwd) lúc chạy.
import { writeFileSync } from "node:fs";
import { fileURLToPath } from "node:url";

// Các locale được sinh ra. "en" là locale mặc định (không prefix URL) và là bản gốc; "vi" là bản dịch
// (prefix /vi/). Thứ tự QUAN TRỌNG: bilingual() / bilingualMenu() phát bản en trước, rồi vi, vì bản
// dịch tham chiếu bản gốc qua `translationOf`. Thêm ngôn ngữ mới không chỉ là thêm mã vào đây: T()
// và resolve() cũng phải mang thêm key ngôn ngữ đó, và i18n trong astro.config.mjs phải khai báo.
const LOCALES = ["en", "vi"];
// T(en, vi) - đánh dấu MỘT giá trị song ngữ.
//   Tham số: en = giá trị tiếng Anh, vi = giá trị tiếng Việt (thường là string, nhưng có thể là gì cũng được).
//   Trả về: object marker { __t: true, en, vi }. Đây CHƯA phải dữ liệu cuối cùng: marker có thể nằm ở
//   bất kỳ độ sâu nào (field phẳng, phần tử trong repeater, trong block...) và được resolve() thay
//   bằng chuỗi đúng ngôn ngữ khi bilingual() sinh entry en / vi.
// Edge cases:
// - Giá trị KHÔNG bọc T() (vd "/products", "$184M", "DL 113", email) được dùng NGUYÊN cho cả hai bản.
// - Một phía rỗng, vd T("", "Black Tiger Prawn"): ở locale đó giá trị là "" nên resolve() loại hẳn key
//   (field vắng mặt chứ không phải chuỗi rỗng). Được dùng có chủ ý ở `caption` của cases_catalog.
// - Nếu quên vi (T("abc")) thì bản vi nhận undefined và key bị loại, trang vi sẽ thiếu chữ đó.
const T = (en, vi) => ({ __t: true, en, vi });

// resolve(value, locale) - "chốt" ngôn ngữ: duyệt đệ quy một cấu trúc dữ liệu và trả về bản sao
// trong đó mọi marker T() đã được thay bằng giá trị của `locale` ("en" | "vi").
// - Mảng: map từng phần tử (KHÔNG lọc phần tử mảng, chỉ lọc key của object).
// - Object marker (có `__t`): trả về value[locale].
// - Object thường (data của entry, block, phần tử repeater): resolve từng thuộc tính rồi LOẠI BỎ
//   thuộc tính có giá trị undefined, null hoặc "" => seed không chứa field rỗng, và component dùng
//   kiểm tra `value.x && ...` / `?? mặc định` coi như field không tồn tại. Hệ quả: không thể biểu diễn
//   chuỗi rỗng cố ý; còn 0 và false thì được GIỮ (vd `share: 0`, `highlight: false`).
// - Primitive (string, number, boolean, null): trả nguyên.
// Hàm thuần (không mutate đầu vào), nên cùng một nguồn `data` được resolve hai lần (en, vi) an toàn.
// Giả định dữ liệu là JSON thuần (không Date hay class instance).
function resolve(value, locale) {
	if (Array.isArray(value)) return value.map((item) => resolve(item, locale));
	if (value && typeof value === "object") {
		if (value.__t) return value[locale];
		return Object.fromEntries(
			Object.entries(value)
				.map(([key, item]) => [key, resolve(item, locale)])
				.filter(([, item]) => item !== undefined && item !== null && item !== ""),
		);
	}
	return value;
}

// bilingual(id, slug, data, extra = {}) - sinh 2 entry (en + vi) cho MỘT mục nội dung.
//   id    : id nội bộ trong seed của bản English (vd "home", "product-btp-pd"); bản vi nhận `${id}-vi`.
//           Đây chỉ là khóa liên kết trong seed (dùng cho translationOf), không phải ULID của DB.
//   slug  : slug URL, GIỐNG nhau ở hai locale (URL vi là /vi/<slug>). Ở runtime entry.id = slug
//           (bản vi có prefix locale); entry.data.id mới là ULID trong database.
//   data  : object dữ liệu chứa các marker T(); được resolve() theo từng locale.
//   extra : thuộc tính cấp entry bổ sung (hiện chưa nơi nào dùng), trải vào entry TRƯỚC `data`
//           nên có thể ghi đè `status`.
//   Trả về: mảng [entryEN, entryVI]. Hình dạng entry: { id, slug, locale, translationOf (chỉ bản vi,
//   trỏ về id bản en), status: "published", ...extra, data }. Dùng với flatMap/spread để nối các
//   cặp thành một danh sách phẳng, bản gốc luôn đứng trước bản dịch.
//   status "published": nội dung mẫu được xuất bản ngay (dù collection có hỗ trợ drafts) để site render
//   được ngay sau khi setup.
/** One entry per locale, Vietnamese linked to the English source. */
function bilingual(id, slug, data, extra = {}) {
	return LOCALES.map((locale) => ({
		id: locale === "en" ? id : `${id}-${locale}`,
		slug,
		locale,
		...(locale === "en" ? {} : { translationOf: id }),
		status: "published",
		...extra,
		data: resolve(data, locale),
	}));
}

// ---------------------------------------------------------------------------
// Schema
// ---------------------------------------------------------------------------

// Các helper khai báo SCHEMA (không phải dữ liệu). Field schema có dạng { slug, label, type, ... }
// với các type dùng trong file: string (1 dòng), text (nhiều dòng), number, integer, boolean, select,
// image (object { src, alt }), repeater (danh sách object con), blocks, portableText.
//
// LINK: field string với pattern kiểm tra URL do editor nhập. Regex cho phép: chuỗi rỗng; hoặc bắt đầu
// bằng https:// http:// mailto: tel: ; hoặc "/" nhưng KHÔNG phải "//" (chặn URL protocol-relative kiểu
// //evil.com); hoặc "#" (anchor); và phải có ít nhất 1 ký tự phía sau. Vì vậy `javascript:` bị chặn.
// Đây là lớp validate ở admin; lúc render, link còn đi qua resolveHref() (src/utils/links.ts) để
// sanitize lần nữa và thêm prefix /vi cho link nội bộ.
const LINK = {
	type: "string",
	validation: { pattern: "^(?:$|(?:https?://|mailto:|tel:|/(?!/)|#).+)$" },
};
// Gợi ý định dạng nối vào label của mọi field link (admin không hiển thị regex cho editor).
const LINK_HINT = "(starts with /, https://, mailto:, tel: or #)";
// Field dùng chung cho MỌI block: `anchor_id` được component render thành thuộc tính `id` của <section>
// (`id={value.anchor_id || undefined}`), cho phép link nhảy tới một phần trang, vd /#markets,
// /about#story, /certifications#aquaculture (xem footerItems). Pattern: rỗng, hoặc chữ thường bắt đầu
// bằng chữ cái rồi a-z 0-9 _ -. Một object dùng chung cho nhiều block: chỉ đọc, không được mutate.
const anchor = {
	slug: "anchor_id",
	label: "Section ID (lowercase, for links like /#markets)",
	type: "string",
	validation: { pattern: "^(?:$|[a-z][a-z0-9_-]*)$" },
};
// Label dùng chung cho field icon: giá trị là TÊN Material Symbols (font icon dùng class
// `material-symbols-outlined`); tên không tồn tại sẽ hiện nguyên chữ thay vì icon.
const iconHint = "Icon (a Material Symbols name such as factory, public, verified)";
// str(slug, label, extra) - rút gọn cho field 1 dòng: { slug, label, type: "string", ...extra }.
// `extra` được trải SAU cùng nên có thể thêm/ghi đè bất cứ thuộc tính nào (required, validation...).
const str = (slug, label, extra = {}) => ({ slug, label, type: "string", ...extra });
// text(slug, label, extra) - như str() nhưng type "text" (textarea nhiều dòng, dùng cho đoạn văn dài).
const text = (slug, label, extra = {}) => ({ slug, label, type: "text", ...extra });
// link(slug, label) - field URL: type string + validation pattern của LINK; label tự động nối thêm
// LINK_HINT. (LINK không có thuộc tính label nên label ở trên không bị ghi đè khi spread.)
const link = (slug, label) => ({ slug, label: `${label} ${LINK_HINT}`, ...LINK });
// repeater(slug, label, subFields, { min = 1, max = 12, required = true }) - danh sách object con.
//   subFields : mảng định nghĩa field con (cùng cú pháp field thường). Với sub-field kiểu select, các lựa
//               chọn nằm trực tiếp ở `options` (KHÁC field select cấp cao nhất, xem moveOptions cuối file).
//   min       : số phần tử tối thiểu, chỉ áp dụng khi required.
//   max       : số phần tử tối đa, luôn được ghi (validation.maxItems); mặc định 12.
//   required  : true (mặc định) => field.required = true và validation.minItems = min;
//               false => không bắt buộc, block có thể không có field này (vd `stats` của hero).
// Trả về field { slug, label, type: "repeater", [required], validation: { [minItems], maxItems, subFields } }.
const repeater = (slug, label, subFields, { min = 1, max = 12, required = true } = {}) => ({
	slug,
	label,
	type: "repeater",
	...(required ? { required: true } : {}),
	validation: { ...(required ? { minItems: min } : {}), maxItems: max, subFields },
});
// blockType(slug, label, description, fields) - khai báo MỘT block type cho trình soạn block.
//   category "CASES": nhóm hiển thị trong bộ chọn block của admin.
//   currentVersion / versions: schema của block được đánh version; dữ liệu block trong trang cũng mang
//   `_version: 1` (xem block() ở phần Pages). Khi đổi cấu trúc field không tương thích với dữ liệu đã
//   lưu, cần tăng version (tham khảo docs EmDash trước khi làm).
const blockType = (slug, label, description, fields) => ({
	slug,
	label,
	description,
	category: "CASES",
	currentVersion: 1,
	versions: [{ version: 1, fields }],
});

// Slug 6 nhóm sản phẩm. Phải khớp ở 3 nơi: options của products.category, cases_catalog.categories[].key
// và mảng `categories` + `productData` bên dưới. btp = Black Tiger Prawn (tôm sú), van = Vannamei (tôm thẻ),
// vap_* = Value-Added Products (chế biến sâu) của từng loại, surimi (gồm cả bột cá), cephalopod = nhuyễn thể.
// Đây là giá trị được lưu trong DB, không nên đổi tên sau khi đã có dữ liệu thật.
const CATEGORY_KEYS = ["btp", "van", "vap_btp", "vap_van", "surimi", "cephalopod"];
// Các dây chuyền nhà máy; dùng cho plants.line và cases_plants.groups[].line. Seed hiện chỉ có nhà máy
// thuộc shrimp và surimi; cephalopod và fishmeal có sẵn để mở rộng.
const PLANT_LINES = ["shrimp", "surimi", "cephalopod", "fishmeal"];
// Nhóm chứng nhận. food_safety / aquaculture / specialist mỗi nhóm được hiển thị bằng một block cases_certs;
// "other" = KHÔNG có thẻ riêng, chỉ xuất hiện trong bảng tổng hợp cases_cert_table (vd US FDA, có Spotlight riêng).
const CERT_GROUPS = ["food_safety", "aquaculture", "specialist", "other"];
// Tông màu cho ô icon (tone) và badge (badge_tone) của thẻ chứng nhận. Component ghép thành class CSS
// `icon-tile-<tone>` và `badge-<tone>` định nghĩa trong src/styles/global.css; thêm tone mới phải thêm CSS tương ứng.
const TONES = ["blue", "green", "amber", "purple"];

// BLOCK TYPES: 13 block `cases_*` mà editor ghép thành trang. Dữ liệu thực tế của một trang là mảng
// block object { _type, _version, _key, ...field } (xem block() ở phần Pages), được render bởi
// src/components/CasesBlocks.astro -> src/components/blocks/*.astro (một component cho mỗi block, đọc
// `Astro.props.value`). Quy ước chung: field rỗng thì component ẩn phần tử đó; `anchor_id` có ở mọi block;
// các link CTA đi qua resolveHref(). Mỗi block bên dưới ghi rõ field nào được component dùng.
const blockTypes = [
	// cases_hero - phần mở đầu trang trên nền navy. 4 biến thể qua field `layout` (Hero.astro):
	// - center : trang chủ. Logo watermark mờ ở nền; eyebrow (pill), headline + headline_accent (xuống dòng),
	//            subheadline và 2 nút (primary/secondary CTA). Không hiển thị `stats`.
	// - split  : About / Infrastructure. Logo bên phải; hiển thị eyebrow, headline, subheadline và `stats`
	//            (value + unit nhỏ + label). KHÔNG dùng headline_accent và CTA.
	// - glow   : trang Certifications. Nền lưới; eyebrow, headline + headline_accent (hiệu ứng shimmer),
	//            subheadline và thẻ `stats` có số đếm tăng dần (data-count lấy từ stat.value; unit không hiển thị).
	// - banner : trang Products. Banner bo góc kiểu catalog; eyebrow, headline + headline_accent, subheadline.
	blockType("cases_hero", "Hero", "Page introduction on the navy background", [
		str("headline", "Headline", { required: true }),
		str("headline_accent", "Second line (highlighted)"),
		str("eyebrow", "Label above the headline"),
		text("subheadline", "Introduction"),
		// Field `layout`: select cấp cao nhất nên sau moveOptions các lựa chọn nằm ở validation.options.
		// `defaultValue: "center"` áp dụng cho block mới (Hero.astro cũng fallback `?? "center"`).
		{
			slug: "layout",
			label: "Layout (center: home with logo watermark · split: logo on the right · glow: grid and stat cards · banner: rounded catalog banner)",
			type: "select",
			required: true,
			defaultValue: "center",
			options: ["center", "split", "glow", "banner"],
		},
		str("primary_cta_label", "Primary button text"),
		link("primary_cta_url", "Primary button link"),
		str("secondary_cta_label", "Secondary button text"),
		link("secondary_cta_url", "Secondary button link"),
		// `stats`: số liệu nổi bật, không bắt buộc, tối đa 4. `unit` là chữ nhỏ đứng sau value (chỉ layout split).
		repeater(
			"stats",
			"Key figures",
			[
				str("value", "Value", { required: true }),
				str("unit", "Unit (small, after the value)"),
				str("label", "Label", { required: true }),
			],
			{ max: 4, required: false },
		),
		anchor,
	]),
	// cases_codes - hàng huy hiệu "mã xuất khẩu" (vd DL 113) với icon verified, lưới 2/3/6 cột.
	// `headline` nhỏ, tùy chọn, in hoa phía trên hàng mã. Mỗi code là một object { code } (repeater bắt buộc
	// có sub-field nên không thể là mảng string thuần).
	blockType("cases_codes", "Export codes", "A row of certified export codes", [
		str("headline", "Headline"),
		repeater("codes", "Codes", [str("code", "Code", { required: true })], { max: 12 }),
		anchor,
	]),
	// cases_reach - "tầm vươn toàn cầu" (nền navy): 3 thẻ số liệu lớn (`figures`), danh sách thị trường xuất khẩu
	// (`markets`) và một dòng tổng ở cuối (`total_*`, chỉ hiện khi có total_value).
	// Các số (value, volume, total) là chuỗi đã format sẵn, không phải number.
	blockType("cases_reach", "Global reach", "Key figures and export markets", [
		str("headline", "Headline", { required: true }),
		str("eyebrow", "Label above the headline"),
		// `figures`: đúng 3 cột ở màn hình rộng (max 3). `highlight` làm nổi bật thẻ (màu nền khác).
		repeater(
			"figures",
			"Key figures",
			[
				str("value", "Value", { required: true }),
				str("label", "Label", { required: true }),
				str("caption", "Caption"),
				{ slug: "highlight", label: "Highlight", type: "boolean", defaultValue: false },
			],
			{ max: 3 },
		),
		// `markets`: mỗi thị trường là một thẻ gồm cờ (emoji), tên, kim ngạch, sản lượng và thanh tiến độ.
		repeater(
			"markets",
			"Markets",
			[
				str("name", "Market", { required: true }),
				str("flag", "Flag emoji"),
				str("value", "Turnover", { required: true }),
				str("volume", "Volume"),
				// `share`: độ dài thanh (0-100). Đây là TỶ LỆ SO VỚI THỊ TRƯỜNG LỚN NHẤT (EU = 100), nhập tay, không phải % tổng.
				// Reach.astro tự kẹp giá trị vào khoảng 0..100.
				{ slug: "share", label: "Bar length (0–100)", type: "number" },
				// `muted`: hiển thị màu xám (dùng cho dòng "Others" / "Khác") thay vì xanh sky.
				{ slug: "muted", label: "Show in grey", type: "boolean", defaultValue: false },
			],
			{ max: 12, required: false },
		),
		str("total_label", "Total: label"),
		str("total_value", "Total: value"),
		str("total_caption", "Total: caption"),
		anchor,
	]),
	// cases_features - lưới thẻ icon (3 cột, tối đa 9) cùng một thẻ kêu gọi hành động tùy chọn ở cuối
	// (chỉ hiện khi có `cta_title`; link đi qua resolveHref, "#contact" mở khu vực liên hệ ở footer).
	blockType("cases_features", "Advantages", "Icon cards with an optional call-to-action card", [
		str("headline", "Headline", { required: true }),
		str("eyebrow", "Label above the headline"),
		repeater(
			"features",
			"Cards",
			[
				str("icon", iconHint, { required: true }),
				str("title", "Title", { required: true }),
				text("description", "Description", { required: true }),
			],
			{ max: 9 },
		),
		str("cta_title", "Call-to-action card: title"),
		text("cta_body", "Call-to-action card: text"),
		str("cta_label", "Call-to-action card: button text"),
		link("cta_url", "Call-to-action card: button link"),
		anchor,
	]),
	// cases_timeline - mốc thời gian dọc đường kẻ giữa (md trở lên). Các mốc xen kẽ trái/phải theo chỉ số
	// lẻ/chẵn. `year` là string (có thể là chữ như "Today"). `note` + `icon` tạo hộp ghi chú nhỏ cạnh mốc.
	blockType("cases_timeline", "Timeline", "Milestones down a centre line", [
		str("headline", "Headline", { required: true }),
		str("eyebrow", "Label above the headline"),
		repeater(
			"milestones",
			"Milestones",
			[
				str("year", "Year", { required: true }),
				str("title", "Title", { required: true }),
				text("body", "Text"),
				str("icon", iconHint),
				str("note", "Side note"),
				// `highlight`: nhấn mạnh mốc (nút tròn lớn hơn, hộp ghi chú đổi màu), thường dùng cho mốc hiện tại.
				{ slug: "highlight", label: "Highlight", type: "boolean", defaultValue: false },
			],
			{ max: 10 },
		),
		anchor,
	]),
	// cases_values - 2 thẻ Sứ mệnh/Tầm nhìn (`cards`, tùy chọn, tối đa 4) + khung trích dẫn nền tối
	// (`quote` + `quote_body`, chỉ hiện khi có `quote`) + tối đa 2 nút CTA.
	blockType("cases_values", "Mission and values", "Mission and vision cards with a quote panel", [
		str("quote", "Quote (large, in the dark panel)"),
		text("quote_body", "Text under the quote"),
		repeater(
			"cards",
			"Cards",
			[
				str("icon", iconHint),
				str("title", "Title", { required: true }),
				text("body", "Text", { required: true }),
			],
			{ max: 4, required: false },
		),
		str("primary_cta_label", "Primary button text"),
		link("primary_cta_url", "Primary button link"),
		str("secondary_cta_label", "Secondary button text"),
		link("secondary_cta_url", "Secondary button link"),
		anchor,
	]),
	// cases_plants - nhà máy lấy từ collection `plants` lúc request (editor sửa nhà máy ở collection, không sửa trang).
	// Mỗi nhóm trong `groups` lọc plants theo `line` rồi sắp theo sort_order. Bố cục tự đổi theo số lượng:
	// nhóm có <= 2 nhà máy hiển thị thẻ lớn 2 cột (kèm tỉnh), nhóm nhiều hơn hiển thị thẻ gọn 3 cột.
	blockType("cases_plants", "Processing plants", "Plants from the Plants collection, grouped by line", [
		str("headline", "Headline", { required: true }),
		str("eyebrow", "Label above the headline"),
		repeater(
			"groups",
			"Groups",
			[
				// Sub-field select: lựa chọn khai báo trực tiếp bằng `options` (không dùng validation.options như select cấp cao nhất).
				{
					slug: "line",
					label: "Product line",
					type: "select",
					required: true,
					options: PLANT_LINES,
				},
				str("label", "Group label", { required: true }),
			],
			{ max: 4 },
		),
		anchor,
	]),
	// cases_tech - thẻ công nghệ có ảnh trên nền navy (lưới 2 cột). `image` là object { src, alt }; khi chưa
	// upload ảnh, component hiển thị khung "photo" nét đứt (PhotoSlot). Seed không kèm ảnh nào.
	blockType("cases_tech", "Technology", "Photo cards on the navy background", [
		str("headline", "Headline", { required: true }),
		str("eyebrow", "Label above the headline"),
		repeater(
			"items",
			"Cards",
			[
				str("title", "Title", { required: true }),
				text("description", "Description"),
				{ slug: "image", label: "Photo", type: "image" },
			],
			{ max: 8 },
		),
		anchor,
	]),
	// cases_certs - thẻ chứng nhận lấy từ collection `certifications` theo `group` (sắp theo sort_order).
	// Nhóm có đúng 2 thẻ được căn giữa trong khung hẹp, các trường hợp khác hiển thị 3 cột.
	blockType("cases_certs", "Certification group", "Cards from the Certifications collection", [
		str("headline", "Headline", { required: true }),
		str("eyebrow", "Label above the headline"),
		{
			slug: "group",
			label: "Group to show",
			type: "select",
			required: true,
			// Loại "other": nhóm đó không có thẻ, chỉ xuất hiện ở bảng tổng hợp.
			options: CERT_GROUPS.filter((group) => group !== "other"),
		},
		anchor,
	]),
	// cases_spotlight - khung nổi bật rộng: tiêu đề + đoạn văn + danh sách tag bên trái, huy hiệu tròn
	// (emoji + tiêu đề + chú thích, chỉ hiện khi có badge_icon hoặc badge_title) bên phải.
	blockType("cases_spotlight", "Spotlight", "A wide feature panel with tags and a round badge", [
		str("headline", "Headline", { required: true }),
		str("eyebrow", "Label above the headline"),
		text("body", "Text"),
		// `tags`: MỘT field text, mỗi dòng là một tag (Spotlight.astro tách theo "\n", trim và bỏ dòng trống).
		// Vì vậy dữ liệu dùng "\n" trong chuỗi, xem block "fda" ở trang Certifications.
		text("tags", "Tags (one per line)"),
		str("badge_icon", "Badge emoji"),
		str("badge_title", "Badge title"),
		str("badge_caption", "Badge caption"),
		anchor,
	]),
	// cases_cert_table - bảng TẤT CẢ chứng nhận (cột: tên, cơ quan cấp, phạm vi, trạng thái) lấy từ collection
	// `certifications`, bỏ những mục có show_in_table = false. Block chỉ có `headline`; dữ liệu do editor
	// quản lý ở collection.
	blockType("cases_cert_table", "Certification table", "Every certification in one table", [
		str("headline", "Headline", { required: true }),
		anchor,
	]),
	// cases_catalog - danh mục sản phẩm: các thẻ danh mục (xếp thành hàng 3 thẻ) mà khi bấm sẽ mở ra danh sách
	// sản phẩm của danh mục đó, lấy từ collection `products` (product.category === categories[].key).
	// Lưu ý: field `hint` có trong schema nhưng Catalog.astro hiện KHÔNG render nó.
	blockType("cases_catalog", "Product catalog", "Category cards that open into products", [
		str("hint", "Hint under the cards"),
		repeater(
			"categories",
			"Categories",
			[
				// `key` ghép với products.category (cùng tập CATEGORY_KEYS). `code` là chữ lớn mờ làm watermark thẻ,
				// `subtitle` là dòng nhỏ dưới code, `name` + `caption` hiển thị dưới thẻ, `emoji` dùng trong popup sản phẩm.
				{
					slug: "key",
					label: "Category (matches the product's category)",
					type: "select",
					required: true,
					options: CATEGORY_KEYS,
				},
				str("code", "Short code (large, e.g. BTP)", { required: true }),
				str("subtitle", "Small line under the code"),
				str("name", "Name", { required: true }),
				str("caption", "Caption under the name"),
				str("emoji", "Emoji (product popup)"),
				// `color` chọn gradient của thẻ; Catalog.astro có bảng COLORS ánh xạ đúng 6 giá trị này sang class Tailwind
				// (viết literal). Thêm màu mới phải sửa cả bảng đó.
				{
					slug: "color",
					label: "Colour",
					type: "select",
					required: true,
					options: ["sky", "cyan", "red", "orange", "teal", "slate"],
				},
			],
			{ max: 12 },
		),
		anchor,
	]),
	// cases_cta - khung gradient một nút kêu gọi hành động. Nút chỉ hiển thị khi có cả `cta_label` lẫn link
	// hợp lệ; `icon` (tên Material Symbols) nằm trước chữ trên nút.
	blockType("cases_cta", "Call to action", "A gradient panel with one button", [
		str("headline", "Headline", { required: true }),
		text("body", "Text"),
		str("icon", iconHint),
		str("cta_label", "Button text"),
		link("cta_url", "Button link"),
		anchor,
	]),
];

// Mix-in `{ translatable: false }` cho các field KHÔNG phụ thuộc ngôn ngữ (enum, số, ảnh, mã đóng gói):
// editor chỉ cần nhập một lần, không phải dịch lại ở bản vi (xem docs EmDash về cách đồng bộ giữa các
// bản dịch). Lưu ý: seed vẫn ghi giá trị vào CẢ HAI entry, vì mỗi locale là một entry riêng.
const notTranslatable = { translatable: false };

// COLLECTIONS: schema các bảng nội dung.
// - supports: tính năng bật trong admin (drafts, revisions, preview, seo, search, scheduling).
// - urlPattern: mẫu URL của entry cho preview/SEO; chỉ `pages` ("/{slug}") và `posts` ("/news/{slug}") có,
//   vì products / certifications / plants không có trang chi tiết riêng mà hiển thị qua block hoặc footer.
// - field flags: required, searchable (đưa vào tìm kiếm, cần supports "search"), translatable,
//   defaultValue, validation.
// - Slug của field KHÔNG được là `status` (cũng không dùng các tên hệ thống) vì `status`
//   (published/draft) là thuộc tính có sẵn của mọi entry; vì thế chứng nhận dùng `cert_status`.
// - Mọi collection đều có `title` bắt buộc làm tiêu đề trong danh sách admin.
const collections = [
	// pages: mỗi trang là danh sách block; slug dùng: home, about, products, infrastructure, certifications.
	{
		slug: "pages",
		label: "Pages",
		labelSingular: "Page",
		urlPattern: "/{slug}",
		supports: ["drafts", "revisions", "preview", "seo"],
		fields: [
			{ slug: "title", label: "Title", type: "string", required: true },
			{
				slug: "content",
				label: "Content",
				type: "blocks",
				// `allowedTypes` lấy từ blockTypes nên thêm block type mới tự động được phép dùng trong trang. Tối đa 20 block.
				validation: { allowedTypes: blockTypes.map((block) => block.slug), maxItems: 20 },
			},
		],
	},
	// products: sản phẩm của trang Products (68 entry = 34 sản phẩm x 2 ngôn ngữ), hiển thị trong block cases_catalog.
	{
		slug: "products",
		label: "Products",
		labelSingular: "Product",
		supports: ["drafts", "revisions", "search"],
		fields: [
			{ slug: "title", label: "Product name", type: "string", required: true, searchable: true },
			// `category` thuộc CATEGORY_KEYS, không dịch. Các field không dịch khác: image, packing, glaze, sort_order.
			{
				slug: "category",
				label: "Category",
				type: "select",
				required: true,
				options: CATEGORY_KEYS,
				...notTranslatable,
			},
			{ slug: "image", label: "Photo", type: "image", ...notTranslatable },
			// `size` dịch được vì có giá trị chữ ("As required" / "Theo yêu cầu"); `packing` và `glaze` là mã/tỷ lệ nên không dịch.
			{ slug: "size", label: "Size range", type: "string" },
			{ slug: "packing", label: "Pack form", type: "string", ...notTranslatable },
			{ slug: "origin", label: "Origin", type: "string" },
			{ slug: "glaze", label: "Glaze", type: "string", ...notTranslatable },
			{ slug: "description", label: "Description", type: "text", searchable: true },
			// `sort_order`: số thứ tự toàn cục, seed cách nhau 10 để editor chèn thêm giữa hai mục mà không phải đánh số lại.
			{ slug: "sort_order", label: "Order", type: "integer", ...notTranslatable },
		],
	},
	// certifications: thẻ chứng nhận (block cases_certs) và dòng trong bảng tổng hợp (block cases_cert_table).
	{
		slug: "certifications",
		label: "Certifications",
		labelSingular: "Certification",
		supports: ["drafts", "revisions"],
		fields: [
			{ slug: "title", label: "Name", type: "string", required: true },
			// `group`: nhóm hiển thị, "other" = chỉ nằm trong bảng.
			{
				slug: "group",
				label: "Group (other: table only)",
				type: "select",
				required: true,
				options: CERT_GROUPS,
				...notTranslatable,
			},
			{ slug: "icon", label: "Emoji", type: "string", ...notTranslatable },
			// `tone` / `badge_tone` (cùng TONES): màu ô icon và màu badge. Thẻ chứng nhận dùng `icon` là emoji (không phải tên Material Symbols).
			{
				slug: "tone",
				label: "Icon colour",
				type: "select",
				options: TONES,
				...notTranslatable,
			},
			{ slug: "badge", label: "Badge text", type: "string" },
			{
				slug: "badge_tone",
				label: "Badge colour",
				type: "select",
				options: TONES,
				...notTranslatable,
			},
			{ slug: "description", label: "Description", type: "text" },
			{ slug: "note", label: "Footnote", type: "string" },
			{ slug: "issuer", label: "Issuing body", type: "string" },
			{ slug: "scope", label: "Scope", type: "string" },
			// Cố ý đặt tên `cert_status` thay vì `status`, vì `status` là thuộc tính hệ thống của entry. Hiển thị ở cột Trạng thái của bảng.
			{ slug: "cert_status", label: "Status", type: "string" },
			// `show_in_table`: mặc định true; tắt để ẩn một chứng nhận khỏi bảng tổng hợp (vẫn hiện ở thẻ nhóm của nó).
			{
				slug: "show_in_table",
				label: "Show in the overview table",
				type: "boolean",
				defaultValue: true,
				...notTranslatable,
			},
			{ slug: "sort_order", label: "Order", type: "integer", ...notTranslatable },
		],
	},
	// plants: nhà máy xuất khẩu, dùng ở block cases_plants (trang Infrastructure) và cột "Main Plants" ở footer (Base.astro).
	{
		slug: "plants",
		label: "Plants",
		labelSingular: "Plant",
		supports: ["drafts", "revisions"],
		fields: [
			{ slug: "title", label: "Plant name", type: "string", required: true },
			// `code`: mã cơ sở xuất khẩu (vd DL 113) là định danh, nên không dịch. `location` là tên ngắn dùng ở footer.
			{ slug: "code", label: "Export code (e.g. DL 113)", type: "string", required: true, ...notTranslatable },
			{
				slug: "line",
				label: "Product line",
				type: "select",
				required: true,
				options: PLANT_LINES,
				...notTranslatable,
			},
			{ slug: "location", label: "Short location (footer)", type: "string" },
			{ slug: "province", label: "Province", type: "string" },
			{ slug: "address", label: "Address", type: "string" },
			{ slug: "image", label: "Photo", type: "image", ...notTranslatable },
			{ slug: "sort_order", label: "Order", type: "integer", ...notTranslatable },
		],
	},
	{
		// (Lưu ý: seed chưa có bài viết mẫu; editor thêm trong admin. Menu chính đã trỏ tới /news.)
		// `content` dùng `portableText` (rich text), khác với `pages.content` dùng `blocks`.
		// Company news, listed newest first at /news (and /vi/news).
		slug: "posts",
		label: "News",
		labelSingular: "Article",
		urlPattern: "/news/{slug}",
		supports: ["drafts", "revisions", "preview", "scheduling", "search", "seo"],
		fields: [
			{ slug: "title", label: "Title", type: "string", required: true, searchable: true },
			{
				slug: "excerpt",
				label: "Summary (shown on the news list and in search results)",
				type: "text",
				searchable: true,
			},
			{ slug: "featured_image", label: "Cover photo", type: "image", ...notTranslatable },
			{ slug: "content", label: "Article", type: "portableText", searchable: true },
		],
	},
];

// ---------------------------------------------------------------------------
// Pages
// ---------------------------------------------------------------------------

// block(type, key, fields) - tạo một block để đặt trong mảng `content` của trang.
//   type   : slug block type (phải nằm trong `allowedTypes` của pages.content, ví dụ "cases_hero").
//   key    : `_key` ổn định, duy nhất trong trang; trình soạn dùng để nhận diện block, và một số component
//            dùng làm id DOM (Catalog: `catalog-<key>`). Quy ước ở đây là tên dễ đọc (hero, story, ...).
//   fields : giá trị các field của block (có thể chứa T()).
// Trả về { _type, _version: 1, _key, ...fields } (_version khớp currentVersion của block type). Marker
// T() bên trong vẫn nguyên cho tới khi bilingual() gọi resolve().
const block = (type, key, fields) => ({ _type: type, _version: 1, _key: key, ...fields });

// Trang chủ (slug "home"): hero (center) -> codes -> reach (anchor "markets") -> features (anchor "advantages").
const home = [
	block("cases_hero", "hero", {
		layout: "center",
		eyebrow: T("Premium Seafood Export · Est. 1996", "Xuất Khẩu Thủy Sản Cao Cấp · Thành Lập 1996"),
		headline: T("Delivering the Oceans' Best", "Mang Tinh Hoa Đại Dương"),
		headline_accent: T("to the World", "Ra Thế Giới"),
		subheadline: T(
			"Specializing in Premium Black Tiger & Vannamei Shrimp — from Cà Mau, Vietnam to global markets.",
			"Chuyên xuất khẩu Tôm Sú và Tôm Thẻ chân trắng cao cấp — từ Cà Mau, Việt Nam ra thị trường toàn cầu.",
		),
		primary_cta_label: T("Explore Products", "Xem Sản Phẩm"),
		primary_cta_url: "/products",
		secondary_cta_label: T("Our Certifications", "Chứng Nhận Của Chúng Tôi"),
		secondary_cta_url: "/certifications",
	}),
	block("cases_codes", "codes", {
		headline: T("Certified European Union Export Codes", "Mã Xuất Khẩu Liên Minh Châu Âu"),
		// Chuỗi mã -> mảng { code } theo yêu cầu của repeater. Thêm mã: thêm vào mảng (tối đa 12).
		codes: ["DL 113", "DL 809", "DL 51", "DL 295", "DL 835", "DL 684"].map((code) => ({ code })),
	}),
	block("cases_reach", "reach", {
		anchor_id: "markets",
		eyebrow: T("Performance & Export Reach", "Năng Lực & Thị Trường Xuất Khẩu"),
		headline: T("Our Global Reach", "Tầm Vươn Toàn Cầu"),
		// `caption` của thẻ thứ 3 là chuỗi thường (không T) vì chỉ gồm các tên chứng nhận viết tắt, giống nhau ở mọi ngôn ngữ.
		figures: [
			{
				value: "29,024",
				label: T("Tons Exported", "Tấn Xuất Khẩu"),
				caption: T("2024 Total Volume", "Tổng Sản Lượng 2024"),
			},
			{
				value: "$184M",
				label: T("Export Turnover", "Kim Ngạch Xuất Khẩu"),
				caption: T("2024 Revenue", "Doanh Thu 2024"),
				highlight: true,
			},
			{
				value: "6+",
				label: T("Certifications", "Chứng Nhận"),
				caption: "HACCP · ISO · BRC · BAP · ASC · HALAL",
			},
		],
		// Bảng thị trường dạng tuple để khỏi lặp lại cấu trúc: [flag, tên EN, tên VI, kim ngạch, tấn, share, muted?].
		// `share` là độ dài thanh so với EU (=100), nhập tay. Phần tử cuối có cờ `true` ở vị trí thứ 7 = "Others" (xám).
		// Thêm thị trường: chèn một dòng (giữ "Others" ở cuối; tối đa 12 dòng theo schema).
		markets: [
			["🇪🇺", "EU", "EU", "$56.6M", "7,638", 100],
			["🇦🇺", "Australia", "Úc", "$34.5M", "3,536", 61],
			["🇰🇷", "Korea", "Hàn Quốc", "$29.9M", "3,913", 53],
			["🇺🇸", "USA", "Mỹ", "$15.1M", "695", 27],
			["🇯🇵", "Japan", "Nhật Bản", "$2.2M", "135", 4],
			["🇦🇪", "UAE", "UAE", "$5.6M", "675", 10],
			["🇨🇦", "Canada", "Canada", "$3.5M", "140", 6],
			["🇹🇼", "Taiwan", "Đài Loan", "$2.3M", "210", 4],
			["🌏", "Others", "Khác", "$13.2M", "1,827", 23, true],
		// Chuyển tuple thành object block: tên là T(en, vi); `volume` ghép số với đơn vị theo ngôn ngữ
		// ("tons" / "tấn", số giữ nguyên định dạng nhập); `muted` ép về boolean để phần tử không có cờ thành false.
		].map(([flag, en, vi, value, tons, share, muted]) => ({
			flag,
			name: T(en, vi),
			value,
			volume: T(`${tons} tons`, `${tons} tấn`),
			share,
			muted: Boolean(muted),
		})),
		// Dòng tổng cuối khối. Chú ý định dạng số khác nhau theo ngôn ngữ ("29,024" EN, "29.024" VI) nên được viết tay riêng.
		total_label: T("Total 2024", "Tổng 2024"),
		total_value: "$184M",
		total_caption: T("29,024 tons · 9 markets", "29.024 tấn · 9 thị trường"),
	}),
	// Mỗi thẻ: icon (tên Material Symbols), title, description. Thẻ CTA cuối: cta_url "#contact" mở khu vực liên hệ ở footer.
	block("cases_features", "advantages", {
		anchor_id: "advantages",
		eyebrow: T("Our Advantages", "Lợi Thế Của Chúng Tôi"),
		headline: T("Why Choose CASES?", "Tại Sao Chọn CASES?"),
		features: [
			{
				icon: "factory",
				title: T("6 Processing Plants", "6 Nhà Máy Chế Biến"),
				description: T(
					"Strategically located across Ca Mau, An Giang & Song Doc — with IQF machines, blast freezers & metal detectors ensuring premium output.",
					"Tọa lạc chiến lược tại Cà Mau, An Giang & Sông Đốc — trang bị máy IQF, tủ đông gió và máy dò kim loại đảm bảo chất lượng đầu ra cao cấp.",
				),
			},
			{
				icon: "workspace_premium",
				title: T("Top-Tier Certifications", "Chứng Nhận Hàng Đầu"),
				description: T(
					"HACCP · ISO 22000 · BRC · HALAL · BAP · ASC — meeting the strictest food safety standards for EU, Japan, USA & global buyers.",
					"HACCP · ISO 22000 · BRC · HALAL · BAP · ASC — đáp ứng tiêu chuẩn ATTP nghiêm ngặt nhất cho EU, Nhật Bản, Mỹ & người mua toàn cầu.",
				),
			},
			{
				icon: "diversity_3",
				title: T("Full Product Range", "Đa Dạng Sản Phẩm"),
				description: T(
					"Black Tiger Prawn, Vannamei Shrimp, Surimi, Cephalopods & Fish Meal — raw, cooked, value-added, and custom spec available.",
					"Tôm Sú, Tôm Thẻ, Surimi, Nhuyễn thể & Bột cá — dạng sống, chín, chế biến sâu và theo yêu cầu riêng.",
				),
			},
			{
				icon: "verified_user",
				title: T("29+ Years Experience", "29+ Năm Kinh Nghiệm"),
				description: T(
					"Founded 1996. Nearly three decades of expertise in frozen seafood processing, export compliance and international logistics.",
					"Thành lập 1996. Gần ba thập kỷ kinh nghiệm chế biến thủy sản đông lạnh, tuân thủ xuất khẩu và logistics quốc tế.",
				),
			},
			{
				icon: "public",
				title: T("9 Export Markets", "9 Thị Trường Xuất Khẩu"),
				description: T(
					"Trusted by buyers across EU, Japan, Korea, Australia, USA, UAE and more — with $184M turnover and 29,024 tons shipped in 2024.",
					"Được tin dùng bởi nhà mua hàng tại EU, Nhật, Hàn, Úc, Mỹ, UAE và nhiều hơn — doanh thu 184 triệu USD và 29.024 tấn xuất khẩu năm 2024.",
				),
			},
		],
		cta_title: T("Ready to Partner?", "Sẵn Sàng Hợp Tác?"),
		cta_body: T(
			"Contact our sales team for product specs, pricing and custom orders.",
			"Liên hệ đội ngũ kinh doanh để biết thông số sản phẩm, báo giá và đơn hàng theo yêu cầu.",
		),
		cta_label: T("Contact Us", "Liên Hệ"),
		cta_url: "#contact",
	}),
];

// Trang About: hero (split) -> timeline (anchor "story") -> values (anchor "mission").
const about = [
	block("cases_hero", "hero", {
		layout: "split",
		eyebrow: T("Est. 1996 · Cà Mau, Vietnam", "Thành Lập 1996 · Cà Mau, Việt Nam"),
		headline: T("Our Story, Our Ocean.", "Câu Chuyện Của Chúng Tôi, Đại Dương Của Chúng Tôi."),
		subheadline: T(
			"From a state-owned enterprise in the Mekong Delta to a global seafood powerhouse — nearly 3 decades of excellence.",
			"Từ doanh nghiệp nhà nước ở đồng bằng sông Cửu Long đến cường quốc thủy sản toàn cầu — gần 3 thập kỷ xuất sắc.",
		),
	}),
	block("cases_timeline", "story", {
		anchor_id: "story",
		eyebrow: T("Our Journey", "Hành Trình Phát Triển"),
		headline: T("The CASES Story", "Hành Trình CASES"),
		milestones: [
			{
				year: "1996",
				icon: "corporate_fare",
				title: T("Founded as State-Owned Enterprise", "Thành Lập Doanh Nghiệp Nhà Nước"),
				body: T(
					"CaMau Seafood Processing and Service Corp established in Cà Mau — Vietnam's southernmost province and seafood capital of the Mekong Delta.",
					"Công ty Chế biến Thủy sản Cà Mau thành lập tại Cà Mau — tỉnh cực nam Việt Nam và thủ đô thủy sản đồng bằng sông Cửu Long.",
				),
				note: T(
					"Began with shrimp processing for domestic & early export markets",
					"Bắt đầu chế biến tôm phục vụ thị trường trong nước & xuất khẩu ban đầu",
				),
			},
			{
				year: "2000s",
				icon: "workspace_premium",
				title: T("Expansion & Certification", "Mở Rộng & Chứng Nhận"),
				body: T(
					"Invested in IQF technology, blast freezers and metal detectors. Obtained HACCP, ISO 22000 and BRC certifications — opening doors to EU and Japanese markets.",
					"Đầu tư công nghệ IQF, tủ đông gió và máy dò kim loại. Đạt chứng nhận HACCP, ISO 22000 và BRC — mở cửa thị trường EU và Nhật Bản.",
				),
				note: T(
					"First EU export certifications secured · Global reach begins",
					"Đạt mã xuất khẩu EU đầu tiên · Bắt đầu vươn ra thị trường quốc tế",
				),
			},
			{
				year: "2016",
				icon: "trending_up",
				title: T("Equitization to Joint-Stock", "Cổ Phần Hóa"),
				body: T(
					"Under government policy, CASES transitioned from state-owned to joint-stock company — unlocking capital for modernisation and accelerating global expansion.",
					"Theo chính sách nhà nước, CASES chuyển đổi từ doanh nghiệp nhà nước sang công ty cổ phần — mở khóa vốn để hiện đại hóa và đẩy nhanh mở rộng toàn cầu.",
				),
				note: T(
					'Joint-stock model · Trade name "CASES" officially adopted',
					'Mô hình cổ phần · Tên thương mại "CASES" được chính thức đặt',
				),
			},
			{
				// Mốc cuối (hiện tại) dùng `year` là chữ nên bọc T(); các mốc trước dùng chuỗi năm thường. `highlight: true` nhấn mạnh mốc này.
				year: T("Today", "Hôm Nay"),
				icon: "public",
				highlight: true,
				title: T("Top-Tier Global Exporter", "Nhà Xuất Khẩu Hàng Đầu Toàn Cầu"),
				body: T(
					"6 processing plants across Ca Mau & An Giang. $184M export turnover in 2024. Supplying EU, Japan, Korea, Australia, USA and more with premium frozen seafood.",
					"6 nhà máy chế biến tại Cà Mau & An Giang. Doanh thu xuất khẩu 184 triệu USD năm 2024. Cung cấp thủy sản đông lạnh cao cấp cho EU, Nhật, Hàn, Úc, Mỹ và nhiều hơn.",
				),
				note: T(
					"$184M turnover · 29,024 tons · 9 export markets · 2024",
					"184 triệu USD · 29.024 tấn · 9 thị trường xuất khẩu · 2024",
				),
			},
		],
	}),
	// Dùng cả 2 phần: thẻ Sứ mệnh/Tầm nhìn (cards) và khung trích dẫn (quote). Dấu nháy cong “ ” là ký tự
	// nằm trong nội dung, không phải cú pháp JS.
	block("cases_values", "values", {
		anchor_id: "mission",
		cards: [
			{
				icon: "flag",
				title: T("Our Mission", "Sứ Mệnh"),
				body: T(
					"“To deliver the finest frozen seafood from Vietnam's richest waters to the world's most discerning buyers — with uncompromising quality, traceability and service.”",
					"“Mang tinh hoa thủy sản đông lạnh từ vùng biển giàu có nhất Việt Nam đến tay khách hàng khó tính nhất thế giới — với chất lượng không thỏa hiệp, truy xuất nguồn gốc và dịch vụ tận tâm.”",
				),
			},
			{
				icon: "visibility",
				title: T("Our Vision", "Tầm Nhìn"),
				body: T(
					"“To be Vietnam's most trusted seafood export partner — recognised globally for sustainable practices, premium products and long-term buyer relationships.”",
					"“Trở thành đối tác xuất khẩu thủy sản uy tín nhất Việt Nam — được công nhận toàn cầu vì thực hành bền vững, sản phẩm cao cấp và quan hệ lâu dài với người mua.”",
				),
			},
		],
		quote: T("“Quality is Life”", "“Chất Lượng Là Sự Sống Còn”"),
		quote_body: T(
			"'Quality is Life' — the founding principle that has guided every decision at CASES for nearly three decades.",
			"'Chất lượng là sự sống còn' — nguyên tắc sáng lập đã định hướng mọi quyết định tại CASES trong gần ba thập kỷ.",
		),
		primary_cta_label: T("View Our Products", "Xem Sản Phẩm"),
		primary_cta_url: "/products",
		secondary_cta_label: T("Contact Sales Team", "Liên Hệ Đội Bán Hàng"),
		secondary_cta_url: "#contact",
	}),
];

// Trang Infrastructure: hero (split, kèm `stats` công suất từng dây chuyền) -> plants -> tech.
const infrastructure = [
	block("cases_hero", "hero", {
		layout: "split",
		eyebrow: T("Processing Infrastructure", "Cơ Sở Hạ Tầng Chế Biến"),
		headline: T("Where Quality Begins.", "Nơi Chất Lượng Khởi Nguồn."),
		subheadline: T(
			"5 processing plants · Cà Mau & An Giang · State-of-the-art IQF technology",
			"5 nhà máy chế biến · Cà Mau & An Giang · Công nghệ IQF tiên tiến hàng đầu",
		),
		// Tuple [công suất, tên EN, tên VI] -> { value, unit: T("t/yr","tấn/năm"), label }. Đơn vị giống nhau cho mọi dòng.
		stats: [
			["20,000", "Shrimp", "Tôm"],
			["20,000", "Surimi", "Surimi"],
			["3,000", "Cephalopod", "Nhuyễn Thể"],
			["8,000", "Fishmeal", "Bột Cá"],
		].map(([value, en, vi]) => ({ value, unit: T("t/yr", "tấn/năm"), label: T(en, vi) })),
	}),
	block("cases_plants", "plants", {
		anchor_id: "plants",
		eyebrow: T("Our Facilities", "Cơ Sở Của Chúng Tôi"),
		headline: T("Processing Plants", "Nhà Máy Chế Biến"),
		// Chỉ hai nhóm vì seed chỉ có nhà máy dây chuyền shrimp (2) và surimi (3). `label` chứa công suất, do editor nhập tay
		// (không tính từ dữ liệu). Thêm nhóm: thêm một `line` thuộc PLANT_LINES (tối đa 4).
		groups: [
			{ line: "shrimp", label: T("Shrimp · 18,000–20,000 t/yr", "Tôm · 18.000–20.000 tấn/năm") },
			{ line: "surimi", label: T("Surimi · 18,000–20,000 t/yr", "Surimi · 18.000–20.000 tấn/năm") },
		],
	}),
	// Thẻ công nghệ chưa kèm `image`, nên hiển thị khung ảnh trống cho tới khi editor upload trong admin.
	block("cases_tech", "technology", {
		anchor_id: "technology",
		eyebrow: T("Core Technology", "Công Nghệ Cốt Lõi"),
		headline: T("Processing Technology", "Công Nghệ Chế Biến"),
		items: [
			{
				title: T("IQF Freezing", "Đông Lạnh IQF"),
				description: T(
					"Super-speed Individually Quick Frozen lines — locking cellular integrity at −40°C for premium texture upon defrost.",
					"Dây chuyền đông lạnh IQF tốc độ cao — khóa độ toàn vẹn tế bào ở −40°C để giữ kết cấu cao cấp sau khi rã đông.",
				),
			},
			{
				title: T("Blast Freezer", "Tủ Đông Gió"),
				description: T(
					"Air blast freezing for block and semi-block products — rapid cycles preserve quality for long-haul international export.",
					"Đông lạnh gió cho sản phẩm block và semi-block — chu kỳ nhanh bảo toàn chất lượng cho xuất khẩu quốc tế đường dài.",
				),
			},
			{
				title: T("Metal Detector & QC", "Máy Dò Kim Loại & QC"),
				description: T(
					"100% product scanning at every stage. Zero-tolerance policy before packaging — meeting EU and international food safety standards.",
					"Quét 100% sản phẩm tại mọi công đoạn. Chính sách không khoan nhượng trước đóng gói — đáp ứng tiêu chuẩn ATTP EU và quốc tế.",
				),
			},
			{
				title: T("Cold Storage", "Kho Lạnh"),
				description: T(
					"Temperature-controlled cold chain from processing to container loading — maintained at −18°C to −22°C throughout.",
					"Chuỗi lạnh kiểm soát nhiệt độ từ chế biến đến xếp container — duy trì −18°C đến −22°C xuyên suốt.",
				),
			},
		],
	}),
];

// Trang Certifications: hero (glow) -> 3 nhóm cases_certs (food_safety / aquaculture / specialist) -> spotlight FDA
// -> bảng tổng hợp. `_key` và `anchor_id` của 3 nhóm trùng với tên nhóm; footerItems trỏ tới #aquaculture và #overview.
const certificationsPage = [
	block("cases_hero", "hero", {
		layout: "glow",
		eyebrow: T("International Certifications", "Chứng Nhận Quốc Tế"),
		headline: T("Quality", "Chất Lượng"),
		headline_accent: T("Recognised Worldwide", "Được Công Nhận Toàn Cầu"),
		subheadline: T(
			"CASES holds the seafood industry's most respected certifications — from HACCP, ISO 22000 and BRC to BAP, ASC, HALAL and ORGANIC — demonstrating our unwavering commitment to food safety and sustainability.",
			"CASES tự hào đạt các chứng nhận uy tín nhất ngành thủy sản — từ HACCP, ISO 22000, BRC đến BAP, ASC, HALAL và ORGANIC — khẳng định cam kết không thỏa hiệp về an toàn thực phẩm và phát triển bền vững.",
		),
		// Số liệu ở hero được nhập tay (không tự tính từ collection), nên cần cập nhật khi thêm/bớt chứng nhận.
		stats: [
			{ value: "10+", label: T("Certifications", "Chứng Nhận") },
			{ value: "30+", label: T("Years Experience", "Năm Kinh Nghiệm") },
			{ value: "9+", label: T("Export Markets", "Thị Trường XK") },
		],
	}),
	// Các block cases_certs chỉ chứa tiêu đề + `group`; thẻ thực tế đến từ collection certifications lúc request.
	block("cases_certs", "food-safety", {
		anchor_id: "food-safety",
		group: "food_safety",
		eyebrow: T("Certification Group", "Nhóm Chứng Nhận"),
		headline: T("Food Safety & Quality", "An Toàn Thực Phẩm & Chất Lượng"),
	}),
	block("cases_certs", "aquaculture", {
		anchor_id: "aquaculture",
		group: "aquaculture",
		eyebrow: T("Certification Group", "Nhóm Chứng Nhận"),
		headline: T("Sustainable Aquaculture", "Nuôi Trồng Bền Vững"),
	}),
	block("cases_certs", "specialist", {
		anchor_id: "specialist",
		group: "specialist",
		eyebrow: T("Certification Group", "Nhóm Chứng Nhận"),
		headline: T("Specialist Markets", "Thị Trường Đặc Thù"),
	}),
	block("cases_spotlight", "fda", {
		anchor_id: "fda",
		eyebrow: T("🇺🇸 USA Market", "🇺🇸 Thị Trường Hoa Kỳ"),
		headline: T("US FDA Registration", "Đăng Ký FDA Hoa Kỳ"),
		body: T(
			"CASES is registered with the US Food & Drug Administration — a prerequisite for seafood exports into the American market. Combined with our HACCP standard, this ensures full FDA import-regulation compliance.",
			"CASES được Cục Quản lý Thực phẩm và Dược phẩm Hoa Kỳ (FDA) đăng ký — điều kiện tiên quyết để xuất khẩu thủy sản vào thị trường Mỹ. Kết hợp với tiêu chuẩn HACCP, điều này đảm bảo tuân thủ đầy đủ quy định nhập khẩu của FDA.",
		),
		// Mỗi tag một dòng ("\n") theo yêu cầu của field `tags`; cờ emoji + tên quốc gia được dịch tên.
		tags: T(
			"🇺🇸 USA\n🇪🇺 EU\n🇯🇵 Japan\n🇰🇷 Korea\n🇦🇺 Australia\n🇨🇦 Canada\n🇦🇪 UAE",
			"🇺🇸 Mỹ\n🇪🇺 EU\n🇯🇵 Nhật Bản\n🇰🇷 Hàn Quốc\n🇦🇺 Úc\n🇨🇦 Canada\n🇦🇪 UAE",
		),
		badge_icon: "🏛️",
		badge_title: T("FDA Registered", "Đã Đăng Ký FDA"),
		badge_caption: "Registrar Corp",
	}),
	// Không có dữ liệu ở đây: bảng đọc toàn bộ collection certifications (trừ mục show_in_table = false).
	block("cases_cert_table", "overview", {
		anchor_id: "overview",
		headline: T("Full Certification Overview", "Tổng Hợp Chứng Nhận"),
	}),
];

// Bảng 6 danh mục của trang Products dạng tuple:
// [key, code (chữ lớn watermark), subtitle (dòng nhỏ), tên EN, tên VI, emoji, màu].
// Thêm danh mục: thêm dòng này, thêm key vào CATEGORY_KEYS, thêm mảng dữ liệu vào productData, và nếu
// dùng màu mới thì thêm vào select `color` và bảng COLORS của Catalog.astro.
const categories = [
	["btp", "BTP", "Black Tiger", "Black Tiger Prawn", "Tôm Sú", "🦐", "sky"],
	["van", "VAN", "Vannamei", "Vannamei Shrimp", "Tôm Thẻ", "🦐", "cyan"],
	["vap_btp", "VAP", "BTP", "Value-Added · BTP", "Sản phẩm GTGT · Tôm Sú", "🍤", "red"],
	["vap_van", "VAP", "Vannamei", "Value-Added · Vannamei", "Sản phẩm GTGT · Tôm Thẻ", "🍤", "orange"],
	["surimi", "SRM", "Surimi", "Frozen Surimi & Fish Meal", "Surimi & Bột Cá", "🐟", "teal"],
	["cephalopod", "CPD", "Cephalopod", "Cephalopod", "Nhuyễn Thể", "🐙", "slate"],
];

// Trang Products: hero (banner) -> catalog (anchor "catalog") -> cta liên hệ qua email.
const productsPage = [
	block("cases_hero", "hero", {
		layout: "banner",
		eyebrow: T("Full Product Catalog", "Danh Mục Sản Phẩm"),
		headline: T("Ocean Premium,", "Tinh Hoa Đại Dương,"),
		headline_accent: T("Delivered Globally", "Xuất Khẩu Toàn Cầu"),
		subheadline: T(
			"Click a category to see its products.",
			"Bấm vào từng danh mục để xem chi tiết sản phẩm.",
		),
	}),
	// Chuyển tuple của `categories` thành object block. `name` là T(tên EN, tên VI).
	block("cases_catalog", "catalog", {
		anchor_id: "catalog",
		categories: categories.map(([key, code, subtitle, name, viName, emoji, color]) => ({
			key,
			code,
			subtitle,
			name: T(name, viName),
			// (Chi tiết kỹ thuật: T("", name) cho bản en bằng chuỗi rỗng nên resolve() loại key `caption` ở bản en.)
			// Vietnamese visitors still see the English trade name as a subtitle.
			caption: T("", name),
			emoji,
			color,
		})),
	}),
	// Nhãn nút là chính địa chỉ email nên là chuỗi thường (không dịch); `cta_url` dùng mailto: (hợp lệ theo pattern của LINK).
	block("cases_cta", "cta", {
		headline: T("Interested in Our Products?", "Quan Tâm Đến Sản Phẩm?"),
		body: T(
			"Contact our sales team for product specifications, pricing, and export inquiries.",
			"Liên hệ đội ngũ kinh doanh để biết thông số sản phẩm, báo giá và yêu cầu xuất khẩu.",
		),
		icon: "mail",
		cta_label: "salesmanager@cases.com.vn",
		cta_url: "mailto:salesmanager@cases.com.vn",
	}),
];

// Danh sách entry của collection `pages`. Mỗi trang gọi bilingual(id, slug, { title, content }); id trùng slug
// (bản vi là `<id>-vi`). Slug khớp các route src/pages (home -> trang chủ, còn lại qua [slug]). Kết quả:
// 5 trang x 2 ngôn ngữ = 10 entry.
const pages = [
	...bilingual("home", "home", { title: T("Home", "Trang Chủ"), content: home }),
	...bilingual("about", "about", { title: T("About Us", "Về Chúng Tôi"), content: about }),
	...bilingual("products", "products", { title: T("Products", "Sản Phẩm"), content: productsPage }),
	...bilingual("infrastructure", "infrastructure", {
		title: T("Infrastructure", "Cơ Sở Hạ Tầng"),
		content: infrastructure,
	}),
	...bilingual("certifications", "certifications", {
		title: T("Certifications", "Chứng Nhận"),
		content: certificationsPage,
	}),
];

// ---------------------------------------------------------------------------
// Products
// ---------------------------------------------------------------------------

// DỮ LIỆU SẢN PHẨM. AS_REQUIRED: giá trị thay cho kích cỡ khi sản phẩm không có size (null trong productData,
// chủ yếu hàng value-added); T() vì đây là chữ hiển thị.
const AS_REQUIRED = T("As required", "Theo yêu cầu");
// ORIGIN: nơi sản xuất, chuỗi thường dùng chung cho mọi sản phẩm và cả hai ngôn ngữ.
const ORIGIN = "Cà Mau, VN";

// productData: object khóa theo CATEGORY_KEYS, mỗi giá trị là mảng các dòng sản phẩm. Dòng:
//   [tên (không dịch), size hoặc null, kiểu đóng gói, glaze, mô tả EN, mô tả VI].
//   size = null -> AS_REQUIRED; glaze "N/A" cho sản phẩm không mạ băng. Tên sản phẩm giữ nguyên tiếng Anh ở
//   cả hai ngôn ngữ (thuật ngữ thương mại), mô tả VI cũng cố ý giữ các thuật ngữ tiếng Anh (PD, HLSO...).
// Thêm sản phẩm: thêm một dòng vào đúng danh mục. Slug được sinh tự động `<category>-<tên>`; trong cùng
// danh mục KHÔNG được có hai sản phẩm trùng tên vì sẽ trùng slug. Thứ tự hiển thị = thứ tự dòng trong mảng.
// [name, size, pack, glaze, English description, Vietnamese description]
const productData = {
	btp: [
		["PD Tail On", "6/8 – 31/40", "IQF / Block", "10–15%", "Black Tiger Prawn – PD Tail On. Peeled and deveined with the tail left on. Popular in the US and EU for shrimp cocktail.", "Black Tiger Prawn – PD Tail On. Bóc vỏ, bỏ chỉ, còn đuôi. Phổ biến tại thị trường Mỹ, EU cho món cocktail shrimp."],
		["PD", "8/12 – 41/50", "IQF / Block", "10–15%", "Black Tiger Prawn – PD. Peeled & Deveined, with shell and back vein fully removed. HACCP export standard.", "Black Tiger Prawn – PD. Peeled & Deveined, bỏ hoàn toàn vỏ và chỉ lưng. Chuẩn xuất khẩu HACCP."],
		["Head On Shell On", "16/20 – 51/60", "Block", "12%", "Black Tiger Prawn – Head On Shell On. Whole prawn with head and shell intact for a natural, fresh flavour.", "Black Tiger Prawn – Head On Shell On. Tôm nguyên con, giữ nguyên đầu và vỏ. Hương vị tự nhiên tươi ngon."],
		["Headless Shell On", "8/12 – 41/50", "IQF / Block", "10–15%", "Black Tiger Prawn – Headless Shell On (HLSO). Head removed, shell on. A favourite export to Japan and Korea.", "Black Tiger Prawn – Headless Shell On (HLSO). Bỏ đầu, còn vỏ. Phổ biến xuất khẩu sang Nhật, Hàn Quốc."],
		["Nobashi", "3L / 4L / 5L", "IQF", "10%", "Black Tiger – Nobashi. Stretched prawn, ideal for tempura and premium Japanese dishes.", "Black Tiger – Nobashi. Tôm kéo dài, thích hợp cho Tempura và các món ăn Nhật Bản cao cấp."],
		["Head On Body Peeled", null, "IQF", "10%", "Black Tiger – Head On Body Peeled. Head kept, body shell fully removed. Made for premium plating.", "Black Tiger – Head On Body Peeled. Còn đầu nhưng bóc toàn bộ thân vỏ. Dùng cho trình bày món ăn cao cấp."],
		["Easy Peeled", "16/20 – 41/50", "IQF", "10%", "Black Tiger – Easy Peeled. Cut along the back for effortless peeling. Convenient for end consumers.", "Black Tiger – Easy Peeled. Khía dọc sống lưng, bóc vỏ dễ dàng. Tiện lợi cho người tiêu dùng cuối."],
		["Cooked PDTO", "16/20 – 41/50", "IQF", "10%", "Black Tiger – Cooked PDTO. Cooked, peeled, deveined, tail on. For premium buffets and cocktails.", "Black Tiger – Cooked PDTO. Tôm luộc, bóc vỏ, còn đuôi, bỏ chỉ lưng. Dùng cho buffet và cocktail cao cấp."],
		["Skewer PDTO", "21/25 – 36/40", "IQF", "10%", "Black Tiger – Skewer PDTO. Peeled tail-on prawns on skewers. Convenient for BBQ and foodservice.", "Black Tiger – Skewer PDTO. Tôm xiên que, bóc vỏ còn đuôi. Tiện lợi cho BBQ và foodservice."],
		["Cooked PD", "31/40 – 71/90", "IQF", "10%", "Black Tiger – Cooked PD. Cooked and fully peeled. Ready to eat; exported to the EU and North America.", "Black Tiger – Cooked PD. Tôm luộc, bóc hoàn toàn vỏ. Tiện dùng ngay, xuất EU và Bắc Mỹ."],
	],
	van: [
		["HLSO", "8/12 – 41/50", "IQF / Block", "10–15%", "Vannamei – HLSO. Headless, shell on. A favourite export to Japan and Korea.", "Vannamei – HLSO. Head Less Shell On, thân tôm bỏ đầu còn vỏ. Phổ biến xuất khẩu sang Nhật, Hàn Quốc."],
		["Nobashi", "3L / 4L / 5L", "IQF", "10%", "Vannamei – Nobashi. Stretched shrimp for tempura in the premium Japanese market.", "Vannamei – Nobashi. Tôm kéo dài phù hợp cho Tempura tại thị trường Nhật Bản cao cấp."],
		["Head On Shell On", "16/20 – 51/60", "Block", "12%", "Vannamei – Head On Shell On. Whole shrimp with head and shell, keeping its full natural flavour.", "Vannamei – Head On Shell On. Tôm nguyên con còn đầu còn vỏ. Giữ trọn hương vị tự nhiên."],
		["Skewer PD", "21/25 – 36/40", "IQF", "10%", "Vannamei – Skewer PD. Peeled shrimp on skewers. Convenient for BBQ restaurants and fast food in the US.", "Vannamei – Skewer PD. Tôm xiên que đã bóc vỏ. Tiện lợi cho nhà hàng BBQ và fast food tại Mỹ."],
		["Cooked PD", "31/40 – 71/90", "IQF", "10%", "Vannamei – Cooked PD. Cooked, peeled and IQF frozen. Ready to eat; exported to Europe and North America.", "Vannamei – Cooked PD. Tôm luộc bóc vỏ, đông IQF. Tiện dùng ngay, xuất châu Âu và Bắc Mỹ."],
		["PD", "16/20 – 61/70", "IQF / Block", "10–15%", "Vannamei – PD. Peeled & Deveined. EU export standard, HACCP certified.", "Vannamei – PD. Peeled & Deveined, bóc vỏ bỏ chỉ lưng. Chuẩn xuất khẩu EU, HACCP certified."],
		["Cooked PDTO", "16/20 – 41/50", "IQF", "10%", "Vannamei – Cooked PDTO. Cooked, peeled, deveined, tail on. For premium cocktails and buffets.", "Vannamei – Cooked PDTO. Tôm luộc bóc vỏ còn đuôi, bỏ chỉ lưng. Dùng cho cocktail và buffet cao cấp."],
		["PDTO", "8/12 – 41/50", "IQF / Block", "10–12%", "Vannamei – PDTO. Peeled, Deveined, Tail On. Popular in the US and Canada.", "Vannamei – PDTO. Peeled, Deveined, Tail On. Bóc vỏ còn đuôi. Phổ biến tại Mỹ và Canada."],
		["Skewer PDTO", "21/25 – 36/40", "IQF", "10%", "Vannamei – Skewer PDTO. Peeled tail-on shrimp on skewers. Ideal for BBQ and foodservice in Japan and the US.", "Vannamei – Skewer PDTO. Tôm xiên que bóc vỏ còn đuôi. Ideal cho BBQ, foodservice Nhật, Mỹ."],
	],
	vap_btp: [
		["Butterfly", null, "IQF", "N/A", "BTP – Butterfly. Butterflied along the back. Typically used for breaded shrimp and restaurant presentation.", "BTP – Butterfly. Tôm bướm, cắt dọc lưng mở rộng. Thường dùng cho breaded shrimp và trình bày nhà hàng."],
		["Coconut Torpedo", null, "IQF", "N/A", "BTP – Coconut Torpedo. Torpedo-shaped shrimp in a coconut coating. A premium value-added product for the EU and US.", "BTP – Coconut Torpedo. Tôm hình ngư lôi phủ dừa. Sản phẩm value-added cao cấp xuất EU, Mỹ."],
		["Sushi Ebi", "3L / 4L / 5L", "IQF", "10%", "BTP – Sushi Ebi. Cooked, stretched shrimp for sushi. Exported to Japan and premium sushi markets.", "BTP – Sushi Ebi. Tôm luộc kéo dài dạng sushi. Xuất khẩu sang Nhật Bản và thị trường sushi cao cấp."],
		["Breaded Fried", null, "IQF", "N/A", "BTP – Breaded Fried. Breaded, fried and IQF frozen. Convenient for foodservice and retail.", "BTP – Breaded Fried. Tôm tẩm bột chiên giòn, đông lạnh IQF. Tiện lợi cho foodservice và retail."],
		["Tempura", null, "IQF", "N/A", "BTP – Tempura. A thin, light tempura batter that keeps the shrimp's fresh texture. A Japanese export speciality.", "BTP – Tempura. Tôm tempura, lớp bột mỏng giòn nhẹ. Đặc sản xuất khẩu Nhật Bản, giữ kết cấu tôm tươi."],
	],
	vap_van: [
		["Breaded Crumb Butterfly", null, "IQF", "N/A", "Vannamei – Breaded Crumb Butterfly. Butterfly shrimp in breadcrumbs. Suited to fried restaurant appetisers.", "Vannamei – Breaded Crumb Butterfly. Tôm bướm phủ breadcrumb. Phù hợp fried appetizer tại nhà hàng."],
		["Breaded Butterfly Fried", null, "IQF", "N/A", "Vannamei – Breaded Butterfly Fried. Pre-fried breaded butterfly shrimp. Retail-ready for the EU and North America.", "Vannamei – Breaded Butterfly Fried. Tôm bướm tẩm bột chiên sẵn. Retail-ready tại EU và Bắc Mỹ."],
		["Tempura", null, "IQF", "N/A", "Vannamei – Tempura. A light, crisp tempura batter. Popular in Japan and premium Asian markets.", "Vannamei – Tempura. Lớp bột tempura nhẹ, giòn. Phổ biến tại Nhật Bản và thị trường châu Á cao cấp."],
		["Coconut Butterfly", null, "IQF", "N/A", "Vannamei – Coconut Butterfly. Butterfly shrimp in shredded coconut. A signature product for the US and Caribbean markets.", "Vannamei – Coconut Butterfly. Tôm bướm phủ dừa bào. Sản phẩm đặc trưng cho thị trường Mỹ và Caribbean."],
		["Coconut Torpedo", null, "IQF", "N/A", "Vannamei – Coconut Torpedo. Torpedo shrimp in a coconut coating. Premium value-added for the US and EU.", "Vannamei – Coconut Torpedo. Tôm hình ngư lôi phủ dừa. Value-added cao cấp, thị trường Mỹ và EU."],
		["Breaded Torpedo", null, "IQF", "N/A", "Vannamei – Breaded Torpedo. Torpedo shrimp in breadcrumbs. Uniform sizing for fast-food chains.", "Vannamei – Breaded Torpedo. Tôm ngư lôi tẩm breadcrumb. Kích thước đồng đều, phù hợp fast food chain."],
	],
	surimi: [
		["Frozen Surimi", null, "Block", "N/A", "Frozen Surimi. Finely minced fish frozen in blocks. Raw material for crabsticks and fishballs.", "Frozen Surimi. Thịt cá xay mịn đông lạnh dạng block. Nguyên liệu sản xuất crabstick, fishball xuất khẩu."],
		["Fish Meal", null, "Bulk", "N/A", "Fish Meal. High-nutrition fish meal. Raw material for livestock and aquaculture feed, exported to many markets.", "Fish Meal. Bột cá dinh dưỡng cao. Nguyên liệu thức ăn chăn nuôi và thủy sản, xuất khẩu nhiều thị trường."],
	],
	cephalopod: [
		["Baby Octopus", null, "IQF / Block", "10%", "Baby Octopus. Whole baby octopus, IQF frozen. Exported to Japan, Korea, the EU and the Mediterranean.", "Baby Octopus. Bạch tuộc non nguyên con đông lạnh IQF. Xuất khẩu sang Nhật, Hàn Quốc, EU và Địa Trung Hải."],
		["Baby Octopus Block Frozen", null, "Block", "10%", "Baby Octopus Block Frozen. Baby octopus frozen in blocks for easy shipping and long-term storage.", "Baby Octopus Block Frozen. Bạch tuộc non đông lạnh dạng block. Tiện vận chuyển và bảo quản dài hạn."],
	],
};

// slugify(value) - tạo slug ASCII: chữ thường, mỗi cụm ký tự không phải a-z0-9 thành một dấu "-", rồi cắt
// "-" ở đầu/cuối. Chưa chuẩn hóa dấu tiếng Việt (ký tự có dấu sẽ thành "-"), ổn vì chỉ dùng cho tên tiếng Anh
// và key danh mục (vd "vap_btp" -> "vap-btp", "Head On Shell On" -> "head-on-shell-on").
const slugify = (value) =>
	value
		.toLowerCase()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-|-$/g, "");

// Bộ đếm thứ tự toàn cục: tăng 10 cho mỗi sản phẩm theo thứ tự khai báo (danh mục rồi dòng) => sort_order
// duy nhất, khoảng cách 10 để chèn tay sau này. Catalog lọc theo danh mục rồi vẫn giữ thứ tự này.
let productOrder = 0;
// flatMap 2 tầng: ngoài duyệt danh mục, trong duyệt dòng sản phẩm; bilingual() trả mảng [en, vi] nên kết
// quả là danh sách phẳng 68 entry (34 sản phẩm x 2 ngôn ngữ). id = `product-<category>-<tên>`.
const products = Object.entries(productData).flatMap(([category, items]) =>
	items.flatMap(([name, size, packing, glaze, en, vi]) => {
		productOrder += 10;
		const slug = `${slugify(category)}-${slugify(name)}`;
		return bilingual(`product-${slug}`, slug, {
			title: name,
			category,
			// `??` (không phải ||): chỉ thay khi size là null/undefined.
			size: size ?? AS_REQUIRED,
			packing,
			origin: ORIGIN,
			glaze,
			description: T(en, vi),
			sort_order: productOrder,
		});
	}),
);

// ---------------------------------------------------------------------------
// Certifications
// ---------------------------------------------------------------------------

// DỮ LIỆU CHỨNG NHẬN. ACTIVE: trạng thái mặc định ("Active"/"Hiệu lực") cho cột Trạng thái của bảng.
const ACTIVE = T("Active", "Hiệu lực");
// certData: mỗi object là một chứng nhận (sinh 2 entry). Quy ước field:
//   slug      : tách ra làm slug/id (`cert-<slug>`), không nằm trong data.
//   title     : tên chuẩn (không dịch); group: nhóm; icon: emoji; tone / badge_tone: màu (TONES).
//   badge, description, note: chữ hiển thị (T). `note` là chuỗi thường khi chỉ là mã/tên riêng (vd số chứng chỉ).
//   issuer, scope: cột của bảng; issuer "—" nghĩa là chưa có thông tin (bảng hiển thị nguyên dấu gạch).
// Giá trị mặc định (cert_status, show_in_table, sort_order) được thêm ở vòng lặp bên dưới; một dòng có thể
// ghi đè chúng. Mục `us-fda` thiếu badge/description vì thuộc group "other" (chỉ hiện trong bảng).
// Thêm chứng nhận: thêm object mới vào mảng; sort_order theo vị trí trong mảng.
const certData = [
	{
		slug: "haccp",
		title: "HACCP",
		group: "food_safety",
		icon: "🛡️",
		tone: "blue",
		badge: T("Hazard Analysis", "Phân Tích Mối Nguy"),
		badge_tone: "blue",
		description: T(
			"Hazard Analysis and Critical Control Points — CASES' foundational food-safety management system since 2000, ensuring full processing-chain control from raw material to final product.",
			"Hệ thống phân tích mối nguy và kiểm soát điểm tới hạn — nền tảng quản lý an toàn thực phẩm của CASES từ năm 2000, đảm bảo kiểm soát toàn bộ chuỗi từ nguyên liệu đến thành phẩm.",
		),
		note: T("Active · Full facility scope", "Hiệu lực · Toàn nhà máy"),
		issuer: "NAFIQAD",
		scope: T("Full facility", "Toàn nhà máy"),
	},
	{
		slug: "iso-22000",
		title: "ISO 22000:2018",
		group: "food_safety",
		icon: "📋",
		tone: "blue",
		badge: T("Food Safety Mgmt", "Quản Lý ATTP"),
		badge_tone: "green",
		description: T(
			"International standard for Food Safety Management Systems — issued by NAFIQAD covering seafood processing and freezing operations at CASES facilities.",
			"Tiêu chuẩn quốc tế về hệ thống quản lý an toàn thực phẩm — do NAFIQAD cấp cho hoạt động chế biến và cấp đông thủy sản tại cơ sở CASES.",
		),
		note: "TCVN ISO 22000:2018 · NAFIQAD",
		issuer: "NAFIQAD",
		scope: T("Seafood processing", "Chế biến thủy sản"),
	},
	{
		slug: "ifs-food",
		title: "IFS Food v8",
		group: "food_safety",
		icon: "🏆",
		tone: "amber",
		badge: T("Retail Standard", "Tiêu Chuẩn Bán Lẻ"),
		badge_tone: "amber",
		description: T(
			"IFS Food Version 8 — the international retail standard awarded by Intertek, opening access to European supermarket chains with a high audit score.",
			"IFS Food phiên bản 8 — tiêu chuẩn bán lẻ quốc tế do Intertek cấp, mở cánh cửa vào chuỗi siêu thị châu Âu với điểm đánh giá cao.",
		),
		note: T("Intertek · EU Retail Markets", "Intertek · Thị trường bán lẻ EU"),
		issuer: "Intertek",
		scope: T("EU retail market", "Thị trường bán lẻ EU"),
	},
	{
		slug: "bap",
		title: "BAP ★★",
		group: "aquaculture",
		icon: "🐟",
		tone: "green",
		badge: T("Best Aquaculture Practices", "Thực Hành Nuôi Trồng Tốt Nhất"),
		badge_tone: "green",
		description: T(
			"2-star BAP certification (plant + farm) from the Global Seafood Alliance — the highest-tier standard for responsible aquaculture, preferred by US and EU importers.",
			"Chứng nhận BAP 2 sao (nhà máy + trang trại) từ Global Seafood Alliance — tiêu chuẩn cao nhất về nuôi trồng thủy sản có trách nhiệm, được nhà nhập khẩu Mỹ và EU ưu tiên.",
		),
		note: T("GSA · Dam Doi Farm · Plant", "GSA · Trại Dam Doi · Nhà máy"),
		issuer: "Global Seafood Alliance",
		scope: T("Plant + farm", "Nhà máy + trang trại"),
	},
	{
		slug: "asc-coc",
		title: "ASC CoC",
		group: "aquaculture",
		icon: "🌊",
		tone: "green",
		badge: T("Chain of Custody", "Chuỗi Hành Trình"),
		badge_tone: "green",
		description: T(
			"ASC Chain of Custody — product traceability certification to Aquaculture Stewardship Council standards, enabling ASC labelling on exported products.",
			"ASC Chain of Custody — chứng nhận truy xuất nguồn gốc theo tiêu chuẩn Hội đồng Quản lý Nuôi trồng, cho phép dán nhãn ASC trên sản phẩm xuất khẩu.",
		),
		note: "Control Union · ASC-CoC-010639",
		issuer: "Control Union",
		scope: T("Product traceability", "Truy xuất nguồn gốc"),
	},
	{
		slug: "global-gap",
		title: "Global G.A.P",
		group: "aquaculture",
		icon: "🌱",
		tone: "green",
		badge: T("Pond Farming", "Nuôi Ao"),
		badge_tone: "blue",
		description: T(
			"Global Seafood Alliance Pond Farming standard awarded by Bureau Veritas for Dam Doi Farm — confirming raw-material control from the start of the supply chain.",
			"Tiêu chuẩn nuôi ao của Global Seafood Alliance do Bureau Veritas cấp cho Trại Dam Doi — xác nhận kiểm soát nguyên liệu từ đầu chuỗi cung ứng.",
		),
		note: T("Bureau Veritas · Dam Doi Farm", "Bureau Veritas · Trại Dam Doi"),
		issuer: "Bureau Veritas",
		scope: T("Dam Doi Farm", "Trại Dam Doi"),
	},
	{
		slug: "halal",
		title: "HALAL",
		group: "specialist",
		icon: "☪️",
		tone: "purple",
		badge: T("Islamic Market", "Thị Trường Hồi Giáo"),
		badge_tone: "purple",
		description: T(
			"HALAL certification ensuring products meet Islamic food requirements, expanding market access to UAE, Malaysia and Southeast Asian markets.",
			"Chứng nhận HALAL đảm bảo sản phẩm đáp ứng yêu cầu thực phẩm Hồi giáo, mở rộng thị trường sang UAE, Malaysia và các nước Đông Nam Á.",
		),
		note: T("Active · UAE, Malaysia, SE Asia", "Hiệu lực · UAE, Malaysia, Đông Nam Á"),
		issuer: "—",
		scope: T("UAE, Malaysia, SE Asia", "UAE, Malaysia, Đông Nam Á"),
	},
	{
		slug: "organic",
		title: "ORGANIC",
		group: "specialist",
		icon: "🌿",
		tone: "green",
		badge: T("Organic Certified", "Hữu Cơ"),
		badge_tone: "green",
		description: T(
			"Organic certification — products farmed without synthetic chemicals, meeting premium-segment demand across EU and North American markets.",
			"Chứng nhận hữu cơ — sản phẩm nuôi trồng không dùng hóa chất tổng hợp, đáp ứng phân khúc cao cấp tại EU và Bắc Mỹ.",
		),
		note: T("Active · EU & North America", "Hiệu lực · EU & Bắc Mỹ"),
		issuer: "—",
		scope: T("EU, North America", "EU, Bắc Mỹ"),
	},
	{
		slug: "us-fda",
		title: "US FDA",
		group: "other",
		icon: "🏛️",
		tone: "blue",
		issuer: "Registrar Corp",
		scope: T("US Market", "Thị Trường Mỹ"),
	},
];

// `({ slug, ...data }, index)`: lấy slug riêng, phần còn lại là data. Thứ tự trải `...data` đứng SAU các giá trị
// mặc định để dòng dữ liệu ghi đè được. sort_order = (vị trí + 1) * 10 (cách quãng để chèn tay).
// Kết quả: 9 chứng nhận x 2 ngôn ngữ = 18 entry.
const certifications = certData.flatMap(({ slug, ...data }, index) =>
	bilingual(`cert-${slug}`, slug, {
		cert_status: ACTIVE,
		show_in_table: true,
		sort_order: (index + 1) * 10,
		...data,
	}),
);

// ---------------------------------------------------------------------------
// Plants
// ---------------------------------------------------------------------------

// DỮ LIỆU NHÀ MÁY. CAMAU / HQ_ADDRESS: giá trị song ngữ dùng lại cho nhiều nhà máy (cùng tỉnh Cà Mau; trụ sở).
const CAMAU = T("Camau Province", "Tỉnh Cà Mau");
const HQ_ADDRESS = T(
	"04 Nguyen Cong Tru, Ly Van Lam Ward, Camau Province",
	"04 Nguyễn Công Trứ, Phường Lý Văn Lâm, Tỉnh Cà Mau",
);
// plantData: mỗi dòng là tuple [slug, mã DL, line (PLANT_LINES), tên, địa danh ngắn (footer), tỉnh, địa chỉ].
// Mã DL là mã cơ sở xuất khẩu EU, tương ứng các mã ở block cases_codes của trang chủ. Thêm nhà máy: thêm một
// dòng; line quyết định nhóm hiển thị của cases_plants.
const plantData = [
	["dl-113", "DL 113", "shrimp", T("Camau Seafood Processing Corp", "Nhà Máy Chế Biến Thủy Sản Cà Mau"), T("Camau", "Cà Mau"), CAMAU, HQ_ADDRESS],
	["dl-809", "DL 809", "shrimp", T("Cangca II Frozen Seafood Factory", "Nhà Máy Đông Lạnh Cảng Cá II"), T("Camau", "Cà Mau"), CAMAU, HQ_ADDRESS],
	["dl-295", "DL 295", "surimi", T("Songdoc Seafood Processing", "Chế Biến Thủy Sản Sông Đốc"), T("Song Doc", "Sông Đốc"), CAMAU, T("Song Doc Commune, Camau Province", "Xã Sông Đốc, Tỉnh Cà Mau")],
	["dl-51", "DL 51", "surimi", T("Tac Cau Seafood Processing", "Chế Biến Thủy Sản Tắc Cậu"), T("Tac Cau", "Tắc Cậu"), T("An Giang Province", "Tỉnh An Giang"), T("Tac Cau Industrial Zone, An Giang Province", "Khu Công Nghiệp Tắc Cậu, Tỉnh An Giang")],
	["dl-835", "DL 835", "surimi", T("Ganh Hao Seafood Processing", "Chế Biến Thủy Sản Gành Hào"), T("Ganh Hao", "Gành Hào"), CAMAU, T("Ganh Hao Commune, Camau Province", "Xã Gành Hào, Tỉnh Cà Mau")],
];

// Sinh entry: id `plant-<slug>`, sort_order = (vị trí + 1) * 10. 5 nhà máy x 2 ngôn ngữ = 10 entry.
const plants = plantData.flatMap(([slug, code, line, title, location, province, address], index) =>
	bilingual(`plant-${slug}`, slug, {
		title,
		code,
		line,
		location,
		province,
		address,
		sort_order: (index + 1) * 10,
	}),
);

// ---------------------------------------------------------------------------
// Menus
// ---------------------------------------------------------------------------

// MENU. navItems / footerItems: tuple [đường dẫn (bản en), nhãn EN, nhãn VI]. Đường dẫn có thể chứa
// anchor (#aquaculture) vì menu item "custom" là URL tự do. Menu primary ở header, footer ở chân trang.
const navItems = [
	["/", "Home", "Trang Chủ"],
	["/about", "About Us", "Về Chúng Tôi"],
	["/products", "Products", "Sản Phẩm"],
	["/infrastructure", "Infrastructure", "Cơ Sở Hạ Tầng"],
	["/certifications", "Certifications", "Chứng Nhận"],
	["/news", "News", "Tin Tức"],
];
const footerItems = [
	["/certifications#aquaculture", "Sustainability", "Phát Triển Bền Vững"],
	["/certifications#overview", "Export Policy", "Chính Sách Xuất Khẩu"],
];

// localUrl(path, locale) - URL menu theo locale: en giữ nguyên; vi thêm tiền tố "/vi" ("/" -> "/vi/" có
// dấu gạch cuối vì trang chủ vi là route vi/index). Khác với link trong block (được resolveHref() thêm
// /vi lúc render), URL của menu được ghi sẵn prefix ngay trong seed.
const localUrl = (path, locale) =>
	locale === "en" ? path : path === "/" ? `/${locale}/` : `/${locale}${path}`;

// bilingualMenu(name, labels, items) - sinh 2 menu (en, vi) với cùng `name` ("primary" | "footer").
//   name   : tên menu mà frontend gọi bằng getMenu(name, { locale }).
//   labels : { en, vi } tên menu hiển thị trong admin.
//   items  : danh sách tuple như navItems.
//   Menu có id `${name}-${locale}`; menu vi có translationOf `${name}-en`. Mỗi item có id
//   `${name}-${locale}-${index}` và item vi trỏ translationOf về item en CÙNG VỊ TRÍ, nên EN và VI
//   phải có cùng số item và cùng thứ tự. type "custom" = URL tự do (khác item trỏ tới trang/bài viết).
function bilingualMenu(name, labels, items) {
	return LOCALES.map((locale) => ({
		id: `${name}-${locale}`,
		name,
		locale,
		...(locale === "en" ? {} : { translationOf: `${name}-en` }),
		label: labels[locale],
		items: items.map(([path, en, vi], index) => ({
			id: `${name}-${locale}-${index}`,
			...(locale === "en" ? {} : { translationOf: `${name}-en-${index}` }),
			type: "custom",
			label: locale === "en" ? en : vi,
			url: localUrl(path, locale),
		})),
	}));
}

// Hai menu x hai ngôn ngữ = 4 menu entry.
const menus = [
	...bilingualMenu("primary", { en: "Primary Navigation", vi: "Điều Hướng Chính" }, navItems),
	...bilingualMenu("footer", { en: "Footer Links", vi: "Liên Kết Chân Trang" }, footerItems),
];

// ---------------------------------------------------------------------------

// POST-PROCESS schema: validator của EmDash yêu cầu select CẤP CAO NHẤT (field của collection hoặc của block)
// giữ lựa chọn trong `validation.options`, còn sub-field của repeater thì dùng `options` trực tiếp. Để
// phần khai báo ở trên ngắn gọn, mọi nơi đều viết `options`, rồi bước dưới đây chuyển lại cho field cấp cao nhất.
// Top-level select fields keep their choices in validation.options (repeater
// sub-fields declare `options` directly).
// moveOptions(field) - nếu field là select / multiSelect và có `options` thì tách `options` ra khỏi field và đặt
// vào `validation.options` (gộp với validation sẵn có); field khác trả nguyên. Không đệ quy vào
// `validation.subFields`, đúng ý: sub-field của repeater phải giữ `options`.
const moveOptions = (field) =>
	field.options && (field.type === "select" || field.type === "multiSelect")
		? (({ options, ...rest }) => ({ ...rest, validation: { ...rest.validation, options } }))(field)
		: field;
// Áp dụng cho fields của từng version của từng block type (gán lại mảng, thay thế tại chỗ)...
for (const type of blockTypes) {
	for (const version of type.versions) version.fields = version.fields.map(moveOptions);
}
// ...và cho fields của từng collection. Phải chạy trước khi dựng object `seed` bên dưới.
for (const collection of collections) collection.fields = collection.fields.map(moveOptions);

// Object seed cuối cùng. `$schema` + `version` để công cụ validate; `meta` mô tả bộ seed. Khi áp dụng, EmDash
// xử lý schema (blockTypes, collections) trước rồi tới menu và `content` (khóa theo slug collection).
const seed = {
	$schema: "https://emdashcms.com/seed.schema.json",
	version: "1",
	meta: {
		name: "CASES",
		description:
			"Corporate site for CaMau Seafood Processing and Service JSC, in English and Vietnamese",
		author: "CASES",
	},
	// Cấu hình site áp dụng lúc setup: tên site, tagline, và `seo.titleSeparator` (ký tự nối giữa tiêu đề trang
	// và tên site trong <title>). Sau setup sửa tại admin, Settings.
	settings: {
		title: "CASES",
		tagline: "Ca Mau Seafood",
		seo: { titleSeparator: "—" },
	},
	blockTypes,
	collections,
	menus,
	// Nội dung mẫu khóa theo slug collection. Mỗi mảng đã là danh sách phẳng entry của cả hai ngôn ngữ.
	// Collection `posts` chưa có bài mẫu nên không xuất hiện ở đây.
	content: {
		pages,
		products,
		certifications,
		plants,
	},
};

// Đường dẫn tuyệt đối tới seed/seed.json, tính từ vị trí file này.
const target = fileURLToPath(new URL("../seed/seed.json", import.meta.url));
// Ghi đè file với JSON thụt lề bằng tab và một ký tự xuống dòng cuối file.
writeFileSync(target, `${JSON.stringify(seed, null, "\t")}\n`);
// Báo cáo số ENTRY (mỗi ngôn ngữ tính một), không phải số mục gốc: vd 68 sản phẩm = 34 mục x 2 ngôn ngữ.
console.log(
	`Wrote ${target}: ${pages.length} pages, ${products.length} products, ${certifications.length} certifications, ${plants.length} plants`,
);
