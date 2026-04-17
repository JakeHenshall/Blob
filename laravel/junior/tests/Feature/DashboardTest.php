<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('guests are redirected from dashboard to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated user sees dashboard stats', function () {
    $user = User::factory()->create();

    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create(['status' => 'active']);
    Task::factory()->count(3)->for($project)->create(['status' => 'todo']);
    Task::factory()->for($project)->done()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Clients')
        ->assertSee('Tasks')
        ->assertSee($project->name);
});
