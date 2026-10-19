@php
    $films = [
        ['05', 'landscape', 'Những khoảnh khắc cùng nhau', 'Không khí lớp học', '00:39'],
        ['03', 'portrait', 'Từng chi tiết, từng nét cọ', 'Quá trình thực hiện makeup', '00:52'],
        ['02', 'portrait', 'Từ kỹ thuật đến sáng tạo', 'Beauty & creative makeup', '00:56'],
        ['04', 'portrait', 'Phía sau một layout', 'Hậu trường trang điểm', '00:47'],
        ['06', 'portrait', 'Sắc màu & cảm hứng', 'Khám phá các phong cách', '00:52'],
        ['01', 'portrait', 'Chuyện người làm nghề', 'Một góc nhìn về makeup', '00:52'],
    ];
@endphp
<section class="section academy-films" id="videos">
    <div class="wrap">
        <div class="section-top"><p class="eyebrow">ACADEMY IN MOTION / FILM JOURNAL</p><span>WATCH THE CRAFT COME TO LIFE</span></div>
        <div class="films-feature">
            <div class="films-intro"><h2>Khi nét cọ<br><em>kể câu chuyện.</em></h2><p>Những thước phim về makeup, quá trình thực hiện và khoảnh khắc cùng nhau. Chọn một video để xem trọn vẹn.</p><span class="films-count">06 THƯỚC PHIM / NHỮNG GÓC NHÌN VỀ NGHỀ</span></div>
            @php([$number, $orientation, $title, $subtitle, $duration] = array_shift($films))
            @include('partials.film-card')
        </div>
        <div class="films-rail-heading"><span>BEHIND THE BEAUTY</span><span class="films-swipe">VUỐT NGANG ĐỂ XEM THÊM @include('partials.icon', ['name' => 'arrow-right'])</span></div>
        <div class="films-grid" role="region" aria-label="Các video makeup dọc — có thể vuốt ngang trên điện thoại" tabindex="0">
            @foreach($films as [$number, $orientation, $title, $subtitle, $duration])
                @include('partials.film-card')
            @endforeach
        </div>
    </div>
</section>
<dialog class="video-lightbox" aria-labelledby="video-lightbox-title">
    <div class="video-lightbox-panel">
        <div class="video-lightbox-heading"><h2 id="video-lightbox-title">Thước phim makeup</h2><button class="video-close" type="button" aria-label="Đóng video" autofocus>@include('partials.icon', ['name' => 'close'])</button></div>
        <video controls playsinline preload="none" aria-label="Video makeup"></video>
        <p class="video-error" role="status" hidden>Video chưa thể phát. <a href="#" target="_blank" rel="noopener">Mở video trực tiếp @include('partials.icon', ['name' => 'arrow-up-right'])</a></p>
    </div>
</dialog>


