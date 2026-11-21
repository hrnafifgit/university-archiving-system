<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->withSession([
            'logged_user_id' => '00000000-0000-0000-0000-000000000001',
            'logged_username' => 'admin',
            'logged_role_name' => 'مدير النظام'
        ])->get('/');

        $response->assertStatus(200);
    }
}
