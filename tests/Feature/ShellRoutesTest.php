<?php

use App\Models\User;

/*
 * The four shell destinations share one gate, so they share one test. Adding a
 * route in a later phase means adding a string to the dataset.
 *
 * Settings is covered by tests/Feature/Settings/* and sits behind `auth` only,
 * so it is deliberately absent here.
 */
it('requires authentication', function (string $name) {
    $this->get(route($name))->assertRedirect(route('login'));
})->with(['garage', 'history', 'insights']);

it('requires a verified email address', function (string $name) {
    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route($name))->assertRedirect(route('verification.notice'));
})->with(['garage', 'history', 'insights']);

it('renders for a verified user', function (string $name) {
    $this->actingAs(User::factory()->create());

    $this->get(route($name))->assertOk();
})->with(['garage', 'history', 'insights']);
