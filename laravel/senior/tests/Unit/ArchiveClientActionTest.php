<?php

namespace Tests\Unit;

use App\Actions\Clients\ArchiveClientAction;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Support\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveClientActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_archives_client_and_archives_active_projects(): void
    {
        $actor = User::factory()->manager()->create();
        $client = Client::factory()->create(['owner_id' => $actor->id]);
        $active = Project::factory()->create([
            'client_id' => $client->id,
            'owner_id' => $actor->id,
            'status' => ProjectStatus::Active,
        ]);
        $completed = Project::factory()->create([
            'client_id' => $client->id,
            'owner_id' => $actor->id,
            'status' => ProjectStatus::Completed,
        ]);

        app(ArchiveClientAction::class)->execute($actor, $client);

        $this->assertNotNull($client->fresh()->archived_at);
        $this->assertEquals(ProjectStatus::Archived, $active->fresh()->status);
        $this->assertEquals(ProjectStatus::Completed, $completed->fresh()->status);
    }

    public function test_already_archived_client_is_not_re_archived(): void
    {
        $actor = User::factory()->admin()->create();
        $client = Client::factory()->archived()->create();
        $archivedAt = $client->archived_at;

        $result = app(ArchiveClientAction::class)->execute($actor, $client);

        $this->assertTrue($archivedAt->equalTo($result->archived_at));
    }
}
