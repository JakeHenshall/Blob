<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

expect()->extend('toBeOne', fn () => $this->toBe(1));

function actingAsAdmin(): \App\Models\User
{
    $user = \App\Models\User::factory()->admin()->create();
    test()->actingAs($user);

    return $user;
}

function actingAsManager(): \App\Models\User
{
    $user = \App\Models\User::factory()->manager()->create();
    test()->actingAs($user);

    return $user;
}

function actingAsUser(): \App\Models\User
{
    $user = \App\Models\User::factory()->create();
    test()->actingAs($user);

    return $user;
}
