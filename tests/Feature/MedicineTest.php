<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MedicineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        $this->artisan('migrate', ['--force' => true]);

        Storage::fake('public');
    }

    private function createPermission(string $key): Permission
    {
        return Permission::create([
            'key' => $key,
            'uuid' => Str::uuid(),
            'name' => ['en' => $key, 'ar' => ''],
            'description' => ['en' => '', 'ar' => ''],
        ]);
    }

    private function createRoleWithPermissions(string $roleName, array $permissionKeys): Role
    {
        $role = Role::create([
            'name' => ['en' => $roleName, 'ar' => ''],
            'slug' => $roleName,
            'scope' => 'facility',
            'uuid' => Str::uuid(),
        ]);

        $permissions = array_map(fn (string $key) => $this->createPermission($key)->id, $permissionKeys);
        $role->permissions()->sync($permissions);

        return $role;
    }

    private function createUserWithRole(string $roleName, array $permissions = []): User
    {
        $role = $this->createRoleWithPermissions($roleName, $permissions);
        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);

        return $user;
    }

    private function medicineData(array $overrides = []): array
    {
        return array_merge([
            'name' => ['en' => 'Panadol', 'ar' => 'بانادول'],
            'description' => ['en' => 'Pain relief', 'ar' => 'مسكن ألم'],
        ], $overrides);
    }

    // ─── Authorization: super_admin ───────────────────────────────────────

    public function test_super_admin_can_create_medicine(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/medicines', $this->medicineData());

        $response->assertCreated()
            ->assertJsonPath('message', 'Medicine created successfully.')
            ->assertJsonStructure(['data' => ['uuid', 'name', 'description', 'image_url', 'created_at']]);
    }

    public function test_super_admin_can_list_medicines(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        Medicine::create(['name' => ['en' => 'Test', 'ar' => 'تجربة']]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/medicines');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_super_admin_can_update_medicine(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $medicine = Medicine::create(['name' => ['en' => 'Old', 'ar' => 'قديم']]);

        $response = $this->actingAs($user, 'web')
            ->putJson("/api/medicines/{$medicine->uuid}", [
                'name' => ['en' => 'Updated', 'ar' => 'محدث'],
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Medicine updated successfully.');
    }

    public function test_super_admin_can_delete_medicine(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $medicine = Medicine::create(['name' => ['en' => 'Test', 'ar' => 'تجربة']]);

        $response = $this->actingAs($user, 'web')
            ->deleteJson("/api/medicines/{$medicine->uuid}");

        $response->assertOk()
            ->assertJsonPath('message', 'Medicine deleted successfully.');
        $this->assertDatabaseMissing('medicines', ['id' => $medicine->id]);
    }

    // ─── Authorization: facility_owner ────────────────────────────────────

    public function test_facility_owner_can_manage_medicines(): void
    {
        $this->createRoleWithPermissions('facility_owner', ['medicines.manage', 'medicines.view']);
        $role = Role::where('slug', 'facility_owner')->first();
        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);

        $createResponse = $this->actingAs($user, 'web')
            ->postJson('/api/medicines', $this->medicineData());
        $createResponse->assertCreated();

        $medicine = Medicine::first();

        $updateResponse = $this->actingAs($user, 'web')
            ->putJson("/api/medicines/{$medicine->uuid}", [
                'name' => ['en' => 'Updated', 'ar' => 'محدث'],
            ]);
        $updateResponse->assertOk();

        $deleteResponse = $this->actingAs($user, 'web')
            ->deleteJson("/api/medicines/{$medicine->uuid}");
        $deleteResponse->assertOk();
    }

    // ─── Authorization: doctor (view only) ────────────────────────────────

    public function test_doctor_can_view_medicines(): void
    {
        $this->createRoleWithPermissions('doctor', ['medicines.view']);
        $role = Role::where('slug', 'doctor')->first();
        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);
        Medicine::create(['name' => ['en' => 'Test', 'ar' => 'تجربة']]);

        $listResponse = $this->actingAs($user, 'web')->getJson('/api/medicines');
        $listResponse->assertOk();

        $medicine = Medicine::first();
        $showResponse = $this->actingAs($user, 'web')
            ->getJson("/api/medicines/{$medicine->uuid}");
        $showResponse->assertOk();
    }

    public function test_doctor_cannot_create_medicine(): void
    {
        $this->createRoleWithPermissions('doctor', ['medicines.view']);
        $role = Role::where('slug', 'doctor')->first();
        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/medicines', $this->medicineData());

        $response->assertForbidden();
    }

    public function test_doctor_cannot_update_medicine(): void
    {
        $this->createRoleWithPermissions('doctor', ['medicines.view']);
        $role = Role::where('slug', 'doctor')->first();
        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);
        $medicine = Medicine::create(['name' => ['en' => 'Test', 'ar' => 'تجربة']]);

        $response = $this->actingAs($user, 'web')
            ->putJson("/api/medicines/{$medicine->uuid}", [
                'name' => ['en' => 'Hacked', 'ar' => 'مخترق'],
            ]);

        $response->assertForbidden();
    }

    public function test_doctor_cannot_delete_medicine(): void
    {
        $this->createRoleWithPermissions('doctor', ['medicines.view']);
        $role = Role::where('slug', 'doctor')->first();
        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);
        $medicine = Medicine::create(['name' => ['en' => 'Test', 'ar' => 'تجربة']]);

        $response = $this->actingAs($user, 'web')
            ->deleteJson("/api/medicines/{$medicine->uuid}");

        $response->assertForbidden();
    }

    // ─── Unauthenticated ──────────────────────────────────────────────────

    public function test_unauthenticated_cannot_access_any_endpoint(): void
    {
        $this->getJson('/api/medicines')->assertUnauthorized();
        $this->getJson('/api/medicines/lookup')->assertUnauthorized();
        $this->postJson('/api/medicines', $this->medicineData())->assertUnauthorized();
        $this->putJson('/api/medicines/some-uuid', ['name' => ['en' => 'X', 'ar' => 'ي']])->assertUnauthorized();
        $this->deleteJson('/api/medicines/some-uuid')->assertUnauthorized();
    }

    // ─── CRUD Operations ──────────────────────────────────────────────────

    public function test_create_medicine_with_image(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $file = UploadedFile::fake()->image('panadol.jpg', 200, 200);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/medicines', $this->medicineData([
                'image' => $file,
            ]));

        $response->assertCreated();

        $medicine = Medicine::first();
        $this->assertNotNull($medicine->getRawOriginal('image_url'));
        Storage::disk('public')->assertExists($medicine->getRawOriginal('image_url'));
    }

    public function test_update_medicine_replaces_image(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('medicines', 'public');

        $medicine = Medicine::create([
            'name' => ['en' => 'Test', 'ar' => 'تجربة'],
            'image_url' => $oldPath,
        ]);

        $newFile = UploadedFile::fake()->image('new.jpg', 300, 300);

        $response = $this->actingAs($user, 'web')
            ->putJson("/api/medicines/{$medicine->uuid}", [
                'image' => $newFile,
            ]);

        $response->assertOk();
        $medicine->refresh();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($medicine->getRawOriginal('image_url'));
    }

    public function test_delete_medicine_removes_image(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $file = UploadedFile::fake()->image('med.jpg');
        $path = $file->store('medicines', 'public');

        $medicine = Medicine::create([
            'name' => ['en' => 'Test', 'ar' => 'تجربة'],
            'image_url' => $path,
        ]);

        $this->actingAs($user, 'web')
            ->deleteJson("/api/medicines/{$medicine->uuid}")
            ->assertOk();

        Storage::disk('public')->assertMissing($path);
    }

    // ─── Listing & Search & Lookup ────────────────────────────────────────

    public function test_list_medicines_paginates(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        Medicine::create(['name' => ['en' => 'A', 'ar' => 'أ']]);
        Medicine::create(['name' => ['en' => 'B', 'ar' => 'ب']]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/medicines?per_page=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_list_medicines_searches_by_name(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        Medicine::create(['name' => ['en' => 'Panadol', 'ar' => 'بانادول']]);
        Medicine::create(['name' => ['en' => 'Aspirin', 'ar' => 'أسبرين']]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/medicines?search=panadol');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_medicines_sorts_by_name(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        Medicine::create(['name' => ['en' => 'Beta', 'ar' => 'بيتا']]);
        Medicine::create(['name' => ['en' => 'Alpha', 'ar' => 'ألفا']]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/medicines?sort_by=name&sort_order=asc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name.en')->toArray();
        $this->assertSame(['Alpha', 'Beta'], $names);
    }

    public function test_lookup_returns_minimal_data(): void
    {
        $this->createRoleWithPermissions('doctor', ['medicines.view']);
        $role = Role::where('slug', 'doctor')->first();
        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);
        Medicine::create(['name' => ['en' => 'Panadol Extra', 'ar' => 'بانادول إكسترا']]);
        Medicine::create(['name' => ['en' => 'Aspirin', 'ar' => 'أسبرين']]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/medicines/lookup?search=panadol');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonStructure([['uuid', 'name']]);
    }

    // ─── Validation ───────────────────────────────────────────────────────

    public function test_store_validates_required_fields(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/medicines', []);

        $response->assertJsonValidationErrors(['name', 'name.en', 'name.ar']);
    }

    public function test_show_returns_medicine(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $medicine = Medicine::create([
            'name' => ['en' => 'Panadol', 'ar' => 'بانادول'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson("/api/medicines/{$medicine->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $medicine->uuid)
            ->assertJsonPath('data.name.en', 'Panadol')
            ->assertJsonPath('data.description.en', 'Desc');
    }

    public function test_show_returns_404_for_nonexistent_uuid(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/medicines/' . Str::uuid());

        $response->assertNotFound();
    }
}
