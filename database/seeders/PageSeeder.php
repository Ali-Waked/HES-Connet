<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'uuid' => Str::uuid(),
                'slug' => 'about-us',
                'title' => [
                    'en' => 'About Us',
                    'ar' => 'من نحن',
                ],
                'content' => [
                    'en' => 'About Us content goes here.',
                    'ar' => 'محتوى من نحن هنا.',
                ],
                'status' => 'draft',
            ],
            [
                'uuid' => Str::uuid(),
                'slug' => 'privacy-policy',
                'title' => [
                    'en' => 'Privacy Policy',
                    'ar' => 'سياسة الخصوصية',
                ],
                'content' => [
                    'en' => 'Privacy Policy content goes here.',
                    'ar' => 'محتوى سياسة الخصوصية هنا.',
                ],
                'status' => 'draft',
            ],
            [
                'uuid' => Str::uuid(),
                'slug' => 'terms-and-conditions',
                'title' => [
                    'en' => 'Terms & Conditions',
                    'ar' => 'الشروط والأحكام',
                ],
                'content' => [
                    'en' => 'Terms and Conditions content goes here.',
                    'ar' => 'محتوى الشروط والأحكام هنا.',
                ],
                'status' => 'draft',
            ],
            [
                'uuid' => Str::uuid(),
                'slug' => 'faq',
                'title' => [
                    'en' => 'FAQ',
                    'ar' => 'الأسئلة الشائعة',
                ],
                'content' => [
                    'en' => 'FAQ content goes here.',
                    'ar' => 'محتوى الأسئلة الشائعة هنا.',
                ],
                'status' => 'draft',
            ],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
