<?php

use App\Models\Task;
use App\Models\User;

test('guests cannot see the dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can see their dashboard', function () {
    $user = User::factory()->create();

    Task::factory()->overdue()->create(['assigned_to' => $user->id]);
    Task::factory()->todo()->create(['assigned_to' => $user->id]);

    $this->actingAs($user)->get('/dashboard')->assertOk()
        ->assertSee('Projects by status')
        ->assertSee('Overdue tasks');
});
