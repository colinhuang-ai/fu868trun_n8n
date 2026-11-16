<?php
/**
 * Seed content for the News section: 4 categories + 10 bilingual articles.
 * General educational content about shrimp (no unsourced company statistics).
 */

$categories = array(
	'product-guide'          => array( 'Product Guide', 'Hướng Dẫn Sản Phẩm' ),
	'quality-safety'         => array( 'Quality & Safety', 'Chất Lượng & An Toàn' ),
	'origin-sustainability'  => array( 'Origin & Sustainability', 'Nguồn Gốc & Bền Vững' ),
	'cooking-health'         => array( 'Cooking & Health', 'Ẩm Thực & Sức Khỏe' ),
);

$posts = array();

/* ------------------------------------------------------------------ 1 */
$posts[] = array(
	'slug'       => 'black-tiger-vs-vannamei-shrimp',
	'cat'        => 'product-guide',
	'cover'      => 'shrimp',
	'date'       => '2026-11-12 09:00:00',
	'title'      => 'Black Tiger vs. Vannamei: Two Shrimp, Two Personalities',
	'title_vi'   => 'Tôm sú và tôm thẻ chân trắng: hai loài tôm, hai cá tính',
	'excerpt'    => 'Both fill the freezer aisle, but they are not interchangeable. Here is how to pick the right species for your menu, your market and your margin.',
	'excerpt_vi' => 'Cả hai đều có mặt trong mọi tủ đông, nhưng không thể thay thế cho nhau. Đây là cách chọn đúng loài tôm cho thực đơn, thị trường và biên lợi nhuận của bạn.',
	'content'    => <<<'HTML'
<p>Walk into any seafood importer's office and you will hear the same two names again and again: <strong>black tiger</strong> and <strong>vannamei</strong>. They are the two most important farmed shrimp in the world, yet they behave very differently on the plate and in the pond.</p>

<h2>Black tiger (<em>Penaeus monodon</em>)</h2>
<p>Native to the Indo-Pacific, the black tiger prawn is the classic "premium" shrimp. It grows large, has a firm, snappy bite and a naturally sweet, almost briny flavour. Its shell carries the dark, tiger-like stripes that give it its name. Because it is usually farmed at lower stocking densities, supply is smaller and prices are higher.</p>

<h2>Vannamei (<em>Litopenaeus vannamei</em>)</h2>
<p>Also called whiteleg shrimp, vannamei originates from the Pacific coast of Latin America and was introduced to Asia in the early 2000s. It grows faster, tolerates higher densities and is today the most widely farmed shrimp species on the planet. The flesh is tender and mild, and sizes are very consistent from batch to batch.</p>

<h2>How to choose</h2>
<ul>
<li><strong>Choose black tiger</strong> for premium presentation: head-on, shell-on, grilled or served whole, and for demanding markets such as Japan.</li>
<li><strong>Choose vannamei</strong> for versatility and value: peeled and deveined, cooked, skewered, and above all value-added products such as breaded and tempura shrimp.</li>
</ul>
<blockquote>Neither is "better". The right shrimp is the one that fits the dish, the customer and the price point.</blockquote>
<p>CASES processes both species, in raw, cooked and value-added forms. Browse the full range on our <a href="/products/">Products page</a>.</p>
HTML,
	'content_vi' => <<<'HTML'
<p>Bước vào văn phòng của bất kỳ nhà nhập khẩu thủy sản nào, bạn sẽ nghe đi nghe lại hai cái tên: <strong>tôm sú</strong> và <strong>tôm thẻ chân trắng</strong>. Đây là hai loài tôm nuôi quan trọng nhất thế giới, nhưng chúng rất khác nhau trên đĩa ăn lẫn ngoài ao nuôi.</p>

<h2>Tôm sú (<em>Penaeus monodon</em>)</h2>
<p>Có nguồn gốc từ vùng Ấn Độ Dương – Thái Bình Dương, tôm sú là dòng tôm "cao cấp" kinh điển. Con tôm lớn, thịt chắc, giòn và vị ngọt đậm đà tự nhiên. Vỏ có những vằn sẫm như da hổ làm nên tên gọi. Vì thường được nuôi với mật độ thấp, sản lượng ít hơn và giá cao hơn.</p>

<h2>Tôm thẻ chân trắng (<em>Litopenaeus vannamei</em>)</h2>
<p>Có nguồn gốc từ bờ Thái Bình Dương của châu Mỹ Latinh và được đưa vào châu Á từ đầu những năm 2000. Tôm thẻ lớn nhanh, chịu được mật độ nuôi cao và hiện là loài tôm được nuôi phổ biến nhất hành tinh. Thịt mềm, vị nhẹ, kích cỡ rất đồng đều giữa các lô hàng.</p>

<h2>Chọn loài nào?</h2>
<ul>
<li><strong>Chọn tôm sú</strong> khi cần trình bày cao cấp: còn đầu, còn vỏ, nướng hoặc dọn nguyên con, và cho các thị trường khắt khe như Nhật Bản.</li>
<li><strong>Chọn tôm thẻ</strong> khi cần sự linh hoạt và giá trị: bóc vỏ bỏ chỉ, chín, xiên que, và đặc biệt là sản phẩm giá trị gia tăng như tôm tẩm bột, tempura.</li>
</ul>
<blockquote>Không loài nào "tốt hơn". Loại tôm đúng là loại phù hợp với món ăn, khách hàng và mức giá.</blockquote>
<p>CASES chế biến cả hai loài, ở dạng sống, chín và giá trị gia tăng. Xem toàn bộ danh mục tại trang <a href="/products/">Sản phẩm</a>.</p>
HTML,
);

/* ------------------------------------------------------------------ 2 */
$posts[] = array(
	'slug'       => 'shrimp-size-counts-explained',
	'cat'        => 'product-guide',
	'cover'      => 'ruler',
	'date'       => '2026-11-03 09:00:00',
	'title'      => 'What Does "16/20" Mean? A Buyer\'s Guide to Shrimp Size Counts',
	'title_vi'   => '"16/20" nghĩa là gì? Hướng dẫn đọc cỡ tôm cho người mua',
	'excerpt'    => 'Size counts look like code, but the logic is simple. Learn to read them in a minute and avoid the most common ordering mistake.',
	'excerpt_vi' => 'Cỡ tôm trông như một mật mã, nhưng quy tắc rất đơn giản. Học cách đọc trong một phút và tránh sai lầm đặt hàng phổ biến nhất.',
	'content'    => <<<'HTML'
<p>On a shrimp specification sheet you will see numbers such as <strong>16/20</strong>, <strong>31/40</strong> or <strong>71/90</strong>. They are not product codes. They are <em>counts</em>.</p>

<h2>The rule</h2>
<p>A size count tells you how many shrimp it takes to make <strong>one pound (about 454 g)</strong>. So <strong>16/20</strong> means 16 to 20 shrimp per pound. The key thing to remember: <em>the smaller the number, the bigger the shrimp</em>. Some markets quote counts per kilogram instead, so always confirm the unit.</p>

<h2>A quick reference</h2>
<ul>
<li><strong>U/15 and 16/20</strong> – large, impressive shrimp for grilling, skewers and plated mains.</li>
<li><strong>21/25 and 26/30</strong> – the all-round favourites for restaurants and retail.</li>
<li><strong>31/40 and 41/50</strong> – medium shrimp for stir-fries, pasta and fried rice.</li>
<li><strong>51/60 to 71/90</strong> – small shrimp for salads, sandwiches and ready meals.</li>
</ul>

<h2>The mistake to avoid</h2>
<p>Counts apply to the product <em>in the form it is sold</em>. A shell-on 16/20 and a peeled-and-deveined 16/20 are very different pieces of shrimp: once the head and shell are removed, you need a much larger animal to reach the same count. Always specify the count together with the form, for example <em>"Vannamei PD, 31/40"</em> or <em>"Black Tiger HLSO, 16/20"</em>.</p>
<blockquote>Tip: choose size by the dish, not by habit. A bigger count is not always better value; a 41/50 is perfect where shrimp are mixed into a sauce.</blockquote>
<p>Need help matching sizes to your application? <a href="#contact" data-open-contact>Talk to our sales team</a>.</p>
HTML,
	'content_vi' => <<<'HTML'
<p>Trên bảng thông số kỹ thuật của tôm, bạn sẽ thấy những con số như <strong>16/20</strong>, <strong>31/40</strong> hay <strong>71/90</strong>. Đó không phải mã sản phẩm, mà là <em>cỡ tôm</em>.</p>

<h2>Quy tắc</h2>
<p>Cỡ tôm cho biết cần bao nhiêu con để đủ <strong>một pound (khoảng 454 g)</strong>. Vì vậy <strong>16/20</strong> nghĩa là 16 đến 20 con mỗi pound. Điều cần nhớ: <em>số càng nhỏ thì con tôm càng to</em>. Một số thị trường tính theo kilogram, vì vậy hãy luôn xác nhận đơn vị.</p>

<h2>Bảng tham khảo nhanh</h2>
<ul>
<li><strong>U/15 và 16/20</strong> – tôm lớn, ấn tượng, hợp để nướng, xiên que và món chính.</li>
<li><strong>21/25 và 26/30</strong> – cỡ "đa dụng" được ưa chuộng ở nhà hàng và bán lẻ.</li>
<li><strong>31/40 và 41/50</strong> – cỡ trung cho món xào, mì Ý và cơm chiên.</li>
<li><strong>51/60 đến 71/90</strong> – cỡ nhỏ cho salad, bánh mì kẹp và bữa ăn chế biến sẵn.</li>
</ul>

<h2>Sai lầm cần tránh</h2>
<p>Cỡ tôm áp dụng cho sản phẩm <em>ở dạng được bán</em>. Tôm còn vỏ cỡ 16/20 và tôm bóc vỏ bỏ chỉ cỡ 16/20 là hai thứ rất khác nhau: sau khi bỏ đầu và vỏ, bạn cần con tôm lớn hơn nhiều để đạt cùng cỡ. Luôn ghi cỡ kèm dạng sản phẩm, ví dụ <em>"Tôm thẻ PD, 31/40"</em> hoặc <em>"Tôm sú HLSO, 16/20"</em>.</p>
<blockquote>Mẹo: chọn cỡ theo món ăn, đừng theo thói quen. Cỡ lớn không phải lúc nào cũng có lợi hơn; cỡ 41/50 là lựa chọn hoàn hảo khi tôm trộn vào nước sốt.</blockquote>
<p>Cần tư vấn chọn cỡ phù hợp? <a href="#contact" data-open-contact>Liên hệ đội ngũ kinh doanh</a>.</p>
HTML,
);

/* ------------------------------------------------------------------ 3 */
$posts[] = array(
	'slug'       => 'iqf-vs-block-frozen-shrimp',
	'cat'        => 'product-guide',
	'cover'      => 'freezer',
	'date'       => '2026-10-26 09:00:00',
	'title'      => 'IQF or Block Frozen? Choosing the Right Format for Your Business',
	'title_vi'   => 'Đông IQF hay đông block? Chọn định dạng phù hợp cho doanh nghiệp của bạn',
	'excerpt'    => 'Two ways to freeze the same shrimp, two very different experiences in the kitchen and the warehouse. Here is how they compare.',
	'excerpt_vi' => 'Hai cách cấp đông cùng một con tôm, hai trải nghiệm rất khác nhau trong bếp và ngoài kho. Đây là cách chúng so sánh với nhau.',
	'content'    => <<<'HTML'
<p>Frozen shrimp is sold in two main formats. They start with the same raw material, but they are designed for different customers.</p>

<h2>IQF: Individually Quick Frozen</h2>
<p>In an IQF line, shrimp travel on a belt through a very cold tunnel and freeze <em>one by one</em>. Because freezing is so fast, only small ice crystals form inside the flesh, which protects the cell structure and keeps the texture firm after thawing. A thin protective glaze is then applied.</p>
<ul>
<li>Pour-and-use: take exactly the amount you need and return the rest to the freezer.</li>
<li>Even, predictable results and attractive presentation.</li>
<li>Ideal for retail packs, restaurants and foodservice.</li>
</ul>

<h2>Block frozen</h2>
<p>In a block, shrimp are laid in trays with water and frozen into one solid slab, usually in a blast freezer. The block is compact, stacks efficiently and is generally more economical to produce, store and ship.</p>
<ul>
<li>Lower cost per kilo and space-efficient logistics.</li>
<li>Best when the whole block will be thawed and used together.</li>
<li>Popular with processors who will re-work the shrimp into other products.</li>
</ul>

<h2>Which one?</h2>
<p>If your customers value convenience and appearance, go IQF. If you process in large batches and cost per kilo is your priority, block is hard to beat. Many of our products, including Vannamei PD and HLSO, are available in <strong>both IQF and block</strong> formats.</p>
<blockquote>Ask for a sample in both formats. The best decision is the one you can taste and measure.</blockquote>
HTML,
	'content_vi' => <<<'HTML'
<p>Tôm đông lạnh được bán chủ yếu ở hai định dạng. Cả hai xuất phát từ cùng một nguyên liệu, nhưng được thiết kế cho những nhóm khách hàng khác nhau.</p>

<h2>IQF: đông lạnh rời từng con</h2>
<p>Trên dây chuyền IQF, tôm di chuyển trên băng chuyền qua đường hầm cực lạnh và đông <em>từng con một</em>. Vì cấp đông rất nhanh nên chỉ hình thành các tinh thể đá nhỏ bên trong thịt, bảo vệ cấu trúc tế bào và giữ độ chắc sau khi rã đông. Sau đó tôm được phủ một lớp mạ băng mỏng bảo vệ.</p>
<ul>
<li>Lấy bao nhiêu dùng bấy nhiêu, phần còn lại cho vào tủ đông.</li>
<li>Kết quả đồng đều, dễ dự đoán và trình bày đẹp mắt.</li>
<li>Lý tưởng cho bao bì bán lẻ, nhà hàng và dịch vụ ăn uống.</li>
</ul>

<h2>Đông block</h2>
<p>Với block, tôm được xếp vào khay cùng nước và đông thành một khối đặc, thường trong tủ đông gió. Khối block gọn, xếp chồng hiệu quả và thường tiết kiệm hơn khi sản xuất, lưu kho và vận chuyển.</p>
<ul>
<li>Chi phí mỗi kg thấp hơn và logistics tiết kiệm không gian.</li>
<li>Phù hợp nhất khi dùng hết cả khối sau khi rã đông.</li>
<li>Được các nhà chế biến ưa chuộng để tiếp tục chế biến thành sản phẩm khác.</li>
</ul>

<h2>Chọn loại nào?</h2>
<p>Nếu khách hàng của bạn coi trọng sự tiện lợi và vẻ ngoài, hãy chọn IQF. Nếu bạn chế biến theo lô lớn và ưu tiên chi phí mỗi kg, block rất khó bị đánh bại. Nhiều sản phẩm của chúng tôi, gồm tôm thẻ PD và HLSO, có sẵn ở <strong>cả hai định dạng IQF và block</strong>.</p>
<blockquote>Hãy yêu cầu mẫu ở cả hai định dạng. Quyết định tốt nhất là quyết định bạn có thể nếm thử và đo lường.</blockquote>
HTML,
);

/* ------------------------------------------------------------------ 4 */
$posts[] = array(
	'slug'       => 'cold-chain-frozen-shrimp',
	'cat'        => 'quality-safety',
	'cover'      => 'ship',
	'date'       => '2026-10-19 09:00:00',
	'title'      => 'The Cold Chain: How Frozen Shrimp Stays Perfect From Plant to Plate',
	'title_vi'   => 'Chuỗi lạnh: làm sao tôm đông lạnh giữ trọn chất lượng từ nhà máy đến bàn ăn',
	'excerpt'    => 'Great shrimp can be ruined in a single warm hour. Follow the journey of a frozen shrimp and the checkpoints that protect its quality.',
	'excerpt_vi' => 'Con tôm ngon có thể hỏng chỉ sau một giờ gặp nhiệt độ cao. Cùng theo dõi hành trình của một con tôm đông lạnh và những điểm kiểm soát bảo vệ chất lượng của nó.',
	'content'    => <<<'HTML'
<p>Freezing locks in quality, but only for as long as the product stays cold. The <strong>cold chain</strong> is the unbroken series of refrigerated steps that carries shrimp from the processing plant to the customer's kitchen. A single break can undo weeks of careful work.</p>

<h2>The journey</h2>
<ol>
<li><strong>Freezing.</strong> Shrimp are frozen quickly, in IQF tunnels at around −40 °C or in blast freezers, so the core reaches storage temperature fast.</li>
<li><strong>Cold storage.</strong> Product is held at a stable −18 °C or colder. At CASES we maintain −18 °C to −22 °C until loading.</li>
<li><strong>Loading.</strong> Containers are pre-cooled, and loading is done quickly so product never warms on the dock.</li>
<li><strong>Sea freight.</strong> Refrigerated containers hold temperature for weeks, often monitored by data loggers.</li>
<li><strong>Destination.</strong> The importer's cold store, distributor, and finally the restaurant or retail freezer.</li>
</ol>

<h2>Why steady temperature matters</h2>
<p>When temperatures swing up and down, small ice crystals melt and re-freeze into larger ones. These damage the flesh, drawing out moisture and leaving shrimp dry, spongy or affected by freezer burn.</p>

<h2>Signs the chain was broken</h2>
<ul>
<li>Large ice crystals or heavy frost inside the pack.</li>
<li>Shrimp stuck together in a deformed block (for IQF products).</li>
<li>Dry, whitish patches on the surface.</li>
<li>An off smell after thawing.</li>
</ul>
<blockquote>Golden rule: once thawed, never refreeze. Thaw only what you plan to use.</blockquote>
HTML,
	'content_vi' => <<<'HTML'
<p>Cấp đông giữ lại chất lượng, nhưng chỉ khi sản phẩm luôn được giữ lạnh. <strong>Chuỗi lạnh</strong> là chuỗi các công đoạn bảo quản lạnh không bị gián đoạn, đưa tôm từ nhà máy chế biến đến bếp của khách hàng. Chỉ một lần đứt gãy cũng có thể phá hỏng công sức nhiều tuần.</p>

<h2>Hành trình của con tôm</h2>
<ol>
<li><strong>Cấp đông.</strong> Tôm được cấp đông nhanh, trong đường hầm IQF khoảng −40 °C hoặc tủ đông gió, để tâm sản phẩm nhanh chóng đạt nhiệt độ bảo quản.</li>
<li><strong>Kho lạnh.</strong> Sản phẩm được giữ ổn định ở −18 °C hoặc thấp hơn. Tại CASES, nhiệt độ được duy trì từ −18 °C đến −22 °C cho đến khi xếp hàng.</li>
<li><strong>Xếp container.</strong> Container được làm lạnh trước và xếp hàng nhanh để sản phẩm không bị nóng lên ngoài bến.</li>
<li><strong>Vận chuyển đường biển.</strong> Container lạnh giữ nhiệt độ trong nhiều tuần, thường được giám sát bằng thiết bị ghi dữ liệu.</li>
<li><strong>Điểm đến.</strong> Kho lạnh của nhà nhập khẩu, nhà phân phối, và cuối cùng là tủ đông của nhà hàng hoặc cửa hàng bán lẻ.</li>
</ol>

<h2>Vì sao nhiệt độ ổn định quan trọng</h2>
<p>Khi nhiệt độ lên xuống thất thường, các tinh thể đá nhỏ tan ra rồi đóng lại thành tinh thể lớn hơn. Chúng làm tổn thương thịt tôm, kéo nước ra ngoài khiến tôm khô, xốp hoặc bị cháy lạnh.</p>

<h2>Dấu hiệu chuỗi lạnh đã bị đứt</h2>
<ul>
<li>Tinh thể đá lớn hoặc lớp tuyết dày bên trong bao bì.</li>
<li>Tôm dính thành khối méo mó (với sản phẩm IQF).</li>
<li>Các mảng khô, trắng đục trên bề mặt.</li>
<li>Mùi lạ sau khi rã đông.</li>
</ul>
<blockquote>Nguyên tắc vàng: đã rã đông thì không cấp đông lại. Chỉ rã đông lượng bạn định dùng.</blockquote>
HTML,
);

/* ------------------------------------------------------------------ 5 */
$posts[] = array(
	'slug'       => 'what-is-ice-glazing-on-frozen-shrimp',
	'cat'        => 'quality-safety',
	'cover'      => 'ice',
	'date'       => '2026-10-10 09:00:00',
	'title'      => 'What Is Glazing? The Thin Layer of Ice That Protects Your Shrimp',
	'title_vi'   => 'Mạ băng là gì? Lớp băng mỏng bảo vệ con tôm của bạn',
	'excerpt'    => 'That glassy coat on frozen shrimp is not a trick. It is protection. But it also affects what you pay for, so it pays to understand it.',
	'excerpt_vi' => 'Lớp băng trong suốt bên ngoài tôm đông lạnh không phải mánh khóe, mà là lớp bảo vệ. Nhưng nó cũng ảnh hưởng đến số tiền bạn trả, vì vậy cần hiểu rõ.',
	'content'    => <<<'HTML'
<p>Open a bag of IQF shrimp and you will notice a clear, glassy coating on each piece. This is the <strong>glaze</strong>: a very thin layer of ice, applied by dipping or spraying the shrimp with chilled water right after freezing.</p>

<h2>What glazing does</h2>
<ul>
<li><strong>Prevents dehydration.</strong> The ice shell seals the surface so moisture cannot escape during storage.</li>
<li><strong>Protects against oxidation</strong> and freezer burn by limiting contact with air.</li>
<li><strong>Preserves appearance,</strong> keeping the shrimp bright and the colour natural.</li>
</ul>

<h2>How glaze is expressed</h2>
<p>Glaze is given as a percentage, for example <strong>10%</strong> or <strong>10–15%</strong>. It tells you how much of the frozen weight is ice rather than shrimp. Because glaze melts away when you thaw, what really matters to a buyer is the <strong>net weight</strong>: the weight of the shrimp itself, with the glaze removed.</p>

<h2>What to look for</h2>
<ul>
<li>Check that the label states both <em>gross</em> and <em>net</em> weight, and the glaze percentage.</li>
<li>Compare prices on a <em>net-weight</em> basis, not per frozen kilo.</li>
<li>A moderate, consistent glaze is a sign of good control. Too thin and shrimp suffer; too thick and you pay for ice.</li>
</ul>
<blockquote>At CASES, glaze is controlled for each specification, typically in the 10–15% range, so customers receive exactly the net weight they ordered.</blockquote>
<p>Want exact glaze and net-weight values for a specific product? Open any item in our <a href="/products/">catalog</a> or <a href="#contact" data-open-contact>contact us</a>.</p>
HTML,
	'content_vi' => <<<'HTML'
<p>Mở một túi tôm IQF, bạn sẽ thấy mỗi con được phủ một lớp trong suốt như thủy tinh. Đó là <strong>lớp mạ băng</strong>: một lớp đá rất mỏng, tạo ra bằng cách nhúng hoặc phun nước lạnh lên tôm ngay sau khi cấp đông.</p>

<h2>Mạ băng để làm gì</h2>
<ul>
<li><strong>Chống mất nước.</strong> Lớp băng bao kín bề mặt để hơi ẩm không thoát ra trong suốt thời gian bảo quản.</li>
<li><strong>Chống oxy hóa</strong> và cháy lạnh bằng cách hạn chế tiếp xúc với không khí.</li>
<li><strong>Giữ vẻ ngoài,</strong> để tôm luôn tươi sáng và màu sắc tự nhiên.</li>
</ul>

<h2>Cách thể hiện tỷ lệ mạ băng</h2>
<p>Mạ băng được ghi bằng phần trăm, ví dụ <strong>10%</strong> hoặc <strong>10–15%</strong>. Con số này cho biết bao nhiêu phần trọng lượng đông lạnh là băng chứ không phải tôm. Vì lớp băng tan đi khi rã đông, điều người mua thực sự quan tâm là <strong>trọng lượng tịnh</strong>: trọng lượng của chính con tôm sau khi bỏ lớp băng.</p>

<h2>Cần lưu ý gì</h2>
<ul>
<li>Kiểm tra nhãn có ghi cả trọng lượng <em>cả bì</em> và <em>tịnh</em>, cùng tỷ lệ mạ băng.</li>
<li>So sánh giá theo <em>trọng lượng tịnh</em>, không phải theo kg đông lạnh.</li>
<li>Lớp mạ băng vừa phải và ổn định là dấu hiệu kiểm soát tốt. Quá mỏng thì tôm bị ảnh hưởng; quá dày thì bạn đang trả tiền mua băng.</li>
</ul>
<blockquote>Tại CASES, tỷ lệ mạ băng được kiểm soát theo từng quy cách, thường trong khoảng 10–15%, để khách hàng nhận đúng trọng lượng tịnh đã đặt.</blockquote>
<p>Muốn biết chính xác tỷ lệ mạ băng và trọng lượng tịnh của một sản phẩm? Mở bất kỳ sản phẩm nào trong <a href="/products/">danh mục</a> hoặc <a href="#contact" data-open-contact>liên hệ chúng tôi</a>.</p>
HTML,
);

/* ------------------------------------------------------------------ 6 */
$posts[] = array(
	'slug'       => 'shrimp-certifications-haccp-bap-asc',
	'cat'        => 'quality-safety',
	'cover'      => 'badge',
	'date'       => '2026-10-01 09:00:00',
	'title'      => 'HACCP, BAP, ASC: How to Read the Certifications on a Pack of Shrimp',
	'title_vi'   => 'HACCP, BAP, ASC: cách đọc các chứng nhận trên bao bì tôm',
	'excerpt'    => 'A wall of acronyms is not a quality guarantee unless you know what each one covers. A plain-language guide for buyers.',
	'excerpt_vi' => 'Một loạt chữ viết tắt chưa phải là bảo chứng chất lượng nếu bạn không biết mỗi chứng nhận bao phủ điều gì. Hướng dẫn dễ hiểu dành cho người mua.',
	'content'    => <<<'HTML'
<p>Seafood labels are full of acronyms. They matter, because importers and retailers around the world use them to decide which suppliers they can trust. But each certificate answers a <em>different question</em>.</p>

<h2>Food safety: "Is it made safely?"</h2>
<ul>
<li><strong>HACCP</strong> – a preventive system that identifies hazards and controls the critical points where they could occur. It is the foundation of seafood food safety.</li>
<li><strong>ISO 22000</strong> – an international standard for food safety management systems, covering the whole organisation.</li>
<li><strong>IFS Food / BRC</strong> – retail standards, demanded by European supermarket chains, with detailed audits of the plant.</li>
</ul>

<h2>Responsible farming: "Was it farmed well?"</h2>
<ul>
<li><strong>BAP (Best Aquaculture Practices)</strong> – run by the Global Seafood Alliance. The star rating shows how much of the chain is certified: a two-star product has both the processing plant and the farm certified.</li>
<li><strong>ASC Chain of Custody</strong> – proves that product sold with the ASC label can be traced back to a responsibly certified farm.</li>
<li><strong>GlobalG.A.P.</strong> – good agricultural practice at the pond, from water quality to feed and worker welfare.</li>
</ul>

<h2>Special markets: "Does it fit my customer?"</h2>
<ul>
<li><strong>HALAL</strong> – meets Islamic dietary requirements, opening markets such as the UAE and Malaysia.</li>
<li><strong>Organic</strong> – farmed without synthetic chemicals, for premium segments.</li>
</ul>

<h2>How to verify</h2>
<p>Ask for the certificate itself. Check the <strong>issuing body</strong>, the <strong>scope</strong> (which plant or farm) and the <strong>expiry date</strong>. A logo on a pack is only as good as the valid certificate behind it.</p>
<p>See the certifications CASES currently holds on our <a href="/certifications/">Certifications page</a>.</p>
HTML,
	'content_vi' => <<<'HTML'
<p>Nhãn mác thủy sản đầy những chữ viết tắt. Chúng quan trọng, vì nhà nhập khẩu và nhà bán lẻ trên khắp thế giới dùng chúng để quyết định nhà cung cấp nào đáng tin. Nhưng mỗi chứng nhận trả lời một <em>câu hỏi khác nhau</em>.</p>

<h2>An toàn thực phẩm: "Có được sản xuất an toàn không?"</h2>
<ul>
<li><strong>HACCP</strong> – hệ thống phòng ngừa, nhận diện mối nguy và kiểm soát các điểm tới hạn nơi chúng có thể xảy ra. Đây là nền tảng của an toàn thực phẩm thủy sản.</li>
<li><strong>ISO 22000</strong> – tiêu chuẩn quốc tế về hệ thống quản lý an toàn thực phẩm, áp dụng cho toàn bộ tổ chức.</li>
<li><strong>IFS Food / BRC</strong> – tiêu chuẩn bán lẻ, được các chuỗi siêu thị châu Âu yêu cầu, với các cuộc đánh giá chi tiết tại nhà máy.</li>
</ul>

<h2>Nuôi trồng có trách nhiệm: "Có được nuôi tốt không?"</h2>
<ul>
<li><strong>BAP (Best Aquaculture Practices)</strong> – do Global Seafood Alliance vận hành. Số sao cho biết mức độ chuỗi được chứng nhận: sản phẩm hai sao có cả nhà máy chế biến và trang trại được chứng nhận.</li>
<li><strong>ASC Chain of Custody</strong> – chứng minh sản phẩm mang nhãn ASC có thể truy xuất về trang trại được chứng nhận có trách nhiệm.</li>
<li><strong>GlobalG.A.P.</strong> – thực hành nông nghiệp tốt tại ao nuôi, từ chất lượng nước, thức ăn đến phúc lợi người lao động.</li>
</ul>

<h2>Thị trường đặc thù: "Có phù hợp với khách hàng của tôi không?"</h2>
<ul>
<li><strong>HALAL</strong> – đáp ứng yêu cầu ăn uống của đạo Hồi, mở ra các thị trường như UAE và Malaysia.</li>
<li><strong>Hữu cơ</strong> – nuôi không dùng hóa chất tổng hợp, dành cho phân khúc cao cấp.</li>
</ul>

<h2>Cách xác minh</h2>
<p>Hãy yêu cầu xem chính chứng nhận. Kiểm tra <strong>tổ chức cấp</strong>, <strong>phạm vi</strong> (nhà máy hoặc trang trại nào) và <strong>ngày hết hạn</strong>. Logo trên bao bì chỉ có giá trị khi có chứng nhận hợp lệ phía sau.</p>
<p>Xem các chứng nhận CASES đang nắm giữ tại trang <a href="/certifications/">Chứng nhận</a>.</p>
HTML,
);

/* ------------------------------------------------------------------ 7 */
$posts[] = array(
	'slug'       => 'ca-mau-vietnams-shrimp-capital',
	'cat'        => 'origin-sustainability',
	'cover'      => 'mangrove',
	'date'       => '2026-09-22 09:00:00',
	'title'      => 'Cà Mau: Where Vietnam\'s Shrimp Story Begins',
	'title_vi'   => 'Cà Mau: nơi câu chuyện con tôm Việt Nam bắt đầu',
	'excerpt'    => 'At the southern tip of Vietnam, rivers meet the sea and mangroves meet the shrimp ponds. This is the landscape behind every CASES shrimp.',
	'excerpt_vi' => 'Ở cực Nam Tổ quốc, sông gặp biển và rừng ngập mặn gặp ao tôm. Đây là cảnh quan đứng sau mỗi con tôm CASES.',
	'content'    => <<<'HTML'
<p>Follow the Mekong Delta south until the land runs out and you reach <strong>Cà Mau</strong>, Vietnam's southernmost province. It is a world of rivers, tidal canals and mangrove forests, washed by the sea on two sides. It is also the heartland of Vietnamese shrimp farming, and the place where CASES was founded in 1996.</p>

<h2>A landscape made for shrimp</h2>
<p>Shrimp thrive in brackish water, where fresh water from the delta mixes with the salt of the sea. Cà Mau's tidal rhythm refreshes this water naturally every day, and its mangrove-lined canals provide shelter and food. Generations of local farmers learned to work with these conditions rather than against them.</p>

<h2>Mangroves and shrimp, side by side</h2>
<p>Some of the region's farms follow integrated models in which shrimp are raised beneath or alongside mangrove forests. Mangroves protect coastlines from erosion and storms, store carbon and shelter wildlife, and they can give farms a lower-input, more nature-friendly way to produce shrimp. Models like these are one reason the region is closely watched by buyers who care about sustainability.</p>

<h2>From pond to plant, close to home</h2>
<p>Being close to the source matters. Our plants in Cà Mau, Sông Đốc and Gành Hào sit near the farms and fishing ports, which shortens the time between harvest and processing, one of the most important factors in shrimp quality.</p>
<blockquote>A good shrimp is the sum of clean water, careful farming and speed. Cà Mau offers all three.</blockquote>
<p>Learn more about our story on the <a href="/about/">About Us page</a> or see our facilities on <a href="/infrastructure/">Infrastructure</a>.</p>
HTML,
	'content_vi' => <<<'HTML'
<p>Đi theo đồng bằng sông Cửu Long về phương Nam cho đến khi đất liền chấm dứt, bạn sẽ đến <strong>Cà Mau</strong>, tỉnh cực Nam của Việt Nam. Đó là thế giới của sông ngòi, kênh rạch chịu ảnh hưởng thủy triều và rừng ngập mặn, được biển ôm lấy từ hai phía. Đây cũng là vùng trọng điểm nuôi tôm của Việt Nam, và là nơi CASES được thành lập năm 1996.</p>

<h2>Cảnh quan sinh ra để nuôi tôm</h2>
<p>Tôm phát triển tốt trong nước lợ, nơi nước ngọt từ đồng bằng hòa với nước mặn của biển. Nhịp thủy triều ở Cà Mau làm mới nguồn nước này mỗi ngày, còn những con kênh rợp bóng rừng ngập mặn tạo nơi trú ẩn và nguồn thức ăn. Nhiều thế hệ nông dân địa phương đã học cách làm việc cùng những điều kiện này thay vì chống lại chúng.</p>

<h2>Rừng ngập mặn và con tôm, bên nhau</h2>
<p>Một số trang trại trong vùng áp dụng mô hình kết hợp, nuôi tôm dưới tán hoặc bên cạnh rừng ngập mặn. Rừng ngập mặn bảo vệ bờ biển khỏi xói lở và bão, lưu giữ carbon và che chở động vật hoang dã; đồng thời mang lại cho trang trại cách nuôi ít đầu vào và thân thiện với thiên nhiên hơn. Những mô hình như vậy là một lý do khiến vùng này được các nhà mua hàng quan tâm đến tính bền vững theo dõi sát sao.</p>

<h2>Từ ao nuôi đến nhà máy, gần nguồn</h2>
<p>Gần nguồn nguyên liệu rất quan trọng. Các nhà máy của chúng tôi tại Cà Mau, Sông Đốc và Gành Hào nằm gần trang trại và cảng cá, rút ngắn thời gian từ thu hoạch đến chế biến, một trong những yếu tố quan trọng nhất đối với chất lượng tôm.</p>
<blockquote>Một con tôm ngon là tổng hòa của nguồn nước sạch, nuôi trồng cẩn trọng và tốc độ. Cà Mau có đủ cả ba.</blockquote>
<p>Tìm hiểu thêm câu chuyện của chúng tôi tại trang <a href="/about/">Về chúng tôi</a> hoặc xem cơ sở vật chất tại <a href="/infrastructure/">Cơ sở hạ tầng</a>.</p>
HTML,
);

/* ------------------------------------------------------------------ 8 */
$posts[] = array(
	'slug'       => 'value-added-shrimp-tempura-butterfly-torpedo',
	'cat'        => 'product-guide',
	'cover'      => 'fried',
	'date'       => '2026-09-14 09:00:00',
	'title'      => 'Tempura, Butterfly, Torpedo: A Foodservice Guide to Value-Added Shrimp',
	'title_vi'   => 'Tempura, bướm, ngư lôi: cẩm nang tôm giá trị gia tăng cho dịch vụ ăn uống',
	'excerpt'    => 'Ready-to-cook shrimp saves kitchens time and delivers consistent results. Learn the main cuts and coatings and where each one shines.',
	'excerpt_vi' => 'Tôm chế biến sẵn giúp bếp tiết kiệm thời gian và cho kết quả đồng nhất. Tìm hiểu các kiểu cắt, lớp phủ chính và nơi mỗi loại phát huy thế mạnh.',
	'content'    => <<<'HTML'
<p>Value-added shrimp is shrimp that has already been shaped, coated and sometimes pre-cooked, so that the kitchen only needs to fry, bake or air-fry. It means less prep, less waste and, above all, <strong>consistent portions and quality</strong> at any volume.</p>

<h2>The shapes</h2>
<ul>
<li><strong>Butterfly</strong> – cut along the back and opened flat. It looks generous on the plate and cooks quickly and evenly.</li>
<li><strong>Torpedo</strong> – peeled with the tail left on and a coated body. The tail works as a handle, so it is perfect for dipping.</li>
<li><strong>Nobashi / Sushi Ebi</strong> – shrimp straightened so they stay flat, essential for sushi and tempura.</li>
</ul>

<h2>The coatings</h2>
<ul>
<li><strong>Tempura</strong> – a thin, light, crisp batter; a Japanese classic that lets the shrimp flavour shine.</li>
<li><strong>Breaded (breadcrumb)</strong> – a crunchy, golden crust, popular for appetisers and fast-food menus.</li>
<li><strong>Coconut</strong> – sweet, tropical and distinctive; a best seller for casual dining and the Caribbean-style segment.</li>
</ul>

<h2>What buyers should ask</h2>
<ul>
<li>Is the product <em>raw-breaded</em> or <em>pre-fried</em>? This changes cooking time and equipment.</li>
<li>What are the shrimp size and the coating ratio? A generous shrimp content is the difference between a premium and a basic product.</li>
<li>What are the recommended cooking instructions (deep fry, oven, air fryer)?</li>
</ul>
<p>CASES offers Butterfly, Coconut Torpedo, Tempura and Breaded lines in both Black Tiger and Vannamei. See the details on the <a href="/products/">Products page</a>.</p>
HTML,
	'content_vi' => <<<'HTML'
<p>Tôm giá trị gia tăng là tôm đã được tạo hình, tẩm bột và đôi khi chế biến chín sẵn, để nhà bếp chỉ cần chiên, nướng hoặc dùng nồi chiên không dầu. Điều đó nghĩa là ít công sơ chế hơn, ít hao hụt hơn và trên hết là <strong>khẩu phần và chất lượng đồng nhất</strong> ở mọi quy mô.</p>

<h2>Các kiểu cắt</h2>
<ul>
<li><strong>Butterfly (bướm)</strong> – cắt dọc lưng và mở phẳng. Trông đầy đặn trên đĩa, chín nhanh và đều.</li>
<li><strong>Torpedo (ngư lôi)</strong> – bóc vỏ chừa đuôi, thân phủ bột. Phần đuôi đóng vai trò cán cầm nên rất hợp để chấm sốt.</li>
<li><strong>Nobashi / Sushi Ebi</strong> – tôm được kéo thẳng để giữ phẳng, cần thiết cho sushi và tempura.</li>
</ul>

<h2>Các lớp phủ</h2>
<ul>
<li><strong>Tempura</strong> – lớp bột mỏng, nhẹ, giòn; món kinh điển của Nhật giúp vị tôm nổi bật.</li>
<li><strong>Tẩm bột chiên xù (breaded)</strong> – lớp vỏ giòn vàng, phổ biến cho món khai vị và thực đơn đồ ăn nhanh.</li>
<li><strong>Dừa</strong> – ngọt, nhiệt đới và khác biệt; món bán chạy cho nhà hàng bình dân và phân khúc phong cách Caribbean.</li>
</ul>

<h2>Người mua nên hỏi gì</h2>
<ul>
<li>Sản phẩm là <em>tẩm bột sống</em> hay <em>đã chiên sơ</em>? Điều này thay đổi thời gian nấu và thiết bị.</li>
<li>Cỡ tôm và tỷ lệ lớp phủ là bao nhiêu? Hàm lượng tôm đầy đặn tạo nên khác biệt giữa sản phẩm cao cấp và phổ thông.</li>
<li>Hướng dẫn chế biến khuyến nghị là gì (chiên ngập dầu, lò nướng, nồi chiên không dầu)?</li>
</ul>
<p>CASES cung cấp dòng Butterfly, Coconut Torpedo, Tempura và Breaded cho cả tôm sú và tôm thẻ. Xem chi tiết tại trang <a href="/products/">Sản phẩm</a>.</p>
HTML,
);

/* ------------------------------------------------------------------ 9 */
$posts[] = array(
	'slug'       => 'is-shrimp-healthy-nutrition-facts',
	'cat'        => 'cooking-health',
	'cover'      => 'heart',
	'date'       => '2026-09-05 09:00:00',
	'title'      => 'Is Shrimp Healthy? What\'s Really Inside a Shrimp',
	'title_vi'   => 'Tôm có tốt cho sức khỏe không? Bên trong một con tôm có gì',
	'excerpt'    => 'High in protein, low in calories and rich in trace minerals. A look at the nutrition of shrimp, and the one thing to watch for.',
	'excerpt_vi' => 'Giàu đạm, ít calo và giàu khoáng chất vi lượng. Cùng xem giá trị dinh dưỡng của tôm, và một điều cần lưu ý.',
	'content'    => <<<'HTML'
<p>Shrimp is one of the most popular seafoods in the world, and nutritionally it earns its place on the table. It is a lean source of protein that fits easily into a balanced diet.</p>

<h2>The highlights</h2>
<ul>
<li><strong>Lean protein.</strong> Roughly 20–24 g of protein per 100 g, with very little fat, for around 100 kcal or less. That makes it filling without being heavy.</li>
<li><strong>Selenium and iodine.</strong> Trace minerals that support the thyroid and the body's antioxidant defences.</li>
<li><strong>Vitamin B12.</strong> Important for nerves and red blood cells.</li>
<li><strong>Astaxanthin.</strong> The natural pigment that turns shrimp pink when cooked; it is an antioxidant.</li>
</ul>

<h2>What about cholesterol?</h2>
<p>Shrimp does contain dietary cholesterol, which once earned it a bad reputation. However, for most healthy people, saturated fat influences blood cholesterol more than dietary cholesterol does, and shrimp is very low in saturated fat. As part of a varied diet, it is generally considered a good choice. If you have a specific medical condition, follow your doctor's advice.</p>

<h2>The one thing to watch: allergy</h2>
<p>Shellfish, including shrimp, is one of the major food allergens. People with a crustacean allergy must avoid it completely, and food businesses should always declare it clearly on labels and menus.</p>

<h2>Keep it healthy</h2>
<p>The nutrition is largely determined by how you cook. Steam, grill, poach or stir-fry with a little oil rather than deep-frying, and let the shrimp's natural sweetness do the work.</p>
<blockquote>This article is general information, not medical advice.</blockquote>
HTML,
	'content_vi' => <<<'HTML'
<p>Tôm là một trong những loại hải sản phổ biến nhất thế giới, và về dinh dưỡng, nó xứng đáng có mặt trên bàn ăn. Đây là nguồn đạm nạc, dễ dàng nằm trong một chế độ ăn cân bằng.</p>

<h2>Những điểm nổi bật</h2>
<ul>
<li><strong>Đạm nạc.</strong> Khoảng 20–24 g protein trên 100 g, rất ít chất béo, với khoảng 100 kcal hoặc ít hơn. Nhờ vậy ăn no mà không nặng bụng.</li>
<li><strong>Selen và iốt.</strong> Các khoáng chất vi lượng hỗ trợ tuyến giáp và hệ chống oxy hóa của cơ thể.</li>
<li><strong>Vitamin B12.</strong> Quan trọng cho hệ thần kinh và hồng cầu.</li>
<li><strong>Astaxanthin.</strong> Sắc tố tự nhiên khiến tôm chuyển hồng khi nấu chín; đây là một chất chống oxy hóa.</li>
</ul>

<h2>Còn cholesterol thì sao?</h2>
<p>Tôm có chứa cholesterol từ thực phẩm, điều từng khiến nó mang tiếng xấu. Tuy nhiên, với hầu hết người khỏe mạnh, chất béo bão hòa ảnh hưởng đến cholesterol trong máu nhiều hơn cholesterol từ thức ăn, và tôm có rất ít chất béo bão hòa. Trong một chế độ ăn đa dạng, tôm thường được xem là lựa chọn tốt. Nếu bạn có bệnh lý cụ thể, hãy làm theo lời khuyên của bác sĩ.</p>

<h2>Một điều cần lưu ý: dị ứng</h2>
<p>Động vật có vỏ, bao gồm tôm, là một trong những tác nhân gây dị ứng thực phẩm chính. Người dị ứng giáp xác phải tránh hoàn toàn, và các cơ sở kinh doanh thực phẩm luôn phải ghi rõ trên nhãn và thực đơn.</p>

<h2>Giữ cho món ăn lành mạnh</h2>
<p>Giá trị dinh dưỡng phần lớn phụ thuộc vào cách chế biến. Hãy hấp, nướng, luộc hoặc xào với ít dầu thay vì chiên ngập dầu, và để vị ngọt tự nhiên của tôm tự tỏa sáng.</p>
<blockquote>Bài viết này chỉ cung cấp thông tin chung, không phải lời khuyên y tế.</blockquote>
HTML,
);

/* ------------------------------------------------------------------ 10 */
$posts[] = array(
	'slug'       => 'how-to-thaw-and-cook-frozen-shrimp',
	'cat'        => 'cooking-health',
	'cover'      => 'chef',
	'date'       => '2026-08-27 09:00:00',
	'title'      => 'How to Thaw and Cook Frozen Shrimp Like a Chef',
	'title_vi'   => 'Cách rã đông và nấu tôm đông lạnh như một đầu bếp',
	'excerpt'    => 'Most "rubbery shrimp" problems are not about the shrimp at all. Master thawing and timing, and you will get juicy, sweet results every time.',
	'excerpt_vi' => 'Phần lớn lỗi "tôm dai như cao su" không nằm ở con tôm. Nắm vững cách rã đông và canh thời gian, bạn sẽ có tôm mọng nước, ngọt thịt mỗi lần nấu.',
	'content'    => <<<'HTML'
<p>Good frozen shrimp is already cooked-quality fresh the moment it leaves the freezer. What happens next, thawing and cooking, decides whether it ends up sweet and juicy or tough and bland. Here is the chef's method.</p>

<h2>1. Thaw gently</h2>
<ul>
<li><strong>Best:</strong> move the shrimp from the freezer to the refrigerator the night before. Slow thawing keeps the texture perfect.</li>
<li><strong>In a hurry:</strong> place the shrimp in a sealed bag, submerge in cold water and wait 15–20 minutes, changing the water once.</li>
<li><strong>Never</strong> use hot water or a microwave. They start to cook the outside while the centre is still frozen.</li>
</ul>
<p>Once thawed, rinse briefly, then <strong>pat dry</strong> with paper towels. Dry shrimp brown and sear; wet shrimp steam.</p>

<h2>2. Season simply</h2>
<p>Salt, pepper, garlic and a squeeze of lemon go a long way. If you marinate with acid (lemon, lime, vinegar), keep it to 15–30 minutes: longer and the texture turns mushy.</p>

<h2>3. Mind the clock</h2>
<p>Shrimp cook <strong>very fast</strong>: usually 2–3 minutes per side in a hot pan, or 2–4 minutes in boiling water. Look at the shape:</p>
<ul>
<li><strong>"C" shape</strong> – gently curled: just right.</li>
<li><strong>"O" shape</strong> – tightly curled: overcooked.</li>
</ul>
<p>The flesh turns opaque and pink-white when done. Take it off the heat a moment early; residual heat finishes the job.</p>

<h2>A note on cooked shrimp</h2>
<p>Cooked products (such as Cooked PD or PDTO) only need thawing and gentle reheating, or can go straight into salads and cold dishes. Don't cook them again for long.</p>
<blockquote>Chef's secret: IQF shrimp can go straight from the freezer into soups, curries and stews. Just add a couple of extra minutes.</blockquote>
HTML,
	'content_vi' => <<<'HTML'
<p>Tôm đông lạnh chất lượng tốt có độ tươi ngon ngay khi lấy ra khỏi tủ đông. Điều xảy ra sau đó, rã đông và nấu, quyết định tôm sẽ ngọt mọng hay dai nhạt. Đây là phương pháp của đầu bếp.</p>

<h2>1. Rã đông nhẹ nhàng</h2>
<ul>
<li><strong>Tốt nhất:</strong> chuyển tôm từ ngăn đông sang ngăn mát vào tối hôm trước. Rã đông chậm giữ kết cấu thịt hoàn hảo.</li>
<li><strong>Khi cần gấp:</strong> cho tôm vào túi kín, ngâm trong nước lạnh khoảng 15–20 phút, thay nước một lần.</li>
<li><strong>Tuyệt đối không</strong> dùng nước nóng hoặc lò vi sóng. Chúng làm chín phần ngoài trong khi bên trong vẫn còn đông cứng.</li>
</ul>
<p>Sau khi rã đông, rửa nhanh rồi <strong>thấm khô</strong> bằng khăn giấy. Tôm khô sẽ vàng và săn mặt; tôm ướt chỉ bị hấp.</p>

<h2>2. Nêm nếm đơn giản</h2>
<p>Muối, tiêu, tỏi và một chút chanh là đủ. Nếu ướp với chất chua (chanh, giấm), chỉ nên 15–30 phút: lâu hơn thịt tôm sẽ bị bở.</p>

<h2>3. Canh thời gian</h2>
<p>Tôm chín <strong>rất nhanh</strong>: thường 2–3 phút mỗi mặt trên chảo nóng, hoặc 2–4 phút trong nước sôi. Hãy nhìn hình dạng con tôm:</p>
<ul>
<li><strong>Hình chữ "C"</strong> – cong nhẹ: vừa chín tới.</li>
<li><strong>Hình chữ "O"</strong> – cuộn chặt: đã quá lửa.</li>
</ul>
<p>Thịt chuyển sang màu trắng hồng đục khi chín. Hãy nhấc khỏi bếp sớm một chút; nhiệt dư sẽ hoàn thiện phần còn lại.</p>

<h2>Lưu ý với tôm đã chín</h2>
<p>Các sản phẩm đã chín (như Cooked PD hoặc PDTO) chỉ cần rã đông và hâm nhẹ, hoặc dùng ngay cho salad và món lạnh. Đừng nấu lại lâu.</p>
<blockquote>Bí quyết của đầu bếp: tôm IQF có thể cho thẳng từ tủ đông vào súp, cà ri và món hầm. Chỉ cần thêm vài phút.</blockquote>
HTML,
);

return array(
	'categories' => $categories,
	'posts'      => $posts,
);
