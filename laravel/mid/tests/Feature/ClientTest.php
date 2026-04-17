<?php

use App\Models\Client;
use App\Models\User;

test('regular users cannot create clients', function () {
    actingAsUser();

    $this->post(route('clients.store'), [
        'name' => 'Acme',
    ])->assertForbidden();
});

test('managers can create clients and activity is logged', function () {
    $manager = actingAsManager();

    $response = $this->post(route('clients.store'), [
        'name' => 'Acme Corp',
        'company' => 'Acme',
        'email' => 'hi@acme.test',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('clients', [
        'name' => 'Acme Corp',
        'user_id' => $manager->id,
    ]);
    $this->assertDatabaseHas('activities', [
        'action' => 'client.created',
        'causer_id' => $manager->id,
    ]);
});

test('non-owners cannot view another regular user\'s client', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $client = Client::factory()->for($owner, 'owner')->create();

    $this->actingAs($intruder)->get(route('clients.show', $client))->assertForbidden();
});

test('admin can view any client', function () {
    actingAsAdmin();
    $client = Client::factory()->create();

    $this->get(route('clients.show', $client))->assertOk();
});

test('clients list supports search', function () {
    actingAsAdmin();
    Client::factory()->create(['name' => 'Zanzibar Ltd']);
    Client::factory()->create(['name' => 'Acme']);

    $this->get(route('clients.index', ['q' => 'Zanzibar']))
        ->assertOk()
        ->assertSee('Zanzibar Ltd')
        ->assertDontSee('Acme');
});
