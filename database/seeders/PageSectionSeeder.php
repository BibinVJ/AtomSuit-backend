<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSectionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Home Page
        $home = Page::create([
            'slug' => 'home',
            'title' => 'Home',
            'meta_title' => 'Welcome to Home',
            'meta_description' => 'Homepage of the event website.',
        ]);

        $home->sections()->createMany([
            [
                'order' => 0,
                'is_enables' => true,
                'background_color' => '#ffffff',
                'background_image' => null,
                'type' => 'text',
                'content' => ['title' => 'Welcome', 'body' => 'Welcome to our event homepage!'],
            ],
            [
                'order' => 1,
                'background_color' => '#f9f9f9',
                'background_image' => null,
                'type' => 'image',
                'content' => ['src' => '/images/banner.jpg', 'alt' => 'Main Banner'],
            ],
            [
                'order' => 2,
                'background_color' => '#e0f7fa',
                'background_image' => null,
                'type' => 'html',
                'content' => '<div class="cta"><h2>Join Now</h2><p>Sign up for the event!</p></div>',
            ],
        ]);

        // 2. Sponsors Page
        $sponsors = Page::create([
            'slug' => 'sponsors',
            'title' => 'Sponsors',
            'meta_title' => 'Our Sponsors',
            'meta_description' => 'Meet our generous sponsors.',
        ]);

        $sponsors->sections()->create([
            'order' => 0,
            'background_color' => '#fff8e1',
            'background_image' => null,
            'type' => 'text',
            'content' => ['title' => 'Gold Sponsors', 'body' => 'We are proudly supported by top companies.'],
        ]);

        // 3. Exhibitors Page
        $exhibitors = Page::create([
            'slug' => 'exhibitors',
            'title' => 'Exhibitors',
            'meta_title' => 'Exhibitor Lineup',
            'meta_description' => 'Check out who is exhibiting.',
        ]);

        $exhibitors->sections()->createMany([
            [
                'order' => 0,
                'background_color' => '#fff3e0',
                'background_image' => null,
                'type' => 'text',
                'content' => ['title' => 'Tech Pavilion', 'body' => 'Latest tech from our exhibitors.'],
            ],
            [
                'order' => 1,
                'background_color' => '#ede7f6',
                'background_image' => null,
                'type' => 'html',
                'content' => '<div class="exhibitor-list"><ul><li>Company A</li><li>Company B</li></ul></div>',
            ],
        ]);
    }
}
