<?php

namespace Database\Seeders;

use App\Models\SizeGuide;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SizeGuideSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial System Settings for Size Guide
        SystemSetting::set('size_guide_title', 'LULU Couture Size Guide');
        SystemSetting::set('size_guide_description', "How to measure your size accurately:\n\n• Bust: Measure around the fullest part of your bust, keeping the tape horizontal.\n• Waist: Measure around your natural waistline, keeping the tape comfortably loose.\n• Hips: Stand with your feet together and measure around the fullest part of your hips.\n• Length: Measure from the highest point of the shoulder seam down to the bottom hem.");
        SystemSetting::set('size_guide_unit', 'Inches (in)');

        // 2. Default Size Guide Entries
        $defaultSizes = [
            [
                'name' => 'XS',
                'bust' => '31 - 32',
                'waist' => '24 - 25',
                'hips' => '34 - 35',
                'length' => '34',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'S',
                'bust' => '33 - 34',
                'waist' => '26 - 27',
                'hips' => '36 - 37',
                'length' => '35',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'M',
                'bust' => '35 - 36',
                'waist' => '28 - 29',
                'hips' => '38 - 39',
                'length' => '36',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'L',
                'bust' => '37 - 39',
                'waist' => '30 - 32',
                'hips' => '40 - 42',
                'length' => '37',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'XL',
                'bust' => '40 - 42',
                'waist' => '33 - 35',
                'hips' => '43 - 45',
                'length' => '38',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($defaultSizes as $size) {
            SizeGuide::updateOrCreate(
                ['name' => $size['name']],
                $size
            );
        }
    }
}
