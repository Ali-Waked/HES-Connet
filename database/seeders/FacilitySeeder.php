<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Facility;
use App\Models\FacilityImage;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::all()->keyBy('id');
        $owner = User::where('email', 'layla@example.com')->first();
        $hani = User::where('email', 'hani@example.com')->first();
        $mariam = User::where('email', 'mariam@example.com')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();
        $orgIds = Organization::pluck('id')->toArray();

        $facilities = [
            [
                'name' => ['en' => 'Al Hayat Medical Center', 'ar' => 'مركز الحياة الطبي'],
                'facility_type' => 'hospital',
                'owner_id' => $owner?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1587351021759-3772687fe598?w=800',
            ],
            [
                'name' => ['en' => 'Nablus Specialized Hospital', 'ar' => 'مستشفى نابلس التخصصي'],
                'facility_type' => 'hospital',
                'owner_id' => $owner?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800',
            ],
            [
                'name' => ['en' => 'Ramallah Diagnostic Center', 'ar' => 'مركز رام الله التشخيصي'],
                'facility_type' => 'clinic',
                'owner_id' => $hani?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800',
            ],
            [
                'name' => ['en' => 'Gaza Medical Complex', 'ar' => 'مجمع غزة الطبي'],
                'facility_type' => 'hospital',
                'owner_id' => $owner?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800',
            ],
            [
                'name' => ['en' => 'Khan Younis Pharmacy', 'ar' => 'صيدلية خان يونس'],
                'facility_type' => 'pharmacy',
                'owner_id' => $mariam?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1586015555751-63c3f3a6e7b0?w=800',
            ],
            [
                'name' => ['en' => 'Hebron Medical Clinic', 'ar' => 'عيادة الخليل الطبية'],
                'facility_type' => 'clinic',
                'owner_id' => $hani?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=800',
            ],
            [
                'name' => ['en' => 'Jenin General Hospital', 'ar' => 'مستشفى جنين العام'],
                'facility_type' => 'hospital',
                'owner_id' => $owner?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?w=800',
            ],
            [
                'name' => ['en' => 'Tulkarm Community Pharmacy', 'ar' => 'صيدلية طولكرم المجتمعية'],
                'facility_type' => 'pharmacy',
                'owner_id' => $mariam?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1576671081837-49000212a370?w=800',
            ],
            [
                'name' => ['en' => 'Deir al-Balah Health Center', 'ar' => 'مركز دير البلح الصحي'],
                'facility_type' => 'medical_point',
                'owner_id' => $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=800',
            ],
            [
                'name' => ['en' => 'Rafah Maternity Clinic', 'ar' => 'عيادة رفح للولادة'],
                'facility_type' => 'clinic',
                'owner_id' => $hani?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=800',
            ],
            [
                'name' => ['en' => 'West Bank Eye Center', 'ar' => 'مركز الضفة للعيون'],
                'facility_type' => 'clinic',
                'owner_id' => $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1579684453423-f84349ef60b0?w=800',
            ],
            [
                'name' => ['en' => 'Palestine Dental Care', 'ar' => 'مركز فلسطين لطب الأسنان'],
                'facility_type' => 'clinic',
                'owner_id' => $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1606811841689-23dfddce3e95?w=800',
            ],
            [
                'name' => ['en' => 'Gaza City Pharmacy', 'ar' => 'صيدلية مدينة غزة'],
                'facility_type' => 'pharmacy',
                'owner_id' => $mariam?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1567581935884-3349723552ca?w=800',
            ],
            [
                'name' => ['en' => 'Al Quds Cancer Center', 'ar' => 'مركز القدس لعلاج السرطان'],
                'facility_type' => 'hospital',
                'owner_id' => $owner?->id ?? $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?w=800',
            ],
            [
                'name' => ['en' => 'Bethlehem Pediatric Clinic', 'ar' => 'عيادة بيت لحم للأطفال'],
                'facility_type' => 'clinic',
                'owner_id' => $admin?->id,
                'cover_image' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=800',
            ],
        ];

        $additionalTypes = ['hospital', 'clinic', 'pharmacy', 'medical_point'];
        for ($i = 0; $i < 10; $i++) {
            $facilities[] = [
                'name' => [
                    'en' => fake()->company().' '.fake()->randomElement(['Health Center', 'Medical Facility', 'Clinic', 'Pharmacy', 'Hospital']),
                    'ar' => fake('ar_SA')->company().' '.fake()->randomElement(['الصحي', 'الطبي', 'الصيدلي']),
                ],
                'facility_type' => $additionalTypes[array_rand($additionalTypes)],
                'owner_id' => $admin?->id,
                'cover_image' => fake()->randomElement([
                    'https://images.unsplash.com/photo-1587351021759-3772687fe598?w=800',
                    'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800',
                    'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800',
                    'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=800',
                ]),
            ];
        }

        foreach ($facilities as $data) {
            $city = $cities->random();
            $orgId = $orgIds[array_rand($orgIds)];

            $facility = Facility::create([
                'uuid' => Str::uuid(),
                'name' => $data['name'],
                'description' => ['en' => 'A modern healthcare facility providing comprehensive medical services to the community.', 'ar' => 'منشأة صحية حديثة تقدم خدمات طبية شاملة للمجتمع.'],
                'latitude' => fake()->randomFloat(7, 31.2, 31.6),
                'longitude' => fake()->randomFloat(7, 34.2, 34.6),
                'facility_type' => $data['facility_type'],
                'status' => 'active',
                'approval_status' => 'approved',
                'cover_image' => $data['cover_image'],
                'organization_id' => $orgId,
                'owner_id' => $data['owner_id'],
                'created_by' => $admin?->id,
                'city_id' => $city->id,
                'is_featured' => fake()->boolean(20),
            ]);

            FacilityImage::create([
                'facility_id' => $facility->id,
                'image_url' => $data['cover_image'],
            ]);
        }
    }
}
