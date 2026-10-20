<?php

namespace Database\Seeders;

use App\Models\WorkshopEvent;
use Illuminate\Database\Seeder;

class WorkshopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            'title' => 'THE BACK',
            'tagline' => 'A Grateful Return. A New Beginning.',
            'introduction' => 'Có những hành trình phải tạm dừng, nhưng tình yêu dành cho nghệ thuật chưa bao giờ dừng lại. THE BACK đánh dấu sự trở lại của Văn Cường Makeup & Academy tại Nhật Bản sau một khoảng thời gian với nhiều thay đổi và thử thách. Với Cường, đây là dịp gửi lời cảm ơn chân thành đến những học viên, khách hàng và những người đã luôn tin tưởng, yêu thương, đồng hành trong suốt hành trình vừa qua.',
            'capacity' => 60,
            'ticket_price' => 0,
            'hero_image' => 'founder-van-cuong-enhanced.png',
            'glam_image' => '2aOboR6WhLoQQe6I8VzjFvxGVTBjIAHxaqrDAMNs.jpg',
            'bridal_image' => 'beauty-bridal-detail.jpg',
        ];

        $previous = WorkshopEvent::where('slug', 'the-art-of-elevation-2026')->first();
        if ($previous && ! WorkshopEvent::where('slug', config('workshop.slug'))->exists()) {
            $previous->update([...$defaults, 'slug' => config('workshop.slug')]);
        }

        WorkshopEvent::firstOrCreate(['slug' => config('workshop.slug')], $defaults);
    }
}
