<?php

declare(strict_types=1);

namespace Tests\Feature\Patient;

use App\Enums\AccountStatus;
use App\Models\AiMedicalConversation;
use App\Models\AiMedicalMessage;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Role;
use App\Models\Specialization;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiConversationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';

        parent::setUp();
    }

    public function test_unauthenticated_cannot_access_conversations(): void
    {
        $this->getJson('/api/patient/ai/conversations')->assertUnauthorized();
        $this->postJson('/api/patient/ai/conversations')->assertUnauthorized();
    }

    public function test_returns_empty_conversations_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/patient/ai/conversations');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_creates_new_conversation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/patient/ai/conversations');

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['uuid', 'title', 'status', 'message_count'],
            ]);

        $this->assertEquals('active', $response->json('data.status'));
        $this->assertEquals(0, $response->json('data.message_count'));
    }

    public function test_creates_conversation_with_custom_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/patient/ai/conversations', [
                'title' => 'My Health Question',
            ]);

        $response->assertCreated();
        $this->assertEquals('My Health Question', $response->json('data.title'));
    }

    public function test_shows_conversation(): void
    {
        $user = User::factory()->create();

        $conversation = AiMedicalConversation::create([
            'user_id' => $user->id,
            'title' => 'Test Conversation',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/patient/ai/conversations/'.$conversation->uuid);

        $response->assertOk()
            ->assertJsonPath('data.conversation.uuid', $conversation->uuid)
            ->assertJsonPath('data.conversation.title', 'Test Conversation')
            ->assertJsonCount(0, 'data.messages.data');
    }

    public function test_cannot_view_other_users_conversation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = AiMedicalConversation::create([
            'user_id' => $user1->id,
            'title' => 'Private',
            'status' => 'active',
        ]);

        $this->actingAs($user2, 'web')
            ->getJson('/api/patient/ai/conversations/'.$conversation->uuid)
            ->assertNotFound();
    }

    public function test_updates_conversation_title(): void
    {
        $user = User::factory()->create();

        $conversation = AiMedicalConversation::create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'web')
            ->putJson('/api/patient/ai/conversations/'.$conversation->uuid, [
                'title' => 'Updated Title',
            ]);

        $response->assertOk();
        $this->assertEquals('Updated Title', $response->json('data.title'));
    }

    public function test_deletes_conversation(): void
    {
        $user = User::factory()->create();

        $conversation = AiMedicalConversation::create([
            'user_id' => $user->id,
            'title' => 'To Delete',
            'status' => 'active',
        ]);

        $this->actingAs($user, 'web')
            ->deleteJson('/api/patient/ai/conversations/'.$conversation->uuid)
            ->assertOk();

        $this->assertNull($conversation->fresh());
    }

    public function test_lists_multiple_conversations_ordered_by_activity(): void
    {
        $user = User::factory()->create();

        $old = AiMedicalConversation::create([
            'user_id' => $user->id,
            'title' => 'Old',
            'status' => 'active',
            'last_activity_at' => now()->subDay(),
        ]);

        $recent = AiMedicalConversation::create([
            'user_id' => $user->id,
            'title' => 'Recent',
            'status' => 'active',
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/patient/ai/conversations');

        $response->assertOk();
        $this->assertEquals('Recent', $response->json('data.0.title'));
        $this->assertEquals('Old', $response->json('data.1.title'));
    }

    public function test_show_conversation_with_messages(): void
    {
        $user = User::factory()->create();

        $conversation = AiMedicalConversation::create([
            'user_id' => $user->id,
            'title' => 'Messages Test',
            'status' => 'active',
        ]);

        AiMedicalMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Hello',
        ]);

        AiMedicalMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Hi there',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/patient/ai/conversations/'.$conversation->uuid);

        $response->assertOk();
        $messages = $response->json('data.messages.data');
        $this->assertCount(2, $messages);
        $this->assertEquals('user', $messages[0]['role']);
        $this->assertEquals('assistant', $messages[1]['role']);
    }

    public function test_any_authenticated_user_can_chat(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson('/api/patient/ai/conversations')
            ->assertCreated();

        $this->assertDatabaseHas('ai_medical_conversations', ['user_id' => $user->id]);
    }

    public function test_recommend_doctor_successfully(): void
    {
        $user = User::factory()->create();

        // 1. Create a Specialization
        $specialization = Specialization::create([
            'name' => [
                'en' => 'General Practice',
                'ar' => 'طب عام',
            ],
            'description' => [
                'en' => 'General health care',
                'ar' => 'رعاية صحية عامة',
            ],
        ]);

        // 2. Create a Role
        $role = Role::create([
            'name' => [
                'en' => 'Doctor Portal User',
                'ar' => 'مستخدم بوابة الطبيب',
            ],
            'slug' => 'doctor_portal_user',
            'scope' => 'facility',
            'is_active' => true,
        ]);

        // 3. Create a Facility
        $facility = Facility::factory()->create([
            'name' => [
                'en' => 'City Clinic',
                'ar' => 'عيادة المدينة',
            ],
        ]);

        // 4. Create Staff (Doctor)
        $staffUser = User::factory()->create();
        $staff = Staff::factory()->create([
            'user_id' => $staffUser->id,
            'specialization_id' => $specialization->id,
            'status' => AccountStatus::ACTIVE,
        ]);

        // 5. Link Staff to Facility
        FacilityStaff::create([
            'staff_id' => $staff->id,
            'facility_id' => $facility->id,
            'role_id' => $role->id,
            'joined_at' => now(),
            'ended_at' => null,
        ]);

        // 6. Create conversation
        $conversation = AiMedicalConversation::create([
            'user_id' => $user->id,
            'title' => 'Triage Conversation',
            'status' => 'active',
            'message_count' => 4,
            'estimated_specialty' => 'General Practice',
            'triage_status' => 'ready',
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson("/api/patient/ai/conversations/{$conversation->uuid}/recommend-doctor");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'triage' => ['specialty', 'urgency', 'confidence', 'symptoms'],
                    'doctors',
                ],
            ]);

        $this->assertCount(1, $response->json('data.doctors'));
        $this->assertEquals('City Clinic', $response->json('data.doctors.0.facility.name'));
    }
}
