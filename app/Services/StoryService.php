<?php

namespace App\Services;

use App\Enums\StoryStatus;
use App\Events\StoryCreated;
use App\Models\Patient;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;

class StoryService
{
    public function create(Patient $patient, array $data): Story
    {
        $data['cover_image'] = $data['cover_image']->store('stories/covers', 'public');

        $story = Story::create([
            'patient_id' => $patient->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'cover_image' => $data['cover_image'],
            'status' => StoryStatus::PENDING,
            'is_fundraising' => $data['is_fundraising'] ?? false,
            'target_amount' => $data['target_amount'] ?? null,
        ]);

        event(new StoryCreated($story));

        return $story;
    }

    public function update(Story $story, array $data): Story
    {
        if (! empty($data['cover_image'])) {
            if ($story->getRawOriginal('cover_image')) {
                Storage::disk('public')->delete($story->getRawOriginal('cover_image'));
            }

            $data['cover_image'] = $data['cover_image']->store('stories/covers', 'public');
        }

        $story->update([
            'category_id' => $data['category_id'] ?? $story->category_id,
            'title' => $data['title'] ?? $story->title,
            'content' => $data['content'] ?? $story->content,
            'cover_image' => $data['cover_image'] ?? $story->getRawOriginal('cover_image'),
            'is_fundraising' => $data['is_fundraising'] ?? $story->is_fundraising,
            'target_amount' => $data['target_amount'] ?? $story->target_amount,
            'status' => StoryStatus::PENDING,
        ]);

        return $story->fresh();
    }
}
