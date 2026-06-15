<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FacilityService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }
    public function paginate(int $perPage = 15, ?string $search = null, ?string $type = null): LengthAwarePaginator {

        return Facility::query()->with(['organization','parent',])
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
        if (! empty($data['cover_image'])) {
        $data['cover_image'] = $data['cover_image']->store(
            "facilities/covers",
            'public'
        );
        }
        $data['organization_id'] = $this->uuid_resolver->resolve(
                    Organization::class,
                    $data['organization_id']
                );
        if( isset($data['parent_id'])){
            $data['parent_id'] = $this->uuid_resolver->resolve(
                        Facility::class,
                        $data['parent_id']
                    );
            }
        $facility =  Facility::create($data);
        foreach ($data['gallery_images'] as $image) {
            $path = $image->store("facilities/images", 'public');

            $facility->facilityImages()->create([
                'image_url' => $path,
            ]);
        }
        foreach ($data['files'] as $file) {
            $path = $file->store("facilities/files", 'public');

            $facility->facilityDocuments()->create([
            'file_url' => $path,
            'document_type' => $file->getClientOriginalExtension(),
            ]);
        }
        return $facility;
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

        $facility->update($data);

        return $facility->refresh();
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
