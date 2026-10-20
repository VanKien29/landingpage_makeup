<?php

namespace Database\Seeders;

use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademySeeder extends Seeder
{
    public function run(): void
    {
        $artworks = [
            ['2aOboR6WiZgtJaXsCt5LunyqWE9n4yTJ5JsJ9sJc.webp', 'The Beauty Edit', 'beauty', 'Lớp nền căng mịn và đường nét sắc sảo'],
            ['2aOboR6WhLnl4g5qZz8TuD5Ckzyfk7yv12HFIviq.webp', 'Soft Bridal', 'bridal', 'Makeup cô dâu dịu dàng với sắc hồng trong trẻo'],
            ['2aOboR6WhLnnVV2E5C0fkXzMbyLDweVDR6iWYt72.webp', 'Pearl & Light', 'editorial', 'Layout ánh nhũ với phụ kiện ngọc trai'],
            ['2aOboR6WhLnU2vVC3X18zqdEG13oVCdeq7Fq8sPw.webp', 'Western Muse', 'creative', 'Layout thời trang với phong cách western'],
            ['2aOboR6WhLnWTkRZYjtKqBKYzmFwR2WL9cP6nGCW.webp', 'Sunlit Skin', 'beauty', 'Makeup tự nhiên trong ánh sáng ấm'],
            ['2aOboR6WhLoQQe6I8VzjFvxGVTBjIAHxaqrDAMNs.webp', 'Modern Glam', 'beauty', 'Đường nét sắc sảo và sắc môi trung tính'],
            ['2aOboR6WhLo6y4ZG6r0CVEu2YjREBQuOtbwQYrTM.webp', 'Bridal Poise', 'bridal', 'Layout cô dâu thanh lịch và tinh tế'],
            ['2aOboR6WhLo6y4ZG6r0CVEo2oSZBjq4pw6BsTdia.webp', 'The Veiled Muse', 'editorial', 'Makeup thời trang kết hợp khăn ren'],
            ['2aOboR6WhLo9OtVdc3sOLa2rFt2aAsKz9vRHu2DY.webp', 'Rose Reverie', 'douyin', 'Makeup sắc hồng với nhũ sáng và mái tóc gợn sóng'],
        ];

        $nonPortfolio = [
            '2aOboR6WhLnidr9T4mGI3s5T8YHZ4Bvi5YqXBqCG.webp',
            '2aOboR6WhLo4XFcsbe80ety1UDNJaccQYH06u9b6.webp',
            '2aOboR6WhLo6y4ZG6r0CVEqQNFcDT4PLtOEC7ChU.webp',
            '2aOboR6WhLo6y4ZG6r0CVFX5GNMwjOClfxs9cEeu.webp',
            '2aOboR6WhLnU2vVC3X18zqmi2dLVVwGRugKGzhse.webp',
            '2aOboR6WhLnidr9T4mGI3sGgDGKCv647fR0I58RU.webp',
        ];
        PortfolioItem::whereIn('image_path', $nonPortfolio)->update(['is_featured' => false]);

        foreach ($artworks as $index => [$image, $title, $category, $alt]) {
            PortfolioItem::updateOrCreate(['image_path' => $image], [
                'title' => $title,
                'category' => $category,
                'alt_text' => $alt,
                'display_order' => $index + 101,
                'is_featured' => true,
            ]);
        }

        if (config('academy.admin_password')) {
            User::updateOrCreate(['email' => config('academy.admin_email')], [
                'name' => 'Văn Cường Admin',
                'password' => config('academy.admin_password'),
            ]);
        }
    }
}
