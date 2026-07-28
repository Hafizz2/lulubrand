<?php

namespace Database\Seeders;

use App\Models\HeroBanner;
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    public function run(): void
    {
        if (HeroBanner::count() === 0) {
            HeroBanner::create([
                'title' => 'New Season Collection',
                'subtitle' => 'Elegance & Couture Redefined',
                'button_text' => 'SHOP NEW DROPS',
                'button_url' => '/categories',
                'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1600&q=80',
                'is_active' => true,
                'sort_order' => 1,
            ]);

            HeroBanner::create([
                'title' => 'House of Luxury',
                'subtitle' => 'Handcrafted Dresses & Evening Wear',
                'button_text' => 'DISCOVER DRESSES',
                'button_url' => '/category/dresses',
                'image_url' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1600&q=80',
                'is_active' => true,
                'sort_order' => 2,
            ]);
        }
    }
}
