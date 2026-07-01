<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'user_id' => User::factory(),
            'content' => fake()->randomElement([
                'This is very informative, thank you for sharing!',
                'Great article! I learned a lot from this.',
                'I wish more people knew about this important topic.',
                'Can you provide more details about this treatment?',
                'Thank you for raising awareness about this issue.',
                'This helped me understand my condition better.',
                'I shared this with my family, very useful information.',
                'Excellent write-up! Looking forward to more content like this.',
                'I have been following this treatment and it works!',
                'Very well researched and presented. Keep it up!',
            ]),
            'is_hidden' => fake()->boolean(5),
        ];
    }
}
