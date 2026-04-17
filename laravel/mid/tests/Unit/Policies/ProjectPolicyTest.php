<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ProjectPolicy;
});

it('allows admins to update any project', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create();

    expect($this->policy->update($admin, $project))->toBeTrue();
});

it('allows a project owner to update their own project', function () {
    $owner = User::factory()->manager()->create();
    $project = Project::factory()->for($owner, 'owner')->create();

    expect($this->policy->update($owner, $project))->toBeTrue();
});

it('denies updates to other managers', function () {
    $owner = User::factory()->manager()->create();
    $other = User::factory()->manager()->create();
    $project = Project::factory()->for($owner, 'owner')->create();

    expect($this->policy->update($other, $project))->toBeFalse();
});

it('allows users to view projects they own or are assigned tasks on', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $project = Project::factory()->for($other, 'owner')->create();
    Task::factory()->for($project)->create(['assigned_to' => $user->id]);

    expect($this->policy->view($user, $project))->toBeTrue();
});
