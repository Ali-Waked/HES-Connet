<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $authorIds = User::pluck('id')->toArray();
        $articleCategories = Category::where('type', 'article')->get();
        $tags = Tag::all();

        $articles = [
            [
                'title' => ['en' => 'The Importance of Regular Health Checkups', 'ar' => 'أهمية الفحوصات الصحية المنتظمة'],
                'cover' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800',
                'views' => 1540,
            ],
            [
                'title' => ['en' => 'Understanding Blood Pressure: A Complete Guide', 'ar' => 'فهم ضغط الدم: دليل شامل'],
                'cover' => 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800',
                'views' => 2300,
            ],
            [
                'title' => ['en' => 'How to Boost Your Immune System Naturally', 'ar' => 'كيفية تعزيز جهاز المناعة طبيعياً'],
                'cover' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=800',
                'views' => 3200,
            ],
            [
                'title' => ['en' => 'Mental Health Awareness in the Workplace', 'ar' => 'الوعي بالصحة النفسية في مكان العمل'],
                'cover' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800',
                'views' => 1850,
            ],
            [
                'title' => ['en' => 'Managing Diabetes: Tips for a Healthy Lifestyle', 'ar' => 'إدارة السكري: نصائح لحياة صحية'],
                'cover' => 'https://images.unsplash.com/photo-1579165466741-7f35e4755661?w=800',
                'views' => 4100,
            ],
            [
                'title' => ['en' => 'Children\'s Vaccination: What Parents Need to Know', 'ar' => 'تطعيم الأطفال: ما يحتاج الآباء معرفته'],
                'cover' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=800',
                'views' => 2800,
            ],
            [
                'title' => ['en' => 'The Benefits of Regular Exercise for Heart Health', 'ar' => 'فوائد التمارين المنتظمة لصحة القلب'],
                'cover' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800',
                'views' => 1950,
            ],
            [
                'title' => ['en' => 'Understanding Cancer: Early Detection Saves Lives', 'ar' => 'فهم السرطان: الكشف المبكر ينقذ الأرواح'],
                'cover' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?w=800',
                'views' => 3650,
            ],
            [
                'title' => ['en' => 'Sleep Hygiene: How to Improve Your Sleep Quality', 'ar' => 'نظافة النوم: كيفية تحسين جودة نومك'],
                'cover' => 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=800',
                'views' => 2100,
            ],
            [
                'title' => ['en' => 'Nutrition Myths Debunked by Science', 'ar' => 'خرافات التغذية التي يكشفها العلم'],
                'cover' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=800',
                'views' => 2780,
            ],
            [
                'title' => ['en' => 'First Aid Basics Everyone Should Know', 'ar' => 'أساسيات الإسعافات الأولية التي يجب أن يعرفها الجميع'],
                'cover' => 'https://images.unsplash.com/photo-1587745416684-47953f16fdd1?w=800',
                'views' => 4500,
            ],
            [
                'title' => ['en' => 'The Impact of Climate Change on Public Health', 'ar' => 'تأثير تغير المناخ على الصحة العامة'],
                'cover' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=800',
                'views' => 1200,
            ],
            [
                'title' => ['en' => 'Prenatal Care: A Guide for Expecting Mothers', 'ar' => 'الرعاية قبل الولادة: دليل للأمهات الحوامل'],
                'cover' => 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=800',
                'views' => 3400,
            ],
            [
                'title' => ['en' => 'Understanding Antibiotic Resistance', 'ar' => 'فهم مقاومة المضادات الحيوية'],
                'cover' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=800',
                'views' => 1650,
            ],
            [
                'title' => ['en' => 'The Role of Telemedicine in Modern Healthcare', 'ar' => 'دور الطب عن بعد في الرعاية الصحية الحديثة'],
                'cover' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800',
                'views' => 890,
            ],
            [
                'title' => ['en' => 'Childhood Obesity: Causes and Prevention', 'ar' => 'السمنة عند الأطفال: الأسباب والوقاية'],
                'cover' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=800',
                'views' => 2200,
            ],
            [
                'title' => ['en' => 'Dental Health: More Than Just a Bright Smile', 'ar' => 'صحة الأسنان: أكثر من مجرد ابتسامة جميلة'],
                'cover' => 'https://images.unsplash.com/photo-1606811841689-23dfddce3e95?w=800',
                'views' => 1780,
            ],
            [
                'title' => ['en' => 'Yoga and Meditation for Stress Relief', 'ar' => 'اليوغا والتأمل لتخفيف التوتر'],
                'cover' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800',
                'views' => 2900,
            ],
            [
                'title' => ['en' => 'Seasonal Allergies: Symptoms and Treatments', 'ar' => 'الحساسية الموسمية: الأعراض والعلاجات'],
                'cover' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=800',
                'views' => 3100,
            ],
            [
                'title' => ['en' => 'The Dangers of Smoking and How to Quit', 'ar' => 'أضرار التدخين وكيفية الإقلاع عنه'],
                'cover' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800',
                'views' => 5250,
            ],
            [
                'title' => ['en' => 'Healthy Aging: Tips for Seniors', 'ar' => 'الشيخوخة الصحية: نصائح لكبار السن'],
                'cover' => 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800',
                'views' => 1450,
            ],
        ];

        $contents = [
            'en' => [
                'Regular health checkups are essential for maintaining good health and detecting potential issues early.',
                'Medical professionals recommend annual physical examinations to monitor vital signs and screen for common conditions.',
                'Early detection of diseases significantly improves treatment outcomes and reduces healthcare costs.',
                'A balanced diet rich in fruits, vegetables, and whole grains provides essential nutrients for optimal body function.',
                'Regular physical activity helps prevent chronic diseases and improves mental well-being.',
                'Vaccination is one of the most effective public health interventions, preventing millions of deaths annually.',
                'Mental health is equally important as physical health and should not be neglected.',
                'Adequate sleep of 7-9 hours per night is crucial for cognitive function and overall health.',
                'Stress management techniques such as meditation and deep breathing can significantly improve quality of life.',
                'Staying hydrated by drinking sufficient water is essential for all bodily functions.',
            ],
            'ar' => [
                'الفحوصات الصحية المنتظمة ضرورية للحفاظ على صحة جيدة واكتشاف المشكلات المحتملة مبكراً.',
                'يوصي الأطباء بإجراء فحوصات بدنية سنوية لمراقبة العلامات الحيوية وفحص الحالات الشائعة.',
                'الكشف المبكر عن الأمراض يحسن نتائج العلاج بشكل كبير ويقلل تكاليف الرعاية الصحية.',
                'النظام الغذائي المتوازن الغني بالفواكه والخضروات والحبوب الكاملة يوفر العناصر الغذائية الأساسية لوظائف الجسم المثلى.',
                'النشاط البدني المنتظم يساعد في الوقاية من الأمراض المزمنة ويحسن الصحة النفسية.',
                'التطعيم هو أحد أكثر تدخلات الصحة العامة فعالية، حيث يمنع ملايين الوفيات سنوياً.',
                'الصحة النفسية لا تقل أهمية عن الصحة الجسدية ولا ينبغي إهمالها.',
                'النوم الكافي لمدة 7-9 ساعات في الليلة أمر بالغ الأهمية للوظائف الإدراكية والصحة العامة.',
                'تقنيات إدارة التوتر مثل التأمل والتنفس العميق يمكن أن تحسن جودة الحياة بشكل كبير.',
                'البقاء رطباً عن طريق شرب كمية كافية من الماء ضروري لجميع وظائف الجسم.',
            ],
        ];

        foreach ($articles as $index => $data) {
            $category = $articleCategories->random();
            $authorId = $authorIds[array_rand($authorIds)];
            $contentIndex = $index % count($contents['en']);

            $slug = Str::slug($data['title']['en']).'-'.Str::lower(Str::random(6));

            $article = Article::create([
                'uuid' => Str::uuid(),
                'author_id' => $authorId,
                'slug' => $slug,
                'title' => $data['title'],
                'content' => [
                    'en' => $contents['en'][$contentIndex]."\n\n".fake()->paragraphs(3, true),
                    'ar' => $contents['ar'][$contentIndex]."\n\n".fake('ar_SA')->paragraphs(3, true),
                ],
                'category_id' => $category->id,
                'cover_image' => $data['cover'],
                'status' => ArticleStatus::PUBLISHED,
                'views' => $data['views'],
                'published_at' => now()->subDays(fake()->numberBetween(1, 180)),
            ]);

            // Attach 2-4 random tags
            $article->tags()->attach(
                $tags->random(fake()->numberBetween(2, 4))->pluck('id')->toArray()
            );
        }
    }
}
