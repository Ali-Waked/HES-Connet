<?php

namespace App\Services;

use App\Models\City;
use App\Models\Facility;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacilityService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {}

    public function paginate(int $perPage = 15, ?string $search = null, ?string $type = null): LengthAwarePaginator
    {

        return Facility::query()->with(['organization', 'parent'])
            ->when(
                $search,
                fn ($query) => $query->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
            )
            ->when(
                $type,
                fn ($query) => $query->where(
                    'facility_type',
                    $type
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Facility
    {
        return DB::transaction(function () use ($data) {

            if (! empty($data['cover_image'])) {
                $data['cover_image'] = $data['cover_image']->store(
                    'facilities/covers',
                    'public'
                );
            }

            if (! empty($data['organization_id'])) {
                $data['organization_id'] = $this->uuid_resolver->resolve(
                    Organization::class,
                    $data['organization_id']
                );
            }
            if (! empty($data['city_id'])) {
                $data['city_id'] = $this->uuid_resolver->resolve(
                    City::class,
                    $data['city_id']
                );
            }

            if (! empty($data['owner_id'])) {
                $data['owner_id'] = $this->uuid_resolver->resolve(
                    User::class,
                    $data['owner_id']
                );
            }

            if (! empty($data['parent_id'])) {
                $data['parent_id'] = $this->uuid_resolver->resolve(
                    Facility::class,
                    $data['parent_id']
                );
            }

            $galleryImages = $data['gallery_images'] ?? [];
            $files = $data['files'] ?? [];

            unset($data['gallery_images'], $data['files']);

            $facility = Facility::create($data);

            foreach ($galleryImages as $image) {
                $path = $image->store('facilities/images', 'public');

                $facility->facilityImages()->create([
                    'image_url' => $path,
                ]);
            }

            foreach ($files as $file) {
                $path = $file->store('facilities/files', 'public');

                $facility->facilityDocuments()->create([
                    'file_url' => $path,
                    'document_type' => $file->getClientOriginalExtension(),
                ]);
            }

            return $facility;
        });
    }

    public function show(Facility $facility): Facility
    {
        return $facility->load([
            'organization',
            'parent',
            'children',
            'facilityImages',
            'facilityDocuments',
        ]);
    }

    public function update(
        Facility $facility,
        array $data
    ): Facility {

        return DB::transaction(function () use ($facility, $data) {

            if (! empty($data['cover_image'])) {
                if ($facility->cover_image) {
                    Storage::disk('public')->delete($facility->cover_image);
                }
                $data['cover_image'] = $data['cover_image']->store(
                    'facilities/covers',
                    'public'
                );
            }

            if (! empty($data['organization_id'])) {
                $data['organization_id'] = $this->uuid_resolver->resolve(
                    Organization::class,
                    $data['organization_id']
                );
            }
            if (! empty($data['city_id'])) {
                $data['city_id'] = $this->uuid_resolver->resolve(
                    City::class,
                    $data['city_id']
                );
            }

            if (! empty($data['owner_id'])) {
                $data['owner_id'] = $this->uuid_resolver->resolve(
                    User::class,
                    $data['owner_id']
                );
            }

            if (! empty($data['parent_id'])) {
                $data['parent_id'] = $this->uuid_resolver->resolve(
                    Facility::class,
                    $data['parent_id']
                );
            }

            $galleryImages = $data['gallery_images'] ?? [];
            $files = $data['files'] ?? [];
            $deletedGalleryImages = $data['deleted_gallery_images'] ?? [];
            $deletedFiles = $data['deleted_files'] ?? [];

            unset(
                $data['gallery_images'],
                $data['files'],
                $data['deleted_gallery_images'],
                $data['deleted_files']
            );

            $facility->update($data);

            $facility->facilityImages()
                ->whereIn('uuid', $deletedGalleryImages)
                ->get()
                ->each(function ($image) {
                    Storage::disk('public')->delete($image->image_url);
                    $image->delete();
                });

            $facility->facilityDocuments()
                ->whereIn('uuid', $deletedFiles)
                ->get()
                ->each(function ($document) {
                    Storage::disk('public')->delete($document->file_url);
                    $document->delete();
                });

            foreach ($galleryImages as $image) {
                $path = $image->store('facilities/images', 'public');

                $facility->facilityImages()->create([
                    'image_url' => $path,
                ]);
            }

            foreach ($files as $file) {
                $path = $file->store('facilities/files', 'public');

                $facility->facilityDocuments()->create([
                    'file_url' => $path,
                    'document_type' => $file->getClientOriginalExtension(),
                ]);
            }

            return $facility->refresh();
        });
    }

    public function destroy(Facility $facility): void
    {
        $facility->delete();
    }

    public function getStats(): array
    {
        $stats = Facility::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")
            ->first();

        return [
            'total' => (int) $stats->total,
            'approved' => (int) $stats->approved,
            'pending' => (int) $stats->pending,
            'rejected' => (int) $stats->rejected,
        ];
    }
}
