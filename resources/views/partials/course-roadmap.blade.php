@php
    $roadmapStages = [
        [
            'title' => 'Xây dựng nền tảng',
            'range' => '01–04',
            'summary' => 'Tư duy nghề · Làn da · Nền & màu sắc',
            'weeks' => [
                ['number' => '01', 'title' => 'Những bước đầu tiên', 'topics' => ['Khai giảng, quan điểm nghề, định hướng thương hiệu cá nhân và lý thuyết sơ cấp về da (Bài 1).', 'Lý thuyết về nền, bánh xe màu sắc (Bài 2).', 'Facechart cơ bản — buổi 1 & 2 (Bài 3–4).']],
                ['number' => '02', 'title' => 'Nền, chân mày & kẻ mắt', 'topics' => ['Sửa bài Facechart.', 'Kỹ thuật nền, lông mày và kẻ mắt (Bài 5).', 'Luyện tập kỹ thuật nền.']],
                ['number' => '03', 'title' => 'Kỹ thuật mắt & makeup tiệc', 'topics' => ['Lý thuyết đánh mắt, phân tích và khắc phục các dáng mắt (Bài 6).', 'Makeup tone tiệc (Bài 7).', 'Luyện tập và hoàn thiện kỹ thuật.']],
                ['number' => '04', 'title' => 'Mi đa tầng & cô dâu ứng dụng', 'topics' => ['Lý thuyết kỹ thuật mi đa tầng (Bài 8).', 'Makeup cô dâu ứng dụng (Bài 9).', 'Luyện tập trên mẫu.']],
            ],
        ],
        [
            'title' => 'Ứng dụng & luyện tập',
            'range' => '05–08',
            'summary' => 'Makeup tiệc · Cô dâu · Beauty concept',
            'weeks' => [
                ['number' => '05', 'title' => 'Tone khói & xử lý da', 'topics' => ['Makeup tone tiệc — tone khói (Bài 10).', 'Makeup tone tiệc — xử lý da khuyết điểm (Bài 11).', 'Luyện tập sau từng nội dung.']],
                ['number' => '06', 'title' => 'Cô dâu concept', 'topics' => ['Makeup cô dâu concept (Bài 12).', 'Luyện tập và chuẩn bị bài thi.'], 'assessment' => 'Thi đánh giá lần 1', 'assessment_note' => 'Thực hiện trên mẫu chuyên nghiệp, chụp hình bằng máy cơ.'],
                ['number' => '07', 'title' => 'Beauty concept & thực hành 1:1', 'topics' => ['Beauty concept (Bài 13).', 'Luyện tập với các buổi Training 1:1.']],
                ['number' => '08', 'title' => 'Beauty & làn da khuyết điểm', 'topics' => ['Beauty concept (Bài 14).', 'Xử lý da chàm, da khuyết điểm (Bài 15).', 'Luyện tập và sửa bài.']],
            ],
        ],
        [
            'title' => 'Nâng cao kỹ thuật',
            'range' => '09–12',
            'summary' => 'Beauty Queen · Cut Crease · Douyin',
            'weeks' => [
                ['number' => '09', 'title' => 'Beauty Queen & hình khối', 'topics' => ['Makeup tone Beauty Queen (Bài 16).', 'Lý thuyết hình khối gương mặt.', 'Luyện tập và hoàn thiện layout.']],
                ['number' => '10', 'title' => 'Nghệ thuật lớp nền', 'topics' => ['Beauty concept — kỹ thuật nền nâng cao: trong, mỏng, bền (Bài 17).', 'Luyện tập kỹ thuật nền.']],
                ['number' => '11', 'title' => 'Kỹ thuật mắt Cut Crease', 'topics' => ['Kỹ thuật mắt Cut Crease (Bài 18).', 'Luyện tập với các buổi Training 1:1.']],
                ['number' => '12', 'title' => 'Douyin concept', 'topics' => ['Douyin concept (Bài 19).', 'Luyện tập và hoàn thiện bài.'], 'assessment' => 'Thi đánh giá lần 2', 'assessment_note' => 'Chụp hình hoàn thiện để đánh giá kỹ thuật và layout.'],
            ],
        ],
        [
            'title' => 'Hoàn thiện dấu ấn',
            'range' => '13–16',
            'summary' => 'High Fashion · Thương hiệu · Tốt nghiệp',
            'weeks' => [
                ['number' => '13', 'title' => 'Tư duy hình ảnh & High Fashion', 'topics' => ['Xây dựng tư duy hình ảnh — High Fashion Makeup.', 'Tư duy phân tích concept.', 'Luyện tập và phát triển ý tưởng.']],
                ['number' => '14', 'title' => 'Phân tích layout & hình ảnh cá nhân', 'topics' => ['Kỹ thuật phân tích layout, chiến lược hình ảnh cá nhân.', 'Luyện tập với các buổi Training 1:1.']],
                ['number' => '15', 'title' => 'Body Art & thương hiệu', 'topics' => ['Body Art.', 'Kỹ thuật xây dựng thương hiệu, phân tích chiến lược khách hàng.', 'Luyện tập và hoàn thiện kỹ năng.']],
                ['number' => '16', 'title' => 'Hình ảnh tốt nghiệp', 'topics' => ['Thực hiện hình ảnh tốt nghiệp — concept cô dâu.', 'Thực hiện hình ảnh Beauty Concept.', 'Sáng tạo với chủ đề tự do.']],
            ],
        ],
    ];
@endphp

<div class="roadmap-summary">
    <h3>Từng bước.<br><em>Vững tay nghề.</em></h3>
    <ol class="roadmap-summary-stages">
        @foreach($roadmapStages as $stage)
            <li><span class="roadmap-summary-number">0{{ $loop->iteration }}</span><div class="roadmap-summary-stage-heading"><h4>{{ $stage['title'] }}</h4><span class="roadmap-summary-weeks">TUẦN {{ $stage['range'] }}</span></div></li>
        @endforeach
    </ol>
    <button class="roadmap-open" type="button" aria-haspopup="dialog" aria-controls="roadmap-dialog">Xem chi tiết lộ trình <span aria-hidden="true">@include('partials.icon', ['name' => 'arrow-up-right'])</span></button>
</div>

<dialog id="roadmap-dialog" class="roadmap-dialog" aria-labelledby="roadmap-dialog-title" aria-describedby="roadmap-dialog-description">
    <header class="roadmap-dialog-header">
        <div><p class="roadmap-kicker">THE PRO CLASS / LỘ TRÌNH ĐÀO TẠO</p><h2 id="roadmap-dialog-title">04 tháng. Một hành trình.</h2><p id="roadmap-dialog-description">16 tuần học tập · 20 buổi Training + 20 buổi Practice</p></div>
        <button class="roadmap-close" type="button" aria-label="Đóng lộ trình" autofocus>@include('partials.icon', ['name' => 'close'])</button>
    </header>
    <nav class="roadmap-month-nav" aria-label="Chuyển đến từng tháng học">
        @foreach($roadmapStages as $stage)
            <button type="button" data-roadmap-month="roadmap-month-{{ $loop->iteration }}" aria-controls="roadmap-month-{{ $loop->iteration }}"><span>THÁNG 0{{ $loop->iteration }}</span><small>Tuần {{ $stage['range'] }}</small></button>
        @endforeach
    </nav>
    <div class="roadmap-dialog-body" tabindex="0" role="region" aria-label="Chi tiết lộ trình 16 tuần">
        @foreach($roadmapStages as $stage)
            <section class="roadmap-month" id="roadmap-month-{{ $loop->iteration }}" aria-labelledby="roadmap-month-title-{{ $loop->iteration }}" tabindex="-1">
                <div class="roadmap-month-heading"><span>0{{ $loop->iteration }}</span><div><p>THÁNG 0{{ $loop->iteration }} · TUẦN {{ $stage['range'] }}</p><h3 id="roadmap-month-title-{{ $loop->iteration }}">{{ $stage['title'] }}</h3></div></div>
                <ol class="roadmap-week-grid" start="{{ (int) $stage['weeks'][0]['number'] }}">
                    @foreach($stage['weeks'] as $week)
                        <li class="roadmap-week @isset($week['assessment']) roadmap-week-assessment @endisset">
                            <span class="roadmap-week-label">TUẦN {{ $week['number'] }}</span>
                            <h4>{{ $week['title'] }}</h4>
                            <ul>@foreach($week['topics'] as $topic)<li>{{ $topic }}</li>@endforeach</ul>
                            @isset($week['assessment'])
                                <div class="roadmap-assessment"><strong>{{ $week['assessment'] }}</strong><p>{{ $week['assessment_note'] }}</p></div>
                            @endisset
                        </li>
                    @endforeach
                </ol>
            </section>
        @endforeach
        <aside class="roadmap-notes" aria-labelledby="roadmap-notes-title">
            <h3 id="roadmap-notes-title">Đồng hành trong từng buổi học.</h3>
            <ul><li>Các buổi thi, học viên chủ động đặt mẫu chuyên nghiệp phù hợp định hướng để xây dựng portfolio cá nhân.</li><li>Training 1:1 linh hoạt trên mẫu thường hoặc mẫu chuyên nghiệp tùy nhu cầu.</li><li>Với mẫu chuyên nghiệp phù hợp concept, học viên được hỗ trợ hair và chụp hình.</li></ul>
        </aside>
    </div>
    <footer class="roadmap-dialog-footer"><span>VĂN CƯỜNG MAKEUP & ACADEMY</span><button type="button" data-roadmap-close>Đóng lộ trình @include('partials.icon', ['name' => 'close'])</button></footer>
</dialog>

