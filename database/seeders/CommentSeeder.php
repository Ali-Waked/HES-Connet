<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $articles = Article::all();
        $userIds = User::pluck('id')->toArray();

        $comments = [
            'This is very informative, thank you for sharing this valuable information with us!',
            'Great article! I learned a lot from this. Keep up the excellent work.',
            'I wish more people knew about this important topic. Thanks for raising awareness.',
            'Can you provide more details about this treatment? I would love to learn more.',
            'Thank you for raising awareness about this issue. It is very important for our community.',
            'This helped me understand my condition better. I appreciate the clear explanation.',
            'I shared this with my family, very useful information that everyone should know.',
            'Excellent write-up! Looking forward to more content like this in the future.',
            'I have been following this treatment and it works! Highly recommended.',
            'Very well researched and presented. The references are very helpful.',
            'This is exactly what I was looking for. Thank you for the detailed guide.',
            'My doctor recommended this article and it answered all my questions.',
            'Such an important topic. Everyone should take the time to read this.',
            'The tips in this article are practical and easy to follow. Great job!',
            'I appreciate how this article breaks down complex medical information.',
            'This changed my perspective on healthcare. Thank you for the insight.',
            'Bookmarking this for future reference. Very comprehensive guide.',
            'I have been practicing these tips and seeing great results. Thank you!',
            'Could you write more about this subject? Your articles are very helpful.',
            'This is a must-read for anyone concerned about their health.',
        ];

        foreach ($articles as $article) {
            $numComments = fake()->numberBetween(1, 5);
            for ($i = 0; $i < $numComments; $i++) {
                Comment::create([
                    'article_id' => $article->id,
                    'user_id' => $userIds[array_rand($userIds)],
                    'content' => $comments[array_rand($comments)],
                    'is_hidden' => fake()->boolean(5),
                ]);
            }
        }
    }
}
