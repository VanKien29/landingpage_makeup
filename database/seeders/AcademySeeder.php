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
            ['2aOboR6WiZgtJaXsCt5LunyqWE9n4yTJ5JsJ9sJc.jpg', 'The Beauty Edit', 'beauty', 'Lớp nền căng mịn và đường nét sắc sảo'],
            ['2aOboR6WhLnl4g5qZz8TuD5Ckzyfk7yv12HFIviq.jpg', 'Soft Bridal', 'bridal', 'Makeup cô dâu dịu dàng với sắc hồng trong trẻo'],
            ['2aOboR6WhLnnVV2E5C0fkXzMbyLDweVDR6iWYt72.jpg', 'Pearl & Light', 'editorial', 'Layout ánh nhũ với phụ kiện ngọc trai'],
            ['2aOboR6WhLnU2vVC3X18zqdEG13oVCdeq7Fq8sPw.jpg', 'Western Muse', 'creative', 'Layout thời trang với phong cách western'],
            ['2aOboR6WhLnWTkRZYjtKqBKYzmFwR2WL9cP6nGCW.jpg', 'Sunlit Skin', 'beauty', 'Makeup tự nhiên trong ánh sáng ấm'],
            ['2aOboR6WhLoQQe6I8VzjFvxGVTBjIAHxaqrDAMNs.jpg', 'Modern Glam', 'beauty', 'Đường nét sắc sảo và sắc môi trung tính'],
            ['2aOboR6WhLo6y4ZG6r0CVEu2YjREBQuOtbwQYrTM.jpg', 'Bridal Poise', 'bridal', 'Layout cô dâu thanh lịch và tinh tế'],
            ['2aOboR6WhLo6y4ZG6r0CVEo2oSZBjq4pw6BsTdia.jpg', 'The Veiled Muse', 'editorial', 'Makeup thời trang kết hợp khăn ren'],
            ['2aOboR6WhLo9OtVdc3sOLa2rFt2aAsKz9vRHu2DY.jpg', 'Rose Reverie', 'douyin', 'Makeup sắc hồng với nhũ sáng và mái tóc gợn sóng'],
        ];

        $nonPortfolio = [
            '2aOboR6WhLnidr9T4mGI3s5T8YHZ4Bvi5YqXBqCG.jpg',
            '2aOboR6WhLo4XFcsbe80ety1UDNJaccQYH06u9b6.jpg',
            '2aOboR6WhLo6y4ZG6r0CVEqQNFcDT4PLtOEC7ChU.jpg',
            '2aOboR6WhLo6y4ZG6r0CVFX5GNMwjOClfxs9cEeu.jpg',
            '2aOboR6WhLnU2vVC3X18zqmi2dLVVwGRugKGzhse.jpg',
            '2aOboR6WhLnidr9T4mGI3sGgDGKCv647fR0I58RU.jpg',
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
