<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StoryStatus;
use App\Models\Category;
use App\Models\Patient;
use App\Models\Story;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StorySeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        $storyCategories = Category::where('type', 'story')->get();

        $stories = [
            [
                'title' => ['en' => 'A Journey to Recovery: Ahmed\'s Story', 'ar' => 'رحلة التعافي: قصة أحمد'],
                'cover' => 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800',
                'is_fundraising' => false,
                'target' => 0,
                'collected' => 0,
            ],
            [
                'title' => ['en' => 'How Community Support Saved My Family', 'ar' => 'كيف أنقذ الدعم المجتمعي عائلتي'],
                'cover' => 'https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=800',
                'is_fundraising' => true,
                'target' => 25000,
                'collected' => 18300,
            ],
            [
                'title' => ['en' => 'From Darkness to Light: My Battle with Illness', 'ar' => 'من الظلام إلى النور: معركتي مع المرض'],
                'cover' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=800',
                'is_fundraising' => false,
                'target' => 0,
                'collected' => 0,
            ],
            [
                'title' => ['en' => 'Together We Can Make a Difference', 'ar' => 'معاً يمكننا إحداث فرق'],
                'cover' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800',
                'is_fundraising' => true,
                'target' => 50000,
                'collected' => 32100,
            ],
            [
                'title' => ['en' => 'A Mother\'s Fight for Her Child\'s Health', 'ar' => 'كفاح أم من أجل صحة طفلها'],
                'cover' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=800',
                'is_fundraising' => true,
                'target' => 15000,
                'collected' => 15200,
            ],
            [
                'title' => ['en' => 'Rebuilding Lives After the Crisis', 'ar' => 'إعادة بناء الحياة بعد الأزمة'],
                'cover' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=800',
                'is_fundraising' => true,
                'target' => 75000,
                'collected' => 45800,
            ],
            [
                'title' => ['en' => 'Your Donation Changed Everything', 'ar' => 'تبرعك غير كل شيء'],
                'cover' => 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800',
                'is_fundraising' => true,
                'target' => 10000,
                'collected' => 10000,
            ],
            [
                'title' => ['en' => 'Hope in the Midst of Adversity', 'ar' => 'الأمل في وسط الشدائد'],
                'cover' => 'https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=800',
                'is_fundraising' => false,
                'target' => 0,
                'collected' => 0,
            ],
            [
                'title' => ['en' => 'The Power of Collective Giving', 'ar' => 'قوة العطاء الجماعي'],
                'cover' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800',
                'is_fundraising' => true,
                'target' => 30000,
                'collected' => 28700,
            ],
            [
                'title' => ['en' => 'A Second Chance at Life', 'ar' => 'فرصة ثانية للحياة'],
                'cover' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=800',
                'is_fundraising' => false,
                'target' => 0,
                'collected' => 0,
            ],
            [
                'title' => ['en' => 'Emergency Surgery Saved My Daughter', 'ar' => 'الجراحة الطارئة أنقذت ابنتي'],
                'cover' => 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800',
                'is_fundraising' => true,
                'target' => 20000,
                'collected' => 16500,
            ],
            [
                'title' => ['en' => 'From Gaza with Hope: A Medical Success Story', 'ar' => 'من غزة بأمل: قصة نجاح طبي'],
                'cover' => 'https://images.unsplash.com/photo-1587351021759-3772687fe598?w=800',
                'is_fundraising' => false,
                'target' => 0,
                'collected' => 0,
            ],
            [
                'title' => ['en' => 'Helping the Elderly in Our Community', 'ar' => 'مساعدة كبار السن في مجتمعنا'],
                'cover' => 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800',
                'is_fundraising' => true,
                'target' => 12000,
                'collected' => 8900,
            ],
            [
                'title' => ['en' => 'A Child\'s Battle with Leukemia', 'ar' => 'معركة طفل مع اللوكيميا'],
                'cover' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=800',
                'is_fundraising' => true,
                'target' => 60000,
                'collected' => 42300,
            ],
            [
                'title' => ['en' => 'Supporting Families in Times of Need', 'ar' => 'دعم العائلات في أوقات الحاجة'],
                'cover' => 'https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=800',
                'is_fundraising' => true,
                'target' => 18000,
                'collected' => 12100,
            ],
            [
                'title' => ['en' => 'Recovery After the Storm: A Family\'s Journey', 'ar' => 'التعافي بعد العاصفة: رحلة عائلة'],
                'cover' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=800',
                'is_fundraising' => false,
                'target' => 0,
                'collected' => 0,
            ],
            [
                'title' => ['en' => 'Providing Medical Care to Remote Areas', 'ar' => 'توفير الرعاية الطبية للمناطق النائية'],
                'cover' => 'https://images.unsplash.com/photo-1587351021759-3772687fe598?w=800',
                'is_fundraising' => true,
                'target' => 35000,
                'collected' => 22100,
            ],
            [
                'title' => ['en' => 'A Story of Resilience and Hope', 'ar' => 'قصة صمود وأمل'],
                'cover' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800',
                'is_fundraising' => false,
                'target' => 0,
                'collected' => 0,
            ],
            [
                'title' => ['en' => 'Your Generosity Made This Possible', 'ar' => 'كرمكم جعل هذا ممكناً'],
                'cover' => 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800',
                'is_fundraising' => true,
                'target' => 8000,
                'collected' => 8050,
            ],
            [
                'title' => ['en' => 'Fighting Malnutrition in Gaza', 'ar' => 'محاربة سوء التغذية في غزة'],
                'cover' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800',
                'is_fundraising' => true,
                'target' => 22000,
                'collected' => 15700,
            ],
        ];

        $storyContents = [
            'en' => [
                'When I was diagnosed with a serious medical condition, my world turned upside down. But with the support of my family and the amazing medical team, I found the strength to fight back.',
                'Our community came together in an incredible way. Neighbors, friends, and even strangers donated generously to help cover my medical expenses. I am forever grateful.',
                'The journey through illness has been challenging, but it has also taught me valuable lessons about life, resilience, and the importance of community support.',
                'This story is about how we can achieve amazing things when we work together. Every donation, no matter how small, makes a real difference in someone\'s life.',
                'As a mother, watching your child suffer is the hardest thing in the world. But the outpouring of support from this community gave us hope and strength to continue the fight.',
                'After losing everything in the crisis, we had to rebuild our lives from scratch. The medical and financial support we received gave us a lifeline when we needed it most.',
                'The donation we received literally saved my life. I was unable to afford the surgery I needed, but because of your generosity, I am now healthy and able to take care of my family again.',
                'In the darkest moments of our lives, we found hope through the kindness of strangers. This experience has humbled me and made me believe in the goodness of humanity.',
                'When we pool our resources together, we can achieve remarkable things. This campaign is proof that collective action can solve even the most daunting challenges.',
                'I was given a second chance at life thanks to the medical team and the donors who made my treatment possible. I will never forget this gift of life.',
            ],
            'ar' => [
                'عندما تم تشخيصي بحالة طبية خطيرة، انقلب عالمي رأساً على عقب. ولكن بدعم من عائلتي والفريق الطبي الرائع، وجدت القوة للقتال.',
                'اجتمع مجتمعنا بطريقة لا تصدق. الجيران والأصدقاء وحتى الغرباء تبرعوا بسخاء للمساعدة في تغطية نفقاتي الطبية. أنا ممتنة إلى الأبد.',
                'كانت الرحلة خلال المرض صعبة، لكنها علمتني دروساً قيمة عن الحياة والصمود وأهمية الدعم المجتمعي.',
                'هذه القصة تدور حول كيف يمكننا تحقيق أشياء مذهلة عندما نعمل معاً. كل تبرع، مهما كان صغيراً، يحدث فرقاً حقيقياً في حياة شخص ما.',
                'كأم، مشاهدة طفلك يعاني هي أصعب شيء في العالم. لكن الدعم المتدفق من هذا المجتمع أعطانا الأمل والقوة لمواصلة الكفاح.',
                'بعد فقدان كل شيء في الأزمة، كان علينا إعادة بناء حياتنا من الصفر. الدعم الطبي والمالي الذي تلقيناه أعطانا شريان حياة عندما كنا في أمس الحاجة إليه.',
                'التبرع الذي تلقيناه أنقذ حياتي حرفياً. لم أكن قادراً على تحمل تكاليف الجراحة التي احتجتها، ولكن بفضل كرمكم، أنا الآن بصحة جيدة وقادر على رعاية عائلتي مرة أخرى.',
                'في أحلك لحظات حياتنا، وجدنا الأمل من خلال لطف الغرباء. هذه التجربة جعلتني متواضعة وجعلتني أؤمن بخير الإنسانية.',
                'عندما نجمع مواردنا معاً، يمكننا تحقيق أشياء رائعة. هذه الحملة دليل على أن العمل الجماعي يمكن أن يحل حتى أكثر التحديات صعوبة.',
                'لقد أعطيت فرصة ثانية للحياة بفضل الفريق الطبي والمتبرعين الذين جعلوا علاجي ممكناً. لن أنسى أبداً هذه الهدية من الحياة.',
            ],
        ];

        foreach ($stories as $index => $data) {
            $patient = $patients->random();
            $category = $storyCategories->random();
            $contentIndex = $index % count($storyContents['en']);

            Story::create([
                'uuid' => Str::uuid(),
                'patient_id' => $patient->id,
                'category_id' => $category->id,
                'title' => $data['title'],
                'content' => [
                    'en' => $storyContents['en'][$contentIndex]."\n\n".fake()->paragraphs(4, true),
                    'ar' => $storyContents['ar'][$contentIndex]."\n\n".fake('ar_SA')->paragraphs(4, true),
                ],
                'cover_image' => $data['cover'],
                'status' => StoryStatus::APPROVED,
                'is_fundraising' => $data['is_fundraising'],
                'target_amount' => $data['target'],
            ]);
        }
    }
}
