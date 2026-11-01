<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_all_dashboard_views_render(): void
    {
        $this->get('/dashboard')->assertStatus(200);
        $this->get('/dashboard/test-email')->assertStatus(200);
        $this->get('/dashboard/logs')->assertStatus(200);
        $this->get('/dashboard/docs')->assertStatus(200);
        $this->get('/dashboard/users')->assertStatus(200);
    }
}
