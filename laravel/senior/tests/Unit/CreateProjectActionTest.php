<?php

namespace Tests\Unit;

use App\Actions\Projects\CreateProjectAction;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_unique_slug_per_client(): void
    {
        $actor = User::factory()->manager()->create();
        $client = Client::factory()->create(['owner_id' => $actor->id]);

        $first = app(CreateProjectAction::class)->execute($actor, $client, ['name' => 'Website']);
        $second = app(CreateProjectAction::class)->execute($actor, $client, ['name' => 'Website']);

        $this->assertNotEquals($first->slug, $second->slug);
        $this->assertSame('website', $first->slug);
        $this->assertSame('website-2', $second->slug);
    }

    public function test_records_activity(): void
    {
        $actor = User::factory()->manager()->create();
        $client = Client::factory()->create(['owner_id' => $actor->id]);

        app(CreateProjectAction::class)->execute($actor, $client, ['name' => 'Website']);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $actor->id,
            'event' => 'project.created',
        ]);
    }
}
