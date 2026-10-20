<div class="roadmap-layout">
    <figure class="roadmap-figure">
        <button class="roadmap-open" type="button" aria-label="Xem lớn lộ trình đào tạo 4 tháng">
            <img src="{{ asset('images/course-roadmap-4-months.webp') }}" alt="Lộ trình đào tạo makeup Văn Cường trong 16 tuần, các nội dung học và hai đợt thi đánh giá" loading="lazy" width="1536" height="1024">
            <span class="roadmap-image-cta">XEM LỘ TRÌNH CHI TIẾT <span aria-hidden="true">@include('partials.icon', ['name' => 'arrow-up-right'])</span></span>
        </button>
    </figure>
    <div class="roadmap-overview">
        <div class="roadmap-overview-top"><span>04 THÁNG / 16 TUẦN</span><span>TỪ NỀN TẢNG ĐẾN DẤU ẤN RIÊNG</span></div>
        <div class="roadmap-stages">
            <article><span class="roadmap-weeks">TUẦN 01–04</span><h3>Xây dựng nền tảng</h3><p>Tư duy nghề, làn da, kỹ thuật nền, màu sắc và Facechart. Làm quen kỹ thuật mắt, mi và makeup cô dâu ứng dụng.</p><span class="roadmap-stage-number" aria-hidden="true">01</span></article>
            <article><span class="roadmap-weeks">TUẦN 05–08</span><h3>Ứng dụng & luyện tập</h3><p>Makeup tiệc, tone khói, cô dâu concept và Beauty concept. Rèn luyện kỹ thuật, xử lý da khuyết điểm.</p><span class="roadmap-assessment">TUẦN 06 · THI ĐÁNH GIÁ LẦN 1</span><span class="roadmap-stage-number" aria-hidden="true">02</span></article>
            <article><span class="roadmap-weeks">TUẦN 09–12</span><h3>Nâng cao kỹ thuật</h3><p>Beauty Queen, hình khối gương mặt, nền nâng cao, Cut Crease và Douyin. Thực hành với các buổi Training 1:1.</p><span class="roadmap-assessment">TUẦN 12 · THI ĐÁNH GIÁ LẦN 2</span><span class="roadmap-stage-number" aria-hidden="true">03</span></article>
            <article><span class="roadmap-weeks">TUẦN 13–16</span><h3>Hoàn thiện dấu ấn</h3><p>High Fashion, phân tích layout, tư duy hình ảnh, xây dựng thương hiệu và Body Art. Thực hiện hình ảnh tốt nghiệp.</p><span class="roadmap-stage-number" aria-hidden="true">04</span></article>
        </div>
        <p class="roadmap-overview-note">Các buổi thi và thực hiện hình ảnh giúp học viên nhìn lại kỹ thuật, hoàn thiện concept và xây dựng portfolio cá nhân.</p>
    </div>
</div>
<dialog class="roadmap-dialog" aria-labelledby="roadmap-dialog-title">
    <div class="roadmap-dialog-panel">
        <div class="roadmap-dialog-header"><div><h2 id="roadmap-dialog-title">Lộ trình đào tạo 4 tháng</h2><p>Phóng to để đọc, kéo hoặc vuốt để di chuyển ảnh.</p></div><button class="roadmap-close" type="button" aria-label="Đóng lộ trình" autofocus>@include('partials.icon', ['name' => 'close'])</button></div>
        <div class="roadmap-zoom-controls" role="group" aria-label="Điều chỉnh kích thước lộ trình"><button type="button" data-roadmap-out aria-label="Thu nhỏ lộ trình">@include('partials.icon', ['name' => 'zoom-out'])</button><button type="button" data-roadmap-fit>Vừa khung</button><button type="button" data-roadmap-in aria-label="Phóng to lộ trình">@include('partials.icon', ['name' => 'zoom-in'])</button><span class="roadmap-zoom-label" role="status" aria-live="polite">100%</span></div>
        <div class="roadmap-viewer" tabindex="0" role="region" aria-label="Ảnh lộ trình — dùng phím mũi tên, kéo hoặc vuốt để đọc"><div class="roadmap-canvas"><img src="{{ asset('images/course-roadmap-4-months.webp') }}" alt="Poster đầy đủ lộ trình 16 tuần: khóa khai giảng 15/06/2026, ca sáng 09:00–12:30" loading="lazy" width="1536" height="1024" draggable="false"></div></div>
    </div>
</dialog>

