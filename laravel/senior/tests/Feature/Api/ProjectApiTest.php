<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/v1/projects')->assertUnauthorized();
    }

    public function test_manager_can_list_projects(): void
    {
        $manager = User::factory()->manager()->create();
        Project::factory(3)->create();

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'status', 'client' => ['id', 'name']],
                ],
                'meta', 'links',
            ]);
    }

    public function test_user_cannot_see_projects_they_do_not_own(): void
    {
        $user = User::factory()->create();
        $otherOwner = User::factory()->manager()->create();
        Project::factory(2)->create(['owner_id' => $otherOwner->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_complete_task_endpoint_marks_task_done(): void
    {
        $owner = User::factory()->manager()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/v1/tasks/{$task->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        $this->assertNotNull($task->fresh()->completed_at);
    }
}
