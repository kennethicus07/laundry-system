<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Services\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'WASH-FOLD',
                'name' => 'Wash & Fold',
                'description' => 'Everyday laundry washing and folding.',
                'image_path' => 'services/wash-fold.jpeg',
                'pricing_unit' => 'kg',
                'default_unit_price' => 45.00,
                'is_active' => true,
            ],
            [
                'code' => 'WASH-IRON',
                'name' => 'Wash, Dry & Iron',
                'description' => 'Laundry washed, dried, and ironed.',
                'image_path' => 'services/wash-iron.jpeg',
                'pricing_unit' => 'kg',
                'default_unit_price' => 65.00,
                'is_active' => true,
            ],
            [
                'code' => 'DRY-CLEAN',
                'name' => 'Dry Cleaning',
                'description' => 'Special care for selected garments.',
                'image_path' => 'services/dry-clean.jpeg',
                'pricing_unit' => 'piece',
                'default_unit_price' => 120.00,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
