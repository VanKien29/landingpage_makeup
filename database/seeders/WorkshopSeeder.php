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
        WorkshopEvent::firstOrCreate(['slug' => config('workshop.slug')], [
            'title' => 'THE ART OF ELEVATION 2026',
            'tagline' => 'Elevate Your Beauty. Elevate Your Art.',
            'introduction' => 'THE ART OF ELEVATION 2026 là không gian gặp gỡ dành cho những người yêu nghệ thuật makeup tại Tokyo. Một ngày để quan sát kỹ hơn, học hỏi sâu hơn và tìm thấy nguồn cảm hứng mới cho hành trình của chính mình. Với quy mô private chỉ 40 khách, mỗi khoảnh khắc được dành cho sự kết nối, chia sẻ và phát triển kỹ thuật, tư duy thẩm mỹ cùng giá trị cá nhân.',
            'hero_image' => 'beauty-pro-technique.jpg',
            'glam_image' => '2aOboR6WhLoQQe6I8VzjFvxGVTBjIAHxaqrDAMNs.jpg',
            'bridal_image' => 'beauty-bridal-detail.jpg',
        ]);
    }
}
