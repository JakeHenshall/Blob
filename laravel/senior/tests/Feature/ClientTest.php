<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Support\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_client(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->post('/clients', [
                'name' => 'Acme Ltd',
                'contact_email' => 'hello@acme.test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'name' => 'Acme Ltd',
            'owner_id' => $manager->id,
        ]);
    }

    public function test_regular_user_cannot_create_client(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $this->actingAs($user)
            ->post('/clients', ['name' => 'Acme Ltd'])
            ->assertForbidden();
    }

    public function test_user_cannot_view_other_users_client(): void
    {
        $owner = User::factory()->manager()->create();
        $other = User::factory()->create(['role' => Role::User]);
        $client = Client::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($other)->get("/clients/{$client->id}")->assertForbidden();
    }

    public function test_admin_can_view_any_client(): void
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();

        $this->actingAs($admin)->get("/clients/{$client->id}")->assertOk();
    }
}
