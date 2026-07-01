<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FacilityDocumentStatus;
use App\Models\Facility;
use App\Models\FacilityDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilityDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = Facility::all();

        if ($facilities->isEmpty()) {
            return;
        }

        $documentTypes = ['license', 'permit', 'certification', 'registration', 'insurance'];
        $unsplashImages = [
            'https://images.unsplash.com/photo-1587351021759-3772687fe598?w=800',
            'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800',
            'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800',
            'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800',
            'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?w=800',
            'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800',
            'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=800',
            'https://images.unsplash.com/photo-1504439468489-c8920d796a29?w=800',
        ];

        for ($i = 0; $i < 20; $i++) {
            $facility = $facilities->random();
            $documentType = $documentTypes[array_rand($documentTypes)];
            $image = $unsplashImages[array_rand($unsplashImages)];

            FacilityDocument::create([
                'facility_id' => $facility->id,
                'document_type' => $documentType,
                'status' => fake()->randomElement(FacilityDocumentStatus::cases()),
                'file_url' => $image.'&sig='.Str::uuid(),
            ]);
        }
    }
}
