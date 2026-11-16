/*
 * site.config.ts — "nguồn sự thật" (single source of truth) cho các THÔNG TIN LIÊN HỆ CỐ ĐỊNH của công ty.
 *
 * Tại sao file này tồn tại:
 *   - Số điện thoại, email, website, logo giống hệt nhau ở cả hai ngôn ngữ (EN và VI), nên
 *     không cần dịch và cũng không cần đưa vào CMS (EmDash). Chuỗi cần dịch (địa chỉ, mô tả,
 *     nhãn nút...) nằm ở src/i18n/ui.ts; nội dung biên tập được nằm trong EmDash.
 *   - Gom về một chỗ để đổi số điện thoại/email chỉ phải sửa ĐÚNG MỘT nơi.
 *
 * Ai import file này (data flow: site.config.ts -> component -> HTML):
 *   - src/layouts/Base.astro: cột "Contact Info" ở footer, các thẻ liên hệ trong panel
 *     trượt (slide-over), thuộc tính data-email của form liên hệ (script dùng nó để dựng
 *     liên kết mailto:), logo ở header/footer và favicon.
 *   - src/components/blocks/Hero.astro: dùng company.logo làm hình watermark của hero.
 *
 * Lưu ý:
 *   - Đây là code, không phải nội dung CMS: muốn đổi phải sửa file rồi build/deploy lại.
 *   - Không có lớp "locale" ở đây; đừng thêm chuỗi cần dịch vào object này.
 */
/**
 * Company facts shown in the header, footer and contact panel. These are the
 * same in every language, so they live here rather than in EmDash content.
 */
// Đối tượng duy nhất được export. `as const` (ở dưới cùng) làm cho mọi giá trị trở thành
// kiểu literal chỉ-đọc (readonly): TypeScript biết chính xác "tel:+842903669777" chứ không
// chỉ là `string`, và không ai vô tình gán lại giá trị lúc chạy.
export const company = {
	// Tên viết tắt thương hiệu. Hiện chưa có nơi nào trong src/ dùng trường này (tên hiển thị
	// ở header lấy từ cài đặt site của EmDash, xem src/utils/site.ts); giữ lại để dùng sau.
	shortName: "CASES",
	// Số điện thoại hiển thị cho người đọc (có dấu cách để dễ đọc). Dùng làm nội dung chữ
	// của liên kết điện thoại ở footer và panel liên hệ.
	phone: "+84 290 3669 777",
	// Giá trị href tương ứng: lược bỏ khoảng trắng, theo chuẩn RFC 3966 (tel:+<mã quốc gia><số>)
	// để bấm trên điện thoại sẽ gọi thẳng. PHẢI giữ đồng bộ thủ công với `phone` ở trên
	// (hai trường chứa cùng một số nhưng định dạng khác nhau, không có gì tự động kiểm tra).
	phoneHref: "tel:+842903669777",
	// Email bộ phận kinh doanh. Dùng ở 3 nơi: liên kết mailto: ở footer, thẻ Email trong panel
	// liên hệ và data-email của form (form không có endpoint server nên script mở ứng dụng
	// email của khách, gửi tới địa chỉ này — xem phần "Known gaps" trong AGENTS.md).
	email: "salesmanager@cases.com.vn",
	// URL đầy đủ của website chính thức; dùng làm href (mở tab mới) ở footer.
	website: "https://www.cases.com.vn",
	// Nhãn hiển thị cho liên kết website (bỏ "https://") để chữ ngắn gọn, đẹp hơn trong footer.
	websiteLabel: "www.cases.com.vn",
	// Đường dẫn logo SVG trong thư mục public/. Đây là logo MẶC ĐỊNH: Base.astro ưu tiên logo
	// do admin tải lên trong Site Settings của EmDash, chỉ rơi về giá trị này khi chưa có.
	// Favicon (thẻ link rel="icon") luôn dùng giá trị này, không phụ thuộc logo trong CMS.
	logo: "/cases-logo.svg",
} as const;
