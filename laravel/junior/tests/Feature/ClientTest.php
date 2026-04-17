<?php

use App\Models\Client;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can view their clients list', function () {
    $mine = Client::factory()->for($this->user)->create(['name' => 'Acme Co']);
    $other = Client::factory()->create(['name' => 'Someone Else']);

    $this->actingAs($this->user)
        ->get(route('clients.index'))
        ->assertOk()
        ->assertSee('Acme Co')
        ->assertDontSee('Someone Else');
});

test('user can create a client', function () {
    $this->actingAs($this->user)
        ->post(route('clients.store'), [
            'name' => 'New Client',
            'company' => 'New Co',
            'email' => 'client@example.com',
            'phone' => '01234 567890',
            'notes' => 'Introduced via referral.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'user_id' => $this->user->id,
        'name' => 'New Client',
        'email' => 'client@example.com',
    ]);
});

test('client creation requires a name', function () {
    $this->actingAs($this->user)
        ->post(route('clients.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('user can search their clients', function () {
    Client::factory()->for($this->user)->create(['name' => 'Findable Client']);
    Client::factory()->for($this->user)->create(['name' => 'Hidden Partner']);

    $this->actingAs($this->user)
        ->get(route('clients.index', ['search' => 'Findable']))
        ->assertOk()
        ->assertSee('Findable Client')
        ->assertDontSee('Hidden Partner');
});

test('user cannot view another user\'s client', function () {
    $other = Client::factory()->create();

    $this->actingAs($this->user)
        ->get(route('clients.show', $other))
        ->assertForbidden();
});

test('user can update their client', function () {
    $client = Client::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->put(route('clients.update', $client), [
            'name' => 'Updated Name',
            'company' => $client->company,
        ])
        ->assertRedirect(route('clients.show', $client));

    expect($client->fresh()->name)->toBe('Updated Name');
});

test('user can delete their client', function () {
    $client = Client::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->delete(route('clients.destroy', $client))
        ->assertRedirect(route('clients.index'));

    $this->assertModelMissing($client);
});
