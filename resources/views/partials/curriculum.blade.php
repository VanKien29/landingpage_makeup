<section class="section curriculum wrap" id="curriculum">
    <div class="section-top"><p class="eyebrow">03 / THE CURRICULUM</p><span>TECHNIQUE. AESTHETICS. IDENTITY.</span></div>
    <div class="curriculum-grid">
        <div class="curriculum-intro">
            <h2>Vững nền tảng.<br><em>Tự do sáng tạo.</em></h2>
            <p>06 nhóm kiến thức, từ những đường cọ đầu tiên đến tư duy của một người làm nghề.</p>
            <img class="curriculum-photo" src="{{ asset('images/educator-classroom.jpg') }}" alt="Giảng viên hướng dẫn trực tiếp trong lớp học makeup" loading="lazy" width="800" height="533">
            <p class="note">Các module là nhóm nội dung dự kiến. Giáo trình chi tiết của 20 buổi Training sẽ được học viện cập nhật.</p>
        </div>
        <div class="accordion curriculum-modules">
            <p class="module-guide">06 MODULES <span>CHẠM ĐỂ KHÁM PHÁ</span></p>
            @foreach($modules as $module)
                <details @if($loop->first) open @endif>
                    <summary>
                        <span class="module-index">0{{ $loop->iteration }}</span>
                        <span class="module-title"><strong>{{ $module[0] }}</strong><small>{{ $module[1] }}</small></span>
                        <span class="plus" aria-hidden="true">@include('partials.icon', ['name' => 'plus'])</span>
                    </summary>
                    <div class="module-body"><ul>@foreach($module[2] as $topic)<li>{{ $topic }}</li>@endforeach</ul></div>
                </details>
            @endforeach
        </div>
    </div>
</section>
