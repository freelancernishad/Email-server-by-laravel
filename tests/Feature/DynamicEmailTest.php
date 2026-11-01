<?php

namespace Tests\Feature;

use App\Models\EmailConfiguration;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DynamicEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_send_email_with_dynamic_configuration()
    {
        Mail::fake();

        // Create a dummy configuration
        $config = EmailConfiguration::create([
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'user@example.com',
            'password' => 'secret',
            'encryption' => 'tls',
            'from_address' => 'sender@example.com',
            'from_name' => 'Test Sender',
            'reply_to_address' => 'default-reply@example.com',
            'reply_to_name' => 'Support Desk',
        ]);

        // Payload
        $payload = [
            'config_key' => $config->key,
            'to' => 'recipient@example.com',
            'subject' => 'Test Subject',
            'body' => '<h1>Hello World</h1>',
        ];

        // Hit the API
        $response = $this->postJson('/api/send-email', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('email_logs', [
            'config_key' => $config->key,
            'to_email' => 'recipient@example.com',
            'reply_to' => 'default-reply@example.com',
            'status' => 'success',
        ]);
    }

    public function test_it_can_override_from_address_and_reply_to_and_send_cc_bcc()
    {
        Mail::fake();

        $config = EmailConfiguration::create([
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'test_user',
            'password' => 'test_pass',
            'encryption' => 'tls',
            'from_address' => 'default@example.com',
            'from_name' => 'Default Name',
            'reply_to_address' => 'old-reply@example.com',
            'reply_to_name' => 'Old Reply',
        ]);

        $payload = [
            'config_key' => $config->key,
            'to' => 'recipient1@example.com, recipient2@example.com',
            'subject' => 'Override Test',
            'body' => 'Testing override',
            'from_email' => 'custom@example.com',
            'from_name' => 'Custom Name',
            'reply_to' => 'custom-reply@example.com',
            'reply_to_name' => 'Custom Reply Name',
            'cc' => 'cc1@example.com, cc2@example.com',
            'bcc' => 'bcc1@example.com',
        ];

        $response = $this->postJson('/api/send-email', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('email_logs', [
            'config_key' => $config->key,
            'from_email' => 'custom@example.com',
            'to_email' => 'recipient1@example.com, recipient2@example.com',
            'reply_to' => 'custom-reply@example.com',
            'cc' => 'cc1@example.com, cc2@example.com',
            'bcc' => 'bcc1@example.com',
            'status' => 'success',
        ]);
    }

    public function test_it_can_create_and_update_configuration_with_reply_to()
    {
        $user = User::factory()->create();

        $createPayload = [
            'type' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'john@example.com',
            'password' => 'secret123',
            'encryption' => 'tls',
            'from_address' => 'sender@example.com',
            'from_name' => 'My Sender',
            'reply_to_address' => 'reply@example.com',
            'reply_to_name' => 'Help Center',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/email-config', $createPayload);
        $response->assertStatus(201);
        $configId = $response->json('data.id');

        $this->assertDatabaseHas('email_configurations', [
            'id' => $configId,
            'reply_to_address' => 'reply@example.com',
            'reply_to_name' => 'Help Center',
        ]);

        $updatePayload = array_merge($createPayload, [
            'reply_to_address' => 'updated-reply@example.com',
            'reply_to_name' => 'Support Team',
        ]);

        $updateResponse = $this->actingAs($user, 'sanctum')->putJson("/api/email-config/{$configId}", $updatePayload);
        $updateResponse->assertStatus(200);

        $this->assertDatabaseHas('email_configurations', [
            'id' => $configId,
            'reply_to_address' => 'updated-reply@example.com',
            'reply_to_name' => 'Support Team',
        ]);
    }
}
