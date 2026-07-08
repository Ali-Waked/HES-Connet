<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard\Ai;

use App\Ai\Providers\OpenAiProvider;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $normalUser;

    protected function setUp(): void
    {
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';

        parent::setUp();

        $this->superAdmin = $this->createSuperAdmin();
        $this->normalUser = User::factory()->create();

        $this->mockAiProvider();
    }

    private function mockAiProvider(): void
    {
        $this->mock(OpenAiProvider::class, function ($mock) {
            $mock->shouldReceive('chatWithMessages')
                ->andReturn([
                    'content' => 'This is a mock AI response.',
                    'tool_calls' => [],
                    'tool_results' => [],
                    'prompt_tokens' => 10,
                    'completion_tokens' => 20,
                    'total_tokens' => 30,
                ]);
        });
    }

    // ─── Authorization ─────────────────────────────────────────────────────

    public function test_unauthenticated_cannot_access_ask(): void
    {
        $this->postJson('/api/dashboard/ai/ask', [
            'message' => 'Hello',
        ])->assertUnauthorized();
    }

    public function test_non_super_admin_cannot_ask(): void
    {
        $this->actingAs($this->normalUser, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'message' => 'Hello',
            ])->assertForbidden();
    }

    public function test_unauthenticated_cannot_list_conversations(): void
    {
        $this->getJson('/api/dashboard/ai/conversations')->assertUnauthorized();
    }

    public function test_non_super_admin_cannot_list_conversations(): void
    {
        $this->actingAs($this->normalUser, 'web')
            ->getJson('/api/dashboard/ai/conversations')
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_show_conversation(): void
    {
        $this->getJson('/api/dashboard/ai/conversations/some-uuid')->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_delete_conversation(): void
    {
        $this->deleteJson('/api/dashboard/ai/conversations/some-uuid')->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_rename_conversation(): void
    {
        $this->patchJson('/api/dashboard/ai/conversations/some-uuid', [
            'title' => 'New Title',
        ])->assertUnauthorized();
    }

    // ─── Conversation Creation ─────────────────────────────────────────────

    public function test_creates_new_conversation_when_no_uuid_provided(): void
    {
        $response = $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'message' => 'How many doctors are there?',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'conversation' => ['uuid', 'title', 'language'],
                'assistant' => ['message', 'tools_used'],
            ]);

        $this->assertNotNull($response->json('conversation.uuid'));
        $this->assertEquals('en', $response->json('conversation.language'));
        $this->assertDatabaseHas('ai_conversations', [
            'uuid' => $response->json('conversation.uuid'),
            'user_id' => $this->superAdmin->id,
        ]);
    }

    public function test_creates_conversation_with_arabic_language(): void
    {
        $response = $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'message' => 'كم عدد الأطباء؟',
            ]);

        $response->assertOk();
        $this->assertEquals('ar', $response->json('conversation.language'));
    }

    public function test_new_conversation_has_generated_title(): void
    {
        $response = $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'message' => 'Show me the total number of patients',
            ]);

        $response->assertOk();
        $title = $response->json('conversation.title');
        $this->assertNotNull($title);
        $this->assertLessThanOrEqual(63, mb_strlen($title));
    }

    public function test_ask_returns_mock_response(): void
    {
        $response = $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'message' => 'Hello',
            ]);

        $response->assertOk();
        $this->assertEquals('This is a mock AI response.', $response->json('assistant.message'));
    }

    // ─── Conversation Continuation ─────────────────────────────────────────

    public function test_continues_existing_conversation(): void
    {
        $conversation = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Test Conversation',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'conversation_uuid' => $conversation->uuid,
                'message' => 'What about this month?',
            ]);

        $response->assertOk();
        $this->assertEquals($conversation->uuid, $response->json('conversation.uuid'));
    }

    public function test_cannot_continue_other_users_conversation(): void
    {
        $otherUser = User::factory()->create();
        $conversation = AiConversation::create([
            'user_id' => $otherUser->id,
            'title' => 'Other Conversation',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'conversation_uuid' => $conversation->uuid,
                'message' => 'Hello?',
            ])->assertNotFound();
    }

    public function test_saves_both_user_and_assistant_messages(): void
    {
        $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'message' => 'List all facilities',
            ]);

        $conversation = AiConversation::first();

        $this->assertNotNull($conversation);

        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();
        $this->assertCount(2, $messages);
        $this->assertEquals('user', $messages[0]->role);
        $this->assertEquals('assistant', $messages[1]->role);
    }

    // ─── Conversation Listing ──────────────────────────────────────────────

    public function test_lists_conversations_latest_first(): void
    {
        $old = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Old',
            'language' => 'en',
            'last_message_at' => now()->subDay(),
        ]);

        $recent = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Recent',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->getJson('/api/dashboard/ai/conversations');

        $response->assertOk();
        $this->assertEquals('Recent', $response->json('data.0.title'));
        $this->assertEquals('Old', $response->json('data.1.title'));
    }

    public function test_paginates_conversations(): void
    {
        AiConversation::factory(25)->create([
            'user_id' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->getJson('/api/dashboard/ai/conversations');

        $response->assertOk();
        $this->assertCount(20, $response->json('data'));
        $this->assertEquals(2, $response->json('meta.last_page'));
    }

    public function test_shows_only_own_conversations(): void
    {
        AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Mine',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $otherUser = User::factory()->create();
        AiConversation::create([
            'user_id' => $otherUser->id,
            'title' => 'Theirs',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->getJson('/api/dashboard/ai/conversations');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Mine', $response->json('data.0.title'));
    }

    // ─── Show Conversation ─────────────────────────────────────────────────

    public function test_shows_conversation_with_messages(): void
    {
        $conversation = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Test',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Hello',
        ]);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Hi there!',
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->getJson('/api/dashboard/ai/conversations/'.$conversation->uuid);

        $response->assertOk()
            ->assertJsonPath('data.conversation.uuid', $conversation->uuid);

        $messages = $response->json('data.messages');
        $this->assertCount(2, $messages);
        $this->assertEquals('user', $messages[0]['role']);
        $this->assertEquals('assistant', $messages[1]['role']);
    }

    public function test_cannot_view_other_users_conversation(): void
    {
        $otherUser = User::factory()->create();
        $conversation = AiConversation::create([
            'user_id' => $otherUser->id,
            'title' => 'Private',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->superAdmin, 'web')
            ->getJson('/api/dashboard/ai/conversations/'.$conversation->uuid)
            ->assertNotFound();
    }

    // ─── Delete Conversation ───────────────────────────────────────────────

    public function test_soft_deletes_conversation(): void
    {
        $conversation = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'To Delete',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->superAdmin, 'web')
            ->deleteJson('/api/dashboard/ai/conversations/'.$conversation->uuid)
            ->assertOk()
            ->assertJson(['message' => 'Conversation deleted.']);

        $this->assertSoftDeleted($conversation);
    }

    public function test_cannot_delete_other_users_conversation(): void
    {
        $otherUser = User::factory()->create();
        $conversation = AiConversation::create([
            'user_id' => $otherUser->id,
            'title' => 'Not Mine',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->superAdmin, 'web')
            ->deleteJson('/api/dashboard/ai/conversations/'.$conversation->uuid)
            ->assertNotFound();
    }

    // ─── Rename Conversation ───────────────────────────────────────────────

    public function test_renames_conversation(): void
    {
        $conversation = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Old Title',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->patchJson('/api/dashboard/ai/conversations/'.$conversation->uuid, [
                'title' => 'New Title',
            ]);

        $response->assertOk();
        $this->assertEquals('New Title', $response->json('data.title'));
        $this->assertDatabaseHas('ai_conversations', [
            'uuid' => $conversation->uuid,
            'title' => 'New Title',
        ]);
    }

    public function test_rename_requires_title(): void
    {
        $conversation = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Title',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->superAdmin, 'web')
            ->patchJson('/api/dashboard/ai/conversations/'.$conversation->uuid, [])
            ->assertJsonValidationErrors(['title']);
    }

    public function test_rename_title_max_60_chars(): void
    {
        $conversation = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Title',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->superAdmin, 'web')
            ->patchJson('/api/dashboard/ai/conversations/'.$conversation->uuid, [
                'title' => str_repeat('a', 61),
            ])->assertJsonValidationErrors(['title']);
    }

    public function test_cannot_rename_other_users_conversation(): void
    {
        $otherUser = User::factory()->create();
        $conversation = AiConversation::create([
            'user_id' => $otherUser->id,
            'title' => 'Not Mine',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->superAdmin, 'web')
            ->patchJson('/api/dashboard/ai/conversations/'.$conversation->uuid, [
                'title' => 'Hacked',
            ])->assertNotFound();
    }

    // ─── Validation ────────────────────────────────────────────────────────

    public function test_ask_requires_message(): void
    {
        $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [])
            ->assertJsonValidationErrors(['message']);
    }

    public function test_ask_invalid_conversation_uuid_returns_validation_error(): void
    {
        $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'conversation_uuid' => 'non-existent-uuid',
                'message' => 'Hello',
            ])->assertJsonValidationErrors(['conversation_uuid']);
    }

    // ─── Response Structure ────────────────────────────────────────────────

    public function test_ask_response_structure(): void
    {
        $response = $this->actingAs($this->superAdmin, 'web')
            ->postJson('/api/dashboard/ai/ask', [
                'message' => 'Show me the dashboard stats',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'conversation' => [
                    'uuid',
                    'title',
                    'language',
                ],
                'assistant' => [
                    'message',
                    'tools_used',
                ],
            ]);
    }

    public function test_conversation_list_response_structure(): void
    {
        AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Test',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->getJson('/api/dashboard/ai/conversations');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'language', 'last_message_at', 'created_at'],
                ],
                'meta' => ['current_page', 'last_page', 'total'],
            ]);
    }

    public function test_show_conversation_response_structure(): void
    {
        $conversation = AiConversation::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Test',
            'language' => 'en',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->getJson('/api/dashboard/ai/conversations/'.$conversation->uuid);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'conversation' => ['uuid', 'title', 'language', 'created_at', 'updated_at'],
                    'messages' => [
                        '*' => ['role', 'content', 'created_at'],
                    ],
                ],
            ]);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    private function createSuperAdmin(): User
    {
        return $this->createUserWithRole('super_admin');
    }

    private function createUserWithRole(string $roleName, array $permissions = []): User
    {
        $role = Role::create([
            'name' => ['en' => $roleName, 'ar' => ''],
            'slug' => $roleName,
            'scope' => 'system',
            'uuid' => Str::uuid(),
        ]);

        $role->permissions()->sync([]);

        $user = User::factory()->create();
        $user->systemRoles()->attach($role->id);

        return $user;
    }
}
