<?php

declare(strict_types=1);

namespace Tests\Feature\Staff;

use App\Models\Article;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    private ?int $defaultCategoryId = null;

    protected function setUp(): void
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');

        parent::setUp();

        Storage::fake('public');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    private function createPermission(string $key): Permission
    {
        $permission = new Permission();
        $permission->uuid = Str::uuid();
        $permission->key = $key;
        $permission->name = ['en' => $key, 'ar' => ''];
        $permission->description = ['en' => '', 'ar' => ''];
        $permission->save();

        return $permission;
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

    private function createCategory(): Category
    {
        return Category::create([
            'uuid' => Str::uuid(),
            'name' => ['en' => 'Health', 'ar' => 'صحة'],
            'type' => 'article',
            'is_active' => true,
        ]);
    }

    private function getDefaultCategoryId(): int
    {
        if ($this->defaultCategoryId === null) {
            $this->defaultCategoryId = $this->createCategory()->id;
        }

        return $this->defaultCategoryId;
    }

    private function createArticle(array $overrides = []): Article
    {
        return Article::create(array_merge([
            'author_id' => User::factory()->create()->id,
            'title' => ['en' => 'Default Title', 'ar' => 'عنوان افتراضي'],
            'content' => ['en' => 'Default content', 'ar' => 'محتوى افتراضي'],
            'status' => 'draft',
            'category_id' => $this->getDefaultCategoryId(),
        ], $overrides));
    }

    private function articleData(array $overrides = []): array
    {
        return array_merge([
            'title' => ['en' => 'Health Tips', 'ar' => 'نصائح صحية'],
            'content' => ['en' => 'Some content here', 'ar' => 'بعض المحتوى هنا'],
            'category_id' => $this->createCategory()->uuid,
            'status' => 'draft',
        ], $overrides);
    }

    // ─── Authorization: super_admin ────────────────────────────────────────

    public function test_super_admin_can_create_article(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/staff/articles', $this->articleData());

        $response->assertCreated()
            ->assertJsonPath('message', 'Article created successfully.')
            ->assertJsonStructure(['data' => ['uuid', 'title', 'content', 'status', 'created_at']]);
    }

    public function test_super_admin_can_list_own_articles(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $this->createArticle(['author_id' => $user->id]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/staff/articles');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_super_admin_can_show_own_article(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $article = $this->createArticle(['author_id' => $user->id]);

        $response = $this->actingAs($user, 'web')
            ->getJson("/api/staff/articles/{$article->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $article->uuid);
    }

    public function test_super_admin_can_update_own_article(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $article = $this->createArticle([
            'author_id' => $user->id,
            'title' => ['en' => 'Old Title', 'ar' => 'عنوان قديم'],
        ]);

        $response = $this->actingAs($user, 'web')
            ->putJson("/api/staff/articles/{$article->uuid}", [
                'title' => ['en' => 'Updated', 'ar' => 'محدث'],
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Article updated successfully.')
            ->assertJsonPath('data.title.en', 'Updated');
    }

    public function test_super_admin_can_delete_own_article(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $article = $this->createArticle(['author_id' => $user->id]);

        $response = $this->actingAs($user, 'web')
            ->deleteJson("/api/staff/articles/{$article->uuid}");

        $response->assertOk()
            ->assertJsonPath('message', 'Article deleted successfully.');
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    // ─── Ownership isolation ──────────────────────────────────────────────

    public function test_cannot_view_another_users_article(): void
    {
        $role = $this->createRoleWithPermissions('staff', ['articles.view', 'articles.manage']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->systemRoles()->attach($role->id);
        $user2->systemRoles()->attach($role->id);

        $article = $this->createArticle(['author_id' => $user2->id]);

        $response = $this->actingAs($user1, 'web')
            ->getJson("/api/staff/articles/{$article->uuid}");

        $response->assertForbidden();
    }

    public function test_cannot_update_another_users_article(): void
    {
        $role = $this->createRoleWithPermissions('staff', ['articles.view', 'articles.manage']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->systemRoles()->attach($role->id);
        $user2->systemRoles()->attach($role->id);

        $article = $this->createArticle(['author_id' => $user2->id]);

        $response = $this->actingAs($user1, 'web')
            ->putJson("/api/staff/articles/{$article->uuid}", [
                'title' => ['en' => 'Hacked', 'ar' => 'مخترق'],
            ]);

        $response->assertForbidden();
    }

    public function test_cannot_delete_another_users_article(): void
    {
        $role = $this->createRoleWithPermissions('staff', ['articles.view', 'articles.manage']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->systemRoles()->attach($role->id);
        $user2->systemRoles()->attach($role->id);

        $article = $this->createArticle(['author_id' => $user2->id]);

        $response = $this->actingAs($user1, 'web')
            ->deleteJson("/api/staff/articles/{$article->uuid}");

        $response->assertForbidden();
    }

    // ─── Permission gating ─────────────────────────────────────────────────

    public function test_cannot_list_without_view_permission(): void
    {
        $user = $this->createUserWithRole('staff', []);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/staff/articles');

        $response->assertForbidden();
    }

    public function test_cannot_create_without_manage_permission(): void
    {
        $user = $this->createUserWithRole('staff', ['articles.view']);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/staff/articles', $this->articleData());

        $response->assertForbidden();
    }

    // ─── Unauthenticated ───────────────────────────────────────────────────

    public function test_unauthenticated_cannot_access_any_endpoint(): void
    {
        $this->getJson('/api/staff/articles')->assertUnauthorized();
        $this->getJson('/api/staff/articles/' . Str::uuid())->assertUnauthorized();
        $this->postJson('/api/staff/articles', $this->articleData())->assertUnauthorized();
        $this->putJson('/api/staff/articles/' . Str::uuid(), ['title' => ['en' => 'X', 'ar' => 'ي']])->assertUnauthorized();
        $this->deleteJson('/api/staff/articles/' . Str::uuid())->assertUnauthorized();
    }

    // ─── CRUD Operations ───────────────────────────────────────────────────

    public function test_create_article_sets_default_status_to_draft(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/staff/articles', $this->articleData());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_create_article_with_pending_review_status(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/staff/articles', $this->articleData([
                'status' => 'pending_review',
            ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending_review');
    }

    public function test_create_article_with_cover_image(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $file = UploadedFile::fake()->image('cover.jpg', 800, 600);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/staff/articles', $this->articleData([
                'cover_image' => $file,
            ]));

        $response->assertCreated();
        $article = Article::first();
        $this->assertNotNull($article->getRawOriginal('cover_image'));
        Storage::disk('public')->assertExists($article->getRawOriginal('cover_image'));
    }

    // ─── Listing & Search & Pagination ─────────────────────────────────────

    public function test_list_articles_only_returns_own_articles(): void
    {
        $role = $this->createRoleWithPermissions('super_admin', []);
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->systemRoles()->attach($role->id);
        $other->systemRoles()->attach($role->id);

        $this->createArticle(['author_id' => $user->id]);
        $this->createArticle(['author_id' => $other->id]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/staff/articles');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_articles_paginates(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $this->createArticle(['author_id' => $user->id]);
        $this->createArticle(['author_id' => $user->id]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/staff/articles?per_page=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_list_articles_filters_by_status(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $this->createArticle(['author_id' => $user->id, 'status' => 'draft']);
        $this->createArticle(['author_id' => $user->id, 'status' => 'published']);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/staff/articles?status=published');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'published');
    }

    public function test_list_articles_searches_by_title(): void
    {
        $user = $this->createUserWithRole('super_admin', []);
        $this->createArticle([
            'author_id' => $user->id,
            'title' => ['en' => 'Health Tips', 'ar' => 'نصائح صحية'],
        ]);
        $this->createArticle([
            'author_id' => $user->id,
            'title' => ['en' => 'Fitness Guide', 'ar' => 'دليل اللياقة'],
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/staff/articles?search=health');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ─── Validation ────────────────────────────────────────────────────────

    public function test_store_validates_required_fields(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/staff/articles', []);

        $response->assertJsonValidationErrors(['title', 'title.en', 'title.ar', 'content', 'content.en', 'content.ar', 'category_id']);
    }

    public function test_store_rejects_invalid_status(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/staff/articles', $this->articleData([
                'status' => 'published',
            ]));

        $response->assertJsonValidationErrors(['status']);
    }

    public function test_show_returns_404_for_nonexistent_uuid(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/staff/articles/' . Str::uuid());

        $response->assertNotFound();
    }

    // ─── Update article with image replacement ─────────────────────────────

    public function test_update_article_replaces_cover_image(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('articles/cover', 'public');

        $article = $this->createArticle([
            'author_id' => $user->id,
            'cover_image' => $oldPath,
        ]);

        $newFile = UploadedFile::fake()->image('new.jpg', 800, 600);

        $response = $this->actingAs($user, 'web')
            ->putJson("/api/staff/articles/{$article->uuid}", [
                'cover_image' => $newFile,
            ]);

        $response->assertOk();
        $article->refresh();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($article->getRawOriginal('cover_image'));
    }

    // ─── Delete with image cleanup ─────────────────────────────────────────

    public function test_delete_article_removes_cover_image(): void
    {
        $user = $this->createUserWithRole('super_admin', []);

        $file = UploadedFile::fake()->image('cover.jpg');
        $path = $file->store('articles/cover', 'public');

        $article = $this->createArticle([
            'author_id' => $user->id,
            'cover_image' => $path,
        ]);

        $this->actingAs($user, 'web')
            ->deleteJson("/api/staff/articles/{$article->uuid}")
            ->assertOk();

        Storage::disk('public')->assertMissing($path);
    }
}
