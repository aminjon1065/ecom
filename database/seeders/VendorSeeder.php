<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = Vendor::create([
            'user_id' => 3,
            'banner' => 'https://storage.alifshop.tj/media/images/settings/873/banner-1766984118664.jpg',
            'shop_name' => 'Vendor',
            'address' => fake()->address(),
            'description' => fake()->text(50),
            'facebook_url' => fake()->url(),
            'instagram_url' => fake()->url(),
            'telegram_url' => fake()->url(),
            'status' => 1
        ]);
    }
}
