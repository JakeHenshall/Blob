<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->client = Client::factory()->for($this->user)->create();
});

test('user can list their projects and filter by status', function () {
    Project::factory()->for($this->client)->create(['name' => 'Active One', 'status' => 'active']);
    Project::factory()->for($this->client)->create(['name' => 'Archived One', 'status' => 'archived']);

    $this->actingAs($this->user)
        ->get(route('projects.index', ['status' => 'active']))
        ->assertOk()
        ->assertSee('Active One')
        ->assertDontSee('Archived One');
});

test('user can create a project', function () {
    $this->actingAs($this->user)
        ->post(route('projects.store'), [
            'client_id' => $this->client->id,
            'name' => 'Website Redesign',
            'status' => 'active',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('projects', [
        'client_id' => $this->client->id,
        'name' => 'Website Redesign',
        'status' => 'active',
    ]);
});

test('project cannot be assigned to another user\'s client', function () {
    $otherClient = Client::factory()->create();

    $this->actingAs($this->user)
        ->post(route('projects.store'), [
            'client_id' => $otherClient->id,
            'name' => 'Sneaky',
            'status' => 'active',
        ])
        ->assertSessionHasErrors('client_id');
});

test('user cannot view another user\'s project', function () {
    $other = Project::factory()->create();

    $this->actingAs($this->user)
        ->get(route('projects.show', $other))
        ->assertForbidden();
});

test('user can delete their project', function () {
    $project = Project::factory()->for($this->client)->create();

    $this->actingAs($this->user)
        ->delete(route('projects.destroy', $project))
        ->assertRedirect(route('projects.index'));

    $this->assertModelMissing($project);
});
