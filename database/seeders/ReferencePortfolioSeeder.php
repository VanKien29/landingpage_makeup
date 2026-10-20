<?php

namespace Database\Seeders;

use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;

class ReferencePortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $references = [
            [38, 'Pearl Muse — Study', 'bridal', 'Layout cô dâu với nhũ sáng và phụ kiện hoa'],
            [40, 'Botanical Beauty — Study', 'beauty', 'Makeup tự nhiên với điểm nhấn xanh lá và hoa trắng'],
            [43, 'Butterfly Editorial — Study', 'editorial', 'Makeup thời trang với đường nét mắt sáng tạo'],
            [36, 'Bridal Silhouette — Study', 'bridal', 'Phong cách cô dâu trong trang phục trắng'],
            [42, 'Natural Light — Study', 'beauty', 'Lớp nền tự nhiên và ánh nhìn dịu dàng'],
            [45, 'Pink Couture — Study', 'editorial', 'Layout thời trang trong trang phục hồng'],
            [39, 'Botanical Details — Study', 'beauty', 'Chi tiết trang điểm với hoa trắng'],
            [32, 'Soft Douyin — Study', 'douyin', 'Makeup sắc hồng, lớp nền sáng và điểm nhấn nhũ'],
            [25, 'Creative Lines — Study', 'creative', 'Trang điểm mắt sáng tạo với sắc hồng và phụ kiện bướm'],
            [41, 'Garden Muse — Study', 'bridal', 'Bố cục cô dâu trong không gian xanh'],
            [47, 'Couture Study', 'editorial', 'Tạo hình thời trang với váy hồng và hoa'],
            [7, 'Pearl Light — Study', 'beauty', 'Gương mặt beauty với sắc hồng và phụ kiện ngọc trai'],
            [18, 'Graphic Eye — Study', 'creative', 'Layout mắt đồ họa và sắc hồng'],
            [20, 'Botanical Glow — Study', 'beauty', 'Tác phẩm makeup với làn da sáng tự nhiên'],
            [44, 'Fashion in Motion — Study', 'editorial', 'Bố cục thời trang và đường nét chuyển động'],
        ];

        foreach ($references as $index => [$number, $title, $category, $description]) {
            PortfolioItem::updateOrCreate(['image_path' => sprintf('academy-%02d.webp', $number)], [
                'title' => $title,
                'category' => $category,
                'alt_text' => $description.' — hình ảnh tham khảo',
                'display_order' => $index + 1,
                'is_featured' => true,
            ]);
        }
    }
}
