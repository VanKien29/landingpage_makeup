# Báo cáo audit và triển khai SEO — 20/10/2026

Thực hiện tại mã nguồn local. Chưa push hoặc deploy production. Không thêm package, migration hay sửa dữ liệu production. Các thay đổi WebP, cache bộ sưu tập và gitignore từ yêu cầu trước được giữ nguyên.

## A. Phạm vi và vấn đề phát hiện

### Kiến trúc đã kiểm tra

- PHP CLI 8.3.29, Laravel 13.37.0, PHPUnit 12.5.40, Pint 1.32.1; đối chiếu bằng Composer và Laravel Boost.
- Giao diện Blade, CSS và JavaScript phục vụ trực tiếp từ `public`. Package frontend khai báo Vite 8 và Tailwind 4, nhưng các landing page hiện không dùng bundle Vite trong layout.
- Trang HTML công khai: `/`, `/workshop/{slug}` nếu đã công bố, `/chinh-sach`.
- Danh mục và bộ lọc tác phẩm nằm trong trang chủ. Không có trang danh mục, tìm kiếm, bài viết hay phân trang công khai độc lập. Toàn bộ tác phẩm có trong HTML do server render; JavaScript điều khiển bộ lọc và số ảnh đang hiển thị.
- Phân trang, tìm kiếm và bộ lọc quản trị thuộc `/admin/*` và `/workshop-admin/*`, cần đăng nhập và quyền quản trị.
- `/tac-pham/{portfolioItem}/anh` phục vụ ảnh công khai; ảnh ẩn trả 404. Đây không phải trang HTML để đưa vào sitemap.
- Route workshop dùng slug có unique index; admin hiện không cho sửa slug. Có một URL workshop cũ được khai báo tường minh.

| Mức độ | Vấn đề và nguyên nhân | File liên quan trước khi sửa |
|---|---|---|
| Cao | Thiếu canonical, Twitter Card; metadata trang chính sách kế thừa mô tả trang chủ | `resources/views/layouts/app.blade.php`, `resources/views/workshop.blade.php`, `resources/views/policies.blade.php` |
| Cao | Không có sitemap; robots tĩnh cho phép crawl cả môi trường phát triển | `routes/web.php`, `public/robots.txt` |
| Cao | Chưa có chính sách X-Robots-Tag đồng nhất cho đăng nhập, quản trị, phản hồi form và lỗi | `bootstrap/app.php`, view admin |
| Vừa | URL workshop cũ dùng 302 dù đã đổi vĩnh viễn | `routes/web.php` |
| Vừa | Chuẩn hóa scheme/host chưa gắn với APP_URL; redirect slash ở Apache có thể tạo thêm bước trước redirect canonical của ứng dụng | `app/Providers/AppServiceProvider.php`, `public/.htaccess` |
| Vừa | Chưa có dữ liệu có cấu trúc | Các layout HTML công khai |
| Vừa | Trang chủ và header cùng truy vấn workshop | `app/Http/Controllers/HomeController.php`, `app/Providers/AppServiceProvider.php` |
| Vừa | Banner tải cùng một ảnh lớn cho nhiều kích thước màn hình | `resources/views/partials/hero.blade.php`, `resources/views/workshop.blade.php` |
| Vừa | Ảnh lộ trình khai báo 1706×2560 trong khi file thực tế là 1536×1024; logo thiếu kích thước HTML | `welcome.blade.php`, partial lộ trình/header/footer |
| Cần xác minh trên hosting | HTTPS/proxy, cache và nén HTTP thực tế, TTFB và Core Web Vitals chưa có phép đo production | Cấu hình hosting và dữ liệu người dùng thực |

### Quyết định index

| Nội dung | Quy tắc sau khi sửa |
|---|---|
| Trang chủ | Index ở production khi cấu hình cho phép |
| Workshop đã công bố | Index và có trong sitemap, kể cả khi đã đủ chỗ hoặc đóng form |
| Workshop chưa công bố/không tồn tại | HTTP 404 thật và header noindex |
| Chính sách đào tạo | `noindex, follow` vì nội dung hiện ghi rõ là bản dự thảo; loại khỏi sitemap |
| Đăng nhập, admin, CSV, phản hồi form, trang lỗi | Header noindex; giữ cơ chế authentication/authorization và status hiện có |
| Ảnh tác phẩm công khai | Không bị áp noindex trên production; ảnh ẩn vẫn 404 |
| Local/staging hoặc SEO_INDEXING=false | HTML có noindex, robots chặn crawl; sitemap trả 404 |

Robots production không chặn CSS/JS, cũng không chặn admin bằng Disallow: bot cần đọc được noindex ở trang đăng nhập. Bảo vệ dữ liệu vẫn do đăng nhập và authorization đảm nhiệm. Môi trường staging có dữ liệu riêng tư vẫn cần giới hạn truy cập ở hosting; robots không phải cơ chế bảo mật.

## B. Các thay đổi thực hiện

### File tạo mới

| File | Mục đích |
|---|---|
| `app/Services/Seo.php` | Metadata dùng chung; URL canonical từ APP_URL và named route; fallback cho nội dung workshop trống; JSON-LD |
| `config/seo.php` | Tên thương hiệu, title/description mặc định, ảnh chia sẻ, cờ indexing và canonical redirect |
| `config/trustedproxy.php` | Danh sách proxy tin cậy từ cấu hình; mặc định không tin header forwarded từ client |
| `app/Http/Middleware/SeoResponse.php` | Header noindex, no-store cho admin và redirect canonical 301 cho trang công khai |
| `app/Http/Controllers/SeoController.php` | Sitemap động, robots theo môi trường, metadata chính sách, redirect URL workshop cũ |
| `resources/views/partials/seo.blade.php` | Một bộ title, description, robots, canonical, Open Graph, Twitter Card và JSON-LD duy nhất |
| `resources/views/sitemap.blade.php` | XML escape URL và khai báo namespace sitemap |
| `tests/Feature/SeoTest.php` | 21 trường hợp kiểm thử SEO qua HTTP, gồm các bộ dữ liệu |
| `public/images/academy-38-640.webp`, `academy-38-960.webp` | Hai biến thể banner đầu trang chủ |
| `public/images/workshop-banner-bridal-enhanced-640.webp`, `workshop-banner-bridal-enhanced-960.webp` | Hai biến thể banner workshop |
| `SEO-AUDIT.md` | Báo cáo bàn giao này |

### File chỉnh sửa hoặc loại bỏ

| File | Thay đổi và lý do |
|---|---|
| `routes/web.php` | Thêm named routes sitemap/robots, dùng controller cho chính sách và redirect workshop cũ 301 |
| `bootstrap/app.php` | Gắn middleware SEO và noindex cho phản hồi từ exception handler |
| `app/Providers/AppServiceProvider.php` | URL sinh ở production theo APP_URL; header tận dụng workshop đã được controller truy vấn |
| `app/Http/Controllers/HomeController.php` | Truyền metadata và dữ liệu workshop dùng chung cho header |
| `app/Http/Controllers/WorkshopController.php` | Metadata theo dữ liệu workshop hiện tại, chỉ render sự kiện đã công bố |
| `.env.example` | Bổ sung SEO_INDEXING, SEO_CANONICAL_REDIRECTS, APP_TRUSTED_PROXIES; không sửa `.env` thật |
| `resources/views/layouts/app.blade.php` | Thay meta cũ bằng partial dùng chung; preload responsive cho ảnh banner đầu tiên |
| `resources/views/workshop.blade.php` | Partial metadata, responsive preload/srcset/sizes, kích thước banner đúng |
| `resources/views/policies.blade.php` | Bỏ khai báo title cũ để tránh hai nguồn metadata |
| `resources/views/partials/hero.blade.php` | srcset/sizes khớp breakpoint CSS và preload; ảnh chính không lazy-load |
| `resources/views/partials/header.blade.php`, `footer.blade.php` | Kích thước logo HTML; logo footer lazy-load |
| `resources/views/welcome.blade.php`, `partials/course-roadmap.blade.php` | Kích thước đúng của ảnh lộ trình để trình duyệt dự trù chỗ trước khi ảnh tải |
| `resources/views/admin/login.blade.php` | Thêm meta noindex bên cạnh header |
| `public/.htaccess` | Đưa chuẩn hóa slash trang công khai về middleware để gộp host/scheme/slash; thêm cache ảnh 7 ngày, CSS/JS 1 giờ và nén văn bản khi module Apache tương ứng có sẵn |
| `public/robots.txt` | Xóa file tĩnh để route động có thể điều khiển robots theo môi trường |

### Metadata, sitemap và structured data

- Các trường HTML dùng Blade escape; JSON-LD dùng JSON encoding với HEX_TAG/AMP/APOS/QUOT để dữ liệu không thể đóng thẻ script.
- Canonical loại query của landing page vì query hiện không thay đổi nội dung server render. Không áp dụng quy tắc này cho trang phân trang công khai trong tương lai. Phân trang admin hiện không index.
- Chuyển HTTP/www/slash về origin cấu hình bằng 301, giữ query phục vụ tracking. URL workshop cũ chuyển thẳng đến URL canonical mới. Không thêm redirect cho URL không tồn tại.
- Sitemap đọc danh sách workshop đã công bố mỗi request bằng query chỉ lấy id/slug. Không lưu bản sitemap lâu nên thay đổi publication/slug không cần đợi hết TTL. Không đưa admin, form, chính sách dự thảo, ảnh, URL cũ hoặc URL 404 vào XML.
- Không gán lastmod bằng thời gian request hay timestamp không đại diện đầy đủ cho thay đổi nội dung. Chỉ bổ sung khi có nguồn ngày sửa nội dung đáng tin cậy.
- JSON-LD có Organization, WebSite, WebPage; workshop có thêm BreadcrumbList: Trang chủ → Workshop. Không tạo danh mục cha không tồn tại.
- Không khai báo Event khi ngày tổ chức chưa chốt; không thêm rating, review, giá hay địa chỉ học viện chưa được xác minh. Khi ngày, giờ và địa điểm được xác nhận, có thể bổ sung Event schema tương ứng.

### Bằng chứng về hiệu suất và giới hạn

- Test xác nhận trang chủ chỉ còn **1 query workshop**, thay vì controller và header cùng thực hiện query như trước.
- Banner trang chủ: file gốc 298.964 byte; bản 640 px là 64.076 byte, bản 960 px là 122.288 byte.
- Banner workshop: file gốc 286.318 byte; bản 640 px là 98.722 byte, bản 960 px là 177.410 byte.
- Trình duyệt mobile đã chọn ảnh 640 px cho cả hai banner. Preload và img dùng chung srcset/sizes để tránh yêu cầu ảnh khác nhau.
- Giữ lazy loading cho ảnh dưới màn hình đầu, fetchpriority cao cho banner chính, JavaScript defer, font display=swap/preconnect và cache tác phẩm đang có.
- Đây là số byte ảnh và số query, **không phải phép đo tăng điểm PageSpeed hoặc cải thiện LCP/INP/CLS**. Chưa đo TTFB/MySQL ở production, chưa profile JavaScript hay font trên thiết bị thực.
- srcset mới tập trung vào banner đầu tiên của hai landing page. Chưa xây pipeline nhiều kích thước cho toàn bộ ảnh upload admin; phần này cần khảo sát dung lượng/tần suất tải thực tế trước khi mở rộng.

## C. Kết quả kiểm tra

- Nhóm SEO: 21 test, 161 assertions đạt.
- Nhóm SEO và workshop sau khi sửa lỗi biên dịch Blade phát hiện trong quá trình kiểm tra: 44 test đạt.
- Toàn bộ test suite sau các điều chỉnh cuối: **102 test, 598 assertions đạt**.
- Nội dung test: metadata đơn nhất, canonical/query, JSON-LD hợp lệ và chống đóng script, fallback dữ liệu, sitemap XML và publication, robots theo môi trường, redirect 301, proxy tin cậy/không tin cậy, lỗi 404/500/422/403, admin cần đăng nhập, ảnh công khai/ảnh ẩn và số query workshop.
- Pint đạt; Blade view cache và PHP lint của toàn bộ file Blade đã compile đạt. Route cache tạo thành công và đã clear lại để local tiếp tục phát triển bình thường. Git diff check không có lỗi whitespace.
- Trình duyệt: trang chủ và workshop có một H1, không thiếu alt hoặc width/height trên ảnh có src; các liên kết neo kiểm tra đều có đích; khung mobile thực tế 375 px không tràn ngang; form workshop còn hiển thị. Desktop workshop hiển thị bình thường.
- Bằng chứng mobile local: `storage/app/private/seo-workshop-mobile.png` (file local, không đưa lên Git).
- Chưa xác minh: module Apache/AllowOverride và header cache/nén trên hosting; SSL/CDN/reverse proxy thực tế; Google indexing, Rich Results và Core Web Vitals. Test dùng SQLite, không đo hiệu năng MySQL production.

## D. Việc cần làm khi triển khai production

1. Kiểm tra `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` là domain HTTPS chính xác. `SEO_INDEXING=true` ở site chính; đặt false cho staging kể cả khi staging dùng APP_ENV=production. Không đưa credential vào Git.
2. Nếu có proxy/CDN kết thúc TLS, xác minh IP/CIDR và khai báo APP_TRUSTED_PROXIES. Không đặt tin mọi IP khi chưa xác minh hạ tầng. Có thể tạm đặt SEO_CANONICAL_REDIRECTS=false nếu hosting đã quản lý redirect; meta canonical vẫn hoạt động.
3. Đồng bộ cả file mới và file bị xóa. Nếu upload ZIP đè lên thư mục cũ, phải bảo đảm **public/robots.txt cũ không còn tồn tại**, nếu không web server sẽ phục vụ file tĩnh thay route động. Kiểm tra bản `.htaccess` đang dùng và quyền AllowOverride của hosting.
4. Chạy lệnh cache thông thường sau khi cập nhật code/cấu hình: `php artisan optimize:clear`, sau đó `php artisan optimize`. Không cần migration mới cho riêng đợt SEO này. Chỉ chạy khi kết nối DB production hoạt động bình thường.
5. Kiểm tra HTTPS/www/slash/URL cũ không tạo loop hoặc chuỗi redirect từ hosting/CDN; response thực tế của trang chủ/workshop là 200, trang không tồn tại là 404. Kiểm tra canonical và OG không mang localhost hoặc domain phụ.
6. Xác minh robots và sitemap trên domain thật; kiểm tra header ảnh/CSS/JS và gzip/Brotli ở hosting. Nếu hạ tầng là Nginx, `.htaccess` không áp dụng: cần cấu hình tương đương ở server/CDN.
7. Google Search Console: xác minh domain, gửi `/sitemap.xml`, URL Inspection cho trang chủ và workshop, kiểm tra rendered HTML/mobile screenshot, canonical Google chọn, Page Indexing và Crawl Stats. Nếu domain cũ vẫn được duy trì, cần thống nhất chiến lược redirect về domain chính.
8. Rich Results Test/Schema Markup Validator: kiểm tra Organization/WebSite/BreadcrumbList bằng URL production. Chốt ngày workshop trước khi bổ sung Event schema.
9. PageSpeed Insights: đo mobile và desktop cho cả hai landing page, xem dữ liệu CrUX nếu đủ lưu lượng. Ghi LCP/INP/CLS/TTFB, waterfall ảnh và font trước khi quyết định tối ưu CSS, font subsets hoặc responsive upload toàn bộ.
10. Rà soát bản chính sách trước khi cho index. Xác minh địa chỉ học viện đầy đủ trước khi thêm LocalBusiness; địa chỉ workshop không đại diện cho địa chỉ thường trực của học viện.

Không bảo đảm thứ hạng Google. Sitemap và structured data giúp cung cấp tín hiệu; việc crawl/index/hiển thị kết quả vẫn do công cụ tìm kiếm quyết định.

### Tài liệu tham chiếu chính thức

- [Canonical và URL trùng lặp — Google Search Central](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls)
- [Xây dựng sitemap và lastmod — Google Search Central](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap)
- [Robots meta và X-Robots-Tag — Google Search Central](https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag)
- [Breadcrumb structured data — Google Search Central](https://developers.google.com/search/docs/appearance/structured-data/breadcrumb)
- [Tối ưu LCP — web.dev](https://web.dev/articles/optimize-lcp)
