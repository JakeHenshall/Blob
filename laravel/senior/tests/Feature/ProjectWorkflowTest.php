<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Support\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProjectWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_project_under_client(): void
    {
        $manager = User::factory()->manager()->create();
        $client = Client::factory()->create(['owner_id' => $manager->id]);

        $this->actingAs($manager)
            ->post("/clients/{$client->id}/projects", [
                'name' => 'Website rebuild',
                'description' => 'Work to migrate CMS.',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'client_id' => $client->id,
            'name' => 'Website rebuild',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'project.created',
        ]);
    }

    public function test_assignment_notifies_assignee(): void
    {
        Notification::fake();

        $owner = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post("/tasks/{$task->id}/assign", ['assignee_id' => $assignee->id])
            ->assertRedirect();

        $this->assertEquals($assignee->id, $task->fresh()->assignee_id);
        Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    }

    public function test_completing_task_sets_completed_at_and_status(): void
    {
        $owner = User::factory()->manager()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post("/tasks/{$task->id}/complete")
            ->assertRedirect();

        $fresh = $task->fresh();
        $this->assertEquals(TaskStatus::Done, $fresh->status);
        $this->assertNotNull($fresh->completed_at);
    }
}
