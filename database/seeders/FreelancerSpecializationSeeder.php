<?php

namespace Database\Seeders;

use App\Models\FreelancerCategory;
use App\Models\FreelancerSpecialization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FreelancerSpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $specializationsByCategorySlug = [
            'graphics-design' => [
                'Logo Design', 'Business Cards & Stationery', 'Illustration',
                'Web & App Design', 'Brand Style Guides', 'Packaging & Label Design',
            ],
            'programming-tech' => [
                'Website Development', 'WordPress', 'Mobile App Development',
                'E-Commerce Development', 'Software Development', 'Database Development',
            ],
            'digital-marketing' => [
                'Social Media Marketing', 'Search Engine Optimization (SEO)', 'Content Marketing',
                'Email Marketing', 'Search Engine Marketing (SEM)', 'Influencer Marketing',
            ],
            'video-animation' => [
                'Video Editing', 'Whiteboard & Animated Explainers', 'Character Animation',
                'Logo Animation', 'Short Video Ads', 'Intro & Outro Videos',
            ],
            'writing-translation' => [
                'Articles & Blog Posts', 'Translation', 'Proofreading & Editing',
                'Resume Writing', 'Creative Writing', 'Technical Writing',
            ],
            'music-audio' => [
                'Voice Over', 'Mixing & Mastering', 'Producers & Composers',
                'Podcast Editing', 'Jingles & Intros', 'Audiobook Production',
            ],
        ];

        foreach ($specializationsByCategorySlug as $categorySlug => $specializations) {
            $category = FreelancerCategory::withoutGlobalScope('translate')->where('slug', $categorySlug)->first();
            if (!$category) {
                continue;
            }

            foreach ($specializations as $index => $name) {
                FreelancerSpecialization::withoutGlobalScope('translate')->firstOrCreate(
                    ['freelancer_category_id' => $category->id, 'slug' => Str::slug($name)],
                    [
                        'name' => $name,
                        'image' => 'def.png',
                        'image_storage_type' => 'public',
                        'priority' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
