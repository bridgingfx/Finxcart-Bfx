<?php

namespace Database\Seeders;

use App\Models\FreelancerCategory;
use Illuminate\Database\Seeder;

class FreelancerCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Graphics & Design', 'slug' => 'graphics-design'],
            ['name' => 'Programming & Tech', 'slug' => 'programming-tech'],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing'],
            ['name' => 'Video & Animation', 'slug' => 'video-animation'],
            ['name' => 'Writing & Translation', 'slug' => 'writing-translation'],
            ['name' => 'Music & Audio', 'slug' => 'music-audio'],
        ];

        foreach ($categories as $index => $category) {
            FreelancerCategory::withoutGlobalScope('translate')->firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'image' => 'def.png',
                    'image_storage_type' => 'public',
                    'parent_id' => null,
                    'position' => 0,
                    'priority' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
