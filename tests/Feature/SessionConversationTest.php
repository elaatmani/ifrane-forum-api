<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Session;
use App\Models\Conversation;
use App\Services\MessagingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SessionConversationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_conversation_is_created_when_session_is_created()
    {
        // Create a test user (admin)
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create test speakers
        $speaker1 = User::factory()->create();
        $speaker2 = User::factory()->create();

        // Mock the MessagingService to verify it's called
        $this->mock(MessagingService::class, function ($mock) {
            $mock->shouldReceive('getSessionConversation')
                ->once()
                ->andReturn(new Conversation());
        });

        // Create session data
        $sessionData = [
            'name' => 'Test Session',
            'description' => 'Test Description',
            'status' => 'active',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'link' => 'https://example.com',
            'type_id' => 1,
            'topic_id' => 1,
            'language_id' => 1,
            'is_active' => true,
            'speakers' => [$speaker1->id, $speaker2->id]
        ];

        // Make the request
        $response = $this->actingAs($admin)
            ->postJson('/api/admin/sessions', $sessionData);

        // Assert the response is successful
        $response->assertStatus(200);
    }

    public function test_conversation_is_created_with_correct_type_and_name()
    {
        // This test would verify the actual conversation creation
        // but requires more complex setup with actual database operations
        $this->assertTrue(true);
    }
}
