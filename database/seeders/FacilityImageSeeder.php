<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityImage;
use Illuminate\Database\Seeder;

class FacilityImageSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = Facility::all();
        $images = [
            'https://images.unsplash.com/photo-1587351021759-3772687fe598?w=800',
            'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800',
            'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800',
            'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800',
            'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?w=800',
        ];

        foreach ($facilities as $facility) {
            // Add 1-3 additional images per facility
            $numImages = fake()->numberBetween(1, 3);
            for ($i = 0; $i < $numImages; $i++) {
                $image = $images[array_rand($images)];
                // Avoid duplicate images
                $existing = FacilityImage::where('facility_id', $facility->id)
                    ->where('image_url', $image)
                    ->exists();
                if (! $existing) {
                    FacilityImage::create([
                        'facility_id' => $facility->id,
                        'image_url' => $image.'&sig='.fake()->uuid(),
                    ]);
                }
            }
        }
    }
}
