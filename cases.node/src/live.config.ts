/**
 * EmDash Live Content Collections
 *
 * Defines the _emdash collection that handles all content types from the database.
 * Query specific types using getEmDashCollection() and getEmDashEntry().
 *
 * Giải thích thêm (tiếng Việt):
 *   - Đây là file cấu hình đặc biệt của Astro (src/live.config.ts) cho "live
 *     content collections": khác collection tĩnh (đọc lúc build), loại live đọc
 *     dữ liệu TẠI THỜI ĐIỂM REQUEST. Vì site chạy `output: "server"` trên
 *     Cloudflare Workers với D1 làm database, editor publish xong là khách thấy
 *     ngay mà không cần build/deploy lại.
 *   - Chỉ có MỘT collection Astro tên `_emdash`. Mọi loại nội dung (pages, posts,
 *     products, certifications, plants...) đều là các bảng trong D1 và được
 *     `emdashLoader()` truy vấn theo tên loại. Ta không đọc `_emdash` trực tiếp
 *     mà qua getEmDashCollection()/getEmDashEntry()/getMenu() của "emdash", vốn
 *     bọc collection này và thêm locale, fallback, cache hint, trạng thái publish.
 *   - Nguồn dữ liệu (D1 binding "DB") cấu hình ở astro.config.mjs (d1(...)) và
 *     wrangler.jsonc, không phải ở đây.
 */

import { defineLiveCollection } from "astro:content";
import { emdashLoader } from "emdash/runtime";

// Tên export `collections` là bắt buộc để Astro nhận ra file này; khoá `_emdash`
// là tên mà các hàm getEmDash* của EmDash tìm kiếm, vì vậy không đổi tên.
export const collections = {
	_emdash: defineLiveCollection({ loader: emdashLoader() }),
};
