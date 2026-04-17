<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectCreatedNotification;
use Illuminate\Support\Facades\Notification;

test('managers can create projects and queue notifications', function () {
    Notification::fake();

    $manager = actingAsManager();
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $response = $this->post(route('projects.store'), [
        'client_id' => $client->id,
        'name' => 'Website revamp',
        'description' => 'A new website for the client.',
        'status' => 'active',
        'starts_at' => now()->toDateString(),
        'due_at' => now()->addMonth()->toDateString(),
    ]);

    $response->assertRedirect();
    $project = Project::firstWhere('name', 'Website revamp');

    expect($project)->not->toBeNull()
        ->and($project->user_id)->toBe($manager->id);

    Notification::assertSentTo($admin, ProjectCreatedNotification::class);
    Notification::assertNotSentTo($manager, ProjectCreatedNotification::class);

    $this->assertDatabaseHas('activities', [
        'action' => 'project.created',
        'subject_type' => (new Project)->getMorphClass(),
        'subject_id' => $project->id,
    ]);
});

test('non-owners cannot edit a project', function () {
    $owner = User::factory()->manager()->create();
    $other = User::factory()->manager()->create();
    $project = Project::factory()->for($owner, 'owner')->create();

    $this->actingAs($other)
        ->patch(route('projects.update', $project), [
            'client_id' => $project->client_id,
            'name' => 'Hacked',
            'status' => 'active',
        ])->assertForbidden();
});

test('filtering projects by status works', function () {
    actingAsAdmin();

    Project::factory()->active()->create(['name' => 'Active one']);
    Project::factory()->completed()->create(['name' => 'Done one']);

    $this->get(route('projects.index', ['status' => 'active']))
        ->assertOk()
        ->assertSee('Active one')
        ->assertDontSee('Done one');
});

test('notes can be added to a project by viewers', function () {
    $user = actingAsUser();
    $project = Project::factory()->create();
    $project->tasks()->create([
        'title' => 'Task',
        'status' => 'todo',
        'priority' => 'medium',
        'assigned_to' => $user->id,
    ]);

    $response = $this->post(route('projects.notes.store', $project), [
        'body' => 'This is a note.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('notes', [
        'project_id' => $project->id,
        'user_id' => $user->id,
        'body' => 'This is a note.',
    ]);
});
